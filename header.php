<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/db_connect.php';

$isStudent = isset($_SESSION['student_id']);
$isDriver = isset($_SESSION['driver_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Campus Cab' : 'Campus Cab & Delivery'; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <a href="index.php" class="brand">Campus<span>Cab</span></a>
    <nav>
        <?php if ($isStudent): ?>
            <a href="request.php">Request a Ride</a>
            <a href="my_rides.php">My Rides</a>
            <span class="who">Hi, <?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
            <a href="logout.php" class="btn-outline">Log out</a>
        <?php elseif ($isDriver): ?>
            <a href="driver.php">Open Requests</a>
            <a href="my_trips.php">My Trips</a>
            <span class="who">Hi, <?php echo htmlspecialchars($_SESSION['driver_name']); ?></span>
            <a href="logout.php" class="btn-outline">Log out</a>
        <?php else: ?>
            <a href="login_student.php">Student Login</a>
            <a href="login_driver.php" class="btn-outline">Driver Login</a>
        <?php endif; ?>
    </nav>
</header>
<main>