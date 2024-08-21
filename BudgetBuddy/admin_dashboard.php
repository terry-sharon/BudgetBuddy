<?php
session_start(); // Start the session

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php'; // Include database configuration

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

// Initialize messages
$success_message = '';
$error_message = '';

// Fetch all users
$all_users = [];
$sql_users = "SELECT id, username, email FROM users";
if ($stmt = $mysqli->prepare($sql_users)) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $all_users = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $error_message .= "Error fetching users: " . $mysqli->error . "<br>";
}

// Fetch all budgets
$all_budgets = [];
$sql_budgets = "SELECT id, user_id, name, total_amount, amount_spent, remaining_amount, start_date, end_date, category, created_at, updated_at FROM budgets";
if ($stmt = $mysqli->prepare($sql_budgets)) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $all_budgets = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $error_message .= "Error fetching budgets: " . $mysqli->error . "<br>";
}

// Fetch all transactions
$all_transactions = [];
$sql_transactions = "SELECT id, user_id, amount, description, date AS transaction_date, category FROM transactions";
if ($stmt = $mysqli->prepare($sql_transactions)) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $all_transactions = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $error_message .= "Error fetching transactions: " . $mysqli->error . "<br>";
}

$mysqli->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
     <style>
        /* Inline CSS to match BudgetBuddy theme */
        body {
            background: linear-gradient(135deg, #004225, #007B3A);
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #004225;
            padding-top: 20px;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
        }
        .sidebar .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .sidebar .logo img {
            width: 120px;
        }
        .sidebar ul {
            padding: 0;
            list-style: none;
        }
        .sidebar ul li {
            padding: 15px 20px;
            border-bottom: 1px solid #006633;
        }
        .sidebar ul li a {
            color: #ffffff;
            text-decoration: none;
            display: block;
        }
        .sidebar ul li a:hover {
            background-color: #007B3A;
            border-radius: 4px;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .container {
            margin: 0 auto;
            margin-top: 30px;
            padding: 40px;
            max-width: 1200px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        h1 {
            color: #00A86B;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }
        h2 {
            color: #004225;
            margin-bottom: 20px;
            font-size: 1.75rem;
            border-bottom: 2px solid #007B3A;
            padding-bottom: 10px;
        }
        .btn {
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #007B3A;
            border-color: #006633;
            border-radius: 8px;
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
        .btn-danger {
            background-color: #d9534f;
            border-color: #c9302c;
        }
        .btn-danger:hover {
            background-color: #c9302c;
            border-color: #ac2925;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table th {
            background-color: #007B3A;
            color: #ffffff;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="budgetlogo.png" alt="BudgetBuddy Logo">
        </div>
        <ul>
            <li><a href="admin_dashboard.php">Views</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="generate_reports.php">Generate reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="container">
            <h1>Admin Dashboard</h1>

            <?php if (!empty($success_message)) : ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)) : ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Users Table -->
            <h2>Users</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_users)) : ?>
                        <?php foreach ($all_users as $user) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Budgets Table -->
            <h2>Budgets</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Total Amount</th>
                        <th>Amount Spent</th>
                        <th>Remaining Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_budgets)) : ?>
                        <?php foreach ($all_budgets as $budget) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($budget['id']); ?></td>
                                <td><?php echo htmlspecialchars($budget['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($budget['name']); ?></td>
                                <td><?php echo htmlspecialchars($budget['total_amount']); ?></td>
                                <td><?php echo htmlspecialchars($budget['amount_spent']); ?></td>
                                <td><?php echo htmlspecialchars($budget['remaining_amount']); ?></td>
                                <td><?php echo htmlspecialchars($budget['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($budget['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($budget['category']); ?></td>
                                <td><?php echo htmlspecialchars($budget['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($budget['updated_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="11">No budgets found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Transactions Table -->
            <h2>Transactions</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Transaction Date</th>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_transactions)) : ?>
                        <?php foreach ($all_transactions as $transaction) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
