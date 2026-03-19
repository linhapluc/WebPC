<?php
$order_id = $_GET['order_id'] ?? 'N/A';
$amount = $_GET['amount'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông Tin Chuyển Khoản</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: auto; padding: 30px; border-radius: 8px; background-color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #0d6efd; }
        .qr-code img { max-width: 250px; border: 1px solid #ddd; padding: 10px; border-radius: 8px; }
        .bank-details { list-style: none; padding: 0; text-align: left; max-width: 350px; margin: 20px auto; }
        .bank-details li { padding: 8px 0; border-bottom: 1px solid #eee; }
        .bank-details li span { font-weight: bold; }
        .note { color: #dc3545; font-weight: bold; margin-top: 15px; }
        a { color: #0d6efd; text-decoration: none; font-weight: bold; margin-top: 20px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quét Mã QR Để Thanh Toán</h1>
        <p>Vui lòng quét mã QR dưới đây bằng ứng dụng ngân hàng của bạn.</p>

        <div class="qr-code">
            <!-- === ĐÂY LÀ PHẦN SỬA LỖI ĐƯỜNG DẪN === -->
            <img src="../assets/images/qr_code_bank.png" alt="Mã QR thanh toán">
        </div>

        <ul class="bank-details">
            <!-- THAY THÔNG TIN CỦA BẠN VÀO ĐÂY -->
            <li>Ngân hàng: <span>MB Bank</span></li>
            <li>Chủ tài khoản: <span>NGUYEN VAN A</span></li>
            <li>Số tài khoản: <span>0123456789</span></li>
            <li>Số tiền: <span style="color: #dc3545;"><?php echo number_format($amount, 0, ',', '.'); ?>₫</span></li>
            <li>Nội dung: <span style="color: #dc3545;"><?php echo 'Thanh toan don hang ' . htmlspecialchars($order_id); ?></span></li>
        </ul>

        <p class="note">LƯU Ý: Vui lòng nhập chính xác nội dung chuyển khoản để đơn hàng được xử lý nhanh nhất.</p>

        <a href="index.php">Hoàn tất & Quay về trang chủ</a>
    </div>
</body>
</html>