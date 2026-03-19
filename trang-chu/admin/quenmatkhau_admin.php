<?php
require_once '../includes/connect.php';
require_once 'sendmail_admin.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Email không hợp lệ.";
    } else {
        $stmt = $conn->prepare("SELECT id_admin FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $update = $conn->prepare("UPDATE admins SET otp_code = ?, otp_expiry = ? WHERE email = ?");
            $update->execute([$otp, $expiry, $email]);

            $body = "Mã xác thực để đặt lại mật khẩu quản trị viên là: <b>$otp</b><br>Mã có hiệu lực trong 5 phút.";
            if (sendOTPEmail($email, $body)) {
                header("Location: xacnhan_otp_admin.php?email=" . urlencode($email));
                exit;
            } else {
                $message = "❌ Không gửi được email. Vui lòng kiểm tra cấu hình hoặc thử lại.";
            }
        } else {
            $message = "❌ Email không tồn tại trong hệ thống quản trị.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quên mật khẩu Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #e3f2fd, #bbdefb);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .container {
      background-color: white;
      padding: 40px 30px;
      border-radius: 16px;
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
    }

    h2 {
      text-align: center;
      color: #1565c0;
      margin-bottom: 24px;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    input[type="email"] {
      padding: 12px 14px;
      font-size: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
      transition: border-color 0.3s;
    }

    input[type="email"]:focus {
      border-color: #42a5f5;
      background-color: #f0f9ff;
    }

    button {
      padding: 12px;
      background: linear-gradient(to right, #42a5f5, #1e88e5);
      color: white;
      font-weight: 600;
      font-size: 15px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background: linear-gradient(to right, #1e88e5, #1565c0);
    }

    .message {
      padding: 10px;
      background-color: #fff3e0;
      border-left: 4px solid #ef6c00;
      color: #c62828;
      font-size: 14px;
      border-radius: 6px;
      text-align: center;
    }

    @media (max-width: 480px) {
      .container {
        padding: 32px 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Khôi phục mật khẩu Admin</h2>
    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Nhập email quản trị viên" required autofocus>
      <button type="submit">Gửi mã xác thực</button>
    </form>
  </div>
</body>
</html>
