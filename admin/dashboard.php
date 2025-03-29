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

// Get all enquiries and analytics data
try {
    // Employer enquiries
    $employerStmt = $conn->prepare("SELECT * FROM employer_enquiries ORDER BY created_at DESC");
    $employerStmt->execute();
    $employerData = $employerStmt->fetchAll();

    // Job seeker enquiries
    $jobSeekerStmt = $conn->prepare("SELECT * FROM job_seeker_enquiries ORDER BY created_at DESC");
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
        LIMIT 6
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
        (SELECT position, COUNT(*) as count FROM employer_enquiries GROUP BY position ORDER BY count DESC LIMIT 5)
        UNION ALL
        (SELECT position, COUNT(*) as count FROM job_seeker_enquiries GROUP BY position ORDER BY count DESC LIMIT 5)
    ");
    $positionStmt->execute();
    $positionData = $positionStmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard query error: " . $e->getMessage());
    $employerData = [];
    $jobSeekerData = [];
    $monthlyData = [];
    $positionData = [];
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
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
        }
        
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            padding: 1.5rem 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            margin-bottom: 2rem;
        }
        
        .logo i {
            font-size: 1.8rem;
            margin-right: 0.8rem;
        }
        
        .logo h1 {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border-left: 3px solid var(--white);
        }
        
        .nav-link i {
            margin-right: 0.8rem;
            font-size: 1.1rem;
        }
        
        /* Main Content Styles */
        .main-content {
            padding: 1.5rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .search-bar {
            position: relative;
            width: 300px;
        }
        
        .search-bar input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: none;
            border-radius: 30px;
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            font-family: 'Poppins', sans-serif;
        }
        
        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-left: 1rem;
            cursor: pointer;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.primary {
            border-top: 4px solid var(--primary);
        }
        
        .stat-card.success {
            border-top: 4px solid var(--success);
        }
        
        .stat-card.warning {
            border-top: 4px solid var(--warning);
        }
        
        .stat-card.danger {
            border-top: 4px solid var(--danger);
        }
        
        .stat-title {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .stat-change {
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .stat-change.positive {
            color: #28a745;
        }
        
        .stat-change.negative {
            color: #dc3545;
        }
        
        /* Data Table */
        .data-table-container {
            background-color: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .table-actions button {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            margin-left: 0.5rem;
            transition: background-color 0.3s ease;
        }
        
        .table-actions button:hover {
            background-color: var(--secondary);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 1rem;
            background-color: #f8f9fa;
            color: var(--gray);
            font-weight: 500;
            border-bottom: 1px solid #dee2e6;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .user-cell {
            display: flex;
            align-items: center;
        }
        
        .user-avatar-sm {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.8rem;
        }
        
        .user-info h4 {
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 0.2rem;
        }
        
        .user-info p {
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin-right: 0.5rem;
        }
        
        .action-btn.view {
            color: var(--primary);
        }
        
        .action-btn.edit {
            color: var(--success);
        }
        
        .action-btn.delete {
            color: var(--danger);
        }
        
        /* Analytics Styles */
        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .chart-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .chart {
            height: 300px;
            width: 100%;
        }
        
        /* Settings Styles */
        .settings-form {
            background-color: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .chart-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .stats-container {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-bar {
                width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-briefcase"></i>
                <h1>RecruitPro</h1>
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
                
                <div class="user-menu">
                    <span><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
                    <div class="user-avatar">
                        <?php echo substr($_SESSION['admin_name'] ?? 'A', 0, 1); ?>
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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts if on analytics tab
            if (document.getElementById('enquiriesChart')) {
                initCharts();
            }
            
            // Search functionality
            const searchInput = document.querySelector('.search-bar input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const activeTable = document.querySelector('.data-table-container:not([style*="display: none"]) table');
                    
                    if (activeTable) {
                        const rows = activeTable.querySelectorAll('tbody tr');
                        
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }
                });
            }
        });
        
        // Initialize charts
        function initCharts() {
            // Enquiries Chart
            const enquiriesCtx = document.getElementById('enquiriesChart').getContext('2d');
            const enquiriesChart = new Chart(enquiriesCtx, {
                type: 'line',
                data: {
                    labels: [
                        <?php 
                        $monthlyData = array_reverse($monthlyData);
                        foreach ($monthlyData as $row) {
                            echo "'" . date('M Y', strtotime($row['month'] . '-01')) . "',";
                        }
                        ?>
                    ],
                    datasets: [{
                        label: 'Total Enquiries',
                        data: [
                            <?php foreach ($monthlyData as $row) {
                                echo $row['total'] . ",";
                            } ?>
                        ],
                        borderColor: 'rgba(67, 97, 238, 1)',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.3,
                        fill: true
                    }, {
                        label: 'Employer Enquiries',
                        data: [
                            <?php foreach ($monthlyData as $row) {
                                echo $row['employers'] . ",";
                            } ?>
                        ],
                        borderColor: 'rgba(76, 201, 240, 1)',
                        backgroundColor: 'rgba(76, 201, 240, 0.1)',
                        tension: 0.3,
                        fill: true
                    }, {
                        label: 'Job Seeker Enquiries',
                        data: [
                            <?php foreach ($monthlyData as $row) {
                                echo $row['job_seekers'] . ",";
                            } ?>
                        ],
                        borderColor: 'rgba(248, 150, 30, 1)',
                        backgroundColor: 'rgba(248, 150, 30, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
            
            // Positions Chart
            const positionsCtx = document.getElementById('positionsChart').getContext('2d');
            const positionsChart = new Chart(positionsCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        <?php foreach ($positionData as $row) {
                            echo "'" . addslashes($row['position']) . "',";
                        } ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach ($positionData as $row) {
                                echo $row['count'] . ",";
                            } ?>
                        ],
                        backgroundColor: [
                            '#4361ee', '#4895ef', '#3f37c9', '#4cc9f0', '#f8961e',
                            '#f72585', '#b5179e', '#560bad', '#7209b7', '#480ca8'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        }
        
        // View details
        function viewDetails(type, id) {
            alert('View details for ' + type + ' ID: ' + id);
            // In a real implementation, you would make an AJAX call to fetch details
            // and display them in a modal
        }
        
        // Edit entry
        function editEntry(type, id) {
            alert('Edit ' + type + ' ID: ' + id);
            // In a real implementation, you would make an AJAX call to fetch the data
            // and display an edit form in a modal
        }
        
        // Delete entry
        function deleteEntry(type, id) {
            if (confirm('Are you sure you want to delete this ' + type + ' enquiry?')) {
                alert('Delete ' + type + ' ID: ' + id);
                // In a real implementation, you would make an AJAX call to delete the record
                // and refresh the table
            }
        }
        
        // Export to CSV
        function exportToCSV(type) {
            window.location.href = 'export_csv.php?type=' + type;
        }
        
        // Save settings
        function saveSettings() {
            alert('Settings saved');
            // In a real implementation, you would make an AJAX call to save the settings
        }
    </script>
</body>
</html>