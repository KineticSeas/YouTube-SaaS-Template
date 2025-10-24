<?php
/**
 * User Logout Page
 * REQ-AUTH-402 & REQ-AUTH-403: Logout functionality
 */

require_once __DIR__ . '/../includes/session.php';

// Destroy session and clear all data
destroySession();

// Set success message
$_SESSION['success_message'] = 'You have been logged out successfully.';

// Redirect to login page
header('Location: /auth/login.php');
exit;
