<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$type = $_GET['type'] ?? '';

try {
    if ($type == 'employer') {
        $query = "SELECT * FROM employer_enquiries ORDER BY created_at DESC";
        $filename = "employer_enquiries_" . date('Y-m-d') . ".csv";
        $headers = ['ID', 'Name', 'Organization', 'Position', 'Hiring Count', 'Location', 'Email', 'Phone', 'Requirements', 'Created At'];
    } elseif ($type == 'jobseeker') {
        $query = "SELECT * FROM job_seeker_enquiries ORDER BY created_at DESC";
        $filename = "jobseeker_enquiries_" . date('Y-m-d') . ".csv";
        $headers = ['ID', 'Name', 'Position', 'Experience', 'Skills', 'Location', 'Email', 'Phone', 'Comments', 'Created At'];
    } else {
        die("Invalid export type");
    }

    $stmt = $conn->query($query);
    $data = $stmt->fetchAll();

    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add headers
    fputcsv($output, $headers);

    // Add data
    foreach ($data as $row) {
        if ($type == 'employer') {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['organisation_name'],
                $row['position'],
                $row['hiring_count'],
                $row['city_state'],
                $row['email'],
                $row['phone'],
                $row['requirements'],
                $row['created_at']
            ]);
        } else {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['position'],
                $row['fresher_experienced'] . ' (' . $row['experience_years'] . ' years)',
                $row['skills_degree'],
                $row['location_preference'],
                $row['email'],
                $row['phone'],
                $row['comments'],
                $row['created_at']
            ]);
        }
    }

    fclose($output);
} catch (PDOException $e) {
    error_log("Export error: " . $e->getMessage());
    die("Error exporting data");
}