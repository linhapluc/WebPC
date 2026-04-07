<?php
// Trong file: WebPC/admin/index.php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu admin chưa đăng nhập thì chuyển hướng về trang đăng nhập
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}

// Kiểm tra nếu không có ID đánh giá, chuyển hướng về trang quản lý đánh giá
if (!isset($_GET['id'])) {
    header("Location: danhgia.php");
    exit();
}

$id = $_GET['id'];

// Lấy thông tin đánh giá
$stmt = $conn->prepare("SELECT * FROM danhgia WHERE id = ?");
$stmt->execute([$id]);
$danhgia = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra nếu không tìm thấy đánh giá, chuyển hướng về trang quản lý đánh giá
if (!$danhgia) {
    header("Location: danhgia.php");
    exit();
}

// Cập nhật đánh giá
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sao = $_POST['sao'];
    $binh_luan = $_POST['binh_luan'];
    
    // Cập nhật thông tin đánh giá
    $stmt_update = $conn->prepare("UPDATE danhgia SET sao = ?, binh_luan = ? WHERE id = ?");
    $stmt_update->execute([$sao, $binh_luan, $id]);
    
    // Chuyển hướng về trang quản lý đánh giá sau khi cập nhật
    header("Location: danhgia.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Đánh Giá</title>
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
<body>
   <nav class="navbar navbar-expand-lg bg-primary"> 
        <div class="container-fluid d-flex align-items-center">
            <span class="navbar-brand text-white fs-4 fw-bold">Trang Quản Trị Admin</span>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </span>
        </div>
    </nav>

<h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">SỬA ĐÁNH GIÁ</h3>

<div class="form-container">
    <form method="POST">
        <label for="sao">Sao</label>
        <input type="number" id="sao" name="sao" min="1" max="5" value="<?= htmlspecialchars($danhgia['sao']) ?>" required>

        <label for="binh_luan">Bình Luận</label>
        <textarea id="binh_luan" name="binh_luan" rows="4" required><?= htmlspecialchars($danhgia['binh_luan']) ?></textarea>

        <input type="submit" value="Cập Nhật Đánh Giá">
    </form>

    <a href="danhgia.php" class="back-button">Quay lại Trang Quản Lý Đánh Giá</a>
</div>

</body>
</html>
