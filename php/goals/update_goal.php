<?php
// Enable error reporting for debugging purposes
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the screen for easier debugging

// Set response headers for JSON content and allow cross-origin requests
header("Content-Type: application/json"); // Set the response format to JSON
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin

// Database configuration (adjust credentials as necessary)
$servername = "localhost"; // Database server address (localhost in this case)
$username = "root"; // Database username
$password = ""; // Database password (empty by default on local setups)
$dbname = "reflexio"; // Database name

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the database connection is successful
if ($conn->connect_error) {
    // If connection fails, send an error response and terminate the script
    die(json_encode(["success" => false, "error" => "Database connection failed"]));
}

// Get raw JSON input from the request body
$json = file_get_contents('php://input'); // Read JSON data from the input
$data = json_decode($json, true); // Decode the JSON into an associative array

// Validate that the JSON is correctly formatted
if (json_last_error() !== JSON_ERROR_NONE) {
    // If JSON decoding fails, send an error response
    die(json_encode(["success" => false, "error" => "Invalid JSON data"]));
}

// Extract values from the decoded data
$goalId = isset($data['goalId']) ? intval($data['goalId']) : 0; // Retrieve goal ID (ensure it's an integer)
$goalTitle = isset($data['goalTitle']) ? trim($data['goalTitle']) : ''; // Retrieve and trim the goal title
$goalDescription = isset($data['goalDescription']) ? trim($data['goalDescription']) : ''; // Retrieve and trim the goal description
$deadline = isset($data['deadline']) ? trim($data['deadline']) : null; // Retrieve and trim the deadline (optional)
$priority = isset($data['priority']) ? trim($data['priority']) : 'medium'; // Retrieve and trim the priority (default: medium)

// Validate that both goalId and goalTitle are provided (goalId must be greater than 0, and goalTitle should not be empty)
if ($goalId <= 0 || empty($goalTitle)) {
    // If goalId or goalTitle are invalid, send an error response and terminate the script
    die(json_encode(["success" => false, "error" => "Goal ID and Title are required"]));
}

// Prepare the SQL statement to update the goal in the database
$sql = "UPDATE goals SET title = ?, description = ?, deadline = ?, priority = ? WHERE goal_id = ?"; // SQL query to update goal data
$stmt = $conn->prepare($sql); // Prepare the SQL statement

// Bind the values to the prepared statement
$stmt->bind_param("ssssi", $goalTitle, $goalDescription, $deadline, $priority, $goalId); // "ssssi" indicates the data types (string, string, string, string, integer)

// Execute the prepared statement
if ($stmt->execute()) {
    // If execution is successful, send a success response with a message
    echo json_encode(["success" => true, "message" => "Goal updated successfully"]);
} else {
    // If execution fails, send an error response with the error message
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

// Close the prepared statement and the database connection
$stmt->close(); // Close the prepared statement to free resources
$conn->close(); // Close the database connection
?>
