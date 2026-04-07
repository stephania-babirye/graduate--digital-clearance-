<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// For local development in a subfolder
if ($host === 'localhost' || $host === '127.0.0.1') {
    // Adjust 'code final' to your actual subfolder name if it's different
    $subfolder = '/code final'; 
    define('BASE_URL', $protocol . $host . $subfolder . '/');
} else {
    // For live server
    define('BASE_URL', $protocol . $host . '/');
}
?>
