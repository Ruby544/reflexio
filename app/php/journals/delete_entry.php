<?php
// Enable detailed error reporting for debugging
ini_set('display_errors', 1); // Show runtime errors
ini_set('display_startup_errors', 1); // Show startup errors
error_reporting(E_ALL); // Report all types of errors

// Set the response headers to allow cross-origin requests (CORS) and set the content type to JSON
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Content-Type: application/json; charset=UTF-8"); // Set content type to JSON
header("Access-Control-Allow-Methods: POST"); // Allow POST requests
header("Access-Control-Allow-Headers: Content-Type"); // Allow Content-Type header in the request

// Database connection details
$servername = "localhost"; // Database server address
$username = "root"; // Database username
$password = ""; // Database password (blank for local development)
$dbname = "reflexio"; // Database name

// Attempt to create a PDO database connection
try {
    // Create a new PDO instance and set the error mode to exceptions
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exceptions for better error handling
} catch(PDOException $e) {
    // If the connection fails, return an error message in JSON format
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
    exit(); // Stop further execution
}

// Read and decode the JSON data sent in the POST request
$data = json_decode(file_get_contents("php://input"), true); // Read raw POST data and decode it into an array

// Log received data for debugging purposes (append to 'debug.log')
file_put_contents('debug.log', "Delete received data: " . print_r($data, true) . "\n", FILE_APPEND); // Store POST data for debugging

// Start the try block to handle possible exceptions
try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ensure 'journal_id' parameter exists in the received data
        if (!isset($data['journal_id'])) {  // Changed from 'id' to 'journal_id'
            throw new Exception("Missing journal_id parameter"); // Throw an exception if 'journal_id' is missing
        }

        // Prepare SQL statement to delete the journal entry with the given 'journal_id'
        $stmt = $conn->prepare("DELETE FROM journal_entries WHERE journal_id = :journal_id");
        // Bind the 'journal_id' from the POST data to the SQL statement
        $stmt->bindParam(':journal_id', $data['journal_id'], PDO::PARAM_INT);
        // Execute the prepared statement
        $stmt->execute();
        
        // Check if any rows were affected (i.e., if a record was deleted)
        if ($stmt->rowCount() > 0) {
            // If the entry was deleted, return a success message
            echo json_encode(["status" => "success", "message" => "Entry deleted successfully"]);
        } else {
            // If no rows were affected, it means no entry with that 'journal_id' was found
            echo json_encode(["status" => "error", "message" => "No entry found with that ID"]);
        }
    } else {
        // If the request method is not POST, throw an exception
        throw new Exception("Invalid request method");
    }
} catch(PDOException $e) {
    // Catch any database-related exceptions and return an error message in JSON format
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
} catch(Exception $e) {
    // Catch general exceptions and return their message in JSON format
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>