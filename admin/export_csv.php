<?php
session_start();
include 'includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$type = $_GET['type'] ?? '';

try {
    $table = ($type === 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';
    $stmt = $conn->query("SELECT * FROM $table");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($data)) {
        die("No data to export");
    }
    
    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $type . '_enquiries_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write headers
    fputcsv($output, array_keys($data[0]));
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
} catch (PDOException $e) {
    error_log("Export error: " . $e->getMessage());
    die("Error exporting data");
}
?>