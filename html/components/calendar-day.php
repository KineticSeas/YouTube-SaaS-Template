<?php
/**
 * Calendar Day View Component
 * Single day focus with full task details
 * REQ-CAL-201 through REQ-CAL-204
 *
 * Expected variables:
 * - $tasks - Array of tasks for the specific day
 * - $date - Date string (YYYY-MM-DD)
 * - $userId - Current user ID
 */

if (!isset($tasks) || !isset($date)) {
    echo '<div class="alert alert-danger m-3">Day view data not available</div>';
    return;
}

$dateDisplay = getFullDateDisplay($date);
?>

<!-- Day View Container -->
<div id="calendar-day-view" class="p-4">
    <!-- Day Header -->
    <div id="calendar-day-header" class="mb-4 pb-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 id="calendar-day-title" class="mb-1">
                    <?php echo htmlspecialchars($dateDisplay); ?>
                </h2>
                <p id="calendar-day-subtitle" class="text-muted mb-0">
                    <?php echo count($tasks); ?> task<?php echo count($tasks) !== 1 ? 's' : ''; ?> scheduled
                </p>
            </div>
            <div>
                <button id="calendar-day-add-btn"
                        class="btn btn-primary"
                        @click="openAddTaskModal('<?php echo $date; ?>')">
                    <i class="bi bi-plus-circle me-2"></i>Add Task
                </button>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div id="calendar-day-tasks">
        <?php if (empty($tasks)): ?>
        <!-- Empty State -->
        <div id="calendar-day-empty" class="text-center py-5">
            <i class="bi bi-calendar-check text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-4 text-muted">No tasks due on this date</h4>
            <p class="text-muted mb-4">Click the button above to add a task for this day</p>
            <button class="btn btn-outline-primary"
                    @click="openAddTaskModal('<?php echo $date; ?>')">
                <i class="bi bi-plus-circle me-2"></i>Add Your First Task
            </button>
        </div>
        <?php else: ?>
        <!-- Task Cards -->
        <div id="calendar-day-task-list" class="row g-3">
            <?php foreach ($tasks as $index => $task):
                $taskId = $task['id'];
                $taskTitle = htmlspecialchars($task['title']);
                $taskDesc = htmlspecialchars($task['description']);
                $taskPriority = $task['priority'];
                $taskStatus = $task['status'];
                $isOverdue = isTaskOverdue($task);
                $isCompleted = $taskStatus === 'completed';
                $createdAt = new DateTime($task['created_at']);

                // Priority badge and border
                $priorityBadge = 'bg-secondary';
                $priorityBorder = 'border-secondary';
                if ($taskPriority === 'high') {
                    $priorityBadge = 'bg-danger';
                    $priorityBorder = 'border-danger';
                } elseif ($taskPriority === 'medium') {
                    $priorityBadge = 'bg-warning text-dark';
                    $priorityBorder = 'border-warning';
                }

                // Status badge
                $statusBadge = 'bg-warning text-dark';
                $statusIcon = 'bi-hourglass-split';
                if ($taskStatus === 'completed') {
                    $statusBadge = 'bg-success';
                    $statusIcon = 'bi-check-circle-fill';
                } elseif ($taskStatus === 'in_progress') {
                    $statusBadge = 'bg-info';
                    $statusIcon = 'bi-arrow-repeat';
                }

                $taskCardId = "calendar-day-task-" . $taskId;
                $cardClasses = ['card', 'h-100', 'border-start', 'border-3', $priorityBorder];
                if ($isCompleted) {
                    $cardClasses[] = 'opacity-75';
                }
            ?>
            <div id="<?php echo $taskCardId; ?>" class="col-lg-6 col-md-12">
                <div class="<?php echo implode(' ', $cardClasses); ?>">
                    <div class="card-body">
                        <!-- Badges Row -->
                        <div class="d-flex gap-2 mb-3 flex-wrap">
                            <span class="badge <?php echo $priorityBadge; ?>">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <?php echo ucfirst($taskPriority); ?> Priority
                            </span>
                            <span class="badge <?php echo $statusBadge; ?>">
                                <i class="<?php echo $statusIcon; ?> me-1"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $taskStatus)); ?>
                            </span>
                            <?php if ($isOverdue): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-exclamation-triangle me-1"></i>Overdue
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Task Title -->
                        <h5 id="<?php echo $taskCardId; ?>-title" class="card-title mb-2">
                            <?php echo $taskTitle; ?>
                        </h5>

                        <!-- Task Description -->
                        <?php if (!empty($taskDesc)): ?>
                        <p id="<?php echo $taskCardId; ?>-description" class="card-text text-muted mb-3">
                            <?php echo nl2br($taskDesc); ?>
                        </p>
                        <?php endif; ?>

                        <!-- Categories -->
                        <?php if (!empty($task['category_names'])): ?>
                        <div id="<?php echo $taskCardId; ?>-categories" class="mb-3">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-tags me-1"></i>Categories:
                            </small>
                            <?php
                            $categories = explode(', ', $task['category_names']);
                            foreach ($categories as $category):
                            ?>
                            <span class="badge border me-1">
                                <?php echo htmlspecialchars($category); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Task Metadata -->
                        <div id="<?php echo $taskCardId; ?>-metadata" class="text-muted small mb-3">
                            <i class="bi bi-clock me-1"></i>
                            Created <?php echo $createdAt->format('M j, Y'); ?>
                        </div>

                        <!-- Action Buttons -->
                        <div id="<?php echo $taskCardId; ?>-actions" class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-fill"
                                    @click="openTaskModal(<?php echo $taskId; ?>)"
                                    aria-label="Edit task">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>

                            <?php if ($taskStatus !== 'completed'): ?>
                            <button class="btn btn-sm btn-outline-success flex-fill"
                                    hx-post="/api/tasks/update-status.php"
                                    hx-vals='{"task_id": <?php echo $taskId; ?>, "status": "completed"}'
                                    hx-trigger="click"
                                    hx-swap="none"
                                    hx-on::after-request="htmx.trigger('body', 'taskUpdated')"
                                    aria-label="Mark complete">
                                <i class="bi bi-check-circle me-1"></i>Complete
                            </button>
                            <?php endif; ?>

                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="if(confirm('Delete this task?')) {
                                        htmx.ajax('POST', '/api/tasks/delete.php', {
                                            values: {task_id: <?php echo $taskId; ?>},
                                            swap: 'none'
                                        }).then(() => htmx.trigger('body', 'taskUpdated'));
                                    }"
                                    aria-label="Delete task">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
