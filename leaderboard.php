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

// Fetch leaderboard data
// Count medals for each user and order by medal count (descending)
$query = "
    SELECT 
        first_name,
        last_name,
        LENGTH(medals) - LENGTH(REPLACE(medals, ',', '')) + 1 as medal_count
    FROM users 
    WHERE medals IS NOT NULL AND medals != ''
    ORDER BY medal_count DESC, first_name ASC
    LIMIT 10
";

$result = $conn->query($query);

$leaderboard = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = [
            'name' => $row['first_name'] . ' ' . substr($row['last_name'], 0, 1) . '.', // Show first name and last initial
            'medal_count' => (int)$row['medal_count']
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