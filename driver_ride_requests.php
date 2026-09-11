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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accept_ride'])) {

    $rideID = (int)($_POST['ride_id'] ?? 0);

    if ($rideID > 0) {

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

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: driver_ride_requests.php");
    exit;
}


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
    die("Database error: " . htmlspecialchars(mysqli_error($conn)));
}



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
      AND r.ride_status IN ('Accepted', 'In Progress')
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

.driver-request-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 25px 20px 40px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0c2d4d;
    color: #ffffff;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 22px;
    transition: 0.2s ease;
}

.back-button:hover {
    background: #185fa5;
    color: #ffffff;
}

.back-arrow {
    font-size: 17px;
}

.page-heading {
    margin-bottom: 25px;
}

.page-heading h1 {
    margin: 0;
    color: #0c2d4d;
    font-size: 28px;
}

.page-heading p {
    margin-top: 7px;
    color: #6b7280;
    font-size: 14px;
}

.section-title {
    margin: 30px 0 15px;
    color: #0c2d4d;
    font-size: 21px;
}

.requests-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}

.request-card {
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 22px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.active-card {
    border-top: 4px solid #0c2d4d;
}

.request-card h3 {
    margin: 0 0 15px;
    color: #0c2d4d;
    font-size: 20px;
}

.request-status {
    display: inline-block;
    margin-bottom: 18px;
    padding: 6px 10px;
    background: #e0a526;
    color: #ffffff;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 600;
}

.active-status {
    background: #0f6e56;
}

.request-date {
    margin-bottom: 15px;
    padding: 9px 11px;
    background: #f6f5f1;
    border-radius: 6px;
    color: #6b7280;
    font-size: 13px;
}

.request-date strong {
    color: #0c2d4d;
}

.request-detail {
    margin-bottom: 12px;
    color: #444;
    font-size: 14px;
    line-height: 1.5;
}

.request-detail strong {
    color: #0c2d4d;
}

.accept-button,
.start-button,
.complete-button {
    width: 100%;
    margin-top: 8px;
    padding: 12px;
    border: none;
    color: #ffffff;
    border-radius: 7px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
}

.accept-button {
    background: #0f6e56;
}

.accept-button:hover {
    background: #0c5946;
}

.start-button {
    background: #0c2d4d;
}

.start-button:hover {
    background: #185fa5;
}

.complete-button {
    background: #0f6e56;
}

.complete-button:hover {
    background: #0c5946;
}

.no-requests {
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 35px 25px;
    text-align: center;
    color: #6b7280;
}

.active-section {
    margin-bottom: 35px;
}

@media (max-width: 600px) {

    .driver-request-page {
        padding: 20px 15px 35px;
    }

    .requests-grid {
        grid-template-columns: 1fr;
    }

}

</style>


<div class="driver-request-page">


    <a href="driver_dashboard.php" class="back-button">

        <span class="back-arrow">←</span>

        Back to Driver Dashboard

    </a>


    <div class="page-heading">

        <h1>
            Ride Requests
        </h1>

        <p>
            View new ride requests and manage your active ride.
        </p>

    </div>


    <!-- ========================================================
         MY ACTIVE RIDE
         ======================================================== -->

    <div class="active-section">

        <h2 class="section-title">
            My Active Ride
        </h2>


        <?php if ($activeRide): ?>

            <div class="requests-grid">

                <div class="request-card active-card">

                    <h3>

                        <?php

                        $passengerName = trim(
                            ($activeRide['StudentFname'] ?? '') .
                            ' ' .
                            ($activeRide['StudentLname'] ?? '')
                        );

                        echo htmlspecialchars(
                            $passengerName !== ''
                                ? $passengerName
                                : 'Passenger'
                        );

                        ?>

                    </h3>


                    <div class="request-status active-status">

                        <?php
                        echo htmlspecialchars(
                            $activeRide['ride_status']
                        );
                        ?>

                    </div>


                    <div class="request-date">

                        <strong>Requested:</strong>

                        <?php

                        if (!empty($activeRide['requested_at'])) {

                            echo htmlspecialchars(
                                date(
                                    'd M Y, H:i',
                                    strtotime(
                                        $activeRide['requested_at']
                                    )
                                )
                            );

                        } else {

                            echo 'Not available';

                        }

                        ?>

                    </div>


                    <?php if (!empty($activeRide['accepted_at'])): ?>

                        <div class="request-date">

                            <strong>Accepted:</strong>

                            <?php

                            echo htmlspecialchars(
                                date(
                                    'd M Y, H:i',
                                    strtotime(
                                        $activeRide['accepted_at']
                                    )
                                )
                            );

                            ?>

                        </div>

                    <?php endif; ?>


                    <div class="request-detail">

                        <strong>Pickup:</strong>

                        <br>

                        <?php

                        echo htmlspecialchars(
                            $activeRide['pickup_address'] ??
                            'Not provided'
                        );

                        ?>

                    </div>


                    <div class="request-detail">

                        <strong>Destination:</strong>

                        <br>

                        <?php

                        echo htmlspecialchars(
                            $activeRide['destination_address'] ??
                            'Not provided'
                        );

                        ?>

                    </div>


                    <div class="request-detail">

                        <strong>Distance:</strong>

                        <?php

                        echo htmlspecialchars(
                            $activeRide['estimated_distance_km'] ??
                            '0'
                        );

                        ?>

                        km

                    </div>


                    <div class="request-detail">

                        <strong>Estimated time:</strong>

                        <?php

                        echo htmlspecialchars(
                            $activeRide['estimated_duration_min'] ??
                            '0'
                        );

                        ?>

                        minutes

                    </div>


                    <div class="request-detail">

                        <strong>Fare:</strong>

                        R<?php

                        echo number_format(
                            (float)(
                                $activeRide['estimated_price'] ?? 0
                            ),
                            2
                        );

                        ?>

                    </div>


                    <?php if ($activeRide['ride_status'] === 'Accepted'): ?>

                        <form
                            method="GET"
                            action="driver_verify_passenger.php"
                        >

                            <input
                                type="hidden"
                                name="rideID"
                                value="<?php echo (int)$activeRide['rideID']; ?>"
                            >

                            <button
                                type="submit"
                                class="start-button"
                            >
                                Start Ride
                            </button>

                        </form>


                    <?php elseif ($activeRide['ride_status'] === 'In Progress'): ?>

                        <form
                            method="GET"
                            action="driver_complete_ride.php"
                            onsubmit="return confirm('Complete this ride?');"
                        >

                            <input
                                type="hidden"
                                name="rideID"
                                value="<?php echo (int)$activeRide['rideID']; ?>"
                            >

                            <button
                                type="submit"
                                class="complete-button"
                            >
                                Complete Ride
                            </button>

                        </form>

                    <?php endif; ?>


                </div>

            </div>


        <?php else: ?>


            <div class="no-requests">

                <p>
                    You currently have no active ride.
                </p>

            </div>


        <?php endif; ?>

    </div>


   
    <h2 class="section-title">
        New Ride Requests
    </h2>


    <div id="rideRequests">


        <?php if (mysqli_num_rows($newRides) > 0): ?>


            <div class="requests-grid">


                <?php while ($ride = mysqli_fetch_assoc($newRides)): ?>


                    <div class="request-card">


                        <h3>

                            <?php

                            $passengerName = trim(
                                ($ride['StudentFname'] ?? '') .
                                ' ' .
                                ($ride['StudentLname'] ?? '')
                            );

                            echo htmlspecialchars(
                                $passengerName !== ''
                                    ? $passengerName
                                    : 'Passenger'
                            );

                            ?>

                        </h3>


                        <div class="request-status">

                            <?php

                            echo htmlspecialchars(
                                $ride['ride_status']
                            );

                            ?>

                        </div>


                        <div class="request-date">

                            <strong>Requested:</strong>

                            <?php

                            if (!empty($ride['requested_at'])) {

                                echo htmlspecialchars(
                                    date(
                                        'd M Y, H:i',
                                        strtotime(
                                            $ride['requested_at']
                                        )
                                    )
                                );

                            } else {

                                echo 'Not available';

                            }

                            ?>

                        </div>


                        <div class="request-detail">

                            <strong>Pickup:</strong>

                            <br>

                            <?php

                            echo htmlspecialchars(
                                $ride['pickup_address'] ??
                                'Not provided'
                            );

                            ?>

                        </div>


                        <div class="request-detail">

                            <strong>Destination:</strong>

                            <br>

                            <?php

                            echo htmlspecialchars(
                                $ride['destination_address'] ??
                                'Not provided'
                            );

                            ?>

                        </div>


                        <div class="request-detail">

                            <strong>Distance:</strong>

                            <?php

                            echo htmlspecialchars(
                                $ride['estimated_distance_km'] ??
                                '0'
                            );

                            ?>

                            km

                        </div>


                        <div class="request-detail">

                            <strong>Estimated time:</strong>

                            <?php

                            echo htmlspecialchars(
                                $ride['estimated_duration_min'] ??
                                '0'
                            );

                            ?>

                            minutes

                        </div>


                        <div class="request-detail">

                            <strong>Estimated fare:</strong>

                            R<?php

                            echo number_format(
                                (float)(
                                    $ride['estimated_price'] ?? 0
                                ),
                                2
                            );

                            ?>

                        </div>


                        <form
                            method="POST"
                            onsubmit="return confirm('Accept this ride request?');"
                        >

                            <input
                                type="hidden"
                                name="ride_id"
                                value="<?php echo (int)$ride['rideID']; ?>"
                            >


                            <button
                                type="submit"
                                name="accept_ride"
                                class="accept-button"
                            >
                                Accept Ride
                            </button>

                        </form>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <div class="no-requests">

                <p>
                    No new ride requests are currently available.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


<script>

setInterval(function() {

    fetch('driver_ride_requests.php')

        .then(response => response.text())

        .then(html => {

            const parser = new DOMParser();

            const documentPage =
                parser.parseFromString(
                    html,
                    'text/html'
                );

            const newRequests =
                documentPage.querySelector(
                    '#rideRequests'
                );

            const currentRequests =
                document.querySelector(
                    '#rideRequests'
                );

            if (newRequests && currentRequests) {

                currentRequests.innerHTML =
                    newRequests.innerHTML;

            }

        })

        .catch(error => {

            console.log(
                'Ride request update failed:',
                error
            );

        });

}, 3000);

</script>

