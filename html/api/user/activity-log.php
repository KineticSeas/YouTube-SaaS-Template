<?php
/**
 * API Endpoint: Get User Activity Log
 * Retrieves user activity history with pagination and filtering
 */

require_once '../../includes/auth-check.php';
require_once '../../includes/user-functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = getCurrentUserId();

// Get query parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$filter = isset($_GET['filter']) ? $_GET['filter'] : null;

// Validate limits
$limit = min($limit, 100); // Max 100 per request
$limit = max($limit, 10);  // Min 10 per request
$offset = max($offset, 0);

// Get activity log
$activityLog = getUserActivityLog($userId, $limit, $offset, $filter);

// Return activity log as HTML fragments (for HTMX)
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    // JSON format
    echo json_encode([
        'success' => true,
        'data' => $activityLog,
        'count' => count($activityLog)
    ]);
    exit;
} else {
    // HTML fragments for HTMX appending
    if (empty($activityLog)) {
        // No more items
        echo '<div class="alert alert-info">No more activity records</div>';
        exit;
    }

    // Render activity items
    foreach ($activityLog as $activity) {
        echo '<div id="activity-item-' . $activity['id'] . '" class="list-group-item p-3 border-bottom">';
        echo '<div class="d-flex justify-content-between align-items-start">';

        // Activity Icon & Description
        echo '<div id="activity-content-' . $activity['id'] . '" class="flex-grow-1">';
        echo '<div id="activity-description-' . $activity['id'] . '" class="fw-bold">';

        switch ($activity['activity_type']) {
            case 'login':
                echo '<i class="bi bi-box-arrow-in-right me-2 text-info"></i>User Logged In';
                break;
            case 'logout':
                echo '<i class="bi bi-box-arrow-right me-2 text-secondary"></i>User Logged Out';
                break;
            case 'profile_update':
                echo '<i class="bi bi-person-check me-2 text-primary"></i>Profile Updated';
                break;
            case 'password_change':
                echo '<i class="bi bi-key me-2 text-warning"></i>Password Changed';
                break;
            case 'email_change':
                echo '<i class="bi bi-envelope me-2 text-primary"></i>Email Changed';
                break;
            case 'security_event':
                echo '<i class="bi bi-shield-exclamation me-2 text-danger"></i>Security Event';
                break;
            case 'preference_change':
                echo '<i class="bi bi-sliders me-2 text-secondary"></i>Preferences Updated';
                break;
            default:
                echo '<i class="bi bi-clock me-2"></i>' . htmlspecialchars($activity['activity_type']);
        }

        echo '</div>';
        echo '<small id="activity-details-' . $activity['id'] . '" class="text-muted">';
        echo htmlspecialchars($activity['description'] ?? '');
        echo '</small>';
        echo '<div id="activity-metadata-' . $activity['id'] . '" class="mt-2 small text-muted">';

        if ($activity['ip_address']) {
            echo '<span id="activity-ip-' . $activity['id'] . '" class="d-inline-block me-3">';
            echo '<i class="bi bi-globe me-1"></i>IP: ' . htmlspecialchars($activity['ip_address']);
            echo '</span>';
        }

        if ($activity['metadata']) {
            $metadata = json_decode($activity['metadata'], true);
            if (isset($metadata['user_agent'])) {
                echo '<span id="activity-device-' . $activity['id'] . '" class="d-inline-block">';
                echo '<i class="bi bi-device-ssd me-1"></i>Device: ' . htmlspecialchars(substr($metadata['user_agent'], 0, 50)) . '...';
                echo '</span>';
            }
        }

        echo '</div>';
        echo '</div>';

        // Timestamp
        echo '<small id="activity-time-' . $activity['id'] . '" class="text-muted text-end ms-3 flex-shrink-0">';
        echo '<div>' . date('M d', strtotime($activity['created_at'])) . '</div>';
        echo '<div>' . date('H:i:s', strtotime($activity['created_at'])) . '</div>';
        echo '</small>';

        echo '</div>';
        echo '</div>';
    }

    exit;
}
