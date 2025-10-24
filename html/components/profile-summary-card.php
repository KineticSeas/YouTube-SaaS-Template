<?php
/**
 * Profile Summary Card Component
 * Displays user profile picture, basic info, and quick statistics
 */

$initials = getUserInitials($userProfile['first_name'], $userProfile['last_name']);
$avatarColor = getAvatarColor($userId);
$completionRate = $userProfile['completion_rate'] ?? 0;
?>

<div id="profile-card" class="card shadow-sm h-100">
    <!-- Avatar Section -->
    <div id="profile-card-avatar-section" class="card-body text-center border-bottom pb-4">
        <!-- Avatar Container -->
        <div id="profile-avatar-container" class="position-relative d-inline-block mb-3">
            <?php if ($userProfile['avatar_url']): ?>
                <img id="profile-avatar-image"
                     src="<?php echo htmlspecialchars($userProfile['avatar_url']); ?>"
                     alt="<?php echo htmlspecialchars($userProfile['first_name'] . ' ' . $userProfile['last_name']); ?>"
                     class="rounded-circle border border-3 border-light"
                     style="width: 150px; height: 150px; object-fit: cover;">
            <?php else: ?>
                <div id="profile-avatar-fallback"
                     class="rounded-circle d-flex align-items-center justify-content-center border border-3 border-light"
                     style="width: 150px; height: 150px; background-color: <?php echo $avatarColor; ?>;">
                    <span id="profile-avatar-initials" class="text-white fw-bold" style="font-size: 48px;">
                        <?php echo htmlspecialchars($initials); ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Upload Overlay -->
            <button id="profile-avatar-upload-btn"
                    type="button"
                    class="btn btn-sm btn-light rounded-circle position-absolute bottom-0 end-0"
                    style="width: 44px; height: 44px; padding: 0;"
                    data-bs-toggle="modal"
                    data-bs-target="#avatarUploadModal"
                    title="Change profile picture">
                <i class="bi bi-camera-fill"></i>
            </button>
        </div>

        <!-- User Name -->
        <h3 id="profile-card-name" class="card-title mb-2">
            <?php echo htmlspecialchars($userProfile['first_name'] . ' ' . $userProfile['last_name']); ?>
        </h3>

        <!-- Email -->
        <div id="profile-card-email-section" class="mb-3">
            <p id="profile-card-email" class="mb-0 text-muted">
                <?php echo htmlspecialchars($userProfile['email']); ?>
            </p>
            <span id="profile-email-verified-badge" class="badge bg-success">
                <i class="bi bi-check-circle me-1"></i>Verified
            </span>
        </div>

        <!-- Member Since Date -->
        <p id="profile-card-member-since" class="text-muted small mb-0">
            Member since <strong><?php echo date('M d, Y', strtotime($userProfile['created_at'])); ?></strong>
        </p>
    </div>

    <!-- Statistics Section -->
    <div id="profile-card-stats-section" class="card-body border-bottom">
        <!-- Total Tasks -->
        <div id="profile-stat-total" class="d-flex justify-content-between align-items-center mb-3">
            <span id="profile-stat-total-label" class="text-muted">Total Tasks</span>
            <strong id="profile-stat-total-value" class="fs-5"><?php echo $userProfile['total_tasks']; ?></strong>
        </div>

        <!-- Completed Tasks -->
        <div id="profile-stat-completed" class="d-flex justify-content-between align-items-center mb-3">
            <span id="profile-stat-completed-label" class="text-muted">Completed</span>
            <strong id="profile-stat-completed-value" class="fs-5"><?php echo $userProfile['completed_tasks']; ?></strong>
        </div>

        <!-- Completion Rate -->
        <div id="profile-stat-completion-section">
            <div id="profile-stat-completion-header" class="d-flex justify-content-between align-items-center mb-2">
                <span id="profile-stat-completion-label" class="text-muted">Completion Rate</span>
                <strong id="profile-stat-completion-rate" class="text-success"><?php echo $completionRate; ?>%</strong>
            </div>
            <div id="profile-stat-completion-progress" class="progress" style="height: 8px;">
                <div id="profile-completion-bar"
                     class="progress-bar bg-success"
                     role="progressbar"
                     style="width: <?php echo $completionRate; ?>%"
                     aria-valuenow="<?php echo $completionRate; ?>"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
        </div>
    </div>

</div>
