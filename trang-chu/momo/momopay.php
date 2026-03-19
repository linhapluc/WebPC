<?php
// trang-chu/momo/momopay.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Đảm bảo có thông tin đơn hàng được truyền qua
if (!isset($_SESSION['order_id_for_momo']) || !isset($_SESSION['total_amount_for_momo'])) {
    die("Lỗi: Thiếu thông tin đơn hàng để tạo thanh toán MoMo.");
}

$amount = $_SESSION['total_amount_for_momo'];
$order_id = $_SESSION['order_id_for_momo'];

unset($_SESSION['order_id_for_momo']);
unset($_SESSION['total_amount_for_momo']);

// --- Bắt đầu logic từ code của bạn ---
function execPostRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

// Thay thế bằng thông tin thật của bạn nếu có
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

$orderInfo = "Thanh toan don hang PC Shop Nasa - Ma: " . $order_id;
$redirectUrl = "http://localhost/WebPC/trang-chu/momo_return.php"; // URL MoMo sẽ trả về
$ipnUrl = "http://localhost/WebPC/trang-chu/momo_ipn.php"; // URL MoMo sẽ gửi IPN
$extraData = "";

$requestId = time() . "";
$requestType = "captureWallet";

$rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $order_id . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
$signature = hash_hmac("sha256", $rawHash, $secretKey);

$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "PC Shop Nasa",
    "storeId" => "PCShopNasa",
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $order_id,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
);

$result = execPostRequest($endpoint, json_encode($data));
$jsonResult = json_decode($result, true);

if (isset($jsonResult['payUrl'])) {
    header('Location: ' . $jsonResult['payUrl']);
    exit();
} else {
    // Xử lý lỗi nếu không nhận được payUrl
    echo "Có lỗi xảy ra khi tạo thanh toán MoMo. Vui lòng thử lại.";
    // In ra lỗi để debug
    print_r($jsonResult);
}
?>