<?php
// chinh-sach-khieu-nai.php

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

$pageSpecificTitle = "Chính sách Giải quyết Khiếu nại - PC Shop Nasa";
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
                <li class="breadcrumb-item active" aria-current="page">Chính sách Giải quyết Khiếu nại</li>
            </ol>
        </nav>

        <h1 class="page-title" style="font-size: 2em; color: #333; margin-bottom: 20px; text-align:center;">CHÍNH SÁCH GIẢI QUYẾT KHIẾU NẠI</h1>
        
        <div class="policy-content" style="font-size: 1.05em; line-height: 1.7;">
            <p style="margin-bottom: 20px; font-style: italic; text-align: center;">
                PC Shop Nasa luôn có trách nhiệm tiếp nhận và xử lý các thắc mắc, khiếu nại của Khách hàng liên quan đến giao dịch tại <strong>pcshopnasa.com</strong> (hoặc tên miền website của bạn). Khi phát sinh các khiếu nại, tranh chấp, PC Shop Nasa đề cao giải pháp thương lượng, hòa giải giữa các bên nhằm duy trì mối quan hệ và sự tin cậy của Khách hàng vào chất lượng dịch vụ của chúng tôi.
            </p>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">1. Cơ chế giải quyết Thắc mắc - Khiếu nại</h2>
                <p style="margin-bottom: 10px;">Trong quá trình giao dịch, nếu có bất kỳ ý kiến thắc mắc nào về sản phẩm, dịch vụ, hay phát hiện thiếu sót trong nghiệp vụ, tác phong và tinh thần phục vụ, PC Shop Nasa rất mong nhận được phản hồi từ phía quý khách hàng.</p>
                <p>Sự đóng góp ý kiến của quý vị sẽ là động lực to lớn giúp chúng tôi có thể hoàn thiện các sản phẩm và dịch vụ tốt nhất, nâng cao trải nghiệm mua sắm của quý khách.</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">2. Chính sách xử lý Thắc mắc - Khiếu nại</h2>
                <p>Khách hàng sử dụng dịch vụ của PC Shop Nasa có thể gửi thắc mắc hoặc khiếu nại của mình bằng các cách sau đây:</p>
                <ul style="list-style-type: none; padding-left: 0;">
                    <li style="margin-bottom: 10px;">
                        <i class="fas fa-phone-alt" style="color: #E53935; margin-right: 8px;"></i>
                        <strong>Điện thoại:</strong> Bằng cách gọi điện tới số điện thoại Chăm sóc khách hàng <strong>1900 1155</strong> (Cước phí theo nhà mạng). Thời gian hỗ trợ: 8h00 - 21h00 từ Thứ 2 đến Chủ Nhật.
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fas fa-envelope" style="color: #E53935; margin-right: 8px;"></i>
                        <strong>Email:</strong> Gửi thư điện tử tới: <strong>pcshopNasa@gmail.com</strong>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fas fa-map-marker-alt" style="color: #E53935; margin-right: 8px;"></i>
                        <strong>Địa chỉ (Gửi thư hoặc đến trực tiếp):</strong> [ĐỊA CHỈ CỬA HÀNG/VĂN PHÒNG CỦA BẠN] (Ví dụ: Khu CNC, Q.9, TP.HCM).
                    </li>
                </ul>
                <p style="margin-top: 15px;"><strong>Quy trình tiếp nhận và xử lý:</strong></p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Mọi Thắc mắc - Khiếu nại của khách hàng sẽ được bộ phận Chăm sóc khách hàng của PC Shop Nasa ghi nhận.</li>
                    <li style="margin-bottom: 8px;">Thời gian phản hồi và giải quyết khiếu nại:
                        <ul style="list-style-type: circle; padding-left: 20px; margin-top: 5px;">
                            <li>Đối với các khiếu nại không phức tạp hoặc không cần xác minh thông tin từ nhiều bộ phận: Phản hồi trong vòng <strong>24 giờ làm việc</strong> kể từ khi tiếp nhận.</li>
                            <li>Đối với các khiếu nại phức tạp hơn, cần thời gian xác minh: Chúng tôi sẽ thông báo cho quý khách về thời gian dự kiến xử lý, nhưng không quá <strong>05 - 07 ngày làm việc</strong>.</li>
                        </ul>
                    </li>
                    <li style="margin-bottom: 8px;">Chúng tôi cam kết xử lý mọi khiếu nại một cách công bằng, khách quan và trên tinh thần hợp tác, đảm bảo quyền lợi chính đáng của khách hàng.</li>
                </ul>
            </section>

            <section style="margin-bottom: 30px;">
                 <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">3. Các trường hợp không thuộc phạm vi giải quyết khiếu nại</h2>
                 <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Khiếu nại không liên quan trực tiếp đến sản phẩm, dịch vụ do PC Shop Nasa cung cấp.</li>
                    <li style="margin-bottom: 8px;">Khiếu nại về các vấn đề đã được quy định rõ trong các chính sách khác (ví dụ: chính sách bảo hành, chính sách đổi trả) và khách hàng đã được thông báo.</li>
                    <li style="margin-bottom: 8px;">Các trường hợp bất khả kháng theo quy định của pháp luật.</li>
                 </ul>
            </section>

            <p style="margin-top: 20px;">Những ý kiến đánh giá và góp ý của quý khách sẽ vô cùng quý giá đối với chúng tôi nhằm không ngừng cải thiện chất lượng dịch vụ.</p>
            <p style="margin-top: 30px; text-align: center; font-weight: bold;">Xin chân thành cảm ơn!</p>
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