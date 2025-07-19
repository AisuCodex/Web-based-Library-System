<?php
require_once '../config.php';
include("adminAuth.php");
include("../database/Register_database.php");

// Create account_history table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS account_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    status ENUM('approved', 'rejected') NOT NULL,
    action_date DATETIME NOT NULL,
    INDEX (status),
    INDEX (action_date)
)";
mysqli_query($conn, $create_table_sql);

// Add code column to student_acc table if it doesn't exist
$check_column_sql = "SHOW COLUMNS FROM student_acc LIKE 'code'";
$result = mysqli_query($conn, $check_column_sql);
if (mysqli_num_rows($result) == 0) {
    $add_column_sql = "ALTER TABLE student_acc ADD COLUMN code VARCHAR(10)";
    mysqli_query($conn, $add_column_sql);
}

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle account approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        
        if ($_POST['action'] === 'approve') {
            // Get email and code before deleting from verification
            $email_query = "SELECT email, code FROM verification WHERE id = ?";
            $stmt = $conn->prepare($email_query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            $email = $user_data['email'];
            $code = $user_data['code'];
            $stmt->close();

            // Copy to student_acc with the code
            $copy_stmt = $conn->prepare("INSERT INTO student_acc (email, password, code) SELECT email, password, code FROM verification WHERE id = ?");
            if ($copy_stmt) {
                $copy_stmt->bind_param("i", $userId);
                if($copy_stmt->execute()) {
                    // Add to history
                    $history_stmt = $conn->prepare("INSERT INTO account_history (email, status, action_date) VALUES (?, 'approved', NOW())");
                    $history_stmt->bind_param("s", $email);
                    $history_stmt->execute();
                    $history_stmt->close();

                    // Delete from verification
                    $delete_stmt = $conn->prepare("DELETE FROM verification WHERE id = ?");
                    $delete_stmt->bind_param("i", $userId);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }
                $copy_stmt->close();
            }
        } elseif ($_POST['action'] === 'reject') {
            // Get email before deleting
            $email_query = "SELECT email FROM verification WHERE id = ?";
            $stmt = $conn->prepare($email_query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            $email = $user_data['email'];
            $stmt->close();

            // Add to history
            $history_stmt = $conn->prepare("INSERT INTO account_history (email, status, action_date) VALUES (?, 'rejected', NOW())");
            $history_stmt->bind_param("s", $email);
            $history_stmt->execute();
            $history_stmt->close();

            // Delete from verification
            $delete_stmt = $conn->prepare("DELETE FROM verification WHERE id = ?");
            $delete_stmt->bind_param("i", $userId);
            $delete_stmt->execute();
            $delete_stmt->close();
        } elseif ($_POST['action'] === 'delete_history') {
            // Delete from history
            $stmt = $conn->prepare("DELETE FROM account_history WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
        }
        
        // Redirect to refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch pending accounts
$query = "SELECT * FROM verification ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Fetch approved accounts history
$approved_query = "SELECT * FROM account_history WHERE status = 'approved' ORDER BY action_date DESC";
$approved_result = mysqli_query($conn, $approved_query);

// Fetch rejected accounts history
$rejected_query = "SELECT * FROM account_history WHERE status = 'rejected' ORDER BY action_date DESC";
$rejected_result = mysqli_query($conn, $rejected_query);

if ($result === false || $approved_result === false || $rejected_result === false) {
    die("Error in query: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Account Verifications</title>
    <link rel="stylesheet" href="../CSS/pending_accounts.css">
    <link rel="stylesheet" href="../CSS/Loading_screen.css">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            padding: 20px;
            box-sizing: border-box;
            overflow: auto;
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90vh;
            object-fit: contain;
        }

        .close {
            position: absolute;
            right: 25px;
            top: 15px;
            color: #f1f1f1;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }

        .account-card img {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .account-card img:hover {
            transform: scale(1.05);
        }

        /* Zoom controls */
        .zoom-controls {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 5px;
            display: none;
            z-index: 1001;
        }

        .zoom-controls button {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Loading screen element -->
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Account Management</h1>
            <a href="adminPage.php" class="back-btn" onclick="showLoadingScreen()">Back to Dashboard</a>
        </div>
        
        <!-- Pending Accounts Section -->
        <section class="account-section pending-section">
            <h2>Pending Accounts</h2>
            <div class="account-grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="account-card">
                            <?php 
                                $profileImage = '../uploads/profile_images/' . htmlspecialchars($row['profile_image']);
                                $defaultImage = '../img/default-id.png';
                            ?>
                            <img src="<?php echo $profileImage; ?>" 
                                 alt="Student ID" 
                                 onerror="this.src='<?php echo $defaultImage; ?>';"
                                 onclick="expandImage(this.src)"
                                 class="student-id-image">
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($row['email']); ?></h3>
                                <p class="request-time">
                                    Requested: <?php echo date('M d, Y g:i A', strtotime($row['created_at'])); ?>
                                </p>
                                <div class="button-group">
                                    <button onclick="approveAccount(<?php echo $row['id']; ?>)" class="approve-btn">Approve</button>
                                    <button onclick="rejectAccount(<?php echo $row['id']; ?>)" class="reject-btn">Reject</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-accounts">No pending accounts</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Image Modal -->
        <div id="imageModal" class="modal">
            <span class="close" onclick="closeModal()">&times;</span>
            <img id="expandedImg" class="modal-content">
        </div>

        <!-- Account History Sections -->
        <div class="history-container">
            <!-- Approved Accounts History -->
            <section class="account-section history-section approved-section">
                <h2>Approved Accounts History</h2>
                <div class="account-grid">
                    <?php if (mysqli_num_rows($approved_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($approved_result)): ?>
                            <div class="account-card history-card approved">
                                <div class="card-content">
                                    <h3><?php echo htmlspecialchars($row['email']); ?></h3>
                                    <p class="date">Approved on: <?php echo date('M d, Y g:i A', strtotime($row['action_date'])); ?></p>
                                    <div class="button-group">
                                        <button onclick="deleteHistory(<?php echo $row['id']; ?>)" class="delete-btn">Delete Record</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-accounts">No approved accounts history</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Rejected Accounts History -->
            <section class="account-section history-section rejected-section">
                <h2>Rejected Accounts History</h2>
                <div class="account-grid">
                    <?php if (mysqli_num_rows($rejected_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($rejected_result)): ?>
                            <div class="account-card history-card rejected">
                                <div class="card-content">
                                    <h3><?php echo htmlspecialchars($row['email']); ?></h3>
                                    <p class="date">Rejected on: <?php echo date('M d, Y g:i A', strtotime($row['action_date'])); ?></p>
                                    <div class="button-group">
                                        <button onclick="deleteHistory(<?php echo $row['id']; ?>)" class="delete-btn">Delete Record</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-accounts">No rejected accounts history</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Add JavaScript for delete functionality -->
    <script>
        function showLoadingScreen() {
            document.getElementById('loading-screen').style.display = 'flex';
        }

        function approveAccount(id) {
            if (confirm('Are you sure you want to approve this account?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'approve';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'user_id';
                idInput.value = id;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function rejectAccount(id) {
            if (confirm('Are you sure you want to reject this account?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'reject';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'user_id';
                idInput.value = id;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteHistory(id) {
            if (confirm('Are you sure you want to delete this record?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_history';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'user_id';
                idInput.value = id;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <script>
        // Image modal functionality
        function expandImage(imgSrc) {
            const modal = document.getElementById('imageModal');
            const expandedImg = document.getElementById('expandedImg');
            expandedImg.src = imgSrc;
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal when clicking outside the image
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php
// Close database connection
if ($conn) {
    mysqli_close($conn);
}
?>
