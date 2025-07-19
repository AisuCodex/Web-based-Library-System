<?php
/**
 * Session Fix Helper
 * This script helps ensure consistent session handling across the application
 * Include this at the top of any PHP file that uses sessions
 */

// Start by ensuring we're using the right session name consistently
session_name('user_session');

// Start or resume the session
session_start();

// Validate session to ensure it has basic required data
function validateUserSession() {
    // Check if user is logged in
    if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
        // Session is invalid or user is not logged in
        return false;
    }
    return true;
}

// Function to safely destroy session and redirect to login
function redirectToLogin($message = "Please log in to continue.") {
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
    
    // Store message in a temporary cookie
    setcookie('login_message', $message, time() + 60, '/');
    
    // Redirect to login page
    header('Location: ../PHP/loginPage.php');
    exit;
}
?>
