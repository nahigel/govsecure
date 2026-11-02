<?php

require_once "auth/protect.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GovSecure Chat - Confidential Files</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="page-container">

    <div class="glass-panel page-header">

        <div>
            <h1>📁 Confidential Files</h1>

            <p>
                Securely manage confidential government documents.
            </p>
        </div>

        <a href="dashboard.php" class="page-button">
            ← Dashboard
        </a>

    </div>


    <div class="glass-panel file-upload-panel">

        <h2>Upload Confidential Document</h2>

        <p>
            Only authorized government staff can access uploaded files.
        </p>

        <form action="#" method="POST" enctype="multipart/form-data">

            <div class="file-input">

                <label for="file">
                    Select Document
                </label>

                <input
                    type="file"
                    id="file"
                    name="file"
                >

            </div>

            <button type="submit" class="page-button">
                Upload File
            </button>

        </form>

    </div>


    <div class="glass-panel files-list">

        <h2>Available Confidential Files</h2>

        <p class="empty-state">
            No confidential files have been uploaded yet.
        </p>

    </div>

</div>

</body>

</html>