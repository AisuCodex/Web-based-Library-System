<?php
function logUserActivity($conn, $email, $user_type, $action_type) {
    // Create user_logs table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS user_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        user_type ENUM('admin', 'student', 'assistant') NOT NULL,
        action_type ENUM('login', 'logout') NOT NULL,
        action_time DATETIME NOT NULL,
        logout_time DATETIME NULL,
        duration INT NULL,
        INDEX (email),
        INDEX (user_type),
        INDEX (action_type),
        INDEX (action_time)
    )";
    mysqli_query($conn, $create_table_sql);

    // Set timezone explicitly for this connection
    mysqli_query($conn, "SET time_zone = '+08:00'");
    
    // Get current time in Asia/Manila timezone
    $manila_time = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $current_time = $manila_time->format('Y-m-d H:i:s');

    // Insert log entry with explicit time
    $stmt = $conn->prepare("INSERT INTO user_logs (email, user_type, action_type, action_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $user_type, $action_type, $current_time);
    $stmt->execute();
    $stmt->close();
}

/**
 * Update the logout time and calculate session duration
 * 
 * @param mysqli $conn Database connection
 * @param string $email User email
 * @return bool Success status
 */
function logUserLogout($conn, $email) {
    if (empty($email)) {
        error_log("Cannot log logout: Empty email provided");
        return false;
    }
    
    try {
        // Set timezone explicitly for this connection
        mysqli_query($conn, "SET time_zone = '+08:00'");
        
        // Find the latest login record for this user
        $stmt = $conn->prepare("
            SELECT id, action_time 
            FROM user_logs 
            WHERE email = ? AND action_type = 'login' 
                AND logout_time IS NULL
            ORDER BY action_time DESC 
            LIMIT 1
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $login_id = $row['id'];
            
            // Get current time in Asia/Manila timezone
            $manila_time = new DateTime('now', new DateTimeZone('Asia/Manila'));
            $current_time = $manila_time->format('Y-m-d H:i:s');
            
            // Calculate duration using timestamps
            $login_time = new DateTime($row['action_time'], new DateTimeZone('Asia/Manila'));
            $duration = $manila_time->getTimestamp() - $login_time->getTimestamp();
            
            // Update the login record with logout time and duration
            $update_stmt = $conn->prepare("
                UPDATE user_logs 
                SET logout_time = ?,
                    duration = ?
                WHERE id = ?
            ");
            
            if (!$update_stmt) {
                error_log("Prepare update failed: " . $conn->error);
                return false;
            }
            
            $update_stmt->bind_param("sii", $current_time, $duration, $login_id);
            $success = $update_stmt->execute();
            $update_stmt->close();
            
            if (!$success) {
                error_log("Failed to update logout record: " . $conn->error);
            }
            
            return $success;
        } else {
            // Insert a new logout entry if no login record found
            logUserActivity($conn, $email, determineUserType($email), 'logout');
            error_log("No matching login record found for $email, created new logout record");
            return true;
        }
    } catch (Exception $e) {
        error_log("Error in logUserLogout: " . $e->getMessage());
        return false;
    }
}

/**
 * Determine user type based on email pattern
 * 
 * @param string $email User email
 * @return string User type (admin, assistant, or student)
 */
function determineUserType($email) {
    if (strpos($email, 'admin') !== false) {
        return 'admin';
    } elseif (strpos($email, 'assistant') !== false) {
        return 'assistant';
    } else {
        return 'student';
    }
}
?>
