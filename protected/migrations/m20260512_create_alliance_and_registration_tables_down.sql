-- Rollback Migration: Drop alliance and registration detail tables
-- Date: 2026-05-12

-- Drop tables in reverse order (respect foreign keys)
DROP TABLE IF EXISTS `registration_detail_attendees`;
DROP TABLE IF EXISTS `alliances`;
DROP TABLE IF EXISTS `alliance_requests`;

-- Remove added column from registration_details
ALTER TABLE `registration_details` DROP COLUMN IF EXISTS `registration_type`;
