<?php
include 'adminAuth.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="../CSS/loading_screen.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
      <div class="admin-page-text">
        <p>Admin Page</p>
      </div>
      <div class="nav-links">
        <a href="changePassword.php" onclick="showLoadingScreen()">Change Pass <i class="fa-solid fa-key"></i></a>
        <a href="ManageStudAcc.php" onclick="showLoadingScreen()">Student Accounts <i class="fa-solid fa-pen-to-square"></i></a>
        <a href="createAdmin.php" onclick="showLoadingScreen()">Assistant Accounts <i class="fa-solid fa-pen-to-square"></i></a>
        <a href="manageReservations.php" onclick="showLoadingScreen()">Book Reservations <i class="fa-solid fa-book-bookmark"></i></a>
        <a href="pending_accounts.php" onclick="showLoadingScreen()">Pending Accounts <i class="fa-solid fa-user-clock"></i></a>
        <a href="user_logs.php" onclick="showLoadingScreen()">User Logs <i class="fa-solid fa-list-check"></i></a>
        <a href="book_views.php" onclick="showLoadingScreen()">Book Views <i class="fa-solid fa-chart-line"></i></a>
        <a href="createAdmin.php" onclick="showLoadingScreen()">Create Assistant <i class="fa-solid fa-user-plus"></i></a>
        <a href="#" id="logout-link">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
      </div>
    </div>

<!-- Side Menu for Mobile -->
<nav class="side-menu">
  <a href="changePassword.php" onclick="showLoadingScreen()">Change Password <i class="fa-solid fa-key"></i></a>
  <a href="ManageStudAcc.php" onclick="showLoadingScreen()">Student Accounts <i class="fa-solid fa-pen-to-square"></i></a>
  <a href="manageAdmin.php" onclick="showLoadingScreen()">Assistant Accounts <i class="fa-solid fa-pen-to-square"></i></a>
  <a href="manageReservations.php" onclick="showLoadingScreen()">Book Reservations <i class="fa-solid fa-book-bookmark"></i></a>
  <a href="pending_accounts.php" onclick="showLoadingScreen()">Pending Accounts <i class="fa-solid fa-user-clock"></i></a>
  <a href="user_logs.php" onclick="showLoadingScreen()">User Logs <i class="fa-solid fa-list-check"></i></a>
  <a href="book_views.php" onclick="showLoadingScreen()">Book Views <i class="fa-solid fa-chart-line"></i></a>
  <a href="createAdmin.php" onclick="showLoadingScreen()">Create Assistant <i class="fa-solid fa-user-plus"></i></a>

  <a href="#" id="logout-link-mobile">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
</nav>

    <!-- main content -->
    <div class="contain">
      <section id="capstone">
          <a class="box-link" onclick="showLoadingScreen()" href="../Capstone_Database/capstoneCrud.php">
              <div class="box">
                  <h2>Capstone Projects</h2>
                  <p>Manage and view <br> capstone projects here.</p>
              </div>
          </a>
      </section>

      <section id="thesis">
          <a class="box-link" onclick="showLoadingScreen()" href="../Thesis_Database/ThesisCrud.php">
              <div class="box">
                  <h2>Thesis Papers</h2>
                  <p>Manage and view <br> thesis papers here.</p>
              </div>
          </a>
      </section>
  </div>
</main>



  

  <!-- Logout Confirmation Modal -->
  <div id="logout-modal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Logout Confirmation</h2>
      <p>Are you sure you want to log out?</p>
      <button id="confirm-logout">Yes, Log Out</button>
      <button id="cancel-logout" class="cancel">Cancel</button>
    </div>
  </div>

  <script>
    function showLoadingScreen() {
      var loadingScreen = document.getElementById('loading-screen');
      loadingScreen.style.display = 'flex';
    }

    function toggleMenu() {
      var sideMenu = document.querySelector('.side-menu');
      var hamburgerMenu = document.querySelector('.hamburger-menu');
      sideMenu.classList.toggle('open');
      hamburgerMenu.classList.toggle('active'); // Toggle active class for animation
    }

    // Get the modal and buttons
    var modal = document.getElementById('logout-modal');
    var closeBtn = document.querySelector('.modal .close');
    var confirmLogoutBtn = document.getElementById('confirm-logout');
    var cancelLogoutBtn = document.getElementById('cancel-logout');

    document.getElementById('logout-link').addEventListener('click', function(event) {
      event.preventDefault(); // Prevent the default link behavior
      modal.style.display = 'block'; // Show the modal
    });

    closeBtn.onclick = function() {
      modal.style.display = 'none'; // Close the modal
    }

    cancelLogoutBtn.onclick = function() {
      modal.style.display = 'none'; // Close the modal
    }

    confirmLogoutBtn.onclick = function() {
      window.location.href = 'adminPage.php?logout=true'; // Redirect to logout URL
    }
    // Get the modal and buttons
var modal = document.getElementById('logout-modal');
var closeBtn = document.querySelector('.modal .close');
var confirmLogoutBtn = document.getElementById('confirm-logout');
var cancelLogoutBtn = document.getElementById('cancel-logout');

// Attach event listeners to both logout links
var logoutLinks = [document.getElementById('logout-link'), document.getElementById('logout-link-mobile')];

logoutLinks.forEach(function(logoutLink) {
  logoutLink.addEventListener('click', function(event) {
    event.preventDefault(); // Prevent the default link behavior
    modal.style.display = 'block'; // Show the modal
  });
});

closeBtn.onclick = function() {
  modal.style.display = 'none'; // Close the modal
}

cancelLogoutBtn.onclick = function() {
  modal.style.display = 'none'; // Close the modal
}

confirmLogoutBtn.onclick = function() {
  window.location.href = 'adminPage.php?logout=true'; // Redirect to logout URL
}

  </script>
</body>
</html>
