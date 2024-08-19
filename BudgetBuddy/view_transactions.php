<?php
session_start();
require_once 'config.php'; // Include database configuration

// Check if the user is logged in; if not, redirect to login or another page
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login page if not authenticated
    exit();
}

$user_id = $_SESSION['id']; // Use 'id' instead of 'user_id'

// Prepare an SQL statement to select distinct transactions for the logged-in user
$sql = "SELECT DISTINCT id, description, amount, date FROM transactions WHERE user_id = ? ORDER BY date DESC";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Handle SQL prepare error
    $transactions = [];
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #004225, #007B3A);
            font-family: 'Arial', sans-serif;
            color: #fff;
        }
        .container {
            background-color: #fff;
            color: #004225;
            border-radius: 12px;
            padding: 20px;
            margin-top: 50px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #007B3A;
            text-align: center;
            margin-bottom: 30px;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        .table-striped > tbody > tr:nth-of-type(even) {
            background-color: #e0f2f1;
        }
        .table > thead {
            background-color: #004225;
            color: #fff;
        }
        .btn-secondary {
            background-color: #007B3A;
            border-color: #006633;
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            text-align: center;
            display: block;
            margin: 30px auto;
        }
        .btn-secondary:hover {
            background-color: #005F2E;
            border-color: #004d2d;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2>Your Transactions</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
                <tr><td colspan="3" class="text-center">No transactions found.</td></tr>
            <?php else: ?>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($transaction['amount'], 2)); ?></td>
                        <td>
                            <?php
                            $date = $transaction['date'];
                            if ($date && $date !== '0000-00-00') {
                                echo htmlspecialchars(date('F j, Y', strtotime($date)));
                            } else {
                                echo 'N/A'; // Display 'N/A' if the date is not valid
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <!-- Back to Dashboard Button -->
    <a href="dashtest.php" class="btn btn-secondary back-button">Back</a>
</div>
</body>
</html>
