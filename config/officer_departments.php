<?php
/**
 * Officer Department/Faculty Mappings
 * 
 * This file maps officers (especially deans) to their departments/faculties.
 * Admins can update this configuration to assign deans to specific faculties.
 */

// Faculty assignments for deans
// Key: dean's user_id or email, Value: Faculty name
$dean_faculties = [
    // Example mappings (update based on actual dean accounts)
    'dean@umu.ac.ug' => 'Faculty of Science',
    'dean2@umu.ac.ug' => 'Faculty of Arts and Social Sciences',
    'dean3@umu.ac.ug' => 'Faculty of Business Administration',
    'dean4@umu.ac.ug' => 'Faculty of Education',
    
    // Add more dean-to-faculty mappings here as needed
];

/**
 * Get faculty name for a dean
 * 
 * @param string $email Dean's email address
 * @param int $user_id Dean's user ID
 * @return string Faculty name
 */
function get_dean_faculty($email, $user_id = null) {
    global $dean_faculties;
    
    // Check by email first
    if (isset($dean_faculties[$email])) {
        return $dean_faculties[$email];
    }
    
    // Check by user ID if provided
    if ($user_id && isset($dean_faculties[$user_id])) {
        return $dean_faculties[$user_id];
    }
    
    // Default faculty if not found
    return 'Faculty of Science';
}

/**
 * Get all available faculties
 * 
 * @return array List of faculties
 */
function get_all_faculties() {
    return [
        'Faculty of Science',
        'Faculty of Arts and Social Sciences',
        'Faculty of Business Administration',
        'Faculty of Education',
        'Faculty of Agriculture',
        'Faculty of Health Sciences',
        'Faculty of Built Environment',
        'Faculty of Law'
    ];
}
?>
