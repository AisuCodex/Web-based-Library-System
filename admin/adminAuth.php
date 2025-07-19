<?php
session_name('admin_session');
session_start();
include("../database/Register_database.php");
include("../PHP/log_activity.php");

// Check if the session cookie exists
if (!isset($_COOKIE['admin_session']) || !isset($_SESSION['email'])) {
    // Redirect to login page if cookie is not set or user is not logged in
    header("Location: ../PHP/adminLogin.php");
    exit();
}

// Logout handling
if (isset($_GET['logout'])) {
    try {
        $admin_email = $_SESSION['email'] ?? null;
        
        if ($admin_email) {
            // Log the logout attempt
            error_log("Admin logout initiated for: $admin_email");
            
            // Use the improved logUserLogout function to record logout time and duration
            if (logUserLogout($conn, $admin_email)) {
                error_log("Admin logout recorded successfully for: $admin_email");
            } else {
                error_log("Failed to record admin logout for: $admin_email");
                // Fallback: Try to log a new logout activity
                logUserActivity($conn, $admin_email, 'admin', 'logout');
            }
        }
    } catch (Exception $e) {
        error_log("Error during admin logout: " . $e->getMessage());
    } finally {
        // Always perform session cleanup, regardless of any database errors
        session_unset();
        session_destroy();

        // Clear the admin session cookie with proper parameters
        if (isset($_COOKIE['admin_session'])) {
            $params = session_get_cookie_params();
            setcookie('admin_session', '', time() - 3600,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Redirect to login page
        header("Location: ../PHP/adminLogin.php");
        exit();
    }
}
?>