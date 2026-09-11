<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

$driverID = (int)($_SESSION['driver_id'] ?? 0);

if ($driverID <= 0) {
    header("Location: login_driver.php");
    exit;
}

/* =========================
   DRIVER NAME
   ========================= */

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

/* =========================
   TODAY'S EARNINGS
   ========================= */

$todayEarnings = 0;

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(driver_earning), 0) AS earnings
    FROM ride
    WHERE DriverID = ?
      AND ride_status = 'Completed'
      AND DATE(Completed_at) = CURDATE()
");

if ($stmt) {

    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $todayEarnings = (float)$row['earnings'];
    }

    $stmt->close();
}

/* =========================
   THIS WEEK'S EARNINGS
   ========================= */

$weekEarnings = 0;

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(driver_earning), 0) AS earnings
    FROM ride
    WHERE DriverID = ?
      AND ride_status = 'Completed'
      AND YEARWEEK(Completed_at, 1) =
          YEARWEEK(CURDATE(), 1)
");

if ($stmt) {

    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $weekEarnings = (float)$row['earnings'];
    }

    $stmt->close();
}

/* =========================
   TOTAL EARNINGS
   ========================= */

$totalEarnings = 0;

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(driver_earning), 0) AS earnings
    FROM ride
    WHERE DriverID = ?
      AND ride_status = 'Completed'
");

if ($stmt) {

    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $totalEarnings = (float)$row['earnings'];
    }

    $stmt->close();
}

/* =========================
   COMPLETED RIDES
   ========================= */

$rides = [];

$stmt = $conn->prepare("
    SELECT
        rideID,
        pickup_address,
        destination_address,
        estimated_price,
        driver_earning,
        campus_commission,
        requested_at,
        Completed_at
    FROM ride
    WHERE DriverID = ?
      AND ride_status = 'Completed'
    ORDER BY Completed_at DESC
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

/* =========================
   PAGE TITLE
   ========================= */

$pageTitle = "Earnings";

include 'header.php';

?>

<style>

:root {
    --navy: #0c2d4d;
    --navy-light: #185fa5;
    --gold: #e0a526;
    --gold-dark: #b8860f;
    --card: #ffffff;
    --border: #e5e7eb;
    --ink: #172033;
    --muted: #6b7280;
    --bg: #f6f5f1;
    --green: #15803d;
}

* {
    box-sizing: border-box;
}

body {
    background: var(--bg);
    color: var(--ink);
    font-family: Arial, Helvetica, sans-serif;
}

a {
    text-decoration: none;
}

.earnings-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 28px 25px 40px;
}

/* BACK BUTTON */

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--navy);
    color: #ffffff;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 22px;
}

.back-button:hover {
    background: var(--navy-light);
    color: #ffffff;
}

.back-arrow {
    font-size: 17px;
}

/* PAGE HEADING */

.page-heading {
    margin-bottom: 24px;
    border-left: 4px solid var(--navy);
    padding-left: 16px;
}

.page-heading h1 {
    margin: 0 0 6px;
    color: var(--navy);
    font-size: 28px;
    font-weight: 700;
}

.page-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
}

/* EARNINGS CARDS */

.earnings-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 28px;
}

.earning-card {
    position: relative;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 23px;
    overflow: hidden;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.earning-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(11, 31, 58, 0.08);
}

.earning-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--navy);
}

.earning-card-label {
    color: var(--muted);
    font-size: 13px;
    margin-bottom: 10px;
    padding-top: 4px;
}

.earning-card-value {
    color: var(--navy);
    font-size: 28px;
    font-weight: 700;
}

.earning-card-line {
    width: 42px;
    height: 3px;
    background: var(--navy);
    border-radius: 3px;
    margin-top: 16px;
}

/* HISTORY */

.history-section {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 12px rgba(11, 31, 58, 0.04);
}

.history-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 22px;
    border-bottom: 1px solid var(--border);
    background: white;
}

.history-header h2 {
    margin: 0;
    color: var(--navy);
    font-size: 18px;
    font-weight: 700;
}

.history-header::after {
    content: "";
    width: 34px;
    height: 3px;
    background: var(--navy);
    border-radius: 3px;
}

/* TABLE */

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: var(--navy);
    color: white;
    font-size: 12px;
    font-weight: 700;
    text-align: left;
    padding: 14px 16px;
    border-bottom: 3px solid var(--navy);
}

td {
    padding: 15px 16px;
    font-size: 13px;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
    vertical-align: top;
}

tbody tr {
    transition: background 0.15s ease;
}

tbody tr:hover {
    background: #f4f7fa;
}

tr:last-child td {
    border-bottom: none;
}

/* RIDE ID */

.ride-id {
    color: var(--navy);
    font-weight: 700;
    white-space: nowrap;
}

/* AMOUNT */

.amount {
    color: var(--green);
    font-weight: 700;
    white-space: nowrap;
}

/* ROUTE */

.route-cell {
    min-width: 260px;
}

.route-from {
    color: var(--ink);
    font-weight: 600;
    margin-bottom: 6px;
}

.route-to {
    color: var(--muted);
}

/* DATE */

.date-cell {
    white-space: nowrap;
}

/* EMPTY STATE */

.empty-state {
    padding: 55px 20px;
    text-align: center;
}

.empty-state h3 {
    margin: 0 0 8px;
    color: var(--navy);
    font-size: 17px;
}

.empty-state p {
    max-width: 500px;
    margin: 0 auto;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.6;
}

/* FOOTER */

.earnings-footer {
    max-width: 1200px;
    margin: 20px auto 0;
    padding: 20px 25px;
    text-align: center;
    color: var(--muted);
    font-size: 12px;
}

/* MOBILE */

@media (max-width: 800px) {

    .earnings-grid {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 600px) {

    .earnings-page {
        padding: 22px 18px 35px;
    }

    .page-heading h1 {
        font-size: 24px;
    }

    .earning-card {
        padding: 20px;
    }

    .earning-card-value {
        font-size: 25px;
    }

    .history-header {
        padding: 18px;
    }

    th,
    td {
        padding: 12px;
    }

    .route-cell {
        min-width: 220px;
    }

}

</style>

<div class="earnings-page">

    <a href="driver_dashboard.php" class="back-button">
        <span class="back-arrow">←</span>
        Back to Driver Dashboard
    </a>

    <div class="page-heading">

        <h1>Earnings</h1>

        <p>
            Track your earnings from completed CampusCab rides.
        </p>

    </div>

    <div class="earnings-grid">

        <div class="earning-card">

            <div class="earning-card-label">
                Today's Earnings
            </div>

            <div class="earning-card-value">
                R<?php echo number_format($todayEarnings, 2); ?>
            </div>

            <div class="earning-card-line"></div>

        </div>

        <div class="earning-card">

            <div class="earning-card-label">
                This Week
            </div>

            <div class="earning-card-value">
                R<?php echo number_format($weekEarnings, 2); ?>
            </div>

            <div class="earning-card-line"></div>

        </div>

        <div class="earning-card">

            <div class="earning-card-label">
                Total Earnings
            </div>

            <div class="earning-card-value">
                R<?php echo number_format($totalEarnings, 2); ?>
            </div>

            <div class="earning-card-line"></div>

        </div>

    </div>

    <section class="history-section">

        <div class="history-header">

            <h2>
                Completed Rides
            </h2>

        </div>

        <?php if (count($rides) > 0): ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Ride
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Route
                            </th>

                            <th>
                                Trip Fare
                            </th>

                            <th>
                                Driver Earnings
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($rides as $ride): ?>

                        <tr>

                            <td class="ride-id">
                                #<?php echo (int)$ride['rideID']; ?>
                            </td>

                            <td class="date-cell">

                                <?php

                                echo date(
                                    'd M Y, H:i',
                                    strtotime(
                                        $ride['Completed_at']
                                    )
                                );

                                ?>

                            </td>

                            <td class="route-cell">

                                <div class="route-from">

                                    <?php

                                    echo htmlspecialchars(
                                        $ride['pickup_address']
                                    );

                                    ?>

                                </div>

                                <div class="route-to">

                                    →

                                    <?php

                                    echo htmlspecialchars(
                                        $ride['destination_address']
                                    );

                                    ?>

                                </div>

                            </td>

                            <td>

                                R<?php

                                echo number_format(
                                    (float)$ride['estimated_price'],
                                    2
                                );

                                ?>

                            </td>

                            <td class="amount">

                                R<?php

                                echo number_format(
                                    (float)$ride['driver_earning'],
                                    2
                                );

                                ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <h3>
                    No completed rides yet
                </h3>

                <p>
                    Your earnings will appear here after you complete CampusCab rides.
                </p>

            </div>

        <?php endif; ?>

    </section>

</div>

