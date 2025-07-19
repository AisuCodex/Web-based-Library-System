<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Select User Type | Capstone/Thesis Management System</title>
  <link rel="stylesheet" href="../CSS/login.css">
  <link rel="stylesheet" type="text/css" href="../CSS/Loading_screen.css"> <!-- Link to the loading screen CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .register {
      text-decoration: none;
      text-align: center;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .school-logo {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 10px;
    }
    
    .school-logo .logo-container {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
    }
    
    .school-logo img {
      margin: 0 5px;
    }
  </style>
</head>
<body>
  <!-- Loading screen element -->
  <div class="loading-overlay" id="loading-screen">
    <div class="loader"></div>
  </div>

  <div class="secondContainer">
    <div class="child">
      <div class="backBtn-div">
        <a class="backBtn" href="../index.php" onclick="showLoadingScreen()" title="Return to home page">X</a>
      </div>
      
      <h2>Select User Type</h2>
      
      <div class="choice">
        <a class="register" href="../PHP/loginPage.php" onclick="showLoadingScreen()">STUDENT</a>
        <a class="register" href="../PHP/adminLogin.php" onclick="showLoadingScreen()">ADMIN</a>
        <a class="register" href="../PHP/assistantLogin.php" onclick="showLoadingScreen()">ASSISTANT</a>
      </div>
      
      <!-- School logo at the bottom -->
      <div class="school-logo">
        <div class="logo-container">
          <img src="../img/greenLogo.png" alt="BulSU Logo" width="60">
          <img src="../img/orangeLogo.png" alt="Capstone Logo" width="60">
        </div>
        <p>Bulacan State University</p>
      </div>
    </div>
  </div> 

  <script src="../JavaScripts/loadingScreen.js"></script>
</body>
</html>
