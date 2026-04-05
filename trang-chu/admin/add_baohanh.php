<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy danh sách sản phẩm để chọn
$stmt_sp = $conn->query("SELECT id_sanpham, ten_sp FROM sanpham ORDER BY ten_sp ASC");
$sanphams = $stmt_sp->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt_last = $conn->query("SELECT id_baohanh FROM baohanh ORDER BY id_baohanh DESC LIMIT 1");
    $last = $stmt_last->fetchColumn();

    if ($last) {
        $number = (int)substr($last, 2);
        $number++;
    } else {
        $number = 1;
    }

    $id_baohanh = 'BH' . str_pad($number, 3, '0', STR_PAD_LEFT);
    $id_sanpham = $_POST['id_sanpham'];
    $thoi_gian_bh = (int)$_POST['thoi_gian_bh'];
    $mo_ta_bh = trim($_POST['mo_ta_bh']);

    // Kiểm tra mã bảo hành trùng
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM baohanh WHERE id_baohanh = ?");
    $stmt_check->execute([$id_baohanh]);
    if ($stmt_check->fetchColumn() > 0) {
        $error = "Mã bảo hành đã tồn tại. Vui lòng nhập mã khác.";
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO baohanh (id_baohanh, id_sanpham, thoi_gian_bh, mo_ta_bh) VALUES (?, ?, ?, ?)");
        $stmt_insert->execute([$id_baohanh, $id_sanpham, $thoi_gian_bh, $mo_ta_bh]);
        header("Location: baohanh.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm bảo hành</title>
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
            <a href="baohanh.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </span>
        </div>
    </nav>
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">THÊM BẢO HÀNH</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-4 rounded shadow-sm">

            <div class="mb-3">
                <label for="id_sanpham" class="form-label">Chọn sản phẩm</label>
                <select name="id_sanpham" id="id_sanpham" class="form-select" required>
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php foreach ($sanphams as $sp): ?>
                        <option value="<?= $sp['id_sanpham'] ?>"><?= htmlspecialchars($sp['ten_sp']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="thoi_gian_bh" class="form-label">Thời gian bảo hành (tháng)</label>
                <input type="number" name="thoi_gian_bh" id="thoi_gian_bh" class="form-control" required min="1">
            </div>

            <div class="mb-3">
                <label for="mo_ta_bh" class="form-label">Mô tả bảo hành</label>
                <textarea name="mo_ta_bh" id="mo_ta_bh" rows="4" class="form-control"></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="baohanh.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Thêm bảo hành</button>
            </div>
        </form>
    </div>
</body>

</html>