<?php
/**
 * Avatar Upload Modal Component
 * Modal for uploading and changing user profile picture
 * Implements REQ-AUTH-304
 */
?>

<div id="avatarUploadModal" class="modal fade" tabindex="-1" aria-labelledby="avatarUploadModalLabel" aria-hidden="true">
    <div id="avatar-modal-dialog" class="modal-dialog modal-lg">
        <div id="avatar-modal-content" class="modal-content">
            <!-- Modal Header -->
            <div id="avatar-modal-header" class="modal-header">
                <h5 id="avatarUploadModalLabel" class="modal-title">
                    <i class="bi bi-image me-2"></i>Change Profile Picture
                </h5>
                <button id="avatar-modal-close-btn" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div id="avatar-modal-body" class="modal-body">
                <!-- Form -->
                <form id="avatar-upload-form"
                      action="/api/user/upload-avatar.php"
                      method="POST"
                      enctype="multipart/form-data"
                      hx-post="/api/user/upload-avatar.php"
                      hx-target="#avatar-upload-response"
                      hx-indicator="#avatar-upload-spinner"
                      hx-encoding="multipart/form-data"
                      novalidate>

                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Response Container -->
                    <div id="avatar-upload-response"></div>

                    <!-- File Input -->
                    <div id="avatar-file-input-section" class="mb-4">
                        <label id="avatar-file-label" for="avatar_file" class="form-label">
                            Select Image File <span class="text-danger">*</span>
                        </label>
                        <input id="avatar_file"
                               type="file"
                               class="form-control"
                               name="avatar_file"
                               accept="image/jpeg,image/png,image/gif"
                               required>
                        <small id="avatar-file-help" class="form-text text-muted d-block mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Supported formats: JPG, PNG, GIF (Max 5MB)
                        </small>
                    </div>

                    <!-- Image Preview -->
                    <div id="avatar-preview-section" class="mb-4" style="display: none;">
                        <label id="avatar-preview-label" class="form-label">Preview</label>
                        <div id="avatar-preview-container" class="text-center p-4 bg-light rounded border">
                            <img id="avatar-preview-img"
                                 src="#"
                                 alt="Preview"
                                 class="rounded-circle"
                                 style="max-width: 200px; max-height: 200px; border: 3px solid #dee2e6;">
                        </div>
                    </div>

                    <!-- File Size Warning -->
                    <div id="avatar-file-size-warning" class="alert alert-warning d-none" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>File too large!</strong> Maximum file size is 5MB.
                    </div>

                    <!-- File Type Warning -->
                    <div id="avatar-file-type-warning" class="alert alert-warning d-none" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Invalid file type!</strong> Please select a JPG, PNG, or GIF image.
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div id="avatar-modal-footer" class="modal-footer">
                <button id="avatar-cancel-btn"
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
                <button id="avatar-upload-btn"
                        type="button"
                        class="btn btn-primary"
                        onclick="submitAvatarUpload()">
                    <i class="bi bi-cloud-upload me-2"></i>Upload Picture
                </button>
                <span id="avatar-upload-spinner" class="spinner-border spinner-border-sm htmx-indicator ms-2" role="status">
                    <span class="visually-hidden">Uploading...</span>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
    const avatarFileInput = document.getElementById('avatar_file');
    const avatarPreviewSection = document.getElementById('avatar-preview-section');
    const avatarPreviewImg = document.getElementById('avatar-preview-img');
    const fileSizeWarning = document.getElementById('avatar-file-size-warning');
    const fileTypeWarning = document.getElementById('avatar-file-type-warning');
    const uploadBtn = document.getElementById('avatar-upload-btn');

    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];

    // Handle file selection
    avatarFileInput.addEventListener('change', function() {
        const file = this.files[0];
        fileSizeWarning.classList.add('d-none');
        fileTypeWarning.classList.add('d-none');
        uploadBtn.disabled = false;

        if (!file) {
            avatarPreviewSection.style.display = 'none';
            return;
        }

        // Check file size
        if (file.size > MAX_FILE_SIZE) {
            fileSizeWarning.classList.remove('d-none');
            uploadBtn.disabled = true;
            avatarPreviewSection.style.display = 'none';
            return;
        }

        // Check file type
        if (!ALLOWED_TYPES.includes(file.type)) {
            fileTypeWarning.classList.remove('d-none');
            uploadBtn.disabled = true;
            avatarPreviewSection.style.display = 'none';
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            avatarPreviewImg.src = e.target.result;
            avatarPreviewSection.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    // Submit avatar upload
    function submitAvatarUpload() {
        const form = document.getElementById('avatar-upload-form');

        // Validate form
        if (!form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }

        // Submit via HTMX
        htmx.ajax('POST', '/api/user/upload-avatar.php', {
            source: form,
            target: '#avatar-upload-response',
            swap: 'innerHTML'
        });
    }

    // Reset form when modal is closed
    document.getElementById('avatarUploadModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('avatar-upload-form').reset();
        document.getElementById('avatar-upload-response').innerHTML = '';
        avatarPreviewSection.style.display = 'none';
    });
</script>
