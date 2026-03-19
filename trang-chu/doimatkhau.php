<?php
require 'connect.php';
$message = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token không hợp lệ.");
}

// Kiểm tra token
$stmt = $conn->prepare("SELECT id_khachhang FROM khachhang WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Link đặt lại mật khẩu đã hết hạn hoặc không hợp lệ.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($newPass !== $confirm) {
        $message = "Mật khẩu không khớp.";
    } elseif (strlen($newPass) < 8) {
        $message = "Mật khẩu quá ngắn.";
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE khachhang SET mat_khau = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id_khachhang = ?");
        $update->execute([$hashed, $user['id_khachhang']]);
        $message = "✅ Đặt lại mật khẩu thành công. <a href='dangnhap.php'>Đăng nhập</a>";
    }
}
?>

<form method="POST">
    <h3>Đặt lại mật khẩu</h3>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
    <button type="submit">Đặt lại mật khẩu</button>
</form>
