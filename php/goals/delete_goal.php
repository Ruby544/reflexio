<?php
// Set headers to allow cross-origin requests and specify the response type as plain text
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Content-Type: text/plain"); // Set the response format to plain text

// Database connection parameters
$servername = "localhost"; // Database server
$username = "root"; // Database username
$password = ""; // Database password (empty for localhost)
$dbname = "reflexio"; // Database name

// Establish a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // If connection fails, output an error message and terminate the script
    die("ERROR: Database connection failed: " . $conn->connect_error);
}

// Get the goal_id from the query string, ensuring it's a valid integer
$goalId = isset($_GET['goal_id']) ? intval($_GET['goal_id']) : 0; // Get goal_id from the URL query parameters

// Validate the goal_id to ensure it's a positive integer
if ($goalId <= 0) {
    // If the goal_id is invalid, output an error message and terminate the script
    die("ERROR: Invalid goal ID provided.");
}

// Prepare the SQL query to delete a goal based on its ID
$sql = "DELETE FROM goals WHERE goal_id = ?"; // Delete goal with the provided goal_id
$stmt = $conn->prepare($sql); // Prepare the SQL statement
$stmt->bind_param("i", $goalId); // Bind the goal_id parameter to the SQL statement (integer type)

// Execute the SQL query
if ($stmt->execute()) {
    // If the query executes successfully, check if any rows were affected (i.e., if the goal exists)
    if ($stmt->affected_rows > 0) {
        // If a row was deleted, output a success message
        echo "Goal deleted successfully!";
    } else {
        // If no rows were affected, output a message indicating that the goal ID might not exist
        echo "No goal was deleted (ID might not exist).";
    }
} else {
    // If the query fails, output the error message
    echo "ERROR: " . $stmt->error;
}

// Close the prepared statement and database connection
$stmt->close(); // Close the statement to free resources
$conn->close(); // Close the database connection
?>
