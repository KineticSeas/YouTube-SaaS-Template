<?php
/**
 * Bulk Task Update API Endpoint
 * Perform bulk operations on multiple tasks
 * Supports: complete, delete, change_priority, change_status
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in.',
        'updated' => 0,
        'failed' => 0
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'updated' => 0,
        'failed' => 0
    ]);
    exit;
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token',
        'updated' => 0,
        'failed' => 0
    ]);
    exit;
}

// Get user ID
$userId = getCurrentUserId();

// Validate required parameters
if (!isset($_POST['task_ids']) || !is_array($_POST['task_ids']) || empty($_POST['task_ids'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No tasks selected',
        'updated' => 0,
        'failed' => 0
    ]);
    exit;
}

if (!isset($_POST['action']) || empty($_POST['action'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No action specified',
        'updated' => 0,
        'failed' => 0
    ]);
    exit;
}

$taskIds = array_map('intval', $_POST['task_ids']);
$action = $_POST['action'];

// Validate action
$validActions = ['complete', 'delete', 'change_priority', 'change_status'];
if (!in_array($action, $validActions)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action',
        'updated' => 0,
        'failed' => 0
    ]);
    exit;
}

// Additional parameters based on action
$priority = null;
$status = null;

if ($action === 'change_priority') {
    $priority = $_POST['priority'] ?? null;
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid priority value',
            'updated' => 0,
            'failed' => 0
        ]);
        exit;
    }
}

if ($action === 'change_status') {
    $status = $_POST['status'] ?? null;
    if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status value',
            'updated' => 0,
            'failed' => 0
        ]);
        exit;
    }
}

// Process bulk operation
$successCount = 0;
$failCount = 0;
$errors = [];

foreach ($taskIds as $taskId) {
    try {
        switch ($action) {
            case 'complete':
                $result = updateTaskStatus($taskId, $userId, 'completed');
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "Task #{$taskId}: " . $result['error'];
                }
                break;

            case 'delete':
                $result = deleteTask($taskId, $userId);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "Task #{$taskId}: " . $result['error'];
                }
                break;

            case 'change_priority':
                $result = updateTask($taskId, $userId, ['priority' => $priority]);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "Task #{$taskId}: " . $result['error'];
                }
                break;

            case 'change_status':
                $result = updateTaskStatus($taskId, $userId, $status);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "Task #{$taskId}: " . $result['error'];
                }
                break;
        }
    } catch (Exception $e) {
        $failCount++;
        $errors[] = "Task #{$taskId}: " . $e->getMessage();
        error_log("Bulk update error for task {$taskId}: " . $e->getMessage());
    }
}

// Prepare response
$response = [
    'success' => $successCount > 0,
    'message' => "{$successCount} task(s) updated successfully" . ($failCount > 0 ? ", {$failCount} failed" : ""),
    'updated' => $successCount,
    'failed' => $failCount,
    'total' => count($taskIds)
];

if ($failCount > 0 && !empty($errors)) {
    $response['errors'] = $errors;
}

// Set appropriate HTTP status code
if ($successCount > 0) {
    http_response_code(200);
} else {
    http_response_code(400);
}

header('Content-Type: application/json');
echo json_encode($response);
