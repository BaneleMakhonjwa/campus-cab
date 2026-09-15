<?php
session_start();
include_once __DIR__ . '/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {
      // In login_student.php
$sql = "SELECT studentID, studentFname, studentLname, studentEmail, studentPwd, email_verified 
        FROM student WHERE studentEmail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            
            // Check if email is verified
            if ($student['email_verified'] == 0) {
                $error = "⚠️ Please verify your email before logging in. Check your inbox for the verification link.";
            } elseif (password_verify($password, $student['studentPwd'])) {
                // Login successful
                $_SESSION['student_id'] = $student['studentID'];
                $_SESSION['student_name'] = $student['studentFname'] . ' ' . $student['studentLname'];
                $_SESSION['student_email'] = $student['studentEmail'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "No account found with this email.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container login-container">
        <div class="login-box">
            <h1>🔑 Student Login</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login_student.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-primary btn-block">🔑 Login</button>
                
                <p class="register-link">
                    Don't have an account? <a href="register_student.php">Register here</a>
                </p>
                <p class="register-link">
                    <a href="resend_verification.php">Resend verification email</a>
                </p>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>