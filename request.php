<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Request a Ride";
include 'header.php';

$message = "";


/* =========================================================
   SOUTH AFRICAN TIME
   ========================================================= */

date_default_timezone_set('Africa/Johannesburg');


/* =========================================================
   GET LOGGED-IN STUDENT OR STAFF
   ========================================================= */

$studentID = (int)($_SESSION['student_id'] ?? 0);
$staffID   = (int)($_SESSION['staff_id'] ?? 0);

$passengerType = "";

if ($studentID > 0) {

    $passengerType = "student";

} elseif ($staffID > 0) {

    $passengerType = "staff";

}


$requestSubmitted = false;
$submittedPrice = 0;
$submittedRideID = 0;


/* =========================================================
   DASHBOARD LINK
   ========================================================= */

if ($passengerType === "student") {

    $dashboardPage = "dashboard.php";

} elseif ($passengerType === "staff") {

    $dashboardPage = "staff_dashboard.php";

} else {

    $dashboardPage = "index.php";

}


/* =========================================================
   FARE CALCULATION
   ========================================================= */

function calculateRideFare($distanceKm)
{

    date_default_timezone_set('Africa/Johannesburg');

    $day = (int)date('N');
    $hour = (int)date('H');

    $ratePerKm = 10;


    /*
       WEEKDAYS
       Before 19:00 = R30 base
       From 19:00    = R50 base

       WEEKENDS
       Before 20:00 = R30 base
       From 20:00    = R60 base
    */


    if ($day >= 6) {

        if ($hour >= 20) {

            $baseFare = 60;

        } else {

            $baseFare = 30;

        }

    } else {

        if ($hour >= 19) {

            $baseFare = 50;

        } else {

            $baseFare = 30;

        }

    }


    $price =
        $baseFare +
        ($distanceKm * $ratePerKm);


    return round($price, 2);

}


/* =========================================================
   CANCEL RIDE REQUEST
   ========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['action']) &&
    $_POST['action'] === 'cancel_request'
) {

    $rideID = (int)($_POST['rideID'] ?? 0);


    if ($passengerType === "") {

        $message = "
            <div class='msg error'>
                You must be logged in to cancel a ride request.
            </div>
        ";


    } elseif ($rideID <= 0) {

        $message = "
            <div class='msg error'>
                Invalid ride request.
            </div>
        ";


    } else {

        $stmt = null;


        /* =================================================
           STUDENT CANCELLATION
           ================================================= */

        if ($passengerType === "student") {

            $stmt = mysqli_prepare(
                $conn,
                "
                UPDATE ride
                SET
                    ride_status = 'Cancelled',
                    cancellation_reason = 'Cancelled by passenger',
                    cancelled_at = NOW()
                WHERE rideID = ?
                  AND studentID = ?
                  AND passenger_type = 'student'
                  AND ride_status = 'Requested'
                  AND DriverID IS NULL
                "
            );


            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "ii",
                    $rideID,
                    $studentID
                );

            }


        /* =================================================
           STAFF CANCELLATION
           ================================================= */

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "
                UPDATE ride
                SET
                    ride_status = 'Cancelled',
                    cancellation_reason = 'Cancelled by passenger',
                    cancelled_at = NOW()
                WHERE rideID = ?
                  AND staffID = ?
                  AND passenger_type = 'staff'
                  AND ride_status = 'Requested'
                  AND DriverID IS NULL
                "
            );


            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "ii",
                    $rideID,
                    $staffID
                );

            }

        }


        /* =================================================
           EXECUTE CANCELLATION
           ================================================= */

        if ($stmt) {

            if (mysqli_stmt_execute($stmt)) {

                if (
                    mysqli_stmt_affected_rows($stmt) === 1
                ) {

                    $message = "
                        <div class='msg success'>
                            Your ride request has been cancelled successfully.
                        </div>
                    ";

                } else {

                    $message = "
                        <div class='msg error'>
                            This ride request can no longer be cancelled.
                            A driver may have already accepted it.
                        </div>
                    ";

                }

            } else {

                $message = "
                    <div class='msg error'>
                        Unable to cancel the ride request:
                        " .
                        htmlspecialchars(
                            mysqli_stmt_error($stmt)
                        ) .
                        "
                    </div>
                ";

            }


            mysqli_stmt_close($stmt);


        } else {

            $message = "
                <div class='msg error'>
                    Unable to cancel the ride request:
                    " .
                    htmlspecialchars(
                        mysqli_error($conn)
                    ) .
                    "
                </div>
            ";

        }

    }

}


/* =========================================================
   SUBMIT RIDE REQUEST
   ========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    !isset($_POST['action'])
) {

    $pickupLat = (
        isset($_POST['pickup_lat']) &&
        $_POST['pickup_lat'] !== ''
    )
        ? floatval($_POST['pickup_lat'])
        : null;


    $pickupLng = (
        isset($_POST['pickup_lng']) &&
        $_POST['pickup_lng'] !== ''
    )
        ? floatval($_POST['pickup_lng'])
        : null;


    $pickupName = trim(
        $_POST['pickup_name'] ?? ''
    );


    $destLat = (
        isset($_POST['destination_lat']) &&
        $_POST['destination_lat'] !== ''
    )
        ? floatval($_POST['destination_lat'])
        : null;


    $destLng = (
        isset($_POST['destination_lng']) &&
        $_POST['destination_lng'] !== ''
    )
        ? floatval($_POST['destination_lng'])
        : null;


    $destName = trim(
        $_POST['destination_name'] ?? ''
    );


    $pickupValid = (
        $pickupLat !== null &&
        $pickupLng !== null &&
        $pickupName !== ''
    );


    $destValid = (
        $destLat !== null &&
        $destLng !== null &&
        $destName !== ''
    );


    $samePoint =
        $pickupValid &&
        $destValid &&
        abs($pickupLat - $destLat) < 0.0001 &&
        abs($pickupLng - $destLng) < 0.0001;


    /* =====================================================
       SERVER-SIDE DISTANCE
       ===================================================== */

    if (
        $pickupValid &&
        $destValid
    ) {

        $earthRadius = 6371;


        $dLat =
            deg2rad(
                $destLat - $pickupLat
            );


        $dLng =
            deg2rad(
                $destLng - $pickupLng
            );


        $a =
            sin($dLat / 2) *
            sin($dLat / 2) +

            cos(
                deg2rad($pickupLat)
            ) *

            cos(
                deg2rad($destLat)
            ) *

            sin($dLng / 2) *
            sin($dLng / 2);


        $c =
            2 *
            atan2(
                sqrt($a),
                sqrt(1 - $a)
            );


        $estDistance =
            $earthRadius * $c;

    } else {

        $estDistance = 0;

    }


    /* =====================================================
       SERVER-SIDE DURATION
       ===================================================== */

    if ($estDistance > 0) {

        $estDuration =
            max(
                2,
                round(
                    ($estDistance / 25) * 60
                )
            );

    } else {

        $estDuration = 0;

    }


    /* =====================================================
       SERVER-SIDE FARE
       ===================================================== */

    $estPrice =
        calculateRideFare(
            $estDistance
        );


    /* =====================================================
       VALIDATION
       ===================================================== */

    if ($passengerType === "") {

        $message = "
            <div class='msg error'>
                You must be logged in to request a ride.
            </div>
        ";


    } elseif (
        !$pickupValid ||
        !$destValid ||
        $samePoint
    ) {

        $message = "
            <div class='msg error'>
                Please choose a valid pickup and a different destination.
            </div>
        ";


    } elseif ($estDistance <= 0) {

        $message = "
            <div class='msg error'>
                Unable to calculate the ride distance. Please select your
                pickup and destination again.
            </div>
        ";


    } else {

        $stmt = null;


        /* =================================================
           STUDENT RIDE
           ================================================= */

        if ($passengerType === "student") {

            $stmt = mysqli_prepare(
                $conn,
                "
                INSERT INTO ride
                (
                    studentID,
                    staffID,
                    passenger_type,
                    request_type,
                    parcel_details,
                    DriverID,
                    pickup_address,
                    pickup_latitude,
                    pickup_longitude,
                    destination_address,
                    destination_latitude,
                    destination_longitude,
                    estimated_distance_km,
                    estimated_duration_min,
                    estimated_price,
                    requested_at,
                    ride_status
                )
                VALUES
                (
                    ?,
                    NULL,
                    'student',
                    'ride',
                    NULL,
                    NULL,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW(),
                    'Requested'
                )
                "
            );


            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "isddssdddid",
                    $studentID,
                    $pickupName,
                    $pickupLat,
                    $pickupLng,
                    $destName,
                    $destLat,
                    $destLng,
                    $estDistance,
                    $estDuration,
                    $estPrice
                );

            }


        /* =================================================
           STAFF RIDE
           ================================================= */

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "
                INSERT INTO ride
                (
                    studentID,
                    staffID,
                    passenger_type,
                    request_type,
                    parcel_details,
                    DriverID,
                    pickup_address,
                    pickup_latitude,
                    pickup_longitude,
                    destination_address,
                    destination_latitude,
                    destination_longitude,
                    estimated_distance_km,
                    estimated_duration_min,
                    estimated_price,
                    requested_at,
                    ride_status
                )
                VALUES
                (
                    NULL,
                    ?,
                    'staff',
                    'ride',
                    NULL,
                    NULL,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW(),
                    'Requested'
                )
                "
            );


            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "isddssdddid",
                    $staffID,
                    $pickupName,
                    $pickupLat,
                    $pickupLng,
                    $destName,
                    $destLat,
                    $destLng,
                    $estDistance,
                    $estDuration,
                    $estPrice
                );

            }

        }


        /* =================================================
           EXECUTE INSERT
           ================================================= */

        if ($stmt) {

            if (mysqli_stmt_execute($stmt)) {

                $submittedRideID =
                    mysqli_insert_id($conn);


                $requestSubmitted =
                    true;


                $submittedPrice =
                    $estPrice;


                $message = "
                    <div class='request-status-card'>

                        <div class='status-icon'>
                            ✓
                        </div>

                        <div class='status-content'>

                            <h2>
                                Ride Request Submitted
                            </h2>

                            <p>
                                Your ride request has been sent successfully.
                            </p>

                            <div class='awaiting-status'>

                                <span class='status-dot'></span>

                                <strong>
                                    Awaiting Driver Acceptance
                                </strong>

                            </div>

                            <p class='status-description'>
                                A nearby available driver will see your
                                request and can accept it. You can check
                                your ride status from My Rides.
                            </p>

                            <div class='status-actions'>

                                <a
                                    href='my_rides.php'
                                    class='status-primary-btn'
                                >
                                    View My Ride
                                </a>

                                <a
                                    href='available_drivers_map.php'
                                    class='status-secondary-btn'
                                >
                                    View Available Drivers
                                </a>

                            </div>

                        </div>

                    </div>
                ";

            } else {

                $message = "
                    <div class='msg error'>
                        Error:
                        " .
                        htmlspecialchars(
                            mysqli_stmt_error($stmt)
                        ) .
                        "
                    </div>
                ";

            }


            mysqli_stmt_close($stmt);


        } else {

            $message = "
                <div class='msg error'>
                    Database error:
                    " .
                    htmlspecialchars(
                        mysqli_error($conn)
                    ) .
                    "
                </div>
            ";

        }

    }

}


/* =========================================================
   LOAD CAMPUS LOCATIONS
   ========================================================= */

$locations = mysqli_query(
    $conn,
    "SELECT * FROM campus_location ORDER BY name ASC"
);

$locationList = [];


if ($locations === false) {

    $message .= "
        <div class='msg error'>
            Database error (loading locations):
            " .
            htmlspecialchars(
                mysqli_error($conn)
            ) .
            "
        </div>
    ";


} else {

    while (
        $loc = mysqli_fetch_assoc($locations)
    ) {

        $locationList[] = $loc;

    }

}

?>


<!-- =========================================================
     PAGE
     ========================================================= -->

<div class="page-wrapper">


    <!-- =====================================================
         BACK TO DASHBOARD
         ===================================================== -->

    <a
         href="dashboard.php"
        class="back-button"
    >
        <span class="back-arrow">←</span>
        Back to Dashboard
    </a>


    <!-- =====================================================
         REQUEST CARD
         ===================================================== -->

    <div class="card narrow">

        <h1>
            CampusCab Rides
        </h1>


        <?php echo $message; ?>


        <?php if (!$requestSubmitted): ?>


            <form
                method="POST"
                id="requestForm"
            >


                <!-- PICKUP -->

                <label>
                    Pickup location
                </label>


                <div class="pickup-toggle">

                    <button
                        type="button"
                        id="useGpsBtn"
                        class="btn-outline"
                    >
                        Use my current location
                    </button>


                    <span
                        id="gpsStatus"
                        class="hint"
                    ></span>

                </div>


                <select
                    name="pickup_location"
                    id="pickup"
                    required
                >

                    <option value="">
                        Select pickup...
                    </option>


                    <?php foreach ($locationList as $loc): ?>

                        <option
                            value="<?php echo htmlspecialchars($loc['location_id']); ?>"
                            data-lat="<?php echo htmlspecialchars($loc['latitude']); ?>"
                            data-lng="<?php echo htmlspecialchars($loc['longitude']); ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $loc['name']
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <!-- PICKUP SEARCH -->

                <div class="location-search">

                    <input
                        type="text"
                        id="pickupSearchInput"
                        placeholder="Search for a place in Alice (e.g. Shoprite, Fort Hare)"
                    >


                    <button
                        type="button"
                        id="pickupSearchBtn"
                        class="btn-outline"
                    >
                        Search
                    </button>

                </div>


                <div
                    id="pickupSearchResults"
                    class="search-results"
                ></div>


                <input
                    type="hidden"
                    name="pickup_lat"
                    id="pickupLat"
                >


                <input
                    type="hidden"
                    name="pickup_lng"
                    id="pickupLng"
                >


                <input
                    type="hidden"
                    name="pickup_name"
                    id="pickupName"
                >


                <!-- DESTINATION -->

                <label>
                    Destination
                </label>


                <select
                    name="destination_location"
                    id="destination"
                    required
                >

                    <option value="">
                        Select destination...
                    </option>


                    <?php foreach ($locationList as $loc): ?>

                        <option
                            value="<?php echo htmlspecialchars($loc['location_id']); ?>"
                            data-lat="<?php echo htmlspecialchars($loc['latitude']); ?>"
                            data-lng="<?php echo htmlspecialchars($loc['longitude']); ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $loc['name']
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <!-- DESTINATION SEARCH -->

                <div class="location-search">

                    <input
                        type="text"
                        id="destinationSearchInput"
                        placeholder="Search for a place in Alice (e.g. Victoria Hospital)"
                    >


                    <button
                        type="button"
                        id="destinationSearchBtn"
                        class="btn-outline"
                    >
                        Search
                    </button>

                </div>


                <div
                    id="destinationSearchResults"
                    class="search-results"
                ></div>


                <input
                    type="hidden"
                    name="destination_lat"
                    id="destinationLat"
                >


                <input
                    type="hidden"
                    name="destination_lng"
                    id="destinationLng"
                >


                <input
                    type="hidden"
                    name="destination_name"
                    id="destinationName"
                >


                <!-- ESTIMATE -->

                <div
                    id="estimateBox"
                    class="estimate-box"
                    style="display:none;"
                >

                    <div>

                        <span class="label">
                            Pickup
                        </span>

                        <span id="pickupText">
                            —
                        </span>

                    </div>


                    <div>

                        <span class="label">
                            Destination
                        </span>

                        <span id="destinationText">
                            —
                        </span>

                    </div>


                    <div>

                        <span class="label">
                            Distance
                        </span>

                        <span id="estDistanceText">
                            —
                        </span>

                    </div>


                    <div>

                        <span class="label">
                            Est. time
                        </span>

                        <span id="estDurationText">
                            —
                        </span>

                    </div>


                    <div>

                        <span class="label">
                            Est. price
                        </span>

                        <span id="estPriceText">
                            —
                        </span>

                    </div>

                </div>


                <input
                    type="hidden"
                    name="estimated_distance_km"
                    id="estDistanceInput"
                >


                <input
                    type="hidden"
                    name="estimated_duration_min"
                    id="estDurationInput"
                >


                <input
                    type="hidden"
                    name="estimated_price"
                    id="estPriceInput"
                >


                <!-- SEND REQUEST -->

                <button
                    type="submit"
                    class="btn-primary full"
                    id="submitBtn"
                    disabled
                >
                    Send request
                </button>


            </form>


        <?php else: ?>


            <!-- REQUEST SUCCESS -->

            <div class="new-request-area">

                <p>

                    Your estimated fare is

                    <strong>
                        R<?php echo number_format($submittedPrice, 2); ?>
                    </strong>.

                </p>


                <!-- CANCEL REQUEST -->

                <form
                    method="POST"
                    onsubmit="return confirm('Are you sure you want to cancel this ride request?');"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="cancel_request"
                    >


                    <input
                        type="hidden"
                        name="rideID"
                        value="<?php echo (int)$submittedRideID; ?>"
                    >


                    <button
                        type="submit"
                        class="cancel-request-btn"
                    >
                        Cancel Request
                    </button>

                </form>


                <!-- BACK TO DASHBOARD -->

                <a
                    href="<?php echo htmlspecialchars($dashboardPage); ?>"
                    class="status-secondary-btn back-request-btn"
                >
                    ← Back to Dashboard
                </a>

            </div>


        <?php endif; ?>

    </div>

</div>


<script>

/* =========================================================
   PRICING MODEL
   ========================================================= */

const RATE_PER_KM = 10;

const AVG_SPEED_KMH = 25;


/* =========================================================
   GET CURRENT FARE
   ========================================================= */

function getFareDetails() {

    const now = new Date();

    const day = now.getDay();

    const hour = now.getHours();

    const isWeekend =
        day === 0 ||
        day === 6;

    let baseFare;


    if (isWeekend) {

        if (hour >= 20) {

            baseFare = 60;

        } else {

            baseFare = 30;

        }

    } else {

        if (hour >= 19) {

            baseFare = 50;

        } else {

            baseFare = 30;

        }

    }


    return {
        baseFare: baseFare,
        ratePerKm: RATE_PER_KM,
        isWeekend: isWeekend
    };

}


/* =========================================================
   HAVERSINE DISTANCE
   ========================================================= */

function haversineKm(
    lat1,
    lng1,
    lat2,
    lng2
) {

    const R = 6371;


    const dLat =
        (lat2 - lat1) *
        Math.PI / 180;


    const dLng =
        (lng2 - lng1) *
        Math.PI / 180;


    const a =
        Math.sin(dLat / 2) ** 2 +

        Math.cos(
            lat1 * Math.PI / 180
        ) *

        Math.cos(
            lat2 * Math.PI / 180
        ) *

        Math.sin(dLng / 2) ** 2;


    return R *
        2 *
        Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );

}


/* =========================================================
   UPDATE RIDE ESTIMATE
   ========================================================= */

function updateEstimate() {

    const pickupEl =
        document.getElementById('pickup');


    const destEl =
        document.getElementById('destination');


    if (!pickupEl || !destEl) {

        return;

    }


    const pickupOpt =
        pickupEl.options[
            pickupEl.selectedIndex
        ];


    const destOpt =
        destEl.options[
            destEl.selectedIndex
        ];


    const samePoint =
        pickupEl.value !== "" &&
        pickupEl.value === destEl.value;


    if (
        !pickupOpt ||
        !pickupOpt.dataset.lat ||
        !destOpt ||
        !destOpt.dataset.lat ||
        samePoint
    ) {

        document.getElementById(
            'estimateBox'
        ).style.display = 'none';


        document.getElementById(
            'submitBtn'
        ).disabled = true;


        return;

    }


    const distance =
        haversineKm(

            parseFloat(
                pickupOpt.dataset.lat
            ),

            parseFloat(
                pickupOpt.dataset.lng
            ),

            parseFloat(
                destOpt.dataset.lat
            ),

            parseFloat(
                destOpt.dataset.lng
            )

        );


    const durationMin =
        Math.max(
            2,
            Math.round(
                (distance / AVG_SPEED_KMH) * 60
            )
        );


    const fareDetails =
        getFareDetails();


    const price =
        fareDetails.baseFare +
        (
            distance *
            fareDetails.ratePerKm
        );


    /* DISPLAY */

    document.getElementById(
        'pickupText'
    ).textContent =
        pickupOpt.textContent.trim();


    document.getElementById(
        'destinationText'
    ).textContent =
        destOpt.textContent.trim();


    document.getElementById(
        'estDistanceText'
    ).textContent =
        distance.toFixed(2) +
        ' km';


    document.getElementById(
        'estDurationText'
    ).textContent =
        durationMin +
        ' min';


    document.getElementById(
        'estPriceText'
    ).textContent =
        'R' +
        price.toFixed(2);


    /* HIDDEN VALUES */

    document.getElementById(
        'estDistanceInput'
    ).value =
        distance.toFixed(2);


    document.getElementById(
        'estDurationInput'
    ).value =
        durationMin;


    document.getElementById(
        'estPriceInput'
    ).value =
        price.toFixed(2);


    document.getElementById(
        'estimateBox'
    ).style.display =
        'grid';


    document.getElementById(
        'submitBtn'
    ).disabled =
        false;


    /* PICKUP */

    document.getElementById(
        'pickupLat'
    ).value =
        pickupOpt.dataset.lat;


    document.getElementById(
        'pickupLng'
    ).value =
        pickupOpt.dataset.lng;


    document.getElementById(
        'pickupName'
    ).value =
        pickupOpt.textContent
            .replace(/^📍\s*/, '')
            .trim();


    /* DESTINATION */

    document.getElementById(
        'destinationLat'
    ).value =
        destOpt.dataset.lat;


    document.getElementById(
        'destinationLng'
    ).value =
        destOpt.dataset.lng;


    document.getElementById(
        'destinationName'
    ).value =
        destOpt.textContent
            .replace(/^📍\s*/, '')
            .trim();

}


/* =========================================================
   DROPDOWN EVENTS
   ========================================================= */

const pickupElement =
    document.getElementById('pickup');


const destinationElement =
    document.getElementById('destination');


if (pickupElement) {

    pickupElement.addEventListener(
        'change',
        updateEstimate
    );

}


if (destinationElement) {

    destinationElement.addEventListener(
        'change',
        updateEstimate
    );

}


/* =========================================================
   GPS
   ========================================================= */

const useGpsBtn =
    document.getElementById('useGpsBtn');


if (useGpsBtn) {

    useGpsBtn.addEventListener(
        'click',
        function () {

            const statusEl =
                document.getElementById(
                    'gpsStatus'
                );


            const pickupEl =
                document.getElementById(
                    'pickup'
                );


            if (!navigator.geolocation) {

                statusEl.textContent =
                    'Geolocation is not supported by your browser.';

                return;

            }


            statusEl.textContent =
                'Getting your location...';


            navigator.geolocation.getCurrentPosition(

                function (position) {

                    const lat =
                        position.coords.latitude;


                    const lng =
                        position.coords.longitude;


                    let gpsOption =
                        pickupEl.querySelector(
                            'option[value="gps"]'
                        );


                    if (!gpsOption) {

                        gpsOption =
                            document.createElement(
                                'option'
                            );


                        gpsOption.value =
                            'gps';


                        pickupEl.insertBefore(
                            gpsOption,
                            pickupEl.options[1] || null
                        );

                    }


                    gpsOption.textContent =
                        'My Current Location';


                    gpsOption.dataset.lat =
                        lat;


                    gpsOption.dataset.lng =
                        lng;


                    pickupEl.value =
                        'gps';


                    statusEl.textContent =
                        'Added to the list and selected';


                    updateEstimate();

                },


                function (error) {

                    statusEl.textContent =
                        'Could not get your location: ' +
                        error.message;

                },


                {
                    enableHighAccuracy: true,
                    timeout: 10000
                }

            );

        }
    );

}


/* =========================================================
   GPS DEFAULT
   ========================================================= */

if (useGpsBtn) {

    useGpsBtn.click();

}


/* =========================================================
   OPENSTREETMAP NOMINATIM
   ========================================================= */

const ALICE_VIEWBOX =
    '26.78,-32.74,26.90,-32.83';


function setupLocationSearch(
    inputId,
    btnId,
    resultsId,
    selectId
) {

    const input =
        document.getElementById(inputId);


    const btn =
        document.getElementById(btnId);


    const resultsBox =
        document.getElementById(resultsId);


    const select =
        document.getElementById(selectId);


    if (
        !input ||
        !btn ||
        !resultsBox ||
        !select
    ) {

        return;

    }


    function runSearch() {

        const query =
            input.value.trim();


        if (query === '') {

            return;

        }


        resultsBox.innerHTML =
            '<div class="hint">Searching...</div>';


        const url =
            'https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=za' +

            '&viewbox=' +
            encodeURIComponent(
                ALICE_VIEWBOX
            ) +

            '&bounded=1' +

            '&q=' +
            encodeURIComponent(
                query +
                ', Alice, Eastern Cape'
            );


        fetch(
            url,
            {
                headers: {
                    'Accept':
                        'application/json'
                }
            }
        )

        .then(
            function (r) {

                return r.json();

            }
        )

        .then(
            function (results) {

                if (!results.length) {

                    resultsBox.innerHTML =
                        '<div class="hint">' +
                        'No matches found near Alice.' +
                        '</div>';

                    return;

                }


                resultsBox.innerHTML = '';


                results.forEach(
                    function (place) {

                        const item =
                            document.createElement(
                                'button'
                            );


                        item.type =
                            'button';


                        item.className =
                            'search-result-item';


                        item.textContent =
                            place.display_name;


                        item.addEventListener(
                            'click',
                            function () {

                                const value =
                                    'poi-' +
                                    place.place_id;


                                let opt =
                                    select.querySelector(
                                        'option[value="' +
                                        value +
                                        '"]'
                                    );


                                if (!opt) {

                                    opt =
                                        document.createElement(
                                            'option'
                                        );


                                    opt.value =
                                        value;


                                    select.insertBefore(
                                        opt,
                                        select.options[1] ||
                                        null
                                    );

                                }


                                opt.textContent =
                                    '📍 ' +
                                    place.display_name;


                                opt.dataset.lat =
                                    place.lat;


                                opt.dataset.lng =
                                    place.lon;


                                select.value =
                                    value;


                                resultsBox.innerHTML =
                                    '';


                                input.value =
                                    '';


                                updateEstimate();

                            }
                        );


                        resultsBox.appendChild(
                            item
                        );

                    }
                );

            }
        )

        .catch(
            function (err) {

                resultsBox.innerHTML =
                    '<div class="hint">' +
                    'Search failed: ' +
                    err.message +
                    '</div>';

            }
        );

    }


    btn.addEventListener(
        'click',
        runSearch
    );


    input.addEventListener(
        'keydown',
        function (e) {

            if (e.key === 'Enter') {

                e.preventDefault();

                runSearch();

            }

        }
    );

}


/* =========================================================
   PICKUP SEARCH
   ========================================================= */

setupLocationSearch(
    'pickupSearchInput',
    'pickupSearchBtn',
    'pickupSearchResults',
    'pickup'
);


/* =========================================================
   DESTINATION SEARCH
   ========================================================= */

setupLocationSearch(
    'destinationSearchInput',
    'destinationSearchBtn',
    'destinationSearchResults',
    'destination'
);

</script>


<style>

/* =========================================================
   PAGE WRAPPER
   ========================================================= */

.page-wrapper {

    max-width: 1100px;

    margin: 0 auto;

    padding: 25px 20px 40px;

}


/* =========================================================
   BACK BUTTON
   ========================================================= */

.back-button {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    margin-bottom: 18px;

    padding: 9px 15px;

    background: #ffffff;

    color: #0b1f3a;

    border: 1px solid #d1d5db;

    border-radius: 8px;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

    transition: all 0.2s ease;

}


.back-button:hover {

    background: #0b1f3a;

    border-color: #0b1f3a;

    color: #ffffff;

}


.back-arrow {

    font-size: 18px;

    line-height: 1;

}


/* =========================================================
   LOCATION SEARCH
   ========================================================= */

.location-search {

    display: flex;

    gap: 8px;

    margin: 8px 0;

}


.location-search input[type="text"] {

    flex: 1;

}


/* =========================================================
   SEARCH RESULTS
   ========================================================= */

.search-results {

    display: flex;

    flex-direction: column;

    gap: 4px;

    margin-bottom: 10px;

}


.search-result-item {

    text-align: left;

    padding: 8px 10px;

    border: 1px solid #d1d5db;

    border-radius: 6px;

    background: #ffffff;

    color: #0b1f3a;

    cursor: pointer;

    font-size: 13px;

}


.search-result-item:hover {

    background: #f5f7fa;

    border-color: #d4a72c;

}


/* =========================================================
   REQUEST SUCCESS STATUS
   ========================================================= */

.request-status-card {

    display: flex;

    gap: 18px;

    align-items: flex-start;

    margin: 25px 0;

    padding: 20px;

    border: 1px solid #dbe5ee;

    border-left: 5px solid #0b1f3a;

    border-radius: 12px;

    background: #f8fafc;

}


.status-icon {

    width: 44px;

    height: 44px;

    min-width: 44px;

    border-radius: 50%;

    background: #0b1f3a;

    color: #ffffff;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;

    font-weight: bold;

}


.status-content {

    flex: 1;

}


.status-content h2 {

    margin: 0 0 6px;

    color: #0b1f3a;

    font-size: 20px;

}


.status-content > p {

    margin: 0 0 14px;

    color: #6b7280;

    font-size: 14px;

    line-height: 1.5;

}


/* =========================================================
   AWAITING ACCEPTANCE
   ========================================================= */

.awaiting-status {

    display: flex;

    align-items: center;

    gap: 9px;

    margin: 14px 0;

    padding: 11px 13px;

    background: #fff8e1;

    border: 1px solid #ead89a;

    border-radius: 8px;

    color: #6b5200;

    font-size: 14px;

}


.status-dot {

    width: 9px;

    height: 9px;

    border-radius: 50%;

    background: #d4a72c;

    display: inline-block;

}


/* =========================================================
   STATUS DESCRIPTION
   ========================================================= */

.status-description {

    color: #6b7280;

    font-size: 13px;

    line-height: 1.5;

}


/* =========================================================
   STATUS BUTTONS
   ========================================================= */

.status-actions {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 17px;

}


/*
   CHANGED:
   These buttons were white.
   They are now NAVY.
*/

.status-primary-btn {

    display: inline-block;

    padding: 10px 15px;

    border-radius: 8px;

    background: #0b1f3a;

    color: #ffffff;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

}


.status-primary-btn:hover {

    background: #17365f;

}


/* View Available Drivers */

.status-secondary-btn {

    display: inline-block;

    padding: 10px 15px;

    border-radius: 8px;

    background: #0b1f3a;

    color: #ffffff;

    border: 1px solid #0b1f3a;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

}


.status-secondary-btn:hover {

    background: #17365f;

    border-color: #17365f;

    color: #ffffff;

}


/* =========================================================
   NEW REQUEST / CANCEL
   ========================================================= */

.new-request-area {

    margin-top: 10px;

}


.new-request-area p {

    color: #6b7280;

    font-size: 14px;

    margin-bottom: 15px;

}


.new-request-area strong {

    color: #0b1f3a;

}


/* =========================================================
   CANCEL REQUEST BUTTON
   ========================================================= */

.cancel-request-btn {

    width: 100%;

    box-sizing: border-box;

    margin-top: 5px;

    padding: 12px 16px;

    background: #0b1f3a;

    color: #ffffff;

    border: 1px solid #0b1f3a;

    border-radius: 8px;

    cursor: pointer;

    font-size: 14px;

    font-weight: 700;

    transition: all 0.2s ease;

}


.cancel-request-btn:hover {

    background: #17365f;

    border-color: #17365f;

}


/* =========================================================
   BACK TO DASHBOARD AFTER REQUEST
   ========================================================= */

.back-request-btn {

    display: block;

    width: 100%;

    box-sizing: border-box;

    text-align: center;

    margin-top: 10px;

}


/* =========================================================
   MESSAGES
   ========================================================= */

.msg {

    padding: 12px 14px;

    margin: 15px 0;

    border-radius: 8px;

    font-size: 13px;

    line-height: 1.5;

}


.msg.success {

    background: #f0fdf4;

    color: #15803d;

    border: 1px solid #bbf7d0;

}


.msg.error {

    background: #fef2f2;

    color: #b91c1c;

    border: 1px solid #fecaca;

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 600px) {

    .page-wrapper {

        padding: 20px 15px 35px;

    }


    .request-status-card {

        padding: 16px;

        gap: 12px;

    }


    .status-icon {

        width: 38px;

        height: 38px;

        min-width: 38px;

        font-size: 18px;

    }


    .status-content h2 {

        font-size: 18px;

    }


    .status-actions {

        flex-direction: column;

    }


    .status-primary-btn,
    .status-secondary-btn {

        text-align: center;

    }


    .location-search {

        flex-direction: column;

    }

}

</style>


<?php include 'footer.php'; ?>
```
