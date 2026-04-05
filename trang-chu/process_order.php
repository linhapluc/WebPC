<?php
// process_order.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'includes/connect.php'; 

// 1. KIỂM TRA BAN ĐẦU
if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_error'] = "Vui lòng đăng nhập để đặt hàng."; 
    header('Location: dangnhap.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    $_SESSION['checkout_error'] = "Giỏ hàng của bạn đang trống. Không thể đặt hàng.";
    header('Location: cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 2. LẤY VÀ VALIDATE DỮ LIỆU TỪ FORM
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');
$customer_address = trim($_POST['customer_address'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? 'COD');
$order_notes = trim($_POST['order_notes'] ?? '');
$user_id_str = $_SESSION['user_id']; 

$errors = [];
if (empty($customer_name)) $errors[] = "Họ và tên không được để trống.";
if (empty($customer_email)) $errors[] = "Email không được để trống.";
elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Địa chỉ email không hợp lệ.";
if (empty($customer_address)) $errors[] = "Địa chỉ nhận hàng không được để trống.";
if (empty($customer_phone)) $errors[] = "Số điện thoại không được để trống.";


if (!empty($errors)) {
    $_SESSION['checkout_error'] = implode("<br>", $errors);
    header('Location: checkout.php');
    exit;
}

$cart_items_to_process = $_SESSION['cart'];

try {
    $conn->beginTransaction(); 

    // KIỂM TRA LẠI SỐ LƯỢNG TỒN KHO (dùng id_sanpham, ten_sp, sl)
    $product_ids_in_cart_str = array_keys($cart_items_to_process);
    $can_process_order = true;
    $error_stock_messages = [];
    $db_stock_map = []; 

    if (!empty($product_ids_in_cart_str)) {
        $placeholders_stock = implode(',', array_fill(0, count($product_ids_in_cart_str), '?'));
        // CSDL : id_sanpham, ten_sp, gia, sl
        $sql_check_stock = "SELECT id_sanpham, ten_sp, gia, sl FROM sanpham WHERE id_sanpham IN ($placeholders_stock) FOR UPDATE";
        $stmt_check_stock = $conn->prepare($sql_check_stock);
        $stmt_check_stock->execute($product_ids_in_cart_str);
        $products_from_db = $stmt_check_stock->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products_from_db as $p_db) {
            $db_stock_map[$p_db['id_sanpham']] = $p_db;
        }

        foreach ($cart_items_to_process as $pid_str => $qty_requested) {
            $qty_requested = (int)$qty_requested;

            if (!isset($db_stock_map[$pid_str])) {
                $can_process_order = false;
                $error_stock_messages[] = "Sản phẩm với ID " . htmlspecialchars($pid_str) . " không còn tồn tại.";
                continue;
            }

            $current_stock = (int)$db_stock_map[$pid_str]['sl'];
            $product_name_stock = $db_stock_map[$pid_str]['ten_sp'];

            if ($qty_requested <= 0) {
                 $can_process_order = false;
                 $error_stock_messages[] = "Số lượng không hợp lệ cho sản phẩm \"" . htmlspecialchars($product_name_stock) . "\".";
                 continue;
            }
            if ($qty_requested > $current_stock) {
                $can_process_order = false;
                $error_stock_messages[] = "Sản phẩm \"" . htmlspecialchars($product_name_stock) . "\" chỉ còn " . $current_stock . " (yêu cầu " . $qty_requested . ").";
            }
        }
    } else {
        $can_process_order = false;
        $error_stock_messages[] = "Giỏ hàng rỗng.";
    }

    if (!$can_process_order) {
        $conn->rollBack();
        $_SESSION['checkout_error'] = "Không thể xử lý đơn hàng:<br>" . implode("<br>", $error_stock_messages);
       
        header('Location: cart.php');
        exit;
    }

    // : TÍNH TOÁN TỔNG TIỀN VÀ CHUẨN BỊ CHI TIẾT ĐƠN HÀNG
    $order_items_for_db = [];
    $total_order_amount = 0;

    foreach ($cart_items_to_process as $pid_str => $qty) {
        $qty = (int)$qty;
        if (isset($db_stock_map[$pid_str]) && $qty > 0) {
            $product_info = $db_stock_map[$pid_str];
            $price_at_purchase = (int)$product_info['gia'];
            $item_subtotal = $price_at_purchase * $qty;
            $total_order_amount += $item_subtotal;

            $order_items_for_db[] = [
                'product_id_str' => $pid_str, 
                'quantity' => $qty,
                'price_at_purchase' => $price_at_purchase
            ];
        }
    }

    if ($total_order_amount <= 0 || empty($order_items_for_db)) {
        $conn->rollBack();
        $_SESSION['checkout_error'] = "Đơn hàng không hợp lệ hoặc tổng tiền bằng 0.";
        header('Location: cart.php');
        exit;
    }

    // : TẠO ĐƠN HÀNG MỚI (BẢNG `donhang`)
    // CSDL: id_donhang, id_khachhang, ngay_dat, tong_tien, ten_nguoinhan, diachi_giaohang, sdt_nguoinhan, ghi_chu, trang_thai, phuongthuc_thanhtoan, xac_nhan_admin
    $new_order_id_str = generate_new_id($conn, 'donhang', 'id_donhang', 'DH', 5); 

    $sql_insert_order = "INSERT INTO donhang (id_donhang, id_khachhang, ngay_dat, tong_tien, ten_nguoinhan, diachi_giaohang, sdt_nguoinhan, ghi_chu, trang_thai, phuongthuc_thanhtoan, xac_nhan_admin)
                         VALUES (:id_donhang, :id_khachhang, NOW(), :total_amount, :customer_name, :customer_address, :customer_phone, :order_notes, 'Chờ xử lý', :payment_method, 0)"; // xac_nhan_admin mặc định là 0
    $stmt_insert_order = $conn->prepare($sql_insert_order);
    $stmt_insert_order->bindParam(':id_donhang', $new_order_id_str, PDO::PARAM_STR);
    $stmt_insert_order->bindParam(':id_khachhang', $user_id_str, PDO::PARAM_STR);
    $stmt_insert_order->bindParam(':total_amount', $total_order_amount, PDO::PARAM_INT);
    $stmt_insert_order->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
    $stmt_insert_order->bindParam(':customer_address', $customer_address, PDO::PARAM_STR);
    $stmt_insert_order->bindParam(':customer_phone', $customer_phone, PDO::PARAM_STR);
    $stmt_insert_order->bindParam(':order_notes', $order_notes, PDO::PARAM_STR);
    $stmt_insert_order->bindParam(':payment_method', $payment_method, PDO::PARAM_STR);
    $stmt_insert_order->execute();
    

    //  LƯU CHI TIẾT ĐƠN HÀNG VÀ TRỪ KHO
    // CSDL : id_chitietdh, id_donhang, id_sanpham, so_luong_mua, gia_luc_mua
    $sql_insert_order_item = "INSERT INTO chitietdonhang (id_chitietdh, id_donhang, id_sanpham, so_luong_mua, gia_luc_mua) 
                              VALUES (:id_chitietdh, :id_donhang, :id_sanpham, :quantity, :price_at_purchase)";
    $stmt_insert_order_item = $conn->prepare($sql_insert_order_item);

    // CSDL : bảng sanpham có cột id_sanpham, sl
    $sql_update_stock = "UPDATE sanpham SET sl = sl - :qty_sold WHERE id_sanpham = :id_sp AND sl >= :qty_sold_check";
    $stmt_update_stock = $conn->prepare($sql_update_stock);

    foreach ($order_items_for_db as $item) {
        $new_order_detail_id_str = generate_new_id($conn, 'chitietdonhang', 'id_chitietdh', 'CTDH', 6); // Tạo ID chi tiết đơn hàng, ví dụ CTDH000001

        $stmt_insert_order_item->execute([
            ':id_chitietdh' => $new_order_detail_id_str,
            ':id_donhang' => $new_order_id_str,
            ':id_sanpham' => $item['product_id_str'],
            ':quantity' => $item['quantity'],
            ':price_at_purchase' => $item['price_at_purchase']
        ]);

        $update_params = [
            ':qty_sold' => $item['quantity'],
            ':id_sp' => $item['product_id_str'],
            ':qty_sold_check' => $item['quantity'] // Đảm bảo vẫn còn đủ hàng khi trừ
        ];
        $stmt_update_stock->execute($update_params);

        if ($stmt_update_stock->rowCount() == 0) {
            throw new PDOException("Không thể cập nhật kho cho sản phẩm ID: " . $item['product_id_str'] . ". Số lượng có thể đã thay đổi.");
        }
    }

    $conn->commit(); // HOÀN TẤT TRANSACTION

    unset($_SESSION['cart']); // Xóa giỏ hàng
    $_SESSION['order_success_message'] = "Đặt hàng thành công! Mã đơn hàng của bạn là " . htmlspecialchars($new_order_id_str) . ". Chúng tôi sẽ liên hệ với bạn sớm.";
    header('Location: order_success.php?order_id=' . urlencode($new_order_id_str)); // Truyền ID đơn hàng qua URL
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Lỗi PDO khi xử lý đơn hàng (process_order.php): " . $e->getMessage() . " --- SQLSTATE: " . $e->getCode());
    // Hiển thị mã lỗi SQLSTATE cho dễ debug hơn
    $_SESSION['checkout_error'] = "Có lỗi nghiêm trọng xảy ra trong quá trình đặt hàng. Vui lòng thử lại hoặc liên hệ hỗ trợ. Lỗi: " . htmlspecialchars($e->getCode());
    header('Location: checkout.php');
    exit;
}
?>