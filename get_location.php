<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['student_id']) && !isset($_SESSION['driver_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$rideID = intval($_GET['ride_id'] ?? 0);

$result = mysqli_query($conn, "
    SELECT latitude, longitude, recorded_at
    FROM location
    WHERE ride_id = $rideID
    ORDER BY recorded_at DESC
    LIMIT 1
");

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'found' => true,
        'lat' => (float)$row['latitude'],
        'lng' => (float)$row['longitude'],
        'recorded_at' => $row['recorded_at']
    ]);
} else {
    echo json_encode(['found' => false]);
}
?>