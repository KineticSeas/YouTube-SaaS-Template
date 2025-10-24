<?php
/**
 * Registration Processing Backend
 * Implements REQ-AUTH-001 through REQ-AUTH-007
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

// Validate CSRF token (REQ-SEC-205)
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $response['message'] = 'Invalid security token. Please refresh the page and try again.';
    echo renderResponse($response);
    exit;
}

// Get and sanitize input
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$terms = isset($_POST['terms']);

// Validation

// REQ-AUTH-006: Validate required fields
if (empty($firstName)) {
    $response['errors'][] = 'First name is required.';
}

if (empty($lastName)) {
    $response['errors'][] = 'Last name is required.';
}

// REQ-AUTH-001: Validate email
if (empty($email)) {
    $response['errors'][] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['errors'][] = 'Please enter a valid email address.';
}

// REQ-AUTH-003: Validate password strength
if (empty($password)) {
    $response['errors'][] = 'Password is required.';
} else {
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

// Validate terms acceptance
if (!$terms) {
    $response['errors'][] = 'You must agree to the Terms of Service and Privacy Policy.';
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
    // REQ-AUTH-002: Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $response['message'] = 'An account with this email already exists.';
        $response['errors'][] = 'Please use a different email address or <a href="/auth/login.php">login to your existing account</a>.';
        echo renderResponse($response);
        exit;
    }

    // REQ-AUTH-206: Hash password with bcrypt
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user - Auto-verify email (email setup not configured)
    $stmt = $conn->prepare("
        INSERT INTO users (email, password_hash, first_name, last_name, email_verified, is_active)
        VALUES (?, ?, ?, ?, 1, 1)
    ");

    $stmt->execute([
        $email,
        $passwordHash,
        $firstName,
        $lastName
    ]);

    $userId = $conn->lastInsertId();

    // Email verification is not required - account is immediately active
    error_log("New user registered: {$email} (ID: {$userId})");

    $response['success'] = true;
    $response['message'] = 'Registration successful!';
    $response['redirect'] = '/auth/login.php';

    // Set success message for next page
    $_SESSION['success_message'] = 'Account created successfully! You can now log in with your credentials.';

    echo renderResponse($response);

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    $response['message'] = 'An error occurred during registration. Please try again.';
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
                $html .= '<li>' . $error . '</li>';
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
 * Send verification email (placeholder for actual implementation)
 *
 * @param string $email User email
 * @param string $name User name
 * @param string $link Verification link
 * @return bool Success status
 */
function sendVerificationEmail($email, $name, $link) {
    // TODO: Implement actual email sending using:
    // - PHPMailer
    // - SendGrid API
    // - AWS SES
    // - Or other email service

    $subject = "Verify Your TodoTracker Account";
    $message = "
        <html>
        <body>
            <h2>Welcome to TodoTracker, {$name}!</h2>
            <p>Thank you for registering. Please click the link below to verify your email address:</p>
            <p><a href='{$link}'>Verify My Email</a></p>
            <p>Or copy and paste this link into your browser:</p>
            <p>{$link}</p>
            <p>This link will expire in 24 hours.</p>
            <p>If you didn't create this account, please ignore this email.</p>
            <br>
            <p>Best regards,<br>The TodoTracker Team</p>
        </body>
        </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: TodoTracker <noreply@todotracker.com>" . "\r\n";

    // For production, use proper email service instead of mail()
    // return mail($email, $subject, $message, $headers);

    error_log("Email would be sent to {$email}: {$subject}");
    return true;
}
