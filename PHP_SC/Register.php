<?php
// Include the database connection
include("../database/Register_database.php");

// Initialize error message
$errorMessage = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize email input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $domain = "bulsu.edu.ph";
    $domainLength = strlen($domain);

    // Sanitize and validate password input
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);

    // Validate email
    if (substr($email, -$domainLength) !== $domain) {
        $errorMessage = 'Use Bulsu email address.';
    } 
    // Validate password
    elseif (!preg_match("/^[a-zA-Z0-9#$%^&*()_+={}\[\]:%;,.?]+$/", $password)) {
        $errorMessage = 'invalid Password.';
    } 
    else {
        // Hash password and insert into database
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO testing (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hash);

        if ($stmt->execute()) {
            // Optionally redirect or handle success
            echo "";
            header("Location: homepage.php");
            exit();
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
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
  <title>Document</title>
  <link rel="stylesheet" href="../CSS_STYLES/log.css">
</head>
<body>
  <div class="container">
    <div class="logo">
      <img src="../img/greenLogo.png" alt="">
    </div>
    <div class="text">
      <h2>
        BULACAN STATE UNIVERSITY<br>
        HAGONOY CAMPUS
      </h2>
      <h3>
        Capstone/Thesis Management System 
      </h3>
    </div>
    <div class="logo"><img src="../img/orangeLogo.png" alt=""></div>
  </div>

  <div class="secondContainer">
    <div class="child">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h2>Register</h2>
        <div class="inputBox">
          <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
          <span>Email</span>
          <i></i>
        </div>
        <div class="inputBox">
          <input type="password" id="password" name="password" required>
          <span>Password</span>
          <i></i>
        </div>
        <input class="register" type="submit" name="submit" value="Register"><br>
        <div class="error">
            <?php echo $errorMessage; ?>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
