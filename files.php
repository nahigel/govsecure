<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once "config.php";
require_once "auth/protect.php";

/* =========================================
   HANDLE FILE UPLOAD
========================================= */

$upload_message = "";
$upload_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_FILES["file"])) {

        $upload_error = "PHP did not receive the uploaded file.";

    } else {

        $file = $_FILES["file"];

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $upload_error =
                "PHP upload error code: " . $file["error"];

        } else {

            $original_filename = basename($file["name"]);
            $temporary_file = $file["tmp_name"];
            $file_size = $file["size"];

            $upload_directory =
                __DIR__ . "/uploads/confidential/";

            if (!is_dir($upload_directory)) {

                mkdir($upload_directory, 0777, true);
            }

            $stored_filename =
                uniqid("secure_", true) . "_" . $original_filename;

            $file_path =
                $upload_directory . $stored_filename;


            /*
             * DEBUG INFORMATION
             */

            if (!is_uploaded_file($temporary_file)) {

                $upload_error =
                    "PHP does not recognize this as an uploaded file.";

            } elseif (!is_writable($upload_directory)) {

                $upload_error =
                    "PHP cannot write to the upload directory.";

            } elseif (move_uploaded_file(
                $temporary_file,
                $file_path
            )) {

                /*
                 * FILE WAS SUCCESSFULLY SAVED
                 */

                try {

                    $sql = "
                        INSERT INTO files
                        (
                            message_id,
                            uploaded_by,
                            original_filename,
                            stored_filename,
                            file_path,
                            file_size,
                            is_encrypted
                        )
                        VALUES
                        (
                            NULL,
                            :uploaded_by,
                            :original_filename,
                            :stored_filename,
                            :file_path,
                            :file_size,
                            1
                        )
                    ";

                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        ":uploaded_by" =>
                            $_SESSION["user_id"],

                        ":original_filename" =>
                            $original_filename,

                        ":stored_filename" =>
                            $stored_filename,

                        ":file_path" =>
                            "uploads/confidential/" .
                            $stored_filename,

                        ":file_size" =>
                            $file_size
                    ]);

                    $upload_message =
                        "File uploaded successfully.";

                } catch (PDOException $e) {

                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }

                    $upload_error =
                        "Database error: " .
                        $e->getMessage();
                }

            } else {

                $upload_error =
                    "PHP received the file, but could not move it into the upload directory.";
            }
        }
    }
}


/* =========================================
   GET UPLOADED FILES
========================================= */

try {

    $sql = "
        SELECT
            f.file_id,
            f.original_filename,
            f.file_size,
            f.is_encrypted,
            f.uploaded_at,
            u.full_name
        FROM files f

        LEFT JOIN users u
            ON f.uploaded_by = u.user_id

        ORDER BY f.uploaded_at DESC
    ";

    $stmt = $pdo->query($sql);

    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $files = [];

    $upload_error =
        "Unable to load confidential files.";
}


/* =========================================
   FORMAT FILE SIZE
========================================= */

function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . " GB";
    }

    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . " MB";
    }

    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . " KB";
    }

    return $bytes . " bytes";
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

    <title>GovSecure Chat - Confidential Files</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>

<div class="page-container">


    <!-- =========================================
         HEADER
    ========================================== -->

    <div class="glass-panel page-header">

        <div>

            <h1>📁 Confidential Files</h1>

            <p>
                Securely manage confidential government documents.
            </p>

        </div>


        <a
            href="dashboard.php"
            class="page-button"
        >
            ← Dashboard
        </a>

    </div>



    <!-- =========================================
         UPLOAD SECTION
    ========================================== -->

    <div class="glass-panel file-upload-panel">

        <h2>
            Upload Confidential Document
        </h2>

        <p>
            Only authorized government staff can access uploaded files.
        </p>


        <?php if ($upload_message): ?>

            <div class="message success-message">

                <?= htmlspecialchars($upload_message) ?>

            </div>

        <?php endif; ?>


        <?php if ($upload_error): ?>

            <div class="message error-message">

                <?= htmlspecialchars($upload_error) ?>

            </div>

        <?php endif; ?>


        <form
            action="files.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="file-input">

                <label for="file">
                    Select Document
                </label>


                <input
                    type="file"
                    id="file"
                    name="file"
                    required
                >

            </div>


            <button
                type="submit"
                class="page-button"
            >
                Upload File
            </button>

        </form>

    </div>



    <!-- =========================================
         FILE LIST
    ========================================== -->

    <div class="glass-panel files-list">

        <h2>
            Available Confidential Files
        </h2>


        <?php if (count($files) === 0): ?>

            <p class="empty-state">

                No confidential files have been uploaded yet.

            </p>

        <?php else: ?>


            <?php foreach ($files as $file): ?>

                <div class="file-item">


                    <div class="file-information">

                        <h3>

                            📄

                            <?= htmlspecialchars(
                                $file["original_filename"]
                            ) ?>

                        </h3>


                        <p>

                            Uploaded by:

                            <strong>
                                <?= htmlspecialchars(
                                    $file["full_name"] ?? "Unknown user"
                                ) ?>
                            </strong>

                        </p>


                        <p>

                            Size:

                            <?= formatFileSize(
                                $file["file_size"]
                            ) ?>

                            &nbsp; • &nbsp;

                            Uploaded:

                            <?= htmlspecialchars(
                                $file["uploaded_at"]
                            ) ?>

                        </p>


                        <p>

                            🔐

                            <?php if ($file["is_encrypted"]): ?>

                                Encrypted

                            <?php else: ?>

                                Not encrypted

                            <?php endif; ?>

                        </p>

                    </div>


                    <div class="file-actions">

                        <a
                            href="download.php?id=<?= $file["file_id"] ?>"
                            class="page-button"
                        >
                            Download
                        </a>

                    </div>


                </div>

            <?php endforeach; ?>


        <?php endif; ?>

    </div>


</div>

</body>

</html>