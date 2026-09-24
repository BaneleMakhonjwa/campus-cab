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
    die("Passenger verification is only available after the driver has arrived.");
}

$userType = '';
$userID = 0;

if (!empty($ride['studentID'])) {

    $userType = 'Student';
    $userID = (int)$ride['studentID'];

} elseif (!empty($ride['staff_id'])) {

    $userType = 'Staff';
    $userID = (int)$ride['staff_id'];

} else {

    die("Passenger could not be identified.");
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($pinEnabled) {

        $enteredPIN = trim($_POST['passenger_pin'] ?? '');

        if ($enteredPIN === '') {

            $error = "Please enter the passenger PIN.";

        } elseif (
            !preg_match('/^\d{4,6}$/', $enteredPIN)
        ) {

            $error = "Please enter a valid 4 to 6 digit PIN.";

        } elseif (
            !password_verify(
                $enteredPIN,
                $safety['passenger_pin']
            )
        ) {

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

            if ($started) {

                $notificationType = "ride_started";
                $notificationMessage = "Your ride has started.";

                $notificationStudentID =
                    ($userType === 'Student') ? $userID : 0;

                $notificationStaffID =
                    ($userType === 'Staff') ? $userID : 0;

                $notificationStmt = $conn->prepare("
                    INSERT INTO notifications
                    (
                        studentID,
                        staff_id,
                        driver_id,
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
                        NULL,
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

            $error = "The ride could not be started.";
        }

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

        if ($started) {

            $notificationType = "ride_started";
            $notificationMessage = "Your ride has started.";

            $notificationStudentID =
                ($userType === 'Student') ? $userID : 0;

            $notificationStaffID =
                ($userType === 'Staff') ? $userID : 0;

            $notificationStmt = $conn->prepare("
                INSERT INTO notifications
                (
                    studentID,
                    staff_id,
                    driver_id,
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
                    NULL,
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
        padding: 0 20px 50px;
    }

    .verify-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 5px 18px rgba(11, 31, 58, 0.07);
    }

    .verify-card h1 {
        margin: 0 0 10px;
        color: var(--navy);
    }

    .verify-subtitle {
        color: var(--muted);
        line-height: 1.6;
        margin-bottom: 25px;
    }

    .passenger-box {
        background: #f5f7fa;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 20px;
    }

    .arrival-box {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .error-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: var(--ink);
    }

    .pin-input {
        width: 100%;
        box-sizing: border-box;
        padding: 15px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 22px;
        letter-spacing: 7px;
        text-align: center;
    }

    .verify-button {
        width: 100%;
        border: none;
        background: var(--teal);
        color: #fff;
        padding: 15px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: var(--navy);
        text-decoration: none;
        font-weight: 600;
    }

    .security-note {
        margin-top: 20px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.5;
        text-align: center;
    }
</style>

<div class="verify-container">

    <a href="driver_ride_requests.php" class="back-link">
        ← Back to Ride Requests
    </a>

    <div class="verify-card">

        <h1>Verify Passenger</h1>

        <p class="verify-subtitle">
            Confirm the passenger before starting the ride.
        </p>

        <div class="passenger-box">
            <strong>Passenger Type:</strong>
            <?php echo htmlspecialchars($userType); ?>
        </div>

        <div class="arrival-box">
            <strong>✓ Driver Arrival Confirmed</strong><br>
            You have arrived at the passenger's pickup location.
        </div>

        <?php if ($error !== ''): ?>

            <div class="error-box">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <?php if ($pinEnabled): ?>

            <form method="POST">

                <div class="form-group">

                    <label for="passenger_pin">
                        Passenger Safety PIN
                    </label>

                    <input
                        type="password"
                        id="passenger_pin"
                        name="passenger_pin"
                        class="pin-input"
                        inputmode="numeric"
                        pattern="[0-9]{4,6}"
                        minlength="4"
                        maxlength="6"
                        autocomplete="off"
                        placeholder="••••"
                        required
                    >

                </div>

                <button type="submit" class="verify-button">
                    Verify Passenger & Start Ride
                </button>

            </form>

        <?php else: ?>

            <form method="POST">

                <button type="submit" class="verify-button">
                    Start Ride
                </button>

            </form>

        <?php endif; ?>

        <div class="security-note">
            The passenger's actual Safety PIN is never displayed to the driver.
        </div>

    </div>

</div>
