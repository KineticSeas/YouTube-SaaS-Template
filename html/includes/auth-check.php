<?php
/**
 * Authentication Check Middleware
 * Include this file at the top of protected pages to ensure user is logged in
 */

// Require session management
require_once __DIR__ . '/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Store the requested page to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';

    // Redirect to login page
    header('Location: /auth/login.php');
    exit;
}

// Validate session against database
if (!validateSession()) {
    // Session is invalid, destroy it and redirect to login
    destroySession();
    $_SESSION['error_message'] = 'Your session has expired. Please log in again.';
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';

    header('Location: /auth/login.php');
    exit;
}

// Clean up old sessions (run occasionally, not on every request)
// Only run cleanup 1% of the time to reduce database load
if (rand(1, 100) === 1) {
    cleanupExpiredSessions();
}
