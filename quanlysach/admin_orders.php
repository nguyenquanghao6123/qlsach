<?php
session_start();
include "connect.php";

// Kiểm tra quyền Admin
if (!isset($_SESSION['user']) || ($_SESSION['role'] !== 'admin' && $_SESSION['user'] !== 'admin123')) {
    header("Location: login.php");
    exit();
}

// 1. Tự động tìm đúng tên bảng đơn hàng trong CSDL
$order_table = "";
$possible_tables = array('don_hang', 'donhang', 'orders', 'hoa_don', 'hoadon');

foreach ($possible_tables as $tbl) {
    $check_tbl = mysqli_query($conn, "SHOW TABLES LIKE '$tbl'");
    if ($check_tbl && mysqli_num_rows($check_tbl) > 0) {
        $order_table = $tbl;
        break;
    }
}

// 2. Xử lý Chốt đơn / Cập nhật trạng thái
if (isset($_POST['update_status']) && !empty($order_table)) {
    $id = mysqli_real_escape_string($conn, $_POST['don_hang_id']);
    $trang_thai = mysqli_real_escape_string($conn, $_POST['trang_thai']);
    
    // Tìm tên cột ID chính xác
    $id_col = 'id';
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM $order_table");
    while ($c = mysqli_fetch_assoc($cols)) {
        if ($c['Field'] == 'ma_don' || $c['Field'] == 'ma_don_hang' || $c['Field'] == 'id_don_hang') {
            $id_col = $c['Field'];
            break;
        }
    }

    mysqli_query($conn, "UPDATE $order_table SET trang_thai = '$trang_thai' WHERE $id_col = '$id'");
    header("Location: admin_orders.php");
    exit();
}

// 3. Truy vấn lấy danh sách đơn hàng
$orders_query = false;
if (!empty($order_table)) {
    $orders_query = mysqli_query($conn, "SELECT * FROM $order_table ORDER BY 1 DESC");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý & Chốt đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <div class="admin-top-bar">
        <h2 class="page-title"><i class="fa-solid fa-file-invoice-dollar"></i> Quản lý & Chốt đơn hàng</h2>
        <a href="admin_dashboard.php" class="btn btn-gray"><i class="fa-solid fa-arrow-left"></i> Quay lại sản phẩm</a>
    </div>

    <div class="admin-card">
        <div class="table-wrapper">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Mã đơn</th>
                        <th>Tên khách hàng</th>
                        <th>SĐT / Địa chỉ</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align: center;">Thao tác chốt đơn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders_query && mysqli_num_rows($orders_query) > 0) {
                        while ($row = mysqli_fetch_assoc($orders_query)) { 
                            // Lấy mã đơn hàng
                            $id_val = $row['id'] ?? $row['ma_don'] ?? $row['ma_don_hang'] ?? reset($row);
                            // Lấy tên khách hàng
                            $name_val = $row['ten_khach_hang'] ?? $row['ho_ten'] ?? $row['ten_khach'] ?? $row['username'] ?? 'Khách hàng';
                            // Lấy SĐT & Địa chỉ
                            $sdt_val = $row['sdt'] ?? $row['dien_thoai'] ?? $row['so_dienthoai'] ?? '';
                            $dc_val = $row['dia_chi'] ?? $row['diachi'] ?? '';
                            // Lấy tổng tiền
                            $money_val = $row['tong_tien'] ?? $row['tongtien'] ?? $row['gia'] ?? 0;
                            // Lấy trạng thái
                            $status_val = $row['trang_thai'] ?? $row['trangthai'] ?? 'Chờ chốt đơn';
                        ?>
                        <tr>
                            <td><b>#<?php echo $id_val; ?></b></td>
                            <td><b><?php echo htmlspecialchars($name_val); ?></b></td>
                            <td>
                                <?php echo htmlspecialchars($sdt_val); ?><br>
                                <small style="color:#64748b;"><?php echo htmlspecialchars($dc_val); ?></small>
                            </td>
                            <td class="price-main"><?php echo number_format($money_val); ?> đ</td>
                            <td>
                                <span class="badge badge-category"><?php echo htmlspecialchars($status_val); ?></span>
                            </td>
                            <td class="text-center">
                                <form method="POST" action="admin_orders.php" style="display:inline-flex; gap:6px; justify-content:center;">
                                    <input type="hidden" name="don_hang_id" value="<?php echo $id_val; ?>">
                                    <select name="trang_thai" class="filter-select" style="width: auto; padding: 5px 8px;">
                                        <option value="Đã chốt đơn" <?php if($status_val == 'Đã chốt đơn') echo 'selected'; ?>>Chốt đơn</option>
                                        <option value="Đang giao hàng" <?php if($status_val == 'Đang giao hàng') echo 'selected'; ?>>Đang giao</option>
                                        <option value="Đã hoàn thành" <?php if($status_val == 'Đã hoàn thành') echo 'selected'; ?>>Hoàn thành</option>
                                        <option value="Đã hủy" <?php if($status_val == 'Đã hủy') echo 'selected'; ?>>Hủy đơn</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-red" style="padding: 5px 12px; font-size: 13px;">Lưu</button>
                                </form>
                            </td>
                        </tr>
                    <?php } } else { ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">
                                <i class="fa-solid fa-cart-arrow-down" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                                Chưa có đơn hàng nào hoặc tên bảng đơn hàng chưa trùng khớp!
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>