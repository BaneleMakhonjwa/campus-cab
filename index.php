<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = "Home";
include 'header.php';

$isLoggedIn = isset($_SESSION['student_id']) || isset($_SESSION['driver_id']);
?>

<?php if (!$isLoggedIn): ?>
<section class="hero">
    <h1>Getting around campus, sorted.</h1>
    <p>Book a ride, get parcel delivered or drive and earn — all in one place.</p>
    <div class="hero-actions">
        <a href="register_student.php" class="btn-primary">📝 Register</a>
        <a href="login_student.php" class="btn-outline-light">🧑🏿‍🎓I'm a student</a>
        <a href="login_driver.php" class="btn-outline-light">🚗 I'm a driver</a>
    </div>
</section>
<?php endif; ?>

<?php include 'footer.php'; ?>