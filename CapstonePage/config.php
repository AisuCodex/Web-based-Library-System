<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if we're on Hostinger
    $is_hostinger = (strpos($_SERVER['HTTP_HOST'] ?? '', 'hostinger') !== false || 
                     strpos($_SERVER['SERVER_NAME'] ?? '', 'hostinger') !== false);

    if ($is_hostinger) {
        // Hostinger database credentials
        $servername = "localhost";
        $username = "u297985594_marc"; // Hostinger database username
        $password = "your_hostinger_password"; // Replace with your Hostinger database password
        $dbname = "u297985594_marc";
    } else {
        // Local development credentials
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "u297985594_marc";
    }

    // Create connection with error handling
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set timezone to match your location
    $conn->query("SET time_zone = '+08:00'");
    
} catch (Exception $e) {
    // Log the error
    error_log("Database Error in config.php: " . $e->getMessage());
    
    // Display user-friendly error
    echo "<div style='background: #ffebee; color: #c62828; padding: 10px; margin: 10px; border: 1px solid #ef9a9a;'>";
    echo "<strong>Database Connection Error:</strong><br>";
    echo "Could not connect to the database. Please try again later or contact support.";
    if ($is_hostinger ?? false) {
        error_log("Error occurred in production environment");
    } else {
        echo "<br><br><strong>Error Details (for admin):</strong><br>";
        echo htmlspecialchars($e->getMessage());
    }
    echo "</div>";
    die();
}
?>
