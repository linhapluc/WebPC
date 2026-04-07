<?php
require_once 'connect.php';
require_once 'sendmail.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Email không hợp lệ.";
    } else {
        $stmt = $conn->prepare("SELECT id_khachhang FROM khachhang WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $update = $conn->prepare("UPDATE khachhang SET otp_code = ?, otp_expiry = ? WHERE email = ?");
            $update->execute([$otp, $expiry, $email]);

            $body = "Mã xác thực của bạn là: <b>$otp</b><br>Mã có hiệu lực trong 5 phút.";
            if (sendOTPEmail($email, $body)) {
                header("Location: xacnhan_otp.php?email=" . urlencode($email));
                exit;
            } else {
                $message = "❌ Gửi email thất bại.";
            }
        } else {
            $message = "❌ Email không tồn tại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quên mật khẩu - PC Shop</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f4f6f8;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      background: #ffffff;
      padding: 36px 32px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      width: 100%;
      max-width: 380px;
    }

    h2 {
      text-align: center;
      margin-bottom: 24px;
      font-weight: 600;
      color: #222;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    input[type="email"] {
      padding: 12px 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      background-color: #fdfdfd;
    }

    input[type="email"]:focus {
      border-color: #2196f3;
      outline: none;
      background-color: #fff;
    }

    button {
      padding: 12px;
      background-color: #2196f3;
      color: white;
      font-size: 15px;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.2s;
    }

    button:hover {
      background-color: #1976d2;
    }

    .message-area {
      margin-top: 16px;
      font-size: 14px;
      color: #c62828;
      text-align: center;
      background-color: #fff3e0;
      padding: 10px;
      border-radius: 6px;
    }

    .back-link {
      text-align: center;
      margin-top: 16px;
      font-size: 14px;
    }

    .back-link a {
      color: #2196f3;
      text-decoration: none;
    }

    .back-link a:hover {
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .container {
        padding: 28px 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Quên mật khẩu</h2>

    <?php if ($message): ?>
      <div class="message-area"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Nhập email đã đăng ký" required>
      <button type="submit">Gửi mã xác thực</button>
    </form>

    <div class="back-link">
      <a href="dangnhap.php">Quay lại đăng nhập</a>
    </div>
  </div>
</body>
</html>
