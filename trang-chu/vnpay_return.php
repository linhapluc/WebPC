<?php
require '../includes/connect.php'; 
require '../vnpay/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$message = ''; $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
$inputData = []; foreach ($_GET as $key => $value) { if (substr($key, 0, 4) == "vnp_") { $inputData[$key] = $value; } }
unset($inputData['vnp_SecureHash']); ksort($inputData);
$hashData = ""; foreach ($inputData as $key => $value) { $hashData .= '&' . urlencode($key) . "=" . urlencode($value); }
$secureHash = hash_hmac('sha512', ltrim($hashData, '&'), $vnp_HashSecret);

if ($secureHash == $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        $message = "Giao dịch được thực hiện thành công. Cảm ơn quý khách đã sử dụng dịch vụ!";
        $order_id = $_GET['vnp_TxnRef'];
        try {
            // Cập nhật trạng thái 'Đã thanh toán' CHO ĐƠN HÀNG VNPAY, KHÔNG PHẢI COD
            $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 'Chờ xử lý' WHERE id_donhang = ? AND phuongthuc_thanhtoan = 'VNPAY'");
            $stmt->execute([$order_id]);
        } catch (PDOException $e) { error_log("Lỗi cập nhật CSDL sau VNPAY: " . $e->getMessage()); }
    } else {
        $message = "Giao dịch không thành công. Mã lỗi: " . ($_GET['vnp_ResponseCode'] ?? 'unknown');
    }
} else { $message = "Chữ ký không hợp lệ!"; }

// Hiển thị trang thông báo cho người dùng
include '../includes/header.php';
echo "<div class='container' style='text-align:center; padding: 50px 0;'><h1>Thông Báo Thanh Toán</h1><p>" . htmlspecialchars($message) . "</p><p><a href='index.php'>Quay về trang chủ</a></p></div>";
include '../includes/footer.php';
?>