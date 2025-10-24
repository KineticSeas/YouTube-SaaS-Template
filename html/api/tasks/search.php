<?php
/**
 * Task Search API Endpoint
 * Returns HTML for search results in current view mode
 * REQ-LIST-301 through REQ-LIST-305
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Unauthorized. Please log in.</div>';
    exit;
}

// Get user ID
$userId = getCurrentUserId();

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get view mode (list or grid)
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'list';
if (!in_array($viewMode, ['list', 'grid'])) {
    $viewMode = 'list';
}

// Get sorting parameters
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$sortDir = isset($_GET['sort_dir']) ? $_GET['sort_dir'] : 'DESC';

// Get page size
$pageSize = isset($_GET['page_size']) ? intval($_GET['page_size']) : 20;
if (!in_array($pageSize, [10, 20, 50, 100])) {
    $pageSize = 20;
}

// Build filters array
$filters = [
    'order_by' => $sortBy,
    'order_dir' => $sortDir,
    'limit' => $pageSize,
    'offset' => 0 // Always reset to first page for search
];

// Add search filter if provided
if (!empty($search)) {
    $filters['search'] = $search;
}

// Get tasks
$tasks = getTasksByUserId($userId, $filters);

// Return appropriate view
if ($viewMode === 'grid') {
    include __DIR__ . '/../../components/task-cards.php';
} else {
    include __DIR__ . '/../../components/task-table.php';
}
