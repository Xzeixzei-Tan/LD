-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2025 at 02:10 AM
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
-- Table structure for table `affiliation`
--

CREATE TABLE `affiliation` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `affiliation`
--

INSERT INTO `affiliation` (`id`, `name`) VALUES
(2, 'Division'),
(1, 'School');

-- --------------------------------------------------------

--
-- Table structure for table `classification`
--

CREATE TABLE `classification` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classification`
--

INSERT INTO `classification` (`id`, `name`) VALUES
(2, 'Non-teaching'),
(1, 'Teaching');

-- --------------------------------------------------------

--
-- Table structure for table `class_position`
--

CREATE TABLE `class_position` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `classification_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_position`
--

INSERT INTO `class_position` (`id`, `name`, `classification_id`) VALUES
(1, 'ACCOUNTANT III', 2),
(2, 'ADMINISTRATIVE AIDE I', 2),
(3, 'ADMINISTRATIVE AIDE II', 2),
(4, 'ADMINISTRATIVE AIDE III', 2),
(5, 'ADMINISTRATIVE AIDE IV', 2),
(6, 'ADMINISTRATIVE AIDE V', 2),
(7, 'ADMINISTRATIVE AIDE VI', 2),
(8, 'ADMINISTRATIVE ASSISTANT I', 2),
(9, 'ADMINISTRATIVE ASSISTANT II', 2),
(10, 'ADMINISTRATIVE ASSISTANT III', 2),
(11, 'ADMINISTRATIVE ASSISTANT IV', 2),
(12, 'ADMINISTRATIVE ASSISTANT V', 2),
(13, 'ADMINISTRATIVE ASSISTANT VI', 2),
(14, 'ADMINISTRATIVE OFFICER II', 2),
(15, 'ADMINISTRATIVE OFFICER IV', 2),
(16, 'ADMINISTRATIVE OFFICER V', 2),
(17, 'ATTORNEY III', 2),
(18, 'CID CHIEF', 2),
(19, 'COUNCIL COMMISSIONER', 2),
(20, 'COUNCIL EXECUTIVE', 2),
(21, 'DENTAL AIDE', 2),
(22, 'DENSTIST I', 2),
(23, 'DENSTIST II', 2),
(24, 'DENSTIST III', 2),
(25, 'EDUCATION PROGRAM SPECIALIST', 2),
(26, 'EDUCATION PROGRAM SPECIALIST II', 2),
(27, 'EDUCATION PROGRAM SUPERVISOR', 2),
(28, 'ENGINEER II', 2),
(29, 'ENGINEER III', 2),
(30, 'INFORMATION TECHNOLOGY OFFICER I', 2),
(31, 'JOB ORDER', 2),
(32, 'LIBRARIAN II', 2),
(33, 'MEDICAL OFFICER III', 2),
(34, 'NURSE II', 2),
(35, 'OIC - ASSISTANT SCHOOLS DIVISION SUPERINTENDENT', 2),
(36, 'OIC - SCHOOLS DIVISION SUPERINTENDENT', 2),
(37, 'OJT', 2),
(38, 'PLANNING OFFICER III', 2),
(39, 'PROJECT DEVELOPMENT OFFICER I', 2),
(40, 'PROJECT DEVELOPMENT OFFICER II', 2),
(41, 'PUBLIC SCHOOL DISTRICT SUPERVISOR', 2),
(42, 'REGISTRAR I', 2),
(43, 'SCHOOLS DIVISION SUPERINTENDENT', 2),
(44, 'SECURITY GUARD', 2),
(45, 'SENIOR EDUCATION PROGRAM SPECIALIST', 2),
(46, 'SGOD CHIEF', 2),
(47, 'ASSISTANT PRINCIPAL I', 1),
(48, 'ASSISTANT PRINCIPAL II', 1),
(49, 'GUIDANCE COUNSELOR', 1),
(50, 'HEAD TEACHER I', 1),
(51, 'HEAD TEACHER II', 1),
(52, 'HEAD TEACHER III', 1),
(53, 'HEAD TEACHER IV', 1),
(54, 'MASTER TEACHER I', 1),
(55, 'MASTER TEACHER II', 1),
(56, 'MASTER TEACHER III', 1),
(57, 'MASTER TEACHER IV', 1),
(58, 'MASTER TEACHER V', 1),
(59, 'MASTER TEACHER VI', 1),
(60, 'MASTER TEACHER VII', 1),
(61, 'PRINCIPAL I', 1),
(62, 'PRINCIPAL II', 1),
(63, 'PRINCIPAL III', 1),
(64, 'PRINCIPAL IV', 1),
(65, 'SPECIAL EDUCATION TEACHER I', 1),
(66, 'SPECIAL EDUCATION TEACHER II', 1),
(67, 'SPECIAL EDUCATION TEACHER III', 1),
(68, 'SPECIAL SCIENCE TEACHER I', 1),
(69, 'SPES', 1),
(70, 'TEACHER I', 1),
(71, 'TEACHER II', 1),
(72, 'TEACHER III', 1),
(73, 'TEACHER IV', 1),
(74, 'TEACHER V', 1),
(75, 'TEACHER VI', 1),
(76, 'TEACHER VII', 1),
(77, 'ASSISTANT PRINCIPAL I', 1),
(78, 'ASSISTANT PRINCIPAL II', 1),
(79, 'GUIDANCE COUNSELOR', 1),
(80, 'HEAD TEACHER I', 1),
(81, 'HEAD TEACHER II', 1),
(82, 'HEAD TEACHER III', 1),
(83, 'HEAD TEACHER IV', 1),
(84, 'MASTER TEACHER I', 1),
(85, 'MASTER TEACHER II', 1),
(86, 'MASTER TEACHER III', 1),
(87, 'MASTER TEACHER IV', 1),
(88, 'MASTER TEACHER V', 1),
(89, 'MASTER TEACHER VI', 1),
(90, 'MASTER TEACHER VII', 1),
(91, 'PRINCIPAL I', 1),
(92, 'PRINCIPAL II', 1),
(93, 'PRINCIPAL III', 1),
(94, 'PRINCIPAL IV', 1),
(95, 'SPECIAL EDUCATION TEACHER I', 1),
(96, 'SPECIAL EDUCATION TEACHER II', 1),
(97, 'SPECIAL EDUCATION TEACHER III', 1),
(98, 'SPECIAL SCIENCE TEACHER I', 1),
(99, 'SPES', 1),
(100, 'TEACHER I', 1),
(101, 'TEACHER II', 1),
(102, 'TEACHER III', 1),
(103, 'TEACHER IV', 1),
(104, 'TEACHER V', 1),
(105, 'TEACHER VI', 1),
(106, 'TEACHER VII', 1);

-- --------------------------------------------------------

--
-- Table structure for table `eligible_participants`
--

CREATE TABLE `eligible_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `school_level` enum('Elementary','JHS','SHS') NOT NULL,
  `specialization` enum('MTB-MLE','Filipino','English','Mathematics','Science','Technology and Livelihood Education','EsP','MAPEH','Edukasyong Pantahanan at Pangkabuhayan','Special Education','TLE','SPED') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eligible_participants`
--

INSERT INTO `eligible_participants` (`id`, `event_id`, `school_level`, `specialization`) VALUES
(1, 1, 'JHS', 'Mathematics'),
(2, 1, 'SHS', 'Science'),
(3, 1, 'Elementary', 'MTB-MLE'),
(4, 2, 'SHS', 'Technology and Livelihood Education'),
(5, 2, 'JHS', 'Science'),
(6, 2, 'Elementary', 'English'),
(7, 3, '', ''),
(8, 3, 'SHS', ''),
(9, 3, 'JHS', 'Mathematics'),
(10, 4, '', ''),
(11, 4, 'SHS', ''),
(12, 4, 'JHS', ''),
(13, 5, '', ''),
(14, 5, 'SHS', ''),
(15, 5, 'JHS', '');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_specification` enum('Training','Activity') NOT NULL,
  `delivery` enum('F2F','Online','Hybrid/Blended') NOT NULL,
  `venue` varchar(255) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `organizer_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `event_specification`, `delivery`, `venue`, `start_datetime`, `end_datetime`, `organizer_name`, `created_at`, `updated_at`, `archived`) VALUES
(1, 'STEM Teacher Training Workshop', 'Training', 'Hybrid/Blended', 'Multi-purpose Hall', '2025-03-10 08:00:00', '2025-03-12 17:00:00', 'John Doe', '2025-03-07 02:31:50', '2025-03-10 08:29:49', 1),
(2, 'Digital Skills Enhancement Seminar', 'Activity', 'Online', 'Zoom Meeting', '2025-04-15 09:00:00', '2025-04-15 16:00:00', 'Jane Smith', '2025-03-07 02:54:38', '2025-03-10 05:44:48', 0),
(3, 'Tech Conference 2025', 'Training', 'F2F', 'Convention Center, Manila & Zoom', '2025-03-07 08:30:00', '2025-03-08 17:30:00', 'John Doe', '2025-03-07 05:56:15', '2025-03-10 07:04:24', 1),
(4, 'AI and Machine Learning Workshop', 'Activity', 'F2F', 'TechHub Auditorium, Cebu City', '2025-06-10 10:00:00', '2025-06-10 15:00:00', 'Alice Johnson', '2025-03-07 05:59:27', '2025-03-10 05:44:48', 0),
(5, 'Cybersecurity Awareness Seminar', 'Training', 'Online', 'Microsoft Teams', '2025-07-05 09:30:00', '2025-02-27 12:30:00', 'Mr. Robert Wilson', '2025-03-10 03:24:38', '2025-03-12 07:48:16', 1);

-- --------------------------------------------------------

--
-- Table structure for table `funding_sources`
--

CREATE TABLE `funding_sources` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `source` enum('MOOE','SEF','PSF','Others') NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `funding_sources`
--

INSERT INTO `funding_sources` (`id`, `event_id`, `source`, `amount`) VALUES
(1, 1, 'MOOE', 50000.00),
(2, 1, 'PSF', 25000.00),
(3, 2, 'SEF', 30000.00),
(4, 2, 'Others', 10000.00),
(5, 3, '', 50000.00),
(6, 3, '', 20000.00),
(7, 4, '', 15000.00),
(8, 4, '', 5000.00),
(9, 5, '', 25000.00),
(10, 5, '', 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `meal_plan`
--

CREATE TABLE `meal_plan` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `meal_type` varchar(255) NOT NULL,
  `day` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_plan`
--

INSERT INTO `meal_plan` (`id`, `event_id`, `meal_type`, `day`) VALUES
(1, 1, 'Breakfast', '2025-03-10');

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
-- Table structure for table `registered_users`
--

CREATE TABLE `registered_users` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_users`
--

INSERT INTO `registered_users` (`id`, `event_id`, `user_id`, `registration_date`) VALUES
(19, 2, 8, '2025-03-11 08:05:11'),
(20, 1, 8, '2025-03-11 08:05:42'),
(24, 5, 12, '2025-03-13 00:53:16');

-- --------------------------------------------------------

--
-- Table structure for table `speakers`
--

CREATE TABLE `speakers` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `speaker_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `speakers`
--

INSERT INTO `speakers` (`id`, `event_id`, `speaker_name`) VALUES
(1, 1, 'Dr. Alice Smith'),
(2, 1, 'Prof. Bob Johnson'),
(3, 2, 'Dr. Michael Brown'),
(4, 2, 'Prof. Emily Davis'),
(5, 3, 'Dr. Alan Roberts'),
(6, 3, 'Ms. Sarah Lee'),
(7, 4, 'Dr. Lucas Wright'),
(8, 4, 'Ms. Sophia Green'),
(9, 5, 'Dr. Anna Thompson'),
(10, 5, 'Mr. Brian Carter');

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

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `sex`, `contact_no`, `email`, `password`, `created_at`, `deleted_at`) VALUES
(8, 'cess', 'jauod', 'tan', '', 'Female', '09123456789', 'cess@gmail.com', '$2y$10$RMmm6J9lXZkyY3uO7gbvau2RUcNOliJzifiG9m0yyVPLZoRlbiczS', '2025-03-06 07:41:55', NULL),
(9, 'chynna', 'larize', 'layos', '', 'Female', '09123456789', 'chynna@gmail.com', '$2y$10$ekqUks1XESuOPn5ZvQTV.O6zYIkQOFVCf113bMZW0PziBXjxJNhjC', '2025-03-13 00:48:32', NULL),
(10, 'cess', 'tan', 'jauod', '', 'Female', '09123456789', 'tan@gmail.com', '$2y$10$3xTRjKjltfj6n2NjU55DaOB/CuXOh0Kq7wLqvdWx6usYcnjFK0fSG', '2025-03-13 00:49:03', NULL),
(11, 'jess', 'tatoy', 'constante', '', 'Male', '09123456789', 'jessie@gmail.com', '$2y$10$OLb/9jsc1JdRSfL4bVvqXORsILHQLF3NZL4BKWhr4PtvNuLIFxYCa', '2025-03-13 00:49:48', NULL),
(12, 'hershey', 'nhadz', 'bayle', '', 'Female', '09123456789', 'bayle@gmail.com', '$2y$10$f2qp8d6SVuejRWtDff0V.e7MVHQuqJKDsHXmPqvR7s9clnPuBoPVO', '2025-03-13 00:50:53', NULL),
(13, 'alex', 'gupong', 'castillas', '', 'Female', '09123456789', 'alex@gmail.com', '$2y$10$uiNtlhZ9gTLzuklkGk2JG.60Upu5yqrSHkiwH6bdSwE9NCUFCTco2', '2025-03-13 00:51:31', NULL),
(14, 'nhadz', 'nhadz', 'dudas', '', 'Female', '09123456789', 'dudas@gmail.com', '$2y$10$lA8ongr6qcXbi69ptGafMORhmgr9xVZ1.OzwW71ml7UFf7q62mixu', '2025-03-13 00:56:27', NULL),
(15, 'chelsea', 'tatoy', 'constante', '', 'Female', '09123456789', 'chelsea@gmail.com', '$2y$10$yWQMDcobe8cU9wnWmrKFPOwGpqNsy7Z/VZhL7MdH9cLGVnp2I..rO', '2025-03-13 00:57:33', NULL),
(16, 'Franchesca', 'Gupong', 'Castillas', '', 'Female', '09123456789', 'franchesca@gmail.com', '$2y$10$QPc9Nr7vQmgV4CuDnoS0Ue7lca7I0MD33BJd5yXR.NcyothJ1BMve', '2025-03-13 00:58:37', NULL),
(17, 'Roberto', 'Sarmiento', 'Layos', '', 'Male', '09123456789', 'roberto@gmail.com', '$2y$10$U/HYTJuQ9zcfzWFSeNgoleGJFacWSJF2MFGnOg2rIUQQcJQXlq5bG', '2025-03-13 01:00:01', NULL);

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
  `user_id` int(11) NOT NULL,
  `affiliation_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `classification_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_lnd`
--

INSERT INTO `users_lnd` (`id`, `user_id`, `affiliation_id`, `position_id`, `classification_id`) VALUES
(1, 8, 2, 13, 2),
(2, 9, 2, 1, 2),
(3, 10, 1, 2, 2),
(4, 11, 2, 3, 2),
(5, 12, 2, 34, 2),
(6, 13, 1, 15, 2),
(7, 14, 1, 94, 1),
(8, 15, 1, 2, 2),
(9, 16, 2, 16, 2),
(10, 17, 2, 18, 2);

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
-- Indexes for table `affiliation`
--
ALTER TABLE `affiliation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `classification`
--
ALTER TABLE `classification`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `class_position`
--
ALTER TABLE `class_position`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classification_id` (`classification_id`);

--
-- Indexes for table `eligible_participants`
--
ALTER TABLE `eligible_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `funding_sources`
--
ALTER TABLE `funding_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `meal_plan`
--
ALTER TABLE `meal_plan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`position`);

--
-- Indexes for table `registered_users`
--
ALTER TABLE `registered_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `speakers`
--
ALTER TABLE `speakers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `affiliation_id` (`affiliation_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `classification_id` (`classification_id`);

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
-- AUTO_INCREMENT for table `affiliation`
--
ALTER TABLE `affiliation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `classification`
--
ALTER TABLE `classification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `class_position`
--
ALTER TABLE `class_position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `eligible_participants`
--
ALTER TABLE `eligible_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `funding_sources`
--
ALTER TABLE `funding_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `meal_plan`
--
ALTER TABLE `meal_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `registered_users`
--
ALTER TABLE `registered_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `speakers`
--
ALTER TABLE `speakers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users_glms`
--
ALTER TABLE `users_glms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_lnd`
--
ALTER TABLE `users_lnd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users_perf`
--
ALTER TABLE `users_perf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_position`
--
ALTER TABLE `class_position`
  ADD CONSTRAINT `class_position_ibfk_1` FOREIGN KEY (`classification_id`) REFERENCES `classification` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eligible_participants`
--
ALTER TABLE `eligible_participants`
  ADD CONSTRAINT `eligible_participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `funding_sources`
--
ALTER TABLE `funding_sources`
  ADD CONSTRAINT `funding_sources_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_plan`
--
ALTER TABLE `meal_plan`
  ADD CONSTRAINT `meal_plan_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registered_users`
--
ALTER TABLE `registered_users`
  ADD CONSTRAINT `registered_users_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registered_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `speakers`
--
ALTER TABLE `speakers`
  ADD CONSTRAINT `speakers_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `users_lnd_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_lnd_ibfk_2` FOREIGN KEY (`affiliation_id`) REFERENCES `affiliation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_lnd_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `class_position` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_lnd_ibfk_4` FOREIGN KEY (`classification_id`) REFERENCES `classification` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_perf`
--
ALTER TABLE `users_perf`
  ADD CONSTRAINT `users_perf_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_perf_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `update_archived_events` ON SCHEDULE EVERY 1 DAY STARTS '2025-03-10 15:02:22' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  UPDATE `events` 
  SET `archived` = 1 
  WHERE `end_datetime` < NOW() AND `archived` = 0;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
