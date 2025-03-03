<?php
include 'includes/db_connect.php';

session_start();

// Set content type to JSON to prevent HTML errors
header('Content-Type: application/json');

// Debug incoming POST data
error_log("Received POST data: " . print_r($_POST, true));

$message = $_POST['message'] ?? '';
$userType = $_POST['userType'] ?? '';
$userData = json_decode($_POST['userData'] ?? '{}', true);
$userId = $_POST['userId'] ?? 'default_user';
$saveUserInput = isset($_POST['saveUserInput']);

if ($saveUserInput) {
    $column = $_POST['column'] ?? '';
    $value = $_POST['value'] ?? '';
    $table = ($userType == 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';

    // Removed all validations, always proceed
    // Debug log for troubleshooting
    error_log("Saving user input: Column=" . $column . ", Value=" . $value . ", UserType=" . $userType . ", Table=" . $table . ", UserID=" . $userId);

    try {
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
        exit(json_encode(['success' => true, 'message' => 'User input saved successfully']));
    } catch (PDOException $e) {
        $conn->rollBack(); // Rollback transaction on error
        error_log("Database error: " . $e->getMessage());
        exit(json_encode(['error' => 'Database error: ' . $e->getMessage() . ' Please try again or contact support at support@example.com.']));
    }
}

$response = '';
$options = [];

if (empty($userType)) {
    if (empty(trim($message))) {
        $response = "Please provide a valid response. Are you an employer or a job seeker?";
        $options = ['Employer', 'Job Seeker'];
    } elseif (strtolower($message) === 'employer' || strtolower($message) === 'job seeker' || $message === 'Employer' || $message === 'Job Seeker') {
        $_SESSION['userType'] = strtolower($message);
        $response = "Awesome, you’re a " . $_SESSION['userType'] . "! What’s your name?";
    } else {
        $response = "Sorry, I didn’t understand. Are you an employer or a job seeker?";
        $options = ['Employer', 'Job Seeker'];
    }
} else {
    $userType = $_SESSION['userType'];
    if (empty(trim($message))) {
        $response = "Please provide a valid response for this step.";
    } else {
        if ($userType == 'employer') {
            $filledFields = array_filter(array_values($userData), function($value) { return !empty(trim($value)); });
            switch (count($filledFields)) {
                case 0: // user_type (already saved)
                    $response = "What’s your name?";
                    saveUserInput('name', $message, $userId);
                    break;
                case 1: // Name
                    $response = "Please provide your email address (e.g., ankit2@email.com).";
                    saveUserInput('email', $message, $userId);
                    break;
                case 2: // Email
                    $response = "Please provide your phone number.";
                    saveUserInput('phone', $message, $userId);
                    break;
                case 3: // Phone
                    $response = "Great! What position are you looking to hire for? E.g., Software Engineer, Sales Manager, etc.";
                    saveUserInput('position', $message, $userId);
                    break;
                case 4: // Position
                    $response = "Nice! How many people do you want to hire for this role?";
                    saveUserInput('hiring_count', $message, $userId);
                    break;
                case 5: // Hiring Count
                    $response = "Got it! Any specific skills, qualifications, or experience you require for this role?";
                    saveUserInput('requirements', $message, $userId);
                    break;
                case 6: // Requirements
                    $response = "Perfect! Any preferred location for this role, like a city or region?";
                    saveUserInput('location', $message, $userId);
                    break;
                case 7: // Location
                    $response = "Thanks for the details! We’ve saved your enquiry. We’ll connect with you soon. Please don’t call us—we’ll reach out to you at: +91 98703 64340";
                    break;
            }
        } elseif ($userType == 'job seeker') {
            $filledFields = array_filter(array_values($userData), function($value) { return !empty(trim($value)); });
            switch (count($filledFields)) {
                case 0: // user_type (already saved)
                    $response = "What’s your name?";
                    saveUserInput('name', $message, $userId);
                    break;
                case 1: // Name
                    $response = "Please provide your email address (e.g., ankit2@email.com).";
                    saveUserInput('email', $message, $userId);
                    break;
                case 2: // Email
                    $response = "Please provide your phone number.";
                    saveUserInput('phone', $message, $userId);
                    break;
                case 3: // Phone
                    $response = "Awesome! What type of job are you looking for? E.g., Software Developer, Marketing Specialist, etc.";
                    saveUserInput('position', $message, $userId);
                    break;
                case 4: // Position
                    $response = "Great! How many years of experience do you have in this field?";
                    saveUserInput('experience', $message, $userId);
                    break;
                case 5: // Experience
                    $response = "Thanks! What specific skills or certifications do you have that make you stand out for this role?";
                    saveUserInput('skills_certifications', $message, $userId);
                    break;
                case 6: // Skills/Certifications
                    $response = "Perfect! Are you open to relocating, or do you prefer a specific location like a city or region?";
                    saveUserInput('location_preference', $message, $userId);
                    break;
                case 7: // Location Preference
                    $response = "Thanks for sharing! We’ve saved your enquiry. We’ll connect with you soon. Please don’t call us—we’ll reach out to you at: +91 98703 64340";
                    break;
            }
        }
    }
}

function saveUserInput($column, $value, $userId) {
    // This is a placeholder; actual saving is handled via AJAX in script.js
}

echo json_encode(['response' => $response, 'options' => $options]);
$conn = null; // Close PDO connection
?>