<?php
session_name('user_session');
session_start();
include("../database/Register_database.php");
include("log_activity.php");

// Check if the session cookie exists
if (!isset($_COOKIE['user_session']) || !isset($_SESSION['email'])) {
    // Redirect to login page if cookie is not set or user is not logged in
    header("Location: ../PHP/loginPage.php");
    exit();
}

// Logout handling
if (isset($_GET['logout'])) {
    try {
        if (isset($_SESSION['email'])) {
            // Store email before clearing session
            $user_email = $_SESSION['email'];
            error_log("Recording logout for user: " . $user_email);
            
            // First, create a direct logout record
            $insert_logout_sql = "INSERT INTO user_logs (email, user_type, action_type, action_time) VALUES (?, 'student', 'logout', NOW())";
            $logout_stmt = $conn->prepare($insert_logout_sql);
            
            if ($logout_stmt) {
                $logout_stmt->bind_param("s", $user_email);
                if (!$logout_stmt->execute()) {
                    error_log("Failed to insert logout record: " . $logout_stmt->error);
                } else {
                    error_log("Successfully inserted logout record");
                }
                $logout_stmt->close();
            }
            
            // Find the most recent login record without logout time
            $find_login_sql = "SELECT id, action_time FROM user_logs 
                              WHERE email = ? 
                              AND action_type = 'login' 
                              AND (logout_time IS NULL OR logout_time = '0000-00-00 00:00:00')
                              ORDER BY action_time DESC LIMIT 1";
            
            $find_stmt = $conn->prepare($find_login_sql);
            if (!$find_stmt) {
                error_log("Failed to prepare find statement: " . $conn->error);
            } else {
                $find_stmt->bind_param("s", $user_email);
                
                if (!$find_stmt->execute()) {
                    error_log("Failed to execute find statement: " . $find_stmt->error);
                } else {
                    $result = $find_stmt->get_result();
                    
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $login_id = $row['id'];
                        $login_time = $row['action_time'];
                        
                        error_log("Found login record ID: " . $login_id . " at time: " . $login_time);
                        
                        // Get current time directly from database for consistency
                        $time_query = "SELECT NOW() as current_time";
                        $time_result = $conn->query($time_query);
                        $current_time = $time_result->fetch_assoc()['current_time'];
                        
                        // Calculate duration in seconds using TIMESTAMPDIFF in SQL for accuracy
                        $duration_query = "SELECT TIMESTAMPDIFF(SECOND, ?, NOW()) as duration";
                        $duration_stmt = $conn->prepare($duration_query);
                        $duration_stmt->bind_param("s", $login_time);
                        $duration_stmt->execute();
                        $duration_result = $duration_stmt->get_result();
                        $duration = $duration_result->fetch_assoc()['duration'];
                        $duration_stmt->close();
                        
                        error_log("Calculated duration: " . $duration . " seconds");
                        
                        // Update the login record with logout time and duration
                        $update_sql = "UPDATE user_logs 
                                      SET logout_time = NOW(), 
                                          duration = ? 
                                      WHERE id = ?";
                        
                        $update_stmt = $conn->prepare($update_sql);
                        if (!$update_stmt) {
                            error_log("Failed to prepare update statement: " . $conn->error);
                        } else {
                            $update_stmt->bind_param("ii", $duration, $login_id);
                            
                            if (!$update_stmt->execute()) {
                                error_log("Failed to update logout time: " . $update_stmt->error);
                            } else {
                                $affected = $update_stmt->affected_rows;
                                error_log("Successfully updated logout time. Affected rows: " . $affected);
                                
                                // Force a commit to ensure changes are saved
                                $conn->query("COMMIT");
                            }
                            $update_stmt->close();
                        }
                    } else {
                        error_log("No matching login record found for user: " . $user_email);
                    }
                }
                $find_stmt->close();
            }
        }
    } catch (Exception $e) {
        error_log("Error during logout process: " . $e->getMessage());
    } finally {
        // Always perform session cleanup
        session_unset();
        session_destroy();
        
        if (isset($_COOKIE['user_session'])) {
            setcookie('user_session', '', time() - 3600, '/');
        }
        
        // Redirect to login page
        header("Location: ../PHP/loginPage.php");
        exit();
    }
}
?>