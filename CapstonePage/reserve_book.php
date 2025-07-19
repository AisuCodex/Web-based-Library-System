<?php
include 'config.php';
session_name('user_session');
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to reserve a book.']);
    exit;
}

// Check if book_id and user_email are provided
if (!isset($_POST['book_id']) || !isset($_POST['user_email'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required information.']);
    exit;
}

$book_id = intval($_POST['book_id']);
$user_email = $_POST['user_email'];

// Verify that the book exists
$check_book = $conn->prepare("SELECT id FROM capstonebooks WHERE id = ?");
$check_book->bind_param("i", $book_id);
$check_book->execute();
$book_result = $check_book->get_result();

if ($book_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Book not found.']);
    exit;
}

// Check if book is already reserved by anyone
$check_book_reserved = $conn->prepare("SELECT id FROM book_reservations WHERE book_id = ? AND status = 'pending'");
$check_book_reserved->bind_param("i", $book_id);
$check_book_reserved->execute();
$reserved_result = $check_book_reserved->get_result();

if ($reserved_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This book is already reserved and pending admin approval.']);
    exit;
}

// Check if user has already reserved this book
$check_existing = $conn->prepare("SELECT id FROM book_reservations WHERE book_id = ? AND user_email = ? AND status = 'pending'");
$check_existing->bind_param("is", $book_id, $user_email);
$check_existing->execute();
$existing_result = $check_existing->get_result();

if ($existing_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reserved this book.']);
    exit;
}

// Create the reservation
$stmt = $conn->prepare("INSERT INTO book_reservations (book_id, user_email, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("is", $book_id, $user_email);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Book reserved successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to reserve book. Please try again.']);
}

$stmt->close();
$conn->close();
?>
