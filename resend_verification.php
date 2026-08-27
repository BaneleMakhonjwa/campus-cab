<?php
session_start();
include_once __DIR__ . '/db_connect.php';
include_once __DIR__ . '/mail_config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if student exists and is not verified
        $sql = "SELECT studentID, studentFname, studentLname, email_verified, verification_token 
                FROM student WHERE studentEmail = ? AND email_verified = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "No unverified account found with this email.";
        } else {
            $student = $result->fetch_assoc();
            
            // Generate new token
            $new_token = bin2hex(random_bytes(32));
            $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $update_sql = "UPDATE student SET verification_token = ?, token_expiry = ? WHERE studentID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $new_token, $token_expiry, $student['studentID']);
            
            if ($update_stmt->execute()) {
                // Send new verification email
                $email_sent = sendVerificationEmail($email, $student['studentFname'] . ' ' . $student['studentLname'], $new_token);
                
                if ($email_sent) {
                    $success = "A new verification email has been sent to $email. Please check your inbox.";
                } else {
                    $error = "Failed to send email. Please contact support.";
                }
            } else {
                $error = "Failed to resend verification. Please try again.";
            }
            $update_stmt->close();
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
    <title>Resend Verification | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container verify-container">
        <div class="verify-box">
            <h1>📧 Resend Verification Email</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                    <br><br>
                    <a href="login_student.php" class="btn-primary">🔑 Go to Login</a>
                </div>
            <?php else: ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Enter your email address</label>
                    <input type="email" id="email" name="email" placeholder="StudentNo@ufh.ac.za" required>
                </div>
                <button type="submit" class="btn-primary btn-block">📧 Resend Verification</button>
                <p class="login-link">
                    <a href="login_student.php">← Back to Login</a>
                </p>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>