<?php
session_start();
require_once 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id']; // Get user ID from session

// Initialize variables
$transactions = [];
$message = "";

// Handle editing a transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_transaction'])) {
        $transaction_id = $_POST['id'];
        $description = htmlspecialchars(trim($_POST['description']));
        $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
        $date = $_POST['date'];
        $category = $_POST['category'];

        if ($description && $amount !== false && $date && $category) {
            $sql = "UPDATE transactions SET description = ?, amount = ?, date = ?, category = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("sdssi", $description, $amount, $date, $category, $transaction_id);
                if ($stmt->execute()) {
                    $message = "Transaction updated successfully.";
                } else {
                    $message = "Error: Could not execute the query.";
                }
                $stmt->close();
            } else {
                $message = "Error: Could not prepare the SQL statement.";
            }
        } else {
            $message = "Invalid input data.";
        }
    } elseif (isset($_POST['delete_transaction'])) {
        $transaction_id = $_POST['id'];

        $sql = "DELETE FROM transactions WHERE id = ? AND user_id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ii", $transaction_id, $user_id);
            if ($stmt->execute()) {
                $message = "Transaction deleted successfully.";
            } else {
                $message = "Error: Could not execute the query.";
            }
            $stmt->close();
        } else {
            $message = "Error: Could not prepare the SQL statement.";
        }
    }
}

// Fetch transactions from the database
$sql = "SELECT id, description, amount, date, category FROM transactions WHERE user_id = ? ORDER BY date DESC";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $message = "Error: Could not prepare the SQL statement.";
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #004225, #007B3A);
            font-family: 'Arial', sans-serif;
        }

        .container {
            padding: 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        h2 {
             color: #00A86B;
            margin-bottom: 30px;
        }

        h3 {
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
    </style>
</head>
<body>
<div class="container mt-4">
    <a href="dashtest.php" class="btn btn-secondary btn-back">Back to Dashboard</a>
    <h2>Manage Transactions</h2>
    <?php if (!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <!-- Display Transactions -->
    <h3>Your Transactions</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transactions)) : ?>
                <?php foreach ($transactions as $transaction) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($transaction['amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                        <td>
                            <!-- Edit Form -->
                            <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $transaction['id']; ?>" data-description="<?php echo htmlspecialchars($transaction['description']); ?>" data-amount="<?php echo htmlspecialchars($transaction['amount']); ?>" data-date="<?php echo htmlspecialchars($transaction['date']); ?>" data-category="<?php echo htmlspecialchars($transaction['category']); ?>">Edit</a>
                            
                            <!-- Delete Form -->
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                                <button type="submit" name="delete_transaction" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transaction?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" class="text-center">No transactions found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group mb-3">
                            <label for="edit_description">Description</label>
                            <input type="text" class="form-control" id="edit_description" name="description" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_amount">Amount</label>
                            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_date">Date</label>
                            <input type="date" class="form-control" id="edit_date" name="date" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_category">Category</label>
                            <select id="edit_category" name="category" class="form-select" required>
                                <option value="" disabled>Select a category</option>
                                <option value="food">Food</option>
                                <option value="transport">Transport</option>
                                <option value="Utilities">Utilities</option>
                                <option value="entertainment">Entertainment</option>
                                <option value="bills">Bills</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        <button type="submit" name="edit_transaction" class="btn btn-primary">Update Transaction</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var description = button.getAttribute('data-description');
        var amount = button.getAttribute('data-amount');
        var date = button.getAttribute('data-date');
        var category = button.getAttribute('data-category');

        var modal = editModal.querySelector('form');
        modal.querySelector('#edit_id').value = id;
        modal.querySelector('#edit_description').value = description;
        modal.querySelector('#edit_amount').value = amount;
        modal.querySelector('#edit_date').value = date;
        modal.querySelector('#edit_category').value = category;
    });
});
</script>
</body>
</html>
