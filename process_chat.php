<?php
// Start output buffering to prevent any accidental output before JSON
ob_start();

// Include database connection
include 'includes/db_connect.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

session_start();

// Add CORS headers (optional, if used by other domains)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
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
            $updateQuery = "UPDATE $table SET $column = ?, created_at = NOW() WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$value, $userId]);
        } else {
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
        $isFinalStep = false;

        if ($userType === 'employer') {
            if ($column === 'user_type') $nextStep = 1;
            elseif ($column === 'name') $nextStep = 2;
            elseif ($column === 'organisation_name') $nextStep = 3;
            elseif ($column === 'city_state') $nextStep = 4;
            elseif ($column === 'position') $nextStep = 5;
            elseif ($column === 'hiring_count') $nextStep = 6;
            elseif ($column === 'requirements') $nextStep = 7;
            elseif ($column === 'email') $nextStep = 8;
            elseif ($column === 'phone') {
                $nextStep = 9;
                $isFinalStep = true; // Mark as final step for employer
            }
        } elseif ($userType === 'job seeker') {
            if ($column === 'user_type') $nextStep = 1;
            elseif ($column === 'name') $nextStep = 2;
            elseif ($column === 'fresher_experienced') $nextStep = 3;
            elseif ($column === 'applying_for_job') $nextStep = 4;
            elseif ($column === 'position') $nextStep = 5;
            elseif ($column === 'experience_years' || $column === 'skills_degree') $nextStep = 6;
            elseif ($column === 'skills_degree') $nextStep = 7;
            elseif ($column === 'location_preference') $nextStep = 8;
            elseif ($column === 'email') $nextStep = 9;
            elseif ($column === 'phone') $nextStep = 10;
            elseif ($column === 'comments') {
                $nextStep = 11;
                $isFinalStep = true; // Mark as final step for job seeker
            }
        }

        // If it's the final step, fetch all data and send email
        if ($isFinalStep) {
            $selectQuery = "SELECT * FROM $table WHERE user_id = ?";
            $stmt = $conn->prepare($selectQuery);
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'rajiv@greencarcarpool.com';
            $mail->Password   = 'Rajiv@111@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Email settings
            $mail->setFrom('rajiv@greencarcarpool.com', 'Enquiry System');
            $mail->addAddress('theankitkumarg@gmail.com'); // Replace with admin email
            $mail->isHTML(true);
            $mail->Subject = "New $userType Enquiry Submitted - User ID: $userId";

            // Build email body
            $emailBody = "<h2>New $userType Enquiry</h2><p>User ID: $userId</p><ul>";
            foreach ($userData as $key => $val) {
                if ($key !== 'user_id' && $key !== 'created_at') {
                    $emailBody .= "<li><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> $val</li>";
                }
            }
            $emailBody .= "</ul>";
            $mail->Body = $emailBody;

            // Send email
            if (!$mail->send()) {
                error_log("Email sending failed: " . $mail->ErrorInfo);
            } else {
                error_log("Email sent successfully to admin for user_id: $userId");
            }
        }

        echo json_encode(['success' => true, 'message' => 'User input saved successfully', 'nextStep' => $nextStep]);
    } else {
        echo json_encode(['success' => true, 'nextStep' => $currentStep + 1]);
    }
} catch (Exception $e) {
    error_log("PHP Error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage() . ' Please try again or contact support at support@example.com.']);
}

// Flush output buffer and send JSON
ob_end_flush();
$conn = null; // Close PDO connection
?>