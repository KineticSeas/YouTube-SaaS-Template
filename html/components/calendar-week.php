<?php
/**
 * Calendar Week View Component
 * Displays 7 day columns with tasks listed vertically
 * REQ-CAL-201 through REQ-CAL-204
 *
 * Expected variables:
 * - $calendarData - Array with 'days' and 'tasks'
 * - $userId - Current user ID
 */

if (!isset($calendarData)) {
    echo '<div class="alert alert-danger m-3">Calendar data not available</div>';
    return;
}

$days = $calendarData['days'];
$tasksByDate = $calendarData['tasks'];
?>

<!-- Week View Container -->
<div id="calendar-week-view" class="p-3">
    <div id="calendar-week-grid" class="row g-2">
        <?php foreach ($days as $index => $day):
            $date = $day['date'];
            $dayName = $day['dayName'];
            $dayNum = $day['day'];
            $isToday = $day['isToday'];

            // Get tasks for this day
            $dayTasks = isset($tasksByDate[$date]) ? $tasksByDate[$date] : [];

            $colId = "calendar-week-col-" . $index;
            $headerClasses = ['text-center', 'p-2', 'rounded-top', 'fw-bold'];
            if ($isToday) {
                $headerClasses[] = 'bg-primary text-white';
            }
        ?>
        <div id="<?php echo $colId; ?>" class="col-lg col-md-3 col-sm-6">
            <div id="<?php echo $colId; ?>-card" class="card h-100 calendar-week-day-card">
                <!-- Day Header -->
                <div id="<?php echo $colId; ?>-header"
                     class="<?php echo implode(' ', $headerClasses); ?>"
                     data-date="<?php echo $date; ?>">
                    <div class="calendar-week-day-name"><?php echo $dayName; ?></div>
                    <div class="calendar-week-day-number h5 mb-0"><?php echo $dayNum; ?></div>
                </div>

                <!-- Tasks List -->
                <div id="<?php echo $colId; ?>-tasks"
                     class="card-body p-2 calendar-week-tasks"
                     style="min-height: 200px; max-height: 500px; overflow-y: auto;"
                     @click.self="openAddTaskModal('<?php echo $date; ?>')">

                    <?php if (empty($dayTasks)): ?>
                    <!-- Empty State -->
                    <div id="<?php echo $colId; ?>-empty"
                         class="text-center text-muted py-4"
                         style="cursor: pointer;">
                        <i class="bi bi-plus-circle" style="font-size: 2rem;"></i>
                        <p class="small mb-0 mt-2">No tasks</p>
                    </div>
                    <?php else: ?>
                    <!-- Task Cards -->
                    <?php foreach ($dayTasks as $taskIndex => $task):
                        $taskId = $task['id'];
                        $taskTitle = htmlspecialchars($task['title']);
                        $taskDesc = htmlspecialchars($task['description']);
                        $taskPriority = $task['priority'];
                        $taskStatus = $task['status'];
                        $isOverdue = isTaskOverdue($task);
                        $isCompleted = $taskStatus === 'completed';

                        // Priority badge color
                        $priorityBadge = 'bg-secondary';
                        if ($taskPriority === 'high') {
                            $priorityBadge = 'bg-danger';
                        } elseif ($taskPriority === 'medium') {
                            $priorityBadge = 'bg-warning text-dark';
                        }

                        // Status badge color
                        $statusBadge = 'bg-warning text-dark';
                        if ($taskStatus === 'completed') {
                            $statusBadge = 'bg-success';
                        } elseif ($taskStatus === 'in_progress') {
                            $statusBadge = 'bg-info';
                        }

                        $taskCardId = $colId . '-task-' . $taskId;
                        $taskCardClasses = ['card', 'mb-2', 'calendar-week-task-card'];
                        if ($isCompleted) {
                            $taskCardClasses[] = 'opacity-75';
                        }
                        if ($isOverdue) {
                            $taskCardClasses[] = 'border-start border-danger border-3';
                        }
                    ?>
                    <div id="<?php echo $taskCardId; ?>"
                         class="<?php echo implode(' ', $taskCardClasses); ?>"
                         style="cursor: pointer;"
                         @click="openTaskModal(<?php echo $taskId; ?>)">
                        <div class="card-body p-2">
                            <!-- Priority and Status Badges -->
                            <div class="d-flex gap-1 mb-1 flex-wrap">
                                <span class="badge <?php echo $priorityBadge; ?> badge-sm">
                                    <?php echo ucfirst($taskPriority); ?>
                                </span>
                                <span class="badge <?php echo $statusBadge; ?> badge-sm">
                                    <?php echo ucfirst(str_replace('_', ' ', $taskStatus)); ?>
                                </span>
                            </div>

                            <!-- Task Title -->
                            <h6 class="card-title mb-1 small fw-bold">
                                <?php echo $taskTitle; ?>
                            </h6>

                            <!-- Task Description (truncated) -->
                            <?php if (!empty($taskDesc)): ?>
                            <p class="card-text small text-muted mb-0"
                               style="font-size: 0.75rem;">
                                <?php
                                $truncatedDesc = strlen($taskDesc) > 60 ? substr($taskDesc, 0, 60) . '...' : $taskDesc;
                                echo $truncatedDesc;
                                ?>
                            </p>
                            <?php endif; ?>

                            <!-- Categories -->
                            <?php if (!empty($task['category_names'])): ?>
                            <div class="mt-1">
                                <?php
                                $categories = explode(', ', $task['category_names']);
                                foreach (array_slice($categories, 0, 2) as $category):
                                ?>
                                <span class="badge border badge-sm">
                                    <?php echo htmlspecialchars($category); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Task Button -->
                <div id="<?php echo $colId; ?>-footer" class="card-footer p-2 bg-white border-top">
                    <button class="btn btn-sm btn-outline-primary w-100"
                            @click="openAddTaskModal('<?php echo $date; ?>')"
                            aria-label="Add task for <?php echo $dayName; ?>">
                        <i class="bi bi-plus-circle me-1"></i>Add Task
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Responsive Note -->
<style>
@media (max-width: 991px) {
    #calendar-week-grid {
        overflow-x: auto;
        flex-wrap: nowrap;
    }
    #calendar-week-view .col-lg {
        min-width: 200px;
    }
}
</style>
