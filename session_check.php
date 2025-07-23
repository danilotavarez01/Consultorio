<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Auto logout after inactivity (30 minutes = 1800 seconds)
$inactive_time = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_time)) {
    // Last activity was more than 30 minutes ago
    session_unset();     // Unset all session variables
    session_destroy();   // Destroy the session
    header("location: login.php?logout=inactive");
    exit;
}
// Update last activity time
$_SESSION['last_activity'] = time();
?>