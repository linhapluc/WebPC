<?php
require('.../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra nếu không phải admin
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}

// Lấy ID admin cần xóa
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: qladmin.php");
    exit();
}

$id = $_GET['id'];

// Không cho phép xóa chính mình
if ($id === $_SESSION['admin_name']) {
    echo "<script>alert('Bạn không thể tự xóa chính mình!'); window.location='qladmin.php';</script>";
    exit();
}

// Xóa admin
$stmt = $conn->prepare("DELETE FROM admins WHERE id_admin = ?");
$stmt->execute([$id]);

header("Location: qladmin.php");
exit();
?>
