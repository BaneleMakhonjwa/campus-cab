<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

$pageTitle = "Request Parcel";

$studentID = (int)($_SESSION['student_id'] ?? 0);
$staffID = (int)($_SESSION['staff_id'] ?? 0);

$passengerType = '';

if ($studentID > 0) {
    $passengerType = 'student';
} elseif ($staffID > 0) {
    $passengerType = 'staff';
} else {
    die("You must be logged in to request a parcel service.");
}

$campusLocations = [];

$locationQuery = mysqli_query(
    $conn,
    "SELECT id, name, latitude, longitude
     FROM campus_location
     ORDER BY name ASC"
);

if ($locationQuery) {
    while ($row = mysqli_fetch_assoc($locationQuery)) {
        $campusLocations[] = $row;
    }
}

$requestSubmitted = false;
$errorMessage = '';
$successMessage = '';
$submittedParcelID = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $parcelAction = trim($_POST['parcel_action'] ?? '');
    $parcelDescription = trim($_POST['parcel_description'] ?? '');
    $parcelSize = trim($_POST['parcel_size'] ?? '');
    $deliveryMethod = trim($_POST['delivery_method'] ?? '');

    $recipientName = trim($_POST['recipient_name'] ?? '');
    $recipientContact = trim($_POST['recipient_contact'] ?? '');

    $pickupName = trim($_POST['pickup_name'] ?? '');
    $destinationName = trim($_POST['destination_name'] ?? '');

    $pickupLatitude = isset($_POST['pickup_latitude'])
        ? (float)$_POST['pickup_latitude']
        : null;

    $pickupLongitude = isset($_POST['pickup_longitude'])
        ? (float)$_POST['pickup_longitude']
        : null;

    $destinationLatitude = isset($_POST['destination_latitude'])
        ? (float)$_POST['destination_latitude']
        : null;

    $destinationLongitude = isset($_POST['destination_longitude'])
        ? (float)$_POST['destination_longitude']
        : null;

    $estimatedDistance = 0;
    $estimatedPrice = 0;

    if (
        $pickupLatitude !== null &&
        $pickupLongitude !== null &&
        $destinationLatitude !== null &&
        $destinationLongitude !== null
    ) {

        $earthRadius = 6371;

        $lat1 = deg2rad($pickupLatitude);
        $lat2 = deg2rad($destinationLatitude);

        $deltaLat = deg2rad(
            $destinationLatitude - $pickupLatitude
        );

        $deltaLon = deg2rad(
            $destinationLongitude - $pickupLongitude
        );

        $a =
            sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1) *
            cos($lat2) *
            sin($deltaLon / 2) *
            sin($deltaLon / 2);

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        $estimatedDistance = $earthRadius * $c;

        $estimatedPrice = 15 + ($estimatedDistance * 10);

        $estimatedPrice = round($estimatedPrice, 2);
    }

    if (!in_array($parcelAction, ['send', 'receive'], true)) {

        $errorMessage = "Please select whether you are sending or receiving a parcel.";

    } elseif ($parcelDescription === '') {

        $errorMessage = "Please enter a description of the parcel.";

    } elseif (!in_array($parcelSize, ['Small', 'Medium', 'Large'], true)) {

        $errorMessage = "Please select a valid parcel size.";

    } elseif (!in_array($deliveryMethod, ['Motorcycle', 'Car', 'Bakkie'], true)) {

        $errorMessage = "Please select a delivery method.";

    } elseif ($recipientName === '') {

        $errorMessage = "Please enter the recipient or sender name.";

    } elseif ($recipientContact === '') {

        $errorMessage = "Please enter the recipient or sender contact number.";

    } elseif ($pickupName === '') {

        $errorMessage = "Please select a pickup location.";

    } elseif ($destinationName === '') {

        $errorMessage = "Please select a destination.";

    } elseif (
        $pickupLatitude === null ||
        $pickupLongitude === null ||
        $destinationLatitude === null ||
        $destinationLongitude === null
    ) {

        $errorMessage = "Please select valid pickup and destination locations.";

    } elseif ($estimatedDistance <= 0) {

        $errorMessage = "Unable to calculate the delivery distance.";

    } else {

        $sql = "
            INSERT INTO parcel
            (
                studentID,
                staff_id,
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
                NULLIF(?, 0),
                NULLIF(?, 0),
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

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "iissssssds",
                $studentID,
                $staffID,
                $parcelDescription,
                $parcelSize,
                $recipientName,
                $recipientContact,
                $pickupName,
                $destinationName,
                $estimatedPrice,
                $deliveryMethod
            );

            if (mysqli_stmt_execute($stmt)) {

                $submittedParcelID = mysqli_insert_id($conn);

                $notificationStudentID =
                    ($passengerType === 'student')
                    ? $studentID
                    : 0;

                $notificationStaffID =
                    ($passengerType === 'staff')
                    ? $staffID
                    : 0;

                $notificationType = 'parcel_requested';

                $notificationMessage =
                    'Your parcel request has been submitted and is waiting for a driver to accept it.';

                $notificationSQL = "
                    INSERT INTO notifications
                    (
                        studentID,
                        staff_id,
                        driver_id,
                        rideID,
                        parcelID,
                        notification_type,
                        message,
                        is_read,
                        created_at
                    )
                    VALUES
                    (
                        NULLIF(?, 0),
                        NULLIF(?, 0),
                        NULL,
                        NULL,
                        ?,
                        ?,
                        ?,
                        0,
                        NOW()
                    )
                ";

                $notificationStmt = mysqli_prepare(
                    $conn,
                    $notificationSQL
                );

                if ($notificationStmt) {

                    mysqli_stmt_bind_param(
                        $notificationStmt,
                        "iiiss",
                        $notificationStudentID,
                        $notificationStaffID,
                        $submittedParcelID,
                        $notificationType,
                        $notificationMessage
                    );

                    mysqli_stmt_execute(
                        $notificationStmt
                    );

                    mysqli_stmt_close(
                        $notificationStmt
                    );
                }

                $requestSubmitted = true;

                $successMessage =
                    "Your parcel request has been submitted successfully.";

            } else {

                $errorMessage =
                    "Unable to submit the parcel request. Please try again.";
            }

            mysqli_stmt_close($stmt);

        } else {

            $errorMessage =
                "Unable to prepare the parcel request.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
    <?php echo htmlspecialchars($pageTitle); ?>
</title>

<link rel="stylesheet" href="style.css">

<style>

.parcel-page {
    max-width: 1000px;
    margin: 40px auto;
    padding: 0 20px 50px;
}

.parcel-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.parcel-title {
    color: #0b1f3a;
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 8px;
}

.parcel-subtitle {
    color: #666;
    margin-bottom: 28px;
}

.form-section {
    margin-bottom: 28px;
}

.form-section h3 {
    color: #0b1f3a;
    margin-bottom: 16px;
    font-size: 18px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label {
    font-weight: 700;
    color: #0b1f3a;
    margin-bottom: 7px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 13px;
    border: 1px solid #d5d9de;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
    outline: none;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #185fa5;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.action-options {
    display: flex;
    gap: 12px;
}

.action-option {
    flex: 1;
}

.action-option input {
    display: none;
}

.action-option label {
    display: block;
    padding: 14px;
    text-align: center;
    border: 2px solid #d5d9de;
    border-radius: 9px;
    cursor: pointer;
    font-weight: 700;
    color: #0b1f3a;
    transition: 0.2s ease;
}

.action-option input:checked + label {
    border-color: #e0a526;
    background: #fff8e5;
}

.location-search {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.location-search input {
    flex: 1;
}

.search-button,
.gps-button {
    border: none;
    border-radius: 8px;
    padding: 12px 16px;
    background: #185fa5;
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
}

.gps-button {
    background: #0f6e56;
}

.search-results {
    display: none;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-top: 8px;
    overflow: hidden;
    background: #ffffff;
}

.search-result {
    padding: 11px 13px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    font-size: 13px;
}

.search-result:last-child {
    border-bottom: none;
}

.search-result:hover {
    background: #f6f5f1;
}

.estimate-box {
    background: #f6f5f1;
    border-left: 4px solid #e0a526;
    padding: 18px;
    border-radius: 8px;
    margin-top: 20px;
}

.estimate-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    color: #444;
}

.estimate-row:last-child {
    margin-bottom: 0;
    font-weight: 800;
    color: #0b1f3a;
    font-size: 17px;
}

.submit-button {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 9px;
    background: #e0a526;
    color: #0b1f3a;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    margin-top: 10px;
}

.submit-button:hover {
    background: #d4a72c;
}

.error-message {
    background: #fbeaea;
    color: #b03a2e;
    border-left: 4px solid #b03a2e;
    padding: 14px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.success-card {
    text-align: center;
    padding: 30px 10px;
}

.success-icon {
    width: 65px;
    height: 65px;
    border-radius: 50%;
    background: #0f6e56;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    font-size: 30px;
}

.success-card h2 {
    color: #0b1f3a;
    margin-bottom: 10px;
}

.success-card p {
    color: #666;
    margin-bottom: 25px;
}

.success-links {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}

.success-links a {
    display: inline-block;
    padding: 12px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
}

.primary-link {
    background: #185fa5;
    color: #ffffff;
}

.secondary-link {
    background: #e0a526;
    color: #0b1f3a;
}

@media (max-width: 700px) {

    .parcel-page {
        margin-top: 25px;
    }

    .parcel-card {
        padding: 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: auto;
    }

    .action-options {
        flex-direction: column;
    }

    .location-search {
        flex-direction: column;
    }

}

</style>

</head>

<body>

<?php include 'header.php'; ?>

<div class="parcel-page">

    <div class="parcel-card">

        <?php if ($requestSubmitted): ?>

            <div class="success-card">

                <div class="success-icon">
                    ✓
                </div>

                <h2>
                    Parcel Request Submitted
                </h2>

                <p>
                    <?php
                    echo htmlspecialchars($successMessage);
                    ?>
                </p>

                <div class="success-links">

                    <a
                        href="my_parcel.php"
                        class="primary-link"
                    >
                        View My Parcels
                    </a>

                    <a
                        href="available_drivers_map.php"
                        class="secondary-link"
                    >
                        View Available Drivers
                    </a>

                    <a
                        href="parcel.php"
                        class="secondary-link"
                    >
                        Back to Parcel Service
                    </a>

                </div>

            </div>

        <?php else: ?>

            <div class="parcel-title">
                Request Parcel Delivery
            </div>

            <div class="parcel-subtitle">
                Send a parcel safely and conveniently around campus.
            </div>

            <?php if ($errorMessage !== ''): ?>

                <div class="error-message">
                    <?php
                    echo htmlspecialchars($errorMessage);
                    ?>
                </div>

            <?php endif; ?>

            <form
                method="POST"
                action=""
                id="parcelForm"
            >

                <div class="form-section">

                    <h3>
                        Parcel Request
                    </h3>

                    <div class="action-options">

                        <div class="action-option">

                            <input
                                type="radio"
                                name="parcel_action"
                                id="send"
                                value="send"
                                checked
                            >

                            <label for="send">
                                Send Parcel
                            </label>

                        </div>

                        <div class="action-option">

                            <input
                                type="radio"
                                name="parcel_action"
                                id="receive"
                                value="receive"
                            >

                            <label for="receive">
                                Receive Parcel
                            </label>

                        </div>

                    </div>

                </div>

                <div class="form-section">

                    <h3>
                        Parcel Details
                    </h3>

                    <div class="form-grid">

                        <div class="form-group full">

                            <label for="parcel_description">
                                Parcel Description
                            </label>

                            <textarea
                                id="parcel_description"
                                name="parcel_description"
                                placeholder="Describe the parcel..."
                                required
                            ></textarea>

                        </div>

                        <div class="form-group">

                            <label for="parcel_size">
                                Parcel Size
                            </label>

                            <select
                                id="parcel_size"
                                name="parcel_size"
                                required
                            >

                                <option value="">
                                    Select size
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

                        <div class="form-group">

                            <label for="delivery_method">
                                Delivery Method
                            </label>

                            <select
                                id="delivery_method"
                                name="delivery_method"
                                required
                            >

                                <option value="">
                                    Select method
                                </option>

                                <option value="Motorcycle">
                                    Motorcycle
                                </option>

                                <option value="Car">
                                    Car
                                </option>

                                <option value="Bakkie">
                                    Bakkie
                                </option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="form-section">

                    <h3 id="personSectionTitle">
                        Recipient Details
                    </h3>

                    <div class="form-grid">

                        <div class="form-group">

                            <label id="personNameLabel" for="recipient_name">
                                Recipient Name
                            </label>

                            <input
                                type="text"
                                id="recipient_name"
                                name="recipient_name"
                                placeholder="Enter name"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label id="personContactLabel" for="recipient_contact">
                                Recipient Contact
                            </label>

                            <input
                                type="text"
                                id="recipient_contact"
                                name="recipient_contact"
                                placeholder="Enter contact number"
                                required
                            >

                        </div>

                    </div>

                </div>

                <div class="form-section">

                    <h3>
                        Pickup Location
                    </h3>

                    <div class="form-group">

                        <label for="pickup_name">
                            Campus Location
                        </label>

                        <select
                            id="pickup_name"
                            name="pickup_name"
                        >

                            <option value="">
                                Select campus location
                            </option>

                            <?php foreach ($campusLocations as $location): ?>

                                <option
                                    value="<?php echo htmlspecialchars($location['name']); ?>"
                                    data-lat="<?php echo htmlspecialchars($location['latitude']); ?>"
                                    data-lng="<?php echo htmlspecialchars($location['longitude']); ?>"
                                >
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div style="margin-top:15px;">

                        <label>
                            Search for another location
                        </label>

                        <div class="location-search">

                            <input
                                type="text"
                                id="pickupSearch"
                                placeholder="Search Alice location..."
                            >

                            <button
                                type="button"
                                class="search-button"
                                onclick="searchLocation('pickup')"
                            >
                                Search
                            </button>

                            <button
                                type="button"
                                class="gps-button"
                                onclick="getCurrentLocation('pickup')"
                            >
                                Use GPS
                            </button>

                        </div>

                        <div
                            id="pickupResults"
                            class="search-results"
                        ></div>

                    </div>

                </div>

                <div class="form-section">

                    <h3>
                        Destination
                    </h3>

                    <div class="form-group">

                        <label for="destination_name">
                            Campus Location
                        </label>

                        <select
                            id="destination_name"
                            name="destination_name"
                        >

                            <option value="">
                                Select campus location
                            </option>

                            <?php foreach ($campusLocations as $location): ?>

                                <option
                                    value="<?php echo htmlspecialchars($location['name']); ?>"
                                    data-lat="<?php echo htmlspecialchars($location['latitude']); ?>"
                                    data-lng="<?php echo htmlspecialchars($location['longitude']); ?>"
                                >
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div style="margin-top:15px;">

                        <label>
                            Search for another location
                        </label>

                        <div class="location-search">

                            <input
                                type="text"
                                id="destinationSearch"
                                placeholder="Search Alice location..."
                            >

                            <button
                                type="button"
                                class="search-button"
                                onclick="searchLocation('destination')"
                            >
                                Search
                            </button>

                            <button
                                type="button"
                                class="gps-button"
                                onclick="getCurrentLocation('destination')"
                            >
                                Use GPS
                            </button>

                        </div>

                        <div
                            id="destinationResults"
                            class="search-results"
                        ></div>

                    </div>

                </div>

                <input
                    type="hidden"
                    name="pickup_latitude"
                    id="pickup_latitude"
                >

                <input
                    type="hidden"
                    name="pickup_longitude"
                    id="pickup_longitude"
                >

                <input
                    type="hidden"
                    name="destination_latitude"
                    id="destination_latitude"
                >

                <input
                    type="hidden"
                    name="destination_longitude"
                    id="destination_longitude"
                >

                <input
                    type="hidden"
                    name="estimated_price"
                    id="estimated_price"
                >

                <div class="estimate-box">

                    <div class="estimate-row">
                        <span>Distance</span>
                        <span id="distanceDisplay">0.00 km</span>
                    </div>

                    <div class="estimate-row">
                        <span>Estimated Time</span>
                        <span id="timeDisplay">0 min</span>
                    </div>

                    <div class="estimate-row">
                        <span>Estimated Price</span>
                        <span id="priceDisplay">R0.00</span>
                    </div>

                </div>

                <button
                    type="submit"
                    class="submit-button"
                >
                    Request Parcel Delivery
                </button>

            </form>

        <?php endif; ?>

    </div>

</div>

<script>

const campusLocations =
    <?php echo json_encode($campusLocations); ?>;

const BASE_FARE = 15;

const RATE_PER_KM = 10;

const AVG_SPEED_KMH = 25;

const ALICE_VIEWBOX =
    '26.78,-32.74,26.90,-32.83';

let pickupLocation = {
    lat: null,
    lng: null
};

let destinationLocation = {
    lat: null,
    lng: null
};

function haversineDistance(
    lat1,
    lon1,
    lat2,
    lon2
) {

    const earthRadius = 6371;

    const dLat =
        (lat2 - lat1) * Math.PI / 180;

    const dLon =
        (lon2 - lon1) * Math.PI / 180;

    const a =
        Math.sin(dLat / 2) *
        Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) *
        Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);

    const c =
        2 * Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );

    return earthRadius * c;
}

function updateEstimate() {

    if (
        pickupLocation.lat === null ||
        pickupLocation.lng === null ||
        destinationLocation.lat === null ||
        destinationLocation.lng === null
    ) {

        document.getElementById(
            'distanceDisplay'
        ).textContent = '0.00 km';

        document.getElementById(
            'timeDisplay'
        ).textContent = '0 min';

        document.getElementById(
            'priceDisplay'
        ).textContent = 'R0.00';

        document.getElementById(
            'estimated_price'
        ).value = '';

        return;
    }

    const distance =
        haversineDistance(
            pickupLocation.lat,
            pickupLocation.lng,
            destinationLocation.lat,
            destinationLocation.lng
        );

    const price =
        BASE_FARE +
        (distance * RATE_PER_KM);

    const durationHours =
        distance / AVG_SPEED_KMH;

    const durationMinutes =
        Math.ceil(durationHours * 60);

    document.getElementById(
        'distanceDisplay'
    ).textContent =
        distance.toFixed(2) + ' km';

    document.getElementById(
        'timeDisplay'
    ).textContent =
        durationMinutes + ' min';

    document.getElementById(
        'priceDisplay'
    ).textContent =
        'R' + price.toFixed(2);

    document.getElementById(
        'estimated_price'
    ).value =
        price.toFixed(2);
}

function setPickupLocation(
    name,
    lat,
    lng
) {

    pickupLocation.lat = parseFloat(lat);

    pickupLocation.lng = parseFloat(lng);

    document.getElementById(
        'pickup_latitude'
    ).value = pickupLocation.lat;

    document.getElementById(
        'pickup_longitude'
    ).value = pickupLocation.lng;

    document.getElementById(
        'pickup_name'
    ).value = name;

    updateEstimate();
}

function setDestinationLocation(
    name,
    lat,
    lng
) {

    destinationLocation.lat =
        parseFloat(lat);

    destinationLocation.lng =
        parseFloat(lng);

    document.getElementById(
        'destination_latitude'
    ).value =
        destinationLocation.lat;

    document.getElementById(
        'destination_longitude'
    ).value =
        destinationLocation.lng;

    document.getElementById(
        'destination_name'
    ).value = name;

    updateEstimate();
}

document.getElementById(
    'pickup_name'
).addEventListener(
    'change',
    function() {

        const option =
            this.options[this.selectedIndex];

        if (!option.dataset.lat) {
            pickupLocation.lat = null;
            pickupLocation.lng = null;

            document.getElementById(
                'pickup_latitude'
            ).value = '';

            document.getElementById(
                'pickup_longitude'
            ).value = '';

            updateEstimate();

            return;
        }

        setPickupLocation(
            option.value,
            option.dataset.lat,
            option.dataset.lng
        );
    }
);

document.getElementById(
    'destination_name'
).addEventListener(
    'change',
    function() {

        const option =
            this.options[this.selectedIndex];

        if (!option.dataset.lat) {
            destinationLocation.lat = null;
            destinationLocation.lng = null;

            document.getElementById(
                'destination_latitude'
            ).value = '';

            document.getElementById(
                'destination_longitude'
            ).value = '';

            updateEstimate();

            return;
        }

        setDestinationLocation(
            option.value,
            option.dataset.lat,
            option.dataset.lng
        );
    }
);

function searchLocation(type) {

    const input =
        document.getElementById(
            type + 'Search'
        );

    const resultsBox =
        document.getElementById(
            type + 'Results'
        );

    const query =
        input.value.trim();

    if (!query) {
        return;
    }

    resultsBox.style.display = 'block';

    resultsBox.innerHTML =
        '<div class="search-result">Searching...</div>';

    const url =
        'https://nominatim.openstreetmap.org/search?' +
        new URLSearchParams({
            q: query + ', Alice, Eastern Cape, South Africa',
            format: 'json',
            addressdetails: '1',
            limit: '5',
            countrycodes: 'za',
            viewbox: ALICE_VIEWBOX,
            bounded: '1'
        });

    fetch(url, {
        headers: {
            'Accept-Language': 'en'
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {

        resultsBox.innerHTML = '';

        if (!data.length) {

            resultsBox.innerHTML =
                '<div class="search-result">No locations found.</div>';

            return;
        }

        data.forEach(function(place) {

            const result =
                document.createElement('div');

            result.className =
                'search-result';

            result.textContent =
                place.display_name;

            result.addEventListener(
                'click',
                function() {

                    if (type === 'pickup') {

                        setPickupLocation(
                            place.display_name,
                            place.lat,
                            place.lon
                        );

                    } else {

                        setDestinationLocation(
                            place.display_name,
                            place.lat,
                            place.lon
                        );

                    }

                    resultsBox.style.display =
                        'none';

                    input.value =
                        place.display_name;
                }
            );

            resultsBox.appendChild(result);
        });

    })
    .catch(function() {

        resultsBox.innerHTML =
            '<div class="search-result">Unable to search for this location.</div>';

    });
}

function getCurrentLocation(type) {

    if (!navigator.geolocation) {

        alert(
            'GPS is not supported by your browser.'
        );

        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {

            const lat =
                position.coords.latitude;

            const lng =
                position.coords.longitude;

            if (type === 'pickup') {

                setPickupLocation(
                    'Current GPS Location',
                    lat,
                    lng
                );

                document.getElementById(
                    'pickupSearch'
                ).value =
                    'Current GPS Location';

            } else {

                setDestinationLocation(
                    'Current GPS Location',
                    lat,
                    lng
                );

                document.getElementById(
                    'destinationSearch'
                ).value =
                    'Current GPS Location';
            }

        },
        function() {

            alert(
                'Unable to get your current location. Please allow location access.'
            );

        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

document.querySelectorAll(
    'input[name="parcel_action"]'
).forEach(function(radio) {

    radio.addEventListener(
        'change',
        function() {

            const title =
                document.getElementById(
                    'personSectionTitle'
                );

            const nameLabel =
                document.getElementById(
                    'personNameLabel'
                );

            const contactLabel =
                document.getElementById(
                    'personContactLabel'
                );

            const nameInput =
                document.getElementById(
                    'recipient_name'
                );

            const contactInput =
                document.getElementById(
                    'recipient_contact'
                );

            if (this.value === 'receive') {

                title.textContent =
                    'Sender Details';

                nameLabel.textContent =
                    'Sender Name';

                contactLabel.textContent =
                    'Sender Contact';

                nameInput.placeholder =
                    'Enter sender name';

                contactInput.placeholder =
                    'Enter sender contact';

            } else {

                title.textContent =
                    'Recipient Details';

                nameLabel.textContent =
                    'Recipient Name';

                contactLabel.textContent =
                    'Recipient Contact';

                nameInput.placeholder =
                    'Enter recipient name';

                contactInput.placeholder =
                    'Enter recipient contact';
            }

        }
    );

});

</script>

</body>

</html>