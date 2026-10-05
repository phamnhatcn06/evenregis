-- ============================================================
-- Seed menu admin (bảng m_controllers) cho Slideshow & Thư viện
-- Ngày: 2026-10-06  |  DB: LOCAL của app Yii (không phải mt_registration_portal)
--
-- Sidebar admin render từ bảng `m_controllers`:
--   - Mục cấp 1 (parent_id = 1) là NHÓM tiêu đề.
--   - Mục con trỏ tới URL = {code}/admin  (vd code '/admin/slideshow' -> '/admin/slideshow/admin').
--   - Chỉ hiển thị mục mà role hiện tại có trong cột `m_roles.controllers` (CSV id).
--
-- Script này tạo 1 nhóm "Website công khai" + 2 mục con (Slideshow, Thư viện).
-- MediaItems không cần mục menu riêng (truy cập từ trong album).
-- ============================================================

-- 1) Nhóm cấp 1
INSERT INTO `m_controllers` (`code`, `parent_id`, `title`, `sort`, `menu`, `permission`, `icon`)
VALUES ('#website', 1, 'Website công khai', 90, 1, 1, 'fa fa-globe');

SET @grp := LAST_INSERT_ID();

-- 2) Các mục con
INSERT INTO `m_controllers` (`code`, `parent_id`, `title`, `sort`, `menu`, `permission`, `icon`) VALUES
('/admin/slideshow',   @grp, 'Slideshow',       10, 1, 1, 'fa fa-image'),
('/admin/mediaAlbums', @grp, 'Thư viện (Album)', 20, 1, 1, 'fa fa-images');

-- 3) (Tuỳ chọn) Cấp quyền hiển thị cho role admin:
--    Thay {ROLE_ID} bằng id role admin. Nối thêm 3 id vừa tạo vào CSV `controllers`.
--    Nên thực hiện qua màn "Phân quyền / Roles" (RolesController) thay vì sửa tay.
--
-- UPDATE `m_roles`
-- SET `controllers` = CONCAT(`controllers`, ',', @grp, ',', @grp+1, ',', @grp+2)
-- WHERE `id` = {ROLE_ID};

-- ============================================================
-- LƯU Ý PHÂN QUYỀN THAO TÁC (create/update/delete):
-- Quyền CRUD lấy từ JWT SSO (PermissionHelper::can). Cần Portal cấp key controller:
--   slideshows, mediaalbums, mediaitems  (hoặc '*' cho admin).
-- Hành động đọc (admin/view) đã nằm trong publicActions nên luôn xem được.
-- ============================================================
