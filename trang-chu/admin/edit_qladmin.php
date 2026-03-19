<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

$error = '';

// Lấy ID admin từ query
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Thiếu ID admin.");
}

$id_admin = $_GET['id'];

// Lấy dữ liệu admin
$stmt = $conn->prepare("SELECT * FROM admins WHERE id_admin = ?");
$stmt->execute([$id_admin]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) {
    die("Không tìm thấy admin.");
}

// Xử lý submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_post   = trim($_POST['name'] ?? '');
    $id_admin_post = $_POST['id_admin'] ?? null;

    if ($name_post === '') {
        $error = "❌ Vui lòng không để trống tên.";
    } else {
        // Kiểm tra trùng tên (ngoại trừ chính mình)
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM admins WHERE name = ? AND id_admin != ?");
        $stmt_check->execute([$name_post, $id_admin_post]);

        if ($stmt_check->fetchColumn() > 0) {
            $error = "❌ Tên admin này đã được sử dụng.";
        } else {
            $stmt_update = $conn->prepare("UPDATE admins SET name = ? WHERE id_admin = ?");
            if ($stmt_update->execute([$name_post, $id_admin_post])) {
                $_SESSION['admin_message_success'] = "✔️ Cập nhật admin thành công.";
                header("Location: qladmin.php");
                exit();
            } else {
                $error = "❌ Lỗi khi cập nhật dữ liệu admin.";
            }
        }
    }

    // Nếu có lỗi, update lại giá trị tên admin để hiển thị trên form
    if ($error !== '') {
        $admin['name'] = $name_post;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">
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
                    class="admin-nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['khuyenmai.php', 'add_khuyenmai.php', 'edit_khuyenmai.php']) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Khuyến mãi</span>
                </a>

                <a href="doanhthu.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'doanhthu.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-database"></i>
                    <span>La bàn dữ liệu</span>
                </a>

                <a href="qladmin.php"
                    class="admin-nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['qladmin.php', 'add_qladmin.php', 'edit_qladmin.php']) ? 'active' : ''; ?>">
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
                    <div class="admin-page-title">Sửa quản trị viên</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">
                <div class="card-panel" style="max-width: 640px; margin: 0 auto;">
                    <div class="card-panel-header d-flex justify-content-between align-items-center">
                        <div class="card-panel-title">Chỉnh sửa thông tin admin</div>
                        <a href="qladmin.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>

                    <div class="card-panel-body mt-3">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Mã admin</label>
                                <input type="text"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($admin['id_admin']); ?>"
                                    disabled>
                                <input type="hidden" name="id_admin"
                                    value="<?php echo htmlspecialchars($admin['id_admin']); ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tên admin <span class="text-danger">*</span></label>
                                <input type="text"
                                    name="name"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($admin['name']); ?>"
                                    required>
                            </div>

                            <div class="col-12 mt-3 d-flex justify-content-between">
                                <a href="qladmin.php" class="btn btn-secondary">
                                    Quay lại
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-floppy-disk"></i> Cập nhật
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>