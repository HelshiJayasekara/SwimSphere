<?php
/**
 * Secure Session Initialization
 * This file should be included at the very top of any PHP script requiring sessions.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters
    session_set_cookie_params([
        'lifetime' => 86400, // 1 day
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // True if HTTPS
        'httponly' => true,
        'samesite' => 'Lax' // Protect against CSRF
    ]);

    // Start the session
    session_start();
}
