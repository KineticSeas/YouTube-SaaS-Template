<?php
/**
 * Archive Page - Archived Tasks Management
 * REQ-ARCH-001 through REQ-ARCH-006
 */

$pageTitle = 'Archived Tasks - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/task-functions.php';
require_once 'includes/header.php';

// Get current user ID
$userId = getCurrentUserId();

// Get view mode from session or default to 'list'
if (isset($_GET['view'])) {
    $_SESSION['archive_view'] = $_GET['view'];
}
$viewMode = $_SESSION['archive_view'] ?? 'list';

// Get filters from GET parameters or session
$filters = [];

// Search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
    $_SESSION['archive_filters']['search'] = $_GET['search'];
} elseif (isset($_SESSION['archive_filters']['search'])) {
    $filters['search'] = $_SESSION['archive_filters']['search'];
}

// Priority filter
if (isset($_GET['priority']) && is_array($_GET['priority'])) {
    $filters['priority'] = $_GET['priority'];
    $_SESSION['archive_filters']['priority'] = $_GET['priority'];
} elseif (isset($_SESSION['archive_filters']['priority'])) {
    $filters['priority'] = $_SESSION['archive_filters']['priority'];
}

// Category filter
if (isset($_GET['categories']) && is_array($_GET['categories'])) {
    $filters['categories'] = $_GET['categories'];
    $_SESSION['archive_filters']['categories'] = $_GET['categories'];
} elseif (isset($_SESSION['archive_filters']['categories'])) {
    $filters['categories'] = $_SESSION['archive_filters']['categories'];
}

// Archived date range filter
if (isset($_GET['archived_from']) && !empty($_GET['archived_from'])) {
    $filters['archived_from'] = $_GET['archived_from'];
    $_SESSION['archive_filters']['archived_from'] = $_GET['archived_from'];
} elseif (isset($_SESSION['archive_filters']['archived_from'])) {
    $filters['archived_from'] = $_SESSION['archive_filters']['archived_from'];
}

if (isset($_GET['archived_to']) && !empty($_GET['archived_to'])) {
    $filters['archived_to'] = $_GET['archived_to'];
    $_SESSION['archive_filters']['archived_to'] = $_GET['archived_to'];
} elseif (isset($_SESSION['archive_filters']['archived_to'])) {
    $filters['archived_to'] = $_SESSION['archive_filters']['archived_to'];
}

// Sorting
if (isset($_GET['sort_by'])) {
    $filters['order_by'] = $_GET['sort_by'];
    $_SESSION['archive_filters']['order_by'] = $_GET['sort_by'];
} elseif (isset($_SESSION['archive_filters']['order_by'])) {
    $filters['order_by'] = $_SESSION['archive_filters']['order_by'];
} else {
    $filters['order_by'] = 'archived_at';
}

if (isset($_GET['sort_dir'])) {
    $filters['order_dir'] = $_GET['sort_dir'];
    $_SESSION['archive_filters']['order_dir'] = $_GET['sort_dir'];
} elseif (isset($_SESSION['archive_filters']['order_dir'])) {
    $filters['order_dir'] = $_SESSION['archive_filters']['order_dir'];
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

// Clear filters if requested
if (isset($_GET['clear_filters'])) {
    unset($_SESSION['archive_filters']);
    header('Location: archive.php');
    exit;
}

// Get total count for pagination
$countFilters = $filters;
unset($countFilters['limit']);
unset($countFilters['offset']);
$allTasks = getArchivedTasks($userId, $countFilters);
$totalTasks = count($allTasks);
$totalPages = ceil($totalTasks / $pageSize);

// Get archived tasks for current page
$tasks = getArchivedTasks($userId, $filters);

// Count active filters
$activeFilterCount = 0;
if (!empty($filters['search'])) $activeFilterCount++;
if (!empty($filters['priority'])) $activeFilterCount++;
if (!empty($filters['categories'])) $activeFilterCount++;
if (!empty($filters['archived_from']) || !empty($filters['archived_to'])) $activeFilterCount++;

// Get user categories for filter
$userCategories = getCategoriesByUserId($userId, true);
?>

<!-- Archive Page -->
<div id="archive-container" class="container-fluid">
    <!-- Page Header -->
    <div id="archive-page-header" class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 id="archive-page-title" class="h2 mb-1">
                        <i class="bi bi-archive me-2"></i>Archived Tasks
                        <span class="badge bg-secondary ms-2"><?php echo $totalTasks; ?></span>
                    </h1>
                    <p id="archive-page-subtitle" class="text-muted mb-0">
                        Tasks you've archived for future reference
                    </p>
                </div>
                <div>
                    <button class="btn btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#auto-archive-modal"
                            id="auto-archive-btn">
                        <i class="bi bi-clock-history me-2"></i>Auto-Archive Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div id="archive-toolbar" class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- View Toggle -->
                <div class="col-lg-2 col-md-3 col-6">
                    <div id="archive-view-toggle" class="btn-group w-100" role="group">
                        <a href="?view=list" class="btn btn-outline-secondary <?php echo $viewMode === 'list' ? 'active' : ''; ?>">
                            <i class="bi bi-list-ul"></i> List
                        </a>
                        <a href="?view=grid" class="btn btn-outline-secondary <?php echo $viewMode === 'grid' ? 'active' : ''; ?>">
                            <i class="bi bi-grid-3x3-gap"></i> Grid
                        </a>
                    </div>
                </div>

                <!-- Search -->
                <div class="col-lg-4 col-md-5 col-6">
                    <div id="archive-search-group" class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control"
                               id="archive-search-input"
                               placeholder="Search archived tasks..."
                               value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                               onkeyup="debounceArchiveSearch()">
                        <?php if (!empty($filters['search'])): ?>
                        <button class="btn btn-outline-secondary" type="button" onclick="clearArchiveSearch()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filter Button -->
                <div class="col-lg-2 col-md-2 col-6">
                    <button class="btn btn-outline-primary w-100"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#archive-filter-panel">
                        <i class="bi bi-funnel me-1"></i> Filters
                        <?php if ($activeFilterCount > 0): ?>
                            <span class="badge bg-danger ms-1"><?php echo $activeFilterCount; ?></span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- Sort Dropdown -->
                <div class="col-lg-3 col-md-2 col-6">
                    <select class="form-select" id="archive-sort-select" onchange="updateArchiveSort(this.value)">
                        <option value="archived_at_DESC" <?php echo $filters['order_by'] === 'archived_at' && $filters['order_dir'] === 'DESC' ? 'selected' : ''; ?>>
                            Recently Archived
                        </option>
                        <option value="archived_at_ASC" <?php echo $filters['order_by'] === 'archived_at' && $filters['order_dir'] === 'ASC' ? 'selected' : ''; ?>>
                            Oldest Archived
                        </option>
                        <option value="due_date_ASC" <?php echo $filters['order_by'] === 'due_date' && $filters['order_dir'] === 'ASC' ? 'selected' : ''; ?>>
                            Due Date (Earliest)
                        </option>
                        <option value="title_ASC" <?php echo $filters['order_by'] === 'title' && $filters['order_dir'] === 'ASC' ? 'selected' : ''; ?>>
                            Title (A-Z)
                        </option>
                        <option value="priority_DESC" <?php echo $filters['order_by'] === 'priority' && $filters['order_dir'] === 'DESC' ? 'selected' : ''; ?>>
                            Priority (High to Low)
                        </option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="col-lg-1 col-md-12">
                    <?php if ($activeFilterCount > 0): ?>
                    <a href="?clear_filters=1" class="btn btn-outline-danger w-100">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Display -->
    <div id="archive-task-display">
        <?php if (empty($tasks)): ?>
            <!-- Empty State -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-archive text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3 text-muted">No Archived Tasks</h3>
                    <p class="text-muted">
                        <?php if ($activeFilterCount > 0): ?>
                            No archived tasks match your filters.
                            <br><a href="?clear_filters=1">Clear filters</a> to see all archived tasks.
                        <?php else: ?>
                            Tasks you archive will appear here for future reference.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <?php if ($viewMode === 'list'): ?>
                <?php include 'components/archive-table.php'; ?>
            <?php else: ?>
                <?php include 'components/archive-cards.php'; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div id="archive-pagination" class="mt-4">
        <nav aria-label="Archive pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Filter Panel (Offcanvas) -->
<?php include 'components/archive-filter-panel.php'; ?>

<!-- Auto-Archive Settings Modal -->
<?php include 'components/auto-archive-modal.php'; ?>

<!-- JavaScript -->
<script>
let searchTimeout;

function updateArchiveSort(value) {
    const [sortBy, sortDir] = value.split('_');
    const params = new URLSearchParams(window.location.search);
    params.set('sort_by', sortBy);
    params.set('sort_dir', sortDir);
    params.set('page', '1');
    window.location.href = 'archive.php?' + params.toString();
}

function debounceArchiveSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const search = document.getElementById('archive-search-input').value;
        const params = new URLSearchParams(window.location.search);
        if (search) {
            params.set('search', search);
        } else {
            params.delete('search');
        }
        params.set('page', '1');
        window.location.href = 'archive.php?' + params.toString();
    }, 500);
}

function clearArchiveSearch() {
    document.getElementById('archive-search-input').value = '';
    const params = new URLSearchParams(window.location.search);
    params.delete('search');
    window.location.href = 'archive.php?' + params.toString();
}

// Unarchive a task (restore from archive)
async function unarchiveTask(taskId) {
    if (!confirm('Restore this task from archive?')) {
        return;
    }

    const csrfToken = '<?php echo generateCSRFToken(); ?>';

    try {
        const response = await fetch('/api/archive/unarchive-task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                task_id: taskId,
                csrf_token: csrfToken
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast('success', data.message || 'Task restored successfully!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('error', data.message || 'Failed to restore task');
        }
    } catch (error) {
        showToast('error', 'An error occurred while restoring the task');
    }
}

// Delete an archived task (move to trash)
async function deleteArchivedTask(taskId) {
    if (!confirm('Move this task to trash?')) {
        return;
    }

    const csrfToken = '<?php echo generateCSRFToken(); ?>';

    try {
        const response = await fetch('/api/trash/delete-task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                task_id: taskId,
                csrf_token: csrfToken
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast('success', data.message || 'Task moved to trash');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('error', data.message || 'Failed to delete task');
        }
    } catch (error) {
        showToast('error', 'An error occurred while deleting the task');
    }
}

// Show toast notification
function showToast(type, message) {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    const container = document.getElementById('toast-container');
    if (container) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = toastHtml;
        const toast = tempDiv.firstElementChild;
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
