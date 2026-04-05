<?php
require('../includes/connect.php'); // Đảm bảo đường dẫn này đúng
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra đăng nhập admin (NÊN CÓ)
// if (!isset($_SESSION['admin_id'])) {
//     // Chuyển hướng đến trang đăng nhập admin
//     header('Location: login.php'); 
//     exit;
// }

$error = '';
$success = ''; // Biến này chưa được dùng để gán thông báo thành công

// Lấy danh sách sản phẩm cho dropdown
try {
    $sanphams = $conn->query("SELECT id_sanpham, ten_sp FROM sanpham ORDER BY ten_sp ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sanphams = [];
    $error = "Lỗi khi tải danh sách sản phẩm: " . $e->getMessage();
    error_log("Lỗi PDO khi lấy sản phẩm cho add_khuyenmai: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tạo ID khuyến mãi tự động (giữ nguyên logic của anh/chị)
    $stmt_last = $conn->query("SELECT id_khuyenmai FROM khuyenmai ORDER BY id_khuyenmai DESC LIMIT 1");
    $last = $stmt_last->fetchColumn();
    if ($last && preg_match('/^KM(\d+)$/', $last, $matches)) {
        $number = (int)$matches[1] + 1;
    } else {
        $number = 1;
    }
    $id_km = 'KM' . str_pad($number, 3, '0', STR_PAD_LEFT);

    // Lấy dữ liệu từ form
    $id_sp = $_POST['id_sanpham'] ?? '';
    $ten_km = trim($_POST['ten_km'] ?? '');
    $giam_percent = !empty($_POST['giam_gia_percent']) ? (int)$_POST['giam_gia_percent'] : 0;
    $giam_tien = !empty($_POST['giam_gia_tien']) ? (int)$_POST['giam_gia_tien'] : 0;
    
    $ngay_bd_input = $_POST['ngay_bat_dau'] ?? ''; // Từ input type="date" sẽ là YYYY-MM-DD
    $ngay_kt_input = $_POST['ngay_ket_thuc'] ?? ''; // Từ input type="date" sẽ là YYYY-MM-DD

    // Validate cơ bản
    if (empty($id_sp) || empty($ten_km) || empty($ngay_bd_input) || empty($ngay_kt_input)) {
        $error = "❌ Vui lòng điền đầy đủ các trường bắt buộc.";
    } elseif ($giam_percent == 0 && $giam_tien == 0) {
        $error = "❌ Vui lòng nhập giảm giá theo % hoặc theo tiền.";
    } elseif ($giam_percent > 0 && $giam_tien > 0) {
        $error = "❌ Chỉ chọn một hình thức giảm giá (theo % hoặc theo tiền).";
    } else {
        // CHUYỂN ĐỔI ĐỊNH DẠNG NGÀY THÁNG SANG DATETIME CHO CSDL
        // Giả sử cột trong CSDL là DATETIME
        $ngay_bd_sql = $ngay_bd_input . " 00:00:00"; // Bắt đầu từ 0 giờ ngày đó
        $ngay_kt_sql = $ngay_kt_input . " 23:59:59"; // Kết thúc vào cuối ngày đó

        // So sánh ngày bằng timestamp để đảm bảo chính xác
        if (strtotime($ngay_bd_sql) > strtotime($ngay_kt_sql)) {
            $error = "❌ Ngày bắt đầu không được lớn hơn ngày kết thúc.";
        } else {
            try {
                // Kiểm tra trùng mã khuyến mãi (mặc dù đã tạo tự động, vẫn nên có để phòng trường hợp hi hữu)
                // $check = $conn->prepare("SELECT id_khuyenmai FROM khuyenmai WHERE id_khuyenmai = ?");
                // $check->execute([$id_km]);
                // if ($check->rowCount() > 0) {
                //     $error = "❌ Mã khuyến mãi đã tồn tại (Lỗi hệ thống, vui lòng thử lại).";
                // } else {
                    $stmt = $conn->prepare("INSERT INTO khuyenmai (id_khuyenmai, id_sanpham, ten_km, giam_gia_percent, giam_gia_tien, ngay_bat_dau, ngay_ket_thuc) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$id_km, $id_sp, $ten_km, $giam_percent, $giam_tien, $ngay_bd_sql, $ngay_kt_sql])) {
                        $_SESSION['admin_message_success'] = "✔️ Khuyến mãi \"".htmlspecialchars($ten_km)."\" cho sản phẩm ".htmlspecialchars($id_sp)." đã được thêm thành công.";
                        header("Location: khuyenmai.php"); // Chuyển hướng về trang danh sách KM
                        exit();
                    } else {
                        $error = "❌ Lỗi khi thêm khuyến mãi vào cơ sở dữ liệu.";
                    }
                // }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ... (CSS giữ nguyên như của anh/chị) ... */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .navbar { background-color: #007bff; padding: 16px 24px; }
        .navbar-brand { font-weight: bold; color: white; font-size: 24px; }
        .admin-name { margin-left: auto; color: white; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-primary">
        <div class="container-fluid d-flex align-items-center">
            <a href="khuyenmai.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <?php if(isset($_SESSION['admin_name'])): ?>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">👋 Xin chào, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></span>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">THÊM KHUYẾN MÃI</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success) && empty($error)): // Chỉ hiển thị success nếu không có error ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="add_khuyenmai.php" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
                <label class="form-label" for="id_sanpham_km">Sản phẩm áp dụng</label>
                <select name="id_sanpham" id="id_sanpham_km" class="form-select" required>
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php foreach ($sanphams as $sp): ?>
                        <option value="<?= htmlspecialchars($sp['id_sanpham']) ?>"><?= htmlspecialchars($sp['id_sanpham']) ?> - <?= htmlspecialchars($sp['ten_sp']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="ten_km_input">Tên khuyến mãi</label>
                <input type="text" name="ten_km" id="ten_km_input" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="giam_gia_percent_input">Giảm giá (%)</label>
                <input type="number" name="giam_gia_percent" id="giam_gia_percent_input" class="form-control" min="0" max="100" placeholder="Để trống nếu giảm theo tiền">
            </div>
            <div class="mb-3">
                <label class="form-label" for="giam_gia_tien_input">Giảm giá theo tiền (₫)</label>
                <input type="number" name="giam_gia_tien" id="giam_gia_tien_input" class="form-control" min="0" placeholder="Để trống nếu giảm theo %">
            </div>
            <div class="mb-3">
                <label class="form-label" for="ngay_bat_dau_input">Ngày bắt đầu</label>
                <input type="date" name="ngay_bat_dau" id="ngay_bat_dau_input" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="ngay_ket_thuc_input">Ngày kết thúc</label>
                <input type="date" name="ngay_ket_thuc" id="ngay_ket_thuc_input" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="d-flex justify-content-between">
                <a href="khuyenmai.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Thêm khuyến mãi</button>
            </div>
        </form>
    </div>
</body>
</html>