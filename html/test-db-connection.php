<?php
/**
 * Database Connection Test Script
 * Tests connection to MySQL and optionally creates schema
 */

require_once 'config/database.php';

echo "====================================\n";
echo "TodoTracker Database Connection Test\n";
echo "====================================\n\n";

// Create database instance
$db = new Database();

// Test 1: Basic connection test
echo "Test 1: Testing database connection...\n";
if ($db->testConnection()) {
    echo "✓ SUCCESS: Database connection established!\n\n";
} else {
    echo "✗ FAILED: Could not connect to database.\n";
    echo "Error: " . $db->getError() . "\n\n";
    exit(1);
}

// Test 2: Check if database exists
echo "Test 2: Checking if todo_tracker database exists...\n";
try {
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'todo_tracker'");
    $result = $stmt->fetch();

    if ($result) {
        echo "✓ Database 'todo_tracker' exists.\n\n";
    } else {
        echo "✗ Database 'todo_tracker' does NOT exist.\n";
        echo "  Please create it manually:\n";
        echo "  CREATE DATABASE todo_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking database: " . $e->getMessage() . "\n\n";
}

// Test 3: Check if tables exist
echo "Test 3: Checking if tables exist...\n";
try {
    $conn = $db->getConnection();
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "✓ Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  - {$table}\n";
        }
        echo "\n";
    } else {
        echo "⚠ No tables found. You may need to run schema.sql\n\n";
    }
} catch (PDOException $e) {
    echo "⚠ Could not check tables: " . $e->getMessage() . "\n\n";
}

// Test 4: Offer to run schema.sql
echo "====================================\n";
echo "Do you want to run schema.sql to create/reset the database? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) === 'yes' || strtolower($line) === 'y') {
    echo "\nRunning schema.sql...\n";

    $schemaPath = __DIR__ . '/schema.sql';
    if ($db->executeSqlFile($schemaPath)) {
        echo "✓ SUCCESS: Schema executed successfully!\n";
        echo "  - All tables created\n";
        echo "  - Sample data inserted\n";
        echo "  - Demo user created: demo@todotracker.com (password: Demo123!)\n\n";
    } else {
        echo "✗ FAILED: Could not execute schema.\n";
        echo "Error: " . $db->getError() . "\n\n";
        exit(1);
    }
} else {
    echo "\nSchema execution skipped.\n\n";
}

echo "====================================\n";
echo "Connection test completed!\n";
echo "====================================\n";
