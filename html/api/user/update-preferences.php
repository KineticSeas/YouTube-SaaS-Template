<?php
/**
 * API Endpoint: Update User Preferences
 * Updates user preferences for display, notifications, and tasks
 * Implements REQ-SET-201 through REQ-SET-205
 */

require_once '../../includes/auth-check.php';
require_once '../../includes/user-functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
    exit;
}

$userId = getCurrentUserId();

// Define valid preference keys and values
$validPreferences = [
    'theme' => ['light', 'dark'],
    'default_view' => ['dashboard', 'tasks', 'kanban', 'calendar'],
    'default_sort' => ['due_date', 'priority', 'created_date', 'title'],
    'tasks_per_page' => ['10', '20', '50', '100'],
    'date_format' => ['MM/DD/YYYY', 'DD/MM/YYYY', 'YYYY-MM-DD'],
    'time_format' => ['12h', '24h'],
    'email_notifications' => ['0', '1'],
    'daily_digest' => ['0', '1'],
    'daily_digest_time' => null, // Any time format HH:MM
    'due_reminders' => ['0', '1'],
    'reminder_timing' => ['24h', '48h', '1w'],
    'auto_archive' => ['0', '1'],
    'auto_archive_after' => null, // Any positive number
    'default_priority' => ['low', 'medium', 'high'],
    'default_status' => ['pending', 'in_progress'],
    'week_start' => ['sunday', 'monday']
];

// Build preferences from POST data
$preferencesToUpdate = [];

foreach ($validPreferences as $key => $allowedValues) {
    if (isset($_POST[$key])) {
        $value = $_POST[$key];

        // Validate the value
        if ($allowedValues === null) {
            // For fields with no restricted values (like time, number)
            if ($key === 'daily_digest_time') {
                // Validate time format HH:MM
                if (preg_match('/^\d{2}:\d{2}$/', $value)) {
                    $preferencesToUpdate[$key] = $value;
                }
            } elseif ($key === 'auto_archive_after') {
                // Validate positive integer
                if (is_numeric($value) && $value > 0 && $value <= 365) {
                    $preferencesToUpdate[$key] = (int)$value;
                }
            }
        } else {
            // For restricted values
            if (in_array($value, $allowedValues)) {
                $preferencesToUpdate[$key] = $value;
            }
        }
    } elseif (in_array($key, ['email_notifications', 'daily_digest', 'due_reminders', 'auto_archive'])) {
        // Checkboxes that might not be in POST if unchecked
        $preferencesToUpdate[$key] = '0';
    }
}

// Update preferences
$success = updateUserPreferences($userId, $preferencesToUpdate);

if ($success) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <strong>Preferences saved successfully!</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
} else {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Error!</strong> Failed to save preferences
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
}
