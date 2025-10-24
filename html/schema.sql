-- ============================================================================
-- TodoTracker SaaS Application - Database Schema
-- ============================================================================
-- Description: Complete database schema for multi-user todo list application
-- Database: MySQL 8.0+ or MariaDB 10.11+
-- Charset: utf8mb4 (supports emoji and international characters)
-- ============================================================================

-- Create database (if running manually)
-- CREATE DATABASE IF NOT EXISTS todo_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE todo_tracker;

-- ============================================================================
-- Table: users
-- Description: Stores user account information and authentication data
-- ============================================================================

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `verification_token` VARCHAR(64) NULL,
    `profile_picture` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME NULL,
    `failed_login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `account_locked_until` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_email` (`email`),
    KEY `idx_email_verified` (`email_verified`),
    KEY `idx_verification_token` (`verification_token`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: tasks
-- Description: Stores all user tasks with status, priority, and dates
-- ============================================================================

DROP TABLE IF EXISTS `tasks`;

CREATE TABLE `tasks` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    `due_date` DATE NULL,
    `completed_at` DATETIME NULL,
    `is_archived` TINYINT(1) NOT NULL DEFAULT 0,
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `deleted_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_is_archived` (`is_archived`),
    KEY `idx_is_deleted` (`is_deleted`),
    KEY `idx_user_status` (`user_id`, `status`),
    KEY `idx_user_deleted` (`user_id`, `is_deleted`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_tasks_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: categories
-- Description: User-defined categories/tags for organizing tasks
-- ============================================================================

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#6c757d',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    UNIQUE KEY `idx_user_category` (`user_id`, `name`),
    CONSTRAINT `fk_categories_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: task_categories
-- Description: Many-to-many relationship between tasks and categories
-- ============================================================================

DROP TABLE IF EXISTS `task_categories`;

CREATE TABLE `task_categories` (
    `task_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`task_id`, `category_id`),
    KEY `idx_category_id` (`category_id`),
    CONSTRAINT `fk_task_categories_task` FOREIGN KEY (`task_id`)
        REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_task_categories_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: sessions
-- Description: Secure session management for logged-in users
-- ============================================================================

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `session_token` VARCHAR(64) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NULL,
    `remember_me` TINYINT(1) NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_session_token` (`session_token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_expires_at` (`expires_at`),
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: password_resets
-- Description: Password reset tokens for forgot password functionality
-- ============================================================================

DROP TABLE IF EXISTS `password_resets`;

CREATE TABLE `password_resets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `reset_token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `used_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_reset_token` (`reset_token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_expires_at` (`expires_at`),
    CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: login_attempts
-- Description: Track login attempts for security monitoring
-- ============================================================================

DROP TABLE IF EXISTS `login_attempts`;

CREATE TABLE `login_attempts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Insert Sample Data (Optional - for development/testing)
-- ============================================================================

-- Sample user (password: Demo123!)
-- Password hash generated with: password_hash('Demo123!', PASSWORD_DEFAULT)
INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `email_verified`) VALUES
('demo@todotracker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo', 'User', 1);

-- Sample categories for demo user
INSERT INTO `categories` (`user_id`, `name`, `color`) VALUES
(1, 'Work', '#0d6efd'),
(1, 'Personal', '#198754'),
(1, 'Shopping', '#ffc107'),
(1, 'Health', '#dc3545'),
(1, 'Learning', '#6f42c1');

-- Sample tasks for demo user
INSERT INTO `tasks` (`user_id`, `title`, `description`, `status`, `priority`, `due_date`) VALUES
(1, 'Complete project proposal', 'Finish the Q4 project proposal and submit to management', 'in_progress', 'high', DATE_ADD(CURDATE(), INTERVAL 2 DAY)),
(1, 'Review team performance', 'Conduct quarterly performance reviews for team members', 'pending', 'medium', DATE_ADD(CURDATE(), INTERVAL 7 DAY)),
(1, 'Update documentation', 'Update API documentation with latest changes', 'pending', 'low', DATE_ADD(CURDATE(), INTERVAL 14 DAY)),
(1, 'Grocery shopping', 'Buy groceries for the week', 'pending', 'medium', CURDATE()),
(1, 'Schedule dentist appointment', 'Book appointment for regular checkup', 'pending', 'low', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(1, 'Learn Alpine.js', 'Complete Alpine.js tutorial series', 'in_progress', 'medium', DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
(1, 'Fix homepage bug', 'Resolve the layout issue on mobile devices', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(1, 'Prepare presentation', 'Create slides for Monday meeting', 'pending', 'high', DATE_ADD(CURDATE(), INTERVAL 1 DAY));

-- Link tasks to categories
INSERT INTO `task_categories` (`task_id`, `category_id`) VALUES
(1, 1), -- Complete project proposal -> Work
(2, 1), -- Review team performance -> Work
(3, 1), -- Update documentation -> Work
(4, 3), -- Grocery shopping -> Shopping
(5, 4), -- Schedule dentist -> Health
(6, 5), -- Learn Alpine.js -> Learning
(7, 1), -- Fix homepage bug -> Work
(8, 1); -- Prepare presentation -> Work

-- ============================================================================
-- Indexes Summary
-- ============================================================================
-- users: email (unique), email_verified, verification_token, created_at
-- tasks: user_id, status, priority, due_date, is_archived, is_deleted,
--        composite (user_id, status), composite (user_id, is_deleted), created_at
-- categories: user_id, composite unique (user_id, name)
-- task_categories: composite primary (task_id, category_id), category_id
-- sessions: session_token (unique), user_id, expires_at
-- password_resets: reset_token (unique), user_id, expires_at
-- login_attempts: email, ip_address, attempted_at
-- ============================================================================

-- Schema creation completed successfully
