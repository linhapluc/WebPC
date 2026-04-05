<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();



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
    $gia = str_replace('.', '', $_POST['gia']); // Xoá dấu chấm
    $gia = filter_var($gia, FILTER_VALIDATE_INT); // Validate lại sau khi loại dấu

    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $thong_so_ky_thuat = trim($_POST['thong_so_ky_thuat'] ?? '');
    $sl = filter_input(INPUT_POST, 'sl', FILTER_VALIDATE_INT);
    $id_loai = $_POST['id_loai'] ?? '';
    $id_thuonghieu = !empty($_POST['id_thuonghieu']) ? $_POST['id_thuonghieu'] : null;

    // Giữ lại giá trị đã nhập để hiển thị lại trên form nếu có lỗi
    $ten_sp_val = $ten_sp;
    $gia_val = $_POST['gia']; // Giữ giá trị gốc người dùng nhập
    $mo_ta_val = $mo_ta;
    $thong_so_ky_thuat_val = $thong_so_ky_thuat;
    $sl_val = $_POST['sl']; // Giữ giá trị gốc
    $id_loai_val = $id_loai;
    $id_thuonghieu_val = $id_thuonghieu;

    $img_name_final_to_db = null; // Sẽ lưu đường dẫn tương đối để lưu vào CSDL

    // Validate dữ liệu cơ bản
    if (empty($ten_sp) || $gia === false || $gia < 0 || $sl === false || $sl < 0 || empty($id_loai)) {
        $error = "❌ Vui lòng điền đầy đủ và chính xác các trường bắt buộc: Tên SP, Giá, Số lượng, Loại sản phẩm.";
    } else {
        try {
            // 1. Kiểm tra trùng tên sản phẩm (không kiểm tra trùng ID nữa)
            $stmt_check_ten = $conn->prepare("SELECT COUNT(*) FROM sanpham WHERE ten_sp = ?");
            $stmt_check_ten->execute([$ten_sp]);
            if ($stmt_check_ten->fetchColumn() > 0) {
                $error = "❌ Tên sản phẩm \"" . htmlspecialchars($ten_sp) . "\" đã tồn tại. Vui lòng chọn tên khác.";
            } else {
                // 2. Xử lý upload hình ảnh (nếu có file được chọn)
                if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
                    $file_info = $_FILES['hinh_anh'];
                    $original_file_name = basename($file_info['name']);
                    $file_tmp_path = $file_info['tmp_name'];
                    $file_size = $file_info['size'];
                    // $file_type = $file_info['type']; // Ít dùng, có thể bỏ
                    $file_ext_arr = explode('.', $original_file_name);
                    $file_ext = strtolower(end($file_ext_arr));

                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $max_file_size = 5 * 1024 * 1024; // 5MB

                    if (!in_array($file_ext, $allowed_extensions)) {
                        $error = "❌ Định dạng file ảnh không hợp lệ (chỉ chấp nhận: " . implode(', ', $allowed_extensions) . ").";
                    } elseif ($file_size > $max_file_size) {
                        $error = "❌ Kích thước file ảnh quá lớn (tối đa 5MB).";
                    } else {
                        // Tạo tên file mới duy nhất hơn
                        $new_file_name_only = "product_" . time() . "_" . uniqid() . "." . $file_ext;


                        $relative_upload_dir_for_db = 'assets/images/';


                        $absolute_upload_dir_on_server = dirname(__DIR__) . '/../' . $relative_upload_dir_for_db;

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
                } elseif (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['hinh_anh']['error'] !== UPLOAD_ERR_OK) {
                    // Nếu có file được chọn nhưng upload bị lỗi (không phải là không chọn file)
                    $error = "❌ Có lỗi xảy ra với file hình ảnh. Mã lỗi PHP: " . $_FILES['hinh_anh']['error'];
                }
                // Nếu không có lỗi nào sau khi validate và xử lý ảnh
                if (empty($error)) {
                    // 3. Tạo ID sản phẩm mới tự động
                    $id_sanpham_new = generate_new_id($conn, 'sanpham', 'id_sanpham', 'SP', 3);

                    // 4. Chuẩn bị câu lệnh INSERT
                    // Thêm cột ngay_tao nếu anh/chị muốn sắp xếp theo ngày thêm
                    // ALTER TABLE sanpham ADD COLUMN ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP;
                    $sql_insert = "INSERT INTO sanpham (id_sanpham, ten_sp, gia, mo_ta, thong_so_ky_thuat, sl, id_loai, id_thuonghieu, hinh_anh) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    // Nếu có ngay_tao: VALUES (?, ..., ?, NOW()) và thêm 1 placeholder

                    $stmt_insert = $conn->prepare($sql_insert);

                    // 5. Thực thi INSERT
                    if ($stmt_insert->execute([$id_sanpham_new, $ten_sp, $gia, $mo_ta, $thong_so_ky_thuat, $sl, $id_loai, $id_thuonghieu, $img_name_final_to_db])) {
                        $_SESSION['admin_message_success'] = "✔️ Sản phẩm \"" . htmlspecialchars($ten_sp) . "\" đã được thêm thành công với mã: " . htmlspecialchars($id_sanpham_new);
                        header("Location: sanpham.php");
                        exit();
                    } else {
                        $error = "❌ Lỗi khi thêm sản phẩm vào cơ sở dữ liệu.";
                    }
                }
            } // Kết thúc else của kiểm tra trùng tên
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ... (CSS giữ nguyên) ... */
    </style>
</head>

<script>
    function formatGia(input) {
        let rawValue = input.value.replace(/\./g, '');
        if (!isNaN(rawValue)) {
            input.value = parseInt(rawValue).toLocaleString('vi-VN');
        }
    }
</script>


<body style="background-color: #f8f9fa;">
    <nav class="navbar navbar-expand-lg bg-primary">
        <div class="container-fluid d-flex align-items-center">
            <a href="sanpham.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <?php if (isset($_SESSION['admin_name'])): // Kiểm tra trước khi hiển thị 
            ?>
                <span class="admin-name ms-auto text-white fs-5 fw-semibold">👋 Xin chào, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></span>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">THÊM SẢN PHẨM MỚI</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): // Mặc dù biến này chưa được gán giá trị thành công nào trong logic trên 
        ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded">
            <!-- Trường Mã sản phẩm đã được comment -->
            <div class="mb-3">
                <label for="ten_sp" class="form-label">Tên sản phẩm</label>
                <input type="text" class="form-control" name="ten_sp" value="<?= htmlspecialchars($ten_sp_val) ?>" required>
            </div>
            <div class="mb-3">
                <label for="gia" class="form-label">Giá</label>
                <input type="text" class="form-control" name="gia" value="<?= number_format((int) $gia_val, 0, ',', '.') ?>" required oninput="this.value = this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" onblur="formatGia(this)">

            </div>
            <div class="mb-3">
                <label for="sl" class="form-label">Số lượng</label>
                <input type="number" class="form-control" name="sl" value="<?= htmlspecialchars($sl_val) ?>" required min="0">
            </div>
            <div class="mb-3">
                <label for="id_loai" class="form-label">Loại sản phẩm</label>
                <select name="id_loai" class="form-select" required>
                    <option value="">-- Chọn loại --</option>
                    <?php
                    // Lấy lại danh sách loại sản phẩm
                    try {
                        $types_query = $conn->query("SELECT id_loaisp, ten_loaisp FROM loaisanpham ORDER BY ten_loaisp ASC");
                        while ($type_row = $types_query->fetch(PDO::FETCH_ASSOC)) {
                            $selected_loai = ($type_row['id_loaisp'] == $id_loai_val) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($type_row['id_loaisp']) . "' {$selected_loai}>" . htmlspecialchars($type_row['ten_loaisp']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option value=''>Lỗi tải loại SP</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_thuonghieu" class="form-label">Thương hiệu</label>
                <select name="id_thuonghieu" id="id_thuonghieu" class="form-select">
                    <option value="">-- Chọn thương hiệu (Nếu có) --</option>
                    <?php
                    try {
                        $brands_query = $conn->query("SELECT id_thuonghieu, ten_thuonghieu FROM thuonghieu ORDER BY ten_thuonghieu ASC");
                        while ($brand_row = $brands_query->fetch(PDO::FETCH_ASSOC)) {
                            $selected_thuonghieu = ($brand_row['id_thuonghieu'] == $id_thuonghieu_val) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($brand_row['id_thuonghieu']) . "' {$selected_thuonghieu}>" . htmlspecialchars($brand_row['ten_thuonghieu']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option value=''>Lỗi tải thương hiệu</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="mo_ta" class="form-label">Mô tả</label>
                <textarea name="mo_ta" class="form-control" rows="4"><?= htmlspecialchars($mo_ta_val) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="thong_so_ky_thuat" class="form-label">Thông số kỹ thuật</label>
                <textarea name="thong_so_ky_thuat" class="form-control" rows="4"><?= htmlspecialchars($thong_so_ky_thuat_val) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="hinh_anh" class="form-label">Hình ảnh</label>
                <input type="file" name="hinh_anh" class="form-control">
                <!-- Có thể hiển thị ảnh cũ nếu là form sửa -->
            </div>
            <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
            <a href="sanpham.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>

</body>

</html>