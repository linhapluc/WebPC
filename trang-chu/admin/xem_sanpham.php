<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

// Kiểm tra tham số ID
if (empty($_GET['id'])) {
    echo "❌ Thiếu ID sản phẩm.";
    exit;
}
$id = $_GET['id'];

// Truy vấn thông tin sản phẩm
$stmt = $conn->prepare("
    SELECT s.*, l.ten_loaisp, b.thoi_gian_bh, b.mo_ta_bh
    FROM sanpham s
    JOIN loaisanpham l ON s.id_loai = l.id_loaisp
    LEFT JOIN baohanh b ON s.id_sanpham = b.id_sanpham
    WHERE s.id_sanpham = ?
");
$stmt->execute([$id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra sản phẩm tồn tại
if (!$sp) {
    echo "❌ Sản phẩm không tồn tại.";
    exit;
}

// ====== Xử lý đường dẫn ảnh (giữ như bạn) ======
$filename = htmlspecialchars($sp['hinh_anh']); // tên file ảnh từ CSDL
$path_assets = "/webpc/trang-chu/assets/images/$filename";
$path_root   = "/webpc/trang-chu/$filename";

// Kiểm tra file thật trên máy chủ
$real_path_assets = __DIR__ . '/../assets/images/' . $filename;
$real_path_root   = __DIR__ . '/../' . $filename;

if (!empty($filename) && file_exists($real_path_assets)) {
    $sp['image_path'] = $path_assets;
} elseif (!empty($filename) && file_exists($real_path_root)) {
    $sp['image_path'] = $path_root;
} else {
    $sp['image_path'] = '/webpc/trang-chu/assets/images/default.png';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">

    <style>
        /* Card chi tiết giống tone admin */
        .product-grid {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 18px;
        }

        @media (max-width: 992px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }

        .product-photo-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
            padding: 18px;
        }

        .product-photo {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 14px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-photo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
        }

        .product-info-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
            padding: 18px;
        }

        .product-title {
            font-weight: 800;
            font-size: 22px;
            margin: 0;
        }

        .product-meta {
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        @media (max-width: 576px) {
            .product-meta {
                grid-template-columns: 1fr;
            }
        }

        .meta-item {
            border: 1px dashed rgba(0, 0, 0, 0.12);
            border-radius: 14px;
            padding: 12px;
            background: #fff;
        }

        .meta-label {
            color: rgba(0, 0, 0, 0.55);
            font-size: 13px;
            margin-bottom: 2px;
        }

        .meta-value {
            font-weight: 700;
        }

        .price-value {
            color: #dc3545;
            font-size: 18px;
        }

        .tab-shell {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
            padding: 14px;
        }

        .nav-tabs .nav-link {
            border-radius: 12px 12px 0 0;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <!-- SIDEBAR (giống home.php/donhang.php) -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="home.php" class="admin-sidebar-brand">
                    <div class="admin-sidebar-logo">NS</div>
                    <div class="admin-sidebar-title">NASA Admin</div>
                </a>
            </div>

            <nav class="admin-sidebar-nav">
                <a href="home.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'home.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i><span>Trang chủ</span>
                </a>
                <a href="donhang.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'donhang.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-clipboard"></i><span>Đơn hàng</span>
                </a>
                <a href="sanpham.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'sanpham.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-box"></i><span>Sản phẩm</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khuyenmai.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khuyenmai.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bullhorn"></i><span>Khuyến mãi</span>
                </a>

                <a href="doanhthu.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'doanhthu.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-database"></i><span>La bàn dữ liệu</span>
                </a>
                <a href="qladmin.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'qladmin.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-id-badge"></i><span>Tình trạng tài khoản</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khachhang.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khachhang.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i><span>Khách hàng</span>
                </a>
                <a href="danhgia.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'danhgia.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-star"></i><span>Đánh giá</span>
                </a>
                <a href="lienket.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'lienket.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-link"></i><span>Liên kết</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="logout.php" class="admin-nav-item admin-sidebar-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i><span>Đăng xuất</span>
                </a>
            </nav>

            <div class="admin-sidebar-footer">
                © <?php echo date('Y'); ?> Nasa Shop
            </div>
        </aside>

        <!-- MAIN -->
        <div class="admin-main">
            <!-- TOPBAR -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <div class="admin-page-title">Chi tiết sản phẩm</div>
                    <span class="badge-soft">Thông tin & mô tả</span>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">

                <div class="card-panel">
                    <div class="card-panel-header">
                        <div class="card-panel-title">Thông tin sản phẩm</div>
                        <div class="d-flex gap-2">
                            <a href="sanpham.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại
                            </a>
                            <a href="edit_sanpham.php?id=<?php echo urlencode($sp['id_sanpham']); ?>" class="btn btn-sm btn-primary">
                                <i class="fa-regular fa-pen-to-square"></i> Sửa sản phẩm
                            </a>
                        </div>
                    </div>

                    <div class="product-grid">
                        <!-- Ảnh -->
                        <div class="product-photo-card">
                            <div class="product-photo">
                                <img src="<?php echo htmlspecialchars($sp['image_path']); ?>"
                                     alt="<?php echo htmlspecialchars($sp['ten_sp']); ?>">
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="product-info-card">
                            <h2 class="product-title"><?php echo htmlspecialchars($sp['ten_sp']); ?></h2>

                            <div class="product-meta">
                                <div class="meta-item">
                                    <div class="meta-label">Mã SP</div>
                                    <div class="meta-value"><?php echo htmlspecialchars($sp['id_sanpham']); ?></div>
                                </div>

                                <div class="meta-item">
                                    <div class="meta-label">Loại</div>
                                    <div class="meta-value"><?php echo htmlspecialchars($sp['ten_loaisp']); ?></div>
                                </div>

                                <div class="meta-item">
                                    <div class="meta-label">Giá</div>
                                    <div class="meta-value price-value">
                                        <?php echo number_format((int)$sp['gia'], 0, ',', '.'); ?>₫
                                    </div>
                                </div>

                                <div class="meta-item">
                                    <div class="meta-label">Số lượng</div>
                                    <div class="meta-value"><?php echo (int)$sp['sl']; ?></div>
                                </div>

                                <div class="meta-item">
                                    <div class="meta-label">Bảo hành</div>
                                    <div class="meta-value">
                                        <?php echo !empty($sp['thoi_gian_bh']) ? htmlspecialchars($sp['thoi_gian_bh']) . " tháng" : "—"; ?>
                                    </div>
                                </div>

                                <div class="meta-item">
                                    <div class="meta-label">Mô tả bảo hành</div>
                                    <div class="meta-value">
                                        <?php echo !empty($sp['mo_ta_bh']) ? htmlspecialchars($sp['mo_ta_bh']) : "—"; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="tab-shell mt-3">
                        <ul class="nav nav-tabs mb-3" id="productTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">
                                    Mô tả sản phẩm
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">
                                    Thông số kỹ thuật
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content p-3" id="productTabContent">
                            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                                <p class="mb-0">
                                    <?php echo !empty($sp['mo_ta']) ? nl2br(htmlspecialchars($sp['mo_ta'])) : 'Sản phẩm hiện chưa có mô tả chi tiết.'; ?>
                                </p>
                            </div>

                            <div class="tab-pane fade" id="specs" role="tabpanel">
                                <?php $specs = !empty($sp['thong_so_ky_thuat']) ? explode("\n", $sp['thong_so_ky_thuat']) : []; ?>

                                <?php if (!empty($specs)): ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($specs as $line): ?>
                                            <?php $line = trim($line); if ($line === '') continue; ?>
                                            <li class="list-group-item"><?php echo htmlspecialchars($line); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="mb-0">Thông số kỹ thuật đang được cập nhật.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
