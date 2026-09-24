<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Ride Requests";

include 'header.php';

$driverID = (int)($_SESSION['driver_id'] ?? 0);

if ($driverID <= 0) {
    die("Driver ID not found.");
}


/*
|--------------------------------------------------------------------------
| ACCEPT RIDE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accept_ride'])) {

    $rideID = (int)($_POST['ride_id'] ?? 0);

    if ($rideID > 0) {

        /*
         * Get the passenger information first.
         */
        $passengerStmt = mysqli_prepare(
            $conn,
            "
            SELECT
                studentID,
                staffID,
                passenger_type
            FROM ride
            WHERE rideID = ?
              AND DriverID IS NULL
              AND ride_status = 'Requested'
            LIMIT 1
            "
        );

        if ($passengerStmt) {

            mysqli_stmt_bind_param(
                $passengerStmt,
                "i",
                $rideID
            );

            mysqli_stmt_execute($passengerStmt);

            $passengerResult = mysqli_stmt_get_result($passengerStmt);

            $passenger = mysqli_fetch_assoc($passengerResult);

            mysqli_stmt_close($passengerStmt);


            if ($passenger) {

                /*
                 * Assign the ride to this driver.
                 */
                $stmt = mysqli_prepare(
                    $conn,
                    "
                    UPDATE ride
                    SET
                        DriverID = ?,
                        ride_status = 'Accepted',
                        accepted_at = NOW()
                    WHERE rideID = ?
                      AND DriverID IS NULL
                      AND ride_status = 'Requested'
                    "
                );

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ii",
                        $driverID,
                        $rideID
                    );

                    $updated = mysqli_stmt_execute($stmt);

                    $affectedRows = mysqli_stmt_affected_rows($stmt);

                    mysqli_stmt_close($stmt);


                    /*
                     * Only notify if this driver actually
                     * successfully accepted the ride.
                     */
                    if ($updated && $affectedRows === 1) {

                        $studentID = !empty($passenger['studentID'])
                            ? (int)$passenger['studentID']
                            : null;

                        $staffID = !empty($passenger['staffID'])
                            ? (int)$passenger['staffID']
                            : null;

                        $notificationType = "ride_accepted";
                        $message = "Your ride has been accepted by a driver.";


                        $notificationStmt = mysqli_prepare(
                            $conn,
                            "
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
                            VALUES (?, ?, ?, NULL, ?, ?, 0, NOW())
                            "
                        );


                        if ($notificationStmt) {

                            mysqli_stmt_bind_param(
                                $notificationStmt,
                                "iiiss",
                                $studentID,
                                $staffID,
                                $rideID,
                                $notificationType,
                                $message
                            );

                            mysqli_stmt_execute($notificationStmt);

                            mysqli_stmt_close($notificationStmt);
                        }
                    }
                }
            }
        }
    }

    header("Location: driver_ride_requests.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| MARK DRIVER AS ARRIVED
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_arrived'])) {

    header('Content-Type: application/json');

    $rideID = (int)($_POST['ride_id'] ?? 0);
    $driverLatitude = (float)($_POST['driver_latitude'] ?? 0);
    $driverLongitude = (float)($_POST['driver_longitude'] ?? 0);


    if (
        $rideID <= 0 ||
        $driverLatitude == 0 ||
        $driverLongitude == 0
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Unable to verify your location.'
        ]);

        exit;
    }


    /*
     * Get pickup coordinates and passenger information.
     */
    $stmt = mysqli_prepare(
        $conn,
        "
        SELECT
            rideID,
            studentID,
            staffID,
            pickup_latitude,
            pickup_longitude
        FROM ride
        WHERE rideID = ?
          AND DriverID = ?
          AND ride_status = 'Accepted'
        LIMIT 1
        "
    );


    if (!$stmt) {

        echo json_encode([
            'success' => false,
            'message' => 'Database error.'
        ]);

        exit;
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $rideID,
        $driverID
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $ride = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if (!$ride) {

        echo json_encode([
            'success' => false,
            'message' => 'This ride is no longer available for arrival verification.'
        ]);

        exit;
    }


    $pickupLatitude = (float)$ride['pickup_latitude'];
    $pickupLongitude = (float)$ride['pickup_longitude'];


    if (
        $pickupLatitude == 0 ||
        $pickupLongitude == 0
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'The pickup location does not have valid coordinates.'
        ]);

        exit;
    }


    /*
     * Haversine distance calculation.
     */
    $earthRadius = 6371000;

    $latDifference = deg2rad(
        $driverLatitude - $pickupLatitude
    );

    $longitudeDifference = deg2rad(
        $driverLongitude - $pickupLongitude
    );

    $a =
        sin($latDifference / 2) *
        sin($latDifference / 2)
        +
        cos(deg2rad($pickupLatitude)) *
        cos(deg2rad($driverLatitude)) *
        sin($longitudeDifference / 2) *
        sin($longitudeDifference / 2);

    $c = 2 * atan2(
        sqrt($a),
        sqrt(1 - $a)
    );

    $distanceMetres = $earthRadius * $c;


    /*
     * Driver must be within 50 metres.
     */
    $allowedDistance = 50;


    if ($distanceMetres > $allowedDistance) {

        echo json_encode([
            'success' => false,
            'message' =>
                'You are approximately ' .
                round($distanceMetres) .
                ' metres from the pickup location. Please move closer before marking yourself as arrived.'
        ]);

        exit;
    }


    /*
     * Mark driver as arrived.
     */
    $updateStmt = mysqli_prepare(
        $conn,
        "
        UPDATE ride
        SET
            ride_status = 'Driver Arrived',
            driver_arrived_at = NOW(),
            driver_arrived_latitude = ?,
            driver_arrived_longitude = ?,
            arrival_distance = ?
        WHERE rideID = ?
          AND DriverID = ?
          AND ride_status = 'Accepted'
        "
    );


    if (!$updateStmt) {

        echo json_encode([
            'success' => false,
            'message' => 'Unable to update the ride.'
        ]);

        exit;
    }


    /*
     * 3 decimal values + 2 integer values.
     */
    mysqli_stmt_bind_param(
        $updateStmt,
        "dddii",
        $driverLatitude,
        $driverLongitude,
        $distanceMetres,
        $rideID,
        $driverID
    );


    $updated = mysqli_stmt_execute($updateStmt);

    $affectedRows = mysqli_stmt_affected_rows($updateStmt);

    mysqli_stmt_close($updateStmt);


    if (!$updated || $affectedRows !== 1) {

        echo json_encode([
            'success' => false,
            'message' => 'The ride could not be updated.'
        ]);

        exit;
    }


    /*
     * Notify the passenger.
     */
    $studentID = !empty($ride['studentID'])
        ? (int)$ride['studentID']
        : null;

    $staffID = !empty($ride['staffID'])
        ? (int)$ride['staffID']
        : null;

    $notificationType = "driver_arrived";
    $message = "Your driver has arrived at your pickup location.";


    $notificationStmt = mysqli_prepare(
        $conn,
        "
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
        VALUES (?, ?, ?, NULL, ?, ?, 0, NOW())
        "
    );


    if ($notificationStmt) {

        mysqli_stmt_bind_param(
            $notificationStmt,
            "iiiss",
            $studentID,
            $staffID,
            $rideID,
            $notificationType,
            $message
        );

        mysqli_stmt_execute($notificationStmt);

        mysqli_stmt_close($notificationStmt);
    }


    echo json_encode([
        'success' => true,
        'message' => 'You have been marked as arrived.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| NEW RIDE REQUESTS
|--------------------------------------------------------------------------
*/

$newRides = mysqli_query(
    $conn,
    "
    SELECT
        r.*,
        s.StudentFname,
        s.StudentLname
    FROM ride r
    LEFT JOIN student s
        ON r.studentID = s.studentID
    WHERE r.DriverID IS NULL
      AND r.ride_status = 'Requested'
    ORDER BY r.requested_at DESC
    "
);


if ($newRides === false) {

    die(
        "Database error: " .
        htmlspecialchars(mysqli_error($conn))
    );
}


/*
|--------------------------------------------------------------------------
| DRIVER'S ACTIVE RIDE
|--------------------------------------------------------------------------
*/

$activeStmt = mysqli_prepare(
    $conn,
    "
    SELECT
        r.*,
        s.StudentFname,
        s.StudentLname
    FROM ride r
    LEFT JOIN student s
        ON r.studentID = s.studentID
    WHERE r.DriverID = ?
      AND r.ride_status IN (
          'Accepted',
          'Driver Arrived',
          'In Progress'
      )
    ORDER BY r.requested_at DESC
    LIMIT 1
    "
);


mysqli_stmt_bind_param(
    $activeStmt,
    "i",
    $driverID
);

mysqli_stmt_execute($activeStmt);

$activeResult = mysqli_stmt_get_result($activeStmt);

$activeRide = mysqli_fetch_assoc($activeResult);

mysqli_stmt_close($activeStmt);

?>


<style>

body {
    background: #f6f5f1;
}

.page-container {
    max-width: 1100px;
    margin: 30px auto;
    padding: 0 20px;
}

.back-button {
    display: inline-block;
    background: #0c2d4d;
    color: white;
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.page-title {
    color: #0c2d4d;
    margin-bottom: 25px;
}

.ride-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 18px;
}

.ride-card h3 {
    color: #0c2d4d;
    margin-top: 0;
}

.ride-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    margin: 15px 0;
}

.info-item {
    background: #f6f5f1;
    padding: 12px;
    border-radius: 8px;
}

.info-item strong {
    display: block;
    color: #0c2d4d;
    margin-bottom: 4px;
}

.status {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}

.status-accepted {
    background: #e0a526;
    color: white;
}

.status-arrived {
    background: #185fa5;
    color: white;
}

.status-progress {
    background: #0f6e56;
    color: white;
}

.btn {
    border: none;
    border-radius: 7px;
    padding: 10px 18px;
    cursor: pointer;
    font-weight: bold;
}

.btn-accept {
    background: #0f6e56;
    color: white;
}

.btn-arrive {
    background: #185fa5;
    color: white;
}

.btn-verify {
    background: #0c2d4d;
    color: white;
}

.btn-complete {
    background: #0f6e56;
    color: white;
}

.empty {
    background: white;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    color: #666;
}

</style>


<div class="page-container">

    <a href="driver_dashboard.php" class="back-button">
        ← Back
    </a>


    <h1 class="page-title">
        Ride Requests
    </h1>


    <?php if ($activeRide): ?>

        <div class="ride-card">

            <h3>
                Active Ride
            </h3>


            <?php

            $activeStatus = $activeRide['ride_status'];

            $statusClass = 'status-accepted';

            if ($activeStatus === 'Driver Arrived') {
                $statusClass = 'status-arrived';
            }

            if ($activeStatus === 'In Progress') {
                $statusClass = 'status-progress';
            }

            ?>


            <span class="status <?php echo $statusClass; ?>">
                <?php echo htmlspecialchars($activeStatus); ?>
            </span>


            <div class="ride-info">

                <div class="info-item">
                    <strong>Passenger</strong>

                    <?php

                    if (
                        !empty($activeRide['StudentFname']) ||
                        !empty($activeRide['StudentLname'])
                    ) {

                        echo htmlspecialchars(
                            trim(
                                $activeRide['StudentFname'] .
                                ' ' .
                                $activeRide['StudentLname']
                            )
                        );

                    } else {

                        echo "Passenger";
                    }

                    ?>

                </div>


                <div class="info-item">
                    <strong>Pickup</strong>

                    <?php echo htmlspecialchars(
                        $activeRide['pickup_address'] ?? 'Not available'
                    ); ?>

                </div>


                <div class="info-item">
                    <strong>Destination</strong>

                    <?php echo htmlspecialchars(
                        $activeRide['destination_address'] ?? 'Not available'
                    ); ?>

                </div>


                <div class="info-item">
                    <strong>Distance</strong>

                    <?php echo htmlspecialchars(
                        $activeRide['estimated_distance_km'] ?? '0'
                    ); ?>
                    km

                </div>


                <div class="info-item">
                    <strong>Estimated Time</strong>

                    <?php echo htmlspecialchars(
                        $activeRide['estimated_duration_min'] ?? '0'
                    ); ?>
                    minutes

                </div>


                <div class="info-item">
                    <strong>Estimated Fare</strong>

                    R<?php echo number_format(
                        (float)($activeRide['estimated_price'] ?? 0),
                        2
                    ); ?>

                </div>


                <div class="info-item">
                    <strong>Requested</strong>

                    <?php echo htmlspecialchars(
                        $activeRide['requested_at'] ?? ''
                    ); ?>

                </div>


                <?php if (!empty($activeRide['accepted_at'])): ?>

                    <div class="info-item">
                        <strong>Accepted</strong>

                        <?php echo htmlspecialchars(
                            $activeRide['accepted_at']
                        ); ?>

                    </div>

                <?php endif; ?>


                <?php if (!empty($activeRide['driver_arrived_at'])): ?>

                    <div class="info-item">
                        <strong>Arrived</strong>

                        <?php echo htmlspecialchars(
                            $activeRide['driver_arrived_at']
                        ); ?>

                    </div>

                <?php endif; ?>

            </div>


            <?php if ($activeStatus === 'Accepted'): ?>

                <button
                    class="btn btn-arrive"
                    onclick="markDriverArrived(<?php echo (int)$activeRide['rideID']; ?>)"
                >
                    I've Arrived
                </button>

            <?php elseif ($activeStatus === 'Driver Arrived'): ?>

                <form method="POST" action="driver_verify_passenger.php">

                    <input
                        type="hidden"
                        name="ride_id"
                        value="<?php echo (int)$activeRide['rideID']; ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-verify"
                    >
                        Verify Passenger
                    </button>

                </form>

            <?php elseif ($activeStatus === 'In Progress'): ?>

                <form method="POST" action="driver_complete_ride.php">

                    <input
                        type="hidden"
                        name="ride_id"
                        value="<?php echo (int)$activeRide['rideID']; ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-complete"
                    >
                        Complete Ride
                    </button>

                </form>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <h2 class="page-title">
        New Ride Requests
    </h2>


    <?php if (mysqli_num_rows($newRides) > 0): ?>

        <?php while ($ride = mysqli_fetch_assoc($newRides)): ?>

            <div class="ride-card">

                <h3>
                    New Ride Request
                </h3>


                <div class="ride-info">

                    <div class="info-item">

                        <strong>Passenger</strong>

                        <?php

                        if (
                            !empty($ride['StudentFname']) ||
                            !empty($ride['StudentLname'])
                        ) {

                            echo htmlspecialchars(
                                trim(
                                    $ride['StudentFname'] .
                                    ' ' .
                                    $ride['StudentLname']
                                )
                            );

                        } else {

                            echo "Passenger";
                        }

                        ?>

                    </div>


                    <div class="info-item">

                        <strong>Pickup</strong>

                        <?php echo htmlspecialchars(
                            $ride['pickup_address'] ?? 'Not available'
                        ); ?>

                    </div>


                    <div class="info-item">

                        <strong>Destination</strong>

                        <?php echo htmlspecialchars(
                            $ride['destination_address'] ?? 'Not available'
                        ); ?>

                    </div>


                    <div class="info-item">

                        <strong>Distance</strong>

                        <?php echo htmlspecialchars(
                            $ride['estimated_distance_km'] ?? '0'
                        ); ?>
                        km

                    </div>


                    <div class="info-item">

                        <strong>Estimated Fare</strong>

                        R<?php echo number_format(
                            (float)($ride['estimated_price'] ?? 0),
                            2
                        ); ?>

                    </div>

                </div>


                <form method="POST">

                    <input
                        type="hidden"
                        name="ride_id"
                        value="<?php echo (int)$ride['rideID']; ?>"
                    >

                    <button
                        type="submit"
                        name="accept_ride"
                        class="btn btn-accept"
                    >
                        Accept Ride
                    </button>

                </form>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">
            No new ride requests available.
        </div>

    <?php endif; ?>

</div>


<script>

function markDriverArrived(rideID) {

    if (!navigator.geolocation) {

        alert("Your browser does not support GPS location.");

        return;
    }


    navigator.geolocation.getCurrentPosition(

        function(position) {

            const formData = new FormData();

            formData.append(
                "mark_arrived",
                "1"
            );

            formData.append(
                "ride_id",
                rideID
            );

            formData.append(
                "driver_latitude",
                position.coords.latitude
            );

            formData.append(
                "driver_longitude",
                position.coords.longitude
            );


            fetch(
                "driver_ride_requests.php",
                {
                    method: "POST",
                    body: formData
                }
            )

            .then(response => response.json())

            .then(data => {

                alert(data.message);

                if (data.success) {
                    location.reload();
                }

            })

            .catch(() => {

                alert(
                    "Something went wrong while checking your location."
                );

            });

        },

        function(error) {

            alert(
                "Please allow location access so we can verify that you have arrived."
            );

        },

        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }

    );
}


/*
 * Refresh new requests.
 */
setTimeout(
    function() {
        location.reload();
    },
    3000
);

</script>