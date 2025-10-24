<?php
/**
 * Archive Cards Component - Grid view for archived tasks
 */
?>

<div class="row g-3" id="archive-cards-container">
    <?php foreach ($tasks as $task): ?>
    <div class="col-lg-4 col-md-6" id="archive-card-wrapper-<?php echo $task['id']; ?>">
        <div class="card h-100 shadow-sm archive-task-card">
            <div class="card-body">
                <!-- Status and Priority Badges -->
                <div class="d-flex justify-content-between mb-2">
                    <?php
                    $statusBadge = '';
                    switch ($task['status']) {
                        case 'pending':
                            $statusBadge = 'bg-warning text-dark';
                            break;
                        case 'in_progress':
                            $statusBadge = 'bg-info text-dark';
                            break;
                        case 'completed':
                            $statusBadge = 'bg-success';
                            break;
                    }

                    $priorityBadge = '';
                    switch ($task['priority']) {
                        case 'high':
                            $priorityBadge = 'bg-danger';
                            break;
                        case 'medium':
                            $priorityBadge = 'bg-warning text-dark';
                            break;
                        case 'low':
                            $priorityBadge = 'bg-secondary';
                            break;
                    }
                    ?>
                    <span class="badge <?php echo $priorityBadge; ?>">
                        <?php echo ucfirst($task['priority']); ?>
                    </span>
                    <span class="badge <?php echo $statusBadge; ?>">
                        <?php echo ucwords(str_replace('_', ' ', $task['status'])); ?>
                    </span>
                </div>

                <!-- Task Title -->
                <h5 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h5>

                <!-- Task Description -->
                <?php if (!empty($task['description'])): ?>
                <p class="card-text text-muted">
                    <?php echo htmlspecialchars(mb_substr($task['description'], 0, 150)) . (mb_strlen($task['description']) > 150 ? '...' : ''); ?>
                </p>
                <?php endif; ?>

                <!-- Categories -->
                <?php if (!empty($task['categories'])): ?>
                <div class="mb-2">
                    <?php foreach ($task['categories'] as $category): ?>
                    <span class="badge badge-sm" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Task Metadata -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted">
                        <?php if ($task['due_date']): ?>
                        <i class="bi bi-calendar-event me-1"></i>
                        <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                        <?php else: ?>
                        <span class="text-muted">No due date</span>
                        <?php endif; ?>
                    </small>
                </div>

                <!-- Archived Date -->
                <div class="border-top pt-2 mb-3">
                    <small class="text-muted">
                        <i class="bi bi-archive me-1"></i>
                        Archived on <?php echo date('M j, Y', strtotime($task['archived_at'])); ?>
                    </small>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm"
                            onclick="unarchiveTask(<?php echo $task['id']; ?>)">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                    </button>
                    <button class="btn btn-outline-danger btn-sm"
                            onclick="deleteArchivedTask(<?php echo $task['id']; ?>)">
                        <i class="bi bi-trash me-1"></i>Move to Trash
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
.archive-task-card {
    background-color: #f8f9fa;
    border-left: 3px solid #6c757d;
}
.archive-task-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.badge-sm {
    font-size: 0.7rem;
    padding: 0.2em 0.4em;
    margin-right: 0.25rem;
}
</style>
