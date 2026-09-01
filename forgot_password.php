<?php
session_start();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? "");
    $role = $_POST['role'] ?? "";

    if (empty($role)) {

        $error = "Please select your role.";

    } elseif (empty($email)) {

        $error = "Please enter your email address.";

    } else {

        $_SESSION['reset_role'] = $role;
$_SESSION['reset_email'] = $email;

header("Location: reset_password.php");
exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Campus Cab - Forgot Password</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="auth-card">

    <h1>Campus Cab</h1>

    <h2>Reset Password</h2>

    <p>
        Select your role and enter the email address
        registered with Campus Cab.
    </p>


    <?php if (!empty($error)): ?>

        <div class="msg error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($success)): ?>

        <div class="msg success">
            <?php echo htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>


    <form method="POST" action="forgot_password.php">


        <!-- ROLE -->

        <p><strong>Choose your role:</strong></p>

        <div class="role-options">

            <label>
                <input
                    type="radio"
                    name="role"
                    value="student"
                    required
                >
                Student
            </label>


            <label>
                <input
                    type="radio"
                    name="role"
                    value="driver"
                >
                Driver
            </label>


            <label>
                <input
                    type="radio"
                    name="role"
                    value="staff"
                >
                Staff
            </label>

        </div>


        <!-- EMAIL -->

        <label for="email">
            Email
        </label>

        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your registered email"
            required
        >


        <!-- BUTTONS -->

        <div class="login-buttons">

            <button type="submit">
                SEND TEMPORARY PASSWORD
            </button>

            <button
                type="reset"
                class="cancel-button">
                CANCEL
            </button>

        </div>


        <!-- BACK TO LOGIN -->

        <div class="login-links">

            <p>
                Remember your password?
                <a href="login.php">
                    Back to Login
                </a>
            </p>

        </div>

    </form>

</div>

</body>

</html>
