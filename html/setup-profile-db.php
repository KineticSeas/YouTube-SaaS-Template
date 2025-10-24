<?php
/**
 * Profile Database Setup
 * Run this once to create profile-related tables
 * Access via browser: /setup-profile-db.php
 */

require_once 'config/database.php';

// Simple security - remove this file after running
$setupKey = 'TEMP_SETUP_KEY_12345';
if (!isset($_GET['key']) || $_GET['key'] !== $setupKey) {
    die('Access denied. Add ?key=' . $setupKey . ' to the URL.');
}

echo "<pre>";
echo "Setting up profile database tables...\n\n";

$db = getDatabase();
$conn = $db->getConnection();

if (!$conn) {
    die("Database connection failed\n");
}

$statements = [
    // Add profile columns to users table
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar_url VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS location VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS timezone VARCHAR(50) DEFAULT 'America/New_York'",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS account_status ENUM('active', 'pending_deletion', 'deleted') DEFAULT 'active'",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) DEFAULT NULL",

    // Create user_preferences table
    "CREATE TABLE IF NOT EXISTS user_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        pref_key VARCHAR(50) NOT NULL,
        pref_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_pref (user_id, pref_key),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Create user_activity_log table
    "CREATE TABLE IF NOT EXISTS user_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        activity_type ENUM('login', 'logout', 'profile_update', 'password_change',
                           'email_change', 'security_event', 'preference_change') NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        metadata JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_activity_type (activity_type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Create user_sessions table
    "CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_token VARCHAR(255) NOT NULL UNIQUE,
        ip_address VARCHAR(45),
        user_agent TEXT,
        device_info VARCHAR(255),
        location VARCHAR(100),
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_session_token (session_token),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

$success = true;
$executed = 0;

foreach ($statements as $index => $statement) {
    try {
        echo "[$index] Executing statement...\n";
        $conn->exec($statement);
        $executed++;
        echo "✓ Success\n\n";
    } catch (PDOException $e) {
        echo "✗ Error: " . $e->getMessage() . "\n\n";
        // Continue with other statements
    }
}

// Create uploads directory
$uploadsDir = __DIR__ . '/uploads/avatars';
if (!is_dir($uploadsDir)) {
    if (mkdir($uploadsDir, 0755, true)) {
        echo "✓ Created uploads/avatars directory\n";
        file_put_contents($uploadsDir . '/.htaccess', "<FilesMatch \"\\.php$\">\n    Deny from all\n</FilesMatch>\n");
        echo "✓ Created .htaccess for avatar security\n";
    } else {
        echo "✗ Failed to create uploads directory\n";
    }
} else {
    echo "✓ uploads/avatars directory already exists\n";
}

echo "\n✓ Setup completed! ($executed statements executed)\n";
echo "\n<strong>IMPORTANT: Delete this file (setup-profile-db.php) after setup is complete!</strong>\n";
echo "</pre>";
