<?php
/**
 * Create Task API Endpoint
 * Handles task creation with validation
 * REQ-TASK-001 through REQ-TASK-008
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

// Note: If task_id is sent to create endpoint, ignore it and create as new task
// The form should handle routing to the correct endpoint, but this provides fallback behavior
if (isset($_POST['task_id']) && !empty($_POST['task_id'])) {
    error_log("Warning: task_id sent to create endpoint (task_id={$_POST['task_id']}). Treating as new task creation.");
    // Continue with create operation - ignore the task_id
}

// Get user ID
$userId = getCurrentUserId();

// Get and sanitize input
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'pending';
$priority = $_POST['priority'] ?? 'medium';
$dueDate = $_POST['due_date'] ?? null;

// Validate title
if (empty($title)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Task title is required'
    ]);
    exit;
}

if (strlen($title) > 255) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Task title must be less than 255 characters'
    ]);
    exit;
}

// Validate description
if (strlen($description) > 5000) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Description must be less than 5000 characters'
    ]);
    exit;
}

// Validate status
$validStatuses = ['pending', 'in_progress', 'completed'];
if (!in_array($status, $validStatuses)) {
    $status = 'pending';
}

// Validate priority
$validPriorities = ['low', 'medium', 'high'];
if (!in_array($priority, $validPriorities)) {
    $priority = 'medium';
}

// Validate due date
if (!empty($dueDate)) {
    // Check format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid due date format. Use YYYY-MM-DD.'
        ]);
        exit;
    }

    // Check if date is valid
    $dateArray = explode('-', $dueDate);
    if (!checkdate((int)$dateArray[1], (int)$dateArray[2], (int)$dateArray[0])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid due date'
        ]);
        exit;
    }
} else {
    $dueDate = null;
}

// Create task
$result = createTask($userId, $title, $description, $status, $priority, $dueDate);

if ($result['success']) {
    $taskId = $result['task_id'];

    // Assign categories if any were selected
    if (isset($_POST['categories']) && is_array($_POST['categories'])) {
        foreach ($_POST['categories'] as $categoryId) {
            $categoryId = (int)$categoryId;
            if ($categoryId > 0) {
                // Verify category belongs to user before assigning
                $category = getCategoryById($categoryId, $userId);
                if ($category) {
                    assignTaskCategory($taskId, $categoryId);
                }
            }
        }
    }

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Task created successfully!',
        'task_id' => $taskId
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $result['error'] ?? 'Failed to create task'
    ]);
}
