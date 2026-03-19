<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}
$adminName = $_SESSION['admin_name'];

/* =================== TIMEZONE SETUP ===================
   Nếu DB của bạn lưu ngay_dat theo UTC:  +00:00  -> +07:00
   Nếu DB đã lưu theo giờ VN: đổi $tzFrom thành '+07:00'
*/
$tzFrom = '+00:00';
$tzTo   = '+07:00';

/* =================== LẤY MỐC THỜI GIAN THEO GIỜ VN (TRONG DB) ===================
   - today_vn: ngày hiện tại theo giờ VN
   - month_start_vn: ngày 1 của tháng hiện tại theo giờ VN
*/
$stmtTime = $conn->query("
    SELECT
      DATE(CONVERT_TZ(NOW(), '$tzFrom', '$tzTo')) AS today_vn,
      DATE_FORMAT(DATE(CONVERT_TZ(NOW(), '$tzFrom', '$tzTo')), '%Y-%m-01') AS month_start_vn
");
$timeRow = $stmtTime->fetch(PDO::FETCH_ASSOC);
$today_vn      = $timeRow['today_vn'];       // yyyy-mm-dd
$month_start_vn = $timeRow['month_start_vn']; // yyyy-mm-01

// =================== 1) DOANH THU HÔM NAY (ĐÃ GIAO) ===================
$stmt_today_revenue = $conn->query("
    SELECT COALESCE(SUM(tong_tien), 0) AS total
    FROM donhang
    WHERE DATE(CONVERT_TZ(ngay_dat, '$tzFrom', '$tzTo')) = '$today_vn'
      AND trang_thai = 'Đã giao hàng'
");
$today_revenue = (float)($stmt_today_revenue->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// =================== 2) ĐƠN HÀNG TRONG THÁNG ===================
// ✅ Đếm distinct để tránh sai về sau
// ✅ Mặc định loại đơn hủy (nếu bạn muốn tính cả đơn hủy thì bỏ 2 dòng NOT LIKE)
$stmt_month_orders = $conn->query("
    SELECT COALESCE(COUNT(DISTINCT id_donhang), 0) AS total
    FROM donhang
    WHERE DATE(CONVERT_TZ(ngay_dat, '$tzFrom', '$tzTo')) >= '$month_start_vn'
      AND trang_thai NOT LIKE '%hủy%'
      AND trang_thai NOT LIKE '%huỷ%'
");
$month_orders = (int)($stmt_month_orders->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// =================== 3) TỔNG DOANH THU (ĐÃ GIAO) ===================
$stmt_total_revenue = $conn->query("
    SELECT COALESCE(SUM(tong_tien), 0) AS total
    FROM donhang
    WHERE trang_thai = 'Đã giao hàng'
");
$total_revenue = (float)($stmt_total_revenue->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// =================== 4) BIỂU ĐỒ 7 NGÀY GẦN NHẤT (ĐÃ GIAO) ===================
// Lấy dữ liệu nhóm theo ngày VN
$stmt_chart = $conn->query("
    SELECT DATE(CONVERT_TZ(ngay_dat, '$tzFrom', '$tzTo')) AS order_date,
           COALESCE(SUM(tong_tien), 0) AS daily_revenue
    FROM donhang
    WHERE DATE(CONVERT_TZ(ngay_dat, '$tzFrom', '$tzTo')) >= DATE_SUB('$today_vn', INTERVAL 6 DAY)
      AND trang_thai = 'Đã giao hàng'
    GROUP BY DATE(CONVERT_TZ(ngay_dat, '$tzFrom', '$tzTo'))
    ORDER BY order_date ASC
");
$chart_data_from_db = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

// Chuẩn hóa đủ 7 ngày (labels + values)
$chart_labels = [];
$chart_values_map = [];

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime($today_vn . " -$i days"));
    $chart_labels[] = date('d/m', strtotime($d));
    $chart_values_map[$d] = 0.0;
}

foreach ($chart_data_from_db as $row) {
    $d = $row['order_date']; // yyyy-mm-dd
    if (isset($chart_values_map[$d])) {
        $chart_values_map[$d] = (float)$row['daily_revenue'];
    }
}
$chart_values_final = array_values($chart_values_map);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Bảng điều khiển Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- CSS admin -->
    <link rel="stylesheet" href="admin.css">
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
                <a href="home.php"
                   class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'home.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i>
                    <span>Trang chủ</span>
                </a>
                <a href="donhang.php"
                   class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'donhang.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-clipboard"></i>
                    <span>Đơn hàng</span>
                </a>
                <a href="sanpham.php"
                   class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'sanpham.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-box"></i>
                    <span>Sản phẩm</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khuyenmai.php"
                   class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khuyenmai.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Khuyến mãi</span>
                </a>

                <a href="doanhthu.php"
                   class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'doanhthu.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-database"></i>
                    <span>La bàn dữ liệu</span>
                </a>
                <a href="qladmin.php"
                   class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'qladmin.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-id-badge"></i>
                    <span>Tình trạng tài khoản</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khachhang.php"
                   class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khachhang.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Khách hàng</span>
                </a>
                <a href="danhgia.php"
                   class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'danhgia.php' ? 'active' : ''; ?>">
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
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <div class="admin-page-title">Bảng điều khiển</div>
                    <span class="badge-soft">Tổng quan 7 ngày gần nhất</span>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <main class="admin-content">
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-card-label">Doanh thu hôm nay</div>
                        <div class="stat-card-value">
                            <?php echo number_format($today_revenue, 0, ',', '.'); ?>₫
                        </div>
                        <div class="stat-card-meta">Chỉ tính đơn hàng đã giao</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-label">Đơn hàng trong tháng</div>
                        <div class="stat-card-value">
                            <?php echo $month_orders; ?>
                        </div>
                        <div class="stat-card-meta">
                            Tính từ ngày <?php echo date('d/m', strtotime($month_start_vn)); ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-label">Tổng doanh thu</div>
                        <div class="stat-card-value">
                            <?php echo number_format($total_revenue, 0, ',', '.'); ?>₫
                        </div>
                        <div class="stat-card-meta">Tất cả đơn đã giao hàng</div>
                    </div>
                </div>

                <div class="card-panel">
                    <div class="card-panel-header">
                        <div class="card-panel-title">Doanh thu 7 ngày gần nhất</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');

            const chartLabels = <?php echo json_encode($chart_labels); ?>;
            const chartValues = <?php echo json_encode($chart_values_final); ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Doanh thu (VND)',
                        data: chartValues,
                        backgroundColor: 'rgba(255, 0, 80, 0.15)',
                        borderColor: 'rgba(255, 0, 80, 1)',
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000) + 'tr';
                                    if (value >= 1000) return (value / 1000) + 'k';
                                    return value.toLocaleString('vi-VN') + '₫';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = 'Doanh thu: ';
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toLocaleString('vi-VN') + '₫';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>
