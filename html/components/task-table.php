<?php
/**
 * Task Table Component - List View
 * Bootstrap responsive table for displaying tasks
 * REQ-LIST-101 through REQ-LIST-105
 */

// This component expects $tasks array to be passed from parent page

if (!isset($tasks)) {
    $tasks = [];
}

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
    $now = new DateTime();
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
?>

<div id="task-table-container" class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 id="task-table-title" class="mb-0">
                <i class="bi bi-list-ul me-2"></i>Task List
            </h5>
        </div>
        <div id="task-table-actions">
            <?php if (!empty($tasks)): ?>
            <small class="text-muted">
                Showing <?php echo count($tasks); ?> task<?php echo count($tasks) !== 1 ? 's' : ''; ?>
            </small>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tasks-table" class="table table-hover table-striped mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" class="form-check-input" id="select-all-tasks" onclick="toggleAllTasks(this)">
                    </th>
                    <th style="width: 40%;">Task</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Priority</th>
                    <th style="width: 15%;">Due Date</th>
                    <th style="width: 15%;">Categories</th>
                    <th style="width: 10%;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="tasks-table-body">
                <?php if (empty($tasks)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div id="no-tasks-message">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3 mb-0">No tasks found</p>
                            <p class="text-muted small">Try adjusting your filters or create a new task</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <?php
                    // Get categories for this task
                    $taskCategories = getTaskCategories($task['id']);
                    $isOverdue = !empty($task['due_date']) && strtotime($task['due_date']) < strtotime('today') && $task['status'] !== 'completed';
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
                            <?php else: ?>
                            <button class="btn btn-outline-secondary"
                                    title="Archive Task"
                                    onclick="archiveTask(<?php echo $task['id']; ?>, '<?php echo htmlspecialchars(addslashes($task['title'])); ?>')">
                                <i class="bi bi-archive"></i>
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
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($tasks)): ?>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Click on a task title to edit, or use the action buttons
            </small>
            <div id="bulk-action-trigger" style="display: none;">
                <small class="text-primary">
                    <span id="selected-count">0</span> task(s) selected
                </small>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Bulk Actions Bar (Fixed at bottom when tasks selected) -->
<div id="bulk-actions-bar" class="position-fixed bottom-0 start-0 end-0 bg-primary text-white p-3 shadow-lg" style="display: none; z-index: 1000;">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-3">
                <strong>
                    <i class="bi bi-check-square me-2"></i>
                    <span id="bulk-selected-count">0</span> task(s) selected
                </strong>
            </div>
            <div class="col-md-6">
                <div class="btn-group" role="group">
                    <button class="btn btn-light btn-sm" onclick="bulkMarkComplete()">
                        <i class="bi bi-check-circle me-1"></i>Mark Complete
                    </button>
                    <button class="btn btn-light btn-sm" onclick="bulkArchive()">
                        <i class="bi bi-archive me-1"></i>Archive Completed
                    </button>
                    <button class="btn btn-light btn-sm" onclick="bulkChangePriority()">
                        <i class="bi bi-exclamation-circle me-1"></i>Change Priority
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="bulkDelete()">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
            <div class="col-md-3 text-end">
                <button class="btn btn-outline-light btn-sm" onclick="clearBulkSelection()">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Bulk selection management
function toggleAllTasks(checkbox) {
    const checkboxes = document.querySelectorAll('.task-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.task-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulk-actions-bar');
    const bulkTrigger = document.getElementById('bulk-action-trigger');
    const countDisplay = document.getElementById('bulk-selected-count');
    const selectedCount = document.getElementById('selected-count');

    if (count > 0) {
        bulkBar.style.display = 'block';
        if (bulkTrigger) bulkTrigger.style.display = 'block';
        if (countDisplay) countDisplay.textContent = count;
        if (selectedCount) selectedCount.textContent = count;
    } else {
        bulkBar.style.display = 'none';
        if (bulkTrigger) bulkTrigger.style.display = 'none';
    }
}

function clearBulkSelection() {
    document.querySelectorAll('.task-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('select-all-tasks').checked = false;
    updateBulkActions();
}

function getSelectedTaskIds() {
    const checkboxes = document.querySelectorAll('.task-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function bulkMarkComplete() {
    const taskIds = getSelectedTaskIds();
    if (taskIds.length === 0) return;

    if (confirm(`Mark ${taskIds.length} task(s) as complete?`)) {
        // Implementation will be in bulk-update.php
        console.log('Bulk complete:', taskIds);
        // TODO: Implement HTMX call to bulk update endpoint
    }
}

function bulkChangePriority() {
    const taskIds = getSelectedTaskIds();
    if (taskIds.length === 0) return;

    const priority = prompt('Enter priority (low, medium, high):');
    if (priority && ['low', 'medium', 'high'].includes(priority.toLowerCase())) {
        console.log('Bulk priority change:', taskIds, priority);
        // TODO: Implement HTMX call to bulk update endpoint
    }
}

function bulkDelete() {
    const taskIds = getSelectedTaskIds();
    if (taskIds.length === 0) return;

    if (confirm(`Are you sure you want to delete ${taskIds.length} task(s)? This action cannot be undone.`)) {
        console.log('Bulk delete:', taskIds);
        // TODO: Implement HTMX call to bulk update endpoint
    }
}

// Load task data and populate modal for editing
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
                if (categorySelect) {
                    // Get task categories
                    fetch(`/api/tasks/get.php?id=${taskId}`)
                        .then(res => res.json())
                        .then(taskData => {
                            if (taskData.task && taskData.task.categories) {
                                const categoryIds = taskData.task.categories.map(cat => cat.id.toString());
                                Array.from(categorySelect.options).forEach(option => {
                                    option.selected = categoryIds.includes(option.value);
                                });
                            }
                        });
                }

                // Update modal title
                const modalTitle = document.getElementById('add-task-modal-title');
                if (modalTitle) {
                    modalTitle.innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Task';
                }

                // Update form action to use update endpoint
                const form = document.getElementById('add-task-form');
                if (form) {
                    form.setAttribute('action', `/api/tasks/update.php?id=${taskId}`);
                    form.setAttribute('hx-post', `/api/tasks/update.php?id=${taskId}`);
                    form.setAttribute('data-task-id', taskId);
                }

                // Clear any previous error messages
                const responseDiv = document.getElementById('add-task-response');
                if (responseDiv) {
                    responseDiv.innerHTML = '';
                }
            } else {
                TodoTracker.showToast('Failed to load task', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading task:', error);
            TodoTracker.showToast('Error loading task', 'error');
        });
}

// Placeholder for delete functionality
function deleteTask(taskId, taskTitle) {
    if (confirm(`Are you sure you want to delete "${taskTitle}"?`)) {
        // Use HTMX to delete
        htmx.ajax('POST', '/api/tasks/delete.php', {
            values: {task_id: taskId},
            target: '#task-row-' + taskId,
            swap: 'delete'
        }).then(() => {
            window.location.reload();
        });
    }
}

// Archive single task
function archiveTask(taskId, taskTitle) {
    if (confirm(`Archive "${taskTitle}"? You can view archived tasks from the Archive page.`)) {
        // Get CSRF token
        const csrfToken = document.querySelector('input[name="csrf_token"]');
        const form = new FormData();
        form.append('task_id', taskId);
        if (csrfToken) {
            form.append('csrf_token', csrfToken.value);
        }

        fetch('/api/archive/archive-task.php', {
            method: 'POST',
            body: form
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                TodoTracker.showToast('Task archived successfully!', 'success');
                // Remove the task row from the table
                const row = document.getElementById('task-row-' + taskId);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        updateBulkActions();
                    }, 300);
                }
            } else {
                TodoTracker.showToast(data.message || 'Failed to archive task', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            TodoTracker.showToast('Error archiving task', 'error');
        });
    }
}

// Bulk archive completed tasks
function bulkArchive() {
    const taskIds = getSelectedTaskIds();
    if (taskIds.length === 0) return;

    // Check if any selected tasks are completed
    const completedTaskIds = taskIds.filter(id => {
        const row = document.querySelector(`#task-row-${id}`);
        if (row) {
            const statusBadge = row.querySelector('.task-status-badge');
            return statusBadge && statusBadge.textContent.includes('Completed');
        }
        return false;
    });

    if (completedTaskIds.length === 0) {
        TodoTracker.showToast('Please select completed tasks to archive', 'warning');
        return;
    }

    const message = completedTaskIds.length === taskIds.length
        ? `Archive ${completedTaskIds.length} completed task(s)?`
        : `Archive ${completedTaskIds.length} completed task(s) out of ${taskIds.length} selected?`;

    if (confirm(message + '\n\nYou can view archived tasks from the Archive page.')) {
        // Get CSRF token
        const csrfToken = document.querySelector('input[name="csrf_token"]');

        // Archive each completed task
        let archivedCount = 0;
        completedTaskIds.forEach(taskId => {
            const form = new FormData();
            form.append('task_id', taskId);
            if (csrfToken) {
                form.append('csrf_token', csrfToken.value);
            }

            fetch('/api/archive/archive-task.php', {
                method: 'POST',
                body: form
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    archivedCount++;
                    // Remove the task row from the table
                    const row = document.getElementById('task-row-' + taskId);
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            if (archivedCount === completedTaskIds.length) {
                                clearBulkSelection();
                                TodoTracker.showToast(`${archivedCount} task(s) archived successfully!`, 'success');
                            }
                        }, 300);
                    }
                }
            })
            .catch(error => {
                console.error('Error archiving task ' + taskId + ':', error);
            });
        });
    }
}
</script>
