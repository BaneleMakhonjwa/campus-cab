<?php
session_start();

include 'db_connect.php';

$error = "";
$selectedRole = "student";
$email = "";

// Clear old login session details when opening login page
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION = [];
    session_regenerate_id(true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $selectedRole = $_POST['role'] ?? "student";
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        // STUDENT LOGIN
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

                session_regenerate_id(true);

                $_SESSION['student_id'] = $student['studentID'];

                $_SESSION['student_name'] =
                    $student['studentFname'] . " " .
                    $student['studentLname'];

                $_SESSION['role'] = "student";

                header("Location: request.php");
                exit;

            } else {

                $error = "Incorrect email or password.";
            }

            mysqli_stmt_close($stmt);
        }

        // DRIVER LOGIN
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

                session_regenerate_id(true);

                $_SESSION['driver_id'] = $driver['DriverID'];

                $_SESSION['driver_name'] =
                    $driver['DriverFname'] . " " .
                    $driver['DriverLname'];

                $_SESSION['role'] = "driver";

                header("Location: driver.php");
                exit;

            } else {

                $error = "Incorrect email or password.";
            }

            mysqli_stmt_close($stmt);
        }

        // STAFF LOGIN
        elseif ($selectedRole == "staff") {

            $error = "Staff login is not available yet.";
        }

        // ADMIN LOGIN
        elseif ($selectedRole == "admin") {

            $error = "Admin login is not available yet.";
        }

        // INVALID ROLE
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Campus Cab - Login</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="auth-card">

    <h1>Campus Cab</h1>

    <h2>Login</h2>

    <?php if (!empty($error)): ?>

        <div class="msg error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="off">

        <p>
            <strong>Please choose your role:</strong>
        </p>

        <div class="role-options">

            <label>
                <input
                    type="radio"
                    name="role"
                    value="student"
                    <?php echo ($selectedRole == "student") ? "checked" : ""; ?>
                    required
                >
                Student
            </label>

            <label>
                <input
                    type="radio"
                    name="role"
                    value="driver"
                    <?php echo ($selectedRole == "driver") ? "checked" : ""; ?>
                >
                Driver
            </label>

            <label>
                <input
                    type="radio"
                    name="role"
                    value="staff"
                    <?php echo ($selectedRole == "staff") ? "checked" : ""; ?>
                >
                Staff
            </label>

            <label>
                <input
                    type="radio"
                    name="role"
                    value="admin"
                    <?php echo ($selectedRole == "admin") ? "checked" : ""; ?>
                >
                Admin
            </label>

        </div>

        <label for="email">
            Email
        </label>

        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your email"
            value="<?php echo htmlspecialchars($email); ?>"
            autocomplete="off"
            required
        >

        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            autocomplete="new-password"
            required
        >

        <div class="login-buttons">

            <button
                type="submit"
                name="login">
                LOGIN
            </button>

            <button
                type="button"
                class="cancel-button"
                onclick="window.location.href='login.php';">
                CANCEL
            </button>

        </div>

        <div class="login-links">

            <p>
                <a href="forgot_password.php">
                    Forgot password?
                </a>
            </p>

            <p>
                You are not registered?

                <a href="register_student.php">
                    REGISTER
                </a>
            </p>

        </div>

    </form>

</div>

<script>
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    roleInputs.forEach(function (roleInput) {

        roleInput.addEventListener('change', function () {

            emailInput.value = "";
            passwordInput.value = "";

            emailInput.focus();

        });

    });
</script>

</body>

</html>
