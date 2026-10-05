-- ============================================================
-- Migration: Slideshow + Media (Thư viện) cho Website công khai Đại hội
-- Ngày tạo: 2026-10-06
-- Áp dụng trên DB backend `mt_registration_portal` (InnoDB, utf8mb4).
-- Phục vụ trang frontend/daihoi: hero slider & khu vực Thư viện ảnh/video.
--
-- Các bảng:
--   slideshows     : slide của hero slider trang chủ (ảnh + tiêu đề + nút)
--   media_albums   : album ảnh/video trong Thư viện
--   media_items    : từng ảnh/video thuộc một album
-- ============================================================

-- ------------------------------------------------------------
-- 1. SLIDESHOWS — Slide hero trang chủ
-- ------------------------------------------------------------
CREATE TABLE `slideshows` (
  `id`          bigint UNSIGNED NOT NULL,
  `event_id`    bigint UNSIGNED NOT NULL,
  `title`       varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tiêu đề lớn',
  `subtitle`    varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dòng nhấn/kicker phía trên tiêu đề',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả ngắn hiển thị dưới tiêu đề',
  `image`       varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh nền slide (desktop)',
  `mobile_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh nền cho mobile (tuỳ chọn)',
  `button_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nhãn nút CTA',
  `button_url`  varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Link nút CTA',
  `theme`       varchar(30)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'blue' COMMENT 'Màu nhấn gradient: blue/amber/purple/teal...',
  `sort_order`  int UNSIGNED NOT NULL DEFAULT '0',
  `is_active`   tinyint(1)   NOT NULL DEFAULT '1',
  `start_at`    timestamp NULL DEFAULT NULL COMMENT 'Bắt đầu hiển thị (NULL = ngay)',
  `end_at`      timestamp NULL DEFAULT NULL COMMENT 'Kết thúc hiển thị (NULL = vô hạn)',
  `created_at`  timestamp NULL DEFAULT NULL,
  `updated_at`  timestamp NULL DEFAULT NULL,
  `deleted_at`  timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `slideshows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slideshows_event_id_index` (`event_id`),
  ADD KEY `slideshows_event_id_is_active_sort_order_index` (`event_id`,`is_active`,`sort_order`),
  ADD KEY `slideshows_schedule_index` (`start_at`,`end_at`);

ALTER TABLE `slideshows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `slideshows`
  ADD CONSTRAINT `fk_slideshows_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

-- ------------------------------------------------------------
-- 2. MEDIA_ALBUMS — Album Thư viện
-- ------------------------------------------------------------
CREATE TABLE `media_albums` (
  `id`          bigint UNSIGNED NOT NULL,
  `event_id`    bigint UNSIGNED NOT NULL,
  `title`       varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug`        varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh bìa album',
  `badge`       varchar(50)  COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nhãn góc: ALBUM/BỘ MÔN/GALA...',
  `type`        enum('photo','video','mixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'photo',
  `item_count`  int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lượng item (denormalized, backend tự cập nhật)',
  `sort_order`  int UNSIGNED NOT NULL DEFAULT '0',
  `is_active`   tinyint(1)   NOT NULL DEFAULT '1',
  `created_at`  timestamp NULL DEFAULT NULL,
  `updated_at`  timestamp NULL DEFAULT NULL,
  `deleted_at`  timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `media_albums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_albums_event_id_slug_unique` (`event_id`,`slug`),
  ADD KEY `media_albums_event_id_is_active_sort_order_index` (`event_id`,`is_active`,`sort_order`);

ALTER TABLE `media_albums`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `media_albums`
  ADD CONSTRAINT `fk_media_albums_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

-- ------------------------------------------------------------
-- 3. MEDIA_ITEMS — Ảnh/video trong album
-- ------------------------------------------------------------
CREATE TABLE `media_items` (
  `id`          bigint UNSIGNED NOT NULL,
  `album_id`    bigint UNSIGNED NOT NULL,
  `event_id`    bigint UNSIGNED NOT NULL COMMENT 'Denormalized để lọc nhanh theo sự kiện',
  `type`        enum('image','video') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `url`         varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Đường dẫn ảnh gốc hoặc link video',
  `thumbnail`   varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title`       varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption`     text COLLATE utf8mb4_unicode_ci,
  `sort_order`  int UNSIGNED NOT NULL DEFAULT '0',
  `is_active`   tinyint(1)   NOT NULL DEFAULT '1',
  `created_at`  timestamp NULL DEFAULT NULL,
  `updated_at`  timestamp NULL DEFAULT NULL,
  `deleted_at`  timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `media_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_items_album_id_index` (`album_id`),
  ADD KEY `media_items_event_id_index` (`event_id`),
  ADD KEY `media_items_album_id_sort_order_index` (`album_id`,`sort_order`);

ALTER TABLE `media_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `media_items`
  ADD CONSTRAINT `fk_media_items_album`
    FOREIGN KEY (`album_id`) REFERENCES `media_albums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_media_items_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
