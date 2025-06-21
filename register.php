<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

try {
    // --- Database Connection ---
    $servername = "localhost";
    $username = "ozbizfin_questuser";
    $password = "yGwPwjOMziXO6L";
    $dbname = "ozbizfin_puzzlepath";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // --- Data Processing ---
    // Get the raw POST data
    $json_data = file_get_contents('php://input');

    // Log the received data for debugging
    error_log("Received JSON data: " . $json_data);

    // Decode the JSON data into a PHP associative array
    $data = json_decode($json_data, true);

    // Check if data is valid
    if ($data === null) {
        throw new Exception("Invalid JSON data received. JSON error: " . json_last_error_msg());
    }

    if (empty($data['password'])) {
        throw new Exception("Password is required.");
    }

    // --- Sanitize and Prepare Data ---
    $firstName = $data['firstName'] ?? '';
    $lastName = $data['lastName'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $ageGroup = $data['ageGroup'] ?? '';
    $groupSize = $data['groupSize'] ?? '';

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email)) {
        throw new Exception("First name, last name, and email are required.");
    }

    // --- Check if user already exists ---
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($checkStmt === false) {
        throw new Exception("Failed to prepare check statement: " . $conn->error);
    }

    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // User already exists
        $checkStmt->close();
        $conn->close();
        
        http_response_code(409); // Conflict - user already exists
        echo json_encode([
            "success" => false,
            "error_code" => "USER_EXISTS",
            "message" => "An account with this email already exists. Please log in instead."
        ]);
        exit;
    }

    $checkStmt->close();

    // --- Securely Hash the Password ---
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

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
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters to the statement
    $stmt->bind_param("ssssssiiisss", $firstName, $lastName, $email, $hashedPassword, $phone, $ageGroup, $groupSize, $newsletter, $marketing, $totalTime, $formattedTime, $medal);

    // Execute the statement
    if ($stmt->execute()) {
        // Return a success response
        echo json_encode([
            "success" => true,
            "message" => "Registration successful"
        ]);
    } else {
        throw new Exception("Database insert failed: " . $stmt->error);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the error
    error_log("Registration error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Registration failed: " . $e->getMessage()
    ]);
}
?> 