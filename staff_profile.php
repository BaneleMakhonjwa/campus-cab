<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

$staffID = (int)($_SESSION['staff_id'] ?? 0);

if ($staffID <= 0) {
    header("Location: login_staff.php");
    exit;
}

$message = '';
$messageType = '';

$stmt = $conn->prepare("
    SELECT
        staff_name,
        surname,
        staff_email,
        staff_phone
    FROM staff
    WHERE staff_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $staffID);
$stmt->execute();

$result = $stmt->get_result();
$staff = $result->fetch_assoc();

$stmt->close();

if (!$staff) {
    die("Staff profile could not be found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update_personal_details'])) {

        $staffName = trim($_POST['staff_name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $email = trim($_POST['staff_email'] ?? '');
        $phone = trim($_POST['staff_phone'] ?? '');

        if (
            $staffName === '' ||
            $surname === '' ||
            $email === '' ||
            $phone === ''
        ) {

            $message = "Please complete all personal details.";
            $messageType = "error";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $message = "Please enter a valid email address.";
            $messageType = "error";

        } else {

            $stmt = $conn->prepare("
                UPDATE staff
                SET
                    staff_name = ?,
                    surname = ?,
                    staff_email = ?,
                    staff_phone = ?
                WHERE staff_id = ?
            ");

            $stmt->bind_param(
                "ssssi",
                $staffName,
                $surname,
                $email,
                $phone,
                $staffID
            );

            if ($stmt->execute()) {

                $_SESSION['staff_name'] = $staffName . ' ' . $surname;

                $message = "Your personal details have been updated successfully.";
                $messageType = "success";

                $staff['staff_name'] = $staffName;
                $staff['surname'] = $surname;
                $staff['staff_email'] = $email;
                $staff['staff_phone'] = $phone;

            } else {

                $message = "Unable to update your personal details.";
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}

$staffHasPin = false;
$staffPinEnabled = false;

$stmt = $conn->prepare("
    SELECT passenger_pin, pin_verification
    FROM passenger_safety
    WHERE user_type = 'Staff'
      AND userID = ?
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param("i", $staffID);
    $stmt->execute();

    $pinResult = $stmt->get_result();
    $pinData = $pinResult->fetch_assoc();

    if ($pinData) {
        $staffHasPin = !empty($pinData['passenger_pin']);
        $staffPinEnabled = ((int)$pinData['pin_verification'] === 1);
    }

    $stmt->close();
}

$emergencyContact = null;

$stmt = $conn->prepare("
    SELECT *
    FROM emergency_contacts
    WHERE staff_id = ?
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param("i", $staffID);
    $stmt->execute();

    $emergencyResult = $stmt->get_result();
    $emergencyContact = $emergencyResult->fetch_assoc();

    $stmt->close();
}

$pageTitle = "Staff Profile";

include 'header.php';

?>

<style>
    .profile-page {
        max-width: 900px;
        margin: 35px auto;
        padding: 0 20px 50px;
    }

    .profile-header {
        margin-bottom: 25px;
    }

    .profile-header h1 {
        margin: 0 0 8px;
        color: var(--navy);
        font-size: 30px;
    }

    .profile-header p {
        margin: 0;
        color: var(--muted);
        line-height: 1.6;
    }

    .profile-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 22px;
        box-shadow: 0 5px 18px rgba(11, 31, 58, 0.06);
    }

    .profile-card h2 {
        margin: 0 0 8px;
        color: var(--navy);
        font-size: 21px;
    }

    .section-description {
        color: var(--muted);
        line-height: 1.6;
        margin-bottom: 22px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .form-group {
        margin-bottom: 5px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 7px;
        font-weight: 700;
        color: var(--ink);
    }

    .form-group input {
        width: 100%;
        box-sizing: border-box;
        padding: 13px 14px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 15px;
        outline: none;
    }

    .form-group input:focus {
        border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .profile-button {
        display: inline-block;
        border: none;
        background: var(--teal);
        color: #fff;
        padding: 13px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 700;
        cursor: pointer;
    }

    .profile-button:hover {
        opacity: 0.92;
    }

    .status-box {
        background: #f5f7fa;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 18px;
        color: var(--ink);
        line-height: 1.5;
    }

    .status-box.success {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #065f46;
    }

    .message {
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 22px;
        font-weight: 600;
    }

    .message.success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .message.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .contact-details {
        display: grid;
        gap: 12px;
    }

    .contact-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }

    .contact-row:last-child {
        border-bottom: none;
    }

    .contact-label {
        color: var(--muted);
        font-weight: 600;
    }

    .contact-value {
        color: var(--ink);
        font-weight: 600;
        text-align: right;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: var(--navy);
        text-decoration: none;
        font-weight: 700;
    }

    @media (max-width: 650px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .profile-card {
            padding: 22px;
        }

        .contact-row {
            flex-direction: column;
            gap: 4px;
        }

        .contact-value {
            text-align: left;
        }
    }
</style>

<div class="profile-page">

    <a href="staff_dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

    <div class="profile-header">
        <h1>Staff Profile</h1>
        <p>Manage your personal information and CampusCab safety settings.</p>
    </div>

    <?php if ($message !== ''): ?>

        <div class="message <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <div class="profile-card">

        <h2>Personal Details</h2>

        <p class="section-description">
            Keep your CampusCab profile information up to date.
        </p>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">

                    <label for="staff_name">First Name</label>

                    <input
                        type="text"
                        id="staff_name"
                        name="staff_name"
                        value="<?php echo htmlspecialchars($staff['staff_name']); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="surname">Surname</label>

                    <input
                        type="text"
                        id="surname"
                        name="surname"
                        value="<?php echo htmlspecialchars($staff['surname']); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="staff_email">Email Address</label>

                    <input
                        type="email"
                        id="staff_email"
                        name="staff_email"
                        value="<?php echo htmlspecialchars($staff['staff_email']); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="staff_phone">Phone Number</label>

                    <input
                        type="text"
                        id="staff_phone"
                        name="staff_phone"
                        value="<?php echo htmlspecialchars($staff['staff_phone']); ?>"
                        required
                    >

                </div>

            </div>

            <br>

            <button
                type="submit"
                name="update_personal_details"
                class="profile-button"
            >
                Save Changes
            </button>

        </form>

    </div>

    <div class="profile-card">

        <h2>Safety PIN</h2>

        <p class="section-description">
            Your Safety PIN helps the CampusCab driver verify that
            they have the correct passenger before starting your ride.
        </p>

        <?php if ($staffHasPin && $staffPinEnabled): ?>

            <div class="status-box success">
                <strong>Safety PIN enabled.</strong><br>
                Your Safety PIN is currently active.
            </div>

        <?php else: ?>

            <div class="status-box">
                No Safety PIN is currently enabled.
            </div>

        <?php endif; ?>

        <a href="safety_pin.php" class="profile-button">
            <?php
            echo ($staffHasPin && $staffPinEnabled)
                ? "Manage Safety PIN"
                : "Create Safety PIN";
            ?>
        </a>

    </div>

    <div class="profile-card">

        <h2>Emergency Contact</h2>

        <p class="section-description">
            Your emergency contact information can be used when
            assistance is required.
        </p>

        <?php if ($emergencyContact): ?>

            <div class="contact-details">

                <?php if (isset($emergencyContact['contact_name'])): ?>

                    <div class="contact-row">
                        <span class="contact-label">Name</span>
                        <span class="contact-value">
                            <?php echo htmlspecialchars($emergencyContact['contact_name']); ?>
                        </span>
                    </div>

                <?php endif; ?>

                <?php if (isset($emergencyContact['contact_phone'])): ?>

                    <div class="contact-row">
                        <span class="contact-label">Phone</span>
                        <span class="contact-value">
                            <?php echo htmlspecialchars($emergencyContact['contact_phone']); ?>
                        </span>
                    </div>

                <?php endif; ?>

                <?php if (isset($emergencyContact['relationship'])): ?>

                    <div class="contact-row">
                        <span class="contact-label">Relationship</span>
                        <span class="contact-value">
                            <?php echo htmlspecialchars($emergencyContact['relationship']); ?>
                        </span>
                    </div>

                <?php endif; ?>

            </div>

        <?php else: ?>

            <div class="status-box">
                No emergency contact has been added yet.
            </div>

        <?php endif; ?>

    </div>

</div>
