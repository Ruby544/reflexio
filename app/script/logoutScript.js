function logout() {

    // Send a GET request to the server to destroy the user's session
    fetch("../../php/profile/logout.php", {
        method: "GET", // Using GET method to trigger logout on the server
    })
    .then(response => response.json()) // Parse the JSON response from the server
    .then(data => {
        if (data.status === "success") {
            // If logout is successful, redirect the user to the index page (home/login)
            window.location.href = "../index.html";
        } else {
            // If logout fails, show an error message to the user
            alert("Error logging out. Please try again.");
        }
    })
    .catch(error => {
        // If there's an error with the request itself (e.g., network issue), log it and alert the user
        console.error("Logout Error:", error);
        alert("An error occurred while logging out.");
    });
}