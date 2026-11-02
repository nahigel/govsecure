<?php

session_start();

require_once "../config.php";


/*
|--------------------------------------------------------------------------
| RECORD LOGOUT IN AUDIT LOG
|--------------------------------------------------------------------------
*/

if (isset($_SESSION["user_id"])) {

    $audit_stmt = $pdo->prepare("
        INSERT INTO audit_logs
        (
            user_id,
            action,
            description,
            ip_address
        )
        VALUES
        (
            :user_id,
            :action,
            :description,
            :ip_address
        )
    ");

    $audit_stmt->execute([
        ":user_id" => $_SESSION["user_id"],
        ":action" => "LOGOUT",
        ":description" => "User logged out of GovSecure.",
        ":ip_address" => $_SERVER["REMOTE_ADDR"] ?? null
    ]);
}


/*
|--------------------------------------------------------------------------
| DESTROY SESSION
|--------------------------------------------------------------------------
*/

session_unset();
session_destroy();

header("Location: login.php");
exit;

?>