-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2025 at 08:32 AM
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
-- Database: `do_gentri`
--

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `position`) VALUES
(1, 'ACCOUNTANT III'),
(2, 'ADMINISTRATIVE AIDE I'),
(3, 'ADMINISTRATIVE AIDE II'),
(4, 'ADMINISTRATIVE AIDE III'),
(5, 'ADMINISTRATIVE AIDE IV'),
(6, 'ADMINISTRATIVE AIDE V'),
(7, 'ADMINISTRATIVE AIDE VI'),
(8, 'ADMINISTRATIVE ASSISTANT I'),
(9, 'ADMINISTRATIVE ASSISTANT II'),
(10, 'ADMINISTRATIVE ASSISTANT III'),
(11, 'ADMINISTRATIVE ASSISTANT IV'),
(12, 'ADMINISTRATIVE ASSISTANT V'),
(13, 'ADMINISTRATIVE ASSISTANT VI'),
(14, 'ADMINISTRATIVE OFFICER II'),
(15, 'ADMINISTRATIVE OFFICER IV'),
(16, 'ADMINISTRATIVE OFFICER V'),
(17, 'ASSISTANT PRINCIPAL I'),
(18, 'ASSISTANT PRINCIPAL II'),
(19, 'ATTORNEY III'),
(20, 'CID CHIEF'),
(21, 'COUNCIL COMMISSIONER'),
(22, 'COUNCIL EXECUTIVE'),
(23, 'DENSTIST I'),
(24, 'DENSTIST II'),
(25, 'DENSTIST III'),
(26, 'DENTAL AIDE'),
(27, 'EDUCATION PROGRAM SPECIALIST'),
(28, 'EDUCATION PROGRAM SPECIALIST II'),
(29, 'EDUCATION PROGRAM SUPERVISOR'),
(30, 'ENGINEER II'),
(31, 'ENGINEER III'),
(32, 'GUIDANCE COUNSELOR'),
(33, 'HEAD TEACHER I'),
(34, 'HEAD TEACHER II'),
(35, 'HEAD TEACHER III'),
(36, 'HEAD TEACHER IV'),
(37, 'INFORMATION TECHNOLOGY OFFICER I'),
(38, 'JOB ORDER'),
(39, 'LIBRARIAN II'),
(40, 'MASTER TEACHER I'),
(41, 'MASTER TEACHER II'),
(42, 'MEDICAL OFFICER III'),
(43, 'NURSE II'),
(44, 'OIC - ASSISTANT SCHOOLS DIVISION SUPERINTENDENT'),
(45, 'OIC - SCHOOLS DIVISION SUPERINTENDENT'),
(46, 'OJT'),
(47, 'PLANNING OFFICER III'),
(48, 'PRINCIPAL I'),
(49, 'PRINCIPAL II'),
(50, 'PRINCIPAL III'),
(51, 'PRINCIPAL IV'),
(52, 'PROJECT DEVELOPMENT OFFICER I'),
(53, 'PROJECT DEVELOPMENT OFFICER II'),
(54, 'PUBLIC SCHOOL DISTRICT SUPERVISOR'),
(55, 'REGISTRAR I'),
(56, 'SCHOOLS DIVISION SUPERINTENDENT'),
(57, 'SECURITY GUARD'),
(58, 'SENIOR EDUCATION PROGRAM SPECIALIST'),
(59, 'SGOD CHIEF'),
(60, 'SPECIAL EDUCATION TEACHER I'),
(61, 'SPECIAL EDUCATION TEACHER II'),
(62, 'SPECIAL EDUCATION TEACHER III'),
(63, 'SPECIAL SCIENCE TEACHER I'),
(64, 'SPES'),
(65, 'TEACHER I'),
(66, 'TEACHER II'),
(67, 'TEACHER III');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_glms`
--

CREATE TABLE `users_glms` (
  `id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `position_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_lnd`
--

CREATE TABLE `users_lnd` (
  `id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `classification` varchar(100) NOT NULL,
  `school_office_assignment` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_perf`
--

CREATE TABLE `users_perf` (
  `id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `position_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`position`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users_glms`
--
ALTER TABLE `users_glms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `users_lnd`
--
ALTER TABLE `users_lnd`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `users_perf`
--
ALTER TABLE `users_perf`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `position_id` (`position_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_glms`
--
ALTER TABLE `users_glms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_lnd`
--
ALTER TABLE `users_lnd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_perf`
--
ALTER TABLE `users_perf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users_glms`
--
ALTER TABLE `users_glms`
  ADD CONSTRAINT `users_glms_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_glms_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_lnd`
--
ALTER TABLE `users_lnd`
  ADD CONSTRAINT `users_lnd_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_lnd_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_perf`
--
ALTER TABLE `users_perf`
  ADD CONSTRAINT `users_perf_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_perf_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
