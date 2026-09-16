```php
<?php
session_start();

require_once "db_connect.php";

$error = "";
$success = false;

// Make sure the user came from forgot_password.php
if (!isset($_SESSION['reset_role']) || !isset($_SESSION['reset_email'])) {

    header("Location: forgot_password.php");
    exit;
}

$role = $_SESSION['reset_role'];
$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $temporary_password = trim($_POST['temporary_password'] ?? "");
    $new_password = $_POST['new_password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";


    // Check all fields
    if (
        empty($temporary_password) ||
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $error = "Please complete all fields.";

    }

    // Check passwords match
    elseif ($new_password !== $confirm_password) {

        $error = "New password and confirm password do not match.";

    }

    // Check password length
    elseif (strlen($new_password) < 8) {

        $error = "Password must be at least 8 characters.";

    }

    // Check for number
    elseif (!preg_match('/[0-9]/', $new_password)) {

        $error = "Password must contain a number.";

    }

    // Check for special character
    elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {

        $error = "Password must contain a special character.";

    }

    else {


        /*
         * =========================
         * STUDENT
         * =========================
         */

        if ($role === "student") {

            $sql = "SELECT studentID, studentPwd, studentAccount_status
                    FROM student
                    WHERE studentEmail = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows === 0) {

                $error = "No student account was found with this email.";

            } else {

                $user = $result->fetch_assoc();


                // Check account status
                if ($user['studentAccount_status'] !== 'Active') {

                    $error = "This student account is inactive.";

                } else {


                    // Check temporary password
                    if (!password_verify(
                        $temporary_password,
                        $user['studentPwd']
                    )) {

                        $error = "The temporary password is incorrect.";

                    } else {


                        // Hash new password
                        $hashed_password = password_hash(
                            $new_password,
                            PASSWORD_DEFAULT
                        );


                        // Update student password
                        $update_sql = "UPDATE student
                                       SET studentPwd = ?
                                       WHERE studentEmail = ?";

                        $update_stmt = $conn->prepare($update_sql);

                        $update_stmt->bind_param(
                            "ss",
                            $hashed_password,
                            $email
                        );


                        if ($update_stmt->execute()) {

                            // Password changed successfully
                            $success = true;

                            // Clear reset session
                            unset($_SESSION['reset_role']);
                            unset($_SESSION['reset_email']);

                        } else {

                            $error = "Password could not be updated.";

                        }


                        $update_stmt->close();

                    }

                }

            }


            $stmt->close();

        }


        /*
         * =========================
         * DRIVER
         * =========================
         */

        elseif ($role === "driver") {

            $sql = "SELECT DriverID, password, driverAccount_Status
                    FROM driver
                    WHERE DriverEmail = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows === 0) {

                $error = "No driver account was found with this email.";

            } else {

                $user = $result->fetch_assoc();


                // Check account status
                if ($user['driverAccount_Status'] !== 'Active') {

                    $error = "This driver account is inactive.";

                } else {


                    // Check temporary password
                    if (!password_verify(
                        $temporary_password,
                        $user['password']
                    )) {

                        $error = "The temporary password is incorrect.";

                    } else {


                        // Hash new password
                        $hashed_password = password_hash(
                            $new_password,
                            PASSWORD_DEFAULT
                        );


                        // Update driver password
                        $update_sql = "UPDATE driver
                                       SET password = ?
                                       WHERE DriverEmail = ?";

                        $update_stmt = $conn->prepare($update_sql);

                        $update_stmt->bind_param(
                            "ss",
                            $hashed_password,
                            $email
                        );


                        if ($update_stmt->execute()) {

                            // Password changed successfully
                            $success = true;

                            // Clear reset session
                            unset($_SESSION['reset_role']);
                            unset($_SESSION['reset_email']);

                        } else {

                            $error = "Password could not be updated.";

                        }


                        $update_stmt->close();

                    }

                }

            }


            $stmt->close();

        }


        /*
         * =========================
         * STAFF
         * =========================
         */

        elseif ($role === "staff") {

            $error = "Staff password reset will be added after we confirm the Staff table structure.";

        }


        /*
         * =========================
         * INVALID ROLE
         * =========================
         */

        else {

            $error = "Invalid account role.";

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

    <title>Campus Cab - Reset Password</title>

    <link rel="stylesheet" href="style.css">

</head>


<body>


<div class="auth-card">


    <h1>Campus Cab</h1>


    <?php if ($success): ?>

        <!-- SUCCESS MESSAGE -->

        <div class="success-box">

            <h2>Password Changed Successfully!</h2>

            <p>
                You have successfully changed your password.
            </p>

            <p>
                You can now login using your new password.
            </p>

            <a href="login.php" class="login-now-button">
                CLICK HERE TO LOGIN
            </a>

        </div>


    <?php else: ?>


        <h2>Reset Password</h2>


        <p>
            Enter the temporary password sent to your email,
            then create your new password.
        </p>


        <!-- ERROR MESSAGE -->

        <?php if (!empty($error)): ?>

            <div class="msg error">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              action="reset_password.php"
              autocomplete="off">


            <!-- TEMPORARY PASSWORD -->

            <label for="temporary_password">
                Temporary Password
            </label>

            <input
                type="password"
                id="temporary_password"
                name="temporary_password"
                placeholder="Enter temporary password"
                autocomplete="off"
                required
            >


            <!-- NEW PASSWORD -->

            <label for="new_password">
                New Password
            </label>

            <input
                type="password"
                id="new_password"
                name="new_password"
                placeholder="Enter new password"
                autocomplete="new-password"
                required
            >


            <!-- PASSWORD REQUIREMENTS -->

            <div class="password-requirements">

                <p id="lengthRequirement"
                   class="requirement">
                    ✓ 8+ characters
                </p>

                <p id="numberRequirement"
                   class="requirement">
                    ✓ 1 number
                </p>

                <p id="specialRequirement"
                   class="requirement">
                    ✓ 1 special character (! @ #)
                </p>

            </div>


            <!-- CONFIRM PASSWORD -->

            <label for="confirm_password">
                Confirm New Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="Confirm new password"
                autocomplete="new-password"
                required
            >


            <!-- BUTTONS -->

            <div class="login-buttons">

                <button type="submit">
                    RESET PASSWORD
                </button>


                <button
                    type="button"
                    class="cancel-button"
                    onclick="window.location.href='login.php';">

                    CANCEL

                </button>

            </div>


            <!-- BACK TO LOGIN -->

            <div class="login-links">

                <p>

                    <a href="login.php">
                        Back to Login
                    </a>

                </p>

            </div>


        </form>


    <?php endif; ?>


</div>


<!-- PASSWORD REQUIREMENT CHECK -->

<script>

const newPassword =
    document.getElementById("new_password");

const lengthRequirement =
    document.getElementById("lengthRequirement");

const numberRequirement =
    document.getElementById("numberRequirement");

const specialRequirement =
    document.getElementById("specialRequirement");


if (newPassword) {

    newPassword.addEventListener("input", function () {

        const password = newPassword.value;


        // 8 characters
        if (password.length >= 8) {

            lengthRequirement.classList.add("valid");

        } else {

            lengthRequirement.classList.remove("valid");

        }


        // Number
        if (/[0-9]/.test(password)) {

            numberRequirement.classList.add("valid");

        } else {

            numberRequirement.classList.remove("valid");

        }


        // Special character
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {

            specialRequirement.classList.add("valid");

        } else {

            specialRequirement.classList.remove("valid");

        }

    });

}

</script>


<style>

.password-requirements {

    margin-top: 5px;

    margin-bottom: 15px;

}


.requirement {

    color: red;

    font-size: 13px;

    margin: 3px 0;

}


.requirement.valid {

    color: green;

}


/* SUCCESS BOX */

.success-box {

    text-align: center;

    margin-top: 25px;

}


.success-box h2 {

    color: green;

    margin-bottom: 15px;

}


.success-box p {

    margin: 8px 0;

}


/* LOGIN BUTTON */

.login-now-button {

    display: inline-block;

    margin-top: 20px;

    padding: 12px 25px;

    background-color: #007bff;

    color: white;

    text-decoration: none;

    border-radius: 5px;

    font-weight: bold;

}


.login-now-button:hover {

    background-color: #0056b3;

}

</style>


</body>

</html>
```
