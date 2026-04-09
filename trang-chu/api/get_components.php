<?php
require __DIR__ . '/../includes/connect.php';
header('Content-Type: application/json');

$id_loai = $_GET['id_loai'] ?? '';
$socket = trim($_GET['socket'] ?? ''); // Lấy socket từ CPU truyền sang
$keyword = trim($_GET['keyword'] ?? '');

if (empty($id_loai)) { echo json_encode([]); exit; }

$sql = "SELECT id_sanpham, ten_sp, gia, hinh_anh, socket, wattage, benchmark_score FROM sanpham WHERE id_loai = :id_loai";
$params = [':id_loai' => $id_loai];

// LOGIC LỌC THÔNG MINH
if (!empty($socket)) {
    // Nếu là Mainboard hoặc RAM, tìm sản phẩm có chứa chữ socket đó (Dùng LIKE cho chắc)
     if (in_array($id_loai, ['LSP011', 'LSP012', 'LSP013'])) {
        $sql .= " AND socket LIKE :socket";
        $params[':socket'] = '%' . $socket . '%';
    }
}

if (!empty($keyword)) {
    $sql .= " AND ten_sp LIKE :keyword";
    $params[':keyword'] = '%' . $keyword . '%';
}

$sql .= " ORDER BY gia ASC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}