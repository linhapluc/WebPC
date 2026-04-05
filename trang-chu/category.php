<?php
// category.php

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

$category_id_str = '';
$category_name = 'Tất cả sản phẩm'; // Mặc định nếu không có id_loai
$products_in_category = [];
$page_message = '';
$pageSpecificTitle = "Danh mục sản phẩm - PC Shop Nasa";

if (isset($_GET['id_loai']) && !empty(trim($_GET['id_loai']))) {
    $category_id_str = trim($_GET['id_loai']);

    if (strpos($category_id_str, 'LSP') === 0 && strlen($category_id_str) > 3) {
        try {
            // 1. Lấy tên loại sản phẩm
            $sql_category_name = "SELECT ten_loaisp FROM loaisanpham WHERE id_loaisp = :id_loai LIMIT 1";
            $stmt_category_name = $conn->prepare($sql_category_name);
            $stmt_category_name->bindParam(':id_loai', $category_id_str, PDO::PARAM_STR);
            $stmt_category_name->execute();
            $category_info = $stmt_category_name->fetch(PDO::FETCH_ASSOC);

            if ($category_info) {
                $category_name = htmlspecialchars($category_info['ten_loaisp']);
                $pageSpecificTitle = $category_name . " - PC Shop Nasa";

                // 2. Lấy tất cả sản phẩm thuộc loại này, BAO GỒM KHUYẾN MÃI
                $now_datetime_cat = $conn->query("SELECT NOW()")->fetchColumn();


                $sql_products = "SELECT 
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
                                     WHERE :now_datetime_cat_sub BETWEEN km_sub.ngay_bat_dau AND km_sub.ngay_ket_thuc
                                 ) km_data ON p.id_sanpham = km_data.id_sanpham AND km_data.rn = 1
                                 WHERE p.id_loai = :id_loai 
                                 ORDER BY p.id_sanpham ASC";


                $stmt_products = $conn->prepare($sql_products);
                $stmt_products->bindParam(':now_datetime_cat_sub', $now_datetime_cat, PDO::PARAM_STR);
                $stmt_products->bindParam(':id_loai', $category_id_str, PDO::PARAM_STR);
                $stmt_products->execute();
                $products_in_category = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

                if (empty($products_in_category)) {
                    $page_message = "Hiện chưa có sản phẩm nào trong danh mục '" . $category_name . "'.";
                }
            } else {
                $page_message = "Không tìm thấy danh mục sản phẩm này.";
                $category_name = "Danh mục không tồn tại";
                $pageSpecificTitle = "Không tìm thấy danh mục - PC Shop Nasa";
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi tải trang danh mục (category.php): " . $e->getMessage() . " --- SQLSTATE: " . $e->getCode());
            $page_message = "Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau. Mã lỗi: " . htmlspecialchars($e->getCode());
            $pageSpecificTitle = "Lỗi tải danh mục - PC Shop Nasa";
        }
    } else {
        $page_message = "Mã danh mục không hợp lệ.";
        $category_name = "Danh mục không hợp lệ";
        $pageSpecificTitle = "Danh mục không hợp lệ - PC Shop Nasa";
    }
} else {
    // Nếu không có id_loai, có thể hiển thị tất cả sản phẩm hoặc thông báo
    $page_message = "Vui lòng chọn một danh mục từ menu để xem sản phẩm.";
    // Hoặc lấy tất cả sản phẩm (có thể cần phân trang)
    // $sql_all_products = "SELECT ... FROM sanpham ORDER BY ...";
    $pageSpecificTitle = "Tất cả sản phẩm - PC Shop Nasa";
}
?>
<script>
    document.title = <?php echo json_encode($pageSpecificTitle); ?>;
</script>

<?php
include 'includes/main_navigation.php';
?>

<main class="category-page" style="padding: 30px 0;">
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

        <nav aria-label="breadcrumb" style="margin-bottom: 25px; font-size: 0.9em;">
            <ol class="breadcrumb" style="background-color: #f8f9fa; padding: 10px 15px; border-radius: 4px;">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $category_name; ?></li>
            </ol>
        </nav>

        <h1 class="page-title" style="font-size: 1.8em; color: #333; margin-bottom: 20px;">
            <?php echo $category_name; ?>
        </h1>
        <hr style="margin: 15px 0 30px 0;">

        <?php if (!empty($page_message) && empty($products_in_category)): ?>
            <div class="alert alert-info" role="alert" style="text-align:center;">
                <?php echo htmlspecialchars($page_message); ?>
            </div>
        <?php elseif (!empty($products_in_category)): ?>
            <div class="product-grid">
                <?php foreach ($products_in_category as $product): ?>
                    <?php
                    // Xử lý giá và khuyến mãi cho từng sản phẩm
                    $gia_hien_thi_final_cat = (int)$product['gia_goc_sp'];
                    $gia_goc_sp_display_cat = (int)$product['gia_goc_sp'];
                    $phan_tram_giam_display_cat = null;
                    $co_khuyen_mai_hien_tai_cat = false;

                    // Kiểm tra xem có thông tin khuyến mãi hợp lệ không
                    if (!empty($product['km_start_date'])) {
                        if (isset($product['giam_gia_percent']) && (int)$product['giam_gia_percent'] > 0) {
                            $co_khuyen_mai_hien_tai_cat = true;
                            $gia_hien_thi_final_cat = $gia_hien_thi_final_cat - ($gia_hien_thi_final_cat * (int)$product['giam_gia_percent'] / 100);
                            $phan_tram_giam_display_cat = (int)$product['giam_gia_percent'];
                        } elseif (isset($product['giam_gia_tien']) && (int)$product['giam_gia_tien'] > 0) {
                            $co_khuyen_mai_hien_tai_cat = true;
                            $gia_hien_thi_final_cat = max(0, $gia_hien_thi_final_cat - (int)$product['giam_gia_tien']);
                            if ($gia_goc_sp_display_cat > 0 && $gia_goc_sp_display_cat > $gia_hien_thi_final_cat) {
                                $phan_tram_giam_display_cat = round((($gia_goc_sp_display_cat - $gia_hien_thi_final_cat) / $gia_goc_sp_display_cat) * 100);
                            }
                        }
                    }
                    $gia_hien_thi_final_cat = round($gia_hien_thi_final_cat);
                    $imageUrl = (!empty($product['hinh_anh']) && file_exists(trim($product['hinh_anh']))) ? htmlspecialchars(trim($product['hinh_anh'])) : 'https://via.placeholder.com/200x200?text=No+Image';
                    $altText = htmlspecialchars($product['ten_sp']);
                    ?>
                    <div class="product-card">
                        <a href="chi-tiet-san-pham.php?id=<?php echo htmlspecialchars($product['id_sanpham']); ?>" class="product-link-wrapper">
                            <?php if ($co_khuyen_mai_hien_tai_cat && $phan_tram_giam_display_cat > 0): ?>
                                <div class="sale-badge-wrapper">
                                    <span class="sale-badge">Giảm <?php echo $phan_tram_giam_display_cat; ?>%</span>
                                </div>
                            <?php endif; ?>
                            <img src="<?php echo $imageUrl; ?>" alt="<?php echo $altText; ?>" class="product-image">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['ten_sp']); ?></h3>
                            <p class="product-price">
                                <?php echo number_format($gia_hien_thi_final_cat, 0, ',', '.'); ?>₫
                                <?php if ($co_khuyen_mai_hien_tai_cat && $gia_goc_sp_display_cat > $gia_hien_thi_final_cat): ?>
                                    <del class="original-price-display"><?php echo number_format($gia_goc_sp_display_cat, 0, ',', '.'); ?>₫</del>
                                <?php endif; ?>
                            </p>
                            <?php if ($co_khuyen_mai_hien_tai_cat && !empty($product['ten_km'])): ?>
                                <div class="product-promo-tag" style="font-size:0.8em; color: #fff; background-color: #E53935; padding: 3px 6px; border-radius: 3px; display:inline-block; margin-top: 5px; margin-bottom:5px;">
                                    <?php echo htmlspecialchars($product['ten_km']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="product-tags" style="margin-top:auto;">PC SHOP Nasa</div>
                        </a>
                        <div class="card-actions" style="padding-top: 10px; margin-top: 10px; border-top: 1px solid #f0f0f0;">
                            <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id_sanpham']); ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-add-cart-small">
                                    <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Phân trang có thể thêm ở đây sau -->
        <?php elseif (empty($page_message)): ?>
            <div class="alert alert-warning" role="alert" style="text-align:center;">
                Vui lòng chọn một danh mục hợp lệ.
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
include 'includes/footer.php';
?>