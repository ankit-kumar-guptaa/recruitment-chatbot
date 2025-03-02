<?php
include 'includes/db_connect.php';

session_start();

$sessionId = $_POST['sessionId'] ?? 'default_session';
$message = $_POST['message'] ?? '';
$step = (int)($_POST['step'] ?? 0);
$userType = $_POST['userType'] ?? '';
$userData = json_decode($_POST['userData'] ?? '{}', true);
$saveInteraction = isset($_POST['saveInteraction']);

if ($saveInteraction) {
    $userMsg = $_POST['message'] ?? '';
    $botResponse = $_POST['response'] ?? '';
    $currentStep = $_POST['step'] ?? 0;
    $table = ($userType == 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';

    // Validate input
    if (empty(trim($userMsg))) {
        exit(json_encode(['error' => 'Empty message cannot be saved.']));
    }

    // Debug log for troubleshooting
    error_log("Saving interaction: UserMsg=" . $userMsg . ", Response=" . $botResponse . ", Step=" . $currentStep . ", SessionID=" . $sessionId . ", Table=" . $table);

    // Escape and sanitize input
    $userMsg = $conn->real_escape_string($userMsg);
    $botResponse = $conn->real_escape_string($botResponse);

    // Ensure table exists and is accessible
    $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
    if ($checkTable->num_rows == 0) {
        error_log("Table $table does not exist.");
        exit(json_encode(['error' => "Table $table does not exist. Please check database setup."]));
    }

    $stmt = $conn->prepare("INSERT INTO $table (step, message, response, session_id) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        exit(json_encode(['error' => 'Database prepare failed: ' . $conn->error]));
    }

    $stmt->bind_param("isss", $currentStep, $userMsg, $botResponse, $sessionId);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        exit(json_encode(['error' => 'Database execute failed: ' . $stmt->error]));
    }
    $stmt->close();
    exit(json_encode(['success' => true, 'message' => 'Interaction saved successfully']));
}

$response = '';
$options = [];

if ($step == 0) {
    $input = strtolower($message);
    if (empty(trim($input))) {
        $response = "Please provide a valid response. Are you an employer or a job seeker?";
        $options = ['Employer', 'Job Seeker'];
    } elseif ($input == 'employer' || $input == 'job seeker') {
        $_SESSION['userType'] = $input;
        $response = "Awesome, you’re a " . $_SESSION['userType'] . "! What’s your name?";
    } else if (in_array($message, ['Employer', 'Job Seeker'])) {
        $_SESSION['userType'] = strtolower($message);
        $response = "Awesome, you’re a " . $_SESSION['userType'] . "! What’s your name?";
    } else {
        $response = "Sorry, I didn’t understand. Are you an employer or a job seeker?";
        $options = ['Employer', 'Job Seeker'];
        $step--;
    }
} else {
    $userType = $_SESSION['userType'];
    if (empty(trim($message))) {
        $response = "Please provide a valid response for this step.";
    } else {
        if ($userType == 'employer') {
            switch ($step) {
                case 1:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter a valid name (max 255 characters). What’s your name?";
                        $step--;
                    } else {
                        $userData['name'] = $message;
                        $response = "Thanks, " . $message . "! Can you share your email address?";
                    }
                    break;
                case 2:
                    if (!validateInput($message, 'email')) {
                        $response = "Please enter a valid email address (e.g., example@domain.com).";
                        $step--;
                    } else {
                        $userData['email'] = $message;
                        $response = "Got your email! Please provide your phone number (e.g., +919876543210).";
                    }
                    break;
                case 3:
                    if (!validateInput($message, 'phone')) {
                        $response = "Please enter a valid phone number (e.g., +919876543210).";
                        $step--;
                    } else {
                        $userData['phone'] = $message;
                        $response = "Great! What position are you looking to hire for? E.g., Software Engineer, Sales Manager, etc.";
                    }
                    break;
                case 4:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter a valid position (max 255 characters). What position are you hiring for?";
                        $step--;
                    } else {
                        $userData['position'] = $message;
                        $response = "Nice! How many people do you want to hire for this role?";
                    }
                    break;
                case 5:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter a valid hiring count (max 50 characters). How many people do you want to hire?";
                        $step--;
                    } else {
                        $userData['hiring_count'] = $message;
                        $response = "Got it! Any specific skills, qualifications, or experience you require for this role?";
                    }
                    break;
                case 6:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter valid requirements (max 255 characters). What skills or qualifications are required?";
                        $step--;
                    } else {
                        $userData['requirements'] = $message;
                        $response = "Perfect! Any preferred location for this role, like a city or region?";
                    }
                    break;
                case 7:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter a valid location (max 255 characters). What’s the preferred location?";
                        $step--;
                    } else {
                        $userData['location'] = $message;
                        $stmt = $conn->prepare("UPDATE employer_enquiries SET name = ?, email = ?, phone = ?, position = ?, hiring_count = ?, requirements = ?, location = ?, step = ? WHERE session_id = ? AND step = 6");
                        $stmt->bind_param("sssssssss", $userData['name'], $userData['email'], $userData['phone'], $userData['position'], $userData['hiring_count'], $userData['requirements'], $userData['location'], $step, $sessionId);
                        if (!$stmt->execute()) {
                            error_log("Update failed: " . $stmt->error);
                            exit(json_encode(['error' => 'Database update failed: ' . $stmt->error]));
                        }
                        $response = "Thanks for the details! We’ve saved your enquiry. We’ll connect with you soon. Please don’t call us—we’ll reach out to you at: +91 98703 64340";
                    }
                    break;
            }
        } elseif ($userType == 'job seeker') {
            switch ($step) {
                case 1:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter a valid name (max 255 characters). What’s your name?";
                        $step--;
                    } else {
                        $userData['name'] = $message;
                        $response = "Nice to meet you, " . $message . "! Can you share your email address?";
                    }
                    break;
                case 2:
                    if (!validateInput($message, 'email')) {
                        $response = "Please enter a valid email address (e.g., example@domain.com).";
                        $step--;
                    } else {
                        $userData['email'] = $message;
                        $response = "Thanks for the email! Please provide your phone number (e.g., +919876543210).";
                    }
                    break;
                case 3:
                    if (!validateInput($message, 'phone')) {
                        $response = "Please enter a valid phone number (e.g., +919876543210).";
                        $step--;
                    } else {
                        $userData['phone'] = $message;
                        $response = "Awesome! What type of job are you looking for? E.g., Software Developer, Marketing Specialist, etc.";
                    }
                    break;
                case 4:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter a valid position (max 255 characters). What job are you looking for?";
                        $step--;
                    } else {
                        $userData['position'] = $message;
                        $response = "Great! How many years of experience do you have in this field?";
                    }
                    break;
                case 5:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter a valid experience (max 50 characters). How many years of experience do you have?";
                        $step--;
                    } else {
                        $userData['experience'] = $message;
                        $response = "Thanks! What specific skills or certifications do you have that make you stand out for this role?";
                    }
                    break;
                case 6:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter valid skills/certifications (max 255 characters). What skills or certifications do you have?";
                        $step--;
                    } else {
                        $userData['skills_certifications'] = $message;
                        $response = "Perfect! Are you open to relocating, or do you prefer a specific location like a city or region?";
                    }
                    break;
                case 7:
                    if (!validateInput($message, 'text')) {
                        $response = "Please enter a valid location preference (max 255 characters). What’s your location preference?";
                        $step--;
                    } else {
                        $userData['location_preference'] = $message;
                        $stmt = $conn->prepare("UPDATE job_seeker_enquiries SET name = ?, email = ?, phone = ?, position = ?, experience = ?, skills_certifications = ?, location_preference = ?, step = ? WHERE session_id = ? AND step = 6");
                        $stmt->bind_param("sssssssss", $userData['name'], $userData['email'], $userData['phone'], $userData['position'], $userData['experience'], $userData['skills_certifications'], $userData['location_preference'], $step, $sessionId);
                        if (!$stmt->execute()) {
                            error_log("Update failed: " . $stmt->error);
                            exit(json_encode(['error' => 'Database update failed: ' . $stmt->error]));
                        }
                        $response = "Thanks for sharing! We’ve saved your enquiry. We’ll connect with you soon. Please don’t call us—we’ll reach out to you at: +91 98703 64340";
                    }
                    break;
            }
        }
    }
}

echo json_encode(['response' => $response, 'options' => $options]);
$conn->close();
?>