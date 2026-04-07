<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Check login
if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

// Xóa admin nếu có yêu cầu
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM admins WHERE id_admin = ?");
    $stmt->execute([$id]);
    header("Location: qladmin.php");
    exit();
}

// Tìm kiếm admin theo ID hoặc tên
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT *
        FROM admins
        WHERE id_admin LIKE ? OR name LIKE ?
        ORDER BY id_admin ASC
    ");
    $like = "%{$search}%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $conn->query("SELECT * FROM admins ORDER BY id_admin ASC");
}
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.order-search');
            const input = form?.querySelector('input[name="search"]');

            if (!form || !input) return;

            form.addEventListener('submit', function(e) {
                const val = input.value.trim();

                if (val === '') {
                    e.preventDefault();

                    const url = new URL(window.location.href);
                    url.searchParams.delete('search'); // ✅ bỏ search để hiện full

                    window.location.href = url.toString();
                }
                // có chữ -> submit bình thường
            });
        });
    </script>

</head>

<body>
    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="home.php" class="admin-sidebar-brand">
                    <div class="admin-sidebar-logo">NS</div>
                    <div class="admin-sidebar-title">NASA Admin</div>
                </a>
            </div>

            <nav class="admin-sidebar-nav">
                <a href="home.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'home.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i>
                    <span>Trang chủ</span>
                </a>

                <a href="donhang.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'donhang.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-clipboard"></i>
                    <span>Đơn hàng</span>
                </a>

                <a href="sanpham.php"
                    class="admin-nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['sanpham.php', 'add_sanpham.php', 'edit_sanpham.php', 'xem_sanpham.php']) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-box"></i>
                    <span>Sản phẩm</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khuyenmai.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khuyenmai.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Khuyến mãi</span>
                </a>

                <a href="doanhthu.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'doanhthu.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-database"></i>
                    <span>La bàn dữ liệu</span>
                </a>

                <a href="qladmin.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'qladmin.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-id-badge"></i>
                    <span>Tình trạng tài khoản</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khachhang.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khachhang.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Khách hàng</span>
                </a>

                <a href="danhgia.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'danhgia.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-star"></i>
                    <span>Đánh giá</span>
                </a>
                <a href="lienket.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'lienket.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-link"></i>
                    <span>Liên kết</span>
                </a>


                <div class="admin-sidebar-divider"></div>

                <a href="logout.php" class="admin-nav-item admin-sidebar-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Đăng xuất</span>
                </a>
            </nav>

            <div class="admin-sidebar-footer">
                © <?php echo date('Y'); ?> Nasa Shop
            </div>
        </aside>

        <!-- MAIN -->
        <div class="admin-main">
            <!-- TOPBAR -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <div class="admin-page-title">Tình trạng tài khoản</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">
                <div class="card-panel">
                    <div class="card-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="card-panel-title">Danh sách quản trị viên</div>

                        <!-- Search + nút thêm -->
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <form method="get" class="order-search">
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Tìm theo tên hoặc mã admin..."
                                        value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </form>

                            <a href="add_qladmin.php" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus"></i> Thêm Admin
                            </a>
                        </div>
                    </div>

                    <!-- BẢNG ADMIN -->
                    <div class="table-responsive mt-3">
                        <table class="table table-modern table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Mã admin</th>
                                    <th>Tên admin</th>
                                    <th style="width: 180px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($admins)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            Chưa có quản trị viên nào.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($admins as $ad): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ad['id_admin']); ?></td>
                                            <td><?php echo htmlspecialchars($ad['name']); ?></td>
                                            <td>
                                                <a href="edit_qladmin.php?id=<?php echo urlencode($ad['id_admin']); ?>"
                                                    class="btn btn-sm btn-outline-primary me-1">
                                                    Sửa
                                                </a>
                                                <a href="qladmin.php?delete=<?php echo urlencode($ad['id_admin']); ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Bạn có chắc muốn xóa admin này không?');">
                                                    Xóa
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>