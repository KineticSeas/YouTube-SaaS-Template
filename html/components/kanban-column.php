<?php
/**
 * Kanban Column Component
 * Displays a single Kanban column (Pending, In Progress, or Completed)
 *
 * Required variables:
 * - $status: string (pending, in_progress, completed)
 * - $tasks: array of task objects
 * - $columnTitle: string (display name)
 * - $columnColor: string (warning, info, success)
 * - $userId: int (current user ID)
 */

$taskCount = count($tasks);
$columnId = 'kanban-column-' . $status;
$columnHeaderId = 'kanban-column-header-' . $status;
$columnBodyId = 'kanban-column-body-' . $status;
$emptyStateId = 'kanban-empty-state-' . $status;
?>

<div class="card kanban-column h-100 shadow-sm" id="<?php echo $columnId; ?>">
    <!-- Column Header -->
    <div class="card-header bg-<?php echo $columnColor; ?> <?php echo $columnColor === 'info' || $columnColor === 'success' ? 'text-white' : 'text-dark'; ?>"
         id="<?php echo $columnHeaderId; ?>">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <?php echo $columnTitle; ?>
                <span class="badge bg-dark ms-2"><?php echo $taskCount; ?></span>
            </h5>
            <button class="btn btn-sm <?php echo $columnColor === 'info' || $columnColor === 'success' ? 'btn-light' : 'btn-dark'; ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#add-task-modal"
                    title="Add task to <?php echo $columnTitle; ?>"
                    onclick="presetTaskStatus('<?php echo $status; ?>')">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>

    <!-- Column Body (Drop Zone) -->
    <div class="card-body kanban-column-body p-2"
         id="<?php echo $columnBodyId; ?>"
         data-status="<?php echo $status; ?>"
         data-droppable="true">

        <?php if (empty($tasks)): ?>
            <!-- Empty State -->
            <div class="kanban-empty-state text-center py-5" id="<?php echo $emptyStateId; ?>">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-2">No <?php echo strtolower($columnTitle); ?> tasks</p>
                <p class="text-muted small">Drop tasks here or</p>
                <button class="btn btn-sm btn-outline-<?php echo $columnColor; ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#add-task-modal"
                        onclick="presetTaskStatus('<?php echo $status; ?>')">
                    <i class="bi bi-plus-circle me-1"></i>Add Task
                </button>
            </div>
        <?php else: ?>
            <!-- Task Cards -->
            <?php foreach ($tasks as $task): ?>
                <?php include __DIR__ . '/kanban-card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Function to preset task status in add modal
function presetTaskStatus(status) {
    // Set the status dropdown in the add-task-modal if it exists
    const statusSelect = document.querySelector('#add-task-modal select[name="status"]');
    if (statusSelect) {
        statusSelect.value = status;
    }
}
</script>
