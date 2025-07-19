<?php
include 'config.php';
include '../PHP/log_activity.php';
session_name('user_session');
session_start();

// Check if the session cookie exists and the user is logged in
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: ../PHP/loginPage.php");
    exit();
}

// Logout handling
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Log the logout activity before destroying the session
    logUserActivity($conn, $_SESSION['email'], 'student', 'logout');
    
    session_unset();
    session_destroy();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    header("Location: ../PHP/loginPage.php");
    exit();
}

// Initialize variables
$search = '';
$isNumeric = false;
$titleCount = 0; // Variable to store the count of matching titles
$abstractCount = 0; // Variable to store the count of matching abstracts
$showAllResults = false; // Flag to determine if pagination should be bypassed for search

if (isset($_POST['search'])) {
    $search = trim($_POST['search']);
    $isNumeric = is_numeric($search);

    // If a search term is provided, bypass pagination
    if ($search !== '') {
        $showAllResults = true;
    }
}

// Pagination settings
$recordsPerPage = 5; // Number of records to display per page
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page number
$offset = ($currentPage - 1) * $recordsPerPage; // Calculate the offset for SQL query

// Prepare the SQL query with a search condition and limit/offset for pagination
$sql = "SELECT * FROM thesisbooks";
$countTitleSql = "SELECT COUNT(*) as title_count FROM thesisbooks";
$countAbstractSql = "SELECT COUNT(*) as abstract_count FROM thesisbooks";

if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $sql .= " WHERE title LIKE '%$search%' OR abstract LIKE '%$search%' OR author LIKE '%$search%' OR year LIKE '%$search%' OR control_number LIKE '%$search%' OR course LIKE '%$search%'";
    $countTitleSql .= " WHERE title LIKE '%$search%' OR control_number LIKE '%$search%' OR course LIKE '%$search%'";
    $countAbstractSql .= " WHERE abstract LIKE '%$search%' OR control_number LIKE '%$search%' OR course LIKE '%$search%'";
}

if (!$showAllResults) {
    $sql .= " LIMIT $offset, $recordsPerPage";
}

$result = $conn->query($sql);
if (!$result) {
    die("Error in query: " . $conn->error);
}

// Count how many titles contain the search term
$titleCountResult = $conn->query($countTitleSql);
if ($titleCountResult && $row = $titleCountResult->fetch_assoc()) {
    $titleCount = $row['title_count'];
}

// Count how many abstracts contain the search term
$abstractCountResult = $conn->query($countAbstractSql);
if ($abstractCountResult && $row = $abstractCountResult->fetch_assoc()) {
    $abstractCount = $row['abstract_count'];
}

// Get total records for pagination (without LIMIT clause for full count)
$totalRecordsSql = "SELECT COUNT(*) as total FROM thesisbooks";
if ($search !== '') {
    $totalRecordsSql .= " WHERE title LIKE '%$search%' OR abstract LIKE '%$search%' OR author LIKE '%$search%' OR year LIKE '%$search%' OR control_number LIKE '%$search%' OR course LIKE '%$search%'";
}
$totalRecordsResult = $conn->query($totalRecordsSql);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Function to highlight search terms
function highlightSearchTerm($text, $searchTerm) {
    if (is_numeric($searchTerm) || $searchTerm === '') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    return preg_replace("/\b(" . preg_quote($searchTerm, '/') . ")\b/i", "<mark>$1</mark>", htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>THESIS PROJECTS</title>
  <link rel="stylesheet" href="../CSS/HomePage.css">
  <link rel="stylesheet" href="../CSS/homePage_slider.css">
  <link rel="stylesheet" href="../CSS/table.css">
  <link rel="stylesheet" href="../CSS/loading_screen.css">
  <link rel="stylesheet" href="../CSS/icon.css">
  <link rel="stylesheet" href="../CSS/ai-logo-img.css">
  <link rel="stylesheet" href="../CSS/modal.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="../JavaScripts/showAbstract.js"></script>
  <script src="../JavaScripts/mobileMenu.js" defer></script>
  <style>
    .ui-autocomplete {
      max-height: 200px;
      overflow-y: auto;
      overflow-x: hidden;
      z-index: 1000;
      background: white;
      border: 1px solid #ccc;
      border-radius: 4px;
      padding: 5px 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .ui-menu-item {
      padding: 5px 10px;
      cursor: pointer;
    }
    .ui-menu-item:hover {
      background: #f0f0f0;
    }
    .ui-helper-hidden-accessible {
      display: none;
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
        background-color: rgba(0, 0, 0, 0.4);
        animation: fadeIn 0.3s;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        border-radius: 8px;
        width: 80%;
        max-width: 500px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.3s;
    }

    @keyframes fadeIn {
        from {opacity: 0}
        to {opacity: 1}
    }

    @keyframes slideIn {
        from {transform: translateY(-50px); opacity: 0;}
        to {transform: translateY(0); opacity: 1;}
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
    }

    .modal h2 {
        color: var(--darker-shade);
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .confirm-btn {
        background-color: var(--base-color);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
    }

    .confirm-btn:hover {
        background-color: var(--darker-shade);
    }

    .cancel-btn {
        background-color: #f1f1f1;
        color: #333;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
    }

    .cancel-btn:hover {
        background-color: #e1e1e1;
    }

    /* Status colors for response modal */
    .success-response h2 {
        color: #4CAF50;
    }

    .error-response h2 {
        color: #F44336;
    }
    /* Scroll to Top Button Styling */
    #topBtn {
        display: none;
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 99;
        font-size: 18px;
        border: none;
        outline: none;
        background-color: var(--darker-shade);
        color: white;
        cursor: pointer;
        padding: 15px;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s, transform 0.3s, opacity 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.7;
    }

    #topBtn i {
        margin: 0;
        padding: 0;
        display: inline-block;
    }

    #topBtn:hover {
        background-color: var(--base-color);
        transform: scale(1.1);
        opacity: 1;
    }
  </style>
</head>
<body>
  <!-- Loading screen element -->
  <div class="loading-overlay" id="loading-screen">
    <div class="loader"></div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div id="logout-modal" class="modal">
    <div class="modal-content">
      <i class="close">&times;</i>
      <h2>Logout Confirmation</h2>
      <p class="confirmation-text">Are you sure you want to log out?</p>
      <button id="confirm-logout">Yes, Log Out</button>
      <button id="cancel-logout" class="cancel">Cancel</button>
    </div>
  </div>

  <!-- Abstract Modal -->
  <div id="abstract-modal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2 id="modal-title"></h2>
      <div id="modal-abstract"></div>
    </div>
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
        <a href="#" id="logout-link">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
      </div>
    </div>

    <!-- Side Menu for Mobile -->
    <div class="side-menu">
      <a href="../PHP/homePage.php" onclick="showLoadingScreen()"><i class="fa-solid fa-house"></i> Home </a>
      <a href="../codex-ai/hagai.php" onclick="showLoadingScreen()">  
          <div class="ai-img-container">
             <div class="ai-logo"><img src="../img/unnamed.png" class="ai"></div> Hag AI
         </div>
      </a>
      <a href="../PHP/guidelines.php" onclick="showLoadingScreen()"><i class="fa-solid fa-scroll guidelines-icon"></i> Guidelines</a>
      <a href="../ThesisPage/thesisView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book thesis-icon"></i> Thesis Projects </a>
      <a href="../CapstonePage/capstoneView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book capstone-icon"></i> Capstone Projects </a>
      <a href="../PHP/pending_reservations.php" onclick="showLoadingScreen()"><i class="fa-solid fa-bookmark"></i> Pending Reservations </a>
      <a href="#" id="mobile-logout-link">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
    
    <h1>THESIS PROJECTS</h1>

    <!-- Search Form -->
    <form method="post" action="" id="searchForm">
      <input type="text" name="search" id="searchInput" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by title, abstract, or author">
      <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <!-- Display the count of matching titles and abstracts centered with darkolivegreen color -->
    <?php if ($search !== ''): ?>
    <div class="count-display">
        <p class="count-text">
            <?php echo $titleCount; ?> title(s) <br> and <br> 
            <?php echo $abstractCount; ?> abstract(s) <br>
            Match = "<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
        </p>
    </div>
<?php endif; ?>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Control Number</th>
            <th>Course</th>
            <th>Title</th>
            <th>Abstract</th>
            <th>Year</th>
            <th>Author</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><span>ID</span> <?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span>Control Number</span> <?php echo str_pad(htmlspecialchars($row['control_number'], ENT_QUOTES, 'UTF-8'), 3, '0', STR_PAD_LEFT); ?></td>
            <td><span>Course</span> <?php echo htmlspecialchars($row['course'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span>Title</span> <?php echo highlightSearchTerm($row['title'], $search); ?></td>
            
            <!-- Abstract text with "Show Abstract" button below -->
            <td>
                <p id="abstract-<?php echo $row['id']; ?>" class="abstract-text" style="display:none;">
                    <?php echo highlightSearchTerm($row['abstract'], $search); ?>
                </p>
                <div class="button-group">
                    <button class="show-abstract-btn" onclick="toggleAbstract(<?php echo $row['id']; ?>, 'thesis')">SHOW ABSTRACT</button>
                    <button class="reserve-btn" onclick="reserveThesis(<?php echo $row['id']; ?>)">RESERVE</button>
                </div>
            </td>

            <td><span>Year</span> <?php echo htmlspecialchars($row['year'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span>Author</span> <?php echo htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
   <!-- Pagination Links -->
<div class="pagination" id="pagination-container">
  <?php if (!$showAllResults): ?>
    <?php if ($currentPage > 1): ?>
      <a href="?page=<?php echo $currentPage - 1; ?>">&laquo; Previous</a>
    <?php endif; ?>
    
    <?php 
    // Show only 5 page numbers at a time
    $startPage = max(1, $currentPage - 2); // Start two pages before current
    $endPage = min($totalPages, $startPage + 4); // End page, 5 numbers in total

    // Adjust startPage if near the last page
    if ($endPage - $startPage < 4 && $totalPages >= 5) {
        $startPage = max(1, $endPage - 4);
    }

    for ($i = $startPage; $i <= $endPage; $i++): ?>
      <a href="?page=<?php echo $i; ?>" class="<?php if ($i == $currentPage) echo 'active'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    
    <?php if ($currentPage < $totalPages): ?>
      <a href="?page=<?php echo $currentPage + 1; ?>">Next &raquo;</a>
    <?php endif; ?>
    
        <!-- Show "Show All" Button -->
        <?php if ($totalPages > 5): ?>
      <button id="show-all-btn" onclick="showAllPages()">Show All</button>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="footer-container">
        <p>&copy; <?php echo date("Y"); ?> Web-based Library System. All rights reserved.</p>
    </div>
</footer>

    <!-- Scroll to Top Button -->
    <button onclick="toTop()" id="topBtn"><i class="fa-solid fa-arrow-up"></i></button>

    <!-- Reservation Modals -->
    <div id="reservation-confirm-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Reservation</h2>
            <p id="reservation-confirm-text">Are you sure you want to reserve this thesis?</p>
            <div class="modal-buttons">
                <button id="confirm-reservation" class="confirm-btn">Yes, Reserve</button>
                <button id="cancel-reservation" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <div id="reservation-response-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="response-title">Reservation Status</h2>
            <p id="response-message"></p>
            <div class="modal-buttons">
                <button id="response-ok" class="confirm-btn">OK</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="../JavaScripts/loadingScreen.js"></script>
    <script src="../JavaScripts/th_cp_LogoutHamburger.js"></script>
    <script src="../JavaScripts/scrollUpBtn.js"></script>
    <script src="../JavaScripts/reserveThesis.js"></script>
    <script src="../JavaScripts/abstractModal.js"></script>
    <!-- Show All Pages Button Script -->
<script>

    // Logout Confirmation Modal Logic
document.getElementById('logout-link').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default action of link
    document.getElementById('logout-modal').style.display = 'block'; // Show the modal
});

document.getElementById('confirm-logout').addEventListener('click', function() {
    window.location.href = "?action=logout"; // Redirect to logout when confirmed
});

document.getElementById('cancel-logout').addEventListener('click', function() {
    document.getElementById('logout-modal').style.display = 'none'; // Hide the modal
});

// Optional: Close modal when clicking the 'X' icon
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('logout-modal').style.display = 'none';
});

  // Function to show all pages
  function showAllPages() {
    const paginationContainer = document.getElementById('pagination-container');
    paginationContainer.innerHTML = '';

    const totalPages = <?php echo $totalPages; ?>;
    const currentPage = <?php echo $currentPage; ?>;

    // Add all pages
    for (let i = 1; i <= totalPages; i++) {
      const pageLink = document.createElement('a');
      pageLink.href = '?page=' + i;
      pageLink.textContent = i;
      if (i == currentPage) {
        pageLink.classList.add('active');
      }
      paginationContainer.appendChild(pageLink);
    }

    // Add Previous and Next buttons
    if (currentPage > 1) {
      const prevLink = document.createElement('a');
      prevLink.href = '?page=' + (currentPage - 1);
      prevLink.innerHTML = '&laquo; Previous';
      paginationContainer.insertBefore(prevLink, paginationContainer.firstChild);
    }

    if (currentPage < totalPages) {
      const nextLink = document.createElement('a');
      nextLink.href = '?page=' + (currentPage + 1);
      nextLink.innerHTML = 'Next &raquo;';
      paginationContainer.appendChild(nextLink);
    }

    // Add "Show Less" button
    const showLessBtn = document.createElement('button');
    showLessBtn.id = 'show-less-btn';
    showLessBtn.textContent = 'Show Less';
    showLessBtn.onclick = showLimitedPages;
    paginationContainer.appendChild(showLessBtn);
  }

  // Function to revert back to showing only 5 pages
  function showLimitedPages() {
    const paginationContainer = document.getElementById('pagination-container');
    paginationContainer.innerHTML = '';

    const totalPages = <?php echo $totalPages; ?>;
    const currentPage = <?php echo $currentPage; ?>;

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, startPage + 4);

    // Adjust startPage if near the last page
    const adjustedStartPage = (endPage - startPage < 4 && totalPages >= 5) ? Math.max(1, endPage - 4) : startPage;

    // Display limited 5 pages again
    for (let i = adjustedStartPage; i <= endPage; i++) {
      const pageLink = document.createElement('a');
      pageLink.href = '?page=' + i;
      pageLink.textContent = i;
      if (i == currentPage) {
        pageLink.classList.add('active');
      }
      paginationContainer.appendChild(pageLink);
    }

    // Add Previous and Next buttons
    if (currentPage > 1) {
      const prevLink = document.createElement('a');
      prevLink.href = '?page=' + (currentPage - 1);
      prevLink.innerHTML = '&laquo; Previous';
      paginationContainer.insertBefore(prevLink, paginationContainer.firstChild);
    }

    if (currentPage < totalPages) {
      const nextLink = document.createElement('a');
      nextLink.href = '?page=' + (currentPage + 1);
      nextLink.innerHTML = 'Next &raquo;';
      paginationContainer.appendChild(nextLink);
    }

    // Add "Show All" button back
    const showAllBtn = document.createElement('button');
    showAllBtn.id = 'show-all-btn';
    showAllBtn.textContent = 'Show All';
    showAllBtn.onclick = showAllPages;
    paginationContainer.appendChild(showAllBtn);
  }
</script>
<script>
    $(document).ready(function() {
        $("#searchInput").autocomplete({
            source: "get_thesisbooks_suggestions.php",
            minLength: 2,
            select: function(event, ui) {
                $("#searchInput").val(ui.item.value);
                $("#searchForm").submit();
            }
        });
    });
</script>
</body>
</html>
