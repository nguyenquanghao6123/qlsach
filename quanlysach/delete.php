<?php
session_start();
include "connect.php";

// 1. Kiểm tra quyền Admin (Ngăn người dùng thường truy cập trái phép)
if (!isset($_SESSION['user']) || (($_SESSION['role'] ?? '') !== 'admin' && $_SESSION['user'] !== 'admin123')) {
    header("Location: index.php");
    exit();
}

// 2. Lấy ID sách và ép kiểu số nguyên để bảo mật (chống SQL Injection)
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 3. Thực hiện xóa sách
    $sql = "DELETE FROM sach WHERE ma_sach = $id";
    mysqli_query($conn, $sql);
}

// 4. Chuyển hướng về lại Trang quản trị Admin
header("Location: admin_dashboard.php");
exit();
?>