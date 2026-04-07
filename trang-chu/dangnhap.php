<?php
// dangnhap.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'connect.php';

$message = "";
$login_required_message = "";

// Thông báo khi bị yêu cầu đăng nhập
if (isset($_SESSION['login_message'])) {
    $login_required_message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

// Thông báo sau khi đăng ký thành công
if (isset($_SESSION['register_success'])) {
    $message = "<p style='color:green;'>" . htmlspecialchars($_SESSION['register_success']) . "</p>";
    unset($_SESSION['register_success']);
}

// Xử lý khi người dùng submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $mat_khau = $_POST["mat_khau"] ?? '';

    if (empty($email) || empty($mat_khau)) {
        $message = "<p style='color:red;'>Vui lòng nhập đầy đủ email và mật khẩu.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>Địa chỉ email không hợp lệ.</p>";
    } else {
        try {
            $sql = "SELECT id_khachhang, ho_ten, mat_khau, active FROM khachhang WHERE email = :email LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($mat_khau, $user['mat_khau'])) {
                if ($user['active'] == 1) {
                    $_SESSION['user_id'] = $user['id_khachhang'];
                    $_SESSION['user_name'] = $user['ho_ten'];

                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect_url = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        header('Location: ' . $redirect_url);
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $message = "<p style='color:red;'>Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.</p>";
                }
            } else {
                $message = "<p style='color:red;'>Email hoặc mật khẩu không đúng.</p>";
            }
        } catch (PDOException $e) {
            error_log("Lỗi PDO khi đăng nhập: " . $e->getMessage());
            $message = "<p style='color:red;'>Đã xảy ra lỗi hệ thống. Mã lỗi: " . $e->getCode() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập - PC Shop Nasa</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f4f6f8;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      background: #ffffff;
      padding: 36px 32px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      width: 100%;
      max-width: 380px;
    }

    h2 {
      text-align: center;
      margin-bottom: 28px;
      font-weight: 600;
      color: #222;
    }

    .form-group {
      position: relative;
      margin-bottom: 22px;
    }

    .form-group input {
      width: 100%;
      padding: 12px 14px 12px 42px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      color: #333;
      background-color: #fdfdfd;
    }

    .form-group input:focus {
      border-color: #2196f3;
      outline: none;
      background-color: #fff;
    }

    .form-group .fa {
      position: absolute;
      top: 12px;
      left: 14px;
      color: #aaa;
    }

    input[type="submit"] {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background-color: #2196f3;
      color: white;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }

    input[type="submit"]:hover {
      background-color: #1976d2;
    }

    .link-area {
      display: flex;
      justify-content: space-between;
      margin-top: 12px;
      font-size: 14px;
    }

    .link-area a {
      color: #2196f3;
      text-decoration: none;
      font-weight: 500;
    }

    .link-area a:hover {
      text-decoration: underline;
    }

    .message-area p {
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 6px;
      background-color: #fff8e1;
      color: #6d4c41;
      text-align: center;
      font-size: 14px;
    }

    @media (max-width: 480px) {
      .container {
        padding: 28px 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Đăng nhập</h2>

    <div class="message-area">
      <?php
        if (!empty($login_required_message)) {
            echo '<p>' . htmlspecialchars($login_required_message) . '</p>';
        }
        if (!empty($message)) {
            echo $message;
        }
      ?>
    </div>

    <form method="POST" action="dangnhap.php">
      <div class="form-group">
        <i class="fa fa-envelope"></i>
        <input type="email" id="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <i class="fa fa-lock"></i>
        <input type="password" id="mat_khau" name="mat_khau" placeholder="Mật khẩu" required>
      </div>

      <input type="submit" value="Đăng nhập">
    </form>

    <div class="link-area">
      <a href="quenmatkhau.php">Quên mật khẩu?</a>
      <a href="dangky.php">Đăng ký</a>
    </div>
  </div>
</body>
</html>
