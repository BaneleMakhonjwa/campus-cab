<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

date_default_timezone_set('Africa/Johannesburg');

$studentID = (int)($_SESSION['student_id'] ?? 0);
$staffID = (int)($_SESSION['staff_id'] ?? 0);

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

$parcels = [];

if ($userType === 'student') {

    $sql = "
        SELECT
            p.parcel,
            p.studentID,
            p.staff_id,
            p.parcel_description,
            p.parcel_size,
            p.recipient_name,
            p.recipient_contact,
            p.pickup_location,
            p.destination,
            p.estimated_price,
            p.DriverID,
            p.requested_at,
            p.parcel_status,
            p.completed_at,
            d.DriverFname,
            d.DriverLname,
            d.DriverPhoneNo
        FROM parcel p
        LEFT JOIN driver d
            ON p.DriverID = d.DriverID
        WHERE p.studentID = ?
        ORDER BY p.requested_at DESC
    ";

} else {

    $sql = "
        SELECT
            p.parcel,
            p.studentID,
            p.staff_id,
            p.parcel_description,
            p.parcel_size,
            p.recipient_name,
            p.recipient_contact,
            p.pickup_location,
            p.destination,
            p.estimated_price,
            p.DriverID,
            p.requested_at,
            p.parcel_status,
            p.completed_at,
            d.DriverFname,
            d.DriverLname,
            d.DriverPhoneNo
        FROM parcel p
        LEFT JOIN driver d
            ON p.DriverID = d.DriverID
        WHERE p.staff_id = ?
        ORDER BY p.requested_at DESC
    ";
}

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $userID);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $parcels[] = $row;
    }

    $stmt->close();
}

function getParcelStatusMessage($status)
{
    switch (strtolower(trim($status))) {

        case 'requested':
            return 'Your parcel request has been submitted and is waiting for a driver.';

        case 'accepted':
            return 'A driver has accepted your parcel request.';

        case 'picked up':
            return 'Your parcel has been picked up and is with the driver.';

        case 'in transit':
            return 'Your parcel is currently in transit to the destination.';

        case 'delivered':
            return 'Your parcel has been delivered successfully.';

        case 'cancelled':
            return 'This parcel delivery has been cancelled.';

        default:
            return 'Your parcel request is being processed.';
    }
}

function getStatusClass($status)
{
    switch (strtolower(trim($status))) {

        case 'requested':
            return 'status-requested';

        case 'accepted':
            return 'status-accepted';

        case 'picked up':
            return 'status-picked-up';

        case 'in transit':
            return 'status-progress';

        case 'delivered':
            return 'status-delivered';

        case 'cancelled':
            return 'status-cancelled';

        default:
            return '';
    }
}

$pageTitle = "My Parcel Requests";

include 'header.php';

?>

<style>

.parcel-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 25px;
}

.back-dashboard {
    margin-bottom: 20px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    background: #0c2d4d;
    color: #ffffff;
    padding: 10px 16px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 600;
    transition: background 0.2s ease;
}

.back-btn:hover {
    background: #185fa5;
}

.page-heading {
    margin-bottom: 25px;
}

.page-heading h1 {
    margin: 0 0 6px;
    color: #0c2d4d;
    font-size: 25px;
}

.page-heading p {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
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

.empty-state {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    padding: 45px 25px;
    text-align: center;
}

.empty-state h3 {
    margin: 0 0 8px;
    color: #0c2d4d;
}

.empty-state p {
    color: #6b7280;
    font-size: 13px;
    margin-bottom: 20px;
}

.request-btn {
    display: inline-block;
    background: #0c2d4d;
    color: #ffffff;
    text-decoration: none;
    padding: 10px 17px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 600;
}

.request-btn:hover {
    background: #185fa5;
}

.parcel-card {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    margin-bottom: 18px;
    overflow: hidden;
    box-shadow: 0 2px 7px rgba(12, 45, 77, 0.04);
}

.parcel-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 17px 20px;
    border-bottom: 1px solid #e8ebee;
}

.parcel-number {
    color: #0c2d4d;
    font-size: 15px;
    font-weight: 700;
}

.status-badge {
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    background: #eef2f5;
    color: #0c2d4d;
}

.status-requested {
    background: #f4f1e8;
    color: #8a6500;
}

.status-accepted {
    background: #eaf2f9;
    color: #185fa5;
}

.status-picked-up {
    background: #f4f1e8;
    color: #8a6500;
}

.status-progress {
    background: #e9f5f1;
    color: #0f6e56;
}

.status-delivered {
    background: #e9f5f1;
    color: #0f6e56;
}

.status-cancelled {
    background: #f8ecea;
    color: #b03a2e;
}

.status-message {
    padding: 13px 20px;
    color: #5f6b76;
    font-size: 12px;
    background: #fafbfc;
    border-bottom: 1px solid #e8ebee;
}

.route-section {
    padding: 20px;
    border-left: 3px solid #0f6e56;
    margin: 18px 20px;
    background: #fafbfc;
}

.route-item {
    margin-bottom: 13px;
}

.route-item:last-child {
    margin-bottom: 0;
}

.route-label {
    display: block;
    color: #7a858f;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 4px;
    font-weight: 700;
}

.route-value {
    color: #0c2d4d;
    font-size: 13px;
    font-weight: 500;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    padding: 0 20px 20px;
}

.detail-box {
    border: 1px solid #e5e8eb;
    border-radius: 7px;
    padding: 13px;
    background: #ffffff;
}

.detail-label {
    display: block;
    color: #7a858f;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 5px;
}

.detail-value {
    color: #0c2d4d;
    font-size: 13px;
    font-weight: 600;
}

.description-section {
    padding: 0 20px 20px;
}

.description-section h4,
.driver-section h4 {
    margin: 0 0 8px;
    color: #0c2d4d;
    font-size: 13px;
}

.description-section p {
    margin: 0;
    color: #5f6b76;
    font-size: 12px;
    line-height: 1.6;
}

.driver-section {
    margin: 0 20px 20px;
    padding: 16px;
    background: #f6f5f1;
    border-radius: 8px;
}

.driver-details {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.driver-info {
    color: #4f5b66;
    font-size: 12px;
}

.driver-info strong {
    color: #0c2d4d;
}

.progress-section {
    padding: 5px 20px 22px;
}

.progress-track {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    position: relative;
    margin-top: 15px;
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

.date-section {
    border-top: 1px solid #e8ebee;
    padding: 13px 20px;
    display: flex;
    gap: 30px;
    color: #6b7280;
    font-size: 11px;
}

.date-section strong {
    color: #0c2d4d;
}

.cancelled-notice {
    margin: 0 20px 20px;
    padding: 12px 15px;
    background: #f8ecea;
    color: #b03a2e;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 600;
}

@media (max-width: 700px) {

    .parcel-page {
        padding: 18px;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .parcel-card-header {
        align-items: flex-start;
        gap: 10px;
    }

    .driver-details {
        flex-direction: column;
        gap: 8px;
    }

    .date-section {
        flex-direction: column;
        gap: 7px;
    }

    .progress-label {
        font-size: 8px;
    }
}

</style>

<div class="parcel-page">

    <div class="back-dashboard">

        <a
            href="<?= htmlspecialchars($dashboardPage) ?>"
            class="back-btn"
        >
            ← Back to Dashboard
        </a>

    </div>

    <div class="page-heading">

        <h1>My Parcel Requests</h1>

        <p>
            View and track your parcel requests.
        </p>

        <div class="auto-refresh">
            <span class="refresh-dot"></span>
            Status updates automatically
        </div>

    </div>

    <?php if (empty($parcels)): ?>

        <div class="empty-state">

            <h3>No Parcel Requests</h3>

            <p>
                You have not submitted any parcel requests yet.
            </p>

            <a
                href="request_parcel.php"
                class="request-btn"
            >
                Request a Parcel
            </a>

        </div>

    <?php else: ?>

        <?php foreach ($parcels as $parcel): ?>

            <?php

            $status =
                $parcel['parcel_status'] ?? 'Requested';

            $statusClass =
                getStatusClass($status);

            $statusMessage =
                getParcelStatusMessage($status);

            $currentStatus =
                strtolower(trim($status));

            $statusSteps = [
                'requested',
                'accepted',
                'picked up',
                'in transit',
                'delivered'
            ];

            $currentStep = array_search(
                $currentStatus,
                $statusSteps
            );

            if ($currentStep === false) {
                $currentStep = 0;
            }

            ?>

            <div class="parcel-card">

                <div class="parcel-card-header">

                    <div class="parcel-number">

                        Parcel #<?= htmlspecialchars(
                            $parcel['parcel']
                        ) ?>

                    </div>

                    <div
                        class="status-badge <?= htmlspecialchars(
                            $statusClass
                        ) ?>"
                    >

                        <?= htmlspecialchars($status) ?>

                    </div>

                </div>

                <div class="status-message">

                    <?= htmlspecialchars($statusMessage) ?>

                </div>

                <?php if ($currentStatus !== 'cancelled'): ?>

                    <div class="progress-section">

                        <div class="progress-track">

                            <div class="progress-line"></div>

                            <?php foreach (
                                $statusSteps as $index => $step
                            ): ?>

                                <?php

                                $stepClass = '';

                                if ($index < $currentStep) {
                                    $stepClass = 'completed';
                                } elseif ($index === $currentStep) {
                                    $stepClass = 'active';
                                }

                                $stepLabel = ucwords($step);

                                ?>

                                <div
                                    class="progress-step <?= $stepClass ?>"
                                >

                                    <div class="progress-circle"></div>

                                    <div class="progress-label">
                                        <?= htmlspecialchars($stepLabel) ?>
                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>

                <div class="route-section">

                    <div class="route-item">

                        <span class="route-label">
                            Pickup Location
                        </span>

                        <div class="route-value">
                            <?= htmlspecialchars(
                                $parcel['pickup_location']
                            ) ?>
                        </div>

                    </div>

                    <div class="route-item">

                        <span class="route-label">
                            Destination
                        </span>

                        <div class="route-value">
                            <?= htmlspecialchars(
                                $parcel['destination']
                            ) ?>
                        </div>

                    </div>

                </div>

                <div class="details-grid">

                    <div class="detail-box">

                        <span class="detail-label">
                            Parcel Size
                        </span>

                        <div class="detail-value">
                            <?= htmlspecialchars(
                                $parcel['parcel_size']
                            ) ?>
                        </div>

                    </div>

                    <div class="detail-box">

                        <span class="detail-label">
                            Estimated Price
                        </span>

                        <div class="detail-value">

                            R<?= number_format(
                                (float)$parcel['estimated_price'],
                                2
                            ) ?>

                        </div>

                    </div>

                    <div class="detail-box">

                        <span class="detail-label">
                            Recipient
                        </span>

                        <div class="detail-value">
                            <?= htmlspecialchars(
                                $parcel['recipient_name']
                            ) ?>
                        </div>

                    </div>

                    <div class="detail-box">

                        <span class="detail-label">
                            Recipient Contact
                        </span>

                        <div class="detail-value">
                            <?= htmlspecialchars(
                                $parcel['recipient_contact']
                            ) ?>
                        </div>

                    </div>

                    <?php if (!empty($parcel['DriverID'])): ?>

                        <div class="detail-box">

                            <span class="detail-label">
                                Driver ID
                            </span>

                            <div class="detail-value">
                                <?= htmlspecialchars(
                                    $parcel['DriverID']
                                ) ?>
                            </div>

                        </div>

                    <?php endif; ?>

                </div>

                <?php if (
                    !empty($parcel['parcel_description'])
                ): ?>

                    <div class="description-section">

                        <h4>
                            Parcel Description
                        </h4>

                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $parcel['parcel_description']
                                )
                            ) ?>

                        </p>

                    </div>

                <?php endif; ?>

                <?php if (!empty($parcel['DriverID'])): ?>

                    <?php

                    $driverName = trim(
                        ($parcel['DriverFname'] ?? '') . ' ' .
                        ($parcel['DriverLname'] ?? '')
                    );

                    if ($driverName === '') {
                        $driverName = 'Assigned Driver';
                    }

                    ?>

                    <div class="driver-section">

                        <h4>
                            Assigned Driver
                        </h4>

                        <div class="driver-details">

                            <div class="driver-info">

                                <strong>
                                    <?= htmlspecialchars(
                                        $driverName
                                    ) ?>
                                </strong>

                            </div>

                            <div class="driver-info">

                                <strong>
                                    Contact:
                                </strong>

                                <?= htmlspecialchars(
                                    $parcel['DriverPhoneNo']
                                    ?? 'Not available'
                                ) ?>

                            </div>

                        </div>

                    </div>

                <?php elseif ($currentStatus === 'requested'): ?>

                    <div class="driver-section">

                        <h4>
                            Driver
                        </h4>

                        <div class="driver-info">
                            No driver has been assigned yet.
                        </div>

                    </div>

                <?php endif; ?>

                <?php if ($currentStatus === 'cancelled'): ?>

                    <div class="cancelled-notice">

                        This parcel delivery has been cancelled.

                    </div>

                <?php endif; ?>

                <div class="date-section">

                    <div>

                        <strong>
                            Requested:
                        </strong>

                        <?= !empty($parcel['requested_at'])
                            ? date(
                                'd M Y, H:i',
                                strtotime(
                                    $parcel['requested_at']
                                )
                            )
                            : 'Not available'
                        ?>

                    </div>

                    <?php if (
                        !empty($parcel['completed_at'])
                    ): ?>

                        <div>

                            <strong>
                                Delivered:
                            </strong>

                            <?= date(
                                'd M Y, H:i',
                                strtotime(
                                    $parcel['completed_at']
                                )
                            ) ?>

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