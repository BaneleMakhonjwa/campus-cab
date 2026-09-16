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
   LOGIN
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
                            $user["studentFname"] . " " .
                            $user["studentLname"];

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
                            $user["DriverFname"] . " " .
                            $user["DriverLname"];

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

                    /*
                    | IMPORTANT:
                    | Staff password is taken directly from
                    | the staff table "password" column.
                    */
                    if (password_verify($password, $user["password"])) {

                        session_regenerate_id(true);

                        $_SESSION["logged_in"] = true;
                        $_SESSION["role"] = "staff";
                        $_SESSION["user_id"] = $user["staffID"];
                        $_SESSION["email"] = $user["staffEmail"];
                        $_SESSION["name"] =
                            $user["staffFname"] . " " .
                            $user["staffLname"];

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
                            $user["adminFname"] . " " .
                            $user["adminLname"];

                        header("Location: admin.php");
                        exit;
                    }
                }

                $stmt->close();
            }

            $error = "Invalid admin email or password.";
        }
    }

    /* Never keep password after failed login */
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

    <link rel="stylesheet" href="style.css">

    <style>

        .login-page {
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            background: #f6f5f1;

            padding: 15px;
        }


        /* Same size as Forgot Password */

        .login-card {
            width: 100%;
            max-width: 360px;

            background: #ffffff;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);
        }


        .login-card h1 {
            margin: 0 0 6px;

            text-align: center;

            color: #0c2d4d;

            font-size: 25px;
        }


        .login-subtitle {
            margin: 0 0 20px;

            text-align: center;

            color: #667085;

            font-size: 13px;
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


        .role-container {
            margin-bottom: 18px;
        }


        .role-title {
            margin-bottom: 9px;

            color: #1c2430;

            font-size: 13px;

            font-weight: 600;
        }


        .role-options {
            display: flex;

            align-items: center;

            justify-content: flex-start;

            gap: 12px;

            margin: 8px 0 18px;

            flex-wrap: nowrap;
        }


        .role-options label {
            display: flex;

            align-items: center;

            gap: 4px;

            margin: 0;

            padding: 0;

            color: #1c2430;

            font-size: 12px;

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


        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;

            padding: 10px 11px;

            border: 1px solid #e2e2dc;

            border-radius: 6px;

            background: #ffffff;

            color: #1c2430;

            font-size: 13px;

            outline: none;
        }


        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #185fa5;
        }


        .login-button {
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


        .login-button:hover {
            background: #0c2d4d;
        }


        .login-links {
            margin-top: 16px;

            text-align: center;
        }


        .login-links a {
            color: #185fa5;

            font-size: 13px;

            text-decoration: none;
        }


        .login-links a:hover {
            text-decoration: underline;
        }


        .register-link {
            margin-top: 10px;

            text-align: center;

            color: #667085;

            font-size: 13px;
        }


        .register-link a {
            color: #185fa5;

            font-weight: 600;

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


        <h1>Campus Cab</h1>


        <p class="login-subtitle">
            Login to your account
        </p>


        <?php if ($error !== ""): ?>

            <div class="error-message">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="login.php"
            id="loginForm"
            autocomplete="off"
        >


            <div class="role-container">

                <div class="role-title">
                    Login as:
                </div>


                <div class="role-options">


                    <label>

                        <input
                            type="radio"
                            name="role"
                            value="student"
                            <?php
                            echo ($role === "student")
                                ? "checked"
                                : "";
                            ?>
                        >

                        Student

                    </label>


                    <label>

                        <input
                            type="radio"
                            name="role"
                            value="driver"
                            <?php
                            echo ($role === "driver")
                                ? "checked"
                                : "";
                            ?>
                        >

                        Driver

                    </label>


                    <label>

                        <input
                            type="radio"
                            name="role"
                            value="staff"
                            <?php
                            echo ($role === "staff")
                                ? "checked"
                                : "";
                            ?>
                        >

                        Staff

                    </label>


                    <label>

                        <input
                            type="radio"
                            name="role"
                            value="admin"
                            <?php
                            echo ($role === "admin")
                                ? "checked"
                                : "";
                            ?>
                        >

                        Admin

                    </label>


                </div>

            </div>


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


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    value=""
                    autocomplete="new-password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >
                Login
            </button>


        </form>


        <div class="login-links">

            <a href="forgot_password.php">
                Forgot Password?
            </a>

        </div>


        <div class="register-link">

            Don't have an account?

            <a href="registration.php">
                Register
            </a>

        </div>


    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| Clear details when changing person type
|--------------------------------------------------------------------------
*/

const roleButtons =
    document.querySelectorAll(
        'input[name="role"]'
    );

const emailInput =
    document.getElementById("email");

const passwordInput =
    document.getElementById("password");


roleButtons.forEach(function(roleButton) {

    roleButton.addEventListener(
        "change",
        function() {

            emailInput.value = "";
            passwordInput.value = "";

            emailInput.focus();

        }
    );

});


/*
|--------------------------------------------------------------------------
| Clear password every time page loads
|--------------------------------------------------------------------------
*/

window.addEventListener(
    "load",
    function() {

        passwordInput.value = "";

    }
);


/*
|--------------------------------------------------------------------------
| Clear old details when returning with browser Back button
|--------------------------------------------------------------------------
*/

window.addEventListener(
    "pageshow",
    function(event) {

        passwordInput.value = "";

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

    }
);

</script>


</body>

</html>
