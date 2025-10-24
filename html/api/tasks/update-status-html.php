<?php
/**
 * Update Task Status and Return HTML Row
 * Used for HTMX requests that need HTML response
 * REQ-TASK-203, REQ-TASK-204
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Unauthorized. Please log in.</div>';
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '<div class="alert alert-danger">Method not allowed</div>';
    exit;
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Invalid CSRF token</div>';
    exit;
}

// Get parameters
$userId = getCurrentUserId();
$taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$newStatus = $_POST['status'] ?? '';

if ($taskId <= 0) {
    http_response_code(400);
    echo '<div class="alert alert-danger">Invalid task ID</div>';
    exit;
}

if (empty($newStatus)) {
    http_response_code(400);
    echo '<div class="alert alert-danger">Status is required</div>';
    exit;
}

// Update status
$result = updateTaskStatus($taskId, $userId, $newStatus);

if ($result['success']) {
    // Get the updated task
    $task = $result['task'];
    $taskCategories = getTaskCategories($task['id']);
    $isOverdue = !empty($task['due_date']) && strtotime($task['due_date']) < strtotime('today') && $task['status'] !== 'completed';

    // Helper functions for display
    function getPriorityBadgeClass($priority) {
        switch ($priority) {
            case 'high': return 'bg-danger';
            case 'medium': return 'bg-warning text-dark';
            case 'low': return 'bg-secondary';
            default: return 'bg-secondary';
        }
    }

    function getStatusBadgeClass($status) {
        switch ($status) {
            case 'completed': return 'bg-success';
            case 'in_progress': return 'bg-info';
            case 'pending': return 'bg-warning text-dark';
            default: return 'bg-secondary';
        }
    }

    function getStatusDisplayName($status) {
        switch ($status) {
            case 'in_progress': return 'In Progress';
            default: return ucfirst($status);
        }
    }

    function formatDueDateShort($dueDate) {
        if (empty($dueDate)) {
            return '<span class="text-muted">No due date</span>';
        }

        $date = new DateTime($dueDate);
        $today = new DateTime('today');
        $tomorrow = new DateTime('tomorrow');

        $isOverdue = $date < $today;
        $isToday = $date->format('Y-m-d') === $today->format('Y-m-d');
        $isTomorrow = $date->format('Y-m-d') === $tomorrow->format('Y-m-d');

        if ($isOverdue) {
            return '<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>' . $date->format('M j, Y') . '</span>';
        } elseif ($isToday) {
            return '<span class="text-warning fw-bold"><i class="bi bi-clock-fill me-1"></i>Today</span>';
        } elseif ($isTomorrow) {
            return '<span class="text-primary">Tomorrow</span>';
        } else {
            return $date->format('M j, Y');
        }
    }

    function truncateText($text, $length = 50) {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    // Output the updated task row
    ?>
<tr class="task-row <?php echo $isOverdue ? 'table-danger' : ''; ?>"
    data-task-id="<?php echo $task['id']; ?>"
    id="task-row-<?php echo $task['id']; ?>">
    <td>
        <input type="checkbox"
               class="form-check-input task-checkbox"
               value="<?php echo $task['id']; ?>"
               onclick="updateBulkActions()">
    </td>
    <td>
        <div class="d-flex flex-column">
            <a href="#"
               class="text-decoration-none fw-bold task-title-link"
               data-bs-toggle="modal"
               data-bs-target="#add-task-modal"
               data-task-id="<?php echo $task['id']; ?>"
               onclick="loadTaskForEdit(<?php echo $task['id']; ?>)">
                <?php echo htmlspecialchars($task['title']); ?>
            </a>
            <?php if (!empty($task['description'])): ?>
            <small class="text-muted task-description">
                <?php echo htmlspecialchars(truncateText($task['description'], 80)); ?>
            </small>
            <?php endif; ?>
        </div>
    </td>
    <td>
        <span class="badge <?php echo getStatusBadgeClass($task['status']); ?> task-status-badge">
            <?php if ($task['status'] === 'completed'): ?>
                <i class="bi bi-check-circle-fill me-1"></i>
            <?php elseif ($task['status'] === 'in_progress'): ?>
                <i class="bi bi-arrow-repeat me-1"></i>
            <?php endif; ?>
            <?php echo getStatusDisplayName($task['status']); ?>
        </span>
    </td>
    <td>
        <span class="badge <?php echo getPriorityBadgeClass($task['priority']); ?> task-priority-badge">
            <?php echo ucfirst($task['priority']); ?>
        </span>
    </td>
    <td class="task-due-date">
        <?php echo formatDueDateShort($task['due_date']); ?>
    </td>
    <td class="task-categories">
        <?php if (!empty($taskCategories)): ?>
            <?php foreach ($taskCategories as $category): ?>
                <span class="badge rounded-pill me-1 mb-1"
                      style="background-color: <?php echo htmlspecialchars($category['color']); ?>">
                    <i class="bi bi-tag-fill me-1"></i>
                    <?php echo htmlspecialchars($category['name']); ?>
                </span>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="text-muted small">None</span>
        <?php endif; ?>
    </td>
    <td class="text-end">
        <div class="btn-group btn-group-sm task-action-buttons" role="group">
            <?php if ($task['status'] !== 'completed'): ?>
            <button class="btn btn-outline-success"
                    title="Mark as Complete"
                    hx-post="/api/tasks/update-status-html.php"
                    hx-vals='{"task_id": "<?php echo $task['id']; ?>", "status": "completed"}'
                    hx-include="[name='csrf_token']"
                    hx-target="#task-row-<?php echo $task['id']; ?>"
                    hx-swap="outerHTML">
                <i class="bi bi-check-circle"></i>
            </button>
            <?php endif; ?>
            <button class="btn btn-outline-primary"
                    title="Edit"
                    data-bs-toggle="modal"
                    data-bs-target="#add-task-modal"
                    onclick="loadTaskForEdit(<?php echo $task['id']; ?>)">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-outline-danger"
                    title="Delete"
                    onclick="deleteTask(<?php echo $task['id']; ?>, '<?php echo htmlspecialchars(addslashes($task['title'])); ?>')">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
</tr>
    <?php
} else {
    http_response_code(400);
    echo '<div class="alert alert-danger">' . htmlspecialchars($result['error'] ?? 'Failed to update status') . '</div>';
}
