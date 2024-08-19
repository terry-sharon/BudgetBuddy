<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budgetbuddy";

// Create connection using procedural style
$mysqli = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$mysqli) {  // Check if the connection was successful
    die("Connection failed: " . mysqli_connect_error());
}
?>
