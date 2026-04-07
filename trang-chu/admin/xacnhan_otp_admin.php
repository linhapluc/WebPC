<?php
require_once '../includes/connect.php';

$email = $_GET['email'] ?? '';
$message = '';
$show_password_form = false;

if (isset($_POST['verify_otp'])) {
    $otp = strtoupper(trim($_POST['otp']));
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM admins WHERE email = ?");
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
        $update = $conn->prepare("UPDATE admins SET password = ?, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
        $update->execute([$hashed, $email]);
        header("Location: index.php?reset=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Xác nhận OTP - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
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

    input[type="text"],
    input[type="password"] {
      padding: 12px 14px;
      font-size: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
      transition: border-color 0.3s;
    }

    input:focus {
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

    .message-area {
      padding: 10px;
      background-color: #fff3e0;
      border-left: 4px solid #ef6c00;
      color: #c62828;
      font-size: 14px;
      border-radius: 6px;
      text-align: center;
      margin-bottom: 16px;
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
        padding: 32px 20px;
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
        <input type="text" name="otp" placeholder="Nhập mã xác thực" pattern="\d{6}" inputmode="numeric" required>
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