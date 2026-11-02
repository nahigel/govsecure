
<?php

require_once "../auth/protect.php";
require_once "../config.php";

$current_user_role = strtolower(trim($_SESSION["role"] ?? ""));

if ($current_user_role !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

// Get all users
$stmt = $pdo->query("
    SELECT 
        user_id,
        full_name,
        email,
        role,
        department,
        account_status,
        created_at
    FROM users
    ORDER BY created_at DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users - GovSecure Chat</title>

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

        <h2>User Management</h2>

        <p>
            View and manage GovSecure Chat users.
        </p>

    </div>


    <!-- USERS TABLE -->
   <div class="glass-panel" style="padding: 25px;">

  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">

    <h2 style="margin:0;">
        Registered Users
    </h2>

    <a href="add_user.php" class="card-button">
        + Add User
    </a>

</div>

    <div style="overflow-x:auto;">

        <table style="width:100%; border-collapse:collapse; margin-top:20px;">

            <table style="width:100%; border-collapse:collapse; margin-top:20px;">

                <thead>

                    <tr>

                        <th style="text-align:left; padding:12px;">
                            Name
                        </th>

                        <th style="text-align:left; padding:12px;">
                            Email
                        </th>

                        <th style="text-align:left; padding:12px;">
                            Role
                        </th>

                        <th style="text-align:left; padding:12px;">
                            Department
                        </th>

                        <th style="text-align:left; padding:12px;">
                            Status
                        </th>

                        <th style="text-align:left; padding:12px;">
                            Created
                        </th>
                        <th style="text-align:left; padding:12px;">
    Actions
</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($users as $user): ?>

                    <tr>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($user["full_name"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($user["email"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($user["role"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($user["department"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($user["account_status"]); ?>
                        </td>

                        <td style="padding:12px;">
                            <?php echo htmlspecialchars($user["created_at"]); ?>

         <td style="padding:12px;">

    <a href="edit_user.php?id=<?php echo $user["user_id"]; ?>"
       class="card-button">
        Edit
    </a>

<?php if (strtolower(trim($user["account_status"])) === "active"): ?>
        <a href="toggle_user.php?id=<?php echo $user["user_id"]; ?>&action=deactivate"
           class="card-button">
            Deactivate
        </a>

    <?php else: ?>

        <a href="toggle_user.php?id=<?php echo $user["user_id"]; ?>&action=activate"
           class="card-button">
            Activate
        </a>

    <?php endif; ?>

</td>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- BACK BUTTON -->
  <div style="margin-top:20px;">

    <a href="index.php" class="card-button">
        ← Admin Dashboard
    </a>

</div>

</div>

</body>

</html>