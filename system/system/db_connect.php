<?php
// Define database credentials
// !! IMPORTANT !!
// You MUST replace 'your_db_username' and 'your_db_password' below
// with the ACTUAL username and password for your MySQL database.

// If you are using XAMPP/WAMP/MAMP defaults:
//   DB_USERNAME is usually 'root'
//   DB_PASSWORD is usually '' (an empty string, no spaces)

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // <--- YOU MUST CHANGE THIS LINE
define('DB_PASSWORD', ''); // <--- YOU MUST CHANGE THIS LINE
define('DB_NAME', 'db_laundry');

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME); // This is line 14 where the error occurs

// Check connection
if ($conn->connect_error) {
    // For security, do not display specific error details in production.
    // Log the detailed error for debugging purposes instead.
    error_log("Database connection failed: " . $conn->connect_error);
    die("A database connection error occurred. Please try again later. (Error Code: DB-1)"); // Generic message for user
}

// Set charset to UTF-8 to prevent encoding issues and some types of SQL injection
$conn->set_charset("utf8mb4");
?>