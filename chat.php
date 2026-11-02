<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once "auth/protect.php";
require_once "config.php";
require_once "encryption.php";

$current_user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| GET SELECTED USER
|--------------------------------------------------------------------------
*/

if (!isset($_GET["user_id"]) || !is_numeric($_GET["user_id"])) {
    header("Location: messages.php");
    exit;
}

$selected_user_id = (int) $_GET["user_id"];

// Prevent users from opening their own chat
if ($selected_user_id === (int) $current_user_id) {
    header("Location: messages.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| MAKE SURE USER IS NOT MESSAGING THEMSELVES
|--------------------------------------------------------------------------
*/

if ($selected_user_id === (int) $current_user_id) {
    header("Location: messages.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET SELECTED USER
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            user_id,
            full_name,
            email,
            role
        FROM users
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ":user_id" => $selected_user_id
    ]);

    $selected_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$selected_user) {
        header("Location: messages.php");
        exit;
    }

} catch (PDOException $e) {

    die("Unable to load user.");

}


/*
/*
|--------------------------------------------------------------------------
| FIND EXISTING CONVERSATION
|--------------------------------------------------------------------------
*/

$conversation_id = null;

try {

    $stmt = $pdo->prepare("
        SELECT cp1.conversation_id

        FROM conversation_participants cp1

        INNER JOIN conversation_participants cp2
            ON cp1.conversation_id = cp2.conversation_id

        WHERE cp1.user_id = :current_user
        AND cp2.user_id = :selected_user

        LIMIT 1
    ");

    $stmt->execute([
        ":current_user" => $current_user_id,
        ":selected_user" => $selected_user_id
    ]);

    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($conversation) {

        $conversation_id = $conversation["conversation_id"];

    }

} catch (PDOException $e) {

    die("Unable to verify conversation access.");

}
/*
|--------------------------------------------------------------------------
| CREATE CONVERSATION IF ONE DOES NOT EXIST
|--------------------------------------------------------------------------
*/

if (!$conversation_id) {

    try {

        $pdo->beginTransaction();

        /*
         * Create conversation
         */

        $stmt = $pdo->prepare("
            INSERT INTO conversations ()
            VALUES ()
        ");

        $stmt->execute();

        $conversation_id = $pdo->lastInsertId();



        $stmt = $pdo->prepare("
            INSERT INTO conversation_participants
            (
                conversation_id,
                user_id
            )
            VALUES
            (
                :conversation_id,
                :user_id
            )
        ");

        $stmt->execute([
            ":conversation_id" => $conversation_id,
            ":user_id" => $current_user_id
        ]);
        /*


        /*
         * Add selected user
         */

        $stmt->execute([
            ":conversation_id" => $conversation_id,
            ":user_id" => $selected_user_id
        ]);


        $pdo->commit();

    } catch (PDOException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("Unable to create conversation.");

    }

}


/*
|--------------------------------------------------------------------------
| SEND MESSAGE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $message_text = trim($_POST["message"] ?? "");

    $encrypted_message = encryptMessage($message_text);

    if ($message_text !== "") {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO messages
                (
                    conversation_id,
                    sender_id,
                    message_text,
                    is_encrypted
                )
                VALUES
                (
                    :conversation_id,
                    :sender_id,
                    :message_text,
                    1
                )
            ");

            $stmt->execute([

                ":conversation_id" => $conversation_id,

                ":sender_id" => $current_user_id,

               ":message_text" => $encrypted_message
            ]);
            /*
|--------------------------------------------------------------------------
| CREATE NOTIFICATION FOR RECIPIENT
|--------------------------------------------------------------------------
*/

$notification_stmt = $pdo->prepare("
    INSERT INTO notifications
    (
        user_id,
        notification_text,
        is_read
    )
    VALUES
    (
        :user_id,
        :notification_text,
        0
    )
");

$notification_stmt->execute([
    ":user_id" => $selected_user_id,
    ":notification_text" => "You received a new secure message."
]);

            /*
             * Prevent duplicate form submission
             */
            // AUDIT LOG
$audit_stmt = $pdo->prepare("
    INSERT INTO audit_logs
    (user_id, action, description, ip_address)
    VALUES
    (:user_id, :action, :description, :ip_address)
");

$audit_stmt->execute([
    ":user_id" => $current_user_id,
    ":action" => "MESSAGE_SENT",
    ":description" => "User sent a secure message.",
    ":ip_address" => $_SERVER["REMOTE_ADDR"] ?? null
]);

            header(
                "Location: chat.php?user_id=" .
                $selected_user_id
            );

            exit;

        } catch (PDOException $e) {

            $error = "Unable to send message.";

        }

    }

}


/*
|--------------------------------------------------------------------------
| LOAD MESSAGES
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            m.message_id,
            m.sender_id,
            m.message_text,
            m.is_encrypted,
            m.sent_at,
            u.full_name

        FROM messages m

        INNER JOIN users u
            ON m.sender_id = u.user_id

        WHERE m.conversation_id = :conversation_id

        ORDER BY m.sent_at ASC
    ");

    $stmt->execute([
    ":conversation_id" => $conversation_id
]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($messages as &$message) {
    if ($message["is_encrypted"] == 1) {
        $message["message_text"] = decryptMessage($message["message_text"]);
    }
}
unset($message);



} catch (PDOException $e) {

    $messages = [];

    $error = "Unable to load messages.";

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

    <title>
        GovSecure Chat -
        <?= htmlspecialchars($selected_user["full_name"]) ?>
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=4"
    >

</head>

<body>

<div class="page-container">


    <!-- HEADER -->

    <div class="glass-panel page-header">

        <div>

            <h1>💬 Secure Chat</h1>

            <p>

                Secure communication with

                <?= htmlspecialchars(
                    $selected_user["full_name"]
                ) ?>.

            </p>

        </div>


        <a
            href="messages.php"
            class="page-button"
        >

            ← Conversations

        </a>

    </div>



    <!-- CHAT -->

    <div class="glass-panel chat-container">


        <!-- CHAT HEADER -->

        <div class="chat-header">

            <div class="user-avatar">

                <?= strtoupper(
                    substr(
                        $selected_user["full_name"],
                        0,
                        1
                    )
                ) ?>

            </div>


            <div>

                <h2>

                    <?= htmlspecialchars(
                        $selected_user["full_name"]
                    ) ?>

                </h2>


                <p>

                    <?= htmlspecialchars(
                        $selected_user["role"]
                    ) ?>

                </p>

            </div>

        </div>



        <!-- MESSAGES -->

        <div class="chat-messages">

            <?php if (empty($messages)): ?>

                <div class="chat-empty">

                    <div class="large-icon">
                        💬
                    </div>

                    <h2>
                        No messages yet
                    </h2>

                    <p>

                        Start a secure conversation with

                        <?= htmlspecialchars(
                            $selected_user["full_name"]
                        ) ?>.

                    </p>

                </div>

            <?php else: ?>


                <?php foreach ($messages as $message): ?>

                    <div
                        class="message-bubble
                        <?= $message["sender_id"] == $current_user_id
                            ? "sent"
                            : "received" ?>"
                    >

                        <div class="message-sender">

                            <?= htmlspecialchars(
                                $message["full_name"]
                            ) ?>

                        </div>


                        <div class="message-text">

                            <?= nl2br(
                                htmlspecialchars(
                                    $message["message_text"]
                                )
                            ) ?>

                        </div>


                        <div class="message-time">

                            <?= htmlspecialchars(
                                $message["sent_at"]
                            ) ?>

                        </div>

                    </div>

                <?php endforeach; ?>


            <?php endif; ?>

        </div>



        <!-- SEND MESSAGE -->

        <form
            class="chat-input"
            method="POST"
            action=""
        >

            <input
                type="text"
                name="message"
                placeholder="Type a secure message..."
                autocomplete="off"
                required
            >


            <button
                type="submit"
                class="page-button"
            >

                Send

            </button>

        </form>


    </div>

</div>

</body>

</html>