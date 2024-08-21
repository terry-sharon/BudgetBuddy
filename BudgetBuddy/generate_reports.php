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

// Initialize variables
$report_data = [];
$report_type = $_GET['report_type'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Fetch Report Data
if ($report_type) {
    if ($report_type == 'transaction_summary') {
        $sql = "SELECT category, SUM(amount) as total_amount FROM transactions WHERE date BETWEEN ? AND ? GROUP BY category";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $report_data = $result->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $mysqli->error;
        }
    } elseif ($report_type == 'budget_analysis') {
        $sql = "SELECT b.name, total_amount as budget_amount, COALESCE(SUM(t.amount), 0) as spent_amount 
                FROM budgets b 
                LEFT JOIN transactions t ON b.id = t.budget_id AND t.date BETWEEN ? AND ? 
                GROUP BY b.id, b.name, total_amount";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $report_data = $result->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $mysqli->error;
        }
    }
}

$mysqli->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Generate Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #004225, #007B3A);
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .container {
            margin-top: 30px;
            padding: 40px;
            max-width: 1200px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        h1, h2 {
            color: #00A86B;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Generate Reports</h1>

        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Report Form -->
        <form action="generate_reports.php" method="GET" class="mb-4">
            <div class="mb-3">
                <label for="report_type" class="form-label">Report Type</label>
                <select id="report_type" name="report_type" class="form-select" required>
                    <option value="" disabled selected>Select report type</option>
                    <option value="transaction_summary">Transaction Summary</option>
                    <option value="budget_analysis">Budget Analysis</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </form>

        <!-- Display Report Data -->
        <?php if (!empty($report_data)) : ?>
            <h2 class="mt-4">Report Results</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php if ($report_type == 'transaction_summary') : ?>
                            <th>Category</th>
                            <th>Total Amount</th>
                        <?php elseif ($report_type == 'budget_analysis') : ?>
                            <th>Budget Name</th>
                            <th>Budget Amount</th>
                            <th>Spent Amount</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row) : ?>
                        <tr>
                            <?php foreach ($row as $column) : ?>
                                <td><?php echo htmlspecialchars($column); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
