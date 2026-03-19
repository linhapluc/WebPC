<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}

// Lấy ID sản phẩm
if (!isset($_GET['id'])) {
    die("Thiếu ID sản phẩm");
}

$id = $_GET['id'];

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM sanpham WHERE id_sanpham = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Không tìm thấy sản phẩm.");
}

// Xử lý khi nhấn nút xác nhận xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM sanpham WHERE id_sanpham = ?");
    $stmt->execute([$id]);
    header('Location: sanpham.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xóa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title text-danger">Xác nhận xóa sản phẩm</h3>
            <p>Bạn có chắc chắn muốn xóa sản phẩm <strong><?= htmlspecialchars($product['ten_sp']) ?></strong> (Mã: <?= htmlspecialchars($product['id_sanpham']) ?>)?</p>
            
            <form method="POST">
                <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                <a href="sanpham.php" class="btn btn-secondary ms-2">Hủy</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
