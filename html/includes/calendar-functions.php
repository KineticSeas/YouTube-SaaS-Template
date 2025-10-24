<?php
/**
 * Calendar Helper Functions
 * Utility functions for calendar views (Month, Week, Day)
 * Handles date calculations, grid generation, and task retrieval
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/task-functions.php';

/**
 * Generate calendar grid for month view
 * Creates 42-cell grid (6 rows x 7 columns) including overflow from previous/next months
 *
 * @param int $year - Year (e.g., 2025)
 * @param int $month - Month (1-12)
 * @return array - Array of date arrays with keys: date, day, isCurrentMonth, isToday
 */
function generateCalendarGrid($year, $month) {
    $grid = [];

    // Get first day of the month
    $firstDay = new DateTime("$year-$month-01");
    $firstDayOfWeek = (int)$firstDay->format('w'); // 0 (Sunday) to 6 (Saturday)

    // Get last day of the month
    $lastDay = new DateTime($firstDay->format('Y-m-t'));

    // Calculate start date (may be from previous month)
    $startDate = clone $firstDay;
    if ($firstDayOfWeek > 0) {
        $startDate->modify("-$firstDayOfWeek days");
    }

    // Generate 42 cells (6 rows x 7 columns)
    $currentDate = clone $startDate;
    $today = new DateTime('today');

    for ($i = 0; $i < 42; $i++) {
        $grid[] = [
            'date' => $currentDate->format('Y-m-d'),
            'day' => (int)$currentDate->format('d'),
            'month' => (int)$currentDate->format('m'),
            'year' => (int)$currentDate->format('Y'),
            'isCurrentMonth' => (int)$currentDate->format('m') === (int)$month,
            'isToday' => $currentDate->format('Y-m-d') === $today->format('Y-m-d'),
            'dayOfWeek' => (int)$currentDate->format('w')
        ];
        $currentDate->modify('+1 day');
    }

    return $grid;
}

/**
 * Get all tasks for a month, grouped by date
 *
 * @param int $year - Year
 * @param int $month - Month (1-12)
 * @param int $userId - User ID
 * @return array - Associative array with dates as keys and task arrays as values
 */
function getMonthCalendar($year, $month, $userId) {
    try {
        // Get calendar grid
        $grid = generateCalendarGrid($year, $month);

        // Get start and end dates for the grid
        $startDate = $grid[0]['date'];
        $endDate = $grid[41]['date'];

        // Fetch all tasks in date range
        $db = getDatabase();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            SELECT t.*,
                   GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as category_names,
                   GROUP_CONCAT(DISTINCT c.id ORDER BY c.name SEPARATOR ',') as category_ids
            FROM tasks t
            LEFT JOIN task_categories tc ON t.id = tc.task_id
            LEFT JOIN categories c ON tc.category_id = c.id
            WHERE t.user_id = ?
            AND t.due_date IS NOT NULL
            AND t.due_date >= ?
            AND t.due_date <= ?
            GROUP BY t.id
            ORDER BY t.due_date ASC, t.priority DESC
        ");

        $stmt->execute([$userId, $startDate, $endDate]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group tasks by date
        $tasksByDate = [];
        foreach ($tasks as $task) {
            $date = $task['due_date'];
            if (!isset($tasksByDate[$date])) {
                $tasksByDate[$date] = [];
            }
            $tasksByDate[$date][] = $task;
        }

        return [
            'grid' => $grid,
            'tasks' => $tasksByDate
        ];

    } catch (PDOException $e) {
        error_log("Error getting month calendar: " . $e->getMessage());
        return [
            'grid' => generateCalendarGrid($year, $month),
            'tasks' => []
        ];
    }
}

/**
 * Get week calendar (7 days starting from Sunday)
 *
 * @param string $startDate - Start date (YYYY-MM-DD), should be a Sunday
 * @param int $userId - User ID
 * @return array - Array with 'days' (array of 7 date info) and 'tasks' (grouped by date)
 */
function getWeekCalendar($startDate, $userId) {
    try {
        $start = new DateTime($startDate);

        // Ensure start is Sunday
        $dayOfWeek = (int)$start->format('w');
        if ($dayOfWeek !== 0) {
            $start->modify('-' . $dayOfWeek . ' days');
        }

        // Generate 7 days
        $days = [];
        $today = new DateTime('today');
        $currentDate = clone $start;

        for ($i = 0; $i < 7; $i++) {
            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'dayName' => $currentDate->format('D'), // Mon, Tue, etc.
                'dayNameFull' => $currentDate->format('l'), // Monday, Tuesday, etc.
                'day' => (int)$currentDate->format('d'),
                'month' => (int)$currentDate->format('m'),
                'year' => (int)$currentDate->format('Y'),
                'isToday' => $currentDate->format('Y-m-d') === $today->format('Y-m-d')
            ];
            $currentDate->modify('+1 day');
        }

        // Get end date
        $end = clone $start;
        $end->modify('+6 days');
        $endDate = $end->format('Y-m-d');

        // Fetch tasks for the week
        $db = getDatabase();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            SELECT t.*,
                   GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as category_names,
                   GROUP_CONCAT(DISTINCT c.id ORDER BY c.name SEPARATOR ',') as category_ids
            FROM tasks t
            LEFT JOIN task_categories tc ON t.id = tc.task_id
            LEFT JOIN categories c ON tc.category_id = c.id
            WHERE t.user_id = ?
            AND t.due_date IS NOT NULL
            AND t.due_date >= ?
            AND t.due_date <= ?
            GROUP BY t.id
            ORDER BY t.due_date ASC,
                     FIELD(t.priority, 'high', 'medium', 'low')
        ");

        $stmt->execute([$userId, $startDate, $endDate]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group tasks by date
        $tasksByDate = [];
        foreach ($tasks as $task) {
            $date = $task['due_date'];
            if (!isset($tasksByDate[$date])) {
                $tasksByDate[$date] = [];
            }
            $tasksByDate[$date][] = $task;
        }

        return [
            'days' => $days,
            'tasks' => $tasksByDate,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $endDate
        ];

    } catch (PDOException $e) {
        error_log("Error getting week calendar: " . $e->getMessage());
        return [
            'days' => [],
            'tasks' => []
        ];
    }
}

/**
 * Get all tasks for a specific day
 *
 * @param string $date - Date (YYYY-MM-DD)
 * @param int $userId - User ID
 * @return array - Array of tasks
 */
function getDayTasks($date, $userId) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            SELECT t.*,
                   GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as category_names,
                   GROUP_CONCAT(DISTINCT c.id ORDER BY c.name SEPARATOR ',') as category_ids
            FROM tasks t
            LEFT JOIN task_categories tc ON t.id = tc.task_id
            LEFT JOIN categories c ON tc.category_id = c.id
            WHERE t.user_id = ?
            AND t.due_date = ?
            GROUP BY t.id
            ORDER BY FIELD(t.priority, 'high', 'medium', 'low'),
                     t.created_at ASC
        ");

        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error getting day tasks: " . $e->getMessage());
        return [];
    }
}

/**
 * Get task count for each date in a range
 *
 * @param int $userId - User ID
 * @param string $startDate - Start date (YYYY-MM-DD)
 * @param string $endDate - End date (YYYY-MM-DD)
 * @return array - Associative array with dates as keys and counts as values
 */
function getTaskCountByDate($userId, $startDate, $endDate) {
    try {
        $db = getDatabase();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            SELECT due_date, COUNT(*) as task_count
            FROM tasks
            WHERE user_id = ?
            AND due_date IS NOT NULL
            AND due_date >= ?
            AND due_date <= ?
            GROUP BY due_date
        ");

        $stmt->execute([$userId, $startDate, $endDate]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert to associative array
        $counts = [];
        foreach ($results as $row) {
            $counts[$row['due_date']] = (int)$row['task_count'];
        }

        return $counts;

    } catch (PDOException $e) {
        error_log("Error getting task counts: " . $e->getMessage());
        return [];
    }
}

/**
 * Calculate the Sunday of the week for a given date
 *
 * @param string $date - Date (YYYY-MM-DD)
 * @return string - Sunday date (YYYY-MM-DD)
 */
function getWeekStart($date) {
    $dateObj = new DateTime($date);
    $dayOfWeek = (int)$dateObj->format('w');

    if ($dayOfWeek !== 0) {
        $dateObj->modify('-' . $dayOfWeek . ' days');
    }

    return $dateObj->format('Y-m-d');
}

/**
 * Get formatted date range for display
 *
 * @param string $startDate - Start date
 * @param string $endDate - End date
 * @return string - Formatted range (e.g., "Oct 20 - Oct 26, 2025")
 */
function getDateRangeDisplay($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);

    $startMonth = $start->format('M');
    $endMonth = $end->format('M');
    $startYear = $start->format('Y');
    $endYear = $end->format('Y');

    // Same month and year
    if ($startMonth === $endMonth && $startYear === $endYear) {
        return $startMonth . ' ' . $start->format('j') . ' - ' . $end->format('j') . ', ' . $startYear;
    }

    // Different months, same year
    if ($startYear === $endYear) {
        return $startMonth . ' ' . $start->format('j') . ' - ' . $endMonth . ' ' . $end->format('j') . ', ' . $startYear;
    }

    // Different years
    return $start->format('M j, Y') . ' - ' . $end->format('M j, Y');
}

/**
 * Get month name from month number
 *
 * @param int $month - Month (1-12)
 * @param int $year - Year
 * @return string - Month name and year (e.g., "October 2025")
 */
function getMonthYearDisplay($month, $year) {
    $date = new DateTime("$year-$month-01");
    return $date->format('F Y');
}

/**
 * Get full date display for day view
 *
 * @param string $date - Date (YYYY-MM-DD)
 * @return string - Full date (e.g., "Monday, October 21, 2025")
 */
function getFullDateDisplay($date) {
    $dateObj = new DateTime($date);
    return $dateObj->format('l, F j, Y');
}

/**
 * Check if a task is overdue
 *
 * @param array $task - Task array with due_date and status
 * @return bool - True if overdue
 */
function isTaskOverdue($task) {
    if (!isset($task['due_date']) || !$task['due_date']) {
        return false;
    }

    // Not overdue if completed
    if ($task['status'] === 'completed') {
        return false;
    }

    $today = new DateTime('today');
    $dueDate = new DateTime($task['due_date']);

    return $dueDate < $today;
}
