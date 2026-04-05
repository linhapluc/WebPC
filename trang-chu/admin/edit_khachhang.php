<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra có ID không
if (!isset($_GET['id'])) {
    die("Thiếu ID khách hàng.");
}

$id = $_GET['id'];

// Lấy dữ liệu khách hàng
$stmt = $conn->prepare("SELECT * FROM khachhang WHERE id_khachhang = ?");
$stmt->execute([$id]);
$khach = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$khach) {
    die("Không tìm thấy khách hàng.");
}

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $error = '';

    // Kiểm tra email trùng
    $stmt_email = $conn->prepare("SELECT COUNT(*) FROM khachhang WHERE email = ? AND id_khachhang != ?");
    $stmt_email->execute([$email, $id]);
    if ($stmt_email->fetchColumn() > 0) {
        $error .= "Email đã được sử dụng bởi khách hàng khác.";
    }

    // Kiểm tra số điện thoại trùng
    $stmt_phone = $conn->prepare("SELECT COUNT(*) FROM khachhang WHERE so_dien_thoai = ? AND id_khachhang != ?");
    $stmt_phone->execute([$so_dien_thoai, $id]);
    if ($stmt_phone->fetchColumn() > 0) {
        $error .= "Số điện thoại đã được sử dụng bởi khách hàng khác.";
    }

    // Nếu không có lỗi thì cập nhật
    if (empty($error)) {
        $stmt_update = $conn->prepare("UPDATE khachhang SET ho_ten = ?, email = ?, so_dien_thoai = ? WHERE id_khachhang = ?");
        $stmt_update->execute([$ho_ten, $email, $so_dien_thoai, $id]);
        header("Location: khachhang.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa khách hàng</title>
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
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">SỬA THÔNG TIN KHÁCH HÀNG</h3>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>


        <form method="POST" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
                <label for="id_khachhang" class="form-label">Mã khách hàng</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($khach['id_khachhang']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="ho_ten" class="form-label">Họ tên</label>
                <input type="text" name="ho_ten" class="form-control" required value="<?= htmlspecialchars($khach['ho_ten']) ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($khach['email']) ?>">
            </div>
            <div class="mb-3">
                <label for="so_dien_thoai" class="form-label">Số điện thoại</label>
                <input type="text" name="so_dien_thoai" class="form-control" value="<?= htmlspecialchars($khach['so_dien_thoai']) ?>">
            </div>
            <div class="text-end">
                <a href="khachhang.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</body>

</html>