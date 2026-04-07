<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}

// Kiểm tra tham số ID
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // Xóa đánh giá
    $stmt = $conn->prepare("DELETE FROM danhgia WHERE id_danhgia = ?");
    $stmt->execute([$id]);
}

// Quay lại danh sách đánh giá
header("Location: danhgia.php");
exit();
