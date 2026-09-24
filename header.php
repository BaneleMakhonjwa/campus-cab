<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

$driverID = (int)($_SESSION['driver_id'] ?? 0);
$rideID = (int)($_GET['rideID'] ?? $_POST['rideID'] ?? 0);

if ($driverID <= 0) {
    die("Driver not logged in.");
}

if ($rideID <= 0) {
    die("Invalid ride.");
}

$stmt = $conn->prepare("
    SELECT r.*
    FROM ride r
    WHERE r.rideID = ?
      AND r.DriverID = ?
    LIMIT 1
");

$stmt->bind_param("ii", $rideID, $driverID);
$stmt->execute();

$result = $stmt->get_result();
$ride = $result->fetch_assoc();

$stmt->close();

if (!$ride) {
    die("Ride not found or this ride does not belong to you.");
}

if ($ride['ride_status'] !== 'Driver Arrived') {
    die("This ride cannot be started.");
}

/* Identify passenger */
$userType = '';
$userID = 0;

if (!empty($ride['studentID'])) {

    $userType = 'Student';
    $userID = (int)$ride['studentID'];

} elseif (!empty($ride['staffID'])) {

    $userType = 'Staff';
    $userID = (int)$ride['staff_id'];

} else {
    die("Passenger could not be identified.");
}

/* Get passenger safety settings */
$stmt = $conn->prepare("
    SELECT passenger_pin, pin_verification
    FROM passenger_safety
    WHERE user_type = ?
      AND userID = ?
    LIMIT 1
");

$stmt->bind_param("si", $userType, $userID);
$stmt->execute();

$safetyResult = $stmt->get_result();
$safety = $safetyResult->fetch_assoc();

$stmt->close();

$pinEnabled = false;

if (
    $safety &&
    (int)$safety['pin_verification'] === 1 &&
    !empty($safety['passenger_pin'])
) {
    $pinEnabled = true;
}

$error = '';

/* Start ride */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $started = false;

    if (!$pinEnabled) {

        $stmt = $conn->prepare("
            UPDATE ride
            SET ride_status = 'In Progress'
            WHERE rideID = ?
              AND DriverID = ?
              AND ride_status = 'Driver Arrived'
        ");

        $stmt->bind_param("ii", $rideID, $driverID);
        $stmt->execute();

        $started = $stmt->affected_rows > 0;

        $stmt->close();

    } else {

        $enteredPIN = trim($_POST['passenger_pin'] ?? '');

        if ($enteredPIN === '') {

            $error = "Please enter the passenger PIN.";

        } elseif (!password_verify($enteredPIN, $safety['passenger_pin'])) {

            $error = "Incorrect passenger PIN. The ride has not started.";

        } else {

            $stmt = $conn->prepare("
                UPDATE ride
                SET ride_status = 'In Progress'
                WHERE rideID = ?
                  AND DriverID = ?
                  AND ride_status = 'Driver Arrived'
            ");

            $stmt->bind_param("ii", $rideID, $driverID);
            $stmt->execute();

            $started = $stmt->affected_rows > 0;

            $stmt->close();
        }
    }

    /* Create notification after ride successfully starts */
    if ($started) {

        $notificationStudentID = !empty($ride['studentID'])
            ? (int)$ride['studentID']
            : 0;

        $notificationStaffID = !empty($ride['staffID'])
            ? (int)$ride['staff_id']
            : 0;

        $notificationType = "ride_started";
        $notificationMessage = "Your ride has started.";

        $notificationStmt = $conn->prepare("
            INSERT INTO notifications
            (
                studentID,
                staffID,
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

        header("Location: driver_ride_requests.php");
        exit;
    }

    if (!$error) {
        $error = "The ride could not be started.";
    }
}

$pageTitle = "Verify Passenger";

include 'header.php';

?>

<style>
.verify-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 0 20px;
}

.verify-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.verify-title {
    color: #0c2d4d;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
}

.verify-subtitle {
    color: #666;
    margin-bottom: 25px;
}

.passenger-type {
    display: inline-block;
    background: #0c2d4d;
    color: #ffffff;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #0c2d4d;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 13px 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    outline: none;
}

.form-group input:focus {
    border-color: #185fa5;
}

.verify-button {
    width: 100%;
    border: none;
    background: #0c2d4d;
    color: #ffffff;
    padding: 13px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
}

.verify-button:hover {
    background: #185fa5;
}

.error-message {
    background: #fbeaea;
    color: #b03a2e;
    padding: 12px 14px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.security-note {
    margin-top: 20px;
    padding: 14px;
    background: #f6f5f1;
    border-radius: 8px;
    color: #555;
    font-size: 13px;
    line-height: 1.5;
}

.back-link {
    display: inline-block;
    margin-top: 20px;
    color: #0c2d4d;
    text-decoration: none;
    font-weight: 600;
}

.back-link:hover {
    color: #185fa5;
}
</style>

<div class="verify-container">

    <div class="verify-card">

        <div class="verify-title">
            Verify Passenger
        </div>

        <div class="verify-subtitle">
            Verify the passenger before starting this ride.
        </div>

        <div class="passenger-type">
            <?php echo htmlspecialchars($userType); ?> Passenger
        </div>

        <?php if ($error): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <?php if ($pinEnabled): ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="rideID"
                    value="<?php echo $rideID; ?>"
                >

                <div class="form-group">

                    <label for="passenger_pin">
                        Passenger Safety PIN
                    </label>

                    <input
                        type="password"
                        id="passenger_pin"
                        name="passenger_pin"
                        maxlength="20"
                        inputmode="numeric"
                        autocomplete="off"
                        placeholder="Enter passenger PIN"
                        required
                    >

                </div>

                <button type="submit" class="verify-button">
                    Verify Passenger &amp; Start Ride
                </button>

            </form>

            <div class="security-note">
                The passenger must provide their safety PIN before the ride can start.
                The driver's screen never displays the stored PIN.
            </div>

        <?php else: ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="rideID"
                    value="<?php echo $rideID; ?>"
                >

                <button type="submit" class="verify-button">
                    Start Ride
                </button>

            </form>

            <div class="security-note">
                PIN verification is not enabled for this passenger.
                The ride can start without PIN verification.
            </div>

        <?php endif; ?>

        <a href="driver_ride_requests.php" class="back-link">
            ← Back to Ride Requests
        </a>

    </div>

</div>