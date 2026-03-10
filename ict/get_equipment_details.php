<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is an ICT officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ict') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $application_id = (int)$_GET['id'];
    
    // Try to fetch equipment details from department_approvals
    $query = "SELECT * FROM department_approvals 
              WHERE application_id = $application_id AND department = 'ict'
              LIMIT 1";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Extract only the fields we need, with defaults if columns don't exist
        $response = [
            'laptop_returned' => isset($data['laptop_returned']) ? $data['laptop_returned'] : 'n/a',
            'equipment_damaged' => isset($data['equipment_damaged']) ? $data['equipment_damaged'] : 'n/a',
            'damage_description' => isset($data['damage_description']) ? $data['damage_description'] : '',
            'equipment_notes' => isset($data['equipment_notes']) ? $data['equipment_notes'] : ''
        ];
    } else {
        // Default values if no record exists yet
        $response = [
            'laptop_returned' => 'n/a',
            'equipment_damaged' => 'n/a',
            'damage_description' => '',
            'equipment_notes' => ''
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No ID provided']);
}
?>
