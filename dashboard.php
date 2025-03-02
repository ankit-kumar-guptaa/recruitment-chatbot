<?php
include 'includes/db_connect.php';
$employerResult = $conn->query("SELECT * FROM employer_enquiries ORDER BY timestamp DESC");
$jobSeekerResult = $conn->query("SELECT * FROM job_seeker_enquiries ORDER BY timestamp DESC");
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
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-black text-white font-sans antialiased">
    <button class="fixed top-4 right-4 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-full shadow-lg toggle-btn" onclick="toggleMode()">
        <i class="fas fa-adjust mr-2"></i> Toggle Theme
    </button>

    <div class="container mx-auto mt-12 p-4 max-w-4xl">
        <div class="bg-gradient-to-r from-blue-900 via-purple-800 to-indigo-900 shadow-3xl rounded-3xl overflow-hidden transform transition-all duration-700 hover:scale-105 hover:shadow-4xl">
            <div class="p-8 bg-gradient-to-r from-blue-800 via-purple-700 to-indigo-800 text-white text-center flex items-center justify-center">
                <i class="fas fa-chart-line text-4xl mr-4 animate-pulse"></i>
                <h1 class="text-4xl font-extrabold tracking-wide">Recruitment Dashboard - Futuristic Insights</h1>
            </div>
            <div class="p-8 space-y-8">
                <div>
                    <h2 class="text-2xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-briefcase text-blue-500 mr-2"></i> Employer Enquiries
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto border-collapse">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="p-5 border-b">ID</th>
                                    <th class="p-5 border-b">Step</th>
                                    <th class="p-5 border-b">Name</th>
                                    <th class="p-5 border-b">Email</th>
                                    <th class="p-5 border-b">Phone</th>
                                    <th class="p-5 border-b">Position</th>
                                    <th class="p-5 border-b">Hiring Count</th>
                                    <th class="p-5 border-b">Requirements</th>
                                    <th class="p-5 border-b">Location</th>
                                    <th class="p-5 border-b">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $employerResult->fetch_assoc()) { ?>
                                <tr class="hover:bg-gray-700 transition duration-300">
                                    <td class="p-5 border-b"><?php echo $row['id']; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['step']; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['name'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['email'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['phone'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['position'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['hiring_count'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['requirements'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['location'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['timestamp']; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-user-tie text-purple-500 mr-2"></i> Job Seeker Enquiries
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto border-collapse">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="p-5 border-b">ID</th>
                                    <th class="p-5 border-b">Step</th>
                                    <th class="p-5 border-b">Name</th>
                                    <th class="p-5 border-b">Email</th>
                                    <th class="p-5 border-b">Phone</th>
                                    <th class="p-5 border-b">Position</th>
                                    <th class="p-5 border-b">Experience</th>
                                    <th class="p-5 border-b">Skills/Certifications</th>
                                    <th class="p-5 border-b">Location Preference</th>
                                    <th class="p-5 border-b">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $jobSeekerResult->fetch_assoc()) { ?>
                                <tr class="hover:bg-gray-700 transition duration-300">
                                    <td class="p-5 border-b"><?php echo $row['id']; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['step']; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['name'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['email'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['phone'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['position'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['experience'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['skills_certifications'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['location_preference'] ?: 'N/A'; ?></td>
                                    <td class="p-5 border-b"><?php echo $row['timestamp']; ?></td>
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
<?php $conn->close(); ?>