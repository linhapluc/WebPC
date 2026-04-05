<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Thiếu mã khuyến mãi.");
}

$id_km = $_GET['id'];

// Lấy dữ liệu khuyến mãi
$stmt = $conn->prepare("SELECT * FROM khuyenmai WHERE id_khuyenmai = ?");
$stmt->execute([$id_km]);
$km = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$km) die("Khuyến mãi không tồn tại!");

// Lấy danh sách sản phẩm
$sp_stmt = $conn->query("SELECT id_sanpham, ten_sp FROM sanpham ORDER BY ten_sp");
$sanphams = $sp_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sanpham = $_POST['id_sanpham'] ?? '';
    $ten_km = trim($_POST['ten_km']) ?? '';
    $giam_percent = $_POST['giam_gia_percent'] ?? 0;
    $giam_tien = $_POST['giam_gia_tien'] ?? 0;
    $ngay_bd = $_POST['ngay_bat_dau'] ?? '';
    $ngay_kt = $_POST['ngay_ket_thuc'] ?? '';

    if (empty($id_sanpham) || empty($ten_km) || empty($ngay_bd) || empty($ngay_kt)) {
        $error = "Vui lòng nhập đủ thông tin.";
    } else {
        $stmt = $conn->prepare("UPDATE khuyenmai SET id_sanpham=?, ten_km=?, giam_gia_percent=?, giam_gia_tien=?, ngay_bat_dau=?, ngay_ket_thuc=? WHERE id_khuyenmai=?");
        $stmt->execute([$id_sanpham, $ten_km, $giam_percent, $giam_tien, $ngay_bd, $ngay_kt, $id_km]);
        header("Location: khuyenmai.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Khuyến Mãi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #007bff;
            padding: 16px 24px;
        }

        .navbar-brand {
            font-weight: bold;
            color: white;
            font-size: 24px;
        }

        .product-wrapper {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            gap: 40px;
            margin: 40px auto;
            max-width: 1200px;
            padding: 0 20px;
        }

        .product-image-container {
            flex: 1;
            max-width: 700px;
            padding: 20px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .product-image-container img {
            width: 100%;
            max-height: 700px;
            object-fit: contain;
        }

        .product-info-container {
            flex: 1;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .product-description {
            margin-top: 40px;
            max-width: 1200px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            line-height: 1.6;
        }

        .product-description h4 {
            margin-bottom: 10px;
            color: #007bff;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .back-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="bg-light">
   <nav class="navbar navbar-expand-lg bg-primary"> 
        <div class="container-fluid d-flex align-items-center">
            <span class="navbar-brand text-white fs-4 fw-bold">Trang Quản Trị Admin</span>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </span>
        </div>
    </nav>
<div class="container mt-5">
    <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">SỬA KHUYẾN MÃI</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label for="id_sanpham" class="form-label">Sản phẩm</label>
            <select name="id_sanpham" class="form-select" required>
                <?php foreach ($sanphams as $sp): ?>
                    <option value="<?= $sp['id_sanpham'] ?>" <?= $sp['id_sanpham'] === $km['id_sanpham'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sp['ten_sp']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="ten_km" class="form-label">Tên khuyến mãi</label>
            <input type="text" name="ten_km" class="form-control" value="<?= htmlspecialchars($km['ten_km']) ?>" required>
        </div>

        <div class="mb-3 row">
            <div class="col">
                <label for="giam_gia_percent" class="form-label">Giảm giá (%)</label>
                <input type="number" name="giam_gia_percent" class="form-control" value="<?= (int)$km['giam_gia_percent'] ?>">
            </div>
            <div class="col">
                <label for="giam_gia_tien" class="form-label">Giảm giá tiền (VNĐ)</label>
                <input type="number" name="giam_gia_tien" class="form-control" value="<?= (int)$km['giam_gia_tien'] ?>">
            </div>
        </div>

        <div class="mb-3 row">
            <div class="col">
                <label for="ngay_bat_dau" class="form-label">Ngày bắt đầu</label>
                <input type="date" name="ngay_bat_dau" class="form-control" value="<?= $km['ngay_bat_dau'] ?>" required>
            </div>
            <div class="col">
                <label for="ngay_ket_thuc" class="form-label">Ngày kết thúc</label>
                <input type="date" name="ngay_ket_thuc" class="form-control" value="<?= $km['ngay_ket_thuc'] ?>" required>
            </div>
        </div>

        <div class="text-end">
            <a href="khuyenmai.php" class="btn btn-secondary">Quay lại</a>
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
    </form>
</div>
</body>
</html>
