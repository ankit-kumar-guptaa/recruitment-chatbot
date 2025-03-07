<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
if (!session_id()) {
    error_log("Session not started properly.");
    session_start();
}

include '../includes/db_connect.php';

error_log("Received API request: " . print_r($_POST, true));
error_log("Session data before processing: " . print_r($_SESSION, true));

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['userId'] ?? 'user_' . time() . '_' . rand(1000, 9999);

    if (!isset($_SESSION['userType_' . $userId])) $_SESSION['userType_' . $userId] = '';
    if (!isset($_SESSION['currentStep_' . $userId])) $_SESSION['currentStep_' . $userId] = 0;

    $userType = $_SESSION['userType_' . $userId];
    $currentStep = $_SESSION['currentStep_' . $userId];

    if (empty($action)) {
        $response = ['status' => 'error', 'message' => 'Action parameter is missing.'];
        echo json_encode($response);
        exit();
    }

    if ($action === 'start') {
        $_SESSION['userType_' . $userId] = '';
        $_SESSION['currentStep_' . $userId] = 0;
        $response = [
            'status' => 'success',
            'message' => 'Chat started',
            'question' => 'Hello! Are you an employer looking to hire, or a job seeker looking for a job?',
            'options' => ['Employer', 'Job Seeker']
        ];
    } elseif ($action === 'send') {
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            $response = ['status' => 'error', 'message' => 'Message parameter is missing.'];
            echo json_encode($response);
            exit();
        }

        if (empty($userType)) {
            $normalizedMessage = strtolower($message);
            if ($normalizedMessage === 'employer' || $normalizedMessage === 'job seeker') {
                $userType = $normalizedMessage;
                $_SESSION['userType_' . $userId] = $userType;
                saveUserInput('user_type', $userType, $userId);
                $currentStep = 1;
                $_SESSION['currentStep_' . $userId] = $currentStep;
                $response = getNextResponse($userType, $currentStep, $userId);
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
                if ($response['question'] === '' && ($currentStep === 9 && $userType === 'employer' || $currentStep === 11 && $userType === 'job_seeker')) {
                    $response['complete'] = true;
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Invalid input. ' . getValidationMessage($column, $message)];
            }
        }
        $response['step'] = $currentStep;
        $response['userType'] = $userType;
    }
}

error_log("Session data after processing: " . print_r($_SESSION, true));
echo json_encode($response);
exit();

function saveUserInput($column, $value, $userId) {
    global $conn;
    $table = ($_SESSION['userType_' . $userId] === 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';

    try {
        $conn->beginTransaction();
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            $updateQuery = "UPDATE $table SET $column = ?, updated_at = NOW() WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$value, $userId]);
        } else {
            $columns = ($column === 'user_type') ? 'user_id, user_type, created_at' : "user_id, $column, created_at";
            $values = ($column === 'user_type') ? "?, ?, NOW()" : "?, ?, NOW()";
            $insertQuery = "INSERT INTO $table ($columns) VALUES ($values)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->execute([$userId, $value]);
        }
        $conn->commit();
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Database save error: " . $e->getMessage());
        throw $e;
    }
}

function getColumnForStep($step, $userType) {
    if ($userType === 'employer') {
        $steps = [
            1 => 'name',
            2 => 'organisation_name',
            3 => 'city_state',
            4 => 'position',
            5 => 'hiring_count',
            6 => 'requirements',
            7 => 'email',
            8 => 'phone'
        ];
    } elseif ($userType === 'job_seeker') {
        $steps = [
            1 => 'name',
            2 => 'fresher_experienced',
            3 => 'applying_for_job',
            4 => 'position',
            5 => 'experience_years',
            6 => 'skills_degree',
            7 => 'location_preference',
            8 => 'email',
            9 => 'phone',
            10 => 'comments'
        ];
    }
    return $steps[$step] ?? '';
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
            11 => "Thank you for sharing your details! We have saved your information and will connect with you soon. Please note that we place candidates based on company requirements—we do not create job openings."
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
    }
    return ['status' => 'success', 'question' => '', 'options' => [], 'step' => $step];
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