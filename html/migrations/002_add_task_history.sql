-- Migration: Add task_history table for audit logging
-- REQ-TASK-202: Track task modification history
-- Created: 2025-10-20

CREATE TABLE IF NOT EXISTS task_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL, -- created, updated, deleted, archived, status_changed, etc.
    field_name VARCHAR(100), -- which field was changed (null for create/delete)
    old_value TEXT, -- previous value (null for create)
    new_value TEXT, -- new value (null for delete)
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_task_id (task_id),
    INDEX idx_user_id (user_id),
    INDEX idx_changed_at (changed_at),

    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to table
ALTER TABLE task_history COMMENT = 'Audit log for task modifications - REQ-TASK-202';
