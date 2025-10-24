<?php
/**
 * Archive Filter Panel - Offcanvas filter panel for archived tasks
 */
?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="archive-filter-panel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            <i class="bi bi-funnel me-2"></i>Filter Archived Tasks
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="archive.php" id="archive-filter-form">
            <!-- Priority Filter -->
            <div class="mb-4">
                <label class="form-label fw-bold">Priority</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="priority[]" value="high"
                           id="archive-filter-priority-high"
                           <?php echo (isset($filters['priority']) && in_array('high', $filters['priority'])) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="archive-filter-priority-high">
                        <span class="badge bg-danger">High</span>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="priority[]" value="medium"
                           id="archive-filter-priority-medium"
                           <?php echo (isset($filters['priority']) && in_array('medium', $filters['priority'])) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="archive-filter-priority-medium">
                        <span class="badge bg-warning text-dark">Medium</span>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="priority[]" value="low"
                           id="archive-filter-priority-low"
                           <?php echo (isset($filters['priority']) && in_array('low', $filters['priority'])) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="archive-filter-priority-low">
                        <span class="badge bg-secondary">Low</span>
                    </label>
                </div>
            </div>

            <!-- Category Filter -->
            <?php if (!empty($userCategories)): ?>
            <div class="mb-4">
                <label class="form-label fw-bold">Categories</label>
                <?php foreach ($userCategories as $category): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]"
                           value="<?php echo $category['id']; ?>"
                           id="archive-filter-category-<?php echo $category['id']; ?>"
                           <?php echo (isset($filters['categories']) && in_array($category['id'], $filters['categories'])) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="archive-filter-category-<?php echo $category['id']; ?>">
                        <span class="badge" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Archived Date Range -->
            <div class="mb-4">
                <label class="form-label fw-bold">Archived Date Range</label>
                <div class="mb-2">
                    <label for="archive-filter-date-from" class="form-label small">From</label>
                    <input type="date" class="form-control" name="archived_from"
                           id="archive-filter-date-from"
                           value="<?php echo htmlspecialchars($filters['archived_from'] ?? ''); ?>">
                </div>
                <div>
                    <label for="archive-filter-date-to" class="form-label small">To</label>
                    <input type="date" class="form-control" name="archived_to"
                           id="archive-filter-date-to"
                           value="<?php echo htmlspecialchars($filters['archived_to'] ?? ''); ?>">
                </div>
            </div>

            <!-- Apply/Clear Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Apply Filters
                </button>
                <a href="archive.php?clear_filters=1" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Clear All Filters
                </a>
            </div>
        </form>
    </div>
</div>
