<?php
// Trong file: WebPC/admin/index.php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu admin chưa đăng nhập thì chuyển hướng về trang đăng nhập
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}

// Kiểm tra nếu không có ID đơn hàng, chuyển hướng về trang quản lý đơn hàng
if (!isset($_GET['id'])) {
    header("Location: donhang.php");
    exit();
}

$id = $_GET['id'];

// Xóa đơn hàng
$stmt = $conn->prepare("DELETE FROM donhang WHERE id = ?");
$stmt->execute([$id]);

// Chuyển hướng về trang quản lý đơn hàng sau khi xóa
header("Location: donhang.php");
exit();
