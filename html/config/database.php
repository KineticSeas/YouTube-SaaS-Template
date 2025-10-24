<?php
/**
 * Database Configuration and Connection Class
 * TodoTracker SaaS Application
 */

class Database {
    // Database credentials
    private $host = '127.0.0.1';
    private $port = '8889';
    private $db_name = 'vibe_templates';  // Using vibe_templates database
    private $username = 'vibe_templates';
    private $password = '#YouTubeDemo123!';
    private $charset = 'utf8mb4';

    private $conn = null;
    private $error = null;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

            return $this->conn;

        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get last error message
     * @return string|null
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Test database connection
     * @return bool
     */
    public function testConnection() {
        $conn = $this->getConnection();
        if ($conn === null) {
            return false;
        }

        try {
            $stmt = $conn->query("SELECT 1");
            return $stmt !== false;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Execute SQL file
     * @param string $filepath Path to SQL file
     * @return bool
     */
    public function executeSqlFile($filepath) {
        if (!file_exists($filepath)) {
            $this->error = "SQL file not found: {$filepath}";
            return false;
        }

        $sql = file_get_contents($filepath);
        if ($sql === false) {
            $this->error = "Failed to read SQL file: {$filepath}";
            return false;
        }

        $conn = $this->getConnection();
        if ($conn === null) {
            return false;
        }

        try {
            // Split SQL file into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    return !empty($stmt) && !preg_match('/^--/', $stmt);
                }
            );

            // Execute each statement
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $conn->exec($statement);
                }
            }

            return true;

        } catch (PDOException $e) {
            $this->error = "SQL Execution Error: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->getConnection()->rollBack();
    }
}

// Helper function to get database instance
function getDatabase() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database;
}
