<?php
// build_pc.php

include 'includes/header.php';
require 'includes/connect.php';

// ---- TÍNH TOÁN BIẾN CHO main_navigation.php ----
$totalQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity_in_cart) { 
        if (is_numeric($quantity_in_cart) && $quantity_in_cart > 0) {
            $totalQuantity += (int)$quantity_in_cart;
        }
    }
}
$cart_page_url = isset($_SESSION['user_id']) ? 'cart.php' : 'dangnhap.php';


$pageSpecificTitle = "Xây dựng cấu hình PC - PC Shop Nasa";

// Danh sách các loại linh kiện cần thiết và id_loai tương ứng
$components_to_build = [
    ['type' => 'cpu', 'name' => 'Vi xử lý (CPU)', 'id_loai' => 'LSP011'],
    ['type' => 'mainboard', 'name' => 'Bo mạch chủ (Mainboard)', 'id_loai' => 'LSP012'],
    ['type' => 'ram', 'name' => 'Bộ nhớ trong (RAM)', 'id_loai' => 'LSP013'],
    ['type' => 'ssd', 'name' => 'Ổ cứng SSD', 'id_loai' => 'LSP014'],
    // ['type' => 'hdd', 'name' => 'Ổ cứng HDD (Tùy chọn)', 'id_loai' => 'LSPXXX'], 
    ['type' => 'vga', 'name' => 'Card màn hình (VGA)', 'id_loai' => 'LSP010'],
    ['type' => 'psu', 'name' => 'Nguồn máy tính (PSU)', 'id_loai' => 'LSP015'], 
    ['type' => 'case', 'name' => 'Vỏ máy tính (Case)', 'id_loai' => 'LSP016'], 
    
];

?>
<script>document.title = <?php echo json_encode($pageSpecificTitle); ?>;</script>

<?php
include 'includes/main_navigation.php';
?>

<main class="build-pc-page" style="padding: 30px 0;">
    <div class="container">
        <h1>Xây dựng cấu hình PC của bạn</h1>
        <p style="margin-bottom:10px; color: #555;">Tự tay lựa chọn từng linh kiện để tạo nên bộ máy tính ưng ý.</p>
        <hr style="margin: 15px 0 30px 0;">

        <div class="build-pc-area" style="display: flex; flex-wrap: wrap; gap: 30px;">
            <div class="component-selection-area" style="flex: 3; min-width: 600px;">
                <table class="component-table" style="width: 100%; border-collapse: collapse; font-size: 0.95em;">
                    <thead style="background-color: #f0f0f0;">
                        <tr>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Linh kiện</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Lựa chọn của bạn</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Giá</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; foreach ($components_to_build as $component): ?>
                        <tr data-component-type="<?php echo htmlspecialchars($component['type']); ?>" 
                            data-id-loai="<?php echo htmlspecialchars($component['id_loai']); ?>"
                            data-component-name="<?php echo htmlspecialchars($component['name']); ?>"
                            style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px; font-weight: 600;"><?php echo $count++; ?>. <?php echo htmlspecialchars($component['name']); ?></td>
                            <td style="padding: 10px;" class="selected-component-name">
                                <span class="name-display">Chưa chọn</span>
                                <img src="" alt="" class="selected-component-image" style="max-width: 40px; max-height: 40px; vertical-align: middle; margin-left: 10px; display:none;">
                            </td>
                            <td style="padding: 10px; text-align: right; font-weight: 500;" class="selected-component-price">0₫</td>
                            <td style="padding: 10px; text-align: center;">
                                <button class="btn-choose-component" style="padding: 6px 12px; cursor:pointer; background-color: #E53935; color:white; border:none; border-radius:4px; font-size:0.9em;">
                                    <i class="fa-solid fa-plus"></i> Chọn
                                </button>
                                <button class="btn-remove-component" style="padding: 6px 12px; cursor:pointer; background-color: #6c757d; color:white; border:none; border-radius:4px; font-size:0.9em; display:none; margin-left:5px;">
                                    <i class="fa-solid fa-trash-can"></i> Xóa
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary-sidebar-area" style="flex: 1; min-width: 300px; background-color: #f8f9fa; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); height:fit-content;">
                <h4 style="margin-top:0; margin-bottom:15px; border-bottom:1px solid #ddd; padding-bottom:10px;">Tóm tắt cấu hình</h4>
                <div id="build-summary-list">
                    <p style="color:#777; text-align:center;"><em>Chọn linh kiện để xem tóm tắt.</em></p>
                </div>
                <hr style="margin:20px 0;">
                <div style="font-size: 1.3em; font-weight: bold; display:flex; justify-content:space-between; margin-bottom:20px;">
                    <span>Tổng cộng:</span>
                    <span id="build-total-price" style="color: #E53935;">0₫</span>
                </div>
                <button id="btn-add-build-to-cart" style="width: 100%; padding: 12px; background-color: #28a745; color:white; border:none; border-radius:4px; cursor:pointer; font-size: 1.1em; font-weight:bold;" disabled>
                    <i class="fa-solid fa-cart-plus"></i> Thêm tất cả vào giỏ
                </button>
                <p id="add-to-cart-message" style="font-size:0.85em; margin-top:10px; text-align:center; color:#777;">Vui lòng chọn ít nhất một linh kiện.</p>
            </div>
        </div>

        <!-- Modal/Popup để chọn sản phẩm -->
        <div id="product-selection-modal" class="build-pc-modal" style="display: none;">
            <div class="build-pc-modal-content">
                <span class="build-pc-close-modal">×</span>
                <h3 id="modal-title" style="margin-top:0; margin-bottom:20px;">Chọn sản phẩm</h3>
               
                <div class="modal-filters" style="margin-bottom:15px; display:flex; gap:10px;">
                    <input type="text" id="modal-search-keyword" placeholder="Tìm theo tên..." style="padding:8px; border:1px solid #ccc; border-radius:4px; flex-grow:1;">
                  
                </div>
                <div id="modal-product-list" style="max-height: 450px; overflow-y: auto;">
                    
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Thêm CSS cho modal vào cuối file hoặc trong style.css -->
<style>
.build-pc-modal {
    display: none; 
    position: fixed; 
    z-index: 1001; 
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; 
    background-color: rgba(0,0,0,0.5); 
    padding-top: 60px;
}
.build-pc-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 25px;
    border: 1px solid #ddd;
    width: 80%;
    max-width: 700px;
    border-radius: 8px;
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
.build-pc-close-modal {
    color: #aaa;
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.build-pc-close-modal:hover,
.build-pc-close-modal:focus {
    color: black;
    text-decoration: none;
}
#modal-product-list .product-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
    gap: 15px;
}
#modal-product-list .product-item:last-child {
    border-bottom: none;
}
#modal-product-list .product-item img {
    width: 60px;
    height: 60px;
    object-fit: contain;
    border: 1px solid #eee;
    border-radius: 4px;
}
#modal-product-list .product-item .info {
    flex-grow: 1;
}
#modal-product-list .product-item .name {
    font-weight: 600;
    font-size: 0.95em;
    margin-bottom: 3px;
}
#modal-product-list .product-item .price {
    color: #E53935;
    font-weight: bold;
}
#modal-product-list .product-item .btn-select-this-product {
    padding: 6px 12px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9em;
}
</style>

<?php
if ($conn) {
    $conn = null;
}
include 'includes/footer.php';
?>