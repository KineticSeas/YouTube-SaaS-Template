<?php
/**
 * Sidebar Navigation
 * Left sidebar menu for authenticated users
 */

// Get current page for active state highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar Wrapper -->
<div id="sidebar-wrapper">
    <!-- Sidebar Toggle Button (Mobile) -->
    <button id="sidebar-toggle-mobile" class="btn btn-primary d-lg-none position-fixed" style="top: 70px; left: 10px; z-index: 1040;">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="sidebar border-end">
        <div id="sidebar-content" class="d-flex flex-column h-100">
            <!-- Main Navigation -->
            <nav id="sidebar-nav" class="flex-grow-1">
                <div id="sidebar-menu" class="list-group list-group-flush">
                    <!-- Dashboard -->
                    <a id="sidebar-dashboard"
                       href="/dashboard.php"
                       class="list-group-item list-group-item-action <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>

                    <!-- All Tasks -->
                    <a id="sidebar-tasks"
                       href="/tasks.php"
                       class="list-group-item list-group-item-action <?php echo ($currentPage === 'tasks.php') ? 'active' : ''; ?>">
                        <i class="bi bi-list-task me-2"></i>All Tasks
                    </a>

                    <!-- Kanban Board -->
                    <a id="sidebar-kanban"
                       href="/kanban.php"
                       class="list-group-item list-group-item-action <?php echo ($currentPage === 'kanban.php') ? 'active' : ''; ?>">
                        <i class="bi bi-columns-gap me-2"></i>Kanban Board
                    </a>

                    <!-- Calendar -->
                    <a id="sidebar-calendar"
                       href="/calendar.php"
                       class="list-group-item list-group-item-action <?php echo ($currentPage === 'calendar.php') ? 'active' : ''; ?>">
                        <i class="bi bi-calendar3 me-2"></i>Calendar
                    </a>

                    <!-- Categories -->
                    <a id="sidebar-categories"
                       href="/categories.php"
                       class="list-group-item list-group-item-action <?php echo ($currentPage === 'categories.php') ? 'active' : ''; ?>">
                        <i class="bi bi-tags me-2"></i>Categories
                    </a>

                    <!-- Archive -->
                    <a id="sidebar-archive"
                       href="/archive.php"
                       class="list-group-item list-group-item-action <?php echo ($currentPage === 'archive.php') ? 'active' : ''; ?>">
                        <i class="bi bi-archive me-2"></i>Archive
                    </a>

                    <!-- Trash -->
                    <a id="sidebar-trash"
                       href="/trash.php"
                       class="list-group-item list-group-item-action <?php echo ($currentPage === 'trash.php') ? 'active' : ''; ?>">
                        <i class="bi bi-trash me-2"></i>Trash
                        <?php
                        // Show badge with trash count if not empty
                        if (function_exists('getTrashedTasks')) {
                            $trashCount = count(getTrashedTasks(getCurrentUserId(), []));
                            if ($trashCount > 0) {
                                echo '<span class="badge bg-danger ms-auto">' . $trashCount . '</span>';
                            }
                        }
                        ?>
                    </a>

                    <!-- Divider -->
                    <div class="dropdown-divider my-2"></div>

                    <!-- Profile -->
                    <a id="sidebar-profile"
                       href="/profile.php"
                       class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['REQUEST_URI'], '/profile.php') !== false) ? 'active' : ''; ?>">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer (Optional) -->
            <div id="sidebar-footer" class="p-3 border-top">
                <div id="sidebar-user-info" class="d-flex align-items-center">
                    <div id="sidebar-user-avatar" class="me-2">
                        <i class="bi bi-person-circle fs-3 text-primary"></i>
                    </div>
                    <div id="sidebar-user-details" class="flex-grow-1">
                        <div id="sidebar-user-name" class="fw-bold small text-truncate" style="max-width: 150px;">
                            <?php echo htmlspecialchars(getCurrentUserName()); ?>
                        </div>
                        <div id="sidebar-user-email" class="text-muted small text-truncate" style="max-width: 150px;">
                            <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>
