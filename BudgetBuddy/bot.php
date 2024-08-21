<?php
// connecting to database
$conn = mysqli_connect("localhost", "root", "", "budgetbuddy") or die("Database Error");

// Check if 'text' is set in the POST request
if (isset($_POST['text'])) {
    // getting user message through ajax
    $getMesg = mysqli_real_escape_string($conn, $_POST['text']);

    // checking user query to database query
    $check_data = "SELECT replies FROM chatbot WHERE queries LIKE '%$getMesg%'";
    $run_query = mysqli_query($conn, $check_data) or die("Error");

    // if user query matched to database query we'll show the reply otherwise it go to else statement
    if (mysqli_num_rows($run_query) > 0) {
        // fetching reply from the database according to the user query
        $fetch_data = mysqli_fetch_assoc($run_query);
        // storing reply to a variable which we'll send to ajax
        $reply = $fetch_data['replies'];
        echo $reply;
    } else {
        echo "Sorry, I can't understand you!";
    }
} else {
    echo "No input received!";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Somehow I got an error, so I comment the title, just uncomment to show -->
    <!-- <title>Online Chatbot in PHP | CampCodes</title> -->
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}
html, body {
    display: grid;
    height: 100%;
    place-items: center;
}
::selection {
    color: #fff;
    background: #007bff;
}
::-webkit-scrollbar {
    width: 3px;
    border-radius: 25px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
    background: #ddd;
}
::-webkit-scrollbar-thumb:hover {
    background: #ccc;
}
.wrapper {
    width: 100%;
    max-width: 370px;
    background: #fff;
    border-radius: 5px;
    border: 1px solid lightgrey;
    border-top: 0px;
}
.wrapper .title {
    background: #007bff;
    color: #fff;
    font-size: 20px;
    font-weight: 500;
    line-height: 60px;
    text-align: center;
    border-bottom: 1px solid #006fe6;
    border-radius: 5px 5px 0 0;
}
.wrapper .form {
    padding: 20px 15px;
    min-height: 400px;
    max-height: 400px;
    overflow-y: auto;
}
.wrapper .form .inbox {
    width: 100%;
    display: flex;
    align-items: baseline;
}
.wrapper .form .user-inbox {
    justify-content: flex-end;
    margin: 13px 0;
}
.wrapper .form .inbox .icon {
    height: 40px;
    width: 40px;
    color: #fff;
    text-align: center;
    line-height: 40px;
    border-radius: 50%;
    font-size: 18px;
    background: #007bff;
}
.wrapper .form .inbox .msg-header {
    max-width: 80%; /* Adjusted for responsiveness */
    margin-left: 10px;
}
.form .inbox .msg-header p {
    color: #fff;
    background: #007bff;
    border-radius: 10px;
    padding: 8px 10px;
    font-size: 14px;
    word-break: break-all;
}
.form .user-inbox .msg-header p {
    color: #333;
    background: #efefef;
}
.wrapper .typing-field {
    display: flex;
    height: 60px;
    width: 100%;
    align-items: center;
    justify-content: space-evenly;
    background: #efefef;
    border-top: 1px solid #d9d9d9;
    border-radius: 0 0 5px 5px;
}
.wrapper .typing-field .input-data {
    height: 40px;
    width: 100%;
    position: relative;
}
.wrapper .typing-field .input-data input {
    height: 100%;
    width: calc(100% - 80px); /* Adjusted for responsiveness */
    outline: none;
    border: 1px solid transparent;
    padding: 0 80px 0 15px;
    border-radius: 3px;
    font-size: 15px;
    background: #fff;
    transition: all 0.3s ease;
}
.typing-field .input-data input:focus {
    border-color: rgba(0,123,255,0.8);
}
.input-data input::placeholder {
    color: #999999;
    transition: all 0.3s ease;
}
.input-data input:focus::placeholder {
    color: #bfbfbf;
}
.wrapper .typing-field .input-data button {
    position: absolute;
    right: 10px;
    top: 50%;
    height: 30px;
    width: 65px;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    outline: none;
    opacity: 0;
    pointer-events: none;
    border-radius: 3px;
    background: #007bff;
    border: 1px solid #007bff;
    transform: translateY(-50%);
    transition: all 0.3s ease;
}
.wrapper .typing-field .input-data input:valid ~ button {
    opacity: 1;
    pointer-events: auto;
}
.typing-field .input-data button:hover {
    background: #006fef;
}

/* Responsive styles */
@media (max-width: 600px) {
    .wrapper {
        max-width: 100%;
        margin: 10px;
    }
    .wrapper .title {
        font-size: 18px;
        line-height: 50px;
    }
    .wrapper .form .inbox .msg-header {
        max-width: 85%;
    }
    .wrapper .typing-field .input-data input {
        width: calc(100% - 75px); /* Adjusted for responsiveness */
    }
    .wrapper .typing-field .input-data button {
        width: 60px;
    }
}

    </style>
</head>
<body>
    <div class="wrapper">
        <div class="title">Simple Online Chatbot</div>
        <div class="form">
            <div class="bot-inbox inbox">
                <div class="icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="msg-header">
                    <p>Hello there, how can I help you?</p>
                </div>
            </div>
        </div>
        <div class="typing-field">
            <div class="input-data">
                <input id="data" type="text" placeholder="Type something here.." required>
                <button id="send-btn">Send</button>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $("#send-btn").on("click", function(){
                $value = $("#data").val();
                $msg = '<div class="user-inbox inbox"><div class="msg-header"><p>'+ $value +'</p></div></div>';
                $(".form").append($msg);
                $("#data").val('');
                
                // start ajax code
                $.ajax({
                    url: 'message.php',
                    type: 'POST',
                    data: 'text='+$value,
                    success: function(result){
                        $replay = '<div class="bot-inbox inbox"><div class="icon"><i class="fas fa-user"></i></div><div class="msg-header"><p>'+ result +'</p></div></div>';
                        $(".form").append($replay);
                        // when chat goes down the scroll bar automatically comes to the bottom
                        $(".form").scrollTop($(".form")[0].scrollHeight);
                    }
                });
            });
        });
    </script>
    
</body>
</html>