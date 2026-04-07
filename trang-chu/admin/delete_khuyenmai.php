<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}

// Kiểm tra id khuyến mãi
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Thiếu mã khuyến mãi.");
}

$id_khuyenmai = $_GET['id'];

// Xóa khuyến mãi
$stmt = $conn->prepare("DELETE FROM khuyenmai WHERE id_khuyenmai = ?");
$stmt->execute([$id_khuyenmai]);

// Chuyển hướng về trang danh sách khuyến mãi
header("Location: khuyenmai.php");
exit();
?>
