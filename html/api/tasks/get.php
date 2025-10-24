<?php
/**
 * Get Single Task API Endpoint
 * Fetches task data for editing
 * REQ-TASK-201
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in.'
    ]);
    exit;
}

// Get task ID
$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid task ID'
    ]);
    exit;
}

// Get user ID
$userId = getCurrentUserId();

// Fetch task
$task = getTaskById($taskId, $userId);

if (!$task) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Task not found or access denied'
    ]);
    exit;
}

// Get task categories
$categories = getTaskCategories($taskId);
$task['categories'] = $categories;

// Return task data
echo json_encode([
    'success' => true,
    'task' => $task
]);
