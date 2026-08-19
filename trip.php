<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = "Trip Details";
include 'header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<?php

$rideID = intval($_GET['id'] ?? 0);
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'complete') {
    $deliveryFee = floatval($_POST['delivery_fee']);
    $itemCost = floatval($_POST['item_cost']);
    $total = $deliveryFee + $itemCost;

    mysqli_query($conn, "UPDATE ride SET ride_status = 'Completed', Completed_at = NOW() WHERE rideID = $rideID");
    mysqli_query($conn, "INSERT INTO payment (ride_id, item_cost, delivery_fee, total_amount, payment_status, paid_at)
                          VALUES ($rideID, $itemCost, $deliveryFee, $total, 'Paid', NOW())");
    $message = "<div class='msg success'>Trip marked complete and payment recorded.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'rate') {
    $studentRating = intval($_POST['student_rating']);
    $driverRating = intval($_POST['driver_rating']);
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);

    mysqli_query($conn, "INSERT INTO rating (ride_id, student_rating, driver_rating, comments, rated_at)
                          VALUES ($rideID, $studentRating, $driverRating, '$comments', NOW())");
    $message = "<div class='msg success'>Rating submitted.</div>";
}

$ride = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT r.*, s.studentFname, s.studentLname, d.DriverFname, d.DriverLname
    FROM ride r
    JOIN student s ON r.studentID = s.studentID
    LEFT JOIN driver d ON r.DriverID = d.DriverID
    WHERE r.rideID = $rideID
"));

$vehicle = $ride && $ride['DriverID'] ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM vehicle WHERE DriverID = {$ride['DriverID']}")) : null;
$payment = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM payment WHERE ride_id = $rideID"));
$rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM rating WHERE ride_id = $rideID"));
$locations = mysqli_query($conn, "SELECT * FROM location WHERE ride_id = $rideID ORDER BY recorded_at ASC");
?>

<?php echo $message; ?>

<?php if (!$ride): ?>
    <div class="card"><p>Ride not found.</p></div>
<?php else: ?>

<div class="card">
    <h1>Trip #<?php echo $rideID; ?>
        <span class="status <?php echo str_replace(' ', '-', $ride['ride_status']); ?>">
            <?php echo $ride['ride_status']; ?>
        </span>
    </h1>
    <div class="trip-grid">
        <div><span class="label">Student</span><?php echo htmlspecialchars($ride['studentFname'] . ' ' . $ride['studentLname']); ?></div>
        <div><span class="label">Driver</span><?php echo $ride['DriverFname'] ? htmlspecialchars($ride['DriverFname'] . ' ' . $ride['DriverLname']) : 'Not yet assigned'; ?></div>
        <?php if ($vehicle): ?>
        <div><span class="label">Vehicle</span><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['colour'] . ')'); ?></div>
        <?php endif; ?>
        <div><span class="label">Pickup</span><?php echo htmlspecialchars($ride['pickup_address']); ?></div>
        <div><span class="label">Destination</span><?php echo htmlspecialchars($ride['destination_address']); ?></div>
        <div><span class="label">Requested</span><?php echo $ride['requested_at']; ?></div>
        <?php if ($ride['estimated_distance_km']): ?>
        <div><span class="label">Est. distance</span><?php echo number_format($ride['estimated_distance_km'], 2); ?> km</div>
        <div><span class="label">Est. time</span><?php echo $ride['estimated_duration_min']; ?> min</div>
        <div><span class="label">Est. price</span>R<?php echo number_format($ride['estimated_price'], 2); ?></div>
        <?php endif; ?>
    </div>
</div>

<?php
$activeStatuses = ['Accepted', 'Driver Arriving', 'In Progress'];
$isActiveTrip = in_array($ride['ride_status'], $activeStatuses);
$isAssignedDriver = isset($_SESSION['driver_id']) && $_SESSION['driver_id'] == $ride['DriverID'];
$hasLocationHistory = mysqli_num_rows($locations) > 0;
?>

<?php if ($isActiveTrip || $hasLocationHistory): ?>
<div class="card">
    <h2>Live tracking</h2>

    <?php if ($isAssignedDriver && $isActiveTrip): ?>
        <p id="shareStatus" class="hint" style="text-align:left; margin-bottom:10px;">Not currently sharing your location.</p>
        <button type="button" id="shareBtn" class="btn-primary">Start sharing my location</button>
    <?php endif; ?>

    <div id="map" style="height:280px; border-radius:10px; margin-top:14px;"></div>
    <p id="lastUpdate" class="hint" style="text-align:left; margin-top:8px;"></p>
</div>

<script>
(function() {
    var rideID = <?php echo $rideID; ?>;
    var map = L.map('map').setView([-32.7830, 26.8430], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    var marker = null;

    function updateMap(lat, lng, recordedAt) {
        if (!marker) {
            marker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 16);
        } else {
            marker.setLatLng([lat, lng]);
        }
        document.getElementById('lastUpdate').textContent = 'Last updated: ' + recordedAt;
    }

    function pollLocation() {
        fetch('get_location.php?ride_id=' + rideID)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.found) updateMap(data.lat, data.lng, data.recorded_at);
            });
    }

    pollLocation();
    setInterval(pollLocation, 5000);

    <?php if ($isAssignedDriver && $isActiveTrip): ?>
    var watchId = null;
    document.getElementById('shareBtn').addEventListener('click', function() {
        if (!navigator.geolocation) {
            document.getElementById('shareStatus').textContent = 'Geolocation is not supported on this browser.';
            return;
        }
        watchId = navigator.geolocation.watchPosition(function(pos) {
            var lat = pos.coords.latitude;
            var lng = pos.coords.longitude;
            document.getElementById('shareStatus').textContent = 'Sharing your live location...';

            var formData = new FormData();
            formData.append('ride_id', rideID);
            formData.append('lat', lat);
            formData.append('lng', lng);
            fetch('save_location.php', { method: 'POST', body: formData });

            updateMap(lat, lng, 'just now');
        }, function(err) {
            document.getElementById('shareStatus').textContent = 'Could not get your location: ' + err.message;
        }, { enableHighAccuracy: true, maximumAge: 4000, timeout: 8000 });
    });
    <?php endif; ?>
})();
</script>
<?php endif; ?>

<div class="card">
    <h2>Payment</h2>
    <?php if ($payment): ?>
        <div class="trip-grid">
            <div><span class="label">Item cost</span>R<?php echo number_format($payment['item_cost'], 2); ?></div>
            <div><span class="label">Delivery fee</span>R<?php echo number_format($payment['delivery_fee'], 2); ?></div>
            <div><span class="label">Total</span>R<?php echo number_format($payment['total_amount'], 2); ?></div>
            <div><span class="label">Status</span><?php echo $payment['payment_status']; ?></div>
        </div>
    <?php elseif (in_array($ride['ride_status'], ['In Progress', 'Accepted'])): ?>
        <form method="POST">
            <input type="hidden" name="action" value="complete">
            <label>Item cost (R0 for a normal ride)</label>
            <input type="number" step="0.01" name="item_cost" value="0.00">
            <label>Delivery fee</label>
            <input type="number" step="0.01" name="delivery_fee" value="<?php echo $ride['estimated_price'] ? number_format($ride['estimated_price'], 2, '.', '') : '35.00'; ?>" required>
            <button type="submit" class="btn-primary">Mark trip complete &amp; record payment</button>
        </form>
    <?php else: ?>
        <p class="empty">No payment yet.</p>
    <?php endif; ?>
</div>

<?php if ($ride['ride_status'] == 'Completed'): ?>
<div class="card">
    <h2>Rating</h2>
    <?php if ($rating): ?>
        <div class="trip-grid">
            <div><span class="label">Driver rated</span><?php echo $rating['driver_rating']; ?>/5</div>
            <div><span class="label">Student rated</span><?php echo $rating['student_rating']; ?>/5</div>
        </div>
        <?php if ($rating['comments']): ?><p><?php echo htmlspecialchars($rating['comments']); ?></p><?php endif; ?>
    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="action" value="rate">
            <label>Rate the driver (1–5)</label>
            <input type="number" name="driver_rating" min="1" max="5" required>
            <label>Rate the student (1–5)</label>
            <input type="number" name="student_rating" min="1" max="5" required>
            <label>Comments (optional)</label>
            <textarea name="comments" rows="2"></textarea>
            <button type="submit" class="btn-primary">Submit rating</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
<?php include 'footer.php'; ?>