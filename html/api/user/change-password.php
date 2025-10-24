<?php
/**
 * API Endpoint: Change User Password
 * Validates current password and updates with new secure password
 * Implements REQ-AUTH-204, REQ-AUTH-205, REQ-AUTH-206
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

// Get form data
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate required fields
$errors = [];
if (empty($currentPassword)) {
    $errors[] = 'Current password is required';
}
if (empty($newPassword)) {
    $errors[] = 'New password is required';
}
if (empty($confirmPassword)) {
    $errors[] = 'Password confirmation is required';
}

// Validate new password strength
// Check: min 8 chars, uppercase, lowercase, number, and special char
$hasUppercase = preg_match('/[A-Z]/', $newPassword);
$hasLowercase = preg_match('/[a-z]/', $newPassword);
$hasNumber = preg_match('/[0-9]/', $newPassword);
$hasSpecial = preg_match('/[!@#$%^&*()_+=\-\[\]{};:\'",.<>?\/?\\|`~]/', $newPassword);
$isLongEnough = strlen($newPassword) >= 8;

if (!empty($newPassword) && (!$hasUppercase || !$hasLowercase || !$hasNumber || !$hasSpecial || !$isLongEnough)) {
    $missing = [];
    if (!$isLongEnough) $missing[] = 'at least 8 characters';
    if (!$hasUppercase) $missing[] = 'an uppercase letter (A-Z)';
    if (!$hasLowercase) $missing[] = 'a lowercase letter (a-z)';
    if (!$hasNumber) $missing[] = 'a number (0-9)';
    if (!$hasSpecial) $missing[] = 'a special character (!@#$%^&*...)';

    $errors[] = 'Password must contain: ' . implode(', ', $missing);
}

// Check if passwords match
if (!empty($newPassword) && !empty($confirmPassword) && $newPassword !== $confirmPassword) {
    $errors[] = 'New password and confirmation do not match. Please ensure both fields are identical.';
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

// Verify current password
try {
    $db = getDatabase();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ? AND account_status = 'active'");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Return 200 so HTMX swaps the error message into the DOM
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Error!</strong> User not found
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        exit;
    }

    // Verify password
    if (!password_verify($currentPassword, $user['password_hash'])) {
        // Return 200 so HTMX swaps the error message into the DOM
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Incorrect Current Password</strong><br>
            <small>Please double-check your current password and try again. Passwords are case-sensitive.</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        exit;
    }

    // Hash new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $updateStmt = $conn->prepare("
        UPDATE users
        SET password_hash = ?
        WHERE id = ?
    ");
    $updateStmt->execute([$newPasswordHash, $userId]);

    // Log activity
    logUserActivity($userId, 'password_change', 'Password changed successfully', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    // Return success response
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <strong>Password changed successfully!</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
} catch (PDOException $e) {
    error_log("Error changing password: " . $e->getMessage());
    // Return 200 so HTMX swaps the error message into the DOM
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Error!</strong> Failed to change password: ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
} catch (Exception $e) {
    error_log("Unexpected error changing password: " . $e->getMessage());
    // Return 200 so HTMX swaps the error message into the DOM
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Error!</strong> ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
}
