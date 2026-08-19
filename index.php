<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = "Home";
include 'header.php';

$isLoggedIn = isset($_SESSION['student_id']) || isset($_SESSION['driver_id']);
?>

<?php if (!$isLoggedIn): ?>
<section class="hero">
    <h1>Getting around campus, sorted.</h1>
    <p>Book a ride, get food delivered, or drive and earn — all in one place.</p>
    <div class="hero-actions">
        <a href="login_student.php" class="btn-primary">I need a ride</a>
        <a href="login_driver.php" class="btn-outline-light">I'm a driver</a>
    </div>
</section>
<?php endif; ?>

<section class="card">
    <h2>Recent activity</h2>
    <table>
        <tr><th>ID</th><th>Student</th><th>Driver</th><th>Pickup</th><th>Destination</th><th>Status</th></tr>
        <?php
        $result = mysqli_query($conn, "
            SELECT r.rideID, s.studentFname, s.studentLname,
                   d.DriverFname, d.DriverLname,
                   r.pickup_address, r.destination_address, r.ride_status
            FROM ride r
            JOIN student s ON r.studentID = s.studentID
            LEFT JOIN driver d ON r.DriverID = d.DriverID
            ORDER BY r.rideID DESC
            LIMIT 10
        ");
        while ($row = mysqli_fetch_assoc($result)):
            $statusClass = str_replace(' ', '-', $row['ride_status']);
        ?>
        <tr>
            <td><a href="trip.php?id=<?php echo $row['rideID']; ?>">#<?php echo $row['rideID']; ?></a></td>
            <td><?php echo htmlspecialchars($row['studentFname']); ?></td>
            <td><?php echo $row['DriverFname'] ? htmlspecialchars($row['DriverFname']) : '—'; ?></td>
            <td><?php echo htmlspecialchars($row['pickup_address']); ?></td>
            <td><?php echo htmlspecialchars($row['destination_address']); ?></td>
            <td><span class="status <?php echo $statusClass; ?>"><?php echo $row['ride_status']; ?></span></td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>

<?php include 'footer.php'; ?>