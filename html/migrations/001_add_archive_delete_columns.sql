-- ============================================================================
-- Migration: Add archive and delete tracking columns
-- Description: Adds archived_at timestamp and additional indexes for
--              archive and trash management system
-- Date: 2025-10-20
-- ============================================================================

USE todo_tracker;

-- Add archived_at column if it doesn't exist
ALTER TABLE `tasks`
ADD COLUMN IF NOT EXISTS `archived_at` DATETIME NULL AFTER `completed_at`;

-- Add indexes for performance on archive/delete queries
ALTER TABLE `tasks`
ADD INDEX IF NOT EXISTS `idx_archived_at` (`archived_at`),
ADD INDEX IF NOT EXISTS `idx_deleted_at` (`deleted_at`),
ADD INDEX IF NOT EXISTS `idx_user_archived` (`user_id`, `is_archived`);

-- ============================================================================
-- Migration complete
-- ============================================================================
