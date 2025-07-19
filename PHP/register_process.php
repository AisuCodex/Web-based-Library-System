<?php
// Include the database connection
include("../database/Register_database.php");

// Initialize error message and success message variables
$errorMessage = '';
$successMessage = '';
$generatedCode = '';

// Function to generate a 6-character alphanumeric code
function generateCode($length = 6) {
    return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize email, password, and confirm password inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $confirmPassword = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
    $domain = "bulsu.edu.ph";
    $domainLength = strlen($domain);

    // Check if image is uploaded
    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errorMessage = 'Profile image is required.';
    } else {
        // Handle image upload
        $profileImage = '';
        if($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                $errorMessage = 'Only JPG, PNG and GIF images are allowed.';
            } elseif ($_FILES['profile_image']['size'] > $maxSize) {
                $errorMessage = 'File size must be less than 5MB.';
            } else {
                $uploadDir = '../uploads/profile_images/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['profile_image']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                    $profileImage = $fileName;
                } else {
                    $errorMessage = 'Failed to upload image.';
                }
            }
        }
    }

    // Validate email
    if (substr($email, -$domainLength) !== $domain) {
        $errorMessage = 'You must use @bulsu.edu.ph';
    } 
    // Validate password length
    elseif (strlen($password) < 4) {
        $errorMessage = 'Password must be at least 4 characters long.'; 
    } 
    // Validate password format
    elseif (!preg_match("/^[a-zA-Z0-9#$%^&*()_+={}\[\]:%;,.?]+$/", $password)) {
        $errorMessage = 'Invalid password format, password must not contain special characters.';
    } 
    // Validate confirm password
    elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } 
    else {
        // Check if the email already exists in verification table
        $check_verification = $conn->prepare("SELECT id FROM verification WHERE email = ?");
        if ($check_verification === false) {
            $errorMessage = 'Database error: ' . $conn->error;
        } else {
            $check_verification->bind_param("s", $email);
            $check_verification->execute();
            $check_verification->store_result();
            
            // Check in student_acc table
            $exists_in_verification = $check_verification->num_rows > 0;
            $check_verification->close();
            
            $check_student = $conn->prepare("SELECT id FROM student_acc WHERE email = ?");
            if ($check_student === false) {
                $errorMessage = 'Database error: ' . $conn->error;
            } else {
                $check_student->bind_param("s", $email);
                $check_student->execute();
                $check_student->store_result();
                
                if ($check_student->num_rows > 0) {
                    $errorMessage = 'This BULSU email is already registered in the system.';
                    $check_student->close();
                } else {
                    $check_student->close();
                    // Generate the 6-character code
                    $generatedCode = generateCode();

                    // Hash the password
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert user into the database
                    $stmt = $conn->prepare("INSERT INTO verification (email, password, code, profile_image) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $email, $hash, $generatedCode, $profileImage);

                    if ($stmt->execute()) {
                        // Set success message
                        $successMessage = 'Registration successful!';
                    } else {
                        $errorMessage = "Error: " . $stmt->error;
                    }
                }
            }
        }
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
  <title>Register | Capstone/Thesis Management System</title>
  <link rel="stylesheet" href="../CSS/register.css">
  <link rel="stylesheet" type="text/css" href="../CSS/Loading_screen.css"> <!-- Link to the loading screen CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <!-- Loading screen element -->
  <div class="loading-overlay" id="loading-screen">
    <div class="loader"></div>
  </div>

  <div class="secondContainer">
    <div class="child">
      <div class="backBtn-div">
        <a class="backBtn" href="../PHP/adminOrStudent.php" onclick="showLoadingScreen()" title="Return to selection page">X</a>
      </div>
      
      <!-- Registration form only displays if there is no success message -->
      <?php if (!$successMessage): ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="showLoadingScreen()" enctype="multipart/form-data">
          <h2>Create Account</h2>
          
          <div class="inputBox">
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES); ?>">
            <span id="email-text">Email</span>
            <i id="email-focus"></i>
            <i class="fa-regular fa-envelope input-icon"></i>
          </div>
          
          <div class="inputBox">
            <input type="password" id="password" name="password" required>
            <span id="password-text">Password</span>
            <i id="password-focus"></i>
            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)">👁️</button>
            <i class="fa-solid fa-lock input-icon"></i>
          </div>
          
          <div class="inputBox">
            <input type="password" id="confirm_password" name="confirm_password" required>
            <span id="confirm-password-text">Confirm Password</span>
            <i id="confirm-password-focus"></i>
            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)">👁️</button>
            <i class="fa-solid fa-lock input-icon"></i>
          </div>
          
          <div class="inputBox file-input-container">
            <input type="file" id="profile_image" name="profile_image" accept="image/*" required style="display: none;">
            <label for="profile_image" class="file-label">
              <i class="fa-solid fa-id-card"></i> Insert Bulsu ID Image
            </label>
            <span id="file-name"></span>
          </div>
          
          <div class="choice">
            <input class="register" type="submit" name="submit" value="Register">
            <a class="already" href="../PHP/loginPage.php" onclick="showLoadingScreen()">Already have an account?</a>
          </div>

          <!-- Show success or error message -->
          <?php if ($errorMessage): ?>
            <div class="error-message">
              <p><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></p>
            </div>
          <?php endif; ?>
        </form>
      <?php else: ?>
        <!-- Display code and success message after registration -->
        <div class="success-message">
          <i class="fa-solid fa-circle-check success-icon"></i>
          <p><?php echo htmlspecialchars($successMessage, ENT_QUOTES); ?></p>
          <p>Your reset code is: <strong><?php echo htmlspecialchars($generatedCode, ENT_QUOTES); ?></strong></p>
          <p>Please save this code for future password reset requests.</p>
        </div>
        <div class="login-btn">
          <a href="../PHP/loginPage.php" class="continue-to-login" onclick="showLoadingScreen()">Continue to Login</a>
        </div>
      <?php endif; ?>
      
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
      const inputs = document.querySelectorAll('.inputBox input:not([type="file"])');
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
    });

    function togglePasswordVisibility(fieldId, toggleButton) {
      const passwordField = document.getElementById(fieldId);
      
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleButton.textContent = '✅'; // Change icon to indicate "hide"
      } else {
        passwordField.type = 'password';
        toggleButton.textContent = '👁️'; // Change icon back to "show"
      }
    }

    // Add file input handler
    document.getElementById('profile_image').addEventListener('change', function(e) {
      const fileName = e.target.files[0]?.name;
      document.getElementById('file-name').textContent = fileName || '';
    });

    function showLoadingScreen() {
      var loadingScreen = document.getElementById('loading-screen');
      loadingScreen.style.display = 'flex';
    }
  </script>
</body>
</html>
