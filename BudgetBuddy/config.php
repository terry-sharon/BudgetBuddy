<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budgetbuddy";

// Create connection
$link =mysqli_connect("localhost", "root", "", "budgetbuddy");

// Check connection
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
