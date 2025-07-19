<?php
// Include the database connection and start session
require_once("../database/adminAcc_database.php");
session_name('assistant_session');
session_start();

// Initialize variables
$errorMessage = $successMessage = '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Function to validate password
function validatePassword($password) {
    // Password must be at least 8 characters long and contain at least one number
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match("/\d/", $password)) {
        return "Password must contain at least one number.";
    }
    return "";
}

// Check if the user is logged in
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: ../PHP/assistantLogin.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    try {
        // Sanitize and validate inputs
        $currentPassword = trim(filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING));
        $newPassword = trim(filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING));
        $confirmPassword = trim(filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING));

        // Validate input presence
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            throw new Exception("All fields are required.");
        }

        // Validate new password
        $passwordError = validatePassword($newPassword);
        if (!empty($passwordError)) {
            throw new Exception($passwordError);
        }

        // Check if new passwords match
        if ($newPassword !== $confirmPassword) {
            throw new Exception("New password and confirmation do not match.");
        }

        // Prepare statement to check current password
        $stmt = $conn->prepare("SELECT password FROM assistant_acc WHERE email = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Database error. Please try again later.");
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            throw new Exception("User account not found.");
        }

        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Verify current password
        if (!password_verify($currentPassword, $hashedPassword)) {
            throw new Exception("Current password is incorrect.");
        }

        // Hash the new password
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $updateStmt = $conn->prepare("UPDATE assistant_acc SET password = ? WHERE email = ?");
        if (!$updateStmt) {
            throw new Exception("Database error while updating password.");
        }

        $updateStmt->bind_param("ss", $newHashedPassword, $email);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update password. Please try again.");
        }

        $successMessage = "Password successfully changed!";
        
        // Clean up
        $updateStmt->close();

    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="../CSS/changePassword.css">
    <link rel="stylesheet" href="../CSS/loading_screen.css">
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <div class="secondContainer">
        <div class="child">
            <div class="backBtn-div">
                <a class="backBtn" href="../assistant/assistantPage.php" onclick="showLoadingScreen()">X</a>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="animate-form">
                <h2>Change Password</h2>
                
                <div class="inputBox">
                    <input type="password" id="current_password" name="current_password" required>
                    <span id="current_password-text">Current Password</span>
                    <i id="current_password-focus"></i>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('current_password', this)">👁️</button>
                </div>
                <div class="inputBox">
                    <input type="password" id="new_password" name="new_password" required>
                    <span id="new_password-text">New Password</span>
                    <i id="new_password-focus"></i>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('new_password', this)">👁️</button>
                </div>
                <div class="inputBox">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span id="confirm_password-text">Confirm New Password</span>
                    <i id="confirm_password-focus"></i>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)">👁️</button>
                </div>
                <div class="choice">
                    <button type="submit" name="submit" class="register">Change Password</button>
                </div>

                <?php if (!empty($errorMessage)): ?>
                    <div class="error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></div>
                <?php endif; ?>
                <?php if (!empty($successMessage)): ?>
                    <div class="success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES); ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script src="../JavaScripts/loadingScreen.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.inputBox input');
        
        inputs.forEach(input => {
            const span = input.nextElementSibling;
            
            // Set initial state
            if (input.value !== '') {
                span.classList.add('active');
            }
            
            // Focus event
            input.addEventListener('focus', () => {
                span.classList.add('active');
                input.parentElement.classList.add('focused');
            });
            
            // Blur event
            input.addEventListener('blur', () => {
                if (input.value === '') {
                    span.classList.remove('active');
                }
                input.parentElement.classList.remove('focused');
            });
        });
    });

    function togglePasswordVisibility(fieldId, button) {
        const field = document.getElementById(fieldId);
        if (field.type === 'password') {
            field.type = 'text';
            button.textContent = '✅';
        } else {
            field.type = 'password';
            button.textContent = '👁️';
        }
    }

    // Show loading screen on form submission
    document.querySelector('form').addEventListener('submit', function() {
        showLoadingScreen();
    });
    </script>
</body>
</html>
