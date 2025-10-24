<?php
/**
 * Web-based Database Setup - Run this in your browser
 * Access via: http://localhost:8889/setup-database.php
 */

// Disable timeout for this script
set_time_limit(0);

require_once 'config/database.php';

// Start output
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TodoTracker - Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; padding: 40px 0; }
        .setup-container { max-width: 800px; margin: 0 auto; }
        .status-box { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .step { margin-bottom: 20px; }
        .success { color: #198754; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        pre { background: #f1f3f5; padding: 10px; border-radius: 5px; }
        .table-list { margin-left: 20px; }
    </style>
</head>
<body>
<div class="container setup-container">
    <div class="status-box">
        <h1 class="mb-4"><i class="bi bi-database"></i> TodoTracker Database Setup</h1>

        <?php
        $success = true;
        $tables_created = false;

        // Create database instance
        $db = new Database();

        // Step 1: Test connection
        echo '<div class="step">';
        echo '<h3>Step 1: Testing Database Connection</h3>';
        echo '<ul>';
        echo '<li>Host: 127.0.0.1</li>';
        echo '<li>Port: 8889</li>';
        echo '<li>User: vibe_templates</li>';
        echo '<li>Database: todo_tracker</li>';
        echo '</ul>';

        if ($db->testConnection()) {
            echo '<p class="success"><i class="bi bi-check-circle"></i> Database connection successful!</p>';
        } else {
            echo '<p class="error"><i class="bi bi-x-circle"></i> Could not connect to database.</p>';
            echo '<p>Error: ' . htmlspecialchars($db->getError()) . '</p>';

            // Try to create database
            echo '<p>Attempting to create database...</p>';
            try {
                $pdo = new PDO(
                    "mysql:host=127.0.0.1;port=8889;charset=utf8mb4",
                    "vibe_templates",
                    "YouTubeDemo123!",
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                $pdo->exec("CREATE DATABASE IF NOT EXISTS todo_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo '<p class="success"><i class="bi bi-check-circle"></i> Database created successfully!</p>';

                // Reconnect
                $db = new Database();
                if (!$db->testConnection()) {
                    echo '<p class="error">Still cannot connect to database.</p>';
                    $success = false;
                }
            } catch (PDOException $e) {
                echo '<p class="error">Could not create database: ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<pre>CREATE DATABASE todo_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>';
                $success = false;
            }
        }
        echo '</div>';

        if ($success) {
            // Step 2: Check existing tables
            echo '<div class="step">';
            echo '<h3>Step 2: Checking Existing Tables</h3>';

            try {
                $conn = $db->getConnection();
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (count($tables) > 0) {
                    echo '<p class="warning"><i class="bi bi-exclamation-triangle"></i> Found ' . count($tables) . ' existing tables:</p>';
                    echo '<ul class="table-list">';
                    foreach ($tables as $table) {
                        echo '<li>' . htmlspecialchars($table) . '</li>';
                    }
                    echo '</ul>';

                    // Check if we should recreate
                    if (isset($_POST['recreate']) && $_POST['recreate'] === 'yes') {
                        echo '<p>Dropping and recreating tables...</p>';
                    } else {
                        echo '<form method="post">';
                        echo '<p class="warning">Tables already exist. Do you want to DROP and recreate them?</p>';
                        echo '<button type="submit" name="recreate" value="yes" class="btn btn-danger">Yes, Recreate Tables</button> ';
                        echo '<button type="submit" name="recreate" value="no" class="btn btn-secondary">No, Keep Existing</button>';
                        echo '</form>';
                        echo '</div></div></div></body></html>';
                        exit;
                    }
                } else {
                    echo '<p class="success"><i class="bi bi-check-circle"></i> Database is empty, ready for import.</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="error">Error checking tables: ' . htmlspecialchars($e->getMessage()) . '</p>';
                $success = false;
            }
            echo '</div>';
        }

        if ($success) {
            // Step 3: Import schema
            echo '<div class="step">';
            echo '<h3>Step 3: Importing Database Schema</h3>';

            $schemaPath = __DIR__ . '/schema.sql';
            if (!file_exists($schemaPath)) {
                echo '<p class="error">Schema file not found!</p>';
                $success = false;
            } else {
                if ($db->executeSqlFile($schemaPath)) {
                    echo '<p class="success"><i class="bi bi-check-circle"></i> Schema imported successfully!</p>';
                    $tables_created = true;
                } else {
                    echo '<p class="error">Failed to import schema: ' . htmlspecialchars($db->getError()) . '</p>';
                    $success = false;
                }
            }
            echo '</div>';
        }

        if ($tables_created) {
            // Step 4: Verify tables
            echo '<div class="step">';
            echo '<h3>Step 4: Verifying Tables</h3>';

            try {
                $conn = $db->getConnection();
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                echo '<p class="success">Created ' . count($tables) . ' tables:</p>';
                echo '<table class="table table-sm">';
                echo '<thead><tr><th>Table Name</th><th>Row Count</th><th>Status</th></tr></thead>';
                echo '<tbody>';

                foreach ($tables as $table) {
                    $countStmt = $conn->query("SELECT COUNT(*) FROM `{$table}`");
                    $count = $countStmt->fetchColumn();
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($table) . '</td>';
                    echo '<td>' . $count . '</td>';
                    echo '<td><span class="text-success"><i class="bi bi-check-circle"></i> OK</span></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';

                // Check demo user
                $stmt = $conn->query("SELECT email FROM users WHERE email = 'demo@todotracker.com'");
                if ($stmt->fetch()) {
                    echo '<div class="alert alert-info mt-3">';
                    echo '<h5>Demo Account Created</h5>';
                    echo '<p class="mb-0">Email: <strong>demo@todotracker.com</strong><br>';
                    echo 'Password: <strong>Demo123!</strong></p>';
                    echo '</div>';
                }

            } catch (PDOException $e) {
                echo '<p class="error">Error verifying tables: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            echo '</div>';
        }
        ?>

        <?php if ($success && $tables_created): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading"><i class="bi bi-check-circle-fill"></i> Setup Complete!</h4>
            <p>Your database has been set up successfully. You can now:</p>
            <hr>
            <div class="d-grid gap-2">
                <a href="/auth/register.php" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Register New Account
                </a>
                <a href="/auth/login.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Login with Demo Account
                </a>
            </div>
        </div>
        <?php elseif (!$success): ?>
        <div class="alert alert-danger">
            <h4 class="alert-heading"><i class="bi bi-x-circle-fill"></i> Setup Failed</h4>
            <p>Please check the error messages above and ensure:</p>
            <ul>
                <li>MySQL is running on port 8889</li>
                <li>Username and password are correct</li>
                <li>You have permission to create databases</li>
            </ul>
            <p>You can also import the schema manually:</p>
            <pre>mysql -u vibe_templates -p -h 127.0.0.1 -P 8889 todo_tracker < schema.sql</pre>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>