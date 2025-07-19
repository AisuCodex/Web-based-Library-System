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
  <link rel="stylesheet" href="../CSS/Homepage_slider.css">
  <link rel="stylesheet" href="../CSS/loading_screen.css">
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

    <div class="carousel">
      <div class="list">
        <div class="item" style="background-image: url(../img/side.jpg);">
          <div class="content">
            <div class="title">Vision</div>
            <div class="name">Bulacan State University is a progressive knowledge-generating institution globally recognized for excellent instruction, pioneering research, and responsive community engagements</div>
            <div class="btn"></div>
          </div>
        </div>

        <div class="item" style="background-image: url(../img/stackbook.jpg);">
          <div class="content">
            <div class="title">Mission</div>
            <div class="name">Bulacan State University exists to produce highly competent, ethical and service-oriented professionals that contribute to the sustainable socio-economic growth and development of the nation</div>
            <div class="btn"></div>
          </div>
        </div>

        <div class="item" style="background-image: url(../img/wide.jpg);">
          <div class="content">
            <div class="title">Vision</div>
            <div class="name">Bulacan State University is a progressive knowledge-generating institution globally recognized for excellent instruction, pioneering research, and responsive community engagements</div>
            <div class="btn"></div>
          </div>
        </div>

        <div class="item" style="background-image: url(../img//much_wider.jpg);">
          <div class="content">
            <div class="title">Mission</div>
            <div class="name">Bulacan State University exists to produce highly competent, ethical and service-oriented professionals that contribute to the sustainable socio-economic growth and development of the nation</div>
            <div class="btn"></div>
          </div>
        </div>
      </div>

      <!--next prev button-->
      <div class="arrows">
        <button class="prev"><</button>
        <button class="next">></button>
      </div>

      <!-- time running -->
      <div class="timeRunning"></div>
    </div>
  </div>

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

  <script src="../JavaScripts/loadingScreen.js"></script>
  <script src="../JavaScripts/homePageAuth.js"></script>
  <script src="../JavaScripts/carousel.js"></script>
  <script src="../JavaScripts/homePage_modal_hamburger.js"></script>

</body>
</html>
