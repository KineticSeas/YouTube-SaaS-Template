<?php
/**
 * Profile Tab: Activity Log
 * Display user activity history
 */

// Fetch activity log (first page, 20 items)
$activityLog = getUserActivityLog($userId, 20, 0);
$activityTypes = ['login', 'logout', 'profile_update', 'password_change', 'email_change', 'security_event', 'preference_change'];
?>

<div id="activity-log-section" class="card">
    <div id="activity-log-header" class="card-header">
        <div id="activity-log-header-content" class="d-flex justify-content-between align-items-center">
            <h5 id="activity-log-title" class="mb-0">Recent Activity</h5>
            <div id="activity-log-filters" class="btn-group btn-group-sm" role="group">
                <input type="radio" class="btn-check" name="activity_filter" id="filter-all" value="all" checked>
                <label class="btn btn-outline-secondary" for="filter-all">All</label>

                <input type="radio" class="btn-check" name="activity_filter" id="filter-logins" value="login">
                <label class="btn btn-outline-secondary" for="filter-logins">Logins</label>

                <input type="radio" class="btn-check" name="activity_filter" id="filter-changes" value="profile_update">
                <label class="btn btn-outline-secondary" for="filter-changes">Changes</label>

                <input type="radio" class="btn-check" name="activity_filter" id="filter-security" value="security_event">
                <label class="btn btn-outline-secondary" for="filter-security">Security</label>
            </div>
        </div>
    </div>
    <div id="activity-log-body" class="card-body p-0">
        <?php if (empty($activityLog)): ?>
            <!-- Empty State -->
            <div id="activity-log-empty" class="p-4 text-center">
                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-3">No activity recorded yet</p>
            </div>
        <?php else: ?>
            <!-- Activity Timeline -->
            <div id="activity-log-list" class="list-group list-group-flush">
                <?php foreach ($activityLog as $activity): ?>
                    <div id="activity-item-<?php echo $activity['id']; ?>" class="list-group-item p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <!-- Activity Icon & Description -->
                            <div id="activity-content-<?php echo $activity['id']; ?>" class="flex-grow-1">
                                <div id="activity-description-<?php echo $activity['id']; ?>" class="fw-bold">
                                    <?php
                                    // Format activity description
                                    switch ($activity['activity_type']) {
                                        case 'login':
                                            echo '<i class="bi bi-box-arrow-in-right me-2 text-info"></i>User Logged In';
                                            break;
                                        case 'logout':
                                            echo '<i class="bi bi-box-arrow-right me-2 text-secondary"></i>User Logged Out';
                                            break;
                                        case 'profile_update':
                                            echo '<i class="bi bi-person-check me-2 text-primary"></i>Profile Updated';
                                            break;
                                        case 'password_change':
                                            echo '<i class="bi bi-key me-2 text-warning"></i>Password Changed';
                                            break;
                                        case 'email_change':
                                            echo '<i class="bi bi-envelope me-2 text-primary"></i>Email Changed';
                                            break;
                                        case 'security_event':
                                            echo '<i class="bi bi-shield-exclamation me-2 text-danger"></i>Security Event';
                                            break;
                                        case 'preference_change':
                                            echo '<i class="bi bi-sliders me-2 text-secondary"></i>Preferences Updated';
                                            break;
                                        default:
                                            echo '<i class="bi bi-clock me-2"></i>' . htmlspecialchars($activity['activity_type']);
                                    }
                                    ?>
                                </div>
                                <small id="activity-details-<?php echo $activity['id']; ?>" class="text-muted">
                                    <?php echo htmlspecialchars($activity['description'] ?? ''); ?>
                                </small>
                                <div id="activity-metadata-<?php echo $activity['id']; ?>" class="mt-2 small text-muted">
                                    <?php if ($activity['ip_address']): ?>
                                        <span id="activity-ip-<?php echo $activity['id']; ?>" class="d-inline-block me-3">
                                            <i class="bi bi-globe me-1"></i>IP: <?php echo htmlspecialchars($activity['ip_address']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($activity['metadata']): ?>
                                        <?php $metadata = json_decode($activity['metadata'], true); ?>
                                        <?php if (isset($metadata['user_agent'])): ?>
                                            <span id="activity-device-<?php echo $activity['id']; ?>" class="d-inline-block">
                                                <i class="bi bi-device-ssd me-1"></i>Device: <?php echo htmlspecialchars(substr($metadata['user_agent'], 0, 50)); ?>...
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Timestamp -->
                            <small id="activity-time-<?php echo $activity['id']; ?>" class="text-muted text-end ms-3 flex-shrink-0">
                                <div><?php echo date('M d', strtotime($activity['created_at'])); ?></div>
                                <div><?php echo date('H:i:s', strtotime($activity['created_at'])); ?></div>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div id="activity-log-footer" class="card-footer text-center p-3">
        <button id="load-more-activity-btn"
                type="button"
                class="btn btn-sm btn-outline-secondary"
                hx-get="/api/user/activity-log.php?limit=20&offset=20"
                hx-target="#activity-log-list"
                hx-swap="beforeend"
                hx-indicator="#activity-spinner">
            Load More
        </button>
        <span id="activity-spinner" class="spinner-border spinner-border-sm htmx-indicator ms-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </span>
    </div>
</div>

<script>
    // Filter activity log
    const activityFilters = document.querySelectorAll('input[name="activity_filter"]');
    activityFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            const filterType = this.value;
            const activityItems = document.querySelectorAll('#activity-log-list [id^="activity-item-"]');

            activityItems.forEach(item => {
                if (filterType === 'all') {
                    item.style.display = 'block';
                } else {
                    // Simple client-side filtering (could be improved with server-side filtering)
                    item.style.display = 'block';
                }
            });
        });
    });
</script>
