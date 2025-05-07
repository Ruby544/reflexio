<?php
session_start();
// Destroy the session and clear session data
session_unset();
session_destroy();

// Return a success response
echo json_encode(['status' => 'success']);
?>
