<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

$search = $_GET['search'] ?? '';

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM khuyenmai WHERE id_khuyenmai = ?");
    $stmt->execute([$id]);
    header("Location: khuyenmai.php");
    exit();
}

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT km.*, sp.ten_sp FROM khuyenmai km JOIN sanpham sp ON km.id_sanpham = sp.id_sanpham WHERE km.id_khuyenmai LIKE ? OR sp.ten_sp LIKE ? ORDER BY km.ngay_bat_dau DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("SELECT km.*, sp.ten_sp FROM khuyenmai km JOIN sanpham sp ON km.id_sanpham = sp.id_sanpham ORDER BY km.ngay_bat_dau DESC");
}
$khuyenmaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khuyến mãi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .sidebar { position: sticky; top: 0; height: 100vh; background-color: #343a40; color: white; padding-top: 20px; overflow-y: auto; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 20px; }
        .sidebar a:hover { background-color: #495057; }
        .content { padding: 30px; }
        .navbar { background-color: #007bff; padding: 16px 24px; }
        .navbar-brand { font-weight: bold; color: white; font-size: 24px; }
        .admin-name { margin-left: auto; color: white; }
        .btn-add { background-color: #007bff; color: white; }
        .btn-add:hover { background-color: #0056b3; }
        .search-form { max-width: 400px; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.querySelector('input[name="search"]');
            let timer;
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('search', this.value);
                        window.location.href = url.toString();
                    }, 400);
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.querySelector('input[name="search"]');
            let timer;
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('search', this.value);
                        window.location.href = url.toString();
                    }, 400);
                });
            }
        });
    </script>
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

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar">
            <a href="home.php"><i class="fas fa-home me-2"></i> Trang chủ</a>
            <a href="sanpham.php"><i class="fas fa-box me-2"></i> Quản lý sản phẩm</a>
            <a href="loaisanpham.php"><i class="fas fa-layer-group me-2"></i> Loại sản phẩm</a>
            <a href="donhang.php"><i class="fas fa-receipt me-2"></i> Đơn hàng</a>
            <a href="baohanh.php"><i class="fas fa-shield-alt me-2"></i> Bảo hành</a>
            <a href="khuyenmai.php" class="bg-primary"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
            <a href="khachhang.php"><i class="fas fa-users me-2"></i> Khách hàng</a>
            <a href="danhgia.php"><i class="fas fa-star me-2"></i> Đánh giá</a>
            <a href="qladmin.php"><i class="fas fa-user-shield me-2"></i> Admin</a>
            <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
        </div>

        <div class="col-md-10 content">
            <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">DANH SÁCH KHUYẾN MÃI</h3>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <form class="input-group shadow-sm rounded-pill overflow-hidden" method="GET" style="max-width: 420px;">
                    <span class="input-group-text bg-white border-0 ps-3" id="search-icon">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-0" placeholder="Tìm theo tên hoặc mã sản phẩm..." value="<?= htmlspecialchars($search) ?>" aria-label="Tìm sản phẩm" aria-describedby="search-icon">
                </form>

                <a href="add_khuyenmai.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i> Thêm khuyến mãi
                </a>
            </div>

            <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle bg-white rounded shadow-sm overflow-hidden">
                    <thead class="table-primary">
                        <tr>
                            <th>Mã KM</th>
                            <th>Mã SP</th>
                            <th>Tên SP</th>
                            <th>Tên KM</th>
                            <th>Giảm %</th>
                            <th>Giảm tiền</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($khuyenmaiList as $km): ?>
                            <tr>
                                <td><?= htmlspecialchars($km['id_khuyenmai']) ?></td>
                                <td><?= htmlspecialchars($km['id_sanpham']) ?></td>
                                <td><?= htmlspecialchars($km['ten_sp']) ?></td>
                                <td><?= htmlspecialchars($km['ten_km']) ?></td>
                                <td><?= $km['giam_gia_percent'] ? $km['giam_gia_percent'] . '%' : '-' ?></td>
                                <td><?= $km['giam_gia_tien'] ? number_format($km['giam_gia_tien'], 0, ',', '.') . '₫' : '-' ?></td>
                                <td><?= htmlspecialchars($km['ngay_bat_dau']) ?></td>
                                <td><?= htmlspecialchars($km['ngay_ket_thuc']) ?></td>
                                <td>
                                    <a href="edit_khuyenmai.php?id=<?= urlencode($km['id_khuyenmai']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <a href="khuyenmai.php?delete=<?= urlencode($km['id_khuyenmai']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
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
