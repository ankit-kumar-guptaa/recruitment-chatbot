<?php
session_start();
include '../includes/db_connect.php';

// Define the security code (hardcoded for now, move to config/env in production)
define('SECRET_ADMIN_CODE', 'EliteRecruitmentAI2025');

if (isset($_POST['signup'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $securityCode = $_POST['security_code'] ?? '';

    try {
        // Validate security code
        if ($securityCode !== SECRET_ADMIN_CODE) {
            $error = "Invalid security code. Please contact support for the correct code.";
        } else {
            // Check if username already exists
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $checkStmt->execute([$username]);
            if ($checkStmt->fetchColumn() > 0) {
                $error = "Username already exists.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new admin user
                $insertStmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $insertStmt->execute([$username, $hashedPassword]);
                
                $_SESSION['admin_id'] = $conn->lastInsertId();
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        error_log("Signup error: " . $e->getMessage());
        $error = "An error occurred. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup - Recruitment Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-100 font-sans antialiased min-h-screen flex items-center justify-center">
    <div class="bg-white/5 backdrop-blur-md shadow-2xl rounded-3xl p-8 w-full max-w-md border border-gray-700/50">
        <h1 class="text-3xl font-bold text-center text-white mb-6">Admin Signup</h1>
        <?php if (isset($error)) { ?>
            <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
        <?php } ?>
        <form method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-300 mb-1">Username</label>
                <input type="text" id="username" name="username" class="w-full p-3 border border-gray-600 rounded-xl bg-gray-800/50 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-transform duration-300 hover:scale-102" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                <input type="password" id="password" name="password" class="w-full p-3 border border-gray-600 rounded-xl bg-gray-800/50 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-transform duration-300 hover:scale-102" required>
            </div>
            <div>
                <label for="security_code" class="block text-sm font-medium text-gray-300 mb-1">Security Code</label>
                <input type="text" id="security_code" name="security_code" class="w-full p-3 border border-gray-600 rounded-xl bg-gray-800/50 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-transform duration-300 hover:scale-102" required>
                <p class="text-xs text-gray-400 mt-1">Contact support for the security code (e.g., support@elitecorporatesolutions.com).</p>
            </div>
            <button type="submit" name="signup" class="w-full bg-blue-700 hover:bg-blue-600 text-white p-3 rounded-xl transition-transform duration-300 hover:scale-105 shadow-md flex items-center justify-center">
                <i class="fas fa-user-plus mr-2"></i> Sign Up
            </button>
        </form>
        <p class="text-center text-gray-400 mt-4">
            Already have an account? 
            <a href="login.php" class="text-blue-500 hover:text-blue-400 underline transition-colors duration-300">Login</a>
        </p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>

<?php $conn = null; ?>