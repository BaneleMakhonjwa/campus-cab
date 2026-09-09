<?php
session_start();
<<<<<<< HEAD
include 'db_connect.php';

// Set page title for header
$pageTitle = "Login";
=======

include 'db_connect.php';
>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196

$error = "";
$selectedRole = "student";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $selectedRole = $_POST['role'] ?? "student";
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    // Check email and password
    if (empty($email) || empty($password)) {
<<<<<<< HEAD
        $error = "Please enter your email and password.";
=======

        $error = "Please enter your email and password.";

>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
    } else {

        // =========================
        // STUDENT LOGIN
        // =========================
        if ($selectedRole == "student") {

            $stmt = mysqli_prepare(
                $conn,
                "SELECT * FROM student
                 WHERE studentEmail = ?
                 AND studentAccount_status = 'Active'"
            );

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $student = mysqli_fetch_assoc($result);

            if ($student && password_verify($password, $student['studentPwd'])) {

                $_SESSION['student_id'] = $student['studentID'];
<<<<<<< HEAD
                $_SESSION['student_name'] = $student['studentFname'] . ' ' . $student['studentLname'];
                $_SESSION['role'] = "student";

                if (file_exists('student_dashboard.php')) {
                    header("Location: student_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;

            } else {
=======

                $_SESSION['student_name'] =
                    $student['studentFname'] . ' ' .
                    $student['studentLname'];

                $_SESSION['role'] = "student";

                header("Location: request.php");
                exit;

            } else {

>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
                $error = "Incorrect email or password.";
            }

            mysqli_stmt_close($stmt);
        }

<<<<<<< HEAD
=======

>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
        // =========================
        // DRIVER LOGIN
        // =========================
        elseif ($selectedRole == "driver") {

            $stmt = mysqli_prepare(
                $conn,
                "SELECT * FROM driver
                 WHERE DriverEmail = ?
                 AND driverAccount_Status = 'Active'"
            );

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $driver = mysqli_fetch_assoc($result);

            if ($driver && password_verify($password, $driver['password'])) {

                $_SESSION['driver_id'] = $driver['DriverID'];
<<<<<<< HEAD
                $_SESSION['driver_name'] = $driver['DriverFname'] . ' ' . $driver['DriverLname'];
                $_SESSION['role'] = "driver";

                if (file_exists('driver_dashboard.php')) {
                    header("Location: driver_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;

            } else {
=======

                $_SESSION['driver_name'] =
                    $driver['DriverFname'] . ' ' .
                    $driver['DriverLname'];

                $_SESSION['role'] = "driver";

                header("Location: driver.php");
                exit;

            } else {

>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
                $error = "Incorrect email or password.";
            }

            mysqli_stmt_close($stmt);
        }

<<<<<<< HEAD
=======

>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
        // =========================
        // STAFF LOGIN
        // =========================
        elseif ($selectedRole == "staff") {
<<<<<<< HEAD
            $error = "Staff login is not available yet.";
        }

=======

            $error = "Staff login is not available yet.";
        }


>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
        // =========================
        // ADMIN LOGIN
        // =========================
        elseif ($selectedRole == "admin") {
<<<<<<< HEAD
            $error = "Admin login is not available yet.";
        }

=======

            $error = "Admin login is not available yet.";
        }


>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
        // =========================
        // INVALID ROLE
        // =========================
        else {
<<<<<<< HEAD
=======

>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
            $error = "Invalid role selected.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<<<<<<< HEAD
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Campus Cab' : 'Campus Cab & Delivery'; ?></title>
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Login Page Specific Styles - Now inside container */
        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
            padding: 40px 20px;
        }

        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }

        .auth-card h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .auth-card h2 {
            text-align: center;
            color: #6c757d;
            font-size: 18px;
            font-weight: normal;
            margin-bottom: 25px;
        }

        .auth-card .msg {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .auth-card .msg.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .auth-card .msg.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .role-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 15px 0 20px 0;
        }

        .role-options label {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .role-options label:hover {
            background: #e9ecef;
        }

        .role-options input[type="radio"] {
            margin-right: 5px;
        }

        .role-options label:has(input:checked) {
            border-color: #007bff;
            background: #e7f1ff;
        }

        .auth-card label {
            display: block;
            font-weight: 600;
            margin: 15px 0 5px 0;
            font-size: 14px;
            color: #333;
        }

        .auth-card input[type="email"],
        .auth-card input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .auth-card input[type="email"]:focus,
        .auth-card input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }

        .login-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .login-buttons button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-buttons button[type="submit"] {
            background: #007bff;
            color: white;
        }

        .login-buttons button[type="submit"]:hover {
            background: #0056b3;
        }

        .login-buttons .cancel-button {
            background: #6c757d;
            color: white;
        }

        .login-buttons .cancel-button:hover {
            background: #5a6268;
        }

        .login-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-links p {
            margin: 8px 0;
        }

        .login-links a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        .login-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 25px;
            }
            
            .role-options {
                grid-template-columns: 1fr 1fr;
            }
            
            .login-buttons {
                flex-direction: column;
            }
        }
    </style>
=======

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Campus Cab - Login</title>

    <link rel="stylesheet" href="style.css">

>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
</head>

<body>

<<<<<<< HEAD
    <!-- ✅ HEADER INCLUDED -->
    <?php include 'header.php'; ?>

    <!-- ✅ WRAPPED IN CONTAINER FOR FULL WIDTH -->
    <div class="container">
        <div class="login-wrapper">

            <div class="auth-card">

                <h1>🚗 Campus Cab</h1>
                <h2>Login to your account</h2>

                <!-- ERROR MESSAGE -->
                <?php if (!empty($error)): ?>
                    <div class="msg error">
                        ❌ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">

                    <!-- ROLE SELECTION -->
                    <p><strong>Choose your role:</strong></p>

                    <div class="role-options">

                        <label>
                            <input
                                type="radio"
                                name="role"
                                value="student"
                                <?php if ($selectedRole == "student") echo "checked"; ?>
                                required
                            >
                            🎓 Student
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="role"
                                value="driver"
                                <?php if ($selectedRole == "driver") echo "checked"; ?>
                            >
                            🚗 Driver
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="role"
                                value="staff"
                                <?php if ($selectedRole == "staff") echo "checked"; ?>
                            >
                            👔 Staff
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="role"
                                value="admin"
                                <?php if ($selectedRole == "admin") echo "checked"; ?>
                            >
                            🔐 Admin
                        </label>

                    </div>

                    <!-- EMAIL -->
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                    >

                    <!-- PASSWORD -->
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                    <!-- LOGIN AND CANCEL BUTTONS -->
                    <div class="login-buttons">
                        <button type="submit" name="login">🔑 LOGIN</button>
                        <button type="reset" class="cancel-button">✖ CANCEL</button>
                    </div>

                    <!-- FORGOT PASSWORD & REGISTER -->
                    <div class="login-links">

                        <p>
                            <a href="forgot_password.php">🔓 Forgot password?</a>
                        </p>

                        <p>
                            Don't have an account?
                            <a href="register.php">Register here</a>
                        </p>

                    </div>

                </form>

            </div>

        </div>
    </div>

    <!-- ✅ FOOTER INCLUDED -->
    <?php include 'footer.php'; ?>

</body>
</html>
=======
<div class="auth-card">

    <h1>Campus Cab</h1>

    <h2>Login</h2>


    <!-- ERROR MESSAGE -->

    <?php if (!empty($error)): ?>

        <div class="msg error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST" action="login.php">


        <!-- ROLE SELECTION -->

        <p>
            <strong>Please choose your role:</strong>
        </p>

        <div class="role-options">

            <!-- STUDENT -->

            <label>

                <input
                    type="radio"
                    name="role"
                    value="student"
                    <?php
                    if ($selectedRole == "student")
                        echo "checked";
                    ?>
                    required
                >

                Student

            </label>


            <!-- DRIVER -->

            <label>

                <input
                    type="radio"
                    name="role"
                    value="driver"
                    <?php
                    if ($selectedRole == "driver")
                        echo "checked";
                    ?>
                >

                Driver

            </label>


            <!-- STAFF -->

            <label>

                <input
                    type="radio"
                    name="role"
                    value="staff"
                    <?php
                    if ($selectedRole == "staff")
                        echo "checked";
                    ?>
                >

                Staff

            </label>


            <!-- ADMIN -->

            <label>

                <input
                    type="radio"
                    name="role"
                    value="admin"
                    <?php
                    if ($selectedRole == "admin")
                        echo "checked";
                    ?>
                >

                Admin

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
            placeholder="Enter your email"
            value="<?php echo htmlspecialchars($email); ?>"
            required
        >


        <!-- PASSWORD -->

        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        >


        <!-- LOGIN AND CANCEL BUTTONS -->

        <div class="login-buttons">

            <button
                type="submit"
                name="login">
                LOGIN
            </button>


            <button
                type="reset"
                class="cancel-button">
                CANCEL
            </button>

        </div>


        <!-- FORGOT PASSWORD -->

        <div class="login-links">

            <p>
                <a href="forgot_password.php">
                    Forgot password?
                </a>
            </p>


            <!-- REGISTER -->

            <p>

                You are not registered?

                <a href="register_student.php">
                    REGISTER
                </a>

            </p>

        </div>

    </form>

</div>

</body>

</html>
>>>>>>> 8930311c38b8fcc9985224cf77a736ac5ca35196
