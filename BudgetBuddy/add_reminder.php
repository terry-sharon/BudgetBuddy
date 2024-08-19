<?php
session_start();
require_once 'config.php'; // Include database configuration

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id']; // Assuming user ID is stored in session
    $title = $_POST['title'];
    $details = $_POST['details'];
    $reminder_date = $_POST['reminder_date'];

    $sql = "INSERT INTO reminders (user_id, title, details, reminder_date) VALUES (?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("isss", $user_id, $title, $details, $reminder_date);
        if ($stmt->execute()) {
            $message = "Reminder added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reminder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Add Reminder</h2>
    <?php if (!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="post" action="add_reminder.php">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="details">Details</label>
            <textarea class="form-control" id="details" name="details" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="reminder_date">Reminder Date</label>
            <input type="date" class="form-control" id="reminder_date" name="reminder_date" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add Reminder</button>
         <button type="submit" class="btn btn-primary mt-3">Manage  Reminder</button>
    </form>
</div>
</body>
</html>
