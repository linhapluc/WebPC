<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT * FROM admins WHERE name = :name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        $storedPassword = $admin['password'];

        if (password_verify($password, $storedPassword)) {
            $_SESSION["admin_id"] = $admin['id_admin'];
            $_SESSION["admin_name"] = $admin['name'];
            header("Location: home.php");
            exit();
        }

        if ($password === $storedPassword) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE admins SET password = ? WHERE id_admin = ?");
            $update->execute([$hashed, $admin['id_admin']]);

            $_SESSION["admin_id"] = $admin['id_admin'];
            $_SESSION["admin_name"] = $admin['name'];
            header("Location: home.php");
            exit();
        }

        $error = "Mật khẩu không đúng!";
    } else {
        $error = "Tên đăng nhập không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: url('./assets/images/Anhindex.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-form {
            background: #fff;
            border-radius: 12px;
            padding: 40px 30px;
            width: 400px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            text-align: center;
        }

        h2 {
            color: #1877f2;
            font-size: 28px;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 10px;
            right: 12px;
            cursor: pointer;
            color: #888;
        }

        input[type="submit"],
        input[type="button"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            color: white;
            margin-top: 10px;
            font-size: 15px;
        }

        input[type="submit"] {
            background-color: #1877f2;
        }

        input[type="submit"]:hover {
            background-color: #0f5ed7;
        }

        input[type="button"] {
            background-color: #d9534f;
        }

        input[type="button"]:hover {
            background-color: #c9302c;
        }

        .error-msg {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .create-account-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .create-account-link a {
            color: #1877f2;
            text-decoration: none;
            margin-left: 5px;
        }

        .create-account-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-form">
        <h2>ĐĂNG NHẬP ADMIN</h2>
        <?php if (!empty($error)) echo "<p class='error-msg'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Tên đăng nhập" required>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Mật khẩu" required>
                <span class="toggle-password" onclick="togglePassword()">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </span>
            </div>
            <input type="submit" value="Đăng nhập">
            <input type="button" value="Quay lại" onclick="window.location.href='../trang-chu/index.php'">
        </form>

        <!-- ✅ Bổ sung quên mật khẩu -->
        <div class="create-account-link">
            <span>Bạn quên mật khẩu?</span>
            <a href="quenmatkhau_admin.php">Khôi phục tại đây</a>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>
</body>
</html>
