<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once "config.php";
?>

<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once "config.php";

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        user_id,
        full_name,
        email,
        role,
        department,
        account_status,
        created_at
    FROM users
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$user = $stmt->fetch();

if (!$user) {
    die("User account not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GovSecure Chat - My Profile</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="page-container">

    <div class="glass-panel page-header">

        <div>
            <h1>👤 My Profile</h1>
            <p>View your GovSecure account information.</p>
        </div>

        <a href="dashboard.php" class="page-button">
            ← Dashboard
        </a>

    </div>


    <div class="glass-panel profile-card">

        <div class="profile-avatar">

            <?php
            echo strtoupper(substr($user["full_name"], 0, 1));
            ?>

        </div>

        <h2>
            <?php echo htmlspecialchars($user["full_name"]); ?>
        </h2>

        <p class="profile-role">
            <?php echo htmlspecialchars($user["role"]); ?>
        </p>


        <div class="profile-details">

            <div class="profile-row">
                <strong>Full Name</strong>
                <span>
                    <?php echo htmlspecialchars($user["full_name"]); ?>
                </span>
            </div>

            <div class="profile-row">
                <strong>Email</strong>
                <span>
                    <?php echo htmlspecialchars($user["email"]); ?>
                </span>
            </div>

            <div class="profile-row">
                <strong>Department</strong>
                <span>
                    <?php echo htmlspecialchars($user["department"]); ?>
                </span>
            </div>

            <div class="profile-row">
                <strong>Role</strong>
                <span>
                    <?php echo htmlspecialchars($user["role"]); ?>
                </span>
            </div>

            <div class="profile-row">
                <strong>Account Status</strong>
                <span>
                    <?php echo htmlspecialchars($user["account_status"]); ?>
                </span>
            </div>

            <div class="profile-row">
                <strong>Account Created</strong>
                <span>
                    <?php echo htmlspecialchars($user["created_at"]); ?>
                </span>
            </div>

        </div>

    </div>

</div>

</body>

</html>