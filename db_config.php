<?php
/**
 * Database Connection Configuration
 * 
 * This file handles the connection to the PostgreSQL database.
 */

// Database configuration
$host = "localhost";
$dbname = "toll_booth";
$user = "postgres";
$password = "Sufiyan@6346"; // Change this to your actual database password

// Error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Function to establish a database connection
 * 
 * @return PDO Database connection object
 */
function getDbConnection() {
    global $host, $dbname, $user, $password;
    
    try {
        // Create a PDO instance
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        
        // Set PDO to throw exceptions on error
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Set default fetch mode to associative array
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        // Log the error to a file
        error_log("Database connection failed: " . $e->getMessage(), 0);
        
        // Return false to indicate connection failure
        return false;
    }
}

/**
 * Function to log database operations for troubleshooting
 * 
 * @param string $operation The operation being performed
 * @param string $query The SQL query being executed
 * @param array $params Parameters passed to the query
 * @param mixed $result Result of the operation
 * @return void
 */
function logDbOperation($operation, $query, $params = [], $result = null) {
    $logFile = __DIR__ . '/logs/db_operations.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Format the log message
    $timestamp = date('Y-m-d H:i:s');
    $paramString = json_encode($params);
    $resultString = is_array($result) ? 'Records: ' . count($result) : var_export($result, true);
    
    $message = "[{$timestamp}] OPERATION: {$operation}\n";
    $message .= "QUERY: {$query}\n";
    $message .= "PARAMS: {$paramString}\n";
    $message .= "RESULT: {$resultString}\n";
    $message .= "---------------------------------------------\n";
    
    // Write to log file
    file_put_contents($logFile, $message, FILE_APPEND);
}
?>