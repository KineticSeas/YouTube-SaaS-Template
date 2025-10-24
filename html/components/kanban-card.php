<?php
/**
 * Kanban Task Card Component
 * Individual draggable task card for Kanban board
 *
 * Required variables:
 * - $task: array (task object with all fields)
 *
 * REQ-KANBAN-101 through REQ-KANBAN-104
 */

// Priority border color classes
$priorityBorderClass = '';
switch ($task['priority']) {
    case 'high':
        $priorityBorderClass = 'border-danger';
        break;
    case 'medium':
        $priorityBorderClass = 'border-warning';
        break;
    case 'low':
    default:
        $priorityBorderClass = 'border-secondary';
        break;
}

// Priority badge color
$priorityBadgeClass = '';
switch ($task['priority']) {
    case 'high':
        $priorityBadgeClass = 'bg-danger';
        break;
    case 'medium':
        $priorityBadgeClass = 'bg-warning text-dark';
        break;
    case 'low':
    default:
        $priorityBadgeClass = 'bg-secondary';
        break;
}

// Truncate title and description
$displayTitle = mb_strlen($task['title']) > 50
    ? mb_substr($task['title'], 0, 50) . '...'
    : $task['title'];

$displayDescription = '';
if (!empty($task['description'])) {
    $displayDescription = mb_strlen($task['description']) > 100
        ? mb_substr($task['description'], 0, 100) . '...'
        : $task['description'];
}

// Check if overdue (due date is past and not completed)
$isOverdue = false;
if ($task['due_date'] && $task['status'] !== 'completed') {
    $dueDateTime = strtotime($task['due_date']);
    $today = strtotime(date('Y-m-d'));
    if ($dueDateTime < $today) {
        $isOverdue = true;
    }
}

// Format due date
$dueDateDisplay = '';
$dueDateBadgeClass = 'bg-secondary';
if ($task['due_date']) {
    $dueDateDisplay = date('M j', strtotime($task['due_date']));
    if ($isOverdue) {
        $dueDateBadgeClass = 'bg-danger';
    }
}

// Get task categories
$taskCategories = [];
if (!empty($task['categories'])) {
    $taskCategories = $task['categories'];
}

// Card ID
$cardId = 'kanban-card-' . $task['id'];
?>

<div class="card kanban-task-card mb-2 shadow-sm border-start border-4 <?php echo $priorityBorderClass; ?>"
     id="<?php echo $cardId; ?>"
     draggable="true"
     data-task-id="<?php echo $task['id']; ?>"
     data-task-status="<?php echo $task['status']; ?>"
     onclick="openTaskModal(<?php echo $task['id']; ?>)"
     style="cursor: move;">

    <div class="card-body p-2">
        <!-- Task Title -->
        <h6 class="card-title fw-bold mb-1" style="font-size: 0.9rem;">
            <?php echo htmlspecialchars($displayTitle); ?>
        </h6>

        <!-- Task Description -->
        <?php if ($displayDescription): ?>
        <p class="card-text small text-muted mb-2" style="font-size: 0.8rem;">
            <?php echo htmlspecialchars($displayDescription); ?>
        </p>
        <?php endif; ?>

        <!-- Badges Row -->
        <div class="d-flex flex-wrap align-items-center gap-1 mb-2">
            <!-- Priority Badge -->
            <span class="badge <?php echo $priorityBadgeClass; ?> badge-sm">
                <?php echo ucfirst($task['priority']); ?>
            </span>

            <!-- Categories -->
            <?php foreach ($taskCategories as $category): ?>
            <span class="badge badge-sm"
                  style="background-color: <?php echo htmlspecialchars($category['color']); ?>; font-size: 0.7rem;">
                <?php echo htmlspecialchars($category['name']); ?>
            </span>
            <?php endforeach; ?>

            <!-- Overdue Indicator -->
            <?php if ($isOverdue): ?>
            <span class="badge bg-danger badge-sm">
                <i class="bi bi-exclamation-triangle-fill"></i> Overdue
            </span>
            <?php endif; ?>
        </div>

        <!-- Due Date Row -->
        <div class="d-flex justify-content-between align-items-center">
            <?php if ($task['due_date']): ?>
            <small>
                <span class="badge <?php echo $dueDateBadgeClass; ?>" style="font-size: 0.7rem;">
                    <i class="bi bi-calendar-event me-1"></i>Due: <?php echo $dueDateDisplay; ?>
                </span>
            </small>
            <?php else: ?>
            <small>
                <span class="badge bg-light text-muted" style="font-size: 0.7rem;">
                    No due date
                </span>
            </small>
            <?php endif; ?>

            <!-- Mobile Move Button (shown on small screens) -->
            <div class="dropdown d-md-none">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle p-1"
                        type="button"
                        data-bs-toggle="dropdown"
                        onclick="event.stopPropagation();"
                        aria-label="Move task">
                    <i class="bi bi-arrow-left-right"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if ($task['status'] !== 'pending'): ?>
                    <li>
                        <a class="dropdown-item" href="#" onclick="moveTaskMobile(<?php echo $task['id']; ?>, 'pending'); event.stopPropagation(); return false;">
                            <i class="bi bi-arrow-left me-2"></i>Move to Pending
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($task['status'] !== 'in_progress'): ?>
                    <li>
                        <a class="dropdown-item" href="#" onclick="moveTaskMobile(<?php echo $task['id']; ?>, 'in_progress'); event.stopPropagation(); return false;">
                            <i class="bi bi-arrow-right me-2"></i>Move to In Progress
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($task['status'] !== 'completed'): ?>
                    <li>
                        <a class="dropdown-item" href="#" onclick="moveTaskMobile(<?php echo $task['id']; ?>, 'completed'); event.stopPropagation(); return false;">
                            <i class="bi bi-check-circle me-2"></i>Move to Completed
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
