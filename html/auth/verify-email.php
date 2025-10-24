<?php
/**
 * Email Verification Page
 * REQ-AUTH-005: User account must be verified via email
 * Token expiry: 24 hours
 */

$pageTitle = 'Email Verification - TodoTracker';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$success = false;
$message = '';
$messageType = 'danger';

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = 'Invalid verification link. Please check your email for the correct link.';
} else {
    $db = getDatabase();
    $conn = $db->getConnection();

    if (!$conn) {
        $message = 'Database connection error. Please try again later.';
    } else {
        try {
            // Find user with this verification token
            $stmt = $conn->prepare("
                SELECT id, email, first_name, email_verified, created_at
                FROM users
                WHERE verification_token = ? AND is_active = 1
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if (!$user) {
                $message = 'Invalid or expired verification link. The token may have already been used.';
            } elseif ($user['email_verified']) {
                $message = 'Your email has already been verified. You can now login to your account.';
                $messageType = 'info';
                $success = true;
            } else {
                // Check if token has expired (24 hours)
                $createdAt = strtotime($user['created_at']);
                $expiryTime = $createdAt + (24 * 60 * 60); // 24 hours

                if (time() > $expiryTime) {
                    $message = 'This verification link has expired. Please request a new verification email.';
                } else {
                    // Verify the email
                    $stmt = $conn->prepare("
                        UPDATE users
                        SET email_verified = 1, verification_token = NULL
                        WHERE id = ?
                    ");
                    $stmt->execute([$user['id']]);

                    $success = true;
                    $messageType = 'success';
                    $message = 'Email verified successfully! You can now login to your account.';

                    // Set success message for login page
                    $_SESSION['success_message'] = 'Your email has been verified! Please login to continue.';
                }
            }
        } catch (PDOException $e) {
            error_log("Email verification error: " . $e->getMessage());
            $message = 'An error occurred during verification. Please try again.';
        }
    }
}
?>

<div id="verify-container" class="auth-container">
    <div id="verify-card" class="card auth-card">
        <!-- Card Header -->
        <div id="verify-header" class="card-header auth-header">
            <?php if ($success): ?>
                <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                <h2 class="mt-2">Email Verified!</h2>
            <?php else: ?>
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                <h2 class="mt-2">Verification Failed</h2>
            <?php endif; ?>
        </div>

        <!-- Card Body -->
        <div id="verify-body" class="card-body auth-body text-center">
            <div id="verify-message" class="alert alert-<?php echo $messageType; ?>" role="alert">
                <i class="bi bi-<?php echo $success ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>

            <?php if ($success): ?>
                <div id="verify-success-actions" class="mt-4">
                    <p class="mb-3">You're all set! Click the button below to login and start managing your tasks.</p>
                    <a id="verify-login-button" href="/auth/login.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login Now
                    </a>
                </div>
            <?php else: ?>
                <div id="verify-failure-actions" class="mt-4">
                    <p class="mb-3">Need help? Here are some options:</p>
                    <div id="verify-action-buttons" class="d-grid gap-2">
                        <a id="verify-resend-button" href="/auth/resend-verification.php" class="btn btn-outline-primary">
                            <i class="bi bi-envelope"></i> Resend Verification Email
                        </a>
                        <a id="verify-register-button" href="/auth/register.php" class="btn btn-outline-secondary">
                            <i class="bi bi-person-plus"></i> Create New Account
                        </a>
                        <a id="verify-login-button-alt" href="/auth/login.php" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-in-right"></i> Back to Login
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card Footer -->
        <div id="verify-footer" class="card-footer auth-footer">
            <p class="mb-0 text-muted">
                <i class="bi bi-info-circle"></i> Questions? <a href="/support.php">Contact Support</a>
            </p>
        </div>
    </div>
</div>

<?php if ($success): ?>
<script>
    // Auto-redirect to login after 5 seconds
    setTimeout(function() {
        window.location.href = '/auth/login.php';
    }, 5000);
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
