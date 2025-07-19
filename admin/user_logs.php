<?php
session_start();
include("../database/Register_database.php");

// Create user_logs table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS user_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'student') NOT NULL,
    action_type ENUM('login', 'logout') NOT NULL,
    action_time DATETIME NOT NULL,
    INDEX (email),
    INDEX (user_type),
    INDEX (action_type),
    INDEX (action_time)
)";
mysqli_query($conn, $create_table_sql);

// Handle delete operations
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM user_logs WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<script>
            window.onload = function() {
                document.getElementById('success-modal').style.display = 'flex';
                document.getElementById('success-message').textContent = 'Log entry deleted successfully!';
            }
        </script>";
    } else {
        echo "<script>
            window.onload = function() {
                document.getElementById('error-modal').style.display = 'flex';
                document.getElementById('error-message').textContent = 'Failed to delete log entry!';
            }
        </script>";
    }
    $stmt->close();
}

// Get search parameters
$search_email = isset($_GET['search_email']) ? trim($_GET['search_email']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';

// Prepare the base query to get the latest status for each login
$query = "WITH LoginSessions AS (
    SELECT 
        l1.id,
        l1.email,
        l1.user_type,
        l1.action_time as login_time,
        COALESCE(
            (SELECT MIN(l2.action_time)
             FROM user_logs l2
             WHERE l2.email = l1.email
             AND l2.action_type = 'logout'
             AND l2.action_time > l1.action_time), 
            NULL
        ) as logout_time
    FROM user_logs l1
    WHERE l1.action_type = 'login'
)
SELECT * FROM LoginSessions";

// Add search conditions if provided
$conditions = [];
$params = [];
$types = "";

if (!empty($search_email)) {
    $conditions[] = "email LIKE ?";
    $params[] = "%$search_email%";
    $types .= "s";
}

if (!empty($start_date)) {
    $conditions[] = "DATE(login_time) = ?";
    $params[] = $start_date;
    $types .= "s";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY login_time DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Logs</title>
    <link rel="stylesheet" href="../CSS/user_logs.css">
    <link rel="stylesheet" href="../CSS/loading_screen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .search-section {
            margin: 20px auto;
            max-width: 800px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-container {
            margin-bottom: 15px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            color: var(--darkest-shade);
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.2rem;
            border-bottom: 1px solid var(--extra-lightest-tint);
            padding-bottom: 10px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 220px;
            margin-bottom: 0;
        }
        
        .form-group label {
            margin-bottom: 5px;
            color: var(--darker-shade);
            font-weight: 500;
            font-size: 14px;
        }

        .search-form input[type="text"],
        .search-form input[type="date"] {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            font-size: 14px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
            height: 45px;
            box-sizing: border-box;
        }

        .search-btn {
            padding: 12px 20px;
            background-color: var(--base-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 120px;
            height: 45px;
            box-sizing: border-box;
        }
        
        .search-btn:hover {
            background-color: var(--darker-shade);
        }

        .user-type, .action-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            color: white;
        }

        .admin {
            background-color: var(--darker-shade);
        }

        .student {
            background-color: var(--lighter-tint);
        }

        .login {
            background-color: var(--base-color);
        }

        .logout {
            background-color: var(--darkest-shade);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 5px;
        }

        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            color: var(--base-color);
            background-color: white;
            border: 1px solid var(--base-color);
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination a.active {
            background-color: var(--base-color);
            color: white;
            border-color: var(--base-color);
        }

        .pagination a:hover:not(.active) {
            background-color: var(--extra-lightest-tint);
        }

        @media screen and (max-width: 768px) {
            .form-row {
                flex-direction: column;
                align-items: stretch;
            }

            .form-group {
                width: 100%;
                min-width: unset;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
            
            .pagination a {
                padding: 6px 12px;
                font-size: 0.9em;
            }
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            position: relative;
            text-align: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-content h2 {
            color: var(--darkest-shade);
            margin-top: 0;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .modal-content p {
            margin-bottom: 25px;
            color: var(--darker-shade);
            font-size: 1rem;
        }

        .modal-btn {
            display: inline-block;
            padding: 10px 25px;
            background-color: var(--base-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            margin: 0 5px;
        }

        .modal-btn:hover {
            background-color: var(--darker-shade);
        }

        .error-btn {
            background-color: var(--darkest-shade);
        }

        .error-btn:hover {
            background-color: #1a2010;
        }

        .cancel-btn {
            background-color: var(--lighter-tint);
        }

        .cancel-btn:hover {
            background-color: var(--base-color);
        }

        .modal-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .success-icon {
            color: var(--base-color);
        }

        .error-icon {
            color: var(--darkest-shade);
        }

        .warning-icon {
            color: var(--darker-shade);
        }

        .modal-btn-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media screen and (max-width: 576px) {
            .modal-content {
                width: 85%;
                padding: 20px;
            }
            
            .modal-btn {
                width: 100%;
                margin: 5px 0;
            }
            
            .modal-btn-container {
                flex-direction: column;
            }
        }
        
        .button-group {
            align-self: flex-end;
            margin-top: 0;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="modal">
        <div class="modal-content">
            <i class="fas fa-check-circle modal-icon success-icon"></i>
            <h2>Success</h2>
            <p id="success-message"></p>
            <a href="user_logs.php" class="modal-btn">Continue</a>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="modal">
        <div class="modal-content">
            <i class="fas fa-exclamation-circle modal-icon error-icon"></i>
            <h2>Error</h2>
            <p id="error-message"></p>
            <a href="user_logs.php" class="modal-btn error-btn">Try Again</a>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="modal">
        <div class="modal-content">
            <i class="fas fa-question-circle modal-icon warning-icon"></i>
            <h2>Confirm Delete</h2>
            <p id="confirm-message">Are you sure you want to delete this log entry?</p>
            <div class="modal-btn-container">
                <button id="confirm-yes-btn" class="modal-btn error-btn">Delete</button>
                <button id="confirm-no-btn" class="modal-btn cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="delete-item-form" method="GET" action="user_logs.php" style="display:none;">
        <input type="hidden" id="delete-id-input" name="delete_id" value="">
    </form>
    
    <h1>User Activity Logs</h1>

    <a href="adminPage.php" class="back-btn" onclick="showLoadingScreen()">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="search-section">
        <div class="search-container">
            <h3 class="section-title"><i class="fas fa-search"></i> Search Logs</h3>
            <form class="search-form" method="GET" onsubmit="showLoadingScreen()">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search_email">Email</label>
                        <input type="text" id="search_email" name="search_email" placeholder="Search by email" 
                            value="<?php echo htmlspecialchars($search_email); ?>">
                    </div>
                    <div class="form-group">
                        <label for="start_date">Date</label>
                        <input type="date" id="start_date" name="start_date" 
                            value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="form-group button-group">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>User Type</th>
                <th>Status</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Duration</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td data-label="User Type">
                        <span class="user-type <?php echo strtolower($row['user_type']); ?>">
                            <?php echo htmlspecialchars($row['user_type']); ?>
                        </span>
                    </td>
                    <td data-label="Status">
                        <span class="action-type <?php echo $row['logout_time'] ? 'logout' : 'login'; ?>">
                            <?php echo $row['logout_time'] ? 'Logged Out' : 'Active'; ?>
                        </span>
                    </td>
                    <td data-label="Login Time"><?php echo date('Y-m-d h:i:s A', strtotime($row['login_time'])); ?></td>
                    <td data-label="Logout Time">
                        <?php 
                        if ($row['logout_time']) {
                            echo date('Y-m-d h:i:s A', strtotime($row['logout_time']));
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td data-label="Duration">
                        <?php
                        if ($row['logout_time']) {
                            $login_time = new DateTime($row['login_time']);
                            $logout_time = new DateTime($row['logout_time']);
                            $duration = $login_time->diff($logout_time);
                            $duration_in_seconds = $duration->h * 3600 + $duration->i * 60 + $duration->s;
                            
                            $hours = floor($duration_in_seconds / 3600);
                            $minutes = floor(($duration_in_seconds % 3600) / 60);
                            $seconds = $duration_in_seconds % 60;
                            
                            if ($hours > 0) {
                                echo $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
                            } else if ($minutes > 0) {
                                echo $minutes . 'm ' . $seconds . 's';
                            } else {
                                echo $seconds . 's';
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td data-label="Action">
                        <a href="#" class="delete-btn" onclick="confirmDelete(<?php echo $row['id']; ?>)">
                            <i class="fa-solid fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <script src="../JavaScript/loading_screen.js"></script>
    <script>
        // Function to refresh the page
        function refreshPage() {
            location.reload();
        }
        
        // Show loading screen for all links and forms
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading screen to form submissions
            var forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    showLoadingScreen();
                });
            });
            
            // Add loading screen to confirm modal yes button
            document.getElementById('confirm-yes-btn').addEventListener('click', function() {
                showLoadingScreen();
            });
        });
        
        // Confirm delete
        function confirmDelete(id) {
            document.getElementById('confirm-message').textContent = 'Are you sure you want to delete this log entry?';
            document.getElementById('confirm-modal').style.display = 'flex';
            
            document.getElementById('confirm-yes-btn').onclick = function() {
                document.getElementById('delete-id-input').value = id;
                document.getElementById('delete-item-form').submit();
                return false;
            };
            
            document.getElementById('confirm-no-btn').onclick = function() {
                document.getElementById('confirm-modal').style.display = 'none';
                return false;
            };
            
            return false;
        }

        // Set up auto-refresh every 30 seconds
        setInterval(refreshPage, 30000);
    </script>
</body>
</html>
