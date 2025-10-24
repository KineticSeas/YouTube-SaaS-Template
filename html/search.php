<?php
/**
 * Search Page - Dedicated search interface for tasks
 * Displays search results in same format as tasks.php
 */

$pageTitle = 'Search Tasks - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/task-functions.php';

require_once 'includes/header.php';

// Get current user ID
$userId = getCurrentUserId();

// Get view mode from session or default to 'list'
if (isset($_GET['view'])) {
    $_SESSION['task_view'] = $_GET['view'];
}
$viewMode = $_SESSION['task_view'] ?? 'list';

// Get filters from GET parameters
$filters = [];

// Search - primary filter for this page
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = trim($_GET['search']);
}

// Status filter (multiple)
if (isset($_GET['status']) && is_array($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}

// Priority filter (multiple)
if (isset($_GET['priority']) && is_array($_GET['priority'])) {
    $filters['priority'] = $_GET['priority'];
}

// Category filter
if (isset($_GET['categories']) && is_array($_GET['categories'])) {
    $filters['categories'] = $_GET['categories'];
}

// Sorting
if (isset($_GET['sort_by'])) {
    $filters['order_by'] = $_GET['sort_by'];
} else {
    $filters['order_by'] = 'created_at';
}

if (isset($_GET['sort_dir'])) {
    $filters['order_dir'] = $_GET['sort_dir'];
} else {
    $filters['order_dir'] = 'DESC';
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = isset($_GET['page_size']) ? intval($_GET['page_size']) : 20;
if (!in_array($pageSize, [10, 20, 50, 100])) {
    $pageSize = 20;
}

$filters['limit'] = $pageSize;
$filters['offset'] = ($page - 1) * $pageSize;

// Get tasks based on search
$tasks = [];
$totalTasks = 0;
$totalPages = 0;

if (!empty($filters['search'])) {
    // Get total count for pagination (without limit/offset)
    $countFilters = $filters;
    unset($countFilters['limit']);
    unset($countFilters['offset']);
    $allTasks = getTasksByUserId($userId, $countFilters);
    $totalTasks = count($allTasks);
    $totalPages = ceil($totalTasks / $pageSize);

    // Get tasks for current page
    $tasks = getTasksByUserId($userId, $filters);
}

// Get user categories for filter
$userCategories = getCategoriesByUserId($userId, true);

// Count active filters (excluding search since it's the primary function)
$activeFilterCount = 0;
if (!empty($filters['status'])) $activeFilterCount++;
if (!empty($filters['priority'])) $activeFilterCount++;
if (!empty($filters['categories'])) $activeFilterCount++;

// Determine page display title
$pageDisplayTitle = 'Search Tasks';
$pageDisplaySubtitle = !empty($filters['search'])
    ? 'Results for "' . htmlspecialchars($filters['search']) . '"'
    : 'Enter a search term to find tasks';
?>

<!-- Search Page -->
<div id="search-page-container" class="container-fluid">
    <!-- Page Header -->
    <div id="search-page-header" class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 id="search-page-title" class="h2 mb-1">
                        <i class="bi bi-search me-2"></i><?php echo $pageDisplayTitle; ?>
                        <?php if (!empty($filters['search'])): ?>
                            <span class="badge bg-secondary ms-2"><?php echo $totalTasks; ?></span>
                        <?php endif; ?>
                    </h1>
                    <p id="search-page-subtitle" class="text-muted mb-0">
                        <?php echo $pageDisplaySubtitle; ?>
                    </p>
                </div>
                <div>
                    <a href="tasks.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to All Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar - Prominent -->
    <div id="search-main-card" class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="search.php" id="search-form">
                <div id="search-main-input-group" class="input-group input-group-lg mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text"
                           class="form-control"
                           id="main-search-input"
                           name="search"
                           placeholder="Search by title, description, or category..."
                           value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                           autofocus>
                    <button class="btn btn-primary" type="submit" id="search-submit-btn">
                        Search
                    </button>
                    <?php if (!empty($filters['search'])): ?>
                    <button class="btn btn-outline-secondary"
                            type="button"
                            id="clear-main-search-btn"
                            onclick="window.location.href='search.php'">
                        <i class="bi bi-x-lg"></i> Clear
                    </button>
                    <?php endif; ?>
                </div>

                <input type="hidden" name="view" value="<?php echo $viewMode; ?>">
                <input type="hidden" name="sort_by" value="<?php echo $filters['order_by']; ?>">
                <input type="hidden" name="sort_dir" value="<?php echo $filters['order_dir']; ?>">
                <input type="hidden" name="page_size" value="<?php echo $pageSize; ?>">
            </form>
        </div>
    </div>

    <?php if (!empty($filters['search'])): ?>

    <!-- Toolbar -->
    <div id="search-toolbar" class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- View Toggle -->
                <div class="col-lg-3 col-md-4 col-6">
                    <div id="view-toggle-buttons-search" class="btn-group w-100" role="group">
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
                           id="search-view-list-btn">
                            <i class="bi bi-list-ul"></i> List
                        </a>
                        <a href="?view=grid<?php echo $viewToggleQuery; ?>"
                           class="btn btn-outline-secondary <?php echo $viewMode === 'grid' ? 'active' : ''; ?>"
                           id="search-view-grid-btn">
                            <i class="bi bi-grid-3x3-gap"></i> Grid
                        </a>
                    </div>
                </div>

                <!-- Filter Button -->
                <div class="col-lg-3 col-md-4 col-6">
                    <button class="btn btn-outline-primary w-100"
                            type="button"
                            id="search-filter-toggle-btn"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#filter-panel">
                        <i class="bi bi-funnel me-1"></i> Filters
                        <?php if ($activeFilterCount > 0): ?>
                            <span class="badge bg-danger ms-1"><?php echo $activeFilterCount; ?></span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- Sort Dropdown -->
                <div class="col-lg-6 col-md-4 col-12">
                    <select class="form-select"
                            id="search-sort-select"
                            onchange="updateSearchSort(this.value)">
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

    <!-- Active Filters Display (excluding search) -->
    <?php if ($activeFilterCount > 0): ?>
    <div id="search-active-filters-display" class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <strong><i class="bi bi-funnel-fill me-2"></i>Active Filters:</strong>
        <?php if (!empty($filters['status'])): ?>
            <span class="badge bg-info me-1">Status: <?php echo implode(', ', array_map('ucfirst', $filters['status'])); ?></span>
        <?php endif; ?>
        <?php if (!empty($filters['priority'])): ?>
            <span class="badge bg-warning text-dark me-1">Priority: <?php echo implode(', ', array_map('ucfirst', $filters['priority'])); ?></span>
        <?php endif; ?>
        <?php if (!empty($filters['categories'])): ?>
            <span class="badge bg-success me-1">Categories: <?php echo count($filters['categories']); ?> selected</span>
        <?php endif; ?>
        <?php
        // Clear filters but keep search
        $clearUrl = 'search.php?search=' . urlencode($filters['search']);
        if (!empty($filters['order_by'])) $clearUrl .= '&sort_by=' . urlencode($filters['order_by']);
        if (!empty($filters['order_dir'])) $clearUrl .= '&sort_dir=' . urlencode($filters['order_dir']);
        ?>
        <a href="<?php echo $clearUrl; ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="bi bi-x-circle me-1"></i>Clear Filters
        </a>
    </div>
    <?php endif; ?>

    <!-- Task Display Container -->
    <div id="search-task-display-container">
        <?php if ($totalTasks > 0): ?>
            <?php if ($viewMode === 'list'): ?>
                <?php include 'components/task-table.php'; ?>
            <?php else: ?>
                <?php include 'components/task-cards.php'; ?>
            <?php endif; ?>
        <?php else: ?>
            <!-- No Results Found -->
            <div id="search-no-results" class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No tasks found</h3>
                    <p class="text-muted mb-4">
                        No tasks match your search for "<?php echo htmlspecialchars($filters['search']); ?>".
                    </p>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-task-modal">
                            <i class="bi bi-plus-circle me-2"></i>Create New Task
                        </button>
                        <a href="search.php" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Clear Search
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div id="search-pagination-container" class="mt-4">
        <?php include 'components/pagination.php'; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>

    <!-- Empty State - No Search Query -->
    <div id="search-empty-state" class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
            <h3 class="mt-3">Search Your Tasks</h3>
            <p class="text-muted mb-4">
                Enter keywords to search through your tasks by title, description, or category.
            </p>
            <div>
                <a href="tasks.php" class="btn btn-outline-primary">
                    <i class="bi bi-list-task me-2"></i>View All Tasks
                </a>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<!-- Filter Panel (Offcanvas) -->
<?php include 'components/filter-panel.php'; ?>

<!-- Add Task Modal -->
<?php include 'components/add-task-modal.php'; ?>

<!-- Sort Update Script -->
<script>
function updateSearchSort(value) {
    const [sortBy, sortDir] = value.split('_');
    const url = new URL(window.location.href);
    url.searchParams.set('sort_by', sortBy);
    url.searchParams.set('sort_dir', sortDir);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}
</script>

<?php require_once 'includes/footer.php'; ?>
