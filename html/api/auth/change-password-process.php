<?php
/**
 * Change Password Processing
 * Implements REQ-AUTH-204 through REQ-AUTH-206
 */

require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: text/html; charset=utf-8');

$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo renderResponse($response);
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $response['message'] = 'Invalid security token.';
    echo renderResponse($response);
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
if (empty($currentPassword)) {
    $response['errors'][] = 'Current password is required.';
}

if (empty($newPassword)) {
    $response['errors'][] = 'New password is required.';
} else {
    if (strlen($newPassword) < 8) $response['errors'][] = 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $newPassword)) $response['errors'][] = 'Password must contain uppercase letter.';
    if (!preg_match('/[a-z]/', $newPassword)) $response['errors'][] = 'Password must contain lowercase letter.';
    if (!preg_match('/[0-9]/', $newPassword)) $response['errors'][] = 'Password must contain a number.';
}

if ($newPassword !== $confirmPassword) {
    $response['errors'][] = 'Passwords do not match.';
}

if ($newPassword === $currentPassword) {
    $response['errors'][] = 'New password must be different from current password.';
}

if (!empty($response['errors'])) {
    $response['message'] = 'Please correct the errors:';
    echo renderResponse($response);
    exit;
}

$db = getDatabase();
$conn = $db->getConnection();

if (!$conn) {
    $response['message'] = 'Database error.';
    echo renderResponse($response);
    exit;
}

try {
    $userId = getCurrentUserId();

    // REQ-AUTH-205: Verify current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        $response['message'] = 'Current password is incorrect.';
        echo renderResponse($response);
        exit;
    }

    // REQ-AUTH-206: Hash new password with bcrypt
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newPasswordHash, $userId]);

    $response['success'] = true;
    $response['message'] = 'Password updated successfully!';

    $_SESSION['success_message'] = 'Your password has been changed successfully.';

    echo renderResponse($response);

} catch (PDOException $e) {
    error_log("Change password error: " . $e->getMessage());
    $response['message'] = 'An error occurred.';
    echo renderResponse($response);
}

function renderResponse($response) {
    $html = '';

    if ($response['success']) {
        $html .= '<div class="alert alert-success alert-dismissible fade show">';
        $html .= '<i class="bi bi-check-circle-fill me-2"></i>';
        $html .= '<strong>Success!</strong> ' . htmlspecialchars($response['message']);
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    } else {
        $html .= '<div class="alert alert-danger alert-dismissible fade show">';
        $html .= '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
        $html .= '<strong>Error!</strong> ' . htmlspecialchars($response['message']);

        if (!empty($response['errors'])) {
            $html .= '<ul class="mb-0 mt-2">';
            foreach ($response['errors'] as $error) {
                $html .= '<li>' . htmlspecialchars($error) . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }

    return $html;
}
