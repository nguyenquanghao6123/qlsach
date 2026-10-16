<?php
session_start();
include "connect.php";

// Kiểm tra quyền Admin (Chỉ mở session_start 1 lần duy nhất)
if (!isset($_SESSION['user']) || (($_SESSION['role'] ?? '') !== 'admin' && $_SESSION['user'] !== 'admin123')) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit'])) {
    $ten_sach = mysqli_real_escape_string($conn, $_POST['ten_sach']);
    $tac_gia = mysqli_real_escape_string($conn, $_POST['tac_gia']);
    $the_loai = mysqli_real_escape_string($conn, $_POST['the_loai']);
    $nha_xuat_ban = mysqli_real_escape_string($conn, $_POST['nha_xuat_ban']);
    $nam_xuat_ban = intval($_POST['nam_xuat_ban']);
    $so_luong = intval($_POST['so_luong']);
    $gia = floatval($_POST['gia']);
    $gioi_thieu = mysqli_real_escape_string($conn, $_POST['gioi_thieu']);

    $hinh_anh = "";

    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['name'] != "") {
        $hinh_anh = time() . "_" . $_FILES['hinh_anh']['name'];
        $tmp = $_FILES['hinh_anh']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $hinh_anh);
    }

    $sql = "INSERT INTO sach
    (ten_sach, tac_gia, the_loai, nha_xuat_ban, nam_xuat_ban, so_luong, gia, hinh_anh, gioi_thieu)
    VALUES
    ('$ten_sach', '$tac_gia', '$the_loai', '$nha_xuat_ban', '$nam_xuat_ban', '$so_luong', '$gia', '$hinh_anh', '$gioi_thieu')";

    mysqli_query($conn, $sql);
    header("Location: admin_dashboard.php"); // Sau khi thêm sách xong quay về trang Quản trị Admin
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sách</title>
    <link rel="stylesheet" href="css/style.css?v=2.0">
</head>
<body>

<div class="form-box">
    <h2>Thêm sách mới</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Tên sách</label>
        <input type="text" name="ten_sach" required>

        <label>Tác giả</label>
        <input type="text" name="tac_gia" required>

        <label>Thể loại</label>
        <input type="text" name="the_loai">

        <label>Nhà xuất bản</label>
        <input type="text" name="nha_xuat_ban">

        <label>Năm xuất bản</label>
        <input type="number" name="nam_xuat_ban">

        <label>Số lượng</label>
        <input type="number" name="so_luong">

        <label>Giá</label>
        <input type="number" name="gia">

        <label>Hình ảnh sách</label>
        <input type="file" name="hinh_anh">

        <label>Giới thiệu sách</label>
        <textarea name="gioi_thieu" rows="4"></textarea>

        <button type="submit" name="submit">Thêm sách</button>
    </form>

    <a class="back-link" href="admin_dashboard.php">Quay lại trang quản trị</a>
</div>

</body>
</html>