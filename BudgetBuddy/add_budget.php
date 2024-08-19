<?php
session_start();
require_once 'config.php'; // Include database configuration

// Check if the user is logged in; if not, redirect to login or another page
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login page if not authenticated
    exit();
}

$success_message = ""; // Initialize as an empty string

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id']; // Use 'id' instead of 'user_id'
    $budget_name = $_POST['budget_name'];
    $amount = $_POST['amount'];

    // Prepare an SQL statement to insert the budget
    $sql = "INSERT INTO budgets (user_id, budget_name, amount) VALUES (?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("isd", $user_id, $budget_name, $amount);
        if ($stmt->execute()) {
            $success_message = "Budget added successfully!";
        } else {
            $success_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $success_message = "Error preparing statement: " . $mysqli->error;
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
<!-- Budget Form -->
<div class="form-container" id="budget-form-container">
    <h2>Add Budget</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="budget_name">Budget Name</label>
            <input type="text" class="form-control" id="budget_name" name="budget_name" required>
        </div>
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category" required>
                <option value="Food">Food</option>
                <option value="Transport">Transport</option>
                <option value="Rent">Rent</option>
                <option value="Entertainment">Entertainment</option>
                <option value="Utilities">Utilities</option>
                <option value="Healthcare">Healthcare</option>
                <option value="Education">Education</option>
                <option value="Savings">Savings</option>
                <option value="Miscellaneous">Miscellaneous</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add Budget</button>
    </form>

    <h2 class="mt-5">Your Budgets</h2>
    <div class="list-group">
        <?php foreach ($budgets as $budget): ?>
            <a href="view_budget.php?id=<?php echo $budget['id']; ?>" class="list-group-item list-group-item-action">
                <?php echo htmlspecialchars($budget['name']); ?> - Ksh <?php echo htmlspecialchars(number_format($budget['total_amount'], 2)); ?> (<?php echo htmlspecialchars($budget['category']); ?>)
            </a>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
