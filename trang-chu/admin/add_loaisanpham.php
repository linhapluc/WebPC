<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_loaisp = strtoupper(trim($_POST['id_loaisp']));
    $ten_loai = trim($_POST['ten_loai']);

    if (empty($id_loaisp) || empty($ten_loai)) {
        $error = "Vui lòng nhập đầy đủ mã và tên loại sản phẩm.";
    } elseif (!preg_match('/^LSP\d{3}$/', $id_loaisp)) {
        $error = "Mã loại sản phẩm phải có định dạng LSPxxx (ví dụ: LSP015).";
    } else {
        // Kiểm tra trùng mã
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM loaisanpham WHERE id_loaisp = ?");
        $stmt_check->execute([$id_loaisp]);
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Mã loại sản phẩm đã tồn tại. Vui lòng nhập mã khác.";
        } else {
            // Thêm vào DB
            $stmt = $conn->prepare("INSERT INTO loaisanpham (id_loaisp, ten_loaisp) VALUES (?, ?)");
            $stmt->execute([$id_loaisp, $ten_loai]);
            header("Location: loaisanpham.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Loại Sản Phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
<body class="bg-light">

<nav class="navbar navbar-expand-lg bg-primary"> 
    <div class="container-fluid d-flex align-items-center">
        <a href="loaisanpham.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
            Trang Quản Trị Admin
        </a>
        <span class="admin-name ms-auto text-white fs-5 fw-semibold">👋 Xin chào, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></span>
    </div>
</nav>

<div class="container mt-5">
    <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">THÊM LOẠI SẢN PHẨM</h3>

    <div class="bg-white p-4 rounded shadow-sm mx-auto" style="max-width: 500px;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="id_loaisp" class="form-label">Mã Loại Sản Phẩm</label>
                <input type="text" name="id_loaisp" id="id_loaisp" class="form-control" required
                       value="<?= isset($_POST['id_loaisp']) ? htmlspecialchars($_POST['id_loaisp']) : '' ?>">
            </div>

            <div class="mb-3">
                <label for="ten_loai" class="form-label">Tên Loại Sản Phẩm</label>
                <input type="text" name="ten_loai" id="ten_loai" class="form-control" required
                       value="<?= isset($_POST['ten_loai']) ? htmlspecialchars($_POST['ten_loai']) : '' ?>">
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="loaisanpham.php" class="btn btn-secondary">← Quay lại</a>
                <button type="submit" class="btn btn-primary">Thêm loại</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
