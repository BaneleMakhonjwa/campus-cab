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

$rideID = isset($_POST['rideID'])
    ? (int)$_POST['rideID']
    : (int)($_GET['rideID'] ?? 0);

if ($rideID <= 0) {
    header("Location: driver_ride_requests.php?error=invalid_ride");
    exit;
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

if (!$stmt) {
    header("Location: driver_ride_requests.php?error=database");
    exit;
}

$stmt->bind_param(
    "ii",
    $rideID,
    $driverID
);

if ($stmt->execute()) {

    if ($stmt->affected_rows === 1) {

        $stmt->close();
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ride Completed - CampusCab</title>

            <style>
                body {
                    margin: 0;
                    font-family: Arial, sans-serif;
                    background: #f6f5f1;
                    color: #0c2d4d;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                }

                .completion-card {
                    width: 90%;
                    max-width: 500px;
                    background: white;
                    padding: 40px;
                    border-radius: 12px;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                }

                .success-icon {
                    width: 65px;
                    height: 65px;
                    margin: 0 auto 20px;
                    border-radius: 50%;
                    background: #0f6e56;
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 32px;
                    font-weight: bold;
                }

                h1 {
                    margin: 0 0 10px;
                    color: #0c2d4d;
                }

                p {
                    color: #555;
                    margin-bottom: 25px;
                }

                .dashboard-button {
                    display: inline-block;
                    padding: 13px 24px;
                    background: #0c2d4d;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                    font-weight: 600;
                    transition: background 0.2s ease;
                }

                .dashboard-button:hover {
                    background: #185fa5;
                }

                .rides-button {
                    display: inline-block;
                    margin-top: 12px;
                    padding: 12px 24px;
                    color: #0c2d4d;
                    text-decoration: none;
                    font-weight: 600;
                }
            </style>
        </head>

        <body>

            <div class="completion-card">

                <div class="success-icon">
                    ✓
                </div>

                <h1>Ride Completed</h1>

                <p>
                    The ride has been successfully completed.
                </p>

                <a href="driver_dashboard.php" class="dashboard-button">
                    Back to Dashboard
                </a>

                <br>

                <a href="driver_ride_requests.php" class="rides-button">
                    View Ride Requests
                </a>

            </div>

        </body>
        </html>

        <?php
        exit;
    }

    $stmt->close();

    header(
        "Location: driver_ride_requests.php?error=not_in_progress"
    );

    exit;
}

$stmt->close();

header(
    "Location: driver_ride_requests.php?error=database"
);

exit;
?>