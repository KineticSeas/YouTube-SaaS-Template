<?php
/**
 * Move Task API Endpoint
 * Update task status via drag-and-drop
 * REQ-KANBAN-203, REQ-KANBAN-204, REQ-KANBAN-205
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
$taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$newStatus = $_POST['new_status'] ?? '';

// Validate task ID
if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid task ID'
    ]);
    exit;
}

// Validate new status
$validStatuses = ['pending', 'in_progress', 'completed'];
if (!in_array($newStatus, $validStatuses)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status. Must be: pending, in_progress, or completed'
    ]);
    exit;
}

try {
    $db = getDatabase();
    $conn = $db->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Verify task belongs to current user
    $stmt = $conn->prepare("SELECT id, status FROM tasks WHERE id = ? AND user_id = ? AND is_deleted = 0");
    $stmt->execute([$taskId, $userId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Task not found or access denied'
        ]);
        exit;
    }

    $oldStatus = $task['status'];

    // Don't update if status is the same
    if ($oldStatus === $newStatus) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Task is already in this status',
            'task' => ['id' => $taskId, 'status' => $newStatus]
        ]);
        exit;
    }

    // Update task status
    if ($newStatus === 'completed') {
        // Moving to completed: set completed_at timestamp
        $stmt = $conn->prepare("
            UPDATE tasks
            SET status = ?,
                completed_at = NOW(),
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$newStatus, $taskId, $userId]);
    } else {
        // Moving away from completed or between pending/in_progress: clear completed_at
        $stmt = $conn->prepare("
            UPDATE tasks
            SET status = ?,
                completed_at = NULL,
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$newStatus, $taskId, $userId]);
    }

    // Log to task history
    addTaskHistory($taskId, $userId, 'status_changed', 'status', $oldStatus, $newStatus);

    // Success response
    $statusLabel = str_replace('_', ' ', $newStatus);
    $statusLabel = ucwords($statusLabel);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => "Task moved to {$statusLabel}",
        'task' => [
            'id' => $taskId,
            'status' => $newStatus,
            'old_status' => $oldStatus
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error moving task: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error moving task: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while moving the task'
    ]);
}
