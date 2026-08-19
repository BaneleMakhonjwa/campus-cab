<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['driver_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in as a driver']);
    exit;
}

$driverID = $_SESSION['driver_id'];
$rideID = intval($_POST['ride_id'] ?? 0);
$lat = floatval($_POST['lat'] ?? 0);
$lng = floatval($_POST['lng'] ?? 0);

// Only allow logging location for a ride actually assigned to this driver
$check = mysqli_query($conn, "SELECT rideID FROM ride WHERE rideID = $rideID AND DriverID = $driverID");
if (mysqli_num_rows($check) == 0) {
    http_response_code(403);
    echo json_encode(['error' => 'This ride is not assigned to you']);
    exit;
}

$sql = "INSERT INTO location (ride_id, driver_id, latitude, longitude, recorded_at)
        VALUES ($rideID, $driverID, $lat, $lng, NOW())";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['status' => 'ok']);
} else {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($conn)]);
}
?>