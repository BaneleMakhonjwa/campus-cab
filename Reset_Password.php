<?php
session_start();

require_once __DIR__ . "/db_connect.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (
    !isset($_SESSION["reset_role"]) ||
    !isset($_SESSION["reset_user_id"])
) {
    header("Location: forgot_password.php");
    exit;
}

$role = $_SESSION["reset_role"];
$userID = $_SESSION["reset_user_id"];

$error = "";
$success = false;


/*
|--------------------------------------------------------------------------
| Check that the role is valid
|--------------------------------------------------------------------------
*/

$allowed_roles = [
    "student",
    "driver",
    "staff",
    "admin"
];

if (!in_array($role, $allowed_roles, true)) {
    session_destroy();
    header("Location: forgot_password.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get the CURRENT password from the database
|--------------------------------------------------------------------------
*/

$stored_password = "";

if ($role === "student") {

    $sql = "
        SELECT studentPwd
        FROM student
        WHERE studentID = ?
        LIMIT 1
    ";

} elseif ($role === "driver") {

    $sql = "
        SELECT password
        FROM driver
        WHERE DriverID = ?
        LIMIT 1
    ";

} elseif ($role === "staff") {

    $sql = "
        SELECT password
        FROM staff
        WHERE staffID = ?
        LIMIT 1
    ";

} else {

    $sql = "
        SELECT adminPWD
        FROM admin
        WHERE adminID = ?
        LIMIT 1
    ";
}


$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $userID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if ($role === "student") {
            $stored_password = $user["studentPwd"];

        } elseif ($role === "driver") {
            $stored_password = $user["password"];

        } elseif ($role === "staff") {
            $stored_password = $user["password"];

        } else {
            $stored_password = $user["adminPWD"];
        }
    }

    $stmt->close();

} else {

    $error = "Unable to connect to the database.";
}


/*
|--------------------------------------------------------------------------
| RESET PASSWORD
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && $error === "") {

    $temporary_password =
        $_POST["temporary_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Check temporary password against database
    |--------------------------------------------------------------------------
    */

    if (
        empty($stored_password) ||
        !password_verify(
            $temporary_password,
            $stored_password
        )
    ) {

        $error = "The temporary password is incorrect.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check new password length
    |--------------------------------------------------------------------------
    */

    elseif (strlen($new_password) < 9) {

        $error =
            "New password must be at least 9 characters long.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check for number
    |--------------------------------------------------------------------------
    */

    elseif (!preg_match("/[0-9]/", $new_password)) {

        $error =
            "New password must contain at least one number.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check for special character
    |--------------------------------------------------------------------------
    */

    elseif (!preg_match("/[^a-zA-Z0-9]/", $new_password)) {

        $error =
            "New password must contain at least one special character.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check passwords match
    |--------------------------------------------------------------------------
    */

    elseif ($new_password !== $confirm_password) {

        $error =
            "The new passwords do not match.";

    }


    /*
    |--------------------------------------------------------------------------
    | SAVE NEW PASSWORD TO DATABASE
    |--------------------------------------------------------------------------
    */

    else {

        /*
        | Hash the NEW password before saving it.
        */
        $new_password_hash =
            password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


        /*
        |--------------------------------------------------------------------------
        | STUDENT
        |--------------------------------------------------------------------------
        */

        if ($role === "student") {

            $updateSql = "
                UPDATE student
                SET studentPwd = ?
                WHERE studentID = ?
            ";

        }


        /*
        |--------------------------------------------------------------------------
        | DRIVER
        |--------------------------------------------------------------------------
        */

        elseif ($role === "driver") {

            $updateSql = "
                UPDATE driver
                SET password = ?
                WHERE DriverID = ?
            ";

        }


        /*
        |--------------------------------------------------------------------------
        | STAFF
        |--------------------------------------------------------------------------
        */

        elseif ($role === "staff") {

            $updateSql = "
                UPDATE staff
                SET password = ?
                WHERE staffID = ?
            ";

        }


        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        else {

            $updateSql = "
                UPDATE admin
                SET adminPWD = ?
                WHERE adminID = ?
            ";
        }


        /*
        |--------------------------------------------------------------------------
        | Execute database update
        |--------------------------------------------------------------------------
        */

        $updateStmt =
            $conn->prepare($updateSql);


        if ($updateStmt) {

            $updateStmt->bind_param(
                "si",
                $new_password_hash,
                $userID
            );


            if ($updateStmt->execute()) {

                if ($updateStmt->affected_rows >= 0) {

                    /*
                    | Password has now been changed
                    | in the actual database.
                    */

                    unset(
                        $_SESSION["reset_role"],
                        $_SESSION["reset_user_id"],
                        $_SESSION["reset_email"],
                        $_SESSION["temporary_password"]
                    );

                    $success = true;
                }

            } else {

                $error =
                    "The password could not be updated.";
            }

            $updateStmt->close();

        } else {

            $error =
                "Unable to update the password.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reset Password - Campus Cab</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .reset-page {
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            background: #f6f5f1;

            padding: 15px;
        }


        .reset-card {
            width: 100%;
            max-width: 360px;

            background: #ffffff;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);
        }


        .reset-card h1 {
            margin: 0 0 6px;

            text-align: center;

            color: #0c2d4d;

            font-size: 25px;
        }


        .reset-description {
            margin: 0 0 20px;

            text-align: center;

            color: #667085;

            font-size: 13px;
        }


        .form-group {
            margin-bottom: 16px;
        }


        .form-group label {
            display: block;

            margin-bottom: 6px;

            color: #1c2430;

            font-size: 13px;

            font-weight: 600;
        }


        .form-group input {
            width: 100%;

            padding: 10px 11px;

            border: 1px solid #e2e2dc;

            border-radius: 6px;

            font-size: 13px;

            outline: none;

            box-sizing: border-box;
        }


        .form-group input:focus {
            border-color: #185fa5;
        }


        .requirements {
            margin-bottom: 16px;

            padding: 10px;

            border-radius: 6px;

            background: #fbecc9;

            color: #8a6d00;

            font-size: 12px;

            line-height: 1.5;
        }


        .reset-button {
            width: 100%;

            padding: 10px;

            border: none;

            border-radius: 6px;

            background: #185fa5;

            color: #ffffff;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;
        }


        .reset-button:hover {
            background: #0c2d4d;
        }


        .error-message {
            margin-bottom: 15px;

            padding: 9px 10px;

            border-radius: 6px;

            background: #fbe2df;

            color: #b03a2e;

            font-size: 12px;

            text-align: center;
        }


        .success-message {
            text-align: center;
        }


        .success-message h2 {
            color: #0f6e56;

            font-size: 20px;
        }


        .success-message p {
            color: #667085;

            font-size: 13px;
        }


        .login-button {
            display: block;

            width: 100%;

            padding: 10px;

            border-radius: 6px;

            background: #185fa5;

            color: white;

            text-align: center;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;
        }


        .login-button:hover {
            background: #0c2d4d;
        }

    </style>

</head>


<body>


<div class="reset-page">

    <div class="reset-card">


        <?php if ($success): ?>

            <div class="success-message">

                <h2>
                    Password Reset Successfully
                </h2>

                <p>
                    Your new password has been saved.
                </p>

                <p>
                    You can now login using your new password.
                </p>


                <a
                    href="login.php"
                    class="login-button"
                >
                    Go to Login
                </a>

            </div>


        <?php else: ?>


            <h1>
                Reset Password
            </h1>


            <p class="reset-description">
                Enter your temporary password and create a new password.
            </p>


            <?php if ($error !== ""): ?>

                <div class="error-message">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="reset_password.php"
                autocomplete="off"
            >


                <div class="form-group">

                    <label for="temporary_password">
                        Temporary Password
                    </label>

                    <input
                        type="password"
                        id="temporary_password"
                        name="temporary_password"
                        autocomplete="off"
                        required
                    >

                </div>


                <div class="requirements">

                    New password must:

                    <br>

                    • Be at least 9 characters long

                    <br>

                    • Contain at least one number

                    <br>

                    • Contain at least one special character
                    such as ! or @

                </div>


                <div class="form-group">

                    <label for="new_password">
                        New Password
                    </label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="confirm_password">
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="reset-button"
                >
                    Reset Password
                </button>


            </form>


        <?php endif; ?>


    </div>

</div>

</body>

</html>
