<?php
/**
 * Forgot Password Page
 * REQ-AUTH-201: System shall provide "Forgot Password" functionality
 */

$pageTitle = 'Forgot Password - TodoTracker';
require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}
?>

<div id="forgot-password-container" class="auth-container">
    <div id="forgot-password-card" class="card auth-card">
        <!-- Card Header -->
        <div id="forgot-password-header" class="card-header auth-header">
            <i class="bi bi-key-fill" style="font-size: 2.5rem;"></i>
            <h2 class="mt-2">Forgot Password?</h2>
            <p class="mb-0">Enter your email to reset your password</p>
        </div>

        <!-- Card Body -->
        <div id="forgot-password-body" class="card-body auth-body">
            <p id="instructions" class="text-muted mb-4">
                <i class="bi bi-info-circle"></i>
                Enter your email address and we'll send you a link to reset your password.
                The link will expire in 1 hour.
            </p>

            <form id="forgot-password-form"
                  action="/api/auth/forgot-password-process.php"
                  method="POST"
                  hx-post="/api/auth/forgot-password-process.php"
                  hx-target="#forgot-password-response"
                  hx-indicator="#forgot-password-spinner">

                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                <!-- Response Container -->
                <div id="forgot-password-response"></div>

                <!-- Email -->
                <div id="email-group" class="mb-4">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email"
                           class="form-control form-control-lg"
                           id="email"
                           name="email"
                           required
                           placeholder="your.email@example.com"
                           autofocus>
                    <div id="email-invalid" class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>

                <!-- Submit Button -->
                <div id="submit-group" class="d-grid gap-2">
                    <button id="forgot-password-button" type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-envelope"></i> Send Reset Link
                        <span id="forgot-password-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>
                    </button>
                </div>

                <!-- Back to Login Link -->
                <div id="back-to-login" class="text-center mt-4">
                    <a href="/auth/login.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>

        <!-- Card Footer -->
        <div id="forgot-password-footer" class="card-footer auth-footer">
            <p class="mb-0">
                Don't have an account?
                <a href="/auth/register.php" class="fw-bold">Register here</a>
            </p>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;

    if (!TodoTracker.validateEmail(email)) {
        e.preventDefault();
        document.getElementById('email').classList.add('is-invalid');
        TodoTracker.showToast('Please enter a valid email address.', 'error');
    } else {
        document.getElementById('email').classList.remove('is-invalid');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
