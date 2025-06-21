<?php
// --- Database Connection ---
// Replace with your actual database credentials from Orange Host
$servername = "localhost";
$username = "ozbizfin_questuser";
$password = "yGwPwjOMziXO6L";
$dbname = "ozbizfin_puzzlepath";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Stop script and return a server error
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}


// --- Data Processing ---

// Get the raw POST data
$json_data = file_get_contents('php://input');

// Decode the JSON data into a PHP associative array
$data = json_decode($json_data, true);

// Check if data is valid
if ($data === null || empty($data['password'])) {
    http_response_code(400); // Bad Request
    die("Invalid JSON data received or password is empty.");
}

// --- Securely Hash the Password ---
$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

// --- Sanitize and Prepare Data ---
// Extract data from the array, using null coalescing operator for safety
$firstName = $data['firstName'] ?? '';
$lastName = $data['lastName'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$ageGroup = $data['ageGroup'] ?? '';
$groupSize = $data['groupSize'] ?? '';

// Convert boolean values to integers (1 for true, 0 for false)
$newsletter = isset($data['newsletter']) && $data['newsletter'] ? 1 : 0;
$marketing = isset($data['marketing']) && $data['marketing'] ? 1 : 0;
$medal = 'broadbeach-quest'; // The first medal they've earned

// Get completion stats
$completionData = $data['completionData'] ?? [];
$totalTime = $completionData['totalTime'] ?? 0; // Stored in milliseconds
$formattedTime = $completionData['formattedTime'] ?? '00:00:00';


// --- SQL Execution ---

// Prepare an SQL statement to prevent SQL injection
$stmt = $conn->prepare(
    "INSERT INTO users (first_name, last_name, email, password, phone_number, age_group, group_size, newsletter_subscribed, marketing_subscribed, total_time_ms, formatted_time, medals) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

// Check if the statement was prepared successfully
if ($stmt === false) {
    http_response_code(500);
    die("Failed to prepare statement: " . $conn->error);
}

// Bind parameters to the statement
// s = string, i = integer
$stmt->bind_param("ssssssiiisss", $firstName, $lastName, $email, $hashedPassword, $phone, $ageGroup, $groupSize, $newsletter, $marketing, $totalTime, $formattedTime, $medal);

// Execute the statement
if ($stmt->execute()) {
    // Return a success response
    http_response_code(200);
    echo json_encode(["message" => "New record created successfully"]);
} else {
    // Return a server error
    http_response_code(500);
    echo json_encode(["message" => "Error: " . $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?> 