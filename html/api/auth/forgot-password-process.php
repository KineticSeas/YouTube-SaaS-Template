<?php
/**
 * Forgot Password Processing
 * Implements REQ-AUTH-201 through REQ-AUTH-202
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
$email = trim($_POST['email'] ?? '');

// Validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address.';
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
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, email, first_name, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // REQ-AUTH-201: Always show success message for security
    // (Don't reveal if email exists or not to prevent email enumeration)
    if (!$user || !$user['is_active']) {
        // Still show success message to prevent email enumeration
        $response['success'] = true;
        $response['message'] = 'If an account exists with that email, a password reset link has been sent.';
        echo renderResponse($response);
        exit;
    }

    // REQ-AUTH-202: Generate password reset token
    $resetToken = bin2hex(random_bytes(32));

    // REQ-AUTH-203: Token expires in 1 hour
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Delete any existing reset tokens for this user
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Insert new reset token
    $stmt = $conn->prepare("
        INSERT INTO password_resets (user_id, reset_token, expires_at)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user['id'], $resetToken, $expiresAt]);

    // Generate reset link
    $resetLink = getBaseUrl() . '/auth/reset-password.php?token=' . $resetToken;

    // Log reset link (in production, send via email)
    error_log("Password reset link for {$email}: {$resetLink}");

    // TODO: Send actual email in production
    // sendPasswordResetEmail($email, $user['first_name'], $resetLink);

    $response['success'] = true;
    $response['message'] = 'Password reset link has been sent to your email.';

    // For demo purposes, show the token in the session
    $_SESSION['info_message'] = 'For demo: Check server logs or use this token: ' . $resetToken;

    echo renderResponse($response);

} catch (PDOException $e) {
    error_log("Forgot password error: " . $e->getMessage());
    $response['message'] = 'An error occurred. Please try again later.';
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

        // Add info about checking email
        $html .= '<div class="alert alert-info mt-2" role="alert">';
        $html .= '<i class="bi bi-info-circle me-2"></i>';
        $html .= 'Please check your email inbox (and spam folder) for the reset link. The link will expire in 1 hour.';
        $html .= '</div>';
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

/**
 * Get base URL of the application
 *
 * @return string Base URL
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}

/**
 * Send password reset email (placeholder)
 *
 * @param string $email User email
 * @param string $name User name
 * @param string $link Reset link
 * @return bool Success status
 */
function sendPasswordResetEmail($email, $name, $link) {
    // TODO: Implement actual email sending

    $subject = "Reset Your TodoTracker Password";
    $message = "
        <html>
        <body>
            <h2>Password Reset Request</h2>
            <p>Hello {$name},</p>
            <p>We received a request to reset your password for your TodoTracker account.</p>
            <p>Click the link below to reset your password:</p>
            <p><a href='{$link}'>Reset My Password</a></p>
            <p>Or copy and paste this link into your browser:</p>
            <p>{$link}</p>
            <p><strong>This link will expire in 1 hour.</strong></p>
            <p>If you didn't request a password reset, please ignore this email and your password will remain unchanged.</p>
            <br>
            <p>Best regards,<br>The TodoTracker Team</p>
        </body>
        </html>
    ";

    error_log("Email would be sent to {$email}: {$subject}");
    return true;
}
