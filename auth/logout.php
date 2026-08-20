<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect back to the homepage
header("Location: /index.php");
exit;
