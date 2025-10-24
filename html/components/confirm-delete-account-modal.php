<?php
/**
 * Confirm Delete Account Modal Component
 * Modal for confirming account deletion with password verification
 * Implements REQ-AUTH-305
 */
?>

<div id="confirmDeleteAccountModal" class="modal fade" tabindex="-1" aria-labelledby="confirmDeleteAccountModalLabel" aria-hidden="true">
    <div id="delete-account-dialog" class="modal-dialog">
        <div id="delete-account-content" class="modal-content border-danger">
            <!-- Modal Header -->
            <div id="delete-account-header" class="modal-header bg-danger bg-opacity-10 border-danger">
                <h5 id="confirmDeleteAccountModalLabel" class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                </h5>
                <button id="delete-account-close-btn" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div id="delete-account-modal-body" class="modal-body">
                <!-- Warning -->
                <div id="delete-account-warning" class="alert alert-danger" role="alert">
                    <strong>Warning:</strong> This action cannot be undone. All your data will be permanently deleted after 30 days.
                </div>

                <!-- Items to be deleted -->
                <div id="delete-account-list" class="mb-4">
                    <p id="delete-account-list-title" class="fw-bold mb-2">The following will be deleted:</p>
                    <ul id="delete-items-ul" class="list-unstyled ms-3">
                        <li id="delete-item-tasks"><i class="bi bi-x-circle text-danger me-2"></i>All tasks</li>
                        <li id="delete-item-categories"><i class="bi bi-x-circle text-danger me-2"></i>All categories</li>
                        <li id="delete-item-preferences"><i class="bi bi-x-circle text-danger me-2"></i>All preferences</li>
                        <li id="delete-item-profile"><i class="bi bi-x-circle text-danger me-2"></i>Profile information</li>
                        <li id="delete-item-activity"><i class="bi bi-x-circle text-danger me-2"></i>Activity history</li>
                    </ul>
                </div>

                <!-- Form -->
                <form id="delete-account-form"
                      action="/api/user/delete-account.php"
                      method="POST"
                      hx-post="/api/user/delete-account.php"
                      hx-target="#delete-account-response"
                      hx-indicator="#delete-account-spinner"
                      novalidate>

                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Response Container -->
                    <div id="delete-account-response"></div>

                    <!-- Confirmation Checkbox -->
                    <div id="delete-account-confirm-checkbox" class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="delete_confirmation"
                                   name="delete_confirmation"
                                   value="1"
                                   required>
                            <label id="delete-confirmation-label" class="form-check-label" for="delete_confirmation">
                                I understand this cannot be undone and all data will be deleted
                            </label>
                        </div>
                        <div id="delete-confirmation-feedback" class="invalid-feedback">
                            You must confirm that you understand the consequences
                        </div>
                    </div>

                    <!-- Password Verification -->
                    <div id="delete-account-password-field" class="mb-3">
                        <label id="delete-password-label" for="delete_password" class="form-label">
                            Confirm with Password <span class="text-danger">*</span>
                        </label>
                        <input id="delete_password"
                               type="password"
                               class="form-control"
                               name="delete_password"
                               required
                               placeholder="Enter your password">
                        <div id="delete-password-feedback" class="invalid-feedback">
                            Password is required
                        </div>
                    </div>

                    <!-- Confirmation Text -->
                    <div id="delete-account-type-field" class="mb-3">
                        <label id="delete-type-label" for="delete_text" class="form-label">
                            Type <strong>"DELETE MY ACCOUNT"</strong> to confirm <span class="text-danger">*</span>
                        </label>
                        <input id="delete_text"
                               type="text"
                               class="form-control"
                               name="delete_text"
                               required
                               placeholder="Type confirmation text">
                        <small id="delete-text-help" class="form-text text-muted">
                            Case-sensitive confirmation text
                        </small>
                        <div id="delete-text-feedback" class="invalid-feedback">
                            You must type exactly "DELETE MY ACCOUNT"
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div id="delete-account-modal-footer" class="modal-footer">
                <button id="delete-account-cancel-btn"
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
                <button id="delete-account-confirm-btn"
                        type="button"
                        class="btn btn-danger"
                        onclick="submitDeleteAccount()"
                        disabled>
                    <i class="bi bi-trash me-2"></i>Delete My Account
                </button>
                <span id="delete-account-spinner" class="spinner-border spinner-border-sm htmx-indicator ms-2" role="status">
                    <span class="visually-hidden">Processing...</span>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
    const deleteForm = document.getElementById('delete-account-form');
    const deleteTextInput = document.getElementById('delete_text');
    const deleteConfirmCheckbox = document.getElementById('delete_confirmation');
    const deletePasswordInput = document.getElementById('delete_password');
    const deleteConfirmBtn = document.getElementById('delete-account-confirm-btn');

    const CONFIRM_TEXT = 'DELETE MY ACCOUNT';

    // Check if all conditions are met
    function checkDeleteFormValidity() {
        const isCheckboxChecked = deleteConfirmCheckbox.checked;
        const isPasswordFilled = deletePasswordInput.value.length > 0;
        const isTextCorrect = deleteTextInput.value === CONFIRM_TEXT;

        deleteConfirmBtn.disabled = !(isCheckboxChecked && isPasswordFilled && isTextCorrect);

        // Update text input styling
        if (deleteTextInput.value.length > 0) {
            if (isTextCorrect) {
                deleteTextInput.classList.remove('is-invalid');
                deleteTextInput.classList.add('is-valid');
            } else {
                deleteTextInput.classList.remove('is-valid');
                deleteTextInput.classList.add('is-invalid');
            }
        } else {
            deleteTextInput.classList.remove('is-valid', 'is-invalid');
        }
    }

    // Event listeners
    deleteConfirmCheckbox.addEventListener('change', checkDeleteFormValidity);
    deletePasswordInput.addEventListener('input', checkDeleteFormValidity);
    deleteTextInput.addEventListener('input', checkDeleteFormValidity);

    // Submit delete account form
    function submitDeleteAccount() {
        // Validate form
        if (!deleteForm.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }

        // Submit via HTMX
        htmx.ajax('POST', '/api/user/delete-account.php', {
            source: deleteForm,
            target: '#delete-account-response',
            swap: 'innerHTML'
        });
    }

    // Reset form when modal is closed
    document.getElementById('confirmDeleteAccountModal').addEventListener('hidden.bs.modal', function() {
        deleteForm.reset();
        document.getElementById('delete-account-response').innerHTML = '';
        deleteTextInput.classList.remove('is-valid', 'is-invalid');
        checkDeleteFormValidity();
    });

    // Initialize button state
    checkDeleteFormValidity();
</script>
