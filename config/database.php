<?php
/**
 * Database Configuration
 * MySQL/MariaDB connection settings for Transcriptomics Database
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'transcriptomics_db');
define('DB_USER', 'root');  // Change this to your MySQL username
define('DB_PASS', '');      // Change this to your MySQL password
define('DB_CHARSET', 'utf8mb4');

// Error reporting mode
define('DEBUG_MODE', true);  // Set to false in production

// Create PDO database connection
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Database Connection Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Database connection failed',
                'message' => $e->getMessage()
            ]);
        } else {
            error_log("Database Connection Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Database connection failed',
                'message' => 'Internal server error'
            ]);
        }
        exit();
    }
}

// Helper function to execute SELECT queries
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Query Error: " . $e->getMessage());
            throw $e;
        } else {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Query execution failed");
        }
    }
}

// Helper function to get single row
function executeQuerySingle($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Query Error: " . $e->getMessage());
            throw $e;
        } else {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Query execution failed");
        }
    }
}

// Helper function to get count
function executeCount($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return isset($result['count']) ? (int)$result['count'] : 0;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Query Error: " . $e->getMessage());
            throw $e;
        } else {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Query execution failed");
        }
    }
}
