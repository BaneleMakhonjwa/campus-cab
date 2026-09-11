<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';


/* =========================================================
   LOGGED-IN DRIVER
   ========================================================= */

$driverID = (int)($_SESSION['driver_id'] ?? 0);

if ($driverID <= 0) {
    header("Location: driver_login.php");
    exit;
}


/* =========================================================
   DRIVER DETAILS
   ========================================================= */

$stmt = $conn->prepare("
    SELECT
        DriverID,
        DriverFname,
        DriverLname,
        DriverEmail,
        DriverPhoneNo,
        driverAccount_Status,
        licence_number,
        availability_status,
        profile_picture
    FROM driver
    WHERE DriverID = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to load driver profile.");
}

$stmt->bind_param("i", $driverID);
$stmt->execute();

$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    session_destroy();
    header("Location: driver_login.php");
    exit;
}

$driver = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   DRIVER INFORMATION
   ========================================================= */

$firstName = trim($driver['DriverFname'] ?? '');
$lastName = trim($driver['DriverLname'] ?? '');

$fullName = trim(
    $firstName . ' ' . $lastName
);

$email = $driver['DriverEmail'] ?? '';
$phone = $driver['DriverPhoneNo'] ?? '';
$licenceNumber = $driver['licence_number'] ?? '';

$availability =
    $driver['availability_status']
    ?? 'Unavailable';

$profilePicture =
    $driver['profile_picture']
    ?? '';


/* =========================================================
   ACCOUNT STATUS
   ========================================================= */

$accountApproved = true;
$accountStatus = 'Active';


/* =========================================================
   AVAILABILITY
   ========================================================= */

$isAvailable =
    strtolower(trim($availability)) === 'available';


/* =========================================================
   INITIALS
   ========================================================= */

$initials = '';

if ($firstName !== '') {
    $initials .= strtoupper(
        substr($firstName, 0, 1)
    );
}

if ($lastName !== '') {
    $initials .= strtoupper(
        substr($lastName, 0, 1)
    );
}

if ($initials === '') {
    $initials = 'DR';
}


/* =========================================================
   PROFILE PICTURE UPLOAD
   ========================================================= */

$uploadMessage = '';
$uploadError = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['upload_picture'])
) {

    if (
        !isset($_FILES['profile_picture']) ||
        $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK
    ) {

        $uploadError =
            "Please select a valid image.";

    } else {

        $file = $_FILES['profile_picture'];

        if ($file['size'] > 5 * 1024 * 1024) {

            $uploadError =
                "The image must not exceed 5MB.";

        } else {

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            $imageInfo =
                getimagesize($file['tmp_name']);

            if ($imageInfo === false) {

                $uploadError =
                    "The selected file is not a valid image.";

            } elseif (
                !isset(
                    $allowedTypes[$imageInfo['mime']]
                )
            ) {

                $uploadError =
                    "Only JPG, PNG and WEBP images are allowed.";

            } else {

                $extension =
                    $allowedTypes[$imageInfo['mime']];

                $uploadDirectory =
                    __DIR__ . '/uploads/driver_profiles/';

                if (
                    !is_dir($uploadDirectory)
                ) {

                    mkdir(
                        $uploadDirectory,
                        0755,
                        true
                    );
                }

                $fileName =
                    'driver_' .
                    $driverID .
                    '_' .
                    time() .
                    '.' .
                    $extension;

                $destination =
                    $uploadDirectory .
                    $fileName;

                $databasePath =
                    'uploads/driver_profiles/' .
                    $fileName;

                if (
                    move_uploaded_file(
                        $file['tmp_name'],
                        $destination
                    )
                ) {

                    $stmt = $conn->prepare("
                        UPDATE driver
                        SET profile_picture = ?
                        WHERE DriverID = ?
                    ");

                    if ($stmt) {

                        $stmt->bind_param(
                            "si",
                            $databasePath,
                            $driverID
                        );

                        if ($stmt->execute()) {

                            $profilePicture =
                                $databasePath;

                            $uploadMessage =
                                "Profile picture updated successfully.";

                        } else {

                            $uploadError =
                                "Unable to save the profile picture.";

                        }

                        $stmt->close();

                    } else {

                        $uploadError =
                            "Unable to update your profile.";
                    }

                } else {

                    $uploadError =
                        "Unable to upload the image.";
                }
            }
        }
    }
}


/* =========================================================
   CREATE DRIVER MESSAGE TABLE
   ========================================================= */

$conn->query("
    CREATE TABLE IF NOT EXISTS driver_messages (
        messageID INT AUTO_INCREMENT PRIMARY KEY,
        DriverID INT NOT NULL,
        sender_type VARCHAR(30) NOT NULL DEFAULT 'Admin',
        subject VARCHAR(255) NOT NULL,
        message_text TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (DriverID)
    )
");


/* =========================================================
   MARK MESSAGE AS READ
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['mark_message_read'])
) {

    $messageID =
        (int)($_POST['messageID'] ?? 0);

    if ($messageID > 0) {

        $stmt = $conn->prepare("
            UPDATE driver_messages
            SET is_read = 1
            WHERE messageID = ?
              AND DriverID = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $messageID,
                $driverID
            );

            $stmt->execute();
            $stmt->close();
        }
    }
}


/* =========================================================
   DELETE MESSAGE
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_message'])
) {

    $messageID =
        (int)($_POST['messageID'] ?? 0);

    if ($messageID > 0) {

        $stmt = $conn->prepare("
            DELETE FROM driver_messages
            WHERE messageID = ?
              AND DriverID = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $messageID,
                $driverID
            );

            $stmt->execute();
            $stmt->close();
        }
    }
}


/* =========================================================
   GET DRIVER MESSAGES
   ========================================================= */

$messages = [];

$stmt = $conn->prepare("
    SELECT
        messageID,
        sender_type,
        subject,
        message_text,
        is_read,
        created_at
    FROM driver_messages
    WHERE DriverID = ?
    ORDER BY created_at DESC
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $driverID
    );

    $stmt->execute();

    $messageResult =
        $stmt->get_result();

    while ($row = $messageResult->fetch_assoc()) {

        $messages[] = $row;
    }

    $stmt->close();
}


/* =========================================================
   UNREAD MESSAGE COUNT
   ========================================================= */

$unreadMessages = 0;

foreach ($messages as $msg) {

    if ((int)$msg['is_read'] === 0) {
        $unreadMessages++;
    }
}


/* =========================================================
   PAGE TITLE + HEADER
   ========================================================= */

$pageTitle = "Driver Profile";

include 'header.php';

?>


<style>

/* =========================================================
   PROFILE PAGE
   ========================================================= */

.profile-page {
    max-width: 1050px;
    margin: 0 auto;
    padding: 35px 25px 60px;
}


/* =========================================================
   BACK BUTTON
   ========================================================= */

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0c2d4d;
    color: #ffffff;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 22px;
}

.back-button:hover {
    background: #185fa5;
    color: #ffffff;
}

.back-arrow {
    font-size: 17px;
}


/* =========================================================
   PAGE TITLE
   ========================================================= */

.profile-heading {
    margin-bottom: 22px;
}

.profile-heading h1 {
    margin: 0 0 6px;
    color: #0b1f3a;
    font-size: 30px;
}

.profile-heading p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}


/* =========================================================
   DROPDOWN SECTIONS
   ========================================================= */

.profile-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(11, 31, 58, 0.03);
}

.section-header {
    width: 100%;
    border: none;
    background: #ffffff;
    padding: 19px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    text-align: left;
}

.section-header:hover {
    background: #f8fafc;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0b1f3a;
    font-size: 16px;
    font-weight: 700;
}

.section-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #fef3c7;
    color: #b8860b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
}

.arrow {
    color: #6b7280;
    font-size: 18px;
    transition: transform 0.25s ease;
}

.profile-section.open .arrow {
    transform: rotate(180deg);
}

.section-content {
    display: none;
    padding: 0 22px 24px;
    border-top: 1px solid #f0f1f3;
}

.profile-section.open .section-content {
    display: block;
}


/* =========================================================
   PROFILE OVERVIEW
   ========================================================= */

.profile-hero {
    display: flex;
    align-items: center;
    gap: 25px;
    padding-top: 22px;
}

.profile-picture-wrapper {
    position: relative;
    flex-shrink: 0;
}

.profile-picture,
.profile-initials {
    width: 105px;
    height: 105px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-picture {
    display: block;
}

.profile-initials {
    background: #0b1f3a;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    font-weight: 700;
}

.profile-info h2 {
    margin: 0 0 6px;
    color: #0b1f3a;
    font-size: 25px;
}

.profile-info p {
    margin: 0 0 12px;
    color: #6b7280;
    font-size: 14px;
}


/* =========================================================
   STATUS BADGES
   ========================================================= */

.status-row {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.status-active {
    background: #dcfce7;
    color: #15803d;
}

.status-available {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-unavailable {
    background: #f3f4f6;
    color: #4b5563;
}


/* =========================================================
   UPLOAD
   ========================================================= */

.upload-area {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #edf0f3;
}

.upload-area h3 {
    margin: 0 0 7px;
    color: #0b1f3a;
    font-size: 15px;
}

.upload-area p {
    margin: 0 0 15px;
    color: #6b7280;
    font-size: 13px;
}

.upload-form {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.file-input {
    border: 1px solid #dfe3e8;
    padding: 9px;
    border-radius: 7px;
    background: #ffffff;
    font-size: 13px;
}

.upload-button {
    border: none;
    background: #0b1f3a;
    color: #ffffff;
    padding: 10px 17px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}

.upload-button:hover {
    background: #17365f;
}

.message {
    margin-top: 15px;
    padding: 11px 14px;
    border-radius: 7px;
    font-size: 13px;
}

.success-message {
    background: #dcfce7;
    color: #166534;
}

.error-message {
    background: #fee2e2;
    color: #991b1b;
}


/* =========================================================
   DETAILS
   ========================================================= */

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
    padding-top: 20px;
}

.detail-item {
    padding: 15px;
    border: 1px solid #edf0f3;
    border-radius: 9px;
    background: #fafbfc;
}

.detail-label {
    display: block;
    color: #6b7280;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.detail-value {
    color: #172033;
    font-size: 14px;
    font-weight: 600;
    word-break: break-word;
}


/* =========================================================
   ACCOUNT STATUS
   ========================================================= */

.account-status-box {
    margin-top: 20px;
    padding: 18px;
    border-radius: 9px;
    background: #f8fafc;
    border: 1px solid #edf0f3;
}

.account-status-main {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-dot {
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: #16a34a;
}

.account-status-main strong {
    color: #172033;
    font-size: 15px;
}

.account-status-box p {
    margin: 9px 0 0;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}


/* =========================================================
   MENU ITEMS
   ========================================================= */

.dropdown-menu {
    padding-top: 15px;
}

.menu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 16px;
    margin-bottom: 8px;
    border: 1px solid #edf0f3;
    border-radius: 9px;
    background: #fafbfc;
    text-decoration: none;
    transition: 0.2s ease;
}

.menu-item:hover {
    background: #ffffff;
    border-color: #d4a72c;
    transform: translateY(-1px);
}

.menu-item-left {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.menu-item-title {
    color: #172033;
    font-size: 14px;
    font-weight: 600;
}

.menu-item-description {
    color: #6b7280;
    font-size: 12px;
}

.menu-arrow {
    color: #9ca3af;
    font-size: 18px;
}


/* =========================================================
   INBOX
   ========================================================= */

.inbox-container {
    padding-top: 15px;
}

.inbox-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 7px;
    margin-left: 7px;
    border-radius: 20px;
    background: #b03a2e;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
}

.inbox-message {
    border: 1px solid #edf0f3;
    border-radius: 9px;
    background: #fafbfc;
    padding: 16px;
    margin-bottom: 10px;
}

.inbox-message.unread {
    background: #f7fbff;
    border-left: 4px solid #185fa5;
}

.inbox-message-header {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    align-items: flex-start;
}

.inbox-subject {
    color: #172033;
    font-size: 14px;
    font-weight: 700;
}

.inbox-date {
    color: #8a929b;
    font-size: 11px;
    white-space: nowrap;
}

.inbox-sender {
    color: #185fa5;
    font-size: 12px;
    font-weight: 600;
    margin-top: 4px;
}

.inbox-text {
    color: #5f6974;
    font-size: 13px;
    line-height: 1.5;
    margin-top: 10px;
}

.inbox-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.inbox-button {
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.read-button {
    background: #e8f0f8;
    color: #185fa5;
}

.delete-button {
    background: #fee2e2;
    color: #b91c1c;
}

.no-messages {
    padding: 25px 15px;
    text-align: center;
    color: #6b7280;
    font-size: 13px;
}


/* =========================================================
   SAFETY
   ========================================================= */

.safety-note {
    margin-top: 18px;
    padding: 14px 16px;
    border-radius: 9px;
    background: #f8fafc;
    border: 1px solid #edf0f3;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}


/* =========================================================
   SIGN OUT
   ========================================================= */

.signout-item {
    border-color: #fee2e2;
}

.signout-item .menu-item-title {
    color: #b91c1c;
}

.signout-item:hover {
    border-color: #fca5a5;
    background: #fffafa;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {

    .profile-page {
        padding: 25px 15px 45px;
    }

    .profile-heading h1 {
        font-size: 26px;
    }

    .profile-hero {
        flex-direction: column;
        text-align: center;
    }

    .status-row {
        justify-content: center;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .upload-form {
        flex-direction: column;
        align-items: stretch;
    }

    .upload-button {
        width: 100%;
    }

    .inbox-message-header {
        flex-direction: column;
        gap: 5px;
    }
}

</style>


<div class="profile-page">


    <!-- =====================================================
         BACK TO DASHBOARD
         ===================================================== -->

    <a
        href="driver_dashboard.php"
        class="back-button"
    >
        <span class="back-arrow">←</span>
        Back to Driver Dashboard
    </a>


    <!-- =====================================================
         PAGE HEADING
         ===================================================== -->

    <div class="profile-heading">

        <h1>Driver Profile</h1>

        <p>
            Manage your profile, account and safety preferences.
        </p>

    </div>


    <!-- =====================================================
         PROFILE OVERVIEW
         ===================================================== -->

    <section class="profile-section open">

        <button
            type="button"
            class="section-header"
        >

            <span class="section-title">

                <span class="section-icon">
                    P
                </span>

                Profile Overview

            </span>

            <span class="arrow">
                ⌄
            </span>

        </button>


        <div class="section-content">

            <div class="profile-hero">

                <div class="profile-picture-wrapper">

                    <?php if (
                        !empty($profilePicture) &&
                        file_exists(
                            __DIR__ . '/' . $profilePicture
                        )
                    ): ?>

                        <img
                            src="<?php
                                echo htmlspecialchars(
                                    $profilePicture
                                );
                            ?>"
                            alt="Driver profile picture"
                            class="profile-picture"
                        >

                    <?php else: ?>

                        <div class="profile-initials">

                            <?php
                            echo htmlspecialchars(
                                $initials
                            );
                            ?>

                        </div>

                    <?php endif; ?>

                </div>


                <div class="profile-info">

                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $fullName
                        );
                        ?>
                    </h2>

                    <p>
                        CampusCab Driver
                    </p>


                    <div class="status-row">

                        <span class="status-badge status-active">
                            ● Account Active
                        </span>


                        <?php if ($isAvailable): ?>

                            <span class="status-badge status-available">
                                ● Available
                            </span>

                        <?php else: ?>

                            <span class="status-badge status-unavailable">
                                ● Unavailable
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <div class="upload-area">

                <h3>
                    Profile Picture
                </h3>

                <p>
                    Upload a JPG, PNG or WEBP image.
                    Maximum size: 5MB.
                </p>


                <form
                    method="POST"
                    enctype="multipart/form-data"
                    class="upload-form"
                >

                    <input
                        type="file"
                        name="profile_picture"
                        class="file-input"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >


                    <button
                        type="submit"
                        name="upload_picture"
                        class="upload-button"
                    >
                        Upload Picture
                    </button>

                </form>


                <?php if (!empty($uploadMessage)): ?>

                    <div class="message success-message">

                        <?php
                        echo htmlspecialchars(
                            $uploadMessage
                        );
                        ?>

                    </div>

                <?php endif; ?>


                <?php if (!empty($uploadError)): ?>

                    <div class="message error-message">

                        <?php
                        echo htmlspecialchars(
                            $uploadError
                        );
                        ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </section>


    <!-- =====================================================
         PERSONAL DETAILS
         ===================================================== -->

    <section class="profile-section">

        <button
            type="button"
            class="section-header"
        >

            <span class="section-title">

                <span class="section-icon">
                    P
                </span>

                Personal Details

            </span>

            <span class="arrow">
                ⌄
            </span>

        </button>


        <div class="section-content">

            <div class="details-grid">


                <div class="detail-item">

                    <span class="detail-label">
                        Driver ID
                    </span>

                    <span class="detail-value">
                        <?php
                        echo (int)$driverID;
                        ?>
                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Full Name
                    </span>

                    <span class="detail-value">
                        <?php
                        echo htmlspecialchars(
                            $fullName
                        );
                        ?>
                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Email Address
                    </span>

                    <span class="detail-value">
                        <?php
                        echo htmlspecialchars(
                            $email ?: 'Not provided'
                        );
                        ?>
                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Phone Number
                    </span>

                    <span class="detail-value">
                        <?php
                        echo htmlspecialchars(
                            $phone ?: 'Not provided'
                        );
                        ?>
                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Licence Number
                    </span>

                    <span class="detail-value">
                        <?php
                        echo htmlspecialchars(
                            $licenceNumber ?: 'Not provided'
                        );
                        ?>
                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Availability
                    </span>

                    <span class="detail-value">
                        <?php
                        echo htmlspecialchars(
                            $availability
                        );
                        ?>
                    </span>

                </div>


            </div>

        </div>

    </section>


    <!-- =====================================================
         SAFETY
         ===================================================== -->

    <section class="profile-section">

        <button
            type="button"
            class="section-header"
        >

            <span class="section-title">

                <span class="section-icon">
                    S
                </span>

                Safety

            </span>

            <span class="arrow">
                ⌄
            </span>

        </button>


        <div class="section-content">

            <div class="dropdown-menu">


                <a
                    href="driver_emergency_contacts.php"
                    class="menu-item"
                >

                    <span class="menu-item-left">

                        <span class="menu-item-title">
                            Emergency Contacts
                        </span>

                        <span class="menu-item-description">
                            Manage the people to contact in an emergency.
                        </span>

                    </span>

                    <span class="menu-arrow">
                        ›
                    </span>

                </a>


                <a
                    href="driver_emergency_assistance.php"
                    class="menu-item"
                >

                    <span class="menu-item-left">

                        <span class="menu-item-title">
                            Emergency Assistance
                        </span>

                        <span class="menu-item-description">
                            Quickly access emergency support while on a trip.
                        </span>

                    </span>

                    <span class="menu-arrow">
                        ›
                    </span>

                </a>


                <a
                    href="driver_share_trip.php"
                    class="menu-item"
                >

                    <span class="menu-item-left">

                        <span class="menu-item-title">
                            Share Trip Location
                        </span>

                        <span class="menu-item-description">
                            Share your trip location with a trusted contact.
                        </span>

                    </span>

                    <span class="menu-arrow">
                        ›
                    </span>

                </a>


                <div class="safety-note">

                    These safety features are linked to your
                    logged-in driver account and are available
                    while completing CampusCab trips.

                </div>

            </div>

        </div>

    </section>


    <!-- =====================================================
         ACCOUNT STATUS
         ===================================================== -->

    <section class="profile-section">

        <button
            type="button"
            class="section-header"
        >

            <span class="section-title">

                <span class="section-icon">
                    A
                </span>

                Account Status

            </span>

            <span class="arrow">
                ⌄
            </span>

        </button>


        <div class="section-content">

            <div class="account-status-box">

                <div class="account-status-main">

                    <span class="status-dot"></span>

                    <strong>
                        Account Active
                    </strong>

                </div>


                <p>
                    Your CampusCab driver account is registered
                    and you are currently logged in. You can
                    receive ride and parcel requests when your
                    availability is set to Available.
                </p>

            </div>

        </div>

    </section>


    <!-- =====================================================
         INBOX
         ===================================================== -->

    <section class="profile-section">

        <button
            type="button"
            class="section-header"
        >

            <span class="section-title">

                <span class="section-icon">
                    I
                </span>

                Inbox

                <?php if ($unreadMessages > 0): ?>

                    <span class="inbox-count">
                        <?php
                        echo $unreadMessages;
                        ?>
                    </span>

                <?php endif; ?>

            </span>

            <span class="arrow">
                ⌄
            </span>

        </button>


        <div class="section-content">

            <div class="inbox-container">

                <?php if (empty($messages)): ?>

                    <div class="no-messages">

                        No messages from the CampusCab
                        administrator yet.

                    </div>

                <?php else: ?>


                    <?php foreach ($messages as $msg): ?>

                        <div class="inbox-message <?php
                            echo (
                                (int)$msg['is_read'] === 0
                            )
                                ? 'unread'
                                : '';
                        ?>">


                            <div class="inbox-message-header">

                                <div>

                                    <div class="inbox-subject">

                                        <?php
                                        echo htmlspecialchars(
                                            $msg['subject']
                                        );
                                        ?>

                                    </div>

                                    <div class="inbox-sender">

                                        From:
                                        <?php
                                        echo htmlspecialchars(
                                            $msg['sender_type']
                                        );
                                        ?>

                                    </div>

                                </div>


                                <div class="inbox-date">

                                    <?php
                                    echo htmlspecialchars(
                                        date(
                                            'd M Y · H:i',
                                            strtotime(
                                                $msg['created_at']
                                            )
                                        )
                                    );
                                    ?>

                                </div>

                            </div>


                            <div class="inbox-text">

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $msg['message_text']
                                    )
                                );
                                ?>

                            </div>


                            <div class="inbox-actions">


                                <?php if (
                                    (int)$msg['is_read'] === 0
                                ): ?>

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="messageID"
                                            value="<?php
                                                echo (int)$msg['messageID'];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="mark_message_read"
                                            class="inbox-button read-button"
                                        >
                                            Mark as Read
                                        </button>

                                    </form>

                                <?php endif; ?>


                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="messageID"
                                        value="<?php
                                            echo (int)$msg['messageID'];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_message"
                                        class="inbox-button delete-button"
                                        onclick="return confirm('Remove this message?');"
                                    >
                                        Remove
                                    </button>

                                </form>

                            </div>

                        </div>

                    <?php endforeach; ?>


                <?php endif; ?>

            </div>

        </div>

    </section>


    <!-- =====================================================
         SIGN OUT
         ===================================================== -->

    <section class="profile-section">

        <button
            type="button"
            class="section-header"
        >

            <span class="section-title">

                <span class="section-icon">
                    X
                </span>

                Sign Out

            </span>

            <span class="arrow">
                ⌄
            </span>

        </button>


        <div class="section-content">

            <div class="dropdown-menu">

                <a
                    href="logout.php"
                    class="menu-item signout-item"
                >

                    <span class="menu-item-left">

                        <span class="menu-item-title">
                            Sign Out of CampusCab
                        </span>

                        <span class="menu-item-description">
                            End your current driver session.
                        </span>

                    </span>

                    <span class="menu-arrow">
                        ›
                    </span>

                </a>

            </div>

        </div>

    </section>


</div>


<script>

/* =========================================================
   DROPDOWN / ACCORDION MENUS
   ========================================================= */

document
    .querySelectorAll('.section-header')
    .forEach(function(button) {

        button.addEventListener(
            'click',
            function() {

                const section =
                    button.closest('.profile-section');

                section.classList.toggle('open');

            }
        );

    });

</script>


</main>

</body>

</html>