<?php //Thêm//
session_start();
require 'includes/connect.php';
require_once __DIR__ . '/mailer.php';


$name    = $_POST['customer_name'];
$email   = $_POST['customer_email'];
$address = $_POST['customer_address'];
$phone   = $_POST['customer_phone'];
$payment = $_POST['payment_method'];
$notes   = $_POST['order_notes'];

// Lưu thông tin đơn hàng tạm thời vào session
$_SESSION['pending_order'] = [
    'name'    => $name,
    'email'   => $email,
    'address' => $address,
    'phone'   => $phone,
    'payment' => $payment,
    'notes'   => $notes,
    'cart'    => $_SESSION['cart']
];

// Tạo mã OTP 6 chữ số
$otp = rand(100000, 999999);
$_SESSION['otp_code']    = $otp;
$_SESSION['otp_expiry']  = time() + 300; // 5 phút

// Soạn nội dung email HTML
$bodyContent = "
    <p>Chào <strong>$name</strong>,</p>
    <p>Mã xác nhận đơn hàng của bạn là:</p>
    <h2 style='color: #E53935;'>$otp</h2>
    <p>Mã có hiệu lực trong 5 phút.</p>
    <p>Cảm ơn bạn đã mua hàng tại <strong>PC Shop</strong>!</p>
";

// Gửi mail
if (sendOTPEmail($email, $bodyContent)) {
    header("Location: confirm_otp.php");
    exit;
} else {
    echo "Không thể gửi email. Vui lòng thử lại.";
}
