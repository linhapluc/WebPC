<?php session_start(); ?> <!-- Thêm -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Xác nhận OTP</title>
    <style>
        :root {
            --primary-color: #1976D2;
            --primary-hover: #1565C0;
            --font: 'Segoe UI', Tahoma, sans-serif;
            --border-radius: 8px;
        }

        body {
            font-family: var(--font);
            background-color: #e3f2fd;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .otp-box {
            background-color: #fff;
            padding: 50px 50px;
            border-radius: var(--border-radius);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .otp-box h2 {
            color: var(--primary-color);
            margin-bottom: 24px;
            font-size: 22px;
        }

        .otp-box label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-size: 15px;
            color: #333;
        }

        .otp-box input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        .otp-box input[type="text"]:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .otp-box button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .otp-box button:hover {
            background-color: var(--primary-hover);
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>
    <div class="otp-box">
        <h2>Xác Nhận Mã OTP Để Hoàn Tất Đặt Hàng</h2>
        <?php if (isset($_SESSION['otp_error'])): ?>
            <div class="error-message"><?php echo $_SESSION['otp_error'];
                                        unset($_SESSION['otp_error']); ?></div>
        <?php endif; ?>
        <form method="POST" action="verify_otp.php">
            <label for="otp_input">Mã OTP đã gửi đến email của bạn:</label>
            <input type="text" name="otp_input" id="otp_input" maxlength="6" required placeholder="Nhập 6 chữ số OTP">
            <button type="submit">Xác nhận</button>
        </form>
        <form method="POST" action="resend_otp.php" style="margin-top: 15px;">
            <button type="submit" style="background-color: #ccc; color: #333;">Gửi lại mã OTP</button>
        </form>
    </div>
</body>

</html>