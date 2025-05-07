<?php
// Start output buffering and session management
ob_start(); // Controls when output is sent to the browser
session_start(); // Starts a new session or resumes existing one

// Enable all errors for debugging purposes
ini_set('display_errors', 1); // Show all PHP errors
ini_set('display_startup_errors', 1); // Show startup errors
error_reporting(E_ALL); // Report all error types

// Set headers to allow cross-origin requests and specify JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    ob_end_clean(); // Clean output buffer
    exit(0); // Stop script execution for OPTIONS requests
}

// Database connection credentials
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "reflexio";

// Default error response
$response = ['status' => 'error', 'message' => ''];

try {
    // Ensure user is logged in and has a valid user ID in session
    if (!isset($_SESSION['logged']) || !$_SESSION['user_id']) {
        throw new Exception("User not logged in.");
    }

    // Get user ID from session
    $user_id = $_SESSION['user_id'];

    // Read JSON input from the request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true); // Decode JSON into associative array

    // Check for JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format.");
    }

    // Extract password data from the request
    $currentPassword = $data['currentPassword'] ?? ''; // Current password (if provided)
    $newPassword = $data['newPassword'] ?? ''; // New password
    $forgot = $data['forgot'] ?? false; // Whether this is a "forgot password" flow

    // Ensure necessary fields are provided
    if (!$newPassword || (!$forgot && !$currentPassword)) {
        throw new Exception("Missing password fields.");
    }

    // Establish a new database connection using MySQLi
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    if ($forgot) {
        // If it's a "forgot password" case, update without checking the current password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Hash the new password
        $updateSql = "UPDATE users SET password = ? WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('si', $hashedPassword, $user_id);
        
        // Execute the update query
        if ($updateStmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Password reset successfully (without current password)'];
        } else {
            throw new Exception("Failed to reset password: " . $updateStmt->error);
        }
        $updateStmt->close(); // Close statement
    } else {
        // Normal flow: check if current password is correct

        // Fetch the current hashed password from the database
        $sql = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify the provided current password against the stored hash
        if ($user && password_verify($currentPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Hash the new password
            $updateSql = "UPDATE users SET password = ? WHERE user_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('si', $hashedPassword, $user_id);

            // Execute the update query
            if ($updateStmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Password updated successfully'];
            } else {
                throw new Exception("Error updating password: " . $updateStmt->error);
            }

            $updateStmt->close(); // Close update statement
        } else {
            throw new Exception("Current password is incorrect."); // Password mismatch
        }

        $stmt->close(); // Close select statement
    }

    $conn->close(); // Close the database connection

} catch (Exception $e) {
    // Catch any exceptions and return the error message
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// Return JSON response
echo json_encode($response);
?>
