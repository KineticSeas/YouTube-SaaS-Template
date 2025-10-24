<?php
/**
 * User Profile Page
 * Displays user profile information, account security, preferences, and activity log
 * Requirements: REQ-AUTH-301 through REQ-AUTH-305, REQ-SET-201 through REQ-SET-205
 */

require_once 'includes/auth-check.php';
require_once 'includes/user-functions.php';

$pageTitle = 'My Profile';
$userId = getCurrentUserId();

// Get full user profile with stats
$userProfile = getUserProfile($userId);

if (!$userProfile) {
    $_SESSION['error_message'] = 'Failed to load profile information';
    header('Location: /dashboard.php');
    exit;
}

// Get user preferences
$userPreferences = getUserPreferences($userId);

require_once 'includes/header.php';
?>

<div id="profile-page" class="container-lg py-4">
    <!-- Page Header -->
    <div id="profile-header" class="mb-4">
        <h1 id="profile-title" class="mb-1">My Profile</h1>
        <p id="profile-subtitle" class="text-muted">Manage your account, preferences, and security settings</p>
    </div>

    <!-- Main Profile Container -->
    <div id="profile-container" class="row g-4">
        <!-- Left Column: Profile Summary Card -->
        <div id="profile-left-column" class="col-lg-4">
            <?php require_once 'components/profile-summary-card.php'; ?>
        </div>

        <!-- Right Column: Tabbed Interface -->
        <div id="profile-right-column" class="col-lg-8">
            <!-- Navigation Tabs -->
            <ul id="profile-tabs" class="nav nav-tabs mb-4 border-bottom" role="tablist">
                <li id="profile-tab-personal-item" class="nav-item" role="presentation">
                    <button id="profile-tab-personal-btn" class="nav-link active"
                            type="button" role="tab" aria-selected="true" aria-controls="profile-tab-personal"
                            data-bs-toggle="tab" data-bs-target="#profile-tab-personal">
                        <i class="bi bi-person me-2"></i>Personal Information
                    </button>
                </li>
                <li id="profile-tab-security-item" class="nav-item" role="presentation">
                    <button id="profile-tab-security-btn" class="nav-link"
                            type="button" role="tab" aria-selected="false" aria-controls="profile-tab-security"
                            data-bs-toggle="tab" data-bs-target="#profile-tab-security">
                        <i class="bi bi-shield-lock me-2"></i>Account Security
                    </button>
                </li>
                <li id="profile-tab-preferences-item" class="nav-item" role="presentation">
                    <button id="profile-tab-preferences-btn" class="nav-link"
                            type="button" role="tab" aria-selected="false" aria-controls="profile-tab-preferences"
                            data-bs-toggle="tab" data-bs-target="#profile-tab-preferences">
                        <i class="bi bi-sliders me-2"></i>Preferences
                    </button>
                </li>
                <li id="profile-tab-activity-item" class="nav-item" role="presentation">
                    <button id="profile-tab-activity-btn" class="nav-link"
                            type="button" role="tab" aria-selected="false" aria-controls="profile-tab-activity"
                            data-bs-toggle="tab" data-bs-target="#profile-tab-activity">
                        <i class="bi bi-clock-history me-2"></i>Activity Log
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div id="profile-tab-content" class="tab-content">
                <!-- Personal Information Tab -->
                <div id="profile-tab-personal" class="tab-pane fade show active" role="tabpanel">
                    <?php require_once 'components/profile-tab-personal.php'; ?>
                </div>

                <!-- Account Security Tab -->
                <div id="profile-tab-security" class="tab-pane fade" role="tabpanel">
                    <?php require_once 'components/profile-tab-security.php'; ?>
                </div>

                <!-- Preferences Tab -->
                <div id="profile-tab-preferences" class="tab-pane fade" role="tabpanel">
                    <?php require_once 'components/profile-tab-preferences.php'; ?>
                </div>

                <!-- Activity Log Tab -->
                <div id="profile-tab-activity" class="tab-pane fade" role="tabpanel">
                    <?php require_once 'components/profile-tab-activity.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php
require_once 'components/avatar-upload-modal.php';
require_once 'components/confirm-delete-account-modal.php';
?>

<?php require_once 'includes/footer.php'; ?>
