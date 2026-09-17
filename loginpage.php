<?php
session_start();

require_once __DIR__ . "/db_connect.php";

/* Prevent old login details from being restored */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$error = "";

$role = $_POST["role"] ?? "student";
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

$allowed_roles = ["student", "driver", "staff", "admin"];

if (!in_array($role, $allowed_roles, true)) {
    $role = "student";
}


/* =========================
   LOGIN PROCESS
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } else {

        /* =========================
           STUDENT LOGIN
           ========================= */

        if ($role === "student") {

            $sql = "
                SELECT
                    studentID,
                    studentFname,
                    studentLname,
                    studentEmail,
                    studentPwd
                FROM student
                WHERE studentEmail = ?
                AND studentAccount_status = 'Active'
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param("s", $email);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 1) {

                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user["studentPwd"])) {

                        session_regenerate_id(true);

                        $_SESSION["logged_in"] = true;
                        $_SESSION["role"] = "student";
                        $_SESSION["user_id"] = $user["studentID"];
                        $_SESSION["email"] = $user["studentEmail"];
                        $_SESSION["name"] =
                            $user["studentFname"] . " " . $user["studentLname"];

                        header("Location: request.php");
                        exit;
                    }
                }

                $stmt->close();
            }

            $error = "Invalid student email or password.";
        }


        /* =========================
           DRIVER LOGIN
           ========================= */

        elseif ($role === "driver") {

            $sql = "
                SELECT
                    DriverID,
                    DriverFname,
                    DriverLname,
                    DriverEmail,
                    password
                FROM driver
                WHERE DriverEmail = ?
                AND driverAccount_Status = 'Active'
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param("s", $email);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 1) {

                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user["password"])) {

                        session_regenerate_id(true);

                        $_SESSION["logged_in"] = true;
                        $_SESSION["role"] = "driver";
                        $_SESSION["user_id"] = $user["DriverID"];
                        $_SESSION["email"] = $user["DriverEmail"];
                        $_SESSION["name"] =
                            $user["DriverFname"] . " " . $user["DriverLname"];

                        header("Location: driver.php");
                        exit;
                    }
                }

                $stmt->close();
            }

            $error = "Invalid driver email or password.";
        }


        /* =========================
           STAFF LOGIN
           ========================= */

        elseif ($role === "staff") {

            $sql = "
                SELECT
                    staffID,
                    staffFname,
                    staffLname,
                    staffEmail,
                    password
                FROM staff
                WHERE staffEmail = ?
                AND staffAccount_status = 'Active'
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param("s", $email);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 1) {

                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user["password"])) {

                        session_regenerate_id(true);

                        $_SESSION["logged_in"] = true;
                        $_SESSION["role"] = "staff";
                        $_SESSION["user_id"] = $user["staffID"];
                        $_SESSION["email"] = $user["staffEmail"];
                        $_SESSION["name"] =
                            $user["staffFname"] . " " . $user["staffLname"];

                        header("Location: staff.php");
                        exit;
                    }
                }

                $stmt->close();
            }

            $error = "Invalid staff email or password.";
        }


        /* =========================
           ADMIN LOGIN
           ========================= */

        elseif ($role === "admin") {

            $sql = "
                SELECT
                    adminID,
                    adminFname,
                    adminLname,
                    admin_email,
                    adminPWD
                FROM admin
                WHERE admin_email = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param("s", $email);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 1) {

                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user["adminPWD"])) {

                        session_regenerate_id(true);

                        $_SESSION["logged_in"] = true;
                        $_SESSION["role"] = "admin";
                        $_SESSION["user_id"] = $user["adminID"];
                        $_SESSION["email"] = $user["admin_email"];
                        $_SESSION["name"] =
                            $user["adminFname"] . " " . $user["adminLname"];

                        header("Location: admin.php");
                        exit;
                    }
                }

                $stmt->close();
            }

            $error = "Invalid admin email or password.";
        }
    }

    /* Clear password after failed login */
    $password = "";
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

    <title>Campus Cab - Login</title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f5f1;
            color: #1c2430;
        }


        /* =========================
           LOGIN PAGE
           ========================= */

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }


        .login-card {
            width: 100%;
            max-width: 360px;
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }


        .login-card h2 {
            margin: 0 0 7px;
            text-align: center;
            color: #0c2d4d;
        }


        .login-subtitle {
            text-align: center;
            color: #667085;
            font-size: 14px;
            margin-bottom: 20px;
        }


        /* =========================
           ERROR
           ========================= */

        .error-message {
            background: #fbe2df;
            color: #b03a2e;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: center;
        }


        /* =========================
           ROLE SELECTION
           ========================= */

        .role-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #1c2430;
        }


        .role-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
            margin-bottom: 18px;
        }


        .role-option {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            white-space: nowrap;
            cursor: pointer;
        }


        .role-option input {
            margin: 0;
            cursor: pointer;
        }


        /* =========================
           FORM
           ========================= */

        .form-group {
            margin-bottom: 15px;
        }


        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
        }


        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e2dc;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
        }


        .form-group input:focus {
            border-color: #185fa5;
        }


        /* =========================
           LOGIN BUTTON
           ========================= */

        .login-button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background: #0c2d4d;
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }


        .login-button:hover {
            background: #185fa5;
        }


        /* =========================
           FORGOT PASSWORD
           ========================= */

        .forgot-link {
            text-align: center;
            margin-top: 13px;
            font-size: 13px;
        }


        .forgot-link a {
            color: #185fa5;
            text-decoration: none;
        }


        .forgot-link a:hover {
            text-decoration: underline;
        }


        /* =========================
           REGISTRATION
           ========================= */

        .register-link {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e2dc;
            font-size: 13px;
            color: #667085;
        }


        .register-link a {
            color: #185fa5;
            font-weight: bold;
            text-decoration: none;
        }


        .register-link a:hover {
            text-decoration: underline;
        }

    </style>

</head>


<body>


<div class="login-page">

    <div class="login-card">

        <h2>Campus Cab</h2>

        <div class="login-subtitle">
            Login to your account
        </div>


        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="login.php"
            id="loginForm"
        >


            <!-- ROLE -->

            <div class="role-title">
                Login as
            </div>


            <div class="role-options">

                <label class="role-option">

                    <input
                        type="radio"
                        name="role"
                        value="student"
                        <?= $role === "student" ? "checked" : "" ?>
                    >

                    Student

                </label>


                <label class="role-option">

                    <input
                        type="radio"
                        name="role"
                        value="driver"
                        <?= $role === "driver" ? "checked" : "" ?>
                    >

                    Driver

                </label>


                <label class="role-option">

                    <input
                        type="radio"
                        name="role"
                        value="staff"
                        <?= $role === "staff" ? "checked" : "" ?>
                    >

                    Staff

                </label>


                <label class="role-option">

                    <input
                        type="radio"
                        name="role"
                        value="admin"
                        <?= $role === "admin" ? "checked" : "" ?>
                    >

                    Admin

                </label>

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    autocomplete="off"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    required
                >

            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                class="login-button"
            >
                Login
            </button>


        </form>


        <!-- FORGOT PASSWORD -->

        <div class="forgot-link">

            <a href="forgot_password.php">
                Forgot Password?
            </a>

        </div>


        <!-- REGISTRATION -->

        <div class="register-link">

            Don't have an account?

            <a
                href="student_registration.php"
                id="registerLink"
            >
                Register
            </a>

        </div>


    </div>

</div>


<script>


/* =========================
   ROLE BUTTONS
   ========================= */

const roleButtons =
    document.querySelectorAll('input[name="role"]');


const registerLink =
    document.getElementById("registerLink");


const emailInput =
    document.getElementById("email");


const passwordInput =
    document.getElementById("password");


const loginForm =
    document.getElementById("loginForm");



/* =========================
   CHANGE REGISTRATION LINK
   ========================= */

function updateRegistrationLink() {

    const selectedRole =
        document.querySelector(
            'input[name="role"]:checked'
        ).value;


    if (selectedRole === "student") {

        registerLink.href =
            "student_registration.php";

    }

    else if (selectedRole === "staff") {

        registerLink.href =
            "staff_registration.php";

    }

    else if (selectedRole === "driver") {

        registerLink.href =
            "registration.php";

    }

    else if (selectedRole === "admin") {

        registerLink.href =
            "registration.php";

    }

}



/* =========================
   ROLE CHANGE
   ========================= */

roleButtons.forEach(function(button) {

    button.addEventListener("change", function() {

        /*
         * Clear the email and password
         * when changing account type.
         */

        emailInput.value = "";
        passwordInput.value = "";

        updateRegistrationLink();

    });

});



/* =========================
   SET INITIAL REGISTRATION LINK
   ========================= */

updateRegistrationLink();



/* =========================
   PREVENT PASSWORD RESTORATION
   ========================= */

passwordInput.value = "";



/* =========================
   BROWSER BACK/FORWARD CACHE
   ========================= */

window.addEventListener("pageshow", function(event) {

    if (event.persisted) {

        emailInput.value = "";
        passwordInput.value = "";

        const studentRole =
            document.querySelector(
                'input[name="role"][value="student"]'
            );

        if (studentRole) {

            studentRole.checked = true;

        }

        updateRegistrationLink();

    }

});


</script>


</body>

</html>
