<?php
session_start();
require_once 'config.php'; // Include database configuration

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['id']; // Assuming user ID is stored in session

$sql = "SELECT id, title, details, reminder_date FROM reminders WHERE user_id = ? ORDER BY reminder_date DESC";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reminders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reminders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Your Reminders</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Details</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reminders)): ?>
                <tr><td colspan="3" class="text-center">No reminders found.</td></tr>
            <?php else: ?>
                <?php foreach ($reminders as $reminder): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reminder['title']); ?></td>
                        <td><?php echo htmlspecialchars($reminder['details']); ?></td>
                        <td><?php echo htmlspecialchars(date('F j, Y', strtotime($reminder['reminder_date']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
