<?php
// Include config file
require_once "config.php";

$username = $password = $confirm_password = $email = "";
$username_err = $password_err = $confirm_password_err = $email_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["Email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["Email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["Email"]);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["Email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (email, username, password) VALUES (?, ?, ?)";
         
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $param_email, $param_username, $param_password);
            
            // Set parameters
            $param_email = $email;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                header("location: login.php");
            } else {
                echo "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($link);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
        .signup-container {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Soft shadow for depth */
        }
        h2 {
            color: #00A86B; /* Dark blue */
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007B3A; /* HIPAA blue */
            border-color: #006633; /* Slightly darker blue for border */
        }
        .btn-primary:hover {
            background-color: #005F2E; /* Darker blue on hover */
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
    <img src="data:image/png;base64,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">
</div>
<div class="container">
    <div class="row">
        <div class="col-md-4 offset-md-4 signup-container">
            <h2>Sign Up</h2>
            <p>Please fill this form to create an account.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="Email" class="form-control ">
                    
                </div> 

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div> 

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Submit">    
                </div>
                <p>Already have an account? <a href="login.php">Login here</a>.</p>
            </form>
        </div>
    </div>
</div>    
</body>
</html>
