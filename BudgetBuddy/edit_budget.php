<?php
session_start(); // Start the session

require_once 'config.php'; // Include database configuration

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Initialize messages
$success_message = '';
$error_message = '';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: manage_budgets.php");
    exit;
}

$budget_id = intval($_GET['id']);

// Fetch the budget data
$budget = [];
$sql = "SELECT id, name, total_amount, amount_spent, remaining_amount, start_date, end_date, category FROM budgets WHERE id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $budget_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $budget = $result->fetch_assoc();
    $stmt->close();
} else {
    $error_message = "Error preparing statement: " . $mysqli->error;
}

// Handle the budget form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['budget_name']) && isset($_POST['total_amount'])) {
    $budget_name = $_POST['budget_name'];
    $total_amount = $_POST['total_amount'];
    $amount_spent = $_POST['amount_spent'] ?? 0.00;
    $remaining_amount = $total_amount - $amount_spent;
    $start_date = $_POST['start_date'] ?? $budget['start_date'];
    $end_date = $_POST['end_date'] ?? $budget['end_date'];
    $category = $_POST['category'] ?? $budget['category'];

    // Prepare an SQL statement to update the budget
    $sql = "UPDATE budgets SET name = ?, total_amount = ?, amount_spent = ?, remaining_amount = ?, start_date = ?, end_date = ?, category = ?, updated_at = NOW() WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssddsssi", $budget_name, $total_amount, $amount_spent, $remaining_amount, $start_date, $end_date, $category, $budget_id);
        if ($stmt->execute()) {
            $success_message = "Budget updated successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $mysqli->error;
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Edit Budget</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Inline CSS to match BudgetBuddy theme */
        body {
            background: linear-gradient(135deg, #004225, #007B3A);
            font-family: 'Arial', sans-serif;
        }
        .container {
            padding: 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            max-width: 600px;
            margin: auto;
        }
        h1 {
            color: #00A86B;
            margin-bottom: 30px;
        }
        .btn {
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #007B3A;
            border-color: #006633;
        }
        .btn-primary:hover {
            background-color: #005F2E;
            border-color: #004d2d;
        }
        .btn-secondary {
            background-color: #004d2d;
            border-color: #003d1f;
        }
        .btn-secondary:hover {
            background-color: #003d1f;
            border-color: #002b14;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="m_budget.php" class="btn btn-secondary mb-3">Back to Manage Budgets</a>
        <h1>Edit Budget</h1>

        <?php if (!empty($success_message)) : ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Budget Edit Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $budget_id; ?>">
            <div class="mb-3">
                <label for="budget_name" class="form-label">Budget Name</label>
                <input type="text" class="form-control" id="budget_name" name="budget_name" value="<?php echo htmlspecialchars($budget['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="total_amount" class="form-label">Total Amount</label>
                <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="<?php echo htmlspecialchars($budget['total_amount']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="amount_spent" class="form-label">Amount Spent</label>
                <input type="number" step="0.01" class="form-control" id="amount_spent" name="amount_spent" value="<?php echo htmlspecialchars($budget['amount_spent']); ?>">
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($budget['start_date']); ?>">
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($budget['end_date']); ?>">
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select id="category" name="category" class="form-select">
                    <option value="Food" <?php echo $budget['category'] == 'Food' ? 'selected' : ''; ?>>Food</option>
                    <option value="Transport" <?php echo $budget['category'] == 'Transport' ? 'selected' : ''; ?>>Transport</option>
                    <option value="Utilities" <?php echo $budget['category'] == 'Utilities' ? 'selected' : ''; ?>>Utilities</option>
                    <option value="Entertainment" <?php echo $budget['category'] == 'Entertainment' ? 'selected' : ''; ?>>Entertainment</option>
                    <option value="Healthcare" <?php echo $budget['category'] == 'Healthcare' ? 'selected' : ''; ?>>Healthcare</option>
                    <option value="Other" <?php echo $budget['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Budget</button>
        </form>
    </div>
</body>
</html>
