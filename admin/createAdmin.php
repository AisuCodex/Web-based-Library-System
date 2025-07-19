<?php
include 'adminAuth.php';

// Database connection
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "u297985594_marc";
$conn = "";

try {
    $conn = mysqli_connect($db_server, $db_user, $db_password, $db_name);
} catch(mysqli_sql_exception) {
    echo "Could not connect to database.";
}

// Function to generate a 6-character alphanumeric code
function generateCode($length = 6) {
    return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

// Create table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS assistant_acc (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $create_table)) {
    die("Error creating table: " . mysqli_error($conn));
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $code = generateCode(); // Generate a 6-character code

    // Basic validation
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email exists
        $check_sql = "SELECT * FROM assistant_acc WHERE email = '$email'";
        $result = mysqli_query($conn, $check_sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $error = "Email already exists!";
        } else {
            // Hash password and insert new admin
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO assistant_acc (email, password, code) VALUES ('$email', '$hashed_password', '$code')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $success = "Admin account created successfully! Your code is: " . $code;
            } else {
                $error = "Error creating account: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
    <link rel="stylesheet" href="../CSS/loading_screen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        :root {
            --darkest-shade: #2a3417;
            --darker-shade: #3f4a22;
            --base-color: #556b2f;
            --lighter-tint: #758b4d;
            --lightest-tint: #99b27a;
            --extra-lightest-tint: #dbe4d0;
        }

        body {
            background-image: url(../img/side.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .secondContainer {
            position: relative;
            width: 90%;
            max-width: 400px;
            background: var(--extra-lightest-tint);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .backBtn-div {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }

        .backBtn {
            text-decoration: none;
            color: var(--darkest-shade);
            font-size: 1.2em;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .backBtn:hover {
            color: var(--base-color);
            transform: scale(1.1);
        }

        h2 {
            color: var(--darkest-shade);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
            font-weight: 600;
        }

        .inputBox {
            position: relative;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--darker-shade);
        }

        .inputBox input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            background: transparent;
            border: none;
            outline: none;
            color: var(--darkest-shade);
            font-size: 1em;
            letter-spacing: 0.05em;
        }

        .inputBox span {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--darker-shade);
            pointer-events: none;
            transition: 0.3s ease all;
        }

        .inputBox span.active {
            top: -5px;
            transform: translateY(0);
            font-size: 0.85em;
            color: var(--base-color);
        }

        .inputBox.focused {
            border-color: var(--base-color);
        }

        .choice {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-create {
            padding: 12px 30px;
            background: var(--base-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-create:hover {
            background: var(--lighter-tint);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .error, .success {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9em;
            animation: message-appear 0.3s ease-out;
        }

        .error {
            color: #ff3333;
            background: rgba(255, 51, 51, 0.1);
        }

        .success {
            color: #4CAF50;
            background: rgba(76, 175, 80, 0.1);
        }

        @keyframes message-appear {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Additional styling for school logo */
        .school-logo {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
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
    <div class="loading-overlay" id="loading-screen">
        <div class="loader"></div>
    </div>

    <div class="secondContainer">
        <div class="child">
            <div class="backBtn-div">
                <a class="backBtn" href="adminPage.php" onclick="showLoadingScreen()">X</a>
            </div>
            
            <form method="POST" action="" class="animate-form">
                <h2>Create Admin Account</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="inputBox">
                    <input type="email" id="username" name="username" required>
                    <span id="username-text">Email</span>
                    <i id="username-focus"></i>
                </div>
                
                <div class="inputBox">
                    <input type="password" id="password" name="password" required>
                    <span id="password-text">Password</span>
                    <i id="password-focus"></i>
                </div>
                
                <div class="inputBox">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span id="confirm_password-text">Confirm Password</span>
                    <i id="confirm_password-focus"></i>
                </div>
                
                <div class="choice">
                    <button type="submit" class="btn-create">Create Admin Account</button>
                </div>
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

    <script src="../JavaScripts/loadingScreen.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.inputBox input');
        
        inputs.forEach(input => {
            const span = document.getElementById(input.id + '-text');
            
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
    </script>
</body>
</html>
