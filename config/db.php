<?php
// Support multiple hosting providers/env naming conventions (Render, Aiven, local).
function env_first(array $keys, $default = null) {
    foreach ($keys as $key) {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
    }
    return $default;
}

// Disable mysqli exception mode so we can control the error output.
mysqli_report(MYSQLI_REPORT_OFF);

$host_raw = env_first(['DB_HOST', 'MYSQLHOST', 'AIVEN_HOST'], 'localhost');
$db       = env_first(['DB_DATABASE', 'MYSQLDATABASE', 'AIVEN_DATABASE'], 'graduation_clearance');
$user     = env_first(['DB_USERNAME', 'MYSQLUSER', 'AIVEN_USER'], 'root');
$pass     = env_first(['DB_PASSWORD', 'MYSQLPASSWORD', 'AIVEN_PASSWORD'], '');
$port_raw = env_first(['DB_PORT', 'MYSQLPORT', 'AIVEN_PORT'], '3307');

$host_raw = trim((string) $host_raw);
$port_raw = trim((string) $port_raw);

// If host is provided as a URL (e.g. mysql://...), extract the hostname.
if (strpos($host_raw, '://') !== false) {
    $parsed_host = parse_url($host_raw, PHP_URL_HOST);
    if (is_string($parsed_host) && $parsed_host !== '') {
        $host_raw = $parsed_host;
    }
}

// Remove accidental protocol/path/port fragments from host values.
$host = preg_replace('#^[a-z]+://#i', '', $host_raw);
$host = preg_replace('#/.*$#', '', $host);
$host = preg_replace('#:\d+$#', '', $host);
$host = trim((string) $host);

$port = (int) $port_raw;
if ($port <= 0 || $port > 65535) {
    $port = 3307;
}

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    // Keep error user-friendly while still exposing which host/port failed.
    die('Database connection failed for host "' . htmlspecialchars($host) . '" on port ' . (int) $port . '. Check DB_HOST/DB_PORT environment values.');
}
?>