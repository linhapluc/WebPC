<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Xử lý xóa bảo hành
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM baohanh WHERE id_baohanh = ?");
    $stmt->execute([$id]);
    header("Location: baohanh.php");
    exit();
}

// Tìm kiếm
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT b.*, s.ten_sp FROM baohanh b JOIN sanpham s ON b.id_sanpham = s.id_sanpham WHERE b.id_baohanh LIKE ? OR s.ten_sp LIKE ? ORDER BY b.id_baohanh DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("SELECT b.*, s.ten_sp FROM baohanh b JOIN sanpham s ON b.id_sanpham = s.id_sanpham ORDER BY b.id_baohanh DESC");
}
$baohanhs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý bảo hành</title>
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

        .btn-add {
            background-color: #007bff;
            color: white;
        }

        .btn-add:hover {
            background-color: #0056b3;
        }

        .search-form {
            max-width: 400px;
        }
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
</head>

<body class="bg-light">
<nav class="navbar navbar-expand-lg bg-primary"> 
    <div class="container-fluid d-flex align-items-center">
        <a href="home.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
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
            <a href="donhang.php"><i class="fas fa-receipt me-2"></i> Đơn hàng</a>
            <a href="baohanh.php" class="bg-primary"><i class="fas fa-shield-alt me-2"></i> Bảo hành</a>
            <a href="khuyenmai.php"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
            <a href="khachhang.php"><i class="fas fa-users me-2"></i> Khách hàng</a>
            <a href="danhgia.php"><i class="fas fa-star me-2"></i> Đánh giá</a>
            <a href="qladmin.php"><i class="fas fa-user-shield me-2"></i> Admin</a>
            <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
        </div>

        <div class="col-md-10 content p-4">
            <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">DANH SÁCH BẢO HÀNH</h3>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <form class="input-group shadow-sm rounded-pill overflow-hidden" method="GET" style="max-width: 420px;">
                    <span class="input-group-text bg-white border-0 ps-3" id="search-icon">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-0" placeholder="Tìm theo tên hoặc mã sản phẩm..." value="<?= htmlspecialchars($search) ?>" aria-label="Tìm sản phẩm" aria-describedby="search-icon">
                </form>

                <a href="add_baohanh.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i> Thêm Bảo Hành
                </a>
            </div>

            <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle bg-white rounded shadow-sm overflow-hidden">
                    <thead class="table-primary text-center">
                        <tr>
                            <th class="text-nowrap">Mã bảo hành</th>
                            <th class="text-nowrap">Mã sản phẩm</th>
                            <th class="text-nowrap">Tên sản phẩm</th>
                            <th class="text-nowrap">Thời gian BH (tháng)</th>
                            <th class="text-nowrap">Mô tả BH</th>
                            <th class="text-nowrap">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($baohanhs as $bh): ?>
                            <tr>
                                <td><?= htmlspecialchars($bh['id_baohanh']) ?></td>
                                <td><?= htmlspecialchars($bh['id_sanpham']) ?></td>
                                <td class="shorten-text" title="<?= htmlspecialchars($bh['ten_sp']) ?>"><?= htmlspecialchars($bh['ten_sp']) ?></td>
                                <td class="text-center"><?= (int)$bh['thoi_gian_bh'] ?></td>
                                <td><?= nl2br(htmlspecialchars($bh['mo_ta_bh'])) ?></td>
                                <td class="text-center">
                                    <a href="edit_baohanh.php?id=<?= urlencode($bh['id_baohanh']) ?>" class="btn btn-sm btn-outline-primary me-1">Sửa</a>
                                    <a href="baohanh.php?delete=<?= urlencode($bh['id_baohanh']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa bảo hành này không?')">Xóa</a>
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
