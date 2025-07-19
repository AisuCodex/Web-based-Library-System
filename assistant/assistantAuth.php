<?php
session_name('assistant_session');
session_start();
include("../database/Register_database.php");
include("../PHP/log_activity.php");

// Check if the session cookie exists
if (!isset($_COOKIE['assistant_session']) || !isset($_SESSION['email'])) {
    // Redirect to login page if cookie is not set or user is not logged in
    header("Location: ../PHP/assistantLogin.php");
    exit();
}

// Logout handling
if (isset($_GET['logout'])) {
    try {
        if (isset($_SESSION['email'])) {
            // Get the latest login time for this user
            $query = "SELECT MAX(action_time) as login_time FROM user_logs WHERE email = ? AND action_type = 'login'";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                error_log("Prepare failed: " . $conn->error);
            } else {
                $stmt->bind_param("s", $_SESSION['email']);
                if (!$stmt->execute()) {
                    error_log("Execute failed: " . $stmt->error);
                } else {
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $login_time = $row['login_time'];

                    if ($login_time) {
                        // Calculate duration in seconds
                        $duration = strtotime('now') - strtotime($login_time);
                        
                        // Update the user_logs table with logout time and duration
                        $update_query = "UPDATE user_logs SET logout_time = NOW(), duration = ? WHERE email = ? AND action_time = ? AND action_type = 'login'";
                        $stmt = $conn->prepare($update_query);
                        if ($stmt) {
                            $stmt->bind_param("iss", $duration, $_SESSION['email'], $login_time);
                            if (!$stmt->execute()) {
                                error_log("Failed to update logout time: " . $stmt->error);
                            }
                        } else {
                            error_log("Failed to prepare update statement: " . $conn->error);
                        }
                    }

                    // Log the logout activity
                    try {
                        logUserActivity($conn, $_SESSION['email'], 'admin', 'logout');
                    } catch (Exception $e) {
                        error_log("Failed to log logout activity: " . $e->getMessage());
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error during logout process: " . $e->getMessage());
    } finally {
        // Always perform session cleanup, regardless of any database errors
        // Destroy only the admin session
        session_unset();
        session_destroy();

        // Clear the admin session cookie
        if (isset($_COOKIE['assistant_session'])) {
            setcookie('assistant_session', '', time() - 3600, '/');
        }

        // Redirect to login page
        header("Location: ../PHP/assistantLogin.php");
        exit();
    }
}
?>