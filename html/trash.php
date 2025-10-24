<?php
/**
 * Trash Page - Deleted Tasks Management
 * REQ-ARCH-101 through REQ-ARCH-106
 */

$pageTitle = 'Trash - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/task-functions.php';
require_once 'includes/header.php';

// Get current user ID
$userId = getCurrentUserId();

// Get filters
$filters = [];

// Search filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Sorting
$filters['order_by'] = $_GET['sort_by'] ?? 'deleted_at';
$filters['order_dir'] = $_GET['sort_dir'] ?? 'DESC';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 20;
$filters['limit'] = $pageSize;
$filters['offset'] = ($page - 1) * $pageSize;

// Get total count for pagination
$countFilters = $filters;
unset($countFilters['limit']);
unset($countFilters['offset']);
$allTasks = getTrashedTasks($userId, $countFilters);
$totalTasks = count($allTasks);
$totalPages = ceil($totalTasks / $pageSize);

// Get trashed tasks for current page
$tasks = getTrashedTasks($userId, $filters);
?>

<!-- Trash Page -->
<div id="trash-container" class="container-fluid">
    <!-- Page Header -->
    <div id="trash-page-header" class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 id="trash-page-title" class="h2 mb-1">
                        <i class="bi bi-trash me-2"></i>Trash
                        <span class="badge bg-danger ms-2"><?php echo $totalTasks; ?></span>
                    </h1>
                    <div class="alert alert-warning py-2 mb-0 mt-2" style="font-size: 0.9rem;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Tasks are automatically deleted permanently after 30 days.
                    </div>
                </div>
                <div>
                    <?php if ($totalTasks > 0): ?>
                    <button class="btn btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#empty-trash-modal"
                            id="empty-trash-btn">
                        <i class="bi bi-trash3 me-2"></i>Empty Trash
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <?php if ($totalTasks > 0): ?>
    <div id="trash-toolbar" class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- Bulk Selection -->
                <div class="col-lg-3 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="select-all-trash" onchange="toggleSelectAll()">
                        <label class="form-check-label fw-bold" for="select-all-trash">
                            Select All (<span id="selected-count">0</span>)
                        </label>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div class="col-lg-5 col-md-4">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary" onclick="bulkRestore()" id="bulk-restore-btn" disabled>
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore Selected
                        </button>
                        <button class="btn btn-outline-danger"
                                onclick="bulkPermanentDelete()"
                                id="bulk-delete-btn"
                                disabled>
                            <i class="bi bi-trash3 me-1"></i>Delete Permanently
                        </button>
                    </div>
                </div>

                <!-- Search -->
                <div class="col-lg-4 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control"
                               id="trash-search-input"
                               placeholder="Search trash..."
                               value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                               onkeyup="debounceTrashSearch()">
                        <?php if (!empty($filters['search'])): ?>
                        <button class="btn btn-outline-secondary" type="button" onclick="clearTrashSearch()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Task Display -->
    <div id="trash-task-display">
        <?php if (empty($tasks)): ?>
            <!-- Empty State -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-trash text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3 text-muted">Trash is Empty</h3>
                    <p class="text-muted">
                        Deleted tasks will appear here and be permanently removed after 30 days.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <?php include 'components/trash-table.php'; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div id="trash-pagination" class="mt-4">
        <nav>
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

<!-- Confirmation Modals -->
<?php include 'components/confirm-permanent-delete-modal.php'; ?>
<?php include 'components/confirm-empty-trash-modal.php'; ?>

<!-- JavaScript -->
<input type="hidden" id="csrf-token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

<script>
let selectedTasks = new Set();
let searchTimeout;

// Toggle select all
function toggleSelectAll() {
    const selectAll = document.getElementById('select-all-trash');
    const checkboxes = document.querySelectorAll('.trash-task-checkbox');

    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedTasks.add(parseInt(cb.value));
        } else {
            selectedTasks.delete(parseInt(cb.value));
        }
    });

    updateSelectedCount();
}

// Toggle individual task selection
function toggleTaskSelection(taskId) {
    if (selectedTasks.has(taskId)) {
        selectedTasks.delete(taskId);
    } else {
        selectedTasks.add(taskId);
    }
    updateSelectedCount();
}

// Update selected count display
function updateSelectedCount() {
    const count = selectedTasks.size;
    document.getElementById('selected-count').textContent = count;
    document.getElementById('bulk-restore-btn').disabled = count === 0;
    document.getElementById('bulk-delete-btn').disabled = count === 0;
}

// Restore single task
async function restoreTask(taskId) {
    if (!confirm('Restore this task from trash?')) return;

    const csrfToken = document.getElementById('csrf-token').value;

    try {
        const response = await fetch('/api/trash/restore-task.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({task_id: taskId, csrf_token: csrfToken})
        });

        const data = await response.json();

        if (data.success) {
            showToast('success', data.message || 'Task restored successfully!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('error', data.message || 'Failed to restore task');
        }
    } catch (error) {
        showToast('error', 'An error occurred');
    }
}

// Permanently delete single task
function permanentDeleteTask(taskId, taskTitle) {
    // Show confirmation modal
    const modal = document.getElementById('confirm-permanent-delete-modal');
    document.getElementById('delete-task-title').textContent = taskTitle;
    document.getElementById('confirm-delete-input').value = '';
    document.getElementById('confirm-delete-task-id').value = taskId;
    new bootstrap.Modal(modal).show();
}

// Bulk restore
async function bulkRestore() {
    if (selectedTasks.size === 0) return;
    if (!confirm(`Restore ${selectedTasks.size} task(s) from trash?`)) return;

    const csrfToken = document.getElementById('csrf-token').value;
    let restored = 0;

    for (const taskId of selectedTasks) {
        try {
            const response = await fetch('/api/trash/restore-task.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({task_id: taskId, csrf_token: csrfToken})
            });
            if (response.ok) restored++;
        } catch (error) {
            console.error('Error restoring task:', taskId);
        }
    }

    showToast('success', `${restored} task(s) restored successfully!`);
    setTimeout(() => window.location.reload(), 1000);
}

// Bulk permanent delete
function bulkPermanentDelete() {
    if (selectedTasks.size === 0) return;

    // Show confirmation modal
    const modal = document.getElementById('confirm-empty-trash-modal');
    document.getElementById('bulk-delete-count').textContent = selectedTasks.size;
    document.getElementById('empty-trash-mode').value = 'bulk';
    new bootstrap.Modal(modal).show();
}

function debounceTrashSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const search = document.getElementById('trash-search-input').value;
        const params = new URLSearchParams(window.location.search);
        if (search) {
            params.set('search', search);
        } else {
            params.delete('search');
        }
        window.location.href = 'trash.php?' + params.toString();
    }, 500);
}

function clearTrashSearch() {
    document.getElementById('trash-search-input').value = '';
    window.location.href = 'trash.php';
}

function showToast(type, message) {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show">
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
