<?php
session_start();
require_once 'config.php'; // Include database configuration

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id']; // Assuming user ID is stored in session
    $query = $_POST['query'];
    
    // For demonstration, we will store the query and show it as "result".
    $sql = "INSERT INTO coaching_requests (user_id, query) VALUES (?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("is", $user_id, $query);
        if ($stmt->execute()) {
            $message = "Your coaching request has been submitted. We will get back to you soon!";
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
    <title>Request Financial Coaching</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Request Financial Coaching</h2>
    <?php if (!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="post" action="request_coaching.php">
        <div class="form-group">
            <label for="query">Your Financial Query</label>
            <textarea class="form-control" id="query" name="query" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Submit Request</button>
    </form>
</div>
</body>
</html>
