<?php
require_once 'connect.php';

$email = $_GET['email'] ?? '';
$message = '';
$show_password_form = false;

// Bước 1: Kiểm tra OTP
if (isset($_POST['verify_otp'])) {
    $otp = trim($_POST['otp']);
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM khachhang WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "❌ Email không tồn tại.";
    } elseif ($user['otp_code'] !== $otp) {
        $message = "❌ Mã xác thực không đúng.";
    } elseif (strtotime($user['otp_expiry']) < time()) {
        $message = "❌ Mã xác thực đã hết hạn.";
    } else {
        $message = "✅ Xác thực thành công. Nhập mật khẩu mới.";
        $show_password_form = true;
    }
}

// Bước 2: Nhập mật khẩu mới
if (isset($_POST['reset_password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "❌ Mật khẩu không khớp.";
        $show_password_form = true;
    } elseif (strlen($password) < 8) {
        $message = "❌ Mật khẩu phải từ 8 ký tự.";
        $show_password_form = true;
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE khachhang SET mat_khau = ?, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
        $update->execute([$hashed, $email]);
        header("Location: dangnhap.php?reset=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Xác nhận OTP - PC Shop</title>
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
      max-width: 400px;
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

    input[type="text"],
    input[type="password"] {
      padding: 12px 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      background-color: #fdfdfd;
    }

    input:focus {
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
      margin-bottom: 16px;
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
    <h2>Xác nhận mã OTP</h2>

    <?php if ($message): ?>
      <div class="message-area"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!$show_password_form): ?>
      <form method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <input type="text" name="otp" placeholder="Nhập mã xác thực" required>
        <button type="submit" name="verify_otp">Xác nhận OTP</button>
      </form>
    <?php endif; ?>

    <?php if ($show_password_form): ?>
      <form method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <input type="password" name="password" placeholder="Mật khẩu mới" required>
        <input type="password" name="confirm" placeholder="Xác nhận mật khẩu" required>
        <button type="submit" name="reset_password">Đặt lại mật khẩu</button>
      </form>
    <?php endif; ?>

    <div class="back-link">
      <a href="dangnhap.php">Quay lại đăng nhập</a>
    </div>
  </div>
</body>
</html>
