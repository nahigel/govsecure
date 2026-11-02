<?php

require_once "../auth/protect.php";
require_once "../config.php";

$current_user_role = strtolower(trim($_SESSION["role"] ?? ""));

if ($current_user_role !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "staff";
    $department = trim($_POST["department"] ?? "");

    if ($full_name === "" || $email === "" || $password === "" || $department === "") {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!in_array($role, ["admin", "staff"], true)) {

        $error = "Invalid role selected.";

    } else {

        // Check if email already exists
        $check = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE email = :email
        ");

        $check->execute([
            ":email" => $email
        ]);

        if ($check->fetch()) {

            $error = "A user with this email already exists.";

        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users
                (full_name, email, password_hash, role, department, account_status)
                VALUES
                (:full_name, :email, :password_hash, :role, :department, 'active')
            ");

            $stmt->execute([
                ":full_name" => $full_name,
                ":email" => $email,
                ":password_hash" => $password_hash,
                ":role" => $role,
                ":department" => $department
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
                ":action" => "USER_CREATED",
                ":description" => "Administrator created a new user account.",
                ":ip_address" => $_SERVER["REMOTE_ADDR"] ?? null
            ]);

            $success = "User created successfully.";

            // Clear form
            $full_name = "";
            $email = "";
            $department = "";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add User - GovSecure Chat</title>

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

        <h2>Add User</h2>

        <p>
            Create a new GovSecure Chat user account.
        </p>

    </div>


    <!-- FORM -->
    <div class="glass-panel" style="padding:25px;">

        <h2 style="margin-top:0;">
            User Information
        </h2>


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


        <!-- FORM -->
<div class="glass-panel" style="padding:25px;">

    <h2 style="margin-top:0;">
        User Information
    </h2>

    <p style="margin-bottom:25px;">
        Enter the details for the new user account.
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
                    value="<?php echo htmlspecialchars($full_name ?? ""); ?>"
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
                    value="<?php echo htmlspecialchars($email ?? ""); ?>"
                    required
                >

            </div>


            <div>

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >

            </div>


            <div>

                <label for="role">
                    Role
                </label>

                <select id="role" name="role">

                    <option value="staff">
                        Staff
                    </option>

                    <option value="admin">
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
                    value="<?php echo htmlspecialchars($department ?? ""); ?>"
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
                Create User
            </button>

            <a href="users.php" class="card-button">
                Cancel
            </a>

        </div>

    </form>

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