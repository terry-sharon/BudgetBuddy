<?php
// add_budget.php
include 'db.php'; // Database connection

session_start();
$user_id = $_SESSION['user_id']; // Ensure user_id is set upon login

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $amount = $_POST['amount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO budgets (user_id, name, amount, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $name, $amount, $start_date, $end_date);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo "Budget added successfully.";
}
?>
