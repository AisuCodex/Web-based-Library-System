<?php
/**
 * Logout Script
 * This script handles user logout by destroying the session
 */

// Include the session fix helper to ensure consistent session handling
require_once 'session_fix.php';
require_once '../CapstonePage/config.php'; // Database connection
require_once 'log_activity.php';  // Activity logging functions

// Store the email before we destroy the session
$user_email = $_SESSION['email'] ?? null;

// Log the logout action to error log for debugging
error_log("User logout initiated: " . ($user_email ?? 'unknown user'));

// Log to database if we have an email and connection
if ($user_email && isset($conn)) {
    logUserLogout($conn, $user_email);
    error_log("Logout recorded in database for: $user_email");
} else {
    error_log("Could not log logout to database: " . 
              (!$user_email ? "No email in session" : "No database connection"));
}

// Clear the session data
$_SESSION = array();

// If a session cookie exists, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Return success response for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// For non-AJAX requests, redirect to the home page
header('Location: ../index.php');
exit;
?>
