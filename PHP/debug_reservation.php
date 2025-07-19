<?php
// This is a debug script to help identify issues with reservations
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Reservation System Debug</h1>";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Session Information</h2>";
echo "<pre>";
if (isset($_SESSION['email'])) {
    echo "User is logged in as: " . htmlspecialchars($_SESSION['email']);
} else {
    echo "User is not logged in.";
}
echo "</pre>";

echo "<h2>POST Data</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h2>Database Connection</h2>";
try {
    require_once '../CapstonePage/config.php';
    
    echo "Database connection successful<br>";
    
    // Check tables
    $tables = array('book_reservations', 'thesis_reservations');
    
    foreach ($tables as $table) {
        echo "<h3>Table: $table</h3>";
        
        // Check if table exists
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "Table exists<br>";
            
            // Check structure
            echo "<h4>Table Structure</h4>";
            $structure = $conn->query("DESCRIBE $table");
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            while ($row = $structure->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            // Check for sample data
            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];
                echo "<h4>Sample Reservations</h4>";
                
                $query = "SELECT * FROM $table WHERE user_email = ? LIMIT 5";
                $stmt = $conn->prepare($query);
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        echo "<table border='1'>";
                        
                        // Headers
                        $headers = array();
                        $row = $result->fetch_assoc();
                        echo "<tr>";
                        foreach ($row as $key => $value) {
                            echo "<th>" . htmlspecialchars($key) . "</th>";
                            $headers[] = $key;
                        }
                        echo "</tr>";
                        
                        // Output first row
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                        echo "</tr>";
                        
                        // Output other rows
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($headers as $key) {
                                echo "<td>" . htmlspecialchars($row[$key] ?? 'NULL') . "</td>";
                            }
                            echo "</tr>";
                        }
                        
                        echo "</table>";
                    } else {
                        echo "No reservations found for this user.";
                    }
                } else {
                    echo "Failed to prepare statement: " . $conn->error;
                }
            }
            
        } else {
            echo "Table does not exist<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Database error: " . htmlspecialchars($e->getMessage());
}

echo "<h2>Testing Cancellation Flow</h2>";
echo "<p>This section will help you test the cancellation process.</p>";

// Output cancellation test form
echo "<form action='../PHP/cancel_reservation.php' method='post' target='_blank'>";
echo "<input type='hidden' name='type' value='capstone'>";
echo "Reservation ID: <input type='text' name='reservation_id' required>";
echo "<button type='submit'>Test Cancel Capstone</button>";
echo "</form>";

echo "<form action='../PHP/cancel_thesis_reservation.php' method='post' target='_blank'>";
echo "Reservation ID: <input type='text' name='reservation_id' required>";
echo "<button type='submit'>Test Cancel Thesis</button>";
echo "</form>";

// Add a link to go back to reservations page
echo "<p><a href='pending_reservations.php'>Back to Reservations</a></p>";
?>
