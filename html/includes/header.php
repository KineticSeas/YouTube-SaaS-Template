<?php
/**
 * Header - Top Navigation and HTML Opening
 * Includes session management, navbar, and layout structure
 */

// Start session if not already started
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/user-functions.php';

// Get user theme preference
$userTheme = 'light'; // Default theme
if (isLoggedIn()) {
    try {
        $userId = getCurrentUserId();
        $userPrefs = getUserPreferences($userId);
        if (isset($userPrefs['theme']) && in_array($userPrefs['theme'], ['light', 'dark'])) {
            $userTheme = $userPrefs['theme'];
        }
    } catch (Exception $e) {
        error_log("Error getting user theme preference: " . $e->getMessage());
        // Keep default theme if error occurs
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo htmlspecialchars($userTheme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TodoTracker - Professional task management and todo list application">
    <meta name="author" content="TodoTracker">
    <title><?php echo $pageTitle ?? 'TodoTracker - Task Management'; ?></title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <?php if (isset($pageTitle) && strpos($pageTitle, 'All Tasks') !== false): ?>
    <link rel="stylesheet" href="/assets/css/tasks-page.css">
    <?php endif; ?>
    <?php if (isset($pageTitle) && strpos($pageTitle, 'Calendar') !== false): ?>
    <link rel="stylesheet" href="/assets/css/calendar.css">
    <?php endif; ?>
    <?php if (isset($pageTitle) && strpos($pageTitle, 'Kanban') !== false): ?>
    <link rel="stylesheet" href="/assets/css/kanban.css">
    <?php endif; ?>

    <!-- Theme Switcher (no defer - must run early) -->
    <script src="/assets/js/theme.js"></script>

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@2.0.0" integrity="sha384-wS5l5IKJBvK6sPTKa2WZ1js3d947pvWXbPJ1OmWfEuxLgeHcEbjUUA5i9V5ZkpCw" crossorigin="anonymous"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <!-- Calendar JavaScript (must load after Alpine.js without defer) -->
    <?php if (isset($pageTitle) && strpos($pageTitle, 'Calendar') !== false): ?>
    <script src="/assets/js/calendar.js"></script>
    <?php endif; ?>

    <!-- Kanban JavaScript (must load after Alpine.js without defer) -->
    <?php if (isset($pageTitle) && strpos($pageTitle, 'Kanban') !== false): ?>
    <script src="/assets/js/kanban.js"></script>
    <?php endif; ?>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav id="main-navbar" class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div id="navbar-container" class="container-fluid">
            <!-- Brand/Logo -->
            <a id="navbar-brand" class="navbar-brand" href="/">
                <i class="bi bi-check-circle-fill me-2"></i>TodoTracker
            </a>

            <!-- Mobile Menu Toggle -->
            <button id="navbar-toggler" class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="navbarContent" class="collapse navbar-collapse">
                <?php if (isLoggedIn()): ?>
                    <!-- Search Bar (for logged-in users) -->
                    <form id="navbar-search-form" class="d-flex mx-auto" style="width: 50%;" action="/search.php" method="GET">
                        <input id="navbar-search-input" class="form-control" type="search" name="search" placeholder="Search tasks..." aria-label="Search">
                    </form>

                    <!-- Right Side Menu (Logged In) -->
                    <ul id="navbar-menu-loggedin" class="navbar-nav ms-auto">
                        <!-- Notifications -->
                        <li id="navbar-notifications" class="nav-item dropdown d-none">
                            <a id="navbar-notifications-link" class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    0
                                </span>
                            </a>
                            <ul id="navbar-notifications-menu" class="dropdown-menu dropdown-menu-end">
                                <li><h6 id="notifications-header" class="dropdown-header">Notifications</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a id="no-notifications" class="dropdown-item text-muted" href="#">No new notifications</a></li>
                            </ul>
                        </li>

                        <!-- User Dropdown -->
                        <li id="navbar-user-menu" class="nav-item dropdown">
                            <a id="navbar-user-link" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5"></i>
                                <span class="d-none d-lg-inline ms-1"><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
                            </a>
                            <ul id="navbar-user-dropdown" class="dropdown-menu dropdown-menu-end">
                                <li><a id="user-profile-link" class="dropdown-item" href="/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a id="user-logout-link" class="dropdown-item text-danger" href="/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                <?php else: ?>
                    <!-- Right Side Menu (Logged Out) -->
                    <ul id="navbar-menu-loggedout" class="navbar-nav ms-auto">
                        <li id="navbar-login" class="nav-item">
                            <a id="navbar-login-link" class="nav-link" href="/auth/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li id="navbar-register" class="nav-item">
                            <a id="navbar-register-link" class="btn btn-primary ms-2" href="/auth/register.php">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if (isLoggedIn()): ?>
    <!-- Layout Wrapper for Logged-in Users (Sidebar + Content) -->
    <div id="layout-wrapper" class="d-flex">
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div id="main-content" class="flex-fill">
    <?php else: ?>
        <!-- Main Content Area (No Sidebar for logged-out users) -->
        <div id="main-content-full" class="container" style="margin-top: 76px;">
    <?php endif; ?>

            <!-- Toast Container for Notifications -->
            <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 60px;">
                <?php
                // Display success message if exists
                if (isset($_SESSION['success_message'])) {
                    echo '<div id="success-toast" class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle me-2"></i>' . htmlspecialchars($_SESSION['success_message']) . '
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>';
                    unset($_SESSION['success_message']);
                }

                // Display error message if exists
                if (isset($_SESSION['error_message'])) {
                    echo '<div id="error-toast" class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-exclamation-triangle me-2"></i>' . htmlspecialchars($_SESSION['error_message']) . '
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>';
                    unset($_SESSION['error_message']);
                }

                // Display info message if exists
                if (isset($_SESSION['info_message'])) {
                    echo '<div id="info-toast" class="toast align-items-center text-white bg-info border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-info-circle me-2"></i>' . htmlspecialchars($_SESSION['info_message']) . '
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>';
                    unset($_SESSION['info_message']);
                }
                ?>
            </div>

            <!-- Page Content Starts Here -->
            <div id="page-content" class="p-4">
