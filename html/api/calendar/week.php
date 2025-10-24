<?php
/**
 * Calendar Week API Endpoint
 * Returns week view HTML for HTMX requests
 * REQ-CAL-201 through REQ-CAL-204
 */

require_once '../../includes/auth-check.php';
require_once '../../includes/calendar-functions.php';

// Get current user ID
$userId = getCurrentUserId();

// Get parameters
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

// Get week start (Sunday)
$weekStart = getWeekStart($date);

// Get calendar data
$calendarData = getWeekCalendar($weekStart, $userId);

// Include component to render
include '../../components/calendar-week.php';
