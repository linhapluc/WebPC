<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

/* ================== FILTER & TÌM KIẾM ================== */

$search       = trim($_GET['search'] ?? '');
$filterStatus = $_GET['status'] ?? 'all';

/* ===== Helper: kiểm tra trạng thái hủy ===== */
function isCanceledStatus($status)
{
    $s = trim((string)$status);
    $s_lower = mb_strtolower($s, 'UTF-8');
    return (strpos($s_lower, 'hủy') !== false || strpos($s_lower, 'huỷ') !== false);
}

/* ===== Helper class màu trạng thái ===== */
function getOrderStatusClass($status)
{
    $status = trim((string)$status);

    if (isCanceledStatus($status)) {
        return 'order-status-canceled';
    }

    switch ($status) {
        case 'Chờ thanh toán':
        case 'Pending':
            return 'order-status-pending-pay';

        case 'Chờ xử lý':
        case 'Đã thanh toán':
        case 'Đã xác nhận':
            return 'order-status-processing';

        case 'Đang vận chuyển':
            return 'order-status-shipping';

        case 'Đã giao hàng':
            return 'order-status-completed';

        default:
            return 'order-status-default';
    }
}

/* ===== Helper: chuẩn hóa phương thức thanh toán ===== */
function mapPaymentMethod($raw)
{
    $m = mb_strtolower(trim((string)$raw), 'UTF-8');

    $codValues = [
        'cod',
        'cash on delivery',
        'thanh toán khi nhận hàng',
        'thanh toan khi nhan hang',
        'cash'
    ];

    $bankValues = [
        'banking',
        'bank transfer',
        'chuyển khoản',
        'chuyen khoan',
        'transfer'
    ];

    if (in_array($m, $codValues, true)) {
        return 'Thanh toán khi nhận hàng';
    }

    if (in_array($m, $bankValues, true)) {
        return 'Chuyển khoản';
    }

    if ($m === 'thanh toán khi nhận hàng' || $m === 'thanh toan khi nhan hang') {
        return 'Thanh toán khi nhận hàng';
    }
    if ($m === 'chuyển khoản' || $m === 'chuyen khoan') {
        return 'Chuyển khoản';
    }

    return $raw ?: '—';
}

/* ===== Đếm số lượng đơn theo trạng thái để hiển thị tab ===== */

$statusCounts = [
    'all'             => 0,
    'pending_payment' => 0,
    'to_ship'         => 0,
    'shipping'        => 0,
    'completed'       => 0,
    'canceled'        => 0,
];

$stmtCount = $conn->query("SELECT trang_thai, COUNT(*) AS total FROM donhang GROUP BY trang_thai");
while ($row = $stmtCount->fetch(PDO::FETCH_ASSOC)) {
    $count  = (int)$row['total'];
    $status = trim($row['trang_thai']);

    $statusCounts['all'] += $count;

    switch ($status) {
        case 'Chờ thanh toán':
        case 'Pending':
            $statusCounts['pending_payment'] += $count;
            break;
        case 'Chờ xử lý':
        case 'Đã thanh toán':
        case 'Đã xác nhận':
            $statusCounts['to_ship'] += $count;
            break;
        case 'Đang vận chuyển':
            $statusCounts['shipping'] += $count;
            break;
        case 'Đã giao hàng':
            $statusCounts['completed'] += $count;
            break;
    }

    if (isCanceledStatus($status)) {
        $statusCounts['canceled'] += $count;
    }
}

/* ================== LẤY DANH SÁCH ĐƠN ================== */

$sql = "
    SELECT dh.*,
           sp.ten_sp,
           sp.hinh_anh
    FROM donhang dh
    LEFT JOIN sanpham sp ON dh.id_sanpham = sp.id_sanpham
    WHERE 1
";

$params = [];

/* ✅ FIX: không reuse placeholder :search (PDO có thể lỗi khi dùng trùng tên)
   + CAST để LIKE ổn định khi id là INT
*/
if ($search !== '') {
    $sql .= " AND (
                CAST(dh.id_donhang AS CHAR) LIKE :s1
                OR CAST(dh.id_khachhang AS CHAR) LIKE :s2
                OR dh.ten_nguoinhan LIKE :s3
              )";
    $kw = "%$search%";
    $params[':s1'] = $kw;
    $params[':s2'] = $kw;
    $params[':s3'] = $kw;
}

switch ($filterStatus) {
    case 'pending_payment':
        $sql .= " AND (dh.trang_thai = 'Chờ thanh toán' OR dh.trang_thai = 'Pending')";
        break;
    case 'to_ship':
        $sql .= " AND dh.trang_thai IN ('Chờ xử lý','Đã thanh toán','Đã xác nhận')";
        break;
    case 'shipping':
        $sql .= " AND dh.trang_thai = 'Đang vận chuyển'";
        break;
    case 'completed':
        $sql .= " AND dh.trang_thai = 'Đã giao hàng'";
        break;
    case 'canceled':
        $sql .= " AND (dh.trang_thai LIKE '%hủy%' OR dh.trang_thai LIKE '%huỷ%')";
        break;
        // all => không filter
}

$sql .= " ORDER BY dh.ngay_dat DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$donhangs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- CSS admin chung -->
    <link rel="stylesheet" href="admin.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.order-search');
            const input = form?.querySelector('input[name="search"]');

            if (!form || !input) return;

            form.addEventListener('submit', function(e) {
                const val = input.value.trim();

                if (val === '') {
                    e.preventDefault();

                    const url = new URL(window.location.href);

                    // ✅ về full danh sách: bỏ search + bỏ status
                    url.searchParams.delete('search');
                    url.searchParams.delete('status');

                    window.location.href = url.toString();
                }
                // có chữ → submit bình thường
            });
        });
    </script>


</head>

<body>
    <div class="admin-layout">
        <!-- SIDEBAR giống home.php -->
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
            <!-- TOPBAR -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <div class="admin-page-title">Đơn hàng</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <!-- CONTENT -->
            <main class="admin-content">

                <div class="card-panel">
                    <div class="card-panel-header">
                        <div class="card-panel-title">Quản lý đơn hàng</div>

                        <!-- Search -->
                        <form method="get" class="order-search">
                            <div class="input-group input-group-sm">
                                <input type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Tìm mã đơn, khách hàng, người nhận..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                                <?php if ($filterStatus !== 'all'): ?>
                                    <input type="hidden" name="status"
                                        value="<?php echo htmlspecialchars($filterStatus); ?>">
                                <?php endif; ?>
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tabs trạng thái -->
                    <div class="order-tabs">
                        <?php
                        $tabs = [
                            'all'             => 'Tất cả',
                            'pending_payment' => 'Chờ thanh toán',
                            'to_ship'         => 'Chờ xử lý / giao',
                            'shipping'        => 'Đang vận chuyển',
                            'completed'       => 'Đã giao',
                            'canceled'        => 'Đã hủy',
                        ];
                        foreach ($tabs as $key => $label): ?>
                            <a href="donhang.php?status=<?php echo $key;
                                                        echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>"
                                class="order-tab <?php echo $filterStatus === $key ? 'active' : ''; ?>">
                                <span><?php echo $label; ?></span>
                                <span class="order-tab-count"><?php echo $statusCounts[$key]; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- BẢNG ĐƠN HÀNG -->
                    <div class="table-responsive mt-2">
                        <table class="table table-modern table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Mã đơn</th>
                                    <th style="width: 120px;">Khách hàng</th>
                                    <th style="width: 260px;">Sản phẩm</th>
                                    <th style="width: 90px;">Số lượng</th>
                                    <th>Người nhận & địa chỉ</th>
                                    <th style="width: 140px;">Ngày đặt</th>
                                    <th style="width: 140px;">Tổng tiền</th>
                                    <th style="width: 140px;">Trạng thái</th>
                                    <th style="width: 160px;">Thanh toán</th>
                                    <th style="width: 220px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($donhangs)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            Không có đơn hàng nào phù hợp bộ lọc.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($donhangs as $dh): ?>
                                        <?php
                                        $id_donhang      = htmlspecialchars($dh['id_donhang']);
                                        $trang_thai_raw  = $dh['trang_thai'];
                                        $trang_thai      = trim($trang_thai_raw);
                                        $statusClass     = getOrderStatusClass($trang_thai);
                                        $img             = $dh['hinh_anh'] ?? null;
                                        $productName     = $dh['ten_sp'] ?? 'Sản phẩm không tồn tại';
                                        $isCanceled      = isCanceledStatus($trang_thai);
                                        $soLuong         = isset($dh['so_luong']) ? (int)$dh['so_luong'] : 1;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo $id_donhang; ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($dh['id_khachhang']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="order-product">
                                                    <?php if (!empty($img)): ?>
                                                        <img src="../<?php echo htmlspecialchars($img); ?>"
                                                            alt="SP"
                                                            class="order-product-thumb">
                                                    <?php else: ?>
                                                        <div class="order-product-thumb no-img"></div>
                                                    <?php endif; ?>

                                                    <div class="order-product-info">
                                                        <div class="order-product-main">
                                                            <?php echo htmlspecialchars($productName); ?>
                                                        </div>
                                                        <?php if (!empty($dh['id_sanpham'])): ?>
                                                            <div class="order-product-sub text-muted">
                                                                Mã SP: <?php echo htmlspecialchars($dh['id_sanpham']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-center">
                                                    <?php echo $soLuong; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="order-recipient">
                                                    <div class="fw-semibold">
                                                        <?php echo htmlspecialchars($dh['ten_nguoinhan']); ?>
                                                    </div>
                                                    <?php if (!empty($dh['diachi_giaohang'])): ?>
                                                        <div class="order-recipient-address">
                                                            <?php echo htmlspecialchars($dh['diachi_giaohang']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($dh['ngay_dat']))); ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?php echo number_format($dh['tong_tien'], 0, ',', '.'); ?>₫
                                                </div>
                                            </td>
                                            <td>
                                                <span class="order-status-pill <?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($trang_thai ?: '—'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars(mapPaymentMethod($dh['phuongthuc_thanhtoan'])); ?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($isCanceled):
                                                ?>
                                                    <a href="edit_donhang.php?id=<?php echo $id_donhang; ?>"
                                                        class="btn btn-sm btn-outline-secondary">
                                                        Xem thông tin đơn hàng
                                                    </a>
                                                <?php
                                                elseif (in_array($trang_thai, ['Chờ xử lý', 'Pending', 'Chờ thanh toán', 'Đã thanh toán', 'Đã xác nhận'], true)):
                                                ?>
                                                    <a href="capnhat_trangthai.php?id_donhang=<?php echo $id_donhang; ?>&action=confirm"
                                                        class="btn btn-sm btn-success"
                                                        onclick="return confirm('Xác nhận đơn hàng này và chuyển sang trạng thái ĐANG VẬN CHUYỂN?');">
                                                        Xác nhận đơn hàng
                                                    </a>
                                                <?php
                                                elseif ($trang_thai === 'Đang vận chuyển'):
                                                ?>
                                                    <a href="capnhat_trangthai.php?id_donhang=<?php echo $id_donhang; ?>&action=shipping_done"
                                                        class="btn btn-sm btn-primary"
                                                        onclick="return confirm('Xác nhận đơn hàng này ĐÃ GIAO HÀNG và hoàn tất?');">
                                                        Vận chuyển đơn hàng
                                                    </a>
                                                <?php
                                                else:
                                                ?>
                                                    <a href="edit_donhang.php?id=<?php echo $id_donhang; ?>"
                                                        class="btn btn-sm btn-outline-secondary">
                                                        Xem thông tin đơn hàng
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>