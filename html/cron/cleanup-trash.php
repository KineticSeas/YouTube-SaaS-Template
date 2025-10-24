<?php
/**
 * Cron Job: Clean up old trashed tasks
 * Automatically deletes tasks that have been in trash for more than 30 days
 *
 * Usage: Run daily via crontab
 * 0 0 * * * /Applications/MAMP/bin/php/php8.3.14/bin/php /path/to/html/cron/cleanup-trash.php >> /path/to/logs/trash-cleanup.log 2>&1
 */

// Prevent direct web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/task-functions.php';

// Log start time
$startTime = microtime(true);
echo "\n" . str_repeat('=', 70) . "\n";
echo "Trash Cleanup Cron Job\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 70) . "\n\n";

try {
    // Purge tasks older than 30 days
    $daysOld = 30;
    echo "Purging tasks deleted more than {$daysOld} days ago...\n";

    $result = purgeOldTrashedTasks($daysOld);

    if ($result['success']) {
        echo "✓ Success!\n";
        echo "  Tasks permanently deleted: {$result['count']}\n";

        if ($result['count'] > 0) {
            echo "\n  Details:\n";
            echo "  - Deletion criteria: Tasks in trash for ≥ {$daysOld} days\n";
            echo "  - These tasks have been permanently removed from the database\n";
            echo "  - Related records (categories, history) also deleted\n";
        } else {
            echo "  No tasks met the deletion criteria.\n";
        }
    } else {
        echo "✗ Failed!\n";
        echo "  Error: " . ($result['error'] ?? 'Unknown error') . "\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "✗ Fatal Error!\n";
    echo "  Exception: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    exit(1);
}

// Log completion
$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

echo "\n" . str_repeat('=', 70) . "\n";
echo "Completed at: " . date('Y-m-d H:i:s') . "\n";
echo "Duration: {$duration} seconds\n";
echo str_repeat('=', 70) . "\n\n";

exit(0);
