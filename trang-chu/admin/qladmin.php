<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();


// Xóa admin nếu có yêu cầu
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM admins WHERE id_admin = ?");
    $stmt->execute([$id]);
    header("Location: qladmin.php");
    exit();
}

// Tìm kiếm admin theo ID hoặc tên
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id_admin LIKE ? OR name LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("SELECT * FROM admins");
}
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            color: white;
            position: sticky;
            top: 0;
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
            background-color: #E53935;
            padding: 16px 24px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand,
        .admin-name {
            color: white;
            font-weight: bold;
            font-size: 20px;
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
                <a href="khuyenmai.php"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
                <a href="khachhang.php"><i class="fas fa-users me-2"></i> Khách hàng</a>
                <a href="danhgia.php"><i class="fas fa-star me-2"></i> Đánh giá</a>
                <a href="qladmin.php" class="bg-primary"><i class="fas fa-user-shield me-2"></i> Admin</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
            </div>

            <div class="col-md-10 content">
                <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">DANH SÁCH QUẢN TRỊ VIÊN</h3>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <form class="input-group shadow-sm rounded-pill overflow-hidden" method="GET" style="max-width: 420px;">
                        <span class="input-group-text bg-white border-0 ps-3" id="search-icon">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0" placeholder="Tìm theo tên hoặc mã admin..." value="<?= htmlspecialchars($search) ?>" aria-label="Tìm sản phẩm" aria-describedby="search-icon">
                    </form>

                    <a href="add_qladmin.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i> Thêm Admin
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle bg-white rounded shadow-sm overflow-hidden">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã admin</th>
                                <th>Tên admin</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $ad): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ad['id_admin']) ?></td>
                                    <td><?= htmlspecialchars($ad['name']) ?></td>
                                    <td>
                                        <a href="edit_qladmin.php?id=<?= urlencode($ad['id_admin']) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                                        <a href="qladmin.php?delete=<?= urlencode($ad['id_admin']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa admin này không?')">Xóa</a>
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