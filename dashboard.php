<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}
if ($_SESSION["role"] === "admin") {
    header("Location: /govsecure/admin/index.php");
    exit;
}

require_once "config.php";
?>

<?php


session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GovSecure Chat - Dashboard</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <div class="dashboard-container">

        <!-- HEADER -->
        <div class="glass-panel dashboard-header">

            <div>
                <h1>GovSecure Chat</h1>
                <p>Secure Government Communication</p>
            </div>

            <div class="user-section">

                <div class="user-info">
                    <strong>
                        <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($_SESSION["role"]); ?>
                    </span>
                </div>

                <a href="auth/logout.php" class="logout-button">
                    Logout
                </a>

            </div>

        </div>


        <!-- WELCOME -->
        <div class="welcome-section">

            <h2>
                Welcome,
                <?php echo htmlspecialchars($_SESSION["full_name"]); ?> 👋
            </h2>

            <p>
                Manage your secure government communications from here.
            </p>

        </div>


        <!-- DASHBOARD CARDS -->
        <div class="dashboard-grid">

            <!-- MESSAGES -->
            <div class="glass-panel dashboard-card">

                <div class="card-icon">💬</div>

                <h3>Secure Messages</h3>

                <p>
                    Send and receive secure messages
                    with authorized government staff.
                </p>

                <a href="messages.php" class="card-button">
                    Open Messages
            </a>

            </div>


            <!-- FILES -->
            <div class="glass-panel dashboard-card">

                <div class="card-icon">📁</div>

                <h3>Confidential Files</h3>

                <p>
                    Upload, share and access confidential
                    government documents securely.
                </p>

                <a href="files.php" class="card-button">
                    View Files
            </a>

            </div>


            <!-- NOTIFICATIONS -->
            <div class="glass-panel dashboard-card">

                <div class="card-icon">🔔</div>

                <h3>Notifications</h3>

                <p>
                    View important messages, alerts
                    and security notifications.
                </p>

                <a href="notifications.php" class="card-button">
                    View Notifications
            </a>

            </div>


            <!-- PROFILE -->
            <div class="glass-panel dashboard-card">

                <div class="card-icon">👤</div>

                <h3>My Profile</h3>

                <p>
                    View your account information,
                    department and role.
                </p>

                <a href="profile.php" class="card-button">
                    View Profile
            </a>

            </div>

        </div>


        <!-- SECURITY STATUS -->
        <div class="glass-panel security-panel">

            <div>

                <h3>🔐 Security Status</h3>

                <p>
                    Your account is currently authenticated
                    and protected by GovSecure Chat.
                </p>

            </div>

            <div class="security-status">

                <span>✓ Account Active</span>
                <span>✓ Secure Session</span>
                <span>✓ Authorized Access</span>

            </div>

        </div>

    </div>

</body>

</html>