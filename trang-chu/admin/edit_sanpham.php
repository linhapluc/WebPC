<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Check login
if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

if (!isset($_GET['id'])) {
    die("Thiếu ID sản phẩm.");
}
$id = $_GET['id'];

// Lấy dữ liệu sản phẩm
$stmt = $conn->prepare("SELECT * FROM sanpham WHERE id_sanpham = ?");
$stmt->execute([$id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sp) {
    die("Không tìm thấy sản phẩm!");
}

// Lấy danh sách loại sản phẩm
$loai_stmt = $conn->query("SELECT id_loaisp, ten_loaisp FROM loaisanpham ORDER BY ten_loaisp ASC");
$loais = $loai_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_id   = trim($_POST['id_sanpham'] ?? '');
    $ten_sp   = trim($_POST['ten_sp'] ?? '');

    // Giá cho phép nhập có dấu chấm -> bỏ chấm, validate int
    $gia_raw  = $_POST['gia'] ?? '';
    $gia_num  = str_replace('.', '', $gia_raw);
    $gia      = filter_var($gia_num, FILTER_VALIDATE_INT);

    $mo_ta    = trim($_POST['mo_ta'] ?? '');
    $thong_so = trim($_POST['thong_so'] ?? '');
    $sl       = filter_input(INPUT_POST, 'sl', FILTER_VALIDATE_INT);
    $id_loai  = $_POST['id_loai'] ?? '';

    // Giữ ảnh cũ mặc định
    $hinh_anh = $sp['hinh_anh'];

    // Validate cơ bản
    if ($new_id === '' || $ten_sp === '' || $gia === false || $gia < 0 || $sl === false || $sl < 0 || $id_loai === '') {
        $error = "❌ Vui lòng điền đầy đủ và chính xác các trường bắt buộc.";
    } else {
        // Upload ảnh mới (nếu có)
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {

            $file_info          = $_FILES['hinh_anh'];
            $original_file_name = basename($file_info['name']);
            $file_tmp_path      = $file_info['tmp_name'];
            $file_size          = $file_info['size'];

            $file_ext_arr = explode('.', $original_file_name);
            $file_ext     = strtolower(end($file_ext_arr));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $max_file_size      = 5 * 1024 * 1024; // 5MB

            if (!in_array($file_ext, $allowed_extensions, true)) {
                $error = "❌ Định dạng file ảnh không hợp lệ (chỉ chấp nhận: " . implode(', ', $allowed_extensions) . ").";
            } elseif ($file_size > $max_file_size) {
                $error = "❌ Kích thước file ảnh quá lớn (tối đa 5MB).";
            } else {
                $new_file_name_only = "product_" . time() . "_" . uniqid() . "." . $file_ext;

                // Đường dẫn tương đối lưu vào DB
                $relative_upload_dir_for_db     = 'assets/images/';
                // Thư mục tuyệt đối trên server
                $absolute_upload_dir_on_server  = dirname(__DIR__) . '/assets/images/';

                if (!is_dir($absolute_upload_dir_on_server)) {
                    if (!mkdir($absolute_upload_dir_on_server, 0775, true)) {
                        $error = "❌ Không thể tạo thư mục upload: " . htmlspecialchars($absolute_upload_dir_on_server);
                    }
                }

                if ($error === '') {
                    $target_path_absolute = rtrim($absolute_upload_dir_on_server, '/') . '/' . $new_file_name_only;

                    if (move_uploaded_file($file_tmp_path, $target_path_absolute)) {
                        $hinh_anh = rtrim($relative_upload_dir_for_db, '/') . '/' . $new_file_name_only;
                    } else {
                        $error = "❌ Upload hình ảnh thất bại. Kiểm tra đường dẫn và quyền ghi.";
                    }
                }
            }
        } elseif (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_OK) {
            $error = "❌ Có lỗi xảy ra với file hình ảnh. Mã lỗi PHP: " . $_FILES['hinh_anh']['error'];
        }

        // Kiểm tra trùng mã SP (nếu đổi mã)
        if ($error === '' && $new_id !== $id) {
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM sanpham WHERE id_sanpham = ?");
            $stmt_check->execute([$new_id]);
            if ((int)$stmt_check->fetchColumn() > 0) {
                $error = "❌ Mã sản phẩm đã tồn tại. Vui lòng chọn mã khác.";
            }
        }

        // Kiểm tra tên sản phẩm trùng (trừ chính nó)
        if ($error === '') {
            $stmt_check_name = $conn->prepare("SELECT COUNT(*) FROM sanpham WHERE ten_sp = ? AND id_sanpham != ?");
            $stmt_check_name->execute([$ten_sp, $id]);
            if ((int)$stmt_check_name->fetchColumn() > 0) {
                $error = "❌ Tên sản phẩm đã tồn tại. Vui lòng chọn tên khác.";
            }
        }

        // Nếu không có lỗi -> cập nhật
        if ($error === '') {
            $update = $conn->prepare("
                UPDATE sanpham
                SET id_sanpham = ?, ten_sp = ?, gia = ?, mo_ta = ?, thong_so_ky_thuat = ?, sl = ?, id_loai = ?, hinh_anh = ?
                WHERE id_sanpham = ?
            ");

            $update->execute([
                $new_id,
                $ten_sp,
                $gia,
                $mo_ta,
                $thong_so,
                $sl,
                $id_loai,
                $hinh_anh,
                $id
            ]);

            header("Location: sanpham.php");
            exit();
        }
    }

    // Gán lại giá trị cho form nếu lỗi
    $sp['id_sanpham']        = $new_id;
    $sp['ten_sp']            = $ten_sp;
    $sp['gia']               = ($gia === false ? 0 : $gia);
    $sp['mo_ta']             = $mo_ta;
    $sp['thong_so_ky_thuat'] = $thong_so;
    $sp['sl']                = ($sl === false ? 0 : $sl);
    $sp['id_loai']           = $id_loai;
    $sp['hinh_anh']          = $hinh_anh;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">

    <style>
        /* Card mini để đồng bộ UI */
        .mini-card {
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 6px 18px rgba(17, 24, 39, .06);
            overflow: hidden;
        }

        .mini-card-head {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(15, 23, 42, .06);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mini-card-title {
            font-weight: 700;
            font-size: .95rem;
            margin: 0;
        }

        .mini-card-body {
            padding: 14px;
        }

        /* Preview ảnh: khung cố định, đẹp, không “hở” */
        .img-preview-box {
            width: 100%;
            aspect-ratio: 4 / 3;
            border-radius: 14px;
            background: #f6f7fb;
            border: 1px solid rgba(15, 23, 42, .08);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* ✅ hết khoảng trống */
            display: block;
        }

        .img-empty {
            color: #94a3b8;
            font-size: .9rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 18px;
            text-align: center;
        }

        /* Textarea nhìn gọn hơn */
        textarea.form-control {
            min-height: 120px;
        }
    </style>

    <script>
        function formatGia(input) {
            let rawValue = input.value.replace(/\./g, '');
            if (!isNaN(rawValue) && rawValue !== '') {
                input.value = parseInt(rawValue).toLocaleString('vi-VN');
            }
        }
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
                    class="admin-nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['sanpham.php', 'add_sanpham.php', 'edit_sanpham.php'], true) ? 'active' : ''; ?>">
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
                    <div class="admin-page-title">Sửa sản phẩm</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">
                <div class="card-panel">
                    <div class="card-panel-header d-flex justify-content-between align-items-center">
                        <div class="card-panel-title">Chỉnh sửa sản phẩm</div>
                        <a href="sanpham.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>

                    <div class="card-panel-body mt-3">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <!-- ✅ Layout mới: trái 8 / phải 4 (không còn lỗ trống) -->
                            <div class="row g-3">
                                <!-- LEFT: Form fields -->
                                <div class="col-lg-8">
                                    <div class="mini-card">
                                        <div class="mini-card-head">
                                            <div class="mini-card-title">Thông tin sản phẩm</div>
                                        </div>
                                        <div class="mini-card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Mã sản phẩm <span class="text-danger">*</span></label>
                                                    <input type="text" name="id_sanpham" class="form-control"
                                                        value="<?php echo htmlspecialchars($sp['id_sanpham']); ?>" required>
                                                </div>

                                                <div class="col-md-8">
                                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                                    <input type="text" name="ten_sp" class="form-control"
                                                        value="<?php echo htmlspecialchars($sp['ten_sp']); ?>" required>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Giá <span class="text-danger">*</span></label>
                                                    <input type="text" name="gia" class="form-control"
                                                        value="<?php echo number_format((int)($sp['gia'] ?? 0), 0, ',', '.'); ?>"
                                                        required
                                                        oninput="this.value = this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                                        onblur="formatGia(this)">
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                                    <input type="number" name="sl" class="form-control"
                                                        value="<?php echo (int)($sp['sl'] ?? 0); ?>" required min="0">
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Loại sản phẩm <span class="text-danger">*</span></label>
                                                    <select name="id_loai" class="form-select" required>
                                                        <?php foreach ($loais as $loai): ?>
                                                            <option value="<?php echo htmlspecialchars($loai['id_loaisp']); ?>"
                                                                <?php echo ($sp['id_loai'] === $loai['id_loaisp']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($loai['ten_loaisp']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT: Image panel -->
                                <div class="col-lg-4">
                                    <div class="mini-card">
                                        <div class="mini-card-head">
                                            <div class="mini-card-title">Hình ảnh</div>
                                        </div>
                                        <div class="mini-card-body">
                                            <div class="img-preview-box mb-3">
                                                <?php if (!empty($sp['hinh_anh'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($sp['hinh_anh']); ?>" alt="Ảnh sản phẩm">
                                                <?php else: ?>
                                                    <div class="img-empty">
                                                        <i class="fa-regular fa-image fa-2x"></i>
                                                        <div>Chưa có hình ảnh</div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <label class="form-label">Thay ảnh mới (nếu muốn)</label>
                                            <input type="file" name="hinh_anh" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                                            <small class="text-muted d-block mt-2">Chấp nhận: jpg, jpeg, png, gif, webp. Tối đa 5MB.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bottom: Description + Specs (cân đều) -->
                                <div class="col-lg-6">
                                    <div class="mini-card">
                                        <div class="mini-card-head">
                                            <div class="mini-card-title">Mô tả</div>
                                        </div>
                                        <div class="mini-card-body">
                                            <textarea name="mo_ta" class="form-control" rows="6"><?php echo htmlspecialchars($sp['mo_ta'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mini-card">
                                        <div class="mini-card-head">
                                            <div class="mini-card-title">Thông số kỹ thuật</div>
                                        </div>
                                        <div class="mini-card-body">
                                            <textarea name="thong_so" class="form-control" rows="6"><?php echo htmlspecialchars($sp['thong_so_ky_thuat'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-12 d-flex justify-content-between mt-2">
                                    <a href="sanpham.php" class="btn btn-secondary">Quay lại</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                                    </button>
                                </div>
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