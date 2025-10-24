<?php
/**
 * Quick Add Task Component
 * Simple one-field task creation for dashboard
 * REQ-TASK-006
 */
?>

<!-- Quick Add Task Card -->
<div id="quick-add-card" class="card mb-4 shadow-sm">
    <div class="card-body">
        <div id="quick-add-header" class="d-flex justify-content-between align-items-center mb-3">
            <h5 id="quick-add-title" class="card-title mb-0">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Quick Add Task
            </h5>
            <button id="quick-add-detailed-btn" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#add-task-modal">
                <i class="bi bi-pencil-square me-1"></i>Detailed
            </button>
        </div>

        <form id="quick-add-form"
              action="/api/tasks/create.php"
              method="POST"
              hx-post="/api/tasks/create.php"
              hx-target="#quick-add-response"
              hx-indicator="#quick-add-spinner"
              hx-swap="none">

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

            <!-- Hidden defaults -->
            <input type="hidden" name="status" value="pending">
            <input type="hidden" name="priority" value="medium">

            <!-- Response Container -->
            <div id="quick-add-response" class="mb-2"></div>

            <!-- Quick Add Input -->
            <div id="quick-add-input-group" class="input-group input-group-lg">
                <input type="text"
                       id="quick-add-input"
                       class="form-control"
                       name="title"
                       placeholder="What needs to be done?"
                       maxlength="255"
                       required>
                <button id="quick-add-button" class="btn btn-primary px-4" type="submit">
                    <i class="bi bi-plus-lg me-1"></i>Add
                    <span id="quick-add-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>
                </button>
            </div>

            <div id="quick-add-help" class="form-text mt-2">
                <i class="bi bi-lightbulb text-warning me-1"></i>
                Quick add creates a task with default settings. Use "Detailed" for more options.
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quickAddForm = document.getElementById('quick-add-form');

    if (quickAddForm) {
        // Handle successful quick add
        quickAddForm.addEventListener('htmx:afterRequest', function(event) {
            // Clear response div to prevent JSON from showing
            const responseDiv = document.getElementById('quick-add-response');
            if (responseDiv) {
                responseDiv.innerHTML = '';
            }

            if (event.detail.successful) {
                try {
                    const response = JSON.parse(event.detail.xhr.responseText);
                    if (response.success) {
                        // Clear input
                        document.getElementById('quick-add-input').value = '';

                        // Show success toast
                        TodoTracker.showToast(response.message || 'Task added!', 'success');

                        // Reload page to update stats and list (after 5.5 seconds to allow toast to display)
                        setTimeout(() => {
                            window.location.reload();
                        }, 5500);
                    } else {
                        // Show error as toast
                        TodoTracker.showToast(response.message || 'Error adding task', 'error');
                    }
                } catch (e) {
                    console.error('Error parsing quick add response:', e);
                    TodoTracker.showToast('Error adding task', 'error');
                }
            }
        });

        // Handle errors
        quickAddForm.addEventListener('htmx:responseError', function(event) {
            TodoTracker.showToast('Error adding task. Please try again.', 'error');
        });

        // Validate before submit
        quickAddForm.addEventListener('submit', function(e) {
            const input = document.getElementById('quick-add-input');
            const title = input.value.trim();

            if (!title) {
                e.preventDefault();
                input.classList.add('is-invalid');
                TodoTracker.showToast('Please enter a task title', 'error');
                return false;
            }

            input.classList.remove('is-invalid');
        });
    }
});
</script>
