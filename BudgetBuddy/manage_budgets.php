<?php
session_start();
require_once 'config.php'; // Include database configuration

// Handle budget deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $budget_id = intval($_GET['delete']); // Ensure the ID is an integer

    // Prepare a delete statement
    $sql = "DELETE FROM budgets WHERE id = ? AND user_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ii", $budget_id, $_SESSION['id']); // Make sure the session variable is correct
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Budget deleted successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    }
}

// Handle budget update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $budget_id = intval($_POST['budget_id']);
    $budget_name = trim($_POST['budget_name']);
    $amount = floatval($_POST['amount']);

    // Prepare an update statement
    $sql = "UPDATE budgets SET budget_name = ?, amount = ? WHERE id = ? AND user_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sdii", $budget_name, $amount, $budget_id, $_SESSION['id']); // Use the correct session variable
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Budget updated successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    }
}

// Fetch all budgets for the logged-in user
$sql = "SELECT id, budget_name, amount FROM budgets WHERE user_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['id']); // Use the correct session variable
    $stmt->execute();
    $result = $stmt->get_result();
    $budgets = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    echo "Error: Could not prepare the query. " . $mysqli->error;
}
$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Budgets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #004225, #007B3A); /* Gradient background */
    color: #333;
}

.container {
    margin-top: 20px;
    padding: 20px;
    background-color: #ffffff; /* White background for container */
    border-radius: 12px; /* Rounded corners */
    box-shadow: 0 8px 16px rgba(0,0,0,0.2); /* Softer shadow */
}

h2 {
    color: #00A86B; /* Green color to match BudgetBuddy theme */
    font-weight: bold;
}

.table {
    background-color: #ffffff; /* White background for table */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Soft shadow */
}

.table th, .table td {
    vertical-align: middle;
    padding: 12px; /* Add some padding */
}

.table thead {
    background-color: #007B3A; /* Dark green header background */
    color: #ffffff; /* White text for header */
}

.table tbody tr:nth-child(even) {
    background-color: #f9f9f9; /* Light gray for even rows */
}

.table tbody tr:hover {
    background-color: #d9f7d9; /* Light green on hover */
}

.btn-primary {
    background-color: #007B3A; /* Dark green */
    border-color: #006633; /* Slightly darker green for borders */
}

.btn-primary:hover {
    background-color: #005F2E; /* Even darker green on hover */
    border-color: #004d2d; /* Darker border on hover */
}

.btn-warning {
    background-color: #FFC107; /* Standard warning color */
    border-color: #FFC107; /* Match border to background */
}

.btn-warning:hover {
    background-color: #e0a800; /* Darker shade on hover */
    border-color: #d39e00; /* Darker border on hover */
}

.btn-danger {
    background-color: #d9534f; /* Bootstrap default danger color */
    border-color: #c9302c; /* Slightly darker border */
}

.btn-danger:hover {
    background-color: #c9302c; /* Darker shade on hover */
    border-color: #ac2925; /* Darker border on hover */
}

.modal-content {
    border-radius: 8px; /* Rounded corners */
}

.modal-header {
    background-color: #007B3A; /* Dark green header background */
    color: #ffffff; /* White text for header */
}

.modal-footer {
    border-top: 1px solid #e0e0e0; /* Light gray border */
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
<div class="container">
    <h2 class="mt-4">Manage Budgets</h2>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Budget Name</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($budgets as $budget): ?>
                <tr>
                    <td><?php echo htmlspecialchars($budget['budget_name']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($budget['amount'], 2)); ?></td>
                    <td>
                        <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" 
                           data-id="<?php echo htmlspecialchars($budget['id']); ?>" 
                           data-name="<?php echo htmlspecialchars($budget['budget_name']); ?>" 
                           data-amount="<?php echo htmlspecialchars($budget['amount']); ?>">Edit</a>
                        <a href="?delete=<?php echo htmlspecialchars($budget['id']); ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to delete this budget?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Back to Dashboard Button -->
    <a href="dashtest.php" class="btn btn-secondary back-button">Back</a>

    <!-- Edit Budget Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Budget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="manage_budget.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="hidden" name="budget_id" id="budget_id">
                            <label for="budget_name">Budget Name</label>
                            <input type="text" class="form-control" id="budget_name" name="budget_name" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var amount = button.getAttribute('data-amount');

        var modal = editModal.querySelector('.modal-body');
        modal.querySelector('#budget_id').value = id;
        modal.querySelector('#budget_name').value = name;
        modal.querySelector('#amount').value = amount;
    });
</script>
</body>
</html>