<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input data
    $user_id = $_SESSION['id']; // Assuming the user is logged in and their ID is stored in the session
    $description = htmlspecialchars(trim($_POST['description']));
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    $date = $_POST['date'];
    $category = $_POST['category']; // Assuming category is a new addition to the transactions table

    // Check if the input data is valid
    if ($description && $amount !== false && $date && $category) {
        // Prepare and execute the SQL statement
        $sql = "INSERT INTO transactions (user_id, description, amount, date, category) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("isdss", $user_id, $description, $amount, $date, $category);
            if ($stmt->execute()) {
                // Redirect to view transactions page after successful insertion
                header("Location: view_transactions.php");
                exit();
            } else {
                $message = "Error: Could not execute the query.";
            }
            $stmt->close();
        } else {
            $message = "Error: Could not prepare the SQL statement.";
        }
    } else {
        $message = "Invalid input data. Please ensure all fields are correctly filled.";
    }

    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Transaction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Add Transaction</h2>
    <?php if (!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group mb-3">
            <label for="description">Description</label>
            <input type="text" class="form-control" id="description" name="description" required>
        </div>
        <div class="form-group mb-3">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
        </div>
        <div class="form-group mb-3">
            <label for="date">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <div class="form-group mb-3">
            <label for="category">Category</label>
            <select id="category" name="category" class="form-select" required>
                <option value="" disabled selected>Select a category</option>
                <option value="food">Food</option>
                <option value="transport">Transport</option>
                <option value="entertainment">Entertainment</option>
                <option value="bills">Bills</option>
                <option value="others">Others</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add Transaction</button>
    </form>
</div>
</body>
</html>
