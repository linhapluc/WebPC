<?php
// Trong file: WebPC/admin/index.php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu người dùng không phải là admin thì chuyển về trang đăng nhập
if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}

// Xử lý xóa loại sản phẩm
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Kiểm tra xem loại sản phẩm có tồn tại không
    $stmt = $conn->prepare("SELECT * FROM loaisanpham WHERE id = ?");
    $stmt->execute([$id]);
    $loaisanpham = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($loaisanpham) {
        // Xóa loại sản phẩm
        $stmt = $conn->prepare("DELETE FROM loaisanpham WHERE id = ?");
        $stmt->execute([$id]);

        // Chuyển hướng về trang quản lý loại sản phẩm sau khi xóa thành công
        header("Location: loaisanpham.php");
        exit();
    } else {
        // Nếu loại sản phẩm không tồn tại
        header("Location: loaisanpham.php");
        exit();
    }
} else {
    // Nếu không có id, chuyển hướng về trang danh sách
    header("Location: loaisanpham.php");
    exit();
}
?>
