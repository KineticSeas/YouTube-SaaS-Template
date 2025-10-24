<?php
/**
 * Delete Category API Endpoint
 * REQ-CAT-006, REQ-CAT-007
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
$categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

if ($categoryId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category ID'
    ]);
    exit;
}

// Delete category
$result = deleteCategory($categoryId, $userId);

if ($result['success']) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Category deleted successfully!'
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $result['error'] ?? 'Failed to delete category'
    ]);
}
