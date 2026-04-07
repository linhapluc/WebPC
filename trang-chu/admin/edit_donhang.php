<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

$id = $_GET['id'] ?? '';
if ($id === '') {
    header('Location: donhang.php');
    exit();
}

// Lấy thông tin đơn + sản phẩm
$sql = "
    SELECT dh.*,
           sp.ten_sp,
           sp.hinh_anh,
           sp.gia AS gia_sanpham
    FROM donhang dh
    LEFT JOIN sanpham sp ON dh.id_sanpham = sp.id_sanpham
    WHERE dh.id_donhang = ?
";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$donhang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donhang) {
    header('Location: donhang.php');
    exit();
}

// ===== Chuẩn bị dữ liệu =====
$soLuong    = isset($donhang['so_luong']) ? (int)$donhang['so_luong'] : 1;
$tongTien   = (int)($donhang['tong_tien'] ?? 0);
$giaSp      = isset($donhang['gia_sanpham']) ? (int)$donhang['gia_sanpham'] : 0;
$trangThai  = $donhang['trang_thai'] ?: '—';
$pttt       = $donhang['phuongthuc_thanhtoan'] ?: '—';
$ngayDat    = !empty($donhang['ngay_dat']) ? date('d/m/Y H:i', strtotime($donhang['ngay_dat'])) : '—';

function mapPaymentMethodLocal($raw)
{
    $m = mb_strtolower(trim((string)$raw), 'UTF-8');
    if (in_array($m, ['thanh toán khi nhận hàng', 'thanh toan khi nhan hang', 'cod', 'cash on delivery', 'cash'], true)) {
        return 'Thanh toán khi nhận hàng';
    }
    if (in_array($m, ['chuyển khoản', 'chuyen khoan', 'banking', 'bank transfer', 'transfer'], true)) {
        return 'Chuyển khoản';
    }
    return $raw ?: '—';
}

function isCanceledLocal($status)
{
    $s = mb_strtolower(trim((string)$status), 'UTF-8');
    return (strpos($s, 'hủy') !== false || strpos($s, 'huỷ') !== false);
}

function getStatusBadgeClass($status)
{
    if (isCanceledLocal($status)) return 'order-status-canceled';

    switch (trim((string)$status)) {
        case 'Đã giao hàng':
            return 'order-status-completed';
        case 'Đang vận chuyển':
            return 'order-status-shipping';
        case 'Chờ xử lý':
        case 'Pending':
        case 'Chờ thanh toán':
        case 'Đã thanh toán':
        case 'Đã xác nhận':
            return 'order-status-processing';
        default:
            return 'order-status-default';
    }
}

$ptttView    = mapPaymentMethodLocal($pttt);
$statusClass = getStatusBadgeClass($trangThai);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng <?php echo htmlspecialchars($donhang['id_donhang']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">

    <!-- UI polish -->
    <style>
        :root {
            --bg: #f6f7fb;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 10px 24px rgba(17, 24, 39, .06);
            --shadow2: 0 4px 14px rgba(17, 24, 39, .06);
            --radius: 16px;
        }

        .admin-content {
            background: var(--bg);
        }

        /* Card */
        .card-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow2);
        }

        .card-panel-header {
            border-bottom: 1px solid var(--border);
            padding: 14px 16px;
            background: linear-gradient(180deg, rgba(99, 102, 241, .06), rgba(255, 255, 255, 0));
            border-top-left-radius: var(--radius);
            border-top-right-radius: var(--radius);
        }

        .card-panel-title {
            font-weight: 800;
            color: var(--text);
            letter-spacing: .2px;
        }

        .card-panel-body {
            padding: 16px;
        }

        /* Header */
        .order-detail-header {
            padding: 16px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, .85);
            backdrop-filter: blur(6px);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 14px;
        }

        .order-detail-title {
            font-size: 20px;
            font-weight: 900;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-id-chip {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(99, 102, 241, .10);
            color: #4338ca;
            border: 1px solid rgba(99, 102, 241, .22);
            font-weight: 800;
        }

        .order-detail-sub {
            margin-top: 6px;
            font-size: 13px;
            color: var(--muted);
        }

        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Status pill */
        .order-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 6px 12px;
            font-weight: 800;
            font-size: 12px;
            border: 1px solid var(--border);
            background: #fff;
            box-shadow: 0 6px 16px rgba(17, 24, 39, .06);
            white-space: nowrap;
        }

        .order-status-pill::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #9ca3af;
        }

        .order-status-completed::before {
            background: #22c55e;
        }

        .order-status-shipping::before {
            background: #3b82f6;
        }

        .order-status-processing::before {
            background: #f59e0b;
        }

        .order-status-canceled::before {
            background: #ef4444;
        }

        /* Detail list */
        .detail-group {
            padding: 10px 0;
            border-bottom: 1px dashed rgba(229, 231, 235, .95);
        }

        .detail-group:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            opacity: .75;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 650;
            color: var(--text);
            line-height: 1.45;
        }

        /* Product */
        .product-summary-wrapper {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .product-summary-thumb,
        .product-summary-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 14px;
            background: #eef2ff;
            object-fit: cover;
            border: 1px solid var(--border);
            box-shadow: var(--shadow2);
            flex: 0 0 auto;
        }

        .product-summary-title {
            font-weight: 900;
            color: var(--text);
            margin-bottom: 6px;
            line-height: 1.25;
        }

        .product-meta {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .product-kv {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            margin-top: 10px;
        }

        .kv-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
            padding: 8px 10px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(249, 250, 251, .9);
        }

        .kv-row strong {
            color: var(--text);
            font-weight: 800;
        }

        /* Back button row */
        .back-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin: 10px 0 14px;
        }

        @media (max-width: 991.98px) {
            .order-actions {
                justify-content: flex-start;
            }

            .product-summary-thumb,
            .product-summary-placeholder {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="home.php" class="admin-sidebar-brand">
                    <div class="admin-sidebar-logo">NS</div>
                    <div class="admin-sidebar-title">NASA Admin</div>
                </a>
            </div>

            <nav class="admin-sidebar-nav">
                <a href="home.php" class="admin-nav-item">
                    <i class="fa-solid fa-house"></i>
                    <span>Trang chủ</span>
                </a>
                <a href="donhang.php" class="admin-nav-item active">
                    <i class="fa-regular fa-clipboard"></i>
                    <span>Đơn hàng</span>
                </a>
                <a href="sanpham.php" class="admin-nav-item">
                    <i class="fa-solid fa-box"></i>
                    <span>Sản phẩm</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khuyenmai.php" class="admin-nav-item">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Khuyến mãi</span>
                </a>
                <a href="doanhthu.php" class="admin-nav-item">
                    <i class="fa-solid fa-database"></i>
                    <span>La bàn dữ liệu</span>
                </a>
                <a href="qladmin.php" class="admin-nav-item">
                    <i class="fa-regular fa-id-badge"></i>
                    <span>Tình trạng tài khoản</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khachhang.php" class="admin-nav-item">
                    <i class="fa-solid fa-users"></i>
                    <span>Khách hàng</span>
                </a>
                <a href="danhgia.php" class="admin-nav-item">
                    <i class="fa-regular fa-star"></i>
                    <span>Đánh giá</span>
                </a>
                <a href="lienket.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'lienket.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-link"></i>
                    <span>Liên kết</span>
                </a>


                <div class="admin-sidebar-divider"></div>

                <a href="logout.php" class="admin-nav-item admin-sidebar-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Đăng xuất</span>
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
                    <div class="admin-page-title">Chi tiết đơn hàng</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">

                <!-- Header chi tiết -->
                <div class="order-detail-header d-flex align-items-center justify-content-between">
                    <div>
                        <div class="order-detail-title">
                            <span>Đơn hàng</span>
                            <span class="order-id-chip">#<?php echo htmlspecialchars($donhang['id_donhang']); ?></span>
                        </div>
                        <div class="order-detail-sub">
                            <i class="fa-regular fa-calendar"></i>
                            Ngày đặt: <strong><?php echo htmlspecialchars($ngayDat); ?></strong>
                            &nbsp;•&nbsp;
                            <i class="fa-regular fa-user"></i>
                            Khách hàng (ID): <strong><?php echo htmlspecialchars($donhang['id_khachhang']); ?></strong>
                        </div>
                    </div>

                    <div class="order-actions text-end">
                        <span class="order-status-pill <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($trangThai); ?>
                        </span>
                        <div class="small text-muted">
                            <i class="fa-regular fa-credit-card"></i>
                            Thanh toán: <strong><?php echo htmlspecialchars($ptttView); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="back-row">
                    <a href="donhang.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>

                <!-- 3 card ngang -->
                <div class="row g-3 align-items-stretch">

                    <!-- Thông tin đơn -->
                    <div class="col-lg-4 d-flex">
                        <div class="card-panel h-100 w-100">
                            <div class="card-panel-header">
                                <div class="card-panel-title">Thông tin đơn</div>
                            </div>
                            <div class="card-panel-body">

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-regular fa-hashtag"></i> Mã đơn</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($donhang['id_donhang']); ?></div>
                                </div>

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-regular fa-calendar"></i> Ngày đặt</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($ngayDat); ?></div>
                                </div>

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-regular fa-circle-check"></i> Trạng thái</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($trangThai); ?></div>
                                </div>

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-regular fa-credit-card"></i> Phương thức thanh toán</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($ptttView); ?></div>
                                </div>

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-solid fa-sack-dollar"></i> Tổng tiền</div>
                                    <div class="detail-value"><?php echo number_format($tongTien, 0, ',', '.'); ?>₫</div>
                                </div>

                                <?php if (!empty($donhang['ghi_chu'])): ?>
                                    <div class="detail-group">
                                        <div class="detail-label"><i class="fa-regular fa-note-sticky"></i> Ghi chú</div>
                                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($donhang['ghi_chu'])); ?></div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                    <!-- Người nhận & địa chỉ -->
                    <div class="col-lg-4 d-flex">
                        <div class="card-panel h-100 w-100">
                            <div class="card-panel-header">
                                <div class="card-panel-title">Người nhận & địa chỉ</div>
                            </div>
                            <div class="card-panel-body">

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-regular fa-user"></i> Tên người nhận</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($donhang['ten_nguoinhan'] ?? '—'); ?></div>
                                </div>

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-solid fa-phone"></i> Số điện thoại</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($donhang['sdt_nguoinhan'] ?? '—'); ?></div>
                                </div>

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-regular fa-envelope"></i> Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($donhang['email'] ?? '—'); ?></div>
                                </div>

                                <div class="detail-group">
                                    <div class="detail-label"><i class="fa-solid fa-location-dot"></i> Địa chỉ giao hàng</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($donhang['diachi_giaohang'] ?? '—')); ?></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Sản phẩm -->
                    <div class="col-lg-4 d-flex">
                        <div class="card-panel h-100 w-100">
                            <div class="card-panel-header">
                                <div class="card-panel-title">Sản phẩm</div>
                            </div>
                            <div class="card-panel-body">
                                <div class="product-summary-wrapper">
                                    <?php if (!empty($donhang['hinh_anh'])): ?>
                                        <img src="../<?php echo htmlspecialchars($donhang['hinh_anh']); ?>"
                                            alt="Sản phẩm"
                                            class="product-summary-thumb">
                                    <?php else: ?>
                                        <div class="product-summary-placeholder"></div>
                                    <?php endif; ?>

                                    <div style="width:100%;">
                                        <div class="product-summary-title">
                                            <?php echo htmlspecialchars($donhang['ten_sp'] ?? 'Sản phẩm không tồn tại'); ?>
                                        </div>

                                        <div class="product-meta">
                                            <i class="fa-regular fa-id-badge"></i>
                                            Mã SP: <strong><?php echo htmlspecialchars($donhang['id_sanpham'] ?? '—'); ?></strong>
                                        </div>

                                        <div class="product-kv">
                                            <div class="kv-row">
                                                <span>Số lượng</span>
                                                <strong><?php echo $soLuong; ?></strong>
                                            </div>
                                            <div class="kv-row">
                                                <span>Đơn giá</span>
                                                <strong><?php echo $giaSp > 0 ? number_format($giaSp, 0, ',', '.') . '₫' : '—'; ?></strong>
                                            </div>
                                            <div class="kv-row">
                                                <span>Thành tiền</span>
                                                <strong><?php echo number_format($tongTien, 0, ',', '.'); ?>₫</strong>
                                            </div>
                                        </div>

                                    </div>
                                </div>
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