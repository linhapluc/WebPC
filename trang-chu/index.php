<?php
// index.php

include 'includes/header.php';
require 'includes/connect.php';

// ---- TÍNH TOÁN BIẾN CHO main_navigation.php ----
$totalQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId_in_cart => $quantity_in_cart) {
        if (is_numeric($quantity_in_cart) && $quantity_in_cart > 0) {
            $totalQuantity += (int)$quantity_in_cart;
        }
    }
}
$cart_page_url = isset($_SESSION['user_id']) ? 'cart.php' : 'dangnhap.php';


// ---- LẤY VÀ HIỂN THỊ THÔNG BÁO TỪ SESSION ----
$page_notification_success = '';
if (isset($_SESSION['cart_message_success'])) {
    $page_notification_success = '<div class="alert alert-success" style="position:fixed; top:80px; right:20px; z-index:9999; padding:15px; background-color:#d4edda; color: #155724; border:1px solid #c3e6cb; border-radius:4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">' . htmlspecialchars($_SESSION['cart_message_success']) . '</div>';
    unset($_SESSION['cart_message_success']);
}
$page_notification_error = '';
if (isset($_SESSION['cart_message_error'])) {
    $page_notification_error = '<div class="alert alert-danger" style="position:fixed; top:80px; right:20px; z-index:9999; padding:15px; background-color:#f8d7da; color: #721c24; border:1px solid #f5c6cb; border-radius:4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">' . htmlspecialchars($_SESSION['cart_message_error']) . '</div>';
    unset($_SESSION['cart_message_error']);
}
// ---- KẾT THÚC THÔNG BÁO ----


echo $page_notification_success;
echo $page_notification_error;

// Include phần navigation (top bar + header chính)
include 'includes/main_navigation.php';
?>

<main>
    <div class="container main-layout" style="display: flex">
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

        <div class="main-content-area">
            <?php

            // === HÀM display_products (CẬP NHẬT CHO KHUYẾN MÃI) ===
            function display_products($pdo_conn, $id_loai_param_str, $limit_val = 4, $no_product_message = "Hiện chưa có sản phẩm.")
            {
                if (!$pdo_conn) {
                    echo "<p style='grid-column: 1 / -1; text-align: center; color: red;'>Lỗi kết nối CSDL.</p>";
                    return;
                }
                $id_loai_str = (string)$id_loai_param_str;
                $limit = (int)$limit_val;

                try {

                    $now_datetime_str = $pdo_conn->query("SELECT NOW()")->fetchColumn();



                    $sql = "SELECT p.id_sanpham, p.ten_sp, p.gia AS gia_goc_sp, p.hinh_anh, 
               km.giam_gia_percent, km.giam_gia_tien, 
               km.ngay_bat_dau AS km_start, km.ngay_ket_thuc AS km_end 
        FROM sanpham p
        LEFT JOIN khuyenmai km ON p.id_sanpham = km.id_sanpham 
                             AND :now_datetime BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc 
        WHERE p.id_loai = :id_loai 
        ORDER BY p.id_sanpham DESC 
        LIMIT :limit_count";

                    $stmt = $pdo_conn->prepare($sql);

                    $stmt->bindParam(':now_datetime', $now_datetime_str, PDO::PARAM_STR);
                    $stmt->bindParam(':id_loai', $id_loai_str, PDO::PARAM_STR);
                    $stmt->bindParam(':limit_count', $limit, PDO::PARAM_INT);
                    $stmt->execute();
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($products && count($products) > 0) {
                        foreach ($products as $row) {
                            $gia_ban_cuoi_cung = (int)$row['gia_goc_sp'];
                            $phan_tram_giam_hien_thi = null;
                            $co_khuyen_mai_hien_tai = false;


                            if ($row['km_start'] !== null && $row['km_end'] !== null) {


                                if (isset($row['giam_gia_percent']) && $row['giam_gia_percent'] > 0) {
                                    $co_khuyen_mai_hien_tai = true;
                                    $gia_goc_de_hien_thi_neu_co_km = $gia_ban_cuoi_cung;
                                    $gia_ban_cuoi_cung = $gia_ban_cuoi_cung - ($gia_ban_cuoi_cung * (int)$row['giam_gia_percent'] / 100);
                                    $phan_tram_giam_hien_thi = (int)$row['giam_gia_percent'];
                                } elseif (isset($row['giam_gia_tien']) && $row['giam_gia_tien'] > 0) {
                                    $co_khuyen_mai_hien_tai = true;
                                    $gia_goc_de_hien_thi_neu_co_km = $gia_ban_cuoi_cung;
                                    $gia_ban_cuoi_cung = max(0, $gia_ban_cuoi_cung - (int)$row['giam_gia_tien']);
                                    if ($gia_goc_de_hien_thi_neu_co_km > 0 && $gia_goc_de_hien_thi_neu_co_km > $gia_ban_cuoi_cung) {
                                        $phan_tram_giam_hien_thi = round((($gia_goc_de_hien_thi_neu_co_km - $gia_ban_cuoi_cung) / $gia_goc_de_hien_thi_neu_co_km) * 100);
                                    }
                                }
                                // }
                            }
                            $gia_ban_cuoi_cung = round($gia_ban_cuoi_cung);

                            $imageUrl = (!empty($row['hinh_anh']) && file_exists(trim($row['hinh_anh']))) ? htmlspecialchars(trim($row['hinh_anh'])) : 'https://via.placeholder.com/200x200?text=No+Image';
                            $altText = htmlspecialchars($row['ten_sp']);?>
                            <div class="product-card">
                                <a href="chi-tiet-san-pham.php?id=<?php echo $row['id_sanpham']; ?>" class="product-link-wrapper">
                                    <?php if ($co_khuyen_mai_hien_tai && $phan_tram_giam_hien_thi > 0): ?>
                                        <div class="sale-badge-wrapper">
                                            <span class="sale-badge">Giảm <?php echo $phan_tram_giam_hien_thi; ?>%</span>
                                        </div>
                                    <?php endif; ?>
                                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo $altText; ?>" class="product-image">
                                    <h3 class="product-name"><?php echo htmlspecialchars($row['ten_sp']); ?></h3>
                                    <p class="product-price">
                                        <?php echo number_format($gia_ban_cuoi_cung, 0, ',', '.'); ?>₫
                                        <?php if ($co_khuyen_mai_hien_tai && !empty($gia_goc_de_hien_thi_neu_co_km) && $gia_goc_de_hien_thi_neu_co_km > $gia_ban_cuoi_cung): ?>
                                            <del class="original-price-display"><?php echo number_format($gia_goc_de_hien_thi_neu_co_km, 0, ',', '.'); ?>₫</del>
                                        <?php endif; ?>
                                    </p>
                                    <div class="product-tags"> PC SHOP Nasa </div>
                                </a>
                                <div class="card-actions" style="padding-top: 10px; margin-top: 10px; border-top: 1px solid #f0f0f0;">
                                    <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id_sanpham']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-add-cart-small">
                                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                                        </button>
                                    </form>
                                </div>
                            </div>
            <?php
                        }
                    } else {
                        echo "<p style='grid-column: 1 / -1; text-align: center;'>{$no_product_message}</p>";
                    }
                } catch (PDOException $e) {
                    error_log("Lỗi PDO trong display_products (index.php): " . $e->getMessage() . " --- SQL: " . ($sql ?? 'Không có SQL'));
                    echo "<p style='grid-column: 1 / -1; text-align: center; color: red;'>Có lỗi xảy ra khi tải sản phẩm.</p>";
                }
            }
            ?>

            <section class="tabbed-products keyboard-mouse-section">
                <nav class="tab-nav">
                    <ul role="tablist">
                        <li><a href="#panel-keyboard" class="tab-link active" data-tab-target="#panel-keyboard" data-id="LSP001" data-name="Bàn phím">Bàn phím</a></li>
                        <li><a href="#panel-mouse" class="tab-link" data-tab-target="#panel-mouse" data-id="LSP002" data-name="Chuột">Chuột</a></li>
                        <li><a href="#panel-headphone" class="tab-link" data-tab-target="#panel-headphone" data-id="LSP003" data-name="Tai nghe">Tai nghe</a></li>
                        <li><a href="#panel-desk" class="tab-link" data-tab-target="#panel-desk" data-id="LSP004" data-name="Bàn">Bàn</a></li>
                        <li><a href="#panel-chair" class="tab-link" data-tab-target="#panel-chair" data-id="LSP005" data-name="Ghế">Ghế</a></li>
                    </ul>
                    <a href="category.php?id_loai=LSP001" class="view-all-link">Xem tất cả Bàn phím »</a>
                </nav>
                <div class="tab-content-container">
                    <div id="panel-keyboard" role="tabpanel" class="tab-content-panel active active-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP001', 4, "Không có sản phẩm bàn phím nào."); ?>
                        </div>
                    </div>
                    <div id="panel-mouse" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP002', 4, "Hiện chưa có sản phẩm chuột nào."); ?>
                        </div>
                    </div>
                    <div id="panel-headphone" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP003', 4, "Hiện chưa có sản phẩm tai nghe nào."); ?>
                        </div>
                    </div>
                    <div id="panel-desk" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP004', 4, "Hiện chưa có sản phẩm bàn nào."); ?>
                        </div>
                    </div>
                    <div id="panel-chair" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP005', 4, "Hiện chưa có sản phẩm ghế nào."); ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="tabbed-products screen-section">
                <nav class="tab-nav">
                    <ul role="tablist">
                        <li><a href="#panel-mh-km" class="tab-link active" data-tab-target="#panel-mh-km" data-id="LSP006" data-name="Màn hình KM">Màn hình KM</a></li>
                        <li><a href="#panel-mh-gaming" class="tab-link" data-tab-target="#panel-mh-gaming" data-id="LSP007" data-name="Màn hình Gaming">Màn hình Gaming</a></li>
                        <li><a href="#panel-mh-vp" class="tab-link" data-tab-target="#panel-mh-vp" data-id="LSP008" data-name="Màn hình VP">Màn hình VP</a></li>
                        <li><a href="#panel-mh-dh" class="tab-link" data-tab-target="#panel-mh-dh" data-id="LSP009" data-name="Màn hình Đồ họa">Màn hình Đồ họa</a></li>
                    </ul>
                    <a href="category.php?id_loai=LSP006" class="view-all-link">Xem tất cả Màn hình KM »</a>

                </nav>
                <div class="tab-content-container">
                    <div id="panel-mh-km" role="tabpanel" class="tab-content-panel active active-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP006', 4, "Chưa có màn hình khuyến mãi."); ?>
                        </div>
                    </div>
                    <div id="panel-mh-gaming" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP007', 4, "Chưa có màn hình gaming."); ?>
                        </div>
                    </div>
                    <div id="panel-mh-vp" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP008', 4, "Chưa có màn hình văn phòng."); ?>
                        </div>
                    </div>
                    <div id="panel-mh-dh" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP009', 4, "Chưa có màn hình đồ họa."); ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="tabbed-products component-section">
                <nav class="tab-nav">
                    <ul role="tablist">
                        <li><a href="#panel-vga" class="tab-link active" data-tab-target="#panel-vga" data-id="LSP010" data-name="VGA">VGA</a></li>
                        <li><a href="#panel-cpu" class="tab-link" data-tab-target="#panel-cpu" data-id="LSP011" data-name="CPU">CPU</a></li>
                        <li><a href="#panel-mainboard" class="tab-link" data-tab-target="#panel-mainboard" data-id="LSP012" data-name="Mainboard">Mainboard</a></li>
                        <li><a href="#panel-ram" class="tab-link" data-tab-target="#panel-ram" data-id="LSP013" data-name="RAM">RAM</a></li>
                        <li><a href="#panel-ssd" class="tab-link" data-tab-target="#panel-ssd" data-id="LSP014" data-name="SSD">SSD</a></li>
                    </ul>
                    <a href="category.php?id_loai=LSP010" class="view-all-link">Xem tất cả VGA »</a>
                </nav>
                <div class="tab-content-container">
                    <div id="panel-vga" role="tabpanel" class="tab-content-panel active active-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP010', 4, "Chưa có sản phẩm VGA."); ?>
                        </div>
                    </div>
                    <div id="panel-cpu" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP011', 4, "Chưa có sản phẩm CPU."); ?>
                        </div>
                    </div>
                    <div id="panel-mainboard" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP012', 4, "Chưa có sản phẩm Mainboard."); ?>
                        </div>
                    </div>
                    <div id="panel-ram" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP013', 4, "Chưa có sản phẩm RAM."); ?>
                        </div>
                    </div>
                    <div id="panel-ssd" role="tabpanel" class="tab-content-panel">
                        <div class="product-grid">
                            <?php display_products($conn, 'LSP014', 4, "Chưa có sản phẩm SSD."); ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sections = document.querySelectorAll(".tabbed-products");

        sections.forEach(section => {
            const tabLinks = section.querySelectorAll(".tab-link");
            const viewAllLink = section.querySelector(".view-all-link");
            const tabPanels = section.querySelectorAll(".tab-content-panel");

            tabLinks.forEach(tab => {
                tab.addEventListener("click", function(e) {
                    e.preventDefault();

                    // 1. Kích hoạt tab
                    tabLinks.forEach(t => t.classList.remove("active", "active-tab"));
                    this.classList.add("active", "active-tab");

                    // 2. Hiển thị panel tương ứng
                    const targetSelector = this.dataset.tabTarget;
                    tabPanels.forEach(panel => panel.classList.remove("active", "active-panel"));
                    const targetPanel = section.querySelector(targetSelector);
                    if (targetPanel) {
                        targetPanel.classList.add("active", "active-panel");
                    }

                    // 3. Cập nhật link "Xem tất cả"
                    const loaiId = this.dataset.id;
                    const loaiTen = this.dataset.name;
                    if (viewAllLink) {
                        viewAllLink.href = `category.php?id_loai=${loaiId}`;
                        viewAllLink.textContent = `Xem tất cả ${loaiTen} »`;
                    }
                });
            });
        });
    });
</script>
<style>
.category-sidebar {
    width: 260px;
    flex-shrink: 0;
    background-color: #FFFFFF;
    padding: 20px;
    border: 1px solid #ECEFF1;
    border-radius: 6px;
    height: fit-content;
    visibility: hidden;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

/* Khi bật menu trên mobile */
.category-sidebar.is-visible {
    visibility: visible;
    opacity: 1;
    pointer-events: auto;

    /* Sticky khi hiện ra */
    position: sticky;
    top: 100px;
    z-index: 100;
}
</style>


<?php
if ($conn) {
    $conn = null;
}
include 'includes/footer.php';
?>