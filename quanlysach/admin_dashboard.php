<?php
session_start();
include "connect.php";

// Kiểm tra quyền Admin
if (!isset($_SESSION['user']) || ($_SESSION['role'] !== 'admin' && $_SESSION['user'] !== 'admin123')) {
    header("Location: login.php");
    exit();
}

// Lấy tham số tìm kiếm & thể loại từ URL
$search = isset($_GET['search']) ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';
$category = isset($_GET['category']) ? trim(mysqli_real_escape_string($conn, $_GET['category'])) : '';

// Xây dựng câu truy vấn SQL tìm kiếm
$where = array();

if ($search !== '') {
    $where[] = "(ten_sach LIKE '%$search%' OR ma_sach LIKE '%$search%' OR tac_gia LIKE '%$search%')";
}

if ($category !== '') {
    // Dùng TRIM để loại bỏ khoảng trắng thừa trong CSDL khi so sánh
    $where[] = "(TRIM(the_loai) = '$category' OR TRIM(danh_muc) = '$category')";
}

$where_sql = "";
if (count($where) > 0) {
    $where_sql = "WHERE " . implode(' AND ', $where);
}

// Truy vấn danh sách sách
$sach_query = mysqli_query($conn, "SELECT * FROM sach $where_sql ORDER BY ma_sach DESC");

// Danh sách thể loại chuẩn khớp với CSDL của bạn
$categories_list = array("Công nghệ", "Tâm lý học", "Khoa học", "Văn học", "Kinh doanh", "phát triển bản thân");

// Thống kê
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM sach"));
$total_categories = count($categories_list);
$total_stock_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(so_luong) as tong_ton FROM sach"));
$total_stock = $total_stock_res['tong_ton'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Top Header Bar -->
    <div class="admin-top-bar">
        <h2 class="page-title"><i class="fa-solid fa-mobile-screen-button"></i> Quản lý sản phẩm</h2>
        <div class="top-bar-buttons">
            <a href="admin_orders.php" class="btn btn-blue"><i class="fa-solid fa-cart-shopping"></i> Quản lý đơn hàng</a>
            <a href="index.php" class="btn btn-gray"><i class="fa-solid fa-arrow-left"></i> Tổng quan</a>
            <a href="add.php" class="btn btn-red"><i class="fa-solid fa-plus"></i> Thêm sản phẩm</a>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="admin-card">
        <!-- Filter Bar -->
        <form method="GET" action="admin_dashboard.php" class="admin-filter-bar">
            <input type="text" name="search" placeholder="Nhập tên sách, mã hoặc tác giả..." value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
            
            <select name="category" class="filter-select">
                <option value="">Tất cả thể loại</option>
                <?php foreach ($categories_list as $cat) { 
                    $selected = ($category === $cat) ? 'selected' : '';
                ?>
                    <option value="<?php echo $cat; ?>" <?php echo $selected; ?>><?php echo $cat; ?></option>
                <?php } ?>
            </select>
            
            <button type="submit" class="btn btn-blue"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
            <a href="admin_dashboard.php" class="btn btn-gray">Làm mới</a>
        </form>

        <!-- Product Table -->
        <div class="table-wrapper">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 80px;">Ảnh</th>
                        <th>Tên sách</th>
                        <th style="width: 150px;">Danh mục</th>
                        <th style="width: 140px;">Giá bán</th>
                        <th style="width: 100px;">Tồn kho</th>
                        <th style="width: 100px; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sach_query && mysqli_num_rows($sach_query) > 0) {
                        while ($row = mysqli_fetch_assoc($sach_query)) { 
                            // Tự động ghép thư mục "uploads/" nếu ảnh lưu tên file
                            $img_src = $row['hinh_anh'];
                            if (!empty($img_src) && !file_exists($img_src) && file_exists("uploads/" . $img_src)) {
                                $img_src = "uploads/" . $img_src;
                            }
                            // Hiển thị tên thể loại
                            $cat_display = !empty($row['the_loai']) ? $row['the_loai'] : (!empty($row['danh_muc']) ? $row['danh_muc'] : 'Sách');
                    ?>
                        <tr>
                            <td><b>#<?php echo $row['ma_sach']; ?></b></td>
                            <td class="text-center">
                                <img src="<?php echo htmlspecialchars($img_src); ?>" class="tbl-img" alt="Product" onerror="this.src='https://via.placeholder.com/50x65?text=No+Image';">
                            </td>
                            <td>
                                <div class="item-name"><?php echo htmlspecialchars($row['ten_sach']); ?></div>
                                <div class="item-sub"><?php echo htmlspecialchars($row['tac_gia']); ?></div>
                            </td>
                            <td>
                                <span class="badge badge-category"><?php echo htmlspecialchars($cat_display); ?></span>
                            </td>
                            <td>
                                <div class="price-main"><?php echo number_format($row['gia']); ?> đ</div>
                            </td>
                            <td><b><?php echo $row['so_luong']; ?></b> cuốn</td>
                            <td class="text-center">
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?php echo $row['ma_sach']; ?>" class="btn-action btn-edit" title="Sửa"><i class="fa-solid fa-pen"></i></a>
                                    <a href="delete.php?id=<?php echo $row['ma_sach']; ?>" class="btn-action btn-delete" onclick="return confirm('Xóa sản phẩm này?');" title="Xóa"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php } } else { ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 25px; color: #64748b;">
                                <i class="fa-solid fa-box-open" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                                Không tìm thấy sản phẩm nào phù hợp!
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stats Boxes -->
    <div class="stats-container">
        <div class="stat-box stat-red">
            <div class="stat-title">Tổng sản phẩm</div>
            <div class="stat-value"><?php echo $total_products; ?></div>
        </div>
        <div class="stat-box stat-blue">
            <div class="stat-title">Tổng danh mục</div>
            <div class="stat-value"><?php echo $total_categories; ?></div>
        </div>
        <div class="stat-box stat-green">
            <div class="stat-title">Tổng tồn kho</div>
            <div class="stat-value"><?php echo $total_stock; ?></div>
        </div>
    </div>
</div>

</body>
</html>