<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: login_driver.php");
    exit;
}

$pageTitle = "My Trips";
include 'header.php';
$driverID = $_SESSION['driver_id'];
?>
<div class="card">
    <h1>My trips</h1>
    <table>
        <tr><th>ID</th><th>Student</th><th>Pickup</th><th>Destination</th><th>Status</th></tr>
        <?php
        $result = mysqli_query($conn, "
            SELECT r.rideID, s.studentFname, s.studentLname, r.pickup_address, r.destination_address, r.ride_status
            FROM ride r
            JOIN student s ON r.studentID = s.studentID
            WHERE r.DriverID = $driverID
            ORDER BY r.rideID DESC
        ");
        if (mysqli_num_rows($result) == 0):
        ?>
        <tr><td colspan="5" class="empty">No trips yet — check <a href="driver.php">open requests</a>.</td></tr>
        <?php
        else:
            while ($row = mysqli_fetch_assoc($result)):
            $statusClass = str_replace(' ', '-', $row['ride_status']);
        ?>
        <tr>
            <td><a href="trip.php?id=<?php echo $row['rideID']; ?>">#<?php echo $row['rideID']; ?></a></td>
            <td><?php echo htmlspecialchars($row['studentFname']); ?></td>
            <td><?php echo htmlspecialchars($row['pickup_address']); ?></td>
            <td><?php echo htmlspecialchars($row['destination_address']); ?></td>
            <td><span class="status <?php echo $statusClass; ?>"><?php echo $row['ride_status']; ?></span></td>
        </tr>
        <?php
            endwhile;
        endif;
        ?>
    </table>
</div>
<?php include 'footer.php'; ?>