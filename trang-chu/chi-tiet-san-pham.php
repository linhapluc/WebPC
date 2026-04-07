<?php
// chi-tiet-san-pham.php

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

$productId_str_url = '';
$productFound = false;
$productData = null;
$errorMessage = '';
$pageSpecificTitle = "Chi tiết sản phẩm - PC Shop Nasa";

// Các biến để hiển thị giá và khuyến mãi
$gia_hien_thi_final = 0;
$gia_goc_sp_display = null;
$phan_tram_giam_display = null;
$co_khuyen_mai_hien_tai = false;

// Lấy thông tin bảo hành sản phẩm (nếu có)
$thoi_gian_baohanh = null;


if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $productId_str_url = trim($_GET['id']);

    // Lấy đánh giá từ database trước
    $reviews = [];
    $sql_reviews = "SELECT dg.*, kh.ho_ten 
                    FROM danhgia dg 
                    JOIN khachhang kh ON dg.id_khachhang = kh.id_khachhang 
                    WHERE dg.id_sanpham = :id_sp 
                    ORDER BY dg.ngay_danhgia DESC";
    $stmt_reviews = $conn->prepare($sql_reviews);
    $stmt_reviews->bindParam(':id_sp', $productId_str_url);
    $stmt_reviews->execute();
    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

    //Sau khi đã có reviews, chèn thêm đánh giá từ session (nếu có)
    if (!empty($_SESSION['last_review']) && $_SESSION['last_review']['id_sanpham'] === $productId_str_url) {
        $session_review = $_SESSION['last_review'];
        $session_review['id_danhgia'] = 'temp';
        $session_review['id_khachhang'] = $_SESSION['user_id'];

        //Kiểm tra xem đã có đánh giá tương tự trong DB chưa
        $isDuplicate = false;
        foreach ($reviews as $r) {
            if (
                $r['id_khachhang'] === $session_review['id_khachhang'] &&
                $r['id_sanpham'] === $session_review['id_sanpham'] &&
                trim($r['binh_luan']) === trim($session_review['binh_luan']) &&
                intval($r['sao']) === intval($session_review['sao'])
            ) {
                $isDuplicate = true;
                break;
            }
        }

        if (!$isDuplicate) {
            array_unshift($reviews, $session_review);
        }

        unset($_SESSION['last_review']);
    }
    // đánh giá

    if (strpos($productId_str_url, 'SP') === 0 && strlen($productId_str_url) > 2) {
        try {
            $now_datetime_str = $conn->query("SELECT NOW()")->fetchColumn();

            $sql = "SELECT p.id_sanpham, p.ten_sp, p.gia AS gia_goc_sanpham, p.mo_ta, 
                           p.thong_so_ky_thuat, p.sl, p.id_loai, p.hinh_anh,
                           km.ten_km, km.giam_gia_percent, km.giam_gia_tien, 
                           km.ngay_bat_dau AS km_ngay_bat_dau, km.ngay_ket_thuc AS km_ngay_ket_thuc
                    FROM sanpham p
                    LEFT JOIN khuyenmai km ON p.id_sanpham = km.id_sanpham 
                                         AND :now_datetime BETWEEN km.ngay_bat_dau AND km.ngay_ket_thuc
                    WHERE p.id_sanpham = :id_sp 
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':now_datetime', $now_datetime_str, PDO::PARAM_STR);
            $stmt->bindParam(':id_sp', $productId_str_url, PDO::PARAM_STR);
            $stmt->execute();
            $productData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($productData) {
                $productFound = true;
                $pageSpecificTitle = htmlspecialchars($productData['ten_sp']) . " - PC Shop Nasa";

                $gia_hien_thi_final = (int)$productData['gia_goc_sanpham'];

                try {
                    $stmt_bh = $conn->prepare("SELECT thoi_gian_bh FROM baohanh WHERE id_sanpham = :id_sp LIMIT 1");
                    $stmt_bh->bindParam(':id_sp', $productData['id_sanpham'], PDO::PARAM_STR);
                    $stmt_bh->execute();
                    $baohanh_data = $stmt_bh->fetch(PDO::FETCH_ASSOC);

                    if ($baohanh_data && isset($baohanh_data['thoi_gian_bh'])) {
                        $thoi_gian_baohanh = (int)$baohanh_data['thoi_gian_bh'];
                    }
                } catch (PDOException $e) {
                    error_log("Lỗi lấy bảo hành: " . $e->getMessage());
                }

                if (!empty($productData['km_ngay_bat_dau']) && !empty($productData['km_ngay_ket_thuc'])) {

                    if (isset($productData['giam_gia_percent']) && (int)$productData['giam_gia_percent'] > 0) {
                        $co_khuyen_mai_hien_tai = true;
                        $gia_goc_sp_display = $gia_hien_thi_final;
                        $gia_hien_thi_final = $gia_hien_thi_final - ($gia_hien_thi_final * (int)$productData['giam_gia_percent'] / 100);
                        $phan_tram_giam_display = (int)$productData['giam_gia_percent'];
                    } elseif (isset($productData['giam_gia_tien']) && (int)$productData['giam_gia_tien'] > 0) {
                        $co_khuyen_mai_hien_tai = true;
                        $gia_goc_sp_display = $gia_hien_thi_final;
                        $gia_hien_thi_final = max(0, $gia_hien_thi_final - (int)$productData['giam_gia_tien']);
                        if ($gia_goc_sp_display > 0 && $gia_goc_sp_display > $gia_hien_thi_final) {
                            $phan_tram_giam_display = round((($gia_goc_sp_display - $gia_hien_thi_final) / $gia_goc_sp_display) * 100);
                        }
                    }
                }
                $gia_hien_thi_final = round($gia_hien_thi_final);
            } else {
                $errorMessage = "Sản phẩm bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.";
                $pageSpecificTitle = "Không tìm thấy sản phẩm - PC Shop Nasa";
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi lấy chi tiết sản phẩm (chi-tiet-san-pham.php): " . $e->getMessage());
            $errorMessage = "Có lỗi xảy ra khi tải thông tin sản phẩm. Vui lòng thử lại sau.";
            $pageSpecificTitle = "Lỗi tải sản phẩm - PC Shop Nasa";
        }
    } else {
        $errorMessage = "ID sản phẩm không hợp lệ.";
        $pageSpecificTitle = "ID không hợp lệ - PC Shop Nasa";
    }
} else {
    $errorMessage = "Không tìm thấy ID sản phẩm.";
    $pageSpecificTitle = "Thiếu ID sản phẩm - PC Shop Nasa";
}
?>
<script>
    document.title = <?php echo json_encode($pageSpecificTitle); ?>;
</script>

<?php
include 'includes/main_navigation.php';
?>

<main class="product-detail-page" style="padding: 40px 0;">
    <div class="container main-layout">

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

        <div class="main-content-area"> <!-- Nội dung chính của trang chi tiết -->
            <?php if ($productFound && $productData): ?>
                <div class="product-detail-container simple-layout">
                    <div class="product-gallery simple-gallery">
                        <div class="main-image-container">
                            <?php
                            $mainImageUrl = (!empty($productData['hinh_anh']) && file_exists(trim($productData['hinh_anh'])))
                                ? htmlspecialchars(trim($productData['hinh_anh']))
                                : 'https://via.placeholder.com/500x500?text=No+Image';
                            ?>
                            <img src="<?php echo $mainImageUrl; ?>" alt="<?php echo htmlspecialchars($productData['ten_sp']); ?>" id="main-product-img">
                        </div>
                    </div>

                    <div class="product-info-main simple-info">
                        <h1><?php echo htmlspecialchars($productData['ten_sp']); ?></h1>
                        <div class="product-sku" style="font-size: 0.9em; color: #777; margin-bottom: 15px;">
                            Mã SP: <?php echo htmlspecialchars($productData['id_sanpham']); ?>
                            <?php if ($thoi_gian_baohanh !== null): ?>
                                &nbsp;|&nbsp; Bảo hành: <?php echo $thoi_gian_baohanh; ?> tháng
                            <?php endif; ?>
                        </div>




                        <div class="product-price-box" style="margin-bottom: 15px; display: flex; align-items: baseline; flex-wrap: wrap;">
                            <span class="current-price" style="font-size: 2em; font-weight: 700; color: #E53935; margin-right: 10px;"><?php echo number_format($gia_hien_thi_final, 0, ',', '.'); ?>₫</span>
                            <?php if ($co_khuyen_mai_hien_tai && !empty($gia_goc_sp_display) && $gia_goc_sp_display > $gia_hien_thi_final): ?>
                                <del class="original-price-display" style="font-size: 1.2em; color: #999; margin-right: 10px;"><?php echo number_format($gia_goc_sp_display, 0, ',', '.'); ?>₫</del>
                                <?php if ($phan_tram_giam_display > 0): ?>
                                    <span class="sale-percentage-tag" style="background-color: #E53935; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.9em; font-weight: bold;">
                                        -<?php echo $phan_tram_giam_display; ?>%
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php
                        $stock_available = isset($productData['sl']) && is_numeric($productData['sl']) && $productData['sl'] > 0;
                        $stock_quantity = isset($productData['sl']) && is_numeric($productData['sl']) ? (int)$productData['sl'] : 0;
                        ?>
                        <div class="product-stock-status" style="margin-bottom: 20px; font-size: 0.9em;">
                            <?php if ($stock_available): ?>
                                <span style="color: green;">Tình trạng: Còn hàng (<?php echo $stock_quantity; ?> sản phẩm)</span>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;">Tình trạng: Hết hàng</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-purchase-section">
                            <form action="add_to_cart.php" method="POST" class="detail-add-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productData['id_sanpham']); ?>">
                                <div class="quantity-box">
                                    <label for="quantity_detail_page" style="margin-right: 10px; font-weight: 600;">Số lượng:</label>
                                    <div class="quantity-input">
                                        <button type="button" class="qty-btn minus" aria-label="Giảm số lượng" <?php if (!$stock_available) echo 'disabled'; ?>>-</button>
                                        <input type="number" id="quantity_detail_page" name="quantity" value="<?php echo ($stock_available ? '1' : '0'); ?>" min="<?php echo ($stock_available ? '1' : '0'); ?>" max="<?php echo $stock_quantity; ?>" aria-label="Số lượng sản phẩm" <?php if (!$stock_available) echo 'disabled'; ?>>
                                        <button type="button" class="qty-btn plus" aria-label="Tăng số lượng" <?php if (!$stock_available) echo 'disabled'; ?>>+</button>
                                    </div>
                                </div>
                                <?php if ($stock_available): ?>
                                    <div class="action-buttons" style="margin-top: 20px;">
                                        <button type="submit" class="btn btn-add-cart"><i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ</button>
                                        <button type="button" class="btn btn-buy-now" onclick="buyNowAction('<?php echo htmlspecialchars($productData['id_sanpham']); ?>')">MUA NGAY</button>
                                    </div>
                                <?php endif;  ?>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="product-full-details" style="margin-top: 50px;">
                    <div class="detail-tabs">
                        <button class="detail-tab-btn active" data-tab-target="#product-desc-section">Mô tả sản phẩm</button>
                        <button class="detail-tab-btn" data-tab-target="#product-specs-section">Thông số kỹ thuật</button>
                    </div>
                    <div class="detail-tab-content">
                        <div id="product-desc-section" class="detail-panel active">
                            <h2>Mô tả sản phẩm</h2>
                            <hr style="margin: 10px 0;">
                            <div><?php echo (!empty($productData['mo_ta'])) ? nl2br(htmlspecialchars($productData['mo_ta'])) : 'Sản phẩm hiện chưa có mô tả chi tiết.'; ?></div>
                        </div>
                        <div id="product-specs-section" class="detail-panel">
                            <h2>Thông số kỹ thuật</h2>
                            <hr style="margin: 10px 0;">
                            <div>
                                <?php
                                if (!empty($productData['thong_so_ky_thuat'])) {
                                    echo nl2br(htmlspecialchars($productData['thong_so_ky_thuat']));
                                } else {
                                    echo 'Thông số kỹ thuật chi tiết đang được cập nhật.';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div style='text-align: center; padding: 50px;'>
                    <h2><?php echo !empty($errorMessage) ? htmlspecialchars($errorMessage) : "Không tìm thấy sản phẩm!"; ?></h2>
                    <p>Vui lòng kiểm tra lại đường dẫn hoặc quay lại <a href='index.php' style='color: #E53935; text-decoration: underline;'>trang chủ</a>.</p>
                </div>
            <?php endif; ?>
        </div> <!-- Kết thúc .main-content-area -->
    </div> <!-- Kết thúc .container .main-layout -->

    <!-- Đánh Giá -->

    <div class="product-review-section simple-layout" style="margin-top: 50px; max-width: 550px; margin-left: auto; margin-right: auto;">

        <div class="review-title">Đánh giá từ khách hàng</div>
        <hr>

        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong><?php echo htmlspecialchars($review['ho_ten']); ?></strong>
                        <small><?php echo date('d/m/Y H:i', strtotime($review['ngay_danhgia'])); ?></small>
                    </div>
                    <div style="color: #f6c343;">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $review['sao'] ? '★' : '☆';
                        }
                        ?>
                    </div>
                    <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['binh_luan'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
        <?php endif; ?>


        <hr style="margin: 40px 0 20px;">

        <div class="submit-review-box">
            <h3>Gửi đánh giá của bạn</h3>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="submit_review.php" method="POST">

                    <input type="hidden" name="id_sanpham" value="<?php echo htmlspecialchars($productData['id_sanpham']); ?>">

                    <label for="sao" style="font-weight: 600;">Số sao:</label>
                    <select name="sao" id="sao" required>
                        <option value="">-- Chọn --</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> sao</option>
                        <?php endfor; ?>
                    </select>

                    <br><br>
                    <label for="binh_luan" style="font-weight: 600;">Bình luận:</label>
                    <textarea name="binh_luan" id="binh_luan" rows="4" required></textarea>

                    <br>
                    <button type="submit" class="btn-submit-review">GỬI ĐÁNH GIÁ</button>
                    <?php if (!empty($_SESSION['review_success'])): ?>
                        <div class="alert success" style="color: green; margin-top: 15px;">
                            <?php echo $_SESSION['review_success'];
                            unset($_SESSION['review_success']); ?>
                        </div>
                    <?php elseif (!empty($_SESSION['review_error'])): ?>
                        <div class="alert error" style="color: red; margin-top: 15px;">
                            <?php echo $_SESSION['review_error'];
                            unset($_SESSION['review_error']); ?>
                        </div>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <p><a href="dangnhap.php" style="color: #007bff; text-decoration: underline;">Đăng nhập</a> để gửi đánh giá của bạn.</p>
            <?php endif; ?>
        </div>
    </div>
    <style>
        .submit-review-box {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin: 40px auto;
        }

        .submit-review-box label,
        .submit-review-box h3 {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .submit-review-box select,
        .submit-review-box textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .btn-submit-review {
            display: block;
            margin: 0 auto;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-submit-review:hover {
            background: #0056b3;
        }
    </style>
    
<?php include 'chatbox.php'; ?>

    <!-- Đánh Giá -->

</main>
<script>
    function buyNowAction(productId) {
        // ... (code JavaScript cho buyNowAction ) ...
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'add_to_cart.php';
        const productIdInput = document.createElement('input');
        productIdInput.type = 'hidden';
        productIdInput.name = 'product_id';
        productIdInput.value = productId;
        form.appendChild(productIdInput);
        const quantityInputEl = document.getElementById('quantity_detail_page');
        const quantityValue = quantityInputEl ? (parseInt(quantityInputEl.value) > 0 ? quantityInputEl.value : '1') : '1';
        const quantityInputHidden = document.createElement('input');
        quantityInputHidden.type = 'hidden';
        quantityInputHidden.name = 'quantity';
        quantityInputHidden.value = quantityValue;
        form.appendChild(quantityInputHidden);
        const buyNowFlagInput = document.createElement('input');
        buyNowFlagInput.type = 'hidden';
        buyNowFlagInput.name = 'buy_now_flag';
        buyNowFlagInput.value = '1';
        form.appendChild(buyNowFlagInput);
        document.body.appendChild(form);
        form.submit();
    }
</script>
<?php
if ($conn) {
    $conn = null;
}
include 'includes/footer.php';
?>