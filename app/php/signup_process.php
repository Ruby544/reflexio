<?php
// Start the session to store user data after registration
session_start();

// Retrieve user input from POST request
$c = $_POST["surname"]; 
$n = $_POST["name"]; 
$d = $_POST["birthdate"]; 
$e = $_POST["email"];
$u = $_POST["username"];
$p = $_POST["password"];

// Check if password and confirm_password match
if ($_POST["password"] != $_POST["confirm_password"]) {
    echo "The passwords do not match!! <br><br>"; 
    echo "<a href='../html/login/signup.html'>Return to the Sign-Up page</a>";	 
    exit; // Stop execution if passwords don't match
}

// Hash the password for secure storage
$hashed_password = password_hash($p, PASSWORD_DEFAULT);

// Set up database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reflexio";

// Create connection to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the email or username is already registered
$_sql_q = "SELECT * FROM users WHERE email = '$e' OR username = '$u';";
$result = mysqli_query($conn, $_sql_q);

// If user already exists, prompt to choose different credentials
if (mysqli_num_rows($result) > 0) {
    echo "<br><br>Email or username already in use. Please select another one!<br>";
    echo "<a href='../html/login/signup.html'>Back to Sign-Up page</a>";
} else {
    // Insert the new user's information into the database
    $sql_query = "INSERT INTO users (surname, name, birthdate, email, username, password) 
                  VALUES ('$c','$n','$d','$e','$u','$hashed_password');"; 

    if (mysqli_query($conn, $sql_query)) {
        // Get the user ID of the newly inserted user
        $user_id = $conn->insert_id;

        // Store relevant data in session for user login state
        $_SESSION["logged"] = true;
        $_SESSION["user_id"] = $user_id;
        $_SESSION["username"] = $u;
        $_SESSION["email"] = $e;

        // Redirect to dashboard upon successful registration
        header('Location: ../html/dashboard.html');
        exit;
    } else {
        // Show error if insertion fails
        echo "Error: " . $conn->error;
    }
}

// Close the database connection
mysqli_close($conn);
?>