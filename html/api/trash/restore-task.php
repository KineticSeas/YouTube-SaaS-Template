<?php
/**
 * Restore Task from Trash API Endpoint
 * REQ-ARCH-102
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$userId = getCurrentUserId();
$taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;

if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit;
}

$result = restoreFromTrash($taskId, $userId);

if ($result['success']) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Task restored from trash successfully!']);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Failed to restore task']);
}
