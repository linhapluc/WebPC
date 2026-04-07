<?php
// submit_review.php

session_start();
require 'includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sanpham = $_POST['id_sanpham'] ?? '';
    $sao = $_POST['sao'] ?? '';
    $binh_luan = trim($_POST['binh_luan'] ?? '');

    if ($id_sanpham && $sao && $binh_luan && isset($_SESSION['user_id'])) {
        try {
            $id_danhgia = uniqid('DG');
            $id_khachhang = $_SESSION['user_id'];
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $now = gmdate('Y-m-d H:i:s', time() + 7 * 3600);


            // Ghi vào database
            $stmt = $conn->prepare("INSERT INTO danhgia (id_danhgia, id_sanpham, id_khachhang, sao, binh_luan, ngay_danhgia) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_danhgia, $id_sanpham, $id_khachhang, $sao, $binh_luan, $now]);

            // Ghi session để hiển thị lại đánh giá ngay
            $_SESSION['last_review'] = [
                'id_sanpham' => $id_sanpham,
                'ho_ten' => $_SESSION['user_name'],
                'sao' => $sao,
                'binh_luan' => $binh_luan,
                'ngay_danhgia' => $now
            ];

            $_SESSION['review_success'] = "🎉 Đánh giá của bạn đã được ghi nhận!";
        } catch (PDOException $e) {
            error_log("Lỗi khi thêm đánh giá: " . $e->getMessage());
            $_SESSION['review_error'] = "Đã có lỗi xảy ra. Vui lòng thử lại.";
        }
    } else {
        $_SESSION['review_error'] = "Vui lòng điền đầy đủ thông tin đánh giá.";
    }

    // Quay lại trang chi tiết sản phẩm
    header("Location: chi-tiet-san-pham.php?id=" . urlencode($id_sanpham));
    exit();
} else {
    header("Location: index.php");
    exit();
}
