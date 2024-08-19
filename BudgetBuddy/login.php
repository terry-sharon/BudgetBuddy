<?php
// Start session
session_start();

// Check if the user is already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashtest.php");
    exit;
}

// Include config file
require_once "config.php";

$username = $password = "";
$username_err = $password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so regenerate session ID to prevent fixation attacks
                            session_regenerate_id(true);
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Redirect user to dashboardtest.php
                            header("location: dashtest.php");
                            exit;
                        } else {
                            // Display an error message if password is not valid
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($mysqli); // Ensure this matches your config variable
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #004225; 
            font-family: Arial, sans-serif;
        }
        div {
            text-align: center;
        }
        .container {
            padding-top: 50px;
        }
        .login-container {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Soft shadow for depth */
        }
        h2 {
            color: #00A86B; /* Dark green */
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007B3A; /* Dark green */
            border-color: #006633; /* Slightly darker green for border */
        }
        .btn-primary:hover {
            background-color: #005F2E; /* Darker green on hover */
        }
        .form-control:focus {
            border-color: #007B3A;
            box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 8px rgba(0,86,179,0.6);
        }
        .invalid-feedback {
            display: block; /* Ensure visibility */
        }
        a {
            color: #007B3A; /* Match the button color for consistency */
        }
    </style>
</head>
<body>
    <div>
        <img src="budgetlogo.png" alt="BudgetBuddy Logo">
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 login-container">
                <h2>BudgetBuddy</h2>
                <p>Please fill in your credentials to login.</p>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Login">
                    </div>
                    <p>New user? <a href="register.php">Register here</a>.</p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
