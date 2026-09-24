<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

$driverID = (int)($_SESSION['driver_id'] ?? 0);
$rideID = (int)($_POST['rideID'] ?? $_GET['rideID'] ?? 0);

if ($driverID <= 0) {
    die("Driver not logged in.");
}

if ($rideID <= 0) {
    die("Invalid ride.");
}

$stmt = $conn->prepare("
    SELECT
        studentID,
        staff_id,
        estimated_price
    FROM ride
    WHERE rideID = ?
      AND DriverID = ?
      AND ride_status = 'In Progress'
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $rideID,
    $driverID
);

$stmt->execute();

$result = $stmt->get_result();
$ride = $result->fetch_assoc();

$stmt->close();

if (!$ride) {
    die("Ride not found or this ride cannot be completed.");
}

$stmt = $conn->prepare("
    UPDATE ride
    SET
        ride_status = 'Completed',
        driver_earning = ROUND(estimated_price * 0.80, 2),
        campus_commission = ROUND(estimated_price * 0.20, 2),
        Completed_at = NOW()
    WHERE rideID = ?
      AND DriverID = ?
      AND ride_status = 'In Progress'
");

$stmt->bind_param(
    "ii",
    $rideID,
    $driverID
);

$stmt->execute();

$completed =
    $stmt->affected_rows > 0;

$stmt->close();

if ($completed) {

    $notificationStudentID =
        !empty($ride['studentID'])
            ? (int)$ride['studentID']
            : 0;

    $notificationStaffID =
        !empty($ride['staff_id'])
            ? (int)$ride['staff_id']
            : 0;

    $notificationType =
        "ride_completed";

    $notificationMessage =
        "Your ride has been completed.";

    $notificationStmt = $conn->prepare("
        INSERT INTO notifications
        (
            studentID,
            staff_id,
            rideID,
            parcelID,
            notification_type,
            message,
            is_read,
            created_at
        )
        VALUES
        (
            NULLIF(?, 0),
            NULLIF(?, 0),
            ?,
            NULL,
            ?,
            ?,
            0,
            NOW()
        )
    ");

    if ($notificationStmt) {

        $notificationStmt->bind_param(
            "iiiss",
            $notificationStudentID,
            $notificationStaffID,
            $rideID,
            $notificationType,
            $notificationMessage
        );

        $notificationStmt->execute();

        $notificationStmt->close();
    }
}

$pageTitle = "Ride Completed";

include 'header.php';

?>

<style>

.completion-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 0 20px;
}

.completion-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 35px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.completion-title {
    color: #0c2d4d;
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 10px;
}

.completion-message {
    color: #666;
    margin-bottom: 25px;
    line-height: 1.5;
}

.back-button {
    display: inline-block;
    background: #0c2d4d;
    color: #ffffff;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
}

.back-button:hover {
    background: #185fa5;
}

</style>

<div class="completion-container">

    <div class="completion-card">

        <?php if ($completed): ?>

            <div class="completion-title">
                Ride Completed
            </div>

            <div class="completion-message">
                The ride has been successfully completed.
            </div>

        <?php else: ?>

            <div class="completion-title">
                Ride Could Not Be Completed
            </div>

            <div class="completion-message">
                This ride may already have been completed or is no longer active.
            </div>

        <?php endif; ?>

        <a
            href="driver_ride_requests.php"
            class="back-button"
        >
            Back to Ride Requests
        </a>

    </div>

</div>

</main>

</body>
</html>