<?php
session_start();

if (
    !isset($_SESSION["temporary_password"]) ||
    !isset($_SESSION["reset_role"]) ||
    !isset($_SESSION["reset_user_id"])
) {
    header("Location: forgot_password.php");
    exit;
}

$temporary_password = $_SESSION["temporary_password"];
$role = $_SESSION["reset_role"];
$email = $_SESSION["reset_email"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Temporary Password - Campus Cab</title>

    <style>

        body {
            margin: 0;
            padding: 0;

            min-height: 100vh;

            font-family: Arial, sans-serif;

            background: #f6f5f1;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 430px;

            background: white;

            padding: 35px;

            border-radius: 10px;

            box-shadow: 0 4px 15px rgba(0,0,0,0.10);

            text-align: center;
        }

        h1 {
            color: #0c2d4d;

            margin-bottom: 10px;
        }

        p {
            color: #667085;

            font-size: 14px;

            line-height: 1.5;
        }

        .password-box {
            margin: 25px 0;

            padding: 18px;

            background: #f6f5f1;

            border: 1px solid #e2e2dc;

            border-radius: 6px;

            color: #0c2d4d;

            font-size: 24px;

            font-weight: bold;

            letter-spacing: 2px;
        }

        .continue-button {
            display: block;

            width: 100%;

            padding: 12px;

            background: #0c2d4d;

            color: white;

            text-decoration: none;

            border-radius: 6px;

            font-weight: 600;
        }

        .continue-button:hover {
            background: #185fa5;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Temporary Password</h1>

    <p>
        Your temporary password is:
    </p>

    <div class="password-box">
        <?php echo htmlspecialchars($temporary_password); ?>
    </div>

    <p>
        Please copy this password and click Continue.
    </p>

    <a href="reset_password.php"
       class="continue-button">
        Continue to Reset Password
    </a>

</div>

</body>

</html>
