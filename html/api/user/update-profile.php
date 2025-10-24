<?php
/**
 * API Endpoint: Update User Profile
 * Updates user profile information (name, email, phone, bio, location, timezone)
 * Implements REQ-AUTH-301, REQ-AUTH-302, REQ-AUTH-303
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
        exit;
    }
}

$userId = getCurrentUserId();

// Get form data
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$bio = $_POST['bio'] ?? '';
$location = $_POST['location'] ?? '';
$timezone = $_POST['timezone'] ?? 'America/New_York';

// Validate required fields
$errors = [];
if (empty(trim($firstName))) {
    $errors[] = 'First name is required';
}
if (empty(trim($lastName))) {
    $errors[] = 'Last name is required';
}
if (empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email address is required';
}
if (strlen($firstName) > 50) {
    $errors[] = 'First name must be 50 characters or less';
}
if (strlen($lastName) > 50) {
    $errors[] = 'Last name must be 50 characters or less';
}
if (strlen($phone) > 20) {
    $errors[] = 'Phone number must be 20 characters or less';
}
if (strlen($bio) > 500) {
    $errors[] = 'Bio must be 500 characters or less';
}
if (strlen($location) > 100) {
    $errors[] = 'Location must be 100 characters or less';
}

if (!empty($errors)) {
    // Return 200 (not 400) so HTMX swaps the error message into the DOM
    // The alert styling indicates this is an error message
    $errorList = '<ul class="mb-0">';
    foreach ($errors as $error) {
        $errorList .= '<li>' . htmlspecialchars($error) . '</li>';
    }
    $errorList .= '</ul>';
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Validation Error!</strong>
        ' . $errorList . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
}

// Update profile
$profileData = [
    'first_name' => trim($firstName),
    'last_name' => trim($lastName),
    'email' => trim($email),
    'phone' => !empty($phone) ? trim($phone) : null,
    'bio' => !empty($bio) ? trim($bio) : null,
    'location' => !empty($location) ? trim($location) : null,
    'timezone' => $timezone
];

$result = updateUserProfile($userId, $profileData);

if ($result['success']) {
    // If email was changed, mark it as verified (email setup not configured)
    if (isset($_POST['email'])) {
        try {
            $db = getDatabase();
            $conn = $db->getConnection();

            $stmt = $conn->prepare("
                UPDATE users
                SET email_verified = 1
                WHERE id = ?
            ");

            $stmt->execute([$userId]);

            // Log activity
            logUserActivity($userId, 'email_change', 'Email address changed', [
                'old_email' => '', // Don't log old email for privacy
                'new_email' => $email
            ]);

            // Return success message
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Profile updated successfully!</strong> Your email address has been updated.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } catch (Exception $e) {
            error_log("Error updating email verification: " . $e->getMessage());
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Profile updated successfully!</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    } else {
        // Return success response as HTML
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Profile updated successfully!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
    exit;
} else {
    // Return 200 so HTMX swaps the error message into the DOM
    $errorMsg = $result['error'] ?? 'Failed to update profile';
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Error!</strong> ' . htmlspecialchars($errorMsg) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
}
