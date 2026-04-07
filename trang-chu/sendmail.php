<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load các file thư viện PHPMailer
require __DIR__ . '/../trang-chu/includes/phpmailer/PHPMailer.php';
require __DIR__ . '/../trang-chu/includes/phpmailer/SMTP.php';
require __DIR__ . '/../trang-chu/includes/phpmailer/Exception.php';

function sendOTPEmail($toEmail, $bodyContent)
{
    $mail = new PHPMailer(true);

    try {
      
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yhiennguyeny@gmail.com';         // Gmail bạn
        $mail->Password   = 'vpzfpgxyezixoegu';           // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('yourgmail@gmail.com', 'PC Shop');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Mã xác thực khôi phục mật khẩu';
        $mail->Body    = '
  <html>
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: Poppins, sans-serif; font-size: 16px; color: #333; }
      </style>
    </head>
    <body>
      ' . $bodyContent . '
    </body>
  </html>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "LỖI GỬI MAIL: " . $mail->ErrorInfo; // In lỗi chi tiết
        return false;
    }
}
