<?php
// Start the session to access session variables
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1); // Show all PHP errors
ini_set('display_startup_errors', 1); // Show errors during PHP startup
error_reporting(E_ALL); // Report all errors and warnings

// Set headers to allow requests from any origin and define response format as JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request (for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allowed HTTP methods
    header("Access-Control-Allow-Headers: Content-Type"); // Allowed request headers
    exit(0); // Stop script execution for preflight request
}

// Database connection credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reflexio";

// Initialize default error response
$response = ['status' => 'error', 'message' => ''];

try {
    // Check if user is logged in and user_id is set in the session
    if (!isset($_SESSION['logged']) || !isset($_SESSION['user_id'])) {
        throw new Exception("User not logged in.");
    }

    // Get the user ID from session and cast it to an integer for safety
    $user_id = (int) $_SESSION['user_id'];

    // Read and decode JSON data from the request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if JSON decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format.");
    }

    // Connect to the MySQL database using MySQLi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check for database connection error
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL DELETE statement to remove the user from the database
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    // Check if statement preparation was successful
    if (!$stmt) {
        throw new Exception("SQL statement preparation failed: " . $conn->error);
    }

    // Bind user_id to the prepared statement as an integer
    $stmt->bind_param('i', $user_id);

    // Execute the deletion
    if ($stmt->execute()) {
        // On success, destroy the session and return success message
        session_destroy();
        $response = ['status' => 'success', 'message' => 'Account deleted successfully'];
    } else {
        // If execution fails, throw an error with the reason
        throw new Exception("Error deleting account: " . $stmt->error);
    }

    // Close the statement and database connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Catch and return any error that occurred during the process
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// Output the final JSON response
echo json_encode($response);
?>