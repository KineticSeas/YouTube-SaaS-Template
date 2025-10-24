<?php
/**
 * Session Management System
 * Secure session handling for TodoTracker SaaS Application
 */

// Prevent direct access
if (!defined('SESSION_LOADED')) {
    define('SESSION_LOADED', true);
}

// Require database connection
require_once __DIR__ . '/../config/database.php';

/**
 * Initialize secure session configuration
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');

        // In production, enable this for HTTPS
        // ini_set('session.cookie_secure', 1);

        // Session name
        session_name('TODOTRACKER_SESSION');

        // Set session lifetime (24 hours)
        ini_set('session.gc_maxlifetime', 86400);

        session_start();

        // Regenerate session ID periodically to prevent fixation attacks
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regenerate session every 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Create user session after successful login
 *
 * @param int $userId User ID
 * @param bool $rememberMe Whether to create extended session
 * @return bool Success status
 */
function createUserSession($userId, $rememberMe = false) {
    $db = getDatabase();
    $conn = $db->getConnection();

    if (!$conn) {
        return false;
    }

    try {
        // Generate secure session token
        $sessionToken = bin2hex(random_bytes(32));

        // Get user information
        $stmt = $conn->prepare("SELECT id, email, first_name, last_name FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        // Calculate expiration
        $expiresAt = $rememberMe
            ? date('Y-m-d H:i:s', strtotime('+30 days'))
            : date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Store session in database
        $stmt = $conn->prepare("
            INSERT INTO sessions (user_id, session_token, ip_address, user_agent, remember_me, expires_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $sessionToken,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $rememberMe ? 1 : 0,
            $expiresAt
        ]);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['logged_in'] = true;

        // Update last login
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);

        // Set remember me cookie if requested
        if ($rememberMe) {
            setcookie('remember_token', $sessionToken, [
                'expires' => strtotime('+30 days'),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        return true;

    } catch (PDOException $e) {
        error_log("Session creation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user is logged in
 *
 * @return bool Login status
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Validate session against database
 *
 * @return bool Validation status
 */
function validateSession() {
    if (!isLoggedIn()) {
        return false;
    }

    $db = getDatabase();
    $conn = $db->getConnection();

    if (!$conn) {
        return false;
    }

    try {
        $stmt = $conn->prepare("
            SELECT s.id, s.expires_at, u.is_active, u.email_verified
            FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.session_token = ? AND s.user_id = ?
        ");

        $stmt->execute([
            $_SESSION['session_token'] ?? '',
            $_SESSION['user_id'] ?? 0
        ]);

        $session = $stmt->fetch();

        if (!$session) {
            return false;
        }

        // Check if session expired
        if (strtotime($session['expires_at']) < time()) {
            destroySession();
            return false;
        }

        // Check if user is active
        if (!$session['is_active']) {
            destroySession();
            return false;
        }

        return true;

    } catch (PDOException $e) {
        error_log("Session validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Destroy user session (logout)
 *
 * @return bool Success status
 */
function destroySession() {
    $db = getDatabase();
    $conn = $db->getConnection();

    // Remove session from database
    if ($conn && isset($_SESSION['session_token'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM sessions WHERE session_token = ?");
            $stmt->execute([$_SESSION['session_token']]);
        } catch (PDOException $e) {
            error_log("Session destruction error: " . $e->getMessage());
        }
    }

    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    // Clear session variables
    $_SESSION = [];

    // Destroy session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    return true;
}

/**
 * Get current user ID
 *
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user email
 *
 * @return string|null User email or null if not logged in
 */
function getCurrentUserEmail() {
    return $_SESSION['email'] ?? null;
}

/**
 * Get current user name
 *
 * @return string User full name or empty string
 */
function getCurrentUserName() {
    if (!isLoggedIn()) {
        return '';
    }

    return trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
}

/**
 * Generate CSRF token
 *
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 *
 * @param string $token Token to validate
 * @return bool Validation status
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Clean up expired sessions (call this periodically)
 *
 * @return int Number of sessions deleted
 */
function cleanupExpiredSessions() {
    $db = getDatabase();
    $conn = $db->getConnection();

    if (!$conn) {
        return 0;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Session cleanup error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Attempt to restore session from remember me cookie
 *
 * @return bool Success status
 */
function restoreRememberMeSession() {
    if (isLoggedIn()) {
        return true;
    }

    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }

    $db = getDatabase();
    $conn = $db->getConnection();

    if (!$conn) {
        return false;
    }

    try {
        $stmt = $conn->prepare("
            SELECT s.user_id, s.expires_at, u.email, u.first_name, u.last_name
            FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.session_token = ? AND s.remember_me = 1 AND u.is_active = 1
        ");

        $stmt->execute([$_COOKIE['remember_token']]);
        $session = $stmt->fetch();

        if (!$session) {
            return false;
        }

        // Check if session expired
        if (strtotime($session['expires_at']) < time()) {
            return false;
        }

        // Restore session
        $_SESSION['user_id'] = $session['user_id'];
        $_SESSION['email'] = $session['email'];
        $_SESSION['first_name'] = $session['first_name'];
        $_SESSION['last_name'] = $session['last_name'];
        $_SESSION['session_token'] = $_COOKIE['remember_token'];
        $_SESSION['logged_in'] = true;

        return true;

    } catch (PDOException $e) {
        error_log("Remember me restore error: " . $e->getMessage());
        return false;
    }
}

// Initialize session on include
initSession();

// Try to restore remember me session if not logged in
if (!isLoggedIn()) {
    restoreRememberMeSession();
}
