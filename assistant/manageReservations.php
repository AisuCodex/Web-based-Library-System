<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'assistantAuth.php';
    include '../CapstonePage/config.php';

    // Handle deletion
    if (isset($_POST['delete_id']) && isset($_POST['table_type'])) {
        $delete_id = intval($_POST['delete_id']);
        $table = $_POST['table_type'];
        
        // Validate table type for security
        if ($table === 'thesis_reservations' || $table === 'book_reservations') {
            $delete_stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
            if (!$delete_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $delete_stmt->bind_param("i", $delete_id);
            
            if ($delete_stmt->execute()) {
                header("Location: manageReservations.php?success=2");
                exit;
            } else {
                throw new Exception("Delete failed: " . $delete_stmt->error);
            }
        }
    }

    // Handle status updates
    if (isset($_POST['reservation_id']) && isset($_POST['status']) && isset($_POST['table_type'])) {
        $id = intval($_POST['reservation_id']);
        $status = $_POST['status'];
        $table = $_POST['table_type'];
        
        // Validate status and table type for security
        $valid_statuses = ['pending', 'approved', 'rejected', 'cancelled'];
        if (in_array($status, $valid_statuses) && ($table === 'thesis_reservations' || $table === 'book_reservations')) {
            $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("si", $status, $id);
            
            if ($stmt->execute()) {
                header("Location: manageReservations.php?success=1");
                exit;
            } else {
                throw new Exception("Update failed: " . $stmt->error);
            }
        }
    }

    // Fetch all reservations with book details
    $query = "(SELECT 
                r.id,
                r.user_email COLLATE utf8mb4_unicode_ci as student_name,
                c.title COLLATE utf8mb4_unicode_ci as book_title,
                c.control_number,
                r.reservation_date,
                r.status COLLATE utf8mb4_unicode_ci as status,
                'capstone' COLLATE utf8mb4_unicode_ci as book_type,
                'book_reservations' COLLATE utf8mb4_unicode_ci as table_type
              FROM book_reservations r 
              JOIN capstonebooks c ON r.book_id = c.id)
              UNION ALL
              (SELECT 
                r.id,
                r.user_email COLLATE utf8mb4_unicode_ci as student_name,
                t.title COLLATE utf8mb4_unicode_ci as book_title,
                t.control_number,
                r.reservation_date,
                r.status COLLATE utf8mb4_unicode_ci as status,
                'thesis' COLLATE utf8mb4_unicode_ci as book_type,
                'thesis_reservations' COLLATE utf8mb4_unicode_ci as table_type
              FROM thesis_reservations r 
              JOIN thesisbooks t ON r.book_id = t.id)
              ORDER BY reservation_date DESC";

    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

} catch (Exception $e) {
    // Log the error
    error_log("Error in manageReservations.php: " . $e->getMessage());
    
    // Display user-friendly error
    echo "<div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border: 1px solid #ef9a9a; font-family: Arial, sans-serif;'>";
    echo "<h2 style='margin-top: 0;'>An Error Occurred</h2>";
    echo "<p><strong>We encountered an error while processing your request.</strong></p>";
    echo "<p>Please try again later or contact support if the problem persists.</p>";
    echo "<p><a href='assistantPage.php' style='color: #2196f3; text-decoration: none;'>← Return to Dashboard</a></p>";
    if (isset($_SESSION['email']) && strpos($_SESSION['email'], 'admin') !== false) {
        echo "<hr style='margin: 20px 0; border: none; border-top: 1px solid #ef9a9a;'>";
        echo "<div style='font-family: monospace; background: #fff; padding: 10px; border: 1px solid #ef9a9a;'>";
        echo "<strong>Error Details (for admin):</strong><br>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    echo "</div>";
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations - Assistant</title>
    <link rel="stylesheet" href="../CSS/loading_screen.css">
    <link rel="stylesheet" href="../CSS/HomePage.css">
    <link rel="stylesheet" href="../CSS/icon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Custom styles for reservations page */
        :root {
            --darkest-shade: #2a3417;
            --darker-shade: #3f4a22;
            --base-color: #556b2f;
            --lighter-tint: #758b4d;
            --lightest-tint: #99b27a;
            --extra-lightest-tint: #dbe4d0;
        }
        
        html {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            font-family: 'Outfit', sans-serif;
        }
        
        .reservations-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .page-header {
            background-color: var(--base-color);
            color: white;
            padding: 20px 40px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 500;
        }
        
        .section-title {
            color: var(--darkest-shade);
            border-bottom: 2px solid var(--extra-lightest-tint);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
        }
        
        .reservations-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .reservations-table th {
            background-color: var(--base-color);
            color: #fff;
            padding: 14px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 15px;
        }
        
        .reservations-table td {
            padding: 14px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        .reservations-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .reservations-table tr:hover {
            background-color: var(--extra-lightest-tint);
            transition: background-color 0.2s;
        }
        
        .status-pending {
            color: #ff9800;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: rgba(255, 152, 0, 0.1);
        }
        
        .status-approved {
            color: #4CAF50;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: rgba(76, 175, 80, 0.1);
        }
        
        .status-rejected {
            color: #F44336;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: rgba(244, 67, 54, 0.1);
        }
        
        .status-cancelled {
            color: #9E9E9E;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: rgba(158, 158, 158, 0.1);
        }
        
        .approve-btn, .reject-btn, .delete-btn, .back-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            margin-right: 5px;
            font-size: 13px;
            transition: all 0.2s;
            display: inline-block;
            text-decoration: none;
        }
        
        .approve-btn {
            background-color: #4CAF50;
            color: white;
        }
        
        .reject-btn {
            background-color: #F44336;
            color: white;
        }
        
        .delete-btn {
            background-color: #607D8B;
            color: white;
        }

        .back-btn {
            background-color: var(--darker-shade);
            color: white;
            margin: 20px;
            display: inline-block;
        }
        
        .approve-btn:hover, .reject-btn:hover, .delete-btn:hover, .back-btn:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }
        
        .success-message, .error-message {
            padding: 15px;
            margin: 20px auto;
            border-radius: 5px;
            font-weight: 500;
            max-width: 1200px;
            text-align: center;
        }
        
        .success-message {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            border-left: 4px solid #4CAF50;
        }
        
        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border-left: 4px solid #F44336;
        }

        .status-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            overflow-x: auto;
        }
        
        .status-tab {
            padding: 12px 25px;
            cursor: pointer;
            font-weight: 500;
            color: #555;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .status-tab.active {
            color: var(--base-color);
            border-bottom-color: var(--base-color);
        }
        
        .status-tab:hover:not(.active) {
            color: var(--lighter-tint);
            border-bottom-color: var(--extra-lightest-tint);
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .reservations-container {
                margin: 20px 10px;
                padding: 20px 15px;
            }
            
            .status-tabs {
                padding-bottom: 5px;
            }
            
            .status-tab {
                padding: 8px 15px;
                font-size: 14px;
            }
            
            .reservations-table {
                display: block;
                overflow-x: auto;
            }
            
            .reservations-table th, 
            .reservations-table td {
                padding: 10px;
            }
            
            .approve-btn, .reject-btn, .delete-btn {
                margin-right: 2px;
                padding: 8px 5px;
                font-size: 12px;
            }
        }

        /* Topbar styling */
        .assistant-topbar {
            background-color: var(--darkest-shade);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .assistant-topbar h2 {
            margin: 0;
            font-size: 18px;
        }

        .assistant-nav {
            display: flex;
            gap: 20px;
        }

        .assistant-nav a {
            color: white;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .assistant-nav a:hover {
            opacity: 0.8;
        }

        /* No reservations message */
        .no-reservations {
            text-align: center;
            padding: 30px;
            color: #757575;
            font-style: italic;
        }
    </style>
    <script>
        function confirmDelete(id, tableType) {
            if (confirm('Are you sure you want to delete this reservation?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('delete_table_type').value = tableType;
                document.getElementById('delete_form').submit();
            }
        }

        function confirmStatusUpdate(id, status, tableType) {
            let actionText = status === 'approved' ? 'approve' : 'reject';
            if (confirm(`Are you sure you want to ${actionText} this reservation?`)) {
                document.getElementById('reservation_id').value = id;
                document.getElementById('status').value = status;
                document.getElementById('table_type').value = tableType;
                document.getElementById('status_form').submit();
            }
        }

        // Tab filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.status-tab');
            const rows = document.querySelectorAll('tbody tr');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    tab.classList.add('active');
                    
                    const status = tab.getAttribute('data-status');
                    
                    // Filter rows based on status
                    rows.forEach(row => {
                        const rowStatus = row.getAttribute('data-status');
                        if (status === 'all' || rowStatus === status) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</head>
<body>
    <!-- Loading screen -->
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <!-- Hidden forms for actions -->
    <form id="delete_form" method="POST" style="display: none;">
        <input type="hidden" id="delete_id" name="delete_id">
        <input type="hidden" id="delete_table_type" name="table_type">
    </form>

    <form id="status_form" method="POST" style="display: none;">
        <input type="hidden" id="reservation_id" name="reservation_id">
        <input type="hidden" id="status" name="status">
        <input type="hidden" id="table_type" name="table_type">
    </form>

    <!-- Page Header -->
    <div class="page-header">
        <h1>Manage Student Reservations</h1>
    </div>

    <!-- Main Content -->
    <div class="reservations-container">
        <a href="assistantPage.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        
        <?php if (isset($_GET['success'])): ?>
            <?php if ($_GET['success'] == '1'): ?>
                <div class="success-message">Reservation status updated successfully!</div>
            <?php elseif ($_GET['success'] == '2'): ?>
                <div class="success-message">Reservation deleted successfully!</div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] == '1'): ?>
                <div class="error-message">Failed to delete reservation. Please try again.</div>
            <?php elseif ($_GET['error'] == '2'): ?>
                <div class="error-message">Failed to update reservation status. Please try again.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="status-tabs">
            <div class="status-tab active" data-status="all">All Reservations</div>
            <div class="status-tab" data-status="pending">Pending</div>
            <div class="status-tab" data-status="approved">Approved</div>
            <div class="status-tab" data-status="rejected">Rejected</div>
            <div class="status-tab" data-status="cancelled">Cancelled</div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table class="reservations-table">
                <thead>
                    <tr>
                        <th>Student Email</th>
                        <th>Book Title</th>
                        <th>Control Number</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-status="<?php echo strtolower($row['status']); ?>">
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['control_number']); ?></td>
                            <td><?php echo date('M d, Y g:i A', strtotime($row['reservation_date'])); ?></td>
                            <td>
                                <span class="status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="button-group">
                                    <?php if ($row['status'] !== 'approved'): ?>
                                        <button onclick="confirmStatusUpdate(<?php echo $row['id']; ?>, 'approved', '<?php echo $row['table_type']; ?>')" class="approve-btn">
                                            Approve
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['status'] !== 'rejected'): ?>
                                        <button onclick="confirmStatusUpdate(<?php echo $row['id']; ?>, 'rejected', '<?php echo $row['table_type']; ?>')" class="reject-btn">
                                            Reject
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo $row['table_type']; ?>')" class="delete-btn">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-reservations">
                <p><i class="fa-solid fa-info-circle"></i> No reservations found in the system.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="../JavaScripts/loadingScreen.js"></script>
</body>
</html>
