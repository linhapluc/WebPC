<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}

// Kiểm tra có ID khách hàng cần xoá không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Thiếu ID khách hàng.");
}

$id = $_GET['id'];

// Thực hiện xoá khách hàng
try {
    $stmt = $conn->prepare("DELETE FROM khachhang WHERE id_khachhang = ?");
    $stmt->execute([$id]);
    header("Location: khachhang.php");
    exit();
} catch (PDOException $e) {
    echo "Lỗi khi xoá khách hàng: " . $e->getMessage();
}
?>
