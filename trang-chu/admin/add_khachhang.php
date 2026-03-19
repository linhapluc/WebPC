<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt_last = $conn->query("SELECT id_khachhang FROM khachhang ORDER BY id_khachhang DESC LIMIT 1");
    $last = $stmt_last->fetchColumn();

    if ($last && preg_match('/^KH(\d+)$/', $last, $matches)) {
        $number = (int)$matches[1] + 1;
    } else {
        $number = 1;
    }

    $id = 'KH' . str_pad($number, 3, '0', STR_PAD_LEFT);
    $name = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $password = trim($_POST['mat_khau']);
    $confirm_password = trim($_POST['xacnhan_mat_khau']);
    $phone = trim($_POST['so_dien_thoai']);

    // Kiểm tra dữ liệu
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ thông tin bắt buộc.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu không khớp.";
    } else {
        // Kiểm tra email đã tồn tại
        $stmt_email = $conn->prepare("SELECT COUNT(*) FROM khachhang WHERE email = ?");
        $stmt_email->execute([$email]);
        if ($stmt_email->fetchColumn() > 0) {
            $error = "Email đã tồn tại.";
        }

        // Kiểm tra số điện thoại đã tồn tại
        elseif (!empty($phone)) {
            $stmt_phone = $conn->prepare("SELECT COUNT(*) FROM khachhang WHERE so_dien_thoai = ?");
            $stmt_phone->execute([$phone]);
            if ($stmt_phone->fetchColumn() > 0) {
                $error = "Số điện thoại đã được sử dụng.";
            }
        }

        // Thêm nếu không có lỗi
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO khachhang (id_khachhang, ho_ten, email, mat_khau, so_dien_thoai) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $name, $email, $password, $phone]);
            header("Location: khachhang.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Khách Hàng</title>
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
            <a href="khachhang.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">👋 Xin chào, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></span>
        </div>
    </nav>
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">THÊM KHÁCH HÀNG</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-4 rounded shadow-sm">

            <div class="mb-3">
                <label for="ho_ten" class="form-label">Họ tên</label>
                <input type="text" name="ho_ten" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="mat_khau" class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <input type="password" id="mat_khau" name="mat_khau" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('mat_khau')">👁</button>
                </div>
            </div>

            <div class="mb-3">
                <label for="xacnhan_mat_khau" class="form-label">Xác nhận mật khẩu</label>
                <div class="input-group">
                    <input type="password" id="xacnhan_mat_khau" name="xacnhan_mat_khau" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('xacnhan_mat_khau')">👁</button>
                </div>
            </div>


            <div class="mb-3">
                <label for="so_dien_thoai" class="form-label">Số điện thoại</label>
                <input type="text" name="so_dien_thoai" class="form-control">
            </div>

            <div class="text-end">
                <a href="khachhang.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Thêm</button>
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