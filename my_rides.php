<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

date_default_timezone_set('Africa/Johannesburg');

$studentID = (int)($_SESSION['student_id'] ?? 0);
$staffID = (int)($_SESSION['staff_id'] ?? 0);

$userType = '';
$userID = 0;
$userName = 'User';
$dashboardPage = 'dashboard.php';

if ($studentID > 0) {

    $userType = 'student';
    $userID = $studentID;
    $userName = $_SESSION['student_name'] ?? 'Student';
    $dashboardPage = 'dashboard.php';

} elseif ($staffID > 0) {

    $userType = 'staff';
    $userID = $staffID;
    $userName = $_SESSION['staff_name'] ?? 'Staff';
    $dashboardPage = 'staff_dashboard.php';

} else {

    header("Location: login_student.php");
    exit;
}

$rides = [];

if ($userType === 'student') {

    $stmt = $conn->prepare("
        SELECT
            r.rideID,
            r.studentID,
            r.staff_id,
            r.pickup_address,
            r.destination_address,
            r.pickup_latitude,
            r.pickup_longitude,
            r.destination_latitude,
            r.destination_longitude,
            r.estimated_distance_km,
            r.estimated_duration_min,
            r.estimated_price,
            r.DriverID,
            r.request_type,
            r.requested_at,
            r.accepted_at,
            r.driver_arrived_at,
            r.ride_status,
            d.DriverFname,
            d.DriverLname,
            d.DriverPhoneNo
        FROM ride r
        LEFT JOIN driver d
            ON r.DriverID = d.DriverID
        WHERE r.studentID = ?
          AND r.request_type = 'ride'
        ORDER BY r.requested_at DESC
    ");

} else {

    $stmt = $conn->prepare("
        SELECT
            r.rideID,
            r.studentID,
            r.staff_id,
            r.pickup_address,
            r.destination_address,
            r.pickup_latitude,
            r.pickup_longitude,
            r.destination_latitude,
            r.destination_longitude,
            r.estimated_distance_km,
            r.estimated_duration_min,
            r.estimated_price,
            r.DriverID,
            r.request_type,
            r.requested_at,
            r.accepted_at,
            r.driver_arrived_at,
            r.ride_status,
            d.DriverFname,
            d.DriverLname,
            d.DriverPhoneNo
        FROM ride r
        LEFT JOIN driver d
            ON r.DriverID = d.DriverID
        WHERE r.staff_id = ?
          AND r.request_type = 'ride'
        ORDER BY r.requested_at DESC
    ");
}

if ($stmt) {

    $stmt->bind_param("i", $userID);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $rides[] = $row;
    }

    $stmt->close();
}

function getRideStatusMessage($status)
{
    switch (strtolower(trim($status))) {

        case 'requested':
            return 'Your ride request has been submitted and is waiting for a driver to accept it.';

        case 'accepted':
            return 'A driver has accepted your ride request. Please be ready at your pickup location.';

        case 'driver arrived':
            return 'Your driver has arrived at the pickup location. Please meet your driver.';

        case 'in progress':
            return 'Your ride is currently in progress.';

        case 'completed':
            return 'Your ride has been completed successfully.';

        case 'cancelled':
            return 'This ride request has been cancelled.';

        default:
            return 'Your ride request is being processed.';
    }
}

function getRideStatusClass($status)
{
    switch (strtolower(trim($status))) {

        case 'requested':
            return 'status-requested';

        case 'accepted':
            return 'status-accepted';

        case 'driver arrived':
            return 'status-arrived';

        case 'in progress':
            return 'status-progress';

        case 'completed':
            return 'status-completed';

        case 'cancelled':
            return 'status-cancelled';

        default:
            return '';
    }
}

function getRideStep($status)
{
    $steps = [
        'requested',
        'accepted',
        'driver arrived',
        'in progress',
        'completed'
    ];

    $index = array_search(
        strtolower(trim($status)),
        $steps
    );

    return $index === false ? 0 : $index;
}

$pageTitle = "My Ride Requests";

include 'header.php';

?>

<style>

.page-wrapper {
    max-width: 1050px;
    margin: 0 auto;
    padding: 30px 20px 50px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0c2d4d;
    color: #ffffff;
    padding: 10px 16px;
    border-radius: 7px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 22px;
    transition: background .2s ease;
}

.back-button:hover {
    background: #185fa5;
}

.page-title {
    margin-bottom: 25px;
}

.page-title h1 {
    margin: 0;
    color: #0c2d4d;
    font-size: 30px;
}

.page-title p {
    margin-top: 7px;
    color: #6b7280;
    font-size: 14px;
}

.auto-refresh {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin-top: 10px;
    color: #6b7280;
    font-size: 11px;
}

.refresh-dot {
    width: 7px;
    height: 7px;
    background: #0f6e56;
    border-radius: 50%;
}

.ride-card {
    background: #ffffff;
    border: 1px solid #e1e4e8;
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.ride-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.ride-number {
    font-size: 18px;
    font-weight: 700;
    color: #0c2d4d;
}

.status {
    display: inline-block;
    padding: 7px 13px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.status-requested {
    background: #f4f1e8;
    color: #8a6500;
}

.status-accepted {
    background: #e0edf7;
    color: #185fa5;
}

.status-arrived {
    background: #e9f5f1;
    color: #0f6e56;
}

.status-progress {
    background: #e9f5f1;
    color: #0f6e56;
}

.status-completed {
    background: #e9f5f1;
    color: #0f6e56;
}

.status-cancelled {
    background: #f8ecea;
    color: #b03a2e;
}

.status-message {
    background: #f6f5f1;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 20px;
    color: #394456;
    line-height: 1.5;
    font-size: 14px;
}

.status-message strong {
    color: #0c2d4d;
}

.progress-section {
    padding: 5px 0 25px;
}

.progress-track {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    position: relative;
    margin-top: 10px;
}

.progress-line {
    position: absolute;
    top: 12px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #dfe4e8;
    z-index: 0;
}

.progress-step {
    position: relative;
    z-index: 1;
    text-align: center;
}

.progress-circle {
    width: 24px;
    height: 24px;
    margin: 0 auto 7px;
    border-radius: 50%;
    background: #dfe4e8;
    border: 3px solid #ffffff;
    box-shadow: 0 0 0 1px #dfe4e8;
}

.progress-step.active .progress-circle,
.progress-step.completed .progress-circle {
    background: #0f6e56;
    box-shadow: 0 0 0 1px #0f6e56;
}

.progress-label {
    font-size: 9px;
    color: #7a858f;
    line-height: 1.3;
}

.progress-step.active .progress-label,
.progress-step.completed .progress-label {
    color: #0c2d4d;
    font-weight: 700;
}

.route-box {
    border-left: 3px solid #0c2d4d;
    padding-left: 17px;
    margin-bottom: 20px;
}

.location {
    margin-bottom: 17px;
}

.location:last-child {
    margin-bottom: 0;
}

.location-label {
    color: #8a919b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.location-text {
    color: #172033;
    font-size: 15px;
    line-height: 1.5;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.detail {
    background: #f6f5f1;
    border-radius: 9px;
    padding: 13px;
}

.detail-label {
    color: #6b7280;
    font-size: 12px;
    margin-bottom: 5px;
}

.detail-value {
    color: #172033;
    font-weight: 700;
    line-height: 1.4;
    font-size: 14px;
}

.driver-box {
    border: 1px solid #e1e4e8;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 20px;
}

.driver-title {
    color: #0c2d4d;
    font-weight: 700;
    margin-bottom: 10px;
}

.driver-name {
    color: #172033;
    font-weight: 700;
    margin-bottom: 5px;
}

.driver-phone {
    color: #6b7280;
    font-size: 14px;
}

.waiting-driver {
    background: #f6f5f1;
    border: 1px solid #e1e4e8;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    color: #394456;
    font-size: 14px;
    line-height: 1.5;
}

.waiting-driver strong {
    color: #0c2d4d;
}

.arrived-box {
    background: #e9f5f1;
    border: 1px solid #c9e6db;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    color: #0f6e56;
    font-size: 14px;
    line-height: 1.5;
}

.arrived-box strong {
    color: #0c5b48;
}

.date-time-box {
    border-top: 1px solid #e1e4e8;
    padding-top: 15px;
    margin-top: 5px;
}

.date-time-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 7px;
    font-size: 13px;
}

.date-time-row:last-child {
    margin-bottom: 0;
}

.date-time-label {
    color: #6b7280;
}

.date-time-value {
    color: #394456;
    font-weight: 600;
    text-align: right;
}

.empty-card {
    background: #ffffff;
    border: 1px solid #e1e4e8;
    border-radius: 14px;
    padding: 40px 25px;
    text-align: center;
}

.empty-card h3 {
    color: #0c2d4d;
    margin-bottom: 8px;
}

.empty-card p {
    color: #6b7280;
    margin-bottom: 20px;
    font-size: 14px;
}

.request-button {
    display: inline-block;
    background: #0c2d4d;
    color: #ffffff;
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
}

.request-button:hover {
    background: #185fa5;
}

.cancelled-message {
    background: #f8ecea;
    color: #b03a2e;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: 13px;
}

@media (max-width: 700px) {

    .page-wrapper {
        padding: 20px 15px 40px;
    }

    .page-title h1 {
        font-size: 25px;
    }

    .ride-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .date-time-row {
        flex-direction: column;
        gap: 3px;
    }

    .date-time-value {
        text-align: left;
    }

    .progress-label {
        font-size: 8px;
    }
}

</style>

<div class="page-wrapper">

    <a
        href="<?php echo htmlspecialchars($dashboardPage); ?>"
        class="back-button"
    >
        ← Back to Dashboard
    </a>

    <div class="page-title">

        <h1>
            My Ride Requests
        </h1>

        <p>
            View and track your CampusCab ride requests.
        </p>

        <div class="auto-refresh">
            <span class="refresh-dot"></span>
            Ride status updates automatically
        </div>

    </div>

    <?php if (empty($rides)): ?>

        <div class="empty-card">

            <h3>
                No Ride Requests Yet
            </h3>

            <p>
                You have not submitted any CampusCab ride requests.
            </p>

            <a
                href="request.php"
                class="request-button"
            >
                Request a Ride
            </a>

        </div>

    <?php else: ?>

        <?php foreach ($rides as $ride): ?>

            <?php

            $status =
                trim($ride['ride_status'] ?? 'Requested');

            $statusClass =
                getRideStatusClass($status);

            $currentStep =
                getRideStep($status);

            $driverName =
                trim(
                    ($ride['DriverFname'] ?? '') . ' ' .
                    ($ride['DriverLname'] ?? '')
                );

            $requestedDate = '';

            if (!empty($ride['requested_at'])) {

                $requestedDate = date(
                    'd M Y, H:i',
                    strtotime($ride['requested_at'])
                );
            }

            $acceptedDate = '';

            if (!empty($ride['accepted_at'])) {

                $acceptedDate = date(
                    'd M Y, H:i',
                    strtotime($ride['accepted_at'])
                );
            }

            $arrivedDate = '';

            if (!empty($ride['driver_arrived_at'])) {

                $arrivedDate = date(
                    'd M Y, H:i',
                    strtotime($ride['driver_arrived_at'])
                );
            }

            $distance = '';

            if (
                isset($ride['estimated_distance_km']) &&
                $ride['estimated_distance_km'] !== ''
            ) {

                $distance =
                    number_format(
                        (float)$ride['estimated_distance_km'],
                        2
                    ) . ' km';
            }

            $duration = '';

            if (
                isset($ride['estimated_duration_min']) &&
                $ride['estimated_duration_min'] !== ''
            ) {

                $duration =
                    number_format(
                        (float)$ride['estimated_duration_min'],
                        0
                    ) . ' min';
            }

            $fare = '';

            if (
                isset($ride['estimated_price']) &&
                $ride['estimated_price'] !== ''
            ) {

                $fare =
                    'R' . number_format(
                        (float)$ride['estimated_price'],
                        2
                    );
            }

            ?>

            <div class="ride-card">

                <div class="ride-header">

                    <div class="ride-number">

                        Ride #
                        <?php echo (int)$ride['rideID']; ?>

                    </div>

                    <span
                        class="status <?php echo htmlspecialchars(
                            $statusClass
                        ); ?>"
                    >

                        <?php echo htmlspecialchars($status); ?>

                    </span>

                </div>

                <div class="status-message">

                    <strong>

                        <?php

                        if ($status === 'Requested') {

                            echo 'Ride request submitted.';

                        } elseif ($status === 'Accepted') {

                            echo 'Driver accepted your ride.';

                        } elseif ($status === 'Driver Arrived') {

                            echo 'Your driver has arrived.';

                        } elseif ($status === 'In Progress') {

                            echo 'Your ride is in progress.';

                        } elseif ($status === 'Completed') {

                            echo 'Ride completed.';

                        } elseif ($status === 'Cancelled') {

                            echo 'Ride request cancelled.';

                        } else {

                            echo 'Ride status updated.';

                        }

                        ?>

                    </strong>

                    <br>

                    <?php

                    echo htmlspecialchars(
                        getRideStatusMessage($status)
                    );

                    ?>

                </div>

                <?php if (
                    strtolower($status) !== 'cancelled'
                ): ?>

                    <div class="progress-section">

                        <div class="progress-track">

                            <div class="progress-line"></div>

                            <?php

                            $steps = [
                                'Requested',
                                'Accepted',
                                'Driver Arrived',
                                'In Progress',
                                'Completed'
                            ];

                            ?>

                            <?php foreach (
                                $steps as $index => $step
                            ): ?>

                                <?php

                                $stepClass = '';

                                if ($index < $currentStep) {

                                    $stepClass = 'completed';

                                } elseif (
                                    $index === $currentStep
                                ) {

                                    $stepClass = 'active';
                                }

                                ?>

                                <div
                                    class="progress-step <?php
                                    echo $stepClass;
                                    ?>"
                                >

                                    <div class="progress-circle"></div>

                                    <div class="progress-label">

                                        <?php
                                        echo htmlspecialchars($step);
                                        ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>

                <?php if (
                    strtolower($status) === 'cancelled'
                ): ?>

                    <div class="cancelled-message">

                        This ride request is no longer active.

                    </div>

                <?php endif; ?>

                <div class="route-box">

                    <div class="location">

                        <div class="location-label">
                            Pickup
                        </div>

                        <div class="location-text">

                            <?php

                            echo htmlspecialchars(
                                $ride['pickup_address'] ?? ''
                            );

                            ?>

                        </div>

                    </div>

                    <div class="location">

                        <div class="location-label">
                            Destination
                        </div>

                        <div class="location-text">

                            <?php

                            echo htmlspecialchars(
                                $ride['destination_address'] ?? ''
                            );

                            ?>

                        </div>

                    </div>

                </div>

                <div class="details-grid">

                    <div class="detail">

                        <div class="detail-label">
                            Estimated Distance
                        </div>

                        <div class="detail-value">

                            <?php

                            echo htmlspecialchars(
                                $distance !== ''
                                    ? $distance
                                    : 'Not available'
                            );

                            ?>

                        </div>

                    </div>

                    <div class="detail">

                        <div class="detail-label">
                            Estimated Duration
                        </div>

                        <div class="detail-value">

                            <?php

                            echo htmlspecialchars(
                                $duration !== ''
                                    ? $duration
                                    : 'Not available'
                            );

                            ?>

                        </div>

                    </div>

                    <div class="detail">

                        <div class="detail-label">
                            Estimated Fare
                        </div>

                        <div class="detail-value">

                            <?php

                            echo htmlspecialchars(
                                $fare !== ''
                                    ? $fare
                                    : 'Not available'
                            );

                            ?>

                        </div>

                    </div>

                </div>

                <?php if (!empty($ride['DriverID'])): ?>

                    <div class="driver-box">

                        <div class="driver-title">
                            Your Driver
                        </div>

                        <div class="driver-name">

                            <?php

                            echo htmlspecialchars(
                                $driverName !== ''
                                    ? $driverName
                                    : 'Driver'
                            );

                            ?>

                        </div>

                        <?php if (
                            !empty($ride['DriverPhoneNo'])
                        ): ?>

                            <div class="driver-phone">

                                Phone:
                                <?php

                                echo htmlspecialchars(
                                    $ride['DriverPhoneNo']
                                );

                                ?>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php elseif ($status === 'Requested'): ?>

                    <div class="waiting-driver">

                        <strong>
                            Driver not assigned yet.
                        </strong>

                        <br>

                        We are waiting for an available
                        CampusCab driver to accept your ride request.

                    </div>

                <?php endif; ?>

                <?php if ($status === 'Driver Arrived'): ?>

                    <div class="arrived-box">

                        <strong>
                            Your driver has arrived at the pickup location.
                        </strong>

                        <br>

                        Please meet your driver to begin your ride.

                    </div>

                <?php endif; ?>

                <div class="date-time-box">

                    <?php if ($requestedDate !== ''): ?>

                        <div class="date-time-row">

                            <span class="date-time-label">
                                Requested
                            </span>

                            <span class="date-time-value">

                                <?php
                                echo htmlspecialchars(
                                    $requestedDate
                                );
                                ?>

                            </span>

                        </div>

                    <?php endif; ?>

                    <?php if ($acceptedDate !== ''): ?>

                        <div class="date-time-row">

                            <span class="date-time-label">
                                Accepted
                            </span>

                            <span class="date-time-value">

                                <?php
                                echo htmlspecialchars(
                                    $acceptedDate
                                );
                                ?>

                            </span>

                        </div>

                    <?php endif; ?>

                    <?php if ($arrivedDate !== ''): ?>

                        <div class="date-time-row">

                            <span class="date-time-label">
                                Driver Arrived
                            </span>

                            <span class="date-time-value">

                                <?php
                                echo htmlspecialchars(
                                    $arrivedDate
                                );
                                ?>

                            </span>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<script>

setInterval(function () {
    window.location.reload();
}, 5000);

</script>