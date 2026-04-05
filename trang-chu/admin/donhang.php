<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Xử lý xác nhận đơn hàng nếu có yêu cầu từ form
if (isset($_GET['xacnhan']) && isset($_GET['id'])) {
    $xacnhan = $_GET['xacnhan'] === '1' ? 1 : 0;
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE donhang SET xac_nhan_admin = ? WHERE id_donhang = ?");
    $stmt->execute([$xacnhan, $id]);
    header("Location: donhang.php");
    exit();
}

// Tìm kiếm đơn hàng
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM donhang WHERE id_donhang LIKE ? ORDER BY ngay_dat DESC");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $conn->query("SELECT * FROM donhang ORDER BY ngay_dat DESC");
}
$donhangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
            <a href="sanpham.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <a href="home.php"><i class="fas fa-home me-2"></i> Trang chủ</a>
                <a href="sanpham.php"><i class="fas fa-box me-2"></i> Quản lý sản phẩm</a>
                <a href="loaisanpham.php"><i class="fas fa-layer-group me-2"></i> Loại sản phẩm</a>
                <a href="donhang.php" class="bg-primary"><i class="fas fa-receipt me-2"></i> Đơn hàng</a>
                <a href="baohanh.php"><i class="fas fa-shield-alt me-2"></i> Bảo hành</a>
                <a href="khuyenmai.php"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
                <a href="khachhang.php"><i class="fas fa-users me-2"></i> Khách hàng</a>
                <a href="danhgia.php"><i class="fas fa-star me-2"></i> Đánh giá</a>
                <a href="qladmin.php"><i class="fas fa-user-shield me-2"></i> Admin</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
            </div>

            <div class="col-md-10 content">
                <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">QUẢN LÝ ĐƠN HÀNG</h3>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <form class="input-group shadow-sm rounded-pill overflow-hidden" method="GET" style="max-width: 420px;">
                        <span class="input-group-text bg-white border-0 ps-3" id="search-icon">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0" placeholder="Tìm theo mã đơn hàng..." value="<?= htmlspecialchars($search) ?>" aria-label="Tìm sản phẩm" aria-describedby="search-icon">
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle bg-white rounded shadow-sm overflow-hidden">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã Đơn</th>
                                <th>Khách Hàng</th>
                                <th>Ngày Đặt</th>
                                <th>Tổng Tiền</th>
                                <th>Người Nhận</th>
                                <th>Trạng Thái</th>
                                <th>PT Thanh Toán</th>
                                <th>Xác Nhận</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donhangs as $dh): ?>
                                <tr class="clickable-row" onclick="window.location='chitiet_donhang.php?id=<?= $dh['id_donhang'] ?>'">
                                    <td><?= htmlspecialchars($dh['id_donhang']) ?></td>
                                    <td><?= htmlspecialchars($dh['id_khachhang']) ?></td>
                                    <td><?= htmlspecialchars($dh['ngay_dat']) ?></td>
                                    <td><?= number_format($dh['tong_tien'], 0, ',', '.') ?>₫</td>
                                    <td><?= htmlspecialchars($dh['ten_nguoinhan']) ?></td>
                                    <td><?= htmlspecialchars($dh['trang_thai']) ?></td>
                                    <td><?= htmlspecialchars($dh['phuongthuc_thanhtoan']) ?></td>
                                    <td>
                                        <?php if ($dh['xac_nhan_admin']): ?>
                                            <a href="?id=<?= $dh['id_donhang'] ?>&xacnhan=0" class="btn btn-sm btn-warning" onclick="event.stopPropagation();">Huỷ xác nhận</a>
                                        <?php else: ?>
                                            <a href="?id=<?= $dh['id_donhang'] ?>&xacnhan=1" class="btn btn-sm btn-success" onclick="event.stopPropagation();">Xác nhận</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit_donhang.php?id=<?= $dh['id_donhang'] ?>" class="btn btn-sm btn-outline-primary me-2" onclick="event.stopPropagation();">Sửa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>