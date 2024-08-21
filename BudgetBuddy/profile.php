<?php
// Start session
session_start();

// Include config file
require_once "config.php";

// Initialize variables with empty values
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";

// Get the current user's data from the session
$user_id = $_SESSION['id'];

// Fetch the current user data
$sql = "SELECT username, email FROM users WHERE id = ?";
if ($stmt = mysqli_prepare($mysqli, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $param_id);
    $param_id = $user_id;
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $username, $email);
        mysqli_stmt_fetch($stmt);
    }
    mysqli_stmt_close($stmt);
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username is provided
    if (!empty(trim($_POST["username"]))) {
        $username = trim($_POST["username"]);
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $param_username, $param_id);
            $param_username = $username;
            $param_id = $user_id;
            if (!mysqli_stmt_execute($stmt)) {
                $username_err = "Error updating username.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Check if email is provided
    if (!empty(trim($_POST["email"]))) {
        $email = trim($_POST["email"]);
        $sql = "UPDATE users SET email = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $param_email, $param_id);
            $param_email = $email;
            $param_id = $user_id;
            if (!mysqli_stmt_execute($stmt)) {
                $email_err = "Error updating email.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Check if password is provided
    if (!empty(trim($_POST["password"]))) {
        // Validate new password
        if (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must have at least 6 characters.";
        } else {
            $password = trim($_POST["password"]);
        }

        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm the password.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($password_err) && ($password != $confirm_password)) {
                $confirm_password_err = "Password did not match.";
            }
        }

        // Update password if no errors
        if (empty($password_err) && empty($confirm_password_err)) {
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
                if (!mysqli_stmt_execute($stmt)) {
                    $password_err = "Error updating password.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Close connection
mysqli_close($mysqli);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #004225; 
            font-family: Arial, sans-serif;
        }
        .container {
            padding-top: 50px;
        }
        .profile-container {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #00A86B;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007B3A;
            border-color: #006633;
        }
        .btn-primary:hover {
            background-color: #005F2E;
        }
        .form-control:focus {
            border-color: #007B3A;
            box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 8px rgba(0,86,179,0.6);
        }
        .btn-back {
            background-color: #004225;
            color: #ffffff;
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-back:hover {
            background-color: #00361B;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 profile-container">
                <h2>Edit Profile</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Save Changes">
                    </div>
                    <a href="dashtest.php" class="btn-back">Back</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
