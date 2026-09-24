-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2026 at 12:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campus-cab`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(11) NOT NULL,
  `adminFname` varchar(255) NOT NULL DEFAULT 'Not Null',
  `adminLname` varchar(255) NOT NULL DEFAULT 'Not Null',
  `admin_email` varchar(255) NOT NULL DEFAULT 'Not Null',
  `adminPWD` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `adminFname`, `adminLname`, `admin_email`, `adminPWD`) VALUES
(1, 'Sabelo', 'Mqushwane', 'sabelo@gmail.com', ''),
(2, 'Sabelo', 'Mqushwane', 'sabelo@gmail.com', 'Sabelo@123');

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
(1, 'Student village 1.1', -32.7830000, 26.8430000),
(2, 'Student village 1.2', -32.7810000, 26.8400000),
(3, 'Student village 1.3', -32.7845000, 26.8410000),
(4, 'Student village 2.1', -32.7855000, 26.8465000),
(5, 'Student village 2.2', -32.7850000, 26.8460000),
(6, 'Student village 2.3', -32.7845000, 26.8455000),
(7, 'Student village 3.1', -32.7838000, 26.8442000),
(8, 'Student village 3.2', -32.7862000, 26.8420000),
(9, 'Student village 3.3', -32.7800000, 26.8390000),
(10, 'Student village 4.1', -32.7833000, 26.8448000);

-- --------------------------------------------------------

--
-- Table structure for table `complaint`
--

CREATE TABLE `complaint` (
  `complaint_id` int(11) NOT NULL,
  `ride_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `DriverID` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `status` enum('New','Investigating','Resolved','Dismissed') DEFAULT 'New',
  `resolution` varchar(255) DEFAULT NULL,
  `filed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `ride_id` int(11) DEFAULT NULL,
  `complaint_type` varchar(100) NOT NULL,
  `complaint_text` text NOT NULL,
  `complaint_status` varchar(30) DEFAULT 'Pending',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `driverAccount_Status` enum('Active','Inactive') DEFAULT 'Active',
  `licence_number` varchar(30) NOT NULL,
  `vehicle_make` varchar(30) DEFAULT NULL,
  `vehicle_model` varchar(30) DEFAULT NULL,
  `availability_status` enum('Available','Unavailable') DEFAULT 'Unavailable',
  `password` varchar(255) NOT NULL DEFAULT '',
  `vehicle_registration` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`DriverID`, `DriverFname`, `DriverLname`, `DriverEmail`, `DriverPhoneNo`, `driverAccount_Status`, `licence_number`, `vehicle_make`, `vehicle_model`, `availability_status`, `password`, `vehicle_registration`, `profile_picture`) VALUES
(1, 'Driver1', 'Surname1', 'driver1@gmail.com', '600000001', 'Active', 'LIC0001', NULL, NULL, 'Unavailable', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(2, 'Driver2', 'Surname2', 'driver2@gmail.com', '600000002', 'Active', 'LIC0002', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(3, 'Driver3', 'Surname3', 'driver3@gmail.com', '600000003', 'Active', 'LIC0003', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(4, 'Driver4', 'Surname4', 'driver4@gmail.com', '600000004', 'Active', 'LIC0004', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(5, 'Driver5', 'Surname5', 'driver5@gmail.com', '600000005', 'Active', 'LIC0005', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(6, 'Driver6', 'Surname6', 'driver6@gmail.com', '600000006', 'Active', 'LIC0006', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(7, 'Driver7', 'Surname7', 'driver7@gmail.com', '600000007', 'Active', 'LIC0007', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(8, 'Driver8', 'Surname8', 'driver8@gmail.com', '600000008', 'Active', 'LIC0008', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(9, 'Driver9', 'Surname9', 'driver9@gmail.com', '600000009', 'Active', 'LIC0009', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(10, 'Driver10', 'Surname10', 'driver10@gmail.com', '600000010', 'Active', 'LIC0010', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `driver_claim`
--

CREATE TABLE `driver_claim` (
  `claim_id` int(11) NOT NULL,
  `DriverID` int(11) NOT NULL,
  `ride_id` int(11) DEFAULT NULL,
  `claim_type` varchar(100) NOT NULL,
  `claim_category` enum('Covered','Discretionary','Not Covered') NOT NULL DEFAULT 'Discretionary',
  `amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Resolved') DEFAULT 'Pending',
  `resolution_note` varchar(255) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_emergency_alerts`
--

CREATE TABLE `driver_emergency_alerts` (
  `alertID` int(11) NOT NULL,
  `DriverID` int(11) NOT NULL,
  `rideID` int(11) DEFAULT NULL,
  `parcelID` int(11) DEFAULT NULL,
  `alert_type` varchar(50) NOT NULL DEFAULT 'Emergency',
  `alert_message` varchar(255) NOT NULL,
  `alert_status` enum('Active','Resolved') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `rated_at` datetime NOT NULL DEFAULT curtime()
) ;

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
  `recorded_at` datetime NOT NULL,
  `request_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `message_` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `ride_id` int(11) DEFAULT NULL,
  `parcel_id` int(11) DEFAULT NULL,
  `notification_type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT curtime()
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
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passenger_safety`
--

CREATE TABLE `passenger_safety` (
  `pin_id` int(11) NOT NULL,
  `user_type` enum('Student','Staff') NOT NULL,
  `userID` int(11) NOT NULL,
  `passenger_pin` varchar(255) NOT NULL,
  `pin_varification` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
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

INSERT INTO `payment` (`payment_id`, `ride_id`, `item_cost`, `delivery_fee`, `total_amount`, `payment_method`, `payment_status`, `paid_at`) VALUES
(1, 1, 0.00, 35.00, 35.00, 'Cash', 'Paid', '2026-08-16 08:10:00'),
(2, 9, 0.00, 10.86, 10.86, 'Cash', 'Paid', '2026-08-25 12:46:04'),
(3, 20, 0.00, 14.41, 14.41, 'Cash', 'Paid', '2026-08-25 12:47:08'),
(4, 21, 0.00, 13.93, 13.93, 'Cash', 'Paid', '2026-08-25 14:26:48'),
(5, 22, 0.00, 11.92, 11.92, 'Cash', 'Paid', '2026-08-25 19:12:38'),
(6, 23, 0.00, 14.70, 14.70, 'Cash', 'Paid', '2026-08-25 19:23:46'),
(7, 29, 0.00, 14.17, 14.17, 'Cash', 'Paid', '2026-09-01 18:59:20'),
(8, 34, 0.00, 14.87, 14.87, 'Cash', 'Paid', '2026-09-01 20:16:33'),
(9, 39, 0.00, 10.45, 10.45, 'Cash', 'Paid', '2026-09-05 12:47:20'),
(10, 62, 0.00, 37.34, 37.34, 'Cash', 'Paid', '2026-09-18 09:18:22'),
(11, 60, 0.00, 65.01, 65.01, 'Cash', 'Paid', '2026-09-18 09:19:06'),
(12, 85, 0.00, 32.11, 32.11, 'Card', 'Paid', '2026-09-22 07:59:40'),
(13, 86, 0.00, 33.68, 33.68, 'Card', 'Paid', '2026-09-22 12:02:55'),
(14, 96, 0.00, 33.58, 33.58, 'Card', 'Paid', '2026-09-22 13:38:06'),
(15, 98, 0.00, 40.68, 40.68, 'Card', 'Paid', '2026-09-22 13:50:29'),
(16, 99, 0.00, 60.68, 60.68, 'Card', 'Paid', '2026-09-22 21:28:21'),
(17, 100, 0.00, 34.95, 34.95, 'Card', 'Paid', '2026-09-23 14:39:15');

-- --------------------------------------------------------

--
-- Table structure for table `payment_method`
--

CREATE TABLE `payment_method` (
  `payment_method_id` int(11) NOT NULL,
  `requester_type` enum('Student','Staff') NOT NULL,
  `requester_id` int(11) NOT NULL,
  `cardholder_name` varchar(100) NOT NULL,
  `card_brand` varchar(20) NOT NULL,
  `card_last4` char(4) NOT NULL,
  `expiry_month` tinyint(2) NOT NULL,
  `expiry_year` smallint(4) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_method`
--

INSERT INTO `payment_method` (`payment_method_id`, `requester_type`, `requester_id`, `cardholder_name`, `card_brand`, `card_last4`, `expiry_month`, `expiry_year`, `updated_at`) VALUES
(1, 'Student', 7, 'T.Mthembu', 'Card', '5455', 9, 2027, '2026-09-22 07:59:40'),
(2, 'Student', 11, 'T.Mthembu', 'Card', '5678', 8, 2027, '2026-09-22 12:02:55'),
(3, 'Staff', 3, 'T.Mthembu', 'Card', '1234', 8, 2027, '2026-09-22 13:38:06'),
(4, 'Student', 12, 'L Rungqu', 'Card', '4566', 8, 2027, '2026-09-22 13:50:28'),
(5, 'Student', 9, 'O Langa', 'Card', '6789', 8, 2027, '2026-09-23 14:39:15');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `rating_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `student_rating` tinyint(4) DEFAULT NULL CHECK (`student_rating` between 1 and 5),
  `staff_rating` tinyint(4) DEFAULT NULL,
  `driver_rating` tinyint(4) DEFAULT NULL CHECK (`driver_rating` between 1 and 5),
  `comments` varchar(255) DEFAULT NULL,
  `rated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `DriverID` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `rated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ride`
--

CREATE TABLE `ride` (
  `rideID` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `passenger_type` enum('student','staff') NOT NULL DEFAULT 'student',
  `DriverID` int(11) DEFAULT NULL,
  `pickup_address` varchar(250) DEFAULT NULL,
  `pickup_latitude` decimal(10,8) DEFAULT NULL,
  `pickup_longitude` decimal(11,8) DEFAULT NULL,
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
  `request_type` enum('ride','parcel') NOT NULL,
  `parcel_details` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `driver_earning` decimal(10,2) NOT NULL DEFAULT 0.00,
  `campus_commission` decimal(10,2) NOT NULL DEFAULT 0.00,
  `driver_arrived_at` datetime DEFAULT NULL,
  `driver_arrived_latitude` decimal(10,8) DEFAULT NULL,
  `driver_arrived_longitude` decimal(11,8) DEFAULT NULL,
  `arrival_distance` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `staff_name` varchar(50) NOT NULL,
  `staff_email` varchar(100) NOT NULL,
  `staff_phone` varchar(15) NOT NULL,
  `staffAccount_status` enum('Active','Inactive') DEFAULT 'Inactive',
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` int(11) NOT NULL,
  `studentFname` varchar(50) NOT NULL,
  `studentLname` varchar(50) NOT NULL,
  `studentEmail` varchar(50) NOT NULL,
  `studentPhoneNo` varchar(15) NOT NULL,
  `studentAccount_status` enum('Active','Inactive') DEFAULT 'Active',
  `password` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studentID`, `studentFname`, `studentLname`, `studentEmail`, `studentPhoneNo`, `studentAccount_status`, `password`) VALUES
(1, 'Bongani', 'Mthembu', 'bonganimthembu@gmail.com', '632423457', 'Active', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC'),
(2, 'Noluthando', 'Ndlovu', 'noluthandondlovu@gmail.com', '632076543', 'Active', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC'),
(3, 'Sipho', 'Xaba', 'siphoxaba@gmail.com', '714567890', 'Active', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC'),
(4, 'Zoleka', 'Dlamini', 'zolekadlamini@gmail.com', '723456789', 'Inactive', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC'),
(5, 'Lwazi', 'Mokoena', 'lwazimokoena@gmail.com', '612345678', 'Active', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC'),
(6, 'Nalo', 'Kolo', 'Sihlem@gmail.com', '0606030662', 'Active', '$2y$10$KaGiO5VzB/DcXFc6r6e6ouxlKaw5n3QtbhlDLuDbNA.VemkCZrniu'),
(7, 'Wendy', 'Booi', 'WendyB@gmail.com', '0606030662', 'Active', '$2y$10$Flxbp2qKEZo5bacv2j6IYO7bFqqhRQxl6qi4K.JG3n2V2tX6jxXSG');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `log_id` int(11) NOT NULL,
  `actor_type` enum('Admin','Driver','Student','Staff') NOT NULL,
  `actor_name` varchar(100) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` varchar(50) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Dumping data for table `vehicle`
--

INSERT INTO `vehicle` (`vehicle_id`, `DriverID`, `registration_no`, `make`, `model`, `colour`, `capacity`) VALUES
(1, 6, 'CA 123-456', 'Toyota', 'Quantum', 'White', 15),
(2, 7, 'EC 987-654', 'Volkswagen', 'Polo', 'Silver', 4),
(3, 8, 'GP 456-789', 'Toyota', 'Corolla', 'White', 4),
(4, 9, 'CA 789-123', 'Nissan', 'NV350', 'White', 14),
(5, 10, 'EC 321-654', 'Ford', 'Ranger', 'Black', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`);

--
-- Indexes for table `campus_location`
--
ALTER TABLE `campus_location`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`DriverID`),
  ADD UNIQUE KEY `DriverEmail` (`DriverEmail`),
  ADD UNIQUE KEY `licence_number` (`licence_number`);

--
-- Indexes for table `driver_emergency_alerts`
--
ALTER TABLE `driver_emergency_alerts`
  ADD PRIMARY KEY (`alertID`),
  ADD KEY `DriverID` (`DriverID`),
  ADD KEY `rideID` (`rideID`),
  ADD KEY `parcelID` (`parcelID`);

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
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `ride_id` (`ride_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `ride_id` (`ride_id`),
  ADD KEY `student_id` (`studentID`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `parcel`
--
ALTER TABLE `parcel`
  ADD PRIMARY KEY (`parcel`),
  ADD KEY `fk_parcel_student` (`studentID`);

--
-- Indexes for table `passenger_safety`
--
ALTER TABLE `passenger_safety`
  ADD PRIMARY KEY (`pin_id`),
  ADD UNIQUE KEY `unique_passenger_pin` (`user_type`,`userID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `ride_id` (`ride_id`);

--
-- Indexes for table `payment_method`
--
ALTER TABLE `payment_method`
  ADD PRIMARY KEY (`payment_method_id`),
  ADD UNIQUE KEY `one_method_per_requester` (`requester_type`,`requester_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `ride_id` (`ride_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_ride_rating` (`ride_id`);

--
-- Indexes for table `ride`
--
ALTER TABLE `ride`
  ADD PRIMARY KEY (`rideID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `DriverID` (`DriverID`);

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
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `staffEmail` (`staff_email`);

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
-- AUTO_INCREMENT for table `campus_location`
--
ALTER TABLE `campus_location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `DriverID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `driver_emergency_alerts`
--
ALTER TABLE `driver_emergency_alerts`
  MODIFY `alertID` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `ratingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parcel`
--
ALTER TABLE `parcel`
  MODIFY `parcel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `passenger_safety`
--
ALTER TABLE `passenger_safety`
  MODIFY `pin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ride`
--
ALTER TABLE `ride`
  MODIFY `rideID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `ride_passenger_verification`
--
ALTER TABLE `ride_passenger_verification`
  MODIFY `verfication_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `safety_pin`
--
ALTER TABLE `safety_pin`
  MODIFY `pin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `location_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `ride` (`rideID`),
  ADD CONSTRAINT `location_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`DriverID`);

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `ride` (`rideID`),
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`),
  ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`DriverID`);

--
-- Constraints for table `parcel`
--
ALTER TABLE `parcel`
  ADD CONSTRAINT `fk_parcel_student` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `ride` (`rideID`);

--
-- Constraints for table `ride`
--
ALTER TABLE `ride`
  ADD CONSTRAINT `ride_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`),
  ADD CONSTRAINT `ride_ibfk_2` FOREIGN KEY (`DriverID`) REFERENCES `driver` (`DriverID`);

--
-- Constraints for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD CONSTRAINT `vehicle_ibfk_1` FOREIGN KEY (`DriverID`) REFERENCES `driver` (`DriverID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
