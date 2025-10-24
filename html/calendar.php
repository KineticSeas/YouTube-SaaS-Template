<?php
/**
 * Calendar Page - Task scheduling and visualization
 * Three view modes: Month, Week, Day
 * REQ-CAL-001 through REQ-CAL-304
 */

$pageTitle = 'Calendar - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/calendar-functions.php';
require_once 'includes/header.php';

// Get current user ID
$userId = getCurrentUserId();

// Get current date parameters from URL or default to today
$view = isset($_GET['view']) ? $_GET['view'] : 'month';
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Validate view
if (!in_array($view, ['month', 'week', 'day'])) {
    $view = 'month';
}

// Validate month
if ($month < 1 || $month > 12) {
    $month = (int)date('n');
}

// Get display title based on view
if ($view === 'month') {
    $displayTitle = getMonthYearDisplay($month, $year);
} elseif ($view === 'week') {
    $weekStart = getWeekStart($date);
    $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
    $displayTitle = getDateRangeDisplay($weekStart, $weekEnd);
} else {
    $displayTitle = getFullDateDisplay($date);
}
?>

<!-- Calendar Page Container -->
<div id="calendar-page" class="container-fluid" x-data="calendarData()">
    <!-- Page Header -->
    <div id="calendar-header" class="row mb-4">
        <div class="col-12">
            <div id="calendar-header-content" class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h1 id="calendar-page-title" class="h2 mb-1">
                        <i class="bi bi-calendar3 me-2"></i>Calendar
                    </h1>
                    <p id="calendar-subtitle" class="text-muted mb-0">
                        Schedule and visualize your tasks
                    </p>
                </div>
                <div>
                    <button id="calendar-add-task-btn"
                            class="btn btn-primary btn-lg"
                            data-bs-toggle="modal"
                            data-bs-target="#add-task-modal"
                            @click="selectedDate = null">
                        <i class="bi bi-plus-circle me-2"></i>New Task
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Toolbar -->
    <div id="calendar-toolbar" class="card shadow-sm mb-4">
        <div id="calendar-toolbar-content" class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- Navigation Buttons -->
                <div id="calendar-nav" class="btn-group" role="group" aria-label="Calendar navigation">
                    <button id="calendar-prev-btn"
                            class="btn btn-outline-primary"
                            @click="navigatePrevious()"
                            aria-label="Previous">
                        <i class="bi bi-chevron-left"></i> Prev
                    </button>
                    <button id="calendar-today-btn"
                            class="btn btn-outline-primary"
                            @click="navigateToday()"
                            aria-label="Go to today">
                        Today
                    </button>
                    <button id="calendar-next-btn"
                            class="btn btn-outline-primary"
                            @click="navigateNext()"
                            aria-label="Next">
                        Next <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <!-- Current Period Display -->
                <div id="calendar-period-display" class="text-center flex-grow-1">
                    <h3 id="calendar-current-period" class="mb-0 h4">
                        <?php echo htmlspecialchars($displayTitle); ?>
                    </h3>
                </div>

                <!-- View Toggle -->
                <div id="calendar-view-toggle" class="btn-group" role="group" aria-label="Calendar view mode">
                    <button id="calendar-view-month"
                            type="button"
                            class="btn <?php echo $view === 'month' ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                            @click="changeView('month')"
                            aria-label="Month view">
                        <i class="bi bi-calendar-month me-1"></i>Month
                    </button>
                    <button id="calendar-view-week"
                            type="button"
                            class="btn <?php echo $view === 'week' ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                            @click="changeView('week')"
                            aria-label="Week view">
                        <i class="bi bi-calendar-week me-1"></i>Week
                    </button>
                    <button id="calendar-view-day"
                            type="button"
                            class="btn <?php echo $view === 'day' ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                            @click="changeView('day')"
                            aria-label="Day view">
                        <i class="bi bi-calendar-day me-1"></i>Day
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Content Container (HTMX swaps content here) -->
    <div id="calendar-content"
         class="card shadow-sm"
         hx-trigger="taskUpdated from:body"
         hx-get="/api/calendar/<?php echo $view; ?>.php?<?php echo http_build_query($_GET); ?>"
         hx-swap="innerHTML">

        <div id="calendar-view-container" class="card-body p-0">
            <?php
            // Load initial view based on current mode
            if ($view === 'month') {
                $calendarData = getMonthCalendar($year, $month, $userId);
                include 'components/calendar-month.php';
            } elseif ($view === 'week') {
                $weekStart = getWeekStart($date);
                $calendarData = getWeekCalendar($weekStart, $userId);
                include 'components/calendar-week.php';
            } else {
                $tasks = getDayTasks($date, $userId);
                include 'components/calendar-day.php';
            }
            ?>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="calendar-loading"
         class="htmx-indicator position-fixed top-50 start-50 translate-middle"
         style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- Include Add Task Modal -->
<?php include 'components/add-task-modal.php'; ?>

<?php require_once 'includes/footer.php'; ?>
