<?php
// checkout.php

include 'includes/header.php';
require 'includes/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_message'] = "Vui lòng đăng nhập để tiến hành thanh toán.";
    if (isset($_SERVER['HTTP_REFERER'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'];
    }
    header('Location: dangnhap.php');
    exit;
}

// Kiểm tra giỏ hàng có trống không
if (empty($_SESSION['cart'])) {
    $_SESSION['cart_message_error'] = "Giỏ hàng của bạn đang trống. Không thể thanh toán.";
    header('Location: cart.php');
    exit;
}


$totalQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId_cart => $quantity_cart) {
        if (is_numeric($quantity_cart) && $quantity_cart > 0) {
            $totalQuantity += (int)$quantity_cart;
        }
    }
}
$cart_page_url = 'cart.php';



// Lấy thông tin người dùng hiện tại
$current_user_name = $_SESSION['user_name'] ?? '';
$current_user_email = '';
$current_user_phone = '';
$user_id_str = $_SESSION['user_id'];

try {
    $stmt_user_info = $conn->prepare("SELECT email, so_dien_thoai FROM khachhang WHERE id_khachhang = :user_id");
    $stmt_user_info->bindParam(':user_id', $user_id_str, PDO::PARAM_STR);
    $stmt_user_info->execute();
    $user_db_info = $stmt_user_info->fetch(PDO::FETCH_ASSOC);
    if ($user_db_info) {
        $current_user_email = $user_db_info['email'] ?? '';
        $current_user_phone = $user_db_info['so_dien_thoai'] ?? '';
    }
} catch (PDOException $e) {
    error_log("Lỗi PDO khi lấy thông tin user cho checkout: " . $e->getMessage());
}

// Lấy thông tin sản phẩm cho tóm tắt đơn hàng, tính cả khuyến mãi
$checkout_cart_items = [];
$checkout_total_price = 0;

if (!empty($_SESSION['cart'])) {
    $productIds_in_cart_str_checkout = array_keys($_SESSION['cart']);

    if (!empty($productIds_in_cart_str_checkout)) {
        try {
            $now_datetime_checkout = $conn->query("SELECT NOW()")->fetchColumn();
            $placeholders_checkout = implode(',', array_fill(0, count($productIds_in_cart_str_checkout), '?'));

            // Câu SQL  để lấy sản phẩm và KM tốt nhất
            $sql_checkout_cart = "SELECT 
                                    p.id_sanpham, p.ten_sp, p.gia AS gia_goc_sp, p.hinh_anh,
                                    km_data.giam_gia_percent, km_data.giam_gia_tien, 
                                    km_data.ten_km, km_data.ngay_bat_dau AS km_start_date, km_data.ngay_ket_thuc AS km_end_date
                                 FROM sanpham p
                                 LEFT JOIN (
                                     SELECT 
                                         km_sub.id_sanpham, km_sub.ten_km, km_sub.giam_gia_percent, km_sub.giam_gia_tien,
                                         km_sub.ngay_bat_dau, km_sub.ngay_ket_thuc,
                                         ROW_NUMBER() OVER (PARTITION BY km_sub.id_sanpham ORDER BY 
                                            CASE 
                                                WHEN km_sub.giam_gia_percent > 0 THEN 1
                                                WHEN km_sub.giam_gia_tien > 0 THEN 2
                                                ELSE 3 
                                            END,
                                            km_sub.giam_gia_percent DESC,
                                            km_sub.giam_gia_tien DESC
                                         ) as rn
                                     FROM khuyenmai km_sub
                                     WHERE ? BETWEEN km_sub.ngay_bat_dau AND km_sub.ngay_ket_thuc
                                 ) km_data ON p.id_sanpham = km_data.id_sanpham AND km_data.rn = 1
                                 WHERE p.id_sanpham IN ($placeholders_checkout)";

            $stmt_checkout_cart = $conn->prepare($sql_checkout_cart);
            $execute_params_checkout = [$now_datetime_checkout];
            foreach ($productIds_in_cart_str_checkout as $pid_str) {
                $execute_params_checkout[] = $pid_str;
            }
            $stmt_checkout_cart->execute($execute_params_checkout);
            $products_in_db_checkout = $stmt_checkout_cart->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products_in_db_checkout as $p_checkout) {
                $pid_checkout_str = $p_checkout['id_sanpham'];
                if (isset($_SESSION['cart'][$pid_checkout_str])) {
                    $qty_checkout = (int)$_SESSION['cart'][$pid_checkout_str];
                    if ($qty_checkout > 0) {

                        $gia_item_final_checkout = (int)$p_checkout['gia_goc_sp'];
                        $gia_goc_hien_thi_item_checkout = (int)$p_checkout['gia_goc_sp'];
                        $co_km_item_checkout = false;


                        if (!empty($p_checkout['km_start_date'])) {
                            if (isset($p_checkout['giam_gia_percent']) && (int)$p_checkout['giam_gia_percent'] > 0) {
                                $gia_item_final_checkout = $gia_item_final_checkout - ($gia_item_final_checkout * (int)$p_checkout['giam_gia_percent'] / 100);
                                $co_km_item_checkout = true;
                            } elseif (isset($p_checkout['giam_gia_tien']) && (int)$p_checkout['giam_gia_tien'] > 0) {
                                $gia_item_final_checkout = max(0, $gia_item_final_checkout - (int)$p_checkout['giam_gia_tien']);
                                $co_km_item_checkout = true;
                            }
                        }
                        $gia_item_final_checkout = round($gia_item_final_checkout);

                        $checkout_cart_items[] = [
                            'id' => $pid_checkout_str,
                            'ten_sp' => $p_checkout['ten_sp'],
                            'gia_display' => $gia_item_final_checkout,
                            'gia_goc_display' => $gia_goc_hien_thi_item_checkout,
                            'co_km' => $co_km_item_checkout,
                            'hinh_anh' => $p_checkout['hinh_anh'],
                            'quantity' => $qty_checkout,
                            'subtotal' => $gia_item_final_checkout * $qty_checkout
                        ];
                        $checkout_total_price += $gia_item_final_checkout * $qty_checkout;
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi lấy tóm tắt đơn hàng cho checkout: " . $e->getMessage() . " --- SQLSTATE: " . $e->getCode());
            $_SESSION['checkout_error'] = "Có lỗi khi tải thông tin tóm tắt đơn hàng. Mã lỗi: " . htmlspecialchars($e->getCode());
        }
    }
}

if (empty($checkout_cart_items) && !empty($_SESSION['cart'])) {
    $_SESSION['checkout_error'] = $_SESSION['checkout_error'] ?? "Một số sản phẩm trong giỏ không còn hợp lệ. Vui lòng kiểm tra lại giỏ hàng.";
}

include 'includes/main_navigation.php';
?>

<main class="checkout-page" style="padding: 40px 0;">
    <div class="container">
        <!-- ========== KHỐI SIDEBAR DANH MỤC ========== -->
        <aside class="category-sidebar" id="category-sidebar-content">
            <h3 class="sidebar-title"> <i class="fa-solid fa-bars"></i> Danh mục sản phẩm</h3>
            <ul class="category-list">
                <li><a href="category.php?id_loai=LSP001"><i class="fa-solid fa-keyboard fa-fw"></i> Bàn phím</a></li>
                <li><a href="category.php?id_loai=LSP002"><i class="fa-solid fa-mouse fa-fw"></i> Chuột</a></li>
                <li><a href="category.php?id_loai=LSP003"><i class="fa-solid fa-headphones fa-fw"></i> Tai nghe</a></li>
                <li><a href="category.php?id_loai=LSP004"><i class="fa-solid fa-table fa-fw"></i> Bàn</a></li>
                <li><a href="category.php?id_loai=LSP005"><i class="fa-solid fa-chair fa-fw"></i> Ghế</a></li>
                <li><a href="category.php?id_loai=LSP010"><i class="fa-solid fa-microchip fa-fw"></i> VGA</a></li>
                <li><a href="category.php?id_loai=LSP011"><i class="fa-solid fa-microchip fa-fw"></i> CPU</a></li>
                <li><a href="category.php?id_loai=LSP012"><i class="fa-solid fa-microchip fa-fw"></i> Mainboard</a></li>
                <li><a href="category.php?id_loai=LSP013"><i class="fa-solid fa-memory fa-fw"></i> RAM</a></li>
                <li><a href="category.php?id_loai=LSP014"><i class="fa-solid fa-hdd fa-fw"></i> SSD</a></li>
            </ul>
        </aside>
        <!-- ========== KẾT THÚC SIDEBAR ========== -->
        <h1>Thông Tin Thanh Toán và Giao Hàng</h1>
        <hr style="margin: 15px 0 30px 0;">

        <?php if (isset($_SESSION['checkout_error'])): ?>
            <div class="alert alert-danger" role="alert" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px;">
                <?php echo nl2br(htmlspecialchars($_SESSION['checkout_error']));
                unset($_SESSION['checkout_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['login_message']) && !isset($_SESSION['user_id'])): ?>
            <div class="alert alert-info" role="alert" style="background-color: #d1ecf1; color: #0c5460; padding: 10px; margin-bottom: 15px; border: 1px solid #bee5eb; border-radius: 4px;">
                <?php echo htmlspecialchars($_SESSION['login_message']);
                unset($_SESSION['login_message']); ?>
            </div>
        <?php endif; ?>

        <div class="row" style="display: flex; flex-wrap: wrap; gap: 20px;">
            <div class="col-lg-7" style="flex: 0 0 58%; max-width: 58%;">
                <h4 style="margin-bottom: 20px;">Thông tin giao hàng</h4>
                <!-- Sửa action của form thành process_order.php -->
                <form action="otp_verification.php" method="POST" id="checkout-form">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="customerName" class="form-label" style="display: block; margin-bottom: 5px;">Họ và Tên *</label>
                        <input type="text" class="form-control" id="customerName" name="customer_name" value="<?php echo htmlspecialchars($current_user_name); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="customerEmail" class="form-label" style="display: block; margin-bottom: 5px;">Email *</label>
                        <input type="email" class="form-control" id="customerEmail" name="customer_email" value="<?php echo htmlspecialchars($current_user_email); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="customerAddress" class="form-label" style="display: block; margin-bottom: 5px;">Địa chỉ nhận hàng *</label>
                        <input type="text" class="form-control" id="customerAddress" name="customer_address" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="customerPhone" class="form-label" style="display: block; margin-bottom: 5px;">Số điện thoại *</label>
                        <input type="tel" class="form-control" id="customerPhone" name="customer_phone" value="<?php echo htmlspecialchars($current_user_phone); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="paymentMethod" class="form-label" style="display: block; margin-bottom: 5px;">Phương thức thanh toán</label>
                        <select id="paymentMethod" name="payment_method" class="form-select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option selected value="COD">Thanh toán khi nhận hàng (COD)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="orderNotes" class="form-label" style="display: block; margin-bottom: 5px;">Ghi chú đơn hàng (tùy chọn)</label>
                        <textarea class="form-control" id="orderNotes" name="order_notes" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                    </div>
                    <div class="form-group" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; padding: 12px; background-color: #E53935; color:white; border:none; border-radius:4px; cursor:pointer; font-size: 1.1em;">HOÀN TẤT ĐẶT HÀNG</button>
                    </div>
                </form>
            </div>

            <div class="col-lg-5" style="flex: 0 0 40%; max-width: 40%;">
                <div style="border: 1px solid #eee; padding: 20px; border-radius: 5px; background-color: #f9f9f9;">
                    <h4 style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Tóm tắt đơn hàng</h4>
                    <?php if (!empty($checkout_cart_items)): ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach ($checkout_cart_items as $item): ?>
                                <li style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dashed #eee;">
                                    <div style="display: flex; align-items: center;">
                                        <?php $checkoutImageUrl = (!empty($item['hinh_anh']) && file_exists(trim($item['hinh_anh']))) ? htmlspecialchars(trim($item['hinh_anh'])) : 'https://via.placeholder.com/50x50?text=Img'; ?>
                                        <img src="<?php echo $checkoutImageUrl; ?>" alt="<?php echo htmlspecialchars($item['ten_sp']); ?>" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px; border-radius: 3px;">
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.95em;"><?php echo htmlspecialchars($item['ten_sp']); ?></div>
                                            <div style="font-size: 0.85em; color: #555;">
                                                SL: <?php echo $item['quantity']; ?> x
                                                <span style="<?php if ($item['co_km'] && $item['gia_display'] < $item['gia_goc_display']) echo 'color: #E53935; font-weight:bold;'; ?>"><?php echo number_format($item['gia_display'], 0, ',', '.'); ?>₫</span>
                                                <?php if ($item['co_km'] && $item['gia_display'] < $item['gia_goc_display']): ?>
                                                    <del style="font-size:0.9em; color:#999; margin-left:5px;"><?php echo number_format($item['gia_goc_display'], 0, ',', '.'); ?>₫</del>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="font-weight: 600; white-space: nowrap; font-size:0.95em;"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?>₫</div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <hr style="margin: 15px 0;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.2em; font-weight: bold; margin-top: 15px;">
                            <span>Tổng cộng:</span>
                            <span style="color: #E53935;"><?php echo number_format($checkout_total_price, 0, ',', '.'); ?>₫</span>
                        </div>
                    <?php elseif (empty($_SESSION['checkout_error'])): ?>
                        <p>Không có sản phẩm nào trong giỏ hàng để thanh toán.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .category-sidebar {
        position: fixed;
        top: 200px;
        left: 50px;
        width: 260px;
        padding: 20px;
        background-color: white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid #eee;
        border-radius: 6px;
        z-index: 2000;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .category-sidebar.is-visible {
        transform: translateX(0);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById("toggleSidebarBtn");
        const sidebar = document.getElementById("category-sidebar-content");

        toggleBtn.addEventListener("click", function() {
            sidebar.classList.toggle("is-visible");
        });

        // Đóng sidebar nếu click ra ngoài (tuỳ chọn)
        document.addEventListener("click", function(e) {
            if (
                sidebar.classList.contains("is-visible") &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)
            ) {
                sidebar.classList.remove("is-visible");
            }
        });
    });
</script>

<?php
if ($conn) {
    $conn = null;
}
include 'includes/footer.php';
?>