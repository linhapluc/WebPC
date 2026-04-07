<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM loaisanpham WHERE id_loaisp = ?");
    $stmt->execute([$deleteId]);
    header("Location: loaisanpham.php");
    exit();
}

// Xử lý tìm kiếm
$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM loaisanpham WHERE id_loaisp LIKE ? OR ten_loaisp LIKE ? ORDER BY id_loaisp ASC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("SELECT * FROM loaisanpham ORDER BY id_loaisp ASC");
}
$loaisanphams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý loại sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
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
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            let timer;
            if (searchInput) {
                searchInput.addEventListener('input', function() {
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
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <a href="home.php"><i class="fas fa-home me-2"></i> Trang chủ</a>
                <a href="sanpham.php"><i class="fas fa-box me-2"></i> Quản lý sản phẩm</a>
                <a href="loaisanpham.php" class="bg-primary"><i class="fas fa-layer-group me-2"></i> Loại sản phẩm</a>
                <a href="donhang.php"><i class="fas fa-receipt me-2"></i> Đơn hàng</a>
                <a href="baohanh.php"><i class="fas fa-shield-alt me-2"></i> Bảo hành</a>
                <a href="khuyenmai.php"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
                <a href="khachhang.php"><i class="fas fa-users me-2"></i> Khách hàng</a>
                <a href="danhgia.php"><i class="fas fa-star me-2"></i> Đánh giá</a>
                <a href="qladmin.php"><i class="fas fa-user-shield me-2"></i> Admin</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
            </div>

            <!-- Content -->
            <div class="col-md-10 content">
                <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">QUẢN LÝ LOẠI SẢN PHẨM</h3>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <form class="input-group shadow-sm rounded-pill overflow-hidden" method="GET" style="max-width: 420px;">
                        <span class="input-group-text bg-white border-0 ps-3" id="search-icon">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0" placeholder="Tìm theo tên hoặc mã loại sản phẩm..." value="<?= htmlspecialchars($search) ?>" aria-label="Tìm sản phẩm" aria-describedby="search-icon">
                    </form>

                    <a href="add_loaisanpham.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i> Thêm loại sản phẩm
                    </a>
                </div>

                <!-- Danh sách loại sản phẩm -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle bg-white rounded shadow-sm overflow-hidden">
                        <thead class="table-primary text-center">
                            <tr>
                                <th width="150">Mã loại</th>
                                <th class="text-center">Tên loại sản phẩm</th>
                                <th class="text-nowrap">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($loaisanphams) > 0): ?>
                                <?php foreach ($loaisanphams as $loai): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($loai['id_loaisp']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($loai['ten_loaisp']) ?></td>
                                        <td class="text-center">
                                            <a href="edit_loaisanpham.php?id=<?= urlencode($loai['id_loaisp']) ?>" class="btn btn-sm btn-outline-primary me-2">Sửa</a>
                                            <a href="loaisanpham.php?delete=<?= urlencode($loai['id_loaisp']) ?>" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa loại sản phẩm này không?')">Xóa</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Không tìm thấy loại sản phẩm nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>



            </div>
        </div>
    </div>

</body>

</html>