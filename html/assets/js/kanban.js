/**
 * Kanban Board - Drag and Drop Functionality
 * REQ-KANBAN-201 through REQ-KANBAN-205
 */

// Global state
let draggedTaskElement = null;
let draggedTaskId = null;
let originalStatus = null;
let isMoving = false;

/**
 * Initialize Kanban drag and drop
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeKanbanDragDrop();
});

/**
 * Setup drag and drop event listeners
 */
function initializeKanbanDragDrop() {
    // Get all task cards
    const taskCards = document.querySelectorAll('.kanban-task-card');

    taskCards.forEach(card => {
        // Drag start
        card.addEventListener('dragstart', handleDragStart);

        // Drag end
        card.addEventListener('dragend', handleDragEnd);
    });

    // Get all drop zones (column bodies)
    const dropZones = document.querySelectorAll('.kanban-column-body');

    dropZones.forEach(zone => {
        // Drag over
        zone.addEventListener('dragover', handleDragOver);

        // Drag enter
        zone.addEventListener('dragenter', handleDragEnter);

        // Drag leave
        zone.addEventListener('dragleave', handleDragLeave);

        // Drop
        zone.addEventListener('drop', handleDrop);
    });
}

/**
 * Handle drag start event
 */
function handleDragStart(e) {
    if (isMoving) {
        e.preventDefault();
        return;
    }

    draggedTaskElement = this;
    draggedTaskId = this.getAttribute('data-task-id');
    originalStatus = this.getAttribute('data-task-status');

    // Set drag data
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
    e.dataTransfer.setData('taskId', draggedTaskId);
    e.dataTransfer.setData('originalStatus', originalStatus);

    // Add dragging class for visual feedback
    setTimeout(() => {
        this.classList.add('dragging');
    }, 0);
}

/**
 * Handle drag over event (must prevent default to allow drop)
 */
function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }

    e.dataTransfer.dropEffect = 'move';
    return false;
}

/**
 * Handle drag enter event
 */
function handleDragEnter(e) {
    // Add highlight to drop zone
    this.classList.add('drag-over');
}

/**
 * Handle drag leave event
 */
function handleDragLeave(e) {
    // Remove highlight when leaving drop zone
    // Check if we're actually leaving the drop zone (not just entering a child)
    if (e.target === this) {
        this.classList.remove('drag-over');
    }
}

/**
 * Handle drop event
 */
async function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }

    e.preventDefault();

    // Remove drag-over class
    this.classList.remove('drag-over');

    // Get the new status from the drop zone
    const newStatus = this.getAttribute('data-status');

    // Get task ID from drag data
    const taskId = e.dataTransfer.getData('taskId');
    const oldStatus = e.dataTransfer.getData('originalStatus');

    // Don't do anything if dropped in same column
    if (newStatus === oldStatus) {
        return false;
    }

    // Move the task
    await moveTask(taskId, newStatus, oldStatus);

    return false;
}

/**
 * Handle drag end event
 */
function handleDragEnd(e) {
    // Remove dragging class
    this.classList.remove('dragging');

    // Remove drag-over from all drop zones
    document.querySelectorAll('.kanban-column-body').forEach(zone => {
        zone.classList.remove('drag-over');
    });

    // Reset global state
    draggedTaskElement = null;
    draggedTaskId = null;
    originalStatus = null;
}

/**
 * Move task to new status via API
 */
async function moveTask(taskId, newStatus, oldStatus) {
    if (isMoving) {
        return;
    }

    isMoving = true;

    // Get CSRF token
    const csrfToken = document.getElementById('csrf-token')?.value || '';

    try {
        // Show loading state on card
        const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
        if (taskCard) {
            taskCard.style.opacity = '0.5';
            taskCard.style.pointerEvents = 'none';
        }

        // Send AJAX request
        const response = await fetch('/api/tasks/move.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                task_id: taskId,
                new_status: newStatus,
                csrf_token: csrfToken
            })
        });

        const result = await response.json();

        if (result.success) {
            // Success! Reload the page to show updated columns
            showToast('success', result.message || 'Task moved successfully!');

            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            // Error - show toast and reload to restore original state
            showToast('error', result.message || 'Failed to move task');

            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    } catch (error) {
        console.error('Error moving task:', error);
        showToast('error', 'An error occurred while moving the task');

        // Reload to restore original state
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } finally {
        isMoving = false;
    }
}

/**
 * Move task from mobile dropdown (no drag-drop)
 */
async function moveTaskMobile(taskId, newStatus) {
    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
    const oldStatus = taskCard?.getAttribute('data-task-status');

    if (newStatus === oldStatus) {
        return;
    }

    await moveTask(taskId, newStatus, oldStatus);
}

/**
 * Show toast notification
 */
function showToast(type, message) {
    // Create toast element
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    // Add to toast container
    const container = document.getElementById('toast-container');
    if (container) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = toastHtml;
        const toast = tempDiv.firstElementChild;
        container.appendChild(toast);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

/**
 * Disable drag on touch devices (unreliable)
 * Enable mobile dropdown instead
 */
if ('ontouchstart' in window) {
    document.addEventListener('DOMContentLoaded', function() {
        const taskCards = document.querySelectorAll('.kanban-task-card');
        taskCards.forEach(card => {
            card.setAttribute('draggable', 'false');
            card.style.cursor = 'pointer';
        });
    });
}
