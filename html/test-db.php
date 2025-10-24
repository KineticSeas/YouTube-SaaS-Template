<?php
require_once 'config/database.php';

echo "Testing connection to vibe_templates database...\n";
echo "Host: 127.0.0.1\n";
echo "Port: 8889\n";
echo "Database: vibe_templates\n\n";

$db = new Database();

if ($db->testConnection()) {
    echo "✓ Connected successfully!\n\n";

    $conn = $db->getConnection();

    // Check existing tables
    echo "Checking existing tables:\n";
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            $countStmt = $conn->query("SELECT COUNT(*) FROM `{$table}`");
            $count = $countStmt->fetchColumn();
            echo "  - {$table} ({$count} rows)\n";
        }
    } else {
        echo "No tables found. Database is empty.\n";
    }

    echo "\nDatabase connection successful!\n";
} else {
    echo "✗ Connection failed: " . $db->getError() . "\n";
}