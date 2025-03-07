<?php
// Database configuration for live server
$servername = "localhost"; // Live server ka host, agar alag hai to change karna (e.g., 'mysql.greencarcarpool.com')
$username = "u141142577_chatbot"; // Live database username
$password = "Chatbot@1925@"; // Live database password
$dbname = "u141142577_chatbot"; // Live database name


// $servername = "localhost";
// $username = "root"; // Apna DB username (e.g., "root")
// $password = ""; // Apna DB password (e.g., "" for XAMPP)
// $dbname = "recruitment_chatbot";

// Connection options with retry mechanism
$maxRetries = 3;
$retryDelay = 2; // Seconds to wait between retries

for ($retry = 0; $retry < $maxRetries; $retry++) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5, // 5-second timeout
        ]);
        error_log("Database connection established successfully: " . $dbname . " (Attempt: " . ($retry + 1) . ")");
        break; // Success, exit loop
    } catch (PDOException $e) {
        error_log("Database connection attempt " . ($retry + 1) . " failed: " . $e->getMessage());
        if ($retry == $maxRetries - 1) {
            // Last attempt failed, send error response
            header('Content-Type: application/json');
            $errorMsg = 'Database connection failed: Please check your settings or contact support at support@elitecorporatesolutions.com. Error: ' . $e->getMessage();
            echo json_encode(['status' => 'error', 'message' => $errorMsg]);
            exit;
        }
        sleep($retryDelay); // Wait before retrying
    }
}

// Optional: Set additional connection attributes for performance
$conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
$conn->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);

?>