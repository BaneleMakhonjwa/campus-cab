<?php
session_start();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $temporary_password = $_POST['temporary_password'] ?? "";
    $new_password = $_POST['new_password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    if (empty($temporary_password) ||
        empty($new_password) ||
        empty($confirm_password)) {

        $error = "Please complete all fields.";

    } elseif ($new_password !== $confirm_password) {

        $error = "New password and confirm password do not match.";

    } elseif (strlen($new_password) < 6) {

        $error = "New password must be at least 6 characters long.";

    } else {

        /*
         * Database verification and password update
         * will be added in the next step.
         */

        $success = "Password details are valid.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Campus Cab - Reset Password</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="auth-card">

    <h1>Campus Cab</h1>

    <h2>Reset Password</h2>

    <p>
        Enter the temporary password sent to your email,
        then create your new password.
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


    <form method="POST" action="reset_password.php">


        <label for="temporary_password">
            Temporary Password
        </label>

        <input
            type="password"
            id="temporary_password"
            name="temporary_password"
            placeholder="Enter temporary password"
            required
        >


        <label for="new_password">
            New Password
        </label>

        <input
            type="password"
            id="new_password"
            name="new_password"
            placeholder="Enter new password"
            required
        >


        <label for="confirm_password">
            Confirm New Password
        </label>

        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            placeholder="Confirm new password"
            required
        >


        <div class="login-buttons">

            <button type="submit">
                RESET PASSWORD
            </button>

            <button
                type="reset"
                class="cancel-button">
                CANCEL
            </button>

        </div>


        <div class="login-links">

            <p>
                <a href="login.php">
                    Back to Login
                </a>
            </p>

        </div>

    </form>

</div>

</body>

</html>
