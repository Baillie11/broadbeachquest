<?php
// Start a new session or resume the existing one
session_start();

// --- Database Connection ---
// These credentials should be the same as in your register.php
$servername = "localhost";
$username = "ozbizfin_questuser";
$password = "yGwPwjOMziXO6L";
$dbname = "ozbizfin_puzzlepath";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // It's better to send a generic error message to the user
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection error."]);
    exit();
}

// --- Data Processing ---

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Email and password are required."]);
    exit();
}

// --- User Verification ---

// Prepare a statement to find the user by email
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, medals FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // User found, now verify the password
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        // Password is correct, login successful

        // Store user data in the session, but not the password!
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['logged_in'] = true;

        // Send a success response back to the JavaScript
        echo json_encode(["success" => true, "message" => "Login successful!"]);
    } else {
        // Invalid password
        echo json_encode(["success" => false, "message" => "Invalid email or password."]);
    }
} else {
    // No user found with that email
    echo json_encode(["success" => false, "message" => "Invalid email or password."]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?> 