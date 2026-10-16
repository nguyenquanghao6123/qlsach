<?php
session_start();
include "connect.php";

// 1. Lấy danh sách 4 cuốn sách bán chạy
$banchay_sql = "SELECT * FROM sach ORDER BY ma_sach DESC LIMIT 4";
$banchay_result = mysqli_query($conn, $banchay_sql);

// Chuyển kết quả sách bán chạy thành mảng để dễ phân tách cuốn #01 và các cuốn #02, #03, #04
$banchay_list = [];
while ($row = mysqli_fetch_assoc($banchay_result)) {
    $banchay_list[] = $row;
}
$first_book = isset($banchay_list[0]) ? $banchay_list[0] : null;
$sub_books  = array_slice($banchay_list, 1);

// 2. Lọc & Tìm kiếm an toàn bằng Prepared Statements
$where_clauses = [];
$params = [];
$types = "";

if (isset($_GET['the_loai']) && trim($_GET['the_loai']) !== "") {
    $where_clauses[] = "the_loai = ?";
    $params[] = $_GET['the_loai'];
    $types .= "s";
}

if (isset($_GET['keyword']) && trim($_GET['keyword']) !== "") {
    $where_clauses[] = "ten_sach LIKE ?";
    $params[] = "%" . $_GET['keyword'] . "%";
    $types .= "s";
}

$sql = "SELECT * FROM sach";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY ma_sach DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 3. Lấy danh sách thể loại
$theloai_sql = "SELECT DISTINCT the_loai FROM sach WHERE the_loai IS NOT NULL AND the_loai != ''";
$theloai_result = mysqli_query($conn, $theloai_sql);

// 4. Đếm số sản phẩm giỏ hàng
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += (int)$qty;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store - Cửa Hàng Sách Trực Tuyến</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Quicksand', sans-serif; }
        body { background-color: #fff8fa; color: #333; }

        /* HEADER & NAV */
        .top-header { background: #ffffff; padding: 15px 8%; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.03); position: relative; z-index: 10; }
        .logo a { font-size: 24px; font-weight: 700; color: #e91e63; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .search-box { width: 40%; }
        .search-box form { display: flex; align-items: center; background: #f7eff3; border-radius: 30px; padding: 4px 5px 4px 18px; }
        .search-box input { border: none; background: transparent; outline: none; width: 100%; font-size: 14px; color: #4a5568; }
        .search-box button { background: #e91e63; border: none; color: white; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
        .search-box button:hover { background: #d81b60; }
        .user-menu { display: flex; align-items: center; gap: 15px; }
        .cart-icon { text-decoration: none; color: #2c3e50; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 6px; }
        .cart-badge { background: #e91e63; color: white; font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: bold; }
        .user-name { font-size: 14px; color: #4a5568; }
        .btn-pink { background: #e91e63; color: white; padding: 7px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; }
        .btn-pink:hover { background: #d81b60; box-shadow: 0 4px 12px rgba(233, 30, 99, 0.3); }

        .main-nav { background: #ffffff; border-top: 1px solid #fce4ec; border-bottom: 1px solid #fce4ec; padding: 0 8%; }
        .main-nav ul { list-style: none; display: flex; align-items: center; gap: 30px; }
        .main-nav ul li a { display: block; padding: 14px 0; text-decoration: none; color: #555; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .main-nav ul li a.active, .main-nav ul li a:hover { color: #e91e63; border-bottom: 3px solid #e91e63; }

        /* HERO & FEATURES */
        .hero-banner { margin: 25px 8%; background: linear-gradient(135deg, #fff0f5 0%, #ffd6e5 100%); border-radius: 24px; padding: 40px 60px; display: flex; justify-content: space-between; align-items: center; position: relative; box-shadow: 0 10px 25px rgba(233, 30, 99, 0.05); }
        .hero-text h3 { color: #e91e63; font-size: 12px; letter-spacing: 2px; font-weight: 700; margin-bottom: 8px; }
        .hero-text h1 { font-size: 32px; color: #2c3e50; margin-bottom: 12px; font-weight: 700; }
        .hero-text p { color: #666; font-size: 14px; margin-bottom: 22px; }
        .quote-stamp { position: absolute; right: 28%; top: 25%; font-family: 'Dancing Script', cursive; font-size: 20px; color: #d81b60; transform: rotate(-8deg); text-align: center; }
        .btn-explore { background: #e91e63; color: white; padding: 11px 26px; border-radius: 25px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 18px rgba(233, 30, 99, 0.25); transition: 0.3s; }
        .btn-explore:hover { transform: translateY(-2px); background: #d81b60; }

        .features-container { margin: 0 8% 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .feature-box { background: #ffffff; padding: 16px 20px; border-radius: 16px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: 0.3s; }
        .feature-box:hover { transform: translateY(-3px); }
        .feature-icon { width: 44px; height: 44px; min-width: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .feature-box:nth-child(1) .feature-icon { background: #ffebee; color: #ff4081; }
        .feature-box:nth-child(2) .feature-icon { background: #e8f5e9; color: #4caf50; }
        .feature-box:nth-child(3) .feature-icon { background: #fff3e0; color: #ff9800; }
        .feature-box:nth-child(4) .feature-icon { background: #f3e5f5; color: #ab47bc; }
        .feature-info h4 { font-size: 12px; font-weight: 700; color: #2c3e50; margin-bottom: 2px; }
        .feature-info p { font-size: 11px; color: #888; }

        /* GRID & CARDS CHUNG */
        .container { padding: 0 8%; }
        .section-box { background: #ffffff; border-radius: 20px; padding: 25px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); }
        .filter-bar { background: #ffffff; padding: 15px 20px; border-radius: 14px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .filter-bar label { font-weight: 700; color: #2c3e50; font-size: 14px; }
        .filter-bar select { padding: 8px 16px; border-radius: 20px; border: 1px solid #ffd1dc; outline: none; background: #fff8fa; color: #e91e63; font-weight: 600; cursor: pointer; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 20px; }
        .product-card { background: #ffffff; border: 1px solid #f7eff3; border-radius: 18px; padding: 16px; text-align: center; position: relative; transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(233, 30, 99, 0.12); border-color: #ffd1dc; }
        .product-img { width: 100%; height: 190px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; overflow: hidden; border-radius: 10px; }
        .product-img img { max-height: 100%; max-width: 100%; object-fit: contain; }
        .book-title { font-size: 14px; font-weight: 700; color: #2c3e50; margin-bottom: 4px; height: 38px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .author { font-size: 12px; color: #888; margin-bottom: 8px; }
        .price { color: #e91e63; font-weight: 700; font-size: 16px; margin-bottom: 12px; }
        .product-actions { display: flex; gap: 8px; }
        .btn-outline { flex: 1; border: 1px solid #ffd1dc; color: #e91e63; padding: 8px; border-radius: 20px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.3s; }
        .btn-outline:hover { background: #fff0f5; }
        .btn-buy { flex: 1; background: #e91e63; color: white; padding: 8px; border-radius: 20px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.3s; display: inline-block; }
        .btn-buy:hover { background: #d81b60; }

        /* STYLES CHO KHỐI SÁCH HOT HERO NÂNG CẤP */
        .hot-section { background: #ffffff; border-radius: 20px; padding: 25px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); }
        .hot-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .hot-title { font-size: 18px; font-weight: 700; color: #2c3e50; display: flex; align-items: center; gap: 8px; }
        .see-all { color: #666; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .see-all:hover { color: #e91e63; }

        .featured-book { display: flex; gap: 24px; background: #fff8fa; border: 1px solid #ffe4ed; border-radius: 16px; padding: 20px; margin-bottom: 20px; }
        .featured-img { width: 150px; height: 210px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .featured-img img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; }
        .featured-info { display: flex; flex-direction: column; justify-content: center; flex: 1; }
        .tag-hot { color: #e91e63; font-size: 12px; font-weight: 700; margin-bottom: 8px; }
        .featured-title { font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 10px; line-height: 1.4; }
        .meta-info { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #666; margin-bottom: 18px; }
        .meta-info .rating { color: #ffa000; font-weight: 700; }
        .meta-info .divider { color: #ddd; }
        .price-action { display: flex; align-items: center; justify-content: space-between; gap: 15px; }
        .featured-price { font-size: 22px; font-weight: 800; color: #e91e63; }
        .btn-buy-now { background: #e91e63; color: #fff; padding: 9px 22px; border-radius: 20px; font-weight: 600; font-size: 13px; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-buy-now:hover { background: #d81b60; box-shadow: 0 4px 12px rgba(233, 30, 99, 0.3); }

        .sub-books-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .sub-book-item { position: relative; background: #fff; border: 1px solid #f7eff3; border-radius: 14px; padding: 12px; text-align: center; transition: 0.3s; display: flex; flex-direction: column; justify-content: space-between; }
        .sub-book-item:hover { border-color: #ffd1dc; transform: translateY(-3px); box-shadow: 0 6px 15px rgba(233, 30, 99, 0.08); }
        .rank { position: absolute; top: 10px; left: 10px; font-weight: 800; font-size: 12px; color: #e91e63; background: #fff0f5; padding: 2px 8px; border-radius: 10px; }
        .sub-img { height: 110px; display: flex; align-items: center; justify-content: center; margin-top: 15px; margin-bottom: 8px; }
        .sub-img img { max-height: 100%; max-width: 100%; object-fit: contain; }
        .sub-title { font-size: 12px; font-weight: 700; color: #333; height: 32px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin-bottom: 4px; }
        .sub-price { font-size: 13px; font-weight: 700; color: #e91e63; margin-bottom: 8px; }

        @media (max-width: 768px) {
            .featured-book { flex-direction: column; align-items: center; text-align: center; }
            .sub-books-grid { grid-template-columns: 1fr; }
            .price-action { flex-direction: column; gap: 10px; }
        }

        /* FOOTER */
        .site-footer { background: #ffffff; color: #718096; padding: 50px 8% 20px; margin-top: 50px; border-top: 1px dashed #ffd1dc; font-size: 13px; }
        .footer-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-bottom: 40px; }
        .footer-brand { color: #e91e63; font-size: 20px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .footer-desc { line-height: 1.6; margin-bottom: 15px; }
        .footer-socials { display: flex; gap: 10px; }
        .footer-socials a { width: 32px; height: 32px; background: #fff0f5; color: #e91e63; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; }
        .footer-socials a:hover { background: #e91e63; color: #fff; }
        .footer-col h4 { color: #2c3e50; font-size: 15px; font-weight: 700; margin-bottom: 18px; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { color: #718096; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; }
        .footer-col ul li a:hover { color: #e91e63; }
        .contact-list li { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .contact-list i { color: #e91e63; }
        .footer-bottom { text-align: center; border-top: 1px solid #f7eff3; padding-top: 20px; color: #a0aec0; }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="top-header">
    <div class="logo">
        <a href="index.php"><i class="fa-solid fa-book-open"></i> Book Store</a>
    </div>

    <div class="search-box">
        <form action="index.php" method="GET">
            <input type="text" name="keyword" placeholder="Tìm kiếm sách..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>

    <div class="user-menu">
        <a href="cart.php" class="cart-icon">
            <i class="fa-solid fa-cart-shopping" style="color: #e91e63;"></i> Giỏ hàng 
            <span class="cart-badge"><?php echo $cart_count; ?></span>
        </a>
        
        <?php if (isset($_SESSION['user'])) { ?>
            <span class="user-name"><i class="fa-regular fa-user"></i> Chào, <b><?php echo htmlspecialchars($_SESSION['user']); ?></b></span>
            
            <?php if ((isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || $_SESSION['user'] === 'admin123') { ?>
                <a href="admin_dashboard.php" class="btn-pink">Trang Admin</a>
            <?php } ?>
            
            <a href="logout.php" class="btn-pink">Đăng xuất</a>
        <?php } else { ?>
            <a href="login.php" class="btn-pink">Đăng nhập</a>
            <a href="register.php" class="btn-pink">Đăng ký</a>
        <?php } ?>
    </div>
</header>

<!-- NAVIGATION BAR -->
<nav class="main-nav">
    <ul>
        <li><a href="index.php" class="active"><i class="fa-solid fa-house"></i> Trang chủ</a></li>
        <li><a href="#san-pham">Sản phẩm sách</a></li>
        <li><a href="#">Giới thiệu</a></li>
        <li><a href="#">Liên hệ</a></li>
    </ul>
</nav>

<!-- HERO BANNER -->
<section class="hero-banner">
    <div class="hero-text">
        <h3>KHÁM PHÁ THẾ GIỚI TRI THỨC</h3>
        <h1>Đọc sách thay đổi cuộc sống 🌸</h1>
        <p>Hàng ngàn cuốn sách hay, đa dạng thể loại, cập nhật liên tục.</p>
        <a href="#san-pham" class="btn-explore">Khám phá ngay <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    
    <div class="quote-stamp">
        Sách là người bạn<br>đồng hành tuyệt vời nhất! 💕
    </div>
</section>

<!-- THANH DỊCH VỤ -->
<div class="features-container">
    <div class="feature-box">
        <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
        <div class="feature-info">
            <h4>GIAO HÀNG NHANH</h4>
            <p>Cho tất cả đơn hàng</p>
        </div>
    </div>
    <div class="feature-box">
        <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="feature-info">
            <h4>SẢN PHẨM CHÍNH HÃNG</h4>
            <p>Cam kết chất lượng</p>
        </div>
    </div>
    <div class="feature-box">
        <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
        <div class="feature-info">
            <h4>HỖ TRỢ 24/7</h4>
            <p>Tất cả ngày trong tuần</p>
        </div>
    </div>
    <div class="feature-box">
        <div class="feature-icon"><i class="fa-solid fa-rotate-left"></i></div>
        <div class="feature-info">
            <h4>HOÀN LẠI TIỀN</h4>
            <p>Nếu không hài lòng</p>
        </div>
    </div>
</div>

<div class="container">

    <!-- KHỐI SÁCH HOT LAYOUT MỚI GIỐNG ẢNH MẪU -->
    <?php if ($first_book): ?>
    <section class="hot-section">
        <div class="hot-header">
            <h2 class="hot-title">
                <i class="fa-solid fa-fire" style="color: #e91e63;"></i>
                <?php echo isset($_SESSION['user']) ? "Dành riêng cho " . htmlspecialchars($_SESSION['user']) . ": " : ""; ?>SÁCH ĐANG HOT
            </h2>
            <a href="#san-pham" class="see-all">[ Xem tất cả <i class="fa-solid fa-arrow-right"></i> ]</a>
        </div>

        <div class="hot-content">
            <!-- Cuốn sách đứng vị trí #01 -->
            <div class="featured-book">
                <div class="featured-img">
                    <?php 
                    $img_filename = $first_book['hinh_anh'];
                    $img_path = (!empty($img_filename) && strpos($img_filename, 'uploads/') === 0) ? $img_filename : "uploads/" . $img_filename;
                    if (!empty($img_filename) && file_exists($img_path)) { ?>
                        <img src="<?php echo htmlspecialchars($img_path); ?>" alt="<?php echo htmlspecialchars($first_book['ten_sach']); ?>">
                    <?php } else { ?>
                        <img src="https://via.placeholder.com/150x200?text=No+Image" alt="No Image">
                    <?php } ?>
                </div>
                <div class="featured-info">
                    <span class="tag-hot">🔥 #01 ĐANG ĐƯỢC QUAN TÂM</span>
                    <h3 class="featured-title"><?php echo htmlspecialchars($first_book['ten_sach']); ?></h3>
                    <div class="meta-info">
                        <span class="rating"><i class="fa-solid fa-star"></i> 4.9</span>
                        <span class="divider">|</span>
                        <span class="sales">1.2k lượt bán</span>
                    </div>
                    <div class="price-action">
                        <span class="featured-price"><?php echo number_format($first_book['gia']); ?> đ</span>
                        <a href="detail.php?id=<?php echo (int)$first_book['ma_sach']; ?>" class="btn-buy-now">[ Xem ngay <i class="fa-solid fa-arrow-right"></i> ]</a>
                    </div>
                </div>
            </div>

            <!-- Các cuốn sách vị trí #02, #03, #04 -->
            <?php if (!empty($sub_books)): ?>
            <div class="sub-books-grid">
                <?php foreach ($sub_books as $index => $sub_book): ?>
                    <div class="sub-book-item">
                        <span class="rank">#0<?php echo $index + 2; ?></span>
                        <div class="sub-img">
                            <?php 
                            $sub_img_filename = $sub_book['hinh_anh'];
                            $sub_img_path = (!empty($sub_img_filename) && strpos($sub_img_filename, 'uploads/') === 0) ? $sub_img_filename : "uploads/" . $sub_img_filename;
                            if (!empty($sub_img_filename) && file_exists($sub_img_path)) { ?>
                                <img src="<?php echo htmlspecialchars($sub_img_path); ?>" alt="<?php echo htmlspecialchars($sub_book['ten_sach']); ?>">
                            <?php } else { ?>
                                <img src="https://via.placeholder.com/120x150?text=No+Image" alt="No Image">
                            <?php } ?>
                        </div>
                        <div class="sub-title"><?php echo htmlspecialchars($sub_book['ten_sach']); ?></div>
                        <div class="sub-price"><?php echo number_format($sub_book['gia']); ?> đ</div>
                        <a href="detail.php?id=<?php echo (int)$sub_book['ma_sach']; ?>" class="btn-outline" style="font-size: 11px; padding: 5px;">Xem ngay</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- LỌC THEO THỂ LOẠI -->
    <div class="filter-bar">
        <h2 id="san-pham" style="font-size: 18px; color: #2c3e50; font-weight: 700;">
            <i class="fa-solid fa-book" style="color:#e91e63;"></i> Sách hay tuyển chọn
        </h2>
        <form method="GET" action="index.php" style="display:flex; align-items:center; gap:10px;">
            <label><i class="fa-solid fa-filter" style="color:#e91e63;"></i> Thể loại sách:</label>
            <select name="the_loai" onchange="this.form.submit()">
                <option value="">Tất cả thể loại</option>
                <?php while ($tl = mysqli_fetch_assoc($theloai_result)) { ?>
                    <option value="<?php echo htmlspecialchars($tl['the_loai']); ?>" <?php if (isset($_GET['the_loai']) && $_GET['the_loai'] == $tl['the_loai']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($tl['the_loai']); ?>
                    </option>
                <?php } ?>
            </select>
        </form>
    </div>

    <!-- DANH SÁCH SÁCH -->
    <div class="product-grid">
        <?php if (mysqli_num_rows($result) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="product-card">
                    <div class="product-img">
                        <?php 
                        $img_filename = $row['hinh_anh'];
                        $img_path = (!empty($img_filename) && strpos($img_filename, 'uploads/') === 0) ? $img_filename : "uploads/" . $img_filename;

                        if (!empty($img_filename) && file_exists($img_path)) {
                        ?>
                            <img src="<?php echo htmlspecialchars($img_path); ?>" alt="<?php echo htmlspecialchars($row['ten_sach']); ?>">
                        <?php } else { ?>
                            <img src="https://via.placeholder.com/200x260?text=No+Image" alt="No Image">
                        <?php } ?>
                    </div>

                    <div>
                        <h3 class="book-title"><?php echo htmlspecialchars($row['ten_sach']); ?></h3>
                        <p class="author"><?php echo htmlspecialchars($row['tac_gia']); ?></p>
                        <p class="price"><?php echo number_format($row['gia']); ?> đ</p>
                    </div>

                    <div class="product-actions">
                        <a href="detail.php?id=<?php echo (int)$row['ma_sach']; ?>" class="btn-outline">Xem chi tiết</a>
                        <?php if (isset($_SESSION['user'])) { ?>
                            <a href="cart.php?action=add&id=<?php echo (int)$row['ma_sach']; ?>" class="btn-buy">Mua ngay</a>
                        <?php } else { ?>
                            <a href="login.php" class="btn-buy" onclick="alert('Vui lòng đăng nhập để mua hàng!');">Mua ngay</a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p style="grid-column: 1/-1; padding: 25px; background: #fff; border-radius: 12px; text-align:center;">Không tìm thấy cuốn sách nào!</p>
        <?php } ?>
    </div>

</div>

<!-- FOOTER -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-col">
            <h3 class="footer-brand"><i class="fa-solid fa-book-open"></i> Book Store</h3>
            <p class="footer-desc">Cửa hàng sách trực tuyến uy tín hàng đầu. Nơi lan tỏa tri thức và niềm đam mê đọc sách đến mọi người với những cuốn sách chất lượng nhất.</p>
            <div class="footer-socials">
                <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-tiktok"></i></a>
                <a href="#"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Liên Kết Nhanh</h4>
            <ul>
                <li><a href="index.php"><i class="fa-solid fa-angle-right"></i> Trang chủ</a></li>
                <li><a href="#san-pham"><i class="fa-solid fa-angle-right"></i> Danh mục sách</a></li>
                <li><a href="cart.php"><i class="fa-solid fa-angle-right"></i> Giỏ hàng</a></li>
                <li><a href="#"><i class="fa-solid fa-angle-right"></i> Chính sách đổi trả</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Thể Loại Nổi Bật</h4>
            <ul>
                <li><a href="#"><i class="fa-solid fa-heart"></i> Sách Kỹ Năng Sống</a></li>
                <li><a href="#"><i class="fa-solid fa-heart"></i> Lập Trình & CNTT</a></li>
                <li><a href="#"><i class="fa-solid fa-heart"></i> Kinh Tế - Quản Lý</a></li>
                <li><a href="#"><i class="fa-solid fa-heart"></i> Văn Học Nước Ngoài</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Thông Tin Liên Hệ</h4>
            <ul class="contact-list">
                <li><i class="fa-solid fa-location-dot"></i> Hà Nội, Việt Nam</li>
                <li><i class="fa-solid fa-phone"></i> Hotline: 1900 xxxx</li>
                <li><i class="fa-solid fa-envelope"></i> Support: hotro@bookstore.com</li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2026 Book Store. Tất cả các quyền được bảo lưu. Made with <i class="fa-solid fa-heart" style="color: #e91e63;"></i></p>
    </div>
</footer>

</body>
</html>