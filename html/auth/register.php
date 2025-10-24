<?php
/**
 * User Registration Page
 * REQ-AUTH-001 through REQ-AUTH-007
 */

$pageTitle = 'Register - TodoTracker';
require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}
?>

<div id="register-container" class="auth-container">
    <div id="register-card" class="card auth-card">
        <!-- Card Header -->
        <div id="register-header" class="card-header auth-header">
            <i class="bi bi-person-plus-fill" style="font-size: 2.5rem;"></i>
            <h2 class="mt-2">Create Your Account</h2>
            <p class="mb-0">Join TodoTracker to start managing your tasks</p>
        </div>

        <!-- Card Body -->
        <div id="register-body" class="card-body auth-body">
            <form id="register-form"
                  action="/api/auth/register-process.php"
                  method="POST"
                  hx-post="/api/auth/register-process.php"
                  hx-target="#register-response"
                  hx-indicator="#register-spinner">

                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                <!-- Response Container -->
                <div id="register-response"></div>

                <!-- First Name -->
                <div id="first-name-group" class="mb-3">
                    <label for="first-name" class="form-label">
                        <i class="bi bi-person"></i> First Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="first-name"
                           name="first_name"
                           required
                           maxlength="100"
                           placeholder="Enter your first name">
                    <div id="first-name-invalid" class="invalid-feedback">
                        Please enter your first name.
                    </div>
                </div>

                <!-- Last Name -->
                <div id="last-name-group" class="mb-3">
                    <label for="last-name" class="form-label">
                        <i class="bi bi-person"></i> Last Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="last-name"
                           name="last_name"
                           required
                           maxlength="100"
                           placeholder="Enter your last name">
                    <div id="last-name-invalid" class="invalid-feedback">
                        Please enter your last name.
                    </div>
                </div>

                <!-- Email -->
                <div id="email-group" class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           required
                           maxlength="255"
                           placeholder="your.email@example.com">
                    <div id="email-invalid" class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                    <div id="email-help" class="form-text">
                        We'll send a verification email to this address.
                    </div>
                </div>

                <!-- Password -->
                <div id="password-group" class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i> Password <span class="text-danger">*</span>
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
                    <div id="password-invalid" class="invalid-feedback">
                        Password must meet the requirements above.
                    </div>
                </div>

                <!-- Confirm Password -->
                <div id="confirm-password-group" class="mb-3">
                    <label for="confirm-password" class="form-label">
                        <i class="bi bi-lock-fill"></i> Confirm Password <span class="text-danger">*</span>
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

                <!-- Terms and Conditions -->
                <div id="terms-group" class="mb-3">
                    <div id="terms-check" class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="terms"
                               name="terms"
                               required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="/terms.php" target="_blank">Terms of Service</a>
                            and <a href="/privacy.php" target="_blank">Privacy Policy</a>
                            <span class="text-danger">*</span>
                        </label>
                        <div id="terms-invalid" class="invalid-feedback">
                            You must agree to the terms to continue.
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div id="submit-group" class="d-grid gap-2">
                    <button id="register-button" type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Create Account
                        <span id="register-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Card Footer -->
        <div id="register-footer" class="card-footer auth-footer">
            <p class="mb-0">
                Already have an account?
                <a href="/auth/login.php" class="fw-bold">Login here</a>
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
document.getElementById('register-form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const email = document.getElementById('email').value;

    let isValid = true;

    // Validate email
    if (!TodoTracker.validateEmail(email)) {
        document.getElementById('email').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('email').classList.remove('is-invalid');
    }

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

    // Check terms
    if (!document.getElementById('terms').checked) {
        document.getElementById('terms').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('terms').classList.remove('is-invalid');
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
