<?php //Thêm//
// order_success.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php'; 


$order_id_display = '';
if (isset($_GET['order_id']) && filter_var($_GET['order_id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
    $order_id_display = "DH" . str_pad((int)$_GET['order_id'], 5, '0', STR_PAD_LEFT);
}
?>
<main style="padding: 50px 0; text-align: center;">
    <div class="container">
        <div class="alert alert-success" style="font-size: 1.2em; padding: 30px; background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: .25rem;">
            <h4 class="alert-heading" style="font-size: 1.5em; margin-bottom: 15px;">Đặt hàng thành công!</h4>
            <?php
            if (isset($_SESSION['order_success_message'])) {
                echo '<p>' . htmlspecialchars($_SESSION['order_success_message']) . '</p>';
                unset($_SESSION['order_success_message']);
            } elseif (!empty($order_id_display)) {
                echo '<p>Cảm ơn bạn đã mua hàng. Mã đơn hàng của bạn là: <strong>' . htmlspecialchars($order_id_display) . '</strong>. Chúng tôi sẽ liên hệ với bạn sớm để xác nhận.</p>';
            } else {
                 echo '<p>Cảm ơn bạn đã mua hàng! Chúng tôi sẽ liên hệ với bạn sớm để xác nhận đơn hàng.</p>';
            }
            ?>
            <hr style="margin: 20px 0;">
            <p class="mb-0">Bạn có thể xem lại đơn hàng trong <a href="order_history.php" style="color: #0f5132; font-weight: bold;">Lịch sử mua hàng</a>.</p>
        </div>
        <a href="index.php" class="btn btn-primary" style="margin-top: 20px; padding: 10px 20px; font-size: 1.1em;">Tiếp tục mua sắm</a>
    </div>
</main>
<?php
include 'includes/footer.php';
?>