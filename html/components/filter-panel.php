<?php
/**
 * Filter Panel Component - Offcanvas
 * Advanced filtering options for tasks
 * REQ-LIST-301 through REQ-LIST-305
 */

// Get user categories if not already loaded
if (!isset($userCategories)) {
    $userCategories = getCategoriesByUserId($userId, true);
}

// Get current filters from session or GET
$currentFilters = $_SESSION['task_filters'] ?? [];
?>

<!-- Filter Offcanvas -->
<div class="offcanvas offcanvas-end"
     tabindex="-1"
     id="filter-panel"
     aria-labelledby="filter-panel-label">
    <!-- Offcanvas Header -->
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title" id="filter-panel-label">
            <i class="bi bi-funnel-fill me-2"></i>Filter Tasks
        </h5>
        <button type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
    </div>

    <!-- Offcanvas Body -->
    <div class="offcanvas-body">
        <form id="filter-form" method="GET" action="tasks.php">
            <!-- Preserve current view and sorting -->
            <input type="hidden" name="view" value="<?php echo $viewMode ?? 'list'; ?>">
            <input type="hidden" name="sort_by" value="<?php echo $filters['order_by'] ?? 'created_at'; ?>">
            <input type="hidden" name="sort_dir" value="<?php echo $filters['order_dir'] ?? 'DESC'; ?>">
            <input type="hidden" name="page_size" value="<?php echo $pageSize ?? 20; ?>">

            <!-- Status Filter -->
            <div id="filter-status-section" class="mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-check-circle me-2"></i>Status
                </h6>
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="status[]"
                           value="pending"
                           id="filter-status-pending"
                           <?php echo isset($currentFilters['status']) && in_array('pending', $currentFilters['status']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter-status-pending">
                        <span class="badge bg-warning text-dark me-1">Pending</span>
                        Not started yet
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="status[]"
                           value="in_progress"
                           id="filter-status-progress"
                           <?php echo isset($currentFilters['status']) && in_array('in_progress', $currentFilters['status']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter-status-progress">
                        <span class="badge bg-info me-1">In Progress</span>
                        Currently working on
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="status[]"
                           value="completed"
                           id="filter-status-completed"
                           <?php echo isset($currentFilters['status']) && in_array('completed', $currentFilters['status']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter-status-completed">
                        <span class="badge bg-success me-1">Completed</span>
                        Finished tasks
                    </label>
                </div>
            </div>

            <hr>

            <!-- Priority Filter -->
            <div id="filter-priority-section" class="mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>Priority
                </h6>
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="priority[]"
                           value="high"
                           id="filter-priority-high"
                           <?php echo isset($currentFilters['priority']) && in_array('high', $currentFilters['priority']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter-priority-high">
                        <span class="badge bg-danger me-1">High</span>
                        Urgent tasks
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="priority[]"
                           value="medium"
                           id="filter-priority-medium"
                           <?php echo isset($currentFilters['priority']) && in_array('medium', $currentFilters['priority']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter-priority-medium">
                        <span class="badge bg-warning text-dark me-1">Medium</span>
                        Normal priority
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="priority[]"
                           value="low"
                           id="filter-priority-low"
                           <?php echo isset($currentFilters['priority']) && in_array('low', $currentFilters['priority']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter-priority-low">
                        <span class="badge bg-secondary me-1">Low</span>
                        Low priority
                    </label>
                </div>
            </div>

            <hr>

            <!-- Category Filter -->
            <div id="filter-category-section" class="mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-tags me-2"></i>Categories
                    <?php if (empty($userCategories)): ?>
                        <small class="text-muted">(None created)</small>
                    <?php endif; ?>
                </h6>

                <?php if (!empty($userCategories)): ?>
                    <div id="category-filter-list" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($userCategories as $category): ?>
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="categories[]"
                                   value="<?php echo $category['id']; ?>"
                                   id="filter-category-<?php echo $category['id']; ?>"
                                   <?php echo isset($currentFilters['categories']) && in_array($category['id'], $currentFilters['categories']) ? 'checked' : ''; ?>>
                            <label class="form-check-label d-flex justify-content-between align-items-center"
                                   for="filter-category-<?php echo $category['id']; ?>">
                                <span>
                                    <span class="badge rounded-pill me-1"
                                          style="background-color: <?php echo htmlspecialchars($category['color']); ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </span>
                                </span>
                                <span class="badge bg-secondary">
                                    <?php echo $category['task_count'] ?? 0; ?>
                                </span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Shows tasks with ANY selected category
                    </small>
                <?php else: ?>
                    <p class="text-muted small">
                        No categories created yet.
                        <a href="categories.php" class="text-decoration-none">Create one</a>
                    </p>
                <?php endif; ?>
            </div>

            <hr>

            <!-- Due Date Range Filter -->
            <div id="filter-date-section" class="mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-calendar-range me-2"></i>Due Date Range
                </h6>

                <div class="mb-3">
                    <label for="filter-date-from" class="form-label small">From Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="filter-date-from"
                           name="date_from"
                           value="<?php echo $currentFilters['date_from'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="filter-date-to" class="form-label small">To Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="filter-date-to"
                           name="date_to"
                           value="<?php echo $currentFilters['date_to'] ?? ''; ?>">
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDatePreset('today')">
                        <i class="bi bi-calendar-day me-1"></i>Due Today
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDatePreset('week')">
                        <i class="bi bi-calendar-week me-1"></i>Due This Week
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDatePreset('month')">
                        <i class="bi bi-calendar-month me-1"></i>Due This Month
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="setDatePreset('overdue')">
                        <i class="bi bi-exclamation-triangle me-1"></i>Overdue
                    </button>
                </div>
            </div>

            <hr>

            <!-- Action Buttons -->
            <div id="filter-actions" class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Apply Filters
                </button>
                <a href="?clear_filters=1" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Clear All Filters
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Date preset functionality
function setDatePreset(preset) {
    const dateFrom = document.getElementById('filter-date-from');
    const dateTo = document.getElementById('filter-date-to');
    const today = new Date();

    // Reset
    dateFrom.value = '';
    dateTo.value = '';

    switch(preset) {
        case 'today':
            const todayStr = today.toISOString().split('T')[0];
            dateFrom.value = todayStr;
            dateTo.value = todayStr;
            break;

        case 'week':
            // This week (Sunday to Saturday)
            const firstDay = new Date(today.setDate(today.getDate() - today.getDay()));
            const lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 6));
            dateFrom.value = firstDay.toISOString().split('T')[0];
            dateTo.value = lastDay.toISOString().split('T')[0];
            break;

        case 'month':
            // This month
            const firstDayMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDayMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            dateFrom.value = firstDayMonth.toISOString().split('T')[0];
            dateTo.value = lastDayMonth.toISOString().split('T')[0];
            break;

        case 'overdue':
            // Everything before today
            dateTo.value = new Date(Date.now() - 86400000).toISOString().split('T')[0]; // Yesterday
            break;
    }
}
</script>
