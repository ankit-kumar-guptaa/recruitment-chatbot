<?php
session_start();
include '../includes/db_connect.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $error = "An error occurred. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Recruitment Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-100 font-sans antialiased min-h-screen flex items-center justify-center">
    <div class="bg-white/5 backdrop-blur-md shadow-2xl rounded-3xl p-8 w-full max-w-md border border-gray-700/50">
        <h1 class="text-3xl font-bold text-center text-white mb-6">Admin Login</h1>
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
            <button type="submit" name="login" class="w-full bg-blue-700 hover:bg-blue-600 text-white p-3 rounded-xl transition-transform duration-300 hover:scale-105 shadow-md flex items-center justify-center">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>
        <p class="text-center text-gray-400 mt-4">
            Don’t have an account? 
            <a href="signup.php" class="text-blue-500 hover:text-blue-400 underline transition-colors duration-300">Sign Up</a>
        </p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>

<?php $conn = null; ?>