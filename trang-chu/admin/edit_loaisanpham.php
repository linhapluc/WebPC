<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
$success = '';

// Kiểm tra ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Thiếu mã loại sản phẩm.");
}

$old_id = $_GET['id'];

// Lấy thông tin loại sản phẩm hiện tại
$stmt = $conn->prepare("SELECT * FROM loaisanpham WHERE id_loaisp = ?");
$stmt->execute([$old_id]);
$loai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loai) {
    die("Không tìm thấy loại sản phẩm.");
}

// Xử lý khi cập nhật
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_id = trim($_POST['id_loaisp']);
    $new_name = trim($_POST['ten_loaisp']);

    if (empty($new_id) || empty($new_name)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        // Kiểm tra trùng mã nếu thay đổi mã
        if ($new_id !== $old_id) {
            $check = $conn->prepare("SELECT * FROM loaisanpham WHERE id_loaisp = ?");
            $check->execute([$new_id]);
            if ($check->fetch()) {
                $error = "Mã loại sản phẩm đã tồn tại. Vui lòng chọn mã khác.";
            }
        }

        // Kiểm tra trùng tên loại (ngoại trừ chính dòng đang sửa)
        $check_name = $conn->prepare("SELECT COUNT(*) FROM loaisanpham WHERE ten_loaisp = ? AND id_loaisp != ?");
        $check_name->execute([$new_name, $old_id]);
        if ($check_name->fetchColumn() > 0) {
            $error = "Tên loại sản phẩm đã tồn tại. Vui lòng chọn tên khác.";
        }

        if (empty($error)) {
            try {
                $stmt = $conn->prepare("UPDATE loaisanpham SET id_loaisp = ?, ten_loaisp = ? WHERE id_loaisp = ?");
                $stmt->execute([$new_id, $new_name, $old_id]);
                header("Location: loaisanpham.php");
                exit();
            } catch (PDOException $e) {
                $error = "Có lỗi xảy ra khi cập nhật: " . $e->getMessage();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa loại sản phẩm</title>
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
            <a href="loaisanpham.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong>
            </span>
        </div>
    </nav>

    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">SỬA LOẠI SẢN PHẨM</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
                <label for="id_loaisp" class="form-label">Mã loại sản phẩm</label>
                <input type="text" class="form-control" id="id_loaisp" name="id_loaisp"
                       value="<?= htmlspecialchars($loai['id_loaisp']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="ten_loaisp" class="form-label">Tên loại sản phẩm</label>
                <input type="text" class="form-control" id="ten_loaisp" name="ten_loaisp"
                       value="<?= htmlspecialchars($loai['ten_loaisp']) ?>" required>
            </div>

            <div class="text-end">
                <a href="loaisanpham.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</body>
</html>
