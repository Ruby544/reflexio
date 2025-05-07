<?php
// Start the session to access session data (e.g., user login status)
session_start(); // 🧠 Access the session

// Set response headers to allow cross-origin requests and specify JSON content type
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Content-Type: application/json; charset=UTF-8"); // Set the response format to JSON

// Database connection configuration
$servername = "localhost"; // Database server address (localhost in this case)
$username = "root"; // Database username
$password = ""; // Database password (empty by default for local development)
$dbname = "reflexio"; // Database name

// Initialize response array with default error values
$response = ['status' => 'error', 'message' => '']; // Default response format

try {
    // Check if the user is logged in (session validation)
    if (!isset($_SESSION["logged"]) || !$_SESSION["logged"]) {
        // If the user is not logged in, throw an exception with an error message
        throw new Exception("User not logged in.");
    }

    // Get the user ID from the session
    $user_id = $_SESSION["user_id"]; // Retrieve the logged-in user's ID

    // Create a new MySQLi connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check if the connection was successful
    if ($conn->connect_error) {
        // If connection fails, throw an exception with the error message
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Prepare an SQL statement to fetch goals for the logged-in user (only their goals)
    $stmt = $conn->prepare("SELECT * FROM goals WHERE user_id = ? ORDER BY goal_id DESC");
    if (!$stmt) {
        // If preparing the SQL statement fails, throw an exception with the error message
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind the user_id parameter to the SQL query (to avoid SQL injection)
    $stmt->bind_param("i", $user_id);

    // Execute the prepared statement
    $stmt->execute();

    // Get the result of the query
    $result = $stmt->get_result();

    // Initialize an empty array to store the goals
    $goals = [];

    // Fetch each row from the result and add it to the goals array
    while ($row = $result->fetch_assoc()) {
        $goals[] = $row; // Add the row to the goals array
    }

    // Return the goals as a JSON response
    echo json_encode($goals); // Send the goals array as a JSON response

    // Close the statement and database connection to free resources
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // If any exception occurs, return an error message in JSON format
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
