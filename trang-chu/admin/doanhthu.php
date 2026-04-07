<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Check login
if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return number_format((float)$n, 0, ',', '.') . '₫'; }

function isCanceledStatus($status) {
    $s = mb_strtolower(trim((string)$status), 'UTF-8');
    return (strpos($s, 'hủy') !== false || strpos($s, 'huỷ') !== false);
}
function mapPaymentMethod($raw) {
    $m = mb_strtolower(trim((string)$raw), 'UTF-8');
    $cod = ['cod','cash on delivery','thanh toán khi nhận hàng','thanh toan khi nhan hang','cash'];
    $bank = ['banking','bank transfer','chuyển khoản','chuyen khoan','transfer'];
    if (in_array($m, $cod, true)) return 'Thanh toán khi nhận hàng';
    if (in_array($m, $bank, true)) return 'Chuyển khoản';
    return $raw ?: '—';
}

/* ================== FILTERS ================== */
$today = new DateTime('now');
$defaultTo = $today->format('Y-m-d');
$defaultFrom = (clone $today)->modify('-29 days')->format('Y-m-d'); // 30 ngày gần nhất

$from = $_GET['from'] ?? $defaultFrom;
$to   = $_GET['to']   ?? $defaultTo;

// scope = delivered_only | exclude_canceled | all
$scope = $_GET['scope'] ?? 'delivered_only';

// validate date
$fromObj = DateTime::createFromFormat('Y-m-d', $from) ?: new DateTime($defaultFrom);
$toObj   = DateTime::createFromFormat('Y-m-d', $to)   ?: new DateTime($defaultTo);

// ép from <= to
if ($fromObj > $toObj) {
    $tmp = $fromObj; $fromObj = $toObj; $toObj = $tmp;
}
$from = $fromObj->format('Y-m-d');
$to   = $toObj->format('Y-m-d');

// filter time range inclusive
$fromDT = $from . ' 00:00:00';
$toDT   = $to   . ' 23:59:59';

/* ================== BUILD BASE WHERE ================== */
$where = " WHERE dh.ngay_dat BETWEEN :fromDT AND :toDT ";
$params = [':fromDT' => $fromDT, ':toDT' => $toDT];

if ($scope === 'delivered_only') {
    $where .= " AND dh.trang_thai = 'Đã giao hàng' ";
} elseif ($scope === 'exclude_canceled') {
    $where .= " AND (dh.trang_thai NOT LIKE '%hủy%' AND dh.trang_thai NOT LIKE '%huỷ%') ";
} // all: không filter trạng thái

/* ================== KPI QUERIES ================== */
// 1) Tổng doanh thu, số đơn, AOV
$sqlKpi = "
    SELECT
        COALESCE(SUM(dh.tong_tien),0) AS total_revenue,
        COUNT(*) AS total_orders,
        COALESCE(AVG(dh.tong_tien),0) AS aov
    FROM donhang dh
    $where
";
$stmt = $conn->prepare($sqlKpi);
$stmt->execute($params);
$kpi = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_revenue'=>0,'total_orders'=>0,'aov'=>0];

// 2) Tổng số lượng sản phẩm bán ra (nếu có so_luong)
$sqlQty = "
    SELECT COALESCE(SUM(COALESCE(dh.so_luong,1)),0) AS total_items
    FROM donhang dh
    $where
";
$stmt = $conn->prepare($sqlQty);
$stmt->execute($params);
$totalItems = (int)($stmt->fetchColumn() ?? 0);

// 3) Doanh thu theo ngày (chart)
$sqlTrend = "
    SELECT DATE(dh.ngay_dat) AS d, COALESCE(SUM(dh.tong_tien),0) AS revenue
    FROM donhang dh
    $where
    GROUP BY DATE(dh.ngay_dat)
    ORDER BY d ASC
";
$stmt = $conn->prepare($sqlTrend);
$stmt->execute($params);
$trendRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fill missing dates (để chart mượt)
$trendMap = [];
foreach ($trendRows as $r) $trendMap[$r['d']] = (float)$r['revenue'];

$labels = [];
$values = [];
$cursor = new DateTime($from);
$end = new DateTime($to);
while ($cursor <= $end) {
    $d = $cursor->format('Y-m-d');
    $labels[] = $d;
    $values[] = $trendMap[$d] ?? 0;
    $cursor->modify('+1 day');
}

// 4) Payment breakdown
$sqlPay = "
    SELECT COALESCE(dh.phuongthuc_thanhtoan,'') AS method,
           COUNT(*) AS cnt,
           COALESCE(SUM(dh.tong_tien),0) AS revenue
    FROM donhang dh
    $where
    GROUP BY COALESCE(dh.phuongthuc_thanhtoan,'')
    ORDER BY revenue DESC
";
$stmt = $conn->prepare($sqlPay);
$stmt->execute($params);
$payRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Top sản phẩm theo doanh thu
$sqlTopProducts = "
    SELECT
        dh.id_sanpham,
        COALESCE(sp.ten_sp,'(Sản phẩm không tồn tại)') AS ten_sp,
        COALESCE(sp.hinh_anh,'') AS hinh_anh,
        COUNT(*) AS orders,
        COALESCE(SUM(COALESCE(dh.so_luong,1)),0) AS qty,
        COALESCE(SUM(dh.tong_tien),0) AS revenue
    FROM donhang dh
    LEFT JOIN sanpham sp ON dh.id_sanpham = sp.id_sanpham
    $where
    GROUP BY dh.id_sanpham, sp.ten_sp, sp.hinh_anh
    ORDER BY revenue DESC
    LIMIT 8
";
$stmt = $conn->prepare($sqlTopProducts);
$stmt->execute($params);
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6) Đơn hàng gần đây
$sqlRecent = "
    SELECT
        dh.id_donhang, dh.id_khachhang, dh.id_sanpham,
        dh.ten_nguoinhan, dh.tong_tien, dh.ngay_dat, dh.trang_thai, dh.phuongthuc_thanhtoan,
        COALESCE(sp.ten_sp,'(Sản phẩm không tồn tại)') AS ten_sp,
        COALESCE(sp.hinh_anh,'') AS hinh_anh,
        COALESCE(dh.so_luong,1) AS so_luong
    FROM donhang dh
    LEFT JOIN sanpham sp ON dh.id_sanpham = sp.id_sanpham
    $where
    ORDER BY dh.ngay_dat DESC
    LIMIT 12
";
$stmt = $conn->prepare($sqlRecent);
$stmt->execute($params);
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Doanh thu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">

    <style>
        .kpi-grid{ display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; }
        .kpi-card{
            border: 1px solid rgba(0,0,0,.06);
            border-radius: 14px;
            background:#fff;
            padding: 14px 14px;
        }
        .kpi-top{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .kpi-title{ font-size:.86rem; color:#6c757d; }
        .kpi-value{ font-size:1.35rem; font-weight:700; margin-top:6px; }
        .kpi-sub{ font-size:.85rem; color:#6c757d; margin-top:2px; }
        .kpi-icon{
            width: 40px; height: 40px; border-radius: 12px;
            display:flex; align-items:center; justify-content:center;
            background: rgba(13,110,253,.08);
            color:#0d6efd;
            flex:0 0 auto;
        }

        .report-card{
            border: 1px solid rgba(0,0,0,.06);
            border-radius: 14px;
            background:#fff;
        }
        .report-card-header{
            padding: 14px 16px;
            border-bottom: 1px solid rgba(0,0,0,.06);
            display:flex; align-items:center; justify-content:space-between; gap:12px;
        }
        .report-card-title{ font-weight: 700; }
        .report-card-body{ padding: 16px; }

        .thumb{
            width: 44px; height: 44px; border-radius: 10px;
            object-fit: cover; background:#f1f3f5;
        }

        @media (max-width: 991.98px){
            .kpi-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
        }
        @media (max-width: 575.98px){
            .kpi-grid{ grid-template-columns: 1fr; }
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

        <div class="admin-sidebar-footer">© <?php echo date('Y'); ?> Nasa Shop</div>
    </aside>

    <!-- MAIN -->
    <div class="admin-main">
        <!-- TOPBAR -->
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <div class="admin-page-title">La bàn dữ liệu</div>
            </div>
            <div class="admin-topbar-right">
                <span>👋 Xin chào, <strong><?php echo h($adminName); ?></strong></span>
            </div>
        </header>

        <!-- CONTENT -->
        <main class="admin-content">

            <!-- FILTER BAR -->
            <div class="card-panel mb-3">
                <div class="card-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="card-panel-title">Báo cáo doanh thu</div>
                    <div class="text-muted small">
                        Khoảng: <strong><?php echo h($from); ?></strong> → <strong><?php echo h($to); ?></strong>
                    </div>
                </div>

                <div class="card-panel-body">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label fw-semibold">Từ ngày</label>
                            <input type="date" name="from" class="form-control" value="<?php echo h($from); ?>">
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label fw-semibold">Đến ngày</label>
                            <input type="date" name="to" class="form-control" value="<?php echo h($to); ?>">
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <label class="form-label fw-semibold">Mốc tính doanh thu</label>
                            <select name="scope" class="form-select">
                                <option value="delivered_only" <?php echo $scope==='delivered_only'?'selected':''; ?>>
                                    Chỉ tính đơn “Đã giao hàng”
                                </option>
                                <option value="exclude_canceled" <?php echo $scope==='exclude_canceled'?'selected':''; ?>>
                                    Tính tất cả trừ “Đã hủy”
                                </option>
                                <option value="all" <?php echo $scope==='all'?'selected':''; ?>>
                                    Tính tất cả (kể cả hủy)
                                </option>
                            </select>
                        </div>
                        <div class="col-sm-12 col-md-2 d-grid">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa-solid fa-filter"></i> Áp dụng
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KPI -->
            <div class="kpi-grid mb-3">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-title">Tổng doanh thu</div>
                        <div class="kpi-icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo money($kpi['total_revenue']); ?></div>
                    <div class="kpi-sub">Trong khoảng lọc</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-title">Số đơn</div>
                        <div class="kpi-icon"><i class="fa-regular fa-clipboard"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo (int)$kpi['total_orders']; ?></div>
                    <div class="kpi-sub">Tổng đơn phù hợp bộ lọc</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-title">Giá trị đơn TB (AOV)</div>
                        <div class="kpi-icon"><i class="fa-solid fa-chart-line"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo money($kpi['aov']); ?></div>
                    <div class="kpi-sub">Doanh thu / số đơn</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-title">Số SP bán ra</div>
                        <div class="kpi-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo (int)$totalItems; ?></div>
                    <div class="kpi-sub">Tổng ∑ so_luong (mặc định 1)</div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Chart -->
                <div class="col-lg-8">
                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-title">Doanh thu theo ngày</div>
                            <div class="text-muted small">
                                <?php echo $scope==='delivered_only' ? 'Chỉ đơn Đã giao hàng' : ($scope==='exclude_canceled' ? 'Trừ đơn hủy' : 'Tất cả'); ?>
                            </div>
                        </div>
                        <div class="report-card-body">
                            <canvas id="revenueChart" height="110"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Payment breakdown -->
                <div class="col-lg-4">
                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-title">Theo phương thức thanh toán</div>
                            <div class="text-muted small">Tỷ trọng theo doanh thu</div>
                        </div>
                        <div class="report-card-body">
                            <?php if (empty($payRows)): ?>
                                <div class="text-muted">Chưa có dữ liệu.</div>
                            <?php else: ?>
                                <?php
                                $totalRev = (float)$kpi['total_revenue'];
                                foreach ($payRows as $pr):
                                    $method = mapPaymentMethod($pr['method']);
                                    $rev = (float)$pr['revenue'];
                                    $pct = $totalRev > 0 ? round($rev * 100 / $totalRev, 1) : 0;
                                ?>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <div class="fw-semibold"><?php echo h($method); ?></div>
                                            <div class="text-muted small"><?php echo (int)$pr['cnt']; ?> đơn</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-semibold"><?php echo money($rev); ?></div>
                                            <div class="text-muted small"><?php echo $pct; ?>%</div>
                                        </div>
                                    </div>
                                    <div class="progress mb-3" style="height:8px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $pct; ?>%"></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Top products -->
                <div class="col-lg-6">
                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-title">Top sản phẩm theo doanh thu</div>
                            <div class="text-muted small">Top 8</div>
                        </div>
                        <div class="report-card-body">
                            <?php if (empty($topProducts)): ?>
                                <div class="text-muted">Chưa có dữ liệu sản phẩm.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width:52px;"></th>
                                                <th>Sản phẩm</th>
                                                <th style="width:90px;" class="text-center">SL</th>
                                                <th style="width:140px;" class="text-end">Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topProducts as $p): ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($p['hinh_anh'])): ?>
                                                            <img class="thumb" src="../<?php echo h($p['hinh_anh']); ?>" alt="thumb">
                                                        <?php else: ?>
                                                            <div class="thumb"></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo h($p['ten_sp']); ?></div>
                                                        <div class="text-muted small">
                                                            Mã SP: <?php echo h($p['id_sanpham'] ?: '—'); ?> • <?php echo (int)$p['orders']; ?> đơn
                                                        </div>
                                                    </td>
                                                    <td class="text-center fw-semibold"><?php echo (int)$p['qty']; ?></td>
                                                    <td class="text-end fw-semibold"><?php echo money($p['revenue']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent orders -->
                <div class="col-lg-6">
                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-title">Đơn hàng gần đây</div>
                            <div class="text-muted small">Tối đa 12 đơn</div>
                        </div>
                        <div class="report-card-body">
                            <?php if (empty($recentOrders)): ?>
                                <div class="text-muted">Không có đơn hàng trong khoảng lọc.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width:120px;">Mã đơn</th>
                                                <th>Sản phẩm</th>
                                                <th style="width:140px;">Ngày</th>
                                                <th style="width:130px;" class="text-end">Tổng tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentOrders as $o): ?>
                                                <tr onclick="window.location='edit_donhang.php?id=<?php echo urlencode($o['id_donhang']); ?>'" style="cursor:pointer;">
                                                    <td class="fw-semibold"><?php echo h($o['id_donhang']); ?></td>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo h($o['ten_sp']); ?></div>
                                                        <div class="text-muted small">
                                                            SL: <?php echo (int)$o['so_luong']; ?> • <?php echo h(mapPaymentMethod($o['phuongthuc_thanhtoan'])); ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted">
                                                        <?php echo h(date('d/m/Y', strtotime($o['ngay_dat']))); ?>
                                                    </td>
                                                    <td class="text-end fw-semibold"><?php echo money($o['tong_tien']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div class="text-muted small mt-2">
                                        * Click vào dòng để xem chi tiết đơn hàng.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div><!-- row -->

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
    const values = <?php echo json_encode($values, JSON_UNESCAPED_UNICODE); ?>;

    const ctx = document.getElementById('revenueChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Doanh thu',
                data: values,
                tension: 0.25,
                fill: true
            }]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (c) => {
                            const v = c.raw || 0;
                            return ' ' + v.toLocaleString('vi-VN') + '₫';
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: (v) => v.toLocaleString('vi-VN') + '₫'
                    }
                }
            }
        }
    });
</script>
</body>
</html>
