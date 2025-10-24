<?php
/**
 * Database Migration Runner
 * Run from command line: php run-migration.php migrations/add_profile_tables.sql
 */

require_once 'config/database.php';

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

if ($argc < 2) {
    die("Usage: php run-migration.php <migration-file>\n");
}

$migrationFile = $argv[1];

if (!file_exists($migrationFile)) {
    die("Migration file not found: $migrationFile\n");
}

echo "Running migration: $migrationFile\n";

$db = getDatabase();
$conn = $db->getConnection();

if (!$conn) {
    die("Database connection failed\n");
}

// Read SQL file
$sql = file_get_contents($migrationFile);

// Split into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt);
    }
);

$success = true;
$executed = 0;

foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;

    try {
        echo "Executing statement...\n";
        $conn->exec($statement);
        $executed++;
        echo "✓ Success\n";
    } catch (PDOException $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        $success = false;
    }
}

if ($success) {
    echo "\n✓ Migration completed successfully! ($executed statements executed)\n";

    // Create uploads directory if it doesn't exist
    $uploadsDir = __DIR__ . '/uploads/avatars';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
        file_put_contents($uploadsDir . '/.htaccess', "<FilesMatch \"\\.php$\">\n    Deny from all\n</FilesMatch>\n");
        echo "✓ Created uploads/avatars directory\n";
    }
} else {
    echo "\n✗ Migration failed\n";
    exit(1);
}
