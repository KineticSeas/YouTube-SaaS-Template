<?php
/**
 * API Endpoint: Delete User Account
 * Soft-deletes user account with 30-day grace period
 * Implements REQ-AUTH-305
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
$password = $_POST['delete_password'] ?? '';
$confirmText = $_POST['delete_text'] ?? '';
$confirmation = $_POST['delete_confirmation'] ?? '0';

// Validate confirmation
$errors = [];
if ($confirmation !== '1') {
    $errors[] = 'You must confirm you understand the consequences';
}
if (empty($password)) {
    $errors[] = 'Password is required';
}
if ($confirmText !== 'DELETE MY ACCOUNT') {
    $errors[] = 'You must type exactly "DELETE MY ACCOUNT"';
}

if (!empty($errors)) {
    // Return 200 so HTMX swaps the error message into the DOM
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

try {
    $db = getDatabase();
    $conn = $db->getConnection();

    // Verify password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ? AND account_status = 'active'");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Error!</strong> User not found
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Error!</strong> Password is incorrect
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        exit;
    }

    // Mark account for deletion (soft delete with 30-day grace period)
    $deletedAt = date('Y-m-d H:i:s');
    $updateStmt = $conn->prepare("
        UPDATE users
        SET account_status = 'pending_deletion',
            deleted_at = ?
        WHERE id = ?
    ");
    $updateStmt->execute([$deletedAt, $userId]);

    // Log activity
    logUserActivity($userId, 'security_event', 'Account deletion initiated', [
        'deletion_date' => $deletedAt,
        'grace_period_days' => 30
    ]);

    // Clear user's sessions to log out everywhere
    $sessionStmt = $conn->prepare("DELETE FROM sessions WHERE user_id = ?");
    $sessionStmt->execute([$userId]);

    // Return success response with redirect instruction in data attribute
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert" data-redirect="/auth/logout.php">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Account Deletion Initiated!</strong> Your account will be permanently deleted in 30 days. You will now be logged out.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
} catch (PDOException $e) {
    error_log("Error deleting account: " . $e->getMessage());
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Error!</strong> Failed to delete account
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
}
