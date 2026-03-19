<?php
// lien-he.php

include 'includes/header.php';
require 'includes/connect.php'; 

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

$pageSpecificTitle = "Liên Hệ - PC Shop Nasa";
?>
<script>document.title = <?php echo json_encode($pageSpecificTitle); ?>;</script>

<?php
include 'includes/main_navigation.php';
?>

<main class="static-page contact-page" style="padding: 30px 0;">
    <div class="container" style="background-color: #fff; padding: 30px 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <nav aria-label="breadcrumb" style="margin-bottom: 25px; font-size: 0.9em;">
            <ol class="breadcrumb" style="background-color: #f8f9fa; padding: 10px 15px; border-radius: 4px;">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Thông Tin Liên Hệ & Hỗ Trợ</li>
            </ol>
        </nav>

        <h1 class="page-title" style="font-size: 2em; color: #333; margin-bottom: 25px; text-align:center;">THÔNG TIN LIÊN HỆ & HỖ TRỢ KHÁCH HÀNG</h1>
        
        <div class="contact-info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            
            <div class="contact-column">
                <h3 style="font-size: 1.3em; color: #E53935; margin-bottom: 15px;"> <i class="fas fa-headset" style="margin-right: 8px;"></i> Tổng Đài Hỗ Trợ</h3>
                <p><strong>Hotline Chăm Sóc Khách Hàng & Khiếu Nại:</strong></p>
                <p style="font-size: 1.2em; font-weight: bold; color: #E53935; margin-bottom: 10px;"><a href="tel:19001155" style="color: #E53935;">1900 1155</a></p>
                <p><strong>Email Hỗ Trợ Chung:</strong></p>
                <p style="margin-bottom: 15px;"><a href="mailto:pcshopNasa@gmail.com">pcshopNasa@gmail.com</a></p>
                
                <p><strong>Thời gian làm việc:</strong></p>
                <p>Thứ 2 - Thứ 7: 8h00 - 21h00</p>
                <p style="margin-bottom: 10px;">Chủ nhật: 9h00 - 18h00</p>
            </div>

            <div class="contact-column">
                <h3 style="font-size: 1.3em; color: #E53935; margin-bottom: 15px;"><i class="fas fa-tools" style="margin-right: 8px;"></i>Hỗ Trợ Kỹ Thuật & Bảo Hành</h3>
                <p><strong>Trung tâm bảo hành:</strong></p>
                <p style="margin-bottom: 10px;">[Địa chỉ trung tâm bảo hành của bạn, ví dụ: Khu CNC, Q.9, TP.HCM]</p>
                <p><strong>Hotline Bảo Hành:</strong></p>
                <p style="margin-bottom: 10px;"><a href="tel:0908419628">0908419628</a> (Mr Bảo.)</p>
                <p>Vui lòng tham khảo chi tiết tại trang <a href="chinh-sach-bao-hanh.php" style="font-weight:bold;">Chính sách Bảo hành</a>.</p>
            </div>

            <div class="contact-column">
                <h3 style="font-size: 1.3em; color: #E53935; margin-bottom: 15px;"><i class="fas fa-shopping-cart" style="margin-right: 8px;"></i>Đặt Hàng & Tư Vấn Mua Hàng</h3>
                <p><strong>Tư vấn mua hàng trực tuyến:</strong></p>
                <p style="margin-bottom: 10px;"><a href="tel:0906 777 452">0906 777 452</a> Kinh doanh 1</p>
                <p style="margin-bottom: 10px;"><a href="tel:0906 591 368">0906 591 368</a> Kinh doanh 2</p>
                
                <p><strong>Tư vấn trả góp:</strong></p>
                <p style="margin-bottom: 10px;"><a href="tel:0902548967">0902548967</a></p>
                <p>Vui lòng tham khảo chi tiết tại trang <a href="tragop.php" style="font-weight:bold;">Chính sách Trả góp</a>.</p>
            </div>
            
            <div class="contact-column">
                <h3 style="font-size: 1.3em; color: #E53935; margin-bottom: 15px;"><i class="fas fa-building" style="margin-right: 8px;"></i>Thông Tin Cửa Hàng</h3>
                <p><strong>PC Shop Nasa</strong></p>
                <p><strong>Địa chỉ:</strong> Khu CNC, Q.9, TP.HCM</p>
                <p><strong>MST:</strong> 0123456789 </p>
                
            </div>
        </div>

        
        
    </div>
</main>

<?php
if ($conn) {
    $conn = null;
}
include 'includes/footer.php';
?>