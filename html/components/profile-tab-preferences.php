<?php
/**
 * Profile Tab: Preferences
 * User display, notification, and task preferences
 * Implements REQ-SET-201-205
 */

// Get user preferences
$prefs = $userPreferences;
$theme = $prefs['theme'] ?? 'light';
$defaultView = $prefs['default_view'] ?? 'dashboard';
$defaultSort = $prefs['default_sort'] ?? 'due_date';
$tasksPerPage = $prefs['tasks_per_page'] ?? '20';
$dateFormat = $prefs['date_format'] ?? 'MM/DD/YYYY';
$timeFormat = $prefs['time_format'] ?? '12h';
$emailNotifications = $prefs['email_notifications'] ?? '1';
$dailyDigest = $prefs['daily_digest'] ?? '0';
$dailyDigestTime = $prefs['daily_digest_time'] ?? '08:00';
$dueReminders = $prefs['due_reminders'] ?? '1';
$reminderTiming = $prefs['reminder_timing'] ?? '24h';
$autoArchive = $prefs['auto_archive'] ?? '0';
$autoArchiveAfter = $prefs['auto_archive_after'] ?? '30';
$defaultPriority = $prefs['default_priority'] ?? 'medium';
$defaultStatus = $prefs['default_status'] ?? 'pending';
$weekStart = $prefs['week_start'] ?? 'sunday';
?>

<div id="preferences-section">
    <form id="preferences-form"
          action="/api/user/update-preferences.php"
          method="POST"
          hx-post="/api/user/update-preferences.php"
          hx-target="#preferences-response"
          hx-indicator="#preferences-spinner"
          novalidate>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <!-- Response Container -->
        <div id="preferences-response"></div>

        <!-- Display Preferences Card -->
        <div id="display-pref-card" class="card mb-4">
            <div id="display-pref-header" class="card-header">
                <h5 id="display-pref-title" class="mb-0">Display Preferences</h5>
            </div>
            <div id="display-pref-body" class="card-body">
                <!-- Theme -->
                <div id="pref-theme-field" class="mb-4">
                    <label id="pref-theme-label" class="form-label">Theme</label>
                    <div id="pref-theme-options" class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="theme" id="theme-light" value="light"
                               <?php echo ($theme === 'light') ? 'checked' : ''; ?>>
                        <label class="btn btn-outline-secondary" for="theme-light">
                            <i class="bi bi-sun me-2"></i>Light
                        </label>

                        <input type="radio" class="btn-check" name="theme" id="theme-dark" value="dark"
                               <?php echo ($theme === 'dark') ? 'checked' : ''; ?>>
                        <label class="btn btn-outline-secondary" for="theme-dark">
                            <i class="bi bi-moon me-2"></i>Dark
                        </label>
                    </div>
                </div>

                <!-- Default View -->
                <div id="pref-default-view-field" class="mb-3">
                    <label id="pref-default-view-label" for="default_view" class="form-label">Default View</label>
                    <select id="default_view" class="form-select" name="default_view">
                        <option value="dashboard" <?php echo ($defaultView === 'dashboard') ? 'selected' : ''; ?>>Dashboard</option>
                        <option value="tasks" <?php echo ($defaultView === 'tasks') ? 'selected' : ''; ?>>All Tasks</option>
                        <option value="kanban" <?php echo ($defaultView === 'kanban') ? 'selected' : ''; ?>>Kanban Board</option>
                        <option value="calendar" <?php echo ($defaultView === 'calendar') ? 'selected' : ''; ?>>Calendar</option>
                    </select>
                </div>

                <!-- Default Sort -->
                <div id="pref-default-sort-field" class="mb-3">
                    <label id="pref-default-sort-label" for="default_sort" class="form-label">Default Sort Order</label>
                    <select id="default_sort" class="form-select" name="default_sort">
                        <option value="due_date" <?php echo ($defaultSort === 'due_date') ? 'selected' : ''; ?>>Due Date</option>
                        <option value="priority" <?php echo ($defaultSort === 'priority') ? 'selected' : ''; ?>>Priority</option>
                        <option value="created_date" <?php echo ($defaultSort === 'created_date') ? 'selected' : ''; ?>>Created Date</option>
                        <option value="title" <?php echo ($defaultSort === 'title') ? 'selected' : ''; ?>>Title (A-Z)</option>
                    </select>
                </div>

                <!-- Tasks Per Page -->
                <div id="pref-tasks-per-page-field" class="mb-3">
                    <label id="pref-tasks-per-page-label" for="tasks_per_page" class="form-label">Tasks Per Page</label>
                    <select id="tasks_per_page" class="form-select" name="tasks_per_page">
                        <option value="10" <?php echo ($tasksPerPage === '10') ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo ($tasksPerPage === '20') ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo ($tasksPerPage === '50') ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ($tasksPerPage === '100') ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>

                <!-- Date Format -->
                <div id="pref-date-format-field" class="mb-3">
                    <label id="pref-date-format-label" for="date_format" class="form-label">Date Format</label>
                    <select id="date_format" class="form-select" name="date_format">
                        <option value="MM/DD/YYYY" <?php echo ($dateFormat === 'MM/DD/YYYY') ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                        <option value="DD/MM/YYYY" <?php echo ($dateFormat === 'DD/MM/YYYY') ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                        <option value="YYYY-MM-DD" <?php echo ($dateFormat === 'YYYY-MM-DD') ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                    </select>
                </div>

                <!-- Time Format -->
                <div id="pref-time-format-field" class="mb-3">
                    <label id="pref-time-format-label" class="form-label">Time Format</label>
                    <div id="pref-time-format-options" class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="time_format" id="time-12h" value="12h"
                               <?php echo ($timeFormat === '12h') ? 'checked' : ''; ?>>
                        <label class="btn btn-outline-secondary" for="time-12h">12-Hour (AM/PM)</label>

                        <input type="radio" class="btn-check" name="time_format" id="time-24h" value="24h"
                               <?php echo ($timeFormat === '24h') ? 'checked' : ''; ?>>
                        <label class="btn btn-outline-secondary" for="time-24h">24-Hour</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Preferences Card -->
        <div id="notification-pref-card" class="card mb-4">
            <div id="notification-pref-header" class="card-header">
                <h5 id="notification-pref-title" class="mb-0">Notification Preferences</h5>
            </div>
            <div id="notification-pref-body" class="card-body">
                <!-- Email Notifications Toggle -->
                <div id="pref-email-notifications-field" class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications"
                               value="1" <?php echo ($emailNotifications) ? 'checked' : ''; ?>>
                        <label id="pref-email-notifications-label" class="form-check-label" for="email_notifications">
                            <strong>Email Notifications</strong>
                            <small class="d-block text-muted">Receive email alerts for important account activities</small>
                        </label>
                    </div>
                </div>

                <!-- Daily Digest -->
                <div id="pref-daily-digest-field" class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="daily_digest" id="daily_digest"
                               value="1" <?php echo ($dailyDigest) ? 'checked' : ''; ?>>
                        <label id="pref-daily-digest-label" class="form-check-label" for="daily_digest">
                            <strong>Daily Digest</strong>
                            <small class="d-block text-muted">Receive a daily summary of your tasks</small>
                        </label>
                    </div>
                </div>

                <!-- Daily Digest Time -->
                <div id="pref-daily-digest-time-field" class="mb-3 ps-4">
                    <label id="pref-daily-digest-time-label" for="daily_digest_time" class="form-label">Digest Time</label>
                    <input type="time" class="form-control" id="daily_digest_time" name="daily_digest_time"
                           value="<?php echo htmlspecialchars($dailyDigestTime); ?>">
                </div>

                <!-- Due Date Reminders -->
                <div id="pref-due-reminders-field" class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="due_reminders" id="due_reminders"
                               value="1" <?php echo ($dueReminders) ? 'checked' : ''; ?>>
                        <label id="pref-due-reminders-label" class="form-check-label" for="due_reminders">
                            <strong>Due Date Reminders</strong>
                            <small class="d-block text-muted">Get reminders before tasks are due</small>
                        </label>
                    </div>
                </div>

                <!-- Reminder Timing -->
                <div id="pref-reminder-timing-field" class="mb-3 ps-4">
                    <label id="pref-reminder-timing-label" for="reminder_timing" class="form-label">Remind Me</label>
                    <select id="reminder_timing" class="form-select" name="reminder_timing">
                        <option value="24h" <?php echo ($reminderTiming === '24h') ? 'selected' : ''; ?>>24 hours before</option>
                        <option value="48h" <?php echo ($reminderTiming === '48h') ? 'selected' : ''; ?>>48 hours before</option>
                        <option value="1w" <?php echo ($reminderTiming === '1w') ? 'selected' : ''; ?>>1 week before</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Task Preferences Card -->
        <div id="task-pref-card" class="card mb-4">
            <div id="task-pref-header" class="card-header">
                <h5 id="task-pref-title" class="mb-0">Task Preferences</h5>
            </div>
            <div id="task-pref-body" class="card-body">
                <!-- Auto-Archive Completed Tasks -->
                <div id="pref-auto-archive-field" class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="auto_archive" id="auto_archive"
                               value="1" <?php echo ($autoArchive) ? 'checked' : ''; ?>>
                        <label id="pref-auto-archive-label" class="form-check-label" for="auto_archive">
                            <strong>Auto-Archive Completed Tasks</strong>
                            <small class="d-block text-muted">Automatically archive tasks after completion</small>
                        </label>
                    </div>
                </div>

                <!-- Auto-Archive After (Days) -->
                <div id="pref-auto-archive-after-field" class="mb-3 ps-4">
                    <label id="pref-auto-archive-after-label" for="auto_archive_after" class="form-label">Archive After</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="auto_archive_after" name="auto_archive_after"
                               value="<?php echo htmlspecialchars($autoArchiveAfter); ?>" min="1" max="365">
                        <span class="input-group-text">days</span>
                    </div>
                </div>

                <!-- Default Task Priority -->
                <div id="pref-default-priority-field" class="mb-3">
                    <label id="pref-default-priority-label" for="default_priority" class="form-label">Default Task Priority</label>
                    <select id="default_priority" class="form-select" name="default_priority">
                        <option value="low" <?php echo ($defaultPriority === 'low') ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo ($defaultPriority === 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo ($defaultPriority === 'high') ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>

                <!-- Default Task Status -->
                <div id="pref-default-status-field" class="mb-3">
                    <label id="pref-default-status-label" for="default_status" class="form-label">Default Task Status</label>
                    <select id="default_status" class="form-select" name="default_status">
                        <option value="pending" <?php echo ($defaultStatus === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo ($defaultStatus === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                    </select>
                </div>

                <!-- Week Starts On -->
                <div id="pref-week-start-field" class="mb-3">
                    <label id="pref-week-start-label" for="week_start" class="form-label">Week Starts On</label>
                    <select id="week_start" class="form-select" name="week_start">
                        <option value="sunday" <?php echo ($weekStart === 'sunday') ? 'selected' : ''; ?>>Sunday</option>
                        <option value="monday" <?php echo ($weekStart === 'monday') ? 'selected' : ''; ?>>Monday</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div id="preferences-actions" class="d-flex gap-2">
            <button id="save-preferences-btn"
                    type="submit"
                    class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>Save Preferences
            </button>
            <span id="preferences-spinner" class="spinner-border spinner-border-sm htmx-indicator ms-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </span>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle theme switching
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Apply theme immediately to the document
            const theme = this.value;
            const htmlElement = document.documentElement;
            htmlElement.setAttribute('data-bs-theme', theme);

            // Store in sessionStorage for consistency during the session
            sessionStorage.setItem('selectedTheme', theme);
        });
    });

    // Handle HTMX responses for preferences form
    const preferencesForm = document.getElementById('preferences-form');
    if (preferencesForm) {
        preferencesForm.addEventListener('htmx:afterSettle', function(event) {
            const responseDiv = document.getElementById('preferences-response');
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

                    // Clear response message after alert disappears
                    setTimeout(function() {
                        responseDiv.innerHTML = '';
                    }, 5500);
                }
            }
        });
    }
});
</script>
