<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['id'])) {
    die("Thiếu ID sản phẩm.");
}

$id = $_GET['id'];

// Lấy dữ liệu sản phẩm
$stmt = $conn->prepare("SELECT * FROM sanpham WHERE id_sanpham = ?");
$stmt->execute([$id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sp) die("Không tìm thấy sản phẩm!");

// Lấy danh sách loại sản phẩm
$loai_stmt = $conn->query("SELECT * FROM loaisanpham");
$loais = $loai_stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_sp = $_POST['ten_sp'];
    $gia = $_POST['gia'];
    $mo_ta = $_POST['mo_ta'];
    $thong_so = $_POST['thong_so'];
    $sl = $_POST['sl'];
    $id_loai = $_POST['id_loai'];

    $hinh_anh = $sp['hinh_anh']; // giữ ảnh cũ mặc định

    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../trang-chu/';
        $filename = basename($_FILES['hinh_anh']['name']);
        $targetFile = $uploadDir . $filename;

        // Kiểm tra trùng tên ảnh (ngoại trừ ảnh cũ)
        $stmt_check_image = $conn->prepare("SELECT COUNT(*) FROM sanpham WHERE hinh_anh = ? AND id_sanpham != ?");
        $stmt_check_image->execute([$filename, $id]);
        if ($stmt_check_image->fetchColumn() > 0) {
            $error = "Tên ảnh đã tồn tại. Vui lòng chọn ảnh khác.";
        } else {
            if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $targetFile)) {
                $hinh_anh = $filename;
            }
        }
    }

    $new_id = trim($_POST['id_sanpham']);
    $ten_sp = $_POST['ten_sp'];


    if ($new_id !== $id) {
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM sanpham WHERE id_sanpham = ?");
        $stmt_check->execute([$new_id]);
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Mã sản phẩm đã tồn tại. Vui lòng chọn mã khác.";
        }
    }
    // Kiểm tra tên sản phẩm trùng (trừ chính sản phẩm đang sửa)

    $stmt_check_name = $conn->prepare("SELECT COUNT(*) FROM sanpham WHERE ten_sp = ? AND id_sanpham != ?");
    $stmt_check_name->execute([$ten_sp, $id]);
    if ($stmt_check_name->fetchColumn() > 0) {
        $error = "Tên sản phẩm đã tồn tại. Vui lòng chọn tên khác.";
    }

    // Nếu không có lỗi thì cập nhật
    if (!isset($error)) {
        $update = $conn->prepare("UPDATE sanpham 
        SET id_sanpham=?, ten_sp=?, gia=?, mo_ta=?, thong_so_ky_thuat=?, sl=?, id_loai=?, hinh_anh=? 
        WHERE id_sanpham=?");
        $update->execute([$new_id, $ten_sp, $gia, $mo_ta, $thong_so, $sl, $id_loai, $hinh_anh, $id]);

        // Nếu đổi mã thì cập nhật $id session/local để đúng redirect
        header("Location: sanpham.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
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
            <a href="sanpham.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </span>
        </div>
    </nav>

    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">SỬA SẢN PHẨM</h3>

        <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="id_sanpham" class="form-label">Mã sản phẩm</label>
                <input type="text" name="id_sanpham" class="form-control" value="<?= htmlspecialchars($sp['id_sanpham']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="ten_sp" class="form-label">Tên sản phẩm</label>
                <input type="text" name="ten_sp" class="form-control" value="<?= htmlspecialchars($sp['ten_sp']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="gia" class="form-label">Giá</label>
                <input type="number" name="gia" class="form-control" value="<?= (int)$sp['gia'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="sl" class="form-label">Số lượng</label>
                <input type="number" name="sl" class="form-control" value="<?= (int)$sp['sl'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="id_loai" class="form-label">Loại sản phẩm</label>
                <select name="id_loai" class="form-select" required>
                    <?php foreach ($loais as $loai): ?>
                        <option value="<?= $loai['id_loaisp'] ?>" <?= ($sp['id_loai'] === $loai['id_loaisp']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loai['ten_loaisp']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="mo_ta" class="form-label">Mô tả</label>
                <textarea name="mo_ta" rows="3" class="form-control"><?= htmlspecialchars($sp['mo_ta']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="thong_so" class="form-label">Thông số kỹ thuật</label>
                <textarea name="thong_so" rows="3" class="form-control"><?= htmlspecialchars($sp['thong_so_ky_thuat']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Hình ảnh hiện tại</label><br>
                <img src="/webpc/trang-chu/<?= htmlspecialchars($sp['hinh_anh']) ?>" alt="Ảnh sản phẩm" style="max-width: 500px;" class="img-thumbnail">
            </div>

            <div class="mb-4">
                <label for="hinh_anh" class="form-label">Thay ảnh mới (nếu muốn)</label>
                <input type="file" name="hinh_anh" class="form-control">
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="sanpham.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>

</body>

</html>