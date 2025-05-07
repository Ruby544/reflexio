<?php 
ob_start();
session_start();

// Enable all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS request (for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    ob_end_clean();
    exit(0);
}

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reflexio";

// Default response
$response = ['status' => 'error', 'message' => ''];

try {
    // Check if the user is logged in
    if (!isset($_SESSION['logged']) || !$_SESSION['user_id']) {
        throw new Exception("User not logged in.");
    }

    // Get user_id from session
    $user_id = $_SESSION['user_id'];

    // Read JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if JSON is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format.");
    }

    // Extract username  from the JSON data
    $newUsername = $data['username'] ?? null;

    if ($newUsername ) {
        // Create a new database connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check if the connection is successful
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Prepare the SQL query to update the user profile
        $sql = "UPDATE users SET username = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("SQL statement preparation failed: " . $conn->error);
        }

        // Bind parameters and execute the query
        $stmt->bind_param('si', $newUsername, $user_id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Username updated successfully'];
        } else {
            throw new Exception("Error updating profile: " . $stmt->error);
        }

        // Close the database connection
        $stmt->close();
        $conn->close();
    } else {
        throw new Exception("Missing username.");
    }
} catch (Exception $e) {
    // Catch any errors and return the response
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// Return the JSON response
echo json_encode($response);
?>
