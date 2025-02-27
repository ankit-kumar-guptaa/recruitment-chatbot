<?php
include 'includes/db_connect.php';

session_start();

$message = $_POST['message'] ?? '';
$step = (int)($_POST['step'] ?? 0);
$userType = $_POST['userType'] ?? '';
$userData = json_decode($_POST['userData'] ?? '{}', true);

$response = '';

if ($step == 0) {
    $input = strtolower($message);
    if ($input == 'employer' || $input == 'job seeker') {
        $_SESSION['userType'] = $input;
        $response = "Great, I see you're a " . $_SESSION['userType'] . "! May I have your name, please?";
    } else {
        $response = "Sorry, I didn't get that. Are you an employer or a job seeker?";
        $step--;
    }
} else {
    $userType = $_SESSION['userType'];
    if ($userType == 'employer') {
        switch ($step) {
            case 1:
                $userData['name'] = $message;
                $response = "Thanks, " . $message . "! Can you share your email address?";
                break;
            case 2:
                $userData['email'] = $message;
                $response = "Got your email! Now, please provide your phone number.";
                break;
            case 3:
                $userData['phone'] = $message;
                $response = "Thanks for the number! What kind of staff are you looking for? For example, are you hiring for doctors, nurses, or other healthcare roles?";
                break;
            case 4:
                $userData['message'] = $message;
                $response = "Awesome! How many staff members are you looking to hire for this role?";
                break;
            case 5:
                $userData['message'] .= " | Hiring count: " . $message;
                $response = "Got it! Any specific requirements for this role, like years of experience or certifications?";
                break;
            case 6:
                $userData['message'] .= " | Requirements: " . $message;
                $stmt = $conn->prepare("INSERT INTO enquiries (user_type, name, email, phone, message) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $userType, $userData['name'], $userData['email'], $userData['phone'], $userData['message']);
                $stmt->execute();
                $response = "Thanks for the details! We've saved your enquiry. We'll connect with you soon. Please don't call us, we'll reach out to you at: +91 98703 64340.";
                break;
        }
    } elseif ($userType == 'job seeker') {
        switch ($step) {
            case 1:
                $userData['name'] = $message;
                $response = "Nice to meet you, " . $message . "! Can you share your email address?";
                break;
            case 2:
                $userData['email'] = $message;
                $response = "Thanks for the email! Now, please provide your phone number.";
                break;
            case 3:
                $userData['phone'] = $message;
                $response = "Got your number! What kind of healthcare role are you looking for? For example, are you a doctor, nurse, lab technician, or something else?";
                break;
            case 4:
                $userData['message'] = $message;
                $response = "Got it! How many years of experience do you have in this field?";
                break;
            case 5:
                $userData['experience'] = $message;
                $response = "Thanks! Do you have any specific specialization or certifications? For example, if you're a doctor, do you specialize in cardiology, pediatrics, etc.?";
                break;
            case 6:
                $userData['specialization'] = $message;
                $userData['message'] .= " | Experience: " . $userData['experience'] . " years | Specialization: " . $message;
                $stmt = $conn->prepare("INSERT INTO enquiries (user_type, name, email, phone, message) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $userType, $userData['name'], $userData['email'], $userData['phone'], $userData['message']);
                $stmt->execute();
                $response = "Thanks for sharing! We've saved your enquiry. We'll connect with you soon. Please don't call us, we'll reach out to you at: +91 98703 64340.";
                break;
        }
    }
}

echo $response;
$conn->close();
?>