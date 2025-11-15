<?php
// Add CORS headers to allow frontend to communicate with API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set headers for JSON response
header('Content-Type: application/json');
date_default_timezone_set('UTC');

// ------------------------------------
// 1. Database Connection (Using Env Vars)
// ------------------------------------
$host = getenv('DB_HOST'); // Should be 'db' (the service name in docker-compose)
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Test the connection
    $pdo->query("SELECT 1");
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(503); // Service Unavailable
    echo json_encode(['success' => false, 'message' => "Database connection error: " . $e->getMessage()]);
    exit;
}

// ------------------------------------
// 2. Request Handling
// ------------------------------------
$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? '';

// Log incoming requests for debugging
error_log("API Request: Method=$request_method, Action=$action, URI=" . $_SERVER['REQUEST_URI']);

switch ($request_method) {
    case 'POST':
        handle_post_request();
        break;
    case 'GET':
        handle_get_request($action);
        break;
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => "Method {$request_method} not supported."]);
        break;
}

// ------------------------------------
// 3. API Functions
// ------------------------------------

function handle_post_request() {
    global $pdo;

    // Get the raw POST data
    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);

    // Log received data for debugging
    error_log("Received POST data: " . $raw_input);

    if ($data === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data received.']);
        return;
    }

    // Check if we have the required fields
    if (empty($data['feedback_text'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Feedback text is required.']);
        return;
    }

    $feedback_text = trim($data['feedback_text']);

    try {
        $stmt = $pdo->prepare("INSERT INTO feedback (feedback_text, status) VALUES (?, 'new')");
        $stmt->execute([$feedback_text]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Feedback submitted successfully.', 
            'id' => $pdo->lastInsertId()
        ]);
        
        error_log("Feedback inserted successfully with ID: " . $pdo->lastInsertId());
    } catch (\Exception $e) {
        error_log("Database insert failed: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to submit feedback: ' . $e->getMessage()]);
    }
}

function handle_get_request($action) {
    global $pdo;

    if ($action !== 'list') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid GET action. Use action=list']);
        return;
    }

    // Filtration and Sorting Logic
    $status = $_GET['status'] ?? '';
    $sort = $_GET['sort'] ?? 'newest';

    $where_clauses = [];
    $params = [];

    // 1. Status Filter
    $valid_statuses = ['new', 'in-progress', 'done'];
    if (!empty($status) && in_array($status, $valid_statuses)) {
        $where_clauses[] = "status = ?";
        $params[] = $status;
    }

    // 2. Sorting
    $order_by = 'created_at DESC'; // Default: newest
    if ($sort === 'oldest') {
        $order_by = 'created_at ASC';
    }

    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";
    $sql = "SELECT id, feedback_text, status, created_at FROM feedback {$where_sql} ORDER BY {$order_by}";

    try {
        error_log("Executing SQL: " . $sql . " with params: " . json_encode($params));
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'data' => $results,
            'count' => count($results)
        ]);
        
        error_log("Retrieved " . count($results) . " feedback entries");
    } catch (\Exception $e) {
        error_log("Database query failed: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve feedback: ' . $e->getMessage()]);
    }
}
