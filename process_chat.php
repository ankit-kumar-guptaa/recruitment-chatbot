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

        // Server-side validation
        if ($column === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format. Please use a valid email address (e.g., ankit2@email.com).");
        }
        if ($column === 'phone' && !preg_match("/^[0-9]{10,15}$/", $value)) {
            throw new Exception("Invalid phone number. Please use 10-15 digits (e.g., 9871916980).");
        }
        if ($column === 'hiring_count' && (!is_numeric($value) || $value <= 0)) {
            throw new Exception("Invalid hiring count. Please enter a positive number.");
        }

        $table = ($userType == 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';

        // Debug log for troubleshooting
        error_log("Saving user input: Column=" . $column . ", Value=" . $value . ", UserType=" . $userType . ", Table=" . $table . ", UserID=" . $userId . ", CurrentStep=" . $currentStep);

        $conn->beginTransaction(); // Start transaction for consistency

        // Check if the row exists for this user_id
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        $exists = $checkStmt->fetchColumn();

        // Prepare the update/insert query
        if ($exists) {
            // Update existing row with timestamp update (fixed SET clause)
            $updateQuery = "UPDATE $table SET $column = ?, created_at = NOW() WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$value, $userId]);
        } else {
            // Insert new row with only the new column, user_id, and created_at
            if ($column === 'user_type') {
                $insertQuery = "INSERT INTO $table (user_id, user_type, created_at) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($insertQuery);
                $stmt->execute([$userId, $value]);
            } else {
                $insertQuery = "INSERT INTO $table ($column, user_id, created_at) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($insertQuery);
                $stmt->execute([$value, $userId]);
            }
        }

        $stmt->closeCursor();
        $conn->commit(); // Commit transaction

        // Determine the next step based on the column being saved
        $nextStep = $currentStep + 1;
        if ($userType === 'employer') {
            if ($column === 'user_type') $nextStep = 1;
            elseif ($column === 'name') $nextStep = 2;
            elseif ($column === 'organisation_name') $nextStep = 3;
            elseif ($column === 'city_state') $nextStep = 4;
            elseif ($column === 'position') $nextStep = 5;
            elseif ($column === 'hiring_count') $nextStep = 6;
            elseif ($column === 'requirements') $nextStep = 7;
            elseif ($column === 'email') $nextStep = 8;
            elseif ($column === 'phone') $nextStep = 9;
        } elseif ($userType === 'job seeker') {
            if ($column === 'user_type') $nextStep = 1;
            elseif ($column === 'name') $nextStep = 2;
            elseif ($column === 'fresher_experienced') $nextStep = 3;
            elseif ($column === 'applying_for_job') $nextStep = 4;
            elseif ($column === 'position') $nextStep = 5;
            elseif ($column === 'experience_years' || $column === 'skills_degree') $nextStep = 6; // Handle conditional step for Fresher/Experienced
            elseif ($column === 'skills_degree') $nextStep = 7;
            elseif ($column === 'location_preference') $nextStep = 8;
            elseif ($column === 'email') $nextStep = 9;
            elseif ($column === 'phone') $nextStep = 10;
            elseif ($column === 'comments') $nextStep = 11;
        }

        echo json_encode(['success' => true, 'message' => 'User input saved successfully', 'nextStep' => $nextStep]);
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