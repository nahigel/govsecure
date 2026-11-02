<?php

require_once "../auth/protect.php";
require_once "../config.php";

$current_user_role = strtolower(trim($_SESSION["role"] ?? ""));

if ($current_user_role !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

// Get audit logs with the user's name
$stmt = $pdo->query("
    SELECT
        audit_logs.log_id,
        audit_logs.action,
        audit_logs.description,
        audit_logs.ip_address,
        audit_logs.created_at,
        users.full_name
    FROM audit_logs
    LEFT JOIN users
        ON audit_logs.user_id = users.user_id
    ORDER BY audit_logs.created_at DESC
");

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Audit Logs - GovSecure Chat</title>

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

        <div class="user-info">

            <strong>
                <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
            </strong>

            <span>
                Administrator
            </span>

            <a href="../auth/logout.php" class="card-button">
                Logout
            </a>

        </div>

    </div>


    <!-- PAGE TITLE -->
    <div class="welcome-section">

        <h2>Audit Logs</h2>

        <p>
            Monitor security and system activity.
        </p>

    </div>


    <!-- AUDIT LOGS -->
    <div class="glass-panel" style="padding:25px;">

        <h2 style="margin-top:0;">
            System Activity
        </h2>

        <p style="margin-bottom:25px;">
            Records of important actions performed within GovSecure Chat.
        </p>


        <div style="overflow-x:auto;">

            <table style="
                width:100%;
                border-collapse:collapse;
            ">

                <thead>

                    <tr>

                        <th style="text-align:left; padding:12px;">
                            User
                        </th>

                        <th style="text-align:left; padding:12px;">
                            Action
                        </th>

                        <th style="text-align:left; padding:12px;">
                            Description
                        </th>

                        <th style="text-align:left; padding:12px;">
                            IP Address
                        </th>

                        <th style="text-align:left; padding:12px;">
                            Date & Time
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if (empty($logs)): ?>

                    <tr>

                        <td colspan="5" style="padding:20px;">
                            No audit logs found.
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($logs as $log): ?>

                        <tr>

                            <td style="padding:12px;">
                                <?php
                                echo htmlspecialchars(
                                    $log["full_name"] ?? "Unknown User"
                                );
                                ?>
                            </td>

                            <td style="padding:12px;">
                                <?php echo htmlspecialchars($log["action"]); ?>
                            </td>

                            <td style="padding:12px;">
                                <?php echo htmlspecialchars($log["description"]); ?>
                            </td>

                            <td style="padding:12px;">
                                <?php echo htmlspecialchars($log["ip_address"] ?? "N/A"); ?>
                            </td>

                            <td style="padding:12px;">
                                <?php echo htmlspecialchars($log["created_at"]); ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- BACK -->
    <div style="margin-top:20px;">

        <a href="index.php" class="card-button">
            ← Admin Dashboard
        </a>

    </div>

</div>

</body>

</html>