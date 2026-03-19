<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy id bảo hành
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Thiếu mã bảo hành.");
}

$id_baohanh = $_GET['id'];

// Lấy dữ liệu hiện tại
$stmt = $conn->prepare("SELECT * FROM baohanh WHERE id_baohanh = ?");
$stmt->execute([$id_baohanh]);
$baohanh = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$baohanh) {
    die("Bảo hành không tồn tại.");
}

// Lấy danh sách sản phẩm
$sp_stmt = $conn->query("SELECT id_sanpham, ten_sp FROM sanpham ORDER BY ten_sp");
$sanphams = $sp_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Không cho phép đổi mã -> dùng lại id hiện tại
    $id_sanpham = $_POST['id_sanpham'] ?? '';
    $thoi_gian_bh = $_POST['thoi_gian_bh'] ?? 0;
    $mo_ta_bh = $_POST['mo_ta_bh'] ?? '';

    if (empty($id_sanpham) || $thoi_gian_bh <= 0) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        // Kiểm tra trùng sản phẩm (mỗi sản phẩm chỉ có 1 bảo hành, trừ chính nó)
        $check = $conn->prepare("SELECT COUNT(*) FROM baohanh WHERE id_sanpham = ? AND id_baohanh != ?");
        $check->execute([$id_sanpham, $id_baohanh]);
        if ($check->fetchColumn() > 0) {
            $error = "Sản phẩm này đã có thông tin bảo hành. Vui lòng chọn sản phẩm khác.";
        }

        // Nếu không có lỗi thì cập nhật
        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE baohanh SET id_sanpham = ?, thoi_gian_bh = ?, mo_ta_bh = ? WHERE id_baohanh = ?");
            $stmt->execute([$id_sanpham, $thoi_gian_bh, $mo_ta_bh, $id_baohanh]);
            header("Location: baohanh.php");
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa bảo hành</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function syncProductName() {
            const select = document.getElementById('id_sanpham');
            const tenInput = document.getElementById('ten_sanpham');
            tenInput.value = select.options[select.selectedIndex].text;
        }
    </script>
    <style>
        .navbar {
            background-color: #007bff;
            padding: 16px 24px;
        }

        .navbar-brand {
            font-weight: bold;
            color: white;
            font-size: 24px;
        }

        .product-wrapper {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            gap: 40px;
            margin: 40px auto;
            max-width: 1200px;
            padding: 0 20px;
        }

        .product-image-container {
            flex: 1;
            max-width: 700px;
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
            max-height: 700px;
            object-fit: contain;
        }

        .product-info-container {
            flex: 1;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

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

        .back-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .back-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-primary">
        <div class="container-fluid d-flex align-items-center">
            <span class="navbar-brand text-white fs-4 fw-bold">Trang Quản Trị Admin</span>
            <span class="admin-name ms-auto text-white fs-5 fw-semibold">
                👋 Xin chào, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
            </span>
        </div>
    </nav>
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">SỬA BẢO HÀNH</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
            <label class="form-label">Mã bảo hành</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($baohanh['id_baohanh']) ?>" disabled>
        </div>


            <div class="mb-3">
                <label for="id_sanpham" class="form-label">Mã sản phẩm</label>
                <select name="id_sanpham" id="id_sanpham" class="form-select" onchange="syncProductName()" required>
                    <option value="">-- Chọn mã sản phẩm --</option>
                    <?php foreach ($sanphams as $sp): ?>
                        <option value="<?= $sp['id_sanpham'] ?>" <?= $sp['id_sanpham'] === $baohanh['id_sanpham'] ? 'selected' : '' ?>>
                            <?= $sp['id_sanpham'] ?> - <?= htmlspecialchars($sp['ten_sp']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="ten_sanpham" class="form-label">Tên sản phẩm</label>
                <input type="text" id="ten_sanpham" class="form-control" value="<?php
                                                                                foreach ($sanphams as $sp) {
                                                                                    if ($sp['id_sanpham'] === $baohanh['id_sanpham']) {
                                                                                        echo htmlspecialchars($sp['ten_sp']);
                                                                                        break;
                                                                                    }
                                                                                }
                                                                                ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="thoi_gian_bh" class="form-label">Thời gian bảo hành (tháng)</label>
                <input type="number" name="thoi_gian_bh" class="form-control" value="<?= (int)$baohanh['thoi_gian_bh'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="mo_ta_bh" class="form-label">Mô tả bảo hành</label>
                <textarea name="mo_ta_bh" class="form-control" rows="4"><?= htmlspecialchars($baohanh['mo_ta_bh']) ?></textarea>
            </div>

            <div class="text-end">
                <a href="baohanh.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</body>

</html>