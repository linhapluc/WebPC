<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Thiếu ID admin.");
}

$id_admin = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM admins WHERE id_admin = ?");
$stmt->execute([$id_admin]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) die("Không tìm thấy admin.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $id_admin = $_POST['id_admin'] ?? null;

    // Kiểm tra rỗng
    if (empty($name)) {
        $error = "Vui lòng không để trống tên.";
    } else {
        // Kiểm tra trùng tên (ngoại trừ chính mình)
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM admins WHERE name = ? AND id_admin != ?");
        $stmt_check->execute([$name, $id_admin]);
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Tên admin này đã được sử dụng.";
        } else {
            $stmt_update = $conn->prepare("UPDATE admins SET name = ? WHERE id_admin = ?");
            if ($stmt_update->execute([$name, $id_admin])) {
                $_SESSION['admin_message_success'] = "✔️ Cập nhật admin thành công.";
                header("Location: qladmin.php");
                exit();
            } else {
                $error = "❌ Lỗi khi cập nhật dữ liệu admin.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h3 class="text-center fw-bold mb-4" style="font-size: 2rem;">SỬA THÔNG TIN QUẢN TRỊ VIÊN</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
                <label class="form-label">ID Admin</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($admin['id_admin']) ?>" disabled>
                <input type="hidden" name="id_admin" value="<?= htmlspecialchars($admin['id_admin']) ?>">

            </div>
            <div class="mb-3">
                <label class="form-label">Tên Admin</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" required>
            </div>
            <div class="text-end">
                <a href="qladmin.php" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>

</body>

</html>