<?php
/**
 * Task List Component
 * Displays tasks as Bootstrap list-group items
 * REQ-TASK-101 through REQ-TASK-107
 *
 * Parameters:
 * - $tasks: Array of task objects
 * - $showEmpty: bool - Show empty state message (default true)
 */

$tasks = $tasks ?? [];
$showEmpty = $showEmpty ?? true;

/**
 * Get badge class for priority
 */
function getPriorityBadgeClass($priority) {
    switch ($priority) {
        case 'high':
            return 'bg-danger';
        case 'medium':
            return 'bg-warning text-dark';
        case 'low':
        default:
            return 'bg-secondary';
    }
}

/**
 * Get badge class for status
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'completed':
            return 'bg-success';
        case 'in_progress':
            return 'bg-info text-dark';
        case 'pending':
        default:
            return 'bg-warning text-dark';
    }
}

/**
 * Get status display name
 */
function getStatusDisplayName($status) {
    switch ($status) {
        case 'in_progress':
            return 'In Progress';
        case 'completed':
            return 'Completed';
        case 'pending':
        default:
            return 'Pending';
    }
}

/**
 * Check if task is overdue
 */
function isTaskOverdue($task) {
    if (empty($task['due_date']) || $task['status'] === 'completed') {
        return false;
    }
    return strtotime($task['due_date']) < strtotime('today');
}

/**
 * Format due date for display
 */
function formatDueDate($dueDate) {
    if (empty($dueDate)) {
        return 'No due date';
    }

    $date = new DateTime($dueDate);
    $now = new DateTime();
    $diff = $now->diff($date);

    // If today
    if ($date->format('Y-m-d') === $now->format('Y-m-d')) {
        return 'Today';
    }

    // If tomorrow
    $tomorrow = new DateTime('tomorrow');
    if ($date->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
        return 'Tomorrow';
    }

    // If within a week
    if ($diff->days < 7 && $diff->invert == 0) {
        return $date->format('l'); // Day name
    }

    // Default format
    return $date->format('M j, Y');
}

/**
 * Truncate description
 */
function truncateDescription($description, $maxLength = 100) {
    if (strlen($description) <= $maxLength) {
        return $description;
    }
    return substr($description, 0, $maxLength) . '...';
}
?>

<div id="task-list-container" class="list-group list-group-flush">
    <?php if (empty($tasks) && $showEmpty): ?>
        <!-- Empty State -->
        <div id="task-list-empty" class="text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
            <p class="text-muted mt-3 mb-0">No tasks found.</p>
            <p class="text-muted small">Create your first task to get started!</p>
        </div>
    <?php else: ?>
        <!-- Task Items -->
        <?php foreach ($tasks as $task): ?>
            <?php
            $isOverdue = isTaskOverdue($task);
            $priorityBadge = getPriorityBadgeClass($task['priority']);
            $statusBadge = getStatusBadgeClass($task['status']);
            $taskCategories = getTaskCategories($task['id']);
            ?>
            <div id="task-item-<?php echo $task['id']; ?>"
                 class="list-group-item list-group-item-action <?php echo $isOverdue ? 'border-start border-danger border-3' : ''; ?>">

                <div class="d-flex w-100 justify-content-between align-items-start">
                    <!-- Task Info -->
                    <div class="flex-grow-1 me-3">
                        <!-- Title -->
                        <h6 id="task-title-<?php echo $task['id']; ?>" class="mb-1 fw-bold">
                            <?php if ($task['status'] === 'completed'): ?>
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($task['title']); ?>
                        </h6>

                        <!-- Description -->
                        <?php if (!empty($task['description'])): ?>
                            <p id="task-description-<?php echo $task['id']; ?>" class="mb-2 text-muted small">
                                <?php echo htmlspecialchars(truncateDescription($task['description'])); ?>
                            </p>
                        <?php endif; ?>

                        <!-- Badges and Meta Info -->
                        <div id="task-meta-<?php echo $task['id']; ?>" class="d-flex flex-wrap gap-2 align-items-center">
                            <!-- Priority Badge -->
                            <span class="badge <?php echo $priorityBadge; ?>">
                                <?php echo ucfirst($task['priority']); ?>
                            </span>

                            <!-- Status Badge -->
                            <span class="badge <?php echo $statusBadge; ?>">
                                <?php echo getStatusDisplayName($task['status']); ?>
                            </span>

                            <!-- Category Badges -->
                            <?php if (!empty($taskCategories)): ?>
                                <?php foreach ($taskCategories as $category): ?>
                                    <span class="badge rounded-pill" style="background-color: <?php echo htmlspecialchars($category['color']); ?>; color: #fff;">
                                        <i class="bi bi-tag-fill me-1"></i>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Due Date -->
                            <?php if (!empty($task['due_date'])): ?>
                                <span class="small text-muted">
                                    <i class="bi bi-calendar3"></i>
                                    <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo formatDueDate($task['due_date']); ?>
                                        <?php if ($isOverdue): ?>
                                            <i class="bi bi-exclamation-triangle text-danger ms-1"></i>
                                        <?php endif; ?>
                                    </span>
                                </span>
                            <?php endif; ?>

                            <!-- Created Date -->
                            <span class="small text-muted">
                                <i class="bi bi-clock"></i>
                                <?php
                                $created = new DateTime($task['created_at']);
                                echo $created->format('M j, g:i A');
                                ?>
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div id="task-actions-<?php echo $task['id']; ?>" class="btn-group btn-group-sm" role="group">
                        <?php if ($task['status'] !== 'completed'): ?>
                            <!-- Mark Complete Button -->
                            <button id="task-complete-btn-<?php echo $task['id']; ?>"
                                    type="button"
                                    class="btn btn-outline-success"
                                    title="Mark as Complete"
                                    onclick="markTaskComplete(<?php echo $task['id']; ?>)">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        <?php endif; ?>

                        <!-- Edit Button -->
                        <button id="task-edit-btn-<?php echo $task['id']; ?>"
                                type="button"
                                class="btn btn-outline-primary"
                                title="Edit Task"
                                onclick="editTask(<?php echo $task['id']; ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <!-- Delete Button -->
                        <button id="task-delete-btn-<?php echo $task['id']; ?>"
                                type="button"
                                class="btn btn-outline-danger"
                                title="Delete Task"
                                onclick="deleteTask(<?php echo $task['id']; ?>, '<?php echo htmlspecialchars(addslashes($task['title'])); ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
/**
 * Mark task as complete
 */
function markTaskComplete(taskId) {
    if (confirm('Mark this task as complete?')) {
        const form = new FormData();
        form.append('task_id', taskId);
        form.append('status', 'completed');

        // Get CSRF token from the page
        const csrfToken = document.querySelector('input[name="csrf_token"]');
        if (csrfToken) {
            form.append('csrf_token', csrfToken.value);
        }

        fetch('/api/tasks/update-status.php', {
            method: 'POST',
            body: form
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                TodoTracker.showToast('Task marked as complete!', 'success');
                // Trigger refresh of task list
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                TodoTracker.showToast(data.message || 'Failed to update task', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            TodoTracker.showToast('Error updating task', 'error');
        });
    }
}

/**
 * Edit task
 */
function editTask(taskId) {
    loadTaskForEdit(taskId);
    const modal = new bootstrap.Modal(document.getElementById('add-task-modal'));
    modal.show();
}

/**
 * Load task data for editing
 */
function loadTaskForEdit(taskId) {
    // Fetch task data from API
    fetch(`/api/tasks/get.php?id=${taskId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load task');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.task) {
                const task = data.task;

                // Populate form fields
                document.getElementById('task-title').value = task.title || '';
                document.getElementById('task-description').value = task.description || '';
                document.getElementById('task-status').value = task.status || 'pending';
                document.getElementById('task-priority').value = task.priority || 'medium';
                document.getElementById('task-due-date').value = task.due_date || '';

                // Update character count
                const charCount = document.getElementById('char-count');
                if (charCount) {
                    charCount.textContent = (task.description || '').length;
                }

                // Set selected categories
                const categorySelect = document.getElementById('task-categories');
                if (categorySelect && task.categories && task.categories.length > 0) {
                    const categoryIds = task.categories.map(cat => cat.id.toString());
                    Array.from(categorySelect.options).forEach(option => {
                        option.selected = categoryIds.includes(option.value);
                    });
                }

                // Update modal title
                const modalTitle = document.getElementById('add-task-modal-title');
                if (modalTitle) {
                    modalTitle.innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Task';
                }

                // Update form action to update endpoint
                const form = document.getElementById('add-task-form');
                if (form) {
                    form.setAttribute('action', '/api/tasks/update.php');
                    form.setAttribute('hx-post', '/api/tasks/update.php');
                    form.setAttribute('data-task-id', taskId);

                    // Re-process the form element for HTMX to recognize the change
                    htmx.process(form);

                    // Add hidden task ID field if it doesn't exist
                    let taskIdField = form.querySelector('input[name="task_id"]');
                    if (!taskIdField) {
                        taskIdField = document.createElement('input');
                        taskIdField.type = 'hidden';
                        taskIdField.name = 'task_id';
                        form.insertBefore(taskIdField, form.firstChild);
                    }
                    taskIdField.value = taskId;

                    // Debug logging
                    console.log('Edit mode loaded:', {
                        taskId: taskId,
                        formAction: form.getAttribute('hx-post'),
                        taskIdFieldValue: taskIdField.value
                    });

                    // Update button text
                    const saveBtn = document.getElementById('add-task-save-btn');
                    if (saveBtn) {
                        saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Update Task<span id="add-task-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>';
                    }
                }
            } else {
                TodoTracker.showToast('Failed to load task details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            TodoTracker.showToast('Error loading task', 'error');
        });
}

/**
 * Delete task
 */
function deleteTask(taskId, taskTitle) {
    if (confirm('Are you sure you want to delete "' + taskTitle + '"?\n\nThis action cannot be undone.')) {
        const form = new FormData();
        form.append('task_id', taskId);

        // Get CSRF token from the page
        const csrfToken = document.querySelector('input[name="csrf_token"]');
        if (csrfToken) {
            form.append('csrf_token', csrfToken.value);
        }

        fetch('/api/tasks/delete.php', {
            method: 'POST',
            body: form
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                TodoTracker.showToast('Task deleted successfully!', 'success');
                // Trigger refresh of task list
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                TodoTracker.showToast(data.message || 'Failed to delete task', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            TodoTracker.showToast('Error deleting task', 'error');
        });
    }
}
</script>
