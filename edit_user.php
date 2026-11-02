<?php

require_once "../auth/protect.php";
require_once "../config.php";

$current_user_role = strtolower(trim($_SESSION["role"] ?? ""));

if ($current_user_role !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_GET["id"] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    header("Location: users.php");
    exit;
}

// Get the user
$stmt = $pdo->prepare("
    SELECT
        user_id,
        full_name,
        email,
        role,
        department,
        account_status
    FROM users
    WHERE user_id = :user_id
");

$stmt->execute([
    ":user_id" => $user_id
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: users.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $role = $_POST["role"] ?? "staff";
    $department = trim($_POST["department"] ?? "");

    if ($full_name === "" || $email === "" || $department === "") {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!in_array($role, ["admin", "staff"], true)) {

        $error = "Invalid role selected.";

    } else {

        // Check whether another user already uses this email
        $check = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE email = :email
            AND user_id != :user_id
        ");

        $check->execute([
            ":email" => $email,
            ":user_id" => $user_id
        ]);

        if ($check->fetch()) {

            $error = "Another user already uses this email address.";

        } else {

            $update = $pdo->prepare("
                UPDATE users
                SET
                    full_name = :full_name,
                    email = :email,
                    role = :role,
                    department = :department
                WHERE user_id = :user_id
            ");

            $update->execute([
                ":full_name" => $full_name,
                ":email" => $email,
                ":role" => $role,
                ":department" => $department,
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
                ":action" => "USER_UPDATED",
                ":description" => "Administrator updated a user account.",
                ":ip_address" => $_SERVER["REMOTE_ADDR"] ?? null
            ]);

            $success = "User updated successfully.";

            // Update displayed values
            $user["full_name"] = $full_name;
            $user["email"] = $email;
            $user["role"] = $role;
            $user["department"] = $department;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit User - GovSecure Chat</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: linear-gradient(135deg, #0f172a, #1e293b) !important;
        color: #f8fafc;
    }

    input:not([type="checkbox"]):not([type="radio"]),
    select,
    textarea {
        background: #172536 !important;
        color: #ffffff !important;
        border: 1px solid #334155 !important;
        border-radius: 10px !important;
        padding: 12px !important;
        width: 100%;
        box-sizing: border-box;
        margin-top: 6px;
    }

    input:focus,
    select:focus,
    textarea:focus {
        border-color: #60a5fa !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
    }

    label {
        color: #e2e8f0;
        font-weight: 600;
    }

    button,
    input[type="submit"] {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
        color: white !important;
        border: none !important;
        border-radius: 10px !important;
        padding: 12px 22px !important;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
    }

    button:hover,
    input[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
    }
</style>

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

        <h2>Edit User</h2>

        <p>
            Update the user's account information.
        </p>

    </div>


    <!-- EDIT FORM -->
    <div class="glass-panel" style="padding:25px;">

        <h2 style="margin-top:0;">
            User Information
        </h2>

        <p style="margin-bottom:25px;">
            Modify the details below and save your changes.
        </p>


        <?php if ($error): ?>

            <div style="margin-bottom:20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <?php if ($success): ?>

            <div style="margin-bottom:20px;">
                <?php echo htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div style="
                display:grid;
                grid-template-columns:1fr 1fr;
                gap:20px;
            ">

                <div>

                    <label for="full_name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php echo htmlspecialchars($user["full_name"]); ?>"
                        required
                    >

                </div>


                <div>

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($user["email"]); ?>"
                        required
                    >

                </div>


                <div>

                    <label for="role">
                        Role
                    </label>

                    <select id="role" name="role">

                        <option
                            value="staff"
                            <?php echo $user["role"] === "staff" ? "selected" : ""; ?>
                        >
                            Staff
                        </option>

                        <option
                            value="admin"
                            <?php echo $user["role"] === "admin" ? "selected" : ""; ?>
                        >
                            Administrator
                        </option>

                    </select>

                </div>


                <div>

                    <label for="department">
                        Department
                    </label>

                    <input
                        type="text"
                        id="department"
                        name="department"
                        value="<?php echo htmlspecialchars($user["department"]); ?>"
                        required
                    >

                </div>

            </div>


            <div style="
                margin-top:30px;
                display:flex;
                gap:15px;
            ">

                <button type="submit" class="card-button">
                    Save Changes
                </button>

                <a href="users.php" class="card-button">
                    Cancel
                </a>

            </div>

        </form>

    </div>


    <!-- BACK -->
    <div style="margin-top:20px;">

        <a href="users.php" class="card-button">
            ← User Management
        </a>

    </div>

</div>

</body>

</html>