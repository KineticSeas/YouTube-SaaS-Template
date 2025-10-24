<?php
/**
 * Login Processing Backend
 * Implements REQ-AUTH-101 through REQ-AUTH-106
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';

// Set JSON header for HTMX responses
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
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['remember_me']);

// Validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address.';
    echo renderResponse($response);
    exit;
}

if (empty($password)) {
    $response['message'] = 'Please enter your password.';
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
    // REQ-AUTH-104: Check for account lockout (5 failed attempts)
    $stmt = $conn->prepare("
        SELECT id, account_locked_until
        FROM users
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $lockCheck = $stmt->fetch();

    if ($lockCheck && $lockCheck['account_locked_until']) {
        $lockUntil = strtotime($lockCheck['account_locked_until']);
        if (time() < $lockUntil) {
            $minutesLeft = ceil(($lockUntil - time()) / 60);
            $response['message'] = "Account temporarily locked due to multiple failed login attempts. Please try again in {$minutesLeft} minutes.";
            echo renderResponse($response);
            exit;
        }
    }

    // Fetch user by email
    $stmt = $conn->prepare("
        SELECT id, email, password_hash, first_name, last_name, email_verified,
               is_active, failed_login_attempts
        FROM users
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // REQ-AUTH-105: Log login attempt
    $stmt = $conn->prepare("
        INSERT INTO login_attempts (email, ip_address, success)
        VALUES (?, ?, ?)
    ");
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (!$user) {
        // User not found
        $stmt->execute([$email, $ipAddress, 0]);
        $response['message'] = 'Invalid email or password.';
        echo renderResponse($response);
        exit;
    }

    // REQ-AUTH-101: Verify password
    if (!password_verify($password, $user['password_hash'])) {
        // Incorrect password
        $stmt->execute([$email, $ipAddress, 0]);

        // Increment failed login attempts
        $failedAttempts = $user['failed_login_attempts'] + 1;

        // REQ-AUTH-104: Lock account after 5 failed attempts for 15 minutes
        if ($failedAttempts >= 5) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $stmt = $conn->prepare("
                UPDATE users
                SET failed_login_attempts = ?, account_locked_until = ?
                WHERE id = ?
            ");
            $stmt->execute([$failedAttempts, $lockUntil, $user['id']]);

            $response['message'] = 'Account locked due to multiple failed login attempts. Please try again in 15 minutes.';
        } else {
            $stmt = $conn->prepare("
                UPDATE users
                SET failed_login_attempts = ?
                WHERE id = ?
            ");
            $stmt->execute([$failedAttempts, $user['id']]);

            $attemptsLeft = 5 - $failedAttempts;
            $response['message'] = "Invalid email or password. {$attemptsLeft} attempt(s) remaining.";
        }

        echo renderResponse($response);
        exit;
    }

    // Check if account is active
    if (!$user['is_active']) {
        $stmt->execute([$email, $ipAddress, 0]);
        $response['message'] = 'Your account has been deactivated. Please contact support.';
        echo renderResponse($response);
        exit;
    }

    // Login successful!
    $stmt->execute([$email, $ipAddress, 1]);

    // Reset failed login attempts and unlock account
    $stmt = $conn->prepare("
        UPDATE users
        SET failed_login_attempts = 0, account_locked_until = NULL
        WHERE id = ?
    ");
    $stmt->execute([$user['id']]);

    // REQ-AUTH-102 & REQ-AUTH-103: Create session with Remember Me
    if (createUserSession($user['id'], $rememberMe)) {
        $response['success'] = true;
        $response['message'] = 'Login successful! Redirecting...';

        // Check if there's a redirect URL stored
        $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard.php';
        unset($_SESSION['redirect_after_login']);

        $response['redirect'] = $redirectUrl;
    } else {
        $response['message'] = 'Failed to create session. Please try again.';
    }

    echo renderResponse($response);

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $response['message'] = 'An error occurred during login. Please try again.';
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
            $html .= 'setTimeout(function() { window.location.href = "' . htmlspecialchars($response['redirect']) . '"; }, 1000);';
            $html .= '</script>';
        }
    } else {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $html .= '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
        $html .= '<strong>Error!</strong> ' . htmlspecialchars($response['message']);

        if (!empty($response['errors'])) {
            $html .= '<ul class="mb-0 mt-2">';
            foreach ($response['errors'] as $error) {
                $html .= '<li>' . $error . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';
    }

    return $html;
}
