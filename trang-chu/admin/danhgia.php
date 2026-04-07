<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

// Xóa đánh giá nếu có yêu cầu
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM danhgia WHERE id_danhgia = ?");
    $stmt->execute([$id]);
    header("Location: danhgia.php");
    exit();
}

// Tìm kiếm đánh giá theo tên sản phẩm hoặc tên khách hàng
$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $stmt = $conn->prepare("
        SELECT dg.*, sp.ten_sp, kh.ho_ten 
        FROM danhgia dg
        JOIN sanpham sp ON dg.id_sanpham = sp.id_sanpham
        JOIN khachhang kh ON dg.id_khachhang = kh.id_khachhang
        WHERE sp.ten_sp LIKE ? OR kh.ho_ten LIKE ?
        ORDER BY dg.ngay_danhgia DESC
    ");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("
        SELECT dg.*, sp.ten_sp, kh.ho_ten 
        FROM danhgia dg
        JOIN sanpham sp ON dg.id_sanpham = sp.id_sanpham
        JOIN khachhang kh ON dg.id_khachhang = kh.id_khachhang
        ORDER BY dg.ngay_danhgia DESC
    ");
}
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đánh giá</title>
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
            <!-- TOPBAR -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <div class="admin-page-title">Đánh giá</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">
                <div class="card-panel">
                    <div class="card-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="card-panel-title">Danh sách đánh giá</div>

                        <!-- Search -->
                        <form class="order-search" method="GET">
                            <div class="input-group input-group-sm">
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Tìm theo tên sản phẩm hoặc tên khách hàng..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TABLE -->
                    <div class="table-responsive mt-3">
                        <table class="table table-modern table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Mã đánh giá</th>
                                    <th style="width: 220px;">Sản phẩm</th>
                                    <th style="width: 180px;">Khách hàng</th>
                                    <th style="width: 90px;">Số sao</th>
                                    <th>Nội dung</th>
                                    <th style="width: 160px;">Ngày đánh giá</th>
                                    <th style="width: 120px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reviews)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Không có đánh giá nào phù hợp điều kiện tìm kiếm.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reviews as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['id_danhgia']); ?></td>
                                            <td><?php echo htmlspecialchars($r['ten_sp']); ?></td>
                                            <td><?php echo htmlspecialchars($r['ho_ten']); ?></td>
                                            <td>
                                                <?php
                                                $sao = (int)$r['sao'];
                                                echo str_repeat('⭐', max(0, min(5, $sao)));
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo nl2br(htmlspecialchars($r['binh_luan'])); ?>
                                            </td>
                                            <td>
                                                <?php
                                                // Format d/m/Y H:i nếu cột là DATETIME, còn nếu là string thì cứ echo
                                                $ngay = $r['ngay_danhgia'];
                                                $ts   = strtotime($ngay);
                                                echo $ts ? date('d/m/Y H:i', $ts) : htmlspecialchars($ngay);
                                                ?>
                                            </td>
                                            <td>
                                                <a href="delete_danhgia.php?id=<?php echo urlencode($r['id_danhgia']); ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Bạn có chắc muốn xóa đánh giá này không?');">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>