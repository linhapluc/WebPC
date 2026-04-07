<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra tham số ID
if (empty($_GET['id'])) {
    echo "❌ Thiếu ID sản phẩm.";
    exit;
}

$id = $_GET['id'];

// Truy vấn thông tin sản phẩm
$stmt = $conn->prepare("SELECT s.*, l.ten_loaisp, b.thoi_gian_bh, b.mo_ta_bh
                        FROM sanpham s 
                        JOIN loaisanpham l ON s.id_loai = l.id_loaisp 
                        LEFT JOIN baohanh b ON s.id_sanpham = b.id_sanpham
                        WHERE s.id_sanpham = ?");

$stmt->execute([$id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra sản phẩm tồn tại
if (!$sp) {
    echo "❌ Sản phẩm không tồn tại.";
    exit;
}

// Xử lý đường dẫn ảnh
$filename = htmlspecialchars($sp['hinh_anh']); // tên file ảnh từ CSDL
$path_assets = "/webpc/trang-chu/assets/images/$filename";
$path_root   = "/webpc/trang-chu/$filename";

// Kiểm tra file thật trên máy chủ
$real_path_assets = __DIR__ . '/../assets/images/' . $filename;
$real_path_root   = __DIR__ . '/../' . $filename;

if (file_exists($real_path_assets)) {
    $sp['image_path'] = $path_assets;
} elseif (file_exists($real_path_root)) {
    $sp['image_path'] = $path_root;
} else {
    $sp['image_path'] = '/webpc/trang-chu/assets/images/default.png'; // fallback ảnh mặc định nếu mất
}
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Wrapper chính */
        .product-wrapper {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            gap: 40px;
            margin: 40px auto;
            max-width: 1200px;
            padding: 0 20px;
        }

        /* Container hình ảnh */
        .product-image-container {
            flex: 1;
            max-width: 500px;
            padding: 20px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .product-image-container img {
            width: 100%;
            max-height: 600px;
            object-fit: contain;
        }

        /* Container thông tin sản phẩm */
        .product-info-container {
            flex: 1;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Khu vực mô tả và thông số kỹ thuật */
        .product-description {
            margin-top: 40px;
            max-width: 1200px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            line-height: 1.6;
        }

        .product-description h4 {
            margin-bottom: 10px;
            color: #007bff;
        }

        /* Link quay lại */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 5px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(0, 86, 179, 0.3);
        }

        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar {
            background-color: #007bff;
            padding: 16px 24px;
        }

        .navbar-brand {
            font-weight: bold;
            color: white;
            font-size: 24px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-primary">
        <div class="container-fluid d-flex align-items-center">
            <a href="sanpham.php" class="navbar-brand text-white fs-4 fw-bold text-decoration-none">
                Trang Quản Trị Admin
            </a>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </span>
        </div>
    </nav>

    <div class="container">
        <div class="product-wrapper">
            <div class="product-image-container">
                <img src="<?= $sp['image_path'] ?>" alt="<?= htmlspecialchars($sp['ten_sp']) ?>" style="max-width: 300px;">
            </div>

            <div class="product-info-container">
                <h2 class="fw-bold mb-3"><?= htmlspecialchars($sp['ten_sp']) ?></h2>
                <p><strong>Mã SP:</strong> <?= htmlspecialchars($sp['id_sanpham']) ?></p>
                <?php if (!empty($sp['thoi_gian_bh'])): ?>
                    <p><strong>Bảo hành:</strong> <?= htmlspecialchars($sp['thoi_gian_bh']) ?> tháng</p>
                <?php endif; ?>
                <p><strong>Giá:</strong> <span class="text-danger fs-5"><?= number_format($sp['gia'], 0, ',', '.') ?>₫</span></p>
                <p><strong>Số lượng:</strong> <?= (int)$sp['sl'] ?></p>
                <p><strong>Loại:</strong> <?= htmlspecialchars($sp['ten_loaisp']) ?></p>
            </div>
        </div>

        <div class="product-full-details mt-5">
            <ul class="nav nav-tabs mb-3" id="productTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">Mô tả sản phẩm</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">Thông số kỹ thuật</button>
                </li>
            </ul>
            <div class="tab-content p-4 bg-white shadow-sm rounded" id="productTabContent">
                <div class="tab-pane fade show active" id="desc" role="tabpanel">
                    <p><?= !empty($sp['mo_ta']) ? nl2br(htmlspecialchars($sp['mo_ta'])) : 'Sản phẩm hiện chưa có mô tả chi tiết.' ?></p>
                </div>
                <div class="tab-pane fade" id="specs" role="tabpanel">
                    <?php
                    $specs = !empty($sp['thong_so_ky_thuat']) ? explode("\n", $sp['thong_so_ky_thuat']) : [];
                    ?>
                    <?php if (!empty($specs)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($specs as $line): ?>
                                <li class="list-group-item"><?= htmlspecialchars(trim($line)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Thông số kỹ thuật đang được cập nhật.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>


    </div>

</body>

</html>