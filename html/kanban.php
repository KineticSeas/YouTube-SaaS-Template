<?php
/**
 * Kanban Board Page - Visual Task Management
 * REQ-KANBAN-001 through REQ-KANBAN-303
 */

$pageTitle = 'Kanban Board - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/task-functions.php';
require_once 'includes/header.php';

// Get current user ID
$userId = getCurrentUserId();

// Get filters from GET parameters or session
// Priority filter
if (isset($_GET['priority']) && !empty($_GET['priority']) && $_GET['priority'] !== 'all') {
    $priorityFilter = $_GET['priority'];
    $_SESSION['kanban_filters']['priority'] = $priorityFilter;
} elseif (isset($_SESSION['kanban_filters']['priority'])) {
    $priorityFilter = $_SESSION['kanban_filters']['priority'];
} else {
    $priorityFilter = 'all';
}

// Category filter (multiple)
if (isset($_GET['categories']) && is_array($_GET['categories']) && !empty($_GET['categories'])) {
    $categoryFilter = $_GET['categories'];
    $_SESSION['kanban_filters']['categories'] = $categoryFilter;
} elseif (isset($_SESSION['kanban_filters']['categories'])) {
    $categoryFilter = $_SESSION['kanban_filters']['categories'];
} else {
    $categoryFilter = [];
}

// Search filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchFilter = $_GET['search'];
    $_SESSION['kanban_filters']['search'] = $searchFilter;
} elseif (isset($_SESSION['kanban_filters']['search'])) {
    $searchFilter = $_SESSION['kanban_filters']['search'];
} else {
    $searchFilter = '';
}

// Clear filters if requested
if (isset($_GET['clear_filters'])) {
    unset($_SESSION['kanban_filters']);
    header('Location: kanban.php');
    exit;
}

// Build filters for task retrieval
$filters = [];
if ($priorityFilter !== 'all') {
    $filters['priority'] = [$priorityFilter];
}
if (!empty($categoryFilter)) {
    $filters['categories'] = $categoryFilter;
}
if (!empty($searchFilter)) {
    $filters['search'] = $searchFilter;
}

// Get all tasks for the user with filters
$allTasks = getTasksByUserId($userId, $filters);

// Group tasks by status
$pendingTasks = [];
$inProgressTasks = [];
$completedTasks = [];

foreach ($allTasks as $task) {
    switch ($task['status']) {
        case 'pending':
            $pendingTasks[] = $task;
            break;
        case 'in_progress':
            $inProgressTasks[] = $task;
            break;
        case 'completed':
            $completedTasks[] = $task;
            break;
    }
}

// Count active filters
$activeFilterCount = 0;
if ($priorityFilter !== 'all') $activeFilterCount++;
if (!empty($categoryFilter)) $activeFilterCount++;
if (!empty($searchFilter)) $activeFilterCount++;

// Get user categories for filter dropdown
$userCategories = getCategoriesByUserId($userId, true);

// Total task count
$totalTasks = count($allTasks);
?>

<!-- Kanban Board Page -->
<div id="kanban-page-container" class="container-fluid">
    <!-- Page Header -->
    <div id="kanban-page-header" class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 id="kanban-page-title" class="h2 mb-1">
                        <i class="bi bi-columns-gap me-2"></i>Kanban Board
                        <span class="badge bg-secondary ms-2"><?php echo $totalTasks; ?></span>
                    </h1>
                    <p id="kanban-page-subtitle" class="text-muted mb-0">
                        Drag and drop tasks to update their status
                    </p>
                </div>
                <div>
                    <button class="btn btn-primary btn-lg"
                            data-bs-toggle="modal"
                            data-bs-target="#add-task-modal"
                            id="kanban-add-task-btn"
                            onclick="resetTaskModal()">
                        <i class="bi bi-plus-circle me-2"></i>New Task
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div id="kanban-filter-toolbar" class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <!-- Priority Filter -->
                <div class="col-lg-3 col-md-4 col-6">
                    <label for="kanban-priority-filter" class="form-label small text-muted mb-1">
                        <i class="bi bi-funnel me-1"></i>Priority
                    </label>
                    <select class="form-select"
                            id="kanban-priority-filter"
                            onchange="applyKanbanFilters()">
                        <option value="all" <?php echo $priorityFilter === 'all' ? 'selected' : ''; ?>>All Priorities</option>
                        <option value="high" <?php echo $priorityFilter === 'high' ? 'selected' : ''; ?>>High Priority</option>
                        <option value="medium" <?php echo $priorityFilter === 'medium' ? 'selected' : ''; ?>>Medium Priority</option>
                        <option value="low" <?php echo $priorityFilter === 'low' ? 'selected' : ''; ?>>Low Priority</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-lg-3 col-md-4 col-6">
                    <label for="kanban-category-filter" class="form-label small text-muted mb-1">
                        <i class="bi bi-tags me-1"></i>Categories
                    </label>
                    <select class="form-select"
                            id="kanban-category-filter"
                            multiple
                            onchange="applyKanbanFilters()"
                            style="height: 38px; overflow: hidden;">
                        <option value="" disabled>Select categories...</option>
                        <?php foreach ($userCategories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"
                                <?php echo in_array($category['id'], $categoryFilter) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search Filter -->
                <div class="col-lg-4 col-md-4 col-12">
                    <label for="kanban-search-input" class="form-label small text-muted mb-1">
                        <i class="bi bi-search me-1"></i>Search
                    </label>
                    <div class="input-group">
                        <input type="text"
                               class="form-control"
                               id="kanban-search-input"
                               placeholder="Search tasks..."
                               value="<?php echo htmlspecialchars($searchFilter); ?>"
                               onkeyup="debounceKanbanSearch()">
                        <?php if (!empty($searchFilter)): ?>
                        <button class="btn btn-outline-secondary"
                                type="button"
                                id="kanban-clear-search-btn"
                                onclick="clearKanbanSearch()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Clear Filters Button -->
                <div class="col-lg-2 col-md-12">
                    <label class="form-label small text-muted mb-1 d-none d-lg-block">&nbsp;</label>
                    <?php if ($activeFilterCount > 0): ?>
                    <a href="?clear_filters=1"
                       class="btn btn-outline-danger w-100"
                       id="kanban-clear-filters-btn">
                        <i class="bi bi-x-circle me-1"></i>Clear Filters
                        <span class="badge bg-danger ms-1"><?php echo $activeFilterCount; ?></span>
                    </a>
                    <?php else: ?>
                    <button class="btn btn-outline-secondary w-100 disabled"
                            id="kanban-no-filters-btn" disabled>
                        <i class="bi bi-funnel me-1"></i>No Filters
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Columns -->
    <div id="kanban-columns-container" class="row g-3">
        <!-- Pending Column -->
        <div class="col-lg-4 col-md-12" id="kanban-pending-column-wrapper">
            <?php
            $status = 'pending';
            $tasks = $pendingTasks;
            $columnTitle = 'Pending';
            $columnColor = 'warning';
            include 'components/kanban-column.php';
            ?>
        </div>

        <!-- In Progress Column -->
        <div class="col-lg-4 col-md-12" id="kanban-in-progress-column-wrapper">
            <?php
            $status = 'in_progress';
            $tasks = $inProgressTasks;
            $columnTitle = 'In Progress';
            $columnColor = 'info';
            include 'components/kanban-column.php';
            ?>
        </div>

        <!-- Completed Column -->
        <div class="col-lg-4 col-md-12" id="kanban-completed-column-wrapper">
            <?php
            $status = 'completed';
            $tasks = $completedTasks;
            $columnTitle = 'Completed';
            $columnColor = 'success';
            include 'components/kanban-column.php';
            ?>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<?php include 'components/add-task-modal.php'; ?>

<!-- CSRF Token (for AJAX requests) -->
<input type="hidden" id="csrf-token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

<!-- Kanban JavaScript -->
<script>
// Filter functions
let searchTimeout;

function applyKanbanFilters() {
    const priority = document.getElementById('kanban-priority-filter').value;
    const categorySelect = document.getElementById('kanban-category-filter');
    const categories = Array.from(categorySelect.selectedOptions).map(opt => opt.value).filter(v => v);
    const search = document.getElementById('kanban-search-input').value;

    // Build URL with filters
    let url = 'kanban.php?';
    if (priority !== 'all') {
        url += 'priority=' + encodeURIComponent(priority) + '&';
    }
    categories.forEach(cat => {
        url += 'categories[]=' + encodeURIComponent(cat) + '&';
    });
    if (search) {
        url += 'search=' + encodeURIComponent(search) + '&';
    }

    window.location.href = url;
}

function debounceKanbanSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyKanbanFilters();
    }, 500);
}

function clearKanbanSearch() {
    document.getElementById('kanban-search-input').value = '';
    applyKanbanFilters();
}

/**
 * Open task modal for editing
 */
function openTaskModal(taskId) {
    editTask(taskId);
}

/**
 * Edit task
 */
function editTask(taskId) {
    loadTaskForEdit(taskId);
    const modal = new bootstrap.Modal(document.getElementById('add-task-modal'));
    modal.show();
}

/**
 * Load task data for editing
 */
function loadTaskForEdit(taskId) {
    // Fetch task data from API
    fetch(`/api/tasks/get.php?id=${taskId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load task');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.task) {
                const task = data.task;

                // Populate form fields
                document.getElementById('task-title').value = task.title || '';
                document.getElementById('task-description').value = task.description || '';
                document.getElementById('task-status').value = task.status || 'pending';
                document.getElementById('task-priority').value = task.priority || 'medium';
                document.getElementById('task-due-date').value = task.due_date || '';

                // Update character count
                const charCount = document.getElementById('char-count');
                if (charCount) {
                    charCount.textContent = (task.description || '').length;
                }

                // Set selected categories
                const categorySelect = document.getElementById('task-categories');
                if (categorySelect && task.categories && task.categories.length > 0) {
                    const categoryIds = task.categories.map(cat => cat.id.toString());
                    Array.from(categorySelect.options).forEach(option => {
                        option.selected = categoryIds.includes(option.value);
                    });
                }

                // Update modal title
                const modalTitle = document.getElementById('add-task-modal-title');
                if (modalTitle) {
                    modalTitle.innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Task';
                }

                // Update submit button text
                const submitBtn = document.querySelector('#add-task-form button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Task';
                }

                // Update form action to update endpoint
                const form = document.getElementById('add-task-form');
                if (form) {
                    form.setAttribute('action', '/api/tasks/update.php');
                    form.setAttribute('hx-post', '/api/tasks/update.php');
                    form.setAttribute('data-task-id', taskId);

                    // Re-process the form element for HTMX to recognize the change
                    htmx.process(form);

                    // Add hidden task ID field if it doesn't exist
                    let taskIdField = form.querySelector('input[name="task_id"]');
                    if (!taskIdField) {
                        taskIdField = document.createElement('input');
                        taskIdField.type = 'hidden';
                        taskIdField.name = 'task_id';
                        form.insertBefore(taskIdField, form.firstChild);
                    }
                    taskIdField.value = taskId;

                    // Debug logging
                    console.log('Edit mode loaded:', {
                        taskId: taskId,
                        formAction: form.getAttribute('hx-post'),
                        taskIdField: taskIdField.value
                    });
                }
            } else {
                TodoTracker.showToast('Failed to load task data', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading task:', error);
            TodoTracker.showToast('Error loading task', 'error');
        });
}

/**
 * Reset task modal for adding new tasks
 */
function resetTaskModal() {
    const form = document.getElementById('add-task-form');
    if (form) {
        // Reset form fields
        form.reset();

        // Reset to create mode
        form.setAttribute('action', '/api/tasks/create.php');
        form.setAttribute('hx-post', '/api/tasks/create.php');
        form.removeAttribute('data-task-id');

        // Re-process for HTMX
        htmx.process(form);

        // Remove task_id field if it exists
        const taskIdField = form.querySelector('input[name="task_id"]');
        if (taskIdField) {
            taskIdField.remove();
        }

        // Reset modal title
        const modalTitle = document.getElementById('add-task-modal-title');
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Task';
        }

        // Reset submit button text
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Task';
        }

        // Reset character count
        const charCount = document.getElementById('char-count');
        if (charCount) {
            charCount.textContent = '0';
        }
    }
}

// Listen for successful form submission (HTMX event)
document.body.addEventListener('htmx:afterSwap', function(event) {
    if (event.detail.target.id === 'add-task-response') {
        // Check if the response contains success message
        const responseText = event.detail.target.textContent;
        if (responseText.includes('successfully') || responseText.includes('created') || responseText.includes('updated')) {
            // Close modal and reload page after a short delay
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('add-task-modal'));
                if (modal) {
                    modal.hide();
                }
                window.location.reload();
            }, 1000);
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
