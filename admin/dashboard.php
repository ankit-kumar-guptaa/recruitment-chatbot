<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Use created_at for ordering (ensure it exists now)
    $employerStmt = $conn->prepare("SELECT * FROM employer_enquiries ORDER BY created_at DESC");
    $employerStmt->execute();
    $employerData = $employerStmt->fetchAll(PDO::FETCH_ASSOC);

    $jobSeekerStmt = $conn->prepare("SELECT * FROM job_seeker_enquiries ORDER BY created_at DESC");
    $jobSeekerStmt->execute();
    $jobSeekerData = $jobSeekerStmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Employer data count: " . count($employerData));
    error_log("Job Seeker data count: " . count($jobSeekerData));
} catch (PDOException $e) {
    error_log("Dashboard query error: " . $e->getMessage());
    $employerData = [];
    $jobSeekerData = [];
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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-100 font-sans antialiased min-h-screen flex flex-col">
    <button class="fixed top-6 right-6 bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-full shadow-md transition-transform duration-300 hover:scale-105 toggle-btn" onclick="toggleMode()">
        <i class="fas fa-adjust mr-2"></i> Toggle Theme
    </button>

    <div class="container mx-auto p-4 pt-20 flex-1 max-w-4xl">
        <div class="bg-white/5 backdrop-blur-md shadow-2xl rounded-3xl overflow-hidden border border-gray-700/50 transform transition-all duration-500 hover:scale-102 hover:shadow-3xl">
            <div class="p-6 bg-gradient-to-r from-blue-800 via-gray-700 to-blue-600 text-white text-center rounded-t-3xl flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-4xl mr-4 animate-pulse-slow"></i>
                    <h1 class="text-3xl font-bold tracking-tight">Recruitment Dashboard</h1>
                </div>
                <a href="logout.php" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl shadow-md transition-transform duration-300 hover:scale-105 flex items-center">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
            <div class="p-6 space-y-6">
                <!-- Tab Navigation -->
                <div class="flex border-b border-gray-700">
                    <button id="employerTab" class="px-6 py-3 text-lg font-semibold text-white bg-blue-700 hover:bg-blue-600 rounded-t-xl transition-all duration-300 flex-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500" onclick="showTab('employer')">Employer Enquiries</button>
                    <button id="jobSeekerTab" class="px-6 py-3 text-lg font-semibold text-gray-300 hover:text-white hover:bg-gray-600 rounded-t-xl transition-all duration-300 flex-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500" onclick="showTab('jobSeeker')">Job Seeker Enquiries</button>
                </div>

                <!-- Employer Enquiries Table -->
                <div id="employerContent" class="tab-content">
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto border-collapse">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="p-4 border-b text-left">ID</th>
                                    <th class="p-4 border-b text-left">User ID</th>
                                    <th class="p-4 border-b text-left">Name</th>
                                    <th class="p-4 border-b text-left">Organisation Name</th>
                                    <th class="p-4 border-b text-left">City & State</th>
                                    <th class="p-4 border-b text-left">Email</th>
                                    <th class="p-4 border-b text-left">Phone</th>
                                    <th class="p-4 border-b text-left">Position</th>
                                    <th class="p-4 border-b text-left">Hiring Count</th>
                                    <th class="p-4 border-b text-left">Requirements</th>
                                    <th class="p-4 border-b text-left">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employerData as $row) { ?>
                                <tr class="hover:bg-gray-600 transition duration-300">
                                    <td class="p-4 border-b"><?php echo $row['id'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['user_id'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['name'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['organisation_name'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['city_state'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['email'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['phone'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['position'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['hiring_count'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['requirements'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['created_at'] ?: 'N/A'; ?></td>
                                </tr>
                                <?php } ?>
                                <?php if (empty($employerData)) { ?>
                                <tr>
                                    <td colspan="11" class="p-4 text-center text-gray-400">No employer enquiries found.</td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Job Seeker Enquiries Table -->
                <div id="jobSeekerContent" class="tab-content hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto border-collapse">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="p-4 border-b text-left">ID</th>
                                    <th class="p-4 border-b text-left">User ID</th>
                                    <th class="p-4 border-b text-left">Name</th>
                                    <th class="p-4 border-b text-left">Email</th>
                                    <th class="p-4 border-b text-left">Phone</th>
                                    <th class="p-4 border-b text-left">Position</th>
                                    <th class="p-4 border-b text-left">Fresher/Experienced</th>
                                    <th class="p-4 border-b text-left">Applying for Job</th>
                                    <th class="p-4 border-b text-left">Experience (Years)</th>
                                    <th class="p-4 border-b text-left">Skills/Degree</th>
                                    <th class="p-4 border-b text-left">Location Preference</th>
                                    <th class="p-4 border-b text-left">Comments</th>
                                    <th class="p-4 border-b text-left">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobSeekerData as $row) { ?>
                                <tr class="hover:bg-gray-600 transition duration-300">
                                    <td class="p-4 border-b"><?php echo $row['id'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['user_id'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['name'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['email'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['phone'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['position'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['fresher_experienced'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['applying_for_job'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['experience_years'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['skills_degree'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['location_preference'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['comments'] ?: 'N/A'; ?></td>
                                    <td class="p-4 border-b"><?php echo $row['created_at'] ?: 'N/A'; ?></td>
                                </tr>
                                <?php } ?>
                                <?php if (empty($jobSeekerData)) { ?>
                                <tr>
                                    <td colspan="13" class="p-4 text-center text-gray-400">No job seeker enquiries found.</td>
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
    <script>
        // Tab switching functionality
        function showTab(tab) {
            if (tab === 'employer') {
                $('#employerContent').removeClass('hidden');
                $('#jobSeekerContent').addClass('hidden');
                $('#employerTab').addClass('bg-blue-700 text-white').removeClass('bg-transparent text-gray-300 hover:bg-gray-600');
                $('#jobSeekerTab').removeClass('bg-blue-700 text-white').addClass('bg-transparent text-gray-300 hover:bg-gray-600');
            } else {
                $('#jobSeekerContent').removeClass('hidden');
                $('#employerContent').addClass('hidden');
                $('#jobSeekerTab').addClass('bg-blue-700 text-white').removeClass('bg-transparent text-gray-300 hover:bg-gray-600');
                $('#employerTab').removeClass('bg-blue-700 text-white').addClass('bg-transparent text-gray-300 hover:bg-gray-600');
            }
        }

        // Show Employer tab by default
        $(document).ready(function() {
            showTab('employer');
        });

        // Dark/Light Mode Toggle
        function toggleMode() {
            $('body').toggleClass('light-mode');
            localStorage.setItem('theme', $('body').hasClass('light-mode') ? 'light' : 'dark');
        }

        $(document).ready(function() {
            if (localStorage.getItem('theme') === 'light') {
                $('body').addClass('light-mode');
            }
        });
    </script>
</body>
</html>

<?php $conn = null; ?>