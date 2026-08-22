<?php
// Secure session initialization
require_once '../includes/session.php';

// Unset all session variables
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
// This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session completely
session_destroy();

// Redirect back to the homepage
header("Location: /index.php");
exit;
