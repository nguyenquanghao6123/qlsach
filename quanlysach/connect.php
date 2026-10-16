<?php
$conn = mysqli_connect("localhost", "root", "", "quanlysach");

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Thiết lập bảng mã tiếng Việt cho kết nối cơ sở dữ liệu
mysqli_set_charset($conn, "utf8");
?>