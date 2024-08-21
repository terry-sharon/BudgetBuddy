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
$negative_remaining_amount_alerts = [];

// Handle budget deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM budgets WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success_message = "Budget deleted successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $mysqli->error;
    }
}

// Fetch all budgets for the user
$all_budgets = [];
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $sql = "SELECT id, name, total_amount, amount_spent, remaining_amount, start_date, end_date, category, created_at, updated_at FROM budgets WHERE user_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $all_budgets = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Check for negative remaining amounts
        foreach ($all_budgets as $budget) {
            if ($budget['remaining_amount'] < 0) {
                $negative_remaining_amount_alerts[] = htmlspecialchars($budget['name']);
            }
        }
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
    <title>Manage Budgets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Inline CSS to match BudgetBuddy theme */
        body {
            background: linear-gradient(135deg, #004225, #007B3A);
            font-family: 'Arial', sans-serif;
        }
        .content {
            margin: 20px;
        }
        .container {
            padding: 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
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
        .btn-edit {
            background-color: #007B3A;
            border-color: #006633;
        }
        .btn-edit:hover {
            background-color: #005F2E;
            border-color: #004d2d;
        }
        .btn-delete {
            background-color: #d9534f;
            border-color: #c9302c;
        }
        .btn-delete:hover {
            background-color: #c9302c;
            border-color: #ac2925;
        }
        .btn-back {
            margin-bottom: 20px;
        }
        .alert-warning {
            background-color: #f0ad4e;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="container">
            <a href="dashtest.php" class="btn btn-secondary btn-back">Back to Dashboard</a>
            <h1>Manage Budgets</h1>

            <?php if (!empty($success_message)) : ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)) : ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($negative_remaining_amount_alerts)) : ?>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> The following budgets have a negative remaining amount:
                    <ul>
                        <?php foreach ($negative_remaining_amount_alerts as $budget_name) : ?>
                            <li><?php echo $budget_name; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Display Budgets -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Total Amount</th>
                        <th>Amount Spent</th>
                        <th>Remaining Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_budgets as $budget) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($budget['name']); ?></td>
                            <td><?php echo htmlspecialchars($budget['total_amount']); ?></td>
                            <td><?php echo htmlspecialchars($budget['amount_spent']); ?></td>
                            <td><?php echo htmlspecialchars($budget['remaining_amount']); ?></td>
                            <td><?php echo htmlspecialchars($budget['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($budget['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($budget['category']); ?></td>
                            <td><?php echo htmlspecialchars($budget['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($budget['updated_at']); ?></td>
                            <td>
                                <a href="edit_budget.php?id=<?php echo $budget['id']; ?>" class="btn btn-edit">Edit</a>
                                <a href="?delete=<?php echo $budget['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this budget?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
