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
   VERIFY DRIVER EXISTS
   ========================================================= */

$stmt = $conn->prepare("
    SELECT DriverFname, DriverLname
    FROM driver
    WHERE DriverID = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to verify driver account.");
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


$driverName = trim(
    ($driver['DriverFname'] ?? '') . ' ' .
    ($driver['DriverLname'] ?? '')
);


/* =========================================================
   CREATE EMERGENCY CONTACT TABLE
   ========================================================= */

$conn->query("
    CREATE TABLE IF NOT EXISTS driver_emergency_contacts (
        contactID INT AUTO_INCREMENT PRIMARY KEY,
        DriverID INT NOT NULL,
        contact_name VARCHAR(100) NOT NULL,
        relationship VARCHAR(50) NOT NULL,
        phone_number VARCHAR(30) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,
        INDEX (DriverID)
    )
");


/* =========================================================
   MESSAGES
   ========================================================= */

$message = "";
$messageType = "";


/* =========================================================
   ADD CONTACT
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_contact'])
) {

    $contactName =
        trim($_POST['contact_name'] ?? '');

    $relationship =
        trim($_POST['relationship'] ?? '');

    $phoneNumber =
        trim($_POST['phone_number'] ?? '');


    if (
        $contactName === '' ||
        $relationship === '' ||
        $phoneNumber === ''
    ) {

        $message =
            "Please complete all emergency contact fields.";

        $messageType = "error";

    } else {

        /* Check number of existing contacts */

        $countStmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM driver_emergency_contacts
            WHERE DriverID = ?
        ");

        $countStmt->bind_param(
            "i",
            $driverID
        );

        $countStmt->execute();

        $countResult =
            $countStmt->get_result();

        $countData =
            $countResult->fetch_assoc();

        $countStmt->close();

        $totalContacts =
            (int)($countData['total'] ?? 0);


        if ($totalContacts >= 3) {

            $message =
                "You can add a maximum of 3 emergency contacts.";

            $messageType = "error";

        } else {

            /* Insert contact */

            $stmt = $conn->prepare("
                INSERT INTO driver_emergency_contacts
                (
                    DriverID,
                    contact_name,
                    relationship,
                    phone_number
                )
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt) {

                $stmt->bind_param(
                    "isss",
                    $driverID,
                    $contactName,
                    $relationship,
                    $phoneNumber
                );

                if ($stmt->execute()) {

                    $message =
                        "Emergency contact added successfully.";

                    $messageType = "success";

                } else {

                    $message =
                        "Unable to add emergency contact.";

                    $messageType = "error";
                }

                $stmt->close();

            } else {

                $message =
                    "Database error.";

                $messageType = "error";
            }
        }
    }
}


/* =========================================================
   EDIT CONTACT
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['edit_contact'])
) {

    $contactID =
        (int)($_POST['contactID'] ?? 0);

    $contactName =
        trim($_POST['contact_name'] ?? '');

    $relationship =
        trim($_POST['relationship'] ?? '');

    $phoneNumber =
        trim($_POST['phone_number'] ?? '');


    if (
        $contactID <= 0 ||
        $contactName === '' ||
        $relationship === '' ||
        $phoneNumber === ''
    ) {

        $message =
            "Please complete all emergency contact fields.";

        $messageType = "error";

    } else {

        /*
         * DriverID is included in WHERE.
         * Therefore one driver cannot edit another
         * driver's emergency contact.
         */

        $stmt = $conn->prepare("
            UPDATE driver_emergency_contacts
            SET
                contact_name = ?,
                relationship = ?,
                phone_number = ?
            WHERE contactID = ?
              AND DriverID = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sssii",
                $contactName,
                $relationship,
                $phoneNumber,
                $contactID,
                $driverID
            );

            if ($stmt->execute()) {

                if ($stmt->affected_rows >= 0) {

                    $message =
                        "Emergency contact updated successfully.";

                    $messageType = "success";

                } else {

                    $message =
                        "Contact could not be updated.";

                    $messageType = "error";
                }

            } else {

                $message =
                    "Unable to update emergency contact.";

                $messageType = "error";
            }

            $stmt->close();

        } else {

            $message =
                "Database error.";

            $messageType = "error";
        }
    }
}


/* =========================================================
   DELETE CONTACT
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_contact'])
) {

    $contactID =
        (int)($_POST['contactID'] ?? 0);


    if ($contactID <= 0) {

        $message =
            "Invalid emergency contact.";

        $messageType = "error";

    } else {

        /*
         * DriverID is included in WHERE.
         */

        $stmt = $conn->prepare("
            DELETE FROM driver_emergency_contacts
            WHERE contactID = ?
              AND DriverID = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $contactID,
                $driverID
            );

            if ($stmt->execute()) {

                if ($stmt->affected_rows === 1) {

                    $message =
                        "Emergency contact removed.";

                    $messageType = "success";

                } else {

                    $message =
                        "Emergency contact could not be found.";

                    $messageType = "error";
                }

            } else {

                $message =
                    "Unable to remove emergency contact.";

                $messageType = "error";
            }

            $stmt->close();

        } else {

            $message =
                "Database error.";

            $messageType = "error";
        }
    }
}


/* =========================================================
   GET DRIVER'S CONTACTS
   ========================================================= */

$contacts = [];

$stmt = $conn->prepare("
    SELECT
        contactID,
        contact_name,
        relationship,
        phone_number,
        created_at
    FROM driver_emergency_contacts
    WHERE DriverID = ?
    ORDER BY created_at ASC
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $driverID
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $contacts[] = $row;
    }

    $stmt->close();
}


$contactCount =
    count($contacts);


$pageTitle = "Emergency Contacts";

include 'header.php';

?>


<style>

/* =========================================================
   PAGE
   ========================================================= */

.safety-page {
    max-width: 900px;
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
    background: #0b1f3a;
    color: #ffffff;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 25px;
}

.back-button:hover {
    background: #17365f;
}

.back-arrow {
    font-size: 17px;
}


/* =========================================================
   HEADING
   ========================================================= */

.page-heading {
    margin-bottom: 25px;
}

.page-heading h1 {
    margin: 0 0 6px;
    color: #0b1f3a;
    font-size: 29px;
}

.page-heading p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}


/* =========================================================
   MESSAGE
   ========================================================= */

.message {
    padding: 12px 15px;
    border-radius: 7px;
    margin-bottom: 20px;
    font-size: 13px;
}

.message.success {
    background: #dcfce7;
    color: #166534;
}

.message.error {
    background: #fee2e2;
    color: #991b1b;
}


/* =========================================================
   INFORMATION CARD
   ========================================================= */

.info-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 18px;
}

.info-card p {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.6;
}


/* =========================================================
   CONTACT CARD
   ========================================================= */

.contact-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 19px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(11, 31, 58, 0.03);
}

.contact-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.contact-person {
    display: flex;
    align-items: center;
    gap: 13px;
}

.contact-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #0b1f3a;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.contact-name {
    color: #172033;
    font-size: 15px;
    font-weight: 700;
}

.contact-relationship {
    color: #6b7280;
    font-size: 12px;
    margin-top: 3px;
}

.contact-phone {
    margin-top: 15px;
    padding-top: 14px;
    border-top: 1px solid #edf0f3;
    color: #172033;
    font-size: 14px;
    font-weight: 600;
}

.contact-phone span {
    color: #6b7280;
    font-size: 11px;
    text-transform: uppercase;
    margin-right: 7px;
}


/* =========================================================
   ACTIONS
   ========================================================= */

.contact-actions {
    display: flex;
    gap: 7px;
}

.action-button {
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.edit-button {
    background: #e8f0f8;
    color: #185fa5;
}

.delete-button {
    background: #fee2e2;
    color: #b91c1c;
}


/* =========================================================
   ADD CONTACT
   ========================================================= */

.add-contact-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 22px;
    margin-top: 20px;
}

.add-contact-card h2 {
    margin: 0 0 6px;
    color: #0b1f3a;
    font-size: 18px;
}

.add-contact-card > p {
    margin: 0 0 20px;
    color: #6b7280;
    font-size: 13px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label {
    color: #172033;
    font-size: 12px;
    font-weight: 700;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #dfe3e8;
    border-radius: 7px;
    padding: 11px 12px;
    font-family: inherit;
    font-size: 13px;
    outline: none;
}

.form-group input:focus {
    border-color: #185fa5;
}

.save-button {
    border: none;
    background: #0b1f3a;
    color: #ffffff;
    padding: 11px 18px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    margin-top: 18px;
}

.save-button:hover {
    background: #17365f;
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    color: #6b7280;
    font-size: 13px;
}


/* =========================================================
   MODAL
   ========================================================= */

.modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(11, 31, 58, .55);
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal.show {
    display: flex;
}

.modal-box {
    width: 100%;
    max-width: 500px;
    background: #ffffff;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 15px 45px rgba(0,0,0,.2);
}

.modal-box h2 {
    margin: 0 0 18px;
    color: #0b1f3a;
    font-size: 20px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 18px;
}

.cancel-button {
    border: none;
    background: #edf0f2;
    color: #172033;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}

.update-button {
    border: none;
    background: #0b1f3a;
    color: #ffffff;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 650px) {

    .safety-page {
        padding: 25px 15px 45px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: auto;
    }

    .contact-top {
        align-items: flex-start;
        flex-direction: column;
    }

    .contact-actions {
        width: 100%;
    }

    .action-button {
        flex: 1;
    }
}

</style>


<div class="safety-page">


    <!-- =====================================================
         BACK
         ===================================================== -->

    <a
        href="driver_profile.php"
        class="back-button"
    >

        <span class="back-arrow">
            ←
        </span>

        Back to Profile

    </a>


    <!-- =====================================================
         HEADING
         ===================================================== -->

    <div class="page-heading">

        <h1>
            Emergency Contacts
        </h1>

        <p>
            Manage the people who should be contacted
            in an emergency.
        </p>

    </div>


    <?php if ($message !== ''): ?>

        <div class="message <?php
            echo htmlspecialchars($messageType);
        ?>">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         INFORMATION
         ===================================================== -->

    <div class="info-card">

        <p>

            These emergency contacts belong to
            <strong>
                <?php
                echo htmlspecialchars($driverName);
                ?>
            </strong>.
            You can save up to three trusted contacts.

        </p>

    </div>


    <!-- =====================================================
         EXISTING CONTACTS
         ===================================================== -->

    <?php if (empty($contacts)): ?>

        <div class="empty-card">

            You have not added any emergency contacts yet.

        </div>

    <?php else: ?>


        <?php foreach ($contacts as $contact): ?>

            <?php

            $contactName =
                $contact['contact_name'];

            $contactInitials = '';

            $parts = preg_split(
                '/\s+/',
                trim($contactName)
            );

            if (!empty($parts[0])) {

                $contactInitials .= strtoupper(
                    substr($parts[0], 0, 1)
                );
            }

            if (
                count($parts) > 1 &&
                !empty($parts[count($parts) - 1])
            ) {

                $contactInitials .= strtoupper(
                    substr(
                        $parts[count($parts) - 1],
                        0,
                        1
                    )
                );
            }

            ?>


            <div class="contact-card">


                <div class="contact-top">


                    <div class="contact-person">

                        <div class="contact-avatar">

                            <?php
                            echo htmlspecialchars(
                                $contactInitials
                            );
                            ?>

                        </div>


                        <div>

                            <div class="contact-name">

                                <?php
                                echo htmlspecialchars(
                                    $contact['contact_name']
                                );
                                ?>

                            </div>

                            <div class="contact-relationship">

                                <?php
                                echo htmlspecialchars(
                                    $contact['relationship']
                                );
                                ?>

                            </div>

                        </div>

                    </div>


                    <div class="contact-actions">


                        <button
                            type="button"
                            class="action-button edit-button"
                            onclick="openEditModal(
                                <?php
                                echo (int)$contact['contactID'];
                                ?>,
                                <?php
                                echo htmlspecialchars(
                                    json_encode(
                                        $contact['contact_name']
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>,
                                <?php
                                echo htmlspecialchars(
                                    json_encode(
                                        $contact['relationship']
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>,
                                <?php
                                echo htmlspecialchars(
                                    json_encode(
                                        $contact['phone_number']
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"
                        >
                            Edit
                        </button>


                        <form
                            method="POST"
                            onsubmit="
                                return confirm(
                                    'Remove this emergency contact?'
                                );
                            "
                        >

                            <input
                                type="hidden"
                                name="contactID"
                                value="<?php
                                    echo (int)$contact['contactID'];
                                ?>"
                            >

                            <button
                                type="submit"
                                name="delete_contact"
                                class="action-button delete-button"
                            >
                                Remove
                            </button>

                        </form>

                    </div>

                </div>


                <div class="contact-phone">

                    <span>
                        Phone
                    </span>

                    <?php
                    echo htmlspecialchars(
                        $contact['phone_number']
                    );
                    ?>

                </div>

            </div>

        <?php endforeach; ?>


    <?php endif; ?>


    <!-- =====================================================
         ADD CONTACT
         ===================================================== -->

    <?php if ($contactCount < 3): ?>

        <div class="add-contact-card">

            <h2>
                Add Emergency Contact
            </h2>

            <p>
                Add someone you trust to contact in an emergency.
            </p>


            <form method="POST">

                <div class="form-grid">


                    <div class="form-group">

                        <label for="contact_name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="contact_name"
                            name="contact_name"
                            placeholder="e.g. Thando Mbeki"
                            maxlength="100"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="relationship">
                            Relationship
                        </label>

                        <input
                            type="text"
                            id="relationship"
                            name="relationship"
                            placeholder="e.g. Mother"
                            maxlength="50"
                            required
                        >

                    </div>


                    <div class="form-group full">

                        <label for="phone_number">
                            Phone Number
                        </label>

                        <input
                            type="tel"
                            id="phone_number"
                            name="phone_number"
                            placeholder="e.g. 082 123 4567"
                            maxlength="30"
                            required
                        >

                    </div>


                </div>


                <button
                    type="submit"
                    name="add_contact"
                    class="save-button"
                >
                    Add Emergency Contact
                </button>

            </form>

        </div>

    <?php endif; ?>


</div>


<!-- =========================================================
     EDIT MODAL
     ========================================================= -->

<div
    class="modal"
    id="editModal"
>

    <div class="modal-box">

        <h2>
            Edit Emergency Contact
        </h2>


        <form method="POST">

            <input
                type="hidden"
                name="contactID"
                id="editContactID"
            >


            <div class="form-grid">


                <div class="form-group full">

                    <label for="editContactName">
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="contact_name"
                        id="editContactName"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="editRelationship">
                        Relationship
                    </label>

                    <input
                        type="text"
                        name="relationship"
                        id="editRelationship"
                        maxlength="50"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="editPhone">
                        Phone Number
                    </label>

                    <input
                        type="tel"
                        name="phone_number"
                        id="editPhone"
                        maxlength="30"
                        required
                    >

                </div>


            </div>


            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-button"
                    onclick="closeEditModal()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    name="edit_contact"
                    class="update-button"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<script>

/* =========================================================
   EDIT MODAL
   ========================================================= */

function openEditModal(
    contactID,
    contactName,
    relationship,
    phone
) {

    document.getElementById(
        'editContactID'
    ).value = contactID;

    document.getElementById(
        'editContactName'
    ).value = contactName;

    document.getElementById(
        'editRelationship'
    ).value = relationship;

    document.getElementById(
        'editPhone'
    ).value = phone;

    document.getElementById(
        'editModal'
    ).classList.add('show');
}


function closeEditModal() {

    document.getElementById(
        'editModal'
    ).classList.remove('show');

}


window.addEventListener(
    'click',
    function(event) {

        const modal =
            document.getElementById('editModal');

        if (event.target === modal) {

            closeEditModal();

        }

    }
);

</script>


</main>

</body>

</html>