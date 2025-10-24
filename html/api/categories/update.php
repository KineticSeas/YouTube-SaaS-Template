<?php
/**
 * Update Category API Endpoint
 * REQ-CAT-005
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

// Set HTML response header for HTMX
header('Content-Type: text/html; charset=utf-8');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo renderError('Unauthorized. Please log in.');
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo renderError('Method not allowed');
    exit;
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo renderError('Invalid CSRF token');
    exit;
}

// Get parameters
$userId = getCurrentUserId();
$categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$name = trim($_POST['name'] ?? '');
$color = $_POST['color'] ?? '#6c757d';

// Log the received data for debugging
error_log("Update category request - ID: {$categoryId}, Name: {$name}, Color: {$color}");

// Validate inputs
if (!isset($_POST['category_id']) || $_POST['category_id'] === '') {
    http_response_code(400);
    echo renderError('Category ID is missing. This is an edit operation.');
    exit;
}

if ($categoryId <= 0) {
    http_response_code(400);
    echo renderError('Invalid category ID');
    exit;
}

if (empty($name)) {
    http_response_code(400);
    echo renderError('Category name is required');
    exit;
}

// Update category
$result = updateCategory($categoryId, $userId, $name, $color);

if ($result['success']) {
    http_response_code(200);
    echo renderSuccess('Category updated successfully!');
} else {
    http_response_code(400);
    echo renderError($result['error'] ?? 'Failed to update category');
}

/**
 * Render success alert HTML
 */
function renderSuccess($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Success!</strong> ' . htmlspecialchars($message) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

/**
 * Render error alert HTML
 */
function renderError($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Error!</strong> ' . htmlspecialchars($message) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}
