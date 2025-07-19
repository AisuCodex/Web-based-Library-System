<?php
// Include the database connection
include("../database/Register_database.php");

// Start session
session_start();

// Initialize variables
$errorMessage = '';
$successMessage = '';
$showResetPasswordForm = false;

// Check if the database connection is established
if ($conn === false) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['code_submit'])) {
        // Retrieve and sanitize the input code
        $inputCode = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);

        // Prepare the query to check if the code exists in the database
        $stmt = $conn->prepare("SELECT * FROM student_acc WHERE code = ?");
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("s", $inputCode);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the code exists
        if ($result->num_rows === 1) {
            // Code exists, display reset password form
            $_SESSION['reset_permission'] = true;
            $_SESSION['reset_code'] = $inputCode;
            $showResetPasswordForm = true;
            $successMessage = 'Code verified. Please reset your password below.';
        } else {
            // Code does not exist
            $errorMessage = 'Your code does not exist.';
        }

        // Close statement
        $stmt->close();
    } elseif (isset($_POST['password_submit']) && isset($_SESSION['reset_permission']) && $_SESSION['reset_permission'] === true) {
        // Handle reset password form submission
        $newPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        // Check if password contains special characters
        if (preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
            $errorMessage = 'Password should not contain special characters.';
        } elseif (strlen($newPassword) < 4) {
            // Check if password is less than 4 characters
            $errorMessage = 'Password must be at least 4 characters long.';
        } else {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password in the database
            $stmt = $conn->prepare("UPDATE student_acc SET password = ? WHERE code = ?");
            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }
            
            $stmt->bind_param("ss", $hashedPassword, $_SESSION['reset_code']);

            if ($stmt->execute()) {
                $successMessage = 'Password successfully reset.';
                // Clear reset permission
                unset($_SESSION['reset_permission']);
                unset($_SESSION['reset_code']);
            } else {
                $errorMessage = 'Failed to reset password. Please try again later.';
            }

            // Close statement
            $stmt->close();
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
  <title>Forgot Password | Capstone/Thesis Management System</title>
  <link rel="stylesheet" href="../CSS/forgotPass.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="forgot-password-container">
    <!-- Close button at the top right corner -->
    <button class="close-btn" onclick="redirectToLogin()" title="Back to login page">X</button>

    <h2>Reset Password</h2>
    <?php if ($showResetPasswordForm) { ?>
        <!-- Reset Password Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="inputBox">
                <input type="password" id="password" name="password" required placeholder="Enter new password">
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">👁️</button>
                <i class="fa-solid fa-lock input-icon"></i>
            </div>
            <button type="submit" name="password_submit" class="btn">Reset Password</button>
            <?php if (!empty($successMessage)) { ?>
                <p class="success"><i class="fa-solid fa-circle-check success-icon"></i> <?php echo $successMessage; ?></p>
            <?php } ?>
            <?php if (!empty($errorMessage)) { ?>
                <p class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></p>
            <?php } ?>
        </form>
    <?php } else { ?>
        <!-- Code Verification Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="inputBox">
                <input type="text" name="code" required placeholder="Enter your code">
                <i class="fa-solid fa-key input-icon"></i>
            </div>
            <button type="submit" name="code_submit" class="btn">Submit Code</button>
            <?php if (!empty($errorMessage)) { ?>
                <p class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></p>
            <?php } ?>
            <?php if (!empty($successMessage)) { ?>
                <p class="success"><i class="fa-solid fa-circle-check success-icon"></i> <?php echo htmlspecialchars($successMessage, ENT_QUOTES); ?></p>
            <?php } ?>
        </form>
    <?php } ?>

    <!-- School logo at the bottom -->
    <div class="school-logo">
        <div class="logo-container">
          <img src="../img/greenLogo.png" alt="BulSU Logo" width="60">
          <img src="../img/orangeLogo.png" alt="Capstone Logo" width="60">
        </div>
        <p>Bulacan State University</p>
    </div>
</div>

<!-- Success Modal -->
<?php if (!empty($successMessage) && isset($_POST['password_submit'])) { ?>
    <div id="successModal" class="modal">
        <div class="modal-content">
            <!-- Close button inside modal -->
            <button class="modal-close-btn" onclick="closeModal()">X</button>
            <i class="fa-solid fa-circle-check success-icon"></i>
            <p>Password successfully reset.</p>
        </div>
    </div>
    <script>
        // Show the modal
        document.getElementById('successModal').style.display = 'flex';

        // Close modal and redirect to login page
        function closeModal() {
            document.getElementById('successModal').style.display = 'none';
            window.location.href = "../PHP/loginPage.php";
        }
    </script>
<?php } ?>

<script>
function redirectToLogin() {
  window.location.href = "../PHP/loginPage.php";
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
</body>
</html>
