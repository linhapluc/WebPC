<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['id'])) {
    die("Thiếu ID đơn hàng.");
}

$id = $_GET['id'];

// Lấy dữ liệu đơn hàng
$stmt = $conn->prepare("SELECT d.*, k.ho_ten, k.email, k.so_dien_thoai FROM donhang d JOIN khachhang k ON d.id_khachhang = k.id_khachhang WHERE id_donhang = ?");
$stmt->execute([$id]);
$donhang = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$donhang) die("Không tìm thấy đơn hàng!");

// Lấy chi tiết đơn hàng
$stmt_ct = $conn->prepare("SELECT ct.*, sp.ten_sp FROM chitietdonhang ct JOIN sanpham sp ON ct.id_sanpham = sp.id_sanpham WHERE id_donhang = ?");
$stmt_ct->execute([$id]);
$chitiet = $stmt_ct->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="text-center fw-bold mb-4 text-uppercase">Chi Tiết Đơn Hàng</h3>

    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h5 class="fw-bold text-primary">Thông tin đơn hàng</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Mã đơn hàng:</strong> <?= htmlspecialchars($donhang['id_donhang']) ?></p>
                <p><strong>Ngày đặt:</strong> <?= htmlspecialchars($donhang['ngay_dat']) ?></p>
                <p><strong>Tổng tiền:</strong> <?= number_format($donhang['tong_tien'], 0, ',', '.') ?>₫</p>
                <p><strong>Trạng thái:</strong> <?= htmlspecialchars($donhang['trang_thai']) ?></p>
                <p><strong>Xác nhận admin:</strong> <?= $donhang['xac_nhan_admin'] ? 'Đã xác nhận' : 'Chưa xác nhận' ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Khách hàng:</strong> <?= htmlspecialchars($donhang['ho_ten']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($donhang['email']) ?></p>
                <p><strong>SĐT:</strong> <?= htmlspecialchars($donhang['so_dien_thoai']) ?></p>
                <p><strong>Người nhận:</strong> <?= htmlspecialchars($donhang['ten_nguoinhan']) ?></p>
                <p><strong>Địa chỉ giao hàng:</strong> <?= htmlspecialchars($donhang['diachi_giaohang']) ?></p>
                <p><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($donhang['ghi_chu'])) ?></p>
                <p><strong>Phương thức thanh toán:</strong> <?= htmlspecialchars($donhang['phuongthuc_thanhtoan']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded shadow-sm">
        <h5 class="fw-bold text-primary mb-3">Chi tiết sản phẩm</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Mã sản phẩm</th>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá lúc mua</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chitiet as $ct): ?>
                        <tr>
                            <td><?= htmlspecialchars($ct['id_sanpham']) ?></td>
                            <td><?= htmlspecialchars($ct['ten_sp']) ?></td>
                            <td class="text-center"><?= $ct['so_luong_mua'] ?></td>
                            <td class="text-end"><?= number_format($ct['thanh_tien'], 0, ',', '.') ?>₫</td>
                            <td class="text-end"><?= number_format($ct['so_luong_mua'] * $ct['thanh_tien'], 0, ',', '.') ?>₫</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-end mt-4">
        <a href="donhang.php" class="btn btn-secondary">← Quay lại danh sách</a>
    </div>
</div>
</body>
</html>
