<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

$userType = '';
$userID = 0;
$recipientColumn = '';

if (isset($_SESSION['student_id'])) {

    $userType = 'Student';
    $userID = (int)$_SESSION['student_id'];
    $recipientColumn = 'studentID';

} elseif (isset($_SESSION['staff_id'])) {

    $userType = 'Staff';
    $userID = (int)$_SESSION['staff_id'];
    $recipientColumn = 'staff_id';

} elseif (isset($_SESSION['driver_id'])) {

    $userType = 'Driver';
    $userID = (int)$_SESSION['driver_id'];
    $recipientColumn = 'driver_id';

} else {

    die("You must be logged in to view notifications.");

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read') {

        $notificationID =
            (int)($_POST['notification_id'] ?? 0);

        if ($notificationID > 0) {

            $sql = "
                UPDATE notifications
                SET is_read = 1
                WHERE notification_id = ?
                  AND $recipientColumn = ?
            ";

            $stmt = $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param(
                    "ii",
                    $notificationID,
                    $userID
                );

                $stmt->execute();
                $stmt->close();
            }
        }

    } elseif ($action === 'mark_all_read') {

        $sql = "
            UPDATE notifications
            SET is_read = 1
            WHERE $recipientColumn = ?
              AND is_read = 0
        ";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("i", $userID);

            $stmt->execute();

            $stmt->close();
        }
    }

    header("Location: notifications.php");
    exit;
}

$sql = "
    SELECT
        notification_id,
        notification_type,
        message,
        rideID,
        parcelID,
        is_read,
        created_at
    FROM notifications
    WHERE $recipientColumn = ?
    ORDER BY created_at DESC, notification_id DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $userID);

$stmt->execute();

$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();

$pageTitle = "Notifications";

include 'header.php';

?>

<style>

.notifications-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.notifications-title {
    color: #0c2d4d;
    font-size: 28px;
    font-weight: 800;
    margin: 0;
}

.mark-all-button {
    border: none;
    background: #0c2d4d;
    color: #ffffff;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.mark-all-button:hover {
    background: #185fa5;
}

.notification-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.07);
    border-left: 4px solid #d4a72c;
}

.notification-card.read {
    border-left-color: #cccccc;
    opacity: 0.78;
}

.notification-card.unread {
    background: #fffdf5;
}

.notification-type {
    color: #0c2d4d;
    font-size: 14px;
    font-weight: 800;
    margin-bottom: 7px;
}

.notification-message {
    color: #444;
    font-size: 15px;
    line-height: 1.5;
    margin-bottom: 10px;
}

.notification-time {
    color: #888;
    font-size: 12px;
}

.notification-actions {
    margin-top: 12px;
}

.mark-read-button {
    border: none;
    background: #185fa5;
    color: #ffffff;
    padding: 7px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
}

.mark-read-button:hover {
    background: #0c2d4d;
}

.empty-notifications {
    background: #ffffff;
    border-radius: 12px;
    padding: 45px 20px;
    text-align: center;
    color: #777;
    box-shadow: 0 3px 12px rgba(0,0,0,0.07);
}

@media (max-width: 600px) {

    .notifications-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .notifications-title {
        font-size: 24px;
    }

}

</style>

<div class="notifications-container">

    <div class="notifications-header">

        <h1 class="notifications-title">
            Notifications
        </h1>

        <?php if (!empty($notifications)): ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="action"
                    value="mark_all_read"
                >

                <button
                    type="submit"
                    class="mark-all-button"
                >
                    Mark All as Read
                </button>

            </form>

        <?php endif; ?>

    </div>

    <?php if (empty($notifications)): ?>

        <div class="empty-notifications">
            You have no notifications.
        </div>

    <?php else: ?>

        <?php foreach ($notifications as $notification): ?>

            <?php

            $type = $notification['notification_type'];

            $typeLabels = [

                'ride_requested' => 'Ride Requested',
                'ride_accepted' => 'Ride Accepted',
                'driver_arrived' => 'Driver Arrived',
                'ride_started' => 'Ride Started',
                'ride_completed' => 'Ride Completed',

                'parcel_requested' => 'Parcel Requested',
                'parcel_accepted' => 'Parcel Accepted',
                'parcel_picked_up' => 'Parcel Picked Up',
                'parcel_in_transit' => 'Parcel In Transit',
                'parcel_delivered' => 'Parcel Delivered'

            ];

            $displayType =
                $typeLabels[$type] ?? 'Notification';

            $isRead =
                (int)$notification['is_read'] === 1;

            ?>

            <div
                class="notification-card <?php echo $isRead ? 'read' : 'unread'; ?>"
            >

                <div class="notification-type">
                    <?php echo htmlspecialchars($displayType); ?>
                </div>

                <div class="notification-message">
                    <?php echo htmlspecialchars($notification['message']); ?>
                </div>

                <div class="notification-time">
                    <?php echo htmlspecialchars($notification['created_at']); ?>
                </div>

                <?php if (!$isRead): ?>

                    <div class="notification-actions">

                        <form method="POST">

                            <input
                                type="hidden"
                                name="action"
                                value="mark_read"
                            >

                            <input
                                type="hidden"
                                name="notification_id"
                                value="<?php echo (int)$notification['notification_id']; ?>"
                            >

                            <button
                                type="submit"
                                class="mark-read-button"
                            >
                                Mark as Read
                            </button>

                        </form>

                    </div>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

</main>

</body>
</html>