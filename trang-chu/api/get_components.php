<?php
require __DIR__ . '/../includes/connect.php';
header('Content-Type: application/json');

// Lấy tham số
$id_loai = $_GET['id_loai'] ?? '';
$socket = isset($_GET['socket']) ? trim(strtoupper($_GET['socket'])) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if (empty($id_loai)) {
    echo json_encode([]);
    exit;
}

// SQL cơ bản
$sql = "SELECT id_sanpham, ten_sp, gia, hinh_anh, socket 
        FROM sanpham 
        WHERE id_loai = :id_loai";

$params = [':id_loai' => $id_loai];

// =============================
// 🔥 LỌC SOCKET CHUẨN
// =============================

// Áp dụng cho CPU, MAINBOARD, RAM
// =============================
// 🔥 LỌC SOCKET CHUẨN 100%
// =============================
// =============================
// 🔥 LỌC SOCKET CHUẨN 100%
// =============================
if (in_array($id_loai, ['LSP011', 'LSP012', 'LSP013'])) {

    if (!empty($socket) && $socket !== 'UNDEFINED' && $socket !== 'NULL') {

        // chuẩn hóa
        $cleanSocket = strtoupper(trim($socket));

        // loại bỏ khoảng trắng trong DB + split bằng dấu phẩy
        $sql .= " AND (
            FIND_IN_SET(:socket, REPLACE(UPPER(socket), ' ', '')) > 0
        )";

        $params[':socket'] = $cleanSocket;
    }
}

// =============================
// 🔍 SEARCH
// =============================
if (!empty($keyword)) {
    $sql .= " AND ten_sp LIKE :keyword";
    $params[':keyword'] = '%' . $keyword . '%';
}

$sql .= " ORDER BY gia ASC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}