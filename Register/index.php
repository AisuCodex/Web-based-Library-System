<?php
  include("../PHP_RegristrationForm_Project/database.php");

?>
<!DOCTYPE html>
  <html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="register.css">
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
      <h2>Login</h2> <br>
      <label class="label" for="email">Enter your email:</label> <br>
      <input class="email" type="email" id="email" name="email" required> <br>
      <label class="label" for="password">Password:</label><br>
      <input class="password" type="password" id="password" name="password" required><br>
      <input class="register" type="submit" name="submit" value="Register"><br>
</form>
    </div>
  </div> 

</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize email input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $domain = "bulsu.edu.ph";
    $domainLength = strlen($domain);

    // Sanitize and validate password input
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);

    // Check if the email ends with the correct domain
    if (substr($email, -$domainLength) !== $domain) {
        echo "Please use BulSu email.";
    }
    // Validate password
    elseif (empty($password)) {
        echo "Please enter a password.";
    } 
    elseif (!preg_match("/^[a-zA-Z0-9#$%^&*()_+={}\[\]:%;,.?]+$/", $password)) {
        // Check for special characters in the password
        echo "The password contains invalid characters.";
    } 
    else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $sql = "INSERT INTO testing (email, password) VALUES('$email', '$hash')";
      mysqli_query($conn, $sql);

    }
}
mysqli_close($conn);
?>


  