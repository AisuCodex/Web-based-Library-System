<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capstone/Thesis Management System</title>
    <link rel="stylesheet" href="./CSS/StarT.css">
    <link rel="stylesheet" href="./CSS/Loading_screen.css"> <!-- Link to the new CSS file -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Loading screen element -->
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <header class="headerContent" id="headerContent">
        <div class="header-content">
            <img src="./img/orangeLogo.png" alt="Logo 1" class="logo">
            <h2><b>BULACAN STATE UNIVERSITY <br> HAGONOY CAMPUS</b></h2>
            <img src="./img/greenLogo.png" alt="Logo 2" class="logo">
        </div>
    </header>
    <main class="main-content">
        <div class="caption-container">
            <h1 class="caption">Welcome to <br> Capstone/Thesis <br>Management System</h1>
            <div class="start-container">
              <a class="start_btn" href="PHP/adminOrStudent.php" onclick="showLoadingScreen()">Get Started</a>
            </div>
        </div>  
    </main> 

    <script src="./JavaScripts/loadingScreen.js"></script>
</body>
</html>
