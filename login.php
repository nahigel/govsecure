<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT *
            FROM users
            WHERE email = ?
            AND account_status = 'Active'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password_hash"])) {

        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role"] = $user["role"];

        

        $sql = "INSERT INTO login_attempts
                (user_id, email, attempt_status, ip_address)
                VALUES (?, ?, 'Successful', ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $user["user_id"],
            $email,
            $_SERVER["REMOTE_ADDR"] ?? null
        ]);

        if ($user["role"] === "admin") {
    header("Location: ../admin/index.php");
    exit;
}

if (strtolower(trim($user["role"])) === "admin") {
    header("Location: ../admin/index.php");
    exit;
}

header("Location: ../dashboard.php");
exit;

    } else {

        $message = "Invalid email or password.";

        $sql = "INSERT INTO login_attempts
                (user_id, email, attempt_status, ip_address)
                VALUES (?, ?, 'Failed', ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $user["user_id"] ?? null,
            $email,
            $_SERVER["REMOTE_ADDR"] ?? null
        ]);
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>GovSecure Chat - Login</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

    <div class="auth-container">

        <h1>GovSecure Chat</h1>

        <h2>Secure Login</h2>

        <?php if (!empty($message)): ?>

            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>

            <button
                type="submit"
                class="auth-button"
            >
                Login
            </button>

        </form>

        <a
            href="register.php"
            class="auth-link"
        >
            Don't have an account? Create one
        </a>

    </div>

</body>

</html>