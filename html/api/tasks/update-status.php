<?php
/**
 * Update Task Status API Endpoint
 * Quick status update for buttons
 * REQ-TASK-203, REQ-TASK-204
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

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token'
    ]);
    exit;
}

// Get parameters
$userId = getCurrentUserId();
$taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$newStatus = $_POST['status'] ?? '';

if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid task ID'
    ]);
    exit;
}

if (empty($newStatus)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Status is required'
    ]);
    exit;
}

// Update status
$result = updateTaskStatus($taskId, $userId, $newStatus);

if ($result['success']) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully!',
        'task' => $result['task']
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $result['error'] ?? 'Failed to update status'
    ]);
}
