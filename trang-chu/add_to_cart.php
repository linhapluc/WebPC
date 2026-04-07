<?php
// add_to_cart.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_message'] = "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.";
    if (isset($_SERVER['HTTP_REFERER'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'];
    } else {
        $_SESSION['redirect_after_login'] = 'index.php';
    }
    header('Location: dangnhap.php');
    exit;
}

// Nếu đã đăng nhập, tiếp tục xử lý
require 'includes/connect.php'; 

$message = "";
$message_type = ""; // "


$request_data = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'])) {
    $request_data = $_GET;
}

if (isset($request_data['product_id']) && !empty(trim($request_data['product_id'])) && 
    isset($request_data['quantity']) && is_numeric($request_data['quantity'])) {

    $productId_str = trim($request_data['product_id']);
    $quantityToAdd = (int)$request_data['quantity'];

    // Validate ID sản phẩm và số lượng
    if (strpos($productId_str, 'SP') === 0 && strlen($productId_str) > 2 && $quantityToAdd > 0) {
        try {
            // 1. Lấy thông tin sản phẩm (tên và số lượng tồn kho) từ CSDL
            // CSDL : id_sanpham, ten_sp, sl
            $sql_check_stock = "SELECT id_sanpham, ten_sp, sl FROM sanpham WHERE id_sanpham = :id_sp LIMIT 1";
            /** @var PDO $conn */
            $stmt_check_stock = $conn->prepare($sql_check_stock);
            $stmt_check_stock->bindParam(':id_sp', $productId_str, PDO::PARAM_STR);
            $stmt_check_stock->execute();
            $product_info = $stmt_check_stock->fetch(PDO::FETCH_ASSOC);

            if ($product_info) {
                $product_stock = (int)$product_info['sl'];
                $product_name = $product_info['ten_sp'];

                // 2. Tính toán số lượng hiện có trong giỏ hàng
                $current_quantity_in_cart = 0;
                if (isset($_SESSION['cart'][$productId_str])) { 
                    $current_quantity_in_cart = (int)$_SESSION['cart'][$productId_str];
                }

                $total_requested_quantity = $current_quantity_in_cart + $quantityToAdd;

                // 3. Kiểm tra tồn kho
                if ($total_requested_quantity <= $product_stock) {
                    if (isset($_SESSION['cart'][$productId_str])) {
                        $_SESSION['cart'][$productId_str] += $quantityToAdd;
                    } else {
                        $_SESSION['cart'][$productId_str] = $quantityToAdd;
                    }
                    $message = "Đã thêm " . $quantityToAdd . " sản phẩm \"" . htmlspecialchars($product_name) . "\" vào giỏ hàng!";
                    $message_type = "success";
                } else {
                    $can_add_more = $product_stock - $current_quantity_in_cart;
                    if ($product_stock == 0) {
                         $message = "Sản phẩm \"" . htmlspecialchars($product_name) . "\" hiện đã hết hàng.";
                    } elseif ($can_add_more > 0) {
                        $message = "Sản phẩm \"" . htmlspecialchars($product_name) . "\" không đủ số lượng. Bạn chỉ có thể thêm tối đa " . $can_add_more . " sản phẩm nữa (tồn kho " . $product_stock . ", trong giỏ " . $current_quantity_in_cart . ").";
                    } else {
                        $message = "Số lượng sản phẩm \"" . htmlspecialchars($product_name) . "\" trong giỏ đã đạt mức tối đa (" . $product_stock . ").";
                    }
                    $message_type = "error";
                }
            } else {
                $message = "Sản phẩm không tồn tại!";
                $message_type = "error";
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi thêm vào giỏ (add_to_cart.php): " . $e->getMessage());
            $message = "Có lỗi máy chủ khi xử lý giỏ hàng, vui lòng thử lại!";
            $message_type = "error";
        }
    } else {
         $message = "ID sản phẩm hoặc số lượng yêu cầu không hợp lệ!";
         $message_type = "error";
    }
} else {
     $message = "Thiếu thông tin sản phẩm để thêm vào giỏ!";
     $message_type = "error";
}

// Lưu thông báo vào session
if (!empty($message)) {
    if ($message_type === "success") {
        $_SESSION['cart_message_success'] = $message;
    } else {
        $_SESSION['cart_message_error'] = $message;
    }
}

// Xử lý chuyển hướng cho nút "Mua ngay"
if (isset($request_data['buy_now_flag']) && $request_data['buy_now_flag'] == '1' && $message_type === "success") {
    // Nếu là mua ngay và thêm thành công, chuyển đến trang thanh toán
    header('Location: checkout.php');
    exit;
}

// Chuyển hướng về trang trước đó
$previousPage = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $previousPage);
exit;
?>