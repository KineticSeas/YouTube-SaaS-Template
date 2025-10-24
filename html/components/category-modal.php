<!-- Category Add/Edit Modal -->
<div class="modal fade" id="category-modal" tabindex="-1" aria-labelledby="category-modal-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="category-modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="category-form" hx-post="/api/categories/create.php" hx-target="#category-response">
                <div class="modal-body">
                    <!-- Response Messages -->
                    <div id="category-response"></div>

                    <!-- Hidden ID field for edit mode -->
                    <input type="hidden" id="category-id" name="category_id" value="">

                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Category Name -->
                    <div class="mb-3">
                        <label for="category-name" class="form-label">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="category-name"
                               name="name"
                               maxlength="50"
                               required
                               placeholder="e.g., Work, Personal, Shopping">
                        <div class="form-text">Maximum 50 characters</div>
                    </div>

                    <!-- Color Picker -->
                    <div class="mb-3">
                        <label for="category-color" class="form-label">
                            Color <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2 mb-2">
                            <div class="col-12">
                                <div id="color-presets" class="d-flex flex-wrap gap-2">
                                    <!-- Bootstrap Color Presets -->
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#0d6efd" style="background-color: #0d6efd; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Primary Blue"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#6c757d" style="background-color: #6c757d; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Secondary Gray"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#198754" style="background-color: #198754; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Success Green"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#dc3545" style="background-color: #dc3545; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Danger Red"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#ffc107" style="background-color: #ffc107; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Warning Yellow"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#0dcaf0" style="background-color: #0dcaf0; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Info Cyan"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#6f42c1" style="background-color: #6f42c1; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Purple"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#d63384" style="background-color: #d63384; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Pink"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#fd7e14" style="background-color: #fd7e14; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Orange"></button>
                                    <button type="button" class="btn btn-sm color-preset-btn" data-color="#20c997" style="background-color: #20c997; width: 40px; height: 40px; border: 2px solid #dee2e6;" title="Teal"></button>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-8">
                                <input type="color"
                                       class="form-control form-control-color w-100"
                                       id="category-color"
                                       name="color"
                                       value="#6c757d"
                                       title="Choose custom color"
                                       style="height: 50px;">
                            </div>
                            <div class="col-4">
                                <div id="color-preview" class="border rounded d-flex align-items-center justify-content-center h-100" style="background-color: #6c757d;">
                                    <span class="badge rounded-pill px-3 py-2 text-white">
                                        <i class="bi bi-tag-fill me-1"></i>
                                        Preview
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">Click a preset or choose a custom color</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Modal JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('category-color');
    const colorPreview = document.getElementById('color-preview');
    const colorPresets = document.querySelectorAll('.color-preset-btn');

    // Update preview when color input changes
    if (colorInput && colorPreview) {
        colorInput.addEventListener('input', function() {
            updateColorPreview(this.value);
        });
    }

    // Handle color preset clicks
    colorPresets.forEach(btn => {
        btn.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            colorInput.value = color;
            updateColorPreview(color);

            // Visual feedback
            colorPresets.forEach(b => b.style.border = '2px solid #dee2e6');
            this.style.border = '3px solid #000';
        });
    });

    function updateColorPreview(color) {
        colorPreview.style.backgroundColor = color;
    }

    // Handle successful form submission
    document.body.addEventListener('htmx:afterSettle', function(event) {
        if (event.detail.target.id === 'category-response') {
            const responseDiv = document.getElementById('category-response');
            const alert = responseDiv.querySelector('.alert');

            if (alert && alert.classList.contains('alert-success')) {
                // Auto-dismiss alert after 5 seconds
                setTimeout(() => {
                    // Fade out the alert
                    alert.classList.remove('show');

                    // After fade completes, close modal and reload
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('category-modal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Reload page to show updated categories
                        window.location.reload();
                    }, 150);
                }, 5000);
            }
        }
    });

    // Reset form on modal close
    const categoryModal = document.getElementById('category-modal');
    if (categoryModal) {
        categoryModal.addEventListener('hidden.bs.modal', function() {
            const responseDiv = document.getElementById('category-response');
            if (responseDiv) {
                responseDiv.innerHTML = '';
            }
            // Reset form fields to prevent stale data
            const form = document.getElementById('category-form');
            if (form) {
                form.reset();
            }
        });
    }
});
</script>
