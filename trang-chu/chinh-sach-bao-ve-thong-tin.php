<?php
// chinh-sach-bao-ve-thong-tin.php 

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

$pageSpecificTitle = "Chính sách Bảo vệ Thông tin Cá nhân - PC Shop Nasa";
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
                <li class="breadcrumb-item active" aria-current="page">Chính sách Bảo vệ Thông tin Cá nhân</li>
            </ol>
        </nav>

        <h1 class="page-title" style="font-size: 2em; color: #333; margin-bottom: 20px; text-align:center;">CHÍNH SÁCH BẢO VỆ THÔNG TIN CÁ NHÂN</h1>
        
        <div class="policy-content" style="font-size: 1.05em; line-height: 1.7;">

            <p style="margin-bottom: 20px;">PC Shop Nasa cam kết bảo vệ thông tin cá nhân của Quý khách hàng. Chính sách này mô tả cách chúng tôi thu thập, sử dụng, lưu trữ và bảo vệ thông tin cá nhân của bạn khi bạn truy cập và sử dụng website <strong>pcshopnasa.com</strong> (hoặc tên miền website của bạn).</p>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">1. Mục đích và Phạm vi thu thập thông tin</h2>
                <p>Chúng tôi thu thập thông tin cá nhân của Quý khách nhằm các mục đích sau:</p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Xác nhận và xử lý đơn đặt hàng, bao gồm việc giao hàng và thanh toán.</li>
                    <li style="margin-bottom: 8px;">Cung cấp các dịch vụ hỗ trợ khách hàng, giải đáp thắc mắc và xử lý khiếu nại.</li>
                    <li style="margin-bottom: 8px;">Cải thiện chất lượng sản phẩm, dịch vụ và trải nghiệm người dùng trên website.</li>
                    <li style="margin-bottom: 8px;">Gửi thông tin về các chương trình khuyến mãi, sản phẩm mới, sự kiện (nếu Quý khách đăng ký nhận tin).</li>
                    <li style="margin-bottom: 8px;">Thực hiện các nghĩa vụ pháp lý theo quy định.</li>
                </ul>
                <p style="margin-top: 10px;">Phạm vi thông tin thu thập có thể bao gồm (nhưng không giới hạn):</p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Thông tin cá nhân: Họ và tên, địa chỉ email, số điện thoại.</li>
                    <li style="margin-bottom: 8px;">Thông tin giao hàng: Địa chỉ nhận hàng.</li>
                    <li style="margin-bottom: 8px;">Thông tin tài khoản: Tên đăng nhập, mật khẩu (được mã hóa).</li>
                    <li style="margin-bottom: 8px;">Thông tin giao dịch: Lịch sử mua hàng, chi tiết đơn hàng.</li>
                    <li style="margin-bottom: 8px;">Thông tin kỹ thuật: Địa chỉ IP, loại trình duyệt, thông tin thiết bị (trong trường hợp cần thiết để hỗ trợ kỹ thuật hoặc phân tích website).</li>
                </ul>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">2. Phạm vi sử dụng thông tin</h2>
                <p>Thông tin cá nhân của Quý khách được sử dụng cho các mục đích đã nêu ở Mục 1 và chỉ được chia sẻ trong các trường hợp sau:</p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Cho các bộ phận nội bộ của PC Shop Nasa để xử lý đơn hàng và hỗ trợ khách hàng.</li>
                    <li style="margin-bottom: 8px;">Cho các đối tác vận chuyển để thực hiện việc giao hàng. Chúng tôi chỉ cung cấp thông tin cần thiết cho việc giao nhận.</li>
                    <li style="margin-bottom: 8px;">Cho các đối tác cổng thanh toán (nếu có) để xử lý giao dịch thanh toán trực tuyến.</li>
                    <li style="margin-bottom: 8px;">Khi có yêu cầu từ các cơ quan nhà nước có thẩm quyền theo quy định của pháp luật.</li>
                </ul>
                <p>PC Shop Nasa cam kết không bán, cho thuê hoặc chia sẻ thông tin cá nhân của Quý khách cho bất kỳ bên thứ ba nào khác vì mục đích thương mại mà không có sự đồng ý của Quý khách.</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">3. Thời gian lưu trữ thông tin</h2>
                <p>Dữ liệu cá nhân của Quý khách sẽ được lưu trữ cho đến khi có yêu cầu hủy bỏ từ Quý khách hoặc khi tài khoản không còn hoạt động trong một khoảng thời gian nhất định theo quy định nội bộ của chúng tôi. Trong mọi trường hợp, thông tin cá nhân của Quý khách sẽ được bảo mật trên hệ thống máy chủ của PC Shop Nasa.</p>
                <p>Đối với các thông tin liên quan đến đơn hàng và giao dịch, chúng tôi có thể lưu trữ theo quy định của pháp luật về kế toán, thuế.</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">4. Đơn vị thu thập và quản lý thông tin cá nhân</h2>
                <ul style="list-style-type: none; padding-left: 0;">
                    <li style="margin-bottom: 8px;"><strong>Tên đơn vị:</strong> CÔNG TY TNHH PC SHOP NASA</li>
                    <li style="margin-bottom: 8px;"><strong>Mã số thuế/Giấy phép kinh doanh:</strong> [Mã số thuế/GPKD của bạn]</li>
                    <li style="margin-bottom: 8px;"><strong>Địa chỉ trụ sở:</strong> [Địa chỉ công ty của bạn] (Ví dụ: Khu CNC, Q.9, TP.HCM)</li>
                    <li style="margin-bottom: 8px;"><strong>Email liên hệ về bảo mật thông tin:</strong> pcshopNasa@gmail.com</li>
                    <li style="margin-bottom: 8px;"><strong>Điện thoại:</strong> 1900 1155</li>
                </ul>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">5. Phương tiện và công cụ để người dùng tiếp cận và chỉnh sửa dữ liệu cá nhân của mình</h2>
                <p>Quý khách có quyền yêu cầu truy cập, xem, chỉnh sửa hoặc yêu cầu xóa dữ liệu cá nhân của mình bằng cách:</p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Đăng nhập vào tài khoản cá nhân trên website <strong>pcshopnasa.com</strong> (nếu có chức năng "Thông tin tài khoản") và tự cập nhật thông tin.</li>
                    <li style="margin-bottom: 8px;">Liên hệ trực tiếp với bộ phận Chăm sóc khách hàng của chúng tôi qua các kênh đã được cung cấp (Email, Hotline) để yêu cầu hỗ trợ.</li>
                </ul>
                <p>Chúng tôi sẽ xác minh danh tính và hỗ trợ Quý khách cập nhật hoặc xử lý yêu cầu trong thời gian sớm nhất, phù hợp với quy định của pháp luật.</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">6. Cam kết bảo mật thông tin cá nhân khách hàng</h2>
                <p>PC Shop Nasa cam kết bảo vệ an toàn thông tin cá nhân của Quý khách bằng các biện pháp kỹ thuật và tổ chức phù hợp:</p>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Mật khẩu của Quý khách được mã hóa để đảm bảo an toàn.</li>
                    <li style="margin-bottom: 8px;">Chúng tôi áp dụng các biện pháp bảo mật vật lý và điện tử để bảo vệ cơ sở dữ liệu chứa thông tin cá nhân khỏi sự truy cập trái phép.</li>
                    <li style="margin-bottom: 8px;">Giới hạn quyền truy cập thông tin cá nhân cho các nhân viên và đối tác cần thiết để thực hiện công việc của họ, và yêu cầu họ tuân thủ các quy định bảo mật.</li>
                    <li style="margin-bottom: 8px;">Trong trường hợp máy chủ lưu trữ thông tin bị tấn công bởi hacker dẫn đến mất mát dữ liệu cá nhân khách hàng, PC Shop Nasa sẽ có trách nhiệm thông báo vụ việc cho cơ quan chức năng điều tra xử lý kịp thời và thông báo cho khách hàng được biết.</li>
                </ul>
                <p>Tuy nhiên, không có biện pháp truyền tải dữ liệu qua Internet hoặc lưu trữ điện tử nào là an toàn tuyệt đối 100%. Do đó, mặc dù chúng tôi cố gắng hết sức để bảo vệ thông tin cá nhân của bạn, chúng tôi không thể đảm bảo an ninh tuyệt đối.</p>
            </section>

            <section>
                <h2 style="font-size: 1.5em; color: #E53935; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #E53935;">7. Thay đổi chính sách</h2>
                <p>Chính sách bảo vệ thông tin cá nhân này có thể được cập nhật định kỳ để phù hợp với các thay đổi trong hoạt động kinh doanh của chúng tôi hoặc các quy định của pháp luật. Mọi thay đổi sẽ được đăng tải trên website này và có hiệu lực ngay khi đăng. Chúng tôi khuyến khích Quý khách thường xuyên xem lại chính sách này để cập nhật thông tin.</p>
            </section>
            
            <p style="margin-top: 40px; text-align: center; font-style: italic;">
                Nếu có bất kỳ câu hỏi nào liên quan đến chính sách này, vui lòng liên hệ với chúng tôi. Xin cảm ơn!
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