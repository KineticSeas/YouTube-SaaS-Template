<?php
/**
 * Recent Tasks API Endpoint
 * Returns HTML for recent tasks list (for HTMX refresh)
 * REQ-DASH-101 to 103
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Unauthorized</div>';
    exit;
}

// Get current user ID
$userId = getCurrentUserId();

// Get recent tasks (last 10, ordered by updated_at)
$tasks = getTasksByUserId($userId, ['limit' => 10, 'order_by' => 'updated_at', 'order_dir' => 'DESC']);
$showEmpty = true;

// Include the task list component
include __DIR__ . '/../../components/task-list.php';
