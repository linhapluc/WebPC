<?php
// trang-chu/momo_pay.php
session_start();
require 'includes/connect.php';

// 1. Lấy thông tin đơn hàng từ Session (Đã được lưu từ process_order.php)
$orderId = $_SESSION['momo_order_id'] ?? null;
$amount_raw = $_SESSION['momo_amount'] ?? null;

if (!$orderId || !$amount_raw) {
    die("Lỗi: Không tìm thấy mã đơn hàng hoặc số tiền. Vui lòng quay lại giỏ hàng.");
}

// 2. LÀM SẠCH SỐ TIỀN: MoMo chỉ chấp nhận số nguyên (Ví dụ: 1200000)
// Loại bỏ tất cả dấu chấm, dấu phẩy, chữ 'đ', khoảng trắng...
$amount = preg_replace('/\D/', '', $amount_raw); 

// 3. Cấu hình thông số MoMo Sandbox (Môi trường test)
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

$orderInfo = "Thanh toan don hang PC Shop NASA #" . $orderId;
$redirectUrl = "http://localhost/WebPC/trang-chu/momo_return.php"; 
$ipnUrl = "http://localhost/WebPC/trang-chu/momo_return.php";
$extraData = "";
$requestId = time() . "";
$requestType = "captureWallet";

// 4. Tạo chữ ký bảo mật (Signature) - Thứ tự các trường này là BẮT BUỘC
$rawHash = "accessKey=" . $accessKey . 
           "&amount=" . $amount . 
           "&extraData=" . $extraData . 
           "&ipnUrl=" . $ipnUrl . 
           "&orderId=" . $orderId . 
           "&orderInfo=" . $orderInfo . 
           "&partnerCode=" . $partnerCode . 
           "&redirectUrl=" . $redirectUrl . 
           "&requestId=" . $requestId . 
           "&requestType=" . $requestType;

$signature = hash_hmac("sha256", $rawHash, $secretKey);

// 5. Chuẩn bị mảng dữ liệu gửi đi
$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "PC Shop NASA",
    "storeId" => "NasaStore",
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
);

// 6. Hàm gửi yêu cầu POST (Đã tối ưu cho Localhost/XAMPP)
function execPostRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ));
    
    // Chống Time out (30 giây)
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    // Bỏ qua kiểm tra SSL (Sửa lỗi HTTPS trên Localhost)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Ép dùng IPv4 để kết nối nhanh hơn
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        return "CURL_ERROR: " . curl_error($ch);
    }

    curl_close($ch);
    return $result;
}

// 7. Thực thi gửi yêu cầu sang MoMo
$result = execPostRequest($endpoint, json_encode($data));

// Kiểm tra nếu kết nối mạng thất bại
if (strpos($result, 'CURL_ERROR') !== false) {
    die("Lỗi kết nối máy chủ MoMo: " . $result . ". Hãy kiểm tra kết nối Internet của bạn.");
}

$jsonResult = json_decode($result, true);

// 8. Xử lý kết quả trả về
if ($jsonResult !== null && isset($jsonResult['payUrl'])) {
    // THÀNH CÔNG: Chuyển khách sang trang thanh toán của MoMo
    header('Location: ' . $jsonResult['payUrl']);
    exit();
} else {
    // THẤT BẠI: Hiển thị lỗi để debug
    echo "<h2>Lỗi khởi tạo thanh toán MoMo</h2>";
    if ($jsonResult === null) {
        echo "Không nhận được phản hồi hợp lệ. Kết quả thô: <pre>" . htmlspecialchars($result) . "</pre>";
    } else {
        echo "<b>Thông báo:</b> " . ($jsonResult['message'] ?? 'Lỗi không xác định') . "<br>";
        echo "<b>Mã lỗi (ResultCode):</b> " . ($jsonResult['resultCode'] ?? 'N/A') . "<br>";
        echo "<b>Chi tiết:</b> Số tiền gửi đi là: <code>" . $amount . "</code>, Mã đơn hàng: <code>" . $orderId . "</code>";
    }
    echo "<br><br><a href='index.php'>Quay về trang chủ</a>";
}
?>