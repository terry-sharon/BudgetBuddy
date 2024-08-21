<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

$user_id = $_GET['id'] ?? null;
$success_message = '';
$error_message = '';

if ($user_id) {
    // Fetch user details
    $sql = "SELECT id, username, email, role FROM users WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
    } else {
        $error_message .= "Error fetching user: " . $mysqli->error . "<br>";
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = trim($_POST['role']);

        if (empty($username) || empty($email)) {
            $error_message .= "Username and Email are required.<br>";
        } else {
            $sql_update_user = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql_update_user)) {
                $stmt->bind_param("sssi", $username, $email, $role, $user_id);
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
} else {
    $error_message .= "Invalid user ID.<br>";
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Edit User</title>
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
            max-width: 800px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        h1 {
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
        .btn-secondary {
            background-color: #004d2d;
            border-color: #003d1f;
        }
        .btn-secondary:hover {
            background-color: #003d1f;
            border-color: #002b14;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit User</h1>

        <?php if (!empty($success_message)) : ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($user) : ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . htmlspecialchars($user_id); ?>" method="post">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                <a href="manage_users.php" class="btn btn-secondary">Back to Users</a>
            </form>
        <?php else : ?>
            <p>No user found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
