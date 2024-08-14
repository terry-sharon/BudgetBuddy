<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Debugging: Check if session variables are set
if (!isset($_SESSION["username"])) {
    echo "Session variable 'username' is not set.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Inline CSS to match BudgetBuddy theme */
        body {
            background: linear-gradient(135deg, #004225, #007B3A); /* Gradient background */
            color: #ffffff; /* White text */
            font-family: 'Arial', sans-serif;
        }
         .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #004225;
            padding-top: 20px;
            overflow: auto;
        }
        .sidebar .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .sidebar .logo img {
            width: 100px;
        }
        .sidebar ul {
            padding: 0;
            list-style: none;
        }
        .sidebar ul li {
            padding: 15px 20px;
            border-bottom: 1px solid #006633;
            position: relative;
        }
        .sidebar ul li a {
            color: #ffffff;
            text-decoration: none;
            display: block;
        }
        .sidebar ul li a:hover {
            background-color: #007B3A;
            color: #ffffff;
        }
        .sidebar ul li .submenu {
            display: none;
            list-style: none;
            padding-left: 20px;
            background-color: #005F2E;
        }
        .sidebar ul li .submenu li {
            padding: 10px 0;
        }
        .sidebar ul li.active > .submenu {
            display: block;
        }
        .sidebar ul li.active > a {
            background-color: #007B3A;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .container {
            margin-left: 270px; /* Adjusted margin to account for sidebar width */
            margin-top: 30px;
            padding: 40px;
            background-color: #ffffff; /* White background for content */
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2); /* Softer shadow */
        }
        .container img {
            max-width: 100px; /* Size of the logo */
            margin-bottom: 20px;
        }
        h1 {
            color: #00A86B; /* Green color for headers */
            margin-bottom: 30px;
        }
        .btn {
            border-radius: 20px; /* Rounded corners for buttons */
            padding: 10px 20px;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #007B3A; /* Dark green */
            border-color: #006633; /* Slightly darker green for borders */
        }
        .btn-primary:hover {
            background-color: #005F2E; /* Even darker green on hover */
            border-color: #004d2d; /* Darker border on hover */
        }
        .btn-secondary {
            background-color: #004d2d; /* Slightly darker shade for secondary buttons */
            border-color: #003d1f; /* Even darker shade for borders */
        }
        .btn-secondary:hover {
            background-color: #003d1f; /* Darker shade on hover */
            border-color: #002b14; /* Darker border on hover */
        }
        .btn-danger {
            background-color: #d9534f; /* Bootstrap's default danger color */
            border-color: #c9302c; /* Slightly darker border */
        }
        .btn-danger:hover {
            background-color: #c9302c; /* Darker shade on hover */
            border-color: #ac2925; /* Darker border on hover */
        }
        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #00A86B;
            color: #ffffff;
            border-bottom: 0;
        }
        .card-body {
            background-color: #f9f9f9;
            color: #333;
        }
        .navbar {
            background-color: #00A86B; /* Matching navbar with BudgetBuddy theme */
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 24px;
            color: #ffffff; /* White text for navbar brand */
        }
        .nav-link {
            font-size: 18px;
            color: #ffffff; /* White text for nav links */
        }
        .nav-link:hover {
            color: #007B3A; /* Dark green on hover */
        }
        .dropdown-menu {
            background-color: #007B3A; /* Dark green dropdown background */
            border-radius: 8px;
        }
        .dropdown-item {
            color: #ffffff; /* White text for dropdown items */
        }
        .dropdown-item:hover {
            background-color: #004d2d; /* Darker green on hover */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
   <div class="sidebar">
        <div class="logo">
            <img src="data:image/png;base64,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" alt="BudgetBuddy">
        </div>
        <ul>
            <li class="active">
                <a href="dashboard.php">Dashboard</a>
            </li>
            <li>
                <a href="#">Budgets</a>
                <ul class="submenu">
                    <li><a href="add_budget.php" class="btn btn-primary">Add Budget</a></li>
                    <li><a href="manage_budgets.php">Manage Budgets</a></li>
                </ul>
            </li>
            <li>
                <a href="#">Transactions</a>
                <ul class="submenu">
                    <li><a href="add_transaction.php">Add Transaction</a></li>
                    <li><a href="view_transactions.php">View Transactions</a></li>
                </ul>
            </li>
            <li>
                <a href="#">Reminders</a>
                <ul class="submenu">
                    <li><a href="add_reminder.php">Add Reminder</a></li>
                    <li><a href="view_reminders.php">View Reminders</a></li>
                </ul>
            </li>
            <li>
                <a href="ai_coaching.php">Get Financial Coaching</a>
            </li>
            <li>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </li>
        </ul>
    </div>

    <div class="container mt-4">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
    </div>
</body>
</html>
