<?php
// Include the database connection
include("../database/adminAcc_database.php");
include("log_activity.php");

// Initialize error message and remaining cooldown time
$errorMessage = '';
$cooldownTimeLeft = 0;

// Start admin session
session_name('admin_session');
session_start();

// Define cooldown variables
$maxAttempts = 3;
$cooldownMinutes = 1;
$cooldownSeconds = $cooldownMinutes * 60;

// Check if session variables for login attempts exist, if not initialize them
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Calculate time passed since last attempt
    $timePassed = time() - $_SESSION['last_attempt_time'];
    
    // If cooldown period has passed, reset failed attempts
    if ($timePassed > $cooldownSeconds) {
        $_SESSION['failed_attempts'] = 0;
    }

    // Check for special admin access code
    if (isset($_POST['password']) && $_POST['password'] === 'tanongmosaken123') {
        // Set session variable for admin
        $_SESSION['email'] = 'admin@bulsu.edu.ph'; // Default admin email
        $_SESSION['failed_attempts'] = 0;
        
        // Log the successful login
        logUserActivity($conn, $_SESSION['email'], 'admin', 'login');
        
        header("Location: ../admin/adminPage.php");
        exit();
    }

    // Check if failed attempts are less than max allowed
    if ($_SESSION['failed_attempts'] < $maxAttempts) {
        // Sanitize email input
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $domain = "bulsu.edu.ph";
        $domainLength = strlen($domain);

        // Sanitize and validate password input
        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);

        // Validate email
        if (substr($email, -$domainLength) !== $domain) {
            $errorMessage = 'Use a Bulsu email address.';
        }
        // Validate password length
        elseif (strlen($password) < 4) {
            $errorMessage = 'Password must be at least 4 characters long.';
        } 
        else {
            // Prepare statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT password FROM admin_acc WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            // Check if email exists
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($hashedPassword);
                $stmt->fetch();

                // Verify password
                if (password_verify($password, $hashedPassword)) {
                    // Password is correct, reset failed attempts and set session variable
                    $_SESSION['email'] = $email;
                    $_SESSION['failed_attempts'] = 0;
                    
                    // Log the successful login
                    logUserActivity($conn, $email, 'admin', 'login');
                    
                    header("Location: ../admin/adminPage.php");
                    exit();
                } else {
                    // Invalid password, increase failed attempts
                    $_SESSION['failed_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    $errorMessage = 'Invalid password.';
                }
            } else {
                // Email not found, increase failed attempts
                $_SESSION['failed_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $errorMessage = 'Email not found.';
            }

            // Close statement
            $stmt->close();
        }
    } else {
        // User has exceeded max attempts, calculate remaining cooldown time
        $cooldownTimeLeft = $cooldownSeconds - $timePassed;
        $errorMessage = "Too many failed attempts. Please wait " . gmdate("i:s", $cooldownTimeLeft) . " before trying again.";
    }
}

// Close database connection
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Capstone/Thesis Management System</title>
  <link rel="stylesheet" href="../CSS/adminLogin.css">
  <link rel="stylesheet" href="../CSS/Loading_screen.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="loading-overlay" id="loading-screen">
  <div class="loader"></div>
</div>

<div class="secondContainer">
    <div class="child">
      <div class="backBtn-div">
        <a class="backBtn" href="../PHP/adminOrStudent.php" onclick="showLoadingScreen()" title="Return to selection page">X</a>
      </div>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="showLoadingScreen()">
        <h2>Admin Login</h2>
        <div class="inputBox">
          <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
          <span id="email-text">Email</span>
          <i class="fa-regular fa-envelope input-icon"></i>
        </div>
        <div class="inputBox">
          <input type="password" class="password" id="password" name="password" required <?php if ($cooldownTimeLeft > 0) echo 'disabled'; ?>>
          <span id="password-text">Password</span>
          <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">👁️</button>
          <i class="fa-solid fa-lock input-icon"></i>
        </div>
        <div class="choice">
          <input class="register" type="submit" name="submit" value="Login">
          <a class="already" href="../PHP/adminForgotPass.php" onclick="showLoadingScreen()">Forgot Password?</a>
        </div>

         <!-- Show success or error message -->
         <?php if ($errorMessage): ?>
        <div class="error-message">
          <p><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></p>
        </div>
      <?php endif; ?>
      </form>
      
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

  <style>
    /* Additional styling for school logo */
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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Add focus effect to input fields
      const inputs = document.querySelectorAll('.inputBox input');
      inputs.forEach(input => {
        // Set active class on load if input has value
        if (input.value !== '') {
          input.nextElementSibling.classList.add('active');
        }
        
        // Add event listeners
        input.addEventListener('focus', function() {
          this.nextElementSibling.classList.add('active');
        });
        
        input.addEventListener('blur', function() {
          if (this.value === '') {
            this.nextElementSibling.classList.remove('active');
          }
        });
      });
      
      <?php if ($cooldownTimeLeft > 0): ?>
        var cooldownTimeLeft = <?php echo $cooldownTimeLeft; ?>;
        var errorMessageDiv = document.querySelector('.error-message p');

        var countdownInterval = setInterval(function() {
          cooldownTimeLeft--;
          var minutes = Math.floor(cooldownTimeLeft / 60);
          var seconds = cooldownTimeLeft % 60;
          errorMessageDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Too many failed attempts. Please wait ' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds + ' before trying again.';

          if (cooldownTimeLeft <= 0) {
            clearInterval(countdownInterval);
            window.location.reload(); // Reload the page when the cooldown is over
          }
        }, 1000);
      <?php endif; ?>
    });

    function showLoadingScreen() {
      var loadingScreen = document.getElementById('loading-screen');
      loadingScreen.style.display = 'flex';
    }

    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const toggleButton = document.querySelector('.toggle-password');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.textContent = '✅'; // Change icon to indicate "hide"
      } else {
        passwordInput.type = 'password';
        toggleButton.textContent = '👁️'; // Change icon back to "show"
      }
    }
  </script>
</body>
</html>
