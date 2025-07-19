<?php
// Include the database connection
include("../database/Register_database.php");
include("adminAuth.php");

// Add status column if it doesn't exist
$alter_table = "ALTER TABLE assistant_acc ADD COLUMN IF NOT EXISTS status ENUM('active', 'disabled') DEFAULT 'active'";
mysqli_query($conn, $alter_table);

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $account_id = intval($_GET['toggle_status']);
    $status_query = "UPDATE assistant_acc SET status = CASE WHEN status = 'active' THEN 'disabled' ELSE 'active' END WHERE id = ?";
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param("i", $account_id);
    if ($stmt->execute()) {
        header("Location: manageAdmin.php?success=Status updated successfully");
    } else {
        header("Location: manageAdmin.php?error=Failed to update status");
    }
    exit();
}

// Delete functionality
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM assistant_acc WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: manageAdmin.php?success=Account deleted successfully");
    } else {
        header("Location: manageAdmin.php?error=Failed to delete account");
    }
}

// Fetch admin accounts with search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT id, email, code, created_at, status FROM assistant_acc";
if (!empty($search)) {
    $search_wild = "%{$search}%";
    $sql .= " WHERE email LIKE ? OR code LIKE ? OR created_at LIKE ?";
}

$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Accounts</title>
    <link rel="stylesheet" href="../CSS/table.css">
    <link rel="stylesheet" href="../CSS/loading_screen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .delete-btn {
            padding: 8px 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-block;
        }
        .delete-btn:hover {
            background-color: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .delete-btn i {
            margin-right: 5px;
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

        .status-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .status-active {
            background-color: #4CAF50;
            color: white;
        }
        .status-disabled {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <a href="adminPage.php" class="back-btn" onclick="showLoadingScreen()">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <div class="container">
        <h1>Manage Assistant Accounts</h1>
        
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="Search by email, code, or date..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Code</th>
                    <th>Created At</th>
                    <th>Status</th>
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
                            <td>" . date('Y-m-d H:i:s', strtotime($row['created_at'])) . "</td>
                            <td>Active</td>
                            <td>
                                <a href='?delete_id={$row['id']}' 
                                   class='delete-btn' 
                                   onclick=\"return confirm('Are you sure you want to delete this account?')\">
                                    <i class='fas fa-trash'></i> Delete
                                </a>
                            </td>
                        </tr>";
                        $row_number++;
                    }
                } else {
                    echo "<tr><td colspan='6'>No admin accounts found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="../JavaScripts/loadingScreen.js"></script>
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
