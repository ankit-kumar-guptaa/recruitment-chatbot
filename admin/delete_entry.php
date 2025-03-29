<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? 0;

try {
    $table = ($type === 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Delete error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>