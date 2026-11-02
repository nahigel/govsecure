<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $department = trim($_POST["department"]);

    if (empty($full_name) || empty($email) || empty($password)) {

        $message = "Please fill in all required fields.";

    } else {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {

            $sql = "INSERT INTO users
                    (full_name, email, password_hash, department)
                    VALUES (?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $full_name,
                $email,
                $password_hash,
                $department
            ]);

            $message = "Account created successfully!";

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {

                $message = "An account with this email already exists.";

            } else {

                $message = "Registration failed: " . $e->getMessage();

            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>GovSecure Chat - Registration</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

    <div class="auth-container">

        <h1>GovSecure Chat</h1>

        <h2>Create Account</h2>

        <?php if (!empty($message)): ?>

            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Full Name</label>

                <input
                    type="text"
                    name="full_name"
                    placeholder="Enter your full name"
                    required
                >

            </div>

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

                <label>Department</label>

                <input
                    type="text"
                    name="department"
                    placeholder="e.g. ICT Department"
                >

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Create a password"
                    required
                >

            </div>

            <button
                type="submit"
                class="auth-button"
            >
                Create Account
            </button>

        </form>

        <a
            href="login.php"
            class="auth-link"
        >
            Already have an account? Login
        </a>

    </div>

</body>

</html>