<?php
session_start();
require_once __DIR__ . "/db_connect.php";

/*
|--------------------------------------------------------------------------
| Prevent browser from restoring old form values from cache
|--------------------------------------------------------------------------
*/
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$error = "";

$role = $_POST["role"] ?? "student";
$email = trim($_POST["email"] ?? "");

$allowed_roles = ["student", "driver", "staff", "admin"];

if (!in_array($role, $allowed_roles, true)) {
    $role = "student";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($email === "") {

        $error = "Please enter your email address.";

    } else {

        $userID = null;

        /*
        |--------------------------------------------------------------------------
        | Find account in database
        |--------------------------------------------------------------------------
        */

        if ($role === "student") {

            $sql = "
                SELECT studentID
                FROM student
                WHERE studentEmail = ?
                AND studentAccount_status = 'Active'
                LIMIT 1
            ";

        } elseif ($role === "driver") {

            $sql = "
                SELECT DriverID
                FROM driver
                WHERE DriverEmail = ?
                AND driverAccount_Status = 'Active'
                LIMIT 1
            ";

        } elseif ($role === "staff") {

            $sql = "
                SELECT staffID
                FROM staff
                WHERE staffEmail = ?
                AND staffAccount_status = 'Active'
                LIMIT 1
            ";

        } else {

            $sql = "
                SELECT adminID
                FROM admin
                WHERE admin_email = ?
                LIMIT 1
            ";
        }


        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            /*
            |--------------------------------------------------------------------------
            | Get correct user ID
            |--------------------------------------------------------------------------
            */

            if ($role === "student") {
                $userID = $user["studentID"];

            } elseif ($role === "driver") {
                $userID = $user["DriverID"];

            } elseif ($role === "staff") {
                $userID = $user["staffID"];

            } else {
                $userID = $user["adminID"];
            }


            /*
            |--------------------------------------------------------------------------
            | Generate temporary password
            |--------------------------------------------------------------------------
            */

            $temporary_password =
                substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ"), 0, 3)
                . rand(10, 99)
                . substr(str_shuffle("!@#$%"), 0, 1)
                . substr(str_shuffle("abcdefghijkmnopqrstuvwxyz"), 0, 3);


            /*
            |--------------------------------------------------------------------------
            | Hash temporary password
            |--------------------------------------------------------------------------
            */

            $temporary_password_hash =
                password_hash($temporary_password, PASSWORD_DEFAULT);


            /*
            |--------------------------------------------------------------------------
            | Save temporary password hash to the REAL database password column
            |--------------------------------------------------------------------------
            */

            if ($role === "student") {

                $updateSql = "
                    UPDATE student
                    SET studentPwd = ?
                    WHERE studentID = ?
                ";

            } elseif ($role === "driver") {

                $updateSql = "
                    UPDATE driver
                    SET password = ?
                    WHERE DriverID = ?
                ";

            } elseif ($role === "staff") {

                $updateSql = "
                    UPDATE staff
                    SET password = ?
                    WHERE staffID = ?
                ";

            } else {

                $updateSql = "
                    UPDATE admin
                    SET adminPWD = ?
                    WHERE adminID = ?
                ";
            }


            $updateStmt = $conn->prepare($updateSql);

            $updateStmt->bind_param(
                "si",
                $temporary_password_hash,
                $userID
            );

            if ($updateStmt->execute()) {

                /*
                |--------------------------------------------------------------------------
                | Remove any old password-reset session information
                |--------------------------------------------------------------------------
                */

                unset(
                    $_SESSION["reset_role"],
                    $_SESSION["reset_user_id"],
                    $_SESSION["reset_email"],
                    $_SESSION["temporary_password"]
                );


                /*
                |--------------------------------------------------------------------------
                | Store information for the NEW reset attempt
                |--------------------------------------------------------------------------
                */

                $_SESSION["reset_role"] = $role;
                $_SESSION["reset_user_id"] = $userID;
                $_SESSION["reset_email"] = $email;

                /*
                 | Only stored temporarily so we can display it on
                 | temporary_password.php.
                 */
                $_SESSION["temporary_password"] = $temporary_password;


                $updateStmt->close();
                $stmt->close();


                /*
                |--------------------------------------------------------------------------
                | Go to temporary password page
                |--------------------------------------------------------------------------
                */

                header("Location: temporary_password.php");
                exit;

            } else {

                $error = "Unable to generate temporary password. Please try again.";
            }

            $updateStmt->close();

        } else {

            $error = "No active " . ucfirst($role) . " account was found with that email address.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Forgot Password - Campus Cab</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .forgot-page {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f6f5f1;
            padding: 20px;
        }

        .forgot-card {
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .forgot-card h1 {
            color: #0c2d4d;
            text-align: center;
            margin-bottom: 8px;
        }

        .forgot-description {
            text-align: center;
            color: #667085;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #1c2430;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input[type="email"] {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #e2e2dc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-group input[type="email"]:focus {
            outline: none;
            border-color: #185fa5;
        }

        .role-title {
            margin-bottom: 10px;
            color: #1c2430;
            font-size: 14px;
            font-weight: 600;
        }

        .role-options {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 18px;
            margin: 8px 0 20px;
            flex-wrap: nowrap;
        }

        .role-options label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            padding: 0;
            color: #1c2430;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
        }

        .role-options input[type="radio"] {
            width: auto;
            margin: 0;
            padding: 0;
            accent-color: #185fa5;
            cursor: pointer;
        }

        .reset-button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #185fa5;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .reset-button:hover {
            background: #0c2d4d;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #185fa5;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #fbe2df;
            color: #b03a2e;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 18px;
            font-size: 14px;
            text-align: center;
        }

    </style>

</head>


<body>

<div class="forgot-page">

    <div class="forgot-card">

        <h1>Forgot Password</h1>

        <p class="forgot-description">
            Select your account type and enter your email address.
        </p>


        <?php if ($error !== ""): ?>

            <div class="error-message">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="forgot_password.php"
            id="forgotPasswordForm"
            autocomplete="off"
        >


            <!-- Account type -->

            <div class="role-title">
                Account type:
            </div>


            <div class="role-options">

                <label>

                    <input
                        type="radio"
                        name="role"
                        value="student"
                        <?php echo ($role === "student") ? "checked" : ""; ?>
                        required
                    >

                    Student

                </label>


                <label>

                    <input
                        type="radio"
                        name="role"
                        value="driver"
                        <?php echo ($role === "driver") ? "checked" : ""; ?>
                    >

                    Driver

                </label>


                <label>

                    <input
                        type="radio"
                        name="role"
                        value="staff"
                        <?php echo ($role === "staff") ? "checked" : ""; ?>
                    >

                    Staff

                </label>


                <label>

                    <input
                        type="radio"
                        name="role"
                        value="admin"
                        <?php echo ($role === "admin") ? "checked" : ""; ?>
                    >

                    Admin

                </label>

            </div>


            <!-- Email -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    autocomplete="off"
                    required
                >

            </div>


            <button
                type="submit"
                class="reset-button"
            >
                Generate Temporary Password
            </button>


        </form>


        <div class="back-link">

            <a href="login.php">
                Back to Login
            </a>

        </div>

    </div>

</div>


<script>

const emailInput =
    document.getElementById("email");

const roleButtons =
    document.querySelectorAll('input[name="role"]');


/*
|--------------------------------------------------------------------------
| Clear email when account type changes
|--------------------------------------------------------------------------
|
| Example:
|
| Student email entered
|        ↓
| User clicks Staff
|        ↓
| Student email disappears
|
*/

roleButtons.forEach(function(roleButton) {

    roleButton.addEventListener("change", function() {

        emailInput.value = "";

        emailInput.focus();

    });

});


/*
|--------------------------------------------------------------------------
| Clear old information when browser Back button restores page
|--------------------------------------------------------------------------
*/

window.addEventListener("pageshow", function(event) {

    if (event.persisted) {

        emailInput.value = "";

        const studentRadio =
            document.querySelector(
                'input[name="role"][value="student"]'
            );

        if (studentRadio) {
            studentRadio.checked = true;
        }
    }

});

</script>

</body>

</html>
