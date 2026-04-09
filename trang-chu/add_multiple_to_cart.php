<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

$product_ids = json_decode($_POST['product_ids'] ?? '[]', true);

if (!empty($product_ids)) {
    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

    foreach ($product_ids as $id) {
        // Nếu sản phẩm đã có trong giỏ thì tăng số lượng, chưa có thì thêm mới = 1
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        } else {
            $_SESSION['cart'][$id] = 1;
        }
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Danh sách trống']);
}