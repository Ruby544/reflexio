<?php
// Start session and output buffering to control the flow of output
ob_start(); // Start output buffering to manage the response cleanly
session_start(); // Start the session to manage user authentication

// Set headers for CORS, content type, and encoding
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin (CORS)
header("Content-Type: application/json; charset=UTF-8"); // Set response content type to JSON

// Enable error reporting for debugging purposes
ini_set('display_errors', 1); // Show errors during development
ini_set('display_startup_errors', 1); // Show startup errors
error_reporting(E_ALL); // Report all types of errors

// Database connection parameters
$servername = "localhost"; // Database server
$username = "root"; // Database username
$password = ""; // Database password (empty for local development)
$dbname = "reflexio"; // Database name

// Initialize response structure
$response = ['status' => 'error', 'entries' => [], 'message' => '']; // Default error response

try {
    // Check if the user is logged in by checking session variable
    if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
        throw new Exception("User not logged in."); // If not logged in, throw an exception
    }

    // Retrieve the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Establish a PDO connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set PDO to throw exceptions on errors

    // Get year and month from GET parameters, default to current year and month if not provided
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');

    // Construct the start and end dates for the month
    $startDate = "$year-$month-01"; // Start date of the month (1st day)
    $endDate = date("Y-m-t", strtotime($startDate)); // End date of the month (last day)

    // Prepare SQL query to fetch journal entries for the user within the specified date range
    $stmt = $conn->prepare("
        SELECT * FROM journal_entries 
        WHERE user_id = :user_id AND entry_date BETWEEN :startDate AND :endDate 
        ORDER BY entry_date
    ");
    // Bind parameters to the SQL query
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // Bind user ID to the query
    $stmt->bindParam(':startDate', $startDate); // Bind start date to the query
    $stmt->bindParam(':endDate', $endDate); // Bind end date to the query

    // Execute the query
    $stmt->execute();

    // Initialize an array to hold the entries grouped by date
    $entries = [];

    // Fetch the results and group them by entry_date
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $date = $row['entry_date']; // Get the entry's date
        // Check if the date is already in the entries array, if not initialize it
        if (!isset($entries[$date])) {
            $entries[$date] = [];
        }
        // Add the journal entry to the corresponding date group
        $entries[$date][] = $row;
    }

    // Set response status to success and include the entries
    $response = [
        'status' => 'success', // Indicate the operation was successful
        'entries' => $entries // Return the grouped entries
    ];

} catch (PDOException $e) {
    // Catch any database-related exceptions and set the error message
    $response['message'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    // Catch any general exceptions and set the error message
    $response['message'] = $e->getMessage();
} finally {
    // End output buffering and clean the buffer
    ob_end_clean();
    // Output the response as a JSON object
    echo json_encode($response);
    exit; // Exit the script after sending the response
}
?>