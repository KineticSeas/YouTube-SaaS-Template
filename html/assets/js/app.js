/**
 * TodoTracker - Main JavaScript Application
 */

// Utility Functions
const TodoTracker = {
    /**
     * Show toast notification
     * @param {string} message - Message to display
     * @param {string} type - Type of toast (success, error, info, warning)
     */
    showToast: function(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;

        const toastId = 'toast-' + Date.now();
        const bgClass = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'danger': 'bg-danger',
            'info': 'bg-info',
            'warning': 'bg-warning'
        }[type] || 'bg-info';

        const icon = {
            'success': 'bi-check-circle',
            'error': 'bi-exclamation-triangle',
            'danger': 'bi-exclamation-triangle',
            'info': 'bi-info-circle',
            'warning': 'bi-exclamation-triangle'
        }[type] || 'bi-info-circle';

        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icon} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHTML);

        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });

        toast.show();

        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    },

    /**
     * Validate email format
     * @param {string} email - Email address to validate
     * @returns {boolean} - True if valid
     */
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    /**
     * Check password strength
     * @param {string} password - Password to check
     * @returns {object} - {strength: 'weak'|'medium'|'strong', score: 0-3}
     */
    checkPasswordStrength: function(password) {
        let score = 0;

        if (!password) return { strength: 'weak', score: 0 };

        // Length check
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;

        // Character variety checks
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^a-zA-Z\d]/.test(password)) score++;

        // Determine strength
        if (score <= 2) return { strength: 'weak', score: 1 };
        if (score <= 4) return { strength: 'medium', score: 2 };
        return { strength: 'strong', score: 3 };
    },

    /**
     * Update password strength indicator
     * @param {HTMLInputElement} passwordInput - Password input element
     * @param {HTMLElement} indicatorElement - Indicator element
     */
    updatePasswordStrength: function(passwordInput, indicatorElement) {
        const password = passwordInput.value;
        const result = this.checkPasswordStrength(password);

        indicatorElement.className = 'password-strength-bar';

        if (password.length === 0) {
            indicatorElement.style.width = '0%';
            return;
        }

        indicatorElement.classList.add('password-strength-' + result.strength);
    },

    /**
     * Show loading overlay
     */
    showLoading: function() {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div>';
        document.body.appendChild(overlay);
    },

    /**
     * Hide loading overlay
     */
    hideLoading: function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    },

    /**
     * Confirm action with modal
     * @param {string} message - Confirmation message
     * @param {function} callback - Callback function if confirmed
     */
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    /**
     * Format date to readable string
     * @param {Date|string} date - Date to format
     * @returns {string} - Formatted date string
     */
    formatDate: function(date) {
        const d = new Date(date);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return d.toLocaleDateString('en-US', options);
    },

    /**
     * Get relative time string (e.g., "2 hours ago")
     * @param {Date|string} date - Date to calculate from
     * @returns {string} - Relative time string
     */
    getRelativeTime: function(date) {
        const now = new Date();
        const then = new Date(date);
        const diffMs = now - then;
        const diffSec = Math.floor(diffMs / 1000);
        const diffMin = Math.floor(diffSec / 60);
        const diffHour = Math.floor(diffMin / 60);
        const diffDay = Math.floor(diffHour / 24);

        if (diffSec < 60) return 'just now';
        if (diffMin < 60) return diffMin + ' minute' + (diffMin > 1 ? 's' : '') + ' ago';
        if (diffHour < 24) return diffHour + ' hour' + (diffHour > 1 ? 's' : '') + ' ago';
        if (diffDay < 7) return diffDay + ' day' + (diffDay > 1 ? 's' : '') + ' ago';
        return this.formatDate(date);
    }
};

// Sidebar Toggle Functionality
const SidebarManager = {
    /**
     * Initialize sidebar toggle functionality
     */
    init: function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle-mobile');

        if (!sidebar || !toggleBtn) return;

        // Create backdrop element
        const backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        backdrop.id = 'sidebar-backdrop';
        document.body.appendChild(backdrop);

        // Toggle sidebar on button click
        toggleBtn.addEventListener('click', () => {
            this.toggle();
        });

        // Close sidebar when clicking backdrop
        backdrop.addEventListener('click', () => {
            this.hide();
        });

        // Close sidebar when clicking a nav link on mobile
        const navLinks = sidebar.querySelectorAll('.list-group-item');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    this.hide();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                this.hide();
            }
        });
    },

    /**
     * Toggle sidebar visibility
     */
    toggle: function() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');

        if (sidebar && backdrop) {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        }
    },

    /**
     * Hide sidebar
     */
    hide: function() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');

        if (sidebar && backdrop) {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
    },

    /**
     * Show sidebar
     */
    show: function() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');

        if (sidebar && backdrop) {
            sidebar.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
};

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle
    SidebarManager.init();

    // Initialize all tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize all popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
    passwordInputs.forEach(function(input) {
        const indicatorId = input.getAttribute('data-strength');
        const indicator = document.getElementById(indicatorId);

        if (indicator) {
            input.addEventListener('input', function() {
                TodoTracker.updatePasswordStrength(input, indicator);
            });
        }
    });

    // Email validation on blur
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (input.value && !TodoTracker.validateEmail(input.value)) {
                input.classList.add('is-invalid');
                let feedback = input.nextElementSibling;
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Please enter a valid email address.';
                    input.parentNode.insertBefore(feedback, input.nextSibling);
                }
            } else {
                input.classList.remove('is-invalid');
            }
        });
    });

    // Auto-focus first input in forms
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        const firstInput = form.querySelector('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"])');
        if (firstInput) {
            firstInput.focus();
        }
    });

    // HTMX event listeners
    document.body.addEventListener('htmx:beforeRequest', function(event) {
        // Show loading indicator
        const target = event.detail.target;
        if (target) {
            const indicator = target.querySelector('.htmx-indicator');
            if (indicator) {
                indicator.style.display = 'inline-block';
            }
        }
    });

    document.body.addEventListener('htmx:afterRequest', function(event) {
        // Hide loading indicator
        const target = event.detail.target;
        if (target) {
            const indicator = target.querySelector('.htmx-indicator');
            if (indicator) {
                indicator.style.display = 'none';
            }
        }

        // Show success/error toast based on response
        const xhr = event.detail.xhr;
        if (xhr.status === 200) {
            // Check for success message in response
            const response = xhr.responseText;
            if (response.includes('data-success-message')) {
                const match = response.match(/data-success-message="([^"]+)"/);
                if (match) {
                    TodoTracker.showToast(match[1], 'success');
                }
            }
        } else if (xhr.status >= 400) {
            TodoTracker.showToast('An error occurred. Please try again.', 'error');
        }
    });

    // Confirm delete actions
    document.body.addEventListener('click', function(event) {
        const deleteBtn = event.target.closest('[data-confirm-delete]');
        if (deleteBtn) {
            event.preventDefault();
            const message = deleteBtn.getAttribute('data-confirm-delete') || 'Are you sure you want to delete this item?';
            TodoTracker.confirm(message, function() {
                // Trigger the actual delete action
                const href = deleteBtn.getAttribute('href');
                const form = deleteBtn.closest('form');

                if (href) {
                    window.location.href = href;
                } else if (form) {
                    form.submit();
                }
            });
        }
    });
});

// HTMX configuration
document.addEventListener('DOMContentLoaded', function() {
    // Configure HTMX to handle JSON responses
    document.body.addEventListener('htmx:responseError', function(event) {
        TodoTracker.showToast('An error occurred. Please try again.', 'error');
    });

    document.body.addEventListener('htmx:sendError', function(event) {
        TodoTracker.showToast('Network error. Please check your connection.', 'error');
    });
});

// Export for use in other scripts
window.TodoTracker = TodoTracker;
window.SidebarManager = SidebarManager;
