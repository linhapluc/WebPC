<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Check login bằng admin_name cho đồng bộ
if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

$error = '';
$success = '';

// Lấy danh sách sản phẩm cho dropdown
try {
    $sanphams = $conn->query("
        SELECT id_sanpham, ten_sp 
        FROM sanpham 
        ORDER BY ten_sp ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sanphams = [];
    $error = "Lỗi khi tải danh sách sản phẩm: " . $e->getMessage();
    error_log("Lỗi PDO khi lấy sản phẩm cho add_khuyenmai: " . $e->getMessage());
}

// Để giữ lại giá trị form khi có lỗi
$id_sp_val          = $_POST['id_sanpham']      ?? '';
$ten_km_val         = $_POST['ten_km']          ?? '';
$giam_percent_val   = $_POST['giam_gia_percent'] ?? '';
$giam_tien_val      = $_POST['giam_gia_tien']   ?? '';
$ngay_bd_val        = $_POST['ngay_bat_dau']    ?? date('Y-m-d');
$ngay_kt_val        = $_POST['ngay_ket_thuc']   ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tạo ID khuyến mãi tự động
    $stmt_last = $conn->query("SELECT id_khuyenmai FROM khuyenmai ORDER BY id_khuyenmai DESC LIMIT 1");
    $last = $stmt_last->fetchColumn();
    if ($last && preg_match('/^KM(\d+)$/', $last, $matches)) {
        $number = (int)$matches[1] + 1;
    } else {
        $number = 1;
    }
    $id_km = 'KM' . str_pad($number, 3, '0', STR_PAD_LEFT);

    // Lấy dữ liệu từ form
    $id_sp        = $_POST['id_sanpham'] ?? '';
    $ten_km       = trim($_POST['ten_km'] ?? '');
    $giam_percent = !empty($_POST['giam_gia_percent']) ? (int)$_POST['giam_gia_percent'] : 0;
    $giam_tien    = !empty($_POST['giam_gia_tien'])    ? (int)$_POST['giam_gia_tien']    : 0;

    $ngay_bd_input = $_POST['ngay_bat_dau']  ?? '';
    $ngay_kt_input = $_POST['ngay_ket_thuc'] ?? '';

    // Validate cơ bản
    if (empty($id_sp) || empty($ten_km) || empty($ngay_bd_input) || empty($ngay_kt_input)) {
        $error = "❌ Vui lòng điền đầy đủ các trường bắt buộc.";
    } elseif ($giam_percent == 0 && $giam_tien == 0) {
        $error = "❌ Vui lòng nhập giảm giá theo % hoặc theo tiền.";
    } elseif ($giam_percent > 0 && $giam_tien > 0) {
        $error = "❌ Chỉ chọn một hình thức giảm giá (theo % hoặc theo tiền).";
    } else {
        // Chuyển sang DATETIME cho CSDL
        $ngay_bd_sql = $ngay_bd_input . " 00:00:00";
        $ngay_kt_sql = $ngay_kt_input . " 23:59:59";

        if (strtotime($ngay_bd_sql) > strtotime($ngay_kt_sql)) {
            $error = "❌ Ngày bắt đầu không được lớn hơn ngày kết thúc.";
        } else {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO khuyenmai (
                        id_khuyenmai, id_sanpham, ten_km, 
                        giam_gia_percent, giam_gia_tien, 
                        ngay_bat_dau, ngay_ket_thuc
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([
                    $id_km,
                    $id_sp,
                    $ten_km,
                    $giam_percent,
                    $giam_tien,
                    $ngay_bd_sql,
                    $ngay_kt_sql
                ])) {
                    $_SESSION['admin_message_success'] =
                        "✔️ Khuyến mãi \"" . htmlspecialchars($ten_km) .
                        "\" cho sản phẩm " . htmlspecialchars($id_sp) . " đã được thêm thành công.";
                    header("Location: khuyenmai.php");
                    exit();
                } else {
                    $error = "❌ Lỗi khi thêm khuyến mãi vào cơ sở dữ liệu.";
                }
            } catch (PDOException $e) {
                error_log("Lỗi PDO khi thêm khuyến mãi: " . $e->getMessage());
                $error = "❌ Lỗi hệ thống khi thêm khuyến mãi. Mã lỗi: " . $e->getCode();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm khuyến mãi</title>
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
                    <div class="admin-page-title">Thêm khuyến mãi</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">
                <div class="card-panel">
                    <div class="card-panel-header d-flex justify-content-between align-items-center">
                        <div class="card-panel-title">Thêm khuyến mãi mới</div>
                        <a href="khuyenmai.php" class="btn btn-sm btn-outline-secondary">
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
                            <div class="col-md-6">
                                <label class="form-label" for="id_sanpham_km">
                                    Sản phẩm áp dụng <span class="text-danger">*</span>
                                </label>
                                <select name="id_sanpham" id="id_sanpham_km" class="form-select" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                    <?php foreach ($sanphams as $sp): ?>
                                        <option value="<?php echo htmlspecialchars($sp['id_sanpham']); ?>"
                                            <?php echo ($id_sp_val === $sp['id_sanpham']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sp['id_sanpham']) . ' - ' . htmlspecialchars($sp['ten_sp']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="ten_km_input">
                                    Tên khuyến mãi <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    name="ten_km"
                                    id="ten_km_input"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($ten_km_val); ?>"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="giam_gia_percent_input">
                                    Giảm giá (%)
                                </label>
                                <input type="number"
                                    name="giam_gia_percent"
                                    id="giam_gia_percent_input"
                                    class="form-control"
                                    min="0" max="100"
                                    placeholder="Để trống nếu giảm theo tiền"
                                    value="<?php echo htmlspecialchars($giam_percent_val); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="giam_gia_tien_input">
                                    Giảm giá theo tiền (₫)
                                </label>
                                <input type="number"
                                    name="giam_gia_tien"
                                    id="giam_gia_tien_input"
                                    class="form-control"
                                    min="0"
                                    placeholder="Để trống nếu giảm theo %"
                                    value="<?php echo htmlspecialchars($giam_tien_val); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="ngay_bat_dau_input">
                                    Ngày bắt đầu <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                    name="ngay_bat_dau"
                                    id="ngay_bat_dau_input"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($ngay_bd_val); ?>"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="ngay_ket_thuc_input">
                                    Ngày kết thúc <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                    name="ngay_ket_thuc"
                                    id="ngay_ket_thuc_input"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($ngay_kt_val); ?>"
                                    required>
                            </div>

                            <div class="col-12 mt-3 d-flex justify-content-between">
                                <a href="khuyenmai.php" class="btn btn-secondary">
                                    Quay lại
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-plus"></i> Thêm khuyến mãi
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