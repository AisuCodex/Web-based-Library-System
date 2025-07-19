<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $control_number = $_POST['control_number'];
    $course = $_POST['course'];
    $title = $_POST['title'];
    $abstract = $_POST['abstract'];
    $year = $_POST['year'];
    $author = $_POST['author'];

    // Use prepared statement to prevent SQL injection and handle special characters
    $stmt = $conn->prepare("INSERT INTO thesisbooks (control_number, course, title, abstract, year, author) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $control_number, $course, $title, $abstract, $year, $author);
    
    if ($stmt->execute()) {
        header("Location: ThesisCrud.php");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Book</title>
    <link rel="stylesheet" href="../CSS/Create.css">
</head>
<body>
    <div class="back-btn-container">
        <a class="back-btn" href="../Thesis_Database/ThesisCrud.php">Back</a>
    </div>
    <h1>Create New Book</h1>
    <form method="POST">
        <label for="control_number">Control Number:</label>
        <input type="text" id="control_number" name="control_number" required><br>
        <label for="course">Course:</label>
        <input type="text" id="course" name="course" required><br>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br>
        <label for="abstract">Abstract:</label>
        <textarea id="abstract" name="abstract"></textarea><br>
        <label for="year">Year:</label>
        <input type="number" id="year" name="year" required><br>
        <label for="author">Author:</label>
        <input type="text" id="author" name="author" required><br>
        <button type="submit">Add Book</button>
    </form>
</body>
</html>
