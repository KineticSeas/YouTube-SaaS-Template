<?php
/**
 * Add Task Modal Component
 * Bootstrap modal for creating new tasks
 * REQ-TASK-001 through REQ-TASK-008
 */
?>

<!-- Add Task Modal -->
<div id="add-task-modal" class="modal fade" tabindex="-1" aria-labelledby="add-task-modal-title" aria-hidden="true">
    <div id="add-task-modal-dialog" class="modal-dialog modal-lg">
        <div id="add-task-modal-content" class="modal-content">
            <!-- Modal Header -->
            <div id="add-task-modal-header" class="modal-header bg-primary text-white">
                <h5 id="add-task-modal-title" class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Add New Task
                </h5>
                <button id="add-task-modal-close" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div id="add-task-modal-body" class="modal-body">
                <form id="add-task-form"
                      action="/api/tasks/create.php"
                      method="POST"
                      hx-post="/api/tasks/create.php"
                      hx-target="#add-task-response"
                      hx-indicator="#add-task-spinner">

                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                    <!-- Response Container -->
                    <div id="add-task-response" class="mb-3"></div>

                    <!-- Task Title -->
                    <div id="task-title-group" class="mb-3">
                        <label for="task-title" class="form-label">
                            Task Title <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="task-title"
                               name="title"
                               required
                               maxlength="255"
                               placeholder="Enter task title"
                               autofocus>
                        <div id="task-title-invalid" class="invalid-feedback">
                            Please enter a task title (max 255 characters).
                        </div>
                    </div>

                    <!-- Task Description -->
                    <div id="task-description-group" class="mb-3">
                        <label for="task-description" class="form-label">
                            Description
                        </label>
                        <textarea class="form-control"
                                  id="task-description"
                                  name="description"
                                  rows="4"
                                  maxlength="5000"
                                  placeholder="Enter task description (optional)"></textarea>
                        <div id="task-description-help" class="form-text">
                            <span id="char-count">0</span> / 5000 characters
                        </div>
                    </div>

                    <!-- Row for Status, Priority, Due Date -->
                    <div id="task-details-row" class="row">
                        <!-- Status -->
                        <div class="col-md-4 mb-3">
                            <label for="task-status" class="form-label">
                                Status
                            </label>
                            <select class="form-select" id="task-status" name="status">
                                <option value="pending" selected>Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-4 mb-3">
                            <label for="task-priority" class="form-label">
                                Priority
                            </label>
                            <select class="form-select" id="task-priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <!-- Due Date -->
                        <div class="col-md-4 mb-3">
                            <label for="task-due-date" class="form-label">
                                Due Date
                            </label>
                            <input type="date"
                                   class="form-control"
                                   id="task-due-date"
                                   name="due_date"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Categories -->
                    <div id="task-categories-group" class="mb-3">
                        <label for="task-categories" class="form-label">
                            Categories
                        </label>
                        <?php
                        // Get user's categories for selection
                        $userCategories = getCategoriesByUserId(getCurrentUserId());
                        ?>
                        <?php if (empty($userCategories)): ?>
                            <div id="no-categories-notice" class="alert alert-info small">
                                <i class="bi bi-info-circle me-1"></i>
                                No categories yet. <a href="/categories.php" class="alert-link">Create categories</a> to organize your tasks.
                            </div>
                        <?php else: ?>
                            <select class="form-select" id="task-categories" name="categories[]" multiple size="<?php echo min(count($userCategories), 5); ?>">
                                <?php foreach ($userCategories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" style="padding: 8px;">
                                        <span class="badge" style="background-color: <?php echo htmlspecialchars($cat['color']); ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </span>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="task-categories-help" class="form-text">
                                Hold Ctrl/Cmd to select multiple categories. <a href="/categories.php" target="_blank">Manage categories</a>
                            </div>
                            <!-- Selected Categories Display -->
                            <div id="selected-categories-display" class="mt-2"></div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div id="add-task-modal-footer" class="modal-footer">
                <button id="add-task-cancel-btn" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button id="add-task-save-btn" type="submit" form="add-task-form" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Save Task
                    <span id="add-task-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Track the element that triggered the modal
let modalTriggerElement = null;

// Character counter for description
document.addEventListener('DOMContentLoaded', function() {
    const descriptionField = document.getElementById('task-description');
    const charCount = document.getElementById('char-count');

    if (descriptionField && charCount) {
        descriptionField.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Handle modal show - focus management
    const addTaskModal = document.getElementById('add-task-modal');
    if (addTaskModal) {
        addTaskModal.addEventListener('show.bs.modal', function(event) {
            // Store the element that triggered modal open (if available)
            if (event.relatedTarget) {
                modalTriggerElement = event.relatedTarget;
            } else {
                // If no relatedTarget, store currently focused element
                modalTriggerElement = document.activeElement;
            }
        });

        // Handle modal shown - ensure focus is managed
        addTaskModal.addEventListener('shown.bs.modal', function() {
            // Focus on the first form field
            const firstInput = document.getElementById('task-title');
            if (firstInput) {
                firstInput.focus();
            }
        });

        // Handle modal hide - restore focus before hiding
        addTaskModal.addEventListener('hide.bs.modal', function() {
            // Ensure focus is removed from modal before aria-hidden is applied
            document.activeElement.blur();
        });

        // Reset form when modal is closed
        addTaskModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('add-task-form');
            if (form) {
                form.reset();
                if (charCount) charCount.textContent = '0';

                // Reset form action back to create endpoint
                form.setAttribute('action', '/api/tasks/create.php');
                form.setAttribute('hx-post', '/api/tasks/create.php');
                form.removeAttribute('data-task-id');

                // Re-process the form element for HTMX to recognize the change
                htmx.process(form);

                // Remove hidden task_id field if it exists (created during edit mode)
                const taskIdField = form.querySelector('input[name="task_id"]');
                if (taskIdField) {
                    taskIdField.remove();
                }

                // Reset save button text
                const saveBtn = document.getElementById('add-task-save-btn');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Task\n                    <span id="add-task-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>';
                }
            }

            // Reset modal title back to "Add New Task"
            const modalTitle = document.getElementById('add-task-modal-title');
            if (modalTitle) {
                modalTitle.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Task';
            }

            // Clear response messages
            const response = document.getElementById('add-task-response');
            if (response) response.innerHTML = '';

            // Return focus to the element that triggered the modal
            // First check if trigger element was stored in dataset (from programmatic open)
            let focusTarget = modalTriggerElement;
            if (addTaskModal.dataset.triggerElement) {
                // Try to use the stored trigger element (may not work due to DOM changes)
                focusTarget = null; // Clear it since it may be stale
            }

            if (focusTarget && focusTarget.focus) {
                // Use setTimeout to ensure focus happens after modal is fully closed
                setTimeout(() => {
                    focusTarget.focus();
                }, 0);
            }

            // Clean up stored trigger element
            addTaskModal.removeAttribute('data-trigger-element');
            modalTriggerElement = null;
        });
    }

    // Preserve due date when modal is opened (for calendar integration)
    if (addTaskModal) {
        addTaskModal.addEventListener('show.bs.modal', function(event) {
            // Due date may already be set by Alpine.js openAddTaskModal function
            // No action needed here, just preserve existing value
        });
    }

    // Form validation and safety checks
    const addTaskForm = document.getElementById('add-task-form');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function(e) {
            const title = document.getElementById('task-title').value.trim();

            if (!title) {
                e.preventDefault();
                document.getElementById('task-title').classList.add('is-invalid');
                TodoTracker.showToast('Please enter a task title', 'error');
                return false;
            }

            if (title.length > 255) {
                e.preventDefault();
                document.getElementById('task-title').classList.add('is-invalid');
                TodoTracker.showToast('Task title is too long (max 255 characters)', 'error');
                return false;
            }

            document.getElementById('task-title').classList.remove('is-invalid');

            // Verify and ensure form is in correct state before submission
            const formAction = addTaskForm.getAttribute('hx-post');
            const taskIdField = addTaskForm.querySelector('input[name="task_id"]');
            const hasTaskId = taskIdField && taskIdField.value;

            console.log('Form submission state:', {
                hasTaskId: hasTaskId,
                formAction: formAction,
                taskIdValue: taskIdField?.value || 'none'
            });

            // Ensure endpoint matches task_id state
            if (hasTaskId && formAction !== '/api/tasks/update.php') {
                console.warn('Correcting form: task_id exists but endpoint is', formAction);
                addTaskForm.setAttribute('hx-post', '/api/tasks/update.php');
                addTaskForm.setAttribute('action', '/api/tasks/update.php');
                // Re-process the form for HTMX
                htmx.process(addTaskForm);
            }

            if (!hasTaskId && formAction !== '/api/tasks/create.php') {
                console.warn('Correcting form: no task_id but endpoint is', formAction);
                addTaskForm.setAttribute('hx-post', '/api/tasks/create.php');
                addTaskForm.setAttribute('action', '/api/tasks/create.php');
                // Re-process the form for HTMX
                htmx.process(addTaskForm);
            }
        });

        // Handle form submission response via HTMX
        addTaskForm.addEventListener('htmx:afterRequest', function(event) {
            try {
                const response = JSON.parse(event.detail.xhr.responseText);

                if (response.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('add-task-modal'));
                    if (modal) modal.hide();

                    // Show success toast
                    TodoTracker.showToast(response.message || 'Task saved successfully!', 'success');

                    // Reset form
                    addTaskForm.reset();
                    if (charCount) charCount.textContent = '0';

                    // Trigger refresh of task list
                    const taskListElement = document.getElementById('task-list');
                    if (taskListElement) {
                        htmx.trigger('#task-list', 'refreshTasks');
                    }

                    // Trigger refresh of stats
                    const statsElement = document.getElementById('stats-row');
                    if (statsElement) {
                        htmx.trigger('#stats-row', 'refreshStats');
                    }

                    // Reload page to update stats and list
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Handle error response
                    const errorMsg = response.message || 'An error occurred while saving the task';
                    console.error('Task save error:', response);
                    TodoTracker.showToast(errorMsg, 'error');

                    // Show error in modal response div as well
                    const responseDiv = document.getElementById('add-task-response');
                    if (responseDiv) {
                        responseDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Error!</strong> ${htmlspecialchars(errorMsg)}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                    }
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                const responseDiv = document.getElementById('add-task-response');
                if (responseDiv) {
                    responseDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error!</strong> An unexpected error occurred. Please check the browser console for details.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                }
            }
        });

        /**
         * Helper function to escape HTML
         */
        function htmlspecialchars(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    }
});
</script>
