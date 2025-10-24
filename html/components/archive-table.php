<?php
/**
 * Archive Table Component - List view for archived tasks
 */
?>

<div class="card shadow-sm" id="archive-table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="archive-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 40%;">Task</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Priority</th>
                    <th style="width: 15%;">Due Date</th>
                    <th style="width: 15%;">Archived</th>
                    <th style="width: 10%;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <tr id="archive-task-row-<?php echo $task['id']; ?>" class="archive-task-row">
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($task['title']); ?></div>
                        <?php if (!empty($task['description'])): ?>
                        <div class="small text-muted text-truncate" style="max-width: 400px;">
                            <?php echo htmlspecialchars(mb_substr($task['description'], 0, 100)); ?>
                        </div>
                        <?php endif; ?>
                        <!-- Categories -->
                        <?php if (!empty($task['categories'])): ?>
                        <div class="mt-1">
                            <?php foreach ($task['categories'] as $category): ?>
                            <span class="badge badge-sm" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
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
                        ?>
                        <span class="badge <?php echo $statusBadge; ?>">
                            <?php echo ucwords(str_replace('_', ' ', $task['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php
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
                    </td>
                    <td>
                        <?php if ($task['due_date']): ?>
                        <span class="small">
                            <i class="bi bi-calendar-event me-1"></i>
                            <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted small">No due date</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="small text-muted">
                            <i class="bi bi-archive me-1"></i>
                            <?php echo date('M j, Y', strtotime($task['archived_at'])); ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary"
                                    onclick="unarchiveTask(<?php echo $task['id']; ?>)"
                                    title="Restore from archive">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button class="btn btn-outline-danger"
                                    onclick="deleteArchivedTask(<?php echo $task['id']; ?>)"
                                    title="Move to trash">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.archive-task-row {
    background-color: #f8f9fa;
}
.archive-task-row:hover {
    background-color: #e9ecef;
}
.badge-sm {
    font-size: 0.7rem;
    padding: 0.2em 0.4em;
    margin-right: 0.25rem;
}
</style>
