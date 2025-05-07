<?php
// Enable error reporting for debugging
ini_set('display_errors', 1); // Display runtime errors
ini_set('display_startup_errors', 1); // Display startup errors
error_reporting(E_ALL); // Report all types of errors

// Set the response content type to JSON
header("Content-Type: application/json"); // Force the response to be in JSON format

// Database connection details
$servername = "localhost"; // Database server address
$username = "root"; // Database username
$password = ""; // Database password (blank for local development)
$dbname = "reflexio"; // Database name

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // If connection fails, return a JSON error message
    echo json_encode(["error" => "Database connection failed"]);
    exit; // Stop further execution if connection fails
}

// Retrieve parameters from the GET request
$habitId = isset($_GET['habit_id']) ? intval($_GET['habit_id']) : 0; // Habit ID from the URL (converted to integer)
$day = isset($_GET['day']) ? intval($_GET['day']) : 0; // Day from the URL (converted to integer)
$monthYear = isset($_GET['month_year']) ? $_GET['month_year'] : ''; // Month and year in YYYY-MM format from the URL

// Validate input parameters, ensure they're not missing or invalid
if ($habitId <= 0 || $day <= 0 || empty($monthYear)) {
    // If any required parameter is missing or invalid, return an error message
    echo json_encode(["error" => "Missing habit_id, day, or month_year"]);
    exit; // Stop further execution if validation fails
}

// Fetch habit's completed days and goal from the database
$sql = "SELECT completed_days, times_monthly FROM habits WHERE habit_id = ?"; // SQL query to get completed days and goal
$stmt = $conn->prepare($sql); // Prepare the query
$stmt->bind_param("i", $habitId); // Bind the habitId parameter to the SQL query
$stmt->execute(); // Execute the query
$result = $stmt->get_result(); // Get the result of the query
$row = $result->fetch_assoc(); // Fetch the row of data

// Check if the habit was found in the database
if (!$row) {
    // If no habit is found with the provided ID, return an error message
    die(json_encode(["success" => false, "error" => "Habit not found"]));
}

// Decode the completed_days JSON data into an array
$completedDays = json_decode($row["completed_days"], true) ?? []; // Default to an empty array if decoding fails

// Get the completed days for the current month (using the monthYear as key)
$completedThisMonth = $completedDays[$monthYear] ?? []; // Default to an empty array if no entry for the current month

// Count the total number of completed days for the current month
$completedCount = count($completedThisMonth);

// Retrieve the habit's goal (number of times the habit should be completed in a month)
$goal = intval($row["times_monthly"]); // Convert to an integer

// Determine if the habit is completed for the current month (meets or exceeds the goal)
$isCompletedForMonth = $completedCount >= $goal;

// Check if the specific day is marked as completed for the habit
$isCompleted = in_array($day, $completedThisMonth); // Check if the day is in the completed days list

// Return a JSON response with the habit details and completion status
echo json_encode([
    "habit_id" => $habitId, // The ID of the habit
    "day" => $day, // The day to check
    "completed" => $isCompleted, // Whether the specific day is completed
    "completed_count" => $completedCount, // The total number of completed days for the current month
    "goal" => $goal, // The habit's monthly goal
    "is_completed_for_month" => $isCompletedForMonth // Whether the habit is completed for the month
]);

// Close the prepared statement and database connection
$stmt->close(); // Close the statement
$conn->close(); // Close the database connection
?>