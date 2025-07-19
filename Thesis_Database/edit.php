<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $control_number = $_POST['control_number'];
    $course = $_POST['course'];
    $title = $_POST['title'];
    $abstract = $_POST['abstract'];
    $year = $_POST['year'];
    $author = $_POST['author'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("UPDATE thesisbooks SET control_number=?, course=?, title=?, abstract=?, year=?, author=? WHERE id=?");
    $stmt->bind_param("ssssssi", $control_number, $course, $title, $abstract, $year, $author, $id);
    
    if ($stmt->execute()) {
        header("Location: ThesisCrud.php");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    // Get the book data
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM thesisbooks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="../CSS/Create.css">
</head>
<body>
    <div class="back-btn-container">
        <a class="back-btn" href="../Thesis_Database/ThesisCrud.php">Back</a>
    </div>
    <h1>Edit Book</h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($book['id']); ?>">
        <label for="control_number">Control Number:</label>
        <input type="text" id="control_number" name="control_number" value="<?php echo htmlspecialchars($book['control_number']); ?>" required><br>
        <label for="course">Course:</label>
        <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($book['course']); ?>" required><br>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required><br>
        <label for="abstract">Abstract:</label>
        <textarea id="abstract" name="abstract"><?php echo htmlspecialchars($book['abstract']); ?></textarea><br>
        <label for="year">Year:</label>
        <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($book['year']); ?>" required><br>
        <label for="author">Author:</label>
        <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required><br>
        <button type="submit">Update Book</button>
    </form>
</body>
</html>
