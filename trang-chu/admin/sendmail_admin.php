<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../includes/phpmailer/PHPMailer.php';
require_once '../includes/phpmailer/SMTP.php';
require_once '../includes/phpmailer/Exception.php';

function sendOTPEmail($toEmail, $bodyContent) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yhiennguyeny@gmail.com'; // Thay bằng Gmail gửi OTP
        $mail->Password   = 'vpzfpgxyezixoegu';       // Thay bằng App Password Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('yhiennguyeny@gmail.com', 'PC Shop Admin');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Mã xác thực khôi phục mật khẩu Admin';
        $mail->Body    = '<div style="font-family: Poppins, sans-serif; font-size: 16px; color: #333;">
                            ' . $bodyContent . '
                          </div>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo 'Lỗi gửi email: ' . $mail->ErrorInfo;
        return false;
    }
}