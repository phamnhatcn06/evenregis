-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th6 16, 2026 lúc 06:37 AM
-- Phiên bản máy phục vụ: 8.0.46-0ubuntu0.22.04.2
-- Phiên bản PHP: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `mt_registration_portal_dev`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `alliances`
--

CREATE TABLE `alliances` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `event_content_id` bigint UNSIGNED DEFAULT NULL,
  `org_a_id` bigint UNSIGNED NOT NULL COMMENT 'Đơn vị A (requester)',
  `org_b_id` bigint UNSIGNED NOT NULL COMMENT 'Đơn vị B (target)',
  `request_id` bigint UNSIGNED DEFAULT NULL COMMENT 'alliance_requests.id gốc',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1: active, 2: dissolved',
  `confirmed_at` int UNSIGNED DEFAULT NULL,
  `dissolved_at` int UNSIGNED DEFAULT NULL,
  `dissolved_by` int UNSIGNED DEFAULT NULL,
  `dissolved_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `alliance_requests`
--

CREATE TABLE `alliance_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED DEFAULT NULL,
  `event_content_id` bigint UNSIGNED DEFAULT NULL,
  `requester_org_id` bigint UNSIGNED NOT NULL COMMENT 'Đơn vị gửi yêu cầu',
  `target_org_id` bigint UNSIGNED NOT NULL COMMENT 'Đơn vị nhận yêu cầu',
  `target_registration_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Registration ID of the target property if known',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1:pending, 2:approved, 3:rejected, 4:cancelled',
  `requested_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'unit_accounts.id',
  `requested_at` datetime DEFAULT NULL,
  `reviewed_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'unit_accounts.id hoặc users.id',
  `reviewed_at` datetime DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `alliance_team_orgs`
--

CREATE TABLE `alliance_team_orgs` (
  `id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL COMMENT 'sport_teams.id (is_alliance=1)',
  `organization_id` bigint UNSIGNED NOT NULL,
  `is_lead` tinyint NOT NULL DEFAULT '0' COMMENT 'Đơn vị chủ trì: 0=không, 1=chủ trì',
  `joined_at` int UNSIGNED DEFAULT NULL,
  `created_at` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `approval_workflows`
--

CREATE TABLE `approval_workflows` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã workflow',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên workflow',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả',
  `total_steps` tinyint UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Tổng số bước duyệt',
  `is_default` tinyint NOT NULL DEFAULT '0' COMMENT '0: không mặc định, 1: mặc định',
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT '0: không kích hoạt, 1: kích hoạt',
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `approval_workflow_approvers`
--

CREATE TABLE `approval_workflow_approvers` (
  `id` int UNSIGNED NOT NULL,
  `workflow_id` int UNSIGNED NOT NULL COMMENT 'approval_workflows.id',
  `step_index` tinyint UNSIGNED NOT NULL COMMENT 'Thứ tự bước',
  `step_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên bước',
  `portal_user_id` int UNSIGNED NOT NULL COMMENT 'ID người duyệt',
  `portal_user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên người duyệt',
  `portal_user_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email người duyệt',
  `organization_id` int UNSIGNED DEFAULT NULL COMMENT 'ID tổ chức',
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT '0: không kích hoạt, 1: kích hoạt',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `approve_attendee_logs`
--

CREATE TABLE `approve_attendee_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `status_approve` tinyint NOT NULL DEFAULT '0' COMMENT '0: pending, 1: approved, 2: rejected',
  `reject_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `auth_mail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email người duyệt',
  `old_data` json DEFAULT NULL COMMENT 'Dữ liệu cũ',
  `new_data` json DEFAULT NULL COMMENT 'Dữ liệu mới'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `approve_registration_logs`
--

CREATE TABLE `approve_registration_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `status_approve` tinyint NOT NULL DEFAULT '0' COMMENT '0: Pending, 1: Approved, 2: Rejected',
  `reject_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `auth_mail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email người duyệt',
  `old_data` json DEFAULT NULL COMMENT 'Dữ liệu cũ',
  `new_data` json DEFAULT NULL COMMENT 'Dữ liệu mới',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `attendees`
--

CREATE TABLE `attendees` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `property_id` bigint UNSIGNED NOT NULL,
  `staff_id` bigint UNSIGNED DEFAULT NULL,
  `role_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_card` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CMND/CCCD/Passport',
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` int DEFAULT NULL COMMENT '0: Man, 1: Woman, 2: Other',
  `unit_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_full_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cccd_front_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh mặt trước CCCD',
  `cccd_back_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh mặt sau CCCD',
  `portrait_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh chân dung 530x530px',
  `contract_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File scan hợp đồng lao động',
  `qr_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge_generated` tinyint NOT NULL DEFAULT '0',
  `badge_printed` tinyint NOT NULL DEFAULT '0',
  `transport_id` bigint UNSIGNED DEFAULT NULL,
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `is_team_lead` tinyint NOT NULL DEFAULT '0',
  `is_active` tinyint NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` bigint UNSIGNED DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `approval_status` tinyint NOT NULL DEFAULT '0' COMMENT '0: Pending, 1: Approved, 2: Rejected',
  `join_hotel_date` date DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `current_approval_index` int DEFAULT NULL COMMENT 'Index duyệt hiện tại',
  `next_approval_index` int DEFAULT NULL COMMENT 'Index duyệt tiếp theo',
  `staff_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_starting_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `attendee_roles`
--

CREATE TABLE `attendee_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `assigned_by` bigint UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `auth_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_table` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` bigint UNSIGNED DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `badges`
--

CREATE TABLE `badges` (
  `id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `template_id` bigint UNSIGNED DEFAULT NULL,
  `generated_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `width_mm` decimal(6,2) NOT NULL DEFAULT '85.60',
  `height_mm` decimal(6,2) NOT NULL DEFAULT '53.98',
  `dpi` int NOT NULL DEFAULT '300',
  `generated_at` timestamp NULL DEFAULT NULL,
  `print_count` int NOT NULL DEFAULT '0',
  `last_printed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banquet_events`
--

CREATE TABLE `banquet_events` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_time` datetime NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_tables` int NOT NULL,
  `seats_per_table` int NOT NULL,
  `layout_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `layout_image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canvas_width` int NOT NULL,
  `canvas_height` int NOT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banquet_seats`
--

CREATE TABLE `banquet_seats` (
  `id` bigint UNSIGNED NOT NULL,
  `table_id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_by` bigint UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `seat_number` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banquet_tables`
--

CREATE TABLE `banquet_tables` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `table_number` int NOT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL DEFAULT '10',
  `pos_x` int NOT NULL DEFAULT '0',
  `pos_y` int NOT NULL DEFAULT '0',
  `shape` enum('circle','rectangle') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'circle',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beauty_competitions`
--

CREATE TABLE `beauty_competitions` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beauty_contestants`
--

CREATE TABLE `beauty_contestants` (
  `id` bigint UNSIGNED NOT NULL,
  `contest_id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED DEFAULT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `candidate_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `height_cm` double(8,2) DEFAULT NULL,
  `weight_kg` double(8,2) DEFAULT NULL,
  `measurements` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `talent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `photo_portrait` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_full_body` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `award` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_rank` int DEFAULT NULL,
  `registered_at` datetime DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0: registered, 1: confirmed, 2: withdrawn, 3: disqualified',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beauty_contests`
--

CREATE TABLE `beauty_contests` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gender` enum('female') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'female',
  `age_min` int NOT NULL,
  `age_max` int NOT NULL,
  `registration_open_at` datetime DEFAULT NULL,
  `registration_close_at` datetime DEFAULT NULL,
  `contest_date` date DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `candidate_prefix` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `candidate_start` int NOT NULL,
  `max_per_org` int DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beauty_registrations`
--

CREATE TABLE `beauty_registrations` (
  `id` bigint UNSIGNED NOT NULL,
  `competition_id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `candidate_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beauty_rounds`
--

CREATE TABLE `beauty_rounds` (
  `id` bigint UNSIGNED NOT NULL,
  `contest_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `round_type` enum('ao_dai','bikini','talent','qa','final') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `round_order` int NOT NULL DEFAULT '1',
  `max_score` double(8,2) NOT NULL DEFAULT '10.00',
  `weight` double(8,2) NOT NULL DEFAULT '1.00',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beauty_round_results`
--

CREATE TABLE `beauty_round_results` (
  `id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `round_id` bigint UNSIGNED NOT NULL,
  `score` decimal(8,2) DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `beauty_scores`
--

CREATE TABLE `beauty_scores` (
  `id` bigint UNSIGNED NOT NULL,
  `round_id` bigint UNSIGNED NOT NULL,
  `contestant_id` bigint UNSIGNED NOT NULL,
  `judge_id` bigint UNSIGNED NOT NULL,
  `score` decimal(8,2) NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `scored_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `competitions`
--

CREATE TABLE `competitions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `registration_open_at` datetime DEFAULT NULL,
  `registration_close_at` datetime DEFAULT NULL,
  `candidate_number_prefix` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `candidate_number_start` int NOT NULL DEFAULT '1',
  `candidate_number_pad` int NOT NULL DEFAULT '3',
  `max_per_org` int DEFAULT NULL,
  `has_qualification` tinyint NOT NULL DEFAULT '1',
  `allow_direct_final` tinyint NOT NULL DEFAULT '0',
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `competition_departments`
--

CREATE TABLE `competition_departments` (
  `id` bigint UNSIGNED NOT NULL,
  `competition_id` bigint UNSIGNED NOT NULL,
  `department_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã phòng ban được phép thi',
  `created_at` int UNSIGNED DEFAULT NULL,
  `updated_at` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `competition_registrations`
--

CREATE TABLE `competition_registrations` (
  `id` bigint UNSIGNED NOT NULL,
  `competition_id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Liên kết với bảng registrations',
  `candidate_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `registered_at` datetime DEFAULT NULL,
  `confirmed_by` bigint UNSIGNED DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `competition_rounds`
--

CREATE TABLE `competition_rounds` (
  `id` bigint UNSIGNED NOT NULL,
  `competition_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `round_order` int NOT NULL DEFAULT '1',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` int UNSIGNED DEFAULT NULL,
  `end_time` int UNSIGNED DEFAULT NULL,
  `instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `competition_round_results`
--

CREATE TABLE `competition_round_results` (
  `id` bigint UNSIGNED NOT NULL,
  `round_id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `score` decimal(8,2) DEFAULT NULL,
  `rank` int DEFAULT NULL,
  `passed` tinyint NOT NULL DEFAULT '0',
  `entry_type` enum('qualification','direct') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qualification',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `scored_by` bigint UNSIGNED DEFAULT NULL,
  `scored_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `competition_teams`
--

CREATE TABLE `competition_teams` (
  `id` bigint UNSIGNED NOT NULL,
  `competition_id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED DEFAULT NULL COMMENT 'registrations.id',
  `candidate_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số báo danh của đội',
  `team_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên đội (optional)',
  `captain_id` bigint UNSIGNED DEFAULT NULL COMMENT 'attendees.id - đội trưởng',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1:registered, 2:confirmed, 3:withdrawn',
  `registered_at` datetime DEFAULT NULL,
  `confirmed_by` bigint UNSIGNED DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `competition_team_members`
--

CREATE TABLE `competition_team_members` (
  `id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL COMMENT 'competition_teams.id',
  `attendee_id` bigint UNSIGNED NOT NULL,
  `role` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vai trò trong đội',
  `is_captain` tinyint NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contents`
--

CREATE TABLE `contents` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `allow_alliance` tinyint DEFAULT '0' COMMENT '0: No, 1: Yes',
  `max_alliance_teams` int DEFAULT '0' COMMENT 'Maximum teams allowed in an alliance',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `content_rounds`
--

CREATE TABLE `content_rounds` (
  `id` bigint UNSIGNED NOT NULL,
  `content_id` bigint UNSIGNED DEFAULT NULL,
  `content_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `database_connections`
--

CREATE TABLE `database_connections` (
  `id` bigint UNSIGNED NOT NULL,
  `property_id` bigint NOT NULL,
  `db_connection` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_host` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_port` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_database` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `property_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `division_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unique_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_member` int DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT ' 1: Active, 2: Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `divisions`
--

CREATE TABLE `divisions` (
  `id` bigint UNSIGNED NOT NULL,
  `property_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unique_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_staff` int NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT ' 1: Active, 2: Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `events`
--

CREATE TABLE `events` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `max_sports_per_attendee` int NOT NULL DEFAULT '3' COMMENT 'Số môn thể thao tối đa mỗi người (tính root sports)',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0: draft, 1: active, 2: completed, 3: cancelled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `event_agenda`
--

CREATE TABLE `event_agenda` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `type` enum('plenary','break','workshop','ceremony','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'plenary',
  `is_public` tinyint NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `event_competitions`
--

CREATE TABLE `event_competitions` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `competition_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `event_contents`
--

CREATE TABLE `event_contents` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `content_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `allow_alliance` tinyint NOT NULL DEFAULT '0' COMMENT '0: No, 1: Yes',
  `max_alliance_teams` int NOT NULL DEFAULT '0' COMMENT 'Max teams in one alliance',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `event_roles`
--

CREATE TABLE `event_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `event_sports`
--

CREATE TABLE `event_sports` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `event_sport_alliance_config`
--

CREATE TABLE `event_sport_alliance_config` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `max_members` int UNSIGNED NOT NULL COMMENT 'Số người tối đa từ đơn vị này cho môn này',
  `created_at` int UNSIGNED DEFAULT NULL,
  `updated_at` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `event_units`
--

CREATE TABLE `event_units` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `property_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0: invited, 1: confirmed, 2: declined',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `logs`
--

CREATE TABLE `logs` (
  `id` bigint UNSIGNED NOT NULL,
  `module` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint NOT NULL,
  `model_id` bigint DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `new_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `meals`
--

CREATE TABLE `meals` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meal_date` date NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack','banquet') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `serving_time` datetime NOT NULL,
  `cutoff_deadline` datetime NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_count` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `meal_attendees`
--

CREATE TABLE `meal_attendees` (
  `id` bigint UNSIGNED NOT NULL,
  `meal_id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `table_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0: registered, 1: confirmed, 2: cancelled',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `meal_checkins`
--

CREATE TABLE `meal_checkins` (
  `id` bigint UNSIGNED NOT NULL,
  `meal_id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `check_in_time` datetime NOT NULL,
  `status` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `meal_cutoffs`
--

CREATE TABLE `meal_cutoffs` (
  `id` bigint UNSIGNED NOT NULL,
  `meal_id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `is_cutoff` tinyint NOT NULL DEFAULT '0',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reported_by` bigint UNSIGNED NOT NULL,
  `reported_at` datetime DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `meal_tables`
--

CREATE TABLE `meal_tables` (
  `id` bigint UNSIGNED NOT NULL,
  `meal_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL DEFAULT '10',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `positions`
--

CREATE TABLE `positions` (
  `id` bigint UNSIGNED NOT NULL,
  `unique_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `division_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT ' 1: Active, 2: Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `properties`
--

CREATE TABLE `properties` (
  `id` bigint UNSIGNED NOT NULL,
  `region_id` bigint UNSIGNED DEFAULT NULL,
  `prefix` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `smile_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active_date` date DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `has_golf` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `regionals`
--

CREATE TABLE `regionals` (
  `id` bigint UNSIGNED NOT NULL,
  `content_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `registrations`
--

CREATE TABLE `registrations` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `property_id` bigint UNSIGNED NOT NULL,
  `relation_property_id` bigint UNSIGNED DEFAULT NULL,
  `period_id` bigint UNSIGNED NOT NULL,
  `submitted_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0: draft, 1: submitted, 2: approved, 3: rejected',
  `document` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `current_approval_index` int DEFAULT NULL COMMENT 'Index duyệt hiện tại',
  `next_approval_index` int DEFAULT NULL COMMENT 'Index duyệt tiếp theo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `registration_approvals`
--

CREATE TABLE `registration_approvals` (
  `id` int UNSIGNED NOT NULL,
  `registration_id` int UNSIGNED NOT NULL COMMENT 'registrations.id',
  `workflow_id` int UNSIGNED NOT NULL COMMENT 'approval_workflows.id',
  `current_index` tinyint UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Bước hiện tại',
  `total_steps` tinyint UNSIGNED NOT NULL COMMENT 'Tổng số bước',
  `status` tinyint UNSIGNED NOT NULL DEFAULT '1' COMMENT '1=pending, 2=approved, 3=rejected, 4=revision',
  `started_at` datetime DEFAULT NULL COMMENT 'Thời gian bắt đầu',
  `completed_at` datetime DEFAULT NULL COMMENT 'Thời gian hoàn thành',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `registration_approval_logs`
--

CREATE TABLE `registration_approval_logs` (
  `id` int UNSIGNED NOT NULL,
  `registration_id` int UNSIGNED NOT NULL COMMENT 'registrations.id',
  `step_index` tinyint UNSIGNED NOT NULL COMMENT 'Bước thực hiện',
  `step_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên bước',
  `action` tinyint UNSIGNED NOT NULL COMMENT '1=approved, 2=rejected, 3=revision, 4=submitted, 5=resubmitted',
  `approver_portal_id` int UNSIGNED DEFAULT NULL COMMENT 'ID người thực hiện',
  `approver_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên người thực hiện',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Nhận xét',
  `acted_at` datetime DEFAULT NULL COMMENT 'Thời gian thực hiện',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `registration_details`
--

CREATE TABLE `registration_details` (
  `id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `registration_type` enum('quantity','detailed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quantity' COMMENT 'quantity=số lượng đội, detailed=danh sách cụ thể',
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `content_id` bigint UNSIGNED DEFAULT NULL,
  `sport_id` bigint UNSIGNED DEFAULT NULL,
  `competition_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `registration_detail_attendees`
--

CREATE TABLE `registration_detail_attendees` (
  `id` bigint UNSIGNED NOT NULL,
  `registration_detail_id` bigint UNSIGNED NOT NULL,
  `staff_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `registration_periods`
--

CREATE TABLE `registration_periods` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `max_per_org` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `note` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `registration_period_contents`
--

CREATE TABLE `registration_period_contents` (
  `id` bigint UNSIGNED NOT NULL,
  `period_id` bigint UNSIGNED NOT NULL COMMENT 'FK → registration_periods',
  `content_id` bigint UNSIGNED NOT NULL COMMENT 'FK → contents',
  `created_at` int UNSIGNED DEFAULT NULL COMMENT 'Unix timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nội dung được phép đăng ký trong từng đợt';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sports`
--

CREATE TABLE `sports` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('team','individual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'team',
  `min_per_team_member` int DEFAULT NULL,
  `max_per_team_member` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `document` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sport_matches`
--

CREATE TABLE `sport_matches` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `stage_id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `round` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `match_type` enum('group','knockout','playoff','final') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'group',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `match_order` int DEFAULT NULL,
  `team_a_id` bigint UNSIGNED DEFAULT NULL,
  `team_b_id` bigint UNSIGNED DEFAULT NULL,
  `match_time` datetime DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_score` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0: scheduled, 1: ongoing, 2: completed, 3: cancelled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sport_match_results`
--

CREATE TABLE `sport_match_results` (
  `id` bigint UNSIGNED NOT NULL,
  `match_id` bigint UNSIGNED NOT NULL,
  `score_a` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score_b` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `winner_team_id` bigint UNSIGNED DEFAULT NULL,
  `is_draw` tinyint NOT NULL DEFAULT '0',
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `recorded_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sport_stages`
--

CREATE TABLE `sport_stages` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stage_type` enum('qualification','playoff','final') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qualification',
  `stage_order` int NOT NULL DEFAULT '1',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0: upcoming, 1: ongoing, 2: completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sport_stage_teams`
--

CREATE TABLE `sport_stage_teams` (
  `id` bigint UNSIGNED NOT NULL,
  `stage_id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL,
  `entry_type` enum('registered','promoted','playoff_winner') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `qualified_from` bigint UNSIGNED DEFAULT NULL,
  `seed` int DEFAULT NULL,
  `final_rank` int DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0: active, 1: eliminated, 2: withdrawn',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sport_standings`
--

CREATE TABLE `sport_standings` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL,
  `played` int NOT NULL DEFAULT '0',
  `won` int NOT NULL DEFAULT '0',
  `drawn` int NOT NULL DEFAULT '0',
  `lost` int NOT NULL DEFAULT '0',
  `goals_for` int NOT NULL DEFAULT '0',
  `goals_against` int NOT NULL DEFAULT '0',
  `points` int NOT NULL DEFAULT '0',
  `rank` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sport_teams`
--

CREATE TABLE `sport_teams` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `registration_id` bigint UNSIGNED DEFAULT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `property_id` bigint UNSIGNED DEFAULT NULL,
  `is_alliance` tinyint NOT NULL DEFAULT '0' COMMENT 'Đội liên quân: 0=đội đơn vị, 1=đội liên quân',
  `alliance_org_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_alliance_team` tinyint NOT NULL DEFAULT '0' COMMENT 'Cờ đội liên quân: 0=không, 1=có',
  `short_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sport_team_members`
--

CREATE TABLE `sport_team_members` (
  `id` bigint UNSIGNED NOT NULL,
  `sport_team_id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jersey_number` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_captain` tinyint NOT NULL DEFAULT '0',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `staffs`
--

CREATE TABLE `staffs` (
  `id` bigint UNSIGNED NOT NULL,
  `unique_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_card` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rank_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `division_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_lecturer` tinyint NOT NULL DEFAULT '2',
  `curren_job_id` tinyint NOT NULL,
  `lecturer_type` bigint DEFAULT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personal_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `terminate_date` date DEFAULT NULL,
  `join_hotel_date` date DEFAULT NULL,
  `end_testing_date` date DEFAULT NULL,
  `married` tinyint(1) DEFAULT NULL,
  `gender` int DEFAULT NULL COMMENT '0: Man, 1: Woman, 2: Other',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT ' 1: Active, 2: Inactive',
  `staff_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal' COMMENT 'smile, internal, external',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `contract_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `talent_categories`
--

CREATE TABLE `talent_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('solo','group') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'solo',
  `min_members` int NOT NULL,
  `max_members` int NOT NULL,
  `max_duration_seconds` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `talent_entries`
--

CREATE TABLE `talent_entries` (
  `id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `property_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `duration_seconds` int DEFAULT NULL,
  `music_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performance_order` int DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=draft, 2=submitted, 3=approved, 4=rejected, 5=performed',
  `final_score` double(8,2) DEFAULT NULL,
  `final_rank` int DEFAULT NULL,
  `award` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `document` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON danh sách tệp đính kèm',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung tiết mục',
  `director` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Người chịu trách nhiệm',
  `director_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số điện thoại người chịu trách nhiệm',
  `origin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Xuất xứ tiết mục',
  `is_alliance_team` tinyint DEFAULT '0' COMMENT 'Cờ đội liên quân: 0=không, 1=có',
  `submitted_at` datetime DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `talent_entry_members`
--

CREATE TABLE `talent_entry_members` (
  `id` bigint UNSIGNED NOT NULL,
  `entry_id` bigint UNSIGNED NOT NULL,
  `attendee_id` bigint UNSIGNED NOT NULL,
  `role` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_lead` tinyint NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `talent_scores`
--

CREATE TABLE `talent_scores` (
  `id` bigint UNSIGNED NOT NULL,
  `entry_id` bigint UNSIGNED NOT NULL,
  `judge_id` bigint UNSIGNED NOT NULL,
  `score` double(8,2) NOT NULL,
  `criteria` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scored_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `talent_shows`
--

CREATE TABLE `talent_shows` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `registration_open_at` datetime DEFAULT NULL,
  `registration_close_at` datetime DEFAULT NULL,
  `show_date` date DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_entries_per_org` int DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transports`
--

CREATE TABLE `transports` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `unit_accounts`
--

CREATE TABLE `unit_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `alliances`
--
ALTER TABLE `alliances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_alliance_event_orgs` (`event_id`,`org_a_id`,`org_b_id`,`event_content_id`),
  ADD KEY `alliances_org_a_id_foreign` (`org_a_id`),
  ADD KEY `alliances_org_b_id_foreign` (`org_b_id`),
  ADD KEY `alliances_event_content_id_index` (`event_content_id`);

--
-- Chỉ mục cho bảng `alliance_requests`
--
ALTER TABLE `alliance_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_alliance_request` (`event_id`,`requester_org_id`,`target_org_id`,`event_content_id`),
  ADD KEY `alliance_requests_requester_org_id_foreign` (`requester_org_id`),
  ADD KEY `alliance_requests_target_org_id_foreign` (`target_org_id`),
  ADD KEY `alliance_requests_event_content_id_index` (`event_content_id`),
  ADD KEY `idx_alliance_requests_registration` (`registration_id`);

--
-- Chỉ mục cho bảng `alliance_team_orgs`
--
ALTER TABLE `alliance_team_orgs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_alliance_team_org` (`team_id`,`organization_id`),
  ADD KEY `idx_ato_org` (`organization_id`);

--
-- Chỉ mục cho bảng `approval_workflows`
--
ALTER TABLE `approval_workflows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_approval_workflows_code` (`code`),
  ADD UNIQUE KEY `approval_workflows_code_unique` (`code`);

--
-- Chỉ mục cho bảng `approval_workflow_approvers`
--
ALTER TABLE `approval_workflow_approvers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_approval_workflow_approvers_workflow` (`workflow_id`),
  ADD KEY `idx_approval_workflow_approvers_user` (`portal_user_id`);

--
-- Chỉ mục cho bảng `approve_attendee_logs`
--
ALTER TABLE `approve_attendee_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_approve_attendee_logs_attendee` (`attendee_id`);

--
-- Chỉ mục cho bảng `approve_registration_logs`
--
ALTER TABLE `approve_registration_logs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `attendees`
--
ALTER TABLE `attendees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendees_qr_token_unique` (`qr_token`),
  ADD UNIQUE KEY `attendees_badge_number_unique` (`badge_number`),
  ADD KEY `idx_attendees_event` (`event_id`),
  ADD KEY `idx_attendees_registration` (`registration_id`),
  ADD KEY `idx_attendees_property` (`property_id`),
  ADD KEY `idx_attendees_staff` (`staff_id`),
  ADD KEY `idx_attendees_qr` (`qr_token`),
  ADD KEY `idx_attendees_team_lead` (`is_team_lead`),
  ADD KEY `attendees_role_id_foreign` (`role_id`),
  ADD KEY `attendees_transport_id_foreign` (`transport_id`);

--
-- Chỉ mục cho bảng `attendee_roles`
--
ALTER TABLE `attendee_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attendee_role` (`attendee_id`,`role_id`),
  ADD KEY `idx_attendee_roles_role` (`role_id`);

--
-- Chỉ mục cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_module` (`module`),
  ADD KEY `idx_audit_target` (`target_table`,`target_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_auth_email` (`auth_email`);

--
-- Chỉ mục cho bảng `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `badges_attendee_id_unique` (`attendee_id`);

--
-- Chỉ mục cho bảng `banquet_events`
--
ALTER TABLE `banquet_events`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `banquet_seats`
--
ALTER TABLE `banquet_seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_table_seat` (`table_id`,`seat_number`),
  ADD KEY `banquet_seats_attendee_id_foreign` (`attendee_id`);

--
-- Chỉ mục cho bảng `banquet_tables`
--
ALTER TABLE `banquet_tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banquet_tables_event_id_foreign` (`event_id`);

--
-- Chỉ mục cho bảng `beauty_competitions`
--
ALTER TABLE `beauty_competitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beauty_competitions_event_id_foreign` (`event_id`);

--
-- Chỉ mục cho bảng `beauty_contestants`
--
ALTER TABLE `beauty_contestants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `beauty_contestants_candidate_number_unique` (`candidate_number`),
  ADD KEY `beauty_contestants_contest_id_foreign` (`contest_id`),
  ADD KEY `beauty_contestants_attendee_id_foreign` (`attendee_id`),
  ADD KEY `idx_beauty_contestants_registration` (`registration_id`);

--
-- Chỉ mục cho bảng `beauty_contests`
--
ALTER TABLE `beauty_contests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beauty_contests_event_id_foreign` (`event_id`);

--
-- Chỉ mục cho bảng `beauty_registrations`
--
ALTER TABLE `beauty_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beauty_registrations_competition_id_foreign` (`competition_id`),
  ADD KEY `beauty_registrations_attendee_id_foreign` (`attendee_id`);

--
-- Chỉ mục cho bảng `beauty_rounds`
--
ALTER TABLE `beauty_rounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beauty_rounds_contest_id_foreign` (`contest_id`);

--
-- Chỉ mục cho bảng `beauty_round_results`
--
ALTER TABLE `beauty_round_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beauty_round_results_registration_id_foreign` (`registration_id`),
  ADD KEY `beauty_round_results_round_id_foreign` (`round_id`);

--
-- Chỉ mục cho bảng `beauty_scores`
--
ALTER TABLE `beauty_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beauty_scores_round_id_foreign` (`round_id`),
  ADD KEY `beauty_scores_contestant_id_foreign` (`contestant_id`);

--
-- Chỉ mục cho bảng `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `competition_departments`
--
ALTER TABLE `competition_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_comp_dept` (`competition_id`,`department_code`),
  ADD KEY `idx_cd_dept` (`department_code`);

--
-- Chỉ mục cho bảng `competition_registrations`
--
ALTER TABLE `competition_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_comp_reg_attendee` (`competition_id`,`attendee_id`),
  ADD UNIQUE KEY `competition_registrations_candidate_number_unique` (`candidate_number`),
  ADD KEY `idx_comp_reg_status` (`status`),
  ADD KEY `competition_registrations_attendee_id_foreign` (`attendee_id`),
  ADD KEY `idx_comp_reg_registration_id` (`registration_id`);

--
-- Chỉ mục cho bảng `competition_rounds`
--
ALTER TABLE `competition_rounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comp_rounds_comp` (`competition_id`);

--
-- Chỉ mục cho bảng `competition_round_results`
--
ALTER TABLE `competition_round_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_round_registration` (`round_id`,`registration_id`),
  ADD KEY `idx_crr_passed` (`passed`),
  ADD KEY `competition_round_results_registration_id_foreign` (`registration_id`);

--
-- Chỉ mục cho bảng `competition_teams`
--
ALTER TABLE `competition_teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_comp_team_candidate` (`competition_id`,`candidate_number`),
  ADD KEY `idx_comp_teams_registration` (`registration_id`),
  ADD KEY `idx_comp_teams_captain` (`captain_id`);

--
-- Chỉ mục cho bảng `competition_team_members`
--
ALTER TABLE `competition_team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_team_attendee` (`team_id`,`attendee_id`),
  ADD KEY `idx_ctm_attendee` (`attendee_id`);

--
-- Chỉ mục cho bảng `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contents_code_unique` (`code`);

--
-- Chỉ mục cho bảng `content_rounds`
--
ALTER TABLE `content_rounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_rounds_content_code_index` (`content_code`),
  ADD KEY `content_rounds_code_index` (`code`);

--
-- Chỉ mục cho bảng `database_connections`
--
ALTER TABLE `database_connections`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_unique_code_unique` (`unique_code`);

--
-- Chỉ mục cho bảng `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `divisions_unique_code_unique` (`unique_code`),
  ADD KEY `divisions_property_code_code_index` (`property_code`,`code`);

--
-- Chỉ mục cho bảng `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `events_code_unique` (`code`);

--
-- Chỉ mục cho bảng `event_agenda`
--
ALTER TABLE `event_agenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agenda_time` (`start_time`);

--
-- Chỉ mục cho bảng `event_competitions`
--
ALTER TABLE `event_competitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_competition` (`event_id`,`competition_id`),
  ADD KEY `event_competitions_competition_id_foreign` (`competition_id`);

--
-- Chỉ mục cho bảng `event_contents`
--
ALTER TABLE `event_contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_content` (`event_id`,`content_id`),
  ADD KEY `event_contents_content_id_foreign` (`content_id`);

--
-- Chỉ mục cho bảng `event_roles`
--
ALTER TABLE `event_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_roles_code_unique` (`code`);

--
-- Chỉ mục cho bảng `event_sports`
--
ALTER TABLE `event_sports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_sport` (`event_id`,`sport_id`),
  ADD KEY `event_sports_sport_id_foreign` (`sport_id`);

--
-- Chỉ mục cho bảng `event_sport_alliance_config`
--
ALTER TABLE `event_sport_alliance_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_esac_event_sport_org` (`event_id`,`sport_id`,`organization_id`),
  ADD KEY `idx_esac_event_sport` (`event_id`,`sport_id`),
  ADD KEY `fk_esac_sport` (`sport_id`),
  ADD KEY `fk_esac_org` (`organization_id`);

--
-- Chỉ mục cho bảng `event_units`
--
ALTER TABLE `event_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_unit` (`event_id`,`property_id`),
  ADD KEY `event_units_property_id_foreign` (`property_id`);

--
-- Chỉ mục cho bảng `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_meal_date` (`meal_date`),
  ADD KEY `meals_event_id_foreign` (`event_id`);

--
-- Chỉ mục cho bảng `meal_attendees`
--
ALTER TABLE `meal_attendees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_attendees_meal_id_foreign` (`meal_id`),
  ADD KEY `meal_attendees_attendee_id_foreign` (`attendee_id`),
  ADD KEY `meal_attendees_table_id_foreign` (`table_id`);

--
-- Chỉ mục cho bảng `meal_checkins`
--
ALTER TABLE `meal_checkins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_checkins_meal_id_foreign` (`meal_id`),
  ADD KEY `meal_checkins_attendee_id_foreign` (`attendee_id`);

--
-- Chỉ mục cho bảng `meal_cutoffs`
--
ALTER TABLE `meal_cutoffs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_cutoffs_meal_id_foreign` (`meal_id`),
  ADD KEY `meal_cutoffs_attendee_id_foreign` (`attendee_id`);

--
-- Chỉ mục cho bảng `meal_tables`
--
ALTER TABLE `meal_tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_tables_meal_id_foreign` (`meal_id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `positions_unique_code_unique` (`unique_code`),
  ADD KEY `positions_property_code_code_index` (`property_code`,`code`),
  ADD KEY `positions_division_code_index` (`division_code`),
  ADD KEY `positions_department_code_index` (`department_code`);

--
-- Chỉ mục cho bảng `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `properties_prefix_unique` (`prefix`),
  ADD UNIQUE KEY `properties_code_unique` (`code`),
  ADD KEY `properties_region_id_foreign` (`region_id`);

--
-- Chỉ mục cho bảng `regionals`
--
ALTER TABLE `regionals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_regionals_code_content` (`code`,`content_id`),
  ADD KEY `regionals_content_id_foreign` (`content_id`);

--
-- Chỉ mục cho bảng `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_registrations_period` (`period_id`),
  ADD KEY `idx_registrations_event` (`event_id`),
  ADD KEY `registrations_relation_property_id_foreign` (`relation_property_id`),
  ADD KEY `idx_registrations_property` (`property_id`);

--
-- Chỉ mục cho bảng `registration_approvals`
--
ALTER TABLE `registration_approvals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_registration_approvals_reg` (`registration_id`),
  ADD KEY `idx_registration_approvals_workflow` (`workflow_id`);

--
-- Chỉ mục cho bảng `registration_approval_logs`
--
ALTER TABLE `registration_approval_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_registration_approval_logs_reg` (`registration_id`),
  ADD KEY `idx_registration_approval_logs_approver` (`approver_portal_id`);

--
-- Chỉ mục cho bảng `registration_details`
--
ALTER TABLE `registration_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_regdetail_registration` (`registration_id`),
  ADD KEY `registration_details_role_id_foreign` (`role_id`),
  ADD KEY `registration_details_content_id_foreign` (`content_id`),
  ADD KEY `registration_details_sport_id_foreign` (`sport_id`),
  ADD KEY `registration_details_competition_id_foreign` (`competition_id`);

--
-- Chỉ mục cho bảng `registration_detail_attendees`
--
ALTER TABLE `registration_detail_attendees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rd_staff_code` (`registration_detail_id`,`staff_code`);

--
-- Chỉ mục cho bảng `registration_periods`
--
ALTER TABLE `registration_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_periods_event_id_foreign` (`event_id`);

--
-- Chỉ mục cho bảng `registration_period_contents`
--
ALTER TABLE `registration_period_contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_period_content` (`period_id`,`content_id`),
  ADD KEY `idx_period_id` (`period_id`),
  ADD KEY `idx_content_id` (`content_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roles_event_id_foreign` (`event_id`);

--
-- Chỉ mục cho bảng `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sports_code_unique` (`code`),
  ADD KEY `idx_sports_parent` (`parent_id`);

--
-- Chỉ mục cho bảng `sport_matches`
--
ALTER TABLE `sport_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sport_matches_event_id_foreign` (`event_id`),
  ADD KEY `sport_matches_sport_id_foreign` (`sport_id`),
  ADD KEY `sport_matches_stage_id_foreign` (`stage_id`),
  ADD KEY `sport_matches_team_a_id_foreign` (`team_a_id`),
  ADD KEY `sport_matches_team_b_id_foreign` (`team_b_id`);

--
-- Chỉ mục cho bảng `sport_match_results`
--
ALTER TABLE `sport_match_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sport_match_results_match_id_unique` (`match_id`),
  ADD KEY `sport_match_results_winner_team_id_foreign` (`winner_team_id`);

--
-- Chỉ mục cho bảng `sport_stages`
--
ALTER TABLE `sport_stages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stages_event` (`event_id`),
  ADD KEY `idx_stages_sport` (`sport_id`);

--
-- Chỉ mục cho bảng `sport_stage_teams`
--
ALTER TABLE `sport_stage_teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_stage_team` (`stage_id`,`team_id`),
  ADD KEY `idx_sst_entry` (`entry_type`),
  ADD KEY `sport_stage_teams_team_id_foreign` (`team_id`),
  ADD KEY `sport_stage_teams_qualified_from_foreign` (`qualified_from`);

--
-- Chỉ mục cho bảng `sport_standings`
--
ALTER TABLE `sport_standings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_standings_team` (`event_id`,`sport_id`,`team_id`),
  ADD KEY `sport_standings_sport_id_foreign` (`sport_id`),
  ADD KEY `sport_standings_team_id_foreign` (`team_id`);

--
-- Chỉ mục cho bảng `sport_teams`
--
ALTER TABLE `sport_teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sport_teams_code_unique` (`code`),
  ADD KEY `idx_sport_teams_event` (`event_id`),
  ADD KEY `idx_sport_teams_sport` (`sport_id`),
  ADD KEY `idx_sport_teams_property` (`property_id`),
  ADD KEY `idx_sport_teams_registration` (`registration_id`);

--
-- Chỉ mục cho bảng `sport_team_members`
--
ALTER TABLE `sport_team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sport_team_members_code_unique` (`code`),
  ADD KEY `idx_team_members_team` (`sport_team_id`),
  ADD KEY `idx_team_members_attendee` (`attendee_id`);

--
-- Chỉ mục cho bảng `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staffs_unique_code_unique` (`unique_code`),
  ADD KEY `staffs_property_code_index` (`property_code`),
  ADD KEY `staffs_division_code_index` (`division_code`),
  ADD KEY `staffs_department_code_index` (`department_code`),
  ADD KEY `staffs_position_code_index` (`position_code`),
  ADD KEY `staffs_code_index` (`code`),
  ADD KEY `staffs_id_card_index` (`id_card`),
  ADD KEY `staffs_full_name_index` (`full_name`),
  ADD KEY `staffs_email_index` (`email`),
  ADD KEY `staffs_status_index` (`status`),
  ADD KEY `staffs_curren_job_id_index` (`curren_job_id`);

--
-- Chỉ mục cho bảng `talent_categories`
--
ALTER TABLE `talent_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `talent_categories_code_unique` (`code`);

--
-- Chỉ mục cho bảng `talent_entries`
--
ALTER TABLE `talent_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `talent_entries_registration_id_foreign` (`registration_id`),
  ADD KEY `talent_entries_category_id_foreign` (`category_id`),
  ADD KEY `talent_entries_property_id_foreign` (`property_id`);

--
-- Chỉ mục cho bảng `talent_entry_members`
--
ALTER TABLE `talent_entry_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `talent_entry_members_entry_id_foreign` (`entry_id`),
  ADD KEY `talent_entry_members_attendee_id_foreign` (`attendee_id`);

--
-- Chỉ mục cho bảng `talent_scores`
--
ALTER TABLE `talent_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `talent_scores_entry_id_foreign` (`entry_id`);

--
-- Chỉ mục cho bảng `talent_shows`
--
ALTER TABLE `talent_shows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `talent_shows_event_id_foreign` (`event_id`);

--
-- Chỉ mục cho bảng `transports`
--
ALTER TABLE `transports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transports_code_unique` (`code`);

--
-- Chỉ mục cho bảng `unit_accounts`
--
ALTER TABLE `unit_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_accounts_username_unique` (`username`),
  ADD KEY `unit_accounts_department_id_foreign` (`department_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `alliances`
--
ALTER TABLE `alliances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `alliance_requests`
--
ALTER TABLE `alliance_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `alliance_team_orgs`
--
ALTER TABLE `alliance_team_orgs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `approval_workflows`
--
ALTER TABLE `approval_workflows`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `approval_workflow_approvers`
--
ALTER TABLE `approval_workflow_approvers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `approve_attendee_logs`
--
ALTER TABLE `approve_attendee_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `approve_registration_logs`
--
ALTER TABLE `approve_registration_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `attendees`
--
ALTER TABLE `attendees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `attendee_roles`
--
ALTER TABLE `attendee_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `badges`
--
ALTER TABLE `badges`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `banquet_events`
--
ALTER TABLE `banquet_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `banquet_seats`
--
ALTER TABLE `banquet_seats`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `banquet_tables`
--
ALTER TABLE `banquet_tables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `beauty_competitions`
--
ALTER TABLE `beauty_competitions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `beauty_contestants`
--
ALTER TABLE `beauty_contestants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `beauty_contests`
--
ALTER TABLE `beauty_contests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `beauty_registrations`
--
ALTER TABLE `beauty_registrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `beauty_rounds`
--
ALTER TABLE `beauty_rounds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `beauty_round_results`
--
ALTER TABLE `beauty_round_results`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `beauty_scores`
--
ALTER TABLE `beauty_scores`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `competition_departments`
--
ALTER TABLE `competition_departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `competition_registrations`
--
ALTER TABLE `competition_registrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `competition_rounds`
--
ALTER TABLE `competition_rounds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `competition_round_results`
--
ALTER TABLE `competition_round_results`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `competition_teams`
--
ALTER TABLE `competition_teams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `competition_team_members`
--
ALTER TABLE `competition_team_members`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `contents`
--
ALTER TABLE `contents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `content_rounds`
--
ALTER TABLE `content_rounds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `database_connections`
--
ALTER TABLE `database_connections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `event_agenda`
--
ALTER TABLE `event_agenda`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `event_competitions`
--
ALTER TABLE `event_competitions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `event_contents`
--
ALTER TABLE `event_contents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `event_roles`
--
ALTER TABLE `event_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `event_sports`
--
ALTER TABLE `event_sports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `event_sport_alliance_config`
--
ALTER TABLE `event_sport_alliance_config`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `event_units`
--
ALTER TABLE `event_units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `meals`
--
ALTER TABLE `meals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `meal_attendees`
--
ALTER TABLE `meal_attendees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `meal_checkins`
--
ALTER TABLE `meal_checkins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `meal_cutoffs`
--
ALTER TABLE `meal_cutoffs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `meal_tables`
--
ALTER TABLE `meal_tables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `positions`
--
ALTER TABLE `positions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `regionals`
--
ALTER TABLE `regionals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `registration_approvals`
--
ALTER TABLE `registration_approvals`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `registration_approval_logs`
--
ALTER TABLE `registration_approval_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `registration_details`
--
ALTER TABLE `registration_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `registration_detail_attendees`
--
ALTER TABLE `registration_detail_attendees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `registration_periods`
--
ALTER TABLE `registration_periods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `registration_period_contents`
--
ALTER TABLE `registration_period_contents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sports`
--
ALTER TABLE `sports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sport_matches`
--
ALTER TABLE `sport_matches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sport_match_results`
--
ALTER TABLE `sport_match_results`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sport_stages`
--
ALTER TABLE `sport_stages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sport_stage_teams`
--
ALTER TABLE `sport_stage_teams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sport_standings`
--
ALTER TABLE `sport_standings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sport_teams`
--
ALTER TABLE `sport_teams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sport_team_members`
--
ALTER TABLE `sport_team_members`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `talent_categories`
--
ALTER TABLE `talent_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `talent_entries`
--
ALTER TABLE `talent_entries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `talent_entry_members`
--
ALTER TABLE `talent_entry_members`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `talent_scores`
--
ALTER TABLE `talent_scores`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `talent_shows`
--
ALTER TABLE `talent_shows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `transports`
--
ALTER TABLE `transports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `unit_accounts`
--
ALTER TABLE `unit_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `alliances`
--
ALTER TABLE `alliances`
  ADD CONSTRAINT `alliances_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alliances_org_a_id_foreign` FOREIGN KEY (`org_a_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `alliances_org_b_id_foreign` FOREIGN KEY (`org_b_id`) REFERENCES `properties` (`id`);

--
-- Các ràng buộc cho bảng `alliance_requests`
--
ALTER TABLE `alliance_requests`
  ADD CONSTRAINT `alliance_requests_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alliance_requests_requester_org_id_foreign` FOREIGN KEY (`requester_org_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `alliance_requests_target_org_id_foreign` FOREIGN KEY (`target_org_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `fk_alliance_requests_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `alliance_team_orgs`
--
ALTER TABLE `alliance_team_orgs`
  ADD CONSTRAINT `fk_ato_org` FOREIGN KEY (`organization_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `fk_ato_team` FOREIGN KEY (`team_id`) REFERENCES `sport_teams` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `approve_attendee_logs`
--
ALTER TABLE `approve_attendee_logs`
  ADD CONSTRAINT `approve_attendee_logs_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`);

--
-- Các ràng buộc cho bảng `attendees`
--
ALTER TABLE `attendees`
  ADD CONSTRAINT `attendees_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `attendees_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `attendees_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`),
  ADD CONSTRAINT `attendees_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`id`),
  ADD CONSTRAINT `attendees_transport_id_foreign` FOREIGN KEY (`transport_id`) REFERENCES `transports` (`id`);

--
-- Các ràng buộc cho bảng `attendee_roles`
--
ALTER TABLE `attendee_roles`
  ADD CONSTRAINT `attendee_roles_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendee_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Các ràng buộc cho bảng `badges`
--
ALTER TABLE `badges`
  ADD CONSTRAINT `badges_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `banquet_seats`
--
ALTER TABLE `banquet_seats`
  ADD CONSTRAINT `banquet_seats_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `banquet_seats_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `banquet_tables` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `banquet_tables`
--
ALTER TABLE `banquet_tables`
  ADD CONSTRAINT `banquet_tables_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `banquet_events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `beauty_competitions`
--
ALTER TABLE `beauty_competitions`
  ADD CONSTRAINT `beauty_competitions_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `beauty_contestants`
--
ALTER TABLE `beauty_contestants`
  ADD CONSTRAINT `beauty_contestants_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `beauty_contestants_contest_id_foreign` FOREIGN KEY (`contest_id`) REFERENCES `beauty_contests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_beauty_contestants_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `beauty_contests`
--
ALTER TABLE `beauty_contests`
  ADD CONSTRAINT `beauty_contests_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `beauty_registrations`
--
ALTER TABLE `beauty_registrations`
  ADD CONSTRAINT `beauty_registrations_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `beauty_registrations_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `beauty_competitions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `beauty_rounds`
--
ALTER TABLE `beauty_rounds`
  ADD CONSTRAINT `beauty_rounds_contest_id_foreign` FOREIGN KEY (`contest_id`) REFERENCES `beauty_contests` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `beauty_round_results`
--
ALTER TABLE `beauty_round_results`
  ADD CONSTRAINT `beauty_round_results_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `beauty_registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `beauty_round_results_round_id_foreign` FOREIGN KEY (`round_id`) REFERENCES `beauty_rounds` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `beauty_scores`
--
ALTER TABLE `beauty_scores`
  ADD CONSTRAINT `beauty_scores_contestant_id_foreign` FOREIGN KEY (`contestant_id`) REFERENCES `beauty_contestants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `beauty_scores_round_id_foreign` FOREIGN KEY (`round_id`) REFERENCES `beauty_rounds` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `competition_departments`
--
ALTER TABLE `competition_departments`
  ADD CONSTRAINT `fk_cd_comp` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `competition_registrations`
--
ALTER TABLE `competition_registrations`
  ADD CONSTRAINT `competition_registrations_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_registrations_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_registrations_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `competition_rounds`
--
ALTER TABLE `competition_rounds`
  ADD CONSTRAINT `competition_rounds_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `competition_round_results`
--
ALTER TABLE `competition_round_results`
  ADD CONSTRAINT `competition_round_results_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `competition_registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_round_results_round_id_foreign` FOREIGN KEY (`round_id`) REFERENCES `competition_rounds` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `event_competitions`
--
ALTER TABLE `event_competitions`
  ADD CONSTRAINT `event_competitions_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_competitions_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `event_contents`
--
ALTER TABLE `event_contents`
  ADD CONSTRAINT `event_contents_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_contents_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `event_sports`
--
ALTER TABLE `event_sports`
  ADD CONSTRAINT `event_sports_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_sports_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `event_sport_alliance_config`
--
ALTER TABLE `event_sport_alliance_config`
  ADD CONSTRAINT `fk_esac_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_esac_org` FOREIGN KEY (`organization_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_esac_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `event_units`
--
ALTER TABLE `event_units`
  ADD CONSTRAINT `event_units_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_units_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `meal_attendees`
--
ALTER TABLE `meal_attendees`
  ADD CONSTRAINT `meal_attendees_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_attendees_meal_id_foreign` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_attendees_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `meal_tables` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `meal_checkins`
--
ALTER TABLE `meal_checkins`
  ADD CONSTRAINT `meal_checkins_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_checkins_meal_id_foreign` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `meal_cutoffs`
--
ALTER TABLE `meal_cutoffs`
  ADD CONSTRAINT `meal_cutoffs_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_cutoffs_meal_id_foreign` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `meal_tables`
--
ALTER TABLE `meal_tables`
  ADD CONSTRAINT `meal_tables_meal_id_foreign` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regionals` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `regionals`
--
ALTER TABLE `regionals`
  ADD CONSTRAINT `regionals_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `registrations_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `registration_periods` (`id`),
  ADD CONSTRAINT `registrations_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `registrations_relation_property_id_foreign` FOREIGN KEY (`relation_property_id`) REFERENCES `properties` (`id`);

--
-- Các ràng buộc cho bảng `registration_details`
--
ALTER TABLE `registration_details`
  ADD CONSTRAINT `registration_details_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`),
  ADD CONSTRAINT `registration_details_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`),
  ADD CONSTRAINT `registration_details_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registration_details_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `registration_details_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`);

--
-- Các ràng buộc cho bảng `registration_detail_attendees`
--
ALTER TABLE `registration_detail_attendees`
  ADD CONSTRAINT `fk_rd_attendee_detail` FOREIGN KEY (`registration_detail_id`) REFERENCES `registration_details` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `registration_periods`
--
ALTER TABLE `registration_periods`
  ADD CONSTRAINT `registration_periods_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `registration_period_contents`
--
ALTER TABLE `registration_period_contents`
  ADD CONSTRAINT `fk_rpc_content` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rpc_period` FOREIGN KEY (`period_id`) REFERENCES `registration_periods` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sports`
--
ALTER TABLE `sports`
  ADD CONSTRAINT `sports_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `sports` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `sport_matches`
--
ALTER TABLE `sport_matches`
  ADD CONSTRAINT `sport_matches_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sport_matches_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sport_matches_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `sport_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sport_matches_team_a_id_foreign` FOREIGN KEY (`team_a_id`) REFERENCES `sport_teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sport_matches_team_b_id_foreign` FOREIGN KEY (`team_b_id`) REFERENCES `sport_teams` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `sport_match_results`
--
ALTER TABLE `sport_match_results`
  ADD CONSTRAINT `sport_match_results_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `sport_matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sport_match_results_winner_team_id_foreign` FOREIGN KEY (`winner_team_id`) REFERENCES `sport_teams` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `sport_stages`
--
ALTER TABLE `sport_stages`
  ADD CONSTRAINT `sport_stages_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sport_stages_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sport_stage_teams`
--
ALTER TABLE `sport_stage_teams`
  ADD CONSTRAINT `sport_stage_teams_qualified_from_foreign` FOREIGN KEY (`qualified_from`) REFERENCES `sport_stages` (`id`),
  ADD CONSTRAINT `sport_stage_teams_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `sport_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sport_stage_teams_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `sport_teams` (`id`);

--
-- Các ràng buộc cho bảng `sport_standings`
--
ALTER TABLE `sport_standings`
  ADD CONSTRAINT `sport_standings_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `sport_standings_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`),
  ADD CONSTRAINT `sport_standings_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `sport_teams` (`id`);

--
-- Các ràng buộc cho bảng `sport_teams`
--
ALTER TABLE `sport_teams`
  ADD CONSTRAINT `fk_sport_teams_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sport_teams_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sport_teams_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sport_teams_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sport_team_members`
--
ALTER TABLE `sport_team_members`
  ADD CONSTRAINT `sport_team_members_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sport_team_members_sport_team_id_foreign` FOREIGN KEY (`sport_team_id`) REFERENCES `sport_teams` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `talent_entries`
--
ALTER TABLE `talent_entries`
  ADD CONSTRAINT `talent_entries_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `talent_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `talent_entries_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `talent_entries_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `talent_entry_members`
--
ALTER TABLE `talent_entry_members`
  ADD CONSTRAINT `talent_entry_members_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `talent_entry_members_entry_id_foreign` FOREIGN KEY (`entry_id`) REFERENCES `talent_entries` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `talent_scores`
--
ALTER TABLE `talent_scores`
  ADD CONSTRAINT `talent_scores_entry_id_foreign` FOREIGN KEY (`entry_id`) REFERENCES `talent_entries` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `talent_shows`
--
ALTER TABLE `talent_shows`
  ADD CONSTRAINT `talent_shows_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `unit_accounts`
--
ALTER TABLE `unit_accounts`
  ADD CONSTRAINT `unit_accounts_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
