<?php
/**
 * Categories Management Page
 * View, create, edit, and delete categories
 * REQ-CAT-001 through REQ-CAT-007
 */

$pageTitle = 'Categories - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/task-functions.php';
require_once 'includes/header.php';

// Get current user ID
$userId = getCurrentUserId();

// Get all categories with task count
$categories = getCategoriesByUserId($userId, true);
?>

<!-- Categories Content -->
<div id="categories-container" class="container-fluid">
    <!-- Page Header -->
    <div id="categories-header" class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h1 id="categories-title" class="h2 mb-1">
                        <i class="bi bi-tags me-2"></i>Categories
                    </h1>
                    <p id="categories-subtitle" class="text-muted mb-0">
                        Organize your tasks with custom categories
                    </p>
                </div>
                <div>
                    <button id="add-category-btn" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#category-modal" data-action="create">
                        <i class="bi bi-plus-circle me-2"></i>Add Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div id="category-stats-row" class="row mb-4">
        <div class="col-md-4">
            <div id="stat-total-categories" class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Total Categories</h6>
                            <h2 class="mb-0 display-6"><?php echo count($categories); ?></h2>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-tags-fill" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div id="stat-categorized-tasks" class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Categorized Tasks</h6>
                            <h2 class="mb-0 display-6"><?php echo array_sum(array_column($categories, 'task_count')); ?></h2>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-check-square-fill" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div id="stat-most-used" class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Most Used</h6>
                            <?php
                            $mostUsed = !empty($categories) ? array_reduce($categories, function($max, $cat) {
                                return ($cat['task_count'] > ($max['task_count'] ?? 0)) ? $cat : $max;
                            }, ['name' => 'None', 'task_count' => 0]) : ['name' => 'None', 'task_count' => 0];
                            ?>
                            <h6 class="mb-0"><?php echo htmlspecialchars($mostUsed['name']); ?></h6>
                            <p class="text-muted small mb-0"><?php echo $mostUsed['task_count']; ?> tasks</p>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-star-fill" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Grid -->
    <div id="categories-grid-section" class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 id="categories-list-title" class="mb-0">
                        <i class="bi bi-grid me-2"></i>All Categories
                        <span class="badge bg-secondary ms-2"><?php echo count($categories); ?></span>
                    </h5>
                </div>
                <div id="categories-grid" class="card-body">
                    <?php if (empty($categories)): ?>
                        <!-- Empty State -->
                        <div id="no-categories-message" class="text-center py-5">
                            <i class="bi bi-tags" style="font-size: 4rem; opacity: 0.3;"></i>
                            <h4 class="mt-3 text-muted">No categories yet</h4>
                            <p class="text-muted">Create your first category to start organizing your tasks</p>
                            <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#category-modal" data-action="create">
                                <i class="bi bi-plus-circle me-2"></i>Create Category
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Categories Table -->
                        <div id="categories-table-wrapper" class="table-responsive">
                            <table id="categories-table" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th id="th-category">Category</th>
                                        <th id="th-tasks" class="text-center">Tasks</th>
                                        <th id="th-created" class="d-none d-md-table-cell">Created</th>
                                        <th id="th-actions" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="categories-tbody">
                                    <?php foreach ($categories as $category): ?>
                                        <tr id="category-row-<?php echo $category['id']; ?>" data-category-id="<?php echo $category['id']; ?>">
                                            <td id="category-name-<?php echo $category['id']; ?>">
                                                <span class="badge rounded-pill px-3 py-2" style="background-color: <?php echo htmlspecialchars($category['color']); ?>; color: #fff; font-size: 0.95rem;">
                                                    <i class="bi bi-tag-fill me-1"></i>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </span>
                                            </td>
                                            <td id="category-count-<?php echo $category['id']; ?>" class="text-center">
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo $category['task_count']; ?>
                                                </span>
                                            </td>
                                            <td id="category-date-<?php echo $category['id']; ?>" class="d-none d-md-table-cell text-muted small">
                                                <?php echo date('M j, Y', strtotime($category['created_at'])); ?>
                                            </td>
                                            <td id="category-actions-<?php echo $category['id']; ?>" class="text-end">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary edit-category-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#category-modal"
                                                            data-action="edit"
                                                            data-category-id="<?php echo $category['id']; ?>"
                                                            data-category-name="<?php echo htmlspecialchars($category['name']); ?>"
                                                            data-category-color="<?php echo htmlspecialchars($category['color']); ?>"
                                                            title="Edit category">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-category-btn"
                                                            data-category-id="<?php echo $category['id']; ?>"
                                                            data-category-name="<?php echo htmlspecialchars($category['name']); ?>"
                                                            data-task-count="<?php echo $category['task_count']; ?>"
                                                            title="Delete category">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Category Modal -->
<?php include 'components/category-modal.php'; ?>

<!-- Category Management JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryModal = document.getElementById('category-modal');
    const modalTitle = document.getElementById('category-modal-title');
    const modalForm = document.getElementById('category-form');
    const categoryIdInput = document.getElementById('category-id');
    const categoryNameInput = document.getElementById('category-name');
    const categoryColorInput = document.getElementById('category-color');

    // Track current mode for form submission
    let currentMode = 'create';

    // Handle modal open for create/edit
    categoryModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const action = button.getAttribute('data-action');

        if (action === 'create') {
            // Create mode
            currentMode = 'create';
            modalTitle.textContent = 'Add Category';
            categoryIdInput.value = '';
            categoryNameInput.value = '';
            categoryColorInput.value = '#6c757d';

            // Update HTMX attributes properly
            modalForm.setAttribute('hx-post', '/api/categories/create.php');
            htmx.process(modalForm); // Re-process the element for HTMX
        } else if (action === 'edit') {
            // Edit mode
            currentMode = 'edit';
            modalTitle.textContent = 'Edit Category';
            const categoryId = button.getAttribute('data-category-id');
            const categoryName = button.getAttribute('data-category-name');
            const categoryColor = button.getAttribute('data-category-color');

            categoryIdInput.value = categoryId;
            categoryNameInput.value = categoryName;
            categoryColorInput.value = categoryColor;

            // Update HTMX attributes properly
            modalForm.setAttribute('hx-post', '/api/categories/update.php');
            htmx.process(modalForm); // Re-process the element for HTMX
        }
    });

    // Handle form submission to ensure correct endpoint and data
    modalForm.addEventListener('htmx:configRequest', function(event) {
        // Verify category_id is set in edit mode
        if (currentMode === 'edit' && !categoryIdInput.value) {
            event.detail.cancelRequest = true;
            alert('Error: Category ID is missing. Please close and reopen the modal.');
            return;
        }
    });

    // Handle delete button clicks
    document.querySelectorAll('.delete-category-btn').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            const taskCount = parseInt(this.getAttribute('data-task-count'));

            let message = `Are you sure you want to delete the category "${categoryName}"?`;
            if (taskCount > 0) {
                message += `\n\nThis category is used by ${taskCount} task(s). The tasks will not be deleted, but this category will be removed from them.`;
            }

            if (confirm(message)) {
                // Delete via HTMX
                htmx.ajax('POST', '/api/categories/delete.php', {
                    values: {
                        category_id: categoryId,
                        csrf_token: '<?php echo generateCSRFToken(); ?>'
                    },
                    target: 'body',
                    swap: 'none'
                }).then(() => {
                    // Reload page on success
                    window.location.reload();
                });
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
