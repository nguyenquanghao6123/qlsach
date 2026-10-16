<?php
session_start();
include "connect.php";

// Bắt buộc đăng nhập khi thực hiện hành động thêm giỏ hàng hoặc xem giỏ hàng
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Khởi tạo giỏ hàng
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// 1. Thêm sản phẩm vào giỏ
if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $id = intval($_GET['id']);
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
    header("Location: cart.php");
    exit();
}

// 2. Xóa 1 sản phẩm khỏi giỏ hàng
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id']);
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: cart.php");
    exit();
}

// 3. Cập nhật giỏ hàng
if (isset($_POST['update_cart'])) {
    if (isset($_POST['qty'])) {
        foreach ($_POST['qty'] as $id => $val) {
            if ($val <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = intval($val);
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// 4. Xử lý đặt hàng kèm Địa chỉ
if (isset($_POST['checkout'])) {
    if (!isset($_SESSION['user'])) {
        $error = "Bạn cần đăng nhập để tiến hành đặt hàng!";
    } elseif (empty($_SESSION['cart'])) {
        $error = "Giỏ hàng của bạn đang trống!";
    } else {
        $ho_ten = mysqli_real_escape_string($conn, $_POST['ho_ten']);
        $so_dien_thoai = mysqli_real_escape_string($conn, $_POST['so_dien_thoai']);
        $dia_chi = mysqli_real_escape_string($conn, $_POST['dia_chi']);

        if (empty($ho_ten) || empty($so_dien_thoai) || empty($dia_chi)) {
            $error = "Vui lòng điền đầy đủ thông tin giao hàng!";
        } else {
            $user_id = $_SESSION['user_id'] ?? 1;
            $tong_tien = 0;

            // Tính tổng tiền
            foreach ($_SESSION['cart'] as $id => $qty) {
                $res = mysqli_query($conn, "SELECT gia FROM sach WHERE ma_sach = $id");
                if ($item = mysqli_fetch_assoc($res)) {
                    $tong_tien += $item['gia'] * $qty;
                }
            }

            // Lưu đơn hàng cùng thông tin người nhận
            $sql_donhang = "INSERT INTO don_hang (id_user, ho_ten, so_dien_thoai, dia_chi, tong_tien, trang_thai) 
                            VALUES ($user_id, '$ho_ten', '$so_dien_thoai', '$dia_chi', $tong_tien, 'Chờ xác nhận')";
            
            if (mysqli_query($conn, $sql_donhang)) {
                $id_don_hang = mysqli_insert_id($conn);

                // Lưu chi tiết từng sản phẩm
                foreach ($_SESSION['cart'] as $id => $qty) {
                    $res = mysqli_query($conn, "SELECT gia FROM sach WHERE ma_sach = $id");
                    if ($item = mysqli_fetch_assoc($res)) {
                        $gia = $item['gia'];
                        mysqli_query($conn, "INSERT INTO chi_tiet_don_hang (id_don_hang, ma_sach, so_luong, gia) 
                                             VALUES ($id_don_hang, $id, $qty, $gia)");
                    }
                }

                // Xóa giỏ hàng
                unset($_SESSION['cart']);
                $success = "Đặt hàng thành công! Đơn hàng sẽ được giao đến: " . htmlspecialchars($dia_chi);
            } else {
                $error = "Lỗi xử lý đơn hàng: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng & Đặt hàng - Book Store</title>
    <!-- Thêm FontAwesome để hiển thị các icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=2.1">
    <style>
        /* Tinh chỉnh bổ sung trực tiếp cho giao diện giỏ hàng phong cách mới */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            margin: 0;
        }
        .main-header {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 12px 0;
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }
        .btn-pink {
            background: #e91e63;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-pink:hover {
            background: #d81b60;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }
        
        /* Banner Giỏ hàng */
        .cart-header-banner {
            background: #fff;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-header-banner h2 {
            margin: 0;
            font-size: 24px;
            color: #2b2b2b;
        }
        .cart-header-banner p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }

        /* Bảng sản phẩm */
        .cart-table-wrapper {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            padding: 20px;
            margin-bottom: 25px;
            overflow-x: auto;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart-table th {
            background-color: #fff;
            color: #555;
            font-weight: 600;
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        /* Thông tin sản phẩm trong bảng (Ảnh + Tên) */
        .cart-product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .cart-product-info img {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #eee;
        }
        .cart-product-details h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }
        .cart-product-details p {
            margin: 0;
            font-size: 13px;
            color: #777;
        }

        /* Nút tăng giảm số lượng (+ -) */
        .quantity-control {
            display: inline-flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }
        .quantity-control button {
            background: #f8f9fa;
            border: none;
            outline: none;
            width: 32px;
            height: 32px;
            font-size: 14px;
            cursor: pointer;
            color: #333;
            transition: background 0.2s;
        }
        .quantity-control button:hover {
            background: #e9iop;
            background: #e9ecef;
        }
        .quantity-control input {
            width: 40px;
            text-align: center;
            border: none;
            outline: none;
            font-size: 14px;
            font-weight: 600;
        }

        /* Nút xóa sản phẩm hình thùng rác */
        .btn-remove {
            background: none;
            border: none;
            color: #ff4d4d;
            cursor: pointer;
            font-size: 18px;
            padding: 8px;
            border-radius: 4px;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-remove:hover {
            background: #ffe6e6;
        }

        /* Tổng thanh toán ngang liền bảng */
        .cart-total-row {
            text-align: right;
            padding: 20px 15px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            border-top: 2px solid #f0f0f0;
            background: #fff;
            border-radius: 0 0 12px 12px;
        }
        .cart-total-row span {
            color: #e91e63;
            font-size: 22px;
            margin-left: 10px;
        }

        /* Bố cục 2 cột cho form và cam kết */
        .checkout-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }
        @media (max-width: 992px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        .checkout-form-card, .checkout-features-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            padding: 25px;
        }
        .checkout-form-card h3 {
            margin-top: 0;
            font-size: 18px;
            color: #e91e63;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
        }
        .form-desc {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #444;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #e91e63;
        }

        /* Cột cam kết bên phải */
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }
        .feature-item i {
            font-size: 24px;
            color: #e91e63;
            margin-top: 2px;
        }
        .feature-item h5 {
            margin: 0 0 3px 0;
            font-size: 15px;
            color: #333;
        }
        .feature-item p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
        .thank-you-text {
            text-align: center;
            font-style: italic;
            color: #e91e63;
            font-weight: 500;
            margin-top: 30px;
        }
        
        .btn-outline {
            background: #fff;
            border: 1px solid #e91e63;
            color: #e91e63;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-outline:hover {
            background: #fce4ec;
        }
    </style>
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php" style="text-decoration:none;"><span style="color:#e91e63; font-size: 20px; font-weight: bold;"><i class="fa-solid fa-book"></i> Book Logo</span></a>
        </div>
        <div class="user-menu">
            <a href="index.php" class="btn-pink"><i class="fa-solid fa-house"></i> Trang chủ</a>
        </div>
    </div>
</header>

<div class="container">
    <!-- Banner tiêu đề giỏ hàng -->
    <div class="cart-header-banner">
        <div>
            <h2>Giỏ hàng của bạn</h2>
            <p>Kiểm tra lại sản phẩm và tiến hành thanh toán để hoàn tất đơn hàng.</p>
        </div>
        <div>
            <i class="fa-solid fa-cart-shopping" style="font-size: 40px; color: #e91e63; opacity: 0.2;"></i>
        </div>
    </div>

    <?php if (isset($error)) echo "<p style='color:red; font-weight:bold; background:#ffe6e6; padding:10px; border-radius:6px;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green; font-weight:bold; background:#e6ffe6; padding:10px; border-radius:6px;'>$success</p>"; ?>

    <?php if (!empty($_SESSION['cart'])) { ?>
        <form method="POST">
            <!-- BẢNG SẢN PHẨM -->
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Sản phẩm</th>
                            <th style="width: 15%;">Giá</th>
                            <th style="width: 15%; text-align: center;">Số lượng</th>
                            <th style="width: 15%;">Thành tiền</th>
                            <th style="width: 10%; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $tong_cong = 0;
                        foreach ($_SESSION['cart'] as $id => $qty) {
                            $res = mysqli_query($conn, "SELECT * FROM sach WHERE ma_sach = $id");
                            if ($item = mysqli_fetch_assoc($res)) {
                                $subtotal = $item['gia'] * $qty;
                                $tong_cong += $subtotal;
                                // Lấy đường dẫn ảnh, mặc định ảnh để trong thư mục uploads/
                                $hinh_anh = !empty($item['hinh_anh']) ? "uploads/" . $item['hinh_anh'] : "uploads/sach_mau.jpg";
                        ?>
                            <tr>
                                <td>
                                    <div class="cart-product-info">
                                        <img src="<?php echo htmlspecialchars($hinh_anh); ?>" alt="Ảnh sách" onerror="this.src='https://via.placeholder.com/60x80?text=Book'">
                                        <div class="cart-product-details">
                                            <h4><?php echo htmlspecialchars($item['ten_sach']); ?></h4>
                                            <p><?php echo isset($item['tac_gia']) ? "Tác giả: " . htmlspecialchars($item['tac_gia']) : "Sách hay chọn lọc"; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['gia']); ?> đ</td>
                                <td style="text-align: center;">
                                    <div class="quantity-control">
                                        <button type="button" onclick="decreaseQty(this)">-</button>
                                        <input type="number" name="qty[<?php echo $id; ?>]" value="<?php echo $qty; ?>" min="1" class="qty-input">
                                        <button type="button" onclick="increaseQty(this)">+</button>
                                    </div>
                                </td>
                                <td style="font-weight: 600; color: #e91e63;"><?php echo number_format($subtotal); ?> đ</td>
                                <td style="text-align: center;">
                                    <a href="cart.php?action=delete&id=<?php echo $id; ?>" class="btn-remove" title="Xóa sản phẩm" onclick="return confirm('Bạn có chắc muốn xóa sách này khỏi giỏ hàng?');">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } 
                        ?>
                    </tbody>
                </table>

                <!-- Tổng thanh toán nằm ngay dưới bảng -->
                <div class="cart-total-row">
                    Tổng thanh toán: <span><?php echo number_format($tong_cong); ?> đ</span>
                </div>
            </div>

            <!-- Nút cập nhật số lượng -->
            <div style="margin-bottom: 25px;">
                <button type="submit" name="update_cart" class="btn-outline">
                    <i class="fa-solid fa-rotate"></i> Cập nhật số lượng
                </button>
            </div>

            <!-- PHẦN THÔNG TIN NHẬN HÀNG VÀ CAM KẾT (Chia 2 cột) -->
            <div class="checkout-grid">
                <!-- Form thông tin nhận hàng bên trái -->
                <div class="checkout-form-card">
                    <h3><i class="fa-solid fa-bag-shopping"></i> Thông tin nhận hàng</h3>
                    <p class="form-desc">Vui lòng điền đầy đủ và chính xác thông tin để chúng tôi giao hàng nhanh nhất!</p>
                    
                    <div class="form-group">
                        <label>Họ và tên người nhận: *</label>
                        <input type="text" name="ho_ten" placeholder="Nhập họ và tên" required>
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại: *</label>
                        <input type="text" name="so_dien_thoai" placeholder="Nhập số điện thoại" required>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ chi tiết (Số nhà, đường, phường/xã, quận/huyện): *</label>
                        <textarea name="dia_chi" rows="3" placeholder="Nhập địa chỉ chi tiết..." required></textarea>
                    </div>

                    <button type="submit" name="checkout" class="btn-pink" style="width:100%; padding:12px; font-size:16px; margin-top: 10px;">
                        <i class="fa-solid fa-rocket"></i> Xác nhận & Đặt hàng
                    </button>
                </div>

                <!-- Khung cam kết bên phải -->
                <div class="checkout-features-card">
                    <div class="feature-item">
                        <i class="fa-solid fa-shield-halved"></i>
                        <div>
                            <h5>Thanh toán an toàn</h5>
                            <p>Bảo mật thông tin của bạn tuyệt đối</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <i class="fa-solid fa-truck-fast"></i>
                        <div>
                            <h5>Giao hàng nhanh chóng</h5>
                            <p>Tận nơi, đúng hẹn theo yêu cầu</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <i class="fa-solid fa-headset"></i>
                        <div>
                            <h5>Hỗ trợ 24/7</h5>
                            <p>Luôn sẵn sàng giải đáp mọi thắc mắc</p>
                        </div>
                    </div>

                    <div class="thank-you-text">
                        Cảm ơn bạn đã tin tưởng! ❤️
                    </div>
                </div>
            </div>
        </form>
    <?php } else { ?>
        <div style="background:#fff; padding:40px; text-align:center; border-radius:12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
            <p style="font-size: 16px; color: #666;">Giỏ hàng của bạn đang trống.</p>
            <a href="index.php" class="btn-pink" style="margin-top: 10px;">Tiếp tục mua sắm</a>
        </div>
    <?php } ?>
</div>

<!-- JavaScript hỗ trợ tăng giảm số lượng bằng nút + và - -->
<script>
function increaseQty(button) {
    let input = button.parentElement.querySelector('.qty-input');
    let currentValue = parseInt(input.value) || 0;
    input.value = currentValue + 1;
}

function decreaseQty(button) {
    let input = button.parentElement.querySelector('.qty-input');
    let currentValue = parseInt(input.value) || 0;
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}
</script>

</body>
</html>