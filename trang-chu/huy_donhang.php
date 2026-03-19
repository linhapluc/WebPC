<?php
// trang-chu/huy_donhang.php

require 'includes/connect.php'; // Đảm bảo file connect.php nằm trong includes
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Kiểm tra đăng nhập và có id_donhang không
if (!isset($_SESSION['user_id']) || !isset($_GET['id_donhang'])) {
    header('Location: dangnhap.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id_donhang'];

try {
    // 2. Lấy thông tin đơn hàng để kiểm tra
    $stmt_check = $conn->prepare("SELECT trang_thai, id_khachhang FROM donhang WHERE id_donhang = ?");
    $stmt_check->execute([$order_id]);
    $order = $stmt_check->fetch(PDO::FETCH_ASSOC);

    // 3. Kiểm tra xem đơn hàng có tồn tại, có thuộc về người dùng này và có được phép hủy không
    if ($order && $order['id_khachhang'] == $user_id && ($order['trang_thai'] == 'Chờ xử lý' || $order['trang_thai'] == 'Chờ thanh toán')) {
        
        // 4. Cập nhật trạng thái thành "Đã hủy"
        $stmt_update = $conn->prepare("UPDATE donhang SET trang_thai = 'Đã hủy' WHERE id_donhang = ?");
        $stmt_update->execute([$order_id]);
        
        $_SESSION['cancel_message'] = "Đã hủy thành công đơn hàng " . htmlspecialchars($order_id);

    } else {
        // Đơn hàng không hợp lệ để hủy
        $_SESSION['cancel_error'] = "Không thể hủy đơn hàng này hoặc đơn hàng không tồn tại.";
    }

} catch (PDOException $e) {
    $_SESSION['cancel_error'] = "Đã có lỗi xảy ra. Vui lòng thử lại.";
    error_log("Lỗi hủy đơn hàng: " . $e->getMessage());
}

// 5. Quay trở lại trang lịch sử mua hàng
header('Location: lichsu_muahang.php');
exit;
?>