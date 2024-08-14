<?php
session_start();
require_once 'config.php'; // Include database configuration

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Handle budget deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $budget_id = $_GET['delete'];

    // Prepare a delete statement
    $sql = "DELETE FROM budgets WHERE id = ? AND user_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ii", $budget_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            echo "Budget deleted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle budget update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $budget_id = $_POST['budget_id'];
    $budget_name = $_POST['budget_name'];
    $amount = $_POST['amount'];

    // Prepare an update statement
    $sql = "UPDATE budgets SET budget_name = ?, amount = ? WHERE id = ? AND user_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sdi", $budget_name, $amount, $budget_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            echo "Budget updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all budgets for the logged-in user
$sql = "SELECT id, budget_name, amount FROM budgets WHERE user_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $budgets = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
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
</head>
<body>
<div class="container">
    <h2>Manage Budgets</h2>
    <a href="add_budget.php" class="btn btn-primary mb-3">Add New Budget</a>
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
                        <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $budget['id']; ?>" data-name="<?php echo htmlspecialchars($budget['budget_name']); ?>" data-amount="<?php echo htmlspecialchars($budget['amount']); ?>">Edit</a>
                        <a href="?delete=<?php echo $budget['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this budget?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

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
