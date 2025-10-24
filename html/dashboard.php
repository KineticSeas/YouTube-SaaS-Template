<?php
/**
 * Dashboard - Main application page
 * Complete functional dashboard with statistics, tasks, and quick actions
 * REQ-DASH-001 through REQ-DASH-403
 */

$pageTitle = 'Dashboard - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/task-functions.php';
require_once 'includes/header.php';

// Get current user ID
$userId = getCurrentUserId();

// Get task statistics (REQ-DASH-001 to 005)
$stats = getUserTaskStats($userId);
$tasksDueToday = getTasksDueToday($userId);
$overdueCount = getOverdueTasksCount($userId);
$completionRate = getCompletionRate($userId);

// Get recent tasks (last 10, ordered by updated_at) (REQ-DASH-101 to 103)
$recentTasks = getTasksByUserId($userId, ['limit' => 10, 'order_by' => 'updated_at', 'order_dir' => 'DESC']);

// Get upcoming tasks (due in next 7 days) (REQ-DASH-201 to 203)
$upcomingTasks = getUpcomingTasks($userId, 7);

// Get overdue tasks
$overdueTasks = getOverdueTasks($userId);

// Merge overdue and upcoming, with overdue at top
$deadlineTasks = array_merge($overdueTasks, $upcomingTasks);
// Limit to 10
$deadlineTasks = array_slice($deadlineTasks, 0, 10);
?>

<!-- Dashboard Content -->
<div id="dashboard-container" class="container-fluid">
    <!-- Page Header -->
    <div id="dashboard-header" class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h1 id="dashboard-title" class="h2 mb-1">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </h1>
                    <p id="dashboard-subtitle" class="text-muted mb-0">
                        Welcome back, <?php echo htmlspecialchars(getCurrentUserName()); ?>!
                        <?php if ($tasksDueToday > 0): ?>
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $tasksDueToday; ?> due today
                            </span>
                        <?php endif; ?>
                        <?php if ($overdueCount > 0): ?>
                            <span class="badge bg-danger ms-2">
                                <i class="bi bi-exclamation-triangle me-1"></i><?php echo $overdueCount; ?> overdue
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#add-task-modal">
                        <i class="bi bi-plus-circle me-2"></i>New Task
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row (REQ-DASH-001 to 005) -->
    <div id="stats-row" class="row g-3 mb-4" hx-get="/api/stats/refresh.php" hx-trigger="taskUpdated from:body" hx-swap="outerHTML">
        <!-- Total Tasks Card -->
        <div class="col-lg-3 col-md-6">
            <a href="/tasks.php" class="text-decoration-none">
                <div id="stat-total-tasks" class="card text-white bg-primary h-100 shadow-sm stat-card-clickable">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1 text-uppercase small">Total Tasks</h6>
                                <h2 id="stat-total-count" class="mb-0 display-5"><?php echo $stats['total']; ?></h2>
                                <p class="mb-0 small text-white-50 mt-1">
                                    <i class="bi bi-graph-up me-1"></i>All active tasks
                                </p>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-list-task" style="font-size: 3.5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pending Tasks Card -->
        <div class="col-lg-3 col-md-6">
            <a href="/tasks.php?status[]=pending" class="text-decoration-none">
                <div id="stat-pending-tasks" class="card text-dark bg-warning h-100 shadow-sm stat-card-clickable">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title opacity-75 mb-1 text-uppercase small">Pending</h6>
                                <h2 id="stat-pending-count" class="mb-0 display-5"><?php echo $stats['pending']; ?></h2>
                                <p class="mb-0 small opacity-75 mt-1">
                                    <i class="bi bi-hourglass me-1"></i>Not started
                                </p>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-hourglass-split" style="font-size: 3.5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- In Progress Tasks Card -->
        <div class="col-lg-3 col-md-6">
            <a href="/tasks.php?status[]=in_progress" class="text-decoration-none">
                <div id="stat-progress-tasks" class="card text-dark bg-info h-100 shadow-sm stat-card-clickable">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title opacity-75 mb-1 text-uppercase small">In Progress</h6>
                                <h2 id="stat-progress-count" class="mb-0 display-5"><?php echo $stats['in_progress']; ?></h2>
                                <p class="mb-0 small opacity-75 mt-1">
                                    <i class="bi bi-arrow-repeat me-1"></i>Currently working
                                </p>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-arrow-repeat" style="font-size: 3.5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Completed Tasks Card -->
        <div class="col-lg-3 col-md-6">
            <a href="/tasks.php?status[]=completed" class="text-decoration-none">
                <div id="stat-completed-tasks" class="card text-white bg-success h-100 shadow-sm stat-card-clickable">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1 text-uppercase small">Completed</h6>
                                <h2 id="stat-completed-count" class="mb-0 display-5"><?php echo $stats['completed']; ?></h2>
                                <p class="mb-0 small text-white-50 mt-1">
                                    <i class="bi bi-check-circle me-1"></i>Done!
                                </p>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-check-circle-fill" style="font-size: 3.5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Progress Bar (REQ-DASH-301, 302) -->
    <div id="progress-section" class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>Overall Progress
                        </h5>
                        <strong class="text-primary fs-4"><?php echo $completionRate; ?>%</strong>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success progress-bar-striped <?php echo $stats['in_progress'] > 0 ? 'progress-bar-animated' : ''; ?>"
                             role="progressbar"
                             style="width: <?php echo $completionRate; ?>%"
                             aria-valuenow="<?php echo $completionRate; ?>"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            <?php if ($completionRate > 10): ?>
                                <?php echo $completionRate; ?>% Complete
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <?php echo $stats['completed']; ?> of <?php echo $stats['total']; ?> tasks completed
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div id="main-content-row" class="row g-4">
        <!-- Left Column - Quick Add & Recent Tasks -->
        <div class="col-lg-8">
            <!-- Quick Add Task (REQ-DASH-401 to 403) -->
            <?php include 'components/quick-add.php'; ?>

            <!-- Recent Tasks (REQ-DASH-101 to 103) -->
            <div id="recent-tasks-card" class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 id="recent-tasks-title" class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Tasks
                        <span class="badge bg-secondary ms-2"><?php echo count($recentTasks); ?></span>
                    </h5>
                    <a id="view-all-tasks-link" href="/tasks.php?clear_filters=1" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-list-ul me-1"></i>View All
                    </a>
                </div>
                <div id="task-list" hx-get="/api/tasks/recent.php" hx-trigger="taskUpdated from:body" hx-swap="innerHTML">
                    <?php
                    $tasks = $recentTasks;
                    $showEmpty = true;
                    include 'components/task-list.php';
                    ?>
                </div>
                <?php if (count($recentTasks) > 0): ?>
                <div class="card-footer text-center">
                    <a href="/tasks.php?clear_filters=1" class="text-decoration-none">
                        View all tasks <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column - Upcoming Deadlines & Quick Actions -->
        <div class="col-lg-4">
            <!-- Upcoming Deadlines (REQ-DASH-201 to 203) -->
            <div id="upcoming-deadlines-card" class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5 id="upcoming-deadlines-title" class="mb-0">
                        <i class="bi bi-calendar-event me-2"></i>Upcoming Deadlines
                        <?php if (count($deadlineTasks) > 0): ?>
                            <span class="badge bg-primary ms-2"><?php echo count($deadlineTasks); ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div id="upcoming-deadlines-list" class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($deadlineTasks)): ?>
                        <div id="no-deadlines-message" class="list-group-item text-center text-muted py-4">
                            <i class="bi bi-calendar-check" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            <p class="mt-2 mb-0">No upcoming deadlines</p>
                            <p class="small mb-0">You're all caught up!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($deadlineTasks as $task): ?>
                            <?php
                            $isOverdue = !empty($task['due_date']) && strtotime($task['due_date']) < strtotime('today') && $task['status'] !== 'completed';
                            $isToday = !empty($task['due_date']) && date('Y-m-d', strtotime($task['due_date'])) === date('Y-m-d');
                            ?>
                            <div class="list-group-item list-group-item-action <?php echo $isOverdue ? 'list-group-item-danger' : ($isToday ? 'list-group-item-warning' : ''); ?>">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1 me-2">
                                        <h6 class="mb-1 small fw-bold text-truncate">
                                            <?php if ($isOverdue): ?>
                                                <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                                            <?php elseif ($isToday): ?>
                                                <i class="bi bi-clock-fill text-warning me-1"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </h6>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php
                                            if ($isOverdue) {
                                                $daysOverdue = floor((time() - strtotime($task['due_date'])) / 86400);
                                                echo '<span class="text-danger fw-bold">' . $daysOverdue . ' day' . ($daysOverdue > 1 ? 's' : '') . ' overdue</span>';
                                            } elseif ($isToday) {
                                                echo '<span class="text-warning fw-bold">Due today</span>';
                                            } else {
                                                $dueDate = new DateTime($task['due_date']);
                                                $now = new DateTime();
                                                $diff = $now->diff($dueDate);
                                                if ($diff->days == 1) {
                                                    echo 'Due tomorrow';
                                                } else {
                                                    echo 'Due in ' . $diff->days . ' days';
                                                }
                                            }
                                            ?>
                                        </small>
                                    </div>
                                    <span class="badge <?php
                                        switch ($task['priority']) {
                                            case 'high': echo 'bg-danger'; break;
                                            case 'medium': echo 'bg-warning text-dark'; break;
                                            default: echo 'bg-secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($task['priority']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions (REQ-DASH-401 to 403) -->
            <div id="quick-actions-card" class="card shadow-sm">
                <div class="card-header">
                    <h5 id="quick-actions-title" class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div id="quick-actions-buttons" class="d-grid gap-2">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#add-task-modal">
                            <i class="bi bi-plus-circle me-2"></i>Add New Task
                        </button>
                        <a href="/tasks.php?clear_filters=1" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul me-2"></i>View All Tasks
                        </a>
                        <a href="/kanban.php" class="btn btn-outline-primary">
                            <i class="bi bi-columns-gap me-2"></i>Kanban Board
                        </a>
                        <a href="/calendar.php" class="btn btn-outline-primary">
                            <i class="bi bi-calendar3 me-2"></i>Calendar View
                        </a>
                        <a href="/categories.php" class="btn btn-outline-secondary">
                            <i class="bi bi-tags me-2"></i>Manage Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Add Task Modal -->
<?php include 'components/add-task-modal.php'; ?>

<!-- HTMX Event Listener for Auto-refresh -->
<script>
// Trigger custom event when task is created/updated/deleted
document.body.addEventListener('htmx:afterRequest', function(event) {
    // Check if it was a task-related request
    if (event.detail.xhr.responseURL &&
        (event.detail.xhr.responseURL.includes('/api/tasks/') &&
         event.detail.successful)) {
        // Trigger custom event to refresh dashboard sections
        htmx.trigger('#stats-row', 'taskUpdated');
        htmx.trigger('#task-list', 'taskUpdated');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
