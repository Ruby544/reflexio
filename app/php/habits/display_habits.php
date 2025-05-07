<?php
// Start session and enable debugging
session_start(); // Starts a new session or resumes the current session
ob_start(); // Turns on output buffering to control when output is sent to the browser

// Enable error reporting for debugging purposes
ini_set('display_errors', 1); // Ensures that all errors are displayed
ini_set('display_startup_errors', 1); // Displays errors that occur during PHP startup
error_reporting(E_ALL); // Set the error reporting level to show all errors

header("Content-Type: application/json"); // Sets the response content type to JSON

// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['logged']) || !$_SESSION['user_id']) {
    // If the user is not logged in, send a JSON response indicating failure
    echo json_encode(["success" => false, "message" => "User not authenticated"]);
    exit; // Exit the script to prevent further code execution
}

$user_id = $_SESSION['user_id']; // Store the user ID from the session for later use

// Database connection credentials
$servername = "localhost"; // Database host
$username = "root"; // Database username
$password = ""; // Database password (empty for local development)
$dbname = "reflexio"; // Database name

// Default response structure
$response = ["success" => false, "habits" => [], "message" => ""];

try {
    // Create a new connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check if the database connection was successful
    if ($conn->connect_error) {
        throw new Exception("Database connection failed"); // Throw an exception if connection fails
    }

    // Prepare SQL query to fetch habits for the logged-in user
    $stmt = $conn->prepare("SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC");

    // Check if the query preparation was successful
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error); // If query preparation fails, throw an exception
    }

    // Bind the user ID parameter to the SQL query
    $stmt->bind_param("i", $user_id); // 'i' indicates the parameter type is an integer

    // Execute the query
    $stmt->execute();

    // Get the result of the query
    $result = $stmt->get_result();

    $habits = []; // Initialize an empty array to store habit data

    // Loop through the query result and populate the habits array
    while ($row = $result->fetch_assoc()) {
        $habits[] = [
            "habit_id" => (int) $row["habit_id"], // Convert habit_id to an integer
            "title" => $row["title"], // Fetch the habit title
            "times_monthly" => (int) $row["times_monthly"], // Convert times_monthly to an integer
            "completed_days" => !empty($row["completed_days"]) ? json_decode($row["completed_days"], true) : [] // Decode the completed days if available, else set to an empty array
        ];
    }

    // If the data was fetched successfully, update the response
    $response["success"] = true;
    $response["habits"] = array_values($habits); // Store habits as a numerically indexed array

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Catch any exceptions and log the error message
    error_log("Error in display_habit.php: " . $e->getMessage());
    // If an error occurs, update the response with the error message
    $response["message"] = $e->getMessage();
}

// Clean the output buffer and send the JSON response with formatted output
ob_end_clean(); // Clean (discard) the output buffer
echo json_encode($response, JSON_PRETTY_PRINT); // Send the JSON response with pretty print formatting
exit; // Exit the script to ensure no further code is executed
?>
