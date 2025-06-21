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
$userId = $_SESSION['user_id'] ?? 0;

// --- Database Connection ---
$servername = "localhost";
$username = "ozbizfin_questuser";
$password = "yGwPwjOMziXO6L";
$dbname = "ozbizfin_puzzlepath";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user's medals
$stmt = $conn->prepare("SELECT medals FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Parse medals (assuming they're stored as comma-separated values)
$medals = [];
if ($user && $user['medals']) {
    $medals = explode(',', $user['medals']);
    $medals = array_map('trim', $medals); // Remove any whitespace
}

$stmt->close();
$conn->close();

// Medal data - you can expand this with more medals
$medalData = [
    'broadbeach-quest' => [
        'name' => 'Broadbeach Quest',
        'description' => 'Completed the Broadbeach puzzle quest',
        'image' => 'Broadbeach Medal.png'
    ]
    // Add more medals here as they become available
];
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

        /* Medal Styles */
        .medals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5em;
            margin-top: 1.5em;
        }
        .medal-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1.5em;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .medal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #fc5c7d;
        }
        .medal-card.earned {
            background: linear-gradient(145deg, #fff3cd 0%, #ffeaa7 100%);
            border-color: #ffc107;
        }
        .medal-card.earned::before {
            content: '🏆';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5em;
        }
        .medal-image {
            width: 80px;
            height: 80px;
            margin: 0 auto 1em;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2em;
        }
        .medal-card.earned .medal-image {
            background: linear-gradient(145deg, #ffd700 0%, #ffed4e 100%);
        }
        .medal-name {
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 0.5em;
            color: #333;
        }
        .medal-description {
            font-size: 0.9em;
            color: #666;
            line-height: 1.4;
        }
        .no-medals {
            text-align: center;
            padding: 2em;
            color: #666;
        }
        .no-medals img {
            width: 100px;
            opacity: 0.3;
            margin-bottom: 1em;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            .medals-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Leaderboard Styles */
        .leaderboard-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8em;
            margin-bottom: 0.5em;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .leaderboard-item:hover {
            background: #e9ecef;
        }
        .leaderboard-item.current-user {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
        }
        .rank {
            font-weight: bold;
            color: #fc5c7d;
            min-width: 30px;
        }
        .rank.rank-1 { color: #ffd700; }
        .rank.rank-2 { color: #c0c0c0; }
        .rank.rank-3 { color: #cd7f32; }
        .user-name {
            flex: 1;
            margin-left: 1em;
        }
        .medal-count {
            background: #fc5c7d;
            color: white;
            padding: 0.3em 0.6em;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .loading {
            text-align: center;
            padding: 2em;
            color: #666;
        }
        .no-data {
            text-align: center;
            padding: 2em;
            color: #666;
        }
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
                <?php if (empty($medals)): ?>
                    <div class="no-medals">
                        <img src="puzzlepath-logo-web.png" alt="No medals yet">
                        <h3>No medals yet!</h3>
                        <p>Complete quests to earn your first medal.</p>
                        <a href="index.html" style="display: inline-block; background: #fc5c7d; color: white; padding: 0.8em 1.5em; text-decoration: none; border-radius: 8px; margin-top: 1em;">Start a Quest</a>
                    </div>
                <?php else: ?>
                    <div class="medals-grid">
                        <?php foreach ($medalData as $medalKey => $medal): ?>
                            <div class="medal-card <?php echo in_array($medalKey, $medals) ? 'earned' : ''; ?>">
                                <div class="medal-image">
                                    <?php if (in_array($medalKey, $medals) && isset($medal['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($medal['image']); ?>" alt="<?php echo htmlspecialchars($medal['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        🏅
                                    <?php endif; ?>
                                </div>
                                <div class="medal-name"><?php echo htmlspecialchars($medal['name']); ?></div>
                                <div class="medal-description"><?php echo htmlspecialchars($medal['description']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="sidebar">
                <h2>Leaderboard</h2>
                <div id="leaderboardContainer">
                    <div class="loading">Loading leaderboard...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load leaderboard data
        async function loadLeaderboard() {
            const container = document.getElementById('leaderboardContainer');
            
            try {
                const response = await fetch('leaderboard.php');
                const data = await response.json();
                
                if (data.success && data.leaderboard.length > 0) {
                    let html = '';
                    data.leaderboard.forEach((user, index) => {
                        const rank = index + 1;
                        const rankClass = rank <= 3 ? `rank-${rank}` : '';
                        const isCurrentUser = user.name.includes('<?php echo htmlspecialchars($firstName); ?>');
                        
                        html += `
                            <div class="leaderboard-item ${isCurrentUser ? 'current-user' : ''}">
                                <div class="rank ${rankClass}">#${rank}</div>
                                <div class="user-name">${user.name}</div>
                                <div class="medal-count">${user.medal_count} medal${user.medal_count !== 1 ? 's' : ''}</div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="no-data">No leaderboard data available yet.</div>';
                }
            } catch (error) {
                console.error('Error loading leaderboard:', error);
                container.innerHTML = '<div class="no-data">Error loading leaderboard.</div>';
            }
        }

        // Load leaderboard when page loads
        document.addEventListener('DOMContentLoaded', loadLeaderboard);
    </script>
</body>
</html> 