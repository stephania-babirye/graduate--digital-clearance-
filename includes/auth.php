<?php
// Authentication logic placeholder
session_start();
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
function getUserRole() {
    return $_SESSION['role'] ?? null;
}
?>