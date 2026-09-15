<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

$pageTitle = "Today's Rides";

include 'header.php';

$driverID = $_SESSION['driver_id'] ?? null;

$todayRides = 0;
$rides = false;

if ($driverID !== null) {

    $driverIDSafe = intval($driverID);

    /* Count today's completed rides */

    $countSQL = "
        SELECT COUNT(*) AS total
        FROM ride
        WHERE DriverID = $driverIDSafe
        AND ride_status = 'Completed'
        AND DATE(Completed_at) = CURDATE()
    ";

    $countResult = mysqli_query($conn, $countSQL);

    if ($countResult) {

        $row = mysqli_fetch_assoc($countResult);

        $todayRides = intval($row['total'] ?? 0);
    }

    /* Get today's completed rides */

    $ridesSQL = "
        SELECT
            r.*,
            CONCAT(
                s.StudentFname,
                ' ',
                s.StudentLname
            ) AS student_name
        FROM ride r
        LEFT JOIN student s
            ON r.studentID = s.studentID
        WHERE r.DriverID = $driverIDSafe
        AND r.ride_status = 'Completed'
        AND DATE(r.Completed_at) = CURDATE()
        ORDER BY r.Completed_at DESC
    ";

    $rides = mysqli_query($conn, $ridesSQL);
}

?>

<style>

.today-rides-page {
    max-width: 1380px;
    margin: 35px auto;
    padding: 0 28px;
    color: #17233c;
}

/* BACK BUTTON */

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
}

.back-button:hover {
    background: #185fa5;
    color: #ffffff;
}

.back-arrow {
    font-size: 17px;
}

/* PAGE HEADER */

.page-heading {
    background: #ffffff;
    border: 1px solid #e2e7ed;
    border-radius: 8px;
    padding: 30px 32px;
    margin-bottom: 20px;
}

.page-heading h1 {
    margin: 0 0 7px;
    font-size: 27px;
    font-weight: 650;
    color: #102a43;
}

.page-heading p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

/* TOTAL BOX */

.today-summary {
    background: #102a43;
    color: #ffffff;
    border-radius: 8px;
    padding: 22px 26px;
    margin-bottom: 25px;
}

.summary-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #cbd5e1;
    margin-bottom: 8px;
}

.summary-value {
    font-size: 30px;
    font-weight: 650;
}

/* SECTION HEADING */

.section-heading {
    margin: 28px 0 15px;
    color: #102a43;
    font-size: 19px;
    font-weight: 650;
}

/* RIDE CARD */

.ride-card {
    background: #ffffff;
    border: 1px solid #e2e7ed;
    border-radius: 8px;
    padding: 25px 27px;
    margin-bottom: 15px;
    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}

.ride-card:hover {
    border-color: #c4ced9;
    box-shadow:
        0 5px 16px rgba(16, 42, 67, .07);
}

/* RIDE TOP */

.ride-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding-bottom: 16px;
    margin-bottom: 18px;
    border-bottom: 1px solid #edf0f3;
}

.ride-title {
    font-size: 17px;
    font-weight: 650;
    color: #102a43;
}

/* STATUS */

.status {
    padding: 5px 9px;
    border-radius: 4px;
    background: #e8f7f0;
    color: #16805a;
    border: 1px solid #ccebdd;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}

/* RIDE DETAILS */

.location {
    margin: 11px 0;
    color: #697586;
    font-size: 13px;
}

.location strong {
    color: #17233c;
    font-weight: 600;
}

/* INFORMATION BOXES */

.ride-info {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin: 20px 0;
}

.info-box {
    background: #f5f7f9;
    border: 1px solid #e7ebef;
    padding: 13px;
    border-radius: 6px;
}

.info-label {
    display: block;
    color: #687386;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 6px;
}

.info-value {
    color: #17233c;
    font-size: 13px;
    font-weight: 600;
}

/* EMPTY */

.empty {
    background: #ffffff;
    border: 1px solid #e2e7ed;
    padding: 30px;
    text-align: center;
    border-radius: 8px;
    color: #687386;
    font-size: 14px;
}

/* RESPONSIVE */

@media(max-width: 1050px) {

    .ride-info {
        grid-template-columns: 1fr 1fr;
    }
}

@media(max-width: 700px) {

    .today-rides-page {
        padding: 0 16px;
        margin: 25px auto;
    }

    .ride-info {
        grid-template-columns: 1fr;
    }

    .page-heading {
        padding: 24px;
    }

    .page-heading h1 {
        font-size: 23px;
    }

    .ride-top {
        align-items: flex-start;
        flex-direction: column;
    }
}

</style>


<div class="today-rides-page">

    <!-- BACK BUTTON -->

    <a href="driver_dashboard.php" class="back-button">
        <span class="back-arrow">←</span>
        Back to Driver Dashboard
    </a>


    <!-- PAGE HEADER -->

    <div class="page-heading">

        <h1>
            Today's Rides
        </h1>

        <p>
            View all rides you completed today.
        </p>

    </div>


    <!-- SUMMARY -->

    <div class="today-summary">

        <div class="summary-label">
            Completed Rides Today
        </div>

        <div class="summary-value">
            <?php echo $todayRides; ?>
        </div>

    </div>


    <!-- RIDES -->

    <h2 class="section-heading">
        Completed Today
    </h2>


    <?php if (
        $rides &&
        mysqli_num_rows($rides) > 0
    ): ?>


        <?php while (
            $ride =
                mysqli_fetch_assoc($rides)
        ): ?>


            <div class="ride-card">


                <div class="ride-top">

                    <div class="ride-title">

                        Ride #<?php
                        echo htmlspecialchars(
                            $ride['rideID']
                        );
                        ?>

                    </div>


                    <span class="status">
                        Completed
                    </span>

                </div>


                <!-- STUDENT -->

                <div class="location">

                    <strong>Student:</strong>

                    <?php
                    echo htmlspecialchars(
                        $ride['student_name']
                        ?? 'Student'
                    );
                    ?>

                </div>


                <!-- PICKUP -->

                <div class="location">

                    <strong>Pickup:</strong>

                    <?php
                    echo htmlspecialchars(
                        $ride['pickup_address']
                        ?? ''
                    );
                    ?>

                </div>


                <!-- DESTINATION -->

                <div class="location">

                    <strong>Destination:</strong>

                    <?php
                    echo htmlspecialchars(
                        $ride['destination_address']
                        ?? ''
                    );
                    ?>

                </div>


                <!-- INFORMATION -->

                <div class="ride-info">


                    <!-- REQUESTED -->

                    <div class="info-box">

                        <span class="info-label">
                            Requested
                        </span>

                        <span class="info-value">

                            <?php

                            if (!empty($ride['requested_at'])) {

                                echo date(
                                    'd M Y, H:i',
                                    strtotime(
                                        $ride['requested_at']
                                    )
                                );

                            } else {

                                echo 'Not available';

                            }

                            ?>

                        </span>

                    </div>


                    <!-- DISTANCE -->

                    <div class="info-box">

                        <span class="info-label">
                            Distance
                        </span>

                        <span class="info-value">

                            <?php
                            echo number_format(
                                floatval(
                                    $ride['estimated_distance_km']
                                    ?? 0
                                ),
                                1
                            );
                            ?>

                            km

                        </span>

                    </div>


                    <!-- FARE -->

                    <div class="info-box">

                        <span class="info-label">
                            Fare
                        </span>

                        <span class="info-value">

                            R<?php
                            echo number_format(
                                floatval(
                                    $ride['estimated_price']
                                    ?? 0
                                ),
                                2
                            );
                            ?>

                        </span>

                    </div>


                    <!-- COMPLETED -->

                    <div class="info-box">

                        <span class="info-label">
                            Completed
                        </span>

                        <span class="info-value">

                            <?php

                            if (!empty($ride['Completed_at'])) {

                                echo date(
                                    'd M Y, H:i',
                                    strtotime(
                                        $ride['Completed_at']
                                    )
                                );

                            } else {

                                echo 'Not available';

                            }

                            ?>

                        </span>

                    </div>


                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="empty">

            You have not completed any rides today.

        </div>


    <?php endif; ?>


</div>
