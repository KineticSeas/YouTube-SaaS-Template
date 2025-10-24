<?php
/**
 * Reset Password Page
 * REQ-AUTH-203: Password reset link shall expire after 1 hour
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Reset Password - TodoTracker';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

// Get token from URL
$token = $_GET['token'] ?? '';
$validToken = false;
$errorMessage = '';

if (empty($token)) {
    $errorMessage = 'Invalid reset link. Please check your email for the correct link.';
} else {
    // Validate token
    $db = getDatabase();
    $conn = $db->getConnection();

    if (!$conn) {
        $errorMessage = 'Database connection error. Please try again later.';
    } else {
        try {
            $stmt = $conn->prepare("
                SELECT pr.id, pr.user_id, pr.expires_at, u.email, u.first_name
                FROM password_resets pr
                JOIN users u ON pr.user_id = u.id
                WHERE pr.reset_token = ? AND pr.used_at IS NULL AND u.is_active = 1
            ");
            $stmt->execute([$token]);
            $reset = $stmt->fetch();

            if (!$reset) {
                $errorMessage = 'Invalid or expired reset link. The link may have already been used.';
            } else if (strtotime($reset['expires_at']) < time()) {
                $errorMessage = 'This reset link has expired. Please request a new password reset.';
            } else {
                $validToken = true;
            }
        } catch (PDOException $e) {
            error_log("Reset password validation error: " . $e->getMessage());
            $errorMessage = 'An error occurred. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div id="reset-password-container" class="auth-container">
    <div id="reset-password-card" class="card auth-card">
        <!-- Card Header -->
        <div id="reset-password-header" class="card-header auth-header">
            <?php if ($validToken): ?>
                <i class="bi bi-key-fill" style="font-size: 2.5rem;"></i>
                <h2 class="mt-2">Reset Your Password</h2>
                <p class="mb-0">Enter your new password below</p>
            <?php else: ?>
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 2.5rem;"></i>
                <h2 class="mt-2">Invalid Reset Link</h2>
            <?php endif; ?>
        </div>

        <!-- Card Body -->
        <div id="reset-password-body" class="card-body auth-body">
            <?php if (!$validToken): ?>
                <div id="error-message" class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>

                <div id="error-actions" class="d-grid gap-2">
                    <a id="request-new-link" href="/auth/forgot-password.php" class="btn btn-primary">
                        <i class="bi bi-envelope"></i> Request New Reset Link
                    </a>
                    <a id="back-to-login" href="/auth/login.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Login
                    </a>
                </div>
            <?php else: ?>
                <form id="reset-password-form"
                      action="/api/auth/reset-password-process.php"
                      method="POST"
                      hx-post="/api/auth/reset-password-process.php"
                      hx-target="#reset-password-response"
                      hx-indicator="#reset-password-spinner">

                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <!-- Response Container -->
                    <div id="reset-password-response"></div>

                    <!-- New Password -->
                    <div id="password-group" class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> New Password <span class="text-danger">*</span>
                        </label>
                        <div id="password-input-group" class="input-group">
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   required
                                   minlength="8"
                                   data-strength="password-strength-bar"
                                   placeholder="Create a strong password">
                            <button id="toggle-password" class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'toggle-password-icon')">
                                <i id="toggle-password-icon" class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="password-strength" class="password-strength">
                            <div id="password-strength-bar" class="password-strength-bar"></div>
                        </div>
                        <div id="password-help" class="form-text">
                            Must be at least 8 characters with uppercase, lowercase, and number.
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div id="confirm-password-group" class="mb-4">
                        <label for="confirm-password" class="form-label">
                            <i class="bi bi-lock-fill"></i> Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div id="confirm-password-input-group" class="input-group">
                            <input type="password"
                                   class="form-control"
                                   id="confirm-password"
                                   name="confirm_password"
                                   required
                                   placeholder="Re-enter your password">
                            <button id="toggle-confirm-password" class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('confirm-password', 'toggle-confirm-password-icon')">
                                <i id="toggle-confirm-password-icon" class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="confirm-password-invalid" class="invalid-feedback">
                            Passwords do not match.
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div id="submit-group" class="d-grid gap-2">
                        <button id="reset-password-button" type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Reset Password
                            <span id="reset-password-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- Card Footer -->
        <div id="reset-password-footer" class="card-footer auth-footer">
            <p class="mb-0">
                Remember your password?
                <a href="/auth/login.php" class="fw-bold">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php if ($validToken): ?>
<script>
// Toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Form validation
document.getElementById('reset-password-form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    let isValid = true;

    // Validate password strength
    const passwordStrength = TodoTracker.checkPasswordStrength(password);
    if (passwordStrength.strength === 'weak') {
        document.getElementById('password').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('password').classList.remove('is-invalid');
    }

    // Check password match
    if (password !== confirmPassword) {
        document.getElementById('confirm-password').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('confirm-password').classList.remove('is-invalid');
    }

    if (!isValid) {
        e.preventDefault();
        TodoTracker.showToast('Please fix the errors in the form.', 'error');
    }
});

// Real-time password match validation
document.getElementById('confirm-password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;

    if (confirmPassword && password !== confirmPassword) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
