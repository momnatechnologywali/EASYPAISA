<?php
// Database configuration - Secure connection with charset
$servername = "localhost"; // Adjust if needed (e.g., remote host)
$username = "uhpdlnsnj1voi";
$password = "rowrmxvbu3z5";
$dbname = "dbb6ky0v6sqnjy";
 
// Create connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);
 
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
// Set charset for UTF-8 support
$conn->set_charset("utf8mb4");
 
// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
 
// Function to safely close connection
function closeConn($conn) {
    if ($conn) {
        $conn->close();
    }
}
 
// Helper function for prepared statements to prevent SQL injection
function executePrepared($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $result = $stmt->execute();
 
    // Check if the query is expected to return a result set (e.g., SELECT)
    if (stripos($sql, 'SELECT') === 0) {
        $result_set = $stmt->get_result();
        $stmt->close();
        return $result_set; // Return result set for SELECT queries
    } else {
        // For non-SELECT queries (INSERT, UPDATE, etc.), return true/false
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows !== -1; // Return true if no error
    }
}
?>
