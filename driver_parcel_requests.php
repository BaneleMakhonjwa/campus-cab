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
            ($row['DriverFname'] ?? '') . ' ' .
            ($row['DriverLname'] ?? '')
        );

        if ($driverName === '') {
            $driverName = "Driver";
        }
    }

    $stmt->close();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $parcelID = (int)($_POST['parcelID'] ?? 0);

    if ($parcelID <= 0) {
        $message = "Invalid parcel.";
        $messageType = "error";
    } else {

        if ($action === 'accept') {

            $stmt = $conn->prepare("
                UPDATE parcel
                SET
                    DriverID = ?,
                    parcel_status = 'Accepted'
                WHERE parcel = ?
                  AND parcel_status = 'Requested'
                  AND DriverID IS NULL
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "ii",
                    $driverID,
                    $parcelID
                );

                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    $message = "Parcel accepted successfully.";
                    $messageType = "success";
                } else {
                    $message = "This parcel is no longer available.";
                    $messageType = "error";
                }

                $stmt->close();
            }

        } elseif ($action === 'start') {

            $stmt = $conn->prepare("
                UPDATE parcel
                SET parcel_status = 'In Progress'
                WHERE parcel = ?
                  AND DriverID = ?
                  AND parcel_status = 'Accepted'
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "ii",
                    $parcelID,
                    $driverID
                );

                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    $message = "Delivery started.";
                    $messageType = "success";
                } else {
                    $message = "Unable to start this delivery.";
                    $messageType = "error";
                }

                $stmt->close();
            }

        } elseif ($action === 'cancel') {

            $reason = trim(
                $_POST['cancellation_reason'] ?? ''
            );

            $stmt = $conn->prepare("
                UPDATE parcel
                SET
                    parcel_status = 'Cancelled',
                    cancelled_at = NOW(),
                    cancellation_reason = ?
                WHERE parcel = ?
                  AND DriverID = ?
                  AND parcel_status IN ('Accepted', 'In Progress')
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "sii",
                    $reason,
                    $parcelID,
                    $driverID
                );

                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    $message = "Delivery cancelled.";
                    $messageType = "success";
                } else {
                    $message = "Unable to cancel this delivery.";
                    $messageType = "error";
                }

                $stmt->close();
            }
        }
    }
}

$stmt = $conn->prepare("
    SELECT
        p.parcel,
        p.studentID,
        p.parcel_description,
        p.parcel_size,
        p.recipient_name,
        p.recipient_contact,
        p.pickup_location,
        p.destination,
        p.estimated_price,
        p.requested_at,
        p.parcel_status,
        s.StudentFname,
        s.StudentLname
    FROM parcel p
    LEFT JOIN student s
        ON p.studentID = s.studentID
    WHERE p.parcel_status = 'Requested'
      AND p.DriverID IS NULL
    ORDER BY p.requested_at ASC
");

$newParcels = [];

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $newParcels[] = $row;
    }

    $stmt->close();
}

$stmt = $conn->prepare("
    SELECT
        p.parcel,
        p.studentID,
        p.parcel_description,
        p.parcel_size,
        p.recipient_name,
        p.recipient_contact,
        p.pickup_location,
        p.destination,
        p.estimated_price,
        p.requested_at,
        p.parcel_status,
        s.StudentFname,
        s.StudentLname
    FROM parcel p
    LEFT JOIN student s
        ON p.studentID = s.studentID
    WHERE p.DriverID = ?
      AND p.parcel_status IN ('Accepted', 'In Progress')
    ORDER BY p.requested_at DESC
");

$activeParcels = [];

if ($stmt) {
    $stmt->bind_param("i", $driverID);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $activeParcels[] = $row;
    }

    $stmt->close();
}

function getStudentName($parcel)
{
    $name = trim(
        ($parcel['StudentFname'] ?? '') . ' ' .
        ($parcel['StudentLname'] ?? '')
    );

    return $name !== '' ? $name : 'Student';
}

function getInitials($name)
{
    $words = preg_split('/\s+/', trim($name));

    if (count($words) >= 2) {
        return strtoupper(
            substr($words[0], 0, 1) .
            substr($words[1], 0, 1)
        );
    }

    return strtoupper(substr($name, 0, 2));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Parcel Requests - CampusCab</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f5f1;
            color: #0c2d4d;
        }

        .page {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0c2d4d;
            text-decoration: none;
            font-weight: 600;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .page-header p {
            margin: 0;
            color: #666;
        }

        .message {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .message.success {
            background: #e8f5ef;
            color: #0f6e56;
        }

        .message.error {
            background: #fbeaea;
            color: #b03a2e;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            margin-bottom: 18px;
            font-size: 22px;
        }

        .parcel-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .parcel-card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            box-shadow:
                0 4px 14px rgba(0, 0, 0, 0.07);
        }

        .student {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .initials {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #0c2d4d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .student-name {
            font-weight: 700;
        }

        .parcel-status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #e8f5ef;
            color: #0f6e56;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .detail {
            margin: 10px 0;
            color: #555;
            line-height: 1.45;
        }

        .detail strong {
            color: #0c2d4d;
        }

        .price {
            margin-top: 15px;
            font-size: 18px;
            font-weight: 700;
            color: #0f6e56;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-button {
            border: none;
            border-radius: 6px;
            padding: 11px 17px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .accept-button {
            background: #0f6e56;
            color: white;
        }

        .start-button {
            background: #185fa5;
            color: white;
        }

        .complete-button {
            background: #0f6e56;
            color: white;
        }

        .cancel-button {
            background: #b03a2e;
            color: white;
        }

        .empty {
            background: white;
            padding: 30px;
            border-radius: 10px;
            color: #666;
            text-align: center;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 450px;
            padding: 30px;
            border-radius: 12px;
        }

        .modal-content h3 {
            margin-top: 0;
        }

        .modal-content textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            resize: vertical;
            margin: 15px 0;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .close-button {
            background: #ddd;
            color: #333;
        }

    </style>

</head>

<body>

<div class="page">

    <a href="driver_dashboard.php" class="back-link">
        ← Back to Driver Dashboard
    </a>

    <div class="page-header">

        <h1>Parcel Requests</h1>

        <p>
            Welcome, <?php echo htmlspecialchars($driverName); ?>.
        </p>

    </div>

    <?php if ($message !== ''): ?>

        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <section class="section">

        <h2>My Active Parcel Deliveries</h2>

        <?php if (empty($activeParcels)): ?>

            <div class="empty">
                You currently have no active parcel deliveries.
            </div>

        <?php else: ?>

            <div class="parcel-grid">

                <?php foreach ($activeParcels as $parcel): ?>

                    <?php
                    $studentName = getStudentName($parcel);
                    $initials = getInitials($studentName);
                    ?>

                    <div class="parcel-card">

                        <div class="student">

                            <div class="initials">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>

                            <div class="student-name">
                                <?php echo htmlspecialchars($studentName); ?>
                            </div>

                        </div>

                        <span class="parcel-status">
                            <?php echo htmlspecialchars($parcel['parcel_status']); ?>
                        </span>

                        <div class="detail">
                            <strong>Parcel:</strong>
                            #<?php echo (int)$parcel['parcel']; ?>
                        </div>

                        <div class="detail">
                            <strong>Description:</strong>
                            <?php echo htmlspecialchars($parcel['parcel_description']); ?>
                        </div>

                        <div class="detail">
                            <strong>Size:</strong>
                            <?php echo htmlspecialchars($parcel['parcel_size']); ?>
                        </div>

                        <div class="detail">
                            <strong>Recipient:</strong>
                            <?php echo htmlspecialchars($parcel['recipient_name']); ?>
                        </div>

                        <div class="detail">
                            <strong>Contact:</strong>
                            <?php echo htmlspecialchars($parcel['recipient_contact']); ?>
                        </div>

                        <div class="detail">
                            <strong>Pickup:</strong>
                            <?php echo htmlspecialchars($parcel['pickup_location']); ?>
                        </div>

                        <div class="detail">
                            <strong>Destination:</strong>
                            <?php echo htmlspecialchars($parcel['destination']); ?>
                        </div>

                        <div class="price">
                            R<?php echo number_format(
                                (float)$parcel['estimated_price'],
                                2
                            ); ?>
                        </div>

                        <div class="actions">

                            <?php if ($parcel['parcel_status'] === 'Accepted'): ?>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="parcelID"
                                        value="<?php echo (int)$parcel['parcel']; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="start"
                                    >

                                    <button
                                        type="submit"
                                        class="action-button start-button"
                                    >
                                        Start Delivery
                                    </button>

                                </form>

                            <?php elseif ($parcel['parcel_status'] === 'In Progress'): ?>

                                <form
                                    method="GET"
                                    action="driver_complete_parcel.php"
                                >

                                    <input
                                        type="hidden"
                                        name="parcelID"
                                        value="<?php echo (int)$parcel['parcel']; ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="action-button complete-button"
                                    >
                                        Complete Delivery
                                    </button>

                                </form>

                            <?php endif; ?>


                            <button
                                type="button"
                                class="action-button cancel-button"
                                onclick="openCancelModal(
                                    <?php echo (int)$parcel['parcel']; ?>
                                )"
                            >
                                Cancel Delivery
                            </button>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>


    <section class="section">

        <h2>New Parcel Requests</h2>

        <?php if (empty($newParcels)): ?>

            <div class="empty">
                There are currently no new parcel requests.
            </div>

        <?php else: ?>

            <div class="parcel-grid">

                <?php foreach ($newParcels as $parcel): ?>

                    <?php
                    $studentName = getStudentName($parcel);
                    $initials = getInitials($studentName);
                    ?>

                    <div class="parcel-card">

                        <div class="student">

                            <div class="initials">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>

                            <div class="student-name">
                                <?php echo htmlspecialchars($studentName); ?>
                            </div>

                        </div>

                        <span class="parcel-status">
                            New Request
                        </span>

                        <div class="detail">
                            <strong>Parcel:</strong>
                            #<?php echo (int)$parcel['parcel']; ?>
                        </div>

                        <div class="detail">
                            <strong>Description:</strong>
                            <?php echo htmlspecialchars($parcel['parcel_description']); ?>
                        </div>

                        <div class="detail">
                            <strong>Size:</strong>
                            <?php echo htmlspecialchars($parcel['parcel_size']); ?>
                        </div>

                        <div class="detail">
                            <strong>Recipient:</strong>
                            <?php echo htmlspecialchars($parcel['recipient_name']); ?>
                        </div>

                        <div class="detail">
                            <strong>Contact:</strong>
                            <?php echo htmlspecialchars($parcel['recipient_contact']); ?>
                        </div>

                        <div class="detail">
                            <strong>Pickup:</strong>
                            <?php echo htmlspecialchars($parcel['pickup_location']); ?>
                        </div>

                        <div class="detail">
                            <strong>Destination:</strong>
                            <?php echo htmlspecialchars($parcel['destination']); ?>
                        </div>

                        <div class="price">
                            R<?php echo number_format(
                                (float)$parcel['estimated_price'],
                                2
                            ); ?>
                        </div>

                        <div class="actions">

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="parcelID"
                                    value="<?php echo (int)$parcel['parcel']; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="accept"
                                >

                                <button
                                    type="submit"
                                    class="action-button accept-button"
                                >
                                    Accept Parcel
                                </button>

                            </form>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</div>


<div class="modal" id="cancelModal">

    <div class="modal-content">

        <h3>Cancel Delivery</h3>

        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="cancel"
            >

            <input
                type="hidden"
                name="parcelID"
                id="cancelParcelID"
            >

            <label for="cancellation_reason">
                Reason for cancellation
            </label>

            <textarea
                name="cancellation_reason"
                id="cancellation_reason"
                required
            ></textarea>

            <div class="modal-actions">

                <button
                    type="button"
                    class="action-button close-button"
                    onclick="closeCancelModal()"
                >
                    Close
                </button>

                <button
                    type="submit"
                    class="action-button cancel-button"
                >
                    Cancel Delivery
                </button>

            </div>

        </form>

    </div>

</div>


<script>

function openCancelModal(parcelID) {

    document.getElementById('cancelParcelID').value =
        parcelID;

    document.getElementById('cancelModal').style.display =
        'flex';
}

function closeCancelModal() {

    document.getElementById('cancelModal').style.display =
        'none';
}

window.addEventListener('click', function(event) {

    const modal =
        document.getElementById('cancelModal');

    if (event.target === modal) {
        closeCancelModal();
    }

});

</script>

</body>
</html>