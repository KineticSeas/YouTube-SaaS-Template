<?php
/**
 * Task Management Functions
 * Database helper functions for task CRUD operations
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Create a new task
 * REQ-TASK-001 through REQ-TASK-008
 *
 * @param int $userId - User ID (task owner)
 * @param string $title - Task title (required, max 255 chars)
 * @param string $description - Task description (optional, max 5000 chars)
 * @param string $status - Task status (pending, in_progress, completed)
 * @param string $priority - Task priority (low, medium, high)
 * @param string|null $dueDate - Due date (YYYY-MM-DD format)
 * @return array - ['success' => bool, 'task_id' => int|null, 'error' => string|null]
 */
function createTask($userId, $title, $description = '', $status = 'pending', $priority = 'medium', $dueDate = null) {
    try {
        // Validation
        if (empty($title)) {
            return ['success' => false, 'task_id' => null, 'error' => 'Title is required'];
        }

        if (strlen($title) > 255) {
            return ['success' => false, 'task_id' => null, 'error' => 'Title must be less than 255 characters'];
        }

        if (strlen($description) > 5000) {
            return ['success' => false, 'task_id' => null, 'error' => 'Description must be less than 5000 characters'];
        }

        // Validate status
        $validStatuses = ['pending', 'in_progress', 'completed'];
        if (!in_array($status, $validStatuses)) {
            $status = 'pending';
        }

        // Validate priority
        $validPriorities = ['low', 'medium', 'high'];
        if (!in_array($priority, $validPriorities)) {
            $priority = 'medium';
        }

        // Validate due date
        if ($dueDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            return ['success' => false, 'task_id' => null, 'error' => 'Invalid due date format'];
        }

        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'task_id' => null, 'error' => 'Database connection failed'];
        }

        $stmt = $conn->prepare("
            INSERT INTO tasks (user_id, title, description, status, priority, due_date, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([$userId, $title, $description, $status, $priority, $dueDate]);

        $taskId = $conn->lastInsertId();

        // Add to history
        addTaskHistory($taskId, $userId, 'created');

        return [
            'success' => true,
            'task_id' => $taskId,
            'error' => null
        ];

    } catch (PDOException $e) {
        error_log("Error creating task: " . $e->getMessage());
        return ['success' => false, 'task_id' => null, 'error' => 'Failed to create task'];
    }
}

/**
 * Get tasks by user ID with optional filters
 * REQ-TASK-101 through REQ-TASK-107
 *
 * @param int $userId - User ID
 * @param array $filters - Optional filters:
 *   - status: string (pending, in_progress, completed)
 *   - priority: string (low, medium, high)
 *   - search: string (search in title and description)
 *   - categories: array of category IDs (OR logic - tasks with any of these categories)
 *   - limit: int (default 50)
 *   - offset: int (default 0)
 *   - order_by: string (created_at, due_date, priority) default created_at
 *   - order_dir: string (ASC, DESC) default DESC
 * @return array - Array of task objects or empty array on error
 */
function getTasksByUserId($userId, $filters = []) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return [];
        }

        // Check if category filter is applied
        $hasCategoryFilter = !empty($filters['categories']) && is_array($filters['categories']);

        // Build query - use JOIN if filtering by categories
        if ($hasCategoryFilter) {
            $sql = "SELECT DISTINCT t.* FROM tasks t
                    INNER JOIN task_categories tc ON t.id = tc.task_id
                    WHERE t.user_id = ? AND t.is_deleted = 0";
        } else {
            $sql = "SELECT * FROM tasks WHERE user_id = ? AND is_deleted = 0";
        }
        $params = [$userId];

        // Apply category filter
        if ($hasCategoryFilter) {
            $categoryIds = array_map('intval', $filters['categories']);
            $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
            $sql .= " AND tc.category_id IN ($placeholders)";
            $params = array_merge($params, $categoryIds);
        }

        // Apply other filters
        // Status filter (supports array or single value)
        if (!empty($filters['status'])) {
            $prefix = $hasCategoryFilter ? 't.' : '';
            if (is_array($filters['status'])) {
                $placeholders = str_repeat('?,', count($filters['status']) - 1) . '?';
                $sql .= " AND {$prefix}status IN ($placeholders)";
                $params = array_merge($params, $filters['status']);
            } else {
                $sql .= " AND {$prefix}status = ?";
                $params[] = $filters['status'];
            }
        }

        // Priority filter (supports array or single value)
        if (!empty($filters['priority'])) {
            $prefix = $hasCategoryFilter ? 't.' : '';
            if (is_array($filters['priority'])) {
                $placeholders = str_repeat('?,', count($filters['priority']) - 1) . '?';
                $sql .= " AND {$prefix}priority IN ($placeholders)";
                $params = array_merge($params, $filters['priority']);
            } else {
                $sql .= " AND {$prefix}priority = ?";
                $params[] = $filters['priority'];
            }
        }

        // Search filter
        if (!empty($filters['search'])) {
            $prefix = $hasCategoryFilter ? 't.' : '';
            $sql .= " AND ({$prefix}title LIKE ? OR {$prefix}description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $prefix = $hasCategoryFilter ? 't.' : '';
            $sql .= " AND {$prefix}due_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $prefix = $hasCategoryFilter ? 't.' : '';
            $sql .= " AND {$prefix}due_date <= ?";
            $params[] = $filters['date_to'];
        }

        // Archived filter
        if (!empty($filters['archived'])) {
            $prefix = $hasCategoryFilter ? 't.' : '';
            $sql .= " AND {$prefix}is_archived = 1";
        } else {
            $prefix = $hasCategoryFilter ? 't.' : '';
            $sql .= " AND {$prefix}is_archived = 0";
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $validOrderBy = ['created_at', 'updated_at', 'due_date', 'priority', 'title', 'status'];
        if (!in_array($orderBy, $validOrderBy)) {
            $orderBy = 'created_at';
        }

        $orderDir = strtoupper($filters['order_dir'] ?? 'DESC');
        if (!in_array($orderDir, ['ASC', 'DESC'])) {
            $orderDir = 'DESC';
        }

        $prefix = $hasCategoryFilter ? 't.' : '';

        // Handle NULL values for due_date sorting
        if ($orderBy === 'due_date') {
            // Sort by due_date, putting NULL values at the end
            if ($orderDir === 'ASC') {
                $sql .= " ORDER BY {$prefix}due_date IS NULL ASC, {$prefix}due_date ASC";
            } else {
                $sql .= " ORDER BY {$prefix}due_date IS NOT NULL DESC, {$prefix}due_date DESC";
            }
        } else {
            $sql .= " ORDER BY {$prefix}$orderBy $orderDir";
        }

        // Limit and offset
        $limit = (int)($filters['limit'] ?? 50);
        $offset = (int)($filters['offset'] ?? 0);
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching tasks: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single task by ID and user ID
 * REQ-TASK-201
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @return array|null - Task object or null if not found
 */
function getTaskById($taskId, $userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return null;
        }

        $stmt = $conn->prepare("
            SELECT * FROM tasks
            WHERE id = ? AND user_id = ? AND is_deleted = 0
        ");

        $stmt->execute([$taskId, $userId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        return $task ?: null;

    } catch (PDOException $e) {
        error_log("Error fetching task: " . $e->getMessage());
        return null;
    }
}

/**
 * Update a task
 * REQ-TASK-301 through REQ-TASK-305
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @param array $data - Data to update (title, description, status, priority, due_date)
 * @return array - ['success' => bool, 'error' => string|null]
 */
function updateTask($taskId, $userId, $data) {
    try {
        // Verify task exists and belongs to user
        $task = getTaskById($taskId, $userId);
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found'];
        }

        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Build update query dynamically based on provided data
        $updateFields = [];
        $params = [];

        // Track changes for history
        $changes = [];

        if (isset($data['title'])) {
            if (empty($data['title']) || strlen($data['title']) > 255) {
                return ['success' => false, 'error' => 'Invalid title'];
            }
            $updateFields[] = "title = ?";
            $params[] = $data['title'];
            $changes[] = ['field' => 'title', 'old' => $task['title'], 'new' => $data['title']];
        }

        if (isset($data['description'])) {
            if (strlen($data['description']) > 5000) {
                return ['success' => false, 'error' => 'Description too long'];
            }
            $updateFields[] = "description = ?";
            $params[] = $data['description'];
            $changes[] = ['field' => 'description', 'old' => $task['description'], 'new' => $data['description']];
        }

        if (isset($data['status'])) {
            $validStatuses = ['pending', 'in_progress', 'completed'];
            if (!in_array($data['status'], $validStatuses)) {
                return ['success' => false, 'error' => 'Invalid status'];
            }
            $updateFields[] = "status = ?";
            $params[] = $data['status'];
            $changes[] = ['field' => 'status', 'old' => $task['status'], 'new' => $data['status']];

            // Set completed_at if status is completed
            if ($data['status'] === 'completed') {
                $updateFields[] = "completed_at = NOW()";
            }
        }

        if (isset($data['priority'])) {
            $validPriorities = ['low', 'medium', 'high'];
            if (!in_array($data['priority'], $validPriorities)) {
                return ['success' => false, 'error' => 'Invalid priority'];
            }
            $updateFields[] = "priority = ?";
            $params[] = $data['priority'];
            $changes[] = ['field' => 'priority', 'old' => $task['priority'], 'new' => $data['priority']];
        }

        if (isset($data['due_date'])) {
            if ($data['due_date'] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['due_date'])) {
                return ['success' => false, 'error' => 'Invalid due date'];
            }
            $updateFields[] = "due_date = ?";
            $params[] = $data['due_date'] ?: null;
            $changes[] = ['field' => 'due_date', 'old' => $task['due_date'], 'new' => $data['due_date']];
        }

        if (empty($updateFields)) {
            return ['success' => false, 'error' => 'No data to update'];
        }

        // Add updated_at
        $updateFields[] = "updated_at = NOW()";

        // Add task ID and user ID to params
        $params[] = $taskId;
        $params[] = $userId;

        $sql = "UPDATE tasks SET " . implode(', ', $updateFields) . " WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Log changes to history
        foreach ($changes as $change) {
            // Only log if value actually changed
            if ($change['old'] != $change['new']) {
                addTaskHistory($taskId, $userId, 'updated', $change['field'], $change['old'], $change['new']);
            }
        }

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error updating task: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update task'];
    }
}

/**
 * Delete a task (soft delete)
 * REQ-TASK-401
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @return array - ['success' => bool, 'error' => string|null]
 */
function deleteTask($taskId, $userId) {
    try {
        // Verify task exists and belongs to user
        $task = getTaskById($taskId, $userId);
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found'];
        }

        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        $stmt = $conn->prepare("
            UPDATE tasks
            SET is_deleted = 1, deleted_at = NOW(), updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$taskId, $userId]);

        // Add to history
        addTaskHistory($taskId, $userId, 'deleted');

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error deleting task: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to delete task'];
    }
}


/**
 * Get user task statistics for dashboard
 * Returns count of tasks by status
 *
 * @param int $userId - User ID
 * @return array - ['total' => int, 'pending' => int, 'in_progress' => int, 'completed' => int]
 */
function getUserTaskStats($userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];
        }

        $stmt = $conn->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM tasks
            WHERE user_id = ? AND is_deleted = 0 AND is_archived = 0
        ");

        $stmt->execute([$userId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => (int)$stats['total'],
            'pending' => (int)$stats['pending'],
            'in_progress' => (int)$stats['in_progress'],
            'completed' => (int)$stats['completed']
        ];

    } catch (PDOException $e) {
        error_log("Error fetching task stats: " . $e->getMessage());
        return ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];
    }
}

/**
 * Get upcoming tasks (due in next 7 days)
 *
 * @param int $userId - User ID
 * @param int $days - Number of days to look ahead (default 7)
 * @return array - Array of task objects
 */
function getUpcomingTasks($userId, $days = 7) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("
            SELECT * FROM tasks
            WHERE user_id = ?
                AND is_deleted = 0
                AND is_archived = 0
                AND status != 'completed'
                AND due_date IS NOT NULL
                AND due_date >= CURDATE()
                AND due_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY due_date ASC
            LIMIT 10
        ");

        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching upcoming tasks: " . $e->getMessage());
        return [];
    }
}

/**
 * Get overdue tasks
 *
 * @param int $userId - User ID
 * @return array - Array of task objects
 */
function getOverdueTasks($userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("
            SELECT * FROM tasks
            WHERE user_id = ?
                AND is_deleted = 0
                AND is_archived = 0
                AND status != 'completed'
                AND due_date IS NOT NULL
                AND due_date < CURDATE()
            ORDER BY due_date ASC
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching overdue tasks: " . $e->getMessage());
        return [];
    }
}

/**
 * Add task history entry
 * REQ-TASK-202: Track task modification history
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID who made the change
 * @param string $action - Action performed (created, updated, deleted, etc.)
 * @param string|null $fieldName - Field that was changed
 * @param string|null $oldValue - Previous value
 * @param string|null $newValue - New value
 * @return bool - True on success, false on failure
 */
function addTaskHistory($taskId, $userId, $action, $fieldName = null, $oldValue = null, $newValue = null) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return false;
        }

        $stmt = $conn->prepare("
            INSERT INTO task_history (task_id, user_id, action, field_name, old_value, new_value, changed_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([$taskId, $userId, $action, $fieldName, $oldValue, $newValue]);
        return true;

    } catch (PDOException $e) {
        error_log("Error adding task history: " . $e->getMessage());
        return false;
    }
}

/**
 * Get task history
 * REQ-TASK-202
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @return array - Array of history entries
 */
function getTaskHistory($taskId, $userId) {
    try {
        // Verify task ownership
        $task = getTaskById($taskId, $userId);
        if (!$task) {
            return [];
        }

        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("
            SELECT th.*, u.first_name, u.last_name
            FROM task_history th
            JOIN users u ON th.user_id = u.id
            WHERE th.task_id = ?
            ORDER BY th.changed_at DESC
        ");

        $stmt->execute([$taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching task history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get tasks due today
 * REQ-DASH-002
 *
 * @param int $userId - User ID
 * @return int - Count of tasks due today
 */
function getTasksDueToday($userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return 0;
        }

        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM tasks
            WHERE user_id = ?
                AND is_deleted = 0
                AND is_archived = 0
                AND status != 'completed'
                AND due_date = CURDATE()
        ");

        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['count'];

    } catch (PDOException $e) {
        error_log("Error fetching tasks due today: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get completion rate percentage
 * REQ-DASH-301, REQ-DASH-302
 *
 * @param int $userId - User ID
 * @return float - Completion rate as percentage (0-100)
 */
function getCompletionRate($userId) {
    $stats = getUserTaskStats($userId);

    if ($stats['total'] == 0) {
        return 0;
    }

    return round(($stats['completed'] / $stats['total']) * 100, 1);
}

/**
 * Get count of overdue tasks
 * REQ-DASH-003
 *
 * @param int $userId - User ID
 * @return int - Count of overdue tasks
 */
function getOverdueTasksCount($userId) {
    $overdueTasks = getOverdueTasks($userId);
    return count($overdueTasks);
}

/**
 * Update task status only
 * Quick status update for buttons
 * REQ-TASK-203, REQ-TASK-204
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @param string $newStatus - New status
 * @return array - ['success' => bool, 'error' => string|null, 'task' => array|null]
 */
function updateTaskStatus($taskId, $userId, $newStatus) {
    try {
        // Verify task exists and belongs to user
        $task = getTaskById($taskId, $userId);
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found', 'task' => null];
        }

        // Validate status
        $validStatuses = ['pending', 'in_progress', 'completed'];
        if (!in_array($newStatus, $validStatuses)) {
            return ['success' => false, 'error' => 'Invalid status', 'task' => null];
        }

        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed', 'task' => null];
        }

        // Update status
        $oldStatus = $task['status'];

        if ($newStatus === 'completed') {
            $stmt = $conn->prepare("
                UPDATE tasks
                SET status = ?, completed_at = NOW(), updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
        } else {
            $stmt = $conn->prepare("
                UPDATE tasks
                SET status = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
        }

        $stmt->execute([$newStatus, $taskId, $userId]);

        // Add to history
        addTaskHistory($taskId, $userId, 'status_changed', 'status', $oldStatus, $newStatus);

        // Fetch updated task
        $updatedTask = getTaskById($taskId, $userId);

        return ['success' => true, 'error' => null, 'task' => $updatedTask];

    } catch (PDOException $e) {
        error_log("Error updating task status: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update status', 'task' => null];
    }
}

// ============================================================================
// CATEGORY MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Create a new category
 * REQ-CAT-001, REQ-CAT-002
 *
 * @param int $userId - User ID (category owner)
 * @param string $name - Category name (required, max 50 chars)
 * @param string $color - Hex color code (e.g., #0d6efd)
 * @return array - ['success' => bool, 'category_id' => int|null, 'error' => string|null]
 */
function createCategory($userId, $name, $color = '#6c757d') {
    try {
        // Validation
        if (empty($name)) {
            return ['success' => false, 'category_id' => null, 'error' => 'Category name is required'];
        }

        $name = trim($name);
        if (strlen($name) > 50) {
            return ['success' => false, 'category_id' => null, 'error' => 'Category name must be less than 50 characters'];
        }

        // Validate color format (hex color)
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $color = '#6c757d'; // Default to Bootstrap secondary color
        }

        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'category_id' => null, 'error' => 'Database connection failed'];
        }

        // Check if category name already exists for this user
        $stmt = $conn->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ?");
        $stmt->execute([$userId, $name]);

        if ($stmt->fetch()) {
            return ['success' => false, 'category_id' => null, 'error' => 'Category name already exists'];
        }

        // Insert category
        $stmt = $conn->prepare("
            INSERT INTO categories (user_id, name, color)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$userId, $name, $color]);

        $categoryId = (int)$conn->lastInsertId();

        return [
            'success' => true,
            'category_id' => $categoryId,
            'error' => null
        ];

    } catch (PDOException $e) {
        error_log("Error creating category: " . $e->getMessage());
        return ['success' => false, 'category_id' => null, 'error' => 'Failed to create category'];
    }
}

/**
 * Get all categories for a user
 * REQ-CAT-003
 *
 * @param int $userId - User ID
 * @param bool $withTaskCount - Include task count for each category
 * @return array - Array of categories
 */
function getCategoriesByUserId($userId, $withTaskCount = false) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return [];
        }

        if ($withTaskCount) {
            // Get categories with task count
            $stmt = $conn->prepare("
                SELECT
                    c.id,
                    c.user_id,
                    c.name,
                    c.color,
                    c.created_at,
                    c.updated_at,
                    COUNT(tc.task_id) as task_count
                FROM categories c
                LEFT JOIN task_categories tc ON c.id = tc.category_id
                LEFT JOIN tasks t ON tc.task_id = t.id AND t.is_deleted = 0
                WHERE c.user_id = ?
                GROUP BY c.id
                ORDER BY c.name ASC
            ");
        } else {
            // Get categories without task count
            $stmt = $conn->prepare("
                SELECT id, user_id, name, color, created_at, updated_at
                FROM categories
                WHERE user_id = ?
                ORDER BY name ASC
            ");
        }

        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single category by ID
 * REQ-CAT-004
 *
 * @param int $categoryId - Category ID
 * @param int $userId - User ID (for ownership verification)
 * @return array|null - Category data or null
 */
function getCategoryById($categoryId, $userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return null;
        }

        $stmt = $conn->prepare("
            SELECT id, user_id, name, color, created_at, updated_at
            FROM categories
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$categoryId, $userId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        return $category ?: null;

    } catch (PDOException $e) {
        error_log("Error fetching category: " . $e->getMessage());
        return null;
    }
}

/**
 * Update a category
 * REQ-CAT-005
 *
 * @param int $categoryId - Category ID
 * @param int $userId - User ID (for ownership verification)
 * @param string $name - New category name
 * @param string $color - New hex color code
 * @return array - ['success' => bool, 'error' => string|null]
 */
function updateCategory($categoryId, $userId, $name, $color) {
    try {
        // Verify category exists and belongs to user
        $category = getCategoryById($categoryId, $userId);
        if (!$category) {
            return ['success' => false, 'error' => 'Category not found'];
        }

        // Validation
        if (empty($name)) {
            return ['success' => false, 'error' => 'Category name is required'];
        }

        $name = trim($name);
        if (strlen($name) > 50) {
            return ['success' => false, 'error' => 'Category name must be less than 50 characters'];
        }

        // Validate color format
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return ['success' => false, 'error' => 'Invalid color format'];
        }

        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Check if new name conflicts with another category
        if ($name !== $category['name']) {
            $stmt = $conn->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ? AND id != ?");
            $stmt->execute([$userId, $name, $categoryId]);

            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'Category name already exists'];
            }
        }

        // Update category
        $stmt = $conn->prepare("
            UPDATE categories
            SET name = ?, color = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$name, $color, $categoryId, $userId]);

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error updating category: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update category'];
    }
}

/**
 * Delete a category
 * REQ-CAT-006, REQ-CAT-007
 * Note: CASCADE delete will remove task_categories entries automatically
 *
 * @param int $categoryId - Category ID
 * @param int $userId - User ID (for ownership verification)
 * @return array - ['success' => bool, 'error' => string|null]
 */
function deleteCategory($categoryId, $userId) {
    try {
        // Verify category exists and belongs to user
        $category = getCategoryById($categoryId, $userId);
        if (!$category) {
            return ['success' => false, 'error' => 'Category not found'];
        }

        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Delete category (CASCADE will remove task_categories entries)
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
        $stmt->execute([$categoryId, $userId]);

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error deleting category: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to delete category'];
    }
}

/**
 * Assign a category to a task
 * REQ-CAT-101, REQ-CAT-102
 *
 * @param int $taskId - Task ID
 * @param int $categoryId - Category ID
 * @return array - ['success' => bool, 'error' => string|null]
 */
function assignTaskCategory($taskId, $categoryId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Check if already assigned
        $stmt = $conn->prepare("SELECT 1 FROM task_categories WHERE task_id = ? AND category_id = ?");
        $stmt->execute([$taskId, $categoryId]);

        if ($stmt->fetch()) {
            return ['success' => true, 'error' => null]; // Already assigned, consider success
        }

        // Assign category
        $stmt = $conn->prepare("
            INSERT INTO task_categories (task_id, category_id)
            VALUES (?, ?)
        ");

        $stmt->execute([$taskId, $categoryId]);

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error assigning category to task: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to assign category'];
    }
}

/**
 * Remove a category from a task
 * REQ-CAT-103
 *
 * @param int $taskId - Task ID
 * @param int $categoryId - Category ID
 * @return array - ['success' => bool, 'error' => string|null]
 */
function removeTaskCategory($taskId, $categoryId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Remove category assignment
        $stmt = $conn->prepare("DELETE FROM task_categories WHERE task_id = ? AND category_id = ?");
        $stmt->execute([$taskId, $categoryId]);

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error removing category from task: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to remove category'];
    }
}

/**
 * Get all categories assigned to a task
 * REQ-CAT-104
 *
 * @param int $taskId - Task ID
 * @return array - Array of categories
 */
function getTaskCategories($taskId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return [];
        }

        $stmt = $conn->prepare("
            SELECT c.id, c.user_id, c.name, c.color
            FROM categories c
            INNER JOIN task_categories tc ON c.id = tc.category_id
            WHERE tc.task_id = ?
            ORDER BY c.name ASC
        ");

        $stmt->execute([$taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching task categories: " . $e->getMessage());
        return [];
    }
}

// ============================================================================
// ARCHIVE MANAGEMENT FUNCTIONS
// REQ-ARCH-001 through REQ-ARCH-006
// ============================================================================

/**
 * Archive a task
 * REQ-ARCH-001
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @return array - ['success' => bool, 'error' => string|null]
 */
function archiveTask($taskId, $userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Verify task belongs to user and is not deleted
        $stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->execute([$taskId, $userId]);
        
        if (!$stmt->fetch()) {
            return ['success' => false, 'error' => 'Task not found or access denied'];
        }

        // Archive the task
        $stmt = $conn->prepare("
            UPDATE tasks
            SET is_archived = 1,
                archived_at = NOW(),
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$taskId, $userId]);

        // Add to history
        addTaskHistory($taskId, $userId, 'archived');

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error archiving task: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to archive task'];
    }
}

/**
 * Unarchive (restore) a task
 * REQ-ARCH-002
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @return array - ['success' => bool, 'error' => string|null]
 */
function unarchiveTask($taskId, $userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Verify task belongs to user
        $stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ? AND is_archived = 1");
        $stmt->execute([$taskId, $userId]);
        
        if (!$stmt->fetch()) {
            return ['success' => false, 'error' => 'Task not found or not archived'];
        }

        // Unarchive the task
        $stmt = $conn->prepare("
            UPDATE tasks
            SET is_archived = 0,
                archived_at = NULL,
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$taskId, $userId]);

        // Add to history
        addTaskHistory($taskId, $userId, 'unarchived');

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error unarchiving task: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to unarchive task'];
    }
}

/**
 * Get all archived tasks for a user
 * REQ-ARCH-003
 *
 * @param int $userId - User ID
 * @param array $filters - Optional filters (same as getTasksByUserId)
 * @return array - Array of archived tasks
 */
function getArchivedTasks($userId, $filters = []) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return [];
        }

        // Base query for archived tasks only
        $sql = "
            SELECT DISTINCT t.*
            FROM tasks t
            WHERE t.user_id = ?
            AND t.is_archived = 1
            AND t.is_deleted = 0
        ";

        $params = [$userId];

        // Apply filters (similar to getTasksByUserId)
        // Status filter
        if (!empty($filters['status']) && is_array($filters['status'])) {
            $placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
            $sql .= " AND t.status IN ($placeholders)";
            $params = array_merge($params, $filters['status']);
        }

        // Priority filter
        if (!empty($filters['priority']) && is_array($filters['priority'])) {
            $placeholders = implode(',', array_fill(0, count($filters['priority']), '?'));
            $sql .= " AND t.priority IN ($placeholders)";
            $params = array_merge($params, $filters['priority']);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Category filter
        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM task_categories tc
                WHERE tc.task_id = t.id
                AND tc.category_id IN (" . implode(',', array_fill(0, count($filters['categories']), '?')) . ")
            )";
            $params = array_merge($params, $filters['categories']);
        }

        // Date range filter
        if (!empty($filters['archived_from'])) {
            $sql .= " AND t.archived_at >= ?";
            $params[] = $filters['archived_from'] . ' 00:00:00';
        }

        if (!empty($filters['archived_to'])) {
            $sql .= " AND t.archived_at <= ?";
            $params[] = $filters['archived_to'] . ' 23:59:59';
        }

        // Sorting
        $orderBy = $filters['order_by'] ?? 'archived_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        
        $validOrderColumns = ['archived_at', 'created_at', 'due_date', 'priority', 'title', 'status'];
        if (!in_array($orderBy, $validOrderColumns)) {
            $orderBy = 'archived_at';
        }
        
        if (!in_array($orderDir, ['ASC', 'DESC'])) {
            $orderDir = 'DESC';
        }

        $sql .= " ORDER BY t.$orderBy $orderDir";

        // Pagination
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
            
            if (isset($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$filters['offset'];
            }
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch categories for each task
        foreach ($tasks as &$task) {
            $task['categories'] = getTaskCategories($task['id']);
        }

        return $tasks;

    } catch (PDOException $e) {
        error_log("Error fetching archived tasks: " . $e->getMessage());
        return [];
    }
}

/**
 * Auto-archive old completed tasks
 * REQ-ARCH-004
 *
 * @param int $userId - User ID
 * @param int $daysOld - Archive tasks completed more than X days ago (default 30)
 * @return array - ['success' => bool, 'count' => int, 'error' => string|null]
 */
function autoArchiveCompletedTasks($userId, $daysOld = 30) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'count' => 0, 'error' => 'Database connection failed'];
        }

        // Find completed tasks older than specified days
        $stmt = $conn->prepare("
            SELECT id FROM tasks
            WHERE user_id = ?
            AND status = 'completed'
            AND completed_at IS NOT NULL
            AND completed_at <= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND is_archived = 0
            AND is_deleted = 0
        ");

        $stmt->execute([$userId, $daysOld]);
        $tasksToArchive = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($tasksToArchive)) {
            return ['success' => true, 'count' => 0, 'error' => null];
        }

        // Archive these tasks
        $placeholders = implode(',', array_fill(0, count($tasksToArchive), '?'));
        $stmt = $conn->prepare("
            UPDATE tasks
            SET is_archived = 1,
                archived_at = NOW(),
                updated_at = NOW()
            WHERE id IN ($placeholders)
            AND user_id = ?
        ");

        $params = array_merge($tasksToArchive, [$userId]);
        $stmt->execute($params);

        $count = $stmt->rowCount();

        // Add history for each archived task
        foreach ($tasksToArchive as $taskId) {
            addTaskHistory($taskId, $userId, 'auto_archived');
        }

        return ['success' => true, 'count' => $count, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error auto-archiving tasks: " . $e->getMessage());
        return ['success' => false, 'count' => 0, 'error' => 'Failed to auto-archive tasks'];
    }
}

// ============================================================================
// TRASH/DELETE MANAGEMENT FUNCTIONS
// REQ-ARCH-101 through REQ-ARCH-106
// ============================================================================

/**
 * Soft delete a task (move to trash)
 * REQ-ARCH-101
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @return array - ['success' => bool, 'error' => string|null]
 */
function softDeleteTask($taskId, $userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Verify task belongs to user
        $stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->execute([$taskId, $userId]);
        
        if (!$stmt->fetch()) {
            return ['success' => false, 'error' => 'Task not found or access denied'];
        }

        // Soft delete the task
        $stmt = $conn->prepare("
            UPDATE tasks
            SET is_deleted = 1,
                deleted_at = NOW(),
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$taskId, $userId]);

        // Add to history
        addTaskHistory($taskId, $userId, 'deleted');

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error soft deleting task: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to delete task'];
    }
}

/**
 * Restore a task from trash
 * REQ-ARCH-102
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @return array - ['success' => bool, 'error' => string|null]
 */
function restoreFromTrash($taskId, $userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Verify task belongs to user and is deleted
        $stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ? AND is_deleted = 1");
        $stmt->execute([$taskId, $userId]);
        
        if (!$stmt->fetch()) {
            return ['success' => false, 'error' => 'Task not found in trash'];
        }

        // Restore the task
        $stmt = $conn->prepare("
            UPDATE tasks
            SET is_deleted = 0,
                deleted_at = NULL,
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$taskId, $userId]);

        // Add to history
        addTaskHistory($taskId, $userId, 'restored');

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error restoring task from trash: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to restore task'];
    }
}

/**
 * Permanently delete a task (irreversible)
 * REQ-ARCH-103
 *
 * @param int $taskId - Task ID
 * @param int $userId - User ID (for ownership verification)
 * @return array - ['success' => bool, 'error' => string|null]
 */
function permanentlyDeleteTask($taskId, $userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'error' => 'Database connection failed'];
        }

        // Verify task belongs to user
        $stmt = $conn->prepare("SELECT id, title FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$taskId, $userId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found or access denied'];
        }

        // Log this permanent deletion for audit trail
        error_log("PERMANENT DELETE: User $userId permanently deleted task $taskId: " . $task['title']);

        // Delete related records first (task_categories, task_history)
        $stmt = $conn->prepare("DELETE FROM task_categories WHERE task_id = ?");
        $stmt->execute([$taskId]);

        // Note: task_history might have CASCADE DELETE, but let's be explicit
        // If you have a task_history table, uncomment this:
        // $stmt = $conn->prepare("DELETE FROM task_history WHERE task_id = ?");
        // $stmt->execute([$taskId]);

        // Permanently delete the task
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$taskId, $userId]);

        return ['success' => true, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error permanently deleting task: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to permanently delete task'];
    }
}

/**
 * Get all deleted tasks (in trash) for a user
 * REQ-ARCH-104
 *
 * @param int $userId - User ID
 * @param array $filters - Optional filters
 * @return array - Array of deleted tasks with days until permanent deletion
 */
function getTrashedTasks($userId, $filters = []) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return [];
        }

        // Base query for deleted tasks only
        $sql = "
            SELECT DISTINCT t.*,
                   DATEDIFF(NOW(), t.deleted_at) as days_in_trash,
                   (30 - DATEDIFF(NOW(), t.deleted_at)) as days_until_permanent_delete
            FROM tasks t
            WHERE t.user_id = ?
            AND t.is_deleted = 1
        ";

        $params = [$userId];

        // Apply filters
        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Sorting
        $orderBy = $filters['order_by'] ?? 'deleted_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        
        $validOrderColumns = ['deleted_at', 'title', 'priority'];
        if (!in_array($orderBy, $validOrderColumns)) {
            $orderBy = 'deleted_at';
        }
        
        if (!in_array($orderDir, ['ASC', 'DESC'])) {
            $orderDir = 'DESC';
        }

        $sql .= " ORDER BY t.$orderBy $orderDir";

        // Pagination
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
            
            if (isset($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$filters['offset'];
            }
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch categories for each task
        foreach ($tasks as &$task) {
            $task['categories'] = getTaskCategories($task['id']);
        }

        return $tasks;

    } catch (PDOException $e) {
        error_log("Error fetching trashed tasks: " . $e->getMessage());
        return [];
    }
}

/**
 * Purge old trashed tasks (auto-delete after X days)
 * REQ-ARCH-105
 *
 * @param int $daysOld - Delete tasks that have been in trash for more than X days (default 30)
 * @return array - ['success' => bool, 'count' => int, 'error' => string|null]
 */
function purgeOldTrashedTasks($daysOld = 30) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'count' => 0, 'error' => 'Database connection failed'];
        }

        // Find tasks that have been deleted for more than X days
        $stmt = $conn->prepare("
            SELECT id, user_id, title FROM tasks
            WHERE is_deleted = 1
            AND deleted_at IS NOT NULL
            AND deleted_at <= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");

        $stmt->execute([$daysOld]);
        $tasksToPurge = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($tasksToPurge)) {
            return ['success' => true, 'count' => 0, 'error' => null];
        }

        $count = 0;

        foreach ($tasksToPurge as $task) {
            // Log for audit trail
            error_log("AUTO-PURGE: Task {$task['id']} (user {$task['user_id']}): {$task['title']}");

            // Delete related records
            $stmt = $conn->prepare("DELETE FROM task_categories WHERE task_id = ?");
            $stmt->execute([$task['id']]);

            // Permanently delete the task
            $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$task['id']]);

            $count++;
        }

        return ['success' => true, 'count' => $count, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error purging old trashed tasks: " . $e->getMessage());
        return ['success' => false, 'count' => 0, 'error' => 'Failed to purge old trash'];
    }
}

/**
 * Empty trash for a user (delete all trashed tasks permanently)
 * REQ-ARCH-106
 *
 * @param int $userId - User ID
 * @return array - ['success' => bool, 'count' => int, 'error' => string|null]
 */
function emptyTrash($userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        if (!$conn) {
            return ['success' => false, 'count' => 0, 'error' => 'Database connection failed'];
        }

        // Get all trashed tasks for this user
        $stmt = $conn->prepare("SELECT id, title FROM tasks WHERE user_id = ? AND is_deleted = 1");
        $stmt->execute([$userId]);
        $tasksToDelete = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($tasksToDelete)) {
            return ['success' => true, 'count' => 0, 'error' => null];
        }

        $taskIds = array_column($tasksToDelete, 'id');

        // Log for audit trail
        error_log("EMPTY TRASH: User $userId permanently deleted " . count($taskIds) . " tasks");

        // Delete related records
        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $conn->prepare("DELETE FROM task_categories WHERE task_id IN ($placeholders)");
        $stmt->execute($taskIds);

        // Permanently delete all trashed tasks for this user
        $stmt = $conn->prepare("DELETE FROM tasks WHERE user_id = ? AND is_deleted = 1");
        $stmt->execute([$userId]);

        $count = $stmt->rowCount();

        return ['success' => true, 'count' => $count, 'error' => null];

    } catch (PDOException $e) {
        error_log("Error emptying trash: " . $e->getMessage());
        return ['success' => false, 'count' => 0, 'error' => 'Failed to empty trash'];
    }
}
