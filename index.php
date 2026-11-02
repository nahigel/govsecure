<?php

require_once "../auth/protect.php";
require_once "../config.php";

$current_user_id = $_SESSION["user_id"];
$current_user_role = strtolower(trim($_SESSION["role"] ?? ""));

if ($current_user_role !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

/* SYSTEM STATISTICS */

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM messages");
$total_messages = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM files");
$total_files = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM audit_logs");
$total_audit_logs = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GovSecure Chat - Admin Dashboard</title>

    <link rel="stylesheet" href="../assets/css/style.css">

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
                    Administrator
                </span>

            </div>

            <a href="../auth/logout.php" class="logout-button">
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
            Manage and monitor the GovSecure communication system from here.
        </p>

    </div>


    <!-- ADMIN DASHBOARD CARDS -->

    <div class="dashboard-grid">


        <!-- USERS -->

        <div class="glass-panel dashboard-card">

            <div class="card-icon">👥</div>

            <h3>Manage Users</h3>

            <p>
                View registered users and manage their
                roles and account status.
            </p>

            <a href="users.php"class="card-button">
                Manage Users
            </a>

        </div>


        <!-- AUDIT LOGS -->

        <div class="glass-panel dashboard-card">

            <div class="card-icon">📋</div>

            <h3>Audit Logs</h3>

            <p>
                Monitor system activity including
                logins, messages and file activity.
            </p>

           <a href="audit_logs.php" class="card-button">
    Audit Logs
</a>
        </div>


        <!-- MESSAGES -->

        <div class="glass-panel dashboard-card">

            <div class="card-icon">💬</div>

            <h3>Secure Messages</h3>

            <p>
                Access the secure communication
                system used by government staff.
            </p>

            <a href="../messages.php" class="card-button">
                Open Messages
            </a>

        </div>


        <!-- FILES -->

        <div class="glass-panel dashboard-card">

            <div class="card-icon">📁</div>

            <h3>Confidential Files</h3>

            <p>
                Monitor and access confidential
                government documents securely.
            </p>

            <a href="../files.php" class="card-button">
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

            <a href="../notifications.php" class="card-button">
                View Notifications
            </a>

        </div>


        <!-- PROFILE -->

        <div class="glass-panel dashboard-card">

            <div class="card-icon">👤</div>

            <h3>My Profile</h3>

            <p>
                View your administrator account
                information and department.
            </p>

            <a href="../profile.php" class="card-button">
                View Profile
            </a>

        </div>

    </div>


    <!-- SYSTEM OVERVIEW -->

    <div class="glass-panel security-panel">

        <div>

            <h3>⚙️ System Overview</h3>

            <p>
                Current GovSecure system statistics.
            </p>

        </div>


        <div class="security-status">

            <span>
                👥 Users: <?= $total_users ?>
            </span>

            <span>
                💬 Messages: <?= $total_messages ?>
            </span>

            <span>
                📁 Files: <?= $total_files ?>
            </span>

            <span>
                📋 Audit Logs: <?= $total_audit_logs ?>
            </span>

        </div>

    </div>


</div>

</body>

</html>