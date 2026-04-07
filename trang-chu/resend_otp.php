<?php //Thêm//
session_start();
require_once 'includes/connect.php';
require_once 'mailer.php'; // đảm bảo đường dẫn đúng

// Kiểm tra xem có đơn hàng pending đang lưu trong session không
if (!isset($_SESSION['pending_order'], $_SESSION['user_id'])) {
    $_SESSION['otp_error'] = "Không thể gửi lại OTP. Vui lòng đặt lại đơn hàng.";
    header("Location: confirm_otp.php");
    exit;
}

$order = $_SESSION['pending_order'];
$otp = rand(100000, 999999);

// Cập nhật OTP mới và thời hạn
$_SESSION['otp_code'] = $otp;
$_SESSION['otp_expiry'] = time() + 300; // 5 phút

// Soạn nội dung email gửi lại
$email_subject = "Mã xác thực đơn hàng mới từ PC Shop";
$email_body = "
  <p>Xin chào <strong>{$order['name']}</strong>,</p>
  <p>Mã OTP mới để xác nhận đơn hàng của bạn là:</p>
  <h2 style='color:#1976D2;'>$otp</h2>
  <p>Mã có hiệu lực trong 5 phút.</p>
  <p>Trân trọng,<br>PC Shop</p>
";

// Gửi lại OTP
if (sendOTPEmail($order['email'], $email_body)) {
    $_SESSION['otp_error'] = "Mã OTP mới đã được gửi lại.";
} else {
    $_SESSION['otp_error'] = "Không thể gửi lại mã OTP. Vui lòng thử lại sau.";
}

header("Location: confirm_otp.php");
exit;
