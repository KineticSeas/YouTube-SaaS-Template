/**
 * Calendar JavaScript
 * Alpine.js component for calendar navigation and interactions
 * REQ-CAL-301 through REQ-CAL-304
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('calendarData', () => ({
        // State
        view: new URLSearchParams(window.location.search).get('view') || 'month',
        year: parseInt(new URLSearchParams(window.location.search).get('year')) || new Date().getFullYear(),
        month: parseInt(new URLSearchParams(window.location.search).get('month')) || (new Date().getMonth() + 1),
        date: new URLSearchParams(window.location.search).get('date') || new Date().toISOString().split('T')[0],
        selectedDate: null,

        /**
         * Initialize component
         */
        init() {
            // Update button styles on initialization
            this.updateViewButtonStyles();

            // Listen for HTMX events to update header
            document.body.addEventListener('htmx:afterSwap', (event) => {
                if (event.detail.target.id === 'calendar-content') {
                    this.updatePeriodDisplay();
                }
            });
        },

        /**
         * Navigate to previous period (month/week/day)
         */
        navigatePrevious() {
            if (this.view === 'month') {
                // Previous month
                this.month--;
                if (this.month < 1) {
                    this.month = 12;
                    this.year--;
                }
            } else if (this.view === 'week') {
                // Previous week (subtract 7 days)
                const currentDate = new Date(this.date);
                currentDate.setDate(currentDate.getDate() - 7);
                this.date = currentDate.toISOString().split('T')[0];
            } else {
                // Previous day
                const currentDate = new Date(this.date);
                currentDate.setDate(currentDate.getDate() - 1);
                this.date = currentDate.toISOString().split('T')[0];
            }

            this.updateCalendar();
        },

        /**
         * Navigate to next period (month/week/day)
         */
        navigateNext() {
            if (this.view === 'month') {
                // Next month
                this.month++;
                if (this.month > 12) {
                    this.month = 1;
                    this.year++;
                }
            } else if (this.view === 'week') {
                // Next week (add 7 days)
                const currentDate = new Date(this.date);
                currentDate.setDate(currentDate.getDate() + 7);
                this.date = currentDate.toISOString().split('T')[0];
            } else {
                // Next day
                const currentDate = new Date(this.date);
                currentDate.setDate(currentDate.getDate() + 1);
                this.date = currentDate.toISOString().split('T')[0];
            }

            this.updateCalendar();
        },

        /**
         * Navigate to today
         */
        navigateToday() {
            const today = new Date();
            this.year = today.getFullYear();
            this.month = today.getMonth() + 1;
            this.date = today.toISOString().split('T')[0];

            this.updateCalendar();
        },

        /**
         * Change view mode (month/week/day)
         */
        changeView(newView) {
            if (newView === this.view) {
                return; // Already in this view
            }

            this.view = newView;
            this.selectedDate = null; // Clear selected date when switching views
            this.updateViewButtonStyles(); // Update button styling
            this.updateCalendar();
        },

        /**
         * Update view toggle button styles based on current view
         */
        updateViewButtonStyles() {
            const monthBtn = document.getElementById('calendar-view-month');
            const weekBtn = document.getElementById('calendar-view-week');
            const dayBtn = document.getElementById('calendar-view-day');

            // Remove active state from all buttons
            [monthBtn, weekBtn, dayBtn].forEach(btn => {
                if (btn) {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-outline-secondary');
                }
            });

            // Add active state to current view button
            let activeBtn = null;
            if (this.view === 'month') {
                activeBtn = monthBtn;
            } else if (this.view === 'week') {
                activeBtn = weekBtn;
            } else if (this.view === 'day') {
                activeBtn = dayBtn;
            }

            if (activeBtn) {
                activeBtn.classList.remove('btn-outline-secondary');
                activeBtn.classList.add('btn-primary');
            }
        },

        /**
         * Update calendar via HTMX
         */
        updateCalendar() {
            this.updateUrlParams();

            const params = this.getUrlParams();
            const endpoint = `/api/calendar/${this.view}.php?${params}`;

            // Trigger HTMX request
            htmx.ajax('GET', endpoint, {
                target: '#calendar-content',
                swap: 'innerHTML'
            });
        },

        /**
         * Update URL parameters (for bookmarking and browser history)
         */
        updateUrlParams() {
            const params = this.getUrlParams();
            const newUrl = `${window.location.pathname}?${params}`;
            window.history.pushState({}, '', newUrl);
        },

        /**
         * Get URL parameters based on current state
         */
        getUrlParams() {
            const params = new URLSearchParams();
            params.set('view', this.view);

            if (this.view === 'month') {
                params.set('year', this.year);
                params.set('month', this.month);
            } else {
                params.set('date', this.date);
            }

            return params.toString();
        },

        /**
         * Update period display text in toolbar
         */
        updatePeriodDisplay() {
            const displayElement = document.getElementById('calendar-current-period');
            if (!displayElement) return;

            let displayText = '';

            if (this.view === 'month') {
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                    'July', 'August', 'September', 'October', 'November', 'December'];
                displayText = `${monthNames[this.month - 1]} ${this.year}`;
            } else if (this.view === 'week') {
                // Calculate week range
                const weekStart = this.getWeekStart(this.date);
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekEnd.getDate() + 6);
                displayText = this.formatDateRange(weekStart, weekEnd);
            } else {
                // Day view
                const dateObj = new Date(this.date);
                displayText = dateObj.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            displayElement.textContent = displayText;
        },

        /**
         * Get week start date (Sunday) for a given date
         */
        getWeekStart(dateStr) {
            const date = new Date(dateStr);
            const dayOfWeek = date.getDay();
            const diff = dayOfWeek; // Days to subtract to get to Sunday

            const weekStart = new Date(date);
            weekStart.setDate(date.getDate() - diff);
            return weekStart;
        },

        /**
         * Format date range for display
         */
        formatDateRange(startDate, endDate) {
            const startMonth = startDate.toLocaleDateString('en-US', { month: 'short' });
            const endMonth = endDate.toLocaleDateString('en-US', { month: 'short' });
            const startYear = startDate.getFullYear();
            const endYear = endDate.getFullYear();

            if (startMonth === endMonth && startYear === endYear) {
                return `${startMonth} ${startDate.getDate()} - ${endDate.getDate()}, ${startYear}`;
            } else if (startYear === endYear) {
                return `${startMonth} ${startDate.getDate()} - ${endMonth} ${endDate.getDate()}, ${startYear}`;
            } else {
                return `${startMonth} ${startDate.getDate()}, ${startYear} - ${endMonth} ${endDate.getDate()}, ${endYear}`;
            }
        },

        /**
         * Open Add Task modal with pre-filled date
         */
        openAddTaskModal(date) {
            this.selectedDate = date;

            // Set the due date field in the modal
            const dueDateField = document.getElementById('task-due-date');
            if (dueDateField) {
                dueDateField.value = date;
            }

            // Open the modal
            const modal = new bootstrap.Modal(document.getElementById('add-task-modal'));
            modal.show();
        },

        /**
         * Open task details modal for editing
         */
        openTaskModal(taskId) {
            // Fetch task data
            fetch(`/api/tasks/get.php?id=${taskId}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        TodoTracker.showToast('Failed to load task', 'error');
                        return;
                    }

                    const task = data.task;

                    // Update modal title to "Edit Task"
                    const modalTitle = document.getElementById('add-task-modal-title');
                    if (modalTitle) {
                        modalTitle.innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Task';
                    }

                    // Populate form fields
                    document.getElementById('task-title').value = task.title || '';
                    document.getElementById('task-description').value = task.description || '';
                    document.getElementById('task-status').value = task.status || 'pending';
                    document.getElementById('task-priority').value = task.priority || 'medium';
                    document.getElementById('task-due-date').value = task.due_date || '';

                    // Update character count
                    const charCount = document.getElementById('char-count');
                    if (charCount) {
                        charCount.textContent = (task.description || '').length;
                    }

                    // Change form action to update endpoint
                    const form = document.getElementById('add-task-form');
                    if (form) {
                        form.setAttribute('action', `/api/tasks/update.php?id=${taskId}`);
                        form.setAttribute('hx-post', `/api/tasks/update.php?id=${taskId}`);
                        // Store task ID for potential use
                        form.dataset.taskId = taskId;
                    }

                    // Show the modal
                    const modalElement = document.getElementById('add-task-modal');
                    const modal = new bootstrap.Modal(modalElement);

                    // Store the trigger element for focus management
                    modalElement.dataset.triggerElement = document.activeElement;

                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading task:', error);
                    TodoTracker.showToast('Error loading task details', 'error');
                });
        },

        /**
         * View all tasks for a specific day (switch to day view)
         */
        viewDayTasks(date) {
            this.date = date;
            this.changeView('day');
        }
    }));
});

// Listen for task updates to refresh calendar
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('taskUpdated', () => {
        // Refresh the calendar view
        const calendarContent = document.getElementById('calendar-content');
        if (calendarContent) {
            htmx.trigger(calendarContent, 'taskUpdated');
        }
    });
});
