<?php
session_start();
require_once 'db.php';

// Check if the user is an admin; if not, redirect or show an error
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php'); // Redirect to login if not an admin
    exit();
}

// Fetch data (users, transactions) for display
$users = $mysqli->query("SELECT * FROM admin")->fetch_all(MYSQLI_ASSOC);
$transactions = $mysqli->query("SELECT * FROM transactions")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <style>
        body {
    font-family: Arial, sans-serif;
    background: #f8f9fa;
}

.container {
    margin-top: 20px;
}

h1 {
    color: #007bff;
}

.table {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th, .table td {
    vertical-align: middle;
}

.table thead {
    background-color: #007bff;
    color: #fff;
}

.table tbody tr:nth-child(even) {
    background-color: #f2f2f2;
}

.table tbody tr:hover {
    background-color: #e9ecef;
}

    </style> <!-- Optional custom CSS -->
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <!-- User Management -->
    <h2 class="mt-4">User Management</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Transactions Management -->
    <h2 class="mt-4">Transactions</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Category</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
