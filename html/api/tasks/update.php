<?php
/**
 * Update Task API Endpoint
 * Handles task updates with validation
 * REQ-TASK-201 through REQ-TASK-206
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

// Get user ID and task ID
$userId = getCurrentUserId();

// Validate that task_id field exists and is not empty
if (!isset($_POST['task_id']) || $_POST['task_id'] === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Task ID is missing. This is an edit operation and requires a task ID.'
    ]);
    exit;
}

$taskId = (int)$_POST['task_id'];

if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid task ID'
    ]);
    exit;
}

// Build update data array
$data = [];

if (isset($_POST['title'])) {
    $data['title'] = trim($_POST['title']);
}

if (isset($_POST['description'])) {
    $data['description'] = trim($_POST['description']);
}

if (isset($_POST['status'])) {
    $data['status'] = $_POST['status'];
}

if (isset($_POST['priority'])) {
    $data['priority'] = $_POST['priority'];
}

if (isset($_POST['due_date'])) {
    $data['due_date'] = $_POST['due_date'];
}

// Validate at least one field is provided
if (empty($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No data provided for update'
    ]);
    exit;
}

// Update task
$result = updateTask($taskId, $userId, $data);

if ($result['success']) {
    // Handle category updates if provided
    if (isset($_POST['categories'])) {
        // Get current categories
        $currentCategories = getTaskCategories($taskId);
        $currentCategoryIds = array_column($currentCategories, 'id');

        // Get new categories from form
        $newCategoryIds = is_array($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];

        // Remove categories that are no longer selected
        foreach ($currentCategoryIds as $catId) {
            if (!in_array($catId, $newCategoryIds)) {
                removeTaskCategory($taskId, $catId);
            }
        }

        // Add new categories
        foreach ($newCategoryIds as $catId) {
            if ($catId > 0 && !in_array($catId, $currentCategoryIds)) {
                // Verify category belongs to user
                $category = getCategoryById($catId, $userId);
                if ($category) {
                    assignTaskCategory($taskId, $catId);
                }
            }
        }
    }

    // Fetch updated task
    $updatedTask = getTaskById($taskId, $userId);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Task updated successfully!',
        'task' => $updatedTask
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $result['error'] ?? 'Failed to update task'
    ]);
}
