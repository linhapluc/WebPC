<?php
// trang-chu/process_order.php
require 'connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: index.php');
    exit();
}

// 1) Check login
if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập. Vui lòng đăng nhập để thanh toán.");
}
$user_id = $_SESSION['user_id'];

// 2) Validate POST
$requiredFields = ['customer_name', 'customer_email', 'customer_address', 'customer_phone', 'payment_method', 'total_amount_for_vnpay'];
foreach ($requiredFields as $f) {
    if (!isset($_POST[$f]) || trim($_POST[$f]) === '') {
        die("Dữ liệu gửi lên không đủ ($f). Vui lòng thử lại từ trang thanh toán.");
    }
}

$customer_name    = trim($_POST['customer_name']);
$customer_email   = trim($_POST['customer_email']);
$customer_address = trim($_POST['customer_address']);
$customer_phone   = trim($_POST['customer_phone']);
$payment_method   = trim($_POST['payment_method']); // COD | BANKING | VNPAY
$order_notes      = isset($_POST['order_notes']) ? trim($_POST['order_notes']) : '';
$total_price      = (int)$_POST['total_amount_for_vnpay'];

// 3) Check cart (schema hiện tại chỉ hợp lý nhất khi mỗi đơn 1 sản phẩm)
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Giỏ hàng trống. Không thể tạo đơn hàng.");
}
if (count($_SESSION['cart']) !== 1) {
    die("Hiện tại hệ thống chỉ hỗ trợ 1 sản phẩm mỗi đơn (do thiết kế bảng donhang). Vui lòng thanh toán từng sản phẩm.");
}

$id_sanpham = array_key_first($_SESSION['cart']);
$so_luong   = (int)$_SESSION['cart'][$id_sanpham];
if ($so_luong <= 0) $so_luong = 1;

// 4) Map payment_method -> ENUM trong DB
// DB chỉ cho phép: COD / Chuyển khoản
// VNPAY cũng map về 'Chuyển khoản' để insert không lỗi
if ($payment_method === 'COD') {
    $phuongthuc_thanhtoan = 'Thanh toán khi nhận hàng';
} elseif ($payment_method === 'BANKING' || $payment_method === 'VNPAY') {
    $phuongthuc_thanhtoan = 'Chuyển khoản';
} elseif ($payment_method === 'Thanh toán khi nhận hàng' || $payment_method === 'Chuyển khoản') {
    $phuongthuc_thanhtoan = $payment_method;
} 
elseif ($payment_method === 'MOMO') {
    $phuongthuc_thanhtoan = 'MOMO';
}else {
    die("Phương thức thanh toán không hợp lệ.");
}

// 5) Order status (theo DB default 'Pending', bạn có thể giữ logic riêng)
$initial_status = 'Pending';

// 6) Generate order id (max 20 chars)
$order_id = "ORD" . date("YmdHis") . rand(100, 999);

try {
    $conn->beginTransaction();

    // Check FK: khachhang tồn tại?
    $stmtCheckKH = $conn->prepare("SELECT 1 FROM khachhang WHERE id_khachhang = ? LIMIT 1");
    $stmtCheckKH->execute([$user_id]);
    if (!$stmtCheckKH->fetchColumn()) {
        throw new Exception("Khách hàng không tồn tại trong CSDL (id_khachhang = $user_id).");
    }

    // Check FK: sanpham tồn tại?
    $stmtCheckSP = $conn->prepare("SELECT 1 FROM sanpham WHERE id_sanpham = ? LIMIT 1");
    $stmtCheckSP->execute([$id_sanpham]);
    if (!$stmtCheckSP->fetchColumn()) {
        throw new Exception("Sản phẩm không tồn tại (id_sanpham = $id_sanpham).");
    }

    // Insert theo schema mới
    $sql_order = "INSERT INTO donhang
        (id_donhang, id_khachhang, id_sanpham, so_luong, ngay_dat, tong_tien,
         ten_nguoinhan, diachi_giaohang, sdt_nguoinhan, email, ghi_chu,
         trang_thai, phuongthuc_thanhtoan)
        VALUES
        (?, ?, ?, ?, NOW(), ?,
         ?, ?, ?, ?, ?,
         ?, ?)";

    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->execute([
        $order_id,
        $user_id,
        $id_sanpham,
        $so_luong,
        $total_price,

        $customer_name,
        $customer_address,
        $customer_phone,
        $customer_email,
        $order_notes,

        $initial_status,
        $phuongthuc_thanhtoan
    ]);

    $conn->commit();

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    die("Lỗi lưu đơn hàng: " . $e->getMessage());
}

// 7) Clear cart
unset($_SESSION['cart']);

// 8) Redirect theo payment_method (GIỮ VNPAY)
if ($payment_method === 'MOMO') {
    $_SESSION['order_id_for_momo'] = $order_id;
    $_SESSION['total_amount_for_momo'] = $total_price;
    // Chuyển hướng đến file momopay.php trong thư mục momo
    header('Location: momo/momopay.php');
    exit();
} 
if ($payment_method === 'VNPAY') {
    $_SESSION['order_id_for_vnpay'] = $order_id;
    $_SESSION['total_amount_for_vnpay'] = $total_price;

    header('Location: vnpay/vnpay_create_payment.php');
    exit();
}

if ($payment_method === 'BANKING') {
    header('Location: banking_info.php?order_id=' . urlencode($order_id) . '&amount=' . urlencode($total_price));
    exit();
}

// COD
header('Location: order_success.php?order_id=' . urlencode($order_id));
exit();
?>
