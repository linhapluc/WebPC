<?php // vnpay/vnpay_create_payment.php
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once("config.php");

$order_id = $_POST['order_id'];
$amount = $_POST['amount'];

$vnp_TxnRef = $order_id;
$vnp_OrderInfo = "Thanh toan don hang " . $order_id;
$vnp_OrderType = 'billpayment';
$vnp_Amount = $amount * 100;
$vnp_Locale = 'vn';
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

$inputData = ["vnp_Version" => "2.1.0", "vnp_TmnCode" => $vnp_TmnCode, "vnp_Amount" => $vnp_Amount, "vnp_Command" => "pay", "vnp_CreateDate" => date('YmdHis'), "vnp_CurrCode" => "VND", "vnp_IpAddr" => $vnp_IpAddr, "vnp_Locale" => $vnp_Locale, "vnp_OrderInfo" => $vnp_OrderInfo, "vnp_OrderType" => $vnp_OrderType, "vnp_ReturnUrl" => $vnp_Returnurl, "vnp_TxnRef" => $vnp_TxnRef];

ksort($inputData);
$query = ""; $hashdata = ""; $i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) { $hashdata .= '&' . urlencode($key) . "=" . urlencode($value); } else { $hashdata .= urlencode($key) . "=" . urlencode($value); $i = 1; }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}
header('Location: ' . $vnp_Url); die();
?>