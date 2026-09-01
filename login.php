<?php
session_start();

include 'db_connect.php';

$error = "";
$selectedRole = "student";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $selectedRole = $_POST['role'] ?? "student";
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    // Check email and password
    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

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

                $_SESSION['student_name'] =
                    $student['studentFname'] . ' ' .
                    $student['studentLname'];

                $_SESSION['role'] = "student";

                header("Location: request.php");
                exit;

            } else {

                $error = "Incorrect email or password.";
            }

            mysqli_stmt_close($stmt);
        }


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

                $_SESSION['driver_name'] =
                    $driver['DriverFname'] . ' ' .
                    $driver['DriverLname'];

                $_SESSION['role'] = "driver";

                header("Location: driver.php");
                exit;

            } else {

                $error = "Incorrect email or password.";
            }

            mysqli_stmt_close($stmt);
        }


        // =========================
        // STAFF LOGIN
        // =========================
        elseif ($selectedRole == "staff") {

            $error = "Staff login is not available yet.";
        }


        // =========================
        // ADMIN LOGIN
        // =========================
        elseif ($selectedRole == "admin") {

            $error = "Admin login is not available yet.";
        }


        // =========================
        // INVALID ROLE
        // =========================
        else {

            $error = "Invalid role selected.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Campus Cab - Login</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

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
