<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

$driverID = (int)($_SESSION['driver_id'] ?? 0);

if ($driverID <= 0) {
    http_response_code(401);
    echo "Driver not logged in.";
    exit;
}

$status = $_POST['status'] ?? '';

if (!in_array($status, ['Available', 'Unavailable'], true)) {
    http_response_code(400);
    echo "Invalid status.";
    exit;
}

$stmt = $conn->prepare("
    UPDATE driver
    SET availability_status = ?
    WHERE DriverID = ?
");

if (!$stmt) {
    http_response_code(500);
    echo "Database error.";
    exit;
}

$stmt->bind_param("si", $status, $driverID);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Unable to update availability.";
}

$stmt->close();