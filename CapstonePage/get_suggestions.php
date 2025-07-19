<?php
include 'config.php';

if (isset($_GET['term'])) {
    $search = trim($_GET['term']);
    $search = $conn->real_escape_string($search);
    
    // Query for suggestions from title, abstract, and author
    $sql = "SELECT DISTINCT 
            CASE 
                WHEN title LIKE '$search%' THEN title
                WHEN abstract LIKE '$search%' THEN abstract
                WHEN author LIKE '$search%' THEN author
            END as suggestion
            FROM capstone
            WHERE title LIKE '$search%' 
            OR abstract LIKE '$search%' 
            OR author LIKE '$search%'
            LIMIT 5";
            
    $result = $conn->query($sql);
    
    $suggestions = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['suggestion'] !== null) {
                $suggestions[] = $row['suggestion'];
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($suggestions);
}
?>
