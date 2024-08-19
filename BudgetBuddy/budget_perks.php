<?php
session_start();
require_once 'config.php'; // Include database configuration

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['id'] ?? null; // Get user ID from session

if ($user_id) {
    $goal_reached = false; // Initialize variable

    // Fetch user budgets
    $sql = "SELECT id, budget_name, amount, goal_reached FROM budgets WHERE user_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $budgets = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Fetch user transactions
    $sql = "SELECT SUM(amount) as total_spent FROM transactions WHERE user_id = ? AND date BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_spent = $result->fetch_assoc()['total_spent'];
        $stmt->close();
    }

    // Check if budgets have been met and update the status
    foreach ($budgets as $budget) {
        if ($total_spent <= $budget['amount']) {
            $goal_reached = true;
            $reward_message = "Congratulations! You've met your budget goal for " . htmlspecialchars($budget['budget_name']) . ". Enjoy your reward!";
        } else {
            $goal_reached = false;
        }

        // Update goal_reached status in the database
        $sql = "UPDATE budgets SET goal_reached = ? WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ii", $goal_reached, $budget['id']);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Provide a reward or incentive (this is a placeholder; you can customize it)
    if ($goal_reached) {
        $reward_message = "Congratulations! You've achieved your budget goal! Check out your perks!";
    } else {
        $reward_message = "Keep going! You're almost there.";
    }
} else {
    $reward_message = "User ID not found.";
}

$mysqli->close();
?>
