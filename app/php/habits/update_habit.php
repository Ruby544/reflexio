<?php
// Enable error reporting for debugging purposes
error_reporting(E_ALL); // Report all types of errors
ini_set('display_errors', 1); // Display errors to help with debugging
header("Content-Type: application/json"); // Set the response content type to JSON

// Database connection credentials
$servername = "localhost"; // Database host
$username = "root"; // Database username
$password = ""; // Database password (empty for local development)
$dbname = "reflexio"; // Database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the database connection was successful
if ($conn->connect_error) {
    // If connection fails, return a JSON error message and stop script execution
    die(json_encode(["success" => false, "error" => "Database connection failed"]));
}

// Get input data from the POST request and sanitize it
$habitId = isset($_POST['habitId']) ? intval($_POST['habitId']) : 0; // Retrieve habit ID from POST data and convert it to an integer
$habitTitle = isset($_POST['habitTitle']) ? trim($_POST['habitTitle']) : ''; // Retrieve habit title from POST data and remove extra spaces
$timesMonthly = isset($_POST['numberOfTimes']) ? intval($_POST['numberOfTimes']) : 0; // Retrieve number of times per month from POST data and convert it to an integer

// Validate input data: ensure habitId is positive, habitTitle is not empty, and timesMonthly is greater than 0
if ($habitId <= 0 || empty($habitTitle) || $timesMonthly <= 0) {
    // If validation fails, return a JSON error message and stop script execution
    die(json_encode(["success" => false, "error" => "Invalid input data"]));
}

// SQL query to update the habit in the database
$sql = "UPDATE habits SET title = ?, times_monthly = ? WHERE habit_id = ?";

// Prepare the SQL statement to prevent SQL injection
$stmt = $conn->prepare($sql);

// Bind parameters to the SQL statement: "sii" means string, integer, integer
$stmt->bind_param("sii", $habitTitle, $timesMonthly, $habitId);

// Execute the prepared statement
if ($stmt->execute()) {
    // If the update is successful, return a JSON success message
    echo json_encode(["success" => true, "message" => "Habit updated successfully"]);
} else {
    // If the update fails, return a JSON error message with the error details
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

// Close the prepared statement and the database connection
$stmt->close();
$conn->close();
?>
