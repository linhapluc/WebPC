<?php // vnpay_return.php
require 'includes/connect.php'; 
require 'vnpay/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$message = ''; $vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = []; foreach ($_GET as $key => $value) { if (substr($key, 0, 4) == "vnp_") { $inputData[$key] = $value; } }
unset($inputData['vnp_SecureHash']); ksort($inputData);
$hashData = ""; foreach ($inputData as $key => $value) { $hashData .= urlencode($key) . "=" . urlencode($value) . '&'; }
$hashData = rtrim($hashData, '&');
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

if ($secureHash == $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        $message = "Giao dịch được thực hiện thành công. Cảm ơn quý khách đã sử dụng dịch vụ!";
        $order_id = $_GET['vnp_TxnRef'];
        try {
            $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 'Đã thanh toán' WHERE id_donhang = ?");
            $stmt->execute([$order_id]);
            // Xóa giỏ hàng sau khi thanh toán thành công
            unset($_SESSION['cart']); 
        } catch (PDOException $e) { error_log("Lỗi cập nhật CSDL sau VNPAY: " . $e->getMessage()); }
    } else {
        $message = "Giao dịch không thành công. Mã lỗi: " . $_GET['vnp_ResponseCode'];
    }
} else { $message = "Chữ ký không hợp lệ!"; }

echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>Kết quả thanh toán</title><style>body{font-family:sans-serif; text-align:center; padding-top: 50px;} .container{max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;} a{color: #0d6efd; text-decoration: none;}</style></head><body><div class='container'><h1>Thông Báo Thanh Toán</h1><p>" . htmlspecialchars($message) . "</p><p><a href='trang-chu/index.php'>Quay về trang chủ</a></p></div></body></html>";
?>