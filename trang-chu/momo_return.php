<?php
// trang-chu/momo_return.php

// Yêu cầu file connect.php nằm ngay trong cùng thư mục trang-chu
require 'connect.php'; 

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$message = '';
$order_id = $_GET['orderId'] ?? 'N/A';

// Chỉ cần dựa vào resultCode để xử lý
if (isset($_GET['resultCode']) && $_GET['resultCode'] == '0') {
    $message = "Thanh toán qua MoMo thành công. Cảm ơn quý khách!";
    try {
        // Cập nhật trạng thái đơn hàng
        $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 'Chờ xử lý' WHERE id_donhang = ? AND phuongthuc_thanhtoan = 'MOMO'");
        $stmt->execute([$order_id]);
    } catch (PDOException $e) {
        error_log("Lỗi cập nhật CSDL sau khi thanh toán MoMo: " . $e->getMessage());
        $message = "Thanh toán thành công nhưng có lỗi khi cập nhật đơn hàng.";
    }
} else {
    $message = "Giao dịch không thành công hoặc đã bị hủy. Vui lòng thử lại.";
}

// === SỬA LẠI ĐƯỜNG DẪN INCLUDE Ở ĐÂY ===
// Giả sử các file này nằm trong `trang-chu/includes/`
include 'includes/header.php';
include 'includes/main_navigation.php';
// =======================================

echo "<div class='container' style='text-align:center; padding: 50px 15px;'>
        <div style='max-width: 600px; margin: auto; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); background: #fff;'>
            <h1>Thông Báo Thanh Toán</h1>
            <p style='font-size: 1.1em;'>" . htmlspecialchars($message) . "</p>
            <p>Mã đơn hàng của bạn: <strong>" . htmlspecialchars($order_id) . "</strong></p>
            <a href='lichsu_muahang.php' style='margin-right: 15px;'>Xem lịch sử mua hàng</a>
            <a href='index.php'>Quay về trang chủ</a>
        </div>
      </div>";

// === SỬA LẠI ĐƯỜNG DẪN INCLUDE Ở ĐÂY ===
include 'includes/footer.php';
// =======================================
?>