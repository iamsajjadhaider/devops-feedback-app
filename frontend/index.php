<?php
// Retrieve the API URL from the environment variable
$api_url = getenv('API_URL');
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback'])) {
    $feedback_text = trim($_POST['feedback']);

    if (!empty($feedback_text)) {
        // Prepare data for the API POST request
        $data = json_encode(['feedback_text' => $feedback_text]);

        $api_endpoint = $api_url . "/index.php";
        
        $ch = curl_init($api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>Connection error: " . htmlspecialchars($curl_error) . "</div>";
        } elseif ($http_code == 200) {
            $result = json_decode($response, true);
            if ($result && isset($result['success']) && $result['success']) {
                $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative' role='alert'>Feedback submitted successfully!</div>";
            } else {
                $error_msg = $result['message'] ?? 'Unknown error from API';
                $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>API Error: " . htmlspecialchars($error_msg) . "</div>";
            }
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>Error submitting feedback. HTTP Code: {$http_code}</div>";
        }
    } else {
        $message = "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative' role='alert'>Feedback cannot be empty.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Feedback Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-xl p-8 bg-white shadow-2xl rounded-xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Submit Your Feedback</h1>
            <a href="admin.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition duration-150">
                Go to Admin Panel &rarr;
            </a>
        </div>

        <?php echo $message; ?>

        <form method="POST" action="" class="space-y-6 mt-4">
            <div>
                <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">Your Thoughts</label>
                <textarea id="feedback" name="feedback" rows="4" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border resize-none"
                    placeholder="Tell us what you think..."></textarea>
            </div>
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                Submit Feedback
            </button>
        </form>
    </div>
</body>
</html>
