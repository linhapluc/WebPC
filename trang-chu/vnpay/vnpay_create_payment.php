<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once("config.php");

if (!isset($_SESSION['order_id_for_vnpay']) || !isset($_SESSION['total_amount_for_vnpay'])) {
    die("Lỗi: Thiếu thông tin đơn hàng để tạo thanh toán VNPAY.");
}
$order_id = $_SESSION['order_id_for_vnpay'];
$amount = $_SESSION['total_amount_for_vnpay'];

// Dọn dẹp session
unset($_SESSION['order_id_for_vnpay']);
unset($_SESSION['total_amount_for_vnpay']);

$vnp_TxnRef = $order_id;
$vnp_OrderInfo = "Thanh toan don hang " . $order_id;
$vnp_OrderType = 'billpayment';
$vnp_Amount = $amount * 100;
$vnp_Locale = 'vn';
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

$inputData = ["vnp_Version" => "2.1.0", "vnp_TmnCode" => $vnp_TmnCode, "vnp_Amount" => $vnp_Amount, "vnp_Command" => "pay", "vnp_CreateDate" => date('YmdHis'), "vnp_CurrCode" => "VND", "vnp_IpAddr" => $vnp_IpAddr, "vnp_Locale" => $vnp_Locale, "vnp_OrderInfo" => $vnp_OrderInfo, "vnp_OrderType" => $vnp_OrderType, "vnp_ReturnUrl" => $vnp_Returnurl, "vnp_TxnRef" => $vnp_TxnRef];
ksort($inputData);
$query = ""; $hashdata = "";
foreach ($inputData as $key => $value) {
    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . rtrim($query, '&');
$vnpSecureHash = hash_hmac('sha512', ltrim($hashdata, '&'), $vnp_HashSecret);
$vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;

header('Location: ' . $vnp_Url);
die();
?>