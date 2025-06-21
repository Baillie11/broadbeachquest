<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit;
}

// Get user's first name from the session to personalize the page
$firstName = $_SESSION['user_first_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Dashboard - Puzzle Path</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #f4f8fb;
            color: #333;
        }
        .header-bar {
            background: #fff;
            padding: 1em 2em;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-bar .logo {
            font-weight: bold;
            font-size: 1.5em;
            color: #333;
        }
        .header-bar .logo img {
            max-height: 40px;
            vertical-align: middle;
            margin-right: 10px;
        }
        .header-bar a {
            text-decoration: none;
            color: #fff;
            background: #fc5c7d;
            padding: 0.5em 1em;
            border-radius: 5px;
            font-weight: 600;
        }
        .container {
            max-width: 1100px;
            margin: 2em auto;
            padding: 1em;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            color: white;
            padding: 2em;
            border-radius: 15px;
            margin-bottom: 2em;
        }
        h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2em;
        }
        .main-content, .sidebar {
            background: #fff;
            padding: 2em;
            border-radius: 15px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.05);
        }
        h2 {
            border-bottom: 3px solid #f4f8fb;
            padding-bottom: 0.5em;
            margin-top: 0;
        }

        /* Styles will go here for medals and leaderboard */

    </style>
</head>
<body>
    <div class="header-bar">
        <div class="logo">
            <img src="puzzlepath-logo-web.png" alt="Puzzle Path Logo">
            Puzzle Path
        </div>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($firstName); ?>!</h1>
            <p>Here are your earned medals and the current quest leaderboard.</p>
        </div>

        <div class="content-grid">
            <div class="main-content">
                <h2>Your Medals</h2>
                <!-- Medal display will go here -->
                <p>Your earned medals will appear here soon!</p>
            </div>
            <div class="sidebar">
                <h2>Leaderboard</h2>
                <!-- Leaderboard will go here -->
                <p>The leaderboard is coming soon!</p>
            </div>
        </div>
    </div>
</body>
</html> 