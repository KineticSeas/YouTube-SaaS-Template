<?php
/**
 * Calendar Month API Endpoint
 * Returns month view HTML for HTMX requests
 * REQ-CAL-101 through REQ-CAL-105
 */

require_once '../../includes/auth-check.php';
require_once '../../includes/calendar-functions.php';

// Get current user ID
$userId = getCurrentUserId();

// Get parameters
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

// Validate month
if ($month < 1 || $month > 12) {
    $month = (int)date('n');
    $year = (int)date('Y');
}

// Get calendar data
$calendarData = getMonthCalendar($year, $month, $userId);

// Include component to render
include '../../components/calendar-month.php';
