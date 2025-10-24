<?php
/**
 * Profile Tab: Personal Information
 * Edit user profile information
 * Implements REQ-AUTH-301, REQ-AUTH-302, REQ-AUTH-303
 */

$timezones = [
    'America/New_York' => '(UTC-5) Eastern Time',
    'America/Chicago' => '(UTC-6) Central Time',
    'America/Denver' => '(UTC-7) Mountain Time',
    'America/Los_Angeles' => '(UTC-8) Pacific Time',
    'America/Anchorage' => '(UTC-9) Alaska Time',
    'Pacific/Honolulu' => '(UTC-10) Hawaii Time',
    'Europe/London' => '(UTC+0) London',
    'Europe/Paris' => '(UTC+1) Paris',
    'Europe/Berlin' => '(UTC+1) Berlin',
    'Europe/Moscow' => '(UTC+3) Moscow',
    'Asia/Dubai' => '(UTC+4) Dubai',
    'Asia/Kolkata' => '(UTC+5:30) India',
    'Asia/Bangkok' => '(UTC+7) Bangkok',
    'Asia/Hong_Kong' => '(UTC+8) Hong Kong',
    'Asia/Tokyo' => '(UTC+9) Tokyo',
    'Australia/Sydney' => '(UTC+10) Sydney',
];

$currentTimezone = $userProfile['timezone'] ?? 'America/New_York';
?>

<div id="personal-info-section" class="card">
    <div id="personal-info-header" class="card-header">
        <h5 id="personal-info-title" class="mb-0">Personal Information</h5>
    </div>
    <div id="personal-info-body" class="card-body">
        <!-- Form -->
        <form id="personal-info-form"
              action="/api/user/update-profile.php"
              method="POST"
              hx-post="/api/user/update-profile.php"
              hx-target="#personal-info-response"
              hx-indicator="#personal-info-spinner"
              novalidate>

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <!-- Response Container -->
            <div id="personal-info-response"></div>

            <!-- First Name -->
            <div id="personal-field-first-name" class="mb-3">
                <label id="personal-label-first-name" for="first_name" class="form-label">
                    First Name <span class="text-danger">*</span>
                </label>
                <input id="first_name"
                       type="text"
                       class="form-control"
                       name="first_name"
                       value="<?php echo htmlspecialchars($userProfile['first_name']); ?>"
                       maxlength="50"
                       required
                       disabled>
                <div id="first-name-feedback" class="invalid-feedback">
                    First name is required
                </div>
            </div>

            <!-- Last Name -->
            <div id="personal-field-last-name" class="mb-3">
                <label id="personal-label-last-name" for="last_name" class="form-label">
                    Last Name <span class="text-danger">*</span>
                </label>
                <input id="last_name"
                       type="text"
                       class="form-control"
                       name="last_name"
                       value="<?php echo htmlspecialchars($userProfile['last_name']); ?>"
                       maxlength="50"
                       required
                       disabled>
                <div id="last-name-feedback" class="invalid-feedback">
                    Last name is required
                </div>
            </div>

            <!-- Email -->
            <div id="personal-field-email" class="mb-3">
                <label id="personal-label-email" for="email" class="form-label">
                    Email Address <span class="text-danger">*</span>
                </label>
                <input id="email"
                       type="email"
                       class="form-control"
                       name="email"
                       value="<?php echo htmlspecialchars($userProfile['email']); ?>"
                       required
                       disabled>
                <small id="email-help" class="form-text text-muted">
                    Changing your email will require verification
                </small>
                <div id="email-feedback" class="invalid-feedback">
                    Please enter a valid email address
                </div>
            </div>

            <!-- Phone -->
            <div id="personal-field-phone" class="mb-3">
                <label id="personal-label-phone" for="phone" class="form-label">
                    Phone Number <span class="text-muted">(Optional)</span>
                </label>
                <input id="phone"
                       type="tel"
                       class="form-control"
                       name="phone"
                       value="<?php echo htmlspecialchars($userProfile['phone'] ?? ''); ?>"
                       maxlength="20"
                       placeholder="(555) 123-4567"
                       disabled>
                <div id="phone-feedback" class="invalid-feedback">
                    Please enter a valid phone number
                </div>
            </div>

            <!-- Bio/About Me -->
            <div id="personal-field-bio" class="mb-3">
                <label id="personal-label-bio" for="bio" class="form-label">
                    About Me <span class="text-muted">(Optional)</span>
                </label>
                <textarea id="bio"
                          class="form-control"
                          name="bio"
                          rows="3"
                          maxlength="500"
                          placeholder="Tell us about yourself..."
                          disabled><?php echo htmlspecialchars($userProfile['bio'] ?? ''); ?></textarea>
                <small id="bio-counter" class="form-text text-muted">
                    <span id="bio-char-count">0</span>/500 characters
                </small>
                <div id="bio-feedback" class="invalid-feedback">
                    About me must be 500 characters or less
                </div>
            </div>

            <!-- Location -->
            <div id="personal-field-location" class="mb-3">
                <label id="personal-label-location" for="location" class="form-label">
                    Location <span class="text-muted">(Optional)</span>
                </label>
                <input id="location"
                       type="text"
                       class="form-control"
                       name="location"
                       value="<?php echo htmlspecialchars($userProfile['location'] ?? ''); ?>"
                       maxlength="100"
                       placeholder="City, Country"
                       disabled>
                <div id="location-feedback" class="invalid-feedback">
                    Location must be 100 characters or less
                </div>
            </div>

            <!-- Time Zone -->
            <div id="personal-field-timezone" class="mb-3">
                <label id="personal-label-timezone" for="timezone" class="form-label">
                    Time Zone <span class="text-danger">*</span>
                </label>
                <select id="timezone"
                        class="form-select"
                        name="timezone"
                        required
                        disabled>
                    <option value="">Select a time zone...</option>
                    <?php foreach ($timezones as $tzValue => $tzLabel): ?>
                        <option value="<?php echo $tzValue; ?>"
                                <?php echo ($currentTimezone === $tzValue) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tzLabel); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="timezone-feedback" class="invalid-feedback">
                    Time zone selection is required
                </div>
            </div>

            <!-- Form Actions -->
            <div id="personal-form-actions" class="d-flex gap-2">
                <button id="personal-edit-btn"
                        type="button"
                        class="btn btn-primary"
                        onclick="enablePersonalInfoEdit()">
                    <i class="bi bi-pencil me-2"></i>Edit
                </button>
                <button id="personal-save-btn"
                        type="submit"
                        class="btn btn-success"
                        style="display: none;">
                    <i class="bi bi-check-circle me-2"></i>Save Changes
                </button>
                <button id="personal-cancel-btn"
                        type="button"
                        class="btn btn-secondary"
                        onclick="disablePersonalInfoEdit()"
                        style="display: none;">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </button>
                <span id="personal-info-spinner" class="spinner-border spinner-border-sm htmx-indicator ms-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </span>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update character counter for bio
    const bioField = document.getElementById('bio');
    const bioCounter = document.getElementById('bio-char-count');

    if (bioField && bioCounter) {
        bioField.addEventListener('input', function() {
            bioCounter.textContent = this.value.length;
        });

        // Initialize character count
        bioCounter.textContent = bioField.value.length;
    }

    // Handle HTMX responses for personal info form
    const form = document.getElementById('personal-info-form');
    if (form) {
        form.addEventListener('htmx:afterSettle', function(event) {
            const responseDiv = document.getElementById('personal-info-response');
            const alert = responseDiv.querySelector('.alert');

            if (alert) {
                // Check if it's a success alert (not an error)
                const isSuccess = alert.classList.contains('alert-success') ||
                                 alert.classList.contains('alert-warning');

                if (isSuccess) {
                    // Auto-dismiss alert after 5 seconds
                    setTimeout(function() {
                        const closeBtn = alert.querySelector('.btn-close');
                        if (closeBtn) {
                            closeBtn.click();
                        }
                    }, 5000);

                    // Exit edit mode after successful save
                    disablePersonalInfoEdit();
                }
            }
        });
    }
});

// Global functions for edit/cancel buttons
function enablePersonalInfoEdit() {
    // Enable all form fields
    document.querySelectorAll('#personal-info-form input, #personal-info-form textarea, #personal-info-form select').forEach(field => {
        field.removeAttribute('disabled');
    });

    // Show/hide buttons
    document.getElementById('personal-edit-btn').style.display = 'none';
    document.getElementById('personal-save-btn').style.display = 'inline-block';
    document.getElementById('personal-cancel-btn').style.display = 'inline-block';
}

function disablePersonalInfoEdit() {
    // Reset form to original values
    document.getElementById('personal-info-form').reset();

    // Disable all form fields
    document.querySelectorAll('#personal-info-form input, #personal-info-form textarea, #personal-info-form select').forEach(field => {
        field.setAttribute('disabled', 'disabled');
    });

    // Show/hide buttons
    document.getElementById('personal-edit-btn').style.display = 'inline-block';
    document.getElementById('personal-save-btn').style.display = 'none';
    document.getElementById('personal-cancel-btn').style.display = 'none';

    // Clear response message after a delay so user can see it
    setTimeout(function() {
        const responseDiv = document.getElementById('personal-info-response');
        if (responseDiv) {
            responseDiv.innerHTML = '';
        }
    }, 5500);
}
</script>
