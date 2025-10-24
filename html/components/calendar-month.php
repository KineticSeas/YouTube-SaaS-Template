<?php
/**
 * Calendar Month View Component
 * Displays month grid with 7 columns (Sun-Sat) and task badges
 * REQ-CAL-101 through REQ-CAL-105
 *
 * Expected variables:
 * - $calendarData - Array with 'grid' and 'tasks'
 * - $userId - Current user ID (for permissions)
 */

if (!isset($calendarData)) {
    echo '<div class="alert alert-danger m-3">Calendar data not available</div>';
    return;
}

$grid = $calendarData['grid'];
$tasksByDate = $calendarData['tasks'];
?>

<!-- Month View Table -->
<div id="calendar-month-view" class="table-responsive">
    <table id="calendar-month-table" class="table table-bordered mb-0 calendar-month-grid">
        <thead id="calendar-month-header">
            <tr id="calendar-weekday-row">
                <th id="calendar-header-sun" class="text-center p-2">Sun</th>
                <th id="calendar-header-mon" class="text-center p-2">Mon</th>
                <th id="calendar-header-tue" class="text-center p-2">Tue</th>
                <th id="calendar-header-wed" class="text-center p-2">Wed</th>
                <th id="calendar-header-thu" class="text-center p-2">Thu</th>
                <th id="calendar-header-fri" class="text-center p-2">Fri</th>
                <th id="calendar-header-sat" class="text-center p-2">Sat</th>
            </tr>
        </thead>
        <tbody id="calendar-month-body">
            <?php
            // Process grid in rows of 7 (weeks)
            for ($week = 0; $week < 6; $week++):
                $rowId = "calendar-week-row-" . $week;
            ?>
            <tr id="<?php echo $rowId; ?>" class="calendar-week-row">
                <?php
                for ($day = 0; $day < 7; $day++):
                    $cellIndex = ($week * 7) + $day;
                    $cell = $grid[$cellIndex];
                    $cellDate = $cell['date'];
                    $cellDay = $cell['day'];
                    $isCurrentMonth = $cell['isCurrentMonth'];
                    $isToday = $cell['isToday'];

                    // Get tasks for this date
                    $cellTasks = isset($tasksByDate[$cellDate]) ? $tasksByDate[$cellDate] : [];
                    $taskCount = count($cellTasks);
                    $visibleTasks = array_slice($cellTasks, 0, 3); // Show max 3 tasks
                    $remainingCount = $taskCount - 3;

                    // Cell CSS classes
                    $cellClasses = ['calendar-day-cell', 'align-top', 'p-2'];
                    if (!$isCurrentMonth) {
                        $cellClasses[] = 'calendar-other-month';
                    }
                    if ($isToday) {
                        $cellClasses[] = 'calendar-today';
                    }

                    $cellId = "calendar-cell-" . str_replace('-', '', $cellDate);
                ?>
                <td id="<?php echo $cellId; ?>"
                    class="<?php echo implode(' ', $cellClasses); ?>"
                    data-date="<?php echo $cellDate; ?>"
                    @click="openAddTaskModal('<?php echo $cellDate; ?>')">

                    <!-- Date Number -->
                    <div id="<?php echo $cellId; ?>-date"
                         class="calendar-day-number fw-bold mb-1 <?php echo !$isCurrentMonth ? 'text-muted' : ''; ?>">
                        <?php echo $cellDay; ?>
                    </div>

                    <!-- Task Badges -->
                    <div id="<?php echo $cellId; ?>-tasks" class="calendar-day-tasks">
                        <?php foreach ($visibleTasks as $index => $task):
                            $taskId = $task['id'];
                            $taskTitle = htmlspecialchars($task['title']);
                            $taskPriority = $task['priority'];
                            $taskStatus = $task['status'];
                            $isOverdue = isTaskOverdue($task);
                            $isCompleted = $taskStatus === 'completed';

                            // Badge color based on priority
                            $badgeColor = 'bg-secondary';
                            if ($taskPriority === 'high') {
                                $badgeColor = 'bg-danger';
                            } elseif ($taskPriority === 'medium') {
                                $badgeColor = 'bg-warning text-dark';
                            }

                            // Additional classes
                            $badgeClasses = [$badgeColor, 'badge', 'w-100', 'text-start', 'text-truncate', 'mb-1', 'calendar-task-badge'];
                            if ($isCompleted) {
                                $badgeClasses[] = 'opacity-50';
                            }
                            if ($isOverdue) {
                                $badgeClasses[] = 'border-start border-danger border-3';
                            }

                            $badgeId = $cellId . '-task-' . $taskId;
                        ?>
                        <div id="<?php echo $badgeId; ?>"
                             class="<?php echo implode(' ', $badgeClasses); ?>"
                             data-task-id="<?php echo $taskId; ?>"
                             @click.stop="openTaskModal(<?php echo $taskId; ?>)"
                             style="cursor: pointer; font-size: 0.75rem;"
                             title="<?php echo $taskTitle; ?>">
                            <?php
                            // Truncate title to 20 characters
                            $displayTitle = strlen($taskTitle) > 20 ? substr($taskTitle, 0, 20) . '...' : $taskTitle;
                            echo $displayTitle;
                            ?>
                        </div>
                        <?php endforeach; ?>

                        <?php if ($remainingCount > 0): ?>
                        <div id="<?php echo $cellId; ?>-more"
                             class="badge border w-100 text-start mb-1"
                             style="cursor: pointer; font-size: 0.7rem;"
                             @click.stop="viewDayTasks('<?php echo $cellDate; ?>')"
                             title="View all tasks for this day">
                            +<?php echo $remainingCount; ?> more
                        </div>
                        <?php endif; ?>
                    </div>
                </td>
                <?php endfor; ?>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>

<!-- Empty State (if no tasks in entire month) -->
<?php
$totalTasks = array_sum(array_map('count', $tasksByDate));
if ($totalTasks === 0):
?>
<div id="calendar-month-empty" class="text-center py-5">
    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
    <p class="text-muted mt-3 mb-0">No tasks scheduled this month</p>
</div>
<?php endif; ?>
