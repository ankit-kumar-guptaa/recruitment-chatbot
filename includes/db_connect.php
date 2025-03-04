<?php
// $servername = "localhost";
// $username = "root"; // Apna DB username (e.g., "root")
// $password = ""; // Apna DB password (e.g., "" for XAMPP)
// $dbname = "recruitment_chatbot";

$servername = "localhost";
$username = "u141142577_chatbot"; // Apna DB username (e.g., "root")
$password = "Chatbot@1925@"; // Apna DB password (e.g., "" for XAMPP)
$dbname = "u141142577_chatbot";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    error_log("Database connection established: " . $dbname);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: Please check your settings or contact support at support@example.com.']);
    exit;
}

function validateInput($input, $type = 'text') {
    $input = trim($input);
    // Removed all validations, always return true
    return true;
}
?>