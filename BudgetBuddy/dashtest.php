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

// Handle the budget form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['budget_name']) && isset($_POST['total_amount']) && isset($_POST['start_date']) && isset($_POST['end_date']) && isset($_POST['category'])) {
    $user_id = $_SESSION['id'];
    $budget_name = $_POST['budget_name'];
    $total_amount = $_POST['total_amount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $category = $_POST['category'];

    // Calculate initial values for amount_spent and remaining_amount
    $amount_spent = 0; // Initially, nothing is spent
    $remaining_amount = $total_amount; // Initially, remaining amount is equal to total amount

    // Prepare an SQL statement to insert the budget
    $sql = "INSERT INTO budgets (user_id, name, total_amount, amount_spent, remaining_amount, start_date, end_date, category, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    if ($stmt = $mysqli->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("isddssss", $user_id, $budget_name, $total_amount, $amount_spent, $remaining_amount, $start_date, $end_date, $category);
        if ($stmt->execute()) {
            $success_message = "Budget added successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $mysqli->error;
    }
}

// Handle the transaction form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['description']) && isset($_POST['amount']) && isset($_POST['category'])) {
    $user_id = $_SESSION['id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date']; // Use date from form
    $budget_id = $_POST['budget_id'] ?? null; // Optional budget ID

    // Prepare an SQL statement to insert the transaction
    $sql = "INSERT INTO transactions (user_id, description, amount, category, date, budget_id) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("issssi", $user_id, $description, $amount, $category, $date, $budget_id);
        if ($stmt->execute()) {
            $success_message = "Transaction added successfully!";
            // Update the budget with the new amount spent and remaining amount
            if ($budget_id) {
                $update_sql = "UPDATE budgets SET amount_spent = amount_spent + ?, remaining_amount = remaining_amount - ?, updated_at = NOW() WHERE id = ?";
                if ($update_stmt = $mysqli->prepare($update_sql)) {
                    $update_stmt->bind_param("ddi", $amount, $amount, $budget_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $mysqli->error;
    }
}

// Fetch categories for the dropdown
$categories = ["Food", "Transport", "Utilities", "Entertainment", "Healthcare", "Other"];

// Fetch budgets for the dropdown
$budgets = [];
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $sql = "SELECT id, name FROM budgets WHERE user_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $budgets = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Handle the reminder form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title']) && isset($_POST['details']) && isset($_POST['reminder_date'])) {
    $user_id = $_SESSION['id'] ?? null; // Use 'id' from session if set
    if ($user_id) {
        $title = $_POST['title'];
        $details = $_POST['details'];
        $reminder_date = $_POST['reminder_date'];

        $sql = "INSERT INTO reminder (user_id, title, details, reminder_date) VALUES (?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("isss", $user_id, $title, $details, $reminder_date);
            if ($stmt->execute()) {
                $success_message = "Reminder added successfully!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "User ID not found.";
    }

    // Clear form fields after submission
    unset($_POST['title']);
    unset($_POST['details']);
    unset($_POST['reminder_date']);
}

$mysqli->close();
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
            background: linear-gradient(135deg, #004225, #007B3A);
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
        }
        .sidebar ul li a {
            color: #ffffff;
            text-decoration: none;
            display: block;
        }
        .sidebar ul li a:hover {
            background-color: #007B3A;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .container {
            margin-left: 270px;
            margin-top: 30px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .container img {
            max-width: 100px;
            margin-bottom: 20px;
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
        }
        .form-container {
            display: none;
        }
        .form-container.active {
            display: block;
        }
        .form-group label {
            font-weight: bold;
            color: #004d2d;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #ccc;
        }
        .form-control:focus {
            border-color: #007B3A;
            box-shadow: 0 0 8px rgba(0, 123, 58, 0.2);
        }
        h2 {
            color: #00A86B;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg"></nav>

    <div class="sidebar">
        <div class="logo">
            <img src="budgetlogo.png" alt="BudgetBuddy">
        </div>
        <ul>
            <li><a href="#" class="toggle-form" data-target="budget-form-container">Budgets</a></li>
            <li><a href="#" class="toggle-form" data-target="transaction-form-container">Transactions</a></li>
            <li><a href="#" class="toggle-form" data-target="reminder-form-container">Reminders</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?= $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message; ?></div>
        <?php endif; ?>

        <!-- Budget Form -->
        <div id="budget-form-container" class="form-container active">
    <h2>Add Budget</h2>
    <form method="post">
        <div class="form-group">
            <label for="budget_name">Budget Name:</label>
            <input type="text" name="budget_name" id="budget_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="total_amount">Total Amount:</label>
            <input type="number" name="total_amount" id="total_amount" class="form-control" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <select name="category" id="category" class="form-control" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category ?>"><?= $category ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Budget</button>
    </form>
</div>


        <!-- Transaction Form -->
        <div id="transaction-form-container" class="form-container">
            <h2>Add Transaction</h2>
            <form method="post">
                <div class="form-group">
                    <label for="description">Description:</label>
                    <input type="text" name="description" id="description" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" class="form-control" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category ?>"><?= $category ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" name="date" id="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="budget_id">Budget:</label>
                    <select name="budget_id" id="budget_id" class="form-control">
                        <option value="">Select Budget</option>
                        <?php foreach ($budgets as $budget): ?>
                            <option value="<?= $budget['id'] ?>"><?= $budget['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Transaction</button>
            </form>
        </div>

        <!-- Reminder Form -->
        <div id="reminder-form-container" class="form-container">
            <h2>Add Reminder</h2>
            <form method="post">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="details">Details:</label>
                    <input type="text" name="details" id="details" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="reminder_date">Reminder Date:</label>
                    <input type="date" name="reminder_date" id="reminder_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Reminder</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript to toggle form visibility
        document.querySelectorAll('.toggle-form').forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                document.querySelectorAll('.form-container').forEach(form => {
                    form.classList.remove('active');
                });
                document.getElementById(button.getAttribute('data-target')).classList.add('active');
            });
        });
    </script>
</body>
</html>
