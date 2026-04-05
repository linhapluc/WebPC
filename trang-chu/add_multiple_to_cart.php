<?php
// add_multiple_to_cart.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Vui lòng đăng nhập để thêm vào giỏ hàng.';
    echo json_encode($response);
    exit;
}

// Lấy dữ liệu JSON từ request body
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); 

if (isset($input['items']) && is_array($input['items']) && !empty($input['items'])) {
    require 'includes/connect.php'; 
    $all_added_successfully = true;
    $error_messages = [];

    foreach ($input['items'] as $item) {
        if (isset($item['product_id']) && isset($item['quantity'])) {
            $productId_str = trim($item['product_id']);
            $quantityToAdd = (int)$item['quantity'];

            if (strpos($productId_str, 'SP') === 0 && strlen($productId_str) > 2 && $quantityToAdd > 0) {
                try {
                    $sql_check_stock = "SELECT sl FROM sanpham WHERE id_sanpham = :id_sp LIMIT 1";
                    $stmt_check_stock = $conn->prepare($sql_check_stock);
                    $stmt_check_stock->bindParam(':id_sp', $productId_str, PDO::PARAM_STR);
                    $stmt_check_stock->execute();
                    $product_info = $stmt_check_stock->fetch(PDO::FETCH_ASSOC);

                    if ($product_info) {
                        $product_stock = (int)$product_info['sl'];
                        $current_quantity_in_cart = isset($_SESSION['cart'][$productId_str]) ? (int)$_SESSION['cart'][$productId_str] : 0;
                        $total_requested_quantity = $current_quantity_in_cart + $quantityToAdd;

                        if ($total_requested_quantity <= $product_stock) {
                            $_SESSION['cart'][$productId_str] = $total_requested_quantity;
                        } else {
                            $all_added_successfully = false;
                            $error_messages[] = "Sản phẩm ID {$productId_str} không đủ số lượng.";
                        }
                    } else {
                        $all_added_successfully = false;
                        $error_messages[] = "Sản phẩm ID {$productId_str} không tồn tại.";
                    }
                } catch (PDOException $e) {
                    $all_added_successfully = false;
                    $error_messages[] = "Lỗi CSDL khi xử lý sản phẩm ID {$productId_str}.";
                    error_log("Lỗi PDO trong add_multiple_to_cart.php: " . $e->getMessage());
                }
            } else {
                $all_added_successfully = false;
                $error_messages[] = "Dữ liệu không hợp lệ cho sản phẩm ID {$productId_str}.";
            }
        }
    }

    if ($conn) $conn = null;

    if ($all_added_successfully) {
        $response['success'] = true;
        $response['message'] = 'Đã thêm cấu hình vào giỏ hàng!';
    } else {
        $response['message'] = 'Một số sản phẩm không thể thêm vào giỏ: ' . implode(', ', $error_messages);
    }

} else {
    $response['message'] = 'Không có sản phẩm nào được chọn để thêm vào giỏ.';
}

echo json_encode($response);
exit;
?>