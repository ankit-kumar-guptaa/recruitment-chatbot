<?php
// $servername = "localhost";
// $username = "root"; // Apna DB username (e.g., "root")
// $password = ""; // Apna DB password (e.g., "" for XAMPP)
// $dbname = "recruitment_chatbot";
$servername = "localhost";
$username = "u141142577_chatbot"; // Apna DB username (e.g., "root")
$password = "Chatbot@1925@"; // Apna DB password (e.g., "" for XAMPP)
$dbname = "u141142577_chatbot";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: Please check your database settings or contact support.");
}

// Ensure UTF-8 encoding for long messages and special characters
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error setting charset: " . $conn->error);
    die("Error setting charset: Please contact support.");
}

// Debugging connection
error_log("Database connection established: " . $dbname);

function validateInput($input, $type = 'text') {
    $input = trim($input);
    if (empty($input)) {
        return false;
    }
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
        case 'phone':
            return preg_match('/^\+?[1-9]\d{9,14}$/', $input); // Basic phone validation
        default:
            return strlen($input) <= 255; // Max length for VARCHAR(255)
    }
}
?>