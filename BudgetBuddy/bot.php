<?php
// Replace with your actual API key
$apiKey = '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'; 

// Function to call the OpenAI API
function getAIResponse($message) {
    global $apiKey;
    $url = 'https://api.openai.com/v1/chat/completions';
    
    // Add context to improve the relevance of responses
    $context = "You are BudgetBuddy, an AI assistant that helps users manage their finances effectively. Always provide clear, concise, and practical financial advice.";
    
    $data = array(
        'model' => 'gpt-3.5-turbo',
        'messages' => array(
            array(
                'role' => 'system',
                'content' => $context
            ),
            array(
                'role' => 'user',
                'content' => $message
            )
        ),
        'max_tokens' => 150,
        'temperature' => 0.5, // Adjusted for more coherent responses
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey",
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    if ($response === FALSE) {
        return 'Sorry, I am having trouble processing your request.';
    }
    curl_close($ch);

    $responseData = json_decode($response, true);

    // Check for errors in response data
    if (isset($responseData['error'])) {
        return 'Error: ' . $responseData['error']['message'];
    }

    return $responseData['choices'][0]['message']['content'] ?? 'Sorry, I am having trouble understanding your request.';
}

// Handle POST request
if (isset($_POST['text'])) {
    $userInput = htmlspecialchars($_POST['text']);
    $aiResponse = getAIResponse($userInput);
    echo $aiResponse;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetBuddy AI Chatbot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .chat-container {
            width: 400px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .chat-header {
            background: #007B3A;
            color: #fff;
            padding: 15px;
            text-align: center;
        }
        .chat-box {
            padding: 15px;
            height: 300px;
            overflow-y: auto;
            border-bottom: 1px solid #ddd;
        }
        .chat-box .message {
            margin-bottom: 10px;
        }
        .chat-box .message.bot {
            text-align: left;
        }
        .chat-box .message.user {
            text-align: right;
        }
        .chat-box .message p {
            display: inline-block;
            padding: 10px;
            border-radius: 5px;
        }
        .chat-box .message.bot p {
            background: #007B3A;
            color: #fff;
        }
        .chat-box .message.user p {
            background: #e1e1e1;
        }
        .chat-input {
            display: flex;
            padding: 10px;
            background: #fff;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .chat-input button {
            padding: 10px 15px;
            border: none;
            background: #007B3A;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }
        .chat-input button:hover {
            background: #005a27;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">BudgetBuddy AI Chatbot</div>
        <div class="chat-box" id="chat-box">
            <div class="message bot">
                <p>Hello! How can I assist you with budgeting today?</p>
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="user-input" placeholder="Type your message here..." />
            <button id="send-button">Send</button>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            function appendMessage(role, message) {
                var messageClass = role === 'user' ? 'user' : 'bot';
                $('#chat-box').append('<div class="message ' + messageClass + '"><p>' + message + '</p></div>');
                $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
            }

            $('#send-button').click(function() {
                var userInput = $('#user-input').val();
                if (userInput.trim() !== '') {
                    appendMessage('user', userInput);
                    $('#user-input').val('');

                    // Send the user's message to the server
                    $.ajax({
                        url: '', // This should point to the same PHP file
                        type: 'POST',
                        data: { text: userInput },
                        success: function(response) {
                            appendMessage('bot', response);
                        },
                        error: function(xhr, status, error) {
                            appendMessage('bot', 'Sorry, there was an error processing your request.');
                        }
                    });
                }
            });

            // Allow pressing Enter to send message
            $('#user-input').keypress(function(e) {
                if (e.which == 13) {
                    $('#send-button').click();
                }
            });
        });
    </script>
</body>
</html>
