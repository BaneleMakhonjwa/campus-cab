<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit;
}

$pageTitle = "Request a Ride";
include 'header.php';

$message = "";
$studentID = $_SESSION['student_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pickupID = intval($_POST['pickup_location']);
    $destinationID = intval($_POST['destination_location']);
    $estDistance = floatval($_POST['estimated_distance_km']);
    $estDuration = intval($_POST['estimated_duration_min']);
    $estPrice = floatval($_POST['estimated_price']);

    $pickup = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM campus_location WHERE location_id = $pickupID"));
    $destination = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM campus_location WHERE location_id = $destinationID"));

    if (!$pickup || !$destination || $pickupID == $destinationID) {
        $message = "<div class='msg error'>Please choose two different locations.</div>";
    } else {
        $sql = "INSERT INTO ride
                (studentID, DriverID, pickup_address, pickup_latitude, pickup_longitude,
                 destination_address, destination_latitude, destination_longitude,
                 estimated_distance_km, estimated_duration_min, estimated_price,
                 requested_at, ride_status)
                VALUES
                ($studentID, NULL, '{$pickup['name']}', {$pickup['latitude']}, {$pickup['longitude']},
                 '{$destination['name']}', {$destination['latitude']}, {$destination['longitude']},
                 $estDistance, $estDuration, $estPrice,
                 NOW(), 'Requested')";

        if (mysqli_query($conn, $sql)) {
            $message = "<div class='msg success'>Request submitted at an estimated R" . number_format($estPrice, 2) . ". A nearby driver will see it shortly.</div>";
        } else {
            $message = "<div class='msg error'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

$locations = mysqli_query($conn, "SELECT * FROM campus_location ORDER BY name ASC");
$locationList = [];
while ($loc = mysqli_fetch_assoc($locations)) {
    $locationList[] = $loc;
}
?>
<div class="card narrow">
    <h1>Request a ride</h1>
    <?php echo $message; ?>
    <form method="POST" id="requestForm">
        <label>Pickup location</label>
        <select name="pickup_location" id="pickup" required>
            <option value="">Select pickup...</option>
            <?php foreach ($locationList as $loc): ?>
            <option value="<?php echo $loc['location_id']; ?>"
                    data-lat="<?php echo $loc['latitude']; ?>"
                    data-lng="<?php echo $loc['longitude']; ?>">
                <?php echo htmlspecialchars($loc['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <label>Destination</label>
        <select name="destination_location" id="destination" required>
            <option value="">Select destination...</option>
            <?php foreach ($locationList as $loc): ?>
            <option value="<?php echo $loc['location_id']; ?>"
                    data-lat="<?php echo $loc['latitude']; ?>"
                    data-lng="<?php echo $loc['longitude']; ?>">
                <?php echo htmlspecialchars($loc['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <div id="estimateBox" class="estimate-box" style="display:none;">
            <div><span class="label">Distance</span><span id="estDistanceText">—</span></div>
            <div><span class="label">Est. time</span><span id="estDurationText">—</span></div>
            <div><span class="label">Est. price</span><span id="estPriceText">—</span></div>
        </div>

        <input type="hidden" name="estimated_distance_km" id="estDistanceInput">
        <input type="hidden" name="estimated_duration_min" id="estDurationInput">
        <input type="hidden" name="estimated_price" id="estPriceInput">

        <button type="submit" class="btn-primary full" id="submitBtn" disabled>Send request</button>
    </form>
</div>

<script>
// Pricing model: base fare + per-km rate, time from an assumed average campus speed.
// This is a straight-line (as-the-crow-flies) estimate, not a road-routed distance.
const BASE_FARE = 10;
const RATE_PER_KM = 6;
const AVG_SPEED_KMH = 25;

function haversineKm(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) ** 2 +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function updateEstimate() {
    const pickupEl = document.getElementById('pickup');
    const destEl = document.getElementById('destination');
    const pickupOpt = pickupEl.options[pickupEl.selectedIndex];
    const destOpt = destEl.options[destEl.selectedIndex];

    if (!pickupOpt.dataset.lat || !destOpt.dataset.lat || pickupEl.value === destEl.value) {
        document.getElementById('estimateBox').style.display = 'none';
        document.getElementById('submitBtn').disabled = true;
        return;
    }

    const distance = haversineKm(
        parseFloat(pickupOpt.dataset.lat), parseFloat(pickupOpt.dataset.lng),
        parseFloat(destOpt.dataset.lat), parseFloat(destOpt.dataset.lng)
    );
    const durationMin = Math.max(2, Math.round((distance / AVG_SPEED_KMH) * 60));
    const price = BASE_FARE + (distance * RATE_PER_KM);

    document.getElementById('estDistanceText').textContent = distance.toFixed(2) + ' km';
    document.getElementById('estDurationText').textContent = durationMin + ' min';
    document.getElementById('estPriceText').textContent = 'R' + price.toFixed(2);

    document.getElementById('estDistanceInput').value = distance.toFixed(2);
    document.getElementById('estDurationInput').value = durationMin;
    document.getElementById('estPriceInput').value = price.toFixed(2);

    document.getElementById('estimateBox').style.display = 'grid';
    document.getElementById('submitBtn').disabled = false;
}

document.getElementById('pickup').addEventListener('change', updateEstimate);
document.getElementById('destination').addEventListener('change', updateEstimate);
</script>

<?php include 'footer.php'; ?>