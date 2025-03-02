<?php
// Include database connection
include 'includes/db_connect.php';

// Prepare and execute the insert query
$stmt = $conn->prepare("INSERT INTO employer_enquiries (step, message, session_id) VALUES (?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$step = 1;
$message = "Test message";
$session_id = "test_session";

$stmt->bind_param("iss", $step, $message, $session_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

echo "Test data inserted successfully!";
$stmt->close();
$conn->close();
?>