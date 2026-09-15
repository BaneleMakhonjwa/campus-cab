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

$parcelID = isset($_POST['parcelID'])
    ? (int)$_POST['parcelID']
    : (int)($_GET['parcelID'] ?? 0);

if ($parcelID <= 0) {
    header("Location: driver_parcel_requests.php?error=invalid_parcel");
    exit;
}

$stmt = $conn->prepare("
    UPDATE parcel
    SET
        parcel_status = 'Completed',
        completed_at = NOW()
    WHERE parcel = ?
      AND DriverID = ?
      AND parcel_status = 'In Progress'
");

if (!$stmt) {
    header("Location: driver_parcel_requests.php?error=database");
    exit;
}

$stmt->bind_param(
    "ii",
    $parcelID,
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
            <title>Parcel Completed - CampusCab</title>

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

                .parcels-button {
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

                <h1>Parcel Completed</h1>

                <p>
                    The parcel delivery has been successfully completed.
                </p>

                <a href="driver_dashboard.php" class="dashboard-button">
                    Back to Dashboard
                </a>

                <br>

                <a href="driver_parcel_requests.php" class="parcels-button">
                    View Parcel Requests
                </a>

            </div>

        </body>
        </html>

        <?php
        exit;
    }

    $stmt->close();

    header(
        "Location: driver_parcel_requests.php?error=not_in_progress"
    );

    exit;
}

$stmt->close();

header(
    "Location: driver_parcel_requests.php?error=database"
);

exit;
?>