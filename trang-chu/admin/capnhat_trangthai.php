<?php
require('../includes/connect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chỉ admin đã login mới được cập nhật
if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}

$id_donhang = $_GET['id_donhang'] ?? '';
$action     = $_GET['action'] ?? '';

if ($id_donhang === '' || $action === '') {
    header('Location: donhang.php');
    exit();
}

$newStatus      = null;
$xacNhanAdmin   = 1; // cả 2 bước confirm và shipping_done đều xem như đã xác nhận

switch ($action) {
    case 'confirm':
        // Bước 1: từ Chờ xử lý / Pending / Chờ thanh toán → Đang vận chuyển
        $newStatus = 'Đang vận chuyển';
        break;

    case 'shipping_done':
        // Bước 2: từ Đang vận chuyển → Đã giao hàng (hoàn tất)
        $newStatus = 'Đã giao hàng';
        break;

    default:
        // Action không hợp lệ → quay về
        header('Location: donhang.php');
        exit();
}

try {
    $stmt = $conn->prepare("
        UPDATE donhang
        SET trang_thai = ?, xac_nhan_admin = ?
        WHERE id_donhang = ?
    ");
    $stmt->execute([$newStatus, $xacNhanAdmin, $id_donhang]);

} catch (PDOException $e) {
    // Nếu muốn debug tạm thời thì echo ra:
    // echo 'Lỗi: ' . $e->getMessage();
    // exit;
}

// Quay lại trang danh sách đơn
header('Location: donhang.php');
exit();
