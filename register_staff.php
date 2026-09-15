<?php
include('db_connect.php');

$message = "";

if (isset($_POST['register'])) {
    $staff_number = $_POST['staff_number'];
    $staff_name = $_POST['staff_name'];
    $surname = $_POST['surname'];
    $staff_email = $_POST['staff_email'];
    $cell_number = $_POST['cell_number'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Check Staff Number length is exactly 9 characters
    if (strlen($staff_number) != 9) {
        $message = "Error: Staff Number must be exactly 9 characters.";
    // 2. Validate email structure restricts to University format (@ufh.ac.za)
    } elseif (!str_ends_with($staff_email, '@ufh.ac.za')) {
        $message = "Error: Email must be a valid staff email ending with @ufh.ac.za";
    // 3. Check Cell Number length is exactly 10 digits
    } elseif (strlen($cell_number) != 10 || !is_numeric($cell_number)) {
        $message = "Error: Cell number must be exactly 10 digits.";
    // 4. Match validation fields
    } elseif ($password !== $confirm_password) {
        $message = "Error: Passwords do not match.";
    } else {
        // Encrypt password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if credentials exist inside database
        $check = mysqli_query($conn, "SELECT * FROM staff WHERE staff_number='$staff_number' OR staff_email='$staff_email'");
        if (mysqli_num_rows($check) > 0) {
            $message = "Error: Staff Number or Email already registered.";
        } else {
            // Write record row entry to table database structure
            $sql = "INSERT INTO staff (staff_number, staff_name, surname, staff_email, cell_number, password) 
                    VALUES ('$staff_number', '$staff_name', '$surname', '$staff_email', '$cell_number', '$hashed_password')";
            
            if (mysqli_query($conn, $sql)) {
                $message = "Registration successful!";
            } else {
                $message = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Registration Page</title>
    <link rel="stylesheet" href="style.css">
    <style>
    .signupcontainer {
    max-width: 450px;
    margin: 40px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    font-family: Arial, sans-serif;
}
.signupcontainer input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}
.signupcontainer label {
    font-weight: bold;
    display: block;
    margin-top: 10px;
}
        .otp-container {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
        }
        .otp-container input { flex: 1; }
        .btn-otp { padding: 8px 12px; background: #6c757d; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .form-actions { display: flex; gap: 15px; margin-top: 20px; }
        .btn-reg { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; flex: 1; font-weight: bold; }
        .btn-cancel { background: #dc3545; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; text-align: center; text-decoration: none; flex: 1; font-weight: bold; }
    </style>
</head>
<body>

<div class="signupcontainer">
    <h2>Registration Page</h2>
    <h3>Staff</h3>
    
    <?php if(!empty($message)) {
        $color = str_contains($message, 'successful') ? 'green' : 'red';
        echo "<p style='color:$color; font-weight:bold;'>$message</p>"; 
    } ?>

    <form action="register_staff.php" method="POST">
        <label>Staff Number:</label>
        <input type="text" name="staff_number" maxlength="9" placeholder="9 characters" required>

        <label>Staff name:</label>
        <input type="text" name="staff_name" required>

        <label>Surname:</label>
        <input type="text" name="surname" required>

        <label>Staff email:</label>
        <input type="email" name="staff_email" placeholder="....@ufh.ac.za" required>

        <label>Cell number:</label>
        <input type="text" name="cell_number" maxlength="10" placeholder="10 digits" required>

        <label>Create password:</label>
        <input type="password" name="password" required>

        <label>Confirm password:</label>
        <input type="password" name="confirm_password" required>

        <label>Authentication by staff email:</label>
        <div class="otp-container">
            <input type="text" name="otp" placeholder="OTP">
            <button type="button" class="btn-otp">Get OTP</button>
        </div>

        <div class="form-actions">
            <button type="submit" name="register" class="btn-reg">Register</button>
            <a href="index.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>
