# User Profile System - Implementation Guide

## Setup Instructions

### 1. Run Database Migration

First, run the database setup by accessing in your browser:
```
http://localhost/setup-profile-db.php?key=TEMP_SETUP_KEY_12345
```

After running successfully, **delete the setup-profile-db.php file**.

### 2. Update Login Process

In `api/auth/login-process.php`, add after successful login:
```php
require_once __DIR__ . '/../../includes/user-functions.php';
updateUserLogin($user['id']);
```

## Files to Create

### Profile Page Components

#### 1. `html/profile.php` - Main Profile Page
```php
<?php
$pageTitle = 'My Profile - TodoTracker';
require_once 'includes/auth-check.php';
require_once 'includes/user-functions.php';
require_once 'includes/header.php';

$userId = getCurrentUserId();
$user = getUserProfile($userId);
$preferences = getUserPreferences($userId);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-person-circle me-2"></i>My Profile</h1>
            <p class="text-muted">Manage your account settings and preferences</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Profile Summary -->
        <div class="col-lg-4">
            <?php include 'components/profile-summary-card.php'; ?>
        </div>

        <!-- Right Column: Tabbed Interface -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <!-- Nav Tabs -->
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal-info">
                                <i class="bi bi-person me-1"></i>Personal Info
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security">
                                <i class="bi bi-shield-lock me-1"></i>Security
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#preferences">
                                <i class="bi bi-sliders me-1"></i>Preferences
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity">
                                <i class="bi bi-clock-history me-1"></i>Activity
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Tab Content -->
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="personal-info">
                            <?php include 'components/personal-info-tab.php'; ?>
                        </div>
                        <div class="tab-pane fade" id="security">
                            <?php include 'components/security-tab.php'; ?>
                        </div>
                        <div class="tab-pane fade" id="preferences">
                            <?php include 'components/preferences-tab.php'; ?>
                        </div>
                        <div class="tab-pane fade" id="activity">
                            <?php include 'components/activity-tab.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'components/avatar-upload-modal.php'; ?>

<?php require_once 'includes/footer.php'; ?>
```

#### 2. `html/components/profile-summary-card.php`
```php
<?php
$initials = getUserInitials($user['first_name'], $user['last_name']);
$avatarColor = getAvatarColor($user['id']);
$memberSince = date('F Y', strtotime($user['created_at']));
?>

<div class="card shadow-sm text-center">
    <div class="card-body py-4">
        <!-- Avatar -->
        <div class="position-relative d-inline-block mb-3" style="cursor: pointer;"
             data-bs-toggle="modal" data-bs-target="#avatarUploadModal">
            <?php if ($user['avatar_url']): ?>
                <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>"
                     alt="Profile"
                     class="rounded-circle border border-3"
                     style="width: 150px; height: 150px; object-fit: cover;">
            <?php else: ?>
                <div class="rounded-circle border border-3 d-flex align-items-center justify-content-center"
                     style="width: 150px; height: 150px; background-color: <?php echo $avatarColor; ?>; color: white; font-size: 3rem; font-weight: bold;">
                    <?php echo $initials; ?>
                </div>
            <?php endif; ?>
            <div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2"
                 style="width: 40px; height: 40px;">
                <i class="bi bi-camera"></i>
            </div>
        </div>

        <!-- Name and Email -->
        <h4 class="mb-1"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
        <p class="text-muted mb-2">
            <?php echo htmlspecialchars($user['email']); ?>
            <?php if ($user['email_verified']): ?>
                <i class="bi bi-patch-check-fill text-success ms-1" title="Verified"></i>
            <?php else: ?>
                <i class="bi bi-exclamation-circle-fill text-warning ms-1" title="Not verified"></i>
            <?php endif; ?>
        </p>
        <p class="small text-muted mb-4">
            <i class="bi bi-calendar3 me-1"></i>Member since <?php echo $memberSince; ?>
        </p>

        <!-- Stats -->
        <div class="row text-center mb-4">
            <div class="col-4">
                <h5 class="mb-0"><?php echo $user['total_tasks']; ?></h5>
                <small class="text-muted">Tasks</small>
            </div>
            <div class="col-4">
                <h5 class="mb-0"><?php echo $user['completed_tasks']; ?></h5>
                <small class="text-muted">Completed</small>
            </div>
            <div class="col-4">
                <h5 class="mb-0"><?php echo $user['completion_rate']; ?>%</h5>
                <small class="text-muted">Rate</small>
            </div>
        </div>

        <!-- Action Button -->
        <button class="btn btn-primary w-100" onclick="enableEditMode()">
            <i class="bi bi-pencil me-1"></i>Edit Profile
        </button>
    </div>
</div>
```

### API Endpoints to Create

#### 1. `html/api/user/update-profile.php`
Handle profile updates with validation.

#### 2. `html/api/user/upload-avatar.php`
Process avatar uploads (use `uploadUserAvatar()` function).

#### 3. `html/api/user/update-preferences.php`
Save user preferences (use `updateUserPreferences()` function).

#### 4. `html/api/user/activity-log.php`
Fetch paginated activity log.

#### 5. `html/api/user/change-password.php`
Handle password changes with current password verification.

### Tab Components to Create

1. `html/components/personal-info-tab.php` - Editable profile form
2. `html/components/security-tab.php` - Password change & security settings
3. `html/components/preferences-tab.php` - User preferences form
4. `html/components/activity-tab.php` - Activity log display
5. `html/components/avatar-upload-modal.php` - Avatar upload modal

### Navigation Updates

Update `html/includes/header.php` to add profile link in user dropdown:
```html
<a class="dropdown-item" href="/profile.php">
    <i class="bi bi-person-circle me-2"></i>My Profile
</a>
```

Show user avatar in navbar if available.

## Key Features Implemented

1. ✅ Complete database schema with migrations
2. ✅ User profile management functions
3. ✅ Avatar upload with image processing (resize, crop)
4. ✅ Preferences system
5. ✅ Activity logging system
6. ✅ Helper functions for initials and avatar colors

## Security Features

- Password verification required for sensitive operations
- Image upload validation (type, size, MIME type)
- SQL injection protection via prepared statements
- XSS protection via htmlspecialchars
- File upload security (.htaccess blocks PHP execution)
- Activity logging for audit trail

## Testing Checklist

- [ ] Run database migration successfully
- [ ] Profile page loads correctly
- [ ] Can update personal information
- [ ] Can upload and change avatar
- [ ] Avatar resize works correctly
- [ ] Preferences save and persist
- [ ] Activity log displays correctly
- [ ] Password change works
- [ ] Email validation works
- [ ] Mobile responsive layout works

## Next Steps

1. Create the profile.php main page
2. Build all tab components
3. Create API endpoints
4. Add profile link to navigation
5. Test thoroughly
6. Apply user preferences throughout the app

All helper functions are ready in `includes/user-functions.php` - just call them from your components and API endpoints!
