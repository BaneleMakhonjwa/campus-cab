<?php
session_start();
include_once __DIR__ . '/db_connect.php';

$message = '';
$message_type = '';

// Get token from URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $message = "No verification token provided.";
    $message_type = 'error';
} else {
    // Find student with this token
    $sql = "SELECT studentID, studentFname, studentLname, email_verified, token_expiry 
            FROM student 
            WHERE verification_token = ? AND email_verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $message = "Invalid verification token. The link may have already been used or expired.";
        $message_type = 'error';
    } else {
        $student = $result->fetch_assoc();
        
        // Check if token expired
        $current_time = date('Y-m-d H:i:s');
        if ($student['token_expiry'] < $current_time) {
            $message = "Verification link has expired. Please register again.";
            $message_type = 'error';
        } else {
            // Verify the email
            $update_sql = "UPDATE student SET email_verified = 1, verification_token = NULL, token_expiry = NULL 
                           WHERE studentID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $student['studentID']);
            
            if ($update_stmt->execute()) {
                $name = $student['studentFname'] . ' ' . $student['studentLname'];
                $message = "✅ Email verified successfully! $name, your account is now active. You can now log in.";
                $message_type = 'success';
            } else {
                $message = "Failed to verify email. Please try again.";
                $message_type = 'error';
            }
            $update_stmt->close();
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container verify-container">
        <div class="verify-box">
            <h1>📧 Email Verification</h1>
            
            <?php if ($message_type === 'success'): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                    <br><br>
                    <a href="login_student.php" class="btn-primary">🔑 Login Now</a>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($message); ?>
                    <br><br>
                    <a href="register_student.php" class="btn-secondary">📝 Register Again</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>