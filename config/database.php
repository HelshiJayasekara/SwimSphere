<?php
// Database configuration and connection setup
$host = '127.0.0.1'; // Using 127.0.0.1 instead of 'localhost' to force TCP and avoid Mac XAMPP socket errors
$dbname = 'swimsphere';
$username = 'root';
$password = ''; // Local XAMPP default has no password

// Define the Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// Configure PDO options for safety and ease of use
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch rows as associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements for better security
];

try {
    // Establish the PDO connection and store it in the $pdo variable
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // If connection fails, catch the exception securely.
    // We intentionally DO NOT output $e->getMessage() to prevent exposing database credentials to the browser.
    die("Database connection failed. Please check your configuration.");
}
