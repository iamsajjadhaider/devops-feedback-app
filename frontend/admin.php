<?php
// Retrieve the API URL from the environment variable
$api_url = getenv('API_URL') . "/index.php";

$filter_status = $_GET['status'] ?? '';
$filter_sort = $_GET['sort'] ?? 'newest';

// Construct the API query URL with filters
$query_params = http_build_query([
    'action' => 'list',
    'status' => $filter_status,
    'sort' => $filter_sort
]);

$api_full_url = "{$api_url}?{$query_params}";

$feedback_data = [];
$error_message = null;

// Call the API
$ch = curl_init($api_full_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $result = json_decode($response, true);
    if (isset($result['success']) && $result['success']) {
        $feedback_data = $result['data'];
    } else {
        $error_message = "API Error: " . ($result['message'] ?? 'Unknown API error.');
    }
} else {
    $error_message = "Failed to connect to API service. HTTP Code: {$http_code}";
}

// Function to determine badge color and icon
function get_status_badge_class($status) {
    switch ($status) {
        case 'done': 
            return 'bg-green-100 text-green-800 border-green-200';
        case 'in-progress': 
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'new': 
        default: 
            return 'bg-blue-100 text-blue-800 border-blue-200';
    }
}

function get_status_icon($status) {
    switch ($status) {
        case 'done': 
            return '✅';
        case 'in-progress': 
            return '🔄';
        case 'new': 
        default: 
            return '🆕';
    }
}

function get_time_ago($datetime) {
    $time = strtotime($datetime);
    $time_difference = time() - $time;

    if ($time_difference < 1) { return 'just now'; }
    $condition = array( 
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60       => 'month',
        24 * 60 * 60            => 'day',
        60 * 60                 => 'hour',
        60                      => 'minute',
        1                       => 'second'
    );

    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;
        if ($d >= 1) {
            $t = round($d);
            return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Feedback Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .feedback-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        .feedback-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .status-new { border-left-color: #3b82f6; }
        .status-in-progress { border-left-color: #f59e0b; }
        .status-done { border-left-color: #10b981; }
    </style>
</head>
<body class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="glass-card rounded-2xl shadow-2xl p-8 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-4 mb-4 lg:mb-0">
                    <div class="bg-indigo-600 p-3 rounded-xl shadow-lg">
                        <i class="fas fa-comments text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Feedback Dashboard</h1>
                        <p class="text-gray-600 mt-1">Manage and review user feedback</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="index.php" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 shadow-lg hover:shadow-xl">
                        <i class="fas fa-plus mr-2"></i>
                        New Feedback
                    </a>
                    <div class="bg-indigo-50 rounded-xl px-4 py-3 border border-indigo-100">
                        <div class="text-sm font-medium text-indigo-800">Total Feedback</div>
                        <div class="text-2xl font-bold text-indigo-600"><?php echo count($feedback_data); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class='glass-card rounded-2xl border border-red-200 p-6 mb-6'>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    <div>
                        <h3 class='text-lg font-semibold text-red-800'>Connection Error</h3>
                        <p class='text-red-600 mt-1'><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filters Card -->
        <div class="glass-card rounded-2xl shadow-xl p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-filter mr-2 text-indigo-600"></i>
                Filter & Sort Feedback
            </h2>
            <form method="GET" action="admin.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                    <select id="status" name="status"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition duration-150">
                        <option value="" <?php if ($filter_status === '') echo 'selected'; ?>>All Statuses</option>
                        <option value="new" <?php if ($filter_status === 'new') echo 'selected'; ?>>🆕 New</option>
                        <option value="in-progress" <?php if ($filter_status === 'in-progress') echo 'selected'; ?>>🔄 In Progress</option>
                        <option value="done" <?php if ($filter_status === 'done') echo 'selected'; ?>>✅ Done</option>
                    </select>
                </div>
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sort" name="sort"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition duration-150">
                        <option value="newest" <?php if ($filter_sort === 'newest') echo 'selected'; ?>>📅 Newest First</option>
                        <option value="oldest" <?php if ($filter_sort === 'oldest') echo 'selected'; ?>>📅 Oldest First</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full md:w-auto py-3 px-8 border border-transparent rounded-xl shadow-lg text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 transform hover:scale-105">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Feedback List -->
        <div class="space-y-4">
            <?php if (empty($feedback_data)): ?>
                <div class="glass-card rounded-2xl p-12 text-center">
                    <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-600 mb-2">No Feedback Found</h3>
                    <p class="text-gray-500 mb-6">No feedback matches your current filter criteria.</p>
                    <a href="admin.php" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
                        <i class="fas fa-refresh mr-2"></i>
                        Clear Filters
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($feedback_data as $feedback): ?>
                    <div class="feedback-card glass-card rounded-2xl p-6 shadow-lg status-<?php echo $feedback['status']; ?>">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex-1">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center shadow-inner">
                                            <i class="fas fa-user text-indigo-600 text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-lg text-gray-800 font-medium leading-relaxed">
                                            <?php echo htmlspecialchars($feedback['feedback_text']); ?>
                                        </p>
                                        <div class="flex flex-wrap items-center gap-3 mt-3">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border <?php echo get_status_badge_class($feedback['status']); ?>">
                                                <?php echo get_status_icon($feedback['status']); ?>
                                                <span class="ml-1"><?php echo ucwords(htmlspecialchars($feedback['status'])); ?></span>
                                            </span>
                                            <span class="text-sm text-gray-500 flex items-center">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php echo get_time_ago($feedback['created_at']); ?>
                                            </span>
                                            <span class="text-sm text-gray-500 flex items-center">
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?>
                                            </span>
                                            <span class="text-sm text-gray-500 flex items-center">
                                                <i class="fas fa-hashtag mr-1"></i>
                                                ID: <?php echo htmlspecialchars($feedback['id']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer Stats -->
        <?php if (!empty($feedback_data)): ?>
            <div class="glass-card rounded-2xl p-6 mt-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                        <div class="text-2xl font-bold text-blue-600">
                            <?php echo count(array_filter($feedback_data, fn($f) => $f['status'] === 'new')); ?>
                        </div>
                        <div class="text-sm font-medium text-blue-800">New</div>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100">
                        <div class="text-2xl font-bold text-yellow-600">
                            <?php echo count(array_filter($feedback_data, fn($f) => $f['status'] === 'in-progress')); ?>
                        </div>
                        <div class="text-sm font-medium text-yellow-800">In Progress</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                        <div class="text-2xl font-bold text-green-600">
                            <?php echo count(array_filter($feedback_data, fn($f) => $f['status'] === 'done')); ?>
                        </div>
                        <div class="text-sm font-medium text-green-800">Completed</div>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100">
                        <div class="text-2xl font-bold text-indigo-600"><?php echo count($feedback_data); ?></div>
                        <div class="text-sm font-medium text-indigo-800">Total</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Floating Action Button for Mobile -->
    <a href="index.php" 
       class="lg:hidden fixed bottom-6 right-6 w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-2xl flex items-center justify-center transition duration-150 transform hover:scale-110 z-50">
        <i class="fas fa-plus text-xl"></i>
    </a>
</body>
</html>
