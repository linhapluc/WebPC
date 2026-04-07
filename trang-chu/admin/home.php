<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}
$adminName = $_SESSION['admin_name'];

// =================== LẤY DỮ LIỆU THỐNG KÊ ===================

// 1. Thẻ thống kê
$today = date('Y-m-d');

// Doanh thu hôm nay (chỉ đơn 'Đã giao hàng')
$stmt_today_revenue = $conn->prepare("
    SELECT SUM(tong_tien) AS total 
    FROM donhang 
    WHERE DATE(ngay_dat) = ? 
      AND trang_thai = 'Đã giao hàng'
");
$stmt_today_revenue->execute([$today]);
$today_revenue = (float)($stmt_today_revenue->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// Đơn hàng trong tháng (từ ngày 1)
$month_start = date('Y-m-01');
$stmt_month_orders = $conn->prepare("
    SELECT COUNT(id_donhang) AS total 
    FROM donhang 
    WHERE ngay_dat >= ?
");
$stmt_month_orders->execute([$month_start]);
$month_orders = (int)($stmt_month_orders->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// Tổng doanh thu (tất cả đơn 'Đã giao hàng')
$stmt_total_revenue = $conn->query("
    SELECT SUM(tong_tien) AS total 
    FROM donhang 
    WHERE trang_thai = 'Đã giao hàng'
");
$total_revenue = (float)($stmt_total_revenue->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// 2. Dữ liệu biểu đồ 7 ngày gần nhất
$seven_days_ago = date('Y-m-d', strtotime('-6 days'));
$stmt_chart = $conn->prepare("
    SELECT DATE(ngay_dat) AS order_date,
           SUM(tong_tien) AS daily_revenue
    FROM donhang
    WHERE ngay_dat >= ?
      AND trang_thai = 'Đã giao hàng'
    GROUP BY DATE(ngay_dat)
    ORDER BY order_date ASC
");
$stmt_chart->execute([$seven_days_ago]);
$chart_data_from_db = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

// Chuẩn hóa đủ 7 ngày
$chart_labels = [];
$chart_values_map = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d/m', strtotime($d));
    $chart_values_map[$d] = 0.0;
}
foreach ($chart_data_from_db as $row) {
    $d = $row['order_date'];
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
                <!-- 🌟 Logo bọc trong thẻ a để click về trang chủ -->
                <a href="home.php" class="admin-sidebar-brand">
                    <div class="admin-sidebar-logo">NS</div>
                    <div class="admin-sidebar-title">NASA Admin</div>
                </a>
            </div>

            <nav class="admin-sidebar-nav">
                <!-- Nhóm 1 -->
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

                <!-- Nhóm 2 -->
                <a href="khuyenmai.php"
                    class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khuyenmai.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Khuyến mãi</span>
                </a>

                <!-- Nhóm 3 -->
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

                <!-- Khách hàng / Đánh giá -->
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
                <a href="live_chat.php" class="admin-nav-item">
                    <i class="fa-solid fa-comments"></i>
                    <span>Chat hỗ trợ</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <!-- Logout -->
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
                    <div class="admin-page-title">Bảng điều khiển</div>
                    <span class="badge-soft">Tổng quan 7 ngày gần nhất</span>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">

                <!-- THẺ THỐNG KÊ -->
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
                            Tính từ ngày <?php echo date('d/m', strtotime($month_start)); ?>
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

                <!-- BIỂU ĐỒ DOANH THU -->
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

    <!-- SCRIPT BIỂU ĐỒ -->
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
                        legend: {
                            display: false
                        },
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