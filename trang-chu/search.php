<?php
// search.php

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


$searchKeywordInput = '';
$searchResults = [];
$searchMessage = '';
$performedSearch = false;

if (isset($_GET['keyword'])) {
    $performedSearch = true;
    $searchKeywordInput = trim($_GET['keyword']);

    if (!empty($searchKeywordInput)) {
        $likeKeyword = "%" . $searchKeywordInput . "%";

        try {
            $now_datetime_search = $conn->query("SELECT NOW()")->fetchColumn();

            // Cập nhật câu SQL để JOIN với bảng khuyenmai và lấy thông tin KM
            $sql = "SELECT 
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
                        WHERE :now_datetime_search_sub BETWEEN km_sub.ngay_bat_dau AND km_sub.ngay_ket_thuc
                    ) km_data ON p.id_sanpham = km_data.id_sanpham AND km_data.rn = 1
                    WHERE p.ten_sp LIKE :keyword 
                    ORDER BY p.id_sanpham ASC"; // Sắp xếp theo ID hoặc relevancy (nếu có)

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':now_datetime_search_sub', $now_datetime_search, PDO::PARAM_STR);
            $stmt->bindParam(':keyword', $likeKeyword, PDO::PARAM_STR);
            $stmt->execute();
            $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($searchResults)) {
                $searchMessage = "Không tìm thấy sản phẩm nào phù hợp với từ khóa \"" . htmlspecialchars($searchKeywordInput) . "\".";
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi tìm kiếm (search.php): " . $e->getMessage() . " --- SQLSTATE: " . $e->getCode());
            $searchMessage = "Có lỗi xảy ra trong quá trình tìm kiếm. Vui lòng thử lại sau. Mã lỗi: " . htmlspecialchars($e->getCode());
        }
    } else {
        $searchMessage = "Vui lòng nhập từ khóa tìm kiếm.";
    }
}

include 'includes/main_navigation.php';
?>
<script>
    document.title = <?php echo json_encode("Kết quả tìm kiếm cho: " . htmlspecialchars($searchKeywordInput) . " - PC Shop Nasa"); ?>;
</script>

<main class="search-results-page" style="padding: 40px 0;">
    <div class="container main-layout"> <!-- Thêm class main-layout để có thể dùng chung layout với sidebar -->

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
        
        <div class="main-content-area"> <!-- Bọc kết quả tìm kiếm vào đây -->
            <?php if ($performedSearch): ?>
                <h1>Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($searchKeywordInput); ?>"</h1>
                <hr style="margin: 15px 0 30px 0;">

                <?php if (!empty($searchMessage) && empty($searchResults)): ?>
                    <div class="alert <?php echo (stripos($searchMessage, 'lỗi') !== false) ? 'alert-danger' : 'alert-info'; ?>" role="alert" style="padding: 15px; border: 1px solid transparent; border-radius: 4px; margin-bottom: 20px; <?php echo (stripos($searchMessage, 'lỗi') !== false) ? 'background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;' : 'background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb;'; ?>">
                        <?php echo htmlspecialchars($searchMessage); ?>
                    </div>
                <?php elseif (!empty($searchResults)): ?>
                    <div class="product-grid">
                        <?php foreach ($searchResults as $product_row):  ?>
                            <?php
                                // Xử lý giá và khuyến mãi cho từng sản phẩm tìm được
                                $gia_hien_thi_search = (int)$product_row['gia_goc_sp'];
                                $gia_goc_sp_display_search = (int)$product_row['gia_goc_sp'];
                                $phan_tram_giam_display_search = null;
                                $co_khuyen_mai_hien_tai_search = false;
                                $ten_km_search = '';

                                if (!empty($product_row['km_start_date'])) {
                                    if (isset($product_row['giam_gia_percent']) && (int)$product_row['giam_gia_percent'] > 0) {
                                        $co_khuyen_mai_hien_tai_search = true;
                                        $gia_hien_thi_search = $gia_hien_thi_search - ($gia_hien_thi_search * (int)$product_row['giam_gia_percent'] / 100);
                                        $phan_tram_giam_display_search = (int)$product_row['giam_gia_percent'];
                                        $ten_km_search = $product_row['ten_km'] ?? '';
                                    } elseif (isset($product_row['giam_gia_tien']) && (int)$product_row['giam_gia_tien'] > 0) {
                                        $co_khuyen_mai_hien_tai_search = true;
                                        $gia_hien_thi_search = max(0, $gia_hien_thi_search - (int)$product_row['giam_gia_tien']);
                                        if ($gia_goc_sp_display_search > 0 && $gia_goc_sp_display_search > $gia_hien_thi_search) {
                                           $phan_tram_giam_display_search = round((($gia_goc_sp_display_search - $gia_hien_thi_search) / $gia_goc_sp_display_search) * 100);
                                        }
                                        $ten_km_search = $product_row['ten_km'] ?? '';
                                    }
                                }
                                $gia_hien_thi_search = round($gia_hien_thi_search);
                                $imageUrl_search = (!empty($product_row['hinh_anh']) && file_exists(trim($product_row['hinh_anh'])))
                                                  ? htmlspecialchars(trim($product_row['hinh_anh']))
                                                  : 'https://via.placeholder.com/250x250?text=No+Image';
                                $altText_search = htmlspecialchars($product_row['ten_sp']);
                            ?>
                            <div class="product-card">
                                <a href="chi-tiet-san-pham.php?id=<?php echo htmlspecialchars($product_row['id_sanpham']); ?>" class="product-link-wrapper">
                                    <?php if ($co_khuyen_mai_hien_tai_search && $phan_tram_giam_display_search > 0): ?>
                                        <div class="sale-badge-wrapper">
                                            <span class="sale-badge">Giảm <?php echo $phan_tram_giam_display_search; ?>%</span>
                                        </div>
                                    <?php endif; ?>
                                    <img src="<?php echo $imageUrl_search; ?>" alt="<?php echo $altText_search; ?>" class="product-image">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product_row['ten_sp']); ?></h3>
                                    <p class="product-price">
                                        <?php echo number_format($gia_hien_thi_search, 0, ',', '.'); ?>₫
                                        <?php if ($co_khuyen_mai_hien_tai_search && $gia_goc_sp_display_search > $gia_hien_thi_search): ?>
                                            <del class="original-price-display"><?php echo number_format($gia_goc_sp_display_search, 0, ',', '.'); ?>₫</del>
                                        <?php endif; ?>
                                    </p>
                                    <?php if($co_khuyen_mai_hien_tai_search && !empty($ten_km_search)): ?>
                                        <div class="product-promo-tag" style="font-size:0.8em; color: #fff; background-color: #E53935; padding: 3px 6px; border-radius: 3px; display:inline-block; margin-top: 5px; margin-bottom:5px;">
                                            <?php echo htmlspecialchars($ten_km_search); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-tags" style="margin-top:auto;">PC SHOP Nasa</div>
                                </a>
                                <div class="card-actions" style="padding-top: 10px; margin-top: 10px; border-top: 1px solid #f0f0f0;">
                                    <form action="add_to_cart.php" method="POST" class="add-to-cart-form"> 
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_row['id_sanpham']); ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-add-cart-small">
                                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info" role="alert" style="padding: 15px; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; color: #0c5460;">
                    Vui lòng nhập từ khóa vào ô tìm kiếm ở trên để tìm sản phẩm.
                </div>
            <?php endif; ?>
        </div> 
    </div> 
</main>

<?php include 'chatbox.php'; ?>


<?php
if ($conn) {
    $conn = null;
}
include 'includes/footer.php';
?>