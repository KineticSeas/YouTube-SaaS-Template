<?php
/**
 * Trash Table Component - Display deleted tasks with countdown
 */
?>

<div class="card shadow-sm border-danger" id="trash-table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="trash-table">
            <thead class="table-danger">
                <tr>
                    <th style="width: 5%;">
                        <input type="checkbox" class="form-check-input" id="table-select-all" onchange="toggleSelectAll()">
                    </th>
                    <th style="width: 35%;">Task</th>
                    <th style="width: 10%;">Priority</th>
                    <th style="width: 15%;">Deleted</th>
                    <th style="width: 20%;">Permanently Deleted In</th>
                    <th style="width: 15%;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <?php
                $daysInTrash = $task['days_in_trash'];
                $daysLeft = $task['days_until_permanent_delete'];
                $urgencyClass = '';
                if ($daysLeft <= 7) {
                    $urgencyClass = 'table-danger';
                } elseif ($daysLeft <= 14) {
                    $urgencyClass = 'table-warning';
                }
                ?>
                <tr id="trash-task-row-<?php echo $task['id']; ?>" class="trash-task-row <?php echo $urgencyClass; ?>">
                    <td>
                        <input type="checkbox"
                               class="form-check-input trash-task-checkbox"
                               value="<?php echo $task['id']; ?>"
                               onchange="toggleTaskSelection(<?php echo $task['id']; ?>)">
                    </td>
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
                        <span class="small text-muted">
                            <i class="bi bi-trash me-1"></i>
                            <?php echo $daysInTrash; ?> day<?php echo $daysInTrash !== 1 ? 's' : ''; ?> ago
                        </span>
                    </td>
                    <td>
                        <?php if ($daysLeft > 0): ?>
                        <span class="badge <?php echo $daysLeft <= 7 ? 'bg-danger' : ($daysLeft <= 14 ? 'bg-warning text-dark' : 'bg-secondary'); ?> fw-bold">
                            <i class="bi bi-clock me-1"></i>
                            <?php echo $daysLeft; ?> day<?php echo $daysLeft !== 1 ? 's' : ''; ?>
                        </span>
                        <?php else: ?>
                        <span class="badge bg-danger fw-bold">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Overdue for deletion
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-success"
                                    onclick="restoreTask(<?php echo $task['id']; ?>)"
                                    title="Restore from trash">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button class="btn btn-outline-danger"
                                    onclick="permanentDeleteTask(<?php echo $task['id']; ?>, '<?php echo addslashes(htmlspecialchars($task['title'])); ?>')"
                                    title="Delete permanently">
                                <i class="bi bi-trash3"></i> Delete
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
.trash-task-row {
    background-color: #fff5f5;
}
.trash-task-row:hover {
    background-color: #ffe5e5;
}
.badge-sm {
    font-size: 0.7rem;
    padding: 0.2em 0.4em;
    margin-right: 0.25rem;
}
</style>
