<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Database connection
$servername = "localhost";
$username = "ozbizfin_questuser";
$password = "yGwPwjOMziXO6L";
$dbname = "ozbizfin_puzzlepath";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Fetch speed leaderboard data
// Get users who have completed the Broadbeach Quest (have the medal) and order by completion time (ascending)
$query = "
    SELECT 
        first_name,
        last_name,
        total_time_ms,
        formatted_time,
        DATE(created_at) as completion_date
    FROM users 
    WHERE medals LIKE '%broadbeach-quest%' 
    AND total_time_ms > 0
    ORDER BY total_time_ms ASC
    LIMIT 10
";

$result = $conn->query($query);

$leaderboard = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = [
            'name' => $row['first_name'] . ' ' . substr($row['last_name'], 0, 1) . '.', // Show first name and last initial
            'time' => $row['formatted_time'],
            'time_ms' => (int)$row['total_time_ms'],
            'date' => $row['completion_date']
        ];
    }
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    "success" => true,
    "leaderboard" => $leaderboard
]);
?> 