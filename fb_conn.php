<?php

//Connect to the MySQL Database locally  
  
$servername = "localhost";
$username = "root";
$password = "database passsword";
$dbname   = "database name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully to database \n";

?>