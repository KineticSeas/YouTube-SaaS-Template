<?php
/**
 * Empty Trash API Endpoint (Delete all trashed tasks permanently)
 * REQ-ARCH-106
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

$result = emptyTrash($userId);

if ($result['success']) {
    http_response_code(200);
    $message = $result['count'] > 0
        ? "Trash emptied. {$result['count']} task(s) permanently deleted."
        : "Trash is already empty";
    echo json_encode([
        'success' => true,
        'message' => $message,
        'count' => $result['count']
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $result['error'] ?? 'Failed to empty trash',
        'count' => 0
    ]);
}
