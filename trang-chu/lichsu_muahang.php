<?php
// trang-chu/lichsu_muahang.php

include 'includes/header.php';
require 'includes/connect.php'; // Đảm bảo file connect.php nằm trong includes

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap.php');
    exit;
}

// 2. Lấy đơn hàng của khách hàng từ CSDL
$user_id = $_SESSION['user_id'];
$orders = [];
try {
    $stmt = $conn->prepare("SELECT * FROM donhang WHERE id_khachhang = ? ORDER BY ngay_dat DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Xử lý lỗi nếu có
    error_log("Lỗi truy vấn lịch sử đơn hàng: " . $e->getMessage());
}

include 'includes/main_navigation.php';
?>

<main style="padding: 40px 0;">
    <div class="container">
        <h1>Lịch Sử Mua Hàng</h1>
        <hr style="margin: 15px 0 30px 0;">

        <?php if (isset($_SESSION['cancel_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['cancel_message']; unset($_SESSION['cancel_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['cancel_error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['cancel_error']; unset($_SESSION['cancel_error']); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Mã Đơn Hàng</th>
                        <th>Ngày Đặt</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th style="width: 150px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Bạn chưa có đơn hàng nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['id_donhang']); ?></strong></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['ngay_dat']))); ?></td>
                                <td><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>₫</td>
                                <td><?php echo htmlspecialchars($order['trang_thai']); ?></td>
                                <td>
                                    <?php
                                    $trang_thai = $order['trang_thai'];
                                    $id_donhang = htmlspecialchars($order['id_donhang']);

                                    // Chỉ cho phép hủy khi đơn hàng ở trạng thái 'Chờ xử lý' hoặc 'Chờ thanh toán'
                                    if ($trang_thai == 'Chờ xử lý' || $trang_thai == 'Chờ thanh toán') {
                                        echo "<a href='huy_donhang.php?id_donhang={$id_donhang}' class='btn btn-sm btn-danger' 
                                                onclick='return confirm(\"Bạn có chắc chắn muốn hủy đơn hàng này không?\");'>
                                                Hủy đơn
                                              </a>";
                                    } else {
                                        // Với các trạng thái khác, có thể hiển thị nút "Xem chi tiết"
                                        echo "<a href='chitiet_donhang.php?id_donhang={$id_donhang}' class='btn btn-sm btn-info'>Xem</a>";
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'chatbox.php'; ?>
<?php
include 'includes/footer.php';
?>