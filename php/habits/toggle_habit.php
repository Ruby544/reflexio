<?php
// Enable error reporting for debugging
ini_set('display_errors', 1); // Display runtime errors
ini_set('display_startup_errors', 1); // Display startup errors
error_reporting(E_ALL); // Report all types of errors

// Set the response content type to JSON
header('Content-Type: application/json'); // Ensure the response is in JSON format

// Debugging: Log the received POST data for troubleshooting
error_log("Received POST data: " . print_r($_POST, true));

// Retrieve input values from the POST request
$habitId = isset($_POST['habitId']) ? intval($_POST['habitId']) : '0'; // Get habit ID, convert to integer
$day = isset($_POST['day']) ? intval($_POST['day']) : 0; // Get day, convert to integer
$currentMonthYear = isset($_POST['currentMonthYear']) ? $_POST['currentMonthYear'] : ''; // Get the month-year string

// Log the received values for debugging
error_log("Received habit_id={$habitId}, day={$day}, currentMonthYear={$currentMonthYear}"); // ✅ Log for debugging

// Validate the received input
if ($habitId <= 0 || $day <= 0 || empty($currentMonthYear)) {
    // If any value is invalid, return an error message
    die(json_encode(
        ["success" => false, 
        "error" => "❌ ERROR: Missing habit_id, day, or month_year", 
        "received" => $_POST])); // Return error and the received data for troubleshooting
}

// Database connection
$servername = "localhost"; // Database server address
$username = "root"; // Database username
$password = ""; // Database password (blank for local development)
$dbname = "reflexio"; // Database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // If the connection fails, return an error message and stop script execution
    die(json_encode(["success" => false, "error" => "❌ ERROR: Database connection failed"]));
}

// Fetch the current completed_days data from the database
$sql = "SELECT completed_days, times_monthly FROM habits WHERE habit_id = ?"; // SQL query to get the data
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // If statement preparation fails, return an error message
    die(json_encode(["success" => false, "error" => "❌ ERROR: Failed to prepare SQL statement"]));
}

// Bind habit ID to the SQL query and execute it
$stmt->bind_param("i", $habitId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc(); // Fetch the result row

// Decode the completed days JSON data, or use an empty array if no data exists
$completedDays = $row ? json_decode($row['completed_days'], true) : [];
$goal = $row["times_monthly"]; // Get the goal (times per month) from the database

// Initialize the month entry if it doesn't exist in completedDays
if (!isset($completedDays[$currentMonthYear])) {
    $completedDays[$currentMonthYear] = [];
}

// Toggle the completion status of the given day (add/remove the day from completedDays array)
if (in_array($day, $completedDays[$currentMonthYear])) {
    // If the day is already marked as completed, remove it
    $completedDays[$currentMonthYear] = array_filter($completedDays[$currentMonthYear], fn($d) => $d != $day);
} else {
    // If the day is not marked as completed, add it
    $completedDays[$currentMonthYear][] = $day;
}

// Convert the updated completed days back into JSON format
$updatedDays = json_encode($completedDays);

// Update the database with the modified completed days data
$updateSql = "UPDATE habits SET completed_days = ? WHERE habit_id = ?"; // SQL query to update data
$updateStmt = $conn->prepare($updateSql);
if (!$updateStmt) {
    // If statement preparation fails, return an error message
    die(json_encode(["success" => false, "error" => "❌ ERROR: Failed to prepare update SQL statement"]));
}

// Bind parameters and execute the update query
$updateStmt->bind_param("si", $updatedDays, $habitId);
$updateStmt->execute();

// Check if the habit goal has been met (count the number of completed days for the current month)
$completedCount = count($completedDays[$currentMonthYear]);
$isGoalMet = ($completedCount >= $goal); // Determine if the goal is met

// Return a JSON response with the updated data and goal status
echo json_encode(
    ["success" => true, 
    "completed_days" => $updatedDays, // The updated completed days
    "completed_count" => $completedCount, // The count of completed days
    "goal" => $goal, // The goal for the month
    "goalMet" => $isGoalMet] // Whether the goal was met
);

// Close the database connections
$stmt->close();
$updateStmt->close();
$conn->close();
?>