<?php
// Start output buffering to manage headers and session data cleanly
ob_start();

// Set session lifetime to 1 hour and configure cookie settings
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600, "/");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Get user input from POST request, using null coalescing for fallback
$username_email = $_POST["username_email"] ?? '';
$password = $_POST["password"] ?? '';

// Database connection settings
$servername = "localhost";
$username = "root";
$password_db = ""; // Keep this separate from the user password to avoid confusion
$dbname = "reflexio";

// Attempt to connect to the database
$conn = mysqli_connect($servername, $username, $password_db, $dbname);
if (!$conn) {
    log_debug("❌ Connection failed: " . mysqli_connect_error());
    die("Database connection failed.");
}

// Prepare SQL statement to find user by username or email
$sql = "SELECT * FROM users WHERE username=? or email=?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    log_debug("❌ Failed to prepare statement: " . $conn->error);
    die("SQL preparation failed.");
}

// Bind the same value to both username and email placeholders
$stmt->bind_param("ss", $username_email, $username_email);
$stmt->execute();
$result = $stmt->get_result();

// If a user was found
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Fetch user data

    // Verify password using PHP's password hashing
    if (password_verify($password, $user['password'])) {

        // Set session variables for authenticated user
        $_SESSION["logged"] = true;
        $_SESSION["user_id"] = $user['user_id'];
        $_SESSION["username"] = $user['username'];
        $_SESSION["username_email"] = $user['email'];

        // Close DB resources
        $stmt->close();
        $conn->close();

        // Redirect to dashboard after successful login
        header('Location: ../html/dashboard.html');
        exit;
    } else {
        // Incorrect password
        $_SESSION["login_error"] = "Wrong email or password.";
    }
} else {
    // No matching user found
    $_SESSION["login_error"] = "Wrong email or password.";
}

// Close DB resources if login failed
$stmt->close();
$conn->close();

// Redirect back to login page on failure
header('Location: ../html/login/login.html');
exit;
?>
