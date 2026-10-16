<?php
include "connect.php";

if (isset($_POST['register'])) {
    // Lọc dữ liệu tránh SQL Injection
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users(fullname, username, password)
            VALUES('$fullname', '$username', '$password')";

    if (mysqli_query($conn, $sql)) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Tài khoản đã tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <!-- Font Awesome Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Quicksand', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #fff5f8;
            position: relative;
            overflow: hidden;
        }

        /* Họa tiết góc màn hình */
        .bg-shape-1 {
            position: absolute;
            top: -100px;
            left: -100px;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(255,182,193,0.6) 0%, rgba(255,230,240,0) 70%);
            border-radius: 50%;
            z-index: 1;
        }

        .bg-shape-2 {
            position: absolute;
            bottom: -120px;
            right: -100px;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(255,105,180,0.4) 0%, rgba(255,220,235,0.1) 70%);
            border-radius: 50%;
            z-index: 1;
        }

        .paper-plane {
            position: absolute;
            top: 15%;
            right: 15%;
            z-index: 1;
            color: #d81b60;
            font-size: 28px;
            transform: rotate(-10deg);
        }

        .quote-box {
            position: absolute;
            bottom: 12%;
            left: 10%;
            z-index: 1;
            text-align: center;
        }

        .quote-box p {
            font-family: 'Dancing Script', cursive;
            font-size: 26px;
            color: #e91e63;
        }

        .quote-box .heart-icon {
            color: #e91e63;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Khung đăng ký */
        .register-card {
            position: relative;
            z-index: 2;
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 40px 35px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(233, 30, 99, 0.08);
            text-align: center;
        }

        .avatar-icon {
            width: 65px;
            height: 65px;
            background: #ffe4ec;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px;
            color: #e91e63;
            font-size: 26px;
        }

        .register-card h2 {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .subtitle {
            color: #95a5a6;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .divider-heart {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .divider-heart::before, .divider-heart::after {
            content: '';
            height: 1px;
            width: 40px;
            background: #ffd1dc;
        }

        .divider-heart i {
            color: #e91e63;
            font-size: 12px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .form-group label i {
            color: #e91e63;
            font-size: 13px;
        }

        .input-box {
            position: relative;
        }

        .input-box i.left-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 14px;
        }

        .input-box input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .input-box input:focus {
            border-color: #ff80ab;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(255, 128, 171, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #ff4081, #c2185b);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(233, 30, 99, 0.3);
            transition: all 0.2s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(233, 30, 99, 0.4);
        }

        .footer-links {
            font-size: 13px;
            color: #718096;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed #edf2f7;
        }

        .footer-links a {
            color: #e91e63;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .error-msg {
            background: #fff5f5;
            color: #e53e3e;
            padding: 10px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 15px;
            border: 1px solid #fed7d7;
        }
    </style>
</head>
<body>

<div class="bg-shape-1"></div>
<div class="bg-shape-2"></div>

<div class="paper-plane">
    <i class="fa-regular fa-paper-plane"></i>
</div>

<div class="quote-box">
    <p>" Bắt đầu từ những<br>điều nhỏ nhất "</p>
    <div class="heart-icon"><i class="fa-solid fa-heart"></i></div>
</div>

<div class="register-card">
    <div class="avatar-icon">
        <i class="fa-solid fa-user-plus"></i>
    </div>
    <h2>Đăng ký tài khoản</h2>
    <div class="subtitle">Tạo tài khoản để bắt đầu hành trình của bạn</div>
    
    <div class="divider-heart">
        <i class="fa-solid fa-heart"></i>
    </div>

    <?php if (isset($error)) { ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fa-regular fa-user"></i> Họ tên</label>
            <div class="input-box">
                <i class="fa-regular fa-user left-icon"></i>
                <input type="text" name="fullname" placeholder="Nhập họ tên của bạn..." required>
            </div>
        </div>

        <div class="form-group">
            <label><i class="fa-regular fa-envelope"></i> Tài khoản</label>
            <div class="input-box">
                <i class="fa-regular fa-envelope left-icon"></i>
                <input type="text" name="username" placeholder="Nhập tài khoản của bạn..." required>
            </div>
        </div>

        <div class="form-group">
            <label><i class="fa-solid fa-lock"></i> Mật khẩu</label>
            <div class="input-box">
                <i class="fa-solid fa-lock left-icon"></i>
                <input type="password" name="password" placeholder="Nhập mật khẩu của bạn..." required>
            </div>
        </div>

        <button type="submit" name="register" class="btn-submit">
            Đăng ký <i class="fa-solid fa-arrow-right"></i>
        </button>
    </form>

    <div class="footer-links">
        Đã có tài khoản? <a href="login.php">Đăng nhập</a>
    </div>
</div>

</body>
</html>