<?php
 include 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hagonoy Web-based Library System</title>
  <link rel="stylesheet" href="../CSS/HomePage.css">
  <link rel="stylesheet" href="../CSS/loading_screen.css">
  <link rel="stylesheet" href="../CSS/guideline.css">
  <link rel="stylesheet" href="../CSS/icon.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="../JavaScripts/mobileMenu.js" defer></script>
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
        <a href="../PHP/homePage.php" onclick="showLoadingScreen()"><i class="fa-solid fa-house"></i> Home </a>
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

    <div class="container">
        <section class="header">
            <h1>Website Guidelines</h1>
        </section>

        <section class="description">
            <p>
                This platform is designed to help students manage, search, and access capstone and thesis projects efficiently. 
                If you're a student, professor, or scholar, the platform has user-friendly tools to assist you in exploring 
                numerous scholarly works effortlessly. The system provides simple access to previous research projects and also 
                provides advanced tools for filtering and suggesting relevant works, which speeds up and streamlines the 
                research process.
            </p>
        </section>

        <section class="features">
            <div class="feature">
                <p><strong style="color: darkolivegreen;">Names of RRL Sites </strong> <b> International </b> <br> Google Scholar <br> Base <br> JSTOR</p>
                <p> <br> <b> Local </b> <br> Philippine E-Journal  <br> University of the Philippines Digital Library <br> De La Salle University Library </p>
            </div>

            <div class="feature">
                <img src="../img/unnamed.png" alt="AI Icon">
                <p><strong>The system includes an AI feature named hagAI</strong> that assists you throughout your research 
                   journey. hagAI helps refine your search queries, recommend relevant projects, and suggest resources tailored 
                   to your research needs.
                </p>
            </div>

            <div class="feature">
              <div class="thesis-capstone-icon-container">
                <div class="capstone-container">
                   <i class="fa-solid fa-book capstone-icon capstoneGuide"></i>
                </div>
              </div>
                <p><strong>Instantly view previous capstone and thesis projects</strong>including detailed abstracts, with just a single click.</p>
            </div>
        </section>
    </div>

    <!-- Footer -->
<footer class="footer">
    <div class="footer-container">
        <p>&copy; <?php echo date("Y"); ?> Web-based Library System. All rights reserved.</p>
    </div>
</footer>

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

    // Logout Modal Functionality
    var modal = document.getElementById('logout-modal');
    var logoutLink = document.getElementById('logout-link');
    var mobileLogoutLink = document.getElementById('mobile-logout-link');
    var closeBtn = document.querySelector('.modal .close');
    var confirmLogoutBtn = document.getElementById('confirm-logout');
    var cancelLogoutBtn = document.getElementById('cancel-logout');

    // Show modal when either logout link is clicked
    [logoutLink, mobileLogoutLink].forEach(function(link) {
      if (link) {
        link.addEventListener('click', function(event) {
          event.preventDefault();
          modal.style.display = 'block';
        });
      }
    });

    // Close modal functions
    closeBtn.onclick = function() {
      modal.style.display = 'none';
    }

    cancelLogoutBtn.onclick = function() {
      modal.style.display = 'none';
    }

    // Close modal if clicking outside
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }

    confirmLogoutBtn.onclick = function() {
      var loadingIndicator = document.createElement('div');
      loadingIndicator.className = 'loading-indicator';
      loadingIndicator.textContent = 'Logging out...';
      document.body.appendChild(loadingIndicator);
      
      window.location.href = 'guidelines.php?action=logout';
    }
  </script>
</body>
</html>
