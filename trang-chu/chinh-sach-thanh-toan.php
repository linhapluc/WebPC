<?php
// chinh-sach-thanh-toan.php

include 'includes/header.php';
require 'includes/connect.php'; 

// ---- TÍNH TOÁN BIẾN CHO main_navigation.php ----
$totalQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId_cart => $quantity_cart) {
        if (is_numeric($quantity_cart) && $quantity_cart > 0) {
            $totalQuantity += (int)$quantity_cart;
        }
    }
}
$cart_page_url = isset($_SESSION['user_id']) ? 'cart.php' : 'dangnhap.php';
// ---- KẾT THÚC TÍNH TOÁN BIẾN ----

$pageSpecificTitle = "Chính sách Thanh Toán - PC Shop Nasa";
?>
<script>document.title = <?php echo json_encode($pageSpecificTitle); ?>;</script>

<?php
include 'includes/main_navigation.php';
?>

<main class="static-page policy-page" style="padding: 30px 0;">
    <div class="container" style="background-color: #fff; padding: 30px 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <nav aria-label="breadcrumb" style="margin-bottom: 25px; font-size: 0.9em;">
            <ol class="breadcrumb" style="background-color: #f8f9fa; padding: 10px 15px; border-radius: 4px;">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chính sách Thanh Toán</li>
            </ol>
        </nav>

        <h1 class="page-title" style="font-size: 2em; color: #333; margin-bottom: 20px; text-align:center;">CHÍNH SÁCH THANH TOÁN</h1>
        
        <div class="policy-content" style="font-size: 1.05em; line-height: 1.7;">

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">1. Hình thức thanh toán</h2>
                <p>PC Shop Nasa cung cấp các phương thức thanh toán linh hoạt để khách hàng dễ dàng lựa chọn:</p>

                <h3 style="font-size: 1.2em; color: #333; margin-top: 20px; margin-bottom: 10px;">1.1. Thanh toán khi nhận hàng (COD - Cash on Delivery)</h3>
                <ul>
                    <li style="margin-bottom: 8px;">Khách hàng thanh toán tiền mặt trực tiếp cho nhân viên giao hàng khi nhận được sản phẩm.</li>
                    <li style="margin-bottom: 8px;">Phương thức này áp dụng cho tất cả các đơn hàng trên toàn quốc (có thể có giới hạn về giá trị đơn hàng tùy theo chính sách vận chuyển tại thời điểm đó).</li>
                    <li style="margin-bottom: 8px;">Quý khách vui lòng kiểm tra kỹ sản phẩm và các chứng từ liên quan (phiếu giao hàng, hóa đơn nếu có) trước khi thanh toán.</li>
                </ul>

                <h3 style="font-size: 1.2em; color: #333; margin-top: 20px; margin-bottom: 10px;">1.2. Thanh toán chuyển khoản ngân hàng</h3>
                <p>Khách hàng có thể chuyển khoản trực tiếp vào tài khoản công ty theo thông tin dưới đây:</p>
                <ul style="list-style-type: none; padding-left: 0;">
                    <li style="margin-bottom: 8px;"><strong>Tên tài khoản:</strong> CÔNG TY TNHH PC SHOP NASA</li>
                    <li style="margin-bottom: 8px;"><strong>Số tài khoản:</strong> [SỐ TÀI KHOẢN NGÂN HÀNG CỦA BẠN]</li>
                    <li style="margin-bottom: 8px;"><strong>Ngân hàng:</strong> [TÊN NGÂN HÀNG - CHI NHÁNH] (Ví dụ: Ngân hàng TMCP Á Châu - ACB, PGD TP.HCM)</li>
                    <li style="margin-bottom: 8px;"><strong>Nội dung chuyển khoản:</strong> [MÃ ĐƠN HÀNG] - [SỐ ĐIỆN THOẠI ĐẶT HÀNG] - [TÊN KHÁCH HÀNG] (Ví dụ: DH00123 - 0909123456 - Nguyen Van A)</li>
                </ul>
                <p><strong>Lưu ý đối với chuyển khoản:</strong></p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Quý khách vui lòng ghi rõ nội dung chuyển khoản theo cú pháp trên để chúng tôi dễ dàng xác nhận đơn hàng.</li>
                    <li style="margin-bottom: 8px;">Sau khi chuyển khoản thành công, quý khách vui lòng thông báo cho PC Shop Nasa qua hotline <strong>1900 1155</strong> hoặc email <strong>pcshopNasa@gmail.com</strong> để chúng tôi tiến hành xác nhận và xử lý đơn hàng nhanh chóng.</li>
                    <li style="margin-bottom: 8px;">Đơn hàng sẽ được xử lý sau khi chúng tôi nhận được thanh toán.</li>
                </ul>

                <h3 style="font-size: 1.2em; color: #333; margin-top: 20px; margin-bottom: 10px;">1.3. Thanh toán thẻ - Trả góp (Nếu có)</h3>
                <p>PC Shop Nasa có thể hỗ trợ các hình thức thanh toán thẻ và trả góp qua các cổng thanh toán hoặc đối tác tài chính. Vui lòng tham khảo thông tin chi tiết tại trang <a href="tragop.php" style="font-weight:bold;">Chính sách Trả góp</a> hoặc liên hệ trực tiếp với chúng tôi để được tư vấn.</p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Hỗ trợ thanh toán qua các loại thẻ ATM nội địa, thẻ tín dụng/ghi nợ quốc tế (Visa, Mastercard, JCB,...).</li>
                    <li style="margin-bottom: 8px;">Có thể hỗ trợ thanh toán qua ví điện tử hoặc quét mã QR (tùy theo tích hợp).</li>
                </ul>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">2. Lưu ý quan trọng</h2>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Tất cả các đơn hàng chỉ được xử lý sau khi PC Shop Nasa xác nhận đã nhận được thanh toán đầy đủ (trừ trường hợp thanh toán COD).</li>
                    <li style="margin-bottom: 8px;">PC Shop Nasa không chịu trách nhiệm đối với các trường hợp chuyển khoản sai thông tin tài khoản hoặc các lỗi phát sinh từ phía ngân hàng của quý khách.</li>
                    <li style="margin-bottom: 8px;">Nếu quý khách gặp bất kỳ khó khăn nào trong quá trình thanh toán, vui lòng liên hệ ngay với bộ phận hỗ trợ của chúng tôi để được trợ giúp:
                        <ul style="list-style-type: circle; padding-left: 20px; margin-top: 5px;">
                            <li>Email: <strong>pcshopNasa@gmail.com</strong></li>
                            <li>Hotline: <strong>1900 1155</strong></li>
                        </ul>
                    </li>
                </ul>
            </section>
            
            <p style="margin-top: 40px; text-align: center; font-style: italic;">
                PC Shop Nasa cam kết mang đến trải nghiệm mua sắm an toàn và tiện lợi. Xin chân thành cảm ơn quý khách!
            </p>
        </div>
    </div>

    <?php include 'chatbox.php'; ?>

    
</main>

<?php
if ($conn) {
    $conn = null;
}
include 'includes/footer.php';
?>