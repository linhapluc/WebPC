<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

$error   = '';
$success = '';

// Giữ lại tên admin khi có lỗi
$name_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tạo ID admin tự động: AD001, AD002,...
    $stmt_last = $conn->query("
        SELECT id_admin 
        FROM admins 
        WHERE id_admin LIKE 'AD%' 
        ORDER BY id_admin DESC 
        LIMIT 1
    ");
    $last = $stmt_last->fetchColumn();

    if ($last && preg_match('/^AD(\d+)$/', $last, $matches)) {
        $number = (int)$matches[1] + 1;
    } else {
        $number = 1;
    }
    $id_admin = 'AD' . str_pad($number, 3, '0', STR_PAD_LEFT);

    $name             = trim($_POST['name'] ?? '');
    $password         = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    $name_val = $name;

    // Validate
    if ($name === '' || $password === '' || $confirm_password === '') {
        $error = "❌ Vui lòng nhập đầy đủ thông tin.";
    } elseif ($password !== $confirm_password) {
        $error = "❌ Mật khẩu không khớp.";
    }

    if ($error === '') {
        // Kiểm tra trùng mã admin (phòng hi hữu)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE id_admin = ?");
        $stmt->execute([$id_admin]);
        $exists_id = $stmt->fetchColumn();

        // Kiểm tra trùng tên admin
        $stmt2 = $conn->prepare("SELECT COUNT(*) FROM admins WHERE name = ?");
        $stmt2->execute([$name]);
        $exists_name = $stmt2->fetchColumn();

        if ($exists_id > 0) {
            $error = "❌ Mã admin đã tồn tại. Vui lòng thử lại.";
        } elseif ($exists_name > 0) {
            $error = "❌ Tên admin đã được sử dụng.";
        } else {
            // Thêm admin mới
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("
                INSERT INTO admins (id_admin, name, password) 
                VALUES (?, ?, ?)
            ");
            if ($stmt_insert->execute([$id_admin, $name, $hashed_password])) {
                $_SESSION['admin_message_success'] =
                    "✔️ Admin \"" . htmlspecialchars($name) . "\" đã được tạo với mã: " . htmlspecialchars($id_admin);
                header("Location: qladmin.php");
                exit();
            } else {
                $error = "❌ Lỗi khi thêm admin vào cơ sở dữ liệu.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Admin</title>
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
                    <div class="admin-page-title">Thêm quản trị viên</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">
                <div class="card-panel" style="max-width: 640px; margin: 0 auto;">
                    <div class="card-panel-header d-flex justify-content-between align-items-center">
                        <div class="card-panel-title">Tạo admin mới</div>
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
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Tên admin <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control"
                                    name="name"
                                    id="name"
                                    value="<?php echo htmlspecialchars($name_val); ?>"
                                    required>
                            </div>

                            <div class="col-12">
                                <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        required>
                                    <button type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="togglePassword('password')">
                                        👁
                                    </button>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        class="form-control"
                                        required>
                                    <button type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="togglePassword('confirm_password')">
                                        👁
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 mt-3 d-flex justify-content-between">
                                <a href="qladmin.php" class="btn btn-secondary">
                                    Quay lại
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-plus"></i> Thêm admin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            input.type = (input.type === "password") ? "text" : "password";
        }
    </script>
</body>

</html>