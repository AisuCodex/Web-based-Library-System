<?php
include("../database/Register_database.php");

header('Content-Type: application/json');

// Function to log errors with timestamp and details
function logError($message, $details = '') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($details) {
        $logMessage .= " | Details: $details";
    }
    error_log($logMessage);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $book_type = isset($_POST['book_type']) ? $_POST['book_type'] : '';

    // Input validation
    if (!$book_id) {
        logError("Invalid book ID provided", "book_id: " . print_r($_POST['book_id'], true));
        echo json_encode(['success' => false, 'message' => 'Invalid book ID']);
        exit;
    }

    if (!in_array($book_type, ['thesis', 'capstone'])) {
        logError("Invalid book type provided", "book_type: $book_type");
        echo json_encode(['success' => false, 'message' => 'Invalid book type']);
        exit;
    }

    // Create book_views table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS book_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_id INT NOT NULL,
        book_type ENUM('thesis', 'capstone') NOT NULL,
        view_count INT DEFAULT 0,
        last_viewed DATETIME,
        UNIQUE KEY book_unique (book_id, book_type)
    )";

    if (!$conn->query($create_table_sql)) {
        logError("Error creating table", $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    // Verify book exists in appropriate table
    $check_book_sql = "";
    if ($book_type === 'thesis') {
        $check_book_sql = "SELECT id FROM thesisbooks WHERE id = ?";
    } else {
        $check_book_sql = "SELECT id FROM capstonebooks WHERE id = ?";
    }

    $check_stmt = $conn->prepare($check_book_sql);
    if (!$check_stmt) {
        logError("Error preparing check statement", $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $check_stmt->bind_param("i", $book_id);
    if (!$check_stmt->execute()) {
        logError("Error executing check statement", $check_stmt->error);
        $check_stmt->close();
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        logError("Book not found", "ID=$book_id, Type=$book_type");
        $check_stmt->close();
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }
    $check_stmt->close();

    // Update view count
    $conn->begin_transaction();

    try {
        // Check if record exists first
        $check_exists_sql = "SELECT id FROM book_views WHERE book_id = ? AND book_type = ?";
        $check_exists_stmt = $conn->prepare($check_exists_sql);
        
        if (!$check_exists_stmt) {
            throw new Exception("Failed to prepare check statement: " . $conn->error);
        }
        
        $check_exists_stmt->bind_param("is", $book_id, $book_type);
        
        if (!$check_exists_stmt->execute()) {
            throw new Exception("Failed to execute check statement: " . $check_exists_stmt->error);
        }
        
        $check_exists_result = $check_exists_stmt->get_result();
        $record_exists = ($check_exists_result->num_rows > 0);
        $check_exists_stmt->close();
        
        if ($record_exists) {
            // Update existing record
            $update_sql = "UPDATE book_views 
                          SET view_count = view_count + 1,
                              last_viewed = NOW() 
                          WHERE book_id = ? AND book_type = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            if (!$update_stmt) {
                throw new Exception("Failed to prepare update statement: " . $conn->error);
            }

            $update_stmt->bind_param("is", $book_id, $book_type);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update view count: " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            // Insert new record
            $insert_sql = "INSERT INTO book_views (book_id, book_type, view_count, last_viewed) 
                          VALUES (?, ?, 1, NOW())";
            
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                throw new Exception("Failed to prepare insert statement: " . $conn->error);
            }

            $insert_stmt->bind_param("is", $book_id, $book_type);
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to insert view count: " . $insert_stmt->error . " (Error code: " . $conn->errno . ")");
            }
            $insert_stmt->close();
        }
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'View count updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        logError("Error tracking book view", $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating view count: ' . $e->getMessage()]);
    }
} else {
    logError("Invalid request method", $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
