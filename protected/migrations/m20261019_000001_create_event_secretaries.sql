-- Migration: tao bang event_secretaries (Ban Thu Ky Doan) + seed du lieu
-- QUAN TRONG: chay tren DATABASE ma Yii frontend su dung (vd prod: yii_event),
-- KHONG phai DB rieng cua backend neu hai ben tach DB.

CREATE TABLE IF NOT EXISTS `event_secretaries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'events.id - NULL = ap dung chung',
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `members` JSON NOT NULL COMMENT 'Danh sach thu ky: [{name,phone}]',
  `units` JSON NOT NULL COMMENT 'Danh sach don vi: ["..."]',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_secretaries_event` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed (idempotent: xoa sach roi chen lai)
DELETE FROM `event_secretaries`;
INSERT INTO `event_secretaries` (`event_id`, `sort_order`, `members`, `units`, `created_at`, `updated_at`, `deleted_at`) VALUES
(NULL, 1, '[{"name":"Mr. Đàm Thận Tiệp","phone":"0988 016 066"},{"name":"Ms. Trần Thị Quyên","phone":"0975 776 888"}]', '["Công ty CP Phát triển địa ốc Cienco5","Công ty TNHH Thang máy DHE","Chi nhánh DVNO Linh Đàm","Chi nhánh DVNO và quản lý KĐT Mường Thanh","Chi nhánh DVNO Thanh Hà","Ban Điều hành Siêu thị và các siêu thị tại Hà Nội","Khách ngoại giao","Khách mời gia đình"]', NOW(), NOW(), NULL),
(NULL, 2, '[{"name":"Ms. Nguyễn Thị Thu Trang","phone":"0973 666 015"},{"name":"Ms. Trần Mai Phương","phone":"0986 950 890"}]', '["Văn phòng Tập đoàn Mường Thanh","Mường Thanh Grand Phương Đông","Trung tâm Giải trí Vinh","Khách sạn Mường Thanh Luxury Diễn Lâm","Khu sinh thái Mường Thanh Diễn Lâm","Mường Thanh Golf Club Diễn Lâm","Dự án Mường Thanh Diễn Lâm II"]', NOW(), NOW(), NULL),
(NULL, 3, '[{"name":"Ms. Lê Thị Ngọc","phone":"0898 600 222"},{"name":"Ms. Hà Hoàng Yến","phone":"0868 495 413"},{"name":"Ms. Lê Thị Thu Uyên","phone":"0329 493 273"}]', '["Mường Thanh Luxury Hà Nam","Mường Thanh Grand Thanh Hóa","Trường THPT Nguyễn Du","Bệnh viện Phủ Diễn","Mường Thanh Diễn Châu","Mường Thanh Grand Hoàng Mai","Mường Thanh Holiday Con Cuông","Mường Thanh Luxury Sông Lam","Mường Thanh Cửa Đông","Mường Thanh Grand Cửa Lò","Dự án Mường Thanh Luxury Cửa Lò","Mường Thanh Vinh","Mường Thanh Thanh Niên Vinh","Siêu thị Mường Thanh Con Cuông","Siêu thị Mường Thanh Hoàng Mai"]', NOW(), NOW(), NULL),
(NULL, 4, '[{"name":"Ms. Đỗ Thị Thanh Huyền","phone":"0376 327 988"},{"name":"Ms. Đinh Thái Thùy Dương","phone":"0377 618 599"},{"name":"Mr. Nguyễn Quốc Anh","phone":"0367 390 558"}]', '["Mường Thanh Quy Nhơn","Mường Thanh Grand Gia Lai","Mường Thanh Luxury Buôn Ma Thuột","Mường Thanh Luxury Nha Trang","Mường Thanh Grand Nha Trang","Mường Thanh Luxury Viễn Triều","Mường Thanh Luxury Khánh Hòa","Chung cư Mường Thanh Viễn Triều","Chung cư Mường Thanh Khánh Hòa","Mường Thanh Holiday Đà Lạt","Mường Thanh Holiday Mũi Né","Mường Thanh Holiday Vũng Tàu","Mường Thanh Luxury Sài Gòn","Mường Thanh Grand Sài Gòn Centre","Dự án Chung cư Mường Thanh Phú Hữu","Mường Thanh Luxury Cà Mau","Mường Thanh Luxury Cần Thơ","Mường Thanh Luxury Phú Quốc"]', NOW(), NOW(), NULL),
(NULL, 5, '[{"name":"Ms. Hoàng Thị Huyền Trang","phone":"0977 752 089"},{"name":"Ms. Đào Minh Anh","phone":"0366 700 935"}]', '["Mường Thanh Grand Hà Nội Centre","Mường Thanh Grand Hà Nội","Mường Thanh Grand Xa La","Mường Thanh Luxury Vientiane","Khách sạn Mường Thanh Luxury Ninh Bình","Dự án Mường Thanh Ninh Bình","Nhà thầu sự kiện (ca sĩ, quay phim chụp ảnh, ekip sự kiện...)"]', NOW(), NOW(), NULL),
(NULL, 6, '[{"name":"Ms. Bùi Thị Nhung","phone":"0978 359 749"},{"name":"Mr. Dương Đức Giang","phone":"0966 831 976"}]', '["Mường Thanh Grand Tuyên Quang","Mường Thanh Luxury Phú Thọ","Mường Thanh Luxury Cao Bằng","Mường Thanh Luxury Bắc Ninh","Mường Thanh Grand Bắc Giang","Mường Thanh Grand Bãi Cháy","Mường Thanh Grand Hạ Long","Mường Thanh Luxury Hạ Long Centre","Mường Thanh Luxury Hạ Long Centre II","Mường Thanh Luxury Quảng Ninh","Mường Thanh Holiday Suối Mơ"]', NOW(), NOW(), NULL),
(NULL, 7, '[{"name":"Ms. Nguyễn Thái Mỹ","phone":"0362 109 907"},{"name":"Ms. Đỗ Thị Kim Phượng","phone":"0934 530 068"}]', '["Mường Thanh Golf Club Xuân Thành","Mường Thanh Luxury Xuân Thành","Mường Thanh Grand Hà Tĩnh","Mường Thanh Holiday Quảng Bình","Mường Thanh Luxury Nhật Lệ","Mường Thanh Grand Quảng Trị","Mường Thanh Holiday Huế","Mường Thanh Grand Đà Nẵng","Mường Thanh Luxury Đà Nẵng","Mường Thanh Luxury Sông Hàn","Chung cư Mường Thanh Luxury Đà Nẵng","Mường Thanh Holiday Hội An","Mường Thanh Grand Quảng Nam","Mường Thanh Holiday Lý Sơn"]', NOW(), NOW(), NULL),
(NULL, 8, '[{"name":"Ms. Lê Thị Thanh Trà","phone":"094 819 8743"},{"name":"Ms. Nguyễn Phương Hà","phone":"0868 356 133"}]', '["Mường Thanh Lai Châu","Mường Thanh Grand Lào Cai","Mường Thanh Sa Pa","Mường Thanh Luxury Lạng Sơn","Mường Thanh Grand Điện Biên Phủ","Mường Thanh Luxury Điện Biên","Dự án Golf Điện Biên","Mường Thanh Luxury Sơn La","Mường Thanh Luxury Mộc Châu"]', NOW(), NOW(), NULL);
