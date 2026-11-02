<?php

require_once "../auth/protect.php";
require_once "../config.php";

$current_user_role = strtolower(trim($_SESSION["role"] ?? ""));

if ($current_user_role !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_GET["id"] ?? null;
$action = $_GET["action"] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    header("Location: users.php");
    exit;
}

if (!in_array($action, ["activate", "deactivate"], true)) {
    header("Location: users.php");
    exit;
}

/*
 * Prevent the administrator from accidentally
 * deactivating their own account.
 */
if ((int)$user_id === (int)$_SESSION["user_id"]) {
    header("Location: users.php");
    exit;
}

$new_status = ($action === "activate") ? "active" : "inactive";

// Update account status
$stmt = $pdo->prepare("
    UPDATE users
    SET account_status = :account_status
    WHERE user_id = :user_id
");

$stmt->execute([
    ":account_status" => $new_status,
    ":user_id" => $user_id
]);

// Audit log
$audit_stmt = $pdo->prepare("
    INSERT INTO audit_logs
    (user_id, action, description, ip_address)
    VALUES
    (:user_id, :action, :description, :ip_address)
");

$audit_stmt->execute([
    ":user_id" => $_SESSION["user_id"],
    ":action" => strtoupper("USER_" . $action . "D"),
    ":description" => "Administrator " . $action . "d a user account.",
    ":ip_address" => $_SERVER["REMOTE_ADDR"] ?? null
]);

header("Location: users.php");
exit;