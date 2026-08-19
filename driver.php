<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: login_driver.php");
    exit;
}

$pageTitle = "Open Requests";
include 'header.php';

$message = "";
$driverID = $_SESSION['driver_id'];

if (isset($_GET['accept'])) {
    $rideID = intval($_GET['accept']);
    $sql = "UPDATE ride SET DriverID = $driverID, ride_status = 'Accepted', accepted_at = NOW()
            WHERE rideID = $rideID AND DriverID IS NULL";
    mysqli_query($conn, $sql);

    if (mysqli_affected_rows($conn) > 0) {
        $message = "<div class='msg success'>Request #$rideID accepted — check My Trips.</div>";
    } else {
        $message = "<div class='msg error'>Too late — another driver already accepted this one.</div>";
    }
}
?>
<div class="card">
    <h1>Open requests</h1>
    <?php echo $message; ?>
    <table>
        <tr><th>ID</th><th>Pickup</th><th>Destination</th><th>Distance</th><th>Price</th><th>Requested at</th><th></th></tr>
        <?php
        $result = mysqli_query($conn, "
            SELECT rideID, pickup_address, destination_address, requested_at,
                   estimated_distance_km, estimated_price
            FROM ride WHERE ride_status = 'Requested' AND DriverID IS NULL
            ORDER BY requested_at ASC
        ");
        if (mysqli_num_rows($result) == 0):
        ?>
        <tr><td colspan="7" class="empty">No open requests right now.</td></tr>
        <?php
        else:
            while ($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
            <td>#<?php echo $row['rideID']; ?></td>
            <td><?php echo htmlspecialchars($row['pickup_address']); ?></td>
            <td><?php echo htmlspecialchars($row['destination_address']); ?></td>
            <td><?php echo $row['estimated_distance_km'] ? number_format($row['estimated_distance_km'], 2) . ' km' : '—'; ?></td>
            <td class="price-tag"><?php echo $row['estimated_price'] ? 'R' . number_format($row['estimated_price'], 2) : '—'; ?></td>
            <td><?php echo $row['requested_at']; ?></td>
            <td><a href="driver.php?accept=<?php echo $row['rideID']; ?>"><button type="button">Accept</button></a></td>
        </tr>
        <?php
            endwhile;
        endif;
        ?>
    </table>
</div>

<?php include 'footer.php'; ?>