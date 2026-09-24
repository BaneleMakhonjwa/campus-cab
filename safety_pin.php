<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

$userType = '';
$userID = 0;

if (!empty($_SESSION['student_id'])) {
    $userType = 'Student';
    $userID = (int)$_SESSION['student_id'];
} elseif (!empty($_SESSION['staff_id'])) {
    $userType = 'Staff';
    $userID = (int)$_SESSION['staff_id'];
}

if ($userID <= 0 || ($userType !== 'Student' && $userType !== 'Staff')) {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pin = trim($_POST['safety_pin'] ?? '');
    $confirmPin = trim($_POST['confirm_pin'] ?? '');

    if ($pin === '' || $confirmPin === '') {

        $message = "Please enter and confirm your Safety PIN.";
        $messageType = "error";

    } elseif (!preg_match('/^\d{4,6}$/', $pin)) {

        $message = "Your Safety PIN must contain 4 to 6 digits.";
        $messageType = "error";

    } elseif ($pin !== $confirmPin) {

        $message = "The Safety PINs do not match.";
        $messageType = "error";

    } else {

        $pinHash = password_hash($pin, PASSWORD_DEFAULT);

        if ($pinHash === false) {

            $message = "Unable to secure your Safety PIN.";
            $messageType = "error";

        } else {

            $stmt = $conn->prepare("
                SELECT pin_id
                FROM passenger_safety
                WHERE user_type = ?
                  AND userID = ?
                LIMIT 1
            ");

            if (!$stmt) {

                $message = "Database error.";
                $messageType = "error";

            } else {

                $stmt->bind_param("si", $userType, $userID);
                $stmt->execute();

                $result = $stmt->get_result();
                $existingPin = $result->fetch_assoc();

                $stmt->close();

                if ($existingPin) {

                    $stmt = $conn->prepare("
                        UPDATE passenger_safety
                        SET
                            passenger_pin = ?,
                            pin_verification = 1,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE pin_id = ?
                          AND user_type = ?
                          AND userID = ?
                    ");

                    if (!$stmt) {

                        $message = "Unable to update your Safety PIN.";
                        $messageType = "error";

                    } else {

                        $stmt->bind_param(
                            "sisi",
                            $pinHash,
                            $existingPin['pin_id'],
                            $userType,
                            $userID
                        );

                        if ($stmt->execute()) {
                            $message = "Your Safety PIN has been updated successfully.";
                            $messageType = "success";
                        } else {
                            $message = "Your Safety PIN could not be updated.";
                            $messageType = "error";
                        }

                        $stmt->close();
                    }

                } else {

                    $stmt = $conn->prepare("
                        INSERT INTO passenger_safety
                        (
                            user_type,
                            userID,
                            passenger_pin,
                            pin_verification,
                            updated_at
                        )
                        VALUES
                        (?, ?, ?, 1, CURRENT_TIMESTAMP)
                    ");

                    if (!$stmt) {

                        $message = "Unable to create your Safety PIN.";
                        $messageType = "error";

                    } else {

                        $stmt->bind_param(
                            "sis",
                            $userType,
                            $userID,
                            $pinHash
                        );

                        if ($stmt->execute()) {
                            $message = "Your Safety PIN has been created successfully.";
                            $messageType = "success";
                        } else {
                            $message = "Your Safety PIN could not be created.";
                            $messageType = "error";
                        }

                        $stmt->close();
                    }
                }
            }
        }
    }
}

$hasPin = false;
$pinEnabled = false;

$stmt = $conn->prepare("
    SELECT passenger_pin, pin_verification
    FROM passenger_safety
    WHERE user_type = ?
      AND userID = ?
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param("si", $userType, $userID);
    $stmt->execute();

    $result = $stmt->get_result();
    $pinData = $result->fetch_assoc();

    if ($pinData) {
        $hasPin = !empty($pinData['passenger_pin']);
        $pinEnabled = ((int)$pinData['pin_verification'] === 1);
    }

    $stmt->close();
}

$pageTitle = "Safety PIN";

include 'header.php';
?>

<style>
    .pin-page {
        max-width: 650px;
        margin: 40px auto;
        padding: 0 20px 50px;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: var(--navy);
        font-weight: 600;
        margin-bottom: 20px;
    }

    .pin-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 5px 18px rgba(11, 31, 58, 0.07);
    }

    .pin-icon {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: #eef7f6;
        color: var(--teal);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 27px;
        margin-bottom: 18px;
    }

    .pin-title {
        margin: 0 0 8px;
        color: var(--navy);
        font-size: 28px;
    }

    .pin-description {
        color: var(--muted);
        line-height: 1.6;
        margin-bottom: 25px;
    }

    .info-box {
        background: #f5f7fa;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 25px;
        color: var(--ink);
        line-height: 1.6;
        font-size: 14px;
    }

    .info-box strong {
        color: var(--navy);
    }

    .message {
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: 600;
        line-height: 1.5;
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--ink);
        font-weight: 700;
    }

    .pin-input {
        width: 100%;
        box-sizing: border-box;
        padding: 15px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 22px;
        letter-spacing: 7px;
        text-align: center;
        outline: none;
    }

    .pin-input:focus {
        border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
    }

    .input-help {
        color: var(--muted);
        font-size: 13px;
        margin-top: 7px;
    }

    .save-button {
        width: 100%;
        border: none;
        background: var(--teal);
        color: white;
        padding: 15px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 5px;
    }

    .save-button:hover {
        opacity: 0.92;
    }

    .status-box {
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 22px;
        line-height: 1.5;
    }

    .status-enabled {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .security-note {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
        text-align: center;
    }

    @media (max-width: 600px) {
        .pin-card {
            padding: 23px;
        }

        .pin-title {
            font-size: 24px;
        }
    }
</style>

<div class="pin-page">

    <a href="javascript:history.back()" class="back-button">
        <span>←</span>
        Back
    </a>

    <div class="pin-card">

        <div class="pin-icon">
            🔐
        </div>

        <h1 class="pin-title">
            Safety PIN
        </h1>

        <p class="pin-description">
            Create a Safety PIN that you will give to your CampusCab
            driver when you meet them. This helps the driver confirm
            that they have the correct passenger before starting your trip.
        </p>

        <?php if ($message !== ''): ?>

            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <?php if ($hasPin && $pinEnabled): ?>

            <div class="status-box status-enabled">
                <strong>Safety PIN is enabled.</strong><br>
                You can update your PIN below if you want to change it.
            </div>

        <?php endif; ?>

        <div class="info-box">
            <strong>How it works</strong><br><br>

            1. Create your personal Safety PIN.<br>
            2. Request a CampusCab ride.<br>
            3. When the driver arrives, give them your PIN.<br>
            4. The driver enters the PIN to verify you.<br>
            5. Once verified, your trip can begin.
        </div>

        <form method="POST" action="">

            <div class="form-group">

                <label for="safety_pin">
                    <?php echo $hasPin ? "New Safety PIN" : "Create Safety PIN"; ?>
                </label>

                <input
                    type="password"
                    id="safety_pin"
                    name="safety_pin"
                    class="pin-input"
                    inputmode="numeric"
                    pattern="[0-9]{4,6}"
                    minlength="4"
                    maxlength="6"
                    autocomplete="new-password"
                    placeholder="••••"
                    required
                >

                <div class="input-help">
                    Use 4 to 6 numbers.
                </div>

            </div>

            <div class="form-group">

                <label for="confirm_pin">
                    Confirm Safety PIN
                </label>

                <input
                    type="password"
                    id="confirm_pin"
                    name="confirm_pin"
                    class="pin-input"
                    inputmode="numeric"
                    pattern="[0-9]{4,6}"
                    minlength="4"
                    maxlength="6"
                    autocomplete="new-password"
                    placeholder="••••"
                    required
                >

            </div>

            <button type="submit" class="save-button">
                <?php echo $hasPin ? "Update Safety PIN" : "Create Safety PIN"; ?>
            </button>

        </form>

        <div class="security-note">
            🔒 Your Safety PIN is securely hashed and is never displayed
            to drivers or other users.
        </div>

    </div>

</div>
