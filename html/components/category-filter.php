<?php
/**
 * Category Filter Component
 * Dropdown filter for filtering tasks by category
 * REQ-CAT-201 through REQ-CAT-203
 */

// Get user's categories with task count
$filterCategories = getCategoriesByUserId(getCurrentUserId(), true);
$selectedCategories = isset($_GET['categories']) ? (is_array($_GET['categories']) ? $_GET['categories'] : [$_GET['categories']]) : [];
?>

<div id="category-filter-container" class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 id="category-filter-title" class="mb-0">
                <i class="bi bi-funnel me-2"></i>Filter by Category
            </h6>
            <?php if (!empty($selectedCategories)): ?>
                <a href="?" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($filterCategories)): ?>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>
                No categories yet. <a href="/categories.php">Create categories</a> to filter your tasks.
            </p>
        <?php else: ?>
            <form id="category-filter-form" method="GET" action="">
                <div id="category-checkboxes" class="d-flex flex-column gap-2">
                    <?php foreach ($filterCategories as $category): ?>
                        <?php
                        $isChecked = in_array($category['id'], array_map('intval', $selectedCategories));
                        ?>
                        <div class="form-check">
                            <input class="form-check-input category-filter-checkbox"
                                   type="checkbox"
                                   name="categories[]"
                                   value="<?php echo $category['id']; ?>"
                                   id="category-filter-<?php echo $category['id']; ?>"
                                   <?php echo $isChecked ? 'checked' : ''; ?>
                                   onchange="document.getElementById('category-filter-form').submit()">
                            <label class="form-check-label d-flex align-items-center justify-content-between w-100"
                                   for="category-filter-<?php echo $category['id']; ?>">
                                <span class="badge rounded-pill"
                                      style="background-color: <?php echo htmlspecialchars($category['color']); ?>; color: #fff;">
                                    <i class="bi bi-tag-fill me-1"></i>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </span>
                                <span class="badge bg-light text-dark border ms-2">
                                    <?php echo $category['task_count']; ?>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Hidden fields to preserve other filters -->
                <?php if (isset($_GET['status'])): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status']); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['priority'])): ?>
                    <input type="hidden" name="priority" value="<?php echo htmlspecialchars($_GET['priority']); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                <?php endif; ?>
            </form>

            <?php if (!empty($selectedCategories)): ?>
                <div id="active-filters" class="mt-3 pt-3 border-top">
                    <small class="text-muted d-block mb-2">Active Filters:</small>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($filterCategories as $category): ?>
                            <?php if (in_array($category['id'], array_map('intval', $selectedCategories))): ?>
                                <span class="badge rounded-pill"
                                      style="background-color: <?php echo htmlspecialchars($category['color']); ?>; color: #fff; font-size: 0.85rem;">
                                    <i class="bi bi-tag-fill me-1"></i>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-submit form when checkbox changes (already handled by onchange attribute)
// Add smooth transition effects
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.category-filter-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Add loading indicator
            const container = document.getElementById('category-filter-container');
            if (container) {
                container.style.opacity = '0.6';
            }
        });
    });
});
</script>
