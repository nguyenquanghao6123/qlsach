-- Tạo cơ sở dữ liệu quanlysach
CREATE DATABASE IF NOT EXISTS `quanlysach` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `quanlysach`;

-- 1. Bảng quản lý người dùng (users)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(255) DEFAULT NULL,
  `role` VARCHAR(50) DEFAULT 'user', -- Phân quyền: 'admin' hoặc 'user'
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Bảng quản lý sách (sach)
CREATE TABLE IF NOT EXISTS `sach` (
  `ma_sach` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_sach` VARCHAR(255) NOT NULL,
  `tac_gia` VARCHAR(255) NOT NULL,
  `the_loai` VARCHAR(100) DEFAULT NULL,
  `nha_xuat_ban` VARCHAR(255) DEFAULT NULL,
  `nam_xuat_ban` INT(11) DEFAULT NULL,
  `so_luong` INT(11) DEFAULT 0,
  `gia` DECIMAL(12,2) DEFAULT 0.00,
  `hinh_anh` VARCHAR(255) DEFAULT NULL,
  `gioi_thieu` TEXT DEFAULT NULL,
  PRIMARY KEY (`ma_sach`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Bảng quản lý đơn hàng (don_hang)
CREATE TABLE IF NOT EXISTS `don_hang` (
  `id_don_hang` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL, -- Liên kết với bảng users
  `ho_ten` VARCHAR(255) DEFAULT NULL,
  `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
  `dia_chi` TEXT DEFAULT NULL,
  `ngay_dat` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `tong_tien` DECIMAL(12,2) DEFAULT 0.00,
  `trang_thai` VARCHAR(100) DEFAULT 'Chờ xử lý',
  PRIMARY KEY (`id_don_hang`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `fk_donhang_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Bảng chi tiết đơn hàng (chi_tiet_don_hang)
CREATE TABLE IF NOT EXISTS `chi_tiet_don_hang` (
  `id_chi_tiet` INT(11) NOT NULL AUTO_INCREMENT,
  `id_don_hang` INT(11) NOT NULL, -- Liên kết với bảng don_hang
  `ma_sach` INT(11) NOT NULL,     -- Liên kết với bảng sach
  `so_luong` INT(11) NOT NULL DEFAULT 1,
  `gia` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_chi_tiet`),
  KEY `id_don_hang` (`id_don_hang`),
  KEY `ma_sach` (`ma_sach`),
  CONSTRAINT `fk_chitiet_donhang` FOREIGN KEY (`id_don_hang`) REFERENCES `don_hang` (`id_don_hang`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_chitiet_sach` FOREIGN KEY (`ma_sach`) REFERENCES `sach` (`ma_sach`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;