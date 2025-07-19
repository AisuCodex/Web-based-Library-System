<?php
include 'config.php';
session_name('user_session');
session_start();
header('Content-Type: application/json');

// Create thesis_reservations table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS thesis_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    reservation_date DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (book_id) REFERENCES thesisbooks(id)
)";

if (!$conn->query($create_table_sql)) {
    echo json_encode(['success' => false, 'message' => 'Failed to create reservations table']);
    exit;
}

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_POST['book_id']) || !isset($_POST['user_email'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$book_id = $_POST['book_id'];
$user_email = $_POST['user_email'];

// Check if book exists
$check_book = $conn->prepare("SELECT * FROM thesisbooks WHERE id = ?");
$check_book->bind_param("i", $book_id);
$check_book->execute();
$result = $check_book->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Book not found']);
    exit;
}

// Check if book is already reserved by anyone
$check_reservation = $conn->prepare("SELECT * FROM thesis_reservations WHERE book_id = ? AND status = 'pending'");
$check_reservation->bind_param("i", $book_id);
$check_reservation->execute();
$result = $check_reservation->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This thesis book is already reserved and pending admin approval']);
    exit;
}

// Check if user has already reserved this book
$check_user_reservation = $conn->prepare("SELECT * FROM thesis_reservations WHERE book_id = ? AND user_email = ? AND status = 'pending'");
$check_user_reservation->bind_param("is", $book_id, $user_email);
$check_user_reservation->execute();
$user_result = $check_user_reservation->get_result();

if ($user_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reserved this thesis book']);
    exit;
}

// Create reservation
$stmt = $conn->prepare("INSERT INTO thesis_reservations (book_id, user_email, reservation_date, status) VALUES (?, ?, NOW(), 'pending')");
$stmt->bind_param("is", $book_id, $user_email);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Thesis book reserved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to reserve thesis book']);
}

$stmt->close();
$conn->close();
?>
