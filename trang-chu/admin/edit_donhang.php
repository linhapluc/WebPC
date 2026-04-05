<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['id'])) {
    die("Thiếu ID đơn hàng.");
}

$id = $_GET['id'];

// Lấy dữ liệu đơn hàng
$stmt = $conn->prepare("SELECT * FROM donhang WHERE id_donhang = ?");
$stmt->execute([$id]);
$donhang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donhang) die("Không tìm thấy đơn hàng!");

// Cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_khachhang = $_POST['id_khachhang'];
    $ngay_dat = $_POST['ngay_dat'];
    $tong_tien = $_POST['tong_tien'];
    $ten_nguoinhan = $_POST['ten_nguoinhan'];
    $diachi_giaohang = $_POST['diachi_giaohang'];
    $sdt_nguoinhan = $_POST['sdt_nguoinhan'];
    $ghi_chu = $_POST['ghi_chu'];
    $trang_thai = $_POST['trang_thai'];
    $phuongthuc_thanhtoan = $_POST['phuongthuc_thanhtoan'];
    $xac_nhan_admin = isset($_POST['xac_nhan_admin']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE donhang SET id_khachhang=?, ngay_dat=?, tong_tien=?, ten_nguoinhan=?, diachi_giaohang=?, sdt_nguoinhan=?, ghi_chu=?, trang_thai=?, phuongthuc_thanhtoan=?, xac_nhan_admin=? WHERE id_donhang=?");
    $stmt->execute([$id_khachhang, $ngay_dat, $tong_tien, $ten_nguoinhan, $diachi_giaohang, $sdt_nguoinhan, $ghi_chu, $trang_thai, $phuongthuc_thanhtoan, $xac_nhan_admin, $id]);

    header("Location: donhang.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa Đơn Hàng</title>
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
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">SỬA ĐƠN HÀNG</h3>
        <form method="POST" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
                <label class="form-label">Mã đơn hàng</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($donhang['id_donhang']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Mã khách hàng</label>
                <input type="text" name="id_khachhang" class="form-control" value="<?= htmlspecialchars($donhang['id_khachhang']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày đặt</label>
                <input type="datetime-local" name="ngay_dat" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($donhang['ngay_dat'])) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tổng tiền</label>
                <input type="number" name="tong_tien" class="form-control" value="<?= (int)$donhang['tong_tien'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Người nhận</label>
                <input type="text" name="ten_nguoinhan" class="form-control" value="<?= htmlspecialchars($donhang['ten_nguoinhan']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Địa chỉ giao hàng</label>
                <textarea name="diachi_giaohang" class="form-control"><?= htmlspecialchars($donhang['diachi_giaohang']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="sdt_nguoinhan" class="form-control" value="<?= htmlspecialchars($donhang['sdt_nguoinhan']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Ghi chú</label>
                <textarea name="ghi_chu" class="form-control"><?= htmlspecialchars($donhang['ghi_chu']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <input type="text" name="trang_thai" class="form-control" value="<?= htmlspecialchars($donhang['trang_thai']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Phương thức thanh toán</label>
                <input type="text" name="phuongthuc_thanhtoan" class="form-control" value="<?= htmlspecialchars($donhang['phuongthuc_thanhtoan']) ?>">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="xac_nhan_admin" id="xac_nhan_admin" <?= $donhang['xac_nhan_admin'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="xac_nhan_admin">
                    Xác nhận đơn hàng
                </label>
            </div>

            <div class="text-end">
                <a href="donhang.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</body>

</html>