<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt_last = $conn->query("SELECT id_admin FROM admins WHERE id_admin LIKE 'AD%' ORDER BY id_admin DESC LIMIT 1");
    $last = $stmt_last->fetchColumn();

    if ($last && preg_match('/^AD(\d+)$/', $last, $matches)) {
        $number = (int)$matches[1] + 1;
    } else {
        $number = 1;
    }

    $id_admin = 'AD' . str_pad($number, 3, '0', STR_PAD_LEFT);
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);


    if (empty($name) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu không khớp.";
    }
    // Kiểm tra rỗng
    if (empty($id_admin) || empty($name) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        // Kiểm tra trùng mã admin
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE id_admin = ?");
        $stmt->execute([$id_admin]);
        $exists_id = $stmt->fetchColumn();

        // Kiểm tra trùng tên admin
        $stmt2 = $conn->prepare("SELECT COUNT(*) FROM admins WHERE name = ?");
        $stmt2->execute([$name]);
        $exists_name = $stmt2->fetchColumn();

        if ($exists_id > 0) {
            $error = "❌ Mã admin đã tồn tại.";
        } elseif ($exists_name > 0) {
            $error = "❌ Tên admin đã được sử dụng.";
        } else {
            // Thêm admin mới
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO admins (id_admin, name, password) VALUES (?, ?, ?)");
            $stmt_insert->execute([$id_admin, $name, $hashed_password]);
            header("Location: qladmin.php");
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Admin</title>
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

<body style="background-color: #f8f9fa;">
    <nav class="navbar navbar-expand-lg bg-primary">
        <div class="container-fluid d-flex align-items-center">
            <a href="qladmin.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">👋 Xin chào, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></span>
        </div>
    </nav>

    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">THÊM QUẢN TRỊ VIÊN</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-4 shadow rounded" style="max-width: 600px; margin: 0 auto;">
            <div class="mb-3">
                <label for="name" class="form-label">Tên admin</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">👁</button>
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password')">👁</button>
                </div>
            </div>

            <div class="text-end">
                <a href="qladmin.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Thêm admin</button>
            </div>
        </form>
    </div>
</body>

<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        input.type = input.type === "password" ? "text" : "password";
    }
</script>

</html>