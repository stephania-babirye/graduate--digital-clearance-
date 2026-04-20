<?php
// Common header for all pages
require_once __DIR__ . '/../config/base_url.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$showGlobalLogout = isset($_SESSION['user_id']) && $currentPage !== 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graduate Digital Clearance System - Uganda Martyrs University</title>
    <!-- Bootstrap CSS -->
    <link id="bootstrap-css" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" onerror="document.documentElement.classList.add('no-bootstrap')">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts - Celtic Style -->
    <link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&family=MedievalSharp&display=swap" rel="stylesheet">
    <!-- Custom CSS (cache-busted to ensure latest styles load) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <script>
        // If CDN Bootstrap is blocked or unavailable, enable fallback styles after page load.
        window.addEventListener('load', function () {
            var hasBootstrapVars = getComputedStyle(document.documentElement)
                .getPropertyValue('--bs-body-font-family')
                .trim() !== '';

            if (!hasBootstrapVars) {
                document.documentElement.classList.add('no-bootstrap');
            }
        });
    </script>
    <script>
        // Compatibility shim to avoid runtime errors from scripts expecting mgt.clearMarks().
        (function () {
            if (typeof window.mgt === 'undefined' || window.mgt === null || typeof window.mgt !== 'object') {
                window.mgt = {};
            }
            if (typeof window.mgt.clearMarks !== 'function') {
                window.mgt.clearMarks = function () {
                    return;
                };
            }
        })();
    </script>
</head>
<body>
<header class="app-header p-3 text-center shadow-sm">
    <?php if ($showGlobalLogout): ?>
    <a href="<?php echo BASE_URL; ?>login/logout.php" class="global-logout global-logout-top" aria-label="Logout">
        Logout
    </a>
    <?php endif; ?>
    <div class="umu-brand">
        <img class="umu-logo" src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="UMU Logo" onerror="this.style.display='none'">
        <div class="umu-wordmark">
            <h1 class="umu-title">
                <span class="umu-accent">U</span>ganda <span class="umu-accent">M</span>artyrs <span class="umu-accent">U</span>niversity
            </h1>
            <p class="motto umu-motto">Making a difference</p>
        </div>
    </div>
</header>
