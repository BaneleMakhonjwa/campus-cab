```php
<?php

session_start();

require_once "db_connect.php";


require_once __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$error = "";
$success = "";




function sendTemporaryPasswordEmail($recipientEmail, $temporaryPassword)
{

    $mail = new PHPMailer(true);

    try {

        

        $mail->isSMTP();

        $mail->Host = 'YOUR_SMTP_HOST';

        $mail->SMTPAuth = true;

        $mail->Username = 'YOUR_SENDER_EMAIL';

        $mail->Password = 'YOUR_EMAIL_PASSWORD';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;


        

        $mail->setFrom(
            'YOUR_SENDER_EMAIL',
            'Campus Cab'
        );


        

        $mail->addAddress($recipientEmail);


        

        $mail->isHTML(true);

        $mail->Subject = 'Campus Cab - Temporary Password';


        
        $mail->Body = "

        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>

            <h2>Campus Cab</h2>

            <p>Hello,</p>

            <p>
                We received a request to reset your Campus Cab password.
            </p>

            <p>
                Your temporary password is:
            </p>

            <div style='
                background: #f2f2f2;
                padding: 15px;
                font-size: 20px;
                font-weight: bold;
                display: inline-block;
                letter-spacing: 1px;
            '>
                " . htmlspecialchars($temporaryPassword) . "
            </div>

            <p>
                Use this temporary password on the Campus Cab
                password reset page to create your new password.
            </p>

            <p>
                <strong>Important:</strong>
                Do not share this temporary password with anyone.
            </p>

            <p>
                If you did not request a password reset,
                please ignore this email.
            </p>

            <p>
                Regards,<br>
                <strong>Campus Cab Team</strong>
            </p>

        </div>
        ";


        

        $mail->AltBody =
            "Campus Cab\n\n" .
            "Your temporary password is: " .
            $temporaryPassword .
            "\n\n" .
            "Use this temporary password to reset your Campus Cab password.\n\n" .
            "If you did not request a password reset, please ignore this email.";


        

        $mail->send();

        return true;

    } catch (Exception $e) {

        return false;
    }
}




if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? "");

    $role = $_POST['role'] ?? "";


    

    if (empty($role)) {

        $error = "Please select your role.";

    }


    

    elseif (empty($email)) {

        $error = "Please enter your email address.";

    }


    else {


        

        if ($role === "student") {

            $sql = "SELECT
                        studentID,
                        studentEmail,
                        studentPwd,
                        studentAccount_status
                    FROM student
                    WHERE studentEmail = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            /*
             * ACCOUNT NOT FOUND
             */

            if ($result->num_rows === 0) {

                $error =
                    "No student account was found with this email.";

            }


            else {

                $user = $result->fetch_assoc();


                /*
                 * CHECK ACCOUNT STATUS
                 */

                if ($user['studentAccount_status'] !== 'Active') {

                    $error =
                        "This student account is inactive.";

                }


                else {


                    /*
                     * GENERATE TEMPORARY PASSWORD
                     */

                    $temporary_password =
                        "Temp@" . random_int(1000, 9999);


                    /*
                     * SEND EMAIL FIRST
                     */

                    $emailSent = sendTemporaryPasswordEmail(
                        $email,
                        $temporary_password
                    );


                    if (!$emailSent) {

                        $error =
                            "The temporary password could not be sent. Please check the email settings.";

                    }


                    else {


                        /*
                         * HASH TEMPORARY PASSWORD
                         */

                        $hashed_temporary_password =
                            password_hash(
                                $temporary_password,
                                PASSWORD_DEFAULT
                            );


                        /*
                         * SAVE TEMPORARY PASSWORD
                         */

                        $update_sql =
                            "UPDATE student
                             SET studentPwd = ?
                             WHERE studentEmail = ?";

                        $update_stmt =
                            $conn->prepare($update_sql);

                        $update_stmt->bind_param(
                            "ss",
                            $hashed_temporary_password,
                            $email
                        );


                        if ($update_stmt->execute()) {


                            /*
                             * SAVE RESET INFORMATION
                             */

                            $_SESSION['reset_role'] =
                                $role;

                            $_SESSION['reset_email'] =
                                $email;


                            /*
                             * SUCCESS MESSAGE
                             */

                            $success =
                                "A temporary password has been sent to your university email address.";

                        }


                        else {

                            $error =
                                "The temporary password was sent, but the account could not be updated.";

                        }


                        $update_stmt->close();

                    }

                }

            }

            $stmt->close();

        }


        /*
         * ==========================================
         * DRIVER
         * ==========================================
         */

        elseif ($role === "driver") {

            $sql = "SELECT
                        DriverID,
                        DriverEmail,
                        password,
                        driverAccount_Status
                    FROM driver
                    WHERE DriverEmail = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            /*
             * ACCOUNT NOT FOUND
             */

            if ($result->num_rows === 0) {

                $error =
                    "No driver account was found with this email.";

            }


            else {

                $user = $result->fetch_assoc();


                /*
                 * CHECK ACCOUNT STATUS
                 */

                if ($user['driverAccount_Status'] !== 'Active') {

                    $error =
                        "This driver account is inactive.";

                }


                else {


                    /*
                     * GENERATE TEMPORARY PASSWORD
                     */

                    $temporary_password =
                        "Temp@" . random_int(1000, 9999);


                    /*
                     * SEND EMAIL FIRST
                     */

                    $emailSent = sendTemporaryPasswordEmail(
                        $email,
                        $temporary_password
                    );


                    if (!$emailSent) {

                        $error =
                            "The temporary password could not be sent. Please check the email settings.";

                    }


                    else {


                        /*
                         * HASH TEMPORARY PASSWORD
                         */

                        $hashed_temporary_password =
                            password_hash(
                                $temporary_password,
                                PASSWORD_DEFAULT
                            );


                        /*
                         * SAVE TEMPORARY PASSWORD
                         */

                        $update_sql =
                            "UPDATE driver
                             SET password = ?
                             WHERE DriverEmail = ?";

                        $update_stmt =
                            $conn->prepare($update_sql);

                        $update_stmt->bind_param(
                            "ss",
                            $hashed_temporary_password,
                            $email
                        );


                        if ($update_stmt->execute()) {


                            /*
                             * SAVE RESET INFORMATION
                             */

                            $_SESSION['reset_role'] =
                                $role;

                            $_SESSION['reset_email'] =
                                $email;


                            /*
                             * SUCCESS MESSAGE
                             */

                            $success =
                                "A temporary password has been sent to your email address.";

                        }


                        else {

                            $error =
                                "The temporary password was sent, but the account could not be updated.";

                        }


                        $update_stmt->close();

                    }

                }

            }

            $stmt->close();

        }


        /*
         * ==========================================
         * STAFF
         * ==========================================
         */

        elseif ($role === "staff") {

            $error =
                "Staff password reset will be added after we confirm the Staff table structure.";

        }


        /*
         * ==========================================
         * INVALID ROLE
         * ==========================================
         */

        else {

            $error =
                "Invalid role selected.";

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

    <title>
        Campus Cab - Forgot Password
    </title>

    <link rel="stylesheet"
          href="style.css">

</head>


<body>


<div class="auth-card">


    <h1>
        Campus Cab
    </h1>


    <h2>
        Reset Password
    </h2>


    <p>
        Select your role and enter the email address
        registered with Campus Cab.
    </p>


    <!-- ERROR -->

    <?php if (!empty($error)): ?>

        <div class="msg error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <!-- SUCCESS -->

    <?php if (!empty($success)): ?>

        <div class="msg success">

            <?php
            echo htmlspecialchars($success);
            ?>

            <br><br>

            <a href="reset_password.php">
                Continue to Reset Password
            </a>

        </div>

    <?php endif; ?>


    <?php if (empty($success)): ?>


    <form method="POST"
          action="forgot_password.php"
          autocomplete="off">


        <!-- ROLE -->

        <p>
            <strong>
                Choose your role:
            </strong>
        </p>


        <div class="role-options">


            <label>

                <input
                    type="radio"
                    name="role"
                    value="student"
                    required
                >

                Student

            </label>


            <label>

                <input
                    type="radio"
                    name="role"
                    value="driver"
                >

                Driver

            </label>


            <label>

                <input
                    type="radio"
                    name="role"
                    value="staff"
                >

                Staff

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
            placeholder="Enter your registered email"
            autocomplete="off"
            required
        >


        <!-- BUTTONS -->

        <div class="login-buttons">


            <button type="submit">

                SEND TEMPORARY PASSWORD

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

                Remember your password?

                <a href="login.php">

                    Back to Login

                </a>

            </p>

        </div>


    </form>


    <?php endif; ?>


</div>


</body>

</html>
```
