<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}
$adminName = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            overflow-y: auto;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .content {
            padding: 30px;
        }

        .navbar {
             background-color: #007bff;  
            padding: 16px 24px;
        }

        .navbar-brand {
            font-weight: bold;
            color: white;
            font-size: 24px;
        }

        .admin-name {
            margin-left: auto;
            color: white;
        }

        .table img {
            width: 80px;
        }

        .btn-add {
            background-color: green;
            color: white;
        }

        .btn-add:hover {
            background-color: darkgreen;
        }

        .search-form input[type="text"] {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .search-form button {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        tr.clickable-row {
            cursor: pointer;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-primary"> 
        <div class="container-fluid d-flex align-items-center">
            <span class="navbar-brand text-white fs-4 fw-bold">Trang Quản Trị Admin</span>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <a href="home.php"><i class="fas fa-home me-2"></i> Trang chủ</a>
                <a href="sanpham.php"><i class="fas fa-box me-2"></i> Quản lý sản phẩm</a>
                <a href="loaisanpham.php"><i class="fas fa-layer-group me-2"></i> Loại sản phẩm</a>
                <a href="donhang.php"><i class="fas fa-receipt me-2"></i> Đơn hàng</a>
                <a href="baohanh.php"><i class="fas fa-shield-alt me-2"></i> Bảo hành</a>
                <a href="khuyenmai.php"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
                <a href="khachhang.php"><i class="fas fa-users me-2"></i> Khách hàng</a>
                <a href="danhgia.php"><i class="fas fa-star me-2"></i> Đánh giá</a>
                <a href="qladmin.php"><i class="fas fa-user-shield me-2"></i> Admin</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
            </div>

            <!-- Content -->
            <div class="col-md-10 content">
                <h3>Chào mừng bạn đến trang quản trị!</h3>
                <p>Vui lòng chọn một chức năng từ menu bên trái để bắt đầu.</p>
            </div>
        </div>
    </div>

</body>

</html>