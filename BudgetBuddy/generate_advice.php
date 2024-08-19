<?php
require 'vendor/autoload.php'; // If using Composer for dependencies

use GuzzleHttp\Client;

// Function to get financial advice from AI
function getFinancialAdvice($userQuery) {
    // Your OpenAI API key
    $apiKey = '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';

    // API endpoint for OpenAI
    $url = 'https://api.openai.com/v1/completions';

    // Initialize the HTTP client
    $client = new Client();

    // Make a request to the OpenAI API
    try {
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'text-davinci-003', // Use the appropriate model
                'prompt' => 'Provide personalized financial advice based on the following query: ' . $userQuery,
                'max_tokens' => 150,
            ],
        ]);

        // Decode the JSON response
        $responseBody = json_decode($response->getBody(), true);
        return $responseBody['choices'][0]['text'];
    } catch (\Exception $e) {
        // Handle exceptions
        return 'Error: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userQuery = $_POST['query'];
    $advice = getFinancialAdvice($userQuery);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Coaching</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Financial Coaching</h2>
    <form method="post" action="">
        <div class="form-group mb-3">
            <label for="query">Your Financial Query</label>
            <textarea class="form-control" id="query" name="query" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Get Advice</button>
    </form>
    <?php if (isset($advice)): ?>
        <div class="mt-4">
            <h4>Advice:</h4>
            <p><?php echo htmlspecialchars($advice); ?></p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
