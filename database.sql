-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2026 at 08:52 PM
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
(1, 'Beda Hall', -32.7830000, 26.8430000),
(2, 'Steve Biko Hall', -32.7810000, 26.8400000),
(3, 'Chris Hani Hall', -32.7845000, 26.8410000),
(4, 'Student Village', -32.7855000, 26.8465000),
(5, 'Main Library', -32.7850000, 26.8460000),
(6, 'Science Building', -32.7845000, 26.8455000),
(7, 'Admin Building', -32.7838000, 26.8442000),
(8, 'Sports Field', -32.7862000, 26.8420000),
(9, 'Main Gate', -32.7800000, 26.8390000),
(10, 'Student Centre', -32.7833000, 26.8448000);

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
  `availability_status` enum('Available','Unavailable') DEFAULT 'Unavailable',
  `password` varchar(255) NOT NULL DEFAULT '',
  `DriverIDNumber` varchar(13) DEFAULT NULL,
  `DriverGender` varchar(10) DEFAULT NULL,
  `DriverAddress` text DEFAULT NULL,
  `DriverPhoto` varchar(255) DEFAULT NULL,
  `VehicleReg` varchar(20) DEFAULT NULL,
  `VehicleModel` varchar(50) DEFAULT NULL,
  `VehiclePhoto` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vehicle_type` varchar(20) DEFAULT 'Car',
  `vehicle_color` varchar(30) DEFAULT NULL,
  `vehicle_capacity` int(11) DEFAULT 4,
  `vehicle_make` varchar(50) DEFAULT NULL,
  `phone_verified` tinyint(1) DEFAULT 0,
  `verification_otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`DriverID`, `DriverFname`, `DriverLname`, `DriverEmail`, `DriverPhoneNo`, `driverAccount_Status`, `licence_number`, `availability_status`, `password`, `DriverIDNumber`, `DriverGender`, `DriverAddress`, `DriverPhoto`, `VehicleReg`, `VehicleModel`, `VehiclePhoto`, `email_verified`, `verification_token`, `token_expiry`, `created_at`, `vehicle_type`, `vehicle_color`, `vehicle_capacity`, `vehicle_make`, `phone_verified`, `verification_otp`, `otp_expiry`) VALUES
(1, 'Anele', 'Buxoki', 'buxokianele@gmail.com', '0652328710', 'Active', 'EC 324 JP', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(2, 'Driver2', 'Surname2', 'driver2@gmail.com', '600000002', 'Active', 'LIC0002', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(3, 'Driver3', 'Surname3', 'driver3@gmail.com', '600000003', 'Active', 'LIC0003', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(4, 'Driver4', 'Surname4', 'driver4@gmail.com', '600000004', 'Active', 'LIC0004', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(5, 'Driver5', 'Surname5', 'driver5@gmail.com', '600000005', 'Active', 'LIC0005', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(6, 'Driver6', 'Surname6', 'driver6@gmail.com', '600000006', 'Active', 'LIC0006', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(7, 'Driver7', 'Surname7', 'driver7@gmail.com', '600000007', 'Active', 'LIC0007', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(8, 'Driver8', 'Surname8', 'driver8@gmail.com', '600000008', 'Active', 'LIC0008', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(9, 'Driver9', 'Surname9', 'driver9@gmail.com', '600000009', 'Active', 'LIC0009', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(10, 'Driver10', 'Surname10', 'driver10@gmail.com', '600000010', 'Active', 'LIC0010', 'Available', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 20:20:16', 'Car', NULL, 4, NULL, 0, NULL, NULL),
(14, 'Nosipho', 'makonjwa', 'makhonjwabanele01@gmail.com', '0652428564', 'Active', 'EC-1237', 'Available', '$2y$10$qNbvhiBRfkj39yEbKmUbue.t2k3jm6D2c4oqhgCQ.3HPnrwyWRPPa', '7888364865648', 'Female', 'Mkhumeni A/A, Flagstaff, Flagstaff, Eastern Cape 4810', 'uploads/drivers/driver_1789042758_6587.png', 'EC-1237', 'Corolla', 'uploads/drivers/vehicle_1789042758_7499.png', 0, 'fe2f9223e05fa53d0c81cf1b6110e040dc8c34e662436027f0ece58295a9e2db', '2026-09-11 14:19:18', '2026-09-10 12:19:18', 'Car', 'White', 4, 'Toyota', 1, NULL, '2026-09-10 14:29:18');

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
(1, 1, 6, -32.7830000, 26.8430000, '2026-08-16 08:00:00'),
(2, 1, 6, -32.7835000, 26.8438000, '2026-08-16 08:01:00');

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

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_id`, `ride_id`, `student_id`, `driver_id`, `message_`, `sent_at`) VALUES
(1, 2, 2, 7, 'I am outside Steve Biko Hall, blue jacket.', '2026-08-16 09:03:00'),
(2, 2, 2, 7, 'On my way, 2 minutes out.', '2026-08-16 09:03:30');

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
(1, 1, 0.00, 35.00, 35.00, 'Cash', 'Paid', '2026-08-16 08:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `rating_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `student_rating` tinyint(4) DEFAULT NULL CHECK (`student_rating` between 1 and 5),
  `driver_rating` tinyint(4) DEFAULT NULL CHECK (`driver_rating` between 1 and 5),
  `comments` varchar(255) DEFAULT NULL,
  `rated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`rating_id`, `ride_id`, `student_rating`, `driver_rating`, `comments`, `rated_at`) VALUES
(1, 1, 5, 5, 'Quick and friendly, thanks!', '2026-08-16 08:12:00');

-- --------------------------------------------------------

--
-- Table structure for table `ride`
--

CREATE TABLE `ride` (
  `rideID` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
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
  `estimated_price` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ride`
--

INSERT INTO `ride` (`rideID`, `studentID`, `DriverID`, `pickup_address`, `pickup_latitude`, `pickup_longitude`, `destination_address`, `destination_latitude`, `destination_longitude`, `requested_at`, `accepted_at`, `started_at`, `Completed_at`, `ride_status`, `cancellation_reason`, `cancelled_at`, `estimated_distance_km`, `estimated_duration_min`, `estimated_price`) VALUES
(1, 1, 6, 'Beda Hall', -32.7830000, 26.8430000, 'Main Library', -32.7850000, 26.8460000, '2026-08-16 07:55:00', '2026-08-16 07:56:00', '2026-08-16 08:00:00', '2026-08-16 08:10:00', 'Completed', NULL, NULL, NULL, NULL, NULL),
(2, 2, 7, 'Steve Biko Hall', -32.7810000, 26.8400000, 'Science Building', -32.7845000, 26.8455000, '2026-08-16 09:00:00', '2026-08-16 09:01:00', '2026-08-16 09:05:00', NULL, 'In Progress', NULL, NULL, NULL, NULL, NULL),
(3, 5, 1, 's.v 4.1', NULL, NULL, 'Champs', NULL, NULL, '2026-08-18 10:42:23', '2026-08-18 10:42:38', NULL, NULL, 'Accepted', NULL, NULL, NULL, NULL, NULL),
(4, 3, 9, 'Mzana', NULL, NULL, 'Boxer', NULL, NULL, '2026-08-18 13:34:44', '2026-08-18 13:35:27', NULL, NULL, 'Accepted', NULL, NULL, NULL, NULL, NULL),
(5, 3, 1, 'New Res', -32.7855000, 26.8465000, 'Student Centre', -32.7833000, 26.8448000, '2026-08-19 18:17:43', '2026-08-19 20:04:43', NULL, NULL, 'Accepted', NULL, NULL, 0.29, 2, 11.75),
(6, 3, 1, 'Admin Building', -32.7838000, 26.8442000, 'New Res', -32.7855000, 26.8465000, '2026-08-19 20:04:30', '2026-08-19 20:04:45', NULL, NULL, 'Accepted', NULL, NULL, 0.29, 2, 11.72),
(7, 3, NULL, 'Beda Hall', -32.7830000, 26.8430000, 'Sports Field', -32.7862000, 26.8420000, '2026-08-19 20:05:53', NULL, NULL, NULL, 'Requested', NULL, NULL, 0.37, 2, 12.21);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `staff_number` varchar(9) NOT NULL,
  `staff_name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `staff_email` varchar(100) NOT NULL,
  `cell_number` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `staffAccount_status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `staff_number`, `staff_name`, `surname`, `staff_email`, `cell_number`, `password`, `created_at`, `email_verified`, `verification_token`, `token_expiry`, `reset_token`, `reset_expiry`, `staffAccount_status`) VALUES
(1, '223090878', 'siphesihle', 'jiba', '223090878@ufh.ac.za', '0782567808', '$2y$10$uX5WfVMAtVVDrS6sEyg1hurPWzbP/mw.CSHk./qD5vluLs9ceE.x2', '2026-09-09 18:25:00', 1, NULL, NULL, NULL, NULL, 'Active'),
(2, '123456789', 'Anele', 'Lugayeni', 'alugayeni@ufh.ac.za', '0607512703', '$2y$10$nVH4.hhSJkL7XV2mgbHtb.oDPv9J8Kwu9U4.NRdROq7XMGIXHCN/i', '2026-09-09 19:00:24', 0, NULL, NULL, NULL, NULL, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` int(11) NOT NULL,
  `studentNumber` varchar(20) DEFAULT NULL,
  `studentFname` varchar(50) NOT NULL,
  `studentLname` varchar(50) NOT NULL,
  `studentEmail` varchar(50) NOT NULL,
  `studentPhoneNo` varchar(15) NOT NULL,
  `studentAccount_status` enum('Active','Inactive') DEFAULT 'Active',
  `studentPwd` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studentID`, `studentNumber`, `studentFname`, `studentLname`, `studentEmail`, `studentPhoneNo`, `studentAccount_status`, `studentPwd`, `email_verified`, `verification_token`, `token_expiry`) VALUES
(1, NULL, 'Bongani', 'Mthembu', 'bonganimthembu@gmail.com', '632423457', 'Active', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', 1, NULL, NULL),
(2, NULL, 'Noluthando', 'Ndlovu', 'noluthandondlovu@gmail.com', '632076543', 'Active', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', 0, NULL, NULL),
(3, NULL, 'Sipho', 'Xaba', 'siphoxaba@gmail.com', '714567890', 'Active', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', 0, NULL, NULL),
(4, NULL, 'Zoleka', 'Dlamini', 'zolekadlamini@gmail.com', '723456789', 'Inactive', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', 0, NULL, NULL),
(5, NULL, 'Lwazi', 'Mokoena', 'lwazimokoena@gmail.com', '612345678', 'Active', '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC', 0, NULL, NULL),
(10, '225074411', 'Banele', 'Makhonjwa', '225074411@ufh.ac.za', '0652428564', 'Active', '$2y$10$AhEB2chIYVRIHSYRxOYHru68npDvGAJdE82tWlQAryFzSOV8ib1Sq', 1, '99154569ce7cdfb562390ea6013f4ae9f0d348d383e0781d0cef6b3591757fb4', '2026-08-28 13:58:03'),
(11, '225789054', 'Nick', 'James', '225789054@ufh.ac.za', '0786345623', 'Active', '$2y$10$xdTSFVGBXPs129GsQqjo9.RNnqDucQig6nCuDMTxiyn/M6RZIj/G2', 1, 'bb62309605d0933ef9f6d9bfb34a6eac2e598eb11c25be994bf08ae2df7b8be0', '2026-09-02 20:29:00'),
(12, '123456789', 'Lwandiso', 'Gwabavu', 'Mlwandos@gmail.com', '0607512703', 'Active', '$2y$10$rR8GxhveiEBiPLZjvi4XcuD1ua7p5I5afQVI026ygIVjN3V3FKb9q', 1, 'ac8eed9445631ca3570bdec0f4e354e5be747b1ff17ed250ab9b65f90d6b22a6', '2026-09-10 20:53:22');

--
-- Indexes for dumped tables
--

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
  ADD UNIQUE KEY `DriverIDNumber` (`DriverIDNumber`);

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
  ADD KEY `student_id` (`student_id`),
  ADD KEY `driver_id` (`driver_id`);

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
  ADD KEY `DriverID` (`DriverID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `staff_number` (`staff_number`),
  ADD UNIQUE KEY `staff_email` (`staff_email`),
  ADD UNIQUE KEY `cell_number` (`cell_number`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studentID`),
  ADD UNIQUE KEY `studentEmail` (`studentEmail`),
  ADD UNIQUE KEY `studentNumber` (`studentNumber`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campus_location`
--
ALTER TABLE `campus_location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `DriverID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ride`
--
ALTER TABLE `ride`
  MODIFY `rideID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`studentID`),
  ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`DriverID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `ride` (`rideID`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
