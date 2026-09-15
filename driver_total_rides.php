<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

$driverID = (int)($_SESSION['driver_id'] ?? 0);


/* =========================================================
   DRIVER NAME
   ========================================================= */

$driverName = "Driver";

$stmt = $conn->prepare("
    SELECT DriverFname, DriverLname
    FROM driver
    WHERE DriverID = ?
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $driverName = trim(
            ($row['DriverFname'] ?? '') . " " .
            ($row['DriverLname'] ?? '')
        );

        if ($driverName === '') {
            $driverName = "Driver";
        }
    }

    $stmt->close();
}


/* =========================================================
   TOTAL COMPLETED RIDES
   ========================================================= */

$totalRides = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM ride
    WHERE DriverID = ?
    AND ride_status = 'Completed'
");

if ($stmt) {

    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totalRides = $row['total'] ?? 0;

    $stmt->close();
}


/* =========================================================
   COMPLETED THIS WEEK
   ========================================================= */

$weekRides = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM ride
    WHERE DriverID = ?
    AND ride_status = 'Completed'
    AND YEARWEEK(completed_at, 1) =
        YEARWEEK(CURDATE(), 1)
");

if ($stmt) {

    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $weekRides = $row['total'] ?? 0;

    $stmt->close();
}


/* =========================================================
   COMPLETED THIS MONTH
   ========================================================= */

$monthRides = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM ride
    WHERE DriverID = ?
    AND ride_status = 'Completed'
    AND YEAR(completed_at) = YEAR(CURDATE())
    AND MONTH(completed_at) = MONTH(CURDATE())
");

if ($stmt) {

    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $monthRides = $row['total'] ?? 0;

    $stmt->close();
}


/* =========================================================
   COMPLETED RIDE HISTORY
   ========================================================= */

$rides = [];

$stmt = $conn->prepare("
    SELECT
        rideID,
        pickup_address,
        destination_address,
        estimated_distance_km,
        estimated_price,
        completed_at
    FROM ride
    WHERE DriverID = ?
    AND ride_status = 'Completed'
    ORDER BY completed_at DESC
");

if ($stmt) {

    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $rides[] = $row;
    }

    $stmt->close();
}


/* =========================================================
   PAGE TITLE
   ========================================================= */

$pageTitle = "Total Rides";

include 'header.php';

?>


<style>

/* =========================================================
   CAMPUSCAB COLOURS
   ========================================================= */

:root {
    --navy:#0b1f3a;
    --navy-light:#17365f;
    --gold:#d4a72c;
    --gold-dark:#b8860f;
    --orange:#e39b24;
    --card:#ffffff;
    --border:#e5e7eb;
    --ink:#172033;
    --muted:#6b7280;
    --bg:#f5f7fa;
    --green:#15803d;
    --red:#b91c1c;
}


/* =========================================================
   GENERAL
   ========================================================= */

* {
    box-sizing:border-box;
}

body {
    margin:0;
    font-family:
        Arial,
        Helvetica,
        sans-serif;
    background:var(--bg);
    color:var(--ink);
}

a {
    text-decoration:none;
}


/* =========================================================
   PAGE
   ========================================================= */

.page-wrapper {
    max-width:1200px;
    margin:0 auto;
    padding:28px 25px 40px;
}


/* =========================================================
   BACK BUTTON
   ========================================================= */

.back-button {
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:10px 16px;
    margin-bottom:20px;
    background:var(--navy);
    color:white;
    border:1px solid var(--navy);
    border-radius:8px;
    font-size:13px;
    font-weight:600;
    transition:
        background 0.2s ease,
        border-color 0.2s ease;
}

.back-button:hover {
    background:var(--navy-light);
    border-color:var(--navy-light);
    color:white;
}

.back-arrow {
    color:white;
    font-size:17px;
    line-height:1;
}


/* =========================================================
   PAGE TITLE
   ========================================================= */

.page-title {
    margin-bottom:25px;
    border-left:4px solid var(--orange);
    padding-left:16px;
}

.page-title h1 {
    margin:0 0 6px;
    font-size:28px;
    color:var(--navy);
    font-weight:700;
}

.page-title p {
    margin:0;
    color:var(--muted);
    font-size:14px;
    line-height:1.5;
}


/* =========================================================
   SUMMARY CARDS
   ========================================================= */

.summary-grid {
    display:grid;
    grid-template-columns:
        repeat(3, 1fr);
    gap:18px;
    margin-bottom:28px;
}

.summary-card {
    position:relative;
    background:var(--card);
    border:1px solid var(--border);
    border-radius:12px;
    padding:23px;
    overflow:hidden;
    box-shadow:
        0 3px 10px rgba(11,31,58,0.04);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.summary-card:hover {
    transform:translateY(-2px);
    box-shadow:
        0 8px 22px rgba(11,31,58,0.08);
}


/* Navy top line */

.summary-card::before {
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:4px;
    background:var(--navy);
}

.summary-card h3 {
    margin:0 0 12px;
    padding-top:3px;
    font-size:13px;
    color:var(--muted);
    font-weight:600;
}

.summary-number {
    font-size:34px;
    font-weight:700;
    color:var(--navy);
}


/* All card accents navy */

.summary-card.today::before,
.summary-card.week::before,
.summary-card.total::before {
    background:var(--navy);
}


/* Small accent underneath number */

.summary-card::after {
    content:"";
    display:block;
    width:40px;
    height:3px;
    margin-top:16px;
    border-radius:3px;
    background:var(--navy);
}


/* =========================================================
   HISTORY CARD
   ========================================================= */

.history-card {
    background:white;
    border:1px solid var(--border);
    border-radius:12px;
    overflow:hidden;
    box-shadow:
        0 3px 10px rgba(11,31,58,0.04);
}

.history-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:20px 22px;
    border-bottom:1px solid var(--border);
    background:white;
}

.history-header h2 {
    margin:0;
    font-size:19px;
    color:var(--navy);
    font-weight:700;
}

.history-accent {
    width:35px;
    height:3px;
    background:var(--navy);
    border-radius:3px;
}


/* =========================================================
   TABLE
   ========================================================= */

.table-wrapper {
    overflow-x:auto;
}

table {
    width:100%;
    border-collapse:collapse;
}

th {
    text-align:left;
    padding:14px 16px;
    background:var(--navy);
    color:white;
    font-size:12px;
    font-weight:700;
    border-bottom:3px solid var(--navy);
}

td {
    padding:15px 16px;
    border-bottom:1px solid var(--border);
    font-size:13px;
    color:var(--muted);
    vertical-align:top;
}

tbody tr {
    transition:
        background 0.15s ease;
}

tbody tr:hover {
    background:#f8fafc;
}

tr:last-child td {
    border-bottom:none;
}


/* =========================================================
   RIDE ID
   ========================================================= */

.ride-id {
    color:var(--navy);
    font-weight:700;
    white-space:nowrap;
}


/* =========================================================
   ROUTE
   ========================================================= */

td.route {
    min-width:280px;
    color:var(--ink);
    line-height:1.6;
}


/* =========================================================
   DISTANCE
   ========================================================= */

.distance {
    white-space:nowrap;
    color:var(--navy);
    font-weight:600;
}


/* =========================================================
   FARE
   ========================================================= */

.fare {
    white-space:nowrap;
    color:var(--green);
    font-weight:700;
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty {
    text-align:center;
    color:var(--muted);
    padding:50px 25px;
    font-size:14px;
}

.empty strong {
    display:block;
    color:var(--navy);
    font-size:16px;
    margin-bottom:6px;
}


/* =========================================================
   FOOTER
   ========================================================= */

.page-footer {
    max-width:1200px;
    margin:0 auto;
    padding:5px 25px 25px;
    text-align:center;
    color:var(--muted);
    font-size:12px;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width:750px) {

    .summary-grid {
        grid-template-columns:1fr;
    }
}

@media (max-width:600px) {

    .page-wrapper {
        padding:
            22px
            18px
            35px;
    }

    .page-title h1 {
        font-size:24px;
    }

    .summary-card {
        padding:20px;
    }

    .summary-number {
        font-size:30px;
    }

    .history-header {
        padding:18px;
    }

    th,
    td {
        padding:12px;
    }

    td.route {
        min-width:230px;
    }

    .page-footer {
        padding-left:18px;
        padding-right:18px;
    }
}

</style>


<!-- =========================================================
     PAGE
     ========================================================= -->

<div class="page-wrapper">


    <!-- BACK -->

    <a
        href="driver_dashboard.php"
        class="back-button"
    >

        <span class="back-arrow">
            ←
        </span>

        <span>
            Back to Dashboard
        </span>

    </a>


    <!-- =====================================================
         TITLE
         ===================================================== -->

    <div class="page-title">

        <h1>
            Total Rides
        </h1>

        <p>

            Ride history and completed ride statistics for

            <?php
            echo htmlspecialchars($driverName);
            ?>.

        </p>

    </div>


    <!-- =====================================================
         SUMMARY
         ===================================================== -->

    <div class="summary-grid">


        <!-- TOTAL -->

        <div class="summary-card total">

            <h3>
                Total Completed Rides
            </h3>

            <div class="summary-number">

                <?php
                echo (int)$totalRides;
                ?>

            </div>

        </div>


        <!-- WEEK -->

        <div class="summary-card week">

            <h3>
                Completed This Week
            </h3>

            <div class="summary-number">

                <?php
                echo (int)$weekRides;
                ?>

            </div>

        </div>


        <!-- MONTH -->

        <div class="summary-card today">

            <h3>
                Completed This Month
            </h3>

            <div class="summary-number">

                <?php
                echo (int)$monthRides;
                ?>

            </div>

        </div>


    </div>


    <!-- =====================================================
         HISTORY
         ===================================================== -->

    <section class="history-card">


        <div class="history-header">

            <h2>
                Completed Ride History
            </h2>

            <div class="history-accent"></div>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>
                            Ride
                        </th>

                        <th>
                            Completed
                        </th>

                        <th>
                            Route
                        </th>

                        <th>
                            Distance
                        </th>

                        <th>
                            Fare
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($rides) === 0): ?>


                    <tr>

                        <td
                            colspan="5"
                            class="empty"
                        >

                            <strong>
                                No completed rides yet
                            </strong>

                            Your completed rides will appear here.

                        </td>

                    </tr>


                <?php else: ?>


                    <?php foreach ($rides as $ride): ?>


                        <tr>


                            <!-- RIDE -->

                            <td class="ride-id">

                                #

                                <?php
                                echo (int)$ride['rideID'];
                                ?>

                            </td>


                            <!-- COMPLETED -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $ride['completed_at']
                                    )
                                ) {

                                    echo date(
                                        'd M Y, H:i',
                                        strtotime(
                                            $ride['completed_at']
                                        )
                                    );

                                } else {

                                    echo '—';

                                }

                                ?>

                            </td>


                            <!-- ROUTE -->

                            <td class="route">

                                <?php
                                echo htmlspecialchars(
                                    $ride['pickup_address'] ?? ''
                                );
                                ?>

                                <span
                                    style="
                                        color:var(--navy);
                                        font-weight:700;
                                        margin:0 5px;
                                    "
                                >
                                    →
                                </span>

                                <?php
                                echo htmlspecialchars(
                                    $ride['destination_address'] ?? ''
                                );
                                ?>

                            </td>


                            <!-- DISTANCE -->

                            <td class="distance">

                                <?php

                                if (
                                    $ride[
                                        'estimated_distance_km'
                                    ] !== null
                                ) {

                                    echo number_format(
                                        (float)
                                        $ride[
                                            'estimated_distance_km'
                                        ],
                                        1
                                    );

                                    echo " km";

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- FARE -->

                            <td class="fare">

                                <?php

                                if (
                                    $ride[
                                        'estimated_price'
                                    ] !== null
                                ) {

                                    echo "R";

                                    echo number_format(
                                        (float)
                                        $ride[
                                            'estimated_price'
                                        ],
                                        2
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


    </section>


</div>



