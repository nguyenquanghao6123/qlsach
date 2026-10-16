<?php
session_start();
include "connect.php";

// Kiểm tra và lấy ID sách
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM sach WHERE ma_sach = $id");
$sach = mysqli_fetch_assoc($result);

if (!$sach) {
    echo "Sách không tồn tại!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($sach['ten_sach']); ?></title>
    <link rel="stylesheet" href="css/style.css?v=2.0">
</head>
<body>

<!-- Header -->
<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php"><span style="color:#d81b60; font-size: 24px; font-weight: bold;">📖 Book Logo</span></a>
        </div>
        <div class="user-menu">
            <a href="cart.php" class="cart-icon">🛍️ Giỏ hàng</a>
            <?php if (isset($_SESSION['user'])) { ?>
                <span>Chào, <b><?php echo $_SESSION['user']; ?></b></span>
                <a href="logout.php" class="btn-pink">Đăng xuất</a>
            <?php } else { ?>
                <a href="login.php" class="btn-pink">Đăng nhập</a>
            <?php } ?>
        </div>
    </div>
</header>

<div class="container" style="margin-top: 30px;">
    <p><a href="index.php" style="color: #d81b60; text-decoration: none;">← Quay lại trang chủ</a></p>
    
    <div style="display: flex; gap: 40px; margin-top: 20px; background: #fff; padding: 20px; border-radius: 8px;">
        <div style="flex: 1; text-align: center;">
            <?php if (!empty($sach['hinh_anh'])) { ?>
                <img src="<?php echo (strpos($sach['hinh_anh'], 'uploads/') !== false) ? $sach['hinh_anh'] : 'uploads/' . $sach['hinh_anh']; ?>" style="max-width: 100%; max-height: 380px; border-radius: 6px;">
            <?php } else { ?>
                <img src="https://via.placeholder.com/250x350?text=No+Image" style="max-width: 100%;">
            <?php } ?>
        </div>
        
        <div style="flex: 2;">
            <h1 style="margin-top: 0; font-size: 26px; color: #333;"><?php echo htmlspecialchars($sach['ten_sach']); ?></h1>
            <p style="font-size: 16px; margin: 10px 0;"><b>Tác giả:</b> <?php echo htmlspecialchars($sach['tac_gia']); ?></p>
            <p style="font-size: 16px; margin: 10px 0;"><b>Thể loại:</b> <?php echo htmlspecialchars($sach['the_loai']); ?></p>
            <p style="font-size: 16px; margin: 10px 0;"><b>Nhà xuất bản:</b> <?php echo htmlspecialchars($sach['nha_xuat_ban']); ?> (<?php echo $sach['nam_xuat_ban']; ?>)</p>
            <p style="font-size: 24px; color: #d81b60; font-weight: bold; margin: 15px 0;">
                <?php echo number_format($sach['gia']); ?> đ
            </p>
            
            <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                <h4 style="margin-top: 0;">📖 Mô tả nội dung:</h4>
                <p style="line-height: 1.6; color: #555;">
                    <?php echo nl2br(htmlspecialchars($sach['gioi_thieu'])); ?>
                </p>
            </div>
            
            <a href="cart.php?action=add&id=<?php echo $sach['ma_sach']; ?>" class="btn-pink" style="display: inline-block; padding: 12px 30px; font-size: 16px; text-decoration: none;">🛒 Thêm vào giỏ hàng</a>
        </div>
    </div>
</div>

</body>
</html>