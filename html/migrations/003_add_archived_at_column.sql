-- ============================================================================
-- Migration: Add archived_at column to tasks table
-- Description: Adds archived_at timestamp for archive management system
-- Date: 2025-10-23
-- ============================================================================

-- Add archived_at column if it doesn't exist
ALTER TABLE `tasks`
ADD COLUMN IF NOT EXISTS `archived_at` DATETIME NULL AFTER `completed_at`;

-- Add indexes for performance on archive queries
ALTER TABLE `tasks`
ADD INDEX IF NOT EXISTS `idx_archived_at` (`archived_at`),
ADD INDEX IF NOT EXISTS `idx_user_archived` (`user_id`, `archived_at`);

-- ============================================================================
-- Migration complete
-- ============================================================================