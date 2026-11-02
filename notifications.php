<?php

session_start();

require_once "auth/protect.php";
require_once "config.php";

$current_user_id = $_SESSION["user_id"];
/*
|--------------------------------------------------------------------------
| MARK NOTIFICATION AS READ
|--------------------------------------------------------------------------
*/

if (isset($_GET["read"])) {

    $notification_id = (int) $_GET["read"];

    try {

        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE notification_id = :notification_id
            AND user_id = :user_id
        ");

        $stmt->execute([
            ":notification_id" => $notification_id,
            ":user_id" => $current_user_id
        ]);

    } catch (PDOException $e) {

        $error = "Unable to update notification.";

    }

}

$notifications = [];
$error = "";

/*
|--------------------------------------------------------------------------
| GET NOTIFICATIONS FOR CURRENT USER
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            notification_id,
            notification_text,
            is_read,
            created_at
        FROM notifications
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ");

    $stmt->execute([
        ":user_id" => $current_user_id
    ]);

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = "Unable to load notifications.";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>GovSecure Chat - Notifications</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=4"
    >

    <style>

        /* =====================================================
           NOTIFICATIONS PAGE
        ===================================================== */

        .notifications-page {
            width: 100%;
            max-width: 1250px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .notification-header {
            width: 100%;
            min-height: 160px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 35px 45px;
            margin-bottom: 35px;

            box-sizing: border-box;
        }

        .notification-header-content h1 {
            margin: 0 0 10px;

            font-size: 38px;
            font-weight: 700;
        }

        .notification-header-content p {
            margin: 0;

            font-size: 17px;
            opacity: 0.8;
        }


        /* =====================================================
           NOTIFICATIONS PANEL
        ===================================================== */

        .notifications-panel {
            width: 100%;
            min-height: 250px;

            padding: 35px 45px;

            box-sizing: border-box;
        }

        .notifications-panel h2 {
            margin: 0 0 35px;

            font-size: 30px;
        }


        /* =====================================================
           EMPTY STATE
        ===================================================== */

        .notification-empty {
            display: flex;
            align-items: center;

            gap: 18px;

            padding: 20px 5px;

            font-size: 18px;

            opacity: 0.8;
        }

        .notification-empty-icon {
            font-size: 30px;
        }


        /* =====================================================
           NOTIFICATION ITEM
        ===================================================== */

        .notification-item {
            display: flex;
            align-items: flex-start;

            gap: 20px;

            padding: 22px;

            margin-bottom: 15px;

            border-radius: 15px;

            background: rgba(255, 255, 255, 0.08);

            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .notification-icon {
            font-size: 28px;
        }

        .notification-content {
            flex: 1;
        }

        .notification-content p {
            margin: 0 0 8px;

            font-size: 18px;
        }

        .notification-date {
            font-size: 14px;
            opacity: 0.65;
        }

        .unread {
            border-left: 4px solid #4da6ff;
        }


        /* =====================================================
           ERROR
        ===================================================== */

        .notification-error {
            padding: 15px 20px;

            margin-bottom: 25px;

            border-radius: 12px;

            background: rgba(255, 80, 80, 0.15);

            border: 1px solid rgba(255, 80, 80, 0.35);
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 700px) {

            .notifications-page {
                padding: 25px 15px 50px;
            }

            .notification-header {
                flex-direction: column;
                align-items: flex-start;

                gap: 25px;

                padding: 30px;
            }

            .notification-header-content h1 {
                font-size: 30px;
            }

            .notifications-panel {
                padding: 30px;
            }

            .notifications-panel h2 {
                font-size: 25px;
            }

        }

    </style>

</head>


<body>

<div class="page-container">

    <div class="notifications-page">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="glass-panel notification-header">

            <div class="notification-header-content">

                <h1>
                    🔔 Notifications
                </h1>

                <p>
                    Important security alerts and system notifications.
                </p>

            </div>


            <a
                href="dashboard.php"
                class="page-button"
            >
                ← Dashboard
            </a>

        </div>



        <!-- =====================================================
             NOTIFICATIONS
        ====================================================== -->

        <div class="glass-panel notifications-panel">

            <h2>
                Your Notifications
            </h2>


            <?php if ($error): ?>

                <div class="notification-error">

                    <?= htmlspecialchars($error) ?>

                </div>


            <?php elseif (count($notifications) === 0): ?>

                <div class="notification-empty">

                    <span class="notification-empty-icon">
                        🔔
                    </span>

                    <span>
                        No notifications yet.
                    </span>

                </div>


            <?php else: ?>


                <?php foreach ($notifications as $notification): ?>

                    <div
                        class="notification-item
                        <?= $notification["is_read"] ? "" : "unread" ?>"
                    >

                        <div class="notification-icon">
                            🔔
                        </div>


                        <div class="notification-content">

                            <p>
                                <?= htmlspecialchars(
                                    $notification["notification_text"]
                                ) ?>
                            </p>

                          <div class="notification-date">

    <?= htmlspecialchars(
        $notification["created_at"]
    ) ?>

    <?php if (!$notification["is_read"]): ?>

        <br>

        <a
            href="notifications.php?read=<?= $notification["notification_id"] ?>"
            style="color: inherit; text-decoration: underline;"
        >
            Mark as read
        </a>

    <?php else: ?>

        <br>
        <span>✓ Read</span>

    <?php endif; ?>

</div>

                            </div>

                        </div>

                    

                <?php endforeach; ?>


            <?php endif; ?>

        </div>


    </div>

</div>

</body>

</html>