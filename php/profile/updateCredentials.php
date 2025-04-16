<?php
// Allow requests from any origin and specify JSON response format
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Handle CORS preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0); // Exit early for preflight request
}

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reflexio";

// Default response format
$response = ['status' => 'error', 'message' => ''];

try {
    // Get the raw JSON input and decode it
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format.");
    }

    // Extract the 'action' key to determine what to do
    $action = $data['action'] ?? '';

    // Connect to the database using MySQLi
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) throw new Exception("DB connection failed.");

    // ---------- UPDATE EMAIL ----------
    if ($action === 'update_email') {
        $usernameOrEmail = $data['identifier'] ?? '';
        $currentPassword = $data['currentPassword'] ?? '';
        $newEmail = $data['newEmail'] ?? '';

        // Check required fields
        if (!$usernameOrEmail || !$currentPassword || !$newEmail) throw new Exception("Missing fields.");

        // Get user data based on username or email
        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify password and update email
        if ($user && password_verify($currentPassword, $user['password'])) {
            $updateStmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $updateStmt->bind_param("si", $newEmail, $user['user_id']);
            $updateStmt->execute();
            $response = ['status' => 'success', 'message' => 'Email updated'];
        } else {
            throw new Exception("Invalid credentials.");
        }

    // ---------- UPDATE PASSWORD ----------
    } elseif ($action === 'update_password') {
        $usernameOrEmail = $data['identifier'] ?? '';
        $newPassword = $data['newPassword'] ?? '';

        // Check required fields
        if (!$usernameOrEmail || !$newPassword) throw new Exception("Missing fields.");

        // Fetch user ID
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Update password after hashing it
        if ($user) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $updateStmt->bind_param("si", $hashed, $user['user_id']);
            $updateStmt->execute();
            $response = ['status' => 'success', 'message' => 'Password updated'];
        } else {
            throw new Exception("User not found.");
        }

    // ---------- UPDATE USERNAME ----------
    } elseif ($action === 'update_username') {
        $email = $data['email'] ?? '';
        $currentPassword = $data['currentPassword'] ?? '';
        $newUsername = $data['newUsername'] ?? '';

        // Check required fields
        if (!$email || !$currentPassword || !$newUsername) throw new Exception("Missing fields.");

        // Get user data based on email
        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify password and update username
        if ($user && password_verify($currentPassword, $user['password'])) {
            $updateStmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
            $updateStmt->bind_param("si", $newUsername, $user['user_id']);
            $updateStmt->execute();
            $response = ['status' => 'success', 'message' => 'Username updated'];
        } else {
            throw new Exception("Invalid credentials.");
        }

    } else {
        // Invalid action provided
        throw new Exception("Invalid action.");
    }

    // Close the DB connection
    $conn->close();

} catch (Exception $e) {
    // Return error response if any exception occurs
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// Output the final JSON response
echo json_encode($response);
?>