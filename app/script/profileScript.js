// Handle Profile Form Submission for username update
document.getElementById('profileForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission behavior
    const username = document.getElementById('username').value; // Get new username from input field
    updateProfile(username); // Call function to update username
});

// Function to send the updated username to the server
function updateProfile(username) {
    fetch('../../php/profile/updateProfile.php', {
        method: 'POST', // Use POST method for data submission
        body: JSON.stringify({ username }), // Send username as JSON
        headers: { 'Content-Type': 'application/json' } // Set content type
    })
    .then(response => response.json()) // Parse response JSON
    .then(data => {
        const statusDiv = document.getElementById('usernameStatus'); // Get div to display status
        if (data.status === 'success') {
            showStatus(statusDiv, 'Username updated successfully!', 'success');
        } else {
            showStatus(statusDiv, 'Failed to update username.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error); // Log any fetch error
        const statusDiv = document.getElementById('usernameStatus');
        showStatus(statusDiv, 'An error occurred while updating username.', 'error');
    });
}

// Handle Email Form Submission for email update
document.getElementById('emailForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form's default behavior
    const email = document.getElementById('email').value; // Get new email
    updateEmail(email); // Call function to update email
});

// Function to send updated email to the server
function updateEmail(email) {
    fetch('../../php/profile/updateEmail.php', {
        method: 'POST', // Use POST for email update
        body: JSON.stringify({ email }), // Send email as JSON
        headers: { 'Content-Type': 'application/json' } // Set header
    })
    .then(response => response.json()) // Convert response to JSON
    .then(data => {
        const statusDiv = document.getElementById('emailStatus');
        if (data.status === 'success') {
            showStatus(statusDiv, 'Email updated successfully!', 'success');
        } else {
            showStatus(statusDiv, 'Failed to update email.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error); // Log any error
        const statusDiv = document.getElementById('emailStatus');
        showStatus(statusDiv, 'An error occurred while updating email.', 'error');
    });
}

// Handle Password Form Submission
document.addEventListener('DOMContentLoaded', () => {
    const passwordForm = document.getElementById('passwordForm');

    // Add submit event listener to password form
    passwordForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent default behavior

        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const forgotPassword = document.getElementById('forgotPasswordCheckbox').checked;

        // Check if passwords match
        if (newPassword !== confirmPassword) {
            const statusDiv = document.getElementById('passwordStatus');
            showStatus(statusDiv, 'Passwords do not match.', 'error');
            return;
        }

        // Ensure fields are not empty
        if (!currentPassword && !forgotPassword) {
            const statusDiv = document.getElementById('passwordStatus');
            showStatus(statusDiv, 'Please enter your current password.', 'error');
            return;
        }

        if (!newPassword) {
            const statusDiv = document.getElementById('passwordStatus');
            showStatus(statusDiv, 'Please enter a new password.', 'error');
            return;
        }

        // Send data as JSON to the server
        fetch('../../php/profile/changePassword.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({ 
                currentPassword, 
                newPassword, 
                forgot: forgotPassword 
            })
        })
        .then(res => res.json()) // Parse server response
        .then(data => {
            const statusDiv = document.getElementById('passwordStatus');
            if (data.status === 'success') {
                showStatus(statusDiv, 'Password changed successfully!', 'success');
                passwordForm.reset(); // Clear form fields
            } else {
                showStatus(statusDiv, data.message || 'Failed to change password.', 'error');
            }
        })
        .catch(err => {
            console.error(err); // Log error
            const statusDiv = document.getElementById('passwordStatus');
            showStatus(statusDiv, 'An error occurred while changing password.', 'error');
        });
    });
});

// Function to delete the account with confirmation
function deleteAccount() {
    // Confirm with the user before deleting
    const confirmDeletion = confirm("Are you sure you want to delete your account? This action cannot be undone.");
    
    if (!confirmDeletion) {
        return; // Exit if user cancels the deletion
    }

    // Proceed with the deletion if the user confirms
    fetch('../../php/profile/deleteAccount.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({}) // You can pass extra data here if needed
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = '../index.html'; // Redirect after success
        } else {
            showStatus(`Failed to delete account: ${data.message}`, 'error');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showStatus('An error occurred. Please try again later.', 'error');
    });
}

// Add event listener to button
document.getElementById('deleteAccountBtn').addEventListener('click', deleteAccount);

// Function to show status messages in the respective div
function showStatus(statusDiv, message, type) {
    statusDiv.textContent = message; // Set the message text
    statusDiv.className = 'status';  // Reset any previous class

    // Apply Tailwind styling depending on message type
    if (type === 'success') {
        statusDiv.classList.add('bg-green-200', 'text-green-800', 'border-green-400');
    } else {
        statusDiv.classList.add('bg-red-200', 'text-red-800', 'border-red-400');
    }

    // Show and style the message box
    statusDiv.classList.remove('hidden');
    statusDiv.classList.add('p-4', 'border', 'rounded-lg', 'shadow-md');

    // Automatically hide the message after 5 seconds
    setTimeout(() => {
        statusDiv.classList.add('hidden');
    }, 5000);
}

   // Function to toggle the visibility of the password field
   function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId); // Get password input field
    const eyeIcon = passwordField.nextElementSibling; // Get the eye icon next to the input

    // Toggle between 'text' and 'password' input types
    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash"); // Show slashed eye
    } else {
        passwordField.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye"); // Show normal eye
    }
}