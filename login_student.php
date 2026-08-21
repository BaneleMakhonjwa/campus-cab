<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db_connect.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM student WHERE studentEmail = '$email'");
    $student = mysqli_fetch_assoc($result);

    if ($student && password_verify($password, $student['studentPwd'])) {
        $_SESSION['student_id'] = $student['studentID'];
        $_SESSION['student_name'] = $student['studentFname'] . ' ' . $student['studentLname'];
        header("Location: request.php");
        exit;
    } else {
        $error = "Incorrect email or password.";
    }
}

$pageTitle = "Student Login";
include 'header.php';
?>
<div class="auth-card">
    <h1>Student login</h1>
    <?php if ($error): ?><div class="msg error"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@example.com" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Log in</button>
    </form>
    <p class="hint">Demo accounts: any student email from your sample data, password <code>campus123</code></p>
    <p class="hint">Are you a driver? <a href="login_driver.php">Log in here</a></p>
    <p class="register-link">
    Don't have an account? <a href="register_student.php">Register here</a>
</p>
</div>
<?php include 'footer.php'; ?>