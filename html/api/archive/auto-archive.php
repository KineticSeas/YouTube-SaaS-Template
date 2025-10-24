<?php
/**
 * Auto-Archive Completed Tasks API Endpoint
 * REQ-ARCH-004
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
$daysOld = isset($_POST['days_old']) ? (int)$_POST['days_old'] : 30;

// Validate days (must be between 1 and 365)
if ($daysOld < 1 || $daysOld > 365) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Days must be between 1 and 365']);
    exit;
}

$result = autoArchiveCompletedTasks($userId, $daysOld);

if ($result['success']) {
    http_response_code(200);
    $message = $result['count'] > 0
        ? "Archived {$result['count']} completed task(s)"
        : "No tasks to archive";
    echo json_encode([
        'success' => true,
        'message' => $message,
        'count' => $result['count']
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $result['error'] ?? 'Failed to auto-archive tasks',
        'count' => 0
    ]);
}
