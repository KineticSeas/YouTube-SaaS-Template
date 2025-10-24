<?php
/**
 * API Endpoint: Upload User Avatar
 * Handles avatar file upload with validation and image resizing
 * Implements REQ-AUTH-304
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

// Check if file was uploaded
if (!isset($_FILES['avatar_file'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Error!</strong> No file uploaded
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
}

// Upload the avatar
$uploadResult = uploadUserAvatar($userId, $_FILES['avatar_file']);

if ($uploadResult['success']) {
    // Return success response with new avatar URL as data attribute
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert" data-avatar-url="' . htmlspecialchars($uploadResult['avatar_url']) . '">
        <i class="bi bi-check-circle me-2"></i>
        <strong>Avatar uploaded successfully!</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
} else {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Error!</strong> ' . htmlspecialchars($uploadResult['error'] ?? 'Failed to upload avatar') . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
}
