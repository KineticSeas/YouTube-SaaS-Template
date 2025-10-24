<?php
/**
 * User Login Page
 * REQ-AUTH-101 through REQ-AUTH-106
 */

$pageTitle = 'Login - TodoTracker';
require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}
?>

<div id="login-container" class="auth-container">
    <div id="login-card" class="card auth-card">
        <!-- Card Header -->
        <div id="login-header" class="card-header auth-header">
            <i class="bi bi-box-arrow-in-right" style="font-size: 2.5rem;"></i>
            <h2 class="mt-2">Welcome Back</h2>
            <p class="mb-0">Login to access your tasks</p>
        </div>

        <!-- Card Body -->
        <div id="login-body" class="card-body auth-body">
            <form id="login-form"
                  action="/api/auth/login-process.php"
                  method="POST"
                  hx-post="/api/auth/login-process.php"
                  hx-target="#login-response"
                  hx-indicator="#login-spinner">

                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                <!-- Response Container -->
                <div id="login-response"></div>

                <!-- Email -->
                <div id="email-group" class="mb-3">
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

                <!-- Password -->
                <div id="password-group" class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <div id="password-input-group" class="input-group input-group-lg">
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               required
                               placeholder="Enter your password">
                        <button id="toggle-password" class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'toggle-password-icon')">
                            <i id="toggle-password-icon" class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="password-invalid" class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>

                <!-- Remember Me and Forgot Password -->
                <div id="remember-forgot-group" class="d-flex justify-content-between align-items-center mb-3">
                    <div id="remember-me-check" class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="remember-me"
                               name="remember_me"
                               value="1">
                        <label class="form-check-label" for="remember-me">
                            Remember me (30 days)
                        </label>
                    </div>
                    <a id="forgot-password-link" href="/auth/forgot-password.php" class="text-decoration-none">
                        <i class="bi bi-question-circle"></i> Forgot Password?
                    </a>
                </div>

                <!-- Submit Button -->
                <div id="submit-group" class="d-grid gap-2">
                    <button id="login-button" type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                        <span id="login-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>
                    </button>
                </div>

                <!-- Divider -->
                <div id="divider" class="divider mt-4">
                    <span>OR</span>
                </div>

                <!-- Demo Account Info -->
                <div id="demo-info" class="alert alert-info mt-3" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Demo Account:</strong><br>
                    <small>
                        Email: demo@todotracker.com<br>
                        Password: Demo123!
                    </small>
                </div>
            </form>
        </div>

        <!-- Card Footer -->
        <div id="login-footer" class="card-footer auth-footer">
            <p class="mb-0">
                Don't have an account?
                <a href="/auth/register.php" class="fw-bold">Register here</a>
            </p>
        </div>
    </div>
</div>

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
document.getElementById('login-form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    let isValid = true;

    // Validate email
    if (!TodoTracker.validateEmail(email)) {
        document.getElementById('email').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('email').classList.remove('is-invalid');
    }

    // Validate password
    if (password.length === 0) {
        document.getElementById('password').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('password').classList.remove('is-invalid');
    }

    if (!isValid) {
        e.preventDefault();
        TodoTracker.showToast('Please fill in all required fields.', 'error');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
