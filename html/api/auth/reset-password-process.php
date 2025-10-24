<?php
/**
 * Reset Password Processing
 * Implements REQ-AUTH-203: Password reset with token validation
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';

// Set HTML header for HTMX responses
header('Content-Type: text/html; charset=utf-8');

// Initialize response
$response = ['success' => false, 'message' => '', 'errors' => []];

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo renderResponse($response);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $response['message'] = 'Invalid security token. Please refresh the page and try again.';
    echo renderResponse($response);
    exit;
}

// Get and sanitize input
$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
if (empty($token)) {
    $response['message'] = 'Invalid reset token.';
    echo renderResponse($response);
    exit;
}

// Validate password
if (empty($password)) {
    $response['errors'][] = 'Password is required.';
}

else {
    if (strlen($password) < 8) {
        $response['errors'][] = 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $response['errors'][] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $response['errors'][] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $response['errors'][] = 'Password must contain at least one number.';
    }
}

// Validate password confirmation
if ($password !== $confirmPassword) {
    $response['errors'][] = 'Passwords do not match.';
}

// If validation errors exist, return them
if (!empty($response['errors'])) {
    $response['message'] = 'Please correct the following errors:';
    echo renderResponse($response);
    exit;
}

// Database operations
$db = getDatabase();
$conn = $db->getConnection();

if (!$conn) {
    $response['message'] = 'Database connection error. Please try again later.';
    echo renderResponse($response);
    exit;
}

try {
    // Validate reset token
    $stmt = $conn->prepare("
        SELECT pr.id, pr.user_id, pr.expires_at, u.email
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.reset_token = ? AND pr.used_at IS NULL AND u.is_active = 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $response['message'] = 'Invalid or expired reset link.';
        echo renderResponse($response);
        exit;
    }

    // REQ-AUTH-203: Check if token expired (1 hour)
    if (strtotime($reset['expires_at']) < time()) {
        $response['message'] = 'This reset link has expired. Please request a new one.';
        echo renderResponse($response);
        exit;
    }

    // Hash new password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password_hash = ?, failed_login_attempts = 0, account_locked_until = NULL WHERE id = ?");
    $stmt->execute([$passwordHash, $reset['user_id']]);

    // Mark reset token as used
    $stmt = $conn->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
    $stmt->execute([$reset['id']]);

    $response['success'] = true;
    $response['message'] = 'Password reset successful!';
    $response['redirect'] = '/auth/login.php';

    // Set success message for login page
    $_SESSION['success_message'] = 'Your password has been reset successfully. Please login with your new password.';

    echo renderResponse($response);

} catch (PDOException $e) {
    error_log("Reset password error: " . $e->getMessage());
    $response['message'] = 'An error occurred. Please try again.';
    echo renderResponse($response);
}

/**
 * Render HTML response for HTMX
 *
 * @param array $response Response data
 * @return string HTML output
 */
function renderResponse($response) {
    $html = '';

    if ($response['success']) {
        $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        $html .= '<i class="bi bi-check-circle-fill me-2"></i>';
        $html .= '<strong>Success!</strong> ' . htmlspecialchars($response['message']);
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';

        // Add redirect script
        if (!empty($response['redirect'])) {
            $html .= '<script>';
            $html .= 'setTimeout(function() { window.location.href = "' . htmlspecialchars($response['redirect']) . '"; }, 2000);';
            $html .= '</script>';
        }
    } else {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $html .= '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
        $html .= '<strong>Error!</strong> ' . htmlspecialchars($response['message']);

        if (!empty($response['errors'])) {
            $html .= '<ul class="mb-0 mt-2">';
            foreach ($response['errors'] as $error) {
                $html .= '<li>' . htmlspecialchars($error) . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';
    }

    return $html;
}
