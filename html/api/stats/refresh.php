<?php
/**
 * Statistics Refresh API Endpoint
 * Returns HTML for statistics cards (for HTMX refresh)
 * REQ-DASH-001 to 005
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/task-functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Unauthorized</div>';
    exit;
}

// Get current user ID
$userId = getCurrentUserId();

// Get task statistics
$stats = getUserTaskStats($userId);
?>

<!-- Statistics Cards Row (REQ-DASH-001 to 005) -->
<div id="stats-row" class="row g-3 mb-4" hx-get="/api/stats/refresh.php" hx-trigger="taskUpdated from:body" hx-swap="outerHTML">
    <!-- Total Tasks Card -->
    <div class="col-lg-3 col-md-6">
        <div id="stat-total-tasks" class="card text-white bg-primary h-100 shadow-sm">
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
    </div>

    <!-- Pending Tasks Card -->
    <div class="col-lg-3 col-md-6">
        <div id="stat-pending-tasks" class="card text-dark bg-warning h-100 shadow-sm">
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
    </div>

    <!-- In Progress Tasks Card -->
    <div class="col-lg-3 col-md-6">
        <div id="stat-progress-tasks" class="card text-dark bg-info h-100 shadow-sm">
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
    </div>

    <!-- Completed Tasks Card -->
    <div class="col-lg-3 col-md-6">
        <div id="stat-completed-tasks" class="card text-white bg-success h-100 shadow-sm">
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
    </div>
</div>
