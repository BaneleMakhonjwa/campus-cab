<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db_connect.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM driver WHERE DriverEmail = '$email'");
    $driver = mysqli_fetch_assoc($result);

    if ($driver && password_verify($password, $driver['password'])) {
        $_SESSION['driver_id'] = $driver['DriverID'];
        $_SESSION['driver_name'] = $driver['DriverFname'] . ' ' . $driver['DriverLname'];
        header("Location: driver.php");
        exit;
    } else {
        $error = "Incorrect email or password.";
    }
}

$pageTitle = "Driver Login";
include 'header.php';
?>
<div class="auth-card">
    <h1>Driver login</h1>
    <?php if ($error): ?><div class="msg error"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@example.com" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Log in</button>
    </form>
    <p class="hint">Demo accounts: any driver email from your sample data, password <code>campus123</code></p>
    <p class="hint">Are you a student? <a href="login_student.php">Log in here</a></p>
</div>
<?php include 'footer.php'; ?>