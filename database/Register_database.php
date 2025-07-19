<?php
  $db_server = "localhost";
  $db_user = "root";
  $db_password = "";
  $db_name = "u297985594_marc";
  $conn = "";

  try{
    $conn = mysqli_connect($db_server, 
                          $db_user, 
                          $db_password, 
                          $db_name);
    
    // Set timezone for this connection to Asia/Manila
    mysqli_query($conn, "SET time_zone = '+08:00'");
  }
  catch(mysqli_sql_exception){
    echo"Could not connect.";
  }
  if(isset($conn)){
    echo"";
  }
?>