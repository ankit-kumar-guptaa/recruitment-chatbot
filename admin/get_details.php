<?php
session_start();
include '../includes/db_connect.php';

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? 0;

try {
    $table = ($type === 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    if ($data) {
        if ($type === 'employer') {
            echo '<div class="space-y-4">';
            echo '<div><span class="font-semibold">Name:</span> ' . htmlspecialchars($data['name'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Organization:</span> ' . htmlspecialchars($data['organisation_name'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Position:</span> ' . htmlspecialchars($data['position'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Hiring Count:</span> ' . htmlspecialchars($data['hiring_count'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Requirements:</span> ' . htmlspecialchars($data['requirements'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Location:</span> ' . htmlspecialchars($data['city_state'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Email:</span> ' . htmlspecialchars($data['email'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Phone:</span> ' . htmlspecialchars($data['phone'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Submitted:</span> ' . date('M d, Y h:i A', strtotime($data['created_at'])) . '</div>';
            echo '</div>';
        } else {
            echo '<div class="space-y-4">';
            echo '<div><span class="font-semibold">Name:</span> ' . htmlspecialchars($data['name'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Position:</span> ' . htmlspecialchars($data['position'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Applying For:</span> ' . htmlspecialchars($data['applying_for_job'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Experience:</span> ' . htmlspecialchars($data['fresher_experienced'] ?? 'N/A') . ' (' . htmlspecialchars($data['experience_years'] ?? '0') . ' years)</div>';
            echo '<div><span class="font-semibold">Skills/Degree:</span> ' . htmlspecialchars($data['skills_degree'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Location Preference:</span> ' . htmlspecialchars($data['location_preference'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Email:</span> ' . htmlspecialchars($data['email'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Phone:</span> ' . htmlspecialchars($data['phone'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Comments:</span> ' . htmlspecialchars($data['comments'] ?? 'N/A') . '</div>';
            echo '<div><span class="font-semibold">Submitted:</span> ' . date('M d, Y h:i A', strtotime($data['created_at'])) . '</div>';
            echo '</div>';
        }
    } else {
        echo '<p class="text-red-500">Error: Record not found</p>';
    }
} catch (PDOException $e) {
    echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>