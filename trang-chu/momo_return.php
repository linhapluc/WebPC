<?php
session_start();
require 'includes/connect.php';

// MoMo trả về các tham số qua GET
$resultCode = $_GET['resultCode'] ?? -1;
$orderId = $_GET['orderId'] ?? '';
$amount = $_GET['amount'] ?? 0;
$message = $_GET['message'] ?? '';

if ($resultCode == 0) {
    // THANH TOÁN THÀNH CÔNG
    try {
        $conn->beginTransaction();

        // 1. Cập nhật trạng thái đơn hàng trong bảng donhang
        $sql_update = "UPDATE donhang SET trang_thai = 'Đã thanh toán', ghi_chu = CONCAT(ghi_chu, ' - GD MoMo thành công') WHERE id_donhang = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->execute([$orderId]);

        // 2. Lưu log vào bảng momos để đối soát
        $sql_log = "INSERT INTO momos (order_id, partner_code, amount, result_code, message, link_data) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->execute([$orderId, $_GET['partnerCode'], $amount, $resultCode, $message, json_encode($_GET)]);

        $conn->commit();
        
        // Chuyển đến trang thành công
        header('Location: order_success.php?order_id=' . $orderId . '&status=success');
    } catch (Exception $e) {
        $conn->rollBack();
        die("Lỗi cập nhật đơn hàng: " . $e->getMessage());
    }
} else {
    // THANH TOÁN THẤT BẠI HOẶC HỦY
    header('Location: order_success.php?order_id=' . $orderId . '&status=failed&msg=' . urlencode($message));
}