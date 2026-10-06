-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 01:44 PM
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
-- Database: `campus_cab_db`
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
(10, 'Driver10', 'Surname10', 'driver10@gmail.com', '600000010', 'Active', 'LIC0010', NULL, NULL, 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL),
(11, 'Sisipho', 'Fubesi', 'sisiphofubesi@gmail.com', '0867876567', 'Active', '1234567876543', 'Toyota', 'focus', 'Unavailable', '$2y$10$VPTp48wyIVU4TJqj.CI8J.e/pFUvZ9r7nyMjZu8jAQrTZSycAFzoe', 'EC LUG 123', 'uploads/drivers/driver_1790947970_4794.png');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_ratings`
--

INSERT INTO `driver_ratings` (`ratingID`, `rideID`, `studentID`, `staff_id`, `passenger_type`, `DriverID`, `rating`, `comment`, `rated_at`) VALUES
(1, 26, 224108363, NULL, 'student', 11, 5, 'nice ride', '2026-10-03 18:38:16'),
(2, 27, 224108363, NULL, 'student', 11, 5, 'good', '2026-10-03 19:27:24'),
(3, 33, 224108363, NULL, 'student', 11, 5, 'good driver', '2026-10-05 22:01:01');

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

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`location_id`, `ride_id`, `driver_id`, `latitude`, `longitude`, `recorded_at`, `request_type`) VALUES
(3, NULL, 11, -32.7851417, 26.8449052, '2026-10-03 18:19:42', 'availability'),
(4, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:32', 'availability'),
(5, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:32', 'availability'),
(6, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:33', 'availability'),
(7, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:33', 'availability'),
(8, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:33', 'availability'),
(9, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:33', 'availability'),
(10, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:33', 'availability'),
(11, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:33', 'availability'),
(12, NULL, 11, -32.7851216, 26.8448999, '2026-10-03 18:21:33', 'availability'),
(13, NULL, 11, -32.7849155, 26.8447194, '2026-10-03 18:41:22', 'availability'),
(14, NULL, 11, -32.7849155, 26.8447194, '2026-10-03 18:41:25', 'availability'),
(15, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:41:37', 'availability'),
(16, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:41:43', 'availability'),
(17, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:41:44', 'availability'),
(18, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:41:59', 'availability'),
(19, NULL, 11, -32.7851213, 26.8449001, '2026-10-03 18:43:10', 'availability'),
(20, NULL, 11, -32.7851213, 26.8449001, '2026-10-03 18:43:10', 'availability'),
(21, NULL, 11, -32.7851213, 26.8449001, '2026-10-03 18:43:10', 'availability'),
(22, NULL, 11, -32.7851213, 26.8449001, '2026-10-03 18:43:11', 'availability'),
(23, NULL, 11, -32.7851213, 26.8449001, '2026-10-03 18:43:11', 'availability'),
(24, NULL, 11, -32.7851213, 26.8449001, '2026-10-03 18:43:11', 'availability'),
(25, NULL, 11, -32.7851213, 26.8449001, '2026-10-03 18:43:11', 'availability'),
(26, NULL, 11, -32.7851213, 26.8449001, '2026-10-03 18:43:13', 'availability'),
(27, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 18:44:13', 'availability'),
(28, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 18:44:13', 'availability'),
(29, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 18:44:13', 'availability'),
(30, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 18:44:13', 'availability'),
(31, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 18:44:14', 'availability'),
(32, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 18:44:14', 'availability'),
(33, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:08', 'availability'),
(34, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:08', 'availability'),
(35, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:08', 'availability'),
(36, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:08', 'availability'),
(37, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:08', 'availability'),
(38, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:09', 'availability'),
(39, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:09', 'availability'),
(40, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:09', 'availability'),
(41, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:14', 'availability'),
(42, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 18:46:24', 'availability'),
(43, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:46:42', 'availability'),
(44, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:46:43', 'availability'),
(45, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:46:59', 'availability'),
(46, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:47:04', 'availability'),
(47, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:47:21', 'availability'),
(48, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:47:22', 'availability'),
(49, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:47:28', 'availability'),
(50, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:47:45', 'availability'),
(51, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:47:48', 'availability'),
(52, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:47:53', 'availability'),
(53, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:47:54', 'availability'),
(54, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(55, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(56, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(57, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(58, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(59, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(60, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(61, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(62, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(63, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:51:49', 'availability'),
(64, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:52:05', 'availability'),
(65, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 18:52:07', 'availability'),
(66, NULL, 11, -32.7851476, 26.8449129, '2026-10-03 19:21:32', 'availability'),
(67, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 19:42:42', 'availability'),
(68, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(69, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(70, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(71, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(72, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(73, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(74, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(75, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(76, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(77, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(78, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(79, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(80, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(81, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(82, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(83, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:39', 'availability'),
(84, NULL, 11, -32.7850303, 26.8447987, '2026-10-03 19:47:41', 'availability'),
(85, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 19:48:37', 'availability'),
(86, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 19:48:37', 'availability'),
(87, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 19:48:37', 'availability'),
(88, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 19:48:38', 'availability'),
(89, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 19:48:38', 'availability'),
(90, NULL, 11, -32.7852042, 26.8449567, '2026-10-03 19:48:41', 'availability'),
(91, NULL, 11, -32.7855748, 26.8462112, '2026-10-05 20:47:56', 'availability'),
(92, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:49:19', 'availability'),
(93, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:49:19', 'availability'),
(94, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:49:19', 'availability'),
(95, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:49:19', 'availability'),
(96, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:49:19', 'availability'),
(97, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:49:19', 'availability'),
(98, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:49:19', 'availability'),
(99, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:49:19', 'availability'),
(100, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(101, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(102, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(103, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(104, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(105, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(106, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(107, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(108, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(109, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(110, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:13', 'availability'),
(111, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:14', 'availability'),
(112, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 20:54:14', 'availability'),
(113, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(114, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(115, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(116, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(117, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(118, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(119, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(120, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(121, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(122, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(123, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(124, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(125, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(126, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(127, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(128, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(129, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(130, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(131, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:07:39', 'availability'),
(132, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:50', 'availability'),
(133, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:50', 'availability'),
(134, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:50', 'availability'),
(135, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:50', 'availability'),
(136, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:50', 'availability'),
(137, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:50', 'availability'),
(138, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:51', 'availability'),
(139, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:51', 'availability'),
(140, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:51', 'availability'),
(141, NULL, 11, -32.7855157, 26.8462065, '2026-10-05 21:10:52', 'availability'),
(142, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:51:58', 'availability'),
(143, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:52:03', 'availability'),
(144, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:53:30', 'availability'),
(145, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(146, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(147, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(148, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(149, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(150, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(151, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(152, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(153, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(154, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(155, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(156, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:09', 'availability'),
(157, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 21:59:10', 'availability'),
(158, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:10:45', 'availability'),
(159, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(160, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(161, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(162, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(163, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(164, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(165, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(166, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(167, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(168, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:30', 'availability'),
(169, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:18:31', 'availability'),
(170, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:21:46', 'availability'),
(171, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:22:46', 'availability'),
(172, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:22:59', 'availability'),
(173, NULL, 11, -32.7855534, 26.8462110, '2026-10-05 22:23:08', 'availability'),
(174, NULL, 11, -32.7855534, 26.8462110, '2026-10-05 22:23:19', 'availability'),
(175, NULL, 11, -32.7855262, 26.8462099, '2026-10-05 22:28:02', 'availability'),
(176, NULL, 11, -32.7855262, 26.8462099, '2026-10-05 22:28:12', 'availability'),
(177, NULL, 11, -32.7855262, 26.8462099, '2026-10-05 22:28:12', 'availability'),
(178, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:29:01', 'availability'),
(179, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:29:05', 'availability'),
(180, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:30:03', 'availability'),
(181, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:30:03', 'availability'),
(182, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:30:04', 'availability'),
(183, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:30:04', 'availability'),
(184, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:30:04', 'availability'),
(185, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:30:07', 'availability'),
(186, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:30:21', 'availability'),
(187, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:37:46', 'availability'),
(188, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:37:50', 'availability'),
(189, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:37:56', 'availability'),
(190, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:38:04', 'availability'),
(191, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:38:13', 'availability'),
(192, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:39:13', 'availability'),
(193, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:39:13', 'availability'),
(194, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:39:13', 'availability'),
(195, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:39:13', 'availability'),
(196, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:39:13', 'availability'),
(197, NULL, 11, -32.7855534, 26.8462099, '2026-10-05 22:39:13', 'availability'),
(198, NULL, 11, -32.7855373, 26.8462085, '2026-10-05 22:42:51', 'availability'),
(199, NULL, 11, -32.7855373, 26.8462085, '2026-10-05 22:42:54', 'availability'),
(200, NULL, 11, -32.7860633, 26.8519460, '2026-10-06 11:27:40', 'availability'),
(201, NULL, 11, -32.7860633, 26.8519460, '2026-10-06 11:27:48', 'availability'),
(202, NULL, 11, -32.7859974, 26.8519444, '2026-10-06 11:28:13', 'availability');

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `student_id`, `staff_id`, `driver_id`, `ride_id`, `parcel_id`, `notification_type`, `message`, `is_read`, `created_at`) VALUES
(1, 224108363, NULL, NULL, NULL, 2, 'parcel_accepted', 'Your parcel request has been accepted by a driver.', 0, '2026-10-02 15:44:57'),
(2, 224108363, NULL, NULL, NULL, 2, 'parcel_picked_up', 'Your parcel has been picked up by the driver.', 0, '2026-10-02 15:45:39'),
(3, 224108363, NULL, NULL, NULL, 2, 'parcel_in_transit', 'Your parcel is now in transit to the destination.', 0, '2026-10-02 15:45:50'),
(4, 224108363, NULL, NULL, NULL, 2, 'parcel_delivered', 'Your parcel has been delivered successfully.', 0, '2026-10-02 15:47:21'),
(5, 224108363, NULL, NULL, 26, NULL, 'ride_requested', 'Ride requested. Waiting for a driver to accept your request.', 0, '2026-10-02 15:49:04'),
(6, 224108363, NULL, NULL, 26, NULL, 'ride_accepted', 'Your ride has been accepted by a driver.', 0, '2026-10-02 15:49:17'),
(7, NULL, 2, NULL, 29, NULL, 'ride_requested', 'Ride requested. Waiting for a driver to accept your request.', 0, '2026-10-05 21:13:24'),
(8, 224108363, NULL, NULL, 30, NULL, 'ride_requested', 'Ride requested. Waiting for a driver to accept your request.', 0, '2026-10-05 21:32:19'),
(9, 224108363, NULL, NULL, 31, NULL, 'ride_requested', 'Ride requested. Waiting for a driver to accept your request.', 0, '2026-10-05 21:45:14'),
(10, 224108363, NULL, NULL, 32, NULL, 'ride_requested', 'Ride requested. Waiting for a driver to accept your request.', 0, '2026-10-05 21:54:01'),
(11, 224108363, NULL, NULL, 33, NULL, 'ride_requested', 'Ride requested. Waiting for a driver to accept your request.', 0, '2026-10-05 21:58:51');

-- --------------------------------------------------------

--
-- Table structure for table `parcel`
--

CREATE TABLE `parcel` (
  `parcel` int(11) NOT NULL,
  `studentID` int(11) DEFAULT NULL,
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

--
-- Dumping data for table `parcel`
--

INSERT INTO `parcel` (`parcel`, `studentID`, `parcel_description`, `parcel_size`, `recipient_name`, `recipient_contact`, `pickup_location`, `destination`, `estimated_price`, `DriverID`, `requested_at`, `completed_at`, `parcel_status`, `delivery_method`, `staff_id`) VALUES
(2, 224108363, 'small box with earphones', 'Small', 'Zulu', '0714452900', 'Main Gate', 'Student village 3.1', 20.00, 11, '2026-10-02 15:42:52', '2026-10-02 15:47:21', 'Delivered', 'On-Campus', NULL),
(3, 224108363, 'box', 'Small', 'Zulu', '0714452900', 'Main Gate', 'Student village 3.2', 20.00, 11, '2026-10-03 18:41:07', '2026-10-03 18:47:53', 'Delivered', 'On-Campus', NULL),
(4, 224108363, 'box', 'Small', 'Zulu', '0714452900', 'Courier Guy Offices', 'Student village 3.3', 20.00, 11, '2026-10-05 22:22:25', '2026-10-05 22:29:04', 'Delivered', 'On-Campus', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parcel_driver_ratings`
--

CREATE TABLE `parcel_driver_ratings` (
  `rating_id` int(11) NOT NULL,
  `parcel` int(11) NOT NULL,
  `DriverID` int(11) NOT NULL,
  `studentID` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `rated_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `parcel_payment`
--

CREATE TABLE `parcel_payment` (
  `payment_id` int(11) NOT NULL,
  `parcel` int(11) NOT NULL,
  `item_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'Pending',
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parcel_payment`
--

INSERT INTO `parcel_payment` (`payment_id`, `parcel`, `item_cost`, `delivery_fee`, `total_amount`, `payment_method`, `payment_status`, `paid_at`) VALUES
(1, 3, 20.00, 0.00, 20.00, 'card', 'Paid', '2026-10-03 18:48:31'),
(2, 2, 20.00, 0.00, 20.00, 'Cash', 'Paid', '2026-10-05 22:12:12'),
(3, 4, 20.00, 0.00, 20.00, 'Card', 'Paid', '2026-10-05 22:29:32');

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
(0, 26, 0.00, 36.86, 36.86, 'EFT', 'Paid', '2026-10-03 18:38:48'),
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
(17, 100, 0.00, 34.95, 34.95, 'Card', 'Paid', '2026-09-23 14:39:15'),
(18, 27, 0.00, 47.85, 47.85, 'Card', 'Paid', '2026-10-03 19:32:33'),
(19, 33, 0.00, 25.15, 25.15, 'Card', 'Paid', '2026-10-05 22:01:22');

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
(5, 'Student', 9, 'O Langa', 'Card', '6789', 8, 2027, '2026-09-23 14:39:15'),
(6, 'Student', 224108363, 'O Langa', 'Visa', '4242', 12, 2040, '2026-10-03 19:32:33');

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
  `studentID` int(11) DEFAULT NULL,
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

--
-- Dumping data for table `ride`
--

INSERT INTO `ride` (`rideID`, `studentID`, `staff_id`, `passenger_type`, `DriverID`, `pickup_address`, `pickup_latitude`, `pickup_longitude`, `destination_address`, `destination_latitude`, `destination_longitude`, `requested_at`, `accepted_at`, `started_at`, `Completed_at`, `ride_status`, `cancellation_reason`, `cancelled_at`, `estimated_distance_km`, `estimated_duration_min`, `estimated_price`, `request_type`, `parcel_details`, `driver_earning`, `campus_commission`, `driver_arrived_at`, `driver_arrived_latitude`, `driver_arrived_longitude`, `arrival_distance`) VALUES
(26, 224108363, NULL, 'student', 11, 'Spar, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.78658520, 26.83915700, 'University of Fort Hare Main Gate, Old Hogsback Road, Ntselamanzi, Nkonkobe Ward 11, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.7882360, 26.8462298, '2026-10-02 15:49:02', '2026-10-02 15:49:17', NULL, '2026-10-03 18:37:29', 'Completed', NULL, NULL, 0.69, 2, 36.86, 'ride', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL),
(27, 224108363, NULL, 'student', 11, 'Spar, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.78658520, 26.83915700, 'University of Fort Hare, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.7848724, 26.8501634, '2026-10-03 19:25:35', '2026-10-03 19:25:41', '2026-10-03 19:26:49', '2026-10-03 19:26:57', 'Completed', NULL, NULL, 1.05, 2, 47.85, 'ride', NULL, 38.28, 9.57, NULL, NULL, NULL, NULL),
(29, NULL, 2, 'staff', 11, 'Spar, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.78658520, 26.83915700, 'University of Fort Hare, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.7848724, 26.8501634, '2026-10-05 21:13:24', '2026-10-05 21:13:33', NULL, NULL, 'Accepted', NULL, NULL, 1.05, 2, 27.85, 'ride', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL),
(30, 224108363, NULL, 'student', NULL, 'Spar, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.78658520, 26.83915700, 'Spar, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.7865852, 26.8391570, '2026-10-05 21:32:19', NULL, NULL, NULL, 'Cancelled', 'changed my mind', '2026-10-05 21:44:31', 0.00, 2, 20.00, 'ride', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL),
(31, 224108363, NULL, 'student', 11, 'Spar, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.78658520, 26.83915700, 'University of Fort Hare Main Gate, Old Hogsback Road, Ntselamanzi, Nkonkobe Ward 11, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.7882360, 26.8462298, '2026-10-05 21:45:14', '2026-10-05 21:52:07', NULL, NULL, 'Cancelled', 'njee', '2026-10-05 21:52:52', 0.69, 2, 25.15, 'ride', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL),
(32, 224108363, NULL, 'student', NULL, 'Student village 1.1', -32.78300000, 26.84300000, 'Victoria Hospital, Kuntselamanzi Road, Ntselamanzi, Nkonkobe Ward 15, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.7745938, 26.8468789, '2026-10-05 21:54:01', NULL, NULL, NULL, 'Cancelled', 'na', '2026-10-05 21:57:58', 1.00, 2, 27.52, 'ride', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL),
(33, 224108363, NULL, 'student', 11, 'Spar, Thompson Street, Nkonkobe Ward 6, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.78658520, 26.83915700, 'University of Fort Hare Main Gate, Old Hogsback Road, Ntselamanzi, Nkonkobe Ward 11, Alice, Nkonkobe Local Municipality, Amathole District Municipality, Eastern Cape, 5700, South Africa', -32.7882360, 26.8462298, '2026-10-05 21:58:51', '2026-10-05 21:59:15', '2026-10-05 21:59:21', '2026-10-05 22:00:19', 'Completed', NULL, NULL, 0.69, 2, 25.15, 'ride', NULL, 20.12, 5.03, NULL, NULL, NULL, NULL);

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
(1, 27, 'Verified', '2026-10-03 19:26:49', 11, '2026-10-03 17:26:49'),
(2, 33, 'Verified', '2026-10-05 21:59:21', 11, '2026-10-05 19:59:21');

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
(1, 'student', 224108363, '$2y$10$7g4TPy/ZV/xgBQ5PMBIZTu7gzuXx22O7avJPbpND0YXqQ4r9FVNrO', '2026-10-02 13:44:10', '2026-10-05 19:58:51');

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

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `surname`, `staff_name`, `staff_email`, `staff_phone`, `staffAccount_status`, `password`) VALUES
(1, 'Madini', 'Liyabona', '20456@ufh.ac.za', '0867565445', 'Inactive', '$2y$10$HOYlMxe.eyvxQ8Yxk/a/0eI6L.J8TEdhQNufp05vt63w7bCxPwTsC'),
(2, 'Majija', 'Bukho', '20245@ufh.ac.za', '0867876567', 'Active', '$2y$10$0xb3/8/6USeEzYduDxt8LO6vNe7JMUnuDA6YR1by5CHsTn5U/KSly');

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
(7, 'Wendy', 'Booi', 'WendyB@gmail.com', '0606030662', 'Active', '$2y$10$Flxbp2qKEZo5bacv2j6IYO7bFqqhRQxl6qi4K.JG3n2V2tX6jxXSG'),
(224108363, 'Oyisa', 'Langa', '224108363@ufh.ac.za', '0635572900', 'Active', '$2y$10$OmYvaDZXMHmi/2nIZfJpx.vHtMkaF1YrDN4OGxaL.zjIuJgBYBzfW');

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
(5, 10, 'EC 321-654', 'Ford', 'Ranger', 'Black', 4),
(6, 11, 'EC LUG 123', 'Toyota', 'focus', 'white', 4);

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
-- Indexes for table `parcel_driver_ratings`
--
ALTER TABLE `parcel_driver_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_parcel_driver_rating` (`parcel`),
  ADD KEY `fk_pdr_driver` (`DriverID`),
  ADD KEY `fk_pdr_student` (`studentID`),
  ADD KEY `fk_pdr_staff` (`staff_id`);

--
-- Indexes for table `parcel_payment`
--
ALTER TABLE `parcel_payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `unique_parcel_payment` (`parcel`);

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
  MODIFY `DriverID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `ratingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=203;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `parcel`
--
ALTER TABLE `parcel`
  MODIFY `parcel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parcel_driver_ratings`
--
ALTER TABLE `parcel_driver_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parcel_payment`
--
ALTER TABLE `parcel_payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `rideID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `ride_passenger_verification`
--
ALTER TABLE `ride_passenger_verification`
  MODIFY `verfication_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `safety_pin`
--
ALTER TABLE `safety_pin`
  MODIFY `pin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=224108364;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Constraints for table `parcel_driver_ratings`
--
ALTER TABLE `parcel_driver_ratings`
  ADD CONSTRAINT `fk_pdr_driver` FOREIGN KEY (`DriverID`) REFERENCES `driver` (`DriverID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pdr_parcel` FOREIGN KEY (`parcel`) REFERENCES `parcel` (`parcel`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pdr_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pdr_student` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `parcel_payment`
--
ALTER TABLE `parcel_payment`
  ADD CONSTRAINT `fk_parcel_payment_parcel` FOREIGN KEY (`parcel`) REFERENCES `parcel` (`parcel`) ON DELETE CASCADE ON UPDATE CASCADE;

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
