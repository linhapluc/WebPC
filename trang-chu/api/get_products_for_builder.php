<?php
header('Content-Type: application/json');
require '../includes/connect.php';

$response = ['success' => false, 'products' => [], 'message' => '', 'filters' => ['brands' => []]];

if (isset($_GET['id_loai'])) {
    $id_loai_str = trim($_GET['id_loai']);
    $searchKeyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    if (!empty($id_loai_str)) {
        try {
            $sql_products = "SELECT p.id_sanpham, p.ten_sp, p.gia, p.hinh_anh, t.ten_thuonghieu 
                             FROM sanpham p
                             LEFT JOIN thuonghieu t ON p.id_thuonghieu = t.id_thuonghieu
                             WHERE p.id_loai = :id_loai";
            
            $params = [':id_loai' => $id_loai_str];

            if (!empty($searchKeyword)) {
                $sql_products .= " AND p.ten_sp LIKE :keyword";
                $params[':keyword'] = '%' . $searchKeyword . '%';
            }
            
            $sql_products .= " ORDER BY p.ten_sp ASC";

            $stmt_products = $conn->prepare($sql_products);
            $stmt_products->execute($params);
            $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

            


            if ($products) {
                $response['success'] = true;
                $response['products'] = $products;
            } else {
                $response['message'] = 'Không tìm thấy sản phẩm nào cho loại này' . (!empty($searchKeyword) ? ' với từ khóa tìm kiếm.' : '.');
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO trong get_products_for_builder.php: " . $e->getMessage());
            $response['message'] = 'Lỗi truy vấn cơ sở dữ liệu.';
        }
    } else {
        $response['message'] = 'ID loại sản phẩm không hợp lệ.';
    }
} else {
    $response['message'] = 'Thiếu ID loại sản phẩm.';
}

if ($conn) {
    $conn = null;
}

echo json_encode($response);
exit;
?>