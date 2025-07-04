-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2025 at 01:43 PM
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
-- Database: `proteq_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN 1', 'admin1@gmail.com', '$2y$10$Q0Ws5H6dHUkuWN8PQ8Ar0ehdelViHDqwsK64HU5Bo4lpzr8l1x6ge', 'admin', 'active', '2025-06-06 05:11:36', '2025-06-06 05:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius_km` decimal(10,2) NOT NULL,
  `status` enum('active','resolved') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `alert_type`, `title`, `description`, `latitude`, `longitude`, `radius_km`, `status`, `created_at`, `updated_at`) VALUES
(4, 'typhoon', 'cvx', 'cvxvcx', 11.59484448, 121.64422639, 0.00, 'resolved', '2025-05-28 12:48:38', '2025-05-29 01:59:01'),
(64, 'typhoon', 'Typhoon Test', 'Typhoon Test', 13.76161700, 121.32809400, 32.00, 'active', '2025-05-29 12:18:46', '2025-05-29 12:18:46'),
(65, 'typhoon', 'Typhoon Test', 'Typhoon Test', 13.76695300, 121.42151800, 3.00, 'active', '2025-05-29 12:25:43', '2025-05-29 12:25:43'),
(66, 'typhoon', 'Sample Test', 'Sample Test', 13.68157200, 121.26764300, 25.00, 'active', '2025-05-29 12:30:45', '2025-05-29 12:30:45'),
(68, 'typhoon', 'Bagyo', 'BAgyo', 13.77984600, 121.23446900, 23.00, 'active', '2025-06-01 01:26:44', '2025-06-01 01:26:44'),
(69, 'fire', 'SADS', 'SAS', -7.00000000, 0.00000000, 0.00, 'active', '2025-06-04 07:31:15', '2025-06-04 07:31:15'),
(71, 'flood', 'testtesttesttesttest', 'testtesttesttesttesttest', 13.84543430, 121.20635020, 4.00, 'resolved', '2025-06-04 12:21:20', '2025-06-04 12:37:49'),
(72, 'typhoon', 'test test etst', 'test test etsttest test etsttest test etsttest test etsttest test etst', 14.60443630, 121.02994690, 2.00, 'active', '2025-06-10 05:59:18', '2025-06-10 05:59:18'),
(73, 'typhoon', 'TESTTESTTESTTESTTEST', 'TESTTESTTESTTESTTESTTEST', 13.82598660, 121.39641570, 30.00, 'active', '2025-06-10 06:07:51', '2025-06-10 06:07:51'),
(74, 'fire', 'TESTTESTTEST', 'TESTTESTTESTTESTTEST', 14.60443630, 121.02994690, 22.00, 'active', '2025-06-10 06:09:49', '2025-06-10 06:09:49'),
(75, 'fire', 'TESTTESTTEST', 'TESTTESTTESTTESTTEST', 14.60443630, 121.02994690, 22.00, 'active', '2025-06-10 06:10:23', '2025-06-10 06:10:23'),
(76, 'typhoon', 'testtesttesttest22121', 'testtesttesttest', 14.10784430, 121.14533040, 211.00, 'active', '2025-06-10 06:12:17', '2025-06-10 06:12:17'),
(77, 'typhoon', 'testtesttesttest22121', 'testtesttesttest', 14.10784430, 121.14533040, 211.00, 'active', '2025-06-10 06:13:30', '2025-06-10 06:13:30'),
(78, 'flood', 'fking6915@gmail.comfking6915@gmail.comfking6915@gmail.com', 'fking6915@gmail.comfking6915@gmail.comfking6915@gmail.com', 14.60443630, 121.02994690, 69.00, 'active', '2025-06-10 06:16:29', '2025-06-10 06:16:29'),
(79, 'other', 'other12', 'other12other12other12other12other12', 14.60443630, 121.02994690, 21.00, 'active', '2025-06-10 06:24:44', '2025-06-10 06:24:44'),
(81, 'earthquake', 'eath12', 'eath12eath12eath12eath12', 13.82598660, 121.39641570, 32.00, 'active', '2025-06-10 06:29:09', '2025-06-10 06:29:09');

-- --------------------------------------------------------

--
-- Table structure for table `emergencies`
--

CREATE TABLE `emergencies` (
  `emergency_id` int(11) NOT NULL,
  `emergency_type` enum('FIRE','EARTHQUAKE','TYPHOON','OTHER') NOT NULL,
  `description` text DEFAULT NULL,
  `triggered_by` int(11) DEFAULT NULL,
  `triggered_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `resolution_reason` text DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emergencies`
--

INSERT INTO `emergencies` (`emergency_id`, `emergency_type`, `description`, `triggered_by`, `triggered_at`, `is_active`, `resolution_reason`, `resolved_by`, `resolved_at`) VALUES
(1, 'EARTHQUAKE', 'Hey test', 1, '2025-06-06 13:49:51', 0, 'goods na haha', 1, '2025-06-06 08:27:35'),
(2, 'EARTHQUAKE', 'AaAaAaAaAa', 1, '2025-06-06 14:48:18', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `evacuation_centers`
--

CREATE TABLE `evacuation_centers` (
  `center_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `capacity` int(11) NOT NULL,
  `current_occupancy` int(11) DEFAULT 0,
  `status` enum('open','full','closed') DEFAULT 'open',
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evacuation_centers`
--

INSERT INTO `evacuation_centers` (`center_id`, `name`, `latitude`, `longitude`, `capacity`, `current_occupancy`, `status`, `contact_person`, `contact_number`, `last_updated`) VALUES
(1, 'BSU Gymnasium', 16.4567890, 120.5678901, 500, 150, 'open', 'Mr. Cruz', '09123456789', '2025-06-04 01:12:34'),
(2, 'Barangay Hall', 16.4579001, 120.5684002, 200, 200, 'full', 'Ms. Reyes', '09129876543', '2025-06-04 01:12:34'),
(3, 'Municipal Covered Court', 16.4598765, 120.5693210, 350, 100, 'open', 'Engr. Torres', '09981234567', '2025-06-04 01:12:34'),
(4, 'Elementary School Grounds', 16.4543211, 120.5654321, 300, 0, 'open', 'Mrs. Bautista', '09097654321', '2025-06-04 01:12:34'),
(5, 'Multi-purpose Hall', 16.4609876, 120.5632109, 250, 250, 'full', 'Mr. De Guzman', '09223456789', '2025-06-04 01:12:34'),
(6, 'Batangas Coliseum', 13.7530412, 121.0519158, 200, 12, 'open', 'Jay Bro', '091212121212', '2025-06-04 02:03:52'),
(7, 'Elementary School', 13.8013427, 121.4372553, 150, 50, 'open', 'Jay bRos', '0921212323', '2025-06-04 02:23:01'),
(8, 'Center 1', 13.8259866, 121.3964157, 30, 12, 'open', 'Jay bros', '213131331', '2025-06-04 02:35:22'),
(9, 'Buhay na sapa Covered Court', 13.7904535, 121.4077032, 60, 65, 'open', 'Jonelllll', '0912122121', '2025-06-05 11:27:26'),
(10, 'BossMalupitona', 14.6044363, 121.0299469, 300, 22, 'open', 'as', '12121', '2025-06-05 11:31:49');

-- --------------------------------------------------------

--
-- Table structure for table `general_users`
--

CREATE TABLE `general_users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `user_type` enum('FACULTY','STUDENT','UNIVERSITY_EMPLOYEE') NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `college` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `general_users`
--

INSERT INTO `general_users` (`user_id`, `first_name`, `last_name`, `user_type`, `password`, `email`, `profile_picture`, `department`, `college`, `created_at`, `updated_at`, `status`) VALUES
(1, 'KEITH', 'CIRUELAS', 'STUDENT', 'KIT1234', 'ciruelaskeithandrei@gmail.com', NULL, 'cics', 'bsu', '2025-05-29 10:18:13', '2025-05-29 10:18:13', 1),
(2, 'Keith', 'CIruelas', 'FACULTY', '$2y$10$qzsZ90VnwJTQNuB9wKrejOOY0p1pPcjZ2ES/a.hvrUg7k5nvLqwui', '22-33950@g.batstate-u.edu.ph', NULL, 'cics', 'IT', '2025-05-29 12:02:39', '2025-05-29 20:18:02', 1),
(5, 'DA', 'DS', 'UNIVERSITY_EMPLOYEE', '$2y$10$O2KtJBnWOluwqqSEwdHmhuQ9H3odPJy6Cx6kNW.kEagL9OxKn65rW', 'jo@gmail.com', NULL, 'N/A', 'Not Applicable', '2025-06-02 09:21:36', '2025-06-02 09:21:36', 1),
(6, 'Keith', 'Ciruelas', 'STUDENT', '$2y$10$h4nSLZbEJc/lJO7CAert0u9QHgYqwpCiUBzWCEDZJu0J8mueN/Ryq', 'kit@gmail.com', NULL, 'CAS', 'Bachelor of Science in Psychology', '2025-06-02 20:08:55', '2025-06-02 20:08:55', 1),
(8, 'Aaron marcus', 'ciruelas', 'UNIVERSITY_EMPLOYEE', '$2y$10$HRdgaYYIywU76ZGvr/UKYema.WauMa21CHuAgNsGnqY/DY4XVPtvG', 'shar@gmail.com', NULL, NULL, NULL, '2025-06-03 09:50:15', '2025-06-03 09:50:15', 0),
(9, 'jae', 'manalo', 'UNIVERSITY_EMPLOYEE', '$2y$10$DcToIPJQR0ruFYTocquiWeDL8QV9pjRItrMbUYCVDLqAOlzPXCzAy', 'abby@gmail.com', NULL, 'N/A', 'Not Applicable', '2025-06-03 09:52:21', '2025-06-03 09:52:21', 1),
(10, 'jae', 'Ciruelas', 'STUDENT', '$2y$10$fqYq9nC69XvllZsIh4CfAe63aJg8c1.u6pWDdWpNAdDR5hZASVpya', 'wqe@gmail.com', NULL, 'CABE', 'Bachelor of Science in Accountancy', '2025-06-03 09:53:07', '2025-06-04 21:03:05', 1),
(11, 'Matt', 'Aposaga', 'STUDENT', '$2y$10$tVjV5ZpCwqR6krEKQNUSIeeg54NfUfirpIQZSlpyqBDWFPc4lTXAC', 'mat@gmail.com', NULL, 'CTE', 'Bachelor of Secondary Education - English', '2025-06-05 19:23:34', '2025-06-07 09:38:24', 0),
(12, 'Aaron marcus', 'manalo', 'UNIVERSITY_EMPLOYEE', '$2y$10$grGXdlfySleP6kGGM3lHBODY5kxUDmPF2NpKproClsgd5wr9VdfLq', 'aa@gmail.com', NULL, 'N/A', 'Not Applicable', '2025-06-10 10:24:54', '2025-06-10 10:24:54', 1);

-- --------------------------------------------------------

--
-- Table structure for table `incident_reports`
--

CREATE TABLE `incident_reports` (
  `incident_id` int(11) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `date_reported` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `validation_status` enum('unvalidated','validated','rejected') NOT NULL DEFAULT 'unvalidated',
  `validation_notes` text DEFAULT NULL,
  `priority_level` enum('low','moderate','high','critical') NOT NULL DEFAULT 'moderate',
  `reporter_safe_status` enum('safe','injured','unknown') NOT NULL DEFAULT 'unknown',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `incident_reports`
--

INSERT INTO `incident_reports` (`incident_id`, `incident_type`, `description`, `longitude`, `latitude`, `date_reported`, `status`, `assigned_to`, `reported_by`, `validation_status`, `validation_notes`, `priority_level`, `reporter_safe_status`, `created_at`, `updated_at`) VALUES
(1, 'fire', 'sdfdgfd', 121.1564032, 14.0804096, '2025-06-09 19:59:21', 'pending', NULL, 6, 'unvalidated', NULL, 'moderate', 'safe', '2025-06-09 19:59:21', '2025-06-09 19:59:21');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) NOT NULL,
  `center_id` int(11) DEFAULT NULL,
  `type` enum('food','water','medical','shelter','other') NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `center_id`, `type`, `quantity`, `updated_at`) VALUES
(1, 6, '', 32, '2025-06-04 02:03:52'),
(2, 7, 'food', 300, '2025-06-04 02:23:01'),
(3, 7, 'water', 250, '2025-06-04 02:23:01'),
(4, 7, '', 15, '2025-06-04 02:23:01'),
(5, 8, '', 2, '2025-06-04 02:35:22'),
(6, 9, 'food', 30, '2025-06-05 11:27:26'),
(7, 9, 'water', 40, '2025-06-05 11:27:26'),
(8, 9, '', 10, '2025-06-05 11:27:26'),
(9, 10, '', 21, '2025-06-05 11:31:49');

-- --------------------------------------------------------

--
-- Table structure for table `safety_protocols`
--

CREATE TABLE `safety_protocols` (
  `protocol_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `type` enum('fire','earthquake','medical','intrusion','general') DEFAULT 'general',
  `file_attachment` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `safety_protocols`
--

INSERT INTO `safety_protocols` (`protocol_id`, `title`, `description`, `type`, `file_attachment`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'test', 'asasasassasa', 'fire', '684428ee15618.png', 1, '2025-06-07 19:56:30', '2025-06-07 19:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('nurse','paramedic','security','firefighter','others') NOT NULL,
  `availability` enum('available','busy','off-duty') NOT NULL DEFAULT 'available',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `name`, `email`, `password`, `role`, `availability`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Kit', 'kit@gmail.com', '$2y$10$EvUKs5blRoI03yfbokDIZetGvJPmEEjbMd.VF4S1Q.lrYj/pQraWW', 'nurse', 'available', 'active', '2025-06-10 02:27:50', '2025-06-11 05:23:40');

-- --------------------------------------------------------

--
-- Table structure for table `staff_locations`
--

CREATE TABLE `staff_locations` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_locations`
--

INSERT INTO `staff_locations` (`id`, `staff_id`, `latitude`, `longitude`, `last_updated`) VALUES
(65, 1, 14.0771328, 121.1596800, '2025-06-11 06:17:22');

-- --------------------------------------------------------

--
-- Table structure for table `user_locations`
--

CREATE TABLE `user_locations` (
  `location_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_locations`
--

INSERT INTO `user_locations` (`location_id`, `user_id`, `latitude`, `longitude`, `created_at`) VALUES
(15, 6, 14.07713280, 121.15968000, '2025-06-11 14:25:57'),
(16, 1, 14.07713280, 121.15968000, '2025-06-11 14:05:05');

-- --------------------------------------------------------

--
-- Table structure for table `welfare_checks`
--

CREATE TABLE `welfare_checks` (
  `welfare_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `emergency_id` int(11) NOT NULL,
  `status` enum('SAFE','NEEDS_HELP','NO_RESPONSE') DEFAULT 'NO_RESPONSE',
  `remarks` text DEFAULT NULL,
  `reported_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `welfare_checks`
--

INSERT INTO `welfare_checks` (`welfare_id`, `user_id`, `emergency_id`, `status`, `remarks`, `reported_at`) VALUES
(1, 2, 1, 'NO_RESPONSE', NULL, '2025-06-06 13:49:51'),
(2, 9, 1, 'NO_RESPONSE', NULL, '2025-06-06 13:49:51'),
(3, 1, 1, 'NO_RESPONSE', NULL, '2025-06-06 13:49:51'),
(4, 5, 1, 'NO_RESPONSE', NULL, '2025-06-06 13:49:51'),
(5, 6, 1, 'SAFE', '', '2025-06-06 14:04:58'),
(6, 11, 1, 'NO_RESPONSE', NULL, '2025-06-06 13:49:51'),
(7, 8, 1, 'NO_RESPONSE', NULL, '2025-06-06 13:49:51'),
(8, 10, 1, 'NO_RESPONSE', NULL, '2025-06-06 13:49:51'),
(9, 6, 2, 'SAFE', '', '2025-06-06 20:48:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emergencies`
--
ALTER TABLE `emergencies`
  ADD PRIMARY KEY (`emergency_id`),
  ADD KEY `triggered_by` (`triggered_by`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `idx_emergency_resolved_at` (`resolved_at`);

--
-- Indexes for table `evacuation_centers`
--
ALTER TABLE `evacuation_centers`
  ADD PRIMARY KEY (`center_id`);

--
-- Indexes for table `general_users`
--
ALTER TABLE `general_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD KEY `center_id` (`center_id`);

--
-- Indexes for table `safety_protocols`
--
ALTER TABLE `safety_protocols`
  ADD PRIMARY KEY (`protocol_id`),
  ADD KEY `fk_created_by` (`created_by`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `staff_locations`
--
ALTER TABLE `staff_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `user_locations`
--
ALTER TABLE `user_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_locations_coords` (`latitude`,`longitude`);

--
-- Indexes for table `welfare_checks`
--
ALTER TABLE `welfare_checks`
  ADD PRIMARY KEY (`welfare_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `emergency_id` (`emergency_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `emergencies`
--
ALTER TABLE `emergencies`
  MODIFY `emergency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `evacuation_centers`
--
ALTER TABLE `evacuation_centers`
  MODIFY `center_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `general_users`
--
ALTER TABLE `general_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `incident_reports`
--
ALTER TABLE `incident_reports`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `safety_protocols`
--
ALTER TABLE `safety_protocols`
  MODIFY `protocol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_locations`
--
ALTER TABLE `staff_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `user_locations`
--
ALTER TABLE `user_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `welfare_checks`
--
ALTER TABLE `welfare_checks`
  MODIFY `welfare_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `emergencies`
--
ALTER TABLE `emergencies`
  ADD CONSTRAINT `emergencies_ibfk_1` FOREIGN KEY (`triggered_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `emergencies_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD CONSTRAINT `incident_reports_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `evacuation_centers` (`center_id`);

--
-- Constraints for table `safety_protocols`
--
ALTER TABLE `safety_protocols`
  ADD CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `staff_locations`
--
ALTER TABLE `staff_locations`
  ADD CONSTRAINT `staff_locations_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_locations`
--
ALTER TABLE `user_locations`
  ADD CONSTRAINT `user_locations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `general_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `welfare_checks`
--
ALTER TABLE `welfare_checks`
  ADD CONSTRAINT `welfare_checks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `general_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `welfare_checks_ibfk_2` FOREIGN KEY (`emergency_id`) REFERENCES `emergencies` (`emergency_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
