<?php
header('Content-Type: application/json');

// Allow CORS for local and live testing
header('Access-Control-Allow-Origin: *'); // Update to specific domain in production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

error_log("Received API request: " . print_r($_POST, true)); // Debug log

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['userId'] ?? 'user_' . time() . '_' . rand(1000, 9999);
    $message = $_POST['message'] ?? '';

    $userType = $_SESSION['userType_' . $userId] ?? '';
    $currentStep = $_SESSION['currentStep_' . $userId] ?? 0;

    if ($action === 'start') {
        if (empty($userType)) {
            $response = [
                'status' => 'success',
                'message' => 'Chat started',
                'question' => 'Hello! Are you an employer looking to hire, or a job seeker looking for a job?',
                'options' => ['Employer', 'Job Seeker']
            ];
        }
    } elseif ($action === 'send') {
        if (empty($message)) {
            $response = ['status' => 'error', 'message' => 'Message cannot be empty.'];
        } else {
            if (empty($userType)) {
                if (stripos($message, 'employer') !== false || stripos($message, 'job seeker') !== false) {
                    $userType = (stripos($message, 'employer') !== false) ? 'employer' : 'job_seeker';
                    $_SESSION['userType_' . $userId] = $userType;
                    saveUserInput('user_type', $userType, $userId);
                    $currentStep = 1;
                    $_SESSION['currentStep_' . $userId] = $currentStep;
                } else {
                    $response = ['status' => 'error', 'message' => 'Please select "Employer" or "Job Seeker".'];
                }
            } else {
                $column = getColumnForStep($currentStep, $userType);
                if ($column && validateInput($message, $column)) {
                    saveUserInput($column, $message, $userId);
                    $currentStep++;
                    $_SESSION['currentStep_' . $userId] = $currentStep;
                    $response = getNextResponse($userType, $currentStep, $userId);
                    $response['step'] = $currentStep;
                } else {
                    $response = ['status' => 'error', 'message' => 'Invalid input. ' . getValidationMessage($column, $message)];
                }
            }
        }
    }

    echo json_encode($response);
    exit();
}

function saveUserInput($column, $value, $userId) {
    global $conn;
    $table = ($_SESSION['userType_' . $userId] == 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';

    try {
        $conn->beginTransaction();
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        $exists = $checkStmt->fetchColumn();

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
        $conn->commit();
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Database error: " . $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Database error. Please try again.'];
        echo json_encode($response);
        exit();
    }
}

function getColumnForStep($step, $userType) {
    if ($userType === 'employer') {
        switch ($step) {
            case 1: return 'name';
            case 2: return 'organisation_name';
            case 3: return 'city_state';
            case 4: return 'position';
            case 5: return 'hiring_count';
            case 6: return 'requirements';
            case 7: return 'email';
            case 8: return 'phone';
            default: return '';
        }
    } elseif ($userType === 'job_seeker') {
        switch ($step) {
            case 1: return 'name';
            case 2: return 'fresher_experienced';
            case 3: return 'applying_for_job';
            case 4: return 'position';
            case 5: return 'experience_years';
            case 6: return 'skills_degree';
            case 7: return 'location_preference';
            case 8: return 'email';
            case 9: return 'phone';
            case 10: return 'comments';
            default: return '';
        }
    }
    return '';
}

function getNextResponse($userType, $step, $userId) {
    $questions = [
        'employer' => [
            1 => "What’s your name?",
            2 => "Your organisation name?",
            3 => "You are from which City & State?",
            4 => "Great! What position are you looking to hire for?",
            5 => "Nice! How many people do you want to hire?",
            6 => "Got it! Any specific skills, qualifications, or experience you require for this role?",
            7 => "Please provide your email address (e.g., ankit2@email.com).",
            8 => "Please provide your phone number.",
            9 => "Thanks for the details! Our Sales Team will connect with you soon. Please call us at 9871916980 for urgent discussion."
        ],
        'job_seeker' => [
            1 => "What’s your name?",
            2 => "Are you a Fresher or Experienced? (Reply with 'Fresher' or 'Experienced')",
            3 => "Are you applying for any job posted by us on our job portal or LinkedIn? (Reply with 'Yes' or 'No')",
            4 => "Awesome! What type of job are you looking for? E.g., Software Developer, Marketing Specialist, etc.",
            5 => "Great! How many years of experience do you have in this field? (For Fresher, enter 0)",
            6 => "Thanks! What specific skills or Degree do you have that make you stand out for this role?",
            7 => "Perfect! Are you open to relocating, or do you prefer a specific location like a city or region?",
            8 => "Please provide your email address (e.g., ankit2@email.com).",
            9 => "Please provide your phone number.",
            10 => "Any other comments?",
            11 => "Thank you for sharing your details! We have saved your information and will connect with you soon."
        ]
    ];

    $question = $questions[$userType][$step] ?? '';
    $options = ($step === 2 && $userType === 'job_seeker') ? ['Fresher', 'Experienced'] : 
               (($step === 3 && $userType === 'job_seeker') ? ['Yes', 'No'] : []);

    if ($question) {
        return [
            'status' => 'success',
            'question' => $question,
            'options' => $options,
            'step' => $step
        ];
    } elseif ($step === 9 && $userType === 'employer' || $step === 11 && $userType === 'job_seeker') {
        return [
            'status' => 'success',
            'question' => $question,
            'options' => [],
            'complete' => true
        ];
    }
    return ['status' => 'error', 'message' => 'Conversation ended'];
}

function validateInput($value, $column) {
    switch ($column) {
        case 'email':
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        case 'phone':
            return preg_match("/^\d{10,15}$/", $value);
        case 'hiring_count':
        case 'experience_years':
            return is_numeric($value) && $value >= 0;
        default:
            return !empty(trim($value));
    }
}

function getValidationMessage($column, $value) {
    switch ($column) {
        case 'email':
            return 'Please enter a valid email address (e.g., ankit2@email.com).';
        case 'phone':
            return 'Please enter a valid phone number (10-15 digits).';
        case 'hiring_count':
        case 'experience_years':
            return 'Please enter a valid number (0 or greater).';
        default:
            return 'Please enter a valid response.';
    }
}
?>
