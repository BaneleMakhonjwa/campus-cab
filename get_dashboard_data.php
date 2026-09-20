<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$response = [];

/* ================= DRIVERS (pending approval) ================= */
$pending = [];
$res = mysqli_query($conn, "SELECT DriverID, DriverFname, DriverLname, DriverEmail, DriverPhoneNo, licence_number, driverAccount_Status, rejection_reason FROM driver WHERE driverAccount_Status = 'Pending'");
while ($row = mysqli_fetch_assoc($res)) {
    $pending[] = $row;
}
$response['pendingDrivers'] = $pending;

/* ================= DRIVERS (approved roster) ================= */
$roster = [];
$res = mysqli_query($conn, "
    SELECT d.DriverID, d.DriverFname, d.DriverLname, d.driverAccount_Status,
           v.make, v.model, v.registration_no
    FROM driver d
    LEFT JOIN vehicle v ON v.DriverID = d.DriverID
    WHERE d.driverAccount_Status IN ('Active','Inactive')
");
while ($row = mysqli_fetch_assoc($res)) {
    $roster[] = $row;
}
$response['driverRoster'] = $roster;

/* ================= COMPLAINTS ================= */
$complaints = [];
$res = mysqli_query($conn, "
    SELECT c.complaint_id, c.category, c.description, c.status, c.resolution, c.filed_at,
           s.studentFname, s.studentLname,
           d.DriverFname, d.DriverLname
    FROM complaint c
    LEFT JOIN student s ON c.student_id = s.studentID
    LEFT JOIN driver d ON c.DriverID = d.DriverID
    ORDER BY c.filed_at DESC
");
while ($row = mysqli_fetch_assoc($res)) {
    $complaints[] = $row;
}
$response['complaints'] = $complaints;

/* ================= DRIVER CLAIMS (Reimbursement) ================= */
$claims = [];
$res = mysqli_query($conn, "
    SELECT dc.claim_id, dc.claim_type, dc.claim_category, dc.amount, dc.status,
           dc.resolution_note, dc.requested_at,
           d.DriverFname, d.DriverLname
    FROM driver_claim dc
    JOIN driver d ON dc.DriverID = d.DriverID
    ORDER BY dc.requested_at DESC
");
while ($row = mysqli_fetch_assoc($res)) {
    $claims[] = $row;
}
$response['driverClaims'] = $claims;

/* ================= DRIVER RATINGS (live average) ================= */
$ratings = [];
$res = mysqli_query($conn, "
    SELECT d.DriverID, d.DriverFname, d.DriverLname,
           AVG(rt.driver_rating) AS average_rating,
           COUNT(rt.rating_id) AS review_count,
           COUNT(DISTINCT r.rideID) AS trip_count
    FROM driver d
    LEFT JOIN ride r ON r.DriverID = d.DriverID
    LEFT JOIN rating rt ON rt.ride_id = r.rideID AND rt.driver_rating IS NOT NULL
    GROUP BY d.DriverID
    HAVING review_count > 0
");
while ($row = mysqli_fetch_assoc($res)) {
    $row['average_rating'] = round((float)$row['average_rating'], 1);
    $ratings[] = $row;
}
$response['driverRatings'] = $ratings;

/* ================= DRIVER COMPENSATION (from ride earnings) ================= */
$compensation = [];
$res = mysqli_query($conn, "
    SELECT d.DriverID, d.DriverFname, d.DriverLname,
           COUNT(r.rideID) AS completed_rides,
           SUM(r.estimated_price) AS total_ride_value,
           SUM(r.driver_earning) AS total_driver_earning,
           SUM(r.campus_commission) AS total_commission
    FROM driver d
    JOIN ride r ON r.DriverID = d.DriverID AND r.ride_status = 'Completed'
    GROUP BY d.DriverID
");
while ($row = mysqli_fetch_assoc($res)) {
    $compensation[] = $row;
}
$response['driverCompensation'] = $compensation;

/* ================= SYSTEM SETTINGS ================= */
$settings = [];
$res = mysqli_query($conn, "SELECT setting_key, setting_value FROM system_settings");
while ($row = mysqli_fetch_assoc($res)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$response['systemSettings'] = $settings;
/* ================= STUDENTS ================= */
$students = [];
$res = mysqli_query($conn, "SELECT studentID, studentNumber, studentFname, studentLname, studentEmail, studentAccount_status FROM student ORDER BY studentFname");
while ($row = mysqli_fetch_assoc($res)) {
    $students[] = $row;
}
$response['students'] = $students;

/* ================= STAFF ================= */
$staffList = [];
$res = mysqli_query($conn, "SELECT staffID, staffFname, staffLname, staffEmail, staffAccount_status FROM staff ORDER BY staffFname");
while ($row = mysqli_fetch_assoc($res)) {
    $staffList[] = $row;
}
$response['staff'] = $staffList;

/* ================= COUNTS FOR DASHBOARD CARDS ================= */
$counts = [];

$res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM student WHERE studentAccount_status = 'Active'");
$counts['activeStudents'] = (int) mysqli_fetch_assoc($res)['c'];

$res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM staff WHERE staffAccount_status = 'Active'");
$counts['activeStaff'] = (int) mysqli_fetch_assoc($res)['c'];

$res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM driver WHERE driverAccount_Status = 'Active'");
$counts['activeDrivers'] = (int) mysqli_fetch_assoc($res)['c'];

$res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM ride WHERE ride_status = 'Cancelled'");
$counts['cancelledRides'] = (int) mysqli_fetch_assoc($res)['c'];

$response['counts'] = $counts;

echo json_encode($response);
mysqli_close($conn);
?>
