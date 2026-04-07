<?php
// Lấy thông tin từ URL (từ file process_order.php truyền sang)
$order_id = $_GET['order_id'] ?? 'DH_UNKNOWN';
$amount = $_GET['amount'] ?? 0;

// --- CẤU HÌNH THÔNG TIN NGÂN HÀNG THẬT CỦA BẠN ---
$bank_id = "MB"; // Mã ngân hàng (MB, VCB, ICB, v.v.)
$account_no = "0123456789"; // SỐ TÀI KHOẢN THẬT CỦA BẠN
$account_name = "TRAN THE LINH"; // TÊN CHỦ TÀI KHOẢN (KHÔNG DẤU)
$template = "compact"; // Kiểu QR: compact, qr_only, hoặc print

// Tạo nội dung chuyển khoản tự động
$description = "Thanh toan don hang " . $order_id;

// Tạo link API VietQR để tạo mã QR động
$vietqr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-{$template}.png?amount={$amount}&addInfo=" . urlencode($description) . "&accountName=" . urlencode($account_name);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán đơn hàng</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; padding: 50px; }
        .payment-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        .qr-code { width: 100%; max-width: 250px; margin: 20px 0; border: 1px solid #eee; padding: 10px; border-radius: 10px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .amount { color: #d32f2f; font-weight: bold; font-size: 1.2em; }
        .note { background: #fff3e0; padding: 10px; border-radius: 5px; color: #e65100; font-size: 0.9em; margin-top: 15px; }
        .btn-done { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<div class="payment-card">
    <h2 style="color: #333;">Quét mã thanh toán</h2>
    <p>Sử dụng App Ngân hàng bất kỳ để quét mã</p>
    
    <!-- Mã QR động từ API -->
    <img src="<?php echo $vietqr_url; ?>" alt="Mã QR Thanh Toán" class="qr-code">

    <div class="info-row">
        <span>Ngân hàng:</span>
        <strong><?php echo $bank_id; ?> Bank</strong>
    </div>
    <div class="info-row">
        <span>Chủ tài khoản:</span>
        <strong><?php echo $account_name; ?></strong>
    </div>
    <div class="info-row">
        <span>Số tài khoản:</span>
        <strong><?php echo $account_no; ?></strong>
    </div>
    <div class="info-row">
        <span>Số tiền:</span>
        <span class="amount"><?php echo number_format($amount); ?>đ</span>
    </div>

    <div class="note">
        <strong>Nội dung chuyển khoản:</strong><br>
        <?php echo $description; ?>
    </div>

    <p style="font-size: 0.8em; color: #666; margin-top: 15px;">
        <i>Lưu ý: Hệ thống sẽ tự động xác nhận sau khi nhận được tiền.</i>
    </p>

    <a href="index.php" class="btn-done">Hoàn tất & Quay về trang chủ</a>
</div>

</body>
</html>