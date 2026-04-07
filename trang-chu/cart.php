<?php
// cart.php

include 'includes/header.php';

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_message'] = "Vui lòng đăng nhập để xem giỏ hàng của bạn.";
    if (isset($_SERVER['HTTP_REFERER'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'];
    }
    header('Location: dangnhap.php');
    exit;
}

require 'includes/connect.php';

// ---- TÍNH TOÁN BIẾN CHO main_navigation.php 
$totalQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity_in_cart) {
        if (is_numeric($quantity_in_cart) && $quantity_in_cart > 0) {
            $totalQuantity += (int)$quantity_in_cart;
        }
    }
}
$cart_page_url = 'cart.php';



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $updateProductId_str = trim($_POST['product_id']);
        $newQuantity = (int)$_POST['quantity'];
        if (isset($_SESSION['cart'][$updateProductId_str])) {
            if ($newQuantity > 0) {
                $_SESSION['cart'][$updateProductId_str] = $newQuantity;
                $_SESSION['message'] = "Đã cập nhật số lượng sản phẩm!";
            } else {
                unset($_SESSION['cart'][$updateProductId_str]);
                $_SESSION['message'] = "Đã xóa sản phẩm khỏi giỏ hàng.";
            }
        }
    } elseif (isset($_POST['remove_item']) && isset($_POST['product_id'])) {
        $removeProductId_str = trim($_POST['product_id']);
        if (isset($_SESSION['cart'][$removeProductId_str])) {
            unset($_SESSION['cart'][$removeProductId_str]);
            $_SESSION['message'] = "Đã xóa sản phẩm khỏi giỏ hàng!";
        }
    }
    header('Location: cart.php');
    exit;
}

// Lấy thông tin sản phẩm trong giỏ hàng từ CSDL, tính cả khuyến mãi
$cartItems = [];
$totalCartPrice = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $productIds_in_cart_str = array_keys($_SESSION['cart']);

    if (!empty($productIds_in_cart_str)) {
        try {
            $now_datetime_cart = $conn->query("SELECT NOW()")->fetchColumn();

            $placeholders = implode(',', array_fill(0, count($productIds_in_cart_str), '?'));

            // Câu SQL lấy sản phẩm và TẤT CẢ khuyến mãi còn hiệu lực của nó
            $sql_cart = "SELECT 
                            p.id_sanpham, p.ten_sp, p.gia AS gia_goc_sp, p.hinh_anh,
                            km.ten_km, km.giam_gia_percent, km.giam_gia_tien
                         FROM sanpham p
                         LEFT JOIN khuyenmai km ON p.id_sanpham = km.id_sanpham 
                                              AND ? BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc
                         WHERE p.id_sanpham IN ($placeholders)";

            $stmt_cart = $conn->prepare($sql_cart);

            $execute_params = [$now_datetime_cart]; 
            foreach ($productIds_in_cart_str as $pid_str) {
                $execute_params[] = $pid_str;
            }

            $stmt_cart->execute($execute_params);
            $results_from_db = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);


            $products_data_grouped = [];
            foreach ($results_from_db as $row) {
                $products_data_grouped[$row['id_sanpham']][] = $row;
            }

            foreach ($productIds_in_cart_str as $productId_in_session) {
                if (isset($products_data_grouped[$productId_in_session]) && isset($_SESSION['cart'][$productId_in_session])) {
                    $product_variants = $products_data_grouped[$productId_in_session];
                    $first_variant_data = $product_variants[0];

                    $quantity_in_session = (int)$_SESSION['cart'][$productId_in_session];

                    if ($quantity_in_session > 0) {
                        $gia_goc_sp = (int)$first_variant_data['gia_goc_sp'];
                        $gia_item_final = $gia_goc_sp;
                        $gia_goc_hien_thi_cho_item = $gia_goc_sp;
                        $co_km_cho_item = false;
                        $ten_km_display = '';
                        $best_discount_value = 0;


                        foreach ($product_variants as $variant) {
                            if (!empty($variant['ten_km'])) {
                                $current_gia_ap_dung_km = $gia_goc_sp;
                                $temp_discount_value = 0;

                                if (isset($variant['giam_gia_percent']) && (int)$variant['giam_gia_percent'] > 0) {
                                    $temp_discount_value = $gia_goc_sp * (int)$variant['giam_gia_percent'] / 100;
                                    $current_gia_ap_dung_km -= $temp_discount_value;
                                } elseif (isset($variant['giam_gia_tien']) && (int)$variant['giam_gia_tien'] > 0) {
                                    $temp_discount_value = (int)$variant['giam_gia_tien'];
                                    $current_gia_ap_dung_km -= $temp_discount_value;
                                }
                                $current_gia_ap_dung_km = max(0, round($current_gia_ap_dung_km));

                                // Nếu KM này tốt hơn KM đã lưu trước đó, hoặc là KM đầu tiên
                                if ($temp_discount_value > $best_discount_value || !$co_km_cho_item) {
                                    $best_discount_value = $temp_discount_value;
                                    $gia_item_final = $current_gia_ap_dung_km;
                                    $ten_km_display = $variant['ten_km'];
                                    $co_km_cho_item = true;
                                }
                            }
                        }

                        $cartItems[] = [
                            'id' => $productId_in_session,
                            'ten_sp' => $first_variant_data['ten_sp'],
                            'gia' => $gia_item_final, 
                            'gia_goc_sp_display' => $gia_goc_hien_thi_cho_item,
                            'co_km' => $co_km_cho_item,
                            'ten_km' => $ten_km_display,
                            'hinh_anh' => $first_variant_data['hinh_anh'],
                            'quantity' => $quantity_in_session,
                            'subtotal' => $gia_item_final * $quantity_in_session
                        ];
                        $totalCartPrice += $gia_item_final * $quantity_in_session;
                    } else {
                        unset($_SESSION['cart'][$productId_in_session]);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi lấy thông tin giỏ hàng (cart.php): " . $e->getMessage() . " --- SQLSTATE: " . $e->getCode());
            $_SESSION['error_message'] = "Có lỗi xảy ra khi tải thông tin giỏ hàng. Mã lỗi: " . htmlspecialchars($e->getCode());
        }
    }
}

include 'includes/main_navigation.php'; // Hiển thị header
?>


<main class="cart-page" style="padding: 40px 0;">
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
        <h1>Giỏ hàng của bạn</h1>
        <hr style="margin: 15px 0 30px 0;">

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px;">
                <?php echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px;">
                <?php echo htmlspecialchars($_SESSION['error_message']);
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['login_message']) && !isset($_SESSION['user_id'])): ?>
            <div class="alert alert-info" style="background-color: #d1ecf1; color: #0c5460; padding: 10px; margin-bottom: 15px; border: 1px solid #bee5eb; border-radius: 4px;">
                <?php echo htmlspecialchars($_SESSION['login_message']);
                unset($_SESSION['login_message']); ?>
            </div>
        <?php endif; ?>


        <?php if (!empty($cartItems)): ?>
            <table class="cart-table" border="1" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95em;">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th style="padding: 10px 8px;">Ảnh</th>
                        <th style="padding: 10px 8px;">Tên sản phẩm</th>
                        <th style="padding: 10px 8px; text-align: right;">Đơn giá</th>
                        <th style="padding: 10px 8px; text-align: center;">Số lượng</th>
                        <th style="padding: 10px 8px; text-align: right;">Thành tiền</th>
                        <th style="padding: 10px 8px; text-align: center;">Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px 8px;">
                                <?php $imageUrl = (!empty($item['hinh_anh']) && file_exists(trim($item['hinh_anh']))) ? htmlspecialchars(trim($item['hinh_anh'])) : 'https://via.placeholder.com/60x60?text=NoImg'; ?>
                                <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($item['ten_sp']); ?>" style="width: 60px; height: 60px; object-fit: contain; border-radius: 3px;">
                            </td>
                            <td style="padding: 10px 8px;">
                                <a href="chi-tiet-san-pham.php?id=<?php echo htmlspecialchars($item['id']); ?>" style="color: #0D6EFD; font-weight: 500;"><?php echo htmlspecialchars($item['ten_sp']); ?></a>
                                <?php if ($item['co_km'] && !empty($item['ten_km'])): ?>
                                    <div style="font-size: 0.8em; color: #E53935; margin-top: 3px;">KM: <?php echo htmlspecialchars($item['ten_km']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 8px; text-align: right;">
                                <span style="color: #E53935; font-weight: bold;"><?php echo number_format($item['gia'], 0, ',', '.'); ?>₫</span>
                                <?php if ($item['co_km'] && $item['gia'] < $item['gia_goc_sp_display']):  ?>
                                    <br><del style="font-size:0.8em; color:#999;"><?php echo number_format($item['gia_goc_sp_display'], 0, ',', '.'); ?>₫</del>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 8px; text-align: center;">
                                <form action="cart.php" method="POST" style="display: inline-flex; align-items: center;">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" style="width: 45px; text-align: center; margin-right: 5px; padding: 5px; border: 1px solid #ccc; border-radius:3px;">
                                    <button type="submit" name="update_quantity" style="padding: 5px 8px; background-color:#0D6EFD; color:white; border:none; border-radius:3px; cursor:pointer;">Cập nhật</button>
                                </form>
                            </td>
                            <td style="padding: 10px 8px; text-align: right; font-weight: bold; color: #E53935;"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?>₫</td>
                            <td style="padding: 10px 8px; text-align: center;">
                                <form action="cart.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    <button type="submit" name="remove_item" style="color: #dc3545; background: none; border: none; cursor: pointer; font-size: 1.2em;" aria-label="Xóa sản phẩm" title="Xóa sản phẩm">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="4" style="padding: 15px 8px; text-align: right; font-weight: bold; font-size: 1.1em;">Tổng cộng:</td>
                        <td style="padding: 15px 8px; text-align: right; font-weight: bold; color: #E53935; font-size: 1.3em;"><?php echo number_format($totalCartPrice, 0, ',', '.'); ?>₫</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="cart-actions" style="margin-top: 30px; text-align: right; display:flex; justify-content: flex-end; gap: 10px;">
                <a href="index.php" class="btn" style="background-color: #6c757d; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Tiếp tục mua hàng</a>
                <a href="checkout.php" class="btn btn-buy-now" style="background-color: #E53935; color:white; padding: 10px 25px; border-radius: 4px; text-decoration: none; font-weight:bold;">Tiến hành đặt hàng</a>
            </div>

        <?php else: ?>
            <div class="alert alert-info" style="padding: 20px; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; text-align: center; font-size:1.1em;">
                Giỏ hàng của bạn đang trống. <a href="index.php" style="color: #0c5460; font-weight: bold;">Quay lại trang chủ</a> để chọn sản phẩm.
            </div>
        <?php endif; ?>

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
document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleSidebarBtn");
    const sidebar = document.getElementById("category-sidebar-content");

    toggleBtn.addEventListener("click", function () {
        sidebar.classList.toggle("is-visible");
    });

    // Đóng sidebar nếu click ra ngoài (tuỳ chọn)
    document.addEventListener("click", function (e) {
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
?>
<?php include 'includes/footer.php'; ?>