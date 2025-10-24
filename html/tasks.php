<?php
/**
 * All Tasks Page - Comprehensive task management
 * REQ-LIST-001 through REQ-LIST-504
 */

$pageTitle = 'All Tasks - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/task-functions.php';

// Clear filters if requested - MUST be before header.php to allow redirect
if (isset($_GET['clear_filters'])) {
    unset($_SESSION['task_filters']);
    header('Location: tasks.php');
    exit;
}

require_once 'includes/header.php';

// Get current user ID
$userId = getCurrentUserId();

// Get view mode from session or default to 'list'
if (isset($_GET['view'])) {
    $_SESSION['task_view'] = $_GET['view'];
}
$viewMode = $_SESSION['task_view'] ?? 'list';

// Get filters from GET parameters or session
$filters = [];

// Search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
    $_SESSION['task_filters']['search'] = $_GET['search'];
} elseif (isset($_SESSION['task_filters']['search'])) {
    $filters['search'] = $_SESSION['task_filters']['search'];
}

// Status filter (multiple)
if (isset($_GET['status']) && is_array($_GET['status'])) {
    $filters['status'] = $_GET['status'];
    $_SESSION['task_filters']['status'] = $_GET['status'];
} elseif (isset($_SESSION['task_filters']['status'])) {
    $filters['status'] = $_SESSION['task_filters']['status'];
}

// Priority filter (multiple)
if (isset($_GET['priority']) && is_array($_GET['priority'])) {
    $filters['priority'] = $_GET['priority'];
    $_SESSION['task_filters']['priority'] = $_GET['priority'];
} elseif (isset($_SESSION['task_filters']['priority'])) {
    $filters['priority'] = $_SESSION['task_filters']['priority'];
}

// Category filter
if (isset($_GET['categories']) && is_array($_GET['categories'])) {
    $filters['categories'] = $_GET['categories'];
    $_SESSION['task_filters']['categories'] = $_GET['categories'];
} elseif (isset($_SESSION['task_filters']['categories'])) {
    $filters['categories'] = $_SESSION['task_filters']['categories'];
}

// Date range filter
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
    $_SESSION['task_filters']['date_from'] = $_GET['date_from'];
} elseif (isset($_SESSION['task_filters']['date_from'])) {
    $filters['date_from'] = $_SESSION['task_filters']['date_from'];
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
    $_SESSION['task_filters']['date_to'] = $_GET['date_to'];
} elseif (isset($_SESSION['task_filters']['date_to'])) {
    $filters['date_to'] = $_SESSION['task_filters']['date_to'];
}

// Sorting
if (isset($_GET['sort_by'])) {
    $filters['order_by'] = $_GET['sort_by'];
    $_SESSION['task_filters']['order_by'] = $_GET['sort_by'];
} elseif (isset($_SESSION['task_filters']['order_by'])) {
    $filters['order_by'] = $_SESSION['task_filters']['order_by'];
} else {
    $filters['order_by'] = 'created_at';
}

if (isset($_GET['sort_dir'])) {
    $filters['order_dir'] = $_GET['sort_dir'];
    $_SESSION['task_filters']['order_dir'] = $_GET['sort_dir'];
} elseif (isset($_SESSION['task_filters']['order_dir'])) {
    $filters['order_dir'] = $_SESSION['task_filters']['order_dir'];
} else {
    $filters['order_dir'] = 'DESC';
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = isset($_GET['page_size']) ? intval($_GET['page_size']) : 20;
if (!in_array($pageSize, [10, 20, 50, 100])) {
    $pageSize = 20;
}
$_SESSION['task_page_size'] = $pageSize;

$filters['limit'] = $pageSize;
$filters['offset'] = ($page - 1) * $pageSize;

// Get total count for pagination (without limit/offset)
$countFilters = $filters;
unset($countFilters['limit']);
unset($countFilters['offset']);
$allTasks = getTasksByUserId($userId, $countFilters);
$totalTasks = count($allTasks);
$totalPages = ceil($totalTasks / $pageSize);

// Get tasks for current page
$tasks = getTasksByUserId($userId, $filters);

// Count active filters
$activeFilterCount = 0;
if (!empty($filters['search'])) $activeFilterCount++;
if (!empty($filters['status'])) $activeFilterCount++;
if (!empty($filters['priority'])) $activeFilterCount++;
if (!empty($filters['categories'])) $activeFilterCount++;
if (!empty($filters['date_from']) || !empty($filters['date_to'])) $activeFilterCount++;

// Get user categories for filter
$userCategories = getCategoriesByUserId($userId, true);

// Determine dynamic page title based on active filters
$pageDisplayTitle = 'All Tasks';
$pageDisplaySubtitle = 'Manage and organize all your tasks';

// Only apply custom titles if there are active filters
if ($activeFilterCount > 0) {
    // Check if there's a single status filter (and no other filters)
    if (!empty($filters['status']) && count($filters['status']) === 1 && $activeFilterCount === 1) {
        $status = $filters['status'][0];
        switch ($status) {
            case 'pending':
                $pageDisplayTitle = 'Pending Tasks';
                $pageDisplaySubtitle = 'Tasks that haven\'t been started yet';
                break;
            case 'in_progress':
                $pageDisplayTitle = 'In Progress Tasks';
                $pageDisplaySubtitle = 'Tasks you\'re currently working on';
                break;
            case 'completed':
                $pageDisplayTitle = 'Completed Tasks';
                $pageDisplaySubtitle = 'Your finished tasks';
                break;
        }
    }
    // Check for search filter
    elseif (!empty($filters['search'])) {
        $pageDisplayTitle = 'Search Results';
        $pageDisplaySubtitle = 'Tasks matching "' . htmlspecialchars($filters['search']) . '"';
    }
    // Check for multiple status filters
    elseif (!empty($filters['status']) && count($filters['status']) > 1) {
        $pageDisplayTitle = 'Filtered Tasks';
        $pageDisplaySubtitle = 'Tasks matching your selected filters';
    }
    // Check for priority filter
    elseif (!empty($filters['priority'])) {
        $pageDisplayTitle = 'Filtered Tasks';
        $pageDisplaySubtitle = 'Tasks matching your selected filters';
    }
    // Check for category filter
    elseif (!empty($filters['categories'])) {
        $pageDisplayTitle = 'Filtered Tasks';
        $pageDisplaySubtitle = 'Tasks in selected categories';
    }
    // Check for date range filter
    elseif (!empty($filters['date_from']) || !empty($filters['date_to'])) {
        $pageDisplayTitle = 'Filtered Tasks';
        $pageDisplaySubtitle = 'Tasks in selected date range';
    }
}
?>

<!-- All Tasks Page -->
<div id="all-tasks-container" class="container-fluid">
    <!-- Page Header -->
    <div id="tasks-page-header" class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 id="tasks-page-title" class="h2 mb-1">
                        <i class="bi bi-list-task me-2"></i><?php echo $pageDisplayTitle; ?>
                        <span class="badge bg-secondary ms-2"><?php echo $totalTasks; ?></span>
                    </h1>
                    <p id="tasks-page-subtitle" class="text-muted mb-0">
                        <?php echo $pageDisplaySubtitle; ?>
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

    <!-- Toolbar -->
    <div id="tasks-toolbar" class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- View Toggle -->
                <div class="col-lg-2 col-md-3 col-6">
                    <div id="view-toggle-buttons" class="btn-group w-100" role="group">
                        <?php
                        // Build query string for view toggle buttons
                        $viewToggleParams = [];
                        if (!empty($filters['search'])) $viewToggleParams[] = 'search=' . urlencode($filters['search']);
                        if (!empty($filters['order_by'])) $viewToggleParams[] = 'sort_by=' . urlencode($filters['order_by']);
                        if (!empty($filters['order_dir'])) $viewToggleParams[] = 'sort_dir=' . urlencode($filters['order_dir']);
                        $viewToggleQuery = !empty($viewToggleParams) ? '&' . implode('&', $viewToggleParams) : '';
                        ?>
                        <a href="?view=list<?php echo $viewToggleQuery; ?>"
                           class="btn btn-outline-secondary <?php echo $viewMode === 'list' ? 'active' : ''; ?>"
                           id="view-list-btn">
                            <i class="bi bi-list-ul"></i> List
                        </a>
                        <a href="?view=grid<?php echo $viewToggleQuery; ?>"
                           class="btn btn-outline-secondary <?php echo $viewMode === 'grid' ? 'active' : ''; ?>"
                           id="view-grid-btn">
                            <i class="bi bi-grid-3x3-gap"></i> Grid
                        </a>
                    </div>
                </div>

                <!-- Search -->
                <div class="col-lg-4 col-md-5 col-6">
                    <div id="search-input-group" class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control"
                               id="task-search-input"
                               placeholder="Search tasks..."
                               value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                               hx-get="/api/tasks/search.php"
                               hx-trigger="keyup changed delay:300ms"
                               hx-target="#task-display-container"
                               hx-include="[name='view'],[name='sort_by'],[name='sort_dir'],[name='page_size']"
                               hx-indicator="#search-spinner">
                        <input type="hidden" name="view" value="<?php echo $viewMode; ?>">
                        <input type="hidden" name="sort_by" value="<?php echo $filters['order_by']; ?>">
                        <input type="hidden" name="sort_dir" value="<?php echo $filters['order_dir']; ?>">
                        <input type="hidden" name="page_size" value="<?php echo $pageSize; ?>">
                        <?php if (!empty($filters['search'])): ?>
                        <button class="btn btn-outline-secondary"
                                type="button"
                                id="clear-search-btn"
                                onclick="window.location.href='tasks.php'">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <?php endif; ?>
                        <span id="search-spinner" class="htmx-indicator spinner-border spinner-border-sm ms-2"></span>
                    </div>
                </div>

                <!-- Filter Button -->
                <div class="col-lg-2 col-md-2 col-6">
                    <button class="btn btn-outline-primary w-100"
                            type="button"
                            id="filter-toggle-btn"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#filter-panel">
                        <i class="bi bi-funnel me-1"></i> Filters
                        <?php if ($activeFilterCount > 0): ?>
                            <span class="badge bg-danger ms-1"><?php echo $activeFilterCount; ?></span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- Sort Dropdown -->
                <div class="col-lg-3 col-md-2 col-6">
                    <select class="form-select"
                            id="sort-select"
                            onchange="updateSort(this.value)">
                        <option value="created_at_DESC" <?php echo $filters['order_by'] === 'created_at' && $filters['order_dir'] === 'DESC' ? 'selected' : ''; ?>>
                            Newest First
                        </option>
                        <option value="created_at_ASC" <?php echo $filters['order_by'] === 'created_at' && $filters['order_dir'] === 'ASC' ? 'selected' : ''; ?>>
                            Oldest First
                        </option>
                        <option value="due_date_ASC" <?php echo $filters['order_by'] === 'due_date' && $filters['order_dir'] === 'ASC' ? 'selected' : ''; ?>>
                            Due Date (Earliest)
                        </option>
                        <option value="due_date_DESC" <?php echo $filters['order_by'] === 'due_date' && $filters['order_dir'] === 'DESC' ? 'selected' : ''; ?>>
                            Due Date (Latest)
                        </option>
                        <option value="priority_DESC" <?php echo $filters['order_by'] === 'priority' && $filters['order_dir'] === 'DESC' ? 'selected' : ''; ?>>
                            Priority (High to Low)
                        </option>
                        <option value="priority_ASC" <?php echo $filters['order_by'] === 'priority' && $filters['order_dir'] === 'ASC' ? 'selected' : ''; ?>>
                            Priority (Low to High)
                        </option>
                        <option value="title_ASC" <?php echo $filters['order_by'] === 'title' && $filters['order_dir'] === 'ASC' ? 'selected' : ''; ?>>
                            Title (A-Z)
                        </option>
                        <option value="title_DESC" <?php echo $filters['order_by'] === 'title' && $filters['order_dir'] === 'DESC' ? 'selected' : ''; ?>>
                            Title (Z-A)
                        </option>
                        <option value="status_ASC" <?php echo $filters['order_by'] === 'status' && $filters['order_dir'] === 'ASC' ? 'selected' : ''; ?>>
                            Status
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filters Display -->
    <?php if ($activeFilterCount > 0): ?>
    <div id="active-filters-display" class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <strong><i class="bi bi-funnel-fill me-2"></i>Active Filters:</strong>
        <?php if (!empty($filters['search'])): ?>
            <span class="badge bg-primary me-1">Search: "<?php echo htmlspecialchars($filters['search']); ?>"</span>
        <?php endif; ?>
        <?php if (!empty($filters['status'])): ?>
            <span class="badge bg-info me-1">Status: <?php echo implode(', ', array_map('ucfirst', $filters['status'])); ?></span>
        <?php endif; ?>
        <?php if (!empty($filters['priority'])): ?>
            <span class="badge bg-warning text-dark me-1">Priority: <?php echo implode(', ', array_map('ucfirst', $filters['priority'])); ?></span>
        <?php endif; ?>
        <?php if (!empty($filters['categories'])): ?>
            <span class="badge bg-success me-1">Categories: <?php echo count($filters['categories']); ?> selected</span>
        <?php endif; ?>
        <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
            <span class="badge bg-secondary me-1">Date Range</span>
        <?php endif; ?>
        <a href="?clear_filters=1" class="btn btn-sm btn-outline-danger ms-2">
            <i class="bi bi-x-circle me-1"></i>Clear All
        </a>
    </div>
    <?php endif; ?>

    <!-- Task Display Container -->
    <div id="task-display-container">
        <?php if ($viewMode === 'list'): ?>
            <?php include 'components/task-table.php'; ?>
        <?php else: ?>
            <?php include 'components/task-cards.php'; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1 || $totalTasks > 10): ?>
    <div id="pagination-container" class="mt-4">
        <?php include 'components/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Filter Panel (Offcanvas) -->
<?php include 'components/filter-panel.php'; ?>

<!-- Add Task Modal -->
<?php include 'components/add-task-modal.php'; ?>

<!-- Sort Update Script -->
<script>
function updateSort(value) {
    const [sortBy, sortDir] = value.split('_');
    const url = new URL(window.location.href);
    url.searchParams.set('sort_by', sortBy);
    url.searchParams.set('sort_dir', sortDir);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}
</script>

<?php require_once 'includes/footer.php'; ?>
