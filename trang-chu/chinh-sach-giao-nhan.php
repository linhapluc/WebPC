<?php
// chinh-sach-giao-nhan.php

include 'includes/header.php';
require 'includes/connect.php'; // Mặc dù trang tĩnh, có thể vẫn cần cho các biến header

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

$pageSpecificTitle = "Chính sách Giao, Nhận Hàng và Kiểm Hàng - PC Shop Nasa";
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
                <li class="breadcrumb-item active" aria-current="page">Chính sách Giao, Nhận Hàng và Kiểm Hàng</li>
            </ol>
        </nav>

        <h1 class="page-title" style="font-size: 2em; color: #333; margin-bottom: 20px; text-align:center;">CHÍNH SÁCH GIAO, NHẬN HÀNG VÀ KIỂM HÀNG</h1>
        
        <div class="policy-content" style="font-size: 1.05em; line-height: 1.7;">

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">1. Phạm vi áp dụng</h2>
                <p>Chính sách này áp dụng cho tất cả các đơn hàng được đặt mua tại website <strong>PC Shop Nasa</strong> và được giao hàng trên toàn quốc.</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">2. Thời gian giao - nhận hàng</h2>
                <ul>
                    <li style="margin-bottom: 10px;">Sau khi tiếp nhận và xử lý đơn hàng, chúng tôi sẽ tiến hành giao hàng trong vòng <strong>24 giờ</strong> (đối với khu vực nội thành TP.HCM) hoặc theo lịch đã thỏa thuận với khách hàng.</li>
                    <li style="margin-bottom: 10px;">Đối với khách hàng ở các tỉnh xa, thời gian nhận hàng dự kiến từ <strong>3 - 5 ngày</strong> làm việc (không tính Thứ 7, Chủ Nhật và các ngày lễ, Tết). Tuy nhiên, thời gian này có thể thay đổi tùy thuộc vào đơn vị vận chuyển và điều kiện giao hàng tại từng địa phương.</li>
                    <li style="margin-bottom: 10px;">Thời gian giao hàng được tính từ lúc PC Shop Nasa hoàn tất thủ tục xác nhận đơn hàng với quý khách cho đến khi đơn vị vận chuyển giao hàng thành công.</li>
                    <li style="margin-bottom: 10px;">PC Shop Nasa sẽ thông báo cụ thể cho quý khách về thời gian giao hàng dự kiến sau khi đơn hàng được xác nhận.</li>
                </ul>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">3. Hình thức giao hàng</h2>
                <ul>
                    <li style="margin-bottom: 10px;"><strong>Giao hàng trực tiếp:</strong> Áp dụng cho các đơn hàng trong phạm vi nội thành TP.HCM (tùy theo chính sách cụ thể tại thời điểm đặt hàng).</li>
                    <li style="margin-bottom: 10px;"><strong>Giao hàng qua đơn vị vận chuyển:</strong> Đối với các đơn hàng ở ngoại thành TP.HCM và các tỉnh thành khác, PC Shop Nasa sẽ sử dụng các đối tác vận chuyển uy tín để giao hàng đến địa chỉ quý khách cung cấp.</li>
                </ul>
                <p><strong>Trách nhiệm các bên trong quá trình giao nhận hàng hóa:</strong></p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;"><strong>PC Shop Nasa:</strong>
                        <ul style="list-style-type: circle; padding-left: 20px; margin-top: 5px;">
                            <li>Đảm bảo hàng hóa được đóng gói cẩn thận, đúng quy cách và phù hợp với tiêu chuẩn vận chuyển.</li>
                            <li>Cung cấp đầy đủ thông tin cần thiết cho đơn vị vận chuyển (tên người nhận, địa chỉ, số điện thoại).</li>
                            <li>Thông báo cho khách hàng về tình trạng đơn hàng và thời gian giao hàng dự kiến.</li>
                        </ul>
                    </li>
                    <li style="margin-bottom: 8px;"><strong>Đơn vị vận chuyển:</strong>
                        <ul style="list-style-type: circle; padding-left: 20px; margin-top: 5px;">
                            <li>Chịu trách nhiệm về sự an toàn của hàng hóa trong suốt quá trình vận chuyển.</li>
                            <li>Giao hàng đúng địa chỉ, đúng người nhận và trong thời gian đã cam kết (hoặc thông báo nếu có sự thay đổi).</li>
                        </ul>
                    </li>
                    <li style="margin-bottom: 8px;"><strong>Khách hàng:</strong>
                        <ul style="list-style-type: circle; padding-left: 20px; margin-top: 5px;">
                            <li>Cung cấp thông tin nhận hàng (tên, địa chỉ, số điện thoại) chính xác và đầy đủ.</li>
                            <li>Có mặt tại địa chỉ nhận hàng theo thời gian đã hẹn hoặc ủy quyền cho người khác nhận hàng (cần thông báo trước cho PC Shop Nasa).</li>
                            <li>Kiểm tra hàng hóa cẩn thận khi nhận hàng (xem mục 4).</li>
                        </ul>
                    </li>
                </ul>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">4. Chính sách kiểm hàng</h2>
                <p>PC Shop Nasa khuyến khích quý khách hàng thực hiện đồng kiểm tra sản phẩm cùng nhân viên giao hàng ngay tại thời điểm nhận hàng. Việc kiểm tra bao gồm:</p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Kiểm tra tình trạng bên ngoài của kiện hàng: còn nguyên vẹn, không bị móp méo, rách, ẩm ướt.</li>
                    <li style="margin-bottom: 8px;">Mở kiện hàng và kiểm tra sản phẩm bên trong:
                        <ul style="list-style-type: circle; padding-left: 20px; margin-top: 5px;">
                            <li>Đúng sản phẩm đã đặt (tên, mã sản phẩm, màu sắc, cấu hình).</li>
                            <li>Đủ số lượng, đủ phụ kiện đi kèm (nếu có).</li>
                            <li>Sản phẩm còn nguyên tem niêm phong (nếu có), không có dấu hiệu đã qua sử dụng (trừ hàng trưng bày có thông báo trước).</li>
                            <li>Sản phẩm không bị trầy xước, nứt vỡ, móp méo do va đập trong quá trình vận chuyển.</li>
                        </ul>
                    </li>
                    <li style="margin-bottom: 8px;">Nếu phát hiện bất kỳ vấn đề nào (hàng sai, hàng lỗi, hàng bị hư hỏng do vận chuyển), quý khách vui lòng từ chối nhận hàng và/hoặc liên hệ ngay với bộ phận Chăm sóc khách hàng của PC Shop Nasa qua hotline <strong>1900 1155</strong> để được hỗ trợ kịp thời.</li>
                    <li style="margin-bottom: 8px;">Trường hợp quý khách không thực hiện đồng kiểm hoặc đã ký nhận hàng mà không có ghi chú về tình trạng hàng hóa, PC Shop Nasa có thể sẽ không giải quyết các khiếu nại về hình thức sản phẩm (trầy xước, móp méo, thiếu phụ kiện...) sau đó.</li>
                </ul>
            </section>

            <section>
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">Hướng dẫn xử lý khiếu nại/đổi hàng</h2>
                <p>Nếu quý khách có bất kỳ thắc mắc hoặc khiếu nại nào liên quan đến quá trình giao nhận và kiểm hàng, vui lòng liên hệ với chúng tôi qua:</p>
                <ul>
                    <li><strong>Email:</strong> pcshopNasa@gmail.com</li>
                    <li><strong>Số điện thoại/Hotline:</strong> 1900 1155</li>
                </ul>
                <p>Chúng tôi cam kết sẽ tiếp nhận và xử lý mọi phản hồi của quý khách một cách nhanh chóng và hợp lý nhất.</p>
            </section>

             <p style="margin-top: 40px; text-align: center; font-style: italic;">
                PC Shop Nasa xin chân thành cảm ơn sự tin tưởng và ủng hộ của quý khách!
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