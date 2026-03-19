<?php
// admin/xacnhan_thanhtoan.php
require '../includes/connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_name'])) { header('Location: index.php'); exit(); }

if (isset($_GET['id_donhang'])) {
    $order_id = $_GET['id_donhang'];
    try {
        // Cập nhật trạng thái từ 'Chờ thanh toán' thành 'Đã thanh toán'
        $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 'Đã thanh toán' WHERE id_donhang = ? AND trang_thai = 'Chờ thanh toán'");
        $stmt->execute([$order_id]);
    } catch (PDOException $e) { /* Xử lý lỗi nếu cần */ }
}
header('Location: donhang.php'); // Quay lại trang danh sách đơn hàng
exit;
?>