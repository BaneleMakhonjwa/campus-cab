-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2026 at 06:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campus_cab_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(11) NOT NULL,
  `adminUsername` varchar(50) NOT NULL,
  `adminFname` varchar(50) NOT NULL,
  `adminLname` varchar(50) NOT NULL,
  `adminEmail` varchar(100) NOT NULL,
  `adminPwd` varchar(255) NOT NULL,
  `adminRole` enum('super_admin','admin') DEFAULT 'admin',
  `accountStatus` enum('Active','Inactive') DEFAULT 'Active',
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `adminUsername`, `adminFname`, `adminLname`, `adminEmail`, `adminPwd`, `adminRole`, `accountStatus`, `createdBy`, `createdAt`) VALUES
(1, 'superadmin01', 'Super', 'Admin One', 'superadmin01@campuscab.co.za', '$2y$10$zNb3lPqpmU2L3BlVcm2vYOhA6RGiWz2UT2vL0zMzwxNYEECAMpqVu', 'super_admin', 'Active', NULL, '2026-09-19 17:48:50'),
(2, 'superadmin02', 'Super', 'Admin Two', 'superadmin02@campuscab.co.za', '$2y$10$zNb3lPqpmU2L3BlVcm2vYOhA6RGiWz2UT2vL0zMzwxNYEECAMpqVu', 'super_admin', 'Active', NULL, '2026-09-19 17:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `logID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `userRole` varchar(30) NOT NULL,
  `userName` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `targetType` varchar(50) DEFAULT NULL,
  `targetID` int(11) DEFAULT NULL,
  `targetName` varchar(100) DEFAULT NULL,
  `logDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`logID`, `userID`, `userRole`, `userName`, `action`, `targetType`, `targetID`, `targetName`, `logDate`) VALUES
(1, 1, 'super_admin', 'Super Admin One', 'Approved Driver', 'driver', 1, 'Sipho Zikode', '2026-09-19 20:08:04'),
(2, 1, 'super_admin', 'Super Admin One', 'Paid Driver', 'driver', 1, 'Sipho Zikode (2 rides)', '2026-09-19 23:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `campus_location`
--

CREATE TABLE `campus_location` (
  `location_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campus_location`
--

INSERT INTO `campus_location` (`location_id`, `name`, `latitude`, `longitude`) VALUES
(1, 'Student Village 1.1', -32.7830000, 26.8430000),
(2, 'Student Village 1.2', -32.7810000, 26.8400000),
(3, 'Student Village 1.3', -32.7845000, 26.8410000),
(4, 'Student Village 2.1', -32.7845000, 26.8410000),
(5, 'Student Village 2.2', -32.7850000, 26.8460000),
(6, 'Student Village 2.3', -32.7830000, 26.8430000),
(7, 'Student Village 3.1', -32.7855000, 26.8465000),
(8, 'Student Village 3.2', -32.7850000, 26.8460000),
(9, 'Student Village 3.3', -32.7845000, 26.8455000),
(10, 'Student Village 4.1', -32.7838000, 26.8442000),
(11, 'Student Village 4.2', -32.7862000, 26.8420000),
(12, 'Student Village 4.3', -32.7800000, 26.8390000),
(13, 'Student Village 5.1', -32.7833000, 26.8448000),
(14, 'Student Village 5.2', -32.7835000, 26.8450000),
(15, 'Student Village 5.3', -32.7837000, 26.8452000),
(16, 'Student Village 6.1', -32.7840000, 26.8455000),
(17, 'Student Village 6.2', -32.7842000, 26.8458000),
(18, 'East Camp', -32.7860000, 26.8470000),
(19, 'Marikana', -32.7870000, 26.8480000),
(20, 'Jolobe 1', -32.7880000, 26.8490000),
(21, 'Jolobe 2', -32.7885000, 26.8495000),
(22, 'ZK Mathews 1', -32.7890000, 26.8500000),
(23, 'ZK Mathews 2', -32.7895000, 26.8505000),
(24, 'Mfundweni', -32.7900000, 26.8510000),
(25, 'Lower Kuwait', -32.7905000, 26.8515000),
(26, 'Upper Kuwait', -32.7910000, 26.8520000),
(27, 'Main Gate', -32.7800000, 26.8390000),
(28, 'Courier Guy Offices', -32.7881000, 26.8394000),
(29, 'Post Office', -32.7879000, 26.8382000),
(30, 'Main Gate', -32.7800000, 26.8390000),
(31, 'Student Centre', -32.7833000, 26.8448000),
(32, 'Admin Building', -32.7838000, 26.8442000),
(33, 'Main Library', -32.7850000, 26.8460000),
(34, 'Student Village 1.1', -32.7830000, 26.8430000),
(35, 'Student Village 2.2', -32.7850000, 26.8460000),
(36, 'East Camp', -32.7860000, 26.8470000);

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `DriverID` int(11) NOT NULL,
  `DriverFname` varchar(50) NOT NULL,
  `DriverLname` varchar(50) NOT NULL,
  `DriverEmail` varchar(50) NOT NULL,
  `DriverPhoneNo` varchar(15) NOT NULL,
  `DriverIDNumber` varchar(13) DEFAULT NULL,
  `DriverGender` varchar(10) DEFAULT NULL,
  `DriverAddress` text DEFAULT NULL,
  `driverAccount_Status` enum('Active','Inactive') DEFAULT 'Active',
  `approval_status` enum('Pending','Approved','Rejected','Suspended') DEFAULT 'Pending',
  `licence_number` varchar(30) NOT NULL,
  `availability_status` enum('Available','Unavailable') DEFAULT 'Unavailable',
  `DriverPhoto` varchar(255) DEFAULT NULL,
  `VehicleReg` varchar(20) DEFAULT NULL,
  `VehicleModel` varchar(50) DEFAULT NULL,
  `vehicle_type` varchar(20) DEFAULT NULL,
  `vehicle_color` varchar(30) DEFAULT NULL,
  `vehicle_capacity` int(11) DEFAULT NULL,
  `vehicle_make` varchar(50) DEFAULT NULL,
  `VehiclePhoto` varchar(255) DEFAULT NULL,
  `DriverPwd` varchar(255) NOT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`DriverID`, `DriverFname`, `DriverLname`, `DriverEmail`, `DriverPhoneNo`, `DriverIDNumber`, `DriverGender`, `DriverAddress`, `driverAccount_Status`, `approval_status`, `licence_number`, `availability_status`, `DriverPhoto`, `VehicleReg`, `VehicleModel`, `vehicle_type`, `vehicle_color`, `vehicle_capacity`, `vehicle_make`, `VehiclePhoto`, `DriverPwd`, `reviewed_by`, `reviewed_at`, `review_notes`, `email_verified`, `verification_token`, `token_expiry`, `reset_token`, `reset_expiry`) VALUES
(1, 'Sipho', 'Zikode', 'makhonjwabanele01@gmail.com', '0789846271', '0711050842082', 'Female', '42 The Avenue, Cape Town, Cape Town, Western Cape 7915', 'Active', 'Approved', 'HRY 191 EC', 'Available', 'uploads/drivers/driver_1789842387_2745.png', 'HRY 191 EC', 'Corolla', 'Car', 'White', 4, 'Toyota', 'uploads/drivers/vehicle_1789842387_5588.png', '$2y$10$xbANbnjUaYARWnBp6bwjEexj42TM46o3QxPxdAGup4gn1ifmk4Vtm', 1, '2026-09-19 22:08:04', NULL, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `driver_emergency_contacts`
--

CREATE TABLE `driver_emergency_contacts` (
  `contactID` int(11) NOT NULL,
  `DriverID` int(11) NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_messages`
--

CREATE TABLE `driver_messages` (
  `messageID` int(11) NOT NULL,
  `DriverID` int(11) NOT NULL,
  `sender_type` varchar(30) NOT NULL DEFAULT 'Admin',
  `subject` varchar(255) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_ratings`
--

CREATE TABLE `driver_ratings` (
  `ratingID` int(11) NOT NULL,
  `rideID` int(11) NOT NULL,
  `studentID` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `passenger_type` enum('student','staff') NOT NULL DEFAULT 'student',
  `DriverID` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `rated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_ratings`
--

INSERT INTO `driver_ratings` (`ratingID`, `rideID`, `studentID`, `staff_id`, `passenger_type`, `DriverID`, `rating`, `comment`, `rated_at`) VALUES
(1, 11, 1, NULL, 'student', 1, 5, 'Very friendly.', '2026-09-20 01:57:56'),
(2, 12, 2, NULL, 'student', 1, 3, 'Took too long to get to me.', '2026-09-20 14:41:08');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `location_id` int(11) NOT NULL,
  `ride_id` int(11) DEFAULT NULL,
  `driver_id` int(11) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `recorded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`location_id`, `ride_id`, `driver_id`, `latitude`, `longitude`, `recorded_at`) VALUES
(1, 12, 1, -32.7856382, 26.8509627, '2026-09-20 14:37:23'),
(2, 12, 1, -32.7853915, 26.8519409, '2026-09-20 14:37:49'),
(3, 12, 1, -32.7853915, 26.8519409, '2026-09-20 14:37:49'),
(4, 12, 1, -32.7853915, 26.8519409, '2026-09-20 14:37:53'),
(5, 12, 1, -32.7858224, 26.8502281, '2026-09-20 14:38:08'),
(6, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:38:18'),
(7, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:38:23'),
(8, 12, 1, -32.7858088, 26.8502262, '2026-09-20 14:38:34'),
(9, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:04'),
(10, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:04'),
(11, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:04'),
(12, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:04'),
(13, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:04'),
(14, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:04'),
(15, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:04'),
(16, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:04'),
(17, 12, 1, -32.7855152, 26.8514861, '2026-09-20 14:40:13'),
(18, 13, 1, -32.7858212, 26.8502337, '2026-09-21 15:37:22'),
(19, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(20, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(21, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(22, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(23, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(24, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(25, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(26, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(27, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(28, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(29, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(30, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(31, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:45:59'),
(32, 14, 1, -32.7855881, 26.8516649, '2026-09-21 15:46:02'),
(33, 14, 1, -32.7855800, 26.8518773, '2026-09-21 15:46:17');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `message_` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parcel`
--

CREATE TABLE `parcel` (
  `parcel` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `parcel_description` text NOT NULL,
  `parcel_size` varchar(20) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `recipient_contact` varchar(30) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `estimated_price` decimal(10,2) NOT NULL,
  `DriverID` int(11) DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `parcel_status` varchar(30) NOT NULL DEFAULT 'Requested',
  `delivery_method` varchar(50) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `studentID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL,
  `passenger_type` enum('student','staff') DEFAULT 'student',
  `item_cost` decimal(8,2) DEFAULT 0.00,
  `delivery_fee` decimal(8,2) NOT NULL,
  `total_amount` decimal(8,2) NOT NULL,
  `payment_method` enum('Cash','Card','EFT') DEFAULT 'Cash',
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `ride_id`, `studentID`, `staffID`, `passenger_type`, `item_cost`, `delivery_fee`, `total_amount`, `payment_method`, `payment_status`, `paid_at`) VALUES
(1, 14, 2, NULL, 'student', 0.00, 28.90, 28.90, 'Card', 'Paid', '2026-09-21 15:52:47');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `rating_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `student_rating` tinyint(4) DEFAULT NULL,
  `driver_rating` tinyint(4) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `rated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ride`
--

CREATE TABLE `ride` (
  `rideID` int(11) NOT NULL,
  `studentID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL,
  `requester_type` enum('Student','Staff') NOT NULL DEFAULT 'Student',
  `request_type` enum('Ride','Parcel') NOT NULL DEFAULT 'Ride',
  `parcel_details` varchar(255) DEFAULT NULL,
  `DriverID` int(11) DEFAULT NULL,
  `pickup_address` varchar(250) DEFAULT NULL,
  `pickup_latitude` decimal(10,7) DEFAULT NULL,
  `pickup_longitude` decimal(10,7) DEFAULT NULL,
  `destination_address` varchar(250) DEFAULT NULL,
  `destination_latitude` decimal(10,7) DEFAULT NULL,
  `destination_longitude` decimal(10,7) DEFAULT NULL,
  `requested_at` datetime DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `Completed_at` datetime DEFAULT NULL,
  `ride_status` enum('Requested','Accepted','Driver Arriving','In Progress','Completed','Cancelled') DEFAULT 'Requested',
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `estimated_distance_km` decimal(6,2) DEFAULT NULL,
  `estimated_duration_min` int(11) DEFAULT NULL,
  `estimated_price` decimal(8,2) DEFAULT NULL,
  `driver_earning` double NOT NULL DEFAULT 0,
  `campus_commission` double NOT NULL DEFAULT 0,
  `notified_drivers` text DEFAULT NULL,
  `accepted_by` int(11) DEFAULT NULL,
  `paid_out` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ride`
--

INSERT INTO `ride` (`rideID`, `studentID`, `staffID`, `requester_type`, `request_type`, `parcel_details`, `DriverID`, `pickup_address`, `pickup_latitude`, `pickup_longitude`, `destination_address`, `destination_latitude`, `destination_longitude`, `requested_at`, `accepted_at`, `started_at`, `Completed_at`, `ride_status`, `cancellation_reason`, `cancelled_at`, `estimated_distance_km`, `estimated_duration_min`, `estimated_price`, `driver_earning`, `campus_commission`, `notified_drivers`, `accepted_by`, `paid_out`) VALUES
(1, 1, NULL, 'Student', 'Ride', NULL, 1, 'Admin Building', NULL, NULL, 'Lat: -32.79173911289032, Lng: 26.832148432731632', -32.7917391, 26.8321484, '2026-09-19 21:50:25', '2026-09-19 22:12:59', NULL, NULL, 'Cancelled', 'Changed my mind', '2026-09-19 23:37:46', 1.43, 3, 50.73, 0, 0, NULL, NULL, 0),
(2, 1, NULL, 'Student', 'Ride', NULL, 1, 'Admin Building', NULL, NULL, 'Lat: -32.78857333870563, Lng: 26.832261085510257', -32.7885733, 26.8322611, '2026-09-19 22:06:18', '2026-09-19 22:13:06', NULL, NULL, 'Cancelled', 'Emergency', '2026-09-19 23:37:30', 1.24, 2, 49.27, 0, 0, NULL, NULL, 0),
(3, 1, NULL, 'Student', 'Ride', NULL, 1, 'Main Gate', NULL, NULL, 'Mfundweni', NULL, NULL, '2026-09-19 23:04:22', '2026-09-19 23:19:44', NULL, NULL, 'Cancelled', 'Driver is taking too long', '2026-09-19 23:37:14', 1.58, 3, 51.85, 0, 0, NULL, 1, 0),
(4, 1, NULL, 'Student', 'Ride', NULL, 1, 'Student Centre', NULL, NULL, 'Marikana', NULL, NULL, '2026-09-19 23:07:27', '2026-09-19 23:19:53', NULL, NULL, 'Cancelled', 'Other', '2026-09-19 23:37:03', 0.51, 2, 43.82, 0, 0, NULL, 1, 0),
(5, 1, NULL, 'Student', 'Ride', NULL, 1, 'Post Office', NULL, NULL, 'Mfundweni', NULL, NULL, '2026-09-19 23:16:48', '2026-09-19 23:20:20', NULL, NULL, 'Cancelled', 'Cancelled by driver', '2026-09-19 23:32:44', 1.22, 2, 49.14, 0, 0, '1', NULL, 0),
(6, 1, NULL, 'Student', 'Ride', NULL, 1, 'Student Village 1.3', NULL, NULL, 'Lat: -32.78063585961151, Lng: 26.834278106689457', -32.7806359, 26.8342781, '2026-09-19 23:39:11', '2026-09-19 23:39:18', '2026-09-19 23:46:14', '2026-09-19 23:47:01', 'Completed', NULL, NULL, 0.76, 2, 45.71, 0, 0, NULL, 1, 1),
(7, 1, NULL, 'Student', 'Ride', NULL, 1, 'Student Village 1.2', NULL, NULL, 'Lat: -32.782800696875036, Lng: 26.82548046112061', -32.7828007, 26.8254805, '2026-09-19 23:48:40', '2026-09-19 23:48:48', '2026-09-19 23:49:55', '2026-09-19 23:50:32', 'Completed', NULL, NULL, 1.37, 3, 50.29, 0, 0, NULL, 1, 1),
(8, 1, NULL, 'Student', 'Ride', NULL, 1, 'Student Village 1.2', NULL, NULL, 'ZK Mathews 1', NULL, NULL, '2026-09-19 23:56:25', '2026-09-19 23:56:53', NULL, NULL, 'Cancelled', 'Other', '2026-09-19 23:57:36', 1.29, 3, 49.68, 0, 0, '1', 1, 0),
(9, 1, NULL, 'Student', 'Ride', NULL, NULL, 'Student Village 1.3', NULL, NULL, 'East Camp', NULL, NULL, '2026-09-19 23:58:04', NULL, NULL, NULL, 'Cancelled', 'Requested by mistake', '2026-09-19 23:59:08', 0.59, 2, 44.39, 0, 0, '1,1', NULL, 0),
(10, 1, NULL, 'Student', 'Ride', NULL, 1, 'Student Village 1.3', NULL, NULL, 'Lat: -32.78842902722552, Lng: 26.83693885803223', -32.7884290, 26.8369389, '2026-09-20 01:50:33', '2026-09-20 01:51:16', '2026-09-20 01:51:49', '2026-09-20 01:52:07', 'Completed', NULL, NULL, 0.58, 2, 34.34, 0, 0, NULL, 1, 0),
(11, 1, NULL, 'Student', 'Ride', NULL, 1, 'Student Village 5.3', NULL, NULL, 'Marikana', NULL, NULL, '2026-09-20 01:53:10', '2026-09-20 01:56:22', '2026-09-20 01:56:48', '2026-09-20 01:57:19', 'Completed', NULL, NULL, 0.45, 2, 33.38, 0, 0, NULL, 1, 0),
(12, 2, NULL, 'Student', 'Ride', NULL, 1, 'Marikana', NULL, NULL, 'Lat: -32.77623386139863, Lng: 26.835179328918457', -32.7762339, 26.8351793, '2026-09-20 14:36:24', '2026-09-20 14:36:39', '2026-09-20 14:37:23', '2026-09-20 14:40:19', 'Completed', NULL, NULL, 1.69, 3, 32.71, 0, 0, NULL, 1, 0),
(13, 2, NULL, 'Student', 'Ride', NULL, 1, 'Student Village 1.3', NULL, NULL, 'Lat: -32.790016440628825, Lng: 26.83423519134522', -32.7900164, 26.8342352, '2026-09-21 15:34:28', '2026-09-21 15:36:55', NULL, NULL, 'Cancelled', 'Cancelled by driver', '2026-09-21 15:38:32', 0.88, 2, 26.61, 0, 0, NULL, 1, 0),
(14, 2, NULL, 'Student', 'Ride', NULL, 1, 'Student Village 1.3', NULL, NULL, 'Lower Kuwait', NULL, NULL, '2026-09-21 15:38:59', '2026-09-21 15:39:02', '2026-09-21 15:46:01', '2026-09-21 15:46:17', 'Completed', NULL, NULL, 1.19, 2, 28.90, 0, 0, NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ride_passenger_verification`
--

CREATE TABLE `ride_passenger_verification` (
  `verfication_id` int(11) NOT NULL,
  `rideID` int(11) NOT NULL,
  `verification_status` enum('Pending','Verified') NOT NULL DEFAULT 'Pending',
  `verified_at` datetime DEFAULT NULL,
  `verified_by_driver` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ride_passenger_verification`
--

INSERT INTO `ride_passenger_verification` (`verfication_id`, `rideID`, `verification_status`, `verified_at`, `verified_by_driver`, `created_at`) VALUES
(1, 6, 'Verified', '2026-09-19 23:46:14', 1, '2026-09-19 21:46:14'),
(2, 7, 'Verified', '2026-09-19 23:49:55', 1, '2026-09-19 21:49:55'),
(3, 10, 'Verified', '2026-09-20 01:51:49', 1, '2026-09-19 23:51:49'),
(4, 11, 'Verified', '2026-09-20 01:56:48', 1, '2026-09-19 23:56:48'),
(5, 12, 'Verified', '2026-09-20 14:37:23', 1, '2026-09-20 12:37:23'),
(6, 14, 'Verified', '2026-09-21 15:46:01', 1, '2026-09-21 13:46:01');

-- --------------------------------------------------------

--
-- Table structure for table `safety_pin`
--

CREATE TABLE `safety_pin` (
  `pin_id` int(11) NOT NULL,
  `user_type` enum('student','staff') NOT NULL,
  `user_id` int(11) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `safety_pin`
--

INSERT INTO `safety_pin` (`pin_id`, `user_type`, `user_id`, `pin_hash`, `created_at`, `updated_at`) VALUES
(1, 'student', 1, '$2y$10$PJ1Y360/K/n1HJYxKKaPTeyGASvViX8qVD9TPf79rCAnx2tXUpDVK', '2026-09-19 21:04:22', '2026-09-19 23:53:10'),
(10, 'student', 2, '$2y$10$5MaxAo86SPfw2BpMnNVkWecnt2.ueMESRG.rmaFXnNy/8tkPg6an.', '2026-09-20 12:36:24', '2026-09-21 13:38:59');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffID` int(11) NOT NULL,
  `staffNumber` varchar(9) DEFAULT NULL,
  `staffFname` varchar(50) NOT NULL,
  `staffLname` varchar(50) NOT NULL,
  `staffEmail` varchar(50) NOT NULL,
  `staffPhoneNo` varchar(15) NOT NULL,
  `staffAccount_status` enum('Active','Inactive') DEFAULT 'Active',
  `staffPwd` varchar(255) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staffID`, `staffNumber`, `staffFname`, `staffLname`, `staffEmail`, `staffPhoneNo`, `staffAccount_status`, `staffPwd`, `reset_token`, `reset_expiry`) VALUES
(1, '22457', 'Sesona', 'Mjoli', 'mjoli.s@ufh.ac.za', '0652428564', 'Active', '$2y$10$iE19ZG/sBwS/r8lEX/HeBuSVZ3NNCrWL12SBYJF7vYd0.azvaTgBq', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` int(11) NOT NULL,
  `studentNumber` varchar(9) DEFAULT NULL,
  `studentFname` varchar(50) NOT NULL,
  `studentLname` varchar(50) NOT NULL,
  `studentEmail` varchar(50) NOT NULL,
  `studentPhoneNo` varchar(15) NOT NULL,
  `studentAccount_status` enum('Active','Inactive') DEFAULT 'Active',
  `studentPwd` varchar(255) NOT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studentID`, `studentNumber`, `studentFname`, `studentLname`, `studentEmail`, `studentPhoneNo`, `studentAccount_status`, `studentPwd`, `verification_token`, `token_expiry`, `email_verified`, `reset_token`, `reset_expiry`) VALUES
(1, '225789054', 'xola', 'Mkhize', '225789054@ufh.ac.za', '0786757675', 'Active', '$2y$10$j08M877GGkLrnXYPFSAe7O3d1RGC2Nwv8gytuJpSHlY9SNxO9LAJ.', 'c48b9b8cc33a19e4a3f822e520f0057e78f0b8b6fdaa56d48c01490287d7d2c1', '2026-09-20 20:06:38', 0, NULL, NULL),
(2, '225074411', 'Banele', 'Makhonjwa', '225074411@ufh.ac.za', '0652428564', 'Active', '$2y$10$TayIp0AJAbBC6tjWMNfJZ.4Ywaa5dbULEYm8EuomsWDSfAZZOcsbi', 'a3d7105194b5f6c16705cf15511d4098d3df30a18b7e4ce5974c301c61cf241b', '2026-09-21 13:17:33', 0, '694614', '2026-09-21 16:18:11');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE `vehicle` (
  `vehicle_id` int(11) NOT NULL,
  `DriverID` int(11) NOT NULL,
  `registration_no` varchar(20) NOT NULL,
  `make` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `colour` varchar(30) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `adminUsername` (`adminUsername`),
  ADD UNIQUE KEY `adminEmail` (`adminEmail`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`logID`);

--
-- Indexes for table `campus_location`
--
ALTER TABLE `campus_location`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`DriverID`),
  ADD UNIQUE KEY `DriverEmail` (`DriverEmail`),
  ADD UNIQUE KEY `licence_number` (`licence_number`),
  ADD KEY `idx_driver_approval` (`approval_status`),
  ADD KEY `idx_driver_availability` (`availability_status`);

--
-- Indexes for table `driver_emergency_contacts`
--
ALTER TABLE `driver_emergency_contacts`
  ADD PRIMARY KEY (`contactID`),
  ADD KEY `DriverID` (`DriverID`);

--
-- Indexes for table `driver_messages`
--
ALTER TABLE `driver_messages`
  ADD PRIMARY KEY (`messageID`),
  ADD KEY `DriverID` (`DriverID`);

--
-- Indexes for table `driver_ratings`
--
ALTER TABLE `driver_ratings`
  ADD PRIMARY KEY (`ratingID`),
  ADD UNIQUE KEY `unique_ride_rating` (`rideID`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `parcel`
--
ALTER TABLE `parcel`
  ADD PRIMARY KEY (`parcel`),
  ADD KEY `idx_parcel_status` (`parcel_status`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `ride_id` (`ride_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `ride_id` (`ride_id`);

--
-- Indexes for table `ride`
--
ALTER TABLE `ride`
  ADD PRIMARY KEY (`rideID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `DriverID` (`DriverID`),
  ADD KEY `staffID` (`staffID`),
  ADD KEY `idx_ride_status` (`ride_status`),
  ADD KEY `idx_ride_student` (`studentID`),
  ADD KEY `idx_ride_driver` (`DriverID`),
  ADD KEY `idx_ride_pending` (`ride_status`,`DriverID`);

--
-- Indexes for table `ride_passenger_verification`
--
ALTER TABLE `ride_passenger_verification`
  ADD PRIMARY KEY (`verfication_id`),
  ADD UNIQUE KEY `unique_ride_verification` (`rideID`);

--
-- Indexes for table `safety_pin`
--
ALTER TABLE `safety_pin`
  ADD PRIMARY KEY (`pin_id`),
  ADD UNIQUE KEY `unique_user_pin` (`user_type`,`user_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffID`),
  ADD UNIQUE KEY `staffEmail` (`staffEmail`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studentID`),
  ADD UNIQUE KEY `studentEmail` (`studentEmail`);

--
-- Indexes for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `registration_no` (`registration_no`),
  ADD KEY `DriverID` (`DriverID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `campus_location`
--
ALTER TABLE `campus_location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `DriverID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `driver_emergency_contacts`
--
ALTER TABLE `driver_emergency_contacts`
  MODIFY `contactID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_messages`
--
ALTER TABLE `driver_messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_ratings`
--
ALTER TABLE `driver_ratings`
  MODIFY `ratingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parcel`
--
ALTER TABLE `parcel`
  MODIFY `parcel` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ride`
--
ALTER TABLE `ride`
  MODIFY `rideID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `ride_passenger_verification`
--
ALTER TABLE `ride_passenger_verification`
  MODIFY `verfication_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `safety_pin`
--
ALTER TABLE `safety_pin`
  MODIFY `pin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
