<?php
// Start output buffering and session management
ob_start(); // Start output buffering to control when the output is sent to the browser
session_start(); // 🔑 Start the session to retrieve session data (like user login status)

// Set response headers to allow cross-origin requests and specify JSON response format
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Content-Type: application/json; charset=UTF-8"); // Set response format to JSON

// Handle pre-flight request for CORS (Cross-Origin Resource Sharing)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and OPTIONS methods
    header("Access-Control-Allow-Headers: Content-Type"); // Allow Content-Type header
    ob_end_clean(); // Clean the output buffer before exiting
    exit(0); // Exit early for OPTIONS request
}

// Database connection parameters
$servername = "localhost"; // Database server
$username = "root"; // Database username
$password = ""; // Database password (empty for localhost)
$dbname = "reflexio"; // Database name

$response = ['status' => 'error', 'message' => '']; // Default response in case of failure

try {
    // Check if the user is logged in by verifying the session
    if (!isset($_SESSION["logged"]) || !$_SESSION["logged"]) {
        throw new Exception("User not logged in."); // Throw an exception if not logged in
    }

    $user_id = $_SESSION["user_id"]; // Retrieve the user ID from session

    // Connect to the database using MySQLi
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed"); // Handle database connection failure
    }

    // Handle POST request to add a goal
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents('php://input'); // Get raw POST data
        $data = json_decode($json, true); // Decode JSON into PHP array

        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON data received"); // Handle invalid JSON
        }

        // Check if the goal title is provided
        if (empty($data['goalTitle'])) {
            throw new Exception("Goal title is required"); // Ensure goal title is not empty
        }

        // Prepare the SQL statement to insert goal data into the database
        $stmt = $conn->prepare("INSERT INTO goals (user_id, title, deadline, progress, description, priority) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error); // Handle SQL statement preparation failure
        }

        // Bind parameters to the prepared statement
        $success = $stmt->bind_param("ississ",
            $user_id, // User ID
            $data['goalTitle'], // Goal title
            $data['deadline'], // Goal deadline
            $data['progress'], // Goal progress
            $data['goalDescription'], // Goal description
            $data['priority'] // Goal priority
        );

        // Check if parameter binding was successful
        if (!$success) {
            throw new Exception("Bind failed: " . $stmt->error); // Handle binding failure
        }

        // Execute the SQL query to insert the goal into the database
        if ($stmt->execute()) {
            // If insertion is successful, return a success response
            $response = [
                'status' => 'success',
                'id' => $stmt->insert_id, // Return the ID of the inserted goal
                'message' => 'Goal added successfully' // Success message
            ];
        } else {
            throw new Exception("Execute failed: " . $stmt->error); // Handle execution failure
        }
    } else {
        // Handle invalid request methods
        throw new Exception("Invalid request method"); // Only POST is allowed for adding goals
    }
} catch (Exception $e) {
    // Catch exceptions and set the response message to the exception message
    $response['message'] = $e->getMessage();
    error_log("Error in add_goal.php: " . $e->getMessage()); // Log the error for debugging
} finally {
    // Close the database connection if it was established
    if (isset($conn)) $conn->close();

    // Clean the output buffer and send the JSON response
    ob_end_clean();
    echo json_encode($response); // Return the response as JSON
    exit; // End the script
}
?>