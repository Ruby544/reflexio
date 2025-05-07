<?php
// Start session and output buffering for cleaner response handling
ob_start(); // Start output buffering to manage the response cleanly
session_start(); // Start a PHP session to manage user login status

// Set response headers to allow CORS, specify content type, and allowed methods
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Content-Type: application/json; charset=UTF-8"); // Set content type as JSON
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and OPTIONS methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow Content-Type header in requests

// For preflight requests (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean(); // Clean output buffer and send response for preflight
    exit(0); // End execution for preflight request
}

// Enable error reporting for debugging purposes
ini_set('display_errors', 1); // Display errors during development
ini_set('display_startup_errors', 1); // Display errors that occur during PHP startup
error_reporting(E_ALL); // Report all types of errors

// Database connection parameters
$servername = "localhost"; // Database server address
$username = "root"; // Database username
$password = ""; // Database password (blank for local development)
$dbname = "reflexio"; // Database name

$response = ['status' => 'error', 'message' => '']; // Initialize response array

try {
    // Check if the user is logged in by checking session variables
    if (!isset($_SESSION["logged"]) || !$_SESSION["logged"]) {
        throw new Exception("User not logged in."); // Throw an exception if the user is not logged in
    }

    // Get the user ID from the session
    $user_id = $_SESSION["user_id"];

    // Establish a PDO connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set PDO error mode to exceptions

    // Get the raw POST data and decode it from JSON into an associative array
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if the JSON format is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format."); // Throw an exception if JSON is malformed
    }

    // Check if required fields are present in the received data
    if (empty($data['title']) || empty($data['content']) || empty($data['entry_date'])) {
        throw new Exception("Missing required fields."); // Throw an exception if any required field is missing
    }

    // Check if 'journal_id' exists and update the existing entry if so
    if (isset($data['journal_id']) && $data['journal_id']) {
        // Prepare the SQL query to update an existing journal entry, ensuring it belongs to the current user
        $stmt = $conn->prepare("
            UPDATE journal_entries 
            SET title = :title, 
                content = :content, 
                entry_date = :entry_date,
                updated_at = NOW() 
            WHERE journal_id = :journal_id AND user_id = :user_id
        ");
        $stmt->bindParam(':journal_id', $data['journal_id'], PDO::PARAM_INT); // Bind journal_id for update
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // Bind user_id to ensure the user owns the journal entry
        $message = "Entry updated successfully"; // Set the success message for update
    } else {
        // Prepare the SQL query to insert a new journal entry
        $stmt = $conn->prepare("
            INSERT INTO journal_entries 
            (user_id, title, content, entry_date, created_at, updated_at) 
            VALUES (:user_id, :title, :content, :entry_date, NOW(), NOW())
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // Bind user_id for new entry
        $message = "Entry created successfully"; // Set the success message for new entry
    }

    // Common parameter bindings for title, content, and entry_date
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':content', $data['content']);
    $stmt->bindParam(':entry_date', $data['entry_date']);

    // Execute the query and check if it was successful
    if ($stmt->execute()) {
        // If it's an update, return the existing journal_id; if it's a new entry, return the last inserted ID
        $journal_id = isset($data['journal_id']) ? $data['journal_id'] : $conn->lastInsertId();

        // Set the response data to indicate success
        $response = [
            "status" => "success",
            "message" => $message, // Set the success message (either created or updated)
            "journal_id" => $journal_id // Include the journal_id of the entry
        ];
    } else {
        // Throw an exception if the query execution fails
        throw new Exception("Failed to execute database query.");
    }

} catch (PDOException $e) {
    // Catch database-related exceptions and set the response message accordingly
    $response['message'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    // Catch general exceptions and set the response message accordingly
    $response['message'] = $e->getMessage();
} finally {
    // Clean up output buffer and return the response as JSON
    ob_end_clean(); // End the output buffer to prevent any unexpected output
    echo json_encode($response); // Send the response back to the client as JSON
    exit; // Exit the script after sending the response
}
?>