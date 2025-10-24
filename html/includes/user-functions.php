<?php
/**
 * User Profile Management Functions
 * Helper functions for user profile, preferences, and activity tracking
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get full user profile data
 * @param int $userId - User ID
 * @return array|null - User profile data or null if not found
 */
function getUserProfile($userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            SELECT id, email, first_name, last_name, avatar_url, bio, phone,
                   location, timezone, email_verified, created_at, last_login_at,
                   last_login_ip, account_status
            FROM users
            WHERE id = ? AND account_status = 'active' AND deleted_at IS NULL
        ");

        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Get task statistics
            $statsStmt = $conn->prepare("
                SELECT
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
                FROM tasks
                WHERE user_id = ? AND is_deleted = 0
            ");
            $statsStmt->execute([$userId]);
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

            $user['total_tasks'] = $stats['total_tasks'];
            $user['completed_tasks'] = $stats['completed_tasks'];
            $user['completion_rate'] = $stats['total_tasks'] > 0
                ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100)
                : 0;
        }

        return $user ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching user profile: " . $e->getMessage());
        return null;
    }
}

/**
 * Update user profile information
 * @param int $userId - User ID
 * @param array $data - Profile data to update
 * @return array - ['success' => bool, 'error' => string|null]
 */
function updateUserProfile($userId, $data) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'bio', 'location', 'timezone'];
        $updates = [];
        $params = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return ['success' => false, 'error' => 'No fields to update'];
        }

        // If email is being changed, check if it's unique
        if (isset($data['email'])) {
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$data['email'], $userId]);
            if ($checkStmt->fetch()) {
                return ['success' => false, 'error' => 'Email address is already in use'];
            }
        }

        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Log activity
        logUserActivity($userId, 'profile_update', 'Profile information updated', [
            'fields_updated' => array_keys(array_filter($data, fn($k) => in_array($k, $allowedFields), ARRAY_FILTER_USE_KEY))
        ]);

        return ['success' => true, 'error' => null];
    } catch (PDOException $e) {
        error_log("Error updating user profile: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update profile'];
    }
}

/**
 * Upload and save user avatar
 * @param int $userId - User ID
 * @param array $file - Uploaded file from $_FILES
 * @return array - ['success' => bool, 'avatar_url' => string|null, 'error' => string|null]
 */
function uploadUserAvatar($userId, $file) {
    try {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'avatar_url' => null, 'error' => 'No file uploaded'];
        }

        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'avatar_url' => null, 'error' => 'File size must be less than 5MB'];
        }

        // Validate it's an actual image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'avatar_url' => null, 'error' => 'File is not a valid image'];
        }

        // Check image type
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
        if (!in_array($imageInfo[2], $allowedTypes)) {
            return ['success' => false, 'avatar_url' => null, 'error' => 'Only JPG, PNG, and GIF images are allowed'];
        }

        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = image_type_to_extension($imageInfo[2], true);
        $filename = 'avatar_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(8)) . $extension;
        $filepath = $uploadDir . '/' . $filename;

        // Resize and save image
        $resized = resizeImage($file['tmp_name'], $filepath, 300, 300, $imageInfo[2]);
        if (!$resized) {
            return ['success' => false, 'avatar_url' => null, 'error' => 'Failed to process image'];
        }

        // Get old avatar to delete
        $db = getDatabase();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT avatar_url FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $oldAvatar = $stmt->fetchColumn();

        // Update database
        $avatarUrl = '/uploads/avatars/' . $filename;
        $updateStmt = $conn->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
        $updateStmt->execute([$avatarUrl, $userId]);

        // Delete old avatar if exists
        if ($oldAvatar && file_exists(__DIR__ . '/..' . $oldAvatar)) {
            @unlink(__DIR__ . '/..' . $oldAvatar);
        }

        // Log activity
        logUserActivity($userId, 'profile_update', 'Avatar updated', ['avatar_url' => $avatarUrl]);

        return ['success' => true, 'avatar_url' => $avatarUrl, 'error' => null];
    } catch (Exception $e) {
        error_log("Error uploading avatar: " . $e->getMessage());
        return ['success' => false, 'avatar_url' => null, 'error' => 'Failed to upload avatar'];
    }
}

/**
 * Resize image to specified dimensions
 * @param string $sourcePath - Source image path
 * @param string $destPath - Destination path
 * @param int $maxWidth - Maximum width
 * @param int $maxHeight - Maximum height
 * @param int $imageType - Image type constant
 * @return bool - Success status
 */
function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight, $imageType) {
    // Create image resource based on type
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    if (!$source) return false;

    $width = imagesx($source);
    $height = imagesy($source);

    // Calculate new dimensions (square crop from center)
    $size = min($width, $height);
    $x = ($width - $size) / 2;
    $y = ($height - $size) / 2;

    // Create new image
    $dest = imagecreatetruecolor($maxWidth, $maxHeight);

    // Preserve transparency for PNG and GIF
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
    }

    // Copy and resize
    imagecopyresampled($dest, $source, 0, 0, $x, $y, $maxWidth, $maxHeight, $size, $size);

    // Save based on type
    $result = false;
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($dest, $destPath, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($dest, $destPath, 9);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($dest, $destPath);
            break;
    }

    imagedestroy($source);
    imagedestroy($dest);

    return $result;
}

/**
 * Get user preferences
 * @param int $userId - User ID
 * @param string|null $key - Specific preference key (optional)
 * @return mixed - Preference value or array of all preferences
 */
function getUserPreferences($userId, $key = null) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if ($key) {
            $stmt = $conn->prepare("SELECT pref_value FROM user_preferences WHERE user_id = ? AND pref_key = ?");
            $stmt->execute([$userId, $key]);
            return $stmt->fetchColumn();
        }

        $stmt = $conn->prepare("SELECT pref_key, pref_value FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);

        $preferences = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $preferences[$row['pref_key']] = $row['pref_value'];
        }

        return $preferences;
    } catch (PDOException $e) {
        error_log("Error fetching user preferences: " . $e->getMessage());
        return $key ? null : [];
    }
}

/**
 * Update user preference
 * @param int $userId - User ID
 * @param string $key - Preference key
 * @param mixed $value - Preference value
 * @return bool - Success status
 */
function updateUserPreference($userId, $key, $value) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            INSERT INTO user_preferences (user_id, pref_key, pref_value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE pref_value = ?, updated_at = NOW()
        ");

        $stmt->execute([$userId, $key, $value, $value]);

        return true;
    } catch (PDOException $e) {
        error_log("Error updating user preference: " . $e->getMessage());
        return false;
    }
}

/**
 * Update multiple user preferences at once
 * @param int $userId - User ID
 * @param array $preferences - Array of key => value preferences
 * @return bool - Success status
 */
function updateUserPreferences($userId, $preferences) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $conn->beginTransaction();

        foreach ($preferences as $key => $value) {
            updateUserPreference($userId, $key, $value);
        }

        $conn->commit();

        // Log activity
        logUserActivity($userId, 'preference_change', 'Preferences updated', [
            'preferences' => array_keys($preferences)
        ]);

        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error updating user preferences: " . $e->getMessage());
        return false;
    }
}

/**
 * Log user activity
 * @param int $userId - User ID
 * @param string $type - Activity type
 * @param string $description - Activity description
 * @param array $metadata - Additional metadata (optional)
 * @return bool - Success status
 */
function logUserActivity($userId, $type, $description, $metadata = []) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $metadataJson = !empty($metadata) ? json_encode($metadata) : null;

        $stmt = $conn->prepare("
            INSERT INTO user_activity_log (user_id, activity_type, description, ip_address, user_agent, metadata)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$userId, $type, $description, $ipAddress, $userAgent, $metadataJson]);

        return true;
    } catch (PDOException $e) {
        error_log("Error logging user activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user activity log
 * @param int $userId - User ID
 * @param int $limit - Number of records to return
 * @param int $offset - Offset for pagination
 * @param string|null $filter - Filter by activity type
 * @return array - Activity log entries
 */
function getUserActivityLog($userId, $limit = 20, $offset = 0, $filter = null) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $sql = "SELECT * FROM user_activity_log WHERE user_id = ?";
        $params = [$userId];

        if ($filter) {
            $sql .= " AND activity_type = ?";
            $params[] = $filter;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching activity log: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user initials for avatar fallback
 * @param string $firstName - First name
 * @param string $lastName - Last name
 * @return string - Initials (e.g., "JD")
 */
function getUserInitials($firstName, $lastName) {
    $first = !empty($firstName) ? strtoupper(substr($firstName, 0, 1)) : '';
    $last = !empty($lastName) ? strtoupper(substr($lastName, 0, 1)) : '';
    return $first . $last;
}

/**
 * Generate avatar color based on user ID
 * @param int $userId - User ID
 * @return string - Hex color code
 */
function getAvatarColor($userId) {
    $colors = [
        '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545',
        '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
    ];
    return $colors[$userId % count($colors)];
}

/**
 * Update user login information
 * @param int $userId - User ID
 * @return bool - Success status
 */
function updateUserLogin($userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        $stmt = $conn->prepare("
            UPDATE users
            SET last_login_at = NOW(), last_login_ip = ?
            WHERE id = ?
        ");

        $stmt->execute([$ipAddress, $userId]);

        // Log login activity
        logUserActivity($userId, 'login', 'User logged in', [
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        return true;
    } catch (PDOException $e) {
        error_log("Error updating user login: " . $e->getMessage());
        return false;
    }
}
