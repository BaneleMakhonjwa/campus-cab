<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

$studentID = (int)($_SESSION['student_id'] ?? 0);

$message = '';
$error = '';
$requestSubmitted = false;


/* =========================================================
   CHECK LOGIN
   ========================================================= */

if ($studentID <= 0) {
    $error = "You must be logged in to request a parcel service.";
}


/* =========================================================
   LOAD CAMPUS LOCATIONS
   ========================================================= */

$campusLocations = [];

$stmt = $conn->prepare("
    SELECT *
    FROM campus_location
    ORDER BY name ASC
");

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $campusLocations[] = $row;
    }

    $stmt->close();
}


/* =========================================================
   SUBMIT PARCEL REQUEST
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $studentID > 0) {

    $parcelAction      = $_POST['parcel_action'] ?? 'send';
    $parcelDescription = trim($_POST['parcel_description'] ?? '');
    $parcelSize        = trim($_POST['parcel_size'] ?? '');
    $deliveryMethod    = trim($_POST['delivery_method'] ?? '');
    $recipientName     = trim($_POST['recipient_name'] ?? '');
    $recipientContact  = trim($_POST['recipient_contact'] ?? '');
    $pickupName        = trim($_POST['pickup_name'] ?? '');
    $destinationName   = trim($_POST['destination_name'] ?? '');

    /*
     * Price is calculated from the submitted distance.
     * The client-side calculation displays the same price.
     */
    $pickupLat       = (float)($_POST['pickup_lat'] ?? 0);
    $pickupLng       = (float)($_POST['pickup_lng'] ?? 0);
    $destinationLat  = (float)($_POST['destination_lat'] ?? 0);
    $destinationLng  = (float)($_POST['destination_lng'] ?? 0);

    $estimatedDistance = 0;

    if (
        $pickupLat != 0 &&
        $pickupLng != 0 &&
        $destinationLat != 0 &&
        $destinationLng != 0
    ) {

        $earthRadius = 6371;

        $dLat = deg2rad($destinationLat - $pickupLat);
        $dLng = deg2rad($destinationLng - $pickupLng);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($pickupLat)) *
            cos(deg2rad($destinationLat)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        $estimatedDistance = $earthRadius * $c;
    }

    /* R15 base fare + R10 per km */
    $estimatedPrice = 15 + ($estimatedDistance * 10);


    /* =====================================================
       VALIDATION
       ===================================================== */

    if (
        $parcelAction !== 'send' &&
        $parcelAction !== 'receive'
    ) {

        $error = "Invalid parcel request type.";

    } elseif (
        empty($parcelDescription) ||
        empty($parcelSize) ||
        empty($deliveryMethod) ||
        empty($recipientName) ||
        empty($recipientContact) ||
        empty($pickupName) ||
        empty($destinationName)
    ) {

        $error = "Please complete all required fields.";

    } elseif (
        $estimatedDistance <= 0 ||
        $estimatedPrice <= 15
    ) {

        $error = "Please select both a pickup location and destination.";

    } else {


        /* =================================================
           SAVE PARCEL REQUEST

           Table: parcel
           Status: Requested
           DriverID: NULL
           ================================================= */

        $sql = "

            INSERT INTO parcel
            (
                studentID,
                parcel_description,
                parcel_size,
                recipient_name,
                recipient_contact,
                pickup_location,
                destination,
                estimated_price,
                DriverID,
                requested_at,
                parcel_status,
                delivery_method
            )

            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                NULL,
                NOW(),
                'Requested',
                ?
            )

        ";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "issssssds",
                $studentID,
                $parcelDescription,
                $parcelSize,
                $recipientName,
                $recipientContact,
                $pickupName,
                $destinationName,
                $estimatedPrice,
                $deliveryMethod
            );


            if ($stmt->execute()) {

                $requestSubmitted = true;

                $message =
                    "Your parcel request has been submitted successfully.";

            } else {

                $error =
                    "Unable to submit parcel request.";
            }

            $stmt->close();

        } else {

            $error =
                "Database error. Please try again.";
        }
    }
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

<title>CampusCab Send</title>


<style>

/* =========================================================
   CAMPUSCAB COLOURS
   ========================================================= */

:root {

    --navy:#0b1f3a;
    --navy-light:#17365f;

    --gold:#d4a72c;
    --gold-dark:#b8860f;

    --card:#ffffff;
    --border:#e5e7eb;

    --ink:#172033;
    --muted:#6b7280;

    --bg:#f5f7fa;
}


/* =========================================================
   RESET
   ========================================================= */

* {
    box-sizing:border-box;
}


/* =========================================================
   BODY
   ========================================================= */

body {

    margin:0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:var(--bg);

    color:var(--ink);
}


/* =========================================================
   PAGE
   ========================================================= */

.page-container {

    max-width:850px;

    margin:0 auto;

    padding:
        25px
        20px
        45px;
}


/* =========================================================
   BACK BUTTON
   ========================================================= */

.back-button {

    display:inline-flex;

    align-items:center;

    gap:8px;

    padding:10px 16px;

    border:1px solid var(--navy);

    border-radius:8px;

    background:var(--navy);

    color:#fff;

    text-decoration:none;

    font-weight:600;

    font-size:14px;

    margin-bottom:20px;

    transition:
        background .2s ease,
        border-color .2s ease;
}

.back-button:hover {

    background:var(--navy-light);

    border-color:var(--navy-light);
}

.back-arrow {

    font-size:18px;

    line-height:1;
}


/* =========================================================
   TITLE
   ========================================================= */

.page-title {

    margin-bottom:20px;

    border-left:4px solid var(--gold);

    padding-left:14px;
}

.page-title h1 {

    margin:0 0 6px;

    font-size:28px;

    color:var(--navy);

    text-transform:lowercase;
}

.page-title p {

    margin:0;

    color:var(--muted);

    font-size:14px;
}


/* =========================================================
   MAIN CARD
   ========================================================= */

.parcel-card {

    background:var(--card);

    border:
        1px solid var(--border);

    border-radius:12px;

    padding:25px;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.04);
}


/* =========================================================
   SEND / RECEIVE
   ========================================================= */

.parcel-options {

    display:flex;

    gap:10px;

    margin-bottom:23px;
}

.parcel-option {

    flex:1;
}

.parcel-option input {

    display:none;
}

.parcel-option label {

    display:block;

    text-align:center;

    padding:11px 14px;

    border:
        1px solid #d9dee7;

    border-radius:7px;

    background:#fff;

    color:var(--navy);

    font-weight:600;

    cursor:pointer;

    transition:
        background .2s ease,
        border-color .2s ease,
        color .2s ease;
}

.parcel-option label:hover {

    border-color:var(--gold);
}

.parcel-option input:checked + label {

    background:var(--navy);

    border-color:var(--navy);

    color:#fff;
}


/* =========================================================
   FORM GROUP
   ========================================================= */

.form-group {

    margin-bottom:18px;
}

.form-group label {

    display:block;

    margin-bottom:7px;

    font-weight:600;

    color:var(--ink);

    font-size:14px;
}


/* =========================================================
   INPUTS
   ========================================================= */

input,
select {

    width:100%;

    padding:11px 13px;

    border:
        1px solid #d9dee7;

    border-radius:7px;

    font-size:14px;

    background:#fff;

    color:var(--ink);

    outline:none;
}

input:focus,
select:focus {

    border-color:var(--gold);

    box-shadow:
        0 0 0 2px
        rgba(212,167,44,0.12);
}


/* =========================================================
   LOCATION
   ========================================================= */

.location-row {

    display:flex;

    gap:10px;
}

.location-row select {

    flex:1;
}

.location-search {

    margin-top:7px;
}

.location-search input {

    width:100%;
}

.location-results {

    margin-top:5px;

    background:#fff;

    border:
        1px solid var(--border);

    border-radius:7px;

    overflow:hidden;

    display:none;

    box-shadow:
        0 4px 12px rgba(0,0,0,0.06);
}

.location-result {

    padding:10px 12px;

    cursor:pointer;

    border-bottom:
        1px solid var(--border);

    font-size:13px;

    color:var(--ink);
}

.location-result:last-child {

    border-bottom:none;
}

.location-result:hover {

    background:#f3f5f8;
}


/* =========================================================
   GPS BUTTON
   ========================================================= */

.current-location-btn {

    margin-top:7px;

    border:
        1px solid #d9dee7;

    background:#fff;

    color:var(--navy);

    padding:9px 12px;

    border-radius:7px;

    cursor:pointer;

    font-weight:600;

    font-size:13px;
}

.current-location-btn:hover {

    background:#f8f9fb;

    border-color:var(--gold);
}


/* =========================================================
   ESTIMATE
   ========================================================= */

.estimate-box {

    margin-top:22px;

    background:#fafbfc;

    border:
        1px solid var(--border);

    border-top:
        3px solid var(--gold);

    border-radius:9px;

    padding:17px;
}

.estimate-box h3 {

    margin:0 0 12px;

    color:var(--navy);

    font-size:17px;
}

.estimate-row {

    display:flex;

    justify-content:space-between;

    gap:15px;

    padding:7px 0;

    color:var(--muted);

    font-size:14px;
}

.estimate-row strong {

    color:var(--ink);

    text-align:right;
}


/* =========================================================
   SUBMIT BUTTON
   ========================================================= */

.submit-btn {

    width:100%;

    margin-top:22px;

    padding:13px;

    border:none;

    border-radius:7px;

    background:var(--navy);

    color:#fff;

    font-size:15px;

    font-weight:600;

    cursor:pointer;

    transition:.2s ease;
}

.submit-btn:hover {

    background:var(--navy-light);
}


/* =========================================================
   SUCCESS CARD
   ========================================================= */

.success-card {

    text-align:center;

    padding:
        20px
        8px
        8px;
}

.success-icon {

    width:55px;

    height:55px;

    margin:
        0 auto
        16px;

    border-radius:50%;

    background:#fdf8e8;

    color:var(--gold-dark);

    display:flex;

    align-items:center;

    justify-content:center;

    font-size:27px;

    font-weight:bold;
}

.success-card h2 {

    margin:
        0 0 8px;

    color:var(--navy);

    font-size:23px;
}

.success-card p {

    margin:
        0 auto
        18px;

    max-width:500px;

    color:var(--muted);

    line-height:1.5;

    font-size:14px;
}


/* =========================================================
   SUCCESS STATUS
   ========================================================= */

.success-status {

    background:var(--navy);

    color:white;

    border-radius:8px;

    padding:15px;

    margin:18px 0;

    border-left:
        4px solid var(--gold);
}

.success-status strong {

    display:block;

    font-size:15px;

    margin-bottom:4px;
}

.success-status span {

    font-size:13px;

    opacity:.9;
}


/* =========================================================
   SUCCESS ACTIONS
   ========================================================= */

.success-actions {

    display:flex;

    gap:9px;

    justify-content:center;

    flex-wrap:wrap;

    margin-top:18px;
}

.success-link {

    display:inline-block;

    padding:10px 16px;

    border-radius:7px;

    text-decoration:none;

    font-weight:600;

    font-size:13px;
}

.primary-link {

    background:var(--navy);

    color:#fff;

    border:1px solid var(--navy);
}

.secondary-link {

    background:#fff;

    color:var(--navy);

    border:
        1px solid var(--navy);
}

.primary-link:hover {

    background:var(--navy-light);
}

.secondary-link:hover {

    background:#f3f5f8;
}


/* =========================================================
   ERROR
   ========================================================= */

.error-message {

    background:#fef2f2;

    color:#991b1b;

    border:
        1px solid #fecaca;

    padding:12px 14px;

    border-radius:7px;

    margin-bottom:18px;

    font-size:14px;
}


/* =========================================================
   INFO NOTE
   ========================================================= */

.info-note {

    margin-top:16px;

    padding:11px 13px;

    background:#fafbfc;

    border:
        1px solid var(--border);

    border-radius:7px;

    color:var(--muted);

    font-size:12px;

    line-height:1.5;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:700px) {

    .page-container {

        padding:
            18px
            14px
            35px;
    }

    .parcel-card {

        padding:19px;
    }

    .page-title h1 {

        font-size:25px;
    }

    .parcel-options {

        flex-direction:column;
    }

    .location-row {

        flex-direction:column;
    }

    .estimate-row {

        flex-direction:column;

        gap:3px;
    }

    .estimate-row strong {

        text-align:left;
    }

    .success-actions {

        flex-direction:column;
    }

    .success-link {

        width:100%;

        text-align:center;
    }
}

</style>

</head>


<body>


<?php

if (file_exists(__DIR__ . '/header.php')) {

    include __DIR__ . '/header.php';

}

?>


<div class="page-container">


    <!-- =====================================================
         BACK TO DASHBOARD
         ===================================================== -->

    <a
        href="dashboard.php"
        class="back-button"
    >

        <span class="back-arrow">
            ←
        </span>

        Back to Dashboard

    </a>


    <!-- =====================================================
         PAGE TITLE
         ===================================================== -->

    <div class="page-title">

        <h1>
            campuscab send
        </h1>

        <p>
            Send or receive parcels safely around campus.
        </p>

    </div>


    <div class="parcel-card">


        <?php if ($error): ?>

            <div class="error-message">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if ($requestSubmitted): ?>


            <!-- =================================================
                 SUCCESS CARD
                 ================================================= -->

            <div class="success-card">


                <div class="success-icon">
                    ✓
                </div>


                <h2>
                    Parcel Request Submitted
                </h2>


                <p>

                    Your CampusCab parcel request has been
                    submitted successfully and is now waiting
                    for a driver to accept it.

                </p>


                <div class="success-status">

                    <strong>
                        Awaiting Driver Acceptance
                    </strong>

                    <span>

                        We will assign the parcel to an
                        available CampusCab driver.

                    </span>

                </div>


                <div class="success-actions">

                    <a
                        href="my_parcel.php"
                        class="success-link primary-link"
                    >
                        View My Parcel
                    </a>


                    <a
                        href="available_drivers_map.php"
                        class="success-link secondary-link"
                    >
                        View Available Drivers
                    </a>


                    <a
                        href="parcel.php"
                        class="success-link secondary-link"
                    >
                        Request Another Parcel
                    </a>

                </div>

            </div>


        <?php else: ?>


            <form
                method="POST"
                id="parcelForm"
            >


                <!-- =================================================
                     SEND / RECEIVE
                     ================================================= -->

                <div class="parcel-options">


                    <div class="parcel-option">

                        <input
                            type="radio"
                            id="sendParcel"
                            name="parcel_action"
                            value="send"
                            checked
                        >

                        <label for="sendParcel">

                            Send a Parcel

                        </label>

                    </div>


                    <div class="parcel-option">

                        <input
                            type="radio"
                            id="receiveParcel"
                            name="parcel_action"
                            value="receive"
                        >

                        <label for="receiveParcel">

                            Receive a Parcel

                        </label>

                    </div>

                </div>


                <!-- =================================================
                     DESCRIPTION
                     ================================================= -->

                <div class="form-group">

                    <label
                        for="parcel_description"
                        id="descriptionLabel"
                    >

                        What are you sending/receiving?

                    </label>


                    <input
                        type="text"
                        id="parcel_description"
                        name="parcel_description"
                        placeholder="e.g. Clothes, books, food"
                        required
                    >

                </div>


                <!-- =================================================
                     SIZE
                     ================================================= -->

                <div class="form-group">

                    <label for="parcel_size">

                        Parcel size

                    </label>


                    <select
                        id="parcel_size"
                        name="parcel_size"
                        required
                    >

                        <option value="">
                            Select parcel size
                        </option>

                        <option value="Small">
                            Small
                        </option>

                        <option value="Medium">
                            Medium
                        </option>

                        <option value="Large">
                            Large
                        </option>

                    </select>

                </div>


                <!-- =================================================
                     DELIVERY METHOD
                     ================================================= -->

                <div class="form-group">

                    <label for="delivery_method">

                        Delivery method

                    </label>


                    <select
                        id="delivery_method"
                        name="delivery_method"
                        required
                    >

                        <option value="">
                            Select delivery method
                        </option>

                        <option value="Motorcycle">
                            Deliver by motorcycle
                        </option>

                        <option value="Car">
                            Deliver by car
                        </option>

                        <option value="Bakkie">
                            Deliver by bakkie
                        </option>

                    </select>

                </div>


                <!-- =================================================
                     PERSON DETAILS
                     ================================================= -->

                <div class="form-group">

                    <label id="personDetailsLabel">
                        Recipient Details
                    </label>

                </div>


                <div class="form-group">

                    <label
                        for="recipient_name"
                        id="personNameLabel"
                    >

                        Recipient's name

                    </label>


                    <input
                        type="text"
                        id="recipient_name"
                        name="recipient_name"
                        placeholder="Enter recipient's name"
                        required
                    >

                </div>


                <div class="form-group">

                    <label
                        for="recipient_contact"
                        id="personContactLabel"
                    >

                        Recipient's contact

                    </label>


                    <input
                        type="text"
                        id="recipient_contact"
                        name="recipient_contact"
                        placeholder="Enter recipient's contact number"
                        required
                    >

                </div>


                <!-- =================================================
                     PICKUP
                     ================================================= -->

                <div class="form-group">

                    <label>
                        Pickup location
                    </label>


                    <div class="location-row">

                        <select id="pickupSelect">

                            <option value="">
                                Select pickup location
                            </option>


                            <?php foreach ($campusLocations as $location): ?>

                                <option
                                    value="campus-<?= htmlspecialchars($location['location_id']) ?>"
                                    data-lat="<?= htmlspecialchars($location['latitude']) ?>"
                                    data-lng="<?= htmlspecialchars($location['longitude']) ?>"
                                >

                                    <?= htmlspecialchars($location['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="location-search">

                        <input
                            type="text"
                            id="pickupSearch"
                            placeholder="Search for a pickup location..."
                        >


                        <div
                            id="pickupResults"
                            class="location-results"
                        ></div>

                    </div>


                    <button
                        type="button"
                        class="current-location-btn"
                        id="pickupGPS"
                    >

                        ◎ Use my current location

                    </button>

                </div>


                <!-- =================================================
                     DESTINATION
                     ================================================= -->

                <div class="form-group">

                    <label>
                        Destination
                    </label>


                    <div class="location-row">

                        <select id="destinationSelect">

                            <option value="">
                                Select destination
                            </option>


                            <?php foreach ($campusLocations as $location): ?>

                                <option
                                    value="campus-<?= htmlspecialchars($location['location_id']) ?>"
                                    data-lat="<?= htmlspecialchars($location['latitude']) ?>"
                                    data-lng="<?= htmlspecialchars($location['longitude']) ?>"
                                >

                                    <?= htmlspecialchars($location['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="location-search">

                        <input
                            type="text"
                            id="destinationSearch"
                            placeholder="Search for a destination..."
                        >


                        <div
                            id="destinationResults"
                            class="location-results"
                        ></div>

                    </div>


                    <button
                        type="button"
                        class="current-location-btn"
                        id="destinationGPS"
                    >

                        ◎ Use my current location

                    </button>

                </div>


                <!-- =================================================
                     HIDDEN LOCATION INFORMATION
                     ================================================= -->

                <input
                    type="hidden"
                    name="pickup_name"
                    id="pickup_name"
                >

                <input
                    type="hidden"
                    name="pickup_lat"
                    id="pickup_lat"
                >

                <input
                    type="hidden"
                    name="pickup_lng"
                    id="pickup_lng"
                >


                <input
                    type="hidden"
                    name="destination_name"
                    id="destination_name"
                >

                <input
                    type="hidden"
                    name="destination_lat"
                    id="destination_lat"
                >

                <input
                    type="hidden"
                    name="destination_lng"
                    id="destination_lng"
                >


                <input
                    type="hidden"
                    name="estimated_price"
                    id="estimated_price"
                    value="0"
                >


                <!-- =================================================
                     ESTIMATE
                     ================================================= -->

                <div class="estimate-box">

                    <h3>
                        Estimated Trip
                    </h3>


                    <div class="estimate-row">

                        <span>
                            Pickup
                        </span>

                        <strong id="estimatePickup">
                            -
                        </strong>

                    </div>


                    <div class="estimate-row">

                        <span>
                            Destination
                        </span>

                        <strong id="estimateDestination">
                            -
                        </strong>

                    </div>


                    <div class="estimate-row">

                        <span>
                            Distance
                        </span>

                        <strong id="estimateDistance">
                            -
                        </strong>

                    </div>


                    <div class="estimate-row">

                        <span>
                            Estimated time
                        </span>

                        <strong id="estimateTime">
                            -
                        </strong>

                    </div>


                    <div class="estimate-row">

                        <span>
                            Estimated price
                        </span>

                        <strong id="estimatePrice">
                            -
                        </strong>

                    </div>

                </div>


                <div class="info-note">

                    Your parcel request will be sent to available
                    CampusCab drivers. The request will remain
                    <strong>Requested</strong> until a driver accepts it.

                </div>


                <!-- =================================================
                     SUBMIT
                     ================================================= -->

                <button
                    type="submit"
                    class="submit-btn"
                >

                    Send Parcel Request

                </button>


            </form>


        <?php endif; ?>


    </div>

</div>


<script>

/* =========================================================
   CAMPUS LOCATIONS
   ========================================================= */

const campusLocations =
    <?= json_encode(
        $campusLocations,
        JSON_UNESCAPED_SLASHES
    ) ?>;


/* =========================================================
   PRICING
   R15 BASE + R10 PER KM
   ========================================================= */

const BASE_FARE = 15;

const RATE_PER_KM = 10;

const AVG_SPEED_KMH = 25;


/* =========================================================
   ALICE SEARCH AREA
   ========================================================= */

const ALICE_VIEWBOX =
    '26.78,-32.74,26.90,-32.83';


/* =========================================================
   SEND / RECEIVE
   ========================================================= */

const sendParcel =
    document.getElementById('sendParcel');

const receiveParcel =
    document.getElementById('receiveParcel');

const personDetailsLabel =
    document.getElementById(
        'personDetailsLabel'
    );

const personNameLabel =
    document.getElementById(
        'personNameLabel'
    );

const personContactLabel =
    document.getElementById(
        'personContactLabel'
    );

const recipientName =
    document.getElementById(
        'recipient_name'
    );

const recipientContact =
    document.getElementById(
        'recipient_contact'
    );


function updateParcelAction() {

    if (receiveParcel.checked) {

        personDetailsLabel.textContent =
            "Sender Details";

        personNameLabel.textContent =
            "Sender's name";

        personContactLabel.textContent =
            "Sender's contact";

        recipientName.placeholder =
            "Enter sender's name";

        recipientContact.placeholder =
            "Enter sender's contact number";

    } else {

        personDetailsLabel.textContent =
            "Recipient Details";

        personNameLabel.textContent =
            "Recipient's name";

        personContactLabel.textContent =
            "Recipient's contact";

        recipientName.placeholder =
            "Enter recipient's name";

        recipientContact.placeholder =
            "Enter recipient's contact number";
    }
}


if (sendParcel && receiveParcel) {

    sendParcel.addEventListener(
        'change',
        updateParcelAction
    );

    receiveParcel.addEventListener(
        'change',
        updateParcelAction
    );
}


/* =========================================================
   HAVERSINE
   ========================================================= */

function haversine(
    lat1,
    lon1,
    lat2,
    lon2
) {

    const R = 6371;

    const dLat =
        (lat2 - lat1) *
        Math.PI / 180;

    const dLon =
        (lon2 - lon1) *
        Math.PI / 180;

    const a =
        Math.sin(dLat / 2) ** 2 +

        Math.cos(
            lat1 * Math.PI / 180
        ) *

        Math.cos(
            lat2 * Math.PI / 180
        ) *

        Math.sin(dLon / 2) ** 2;

    const c =
        2 *
        Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );

    return R * c;
}


/* =========================================================
   LOCATION VARIABLES
   ========================================================= */

let pickupLocation = null;

let destinationLocation = null;


/* =========================================================
   SELECT LOCATION
   ========================================================= */

function setupSelect(
    selectID,
    hiddenNameID,
    hiddenLatID,
    hiddenLngID,
    type
) {

    const select =
        document.getElementById(selectID);

    if (!select) {
        return;
    }


    select.addEventListener(
        'change',
        function () {

            const option =
                this.options[
                    this.selectedIndex
                ];


            if (!option.value) {
                return;
            }


            const lat =
                parseFloat(
                    option.dataset.lat
                );

            const lng =
                parseFloat(
                    option.dataset.lng
                );

            const name =
                option.textContent.trim();


            const location = {

                name:name,

                lat:lat,

                lng:lng
            };


            if (type === 'pickup') {

                pickupLocation =
                    location;

            } else {

                destinationLocation =
                    location;
            }


            document.getElementById(
                hiddenNameID
            ).value = name;


            document.getElementById(
                hiddenLatID
            ).value = lat;


            document.getElementById(
                hiddenLngID
            ).value = lng;


            updateEstimate();

        }
    );
}


setupSelect(
    'pickupSelect',
    'pickup_name',
    'pickup_lat',
    'pickup_lng',
    'pickup'
);


setupSelect(
    'destinationSelect',
    'destination_name',
    'destination_lat',
    'destination_lng',
    'destination'
);


/* =========================================================
   NOMINATIM SEARCH
   ========================================================= */

function setupSearch(
    inputID,
    resultsID,
    selectID,
    hiddenNameID,
    hiddenLatID,
    hiddenLngID,
    type
) {

    const input =
        document.getElementById(inputID);

    const results =
        document.getElementById(resultsID);

    let timer = null;


    if (!input || !results) {
        return;
    }


    input.addEventListener(
        'input',
        function () {

            clearTimeout(timer);


            const query =
                this.value.trim();


            if (query.length < 3) {

                results.innerHTML = '';

                results.style.display =
                    'none';

                return;
            }


            timer = setTimeout(
                async function () {

                    try {

                        const params =
                            new URLSearchParams({

                                format:'json',

                                q:
                                    query +
                                    ', Alice, Eastern Cape',

                                countrycodes:'za',

                                bounded:'1',

                                viewbox:
                                    ALICE_VIEWBOX,

                                limit:'5'

                            });


                        const url =
                            'https://nominatim.openstreetmap.org/search?' +
                            params.toString();


                        const response =
                            await fetch(
                                url,
                                {
                                    headers:{
                                        'Accept':
                                            'application/json'
                                    }
                                }
                            );


                        if (!response.ok) {

                            throw new Error(
                                'Search failed'
                            );
                        }


                        const data =
                            await response.json();


                        results.innerHTML = '';


                        if (!data.length) {

                            const empty =
                                document.createElement(
                                    'div'
                                );

                            empty.className =
                                'location-result';

                            empty.textContent =
                                'No locations found.';

                            results.appendChild(
                                empty
                            );

                            results.style.display =
                                'block';

                            return;
                        }


                        data.forEach(
                            function(place) {

                                const div =
                                    document.createElement(
                                        'div'
                                    );

                                div.className =
                                    'location-result';

                                div.textContent =
                                    place.display_name;


                                div.addEventListener(
                                    'click',
                                    function() {

                                        const location = {

                                            name:
                                                place.display_name,

                                            lat:
                                                parseFloat(
                                                    place.lat
                                                ),

                                            lng:
                                                parseFloat(
                                                    place.lon
                                                )
                                        };


                                        if (
                                            type ===
                                            'pickup'
                                        ) {

                                            pickupLocation =
                                                location;

                                        } else {

                                            destinationLocation =
                                                location;
                                        }


                                        document.getElementById(
                                            hiddenNameID
                                        ).value =
                                            location.name;


                                        document.getElementById(
                                            hiddenLatID
                                        ).value =
                                            location.lat;


                                        document.getElementById(
                                            hiddenLngID
                                        ).value =
                                            location.lng;


                                        const select =
                                            document.getElementById(
                                                selectID
                                            );


                                        const option =
                                            document.createElement(
                                                'option'
                                            );


                                        option.value =
                                            'poi-' +
                                            place.place_id;


                                        option.textContent =
                                            place.display_name;


                                        option.dataset.lat =
                                            place.lat;


                                        option.dataset.lng =
                                            place.lon;


                                        select.appendChild(
                                            option
                                        );


                                        option.selected =
                                            true;


                                        input.value =
                                            place.display_name;


                                        results.innerHTML =
                                            '';

                                        results.style.display =
                                            'none';


                                        updateEstimate();

                                    }
                                );


                                results.appendChild(
                                    div
                                );

                            }
                        );


                        results.style.display =
                            'block';


                    } catch (error) {

                        console.error(
                            'Location search error:',
                            error
                        );
                    }

                },
                400
            );
        }
    );
}


setupSearch(
    'pickupSearch',
    'pickupResults',
    'pickupSelect',
    'pickup_name',
    'pickup_lat',
    'pickup_lng',
    'pickup'
);


setupSearch(
    'destinationSearch',
    'destinationResults',
    'destinationSelect',
    'destination_name',
    'destination_lat',
    'destination_lng',
    'destination'
);


/* =========================================================
   GPS
   ========================================================= */

function useGPS(type) {

    if (!navigator.geolocation) {

        alert(
            'Your browser does not support GPS location.'
        );

        return;
    }


    navigator.geolocation.getCurrentPosition(

        function(position) {

            const lat =
                position.coords.latitude;

            const lng =
                position.coords.longitude;


            const location = {

                name:
                    'My Current Location',

                lat:lat,

                lng:lng
            };


            if (type === 'pickup') {

                pickupLocation =
                    location;

            } else {

                destinationLocation =
                    location;
            }


            const select =
                document.getElementById(

                    type === 'pickup'
                        ? 'pickupSelect'
                        : 'destinationSelect'

                );


            /* Remove previous GPS option */

            Array.from(
                select.options
            ).forEach(
                function(option) {

                    if (
                        option.value === 'gps'
                    ) {

                        option.remove();
                    }

                }
            );


            const option =
                document.createElement(
                    'option'
                );


            option.value =
                'gps';


            option.textContent =
                'My Current Location';


            option.dataset.lat =
                lat;


            option.dataset.lng =
                lng;


            select.appendChild(
                option
            );


            option.selected =
                true;


            const nameID =
                type === 'pickup'
                    ? 'pickup_name'
                    : 'destination_name';


            const latID =
                type === 'pickup'
                    ? 'pickup_lat'
                    : 'destination_lat';


            const lngID =
                type === 'pickup'
                    ? 'pickup_lng'
                    : 'destination_lng';


            document.getElementById(
                nameID
            ).value =
                location.name;


            document.getElementById(
                latID
            ).value =
                lat;


            document.getElementById(
                lngID
            ).value =
                lng;


            updateEstimate();

        },


        function() {

            alert(
                'Unable to access your current location.'
            );

        }

    );
}


const pickupGPS =
    document.getElementById(
        'pickupGPS'
    );


const destinationGPS =
    document.getElementById(
        'destinationGPS'
    );


if (pickupGPS) {

    pickupGPS.addEventListener(
        'click',
        function() {

            useGPS('pickup');

        }
    );
}


if (destinationGPS) {

    destinationGPS.addEventListener(
        'click',
        function() {

            useGPS('destination');

        }
    );
}


/* =========================================================
   ESTIMATE
   R15 + R10 PER KM
   ========================================================= */

function updateEstimate() {

    const pickupText =
        document.getElementById(
            'estimatePickup'
        );

    const destinationText =
        document.getElementById(
            'estimateDestination'
        );

    const distanceText =
        document.getElementById(
            'estimateDistance'
        );

    const timeText =
        document.getElementById(
            'estimateTime'
        );

    const priceText =
        document.getElementById(
            'estimatePrice'
        );


    if (
        pickupText &&
        pickupLocation
    ) {

        pickupText.textContent =
            pickupLocation.name;
    }


    if (
        destinationText &&
        destinationLocation
    ) {

        destinationText.textContent =
            destinationLocation.name;
    }


    if (
        !pickupLocation ||
        !destinationLocation
    ) {

        return;
    }


    const distance =
        haversine(

            pickupLocation.lat,

            pickupLocation.lng,

            destinationLocation.lat,

            destinationLocation.lng

        );


    const duration =
        (distance / AVG_SPEED_KMH) * 60;


    /* R15 base + R10 per kilometre */

    const price =
        BASE_FARE +
        (distance * RATE_PER_KM);


    distanceText.textContent =
        distance.toFixed(2) +
        ' km';


    timeText.textContent =
        Math.max(
            1,
            Math.round(duration)
        ) +
        ' min';


    priceText.textContent =
        'R' +
        price.toFixed(2);


    document.getElementById(
        'estimated_price'
    ).value =
        price.toFixed(2);
}


/* =========================================================
   CLOSE SEARCH RESULTS
   ========================================================= */

document.addEventListener(
    'click',
    function(event) {

        if (
            !event.target.closest(
                '.location-search'
            )
        ) {

            document
                .querySelectorAll(
                    '.location-results'
                )
                .forEach(
                    function(element) {

                        element.style.display =
                            'none';

                    }
                );
        }
    }
);


/* =========================================================
   INITIALISE
   ========================================================= */

updateParcelAction();

</script>


</body>

</html>