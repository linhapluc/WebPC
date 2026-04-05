<?php
// includes/main_navigation.php
$currentTotalQuantity = $totalQuantity ?? 0;
$currentCartPageUrl = $cart_page_url ?? 'dangnhap.php';
?>
<div class="top-bar">
    <div class="container top-bar-content">
        <ul class="top-bar-links">
       
            <li><a href="tragop.php"><i class="fa-solid fa-credit-card"></i> Trả góp</a></li>
            
            <li><a href="build_pc.php"><i class="fa-solid fa-wrench"></i> Xây dựng cấu hình</a></li>
            <li><a href="chinh-sach-bao-hanh.php"><i class="fa-solid fa-shield-halved"></i> Chính sách bảo hành</a></li>
        </ul>
        <div class="top-bar-hotline">
            <a href="tel:19001155"><i class="fa-solid fa-phone-volume"></i> Hotline: <strong>1900 1155</strong></a>
            <span class="top-bar-notice">*Hoạt động bình thường từ 02/05</span>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="container header-content">
        <div class="logo">
            <a href="index.php">PC Shop Nasa</a>
        </div>
        <button class="category-toggle-btn" id="category-toggle" aria-label="Toggle Categories" aria-expanded="false">
            <i class="fa-solid fa-bars"></i>
            <span>Danh mục</span>
        </button>
        <form action="search.php" method="GET" class="search-bar">
            <input type="text" name="keyword" placeholder="Tìm kiếm sản phẩm..." required>
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Tìm</button>
        </form>

        <div class="user-actions">
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])): ?>
                <div class="dropdown-container">
                    <a href="#" class="dropdown-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-regular fa-user"></i>
                        Chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        <i class="fa-solid fa-caret-down icon-caret"></i>
                    </a>
                    <ul class="dropdown-menu-content" role="menu">
                      
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="dangxuat.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="dropdown-container">
                    <a href="#" class="dropdown-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-regular fa-user"></i> Tài khoản
                        <i class="fa-solid fa-caret-down icon-caret"></i>
                    </a>
                    <ul class="dropdown-menu-content" role="menu">
                        <li><a class="dropdown-item" href="dangnhap.php">Đăng nhập</a></li>
                        <li><a class="dropdown-item" href="dangky.php">Đăng ký</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <a href="<?php echo htmlspecialchars($currentCartPageUrl); ?>" class="cart-link">
                <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
                (<?php echo $currentTotalQuantity; ?>)
            </a>
        </div>
    </div>
</header>