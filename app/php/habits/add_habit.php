<?php
// Start session and output buffering to manage headers and session data
ob_start(); // Start output buffering
session_start(); // Start a session to access user data

// Create or append to custom debug log file for testing write access
file_put_contents("php_error_log.txt", "Testing write access\n", FILE_APPEND); // Log write access test

// Debug helper function to log messages to a file
function log_debug($msg) {
    // Append the log message with a timestamp to a custom debug log file
    file_put_contents("php_error_log.txt", date("[Y-m-d H:i:s] ") . $msg . "\n", FILE_APPEND);
}

// Set headers for CORS (Cross-Origin Resource Sharing) and JSON response type
header("Access-Control-Allow-Origin: *"); // Allow all origins for CORS
header("Content-Type: application/json; charset=UTF-8"); // Set content type to JSON

// Enable error reporting for debugging
ini_set('display_errors', 1); // Show all errors
ini_set('display_startup_errors', 1); // Show errors at startup
error_reporting(E_ALL); // Report all errors

// Handle preflight OPTIONS requests for CORS compliance
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and OPTIONS methods
    header("Access-Control-Allow-Headers: Content-Type"); // Allow Content-Type header
    ob_end_clean(); // End output buffering to prevent issues with OPTIONS requests
    exit(0); // Exit the script for OPTIONS requests
}

// Database connection credentials
$servername = "localhost"; // Database server address
$username = "root"; // Database username
$password = ""; // Database password (empty by default for local development)
$dbname = "reflexio"; // Database name

// Default response structure
$response = ['status' => 'error', 'message' => '']; // Default error response

try {
    // Log session contents for debugging purposes
    log_debug("Session contents: " . print_r($_SESSION, true));

    // Check if the user is logged in by verifying session data
    if (!isset($_SESSION['logged']) || empty($_SESSION['user_id'])) {
        throw new Exception("User not logged in."); // Throw exception if not logged in
    }

    // Get the user ID from the session
    $user_id = $_SESSION['user_id'];
    log_debug("User ID: $user_id"); // Log user ID for debugging

    // Read raw JSON input from the request
    $json = file_get_contents('php://input');
    log_debug("Raw JSON input: " . $json); // Log raw JSON input

    // Decode the JSON input into a PHP array
    $data = json_decode($json, true);
    log_debug("Decoded data: " . print_r($data, true)); // Log the decoded data for debugging

    // Validate if the JSON data is properly formatted
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format: " . json_last_error_msg()); // Throw exception if JSON is invalid
    }

    // Extract and validate input values from the decoded JSON
    $habitTitle = isset($data['habitTitle']) ? trim($data['habitTitle']) : ''; // Get habit title
    $timesMonthly = isset($data['numberOfTimes']) ? (int)$data['numberOfTimes'] : 0; // Get frequency of habit

    log_debug("Habit title: '$habitTitle'"); // Log habit title
    log_debug("Times monthly: $timesMonthly"); // Log number of times monthly

    // Check if the necessary input values are provided
    if (empty($habitTitle) || $timesMonthly <= 0) {
        throw new Exception("Missing or invalid input: title or frequency."); // Throw exception for invalid input
    }

    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error); // Throw exception if DB connection fails
    }

    log_debug("Connected to DB successfully."); // Log successful DB connection

    // Prepare an SQL statement to insert a new habit into the database
    $stmt = $conn->prepare("INSERT INTO habits (title, times_monthly, user_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error); // Throw exception if prepare statement fails
    }

    // Bind parameters to the prepared statement (habit title, frequency, user ID)
    $stmt->bind_param("sii", $habitTitle, $timesMonthly, $user_id);

    // Execute the SQL statement
    if ($stmt->execute()) {
        // If insertion is successful, return a success response with the habit ID
        $response = [
            'status' => 'success',
            'habit_id' => $stmt->insert_id, // Get the inserted habit's ID
            'message' => 'Habit added successfully'
        ];
        log_debug("Habit inserted successfully with ID: " . $stmt->insert_id); // Log success
    } else {
        throw new Exception("Insert failed: " . $stmt->error); // Throw exception if insert fails
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
    log_debug("DB connection closed."); // Log DB connection closure

} catch (Exception $e) {
    // Log the exception message and stack trace for debugging
    log_debug("Exception caught: " . $e->getMessage());
    log_debug("Stack trace: " . $e->getTraceAsString());

    // Set the response message based on the caught exception
    $response['message'] = "Caught Exception: " . $e->getMessage();
} finally {
    // Final cleanup: end output buffering and log the final response
    ob_end_clean();
    log_debug("Final response: " . json_encode($response)); // Log final response for debugging

    // Return the response in JSON format
    echo json_encode($response);
    exit; // Exit script
}
?>
