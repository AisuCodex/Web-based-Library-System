<?php
require_once '../config.php';

// SQL to create the book_views table
$sql = "CREATE TABLE IF NOT EXISTS book_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    book_type VARCHAR(10) NOT NULL,
    view_date DATETIME NOT NULL,
    INDEX book_idx (book_id, book_type),
    INDEX view_date_idx (view_date)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table book_views created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
