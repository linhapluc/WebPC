<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

// Xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM sanpham WHERE id_sanpham = ?");
    $stmt->execute([$id]);
    header("Location: sanpham.php");
    exit();
}

/* ================== TÌM KIẾM ================== */
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT s.*, l.ten_loaisp
    FROM sanpham s
    JOIN loaisanpham l ON s.id_loai = l.id_loaisp
    WHERE 1
";

$params = [];

if ($search !== '') {
    // ✅ FIX ổn định: dùng named placeholder + CAST để LIKE chắc chắn
    $sql .= " AND (
                s.ten_sp LIKE :s1
                OR CAST(s.id_sanpham AS CHAR) LIKE :s2
              )";
    $kw = "%$search%";
    $params[':s1'] = $kw;
    $params[':s2'] = $kw;
}

$sql .= " ORDER BY s.id_sanpham ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$sanphams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung (giống donhang.php) -->
    <link rel="stylesheet" href="admin.css">

    <!-- Thumb cho cột hình ảnh -->
    <style>
        .product-thumb-sm {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 10px;
            background: #f1f3f5;
        }

        tr.clickable-row {
            cursor: pointer;
        }
    </style>

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
        <!-- SIDEBAR giống donhang.php -->
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
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'sanpham.php' ? 'active' : ''; ?>">
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
            <!-- TOPBAR giống donhang.php -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <div class="admin-page-title">Sản phẩm</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">
                <div class="card-panel">
                    <div class="card-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="card-panel-title">Quản lý sản phẩm</div>

                        <!-- Search + nút thêm -->
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <form method="get" class="order-search">
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Tìm theo tên hoặc mã sản phẩm..."
                                        value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </form>

                            <a href="add_sanpham.php" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus"></i> Thêm sản phẩm
                            </a>
                        </div>
                    </div>

                    <!-- BẢNG SẢN PHẨM -->
                    <div class="table-responsive mt-3">
                        <table class="table table-modern table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Mã SP</th>
                                    <th>Tên sản phẩm</th>
                                    <th style="width: 90px;">Hình ảnh</th>
                                    <th style="width: 140px;">Giá</th>
                                    <th style="width: 120px;">Số lượng</th>
                                    <th style="width: 180px;">Loại sản phẩm</th>
                                    <th style="width: 180px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sanphams)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Không có sản phẩm nào.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sanphams as $sp): ?>
                                        <tr class="clickable-row"
                                            onclick="window.location='xem_sanpham.php?id=<?php echo urlencode($sp['id_sanpham']); ?>'">
                                            <td>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($sp['id_sanpham']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($sp['ten_sp']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($sp['hinh_anh'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($sp['hinh_anh']); ?>"
                                                        alt="Ảnh sản phẩm"
                                                        class="product-thumb-sm">
                                                <?php else: ?>
                                                    <div class="product-thumb-sm"></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?php echo number_format($sp['gia'], 0, ',', '.'); ?>₫
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo (int)$sp['sl']; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($sp['ten_loaisp']); ?>
                                            </td>
                                            <td>
                                                <a href="edit_sanpham.php?id=<?php echo urlencode($sp['id_sanpham']); ?>"
                                                    class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="event.stopPropagation();">
                                                    Sửa
                                                </a>
                                                <a href="sanpham.php?delete=<?php echo urlencode($sp['id_sanpham']); ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="event.stopPropagation(); return confirm('Bạn có chắc muốn xóa sản phẩm này không?');">
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