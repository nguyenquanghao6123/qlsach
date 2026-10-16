<?php
session_start();
include "connect.php";

// 1. Kiểm tra quyền Admin (Ngăn khách thường truy cập)
if (!isset($_SESSION['user']) || (($_SESSION['role'] ?? '') !== 'admin' && $_SESSION['user'] !== 'admin123')) {
    header("Location: index.php");
    exit();
}

// 2. Lấy ID sách an toàn
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}
$id = intval($_GET['id']);

// 3. Truy vấn lấy dữ liệu sách hiện tại
$sql = "SELECT * FROM sach WHERE ma_sach = $id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "Sách không tồn tại!";
    exit();
}

// 4. Xử lý khi nhấn Cập nhật
if (isset($_POST['submit'])) {
    $ten_sach = mysqli_real_escape_string($conn, $_POST['ten_sach']);
    $tac_gia = mysqli_real_escape_string($conn, $_POST['tac_gia']);
    $the_loai = mysqli_real_escape_string($conn, $_POST['the_loai']);
    $nha_xuat_ban = mysqli_real_escape_string($conn, $_POST['nha_xuat_ban']);
    $nam_xuat_ban = intval($_POST['nam_xuat_ban']);
    $so_luong = intval($_POST['so_luong']);
    $gia = floatval($_POST['gia']);
    $gioi_thieu = mysqli_real_escape_string($conn, $_POST['gioi_thieu']);

    $hinh_anh = $row['hinh_anh']; // Giữ lại ảnh cũ mặc định

    // Nếu người dùng chọn ảnh mới
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['name'] != "") {
        $hinh_anh = time() . "_" . $_FILES['hinh_anh']['name'];
        $tmp = $_FILES['hinh_anh']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $hinh_anh);
    }

    $sql_update = "UPDATE sach SET
        ten_sach = '$ten_sach',
        tac_gia = '$tac_gia',
        the_loai = '$the_loai',
        nha_xuat_ban = '$nha_xuat_ban',
        nam_xuat_ban = '$nam_xuat_ban',
        so_luong = '$so_luong',
        gia = '$gia',
        hinh_anh = '$hinh_anh',
        gioi_thieu = '$gioi_thieu'
        WHERE ma_sach = $id";

    mysqli_query($conn, $sql_update);
    header("Location: admin_dashboard.php"); // Quay lại trang Admin sau khi sửa thành công
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa thông tin sách</title>
    <link rel="stylesheet" href="css/style.css?v=2.0">
</head>
<body>

<div class="form-box">
    <h2>Sửa thông tin sách</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Tên sách</label>
        <input type="text" name="ten_sach" value="<?php echo htmlspecialchars($row['ten_sach']); ?>" required>

        <label>Tác giả</label>
        <input type="text" name="tac_gia" value="<?php echo htmlspecialchars($row['tac_gia']); ?>" required>

        <label>Thể loại</label>
        <input type="text" name="the_loai" value="<?php echo htmlspecialchars($row['the_loai']); ?>">

        <label>Nhà xuất bản</label>
        <input type="text" name="nha_xuat_ban" value="<?php echo htmlspecialchars($row['nha_xuat_ban']); ?>">

        <label>Năm xuất bản</label>
        <input type="number" name="nam_xuat_ban" value="<?php echo $row['nam_xuat_ban']; ?>">

        <label>Số lượng</label>
        <input type="number" name="so_luong" value="<?php echo $row['so_luong']; ?>">

        <label>Giá</label>
        <input type="number" name="gia" value="<?php echo $row['gia']; ?>">

        <label>Ảnh hiện tại</label><br>
        <?php if (!empty($row['hinh_anh'])) { ?>
            <img class="current-img" src="uploads/<?php echo $row['hinh_anh']; ?>" width="100" style="margin: 10px 0; border-radius: 4px;">
        <?php } else { ?>
            <p>Chưa có ảnh</p>
        <?php } ?>

        <label>Đổi hình ảnh sách</label>
        <input type="file" name="hinh_anh">

        <label>Giới thiệu sách</label>
        <textarea name="gioi_thieu" rows="4"><?php echo htmlspecialchars($row['gioi_thieu']); ?></textarea>

        <button type="submit" name="submit">Cập nhật</button>
    </form>

    <a class="back-link" href="admin_dashboard.php">Quay lại trang quản trị</a>
</div>

</body>
</html>