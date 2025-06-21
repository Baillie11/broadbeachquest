<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "ozbizfin_questuser";
$password = "yGwPwjOMziXO6L";
$dbname = "ozbizfin_puzzlepath";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<h2>Database Connection: SUCCESS</h2>";

    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        throw new Exception("Users table does not exist!");
    }

    echo "<h2>Users Table: EXISTS</h2>";

    // Get table structure
    $result = $conn->query("DESCRIBE users");
    
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Check for required columns
    $required_columns = [
        'id', 'first_name', 'last_name', 'email', 'password', 
        'phone_number', 'age_group', 'group_size', 
        'newsletter_subscribed', 'marketing_subscribed', 
        'total_time_ms', 'formatted_time', 'medals'
    ];

    $result = $conn->query("DESCRIBE users");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }

    echo "<h3>Column Check:</h3>";
    foreach ($required_columns as $column) {
        if (in_array($column, $existing_columns)) {
            echo "✅ $column: EXISTS<br>";
        } else {
            echo "❌ $column: MISSING<br>";
        }
    }

    $conn->close();

} catch (Exception $e) {
    echo "<h2>ERROR: " . $e->getMessage() . "</h2>";
}
?> 