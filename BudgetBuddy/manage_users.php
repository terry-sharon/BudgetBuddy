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

// Handle Edit User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($email)) {
        $error_message .= "Username and Email are required.<br>";
    } else {
        $sql_update_user = "UPDATE users SET username = ?, email = ?, role = ?" . (!empty($password) ? ", password = ?" : "") . " WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql_update_user)) {
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bind_param("sssi", $username, $email, $role, $user_id);
                $stmt->bind_param("ssss", $username, $email, $role, $password_hash, $user_id);
            } else {
                $stmt->bind_param("ssi", $username, $email, $role, $user_id);
            }
            if ($stmt->execute()) {
                $success_message .= "User updated successfully.<br>";
            } else {
                $error_message .= "Error updating user: " . $mysqli->error . "<br>";
            }
            $stmt->close();
        } else {
            $error_message .= "Error preparing statement: " . $mysqli->error . "<br>";
        }
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    $sql_delete_user = "DELETE FROM users WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql_delete_user)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success_message .= "User deleted successfully.<br>";
        } else {
            $error_message .= "Error deleting user: " . $mysqli->error . "<br>";
        }
        $stmt->close();
    } else {
        $error_message .= "Error preparing statement: " . $mysqli->error . "<br>";
    }
}

// Fetch all users
$all_users = [];
$sql_users = "SELECT id, username, email, role FROM users";
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

$mysqli->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Manage Users</title>
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
        <h1>Manage Users</h1>

        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

        <?php if (!empty($success_message)) : ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Users Table -->
        <h2 class="mt-4">Users List</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($all_users)) : ?>
                    <?php foreach ($all_users as $user) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?delete=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
