<?php
/**
 * Profile Tab: Account Security
 * Manage password, sessions, and account security
 * Implements REQ-AUTH-204-206, REQ-AUTH-404-405
 */
?>

<div id="account-security-section">
    <!-- Security Information Card -->
    <div id="security-info-card" class="card mb-4">
        <div id="security-info-header" class="card-header">
            <h5 id="security-info-title" class="mb-0">Security Information</h5>
        </div>
        <div id="security-info-body" class="card-body">
            <!-- Last Login -->
            <div id="security-last-login" class="row mb-3">
                <div id="security-last-login-label" class="col-md-3 text-muted">Last Login:</div>
                <div id="security-last-login-value" class="col-md-9">
                    <?php if ($userProfile['last_login_at']): ?>
                        <strong><?php echo date('M d, Y H:i:s', strtotime($userProfile['last_login_at'])); ?></strong>
                        <small class="text-muted d-block">
                            IP: <?php echo htmlspecialchars($userProfile['last_login_ip'] ?? 'N/A'); ?>
                        </small>
                    <?php else: ?>
                        <span class="text-muted">No login history</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email Status -->
            <div id="security-email-status" class="row mb-3">
                <div id="security-email-label" class="col-md-3 text-muted">Email Status:</div>
                <div id="security-email-value" class="col-md-9">
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle me-1"></i>Verified
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div id="change-password-card" class="card mb-4">
        <div id="change-password-header" class="card-header">
            <h5 id="change-password-title" class="mb-0">Change Password</h5>
        </div>
        <div id="change-password-body" class="card-body">
            <form id="change-password-form"
                  action="/api/user/change-password.php"
                  method="POST"
                  hx-post="/api/user/change-password.php"
                  hx-target="#change-password-response"
                  hx-indicator="#change-password-spinner"
                  novalidate>

                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <!-- Response Container -->
                <div id="change-password-response"></div>

                <!-- Current Password -->
                <div id="password-field-current" class="mb-3">
                    <label id="password-label-current" for="current_password" class="form-label">
                        Current Password <span class="text-danger">*</span>
                    </label>
                    <input id="current_password"
                           type="password"
                           class="form-control"
                           name="current_password"
                           required>
                    <div id="current-password-feedback" class="invalid-feedback">
                        Current password is required
                    </div>
                </div>

                <!-- New Password -->
                <div id="password-field-new" class="mb-3">
                    <label id="password-label-new" for="new_password" class="form-label">
                        New Password <span class="text-danger">*</span>
                    </label>
                    <input id="new_password"
                           type="password"
                           class="form-control"
                           name="new_password"
                           required
                           placeholder="Minimum 8 characters">
                    <small id="password-requirements" class="form-text text-muted d-block mt-2">
                        <div id="password-req-list">
                            <span id="req-length" class="d-block"><i class="bi bi-circle me-1"></i>At least 8 characters</span>
                            <span id="req-uppercase" class="d-block"><i class="bi bi-circle me-1"></i>One uppercase letter (A-Z)</span>
                            <span id="req-lowercase" class="d-block"><i class="bi bi-circle me-1"></i>One lowercase letter (a-z)</span>
                            <span id="req-number" class="d-block"><i class="bi bi-circle me-1"></i>One number (0-9)</span>
                            <span id="req-special" class="d-block"><i class="bi bi-circle me-1"></i>One special character (!@#$%^&*)</span>
                        </div>
                    </small>
                </div>

                <!-- Password Strength Indicator -->
                <div id="password-strength-container" class="mb-3">
                    <div id="password-strength-bar" class="progress" style="height: 6px; display: none;">
                        <div id="password-strength-fill"
                             class="progress-bar"
                             role="progressbar"
                             style="width: 0%"
                             aria-valuenow="0"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <small id="password-strength-text" class="form-text text-muted d-block" style="display: none;"></small>
                </div>

                <!-- Confirm Password -->
                <div id="password-field-confirm" class="mb-3">
                    <label id="password-label-confirm" for="confirm_password" class="form-label">
                        Confirm New Password <span class="text-danger">*</span>
                    </label>
                    <input id="confirm_password"
                           type="password"
                           class="form-control"
                           name="confirm_password"
                           required>
                    <div id="confirm-password-feedback" class="invalid-feedback">
                        Passwords must match
                    </div>
                </div>

                <!-- Form Actions -->
                <div id="change-password-actions" class="d-flex gap-2">
                    <button id="change-password-btn"
                            type="submit"
                            class="btn btn-primary">
                        <i class="bi bi-key me-2"></i>Change Password
                    </button>
                    <span id="change-password-spinner" class="spinner-border spinner-border-sm htmx-indicator ms-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Two-Factor Authentication Card (Placeholder) -->
    <div id="two-factor-card" class="card mb-4 opacity-75">
        <div id="two-factor-header" class="card-header">
            <h5 id="two-factor-title" class="mb-0">Two-Factor Authentication</h5>
        </div>
        <div id="two-factor-body" class="card-body">
            <p id="two-factor-status" class="text-muted mb-3">
                <strong>Status:</strong> <span class="badge bg-secondary">Disabled</span>
            </p>
            <p id="two-factor-description" class="text-muted mb-3">
                Adds an extra layer of security to your account by requiring a code from your phone or authenticator app.
            </p>
            <button id="enable-2fa-btn"
                    type="button"
                    class="btn btn-outline-primary"
                    disabled
                    title="Coming in a future update">
                <i class="bi bi-unlock me-2"></i>Enable 2FA
                <span class="badge bg-info ms-2">Coming Soon</span>
            </button>
        </div>
    </div>

    <!-- Account Deletion Card (Danger Zone) -->
    <div id="account-deletion-card" class="card border-danger mb-4">
        <div id="account-deletion-header" class="card-header bg-danger bg-opacity-10 border-danger">
            <h5 id="account-deletion-title" class="mb-0 text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
            </h5>
        </div>
        <div id="account-deletion-body" class="card-body">
            <p id="account-deletion-warning" class="text-danger mb-3">
                <strong>Warning:</strong> Deleting your account is permanent and cannot be undone.
            </p>
            <p id="account-deletion-description" class="text-muted mb-3">
                All your tasks, categories, preferences, and account data will be deleted. You'll have 30 days to cancel the deletion.
            </p>
            <button id="delete-account-btn"
                    type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmDeleteAccountModal">
                <i class="bi bi-trash me-2"></i>Delete My Account
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    function checkPasswordStrength() {
        const password = newPasswordInput.value;
        let strength = 0;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*()_+=\-\[\]{};':"\\|,.<>\/?]/.test(password)
        };

        // Update requirement indicators
        document.getElementById('req-length').className = requirements.length ? 'd-block text-success' : 'd-block';
        document.getElementById('req-uppercase').className = requirements.uppercase ? 'd-block text-success' : 'd-block';
        document.getElementById('req-lowercase').className = requirements.lowercase ? 'd-block text-success' : 'd-block';
        document.getElementById('req-number').className = requirements.number ? 'd-block text-success' : 'd-block';
        document.getElementById('req-special').className = requirements.special ? 'd-block text-success' : 'd-block';

        // Calculate strength
        strength = Object.values(requirements).filter(Boolean).length;

        // Show/update strength bar
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthFill = document.getElementById('password-strength-fill');
        const strengthText = document.getElementById('password-strength-text');

        if (password.length > 0) {
            strengthBar.style.display = 'block';
            strengthText.style.display = 'block';

            const percentage = (strength / 5) * 100;
            strengthFill.style.width = percentage + '%';
            strengthFill.setAttribute('aria-valuenow', Math.round(percentage));

            if (strength < 2) {
                strengthFill.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Weak password';
                strengthText.className = 'form-text text-danger d-block';
            } else if (strength < 4) {
                strengthFill.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Medium password';
                strengthText.className = 'form-text text-warning d-block';
            } else {
                strengthFill.className = 'progress-bar bg-success';
                strengthText.textContent = 'Strong password';
                strengthText.className = 'form-text text-success d-block';
            }
        } else {
            strengthBar.style.display = 'none';
            strengthText.style.display = 'none';
        }
    }

    newPasswordInput.addEventListener('input', checkPasswordStrength);
    confirmPasswordInput.addEventListener('input', function() {
        if (newPasswordInput.value !== '' && confirmPasswordInput.value !== '') {
            if (newPasswordInput.value === confirmPasswordInput.value) {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            } else {
                confirmPasswordInput.classList.remove('is-valid');
                confirmPasswordInput.classList.add('is-invalid');
            }
        }
    });

    // Handle HTMX responses for password change form
    const passwordForm = document.getElementById('change-password-form');
    if (passwordForm) {
        passwordForm.addEventListener('htmx:afterSettle', function(event) {
            const responseDiv = document.getElementById('change-password-response');
            const alert = responseDiv.querySelector('.alert');

            if (alert) {
                // Check if it's a success alert (not an error)
                const isSuccess = alert.classList.contains('alert-success');

                if (isSuccess) {
                    // Auto-dismiss alert after 5 seconds
                    setTimeout(function() {
                        const closeBtn = alert.querySelector('.btn-close');
                        if (closeBtn) {
                            closeBtn.click();
                        }
                    }, 5000);

                    // Reset form
                    setTimeout(function() {
                        passwordForm.reset();
                        responseDiv.innerHTML = '';

                        // Reset password strength indicator
                        document.getElementById('password-strength-bar').style.display = 'none';
                        document.getElementById('password-strength-text').style.display = 'none';

                        // Reset confirm password styling
                        confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
                    }, 100);
                }
            }
        });
    }
});
</script>
