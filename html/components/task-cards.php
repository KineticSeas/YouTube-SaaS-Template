<?php
/**
 * Task Cards Component - Grid View
 * Bootstrap card grid for displaying tasks
 * REQ-LIST-201 through REQ-LIST-203
 */

// This component expects $tasks array to be passed from parent page

if (!isset($tasks)) {
    $tasks = [];
}

// Helper functions for display (same as table view)
function getPriorityBadgeClassCard($priority) {
    switch ($priority) {
        case 'high': return 'bg-danger';
        case 'medium': return 'bg-warning text-dark';
        case 'low': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}

function getStatusBadgeClassCard($status) {
    switch ($status) {
        case 'completed': return 'bg-success';
        case 'in_progress': return 'bg-info';
        case 'pending': return 'bg-warning text-dark';
        default: return 'bg-secondary';
    }
}

function getStatusDisplayNameCard($status) {
    switch ($status) {
        case 'in_progress': return 'In Progress';
        default: return ucfirst($status);
    }
}

function formatDueDateCard($dueDate) {
    if (empty($dueDate)) {
        return '<span class="text-muted"><i class="bi bi-calendar-x me-1"></i>No due date</span>';
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
        return '<span class="text-primary"><i class="bi bi-calendar-check me-1"></i>Tomorrow</span>';
    } else {
        return '<i class="bi bi-calendar3 me-1"></i>' . $date->format('M j, Y');
    }
}

function truncateDescription($text, $length = 120) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>

<div id="task-cards-container">
    <!-- Cards Header -->
    <div id="task-cards-header" class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">
                <i class="bi bi-grid-3x3-gap me-2"></i>Task Grid
            </h5>
        </div>
        <div>
            <?php if (!empty($tasks)): ?>
            <small class="text-muted">
                Showing <?php echo count($tasks); ?> task<?php echo count($tasks) !== 1 ? 's' : ''; ?>
            </small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cards Grid -->
    <?php if (empty($tasks)): ?>
    <div id="no-tasks-grid-message" class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
            <h4 class="text-muted mt-3">No tasks found</h4>
            <p class="text-muted">Try adjusting your filters or create a new task</p>
            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#add-task-modal">
                <i class="bi bi-plus-circle me-2"></i>Create Your First Task
            </button>
        </div>
    </div>
    <?php else: ?>
    <div id="task-cards-grid" class="row g-3">
        <?php foreach ($tasks as $task): ?>
            <?php
            // Get categories for this task
            $taskCategories = getTaskCategories($task['id']);
            $isOverdue = !empty($task['due_date']) && strtotime($task['due_date']) < strtotime('today') && $task['status'] !== 'completed';
            ?>
        <div class="col-lg-4 col-md-6" id="task-card-col-<?php echo $task['id']; ?>">
            <div class="card h-100 shadow-sm task-card <?php echo $isOverdue ? 'border-danger' : ''; ?>"
                 data-task-id="<?php echo $task['id']; ?>"
                 id="task-card-<?php echo $task['id']; ?>">
                <!-- Card Header with Badges -->
                <div class="card-header border-bottom-0 pb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <span class="badge <?php echo getPriorityBadgeClassCard($task['priority']); ?> me-1">
                                <?php if ($task['priority'] === 'high'): ?>
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <?php endif; ?>
                                <?php echo ucfirst($task['priority']); ?>
                            </span>
                            <span class="badge <?php echo getStatusBadgeClassCard($task['status']); ?>">
                                <?php if ($task['status'] === 'completed'): ?>
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                <?php elseif ($task['status'] === 'in_progress'): ?>
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                <?php endif; ?>
                                <?php echo getStatusDisplayNameCard($task['status']); ?>
                            </span>
                        </div>
                        <div>
                            <input type="checkbox"
                                   class="form-check-input task-checkbox"
                                   value="<?php echo $task['id']; ?>"
                                   onclick="updateBulkActions()">
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body pt-2">
                    <h5 class="card-title">
                        <a href="#"
                           class="text-decoration-none text-dark task-title-link"
                           data-bs-toggle="modal"
                           data-bs-target="#add-task-modal"
                           onclick="loadTaskForEdit(<?php echo $task['id']; ?>)">
                            <?php echo htmlspecialchars($task['title']); ?>
                        </a>
                    </h5>

                    <?php if (!empty($task['description'])): ?>
                    <p class="card-text text-muted small task-description">
                        <?php echo htmlspecialchars(truncateDescription($task['description'])); ?>
                    </p>
                    <?php else: ?>
                    <p class="card-text text-muted small fst-italic">
                        No description provided
                    </p>
                    <?php endif; ?>

                    <!-- Due Date -->
                    <div class="mb-2 task-due-date small">
                        <?php echo formatDueDateCard($task['due_date']); ?>
                    </div>

                    <!-- Categories -->
                    <?php if (!empty($taskCategories)): ?>
                    <div class="task-categories mb-2">
                        <?php foreach ($taskCategories as $category): ?>
                            <span class="badge rounded-pill me-1 mb-1"
                                  style="background-color: <?php echo htmlspecialchars($category['color']); ?>; font-size: 0.75rem;">
                                <i class="bi bi-tag-fill me-1"></i>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Created Date -->
                    <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i>
                        Created <?php echo date('M j, Y', strtotime($task['created_at'])); ?>
                    </div>
                </div>

                <!-- Card Footer with Actions -->
                <div class="card-footer border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group btn-group-sm task-action-buttons" role="group">
                            <?php if ($task['status'] !== 'completed'): ?>
                            <button class="btn btn-outline-success"
                                    title="Mark as Complete"
                                    hx-post="/api/tasks/update-status.php"
                                    hx-vals='{"task_id": "<?php echo $task['id']; ?>", "status": "completed"}'
                                    hx-include="[name='csrf_token']"
                                    hx-target="#task-card-col-<?php echo $task['id']; ?>"
                                    hx-swap="outerHTML">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn btn-outline-secondary"
                                    title="Archive Task"
                                    onclick="archiveTaskCard(<?php echo $task['id']; ?>, '<?php echo htmlspecialchars(addslashes($task['title'])); ?>')">
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
                                    onclick="deleteTaskCard(<?php echo $task['id']; ?>, '<?php echo htmlspecialchars(addslashes($task['title'])); ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <?php if ($isOverdue): ?>
                        <small class="text-danger fw-bold">
                            <i class="bi bi-exclamation-circle-fill"></i> Overdue
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Bulk Actions Bar (same as table view) -->
<div id="bulk-actions-bar-grid" class="position-fixed bottom-0 start-0 end-0 bg-primary text-white p-3 shadow-lg" style="display: none; z-index: 1000;">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-3">
                <strong>
                    <i class="bi bi-check-square me-2"></i>
                    <span id="bulk-selected-count-grid">0</span> task(s) selected
                </strong>
            </div>
            <div class="col-md-6">
                <div class="btn-group" role="group">
                    <button class="btn btn-light btn-sm" onclick="bulkMarkCompleteGrid()">
                        <i class="bi bi-check-circle me-1"></i>Mark Complete
                    </button>
                    <button class="btn btn-light btn-sm" onclick="bulkArchiveGrid()">
                        <i class="bi bi-archive me-1"></i>Archive Completed
                    </button>
                    <button class="btn btn-light btn-sm" onclick="bulkChangePriorityGrid()">
                        <i class="bi bi-exclamation-circle me-1"></i>Change Priority
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="bulkDeleteGrid()">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
            <div class="col-md-3 text-end">
                <button class="btn btn-outline-light btn-sm" onclick="clearBulkSelectionGrid()">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Card hover effects */
.task-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.task-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.task-card.border-danger {
    border-width: 2px;
    border-left-width: 5px;
}
</style>

<script>
// Grid view bulk selection (similar to table view)
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.task-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulk-actions-bar-grid');
    const countDisplay = document.getElementById('bulk-selected-count-grid');

    if (count > 0) {
        if (bulkBar) bulkBar.style.display = 'block';
        if (countDisplay) countDisplay.textContent = count;
    } else {
        if (bulkBar) bulkBar.style.display = 'none';
    }
}

function clearBulkSelectionGrid() {
    document.querySelectorAll('.task-checkbox').forEach(cb => cb.checked = false);
    updateBulkActions();
}

function getSelectedTaskIdsGrid() {
    const checkboxes = document.querySelectorAll('.task-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function bulkMarkCompleteGrid() {
    const taskIds = getSelectedTaskIdsGrid();
    if (taskIds.length === 0) return;

    if (confirm(`Mark ${taskIds.length} task(s) as complete?`)) {
        console.log('Bulk complete:', taskIds);
        // TODO: Implement HTMX call to bulk update endpoint
    }
}

function bulkChangePriorityGrid() {
    const taskIds = getSelectedTaskIdsGrid();
    if (taskIds.length === 0) return;

    const priority = prompt('Enter priority (low, medium, high):');
    if (priority && ['low', 'medium', 'high'].includes(priority.toLowerCase())) {
        console.log('Bulk priority change:', taskIds, priority);
        // TODO: Implement HTMX call to bulk update endpoint
    }
}

function bulkDeleteGrid() {
    const taskIds = getSelectedTaskIdsGrid();
    if (taskIds.length === 0) return;

    if (confirm(`Are you sure you want to delete ${taskIds.length} task(s)? This action cannot be undone.`)) {
        console.log('Bulk delete:', taskIds);
        // TODO: Implement HTMX call to bulk update endpoint
    }
}

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

function deleteTaskCard(taskId, taskTitle) {
    if (confirm(`Are you sure you want to delete "${taskTitle}"?`)) {
        htmx.ajax('POST', '/api/tasks/delete.php', {
            values: {task_id: taskId},
            target: '#task-card-col-' + taskId,
            swap: 'delete'
        }).then(() => {
            window.location.reload();
        });
    }
}

// Archive single task in grid view
function archiveTaskCard(taskId, taskTitle) {
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
                // Remove the task card from the grid
                const cardCol = document.getElementById('task-card-col-' + taskId);
                if (cardCol) {
                    cardCol.style.transition = 'opacity 0.3s';
                    cardCol.style.opacity = '0';
                    setTimeout(() => {
                        cardCol.remove();
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

// Bulk archive for grid view
function bulkArchiveGrid() {
    const taskIds = getSelectedTaskIdsGrid();
    if (taskIds.length === 0) return;

    // Check if any selected tasks are completed
    const completedTaskIds = taskIds.filter(id => {
        const card = document.querySelector(`#task-card-${id}`);
        if (card) {
            const statusBadge = card.querySelector('.badge');
            // Check if any badge contains "Completed"
            const badges = card.querySelectorAll('.badge');
            for (let badge of badges) {
                if (badge.textContent.includes('Completed')) {
                    return true;
                }
            }
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
                    // Remove the task card from the grid
                    const cardCol = document.getElementById('task-card-col-' + taskId);
                    if (cardCol) {
                        cardCol.style.transition = 'opacity 0.3s';
                        cardCol.style.opacity = '0';
                        setTimeout(() => {
                            cardCol.remove();
                            if (archivedCount === completedTaskIds.length) {
                                clearBulkSelectionGrid();
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
