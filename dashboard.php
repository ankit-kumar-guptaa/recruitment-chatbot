<?php
include 'includes/db_connect.php';
try {
    $employerStmt = $conn->prepare("SELECT * FROM employer_enquiries ORDER BY timestamp DESC");
    $employerStmt->execute();
    $jobSeekerStmt = $conn->prepare("SELECT * FROM job_seeker_enquiries ORDER BY timestamp DESC");
    $jobSeekerStmt->execute();
} catch (PDOException $e) {
    error_log("Dashboard query error: " . $e->getMessage());
    $employerStmt = false;
    $jobSeekerStmt = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-100 font-sans antialiased">
    <button class="fixed top-4 right-4 bg-gray-600 hover:bg-gray-500 text-white px-3 py-2 rounded-full shadow-lg toggle-btn" onclick="toggleMode()">
        <i class="fas fa-adjust mr-1"></i> Theme
    </button>

    <div class="container mx-auto mt-8 p-4 max-w-2xl">
        <div class="bg-gradient-to-r from-blue-900 via-gray-800 to-blue-700 shadow-2xl rounded-2xl overflow-hidden transform transition-all duration-500 hover:scale-102 hover:shadow-3xl">
            <div class="p-6 bg-gradient-to-r from-blue-800 via-gray-700 to-blue-600 text-white text-center flex items-center justify-center">
                <i class="fas fa-chart-line text-3xl mr-3 animate-pulse"></i>
                <h1 class="text-3xl font-bold tracking-wide">Recruitment Dashboard</h1>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <h2 class="text-2xl font-semibold mb-3 flex items-center">
                        <i class="fas fa-briefcase text-blue-500 mr-2"></i> Employer Enquiries
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto border-collapse">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="p-4 border-b">ID</th>
                                    <th class="p-4 border-b">User ID</th>
                                    <th class="p-4 border-b">User Type</th>
                                    <th class="p-4 border-b">Name</th>
                                    <th class="p-4 border-b">Email</th>
                                    <th class="p-4 border-b">Phone</th>
                                    <th class="p-4 border-b">Position</th>
                                    <th class="p-4 border-b">Hiring Count</th>
                                    <th class="p-4 border-b">Requirements</th>
                                    <th class="p-4 border-b">Location</th>
                                    <th class="p-4 border-b">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $employerStmt ? $employerStmt->fetch() : []) { ?>
                                <tr class="hover:bg-gray-600 transition duration-300">
                                    <td class="p-4 border-b"><?php echo $row['id'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['user_id'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['user_type'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['name'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['email'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['phone'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['position'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['hiring_count'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['requirements'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['location'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['timestamp'] ?: 'N/A'; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold mb-3 flex items-center">
                        <i class="fas fa-user-tie text-blue-500 mr-2"></i> Job Seeker Enquiries
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto border-collapse">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="p-4 border-b">ID</th>
                                    <th class="p-4 border-b">User ID</th>
                                    <th class="p-4 border-b">User Type</th>
                                    <th class="p-4 border-b">Name</th>
                                    <th class="p-4 border-b">Email</th>
                                    <th class="p-4 border-b">Phone</th>
                                    <th class="p-4 border-b">Position</th>
                                    <th class="p-4 border-b">Experience</th>
                                    <th class="p-4 border-b">Skills/Certifications</th>
                                    <th class="p-4 border-b">Location Preference</th>
                                    <th class="p-4 border-b">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $jobSeekerStmt ? $jobSeekerStmt->fetch() : []) { ?>
                                <tr class="hover:bg-gray-600 transition duration-300">
                                    <td class="p-4 border-b"><?php echo $row['id'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['user_id'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['user_type'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['name'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['email'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['phone'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['position'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['experience'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['skills_certifications'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['location_preference'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['timestamp'] ?: 'N/A'; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php $conn = null; // Close PDO connection ?>