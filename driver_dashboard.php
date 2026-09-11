<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

$pageTitle = "Driver Dashboard";

$driverID = (int)($_SESSION['driver_id'] ?? 0);
$driverFirstName = 'Driver';
$driverFullName = 'Driver';
$availabilityStatus = 'Unavailable';

if ($driverID > 0) {

    $stmt = $conn->prepare("
        SELECT
            DriverID,
            DriverFname,
            DriverLname,
            availability_status
        FROM driver
        WHERE DriverID = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("i", $driverID);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $driverID = (int)$row['DriverID'];

            $driverFirstName = trim(
                $row['DriverFname'] ?? 'Driver'
            );

            $driverFullName = trim(
                ($row['DriverFname'] ?? '') . ' ' .
                ($row['DriverLname'] ?? '')
            );

            if ($driverFullName === '') {
                $driverFullName = 'Driver';
            }

            $availabilityStatus =
                $row['availability_status'] ?? 'Unavailable';
        }

        $stmt->close();
    }
}

$isAvailable =
    strtolower(trim($availabilityStatus)) === 'available';


$todayRides = 0;

if ($driverID > 0) {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM ride
        WHERE DriverID = ?
        AND DATE(requested_at) = CURDATE()
    ");

    if ($stmt) {

        $stmt->bind_param("i", $driverID);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $todayRides = (int)$row['total'];
        }

        $stmt->close();
    }
}


$currentRide = null;

if ($driverID > 0) {

    $stmt = $conn->prepare("
        SELECT
            r.rideID,
            r.pickup_address,
            r.destination_address,
            r.ride_status,
            r.studentID,
            r.staffID,
            r.requested_at,
            s.StudentFname,
            s.StudentLname
        FROM ride r
        LEFT JOIN student s
            ON r.studentID = s.studentID
        WHERE r.DriverID = ?
        AND r.ride_status IN ('Accepted', 'In Progress')
        ORDER BY r.requested_at DESC
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("i", $driverID);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $currentRide = $row;
        }

        $stmt->close();
    }
}


$passengerName = 'Passenger';

if ($currentRide) {

    $passengerName = trim(
        ($currentRide['StudentFname'] ?? '') . ' ' .
        ($currentRide['StudentLname'] ?? '')
    );

    if ($passengerName === '') {
        $passengerName = 'Passenger';
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

    <title>
        <?php echo htmlspecialchars($pageTitle); ?> — CampusCab
    </title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;
            background: #f6f5f1;
            color: #0c2d4d;
        }

        a {
            text-decoration: none;
        }

        button,
        input {
            font-family: inherit;
        }


        /* =====================================================
           HEADER
           ===================================================== */

        .site-header {
            height: 72px;
            background: #0c2d4d;
            /* Gold line removed */
            border-bottom: none;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-inner {
            width: 100%;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            font-size: 25px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .brand-campus {
            color: #ffffff;
        }

        .brand-cab {
            color: #e0a526;
        }


        /* =====================================================
           DRIVER PROFILE
           ===================================================== */

        .driver-profile {
            display: flex;
            align-items: center;
            gap: 11px;
            color: #ffffff;
            padding: 7px 10px;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .driver-profile:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .profile-avatar {
            width: 39px;
            height: 39px;
            border-radius: 50%;
            background: #e0a526;
            color: #0c2d4d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .profile-name {
            font-size: 14px;
            font-weight: 600;
        }

        .profile-role {
            font-size: 11px;
            color: #d6dfe5;
            margin-top: 3px;
        }


        /* =====================================================
           MAIN PAGE LAYOUT
           ===================================================== */

        .page-layout {
            display: flex;
            min-height: calc(100vh - 72px);
            align-items: stretch;
        }


        /* =====================================================
           SIDEBAR
           ===================================================== */

        .sidebar {
            width: 245px;
            flex-shrink: 0;
            background: #ffffff;
            border-right: 1px solid #dfe3e6;
            padding: 22px 14px;
            transition: width 0.25s ease;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: 76px;
        }


        /* =====================================================
           MENU BUTTON
           ===================================================== */

        .menu-toggle {
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 8px;
            background: #0c2d4d;
            color: #ffffff;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 0 14px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .menu-toggle:hover {
            background: #185fa5;
        }

        .hamburger {
            width: 20px;
            min-width: 20px;
            font-size: 19px;
            text-align: center;
        }

        .menu-label {
            white-space: nowrap;
        }


        /* =====================================================
           NAVIGATION
           ===================================================== */

        .nav-item,
        .nav-parent {
            width: 100%;
            margin-bottom: 5px;
        }

        .nav-link,
        .nav-parent-button {
            width: 100%;
            min-height: 44px;
            border: none;
            background: transparent;
            color: #0c2d4d;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 0 13px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-align: left;
        }

        .nav-link:hover,
        .nav-parent-button:hover {
            background: #f1f4f6;
        }

        .nav-link.active {
            background: #f1f4f6;
            border-left: 3px solid #e0a526;
        }

        .nav-icon {
            width: 22px;
            min-width: 22px;
            text-align: center;
            font-size: 17px;
            color: #0c2d4d;
        }

        .arrow {
            margin-left: auto;
            font-size: 13px;
            transition: transform 0.2s ease;
        }

        .nav-parent.open .arrow {
            transform: rotate(180deg);
        }


        /* =====================================================
           SUBMENU
           ===================================================== */

        .submenu {
            display: none;
            padding: 3px 0 5px 35px;
        }

        .nav-parent.open .submenu {
            display: block;
        }

        .submenu a {
            display: block;
            padding: 9px 10px;
            color: #64737e;
            font-size: 13px;
            border-radius: 6px;
            margin-bottom: 2px;
        }

        .submenu a:hover {
            color: #0c2d4d;
            background: #f4f6f7;
        }


        /* =====================================================
           LOGOUT
           ===================================================== */

        .logout-link {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e8ea;
        }

        .logout-link .nav-icon {
            color: #b03a2e;
        }


        /* =====================================================
           COLLAPSED SIDEBAR
           ===================================================== */

        .sidebar.collapsed .menu-label,
        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .arrow {
            display: none;
        }

        .sidebar.collapsed .menu-toggle {
            justify-content: center;
            padding: 0;
        }

        .sidebar.collapsed .nav-link,
        .sidebar.collapsed .nav-parent-button {
            justify-content: center;
            padding: 0;
        }

        .sidebar.collapsed .submenu {
            display: none !important;
        }


        /* =====================================================
           MAIN CONTENT
           ===================================================== */

        .main-content {
            flex: 1;
            min-width: 0;
            padding: 30px;
            transition: width 0.25s ease;
        }

        .content-container {
            width: 100%;
            max-width: 1250px;
            margin: 0 auto;
        }


        /* =====================================================
           WELCOME SECTION
           ===================================================== */

        .welcome-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .welcome-text h1 {
            font-size: 27px;
            color: #0c2d4d;
            margin-bottom: 7px;
        }

        .welcome-text p {
            color: #687984;
            font-size: 14px;
        }


        /* =====================================================
           AVAILABILITY
           ===================================================== */

        .availability-card {
            background: #ffffff;
            border: 1px solid #e1e5e8;
            border-radius: 10px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .availability-text {
            display: flex;
            flex-direction: column;
        }

        .availability-text strong {
            font-size: 13px;
            color: #0c2d4d;
        }

        .availability-text span {
            font-size: 11px;
            color: #788791;
            margin-top: 3px;
        }


        /* =====================================================
           TOGGLE
           ===================================================== */

        .switch {
            position: relative;
            width: 45px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            inset: 0;
            background: #c9d0d5;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.2s;
        }

        .slider:before {
            content: "";
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 3px;
            background: #ffffff;
            border-radius: 50%;
            transition: 0.2s;
        }

        input:checked + .slider {
            background: #e0a526;
        }

        input:checked + .slider:before {
            transform: translateX(21px);
        }


        /* =====================================================
           DASHBOARD CARDS
           ===================================================== */

        .cards-grid {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .dashboard-card {
            background: #ffffff;
            border: 1px solid #e0e4e7;
            border-radius: 11px;
            padding: 21px;
            min-height: 145px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: inherit;
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5dc;
            box-shadow:
                0 7px 20px
                rgba(12, 45, 77, 0.08);
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .card-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #f1f4f6;
            color: #0c2d4d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            font-weight: 700;
        }

        .card-arrow {
            color: #a4afb6;
            font-size: 17px;
        }

        .dashboard-card h3 {
            font-size: 15px;
            color: #0c2d4d;
            margin-top: 15px;
        }

        .dashboard-card p {
            font-size: 12px;
            color: #75838d;
            margin-top: 5px;
            line-height: 1.4;
        }

        .today-number {
            font-size: 27px;
            font-weight: 700;
            color: #e0a526;
        }


        /* =====================================================
           CURRENT RIDE
           ===================================================== */

        .section-title {
            font-size: 18px;
            color: #0c2d4d;
            margin-bottom: 13px;
        }

        .current-ride {
            background: #ffffff;
            border: 1px solid #e0e4e7;
            border-radius: 11px;
            overflow: hidden;
        }

        .ride-header {
            padding: 17px 20px;
            border-bottom: 1px solid #e8ebed;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .ride-header h3 {
            font-size: 15px;
            color: #0c2d4d;
        }

        .status-badge {
            padding: 6px 10px;
            border-radius: 20px;
            background: #fff5d9;
            color: #806000;
            font-size: 11px;
            font-weight: 700;
        }

        .ride-body {
            padding: 20px;
            display: grid;
            grid-template-columns:
                1fr 1fr auto;
            gap: 25px;
            align-items: center;
        }

        .ride-detail label {
            display: block;
            color: #89949b;
            font-size: 11px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .ride-detail strong {
            display: block;
            color: #0c2d4d;
            font-size: 14px;
            line-height: 1.4;
        }

        .view-ride {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 17px;
            border-radius: 7px;
            background: #0c2d4d;
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .view-ride:hover {
            background: #185fa5;
        }

        .no-ride {
            padding: 35px 20px;
            text-align: center;
            color: #7c8991;
            font-size: 13px;
        }


        /* =====================================================
           FOOTER
           ===================================================== */

        footer {
            margin-top: 30px;
            padding: 18px 0;
            border-top: 1px solid #e0e4e7;
            color: #89949b;
            font-size: 11px;
            text-align: center;
        }


        /* =====================================================
           TABLET
           ===================================================== */

        @media (max-width: 1100px) {

            .cards-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .ride-body {
                grid-template-columns:
                    1fr 1fr;
            }

            .view-ride {
                grid-column: 1 / -1;
                justify-self: start;
            }
        }


        /* =====================================================
           MOBILE
           ===================================================== */

        @media (max-width: 760px) {

            .site-header {
                height: 64px;
            }

            .header-inner {
                padding: 0 16px;
            }

            .brand {
                font-size: 21px;
            }

            .profile-info {
                display: none;
            }

            .page-layout {
                display: block;
                min-height: calc(100vh - 64px);
            }

            .sidebar {
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid #dfe3e6;
                padding: 10px 14px;
            }

            .sidebar.collapsed {
                width: 100%;
                height: 64px;
            }

            .menu-toggle {
                margin-bottom: 0;
            }

            .sidebar:not(.collapsed) .menu-toggle {
                margin-bottom: 10px;
            }

            .sidebar.collapsed .menu-toggle {
                justify-content: flex-start;
                padding: 0 14px;
            }

            .sidebar.collapsed .menu-label {
                display: block;
            }

            .sidebar.collapsed .nav-link,
            .sidebar.collapsed .nav-parent-button {
                display: none;
            }

            .main-content {
                padding: 20px 15px;
            }

            .welcome-row {
                flex-direction: column;
                align-items: stretch;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .ride-body {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .view-ride {
                grid-column: auto;
                width: 100%;
            }
        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER
     ===================================================== -->

<header class="site-header">

    <div class="header-inner">

        <a
            href="driver_dashboard.php"
            class="brand"
        >
            <span class="brand-campus">Campus</span><span class="brand-cab">Cab</span>
        </a>


        <a
            href="driver_profile.php"
            class="driver-profile"
        >

            <div class="profile-avatar">

                <?php

                echo htmlspecialchars(
                    strtoupper(
                        substr($driverFirstName, 0, 1)
                    )
                );

                ?>

            </div>


            <div class="profile-info">

                <span class="profile-name">

                    <?php

                    echo htmlspecialchars($driverFullName);

                    ?>

                </span>


                <span class="profile-role">

                    Driver ID:

                    <?php

                    echo htmlspecialchars((string)$driverID);

                    ?>

                </span>

            </div>

        </a>

    </div>

</header>


<!-- =====================================================
     PAGE LAYOUT
     ===================================================== -->

<div class="page-layout">


    <!-- =================================================
         SIDEBAR
         ================================================= -->

    <aside
        class="sidebar"
        id="sidebar"
    >


        <!-- MENU BUTTON -->

        <button
            class="menu-toggle"
            type="button"
            onclick="toggleSidebar()"
        >

            <span class="hamburger">
                ☰
            </span>

            <span class="menu-label">
                Menu
            </span>

        </button>


        <!-- HOME -->

        <div class="nav-item">

            <a
                href="driver_dashboard.php"
                class="nav-link active"
            >

                <span class="nav-icon">
                    ⌂
                </span>

                <span class="nav-text">
                    Home
                </span>

            </a>

        </div>


        <!-- SERVICES -->

        <div class="nav-parent">

            <button
                class="nav-parent-button"
                type="button"
                onclick="toggleSubmenu(this)"
            >

                <span class="nav-icon">
                    ▣
                </span>

                <span class="nav-text">
                    Services
                </span>

                <span class="arrow">
                    ⌄
                </span>

            </button>


            <div class="submenu">

                <a href="driver_ride_requests.php">
                    Ride Requests
                </a>

                <a href="driver_parcel_requests.php">
                    Parcel Requests
                </a>

                <a href="driver_rates.php">
                    My Rates
                </a>

            </div>

        </div>


        <!-- ACTIVITY -->

        <div class="nav-parent">

            <button
                class="nav-parent-button"
                type="button"
                onclick="toggleSubmenu(this)"
            >

                <span class="nav-icon">
                    ◷
                </span>

                <span class="nav-text">
                    Activity
                </span>

                <span class="arrow">
                    ⌄
                </span>

            </button>


            <div class="submenu">

                <a href="driver_today_rides.php">
                    Today's Rides
                </a>

                <a href="driver_earnings.php">
                    Earnings
                </a>

                <a href="driver_total_rides.php">
                    Total Rides
                </a>

            </div>

        </div>


        <!-- ACCOUNT -->

        <div class="nav-parent">

            <button
                class="nav-parent-button"
                type="button"
                onclick="toggleSubmenu(this)"
            >

                <span class="nav-icon">
                    ○
                </span>

                <span class="nav-text">
                    Account
                </span>

                <span class="arrow">
                    ⌄
                </span>

            </button>


            <div class="submenu">

                <a href="driver_profile.php">
                    Profile
                </a>

                <a href="help_support.php">
                    Help &amp; Support
                </a>

            </div>

        </div>


        <!-- LOGOUT -->

        <div class="nav-item logout-link">

            <a
                href="logout.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    ↪
                </span>

                <span class="nav-text">
                    Log Out
                </span>

            </a>

        </div>

    </aside>


    <!-- =================================================
         MAIN CONTENT
         ================================================= -->

    <main class="main-content">

        <div class="content-container">


            <!-- WELCOME -->

            <div class="welcome-row">

                <div class="welcome-text">

                    <h1>

                        Welcome back,

                        <?php

                        echo htmlspecialchars($driverFirstName);

                        ?>

                    </h1>

                    <p>
                        Manage your CampusCab trips and requests from here.
                    </p>

                </div>


                <!-- AVAILABILITY -->

                <div class="availability-card">

                    <div class="availability-text">

                        <strong id="availabilityTitle">

                            <?php

                            echo $isAvailable
                                ? "You're online"
                                : "You're offline";

                            ?>

                        </strong>


                        <span id="availabilityDescription">

                            <?php

                            echo $isAvailable
                                ? "Available for requests"
                                : "Not receiving requests";

                            ?>

                        </span>

                    </div>


                    <label class="switch">

                        <input
                            type="checkbox"
                            id="availabilityToggle"

                            <?php

                            echo $isAvailable
                                ? 'checked'
                                : '';

                            ?>

                        >

                        <span class="slider"></span>

                    </label>

                </div>

            </div>


            <!-- =================================================
                 DASHBOARD CARDS
                 ================================================= -->

            <div class="cards-grid">


                <!-- RIDE REQUESTS -->

                <a
                    href="driver_ride_requests.php"
                    class="dashboard-card"
                >

                    <div class="card-top">

                        <div class="card-icon">
                            🚕
                        </div>

                        <span class="card-arrow">
                            →
                        </span>

                    </div>


                    <div>

                        <h3>
                            Ride Requests
                        </h3>

                        <p>
                            View and manage incoming ride requests.
                        </p>

                    </div>

                </a>


                <!-- PARCEL REQUESTS -->

                <a
                    href="driver_parcel_requests.php"
                    class="dashboard-card"
                >

                    <div class="card-top">

                        <div class="card-icon">
                            📦
                        </div>

                        <span class="card-arrow">
                            →
                        </span>

                    </div>


                    <div>

                        <h3>
                            Parcel Requests
                        </h3>

                        <p>
                            View and manage parcel deliveries.
                        </p>

                    </div>

                </a>


                <!-- TODAY'S RIDES -->

                <a
                    href="today_rides.php"
                    class="dashboard-card"
                >

                    <div class="card-top">

                        <div class="card-icon">
                            📊
                        </div>

                        <span class="card-arrow">
                            →
                        </span>

                    </div>


                    <div>

                        <h3>
                            Today's Rides
                        </h3>

                        <p>
                            Rides assigned to you today.
                        </p>

                    </div>

                </a>


                <!-- MY RATES -->

                <a
                    href="my_rates.php"
                    class="dashboard-card"
                >

                    <div class="card-top">

                        <div
                            class="card-icon"
                            style="color: #e0a526;"
                        >
                            ★
                        </div>

                        <span class="card-arrow">
                            →
                        </span>

                    </div>


                    <div>

                        <h3>
                            My Rates
                        </h3>

                        <p>
                            View your current CampusCab rates.
                        </p>

                    </div>

                </a>

            </div>


            <!-- =================================================
                 CURRENT RIDE
                 ================================================= -->

            <section>

                <h2 class="section-title">
                    Current Ride
                </h2>


                <div class="current-ride">

                    <?php if ($currentRide): ?>


                        <div class="ride-header">

                            <h3>
                                Active trip
                            </h3>

                            <span class="status-badge">

                                <?php

                                echo htmlspecialchars(
                                    $currentRide['ride_status']
                                );

                                ?>

                            </span>

                        </div>


                        <div class="ride-body">


                            <div class="ride-detail">

                                <label>
                                    Passenger
                                </label>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $passengerName
                                    );

                                    ?>

                                </strong>

                            </div>


                            <div class="ride-detail">

                                <label>
                                    Route
                                </label>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $currentRide['pickup_address']
                                    );

                                    ?>

                                    →

                                    <?php

                                    echo htmlspecialchars(
                                        $currentRide['destination_address']
                                    );

                                    ?>

                                </strong>

                            </div>


                            <a
                                href="verify_passenger.php?rideID=<?php echo (int)$currentRide['rideID']; ?>"
                                class="view-ride"
                            >
                                View Ride
                            </a>

                        </div>


                    <?php else: ?>


                        <div class="no-ride">
                            You currently have no active rides.
                        </div>


                    <?php endif; ?>

                </div>

            </section>


            <!-- FOOTER -->

            <footer>

                CampusCab &copy;

                <?php echo date('Y'); ?>

            </footer>


        </div>

    </main>

</div>


<script>


/* =========================================================
   SIDEBAR EXPAND / COLLAPSE
   ========================================================= */

function toggleSidebar() {

    const sidebar =
        document.getElementById('sidebar');

    sidebar.classList.toggle('collapsed');

}


/* =========================================================
   SUBMENU
   ========================================================= */

function toggleSubmenu(button) {

    const parent =
        button.closest('.nav-parent');

    const allMenus =
        document.querySelectorAll('.nav-parent');

    allMenus.forEach(function(menu) {

        if (menu !== parent) {
            menu.classList.remove('open');
        }

    });

    parent.classList.toggle('open');

}


/* =========================================================
   DRIVER AVAILABILITY
   ========================================================= */

const availabilityToggle =
    document.getElementById('availabilityToggle');

const availabilityTitle =
    document.getElementById('availabilityTitle');

const availabilityDescription =
    document.getElementById('availabilityDescription');


if (availabilityToggle) {

    availabilityToggle.addEventListener(
        'change',
        function() {

            const toggle =
                this;

            const newStatus =
                toggle.checked
                    ? 'Available'
                    : 'Unavailable';


            /*
             * Disable the toggle while the request
             * is being processed.
             */

            toggle.disabled = true;


            fetch(
                'update_driver_status.php',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/x-www-form-urlencoded'
                    },

                    body:
                        'status=' +
                        encodeURIComponent(newStatus)
                }
            )

            .then(function(response) {

                if (!response.ok) {

                    throw new Error(
                        'Server returned an error.'
                    );

                }

                return response.text();

            })

            .then(function(responseText) {

                if (responseText.trim() !== 'success') {

                    throw new Error(
                        responseText ||
                        'Unable to update availability.'
                    );

                }


                /*
                 * Update the displayed text without
                 * reloading the whole dashboard.
                 */

                if (newStatus === 'Available') {

                    availabilityTitle.textContent =
                        "You're online";

                    availabilityDescription.textContent =
                        "Available for requests";

                } else {

                    availabilityTitle.textContent =
                        "You're offline";

                    availabilityDescription.textContent =
                        "Not receiving requests";

                }

            })

            .catch(function(error) {

                console.error(
                    'Error updating availability:',
                    error
                );


                /*
                 * If the database update failed,
                 * return the switch to its previous state.
                 */

                toggle.checked =
                    !toggle.checked;


                alert(
                    'Unable to update your availability. Please try again.'
                );

            })

            .finally(function() {

                toggle.disabled = false;

            });

        }
    );

}

</script>


</body>

</html>