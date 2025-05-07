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
    die(json_encode([
        "success" => false, // Indicate failure
        "error" => "Database connection failed: " . $conn->connect_error // Include error message
    ]));
}

// Get raw JSON input from the request body
$json = file_get_contents('php://input'); // Read JSON data from the input
$data = json_decode($json, true); // Decode the JSON into an associative array

// Validate that the JSON is correctly formatted
if (json_last_error() !== JSON_ERROR_NONE) {
    // If JSON decoding fails, send an error response
    die(json_encode([
        "success" => false, // Indicate failure
        "error" => "Invalid JSON data" // Provide error message
    ]));
}

// Extract goal ID and progress from the decoded data
$goalId = isset($data['id']) ? intval($data['id']) : 0; // Retrieve goal ID (ensure it's an integer)
$progress = isset($data['progress']) ? intval($data['progress']) : 0; // Retrieve progress (ensure it's an integer)

// Validate that the goal ID is valid (greater than 0)
if ($goalId <= 0) {
    // If the goal ID is invalid, send an error response
    die(json_encode([
        "success" => false, // Indicate failure
        "error" => "Invalid goal ID" // Provide error message
    ]));
}

// Ensure that the progress value stays within the valid range of 0 to 100
$progress = max(0, min(100, $progress)); // Clamp the progress between 0 and 100

// Prepare the SQL statement to update the goal's progress in the database
$sql = "UPDATE goals SET progress = ? WHERE goal_id = ?"; // SQL query to update progress for the given goal ID
$stmt = $conn->prepare($sql); // Prepare the SQL statement

// Check if the statement preparation was successful
if (!$stmt) {
    // If preparation fails, send an error response
    die(json_encode([
        "success" => false, // Indicate failure
        "error" => "Prepare failed: " . $conn->error // Include error message
    ]));
}

// Bind the goal ID and progress values to the prepared statement
$stmt->bind_param("ii", $progress, $goalId); // "ii" specifies that both parameters are integers

// Execute the prepared statement
if ($stmt->execute()) {
    // If execution is successful, check if any rows were affected
    if ($stmt->affected_rows > 0) {
        // If rows were updated, send a success response with the updated progress
        echo json_encode([
            "success" => true, // Indicate success
            "message" => "Progress updated successfully", // Success message
            "newProgress" => $progress // Include the new progress value
        ]);
    } else {
        // If no rows were updated, send an error response indicating that the goal may not exist
        echo json_encode([
            "success" => false, // Indicate failure
            "error" => "No changes made - goal may not exist" // Provide error message
        ]);
    }
} else {
    // If execution fails, send an error response with the failure message
    echo json_encode([
        "success" => false, // Indicate failure
        "error" => "Execute failed: " . $stmt->error // Include error message
    ]);
}

// Close the prepared statement and the database connection
$stmt->close(); // Close the prepared statement to free resources
$conn->close(); // Close the database connection
?>
