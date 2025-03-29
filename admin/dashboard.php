<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Determine active tab from URL
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Get filter values
$filter_position = isset($_GET['position']) ? $_GET['position'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Get all enquiries and analytics data
try {
    // Base queries
    $employerQuery = "SELECT * FROM employer_enquiries";
    $jobSeekerQuery = "SELECT * FROM job_seeker_enquiries";
    
    // Apply filters if set
    if ($filter_position) {
        $employerQuery .= " WHERE position LIKE '%$filter_position%'";
        $jobSeekerQuery .= " WHERE position LIKE '%$filter_position%'";
    }
    if ($filter_date) {
        $connector = strpos($employerQuery, 'WHERE') === false ? ' WHERE' : ' AND';
        $employerQuery .= "$connector DATE(created_at) = '$filter_date'";
        
        $connector = strpos($jobSeekerQuery, 'WHERE') === false ? ' WHERE' : ' AND';
        $jobSeekerQuery .= "$connector DATE(created_at) = '$filter_date'";
    }
    
    // Finalize queries
    $employerQuery .= " ORDER BY created_at DESC";
    $jobSeekerQuery .= " ORDER BY created_at DESC";
    
    // Execute queries
    $employerStmt = $conn->prepare($employerQuery);
    $employerStmt->execute();
    $employerData = $employerStmt->fetchAll();

    $jobSeekerStmt = $conn->prepare($jobSeekerQuery);
    $jobSeekerStmt->execute();
    $jobSeekerData = $jobSeekerStmt->fetchAll();

    // Analytics data
    $totalEnquiries = count($employerData) + count($jobSeekerData);
    
    // Monthly data for charts
    $monthlyStmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') AS month,
            COUNT(*) AS total,
            SUM(CASE WHEN user_type = 'employer' THEN 1 ELSE 0 END) AS employers,
            SUM(CASE WHEN user_type = 'job seeker' THEN 1 ELSE 0 END) AS job_seekers
        FROM (
            SELECT 'employer' AS user_type, created_at FROM employer_enquiries
            UNION ALL
            SELECT 'job seeker' AS user_type, created_at FROM job_seeker_enquiries
        ) AS combined
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ");
    $monthlyStmt->execute();
    $monthlyData = $monthlyStmt->fetchAll();

    // Today's count
    $today = date('Y-m-d');
    $todayEmployer = array_filter($employerData, function($item) use ($today) {
        return substr($item['created_at'], 0, 10) === $today;
    });
    $todayJobSeeker = array_filter($jobSeekerData, function($item) use ($today) {
        return substr($item['created_at'], 0, 10) === $today;
    });
    $todayCount = count($todayEmployer) + count($todayJobSeeker);

    // Position analytics
    $positionStmt = $conn->prepare("
        (SELECT position, COUNT(*) as count FROM employer_enquiries GROUP BY position ORDER BY count DESC LIMIT 10)
        UNION ALL
        (SELECT position, COUNT(*) as count FROM job_seeker_enquiries GROUP BY position ORDER BY count DESC LIMIT 10)
    ");
    $positionStmt->execute();
    $positionData = $positionStmt->fetchAll();

    // Experience distribution
    $experienceStmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN experience_years = 0 THEN 'Fresher'
                WHEN experience_years BETWEEN 1 AND 3 THEN '1-3 Years'
                WHEN experience_years BETWEEN 4 AND 7 THEN '4-7 Years'
                ELSE '8+ Years'
            END AS experience_range,
            COUNT(*) as count
        FROM job_seeker_enquiries
        GROUP BY experience_range
    ");
    $experienceStmt->execute();
    $experienceData = $experienceStmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard query error: " . $e->getMessage());
    $employerData = [];
    $jobSeekerData = [];
    $monthlyData = [];
    $positionData = [];
    $experienceData = [];
    $totalEnquiries = 0;
    $todayCount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Dashboard</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include your CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <!-- <i class="fas fa-briefcase"></i> -->
                <img src="https://elitecorporatesolutions.com/images/logo/logo.png" alt="">
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="?tab=dashboard" class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?tab=employer" class="nav-link <?php echo $active_tab == 'employer' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i>
                        <span>Employer Enquiries</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?tab=jobseeker" class="nav-link <?php echo $active_tab == 'jobseeker' ? 'active' : ''; ?>">
                        <i class="fas fa-user-tie"></i>
                        <span>Job Seeker Enquiries</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?tab=analytics" class="nav-link <?php echo $active_tab == 'analytics' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?tab=settings" class="nav-link <?php echo $active_tab == 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search for...">
                </div>
                
                <div class="header-actions">
        <!-- Dark Mode Toggle -->
        <button id="darkModeToggle" class="dark-mode-toggle" title="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
            <i class="fas fa-sun" style="display: none;"></i>
        </button>
        
        <div class="user-menu">
            <span><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
            <div class="user-avatar">
                <?php echo substr($_SESSION['admin_name'] ?? 'A', 0, 1); ?>
            </div>
        </div>
    </div>
            </div>
            
            <?php if ($active_tab == 'dashboard' || $active_tab == 'employer' || $active_tab == 'jobseeker'): ?>
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card primary">
                    <div class="stat-title">TOTAL ENQUIRIES</div>
                    <div class="stat-value"><?php echo $totalEnquiries; ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 12% from last month
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-title">EMPLOYER ENQUIRIES</div>
                    <div class="stat-value"><?php echo count($employerData); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 8% from last month
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-title">JOB SEEKER ENQUIRIES</div>
                    <div class="stat-value"><?php echo count($jobSeekerData); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 15% from last month
                    </div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-title">TODAY'S ENQUIRIES</div>
                    <div class="stat-value"><?php echo $todayCount; ?></div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 5% from yesterday
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($active_tab == 'dashboard' || $active_tab == 'employer'): ?>
            <!-- Employer Enquiries Table -->
            <div class="data-table-container" <?php echo $active_tab != 'dashboard' && $active_tab != 'employer' ? 'style="display: none;"' : ''; ?>>
                <div class="table-header">
                    <h2 class="table-title">Employer Enquiries</h2>
                    <div class="table-actions">
                        <button><i class="fas fa-filter"></i> Filter</button>
                        <button onclick="exportToCSV('employer')"><i class="fas fa-download"></i> Export</button>
                    </div>
                </div>
                
                <table id="employerTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employerData as $row): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm">
                                        <?php echo substr($row['name'] ?? 'E', 0, 1); ?>
                                    </div>
                                    <div class="user-info">
                                        <h4><?php echo $row['name'] ?? 'N/A'; ?></h4>
                                        <p><?php echo $row['email'] ?? ''; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $row['organisation_name'] ?? 'N/A'; ?></td>
                            <td>
                                <div><?php echo $row['position'] ?? 'N/A'; ?></div>
                                <small>Hiring: <?php echo $row['hiring_count'] ?? '0'; ?></small>
                            </td>
                            <td><?php echo $row['city_state'] ?? 'N/A'; ?></td>
                            <td>
                                <div><?php echo $row['phone'] ?? 'N/A'; ?></div>
                                <small><?php echo $row['email'] ?? ''; ?></small>
                            </td>
                            <td>
                                <div><?php echo date('M d, Y', strtotime($row['created_at'] ?? '')); ?></div>
                                <small><?php echo date('h:i A', strtotime($row['created_at'] ?? '')); ?></small>
                            </td>
                            <td>
                                <button class="action-btn view" title="View" onclick="viewDetails('employer', '<?php echo $row['id']; ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit" title="Edit" onclick="editEntry('employer', '<?php echo $row['id']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" title="Delete" onclick="deleteEntry('employer', '<?php echo $row['id']; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($employerData)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No employer enquiries found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php if ($active_tab == 'jobseeker'): ?>
            <!-- Job Seeker Enquiries Table -->
            <div class="data-table-container" <?php echo $active_tab != 'jobseeker' ? 'style="display: none;"' : ''; ?>>
                <div class="table-header">
                    <h2 class="table-title">Job Seeker Enquiries</h2>
                    <div class="table-actions">
                        <button><i class="fas fa-filter"></i> Filter</button>
                        <button onclick="exportToCSV('jobseeker')"><i class="fas fa-download"></i> Export</button>
                    </div>
                </div>
                
                <table id="jobSeekerTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Experience</th>
                            <th>Skills</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobSeekerData as $row): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm" style="background-color: var(--warning);">
                                        <?php echo substr($row['name'] ?? 'J', 0, 1); ?>
                                    </div>
                                    <div class="user-info">
                                        <h4><?php echo $row['name'] ?? 'N/A'; ?></h4>
                                        <p><?php echo $row['email'] ?? ''; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo $row['position'] ?? 'N/A'; ?></div>
                                <small><?php echo $row['applying_for_job'] ?? ''; ?></small>
                            </td>
                            <td>
                                <div><?php echo $row['fresher_experienced'] ?? 'N/A'; ?></div>
                                <small><?php echo $row['experience_years'] ?? '0'; ?> yrs</small>
                            </td>
                            <td><?php echo $row['skills_degree'] ?? 'N/A'; ?></td>
                            <td><?php echo $row['location_preference'] ?? 'N/A'; ?></td>
                            <td>
                                <div><?php echo date('M d, Y', strtotime($row['created_at'] ?? '')); ?></div>
                                <small><?php echo date('h:i A', strtotime($row['created_at'] ?? '')); ?></small>
                            </td>
                            <td>
                                <button class="action-btn view" title="View" onclick="viewDetails('jobseeker', '<?php echo $row['id']; ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit" title="Edit" onclick="editEntry('jobseeker', '<?php echo $row['id']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" title="Delete" onclick="deleteEntry('jobseeker', '<?php echo $row['id']; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($jobSeekerData)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No job seeker enquiries found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php if ($active_tab == 'analytics'): ?>
            <!-- Analytics Content -->
            <div class="chart-container">
                <div class="chart-card">
                    <h3 class="chart-title">Enquiries Overview</h3>
                    <div class="chart">
                        <canvas id="enquiriesChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Popular Positions</h3>
                    <div class="chart">
                        <canvas id="positionsChart"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($active_tab == 'settings'): ?>
            <!-- Settings Content -->
            <div class="settings-form">
                <h2>Settings</h2>
                <form id="settingsForm">
                    <div class="form-group">
                        <label for="adminName">Admin Name</label>
                        <input type="text" id="adminName" value="<?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>">
                    </div>
                    <div class="form-group">
                        <label for="adminEmail">Email Address</label>
                        <input type="email" id="adminEmail" value="admin@example.com">
                    </div>
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select id="timezone">
                            <option value="UTC">UTC</option>
                            <option value="Asia/Kolkata" selected>Asia/Kolkata</option>
                            <option value="America/New_York">America/New York</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="saveSettings()">Save Settings</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Initialize charts with all datasets
        function initCharts() {
            // Enquiries Trend Chart
            const enquiriesCtx = document.getElementById('enquiriesChart').getContext('2d');
            new Chart(enquiriesCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_map(function($row) {
                        return date('M Y', strtotime($row['month'] . '-01'));
                    }, array_reverse($monthlyData))); ?>,
                    datasets: [
                        {
                            label: 'Total Enquiries',
                            data: <?php echo json_encode(array_map(function($row) {
                                return $row['total'];
                            }, array_reverse($monthlyData))); ?>,
                            borderColor: '#4361ee',
                            backgroundColor: 'rgba(67, 97, 238, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Employers',
                            data: <?php echo json_encode(array_map(function($row) {
                                return $row['employers'];
                            }, array_reverse($monthlyData))); ?>,
                            borderColor: '#4cc9f0',
                            backgroundColor: 'rgba(76, 201, 240, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Job Seekers',
                            data: <?php echo json_encode(array_map(function($row) {
                                return $row['job_seekers'];
                            }, array_reverse($monthlyData))); ?>,
                            borderColor: '#f8961e',
                            backgroundColor: 'rgba(248, 150, 30, 0.1)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Enquiries Trend (Last 12 Months)'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Enquiries'
                            }
                        }
                    }
                }
            });

            // Positions Distribution Chart
            const positionsCtx = document.getElementById('positionsChart').getContext('2d');
            new Chart(positionsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($positionData, 'position')); ?>,
                    datasets: [{
                        label: 'Number of Enquiries',
                        data: <?php echo json_encode(array_column($positionData, 'count')); ?>,
                        backgroundColor: [
                            '#4361ee', '#4895ef', '#3f37c9', '#4cc9f0', '#f8961e',
                            '#f72585', '#b5179e', '#560bad', '#7209b7', '#480ca8'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Top 10 Positions by Enquiries'
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Enquiries'
                            }
                        }
                    }
                }
            });

            // Experience Distribution Chart
            const experienceCtx = document.getElementById('experienceChart').getContext('2d');
            new Chart(experienceCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_column($experienceData, 'experience_range')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($experienceData, 'count')); ?>,
                        backgroundColor: [
                            '#4361ee', '#4895ef', '#4cc9f0', '#f8961e'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Job Seekers Experience Distribution'
                        }
                    }
                }
            });
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('enquiriesChart')) {
                initCharts();
            }
        });
    </script>
</body>
</html>