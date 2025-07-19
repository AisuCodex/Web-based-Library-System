<?php
// Disable any error output that might corrupt JSON
ini_set('display_errors', 0);
error_reporting(0);

// Enable error logging
ini_set('log_errors', 1);
error_log("Cancel thesis reservation script started");

// Include the session fix helper
require_once 'session_fix.php';

// Set content type header
header('Content-Type: application/json');

// Debug session data
error_log("SESSION data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!validateUserSession()) {
    error_log("User not logged in - SESSION: " . print_r($_SESSION, true));
    echo json_encode(['success' => false, 'message' => 'Please log in to cancel a reservation.']);
    exit;
}

// Log POST data for debugging
error_log("POST data: " . print_r($_POST, true));
error_log("Session email: " . $_SESSION['email']);

// Check if the necessary information is provided
if (!isset($_POST['reservation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing reservation ID.']);
    exit;
}

$reservation_id = intval($_POST['reservation_id']);
$user_email = $_SESSION['email'];

try {
    // Include database connection
    require_once '../ThesisPage/config.php';
    
    // Log reservation info
    error_log("Processing thesis reservation cancellation: ID=$reservation_id, User=$user_email");
    
    // Verify the reservation belongs to the user
    $check_sql = "SELECT id FROM thesis_reservations WHERE id = ? AND user_email = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Database preparation error: " . $conn->error);
    }
    
    $check_stmt->bind_param("is", $reservation_id, $user_email);
    if (!$check_stmt->execute()) {
        throw new Exception("Execution error: " . $check_stmt->error);
    }
    
    $result = $check_stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Reservation not found or does not belong to you.']);
        exit;
    }
    
    // Update the reservation status to 'cancelled'
    $update_sql = "UPDATE thesis_reservations SET status = 'cancelled' WHERE id = ? AND user_email = ?";
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        throw new Exception("Database preparation error: " . $conn->error);
    }
    
    $update_stmt->bind_param("is", $reservation_id, $user_email);
    if (!$update_stmt->execute()) {
        throw new Exception("Execution error: " . $update_stmt->error);
    }
    
    if ($update_stmt->affected_rows > 0) {
        error_log("Thesis reservation cancelled successfully: ID=$reservation_id");
        echo json_encode(['success' => true, 'message' => 'Thesis reservation cancelled successfully.']);
    } else {
        error_log("No rows affected when cancelling thesis reservation: ID=$reservation_id");
        echo json_encode(['success' => false, 'message' => 'Failed to cancel reservation. No changes made.']);
    }
    
    $update_stmt->close();
    $check_stmt->close();
    
} catch (Exception $e) {
    // Log the detailed error
    error_log("Thesis reservation cancellation error: " . $e->getMessage());
    
    // Return a clean error message to the user
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request: ' . $e->getMessage()]);
}

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>
