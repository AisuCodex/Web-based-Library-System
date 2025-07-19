<?php
include 'config.php';

$id = $_GET['id'];

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("DELETE FROM capstonebooks WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: capstoneCrud.php"); // Redirect to capstoneCrud.php
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
?>
