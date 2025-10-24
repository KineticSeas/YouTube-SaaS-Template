<?php
/**
 * Database Check and Schema Import Script
 * This script will check database connection and create tables if needed
 */

require_once 'config/database.php';

echo "=====================================\n";
echo "TodoTracker Database Setup Script\n";
echo "=====================================\n\n";

// Create database instance
$db = new Database();

// Test 1: Basic connection test
echo "Step 1: Testing database connection...\n";
echo "Host: 127.0.0.1\n";
echo "Port: 8889\n";
echo "User: vibe_templates\n";
echo "Database: todo_tracker\n\n";

if ($db->testConnection()) {
    echo "✓ SUCCESS: Database connection established!\n\n";
} else {
    echo "✗ FAILED: Could not connect to database.\n";
    echo "Error: " . $db->getError() . "\n\n";

    // Try to create the database
    echo "Attempting to create database...\n";
    try {
        $pdo = new PDO(
            "mysql:host=127.0.0.1;port=8889;charset=utf8mb4",
            "vibe_templates",
            "YouTubeDemo123!",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $pdo->exec("CREATE DATABASE IF NOT EXISTS todo_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✓ Database 'todo_tracker' created successfully!\n\n";

        // Reconnect to the new database
        $db = new Database();
        if (!$db->testConnection()) {
            echo "✗ Still cannot connect to database.\n";
            exit(1);
        }
    } catch (PDOException $e) {
        echo "✗ Could not create database: " . $e->getMessage() . "\n";
        echo "\nPlease create the database manually:\n";
        echo "CREATE DATABASE todo_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
        exit(1);
    }
}

// Test 2: Check existing tables
echo "Step 2: Checking existing tables...\n";
try {
    $conn = $db->getConnection();
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "Found " . count($tables) . " existing tables:\n";
        foreach ($tables as $table) {
            echo "  - {$table}\n";
        }
        echo "\n";

        echo "WARNING: Tables already exist. Do you want to DROP and recreate them? (yes/no): ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));

        if (strtolower($line) !== 'yes' && strtolower($line) !== 'y') {
            echo "\nSchema import cancelled. Existing tables preserved.\n";
            exit(0);
        }
        echo "\n";
    } else {
        echo "No tables found. Database is empty.\n\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking tables: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 3: Import schema
echo "Step 3: Importing database schema...\n";
$schemaPath = __DIR__ . '/schema.sql';

if (!file_exists($schemaPath)) {
    echo "✗ Schema file not found at: {$schemaPath}\n";
    exit(1);
}

echo "Using schema file: {$schemaPath}\n";

if ($db->executeSqlFile($schemaPath)) {
    echo "✓ SUCCESS: Schema imported successfully!\n\n";

    // Verify tables were created
    echo "Step 4: Verifying tables...\n";
    try {
        $conn = $db->getConnection();
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo "Created " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            // Get row count for each table
            $countStmt = $conn->query("SELECT COUNT(*) FROM `{$table}`");
            $count = $countStmt->fetchColumn();
            echo "  ✓ {$table} ({$count} rows)\n";
        }
        echo "\n";

        // Check for demo user
        $stmt = $conn->query("SELECT email FROM users WHERE email = 'demo@todotracker.com'");
        if ($stmt->fetch()) {
            echo "✓ Demo user created successfully!\n";
            echo "  Email: demo@todotracker.com\n";
            echo "  Password: Demo123!\n\n";
        }

    } catch (PDOException $e) {
        echo "✗ Error verifying tables: " . $e->getMessage() . "\n";
    }

    echo "=====================================\n";
    echo "Database setup completed successfully!\n";
    echo "=====================================\n\n";

    echo "You can now:\n";
    echo "1. Register a new account at: /auth/register.php\n";
    echo "2. Login with demo account at: /auth/login.php\n";
    echo "   - Email: demo@todotracker.com\n";
    echo "   - Password: Demo123!\n\n";

} else {
    echo "✗ FAILED: Could not import schema.\n";
    echo "Error: " . $db->getError() . "\n\n";

    echo "You can try importing manually:\n";
    echo "mysql -u vibe_templates -p -h 127.0.0.1 -P 8889 todo_tracker < schema.sql\n\n";
    exit(1);
}

echo "Done!\n";