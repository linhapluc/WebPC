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
$category_name = 'Tất cả sản phẩm';
$products_in_category = [];
$page_message = '';
$pageSpecificTitle = "Danh mục sản phẩm - PC Shop Nasa";

if (isset($_GET['id_loai']) && !empty(trim($_GET['id_loai']))) {
    $category_id_str = trim($_GET['id_loai']);

    try {
        // Lấy tên loại sản phẩm
        $stmt_category_name = $conn->prepare("SELECT ten_loaisp FROM loaisanpham WHERE id_loaisp = :id_loai LIMIT 1");
        $stmt_category_name->bindParam(':id_loai', $category_id_str, PDO::PARAM_STR);
        $stmt_category_name->execute();
        $category_info = $stmt_category_name->fetch(PDO::FETCH_ASSOC);

        if ($category_info) {
            $category_name = htmlspecialchars($category_info['ten_loaisp']);
            $pageSpecificTitle = $category_name . " - PC Shop Nasa";

            // Lấy tất cả sản phẩm thuộc loại này, bao gồm cả khuyến mãi
            $now_datetime_cat = $conn->query("SELECT NOW()")->fetchColumn();
            $sql_products = "SELECT p.*, km.ten_km, km.giam_gia_percent, km.giam_gia_tien, km.ngay_bat_dau AS km_start_date, km.ngay_ket_thuc AS km_end_date
                             FROM sanpham p
                             LEFT JOIN khuyenmai km ON p.id_sanpham = km.id_sanpham AND :now_datetime BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc
                             WHERE p.id_loai = :id_loai 
                             ORDER BY p.id_sanpham ASC";
            $stmt_products = $conn->prepare($sql_products);
            $stmt_products->bindParam(':now_datetime', $now_datetime_cat, PDO::PARAM_STR);
            $stmt_products->bindParam(':id_loai', $category_id_str, PDO::PARAM_STR);
            $stmt_products->execute();
            $products_in_category = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

            if (empty($products_in_category)) {
                $page_message = "Hiện chưa có sản phẩm nào trong danh mục '" . $category_name . "'.";
            }
        } else {
            $page_message = "Không tìm thấy danh mục sản phẩm này.";
            $category_name = "Danh mục không tồn tại";
        }
    } catch (PDOException $e) {
        error_log("Lỗi PDO khi tải trang danh mục: " . $e->getMessage());
        $page_message = "Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau.";
    }
} else {
    $page_message = "Vui lòng chọn một danh mục từ menu để xem sản phẩm.";
}
?>
<script>
    document.title = <?php echo json_encode($pageSpecificTitle); ?>;
</script>

<?php
include 'includes/main_navigation.php';
?>

<main class="category-page" style="padding: 30px 0;">
    <div class="container main-layout">
        <aside class="category-sidebar" id="category-sidebar-content">
            <h3 class="sidebar-title"><i class="fa-solid fa-bars"></i> Danh mục sản phẩm</h3>
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
        
        <div class="main-content-area">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $category_name; ?></li>
                </ol>
            </nav>

            <h1 class="page-title"><?php echo $category_name; ?></h1>
            <hr>

            <?php if (!empty($products_in_category)): ?>
                <div class="product-grid">
                    <?php foreach ($products_in_category as $product): ?>
                        <?php
                        $gia_final = (int)$product['gia'];
                        $gia_goc = (int)$product['gia'];
                        $phan_tram_giam = 0;
                        $co_km = false;

                        if (!empty($product['km_start_date'])) {
                            if (isset($product['giam_gia_percent']) && $product['giam_gia_percent'] > 0) {
                                $co_km = true;
                                $gia_final = $gia_final - ($gia_final * $product['giam_gia_percent'] / 100);
                                $phan_tram_giam = $product['giam_gia_percent'];
                            } elseif (isset($product['giam_gia_tien']) && $product['giam_gia_tien'] > 0) {
                                $co_km = true;
                                $gia_final = max(0, $gia_final - $product['giam_gia_tien']);
                                if ($gia_goc > 0) {
                                    $phan_tram_giam = round((($gia_goc - $gia_final) / $gia_goc) * 100);
                                }
                            }
                        }
                        $imageUrl = htmlspecialchars(trim($product['hinh_anh']));
                        $altText = htmlspecialchars($product['ten_sp']);
                        ?>
                        <div class="product-card">
                            <a href="chi-tiet-san-pham.php?id=<?php echo htmlspecialchars($product['id_sanpham']); ?>" class="product-link-wrapper">
                                <?php if ($co_km && $phan_tram_giam > 0): ?>
                                    <div class="sale-badge-wrapper">
                                        <span class="sale-badge">Giảm <?php echo $phan_tram_giam; ?>%</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-image-container">
                                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo $altText; ?>" class="product-image">
                                </div>
                                
                                <h3 class="product-name"><?php echo htmlspecialchars($product['ten_sp']); ?></h3>
                                <p class="product-price">
                                    <?php echo number_format($gia_final, 0, ',', '.'); ?>₫
                                    <?php if ($co_km): ?>
                                        <del class="original-price-display"><?php echo number_format($gia_goc, 0, ',', '.'); ?>₫</del>
                                    <?php endif; ?>
                                </p>
                                <div class="product-tags">PC SHOP Nasa</div>
                            </a>
                            <div class="card-actions">
                                <form action="add_to_cart.php" method="POST">
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
            <?php else: ?>
                 <div class="alert alert-info text-center"><?php echo htmlspecialchars($page_message); ?></div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
?>