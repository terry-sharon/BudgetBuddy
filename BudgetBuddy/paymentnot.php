<?php
// notification_handler.php

// Read and decode the JSON payload sent by MPesa
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Log the incoming notification for debugging purposes
file_put_contents('mpesa_notifications.log', print_r($data, true), FILE_APPEND);

// Extract data from the notification
$transaction_id = $data['TransactionID'];
$amount = $data['Amount'];
$phone_number = $data['MSISDN'];
$description = $data['Description'];
$date = $data['TransactionDate'];

// Store the transaction details into the database
require_once 'config.php'; // Include database configuration

$sql = "INSERT INTO transactions (user_id, description, amount, date, category) VALUES (?, ?, ?, ?, ?)";
$user_id = get_user_id_from_phone_number($phone_number); // Implement this function based on your needs
$category = 'mpesa'; // Or any other category you prefer

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("issss", $user_id, $description, $amount, $date, $category);
    if ($stmt->execute()) {
        // Successfully inserted into the database
    } else {
        // Log or handle errors
    }
    $stmt->close();
} else {
    // Log or handle errors
}
$mysqli->close();
?>
