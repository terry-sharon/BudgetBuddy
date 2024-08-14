<?php
session_start();
require_once 'config.php'; // Include database configuration

// Check if the user is logged in; if not, redirect to login or another page
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login page if not authenticated
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id']; // Use 'id' instead of 'user_id'
    $budget_name = $_POST['budget_name'];
    $amount = $_POST['amount'];

    // Prepare an SQL statement to insert the budget
    $sql = "INSERT INTO budgets (user_id, budget_name, amount) VALUES (?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("isd", $user_id, $budget_name, $amount);
        if ($stmt->execute()) {
            echo "Budget added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $mysqli->error;
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Budget</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Add Budget</h2>
    <form method="post" action="add_budget.php">
        <div class="form-group">
            <label for="budget_name">Budget Name</label>
            <input type="text" class="form-control" id="budget_name" name="budget_name" required>
        </div>
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add Budget</button>
    </form>
</div>
</body>
</html>
