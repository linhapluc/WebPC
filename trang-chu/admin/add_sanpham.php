<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

$error = '';
$success = '';

$ten_sp_val = '';
$gia_val = '';
$mo_ta_val = '';
$thong_so_ky_thuat_val = '';
$sl_val = '';
$id_loai_val = '';
$id_thuonghieu_val = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy và làm sạch dữ liệu từ form
    $ten_sp = trim($_POST['ten_sp'] ?? '');
    $gia_raw = $_POST['gia'] ?? '';
    $gia = str_replace('.', '', $gia_raw);
    $gia = filter_var($gia, FILTER_VALIDATE_INT);

    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $thong_so_ky_thuat = trim($_POST['thong_so_ky_thuat'] ?? '');
    $sl = filter_input(INPUT_POST, 'sl', FILTER_VALIDATE_INT);
    $id_loai = $_POST['id_loai'] ?? '';
    $id_thuonghieu = !empty($_POST['id_thuonghieu']) ? $_POST['id_thuonghieu'] : null;

    // Giữ lại giá trị đã nhập để hiển thị lại trên form nếu có lỗi
    $ten_sp_val = $ten_sp;
    $gia_val = $gia_raw;
    $mo_ta_val = $mo_ta;
    $thong_so_ky_thuat_val = $thong_so_ky_thuat;
    $sl_val = $_POST['sl'] ?? '';
    $id_loai_val = $id_loai;
    $id_thuonghieu_val = $id_thuonghieu;

    $img_name_final_to_db = null;

    // Validate cơ bản
    if (empty($ten_sp) || $gia === false || $gia < 0 || $sl === false || $sl < 0 || empty($id_loai)) {
        $error = "❌ Vui lòng điền đầy đủ và chính xác các trường bắt buộc: Tên SP, Giá, Số lượng, Loại sản phẩm.";
    } else {
        try {
            // 1. Kiểm tra trùng tên sản phẩm
            $stmt_check_ten = $conn->prepare("SELECT COUNT(*) FROM sanpham WHERE ten_sp = ?");
            $stmt_check_ten->execute([$ten_sp]);
            if ($stmt_check_ten->fetchColumn() > 0) {
                $error = "❌ Tên sản phẩm \"" . htmlspecialchars($ten_sp) . "\" đã tồn tại. Vui lòng chọn tên khác.";
            } else {
                // 2. Xử lý upload hình ảnh (nếu có)
                if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
                    $file_info = $_FILES['hinh_anh'];
                    $original_file_name = basename($file_info['name']);
                    $file_tmp_path = $file_info['tmp_name'];
                    $file_size = $file_info['size'];
                    $file_ext_arr = explode('.', $original_file_name);
                    $file_ext = strtolower(end($file_ext_arr));

                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $max_file_size = 5 * 1024 * 1024; // 5MB

                    if (!in_array($file_ext, $allowed_extensions)) {
                        $error = "❌ Định dạng file ảnh không hợp lệ (chỉ chấp nhận: " . implode(', ', $allowed_extensions) . ").";
                    } elseif ($file_size > $max_file_size) {
                        $error = "❌ Kích thước file ảnh quá lớn (tối đa 5MB).";
                    } else {
                        $new_file_name_only = "product_" . time() . "_" . uniqid() . "." . $file_ext;

                        // Đường dẫn lưu trong DB (tương đối từ root project)
                        $relative_upload_dir_for_db = 'assets/images/';

                        // Thư mục lưu thật trên server (trong /trang-chu/assets/images/)
                        $absolute_upload_dir_on_server = dirname(__DIR__) . '/assets/images/';

                        if (!is_dir($absolute_upload_dir_on_server)) {
                            if (!mkdir($absolute_upload_dir_on_server, 0775, true)) {
                                $error = "❌ Không thể tạo thư mục upload: " . htmlspecialchars($absolute_upload_dir_on_server);
                            }
                        }

                        if (empty($error)) {
                            $target_path_absolute = rtrim($absolute_upload_dir_on_server, '/') . '/' . $new_file_name_only;
                            if (move_uploaded_file($file_tmp_path, $target_path_absolute)) {
                                $img_name_final_to_db = rtrim($relative_upload_dir_for_db, '/') . '/' . $new_file_name_only;
                            } else {
                                $error = "❌ Upload hình ảnh thất bại. Kiểm tra đường dẫn và quyền ghi: " . htmlspecialchars($target_path_absolute);
                            }
                        }
                    }
                } elseif (
                    isset($_FILES['hinh_anh']) &&
                    $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_NO_FILE &&
                    $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_OK
                ) {
                    $error = "❌ Có lỗi xảy ra với file hình ảnh. Mã lỗi PHP: " . $_FILES['hinh_anh']['error'];
                }

                // 3. Nếu không có lỗi -> INSERT
                if (empty($error)) {
                    // Hàm generate_new_id giả định đã khai báo ở file khác
                    $id_sanpham_new = generate_new_id($conn, 'sanpham', 'id_sanpham', 'SP', 3);

                    $sql_insert = "INSERT INTO sanpham 
                        (id_sanpham, ten_sp, gia, mo_ta, thong_so_ky_thuat, sl, id_loai, id_thuonghieu, hinh_anh) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt_insert = $conn->prepare($sql_insert);

                    if ($stmt_insert->execute([
                        $id_sanpham_new,
                        $ten_sp,
                        $gia,
                        $mo_ta,
                        $thong_so_ky_thuat,
                        $sl,
                        $id_loai,
                        $id_thuonghieu,
                        $img_name_final_to_db
                    ])) {
                        $_SESSION['admin_message_success'] =
                            "✔️ Sản phẩm \"" . htmlspecialchars($ten_sp) .
                            "\" đã được thêm thành công với mã: " . htmlspecialchars($id_sanpham_new);
                        header("Location: sanpham.php");
                        exit();
                    } else {
                        $error = "❌ Lỗi khi thêm sản phẩm vào cơ sở dữ liệu.";
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi thêm sản phẩm: " . $e->getMessage() . " --- SQLSTATE: " . $e->getCode());
            $error = "❌ Lỗi hệ thống khi thêm sản phẩm. Vui lòng thử lại. Mã lỗi: " . $e->getCode();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">

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
                    <div class="admin-page-title">Thêm sản phẩm</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">
                <div class="card-panel">
                    <div class="card-panel-header d-flex justify-content-between align-items-center">
                        <div class="card-panel-title">Thêm sản phẩm mới</div>
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
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="row g-3">
                            <div class="col-md-6">
                                <label for="ten_sp" class="form-label">
                                    Tên sản phẩm <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="ten_sp"
                                    value="<?php echo htmlspecialchars($ten_sp_val); ?>" required>
                            </div>

                            <div class="col-md-3">
                                <label for="gia" class="form-label">
                                    Giá <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    name="gia"
                                    value="<?php
                                            if ($gia_val !== '') {
                                                $clean = str_replace('.', '', $gia_val);
                                                echo number_format((int)$clean, 0, ',', '.');
                                            }
                                            ?>"
                                    required
                                    oninput="this.value = this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                    onblur="formatGia(this)">
                            </div>

                            <div class="col-md-3">
                                <label for="sl" class="form-label">
                                    Số lượng <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="sl"
                                    value="<?php echo htmlspecialchars($sl_val); ?>" required min="0">
                            </div>

                            <div class="col-md-6">
                                <label for="id_loai" class="form-label">
                                    Loại sản phẩm <span class="text-danger">*</span>
                                </label>
                                <select name="id_loai" class="form-select" required>
                                    <option value="">-- Chọn loại --</option>
                                    <?php
                                    try {
                                        $types_query = $conn->query("SELECT id_loaisp, ten_loaisp FROM loaisanpham ORDER BY ten_loaisp ASC");
                                        while ($type_row = $types_query->fetch(PDO::FETCH_ASSOC)) {
                                            $selected_loai = ($type_row['id_loaisp'] == $id_loai_val) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($type_row['id_loaisp']) . "' {$selected_loai}>" .
                                                htmlspecialchars($type_row['ten_loaisp']) . "</option>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<option value=''>Lỗi tải loại SP</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="id_thuonghieu" class="form-label">Thương hiệu</label>
                                <select name="id_thuonghieu" id="id_thuonghieu" class="form-select">
                                    <option value="">-- Chọn thương hiệu (Nếu có) --</option>
                                    <?php
                                    try {
                                        $brands_query = $conn->query("SELECT id_thuonghieu, ten_thuonghieu FROM thuonghieu ORDER BY ten_thuonghieu ASC");
                                        while ($brand_row = $brands_query->fetch(PDO::FETCH_ASSOC)) {
                                            $selected_th = ($brand_row['id_thuonghieu'] == $id_thuonghieu_val) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($brand_row['id_thuonghieu']) . "' {$selected_th}>" .
                                                htmlspecialchars($brand_row['ten_thuonghieu']) . "</option>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<option value=''>Lỗi tải thương hiệu</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="mo_ta" class="form-label">Mô tả</label>
                                <textarea name="mo_ta" class="form-control" rows="3"><?php
                                                                                        echo htmlspecialchars($mo_ta_val); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="thong_so_ky_thuat" class="form-label">Thông số kỹ thuật</label>
                                <textarea name="thong_so_ky_thuat" class="form-control" rows="3"><?php
                                                                                                    echo htmlspecialchars($thong_so_ky_thuat_val); ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="hinh_anh" class="form-label">Hình ảnh</label>
                                <input type="file" name="hinh_anh" class="form-control">
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-plus"></i> Thêm sản phẩm
                                </button>
                                <a href="sanpham.php" class="btn btn-secondary ms-2">Quay lại</a>
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