<?php
/**
 * Assistant Logout Script
 * This script handles assistant logout by destroying the session and logging the activity
 */

// Include necessary files
require_once '../database/Register_database.php';
require_once '../PHP/log_activity.php';

// Store the email before we destroy the session
$assistant_email = $_SESSION['email'] ?? null;

// Log the logout action to error log for debugging
error_log("Assistant logout initiated: " . ($assistant_email ?? 'unknown assistant'));

// Log to database if we have an email and connection
if ($assistant_email && isset($conn)) {
    logUserLogout($conn, $assistant_email);
    error_log("Logout recorded in database for: $assistant_email");
} else {
    error_log("Could not log logout to database: " . 
              (!$assistant_email ? "No email in session" : "No database connection"));
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
