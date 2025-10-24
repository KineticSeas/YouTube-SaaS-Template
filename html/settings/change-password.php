<?php
/**
 * Change Password Page (Settings)
 * REQ-AUTH-204 through REQ-AUTH-206
 */

$pageTitle = 'Change Password - TodoTracker';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div id="change-password-container" class="container py-4">
    <div id="breadcrumb-nav" class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/profile.php">Profile</a></li>
                    <li class="breadcrumb-item active">Change Password</li>
                </ol>
            </nav>
        </div>
    </div>

    <div id="page-header" class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-key"></i> Change Password</h1>
            <p class="text-muted">Update your account password</p>
        </div>
    </div>

    <div id="change-password-content" class="row">
        <div class="col-lg-8">
            <div id="change-password-card" class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="change-password-form"
                          action="/api/auth/change-password-process.php"
                          method="POST"
                          hx-post="/api/auth/change-password-process.php"
                          hx-target="#change-password-response"
                          hx-indicator="#change-password-spinner">

                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                        <!-- Response Container -->
                        <div id="change-password-response"></div>

                        <!-- Current Password -->
                        <div id="current-password-group" class="mb-3">
                            <label for="current-password" class="form-label">
                                <i class="bi bi-lock"></i> Current Password <span class="text-danger">*</span>
                            </label>
                            <div id="current-password-input-group" class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="current-password"
                                       name="current_password"
                                       required
                                       placeholder="Enter your current password">
                                <button id="toggle-current-password" class="btn btn-outline-secondary" type="button" onclick="togglePassword('current-password', 'toggle-current-icon')">
                                    <i id="toggle-current-icon" class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="current-password-help" class="form-text">
                                You must verify your current password to make changes.
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- New Password -->
                        <div id="new-password-group" class="mb-3">
                            <label for="new-password" class="form-label">
                                <i class="bi bi-key-fill"></i> New Password <span class="text-danger">*</span>
                            </label>
                            <div id="new-password-input-group" class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="new-password"
                                       name="new_password"
                                       required
                                       minlength="8"
                                       data-strength="password-strength-bar"
                                       placeholder="Create a strong password">
                                <button id="toggle-new-password" class="btn btn-outline-secondary" type="button" onclick="togglePassword('new-password', 'toggle-new-icon')">
                                    <i id="toggle-new-icon" class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="password-strength" class="password-strength">
                                <div id="password-strength-bar" class="password-strength-bar"></div>
                            </div>
                            <div id="new-password-help" class="form-text">
                                Must be at least 8 characters with uppercase, lowercase, and number.
                            </div>
                        </div>

                        <!-- Confirm New Password -->
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
                                       placeholder="Re-enter your new password">
                                <button id="toggle-confirm-password" class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm-password', 'toggle-confirm-icon')">
                                    <i id="toggle-confirm-icon" class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="confirm-password-invalid" class="invalid-feedback">
                                Passwords do not match.
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div id="form-actions" class="d-flex gap-2">
                            <button id="change-password-button" type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Password
                                <span id="change-password-spinner" class="spinner-border spinner-border-sm ms-2 htmx-indicator" role="status" aria-hidden="true"></span>
                            </button>
                            <a id="cancel-button" href="/profile.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Tips -->
            <div id="security-tips" class="card mt-4 bg-light border-0">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-shield-check text-primary"></i> Password Security Tips</h5>
                    <ul class="mb-0 small">
                        <li>Use a unique password that you don't use for other accounts</li>
                        <li>Mix uppercase and lowercase letters, numbers, and symbols</li>
                        <li>Avoid common words, phrases, or patterns</li>
                        <li>Consider using a password manager to generate and store passwords</li>
                        <li>Change your password regularly and immediately if you suspect unauthorized access</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div id="settings-nav-card" class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Settings</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/profile.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person"></i> Profile
                    </a>
                    <a href="/settings/change-password.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-key"></i> Change Password
                    </a>
                    <a href="/settings/notifications.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-bell"></i> Notifications
                    </a>
                    <a href="/settings/preferences.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-sliders"></i> Preferences
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
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
document.getElementById('change-password-form').addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    let isValid = true;

    // Check current password
    if (!currentPassword) {
        isValid = false;
    }

    // Validate new password strength
    const passwordStrength = TodoTracker.checkPasswordStrength(newPassword);
    if (passwordStrength.strength === 'weak') {
        document.getElementById('new-password').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('new-password').classList.remove('is-invalid');
    }

    // Check password match
    if (newPassword !== confirmPassword) {
        document.getElementById('confirm-password').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('confirm-password').classList.remove('is-invalid');
    }

    // Check that new password is different from current
    if (newPassword === currentPassword) {
        document.getElementById('new-password').classList.add('is-invalid');
        TodoTracker.showToast('New password must be different from current password.', 'error');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
        TodoTracker.showToast('Please fix the errors in the form.', 'error');
    }
});

// Real-time password match validation
document.getElementById('confirm-password').addEventListener('input', function() {
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = this.value;

    if (confirmPassword && newPassword !== confirmPassword) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
