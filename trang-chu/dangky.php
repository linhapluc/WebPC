<?php
include 'connect.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoTen = $_POST["ho_ten"];
    $email = $_POST["email"];
    $mat_khau = $_POST["mat_khau"];
    $xacnhan_matkhau = $_POST["xacnhan_matkhau"];
    $so_dien_thoai = $_POST["so_dien_thoai"];

    // Kiểm tra mật khẩu
    if ($mat_khau !== $xacnhan_matkhau) {
        $message = "<div class='alert error'>❌ Mật khẩu không khớp.</div>";
    } elseif (
        strlen($mat_khau) < 8 ||
        !preg_match("#[0-9]+#", $mat_khau) ||
        !preg_match("#[a-z]+#", $mat_khau) ||
        !preg_match("#[A-Z]+#", $mat_khau) ||
        !preg_match("#[^\w]#", $mat_khau)
    ) {
        $message = "<div class='alert error'>❌ Mật khẩu yếu. Cần ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.</div>";
    } else {
        // Kiểm tra email hoặc số điện thoại đã tồn tại
        $stmt = $conn->prepare("SELECT * FROM khachhang WHERE email = ? OR so_dien_thoai = ?");
        $stmt->execute([$email, $so_dien_thoai]);
        if ($stmt->rowCount() > 0) {
            $message = "<div class='alert error'>❌ Email hoặc số điện thoại đã được sử dụng.</div>";
        } else {
            // Mã hóa mật khẩu
            $hashedPassword = password_hash($mat_khau, PASSWORD_DEFAULT);

            // Tạo mã khách hàng mới
            $stmt = $conn->query("SELECT MAX(id_khachhang) AS max_id FROM khachhang");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $maxId = $row['max_id'];
            if ($maxId) {
                $num = (int)substr($maxId, 2) + 1;
                $id_khachhang = 'KH' . str_pad($num, 3, '0', STR_PAD_LEFT);
            } else {
                $id_khachhang = 'KH001';
            }

            // Thêm vào cơ sở dữ liệu
            $stmt = $conn->prepare("INSERT INTO khachhang (id_khachhang, ho_ten, email, mat_khau, so_dien_thoai) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_khachhang, $hoTen, $email, $hashedPassword, $so_dien_thoai]);

            $message = "<div class='alert success'>✅ Đăng ký thành công! <a href='dangnhap.php'>Đăng nhập ngay</a>.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(120deg,rgb(52, 61, 75), #c2e9fb);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: #fff;
            padding: 30px 35px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 480px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }

        input[type="submit"],
        input[type="button"] {
            flex: 1;
            padding: 12px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        input[type="button"] {
            background-color: #6c757d;
            color: white;
        }

        input[type="button"]:hover {
            background-color: #5a6268;
        }

        .alert {
            text-align: center;
            padding: 12px;
            margin: 15px 0;
            border-radius: 8px;
        }

        .alert.error {
            background: #f8d7da;
            color: #842029;
        }

        .alert.success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .alert a {
            text-decoration: underline;
            color: #0c63e4;
        }

        @media screen and (max-width: 480px) {
            .btn-container {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Đăng ký tài khoản</h2>

        <?php if ($message) echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label>Họ và tên:</label>
                <input type="text" name="ho_ten" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại:</label>
                <input type="text" name="so_dien_thoai" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="mat_khau" required>
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu:</label>
                <input type="password" name="xacnhan_matkhau" required>
            </div>

            <div class="btn-container">
                <input type="submit" value="Đăng ký">
                <input type="button" value="Quay lại" onclick="window.location.href='index.php';">
            </div>
        </form>
    </div>
</body>

</html>