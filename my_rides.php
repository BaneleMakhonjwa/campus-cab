<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit;
}

$pageTitle = "My Rides";
include 'header.php';
$studentID = $_SESSION['student_id'];
?>
<div class="card">
    <h1>My rides</h1>
    <table>
        <tr><th>ID</th><th>Driver</th><th>Pickup</th><th>Destination</th><th>Status</th></tr>
        <?php
        $result = mysqli_query($conn, "
            SELECT r.rideID, d.DriverFname, d.DriverLname, r.pickup_address, r.destination_address, r.ride_status
            FROM ride r
            LEFT JOIN driver d ON r.DriverID = d.DriverID
            WHERE r.studentID = $studentID
            ORDER BY r.rideID DESC
        ");
        if (mysqli_num_rows($result) == 0):
        ?>
        <tr><td colspan="5" class="empty">No rides yet — <a href="request.php">request one</a>.</td></tr>
        <?php
        else:
            while ($row = mysqli_fetch_assoc($result)):
            $statusClass = str_replace(' ', '-', $row['ride_status']);
        ?>
        <tr>
            <td><a href="trip.php?id=<?php echo $row['rideID']; ?>">#<?php echo $row['rideID']; ?></a></td>
            <td><?php echo $row['DriverFname'] ? htmlspecialchars($row['DriverFname']) : '—'; ?></td>
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