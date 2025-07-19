<?php
// Include the database connection
include("../database/Register_database.php");
include("assistantAuth.php");

// Delete functionality
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Ensure it's an integer
    $delete_query = "DELETE FROM student_acc WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<script>alert('Account deleted successfully!'); window.location='ManageStudAcc.php';</script>";
    } else {
        echo "<script>alert('Failed to delete account!');</script>";
    }
    $stmt->close();
}

// Fetch student accounts
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT id, email, code, registration_date FROM student_acc";
if (!empty($search)) {
    $search_wild = "%{$search}%";
    $sql .= " WHERE email LIKE ? OR code LIKE ? OR registration_date LIKE ?";
}
$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $stmt->bind_param("sss", $search_wild, $search_wild, $search_wild);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Accounts</title>
    <link rel="stylesheet" href="../CSS/table.css">
    <link rel="stylesheet" href="../CSS/loading_screen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .delete-btn {
            padding: 8px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .delete-btn:hover {
            opacity: 0.8;
        }

        .search-section {
            margin: 20px auto;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: var(--base-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
            margin: 20px;
        }

        .back-btn:hover {
            opacity: 0.8;
        }

        body {
            padding-top: 0;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <a href="assistantPage.php" class="back-btn" onclick="showLoadingScreen()">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <div class="container">
        <h1>Manage Student Accounts</h1>
        
        <!-- Search form -->
        <form method="GET" action="" class="search-form">
            <input type="text" name="search" placeholder="Search by email, code, or date..." 
                   value="<?php echo htmlspecialchars($search); ?>" class="search-input">
            <button type="submit" class="search-btn">Search</button>
            <?php if (!empty($search)): ?>
                <a href="ManageStudAcc.php" class="clear-search">Clear Search</a>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Code</th>
                    <th>Registration Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $row_number = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row_number}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['code']}</td>
                            <td>{$row['registration_date']}</td>
                            <td>
                                <a href='ManageStudAcc.php?delete_id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this account?\")'>
                                    <button class='delete-btn'>Delete</button>
                                </a>
                            </td>
                        </tr>";
                        $row_number++;
                    }
                } else {
                    echo "<tr><td colspan='5'>No accounts found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
    function showLoadingScreen() {
        document.getElementById('loading-screen').style.display = 'flex';
    }
    </script>
</body>
</html>

<?php
$conn->close();
?>
