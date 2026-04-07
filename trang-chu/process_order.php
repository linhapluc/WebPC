<?php
require 'connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: index.php'); exit();
}

if (!isset($_POST['payment_method'])) {
    die("Lỗi: Không nhận được phương thức thanh toán.");
}
if (empty($_SESSION['user_id'])) {
    die("Lỗi: Bạn chưa đăng nhập.");
}
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    die("Lỗi: Giỏ hàng trống.");
}

$payment_method = $_POST['payment_method']; // ví dụ: BANKING hoặc COD
$user_id = $_SESSION['user_id'];

$customer_name    = trim($_POST['customer_name'] ?? '');
$customer_email   = trim($_POST['customer_email'] ?? '');
$customer_address = trim($_POST['customer_address'] ?? '');
$customer_phone   = trim($_POST['customer_phone'] ?? '');
$order_notes      = trim($_POST['order_notes'] ?? '');

$total_price = (int)($_POST['total_amount_for_vnpay'] ?? 0);
if ($total_price <= 0) die("Lỗi: Tổng tiền không hợp lệ.");

$order_id = "DH" . date("YmdHis") . rand(100, 999);

// ✅ Map sang ENUM trong DB
$db_payment_method = ($payment_method === 'BANKING') ? 'Chuyển khoản' : 'Thanh toán khi nhận hàng';
$initial_status    = ($payment_method === 'BANKING') ? 'Chờ thanh toán' : 'Chờ xử lý';

try {
    $conn->beginTransaction();

    // 1) Insert DONHANG (header)
    $sql_order = "
        INSERT INTO donhang (
            id_donhang, id_khachhang, ngay_dat, tong_tien,
            ten_nguoinhan, diachi_giaohang, sdt_nguoinhan, email,
            ghi_chu, trang_thai, phuongthuc_thanhtoan
        ) VALUES (
            ?, ?, NOW(), ?,
            ?, ?, ?, ?,
            ?, ?, ?
        )
    ";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->execute([
        $order_id, $user_id, $total_price,
        $customer_name, $customer_address, $customer_phone, $customer_email,
        $order_notes, $initial_status, $db_payment_method
    ]);

    // 2) Insert CHITIETDONHANG (items)
    $sql_detail = "
        INSERT INTO chitietdonhang (
            id_chitietdh, id_donhang, id_sanpham, so_luong_mua, gia_luc_mua
        ) VALUES (?, ?, ?, ?, ?)
    ";
    $stmt_detail = $conn->prepare($sql_detail);

    // chuẩn hoá cart: cố gắng đọc các key phổ biến
   foreach ($_SESSION['cart'] as $idx => $item) {
    // Nếu trong $item không có id thì lấy luôn cái nhãn $idx làm id_sanpham
    $id_sanpham = $item['id_sanpham'] ?? $item['product_id'] ?? $item['id'] ?? $idx;
        $so_luong   = (int)($item['so_luong'] ?? $item['quantity'] ?? 1);
        $don_gia    = (int)($item['gia'] ?? $item['price'] ?? 0);

        $id_sanpham = is_string($id_sanpham) ? trim($id_sanpham) : $id_sanpham;

        if (empty($id_sanpham)) {
            throw new Exception("Cart item #$idx thiếu id_sanpham");
        }
        if ($so_luong <= 0) $so_luong = 1;
        if ($don_gia <= 0) {
            // nếu cart không có giá thì lấy giá hiện tại từ DB
            $stmtP = $conn->prepare("SELECT gia FROM sanpham WHERE id_sanpham = ?");
            $stmtP->execute([$id_sanpham]);
            $don_gia = (int)$stmtP->fetchColumn();
            if ($don_gia <= 0) {
                throw new Exception("Sản phẩm không tồn tại hoặc không có giá: $id_sanpham");
            }
        }

        // id chi tiết
        $id_chitietdh = "CT" . date("YmdHis") . rand(100, 999) . $idx;

        $stmt_detail->execute([$id_chitietdh, $order_id, $id_sanpham, $so_luong, $don_gia]);
    }

    $conn->commit();

} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    die("Lỗi lưu đơn hàng: " . $e->getMessage());
}

unset($_SESSION['cart']);

// ... (Đoạn code lưu đơn hàng vào bảng donhang và chitietdonhang giữ nguyên) ...

// ... sau khi đã lưu xong đơn hàng vào CSDL ...

if ($payment_method === 'MOMO') {
    // Lưu thông tin vào session để trang momo_pay.php lấy dữ liệu
    $_SESSION['momo_order_id'] = $order_id;
    $_SESSION['momo_amount'] = $total_price;
    
    // CHUYỂN HƯỚNG SANG TRANG MOMO
    header('Location: momo_pay.php'); 
    exit();
} 
elseif ($payment_method === 'BANKING') {
    // CHUYỂN HƯỚNG SANG TRANG NGÂN HÀNG (Ảnh bạn đang thấy)
    header('Location: banking_info.php?order_id=' . urlencode($order_id) . '&amount=' . $total_price);
    exit();
} else {
    // Thanh toán COD hoặc khác
    header('Location: order_success.php?order_id=' . urlencode($order_id));
    exit();
}
header('Location: order_success.php?order_id=' . urlencode($order_id));
exit();
?>
