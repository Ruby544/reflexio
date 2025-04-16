<?php
// Enable error reporting for debugging
ini_set('display_errors', 1); // Enable display of errors for troubleshooting
ini_set('display_startup_errors', 1); // Show errors that occur during startup
error_reporting(E_ALL); // Report all types of errors (notices, warnings, and fatal errors)

// Database connection details
$servername = "localhost"; // Database server address
$username = "root"; // Database username
$password = ""; // Database password (empty by default for local development)
$dbname = "reflexio"; // Database name

// Establish connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // If there was an error connecting to the database, display the error and terminate the script
    die("❌ ERROR: Database connection failed: " . $conn->connect_error);
}

// Read the habit_id from the GET request
if (isset($_GET['habit_id']) && is_numeric($_GET['habit_id'])) {
    // If 'habit_id' is provided in the GET request and is numeric, convert it to an integer
    $habitId = intval($_GET['habit_id']); // Convert the habit ID to an integer for safety
} else {
    // If 'habit_id' is not set or is not numeric, display an error message and terminate
    die("❌ ERROR: Invalid habit ID.");
}

// Print the habit ID for debugging purposes
echo "Habit_id={$habitId} ";

// Ensure the habit ID is valid (greater than 0)
if ($habitId <= 0) {
    // If the habit ID is invalid, display an error message and terminate the script
    die("❌ ERROR: Habit ID must be greater than 0.");
}

// SQL query to delete a habit from the 'habits' table using the habit ID
$sql = "DELETE FROM habits WHERE habit_id = ?"; // Prepare DELETE SQL statement
$stmt = $conn->prepare($sql); // Prepare the SQL statement for execution
$stmt->bind_param("i", $habitId); // Bind the habit ID parameter to the SQL query (integer type)

// Execute the delete query
if ($stmt->execute()) {
    // If the query executes successfully, display a success message
    echo "was deleted successfully.";
} else {
    // If there was an error executing the query, display the error message
    echo "❌ ERROR: " . $stmt->error;
}

// Close the prepared statement and database connection
$stmt->close(); // Close the prepared statement to free up resources
$conn->close(); // Close the database connection
?>
