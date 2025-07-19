<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Hag AI - Your AI Assistant" />
    <meta name="keywords" content="AI, Assistant, Hag AI" />
    <meta name="author" content="AisuCodex" />
    <title>Hag AI</title>
    <link rel="stylesheet" href="../codex-ai/c/ai-navbar.css">
    <link rel="stylesheet" href="../CSS/icon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
      rel="icon"
      href="../img/unnamed.png"
      sizes="32x32"
      type="image/png"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/gh/FortAwesome/Font-Awesome@6.x/css/all.min.css"
      crossorigin="anonymous"
      media="all"
    />
    <script
      src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"
      defer
      crossorigin="anonymous"
    ></script>
    <script src="../JavaScripts/logoutFunction.js"></script>
    <script src="../JavaScripts/hamburgerMenu.js"></script>
    <style>
      .iframe-container {
        position: fixed;
        top: 60px; /* Height of the navbar */
        left: 0;
        width: 100%;
        height: calc(100% - 60px); /* Full height minus navbar height */
        z-index: 10;
      }
      
      .iframe-container iframe {
        width: 100%;
        height: 100%;
        border: none;
      }
    </style>
  </head>
  <body>
    <div class="navbar">
      <div class="hamburger-menu" onclick="toggleMenu()">
        <div class="bar bar1"></div>
        <div class="bar bar2"></div>
        <div class="bar bar3"></div>
      </div>
      <div class="nav-links">
        <a href="../PHP/homePage.php" onclick="showLoadingScreen()"><i class="fa-solid fa-house home"></i> Home </a>
        <a href="../PHP/guidelines.php" onclick="showLoadingScreen()"><i class="fa-solid fa-scroll guidelines-icon"></i> Guidelines</a>
        <a href="../ThesisPage/thesisView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book thesis-icon"></i> Thesis Projects </a>
        <a href="../CapstonePage/capstoneView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book capstone-icon"></i> Capstone Projects </a>
        <a href="../PHP/pending_reservations.php" onclick="showLoadingScreen()"><i class="fa-solid fa-bookmark"></i> Pending Reservations </a>
      </div>
      <div>

      </div>
    </div>

    <!-- Side Menu for Mobile -->
    <nav class="side-menu">
      <a href="../PHP/homePage.php" onclick="showLoadingScreen()"><i class="fa-solid fa-house"></i> Home </a>
      <a href="../PHP/guidelines.php" onclick="showLoadingScreen()"><i class="fa-solid fa-scroll guidelines-icon"></i> Guidelines</a>
      <a href="../ThesisPage/thesisView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book thesis-icon"></i> Thesis Projects </a>
      <a href="../CapstonePage/capstoneView.php" onclick="showLoadingScreen()"><i class="fa-solid fa-book capstone-icon"></i> Capstone Projects </a>
      <a href="../PHP/pending_reservations.php" onclick="showLoadingScreen()"><i class="fa-solid fa-bookmark"></i> Pending Reservations </a>
    </nav>

    <div class="iframe-container">
      <iframe src="https://hag-ai-prochat.onrender.com/">
        Your browser does not support iframes.
      </iframe>
    </div>
    
    <script>
      function showLoadingScreen() {
        var loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen) {
          loadingScreen.style.display = 'flex';
        }
      }
      
      function toggleMenu() {
        var sideMenu = document.querySelector('.side-menu');
        var hamburgerMenu = document.querySelector('.hamburger-menu');
        sideMenu.classList.toggle('open');
        hamburgerMenu.classList.toggle('active');
      }
    </script>
  </body>
</html>