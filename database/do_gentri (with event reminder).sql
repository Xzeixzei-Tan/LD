-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2025 at 08:44 AM
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
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `generated_date` datetime NOT NULL,
  `certificate_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `user_id`, `event_id`, `generated_date`, `certificate_path`) VALUES
(1, 8, 56, '2025-03-18 15:21:13', 'certificates/SAMPLE_/cess_jauod_tan.pdf'),
(2, 9, 56, '2025-03-18 15:21:14', 'certificates/SAMPLE_/Jess_Tatoy_Constante.pdf'),
(3, 8, 56, '2025-03-18 16:30:32', 'certificates/SAMPLE_/cess_jauod_tan.pdf'),
(4, 9, 56, '2025-03-18 16:30:34', 'certificates/SAMPLE_/Jess_Tatoy_Constante.pdf'),
(5, 8, 56, '2025-03-18 16:45:52', 'certificates/SAMPLE_/cess_jauod_tan.pdf'),
(6, 9, 56, '2025-03-18 16:45:53', 'certificates/SAMPLE_/Jess_Tatoy_Constante.pdf'),
(7, 8, 59, '2025-03-19 09:45:14', 'certificates/Sample_2/cess_jauod_tan.pdf'),
(8, 8, 59, '2025-03-19 09:48:55', 'certificates/Sample_2/cess_jauod_tan.pdf'),
(9, 8, 59, '2025-03-19 09:53:12', 'certificates/Sample_2/cess_jauod_tan.pdf'),
(10, 8, 56, '2025-03-19 14:06:18', 'certificates/SAMPLE_/cess_jauod_tan.pdf'),
(11, 9, 56, '2025-03-19 14:06:19', 'certificates/SAMPLE_/Jess_Tatoy_Constante.pdf'),
(12, 8, 59, '2025-03-20 14:55:15', 'certificates/Sample_2/cess_jauod_tan.pdf');

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
-- Table structure for table `division_participants`
--

CREATE TABLE `division_participants` (
  `id` int(11) NOT NULL,
  `eligible_participant_id` int(11) NOT NULL,
  `department_name` enum('Office of the Assistant Schools Division Superintendent','Legal Services','ICT Services','Administrative Services','Personnel','Records','Cashier','Supply & Property','Budget','Finance','Curriculum Implementation Division (CID)','Learning Resource Management','Curriculum Implementation Management','District Instructional Management','Alternative Learning System','School Governance and Operations Division (SGOD)','School Management Monitoring & Evaluation','Human Resource Development','Social Mobilization and Networking','Planning & Research','Education Facilities','School Health & Nutrition Unit') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `division_participants`
--

INSERT INTO `division_participants` (`id`, `eligible_participant_id`, `department_name`) VALUES
(71, 14, 'Office of the Assistant Schools Division Superintendent'),
(72, 14, 'Legal Services'),
(73, 14, 'ICT Services'),
(74, 14, 'Administrative Services'),
(75, 14, 'Personnel'),
(76, 14, 'Records'),
(77, 14, 'Cashier'),
(78, 14, 'Supply & Property'),
(79, 14, 'Budget'),
(80, 14, 'Finance'),
(81, 14, 'Curriculum Implementation Division (CID)'),
(82, 14, 'Learning Resource Management'),
(83, 14, 'Curriculum Implementation Management'),
(84, 14, 'District Instructional Management'),
(85, 14, 'Alternative Learning System'),
(86, 14, 'School Governance and Operations Division (SGOD)'),
(87, 14, 'School Management Monitoring & Evaluation'),
(88, 14, 'Human Resource Development'),
(89, 14, 'Social Mobilization and Networking'),
(90, 14, 'Planning & Research'),
(91, 14, 'Education Facilities'),
(92, 14, 'School Health & Nutrition Unit'),
(93, 23, 'Office of the Assistant Schools Division Superintendent'),
(94, 23, 'Legal Services'),
(95, 23, 'ICT Services'),
(96, 23, 'Administrative Services'),
(97, 23, 'Personnel'),
(98, 23, 'Records'),
(99, 23, 'Cashier'),
(100, 23, 'Supply & Property'),
(101, 23, 'Budget'),
(102, 23, 'Finance'),
(103, 23, 'Curriculum Implementation Division (CID)'),
(104, 23, 'Learning Resource Management'),
(105, 23, 'Curriculum Implementation Management'),
(106, 23, 'District Instructional Management'),
(107, 23, 'Alternative Learning System'),
(108, 23, 'School Governance and Operations Division (SGOD)'),
(109, 23, 'School Management Monitoring & Evaluation'),
(110, 23, 'Human Resource Development'),
(111, 23, 'Social Mobilization and Networking'),
(112, 23, 'Planning & Research'),
(113, 23, 'Education Facilities'),
(114, 23, 'School Health & Nutrition Unit'),
(115, 24, 'Office of the Assistant Schools Division Superintendent'),
(116, 24, 'Legal Services'),
(117, 24, 'ICT Services'),
(118, 24, 'Administrative Services'),
(119, 24, 'Personnel'),
(120, 24, 'Records'),
(121, 24, 'Cashier'),
(122, 24, 'Supply & Property'),
(123, 24, 'Budget'),
(124, 24, 'Finance'),
(125, 24, 'Curriculum Implementation Division (CID)'),
(126, 24, 'Learning Resource Management'),
(127, 24, 'Curriculum Implementation Management'),
(128, 24, 'District Instructional Management'),
(129, 24, 'Alternative Learning System'),
(130, 24, 'School Governance and Operations Division (SGOD)'),
(131, 24, 'School Management Monitoring & Evaluation'),
(132, 24, 'Human Resource Development'),
(133, 24, 'Social Mobilization and Networking'),
(134, 24, 'Planning & Research'),
(135, 24, 'Education Facilities'),
(136, 24, 'School Health & Nutrition Unit'),
(335, 37, 'Office of the Assistant Schools Division Superintendent'),
(336, 37, 'Legal Services'),
(337, 37, 'ICT Services'),
(338, 37, 'Administrative Services'),
(339, 37, 'Personnel'),
(340, 37, 'Records'),
(341, 37, 'Cashier'),
(342, 37, 'Supply & Property'),
(343, 37, 'Budget'),
(344, 37, 'Finance'),
(345, 37, 'Curriculum Implementation Division (CID)'),
(346, 37, 'Learning Resource Management'),
(347, 37, 'Curriculum Implementation Management'),
(348, 37, 'District Instructional Management'),
(349, 37, 'Alternative Learning System'),
(350, 37, 'School Governance and Operations Division (SGOD)'),
(351, 37, 'School Management Monitoring & Evaluation'),
(352, 37, 'Human Resource Development'),
(353, 37, 'Social Mobilization and Networking'),
(354, 37, 'Planning & Research'),
(355, 37, 'Education Facilities'),
(356, 37, 'School Health & Nutrition Unit'),
(357, 44, 'Office of the Assistant Schools Division Superintendent'),
(358, 44, 'Legal Services'),
(359, 44, 'ICT Services'),
(360, 44, 'Administrative Services'),
(361, 44, 'Personnel'),
(362, 44, 'Records'),
(363, 44, 'Cashier'),
(364, 44, 'Supply & Property'),
(365, 44, 'Budget'),
(366, 44, 'Finance'),
(367, 44, 'Curriculum Implementation Division (CID)'),
(368, 44, 'Learning Resource Management'),
(369, 44, 'Curriculum Implementation Management'),
(370, 44, 'District Instructional Management'),
(371, 44, 'Alternative Learning System'),
(372, 44, 'School Governance and Operations Division (SGOD)'),
(373, 44, 'School Management Monitoring & Evaluation'),
(374, 44, 'Human Resource Development'),
(375, 44, 'Social Mobilization and Networking'),
(376, 44, 'Planning & Research'),
(377, 44, 'Education Facilities'),
(378, 44, 'School Health & Nutrition Unit'),
(379, 45, 'Office of the Assistant Schools Division Superintendent'),
(380, 45, 'Legal Services'),
(381, 45, 'ICT Services'),
(382, 45, 'Administrative Services'),
(383, 45, 'Personnel'),
(384, 45, 'Records'),
(385, 45, 'Cashier'),
(386, 45, 'Supply & Property'),
(387, 45, 'Budget'),
(388, 45, 'Finance'),
(389, 45, 'Curriculum Implementation Division (CID)'),
(390, 45, 'Learning Resource Management'),
(391, 45, 'Curriculum Implementation Management'),
(392, 45, 'District Instructional Management'),
(393, 45, 'Alternative Learning System'),
(394, 45, 'School Governance and Operations Division (SGOD)'),
(395, 45, 'School Management Monitoring & Evaluation'),
(396, 45, 'Human Resource Development'),
(397, 45, 'Social Mobilization and Networking'),
(398, 45, 'Planning & Research'),
(399, 45, 'Education Facilities'),
(400, 45, 'School Health & Nutrition Unit'),
(401, 46, 'Office of the Assistant Schools Division Superintendent'),
(402, 46, 'Legal Services'),
(403, 46, 'ICT Services'),
(404, 46, 'Administrative Services'),
(405, 46, 'Personnel'),
(406, 46, 'Records'),
(407, 46, 'Cashier'),
(408, 46, 'Supply & Property'),
(409, 46, 'Budget'),
(410, 46, 'Finance'),
(411, 46, 'Curriculum Implementation Division (CID)'),
(412, 46, 'Learning Resource Management'),
(413, 46, 'Curriculum Implementation Management'),
(414, 46, 'District Instructional Management'),
(415, 46, 'Alternative Learning System'),
(416, 46, 'School Governance and Operations Division (SGOD)'),
(417, 46, 'School Management Monitoring & Evaluation'),
(418, 46, 'Human Resource Development'),
(419, 46, 'Social Mobilization and Networking'),
(420, 46, 'Planning & Research'),
(421, 46, 'Education Facilities'),
(422, 46, 'School Health & Nutrition Unit'),
(423, 47, 'Office of the Assistant Schools Division Superintendent'),
(424, 47, 'Legal Services'),
(425, 47, 'ICT Services'),
(426, 47, 'Administrative Services'),
(427, 47, 'Personnel'),
(428, 47, 'Records'),
(429, 47, 'Cashier'),
(430, 47, 'Supply & Property'),
(431, 47, 'Budget'),
(432, 47, 'Finance'),
(433, 47, 'Curriculum Implementation Division (CID)'),
(434, 47, 'Learning Resource Management'),
(435, 47, 'Curriculum Implementation Management'),
(436, 47, 'District Instructional Management'),
(437, 47, 'Alternative Learning System'),
(438, 47, 'School Governance and Operations Division (SGOD)'),
(439, 47, 'School Management Monitoring & Evaluation'),
(440, 47, 'Human Resource Development'),
(441, 47, 'Social Mobilization and Networking'),
(442, 47, 'Planning & Research'),
(443, 47, 'Education Facilities'),
(444, 47, 'School Health & Nutrition Unit');

-- --------------------------------------------------------

--
-- Table structure for table `eligible_participants`
--

CREATE TABLE `eligible_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `target` enum('School','Division','Both','') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eligible_participants`
--

INSERT INTO `eligible_participants` (`id`, `event_id`, `target`) VALUES
(14, 24, 'Division'),
(22, 32, 'School'),
(23, 33, 'Division'),
(24, 34, 'Division'),
(37, 53, 'Division'),
(38, 54, 'School'),
(40, 56, 'School'),
(41, 57, 'School'),
(42, 58, 'School'),
(43, 59, 'School'),
(44, 60, 'Division'),
(45, 61, 'Division'),
(46, 62, 'Division'),
(47, 63, 'Division');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `specification` enum('training','activity') NOT NULL,
  `delivery` enum('face-to-face','online','hybrid-blended') NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `proponent` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `specification`, `delivery`, `venue`, `start_date`, `end_date`, `proponent`, `created_at`, `updated_at`, `archived`) VALUES
(24, 'division', 'training', 'online', '', '2025-03-14', '2025-03-14', '1', '2025-03-12 18:31:56', '2025-03-14 00:18:03', 1),
(32, 'school', 'training', 'face-to-face', 'sample', '2025-03-14', '2025-03-14', 'sample', '2025-03-13 00:40:41', '2025-03-14 00:18:03', 1),
(33, 'meeting', 'training', 'hybrid-blended', 'mph', '2025-03-14', '2025-03-15', 'sample', '2025-03-13 01:10:21', '2025-03-15 11:15:40', 1),
(34, 'sample', 'activity', 'face-to-face', 'sam[ple venue', '2025-03-14', '2025-03-14', 'sample', '2025-03-13 01:20:08', '2025-03-14 00:18:03', 1),
(53, 'sample', 'training', 'face-to-face', 'sample', '2025-03-16', '2025-03-18', 'sample', '2025-03-13 23:09:21', '2025-03-18 00:16:54', 1),
(54, '1', 'training', 'face-to-face', '7', '2025-03-15', '2025-03-15', '1', '2025-03-14 01:52:17', '2025-03-14 08:53:07', 1),
(56, 'SAMPLE ', 'training', 'face-to-face', 'Sample Venue', '2025-03-18', '2025-03-19', 'Sample Name', '2025-03-16 22:56:52', '2025-03-19 06:55:05', 1),
(57, 'Event 2', 'training', 'face-to-face', 'Sample Venue', '2025-03-19', '2025-03-19', 'Sample Name', '2025-03-17 19:10:34', '2025-03-19 00:23:06', 1),
(58, 'New event', 'training', 'face-to-face', 'Sample Venue', '2025-03-03', '2025-03-04', 'Sample Name', '2025-03-17 22:46:09', '2025-03-18 05:46:10', 1),
(59, 'Sample 2', 'training', 'face-to-face', 'Sample Venue', '2025-03-21', '2025-03-21', 'Sample Name', '2025-03-17 23:21:36', '2025-03-21 06:30:54', 0),
(60, 'Sample Event 3', 'training', 'face-to-face', 'Sample Venue', '2025-03-22', '2025-03-22', 'Sample Name', '2025-03-17 23:23:27', '2025-03-21 03:09:35', 0),
(61, 'SAMPLE EVENT 47', 'training', 'online', '', '2025-03-22', '2025-03-22', 'Sample Name', '2025-03-19 23:15:49', '2025-03-21 00:50:46', 0),
(62, 'Sample Event 4', 'training', 'face-to-face', 'Sample Venue', '2025-03-22', '2025-03-22', 'Sample Name', '2025-03-20 20:37:20', '2025-03-20 20:37:20', 0),
(63, 'SAMPLE SAME DAY', 'training', 'face-to-face', 'Sample Venue', '2025-03-21', '2025-03-21', 'Sample Name', '2025-03-20 20:54:02', '2025-03-21 06:30:36', 0);

-- --------------------------------------------------------

--
-- Table structure for table `event_days`
--

CREATE TABLE `event_days` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `day_date` date NOT NULL,
  `day_number` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_days`
--

INSERT INTO `event_days` (`id`, `event_id`, `day_date`, `day_number`, `start_time`, `end_time`) VALUES
(30, 53, '2025-03-16', 1, '08:00:00', '17:00:00'),
(31, 53, '2025-03-17', 2, '13:00:00', '17:00:00'),
(32, 53, '2025-03-18', 3, '08:00:00', '11:00:00'),
(33, 54, '2025-03-15', 1, '08:00:00', '17:00:00'),
(36, 56, '2025-03-18', 1, '08:00:00', '17:00:00'),
(37, 56, '2025-03-19', 2, '08:00:00', '17:00:00'),
(38, 57, '2025-03-19', 1, '08:00:00', '17:00:00'),
(39, 58, '2025-03-03', 1, '08:00:00', '17:00:00'),
(40, 58, '2025-03-04', 2, '08:00:00', '17:00:00'),
(41, 59, '2025-03-20', 1, '08:00:00', '17:00:00'),
(42, 60, '2025-03-19', 1, '08:00:00', '17:00:00'),
(43, 61, '2025-03-21', 1, '08:00:00', '17:00:00'),
(44, 62, '2025-03-22', 1, '08:00:00', '17:00:00'),
(45, 63, '2025-03-21', 1, '08:00:00', '17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `funding_sources`
--

CREATE TABLE `funding_sources` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `source` enum('MOOE','SEF','PSF','Other') NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `other_specify` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `funding_sources`
--

INSERT INTO `funding_sources` (`id`, `event_id`, `source`, `amount`, `other_specify`, `created_at`, `updated_at`) VALUES
(27, 24, 'MOOE', 5000.00, NULL, '2025-03-13 01:31:56', '2025-03-13 01:31:56'),
(38, 32, 'MOOE', 1000.00, NULL, '2025-03-13 07:40:41', '2025-03-13 07:40:41'),
(39, 33, 'MOOE', 1.00, NULL, '2025-03-13 08:10:21', '2025-03-13 08:10:21'),
(40, 34, 'MOOE', 500000.00, NULL, '2025-03-13 08:20:08', '2025-03-13 08:20:08'),
(59, 53, 'MOOE', 5.00, NULL, '2025-03-14 06:09:21', '2025-03-14 06:09:21'),
(60, 54, 'MOOE', 1.00, NULL, '2025-03-14 08:52:17', '2025-03-14 08:52:17'),
(62, 56, 'MOOE', 100000.00, NULL, '2025-03-17 05:56:53', '2025-03-17 05:56:53'),
(63, 57, 'MOOE', 20.00, NULL, '2025-03-18 02:10:35', '2025-03-18 02:10:35'),
(64, 58, 'SEF', 100000.00, NULL, '2025-03-18 05:46:10', '2025-03-18 05:46:10'),
(65, 59, 'PSF', 100.00, NULL, '2025-03-18 06:21:36', '2025-03-18 06:21:36'),
(66, 60, 'MOOE', 10.00, NULL, '2025-03-18 06:23:27', '2025-03-18 06:23:27'),
(67, 61, 'MOOE', 47.00, NULL, '2025-03-20 06:15:49', '2025-03-20 06:15:49'),
(68, 62, 'MOOE', 10.00, NULL, '2025-03-21 03:37:20', '2025-03-21 03:37:20'),
(69, 63, 'MOOE', 10.00, NULL, '2025-03-21 03:54:02', '2025-03-21 03:54:02');

-- --------------------------------------------------------

--
-- Table structure for table `meal_plan`
--

CREATE TABLE `meal_plan` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `day_date` date NOT NULL,
  `meal_types` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_plan`
--

INSERT INTO `meal_plan` (`id`, `event_id`, `day_date`, `meal_types`) VALUES
(4, 53, '2025-03-16', 'Breakfast, AM Snack'),
(5, 53, '2025-03-17', 'Breakfast'),
(6, 53, '2025-03-18', 'Breakfast'),
(7, 54, '2025-03-15', 'Breakfast'),
(10, 56, '2025-03-18', 'Breakfast, AM Snack, Lunch'),
(11, 56, '2025-03-19', 'PM Snack, Dinner'),
(12, 57, '2025-03-19', 'Breakfast, AM Snack, Lunch, PM Snack'),
(13, 58, '2025-03-03', 'Breakfast, AM Snack, Lunch'),
(14, 58, '2025-03-04', 'Breakfast, AM Snack, Lunch, PM Snack'),
(15, 59, '2025-03-20', 'Breakfast, AM Snack, Lunch, PM Snack'),
(16, 60, '2025-03-19', 'Breakfast, AM Snack, Lunch'),
(17, 61, '2025-03-21', 'Breakfast, AM Snack, Lunch, PM Snack'),
(18, 62, '2025-03-22', 'Breakfast, AM Snack, Lunch, PM Snack'),
(19, 63, '2025-03-21', 'Breakfast, AM Snack');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `notification_type` enum('user','admin') NOT NULL,
  `notification_subtype` varchar(50) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `created_at`, `is_read`, `notification_type`, `notification_subtype`, `event_id`) VALUES
(1, 8, 'You have unregistered from the event: Sample 2', '2025-03-19 10:27:16', 1, 'user', NULL, NULL),
(3, 8, 'You have successfully registered for event: Sample 2', '2025-03-19 10:27:21', 1, 'user', 'event_registration', NULL),
(4, 8, 'Your certificate for event: SAMPLE  is now available to download', '2025-03-19 14:06:18', 1, 'user', 'certificate', 56),
(5, 9, 'Your certificate for event: SAMPLE  is now available to download', '2025-03-19 14:06:19', 1, 'user', 'certificate', 56),
(6, 8, 'User cess has registered for event: Sample 2', '2025-03-20 14:05:57', 0, 'admin', 'admin_event_registration', 59),
(7, 8, 'You have successfully registered for event: Sample 2', '2025-03-20 14:05:57', 1, 'user', 'event_registration', NULL),
(8, 8, 'User cess has registered for event: Sample 2', '2025-03-20 14:06:47', 0, 'admin', 'admin_event_registration', 59),
(9, 8, 'You have successfully registered for event: Sample 2', '2025-03-20 14:06:47', 1, 'user', 'event_registration', 59),
(10, 8, 'User cess has registered for event: Sample 2', '2025-03-20 14:08:27', 0, 'admin', 'admin_event_registration', 59),
(11, 8, 'You have successfully registered for event: Sample 2', '2025-03-20 14:08:27', 1, 'user', 'event_registration', 59),
(12, NULL, 'New event created. Click for more info: SAMPLE EVENT 47', '2025-03-20 14:15:50', 1, 'user', 'new_event', 61),
(13, 8, 'Your certificate for event: Sample 2 is now available to download', '2025-03-20 14:55:16', 1, 'user', 'certificate', 59),
(14, 8, 'User cess has registered for event: SAMPLE EVENT 47', '2025-03-20 15:47:30', 0, 'admin', 'admin_event_registration', 61),
(15, 8, 'You have successfully registered for event: SAMPLE EVENT 47', '2025-03-20 15:47:30', 0, 'user', 'event_registration', 61),
(16, 8, 'User cess has registered for event: Sample 2', '2025-03-20 16:25:48', 0, 'admin', 'admin_event_registration', 59),
(17, 8, 'You have successfully registered for event: Sample 2', '2025-03-20 16:25:48', 0, 'user', 'event_registration', 59),
(18, 8, 'User cess has registered for event: SAMPLE EVENT 47', '2025-03-21 08:59:38', 0, 'admin', 'admin_event_registration', 61),
(19, 8, 'You have successfully registered for event: SAMPLE EVENT 47', '2025-03-21 08:59:38', 0, 'user', 'event_registration', 61),
(20, 8, 'Reminder: The training \"SAMPLE EVENT 47\" is scheduled for tomorrow, March 22, 2025. This is an online event. Please check your email for access details.', '2025-03-21 09:10:16', 1, 'user', 'event_reminder', 61),
(21, 8, 'User cess has registered for event: Sample Event 3', '2025-03-21 10:23:58', 0, 'admin', 'admin_event_registration', 60),
(22, 8, 'You have successfully registered for event: Sample Event 3', '2025-03-21 10:23:58', 0, 'user', 'event_registration', 60),
(23, NULL, 'New event created. Click for more info: Sample Event 4', '2025-03-21 11:37:23', 0, 'user', 'new_event', 62),
(24, 8, 'Reminder: The training \"Sample Event 3\" is scheduled for tomorrow, March 22, 2025. Venue: Sample Venue', '2025-03-21 11:40:36', 1, 'user', 'event_reminder', 60),
(25, 8, 'User cess has registered for event: Sample Event 4', '2025-03-21 11:42:44', 0, 'admin', 'admin_event_registration', 62),
(26, 8, 'You have successfully registered for event: Sample Event 4', '2025-03-21 11:42:44', 1, 'user', 'event_registration', 62),
(27, 8, 'User cess has registered for event: Sample Event 4', '2025-03-21 11:47:57', 0, 'admin', 'admin_event_registration', 62),
(28, 8, 'You have successfully registered for event: Sample Event 4', '2025-03-21 11:47:57', 1, 'user', 'event_registration', 62),
(29, NULL, 'New event created. Click for more info: SAMPLE SAME DAY', '2025-03-21 11:54:04', 1, 'user', 'new_event', 63),
(31, 8, 'Reminder: The training \"Sample Event 4\" is scheduled for tomorrow, March 22, 2025. Venue: Sample Venue', '2025-03-21 14:06:00', 1, 'user', 'event_reminder', 62);

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
(1, 24, 8, '2025-03-13 14:40:33'),
(2, 33, 8, '2025-03-14 01:12:29'),
(5, 54, 8, '2025-03-14 09:28:22'),
(8, 32, 8, '2025-03-17 03:08:28'),
(9, 53, 8, '2025-03-17 03:31:52'),
(10, 56, 9, '2025-03-17 06:41:19'),
(11, 56, 8, '2025-03-17 06:41:47'),
(21, 59, 8, '2025-03-20 08:25:48'),
(22, 61, 8, '2025-03-21 00:59:38'),
(23, 60, 8, '2025-03-21 02:23:58'),
(25, 62, 8, '2025-03-21 03:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `school_level`
--

CREATE TABLE `school_level` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_level`
--

INSERT INTO `school_level` (`id`, `name`) VALUES
(1, 'Elementary'),
(2, 'Junior High School'),
(3, 'Senior High School');

-- --------------------------------------------------------

--
-- Table structure for table `school_participants`
--

CREATE TABLE `school_participants` (
  `id` int(11) NOT NULL,
  `eligible_participant_id` int(11) NOT NULL,
  `school_level` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_participants`
--

INSERT INTO `school_participants` (`id`, `eligible_participant_id`, `school_level`, `type`, `specialization`) VALUES
(25, 22, '1', '2', '1,2'),
(29, 38, '1', '2', '2'),
(31, 40, '2', '1', '9,10'),
(32, 41, '2', '2', '6,7'),
(33, 42, '1', '2', '2,3'),
(34, 43, '2', '2', '4');

-- --------------------------------------------------------

--
-- Table structure for table `speakers`
--

CREATE TABLE `speakers` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `speakers`
--

INSERT INTO `speakers` (`id`, `event_id`, `name`, `created_at`, `updated_at`) VALUES
(24, 24, '1', '2025-03-12 18:31:56', '2025-03-12 18:31:56'),
(32, 32, 'sample', '2025-03-13 00:40:41', '2025-03-13 00:40:41'),
(33, 33, 'sample', '2025-03-13 01:10:21', '2025-03-13 01:10:21'),
(34, 34, 'sample', '2025-03-13 01:20:08', '2025-03-13 01:20:08'),
(47, 53, 'sample', '2025-03-13 23:09:21', '2025-03-13 23:09:21'),
(48, 54, '1', '2025-03-14 01:52:17', '2025-03-14 01:52:17'),
(51, 56, 'Sample', '2025-03-16 22:56:52', '2025-03-16 22:56:52'),
(52, 57, 'Sample', '2025-03-17 19:10:34', '2025-03-17 19:10:34'),
(53, 58, 'Sample', '2025-03-17 22:46:09', '2025-03-17 22:46:09'),
(54, 59, 'Sample', '2025-03-17 23:21:36', '2025-03-17 23:21:36'),
(55, 60, 'Sample', '2025-03-17 23:23:27', '2025-03-17 23:23:27'),
(56, 61, 'Sample', '2025-03-19 23:15:49', '2025-03-19 23:15:49'),
(57, 62, 'Sample', '2025-03-20 20:37:20', '2025-03-20 20:37:20'),
(58, 63, 'Sample', '2025-03-20 20:54:02', '2025-03-20 20:54:02');

-- --------------------------------------------------------

--
-- Table structure for table `specialization`
--

CREATE TABLE `specialization` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialization`
--

INSERT INTO `specialization` (`id`, `name`) VALUES
(1, 'Mother Tongue'),
(2, 'Filipino'),
(3, 'English'),
(4, 'Mathematics'),
(5, 'Science'),
(6, 'Araling Panlipunan (AP)'),
(7, 'Edukasyon sa Pagpapakatao (EsP)'),
(8, 'Music, Arts, PE, and Health (MAPEH)'),
(9, 'Edukasyong Pantahanan at Pangkabuhayan (EPP)'),
(10, 'Special Education (SPED)');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `action`, `message`, `created_at`) VALUES
(1, 'event_reminder', 'Ran notification check at 2025-03-21 11:40:37. Inserted 1 notifications.', '2025-03-21 11:40:37'),
(2, 'event_reminder', 'Ran notification check at 2025-03-21 14:06:00. Inserted 1 notifications.', '2025-03-21 14:06:00');

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
(9, 'Jess', 'Tatoy', 'Constante', '', 'Male', '09123456789', 'jess@gmail.com', '$2y$10$uamFDTyS8peBVA1IOH7YKOESbyVBZK4oub81.ZEDCXFEv7Dbc26ja', '2025-03-17 06:40:35', NULL);

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
(2, 9, 1, 1, 2);

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
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

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
-- Indexes for table `division_participants`
--
ALTER TABLE `division_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eligible_participant_id` (`eligible_participant_id`);

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
-- Indexes for table `event_days`
--
ALTER TABLE `event_days`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`day_date`);

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `school_level`
--
ALTER TABLE `school_level`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_participants`
--
ALTER TABLE `school_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eligible_participant_id` (`eligible_participant_id`);

--
-- Indexes for table `speakers`
--
ALTER TABLE `speakers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `specialization`
--
ALTER TABLE `specialization`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
-- AUTO_INCREMENT for table `division_participants`
--
ALTER TABLE `division_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=445;

--
-- AUTO_INCREMENT for table `eligible_participants`
--
ALTER TABLE `eligible_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `event_days`
--
ALTER TABLE `event_days`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `funding_sources`
--
ALTER TABLE `funding_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `meal_plan`
--
ALTER TABLE `meal_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `registered_users`
--
ALTER TABLE `registered_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `school_level`
--
ALTER TABLE `school_level`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_participants`
--
ALTER TABLE `school_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `speakers`
--
ALTER TABLE `speakers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `specialization`
--
ALTER TABLE `specialization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users_glms`
--
ALTER TABLE `users_glms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_lnd`
--
ALTER TABLE `users_lnd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users_perf`
--
ALTER TABLE `users_perf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `class_position`
--
ALTER TABLE `class_position`
  ADD CONSTRAINT `class_position_ibfk_1` FOREIGN KEY (`classification_id`) REFERENCES `classification` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `division_participants`
--
ALTER TABLE `division_participants`
  ADD CONSTRAINT `division_participants_ibfk_1` FOREIGN KEY (`eligible_participant_id`) REFERENCES `eligible_participants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eligible_participants`
--
ALTER TABLE `eligible_participants`
  ADD CONSTRAINT `eligible_participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_days`
--
ALTER TABLE `event_days`
  ADD CONSTRAINT `event_days_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `school_participants`
--
ALTER TABLE `school_participants`
  ADD CONSTRAINT `school_participants_ibfk_1` FOREIGN KEY (`eligible_participant_id`) REFERENCES `eligible_participants` (`id`) ON DELETE CASCADE;

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
CREATE DEFINER=`root`@`localhost` EVENT `daily_event_reminder` ON SCHEDULE EVERY 1 DAY STARTS '2025-03-21 18:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  DECLARE rows_affected INT;
  
  -- Insert notifications for tomorrow's events
  INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id)
  SELECT 
      ru.user_id,
      CONCAT('Reminder: The ', e.specification, ' "', e.title, '" is scheduled for tomorrow, ', 
             DATE_FORMAT(e.start_date, '%M %d, %Y'), 
             IF(e.delivery = 'online', 
                '. This is an online event. Please check your email for access details.', 
                CONCAT('. Venue: ', e.venue))),
      NOW(),
      0,
      'user',
      'event_reminder',
      e.id
  FROM events e
  JOIN registered_users ru ON e.id = ru.event_id
  JOIN users u ON ru.user_id = u.id
  WHERE DATE(e.start_date) = CURDATE() + INTERVAL 1 DAY
      AND e.archived = 0
      AND u.deleted_at IS NULL
      -- Prevent duplicate notifications
      AND NOT EXISTS (
          SELECT 1 FROM notifications n 
          WHERE n.user_id = ru.user_id 
              AND n.event_id = e.id 
              AND n.notification_subtype = 'event_reminder'
              AND DATE(n.created_at) = CURDATE()
      );
  
  -- Get number of affected rows
  SET rows_affected = ROW_COUNT();
  
  -- Log the execution with details
  INSERT INTO system_logs (action, message, created_at)
  VALUES ('event_reminder', 
          CONCAT('Ran notification check at ', NOW(), '. Inserted ', rows_affected, ' notifications.'), 
          NOW());
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
