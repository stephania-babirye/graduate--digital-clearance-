<?php
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
if (!empty($forwardedProto)) {
    $primaryProto = strtolower(trim(explode(',', $forwardedProto)[0]));
    $protocol = ($primaryProto === 'https') ? 'https://' : 'http://';
} else {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $protocol = $isHttps ? 'https://' : 'http://';
}

$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
$host = trigm(explode(',', $host)[0]);

$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/';
$pathParts = explode('/', trim(dirname($scriptPath), '/'));
$subfolder = '';

if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    if (!empty($pathParts[0])) {
        $subfolder = '/' . $pathParts[0];
    }
}

define('BASE_URL', $protocol . $host . $subfolder . '/');
?>
