<?php //Thêm//
session_start();
require 'includes/connect.php';
require_once __DIR__ . '/mailer.php';

// Kiểm tra OTP
if (!isset($_SESSION['otp_code']) || time() > $_SESSION['otp_expiry']) {
    $_SESSION['otp_error'] = "Mã OTP đã hết hạn. Vui lòng đặt lại đơn hàng.";
    header("Location: confirm_otp.php");
    exit;
}

if ($_POST['otp_input'] != $_SESSION['otp_code']) {
    $_SESSION['otp_error'] = "Mã OTP không đúng. Vui lòng thử lại.";
    header("Location: confirm_otp.php");
    exit;
}

// Kiểm tra dữ liệu đơn hàng
if (!isset($_SESSION['pending_order'], $_SESSION['user_id'])) {
    die("Lỗi: Phiên đặt hàng không tồn tại hoặc đã hết hạn.");
}

$order = $_SESSION['pending_order'];
$user_id = $_SESSION['user_id'];

// Tạo mã đơn hàng duy nhất
function generateOrderId($prefix = 'DH')
{
    return $prefix . strtoupper(uniqid());
}

$order_id = generateOrderId();

$method_map = [
    'COD' => 'Thanh toán khi nhận hàng',
];

$order['payment'] = $method_map[$order['payment']] ?? $order['payment'];
// Tính tổng tiền đơn hàng
$total_amount = 0;

$stmt = $conn->prepare("SELECT gia FROM sanpham WHERE id_sanpham = ?");
$stmt_km = $conn->prepare("
    SELECT giam_gia_percent, giam_gia_tien 
    FROM khuyenmai 
    WHERE id_sanpham = ? AND CURDATE() BETWEEN ngay_bat_dau AND ngay_ket_thuc
    LIMIT 1
");

foreach ($order['cart'] as $id_sp => $qty) {
    $stmt->execute([$id_sp]);
    $gia = $stmt->fetchColumn();

    $stmt_km->execute([$id_sp]);
    $km = $stmt_km->fetch(PDO::FETCH_ASSOC);

    if ($km) {
        if ($km['giam_gia_tien'] > 0) {
            $gia -= $km['giam_gia_tien'];
        } elseif ($km['giam_gia_percent'] > 0) {
            $gia -= $gia * ($km['giam_gia_percent'] / 100);
        }
        $gia = max(0, $gia); // đảm bảo không âm
    }

    $total_amount += $gia * $qty;
}

try {
    $conn->beginTransaction();

    // Thêm vào bảng `donhang`
    $stmt = $conn->prepare("INSERT INTO donhang (
        id_donhang, id_khachhang, ngay_dat, tong_tien,
        ten_nguoinhan, diachi_giaohang, sdt_nguoinhan, email,
        ghi_chu, trang_thai, phuongthuc_thanhtoan, xac_nhan_admin,
        otp_code, otp_expiry
    ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, 'Chờ Xử Lý', ?, 1, ?, ?)");

    $stmt->execute([
        $order_id,
        $user_id,
        $total_amount,
        $order['name'],
        $order['address'],
        $order['phone'],
        $order['email'],
        $order['notes'],
        $order['payment'],
        $_SESSION['otp_code'],
        date('Y-m-d H:i:s', $_SESSION['otp_expiry'])
    ]);

    // Thêm vào bảng `chitietdonhang`
    // Chuẩn bị câu lệnh trước vòng lặp
    // Chuẩn bị câu lệnh trước vòng lặp
    $stmt_ct = $conn->prepare("
    INSERT INTO chitietdonhang (
        id_chitietdh, id_donhang, id_sanpham, so_luong_mua, gia_luc_mua
    ) VALUES (?, ?, ?, ?, ?)
");

    $stmt_km = $conn->prepare("
    SELECT giam_gia_percent, giam_gia_tien 
    FROM khuyenmai 
    WHERE id_sanpham = ? 
      AND CURDATE() BETWEEN ngay_bat_dau AND ngay_ket_thuc
    LIMIT 1
");

    foreach ($order['cart'] as $id_sp => $qty) {
        // Lấy giá gốc
        $stmt_gia = $conn->prepare("SELECT gia FROM sanpham WHERE id_sanpham = ?");
        $stmt_gia->execute([$id_sp]);
        $gia = (int)$stmt_gia->fetchColumn();

        // Áp dụng khuyến mãi nếu có
        $stmt_km->execute([$id_sp]);
        $km = $stmt_km->fetch(PDO::FETCH_ASSOC);

        if ($km) {
            if ((int)$km['giam_gia_tien'] > 0) {
                $gia -= (int)$km['giam_gia_tien'];
            } elseif ((int)$km['giam_gia_percent'] > 0) {
                $gia -= $gia * ((int)$km['giam_gia_percent'] / 100);
            }
            $gia = max(0, $gia);
        }

        // Tạo id_chitietdh tối đa 20 ký tự: CTDH + thời gian + random
        $timestamp = date('His'); // giờ-phút-giây
        $rand = rand(1000, 9999);
        $id_chitietdh = 'CT' . $timestamp . $rand;

        // Thêm vào bảng chi tiết đơn hàng
        $stmt_ct->execute([$id_chitietdh, $order_id, $id_sp, $qty, $gia]);
    }






    $conn->commit();

    // Dọn dẹp session
    unset($_SESSION['cart'], $_SESSION['pending_order'], $_SESSION['otp_code'], $_SESSION['otp_expiry']);
    $success = true;
} catch (PDOException $e) {
    $conn->rollBack();
    $error_message = "Lỗi khi lưu đơn hàng: " . $e->getMessage();
}

// Nội dung gửi email xác nhận đơn hàng
// Map phương thức thanh toán sang dạng hiển thị
function getPaymentMethodText($code)
{
    return [
        'COD' => 'Thanh toán khi nhận hàng',
        'BANK' => 'Chuyển khoản ngân hàng',
        'MOMO' => 'Ví MoMo',
    ][$code] ?? $code;
}

$display_payment = getPaymentMethodText($order['payment']);
$total_formatted = number_format($total_amount, 0, ',', '.');

// Soạn nội dung email xác nhận
$email_subject = "Xác nhận đơn hàng từ PC Shop";
$email_body = "
    <p>Xin chào <strong>{$order['name']}</strong>,</p>
    <p>Cảm ơn bạn đã đặt hàng tại <strong>PC Shop Nasa</strong>.</p>
    <p>Thông tin đơn hàng của bạn như sau:</p>
    <ul>
        <li><strong>Mã đơn hàng:</strong> {$order_id}</li>
        <li><strong>Tổng tiền:</strong> {$total_formatted}đ</li>
        <li><strong>Phương thức thanh toán:</strong> {$display_payment}</li>
    </ul>
    <p>Chúng tôi sẽ xử lý và liên hệ với bạn trong thời gian sớm nhất.</p>
    <p>Trân trọng,<br><strong>PC Shop Nasa</strong></p>
";

// Gửi email xác nhận đơn hàng
sendOTPEmail($order['email'], $email_body);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Kết quả đặt hàng</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .result-box {
            background: #fff;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            max-width: 700px;
            width: 90%;
            text-align: center;
        }

        .result-box h2 {
            color: #2E7D32;
            margin-bottom: 15px;
        }

        .result-box p {
            font-size: 17px;
            color: #333;
            line-height: 1.6;
        }

        .error-box h2 {
            color: #D32F2F;
        }

        a.btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 24px;
            background-color: #1976D2;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        a.btn:hover {
            background-color: #1565C0;
        }
    </style>
</head>

<body>
    <div class="result-box <?php echo isset($error_message) ? 'error-box' : ''; ?>">
        <?php if (isset($success)): ?>
            <h2>✅ Đặt hàng thành công</h2>
            <p>Mã đơn hàng của bạn là: <strong><?= htmlspecialchars($order_id) ?></strong></p>
            <a href="index.php" class="btn">Về trang chủ</a>
        <?php else: ?>
            <h2>❌ Đặt hàng thất bại</h2>
            <p><?= htmlspecialchars($error_message ?? 'Đã có lỗi xảy ra.') ?></p>
            <a href="checkout.php" class="btn">Thử lại</a>
        <?php endif; ?>
    </div>
</body>

</html>