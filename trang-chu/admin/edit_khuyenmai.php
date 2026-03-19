<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Check login
if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    die("Thiếu mã khuyến mãi.");
}
$id_km = trim($_GET['id']);

// Lấy dữ liệu khuyến mãi
$stmt = $conn->prepare("SELECT * FROM khuyenmai WHERE id_khuyenmai = ?");
$stmt->execute([$id_km]);
$km = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$km) die("Khuyến mãi không tồn tại!");

// Lấy danh sách sản phẩm
$sp_stmt = $conn->query("SELECT id_sanpham, ten_sp FROM sanpham ORDER BY ten_sp ASC");
$sanphams = $sp_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sanpham   = trim($_POST['id_sanpham'] ?? '');
    $ten_km       = trim($_POST['ten_km'] ?? '');

    $giam_percent = isset($_POST['giam_gia_percent']) && $_POST['giam_gia_percent'] !== ''
        ? (int)$_POST['giam_gia_percent'] : 0;

    $giam_tien    = isset($_POST['giam_gia_tien']) && $_POST['giam_gia_tien'] !== ''
        ? (int)$_POST['giam_gia_tien'] : 0;

    $ngay_bd      = $_POST['ngay_bat_dau'] ?? '';
    $ngay_kt      = $_POST['ngay_ket_thuc'] ?? '';

    if ($id_sanpham === '' || $ten_km === '' || $ngay_bd === '' || $ngay_kt === '') {
        $error = "❌ Vui lòng nhập đủ thông tin bắt buộc.";
    } elseif (strtotime($ngay_kt) < strtotime($ngay_bd)) {
        $error = "❌ Ngày kết thúc không được nhỏ hơn ngày bắt đầu.";
    } else {
        $stmt = $conn->prepare("
            UPDATE khuyenmai
            SET id_sanpham = ?, ten_km = ?, giam_gia_percent = ?, giam_gia_tien = ?, ngay_bat_dau = ?, ngay_ket_thuc = ?
            WHERE id_khuyenmai = ?
        ");
        $stmt->execute([$id_sanpham, $ten_km, $giam_percent, $giam_tien, $ngay_bd, $ngay_kt, $id_km]);

        header("Location: khuyenmai.php");
        exit();
    }

    // giữ lại input khi báo lỗi
    $km['id_sanpham']       = $id_sanpham;
    $km['ten_km']           = $ten_km;
    $km['giam_gia_percent'] = $giam_percent;
    $km['giam_gia_tien']    = $giam_tien;
    $km['ngay_bat_dau']     = $ngay_bd;
    $km['ngay_ket_thuc']    = $ngay_kt;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa khuyến mãi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">

    <style>
        .form-hint{ font-size:.85rem; color:#6c757d; }
    </style>
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
            <a href="home.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'home.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-house"></i><span>Trang chủ</span>
            </a>
            <a href="donhang.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'donhang.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-clipboard"></i><span>Đơn hàng</span>
            </a>
            <a href="sanpham.php" class="admin-nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['sanpham.php','add_sanpham.php','edit_sanpham.php','xem_sanpham.php']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-box"></i><span>Sản phẩm</span>
            </a>

            <div class="admin-sidebar-divider"></div>

            <a href="khuyenmai.php" class="admin-nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['khuyenmai.php','add_khuyenmai.php','edit_khuyenmai.php']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-bullhorn"></i><span>Khuyến mãi</span>
            </a>

            <a href="doanhthu.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'doanhthu.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-database"></i><span>La bàn dữ liệu</span>
            </a>
            <a href="qladmin.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'qladmin.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-id-badge"></i><span>Tình trạng tài khoản</span>
            </a>

            <div class="admin-sidebar-divider"></div>

            <a href="khachhang.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khachhang.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i><span>Khách hàng</span>
            </a>
            <a href="danhgia.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'danhgia.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-star"></i><span>Đánh giá</span>
            </a>
            <a href="lienket.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'lienket.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-link"></i><span>Liên kết</span>
            </a>

            <div class="admin-sidebar-divider"></div>

            <a href="logout.php" class="admin-nav-item admin-sidebar-logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i><span>Đăng xuất</span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">© <?php echo date('Y'); ?> Nasa Shop</div>
    </aside>

    <!-- MAIN -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <div class="admin-page-title">Sửa khuyến mãi</div>
            </div>
            <div class="admin-topbar-right">
                <span>👋 Xin chào, <strong><?php echo h($adminName); ?></strong></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="card-panel">
                <div class="card-panel-header d-flex justify-content-between align-items-center gap-2">
                    <div class="card-panel-title">Chỉnh sửa khuyến mãi</div>
                    <a href="khuyenmai.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>

                <div class="card-panel-body mt-3">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo h($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                            <select name="id_sanpham" class="form-select" required>
                                <?php foreach ($sanphams as $sp): ?>
                                    <option value="<?php echo h($sp['id_sanpham']); ?>"
                                        <?php echo ($sp['id_sanpham'] == $km['id_sanpham']) ? 'selected' : ''; ?>>
                                        <?php echo h($sp['ten_sp']); ?> (<?php echo h($sp['id_sanpham']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-hint mt-1">Chọn đúng sản phẩm áp dụng khuyến mãi.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                            <input type="text" name="ten_km" class="form-control"
                                   value="<?php echo h($km['ten_km']); ?>" required>
                            <div class="form-hint mt-1">VD: Flash Sale 12/12, Giảm giá Noel...</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Giảm giá (%)</label>
                            <input type="number" name="giam_gia_percent" class="form-control"
                                   min="0" max="100"
                                   value="<?php echo (int)$km['giam_gia_percent']; ?>">
                            <div class="form-hint mt-1">Nhập 0 nếu không dùng giảm theo %.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Giảm giá tiền (VNĐ)</label>
                            <input type="number" name="giam_gia_tien" class="form-control"
                                   min="0"
                                   value="<?php echo (int)$km['giam_gia_tien']; ?>">
                            <div class="form-hint mt-1">Nhập 0 nếu không dùng giảm theo số tiền.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="date" name="ngay_bat_dau" class="form-control"
                                   value="<?php echo h($km['ngay_bat_dau']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="ngay_ket_thuc" class="form-control"
                                   value="<?php echo h($km['ngay_ket_thuc']); ?>" required>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <a href="khuyenmai.php" class="btn btn-outline-secondary">
                                Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
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
