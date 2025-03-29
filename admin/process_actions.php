<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$action = $_POST['action'] ?? '';
$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? 0;

try {
    switch ($action) {
        case 'view':
            $table = ($type == 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';
            $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            
            if ($data) {
                // Format created_at date
                $data['created_at'] = date('M d, Y h:i A', strtotime($data['created_at']));
                
                // Add additional fields for job seekers
                if ($type == 'jobseeker') {
                    $data['comments'] = $data['comments'] ?? 'No comments';
                }
                
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['error' => 'Record not found']);
            }
            break;
            
        case 'edit':
            $table = ($type == 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';
            $columns = [];
            $values = [];
            
            foreach ($_POST as $key => $value) {
                if ($key != 'action' && $key != 'type' && $key != 'id') {
                    $columns[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            $values[] = $id;
            $query = "UPDATE $table SET " . implode(', ', $columns) . " WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute($values);
            
            echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
            break;
            
        case 'delete':
            $table = ($type == 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';
            $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log("Action error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>