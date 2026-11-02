<?php

session_start();

require_once "auth/protect.php";
require_once "config.php";

$current_user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| GET OTHER USERS
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        SELECT
            user_id,
            full_name,
            email,
            role
        FROM users
        WHERE user_id != :current_user
        ORDER BY full_name ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":current_user" => $current_user_id
    ]);

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $users = [];

    $error = "Unable to load users.";

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

    <title>GovSecure Chat - Secure Messages</title>

    <link
    rel="stylesheet"
    href="assets/css/style.css?v=2"
>
</head>

<body>

<div class="page-container">

    <!-- HEADER -->

    <div class="glass-panel page-header">

        <div>

            <h1>💬 Secure Messages</h1>

            <p>
                Communicate securely with authorized government staff.
            </p>

        </div>

        <a
            href="dashboard.php"
            class="page-button"
        >
            ← Dashboard
        </a>

    </div>


    <!-- MESSAGING AREA -->

    <div class="messages-layout">


        <!-- USERS / CONVERSATIONS -->

        <div class="glass-panel users-panel">

            <div class="panel-title">

                <h2>Conversations</h2>

                <p>
                    Select a staff member to communicate with.
                </p>

            </div>


            <?php if (isset($error)): ?>

                <div class="message error-message">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <?php if (count($users) === 0): ?>

                <div class="empty-state">

                    No other users are available.

                </div>

            <?php else: ?>


                <div class="user-list">

    <?php foreach ($users as $user): ?>

        <a
            href="chat.php?user_id=<?= $user["user_id"] ?>"
            class="user-item"
            style="text-decoration: none; color: inherit;"
        >

            <div class="user-avatar">
                <?= strtoupper(
                    substr(
                        $user["full_name"],
                        0,
                        1
                    )
                ) ?>
            </div>

            <div class="user-details">

                <strong>
                    <?= htmlspecialchars(
                        $user["full_name"]
                    ) ?>
                </strong>

                <span>
                    <?= htmlspecialchars(
                        $user["role"]
                    ) ?>
                </span>

            </div>

        </a>

    <?php endforeach; ?>

</div>

            <?php endif; ?>

        </div>


        <!-- MESSAGE WINDOW -->

        <div class="glass-panel chat-panel">

            <div class="chat-empty">

                <div class="large-icon">
                    💬
                </div>

                <h2>
                    Secure Communication
                </h2>

                <p>
                    Select a government staff member
                    from the left to start a secure conversation.
                </p>

            </div>

        </div>


    </div>

</div>

</body>

</html>