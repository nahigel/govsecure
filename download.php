<?php

session_start();

require_once "auth/protect.php";
require_once "config.php";


/*
|--------------------------------------------------------------------------
| CHECK USER ROLE
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT role
        FROM users
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ":user_id" => $_SESSION["user_id"]
    ]);

    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Unable to verify user authorization.");

}


/*
|--------------------------------------------------------------------------
| ONLY STAFF CAN DOWNLOAD CONFIDENTIAL FILES
|--------------------------------------------------------------------------
*/

if (!$current_user || $current_user["role"] !== "Staff") {

    http_response_code(403);

    die("Access denied. You are not authorized to download confidential files.");

}


/*
|--------------------------------------------------------------------------
| CHECK FILE ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid file.");
}

$file_id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| GET FILE FROM DATABASE
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            file_id,
            original_filename,
            stored_filename,
            file_path,
            file_size
        FROM files
        WHERE file_id = :file_id
        LIMIT 1
    ");

    $stmt->execute([
        ":file_id" => $file_id
    ]);

    $file = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Unable to access file.");

}


/*
|--------------------------------------------------------------------------
| FILE DOES NOT EXIST IN DATABASE
|--------------------------------------------------------------------------
*/

if (!$file) {
    die("File not found.");
}


/*
|--------------------------------------------------------------------------
| BUILD SAFE FILE PATH
|--------------------------------------------------------------------------
*/

$base_directory = __DIR__ . "/uploads/confidential/";

$file_path = $base_directory . $file["stored_filename"];


/*
|--------------------------------------------------------------------------
| CHECK FILE ACTUALLY EXISTS
|--------------------------------------------------------------------------
*/

if (!file_exists($file_path)) {
    die("The requested file does not exist on the server.");
}


/*
|--------------------------------------------------------------------------
| DOWNLOAD FILE
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| RECORD FILE DOWNLOAD IN AUDIT LOG
|--------------------------------------------------------------------------
*/

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
    ":action" => "FILE_DOWNLOADED",
    ":description" => "User downloaded a confidential file.",
    ":ip_address" => $_SERVER["REMOTE_ADDR"] ?? null
]);

header("Content-Description: File Transfer");

header(
    "Content-Type: application/octet-stream"
);

header(
    'Content-Disposition: attachment; filename="' .
    basename($file["original_filename"]) .
    '"'
);

header(
    "Content-Length: " . filesize($file_path)
);

header("Cache-Control: no-cache, must-revalidate");

header("Pragma: public");

readfile($file_path);

exit;

?>