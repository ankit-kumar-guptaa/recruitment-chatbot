<?php
// Start output buffering to prevent any accidental output before JSON
ob_start();

// Include database connection
include 'includes/db_connect.php';

session_start();

// Set content type to JSON explicitly
header('Content-Type: application/json');

// Debug incoming POST data
error_log("Received POST data: " . print_r($_POST, true));

$message = $_POST['message'] ?? '';
$userType = $_POST['userType'] ?? '';
$userData = json_decode($_POST['userData'] ?? '{}', true);
$userId = $_POST['userId'] ?? 'default_user';
$saveUserInput = isset($_POST['saveUserInput']);
$currentStep = $_POST['currentStep'] ?? 0;

try {
    if ($saveUserInput) {
        $column = $_POST['column'] ?? '';
        $value = $_POST['value'] ?? '';
        $table = ($userType == 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';

        // Removed all validations, always proceed
        // Debug log for troubleshooting
        error_log("Saving user input: Column=" . $column . ", Value=" . $value . ", UserType=" . $userType . ", Table=" . $table . ", UserID=" . $userId . ", CurrentStep=" . $currentStep);

        $conn->beginTransaction(); // Start transaction for consistency

        // Check if the row exists for this user_id
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        $exists = $checkStmt->fetchColumn();

        // Prepare the update/insert query
        if ($exists) {
            // Update existing row
            $updateQuery = "UPDATE $table SET $column = ?, user_id = ? WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$value, $userId, $userId]);
        } else {
            // Insert new row with only the new column and user_id (and user_type if first save)
            if ($column === 'user_type') {
                $insertQuery = "INSERT INTO $table (user_id, user_type) VALUES (?, ?)";
                $stmt = $conn->prepare($insertQuery);
                $stmt->execute([$userId, $value]);
            } else {
                $insertQuery = "INSERT INTO $table ($column, user_id) VALUES (?, ?)";
                $stmt = $conn->prepare($insertQuery);
                $stmt->execute([$value, $userId]);
            }
        }

        $stmt->closeCursor();
        $conn->commit(); // Commit transaction
        echo json_encode(['success' => true, 'message' => 'User input saved successfully', 'nextStep' => $currentStep + 1]);
    } else {
        // No question logic here, just return success for chat flow
        echo json_encode(['success' => true, 'nextStep' => $currentStep + 1]);
    }
} catch (Exception $e) {
    // Catch any PHP errors and return as JSON
    error_log("PHP Error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage() . ' Please try again or contact support at support@example.com.']);
}

// Flush output buffer and send JSON
ob_end_flush();
$conn = null; // Close PDO connection
?>