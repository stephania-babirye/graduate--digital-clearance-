<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is a dean
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dean') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $application_id = (int)$_GET['id'];
    
    // Try to fetch faculty approval details from department_approvals
    $query = "SELECT * FROM department_approvals 
              WHERE application_id = $application_id AND department = 'faculty'
              LIMIT 1";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Extract only the fields we need, with defaults if columns don't exist
        $response = [
            'results_confirmed' => isset($data['results_confirmed']) ? $data['results_confirmed'] : 'pending',
            'dissertation_approved' => isset($data['dissertation_approved']) ? $data['dissertation_approved'] : 'pending',
            'faculty_name' => isset($data['faculty_name']) ? $data['faculty_name'] : '',
            'notes' => isset($data['notes']) ? $data['notes'] : ''
        ];
    } else {
        // Default values if no record exists yet
        $response = [
            'results_confirmed' => 'pending',
            'dissertation_approved' => 'pending',
            'faculty_name' => '',
            'notes' => ''
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No ID provided']);
}
?>
