<?php
/**
 * List Tasks API Endpoint
 * Returns tasks for the current user with optional filters
 * REQ-TASK-101 through REQ-TASK-107
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in.',
        'tasks' => []
    ]);
    exit;
}

// Get user ID
$userId = getCurrentUserId();

// Get filters from query parameters
$filters = [];

if (isset($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}

if (isset($_GET['priority'])) {
    $filters['priority'] = $_GET['priority'];
}

if (isset($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

if (isset($_GET['archived'])) {
    $filters['archived'] = (bool)$_GET['archived'];
}

if (isset($_GET['limit'])) {
    $filters['limit'] = (int)$_GET['limit'];
}

if (isset($_GET['offset'])) {
    $filters['offset'] = (int)$_GET['offset'];
}

if (isset($_GET['order_by'])) {
    $filters['order_by'] = $_GET['order_by'];
}

if (isset($_GET['order_dir'])) {
    $filters['order_dir'] = $_GET['order_dir'];
}

// Fetch tasks
$tasks = getTasksByUserId($userId, $filters);

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'tasks' => $tasks,
    'count' => count($tasks)
]);
