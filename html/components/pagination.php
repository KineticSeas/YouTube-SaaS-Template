<?php
/**
 * Pagination Component
 * Reusable pagination with page size selector
 * REQ-LIST-501 through REQ-LIST-504
 */

// Expected variables from parent:
// $page - current page number
// $totalPages - total number of pages
// $totalTasks - total number of tasks
// $pageSize - current page size
// $filters - array of active filters

if (!isset($page)) $page = 1;
if (!isset($totalPages)) $totalPages = 1;
if (!isset($totalTasks)) $totalTasks = 0;
if (!isset($pageSize)) $pageSize = 20;

// Calculate showing range
$showing_from = $totalTasks > 0 ? (($page - 1) * $pageSize) + 1 : 0;
$showing_to = min($page * $pageSize, $totalTasks);

// Build URL parameters
function buildPaginationUrl($newPage, $newPageSize = null) {
    global $pageSize;
    $params = $_GET;
    $params['page'] = $newPage;
    $params['page_size'] = $newPageSize ?? $pageSize;
    return '?' . http_build_query($params);
}

// Calculate page numbers to display (max 5)
$pageNumbers = [];
$maxPages = 5;

if ($totalPages <= $maxPages) {
    // Show all pages
    for ($i = 1; $i <= $totalPages; $i++) {
        $pageNumbers[] = $i;
    }
} else {
    // Show pages around current page
    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);

    // Adjust if we're at the beginning or end
    if ($page <= 3) {
        $end = min($totalPages, 5);
    } elseif ($page >= $totalPages - 2) {
        $start = max(1, $totalPages - 4);
    }

    for ($i = $start; $i <= $end; $i++) {
        $pageNumbers[] = $i;
    }
}
?>

<div id="pagination-component" class="card shadow-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <!-- Showing Info -->
            <div class="col-md-4 mb-3 mb-md-0">
                <div id="pagination-info">
                    <span class="text-muted">
                        Showing <strong><?php echo $showing_from; ?>-<?php echo $showing_to; ?></strong>
                        of <strong><?php echo $totalTasks; ?></strong> task<?php echo $totalTasks !== 1 ? 's' : ''; ?>
                    </span>
                </div>
            </div>

            <!-- Page Size Selector -->
            <div class="col-md-3 mb-3 mb-md-0">
                <div id="page-size-selector" class="input-group input-group-sm">
                    <label class="input-group-text" for="page-size-select">
                        <i class="bi bi-list-ol me-1"></i> Per page
                    </label>
                    <select class="form-select"
                            id="page-size-select"
                            onchange="changePageSize(this.value)">
                        <option value="10" <?php echo $pageSize === 10 ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo $pageSize === 20 ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo $pageSize === 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $pageSize === 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="col-md-5">
                <nav id="pagination-nav" aria-label="Task pagination">
                    <ul class="pagination pagination-sm mb-0 justify-content-end">
                        <!-- Previous Button -->
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="<?php echo $page > 1 ? buildPaginationUrl($page - 1) : '#'; ?>"
                               <?php echo $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        </li>

                        <!-- First Page -->
                        <?php if (!in_array(1, $pageNumbers)): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl(1); ?>">1</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php foreach ($pageNumbers as $pageNum): ?>
                        <li class="page-item <?php echo $pageNum === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrl($pageNum); ?>">
                                <?php echo $pageNum; ?>
                                <?php if ($pageNum === $page): ?>
                                    <span class="visually-hidden">(current)</span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>

                        <!-- Last Page -->
                        <?php if (!in_array($totalPages, $pageNumbers) && $totalPages > 0): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($totalPages); ?>">
                                <?php echo $totalPages; ?>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Next Button -->
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="<?php echo $page < $totalPages ? buildPaginationUrl($page + 1) : '#'; ?>"
                               <?php echo $page >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Quick Jump (Optional) -->
        <?php if ($totalPages > 10): ?>
        <div class="row mt-3">
            <div class="col-md-12 text-center">
                <div class="input-group input-group-sm d-inline-flex" style="width: auto;">
                    <span class="input-group-text">Jump to page</span>
                    <input type="number"
                           class="form-control"
                           id="jump-to-page"
                           min="1"
                           max="<?php echo $totalPages; ?>"
                           value="<?php echo $page; ?>"
                           style="width: 80px;">
                    <button class="btn btn-outline-secondary" type="button" onclick="jumpToPage()">
                        Go
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function changePageSize(newSize) {
    const url = new URL(window.location.href);
    url.searchParams.set('page_size', newSize);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}

function jumpToPage() {
    const pageInput = document.getElementById('jump-to-page');
    const pageNum = parseInt(pageInput.value);
    const maxPages = <?php echo $totalPages; ?>;

    if (pageNum >= 1 && pageNum <= maxPages) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', pageNum);
        window.location.href = url.toString();
    } else {
        alert('Please enter a valid page number between 1 and ' + maxPages);
    }
}
</script>
