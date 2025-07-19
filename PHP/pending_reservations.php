<?php
// We still need auth.php for session and database connection
include 'auth.php';
require_once '../CapstonePage/config.php'; // Ensure we have the database connection
require_once 'log_activity.php';  // Include the logging functions

// If auth.php tries to handle logout via GET parameter, we'll still handle it here
// Custom logout handling for action=logout parameter (matching thesisView.php format)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Get user's email before destroying session
    $user_email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
    
    if ($user_email && isset($conn)) {
        // Use our improved logout function that calculates session duration
        if (logUserLogout($conn, $user_email)) {
            error_log("Successfully logged out user and recorded time: " . $user_email);
        } else {
            error_log("Failed to record logout time for user: " . $user_email);
        }
    } else {
        error_log("Cannot log logout: " . (!$user_email ? "No email in session" : "No database connection"));
    }
    
    // Clear all session data
    $_SESSION = array();
    
    // If a session cookie exists, destroy it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: loginPage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pending Reservations - Hagonoy Web-based Library System</title>
  <link rel="stylesheet" href="../CSS/HomePage.css">
  <link rel="stylesheet" href="../CSS/loading_screen.css">
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
    
    .cancel-btn {
      background-color: var(--darker-shade);
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      transition: background-color 0.2s;
    }
    
    .cancel-btn:hover {
      background-color: #c0392b;
    }
    
    .no-reservations {
      text-align: center;
      padding: 30px;
      color: #7f8c8d;
      font-size: 16px;
      background-color: var(--extra-lightest-tint);
      border-radius: 6px;
      margin: 20px 0;
    }
    
    .back-to-home {
      display: inline-block;
      margin-top: 20px;
      background-color: var(--base-color);
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: 500;
      transition: background-color 0.2s;
    }
    
    .back-to-home:hover {
      background-color: var(--darker-shade);
    }
    
    .status-tabs {
      display: flex;
      margin-bottom: 20px;
      border-bottom: 1px solid var(--extra-lightest-tint);
    }
    
    .status-tab {
      padding: 12px 20px;
      margin-right: 5px;
      cursor: pointer;
      border-radius: 5px 5px 0 0;
      background-color: #f5f5f5;
      transition: all 0.3s ease;
      font-weight: 500;
    }
    
    .status-tab.active {
      background-color: var(--base-color);
      color: white;
    }
    
    /* Loading indicator */
    .loading-indicator {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 1.5rem;
      z-index: 9999;
    }
    
    .error-message {
      padding: 15px;
      background-color: #ffebee;
      color: #c62828;
      border-radius: 4px;
      margin-bottom: 20px;
      border-left: 4px solid #c62828;
    }
    
    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      width: 400px;
      text-align: center;
      position: relative;
    }
    
    .close {
      position: absolute;
      right: 15px;
      top: 10px;
      font-size: 24px;
      font-weight: bold;
      color: #aaa;
      cursor: pointer;
    }
    
    .close:hover {
      color: #333;
    }
    
    .confirmation-text {
      margin: 20px 0;
      font-size: 18px;
      color: #444;
    }
    
    #confirm-logout {
      background-color: var(--darker-shade);
      color: white;
      border: none;
      padding: 10px 20px;
      margin-right: 10px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
    }
    
    #cancel-logout {
      background-color: #7f8c8d;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
    }
    
    #confirm-logout:hover {
      background-color: #c0392b;
    }
    
    #cancel-logout:hover {
      background-color: #6c7a89;
    }
    
    .page-header {
      background-color: var(--base-color);
      color: white;
      padding: 20px 0;
      margin-bottom: 30px;
      border-radius: 8px;
      text-align: center;
    }
    
    .page-header h1 {
      margin: 0;
      font-size: 28px;
    }
    
    /* Footer styling to match thesisView page */
    .footer {
      background-color: var(--darker-shade);
      padding: 20px 0;
      text-align: center;
      margin-top: auto;
      width: 100%;
      flex-shrink: 0;
      bottom: 0;
    }
    
    .footer p {
      color: white;
      margin: 0;
    }
    
    .footer-container {
      max-width: 800px;
      margin: 0 auto;
    }
    
    @media screen and (max-width: 576px) {
      .footer {
        padding: 15px;
        margin-top: 20px;
      }
      
      .footer p {
        font-size: 14px;
      }
    }
    
    /* Add responsive styles */
    @media screen and (max-width: 768px) {
      .reservations-container {
        max-width: 100%;
        margin: 20px auto;
        padding: 15px;
      }
      
      .page-header h1 {
        font-size: 22px;
      }
      
      .status-tabs {
        flex-wrap: wrap;
        justify-content: center;
        padding-bottom: 10px;
      }
      
      .status-tab {
        padding: 8px 15px;
        margin-bottom: 5px;
        font-size: 13px;
      }
      
      .section-title {
        font-size: 20px;
      }
    }
    
    @media screen and (max-width: 576px) {
      .reservations-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
      }
      
      .reservations-table th,
      .reservations-table td {
        padding: 10px;
        font-size: 13px;
      }
      
      .cancel-btn {
        padding: 6px 8px;
        font-size: 12px;
      }
      
      .page-header {
        padding: 15px 0;
        margin-bottom: 20px;
      }
      
      .page-header h1 {
        font-size: 20px;
      }
      
      .reservations-container {
        padding: 10px;
      }
      
      .status-tab {
        flex: 1 0 40%;
        text-align: center;
        margin-right: 2px;
        padding: 8px 5px;
        font-size: 12px;
      }
    }
  </style>
</head>
<body>
  <div class="loading-overlay" id="loading-screen">
    <div class="loader"></div>
  </div>
  <div class="allContainer">
    <div class="navbar">
      <div class="hamburger-menu" onclick="toggleMenu()">
        <div class="bar bar1"></div>
        <div class="bar bar2"></div>
        <div class="bar bar3"></div>
      </div>
      <div class="nav-links">
        <a href="../PHP/homePage.php" onclick="showLoadingScreen()"><i class="fa-solid fa-house home"></i> Home </a>
        <a class="ai-anchor" href="../codex-ai/hagai.php" onclick="showLoadingScreen()">  
          <div class="ai-img-container">
             <div class="ai-logo"><img src="../img/unnamed.png" class="ai"></div> Hag AI
         </div>
        </a>
        <a href="../PHP/guidelines.php" onclick="showLoadingScreen()"><i class="fa-solid fa-scroll guidelines-icon"></i> Guidelines</a>
        <a href="../ThesisPage/thesisView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book thesis-icon"></i> Thesis Projects </a>
        <a href="../CapstonePage/capstoneView.php" onclick="showLoadingScreen()"> <i class="fa-solid fa-book capstone-icon"></i> Capstone Projects </a>
        <a href="../PHP/pending_reservations.php" onclick="showLoadingScreen()"><i class="fa-solid fa-bookmark"></i> Pending Reservations </a>
      </div>
      <div>
      </div>
    </div>

    <!-- Side Menu for Mobile -->
    <nav class="side-menu">
      <a href="../PHP/homePage.php" onclick="showLoadingScreen()"><i class="fa-solid fa-house"></i> Home </a>
      <a href="../codex-ai/hagai.php" onclick="showLoadingScreen()">  
          <div class="ai-img-container">
             <div class="ai-logo"><img src="../img/unnamed.png" class="ai">
             </div> Hag AI
         </div>
      </a>
      <a href="../PHP/guidelines.php" onclick="showLoadingScreen()"><i class="fa-solid fa-scroll guidelines-icon"></i> Guidelines</a>
      <a href="../ThesisPage/thesisView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book thesis-icon"></i> Thesis Projects </a>
      <a href="../CapstonePage/capstoneView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book capstone-icon"></i> Capstone Projects </a>
      <a href="../PHP/pending_reservations.php" onclick="showLoadingScreen()"><i class="fa-solid fa-bookmark"></i> Pending Reservations </a>
    </nav>
    
    <div class="page-header">
      <h1>Your Book Reservations</h1>
    </div>

    <div class="main-content">
      <div class="reservations-container">
        <div class="status-tabs">
          <div class="status-tab active" data-status="all">All Reservations</div>
          <div class="status-tab" data-status="pending">Pending</div>
          <div class="status-tab" data-status="approved">Approved</div>
          <div class="status-tab" data-status="rejected">Rejected</div>
          <div class="status-tab" data-status="cancelled">Cancelled</div>
        </div>
        <?php
        // Enable error reporting for debugging (remove in production)
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Check if user is logged in
        if (!isset($_SESSION['email'])) {
          header("Location: loginPage.php");
          exit();
        }

        $user_email = $_SESSION['email'];
        $has_reservations = false;
        
        // Check database connection
        try {
          require_once '../CapstonePage/config.php';
          
          // Test connection
          if (!isset($conn) || $conn->connect_error) {
            throw new Exception("Database connection failed");
          }
        } catch (Exception $e) {
          echo "<div class='error-message'>Database connection error. Please try again later.</div>";
          error_log("DB Connection Error: " . $e->getMessage());
          exit;
        }

        // ==================== HELPER FUNCTION ====================
        function displayReservations($conn, $user_email, $type = 'capstone') {
          $tableHtml = '';
          $count = 0;
          
          try {
            if ($type == 'capstone') {
              $tableName = 'book_reservations';
              $joinTable = 'capstonebooks';
              $joinField = 'book_id';
              $title = 'Capstone Projects';
            } else {
              $tableName = 'thesis_reservations';
              $joinTable = 'thesisbooks';
              
              // Try to determine correct join field by checking if thesis_id exists in the table
              $result = $conn->query("SHOW COLUMNS FROM {$tableName} LIKE 'thesis_id'");
              if ($result && $result->num_rows > 0) {
                $joinField = 'thesis_id';
              } else {
                $joinField = 'book_id';
              }
              $title = 'Thesis Projects';
            }
            
            // Check if created_at or reservation_date exists
            $dateField = 'created_at';
            $result = $conn->query("SHOW COLUMNS FROM {$tableName} LIKE 'created_at'");
            if (!$result || $result->num_rows == 0) {
              $dateField = 'reservation_date';
            }
            
            // Prepare the query with appropriate joins and conditions
            $query = "SELECT r.id as reservation_id, 
                      b.title, b.author, r.status, 
                      DATE(r.{$dateField}) as reserve_date 
                      FROM {$tableName} r 
                      JOIN {$joinTable} b ON r.{$joinField} = b.id 
                      WHERE r.user_email = ?
                      ORDER BY r.id DESC";
            
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
              throw new Exception("Query preparation failed: " . $conn->error);
            }
            
            $stmt->bind_param("s", $user_email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
              $tableHtml .= "<h2 class='section-title'>{$title}</h2>";
              $tableHtml .= "<table class='reservations-table'>";
              $tableHtml .= "<thead><tr><th>Title</th><th>Author</th><th>Reserved On</th><th>Status</th><th>Action</th></tr></thead>";
              $tableHtml .= "<tbody>";
              
              while ($row = $result->fetch_assoc()) {
                $status = strtolower($row['status'] ?? 'pending');
                $formattedDate = 'N/A';
                
                if (!empty($row['reserve_date'])) {
                  try {
                    $date = new DateTime($row['reserve_date']);
                    $formattedDate = $date->format('M d, Y');
                  } catch (Exception $e) {
                    // Date format issue, keep N/A
                  }
                }
                
                $tableHtml .= "<tr data-status='{$status}'>";
                $tableHtml .= "<td>" . htmlspecialchars($row['title']) . "</td>";
                $tableHtml .= "<td>" . htmlspecialchars($row['author']) . "</td>";
                $tableHtml .= "<td>{$formattedDate}</td>";
                $tableHtml .= "<td><span class='status-{$status}'>" . ucfirst($status) . "</span></td>";
                $tableHtml .= "<td>";
                
                if ($status === 'pending') {
                  $tableHtml .= "<button class='cancel-btn' onclick='cancelReservation(\"{$type}\", {$row['reservation_id']})'>Cancel</button>";
                } else {
                  $tableHtml .= "<span>—</span>";
                }
                
                $tableHtml .= "</td></tr>";
              }
              
              $tableHtml .= "</tbody></table>";
              $count = $result->num_rows;
            }
            
            $stmt->close();
          } catch (Exception $e) {
            $tableHtml .= "<div class='error-message'>Error loading {$title}: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log("Reservation Display Error ({$type}): " . $e->getMessage());
          }
          
          return ['html' => $tableHtml, 'count' => $count];
        }

        // ==================== DISPLAY RESERVATIONS ====================
        $capstoneDisplay = displayReservations($conn, $user_email, 'capstone');
        $thesisDisplay = displayReservations($conn, $user_email, 'thesis');
        
        // Output the HTML
        echo $capstoneDisplay['html'];
        echo $thesisDisplay['html'];
        
        // Check if any reservations were found
        $totalReservations = $capstoneDisplay['count'] + $thesisDisplay['count'];
        $has_reservations = $totalReservations > 0;
        
        if (!$has_reservations) {
          echo '<div class="no-reservations">';
          echo '<p>You don\'t have any reservations.</p>';
          echo '<a href="homePage.php" class="back-to-home">Browse Books</a>';
          echo '</div>';
        }
        
        // Close the database connection
        if (isset($conn)) {
          $conn->close();
        }
        ?>
      </div>
    </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-container">
        <p>&copy; <?php echo date("Y"); ?> Web-based Library System. All rights reserved.</p>
    </div>
  </footer>

  <script>
    // Hamburger Menu Functionality
    function toggleMenu() {
      const sideMenu = document.querySelector('.side-menu');
      const hamburger = document.querySelector('.hamburger-menu');
      sideMenu.classList.toggle('active');
      hamburger.classList.toggle('active');
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
      const sideMenu = document.querySelector('.side-menu');
      const hamburger = document.querySelector('.hamburger-menu');
      if (!event.target.closest('.hamburger-menu') && !event.target.closest('.side-menu')) {
        sideMenu.classList.remove('active');
        hamburger.classList.remove('active');
      }
    });

    // Logout Modal Functionality
    var modal = document.getElementById('logout-modal');
    var logoutLink = document.getElementById('logout-link');
    var closeBtn = document.querySelector('.modal .close');
    var confirmLogoutBtn = document.getElementById('confirm-logout');
    var cancelLogoutBtn = document.getElementById('cancel-logout');

    // Show the modal when the logout link is clicked
    logoutLink.addEventListener('click', function(event) {
      event.preventDefault(); // Prevent the default link behavior
      modal.style.display = 'block'; // Show the modal
    });

    // Close modal when the close button is clicked
    closeBtn.addEventListener('click', function() {
      modal.style.display = 'none';
    });

    // Close modal when the cancel button is clicked
    cancelLogoutBtn.addEventListener('click', function() {
      modal.style.display = 'none';
    });

    // Close modal if the user clicks outside of it
    window.addEventListener('click', function(event) {
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    });

    // Handle logout confirmation
    confirmLogoutBtn.addEventListener('click', function() {
      var loadingIndicator = document.createElement('div');
      loadingIndicator.className = 'loading-indicator';
      loadingIndicator.textContent = 'Logging out...';
      document.body.appendChild(loadingIndicator);
      
      // Redirect to the same page with logout action parameter
      window.location.href = 'pending_reservations.php?action=logout';
    });

    // Status Tab Functionality
    document.addEventListener('DOMContentLoaded', function() {
      const statusTabs = document.querySelectorAll('.status-tab');
      const reservationRows = document.querySelectorAll('.reservations-table tbody tr');
      
      // Add click event listeners to status tabs
      statusTabs.forEach(tab => {
        tab.addEventListener('click', function() {
          // Remove active class from all tabs
          statusTabs.forEach(t => t.classList.remove('active'));
          
          // Add active class to clicked tab
          this.classList.add('active');
          
          // Get the selected status
          const status = this.getAttribute('data-status');
          
          // Filter the rows based on the selected status
          reservationRows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            
            if (status === 'all' || status === rowStatus) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          });
          
          // Show message if no reservations are visible
          const visibleRows = document.querySelectorAll('.reservations-table tbody tr[style=""]');
          const noReservationsMsg = document.querySelector('.no-reservations');
          
          if (visibleRows.length === 0 && !noReservationsMsg) {
            const table = document.querySelector('.reservations-table');
            const msg = document.createElement('div');
            msg.className = 'no-reservations';
            msg.textContent = 'No ' + (status !== 'all' ? status + ' ' : '') + 'reservations found.';
            
            if (table) {
              table.insertAdjacentElement('afterend', msg);
            }
          } else if (visibleRows.length > 0 && noReservationsMsg) {
            noReservationsMsg.remove();
          }
        });
      });
    });
    
    // Cancel reservation functionality
    function cancelReservation(type, id) {
      if (!confirm("Are you sure you want to cancel this reservation?")) {
        return;
      }
      
      // Prepare the URL based on the type
      let url = '';
      if (type === 'capstone') {
        url = '../PHP/cancel_reservation.php';
      } else if (type === 'thesis') {
        url = '../PHP/cancel_thesis_reservation.php';
      } else {
        alert('Invalid reservation type.');
        return;
      }
      
      // Create FormData and append necessary data
      const formData = new FormData();
      formData.append('reservation_id', id);
      formData.append('type', type);
      
      // Show loading indicator
      const loadingIndicator = document.createElement('div');
      loadingIndicator.className = 'loading-indicator';
      loadingIndicator.textContent = 'Cancelling...';
      document.body.appendChild(loadingIndicator);
      
      // Send the AJAX request with credentials
      fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin' // Important: include cookies for session handling
      })
      .then(response => {
        // Remove loading indicator
        document.body.removeChild(loadingIndicator);
        
        // Check if the response is valid JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return response.json();
        } else {
          // Not JSON - likely HTML error page
          return response.text().then(text => {
            // If we got HTML instead of JSON, return an error object
            throw new Error('Invalid response format: ' + text.substring(0, 100) + '...');
          });
        }
      })
      .then(data => {
        if (data.success) {
          alert('Reservation cancelled successfully!');
          location.reload(); // Refresh the page
        } else {
          // Check if it's a session/login issue
          if (data.message && data.message.toLowerCase().includes('log in')) {
            alert('Your session has expired. Please log in again.');
            // Redirect to login page
            window.location.href = '../PHP/loginPage.php';
          } else {
            // Show detailed error for debugging
            console.error('Error response:', data);
            alert('Error: ' + (data.message || 'Failed to cancel reservation. Please try again.'));
          }
        }
      })
      .catch(error => {
        // Remove loading indicator if still present
        if (document.contains(loadingIndicator)) {
          document.body.removeChild(loadingIndicator);
        }
        
        console.error('Error:', error);
        
        // If the error seems like a session issue
        if (error.message && error.message.toLowerCase().includes('log in')) {
          alert('Session expired. Please log in again.');
          window.location.href = '../PHP/loginPage.php';
        } else {
          alert('An error occurred while cancelling the reservation. Please try again.');
        }
      });
    }
  </script>
</body>
</html>
