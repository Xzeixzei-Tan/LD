-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2025 at 03:51 AM
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
(1, 8, 53, '2025-03-17 16:51:32', 'certificates/53/certificate_9.docx'),
(2, 8, 56, '2025-03-18 08:18:20', 'certificates/56/certificate_11.docx'),
(3, 9, 56, '2025-03-18 08:18:21', 'certificates/56/certificate_10.docx'),
(4, 8, 56, '2025-03-18 08:33:54', 'certificates/56/certificate_cess jauod tan.docx'),
(5, 9, 56, '2025-03-18 08:33:55', 'certificates/56/certificate_Jess Tatoy Constante.docx'),
(6, 8, 56, '2025-03-18 08:38:28', 'certificates/56/certificate_cess jauod tan.docx'),
(7, 9, 56, '2025-03-18 08:38:29', 'certificates/56/certificate_Jess Tatoy Constante.docx'),
(8, 8, 56, '2025-03-18 08:48:14', 'certificates/56/certificate_11.pdf'),
(9, 9, 56, '2025-03-18 08:48:14', 'certificates/56/certificate_10.pdf'),
(10, 8, 56, '2025-03-18 09:05:27', 'certificates/56/certificate_11.pdf'),
(11, 9, 56, '2025-03-18 09:05:28', 'certificates/56/certificate_10.pdf'),
(12, 8, 56, '2025-03-18 09:09:24', 'certificates/56/certificate_cess jauod tan.pdf'),
(13, 9, 56, '2025-03-18 09:09:27', 'certificates/56/certificate_Jess Tatoy Constante.pdf'),
(14, 8, 56, '2025-03-18 09:10:23', 'certificates/56/certificate_11.pdf'),
(15, 9, 56, '2025-03-18 09:10:25', 'certificates/56/certificate_10.pdf'),
(16, 8, 56, '2025-03-18 09:18:35', 'certificates/SAMPLE_/cess_jauod_tan.pdf'),
(17, 9, 56, '2025-03-18 09:18:36', 'certificates/SAMPLE_/Jess_Tatoy_Constante.pdf'),
(18, 8, 56, '2025-03-18 09:21:42', 'certificates/SAMPLE_/cess_jauod_tan.pdf'),
(19, 9, 56, '2025-03-18 09:21:44', 'certificates/SAMPLE_/Jess_Tatoy_Constante.pdf'),
(20, 8, 56, '2025-03-18 09:43:16', 'certificates/SAMPLE_/cess_jauod_tan.pdf'),
(21, 9, 56, '2025-03-18 09:43:18', 'certificates/SAMPLE_/Jess_Tatoy_Constante.pdf'),
(22, 9, 56, '2025-03-19 13:24:56', 'certificates/bday_mo____bday_ko_rin__/Alessandra_Gupong_Castillas.pdf'),
(23, 9, 56, '2025-03-19 13:25:19', 'certificates/bday_mo____bday_ko_rin__/Alessandra_Gupong_Castillas.pdf'),
(24, 9, 56, '2025-03-19 13:39:19', 'certificates/bday_mo____bday_ko_rin__/Alessandra_Gupong_Castillas.pdf'),
(25, 9, 56, '2025-03-19 13:47:18', 'certificates/bday_mo____bday_ko_rin__/Alessandra_Gupong_Castillas.pdf'),
(26, 9, 56, '2025-03-19 13:48:12', 'certificates/bday_mo____bday_ko_rin__/Alessandra_Gupong_Castillas.pdf'),
(27, 9, 58, '2025-03-19 13:51:14', 'certificates/bday_ni_alex/Alessandra_Gupong_Castillas.pdf'),
(28, 9, 58, '2025-03-19 14:15:53', 'certificates/bday_ni_alex/Alessandra_Gupong_Castillas.pdf'),
(29, 9, 56, '2025-03-19 15:27:31', 'certificates/bday_mo____bday_ko_rin__/Alessandra_Gupong_Castillas.pdf'),
(30, 9, 58, '2025-03-19 15:28:44', 'certificates/bday_ni_alex/Alessandra_Gupong_Castillas.pdf');

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
(357, 41, 'Office of the Assistant Schools Division Superintendent'),
(358, 41, 'Legal Services'),
(359, 41, 'ICT Services'),
(360, 41, 'Administrative Services'),
(361, 41, 'Personnel'),
(362, 41, 'Records'),
(363, 41, 'Cashier'),
(364, 41, 'Supply & Property'),
(365, 41, 'Budget'),
(366, 41, 'Finance'),
(367, 41, 'Curriculum Implementation Division (CID)'),
(368, 41, 'Learning Resource Management'),
(369, 41, 'Curriculum Implementation Management'),
(370, 41, 'District Instructional Management'),
(371, 41, 'Alternative Learning System'),
(372, 41, 'School Governance and Operations Division (SGOD)'),
(373, 41, 'School Management Monitoring & Evaluation'),
(374, 41, 'Human Resource Development'),
(375, 41, 'Social Mobilization and Networking'),
(376, 41, 'Planning & Research'),
(377, 41, 'Education Facilities'),
(378, 41, 'School Health & Nutrition Unit'),
(379, 42, 'Office of the Assistant Schools Division Superintendent'),
(380, 42, 'Legal Services'),
(381, 42, 'ICT Services'),
(382, 42, 'Administrative Services'),
(383, 42, 'Personnel'),
(384, 42, 'Records'),
(385, 42, 'Cashier'),
(386, 42, 'Supply & Property'),
(387, 42, 'Budget'),
(388, 42, 'Finance'),
(389, 42, 'Curriculum Implementation Division (CID)'),
(390, 42, 'Learning Resource Management'),
(391, 42, 'Curriculum Implementation Management'),
(392, 42, 'District Instructional Management'),
(393, 42, 'Alternative Learning System'),
(394, 42, 'School Governance and Operations Division (SGOD)'),
(395, 42, 'School Management Monitoring & Evaluation'),
(396, 42, 'Human Resource Development'),
(397, 42, 'Social Mobilization and Networking'),
(398, 42, 'Planning & Research'),
(399, 42, 'Education Facilities'),
(400, 42, 'School Health & Nutrition Unit'),
(401, 43, 'Office of the Assistant Schools Division Superintendent'),
(402, 43, 'Legal Services'),
(403, 43, 'ICT Services'),
(404, 43, 'Administrative Services'),
(405, 43, 'Personnel'),
(406, 43, 'Records'),
(407, 43, 'Cashier'),
(408, 43, 'Supply & Property'),
(409, 43, 'Budget'),
(410, 43, 'Finance'),
(411, 43, 'Curriculum Implementation Division (CID)'),
(412, 43, 'Learning Resource Management'),
(413, 43, 'Curriculum Implementation Management'),
(414, 43, 'District Instructional Management'),
(415, 43, 'Alternative Learning System'),
(416, 43, 'School Governance and Operations Division (SGOD)'),
(417, 43, 'School Management Monitoring & Evaluation'),
(418, 43, 'Human Resource Development'),
(419, 43, 'Social Mobilization and Networking'),
(420, 43, 'Planning & Research'),
(421, 43, 'Education Facilities'),
(422, 43, 'School Health & Nutrition Unit'),
(423, 44, 'Office of the Assistant Schools Division Superintendent'),
(424, 44, 'Legal Services'),
(425, 44, 'ICT Services'),
(426, 44, 'Administrative Services'),
(427, 44, 'Personnel'),
(428, 44, 'Records'),
(429, 44, 'Cashier'),
(430, 44, 'Supply & Property'),
(431, 44, 'Budget'),
(432, 44, 'Finance'),
(433, 44, 'Curriculum Implementation Division (CID)'),
(434, 44, 'Learning Resource Management'),
(435, 44, 'Curriculum Implementation Management'),
(436, 44, 'District Instructional Management'),
(437, 44, 'Alternative Learning System'),
(438, 44, 'School Governance and Operations Division (SGOD)'),
(439, 44, 'School Management Monitoring & Evaluation'),
(440, 44, 'Human Resource Development'),
(441, 44, 'Social Mobilization and Networking'),
(442, 44, 'Planning & Research'),
(443, 44, 'Education Facilities'),
(444, 44, 'School Health & Nutrition Unit'),
(489, 49, 'Legal Services'),
(490, 49, 'ICT Services'),
(497, 66, 'ICT Services'),
(498, 71, 'Office of the Assistant Schools Division Superintendent'),
(499, 71, 'Legal Services'),
(500, 71, 'ICT Services'),
(501, 71, 'Administrative Services'),
(502, 71, 'Personnel'),
(503, 71, 'Records'),
(504, 71, 'Cashier'),
(505, 71, 'Supply & Property'),
(506, 71, 'Budget'),
(507, 71, 'Finance'),
(508, 71, 'Curriculum Implementation Division (CID)'),
(509, 71, 'Learning Resource Management'),
(510, 71, 'Curriculum Implementation Management'),
(511, 71, 'District Instructional Management'),
(512, 71, 'Alternative Learning System'),
(513, 71, 'School Governance and Operations Division (SGOD)'),
(514, 71, 'School Management Monitoring & Evaluation'),
(515, 71, 'Human Resource Development'),
(516, 71, 'Social Mobilization and Networking'),
(517, 71, 'Planning & Research'),
(518, 71, 'Education Facilities'),
(519, 71, 'School Health & Nutrition Unit'),
(520, 79, 'Office of the Assistant Schools Division Superintendent'),
(521, 79, 'Legal Services'),
(522, 79, 'ICT Services'),
(523, 79, 'Administrative Services'),
(524, 79, 'Personnel'),
(525, 79, 'Records'),
(526, 79, 'Cashier'),
(527, 79, 'Supply & Property'),
(528, 79, 'Budget'),
(529, 79, 'Finance'),
(530, 79, 'Curriculum Implementation Division (CID)'),
(531, 79, 'Learning Resource Management'),
(532, 79, 'Curriculum Implementation Management'),
(533, 79, 'District Instructional Management'),
(534, 79, 'Alternative Learning System'),
(535, 79, 'School Governance and Operations Division (SGOD)'),
(536, 79, 'School Management Monitoring & Evaluation'),
(537, 79, 'Human Resource Development'),
(538, 79, 'Social Mobilization and Networking'),
(539, 79, 'Planning & Research'),
(540, 79, 'Education Facilities'),
(541, 79, 'School Health & Nutrition Unit'),
(543, 81, 'ICT Services');

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
(40, 56, 'Both'),
(41, 57, 'Both'),
(42, 58, 'Both'),
(43, 59, 'Division'),
(44, 60, 'Both'),
(49, 63, 'Both'),
(52, 61, ''),
(66, 62, 'Division'),
(71, 65, 'Division'),
(72, 66, 'School'),
(73, 67, 'School'),
(75, 68, 'School'),
(76, 69, 'School'),
(77, 70, 'School'),
(78, 71, 'School'),
(79, 72, 'Division'),
(81, 73, 'Division'),
(82, 64, 'School');

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
  `archived` tinyint(1) DEFAULT 0,
  `estimated_participants` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `specification`, `delivery`, `venue`, `start_date`, `end_date`, `proponent`, `created_at`, `updated_at`, `archived`, `estimated_participants`) VALUES
(24, 'division', 'training', 'online', '', '2025-03-14', '2025-03-14', '1', '2025-03-12 18:31:56', '2025-03-14 00:18:03', 1, 0),
(32, 'school', 'training', 'face-to-face', 'sample', '2025-03-14', '2025-03-14', 'sample', '2025-03-13 00:40:41', '2025-03-14 00:18:03', 1, 0),
(33, 'meeting', 'training', 'hybrid-blended', 'mph', '2025-03-14', '2025-03-15', 'sample', '2025-03-13 01:10:21', '2025-03-15 11:15:40', 1, 0),
(34, 'sample', 'activity', 'face-to-face', 'sam[ple venue', '2025-03-14', '2025-03-14', 'sample', '2025-03-13 01:20:08', '2025-03-14 00:18:03', 1, 0),
(53, 'sample', 'training', 'face-to-face', 'sample', '2025-03-16', '2025-03-18', 'sample', '2025-03-13 23:09:21', '2025-03-18 00:19:05', 1, 0),
(54, '1', 'training', 'face-to-face', '7', '2025-03-15', '2025-03-15', '1', '2025-03-14 01:52:17', '2025-03-14 08:53:07', 1, 0),
(56, 'bday mo??? bday ko rin!!', 'activity', 'face-to-face', 'sa imong tanan', '2025-03-19', '2025-03-20', 'booricakes', '2025-03-18 22:08:27', '2025-03-20 00:35:56', 1, 0),
(57, 'bday mo?? bday ko rin!!', 'activity', 'face-to-face', 'sa imong tanan', '2025-03-19', '2025-03-20', 'booricakes', '2025-03-18 22:17:52', '2025-03-20 00:35:56', 1, 0),
(58, 'bday ni alex', 'activity', 'face-to-face', 'mph', '2025-03-19', '2025-03-20', 'bsit', '2025-03-18 22:50:29', '2025-03-20 00:35:56', 1, 0),
(59, 'birthday ko', 'training', 'face-to-face', 'mph', '2025-03-19', '2025-03-19', 'bsit', '2025-03-18 23:14:32', '2025-03-19 06:14:32', 1, 0),
(60, 'Bday', 'training', 'face-to-face', 'Sa imong tanan', '2025-03-20', '2025-03-20', 'Boo', '2025-03-19 18:10:12', '2025-03-20 01:10:13', 1, 0),
(61, 'bIRTHDAY NI ALEX', 'activity', 'face-to-face', 'mph', '2025-03-20', '2025-03-21', 'gorjuice', '2025-03-19 18:26:07', '2025-03-21 00:48:45', 1, 0),
(62, 'thesis', 'training', 'hybrid-blended', 'mph', '2025-03-21', '2025-03-22', 'house', '2025-03-19 20:26:39', '2025-03-24 00:25:36', 1, 12),
(63, 'tgif', 'activity', 'face-to-face', 'mph', '2025-03-20', '2025-03-22', 'gorjuice', '2025-03-20 01:08:44', '2025-03-24 00:25:36', 1, 0),
(64, 'Graduation day', 'activity', 'face-to-face', 'mph', '2025-04-01', '2025-04-03', 'cvsu ceit', '2025-03-20 19:50:07', '2025-03-23 19:11:07', 0, 47),
(65, 'Today', 'activity', 'face-to-face', 'mph', '2025-03-21', '2025-03-21', 'Division office', '2025-03-20 22:45:34', '2025-03-21 05:45:34', 1, 0),
(66, 'april fools day', 'activity', 'face-to-face', 'icon', '2025-04-01', '2025-04-02', 'gorjuice', '2025-03-20 22:51:52', '2025-03-20 22:51:52', 0, 0),
(67, 'april fools day', 'activity', 'face-to-face', 'icon', '2025-04-01', '2025-04-02', 'gorjuice', '2025-03-20 22:52:56', '2025-03-20 22:52:56', 0, 0),
(68, 'presentation', 'activity', 'face-to-face', 'mph', '2025-03-21', '2025-03-23', 'gorjuice', '2025-03-20 23:05:07', '2025-03-24 00:25:36', 1, 56),
(69, 'THANKS JESS', 'activity', 'face-to-face', 'ICT UNIT', '2025-03-21', '2025-03-23', 'gorjuice', '2025-03-20 23:14:16', '2025-03-24 00:25:36', 1, 2025),
(70, 'hershey & jansen wedding', 'activity', 'face-to-face', 'baguio city', '2025-03-22', '2025-03-23', 'gorjuice', '2025-03-20 23:17:08', '2025-03-24 00:25:36', 1, 2025),
(71, 'hershey & jansen wedding', 'activity', 'face-to-face', 'baguio city', '2025-03-22', '2025-03-23', 'gorjuice', '2025-03-20 23:18:23', '2025-03-24 00:25:36', 1, 2025),
(72, 'jess & darrik wedding', 'activity', 'face-to-face', 'sa tabi lang', '2025-03-22', '2025-03-23', 'gorjuice', '2025-03-20 23:30:38', '2025-03-24 00:25:36', 1, 2025),
(73, 'cess & marvin wedding', 'activity', 'hybrid-blended', 'sa imong tanan', '2025-03-22', '2025-03-24', 'gorjuice', '2025-03-20 23:44:32', '2025-03-24 00:25:36', 1, 65);

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
(36, 56, '2025-03-19', 1, '08:00:00', '17:00:00'),
(37, 56, '2025-03-20', 2, '08:00:00', '17:00:00'),
(38, 57, '2025-03-19', 1, '08:00:00', '17:00:00'),
(39, 57, '2025-03-20', 2, '08:00:00', '17:00:00'),
(40, 58, '2025-03-19', 1, '08:00:00', '17:00:00'),
(41, 58, '2025-03-20', 2, '08:00:00', '17:00:00'),
(42, 59, '2025-03-19', 1, '08:00:00', '17:00:00'),
(43, 60, '2025-03-20', 1, '08:00:00', '17:00:00'),
(57, 63, '2025-03-20', 1, '08:00:00', '17:00:00'),
(58, 63, '2025-03-21', 2, '08:00:00', '17:00:00'),
(59, 63, '2025-03-22', 3, '08:00:00', '17:00:00'),
(64, 61, '2025-03-20', 1, '08:00:00', '17:00:00'),
(65, 61, '2025-03-21', 2, '08:00:00', '17:00:00'),
(128, 62, '2025-03-21', 1, '08:00:00', '17:00:00'),
(129, 62, '2025-03-22', 2, '08:00:00', '17:00:00'),
(130, 62, '2025-03-23', 3, '08:00:00', '17:00:00'),
(131, 62, '2025-03-24', 4, '14:28:00', '15:28:00'),
(140, 65, '2025-03-21', 1, '08:00:00', '17:00:00'),
(141, 66, '2025-04-01', 1, '08:00:00', '17:00:00'),
(142, 66, '2025-04-02', 2, '08:00:00', '17:00:00'),
(146, 68, '2025-03-21', 1, '08:00:00', '17:00:00'),
(147, 68, '2025-03-22', 2, '08:00:00', '17:00:00'),
(148, 68, '2025-03-23', 3, '08:00:00', '17:00:00'),
(149, 69, '2025-03-21', 1, '08:00:00', '17:00:00'),
(150, 69, '2025-03-22', 2, '08:00:00', '17:00:00'),
(151, 69, '2025-03-23', 3, '08:00:00', '17:00:00'),
(152, 70, '2025-03-22', 1, '08:00:00', '17:00:00'),
(153, 70, '2025-03-23', 2, '08:00:00', '17:00:00'),
(154, 72, '2025-03-22', 1, '08:00:00', '17:00:00'),
(155, 72, '2025-03-23', 2, '08:00:00', '17:00:00'),
(159, 73, '2025-03-22', 1, '08:00:00', '17:00:00'),
(160, 73, '2025-03-23', 2, '08:00:00', '17:00:00'),
(161, 73, '2025-03-24', 3, '08:00:00', '17:00:00'),
(162, 64, '2025-04-01', 1, '08:00:00', '18:00:00'),
(163, 64, '2025-04-02', 2, '08:00:00', '17:00:00');

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
(62, 56, 'MOOE', 1.00, NULL, '2025-03-19 05:08:29', '2025-03-19 05:08:29'),
(63, 57, 'MOOE', 1.00, NULL, '2025-03-19 05:17:52', '2025-03-19 05:17:52'),
(64, 58, 'MOOE', 1.00, NULL, '2025-03-19 05:50:29', '2025-03-19 05:50:29'),
(65, 59, 'MOOE', -1.00, NULL, '2025-03-19 06:14:32', '2025-03-19 06:14:32'),
(66, 59, 'SEF', 0.00, NULL, '2025-03-19 06:14:32', '2025-03-19 06:14:32'),
(67, 59, 'PSF', 0.00, NULL, '2025-03-19 06:14:32', '2025-03-19 06:14:32'),
(68, 59, 'Other', 0.00, NULL, '2025-03-19 06:14:32', '2025-03-19 06:14:32'),
(69, 60, 'MOOE', 3.00, NULL, '2025-03-20 01:10:12', '2025-03-20 01:10:12'),
(74, 63, 'MOOE', 12.00, NULL, '2025-03-20 08:08:44', '2025-03-20 08:08:44'),
(75, 63, 'SEF', 0.00, NULL, '2025-03-20 08:08:44', '2025-03-20 08:08:44'),
(78, 61, 'MOOE', 2.00, NULL, '2025-03-20 08:45:22', '2025-03-20 08:45:22'),
(104, 62, 'MOOE', 2.00, NULL, '2025-03-21 02:25:00', '2025-03-21 02:25:00'),
(105, 62, 'SEF', 0.00, NULL, '2025-03-21 02:25:00', '2025-03-21 02:25:00'),
(106, 62, 'PSF', 0.00, NULL, '2025-03-21 02:25:00', '2025-03-21 02:25:00'),
(112, 65, 'MOOE', 2000.00, NULL, '2025-03-21 05:45:34', '2025-03-21 05:45:34'),
(113, 66, 'MOOE', 6000.00, NULL, '2025-03-21 05:51:52', '2025-03-21 05:51:52'),
(114, 67, 'MOOE', 6000.00, NULL, '2025-03-21 05:52:56', '2025-03-21 05:52:56'),
(116, 68, 'MOOE', 10000.00, NULL, '2025-03-21 06:06:25', '2025-03-21 06:06:25'),
(117, 69, 'MOOE', 470.00, NULL, '2025-03-21 06:14:16', '2025-03-21 06:14:16'),
(118, 70, 'MOOE', 1.00, NULL, '2025-03-21 06:17:08', '2025-03-21 06:17:08'),
(119, 71, 'MOOE', 1.00, NULL, '2025-03-21 06:18:23', '2025-03-21 06:18:23'),
(120, 72, 'MOOE', 0.00, NULL, '2025-03-21 06:30:38', '2025-03-21 06:30:38'),
(122, 73, 'MOOE', 1.00, NULL, '2025-03-21 06:46:49', '2025-03-21 06:46:49');

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
(10, 56, '2025-03-19', 'Breakfast, AM Snack, Lunch, PM Snack'),
(11, 56, '2025-03-20', 'Breakfast, AM Snack, Lunch, PM Snack'),
(12, 57, '2025-03-19', 'Breakfast, AM Snack, Lunch, PM Snack'),
(13, 58, '2025-03-19', 'Breakfast, AM Snack, Lunch'),
(14, 59, '2025-03-19', 'Breakfast, AM Snack, Lunch, PM Snack'),
(23, 63, '2025-03-20', 'Breakfast'),
(24, 63, '2025-03-21', 'Breakfast'),
(25, 63, '2025-03-22', 'AM Snack'),
(44, 62, '2025-03-21', 'Breakfast, AM Snack, Lunch, PM Snack, Dinner'),
(45, 62, '2025-03-22', 'Breakfast, AM Snack, Lunch, PM Snack, Dinner'),
(52, 65, '2025-03-21', 'Breakfast, AM Snack, Lunch, PM Snack, Dinner'),
(53, 66, '2025-04-01', 'Breakfast'),
(54, 66, '2025-04-02', 'Breakfast'),
(58, 68, '2025-03-21', 'Breakfast, AM Snack, Lunch, PM Snack'),
(59, 68, '2025-03-22', 'Breakfast, AM Snack, Lunch'),
(60, 68, '2025-03-23', 'Breakfast, AM Snack, Lunch, PM Snack'),
(61, 69, '2025-03-21', 'Breakfast, AM Snack'),
(62, 69, '2025-03-22', 'Breakfast, AM Snack'),
(63, 69, '2025-03-23', 'AM Snack'),
(64, 70, '2025-03-22', 'Breakfast, AM Snack, Lunch, PM Snack'),
(65, 70, '2025-03-23', 'Dinner'),
(66, 72, '2025-03-22', 'Breakfast, AM Snack, Lunch, PM Snack'),
(67, 72, '2025-03-23', 'Dinner'),
(71, 73, '2025-03-22', 'Dinner'),
(72, 73, '2025-03-23', 'Dinner'),
(73, 73, '2025-03-24', 'Dinner'),
(74, 64, '2025-04-01', 'Breakfast, AM Snack, Lunch, PM Snack, Dinner');

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
(1, 9, 'You have successfully registered for event: ', '2025-03-13 16:07:44', 0, 'user', 'event_registration', 0),
(2, 9, 'You have successfully registered for event: Tech Conference 2025', '2025-03-13 16:09:57', 0, 'admin', 'admin_event_registration', 0),
(3, 9, 'You have successfully registered for event: Digital Skills Enhancement Seminar', '2025-03-13 16:42:29', 0, 'user', 'event_registration', 0),
(4, 9, 'You have unregistered from the event: Cybersecurity Awareness Seminar', '2025-03-13 16:45:21', 0, 'user', 'event_unregistration', 0),
(5, 9, 'You have successfully registered for event: Cybersecurity Awareness Seminar', '2025-03-13 16:45:25', 0, 'admin', 'admin_event_registration', 0),
(6, 9, 'You have unregistered from the event: Cybersecurity Awareness Seminar', '2025-03-13 16:46:49', 0, 'user', 'event_unregistration', 0),
(7, 9, 'User jess has registered for event: Cybersecurity Awareness Seminar', '2025-03-13 16:47:24', 0, 'admin', 'admin_event_registration', 0),
(8, 8, 'User cess has registered for event: school', '2025-03-14 09:24:26', 0, 'admin', 'admin_event_registration', 0),
(9, 9, 'Welcome to the platform! Your account has been created successfully.', '2025-03-14 09:00:00', 0, 'user', 'signup', 0),
(10, 9, 'Congratulations! You have earned a certificate for completing \"Introduction to SQL\".', '2025-03-14 10:30:00', 0, 'user', 'certificate', 0),
(11, 9, 'Your event registration for \"Data Science Workshop\" has been confirmed.', '2025-03-14 11:15:00', 0, 'user', 'event_registration', 0),
(12, 9, 'New user john_doe has signed up on the platform.', '2025-03-14 12:00:00', 0, 'admin', 'new_user_signup', 0),
(13, 9, 'A certificate has been issued to user sarah_smith for \"Advanced Database Management\".', '2025-03-14 13:45:00', 0, 'admin', 'certificate_issued', 0),
(14, 8, 'User cess has registered for event: sample', '2025-03-14 16:16:14', 0, 'admin', NULL, 51),
(15, 8, 'You have successfully registered for event: sample', '2025-03-14 16:16:14', 0, 'user', 'event_registration', NULL),
(16, 8, 'User cess has registered for event: 1', '2025-03-14 17:28:22', 0, 'admin', NULL, 54),
(17, 8, 'You have successfully registered for event: 1', '2025-03-14 17:28:22', 0, 'user', 'event_registration', NULL),
(18, 9, 'Welcome to the platform! Your account has been created successfully.', '2025-03-18 01:21:14', 0, 'user', 'signup', NULL),
(19, NULL, 'New user Alessandra Castillas has signed up on the platform.', '2025-03-18 01:21:14', 0, 'admin', 'new_user_signup', NULL),
(20, NULL, 'New event created. Click for more info: bday mo?? bday ko rin!!', '2025-03-19 13:17:52', 0, 'user', 'new_event', 57),
(21, 9, 'User Alessandra has registered for event: bday mo??? bday ko rin!!', '2025-03-19 13:19:07', 0, 'admin', NULL, 56),
(22, 9, 'You have successfully registered for event: bday mo??? bday ko rin!!', '2025-03-19 13:19:07', 0, 'user', 'event_registration', NULL),
(23, 9, 'Your certificate for event: bday mo??? bday ko rin!! is now available for download', '2025-03-19 13:24:56', 0, 'user', 'certificate', 56),
(24, 9, 'Your certificate for event: bday mo??? bday ko rin!! is now available for download', '2025-03-19 13:25:19', 1, 'user', 'certificate', 56),
(25, 9, 'Your certificate for event: bday mo??? bday ko rin!! is now available for download', '2025-03-19 13:39:19', 1, 'user', 'certificate', 56),
(26, 9, 'Your certificate for event: bday mo??? bday ko rin!! is now available for download', '2025-03-19 13:47:18', 1, 'user', 'certificate', 56),
(27, 9, 'Your certificate for event: bday mo??? bday ko rin!! is now available for download', '2025-03-19 13:48:12', 1, 'user', 'certificate', 56),
(28, NULL, 'New event created. Click for more info: bday ni alex', '2025-03-19 13:50:29', 0, 'user', 'new_event', 58),
(29, 9, 'User Alessandra has registered for event: bday ni alex', '2025-03-19 13:50:48', 0, 'admin', NULL, 58),
(30, 9, 'You have successfully registered for event: bday ni alex', '2025-03-19 13:50:48', 0, 'user', 'event_registration', NULL),
(31, 9, 'Your certificate for event: bday ni alex is now available for download', '2025-03-19 13:51:14', 1, 'user', 'certificate', 58),
(32, NULL, 'New event created. Click for more info: birthday ko', '2025-03-19 14:14:32', 0, 'user', 'new_event', 59),
(33, 9, 'Your certificate for event: bday ni alex is now available for download', '2025-03-19 14:15:54', 1, 'user', 'certificate', 58),
(34, 9, 'Your certificate for event: bday mo??? bday ko rin!! is now available to download', '2025-03-19 15:27:31', 1, 'user', 'certificate', 56),
(35, 9, 'Your certificate for event: bday ni alex is now available to download', '2025-03-19 15:28:44', 1, 'user', 'certificate', 58),
(36, NULL, 'New event created. Click for more info: ', '2025-03-20 08:55:56', 0, 'user', 'new_event', NULL),
(37, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:01', 0, 'user', 'new_event', NULL),
(38, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:02', 0, 'user', 'new_event', NULL),
(39, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:03', 0, 'user', 'new_event', NULL),
(40, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:03', 0, 'user', 'new_event', NULL),
(41, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:04', 0, 'user', 'new_event', NULL),
(42, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:04', 0, 'user', 'new_event', NULL),
(43, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:04', 0, 'user', 'new_event', NULL),
(44, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:05', 0, 'user', 'new_event', NULL),
(45, NULL, 'New event created. Click for more info: ', '2025-03-20 08:56:05', 0, 'user', 'new_event', NULL),
(46, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:35', 0, 'user', 'new_event', NULL),
(47, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:36', 0, 'user', 'new_event', NULL),
(48, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:36', 0, 'user', 'new_event', NULL),
(49, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:37', 0, 'user', 'new_event', NULL),
(50, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:37', 0, 'user', 'new_event', NULL),
(51, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:37', 0, 'user', 'new_event', NULL),
(52, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:38', 0, 'user', 'new_event', NULL),
(53, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:52', 0, 'user', 'new_event', NULL),
(54, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:53', 0, 'user', 'new_event', NULL),
(55, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:54', 0, 'user', 'new_event', NULL),
(56, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:54', 0, 'user', 'new_event', NULL),
(57, NULL, 'New event created. Click for more info: ', '2025-03-20 09:00:54', 0, 'user', 'new_event', NULL),
(58, NULL, 'New event created. Click for more info: Bday', '2025-03-20 09:10:13', 0, 'user', 'new_event', 60),
(59, NULL, 'New event created. Click for more info: birhday ', '2025-03-20 09:26:07', 0, 'user', 'new_event', 61),
(60, NULL, 'New event created. Click for more info: graduation day', '2025-03-20 11:26:41', 0, 'user', 'new_event', 62),
(61, 10, 'Welcome to the platform! Your account has been created successfully.', '2025-03-20 06:11:46', 0, 'user', 'signup', NULL),
(62, NULL, 'New user chelsea dudas has signed up on the platform.', '2025-03-20 06:11:46', 0, 'admin', 'new_user_signup', NULL),
(63, 11, 'Welcome to the platform! Your account has been created successfully.', '2025-03-20 06:13:47', 0, 'user', 'signup', NULL),
(64, NULL, 'New user franchesca dudas has signed up on the platform.', '2025-03-20 06:13:47', 0, 'admin', 'new_user_signup', NULL),
(65, 12, 'Welcome to the platform! Your account has been created successfully.', '2025-03-20 06:17:42', 0, 'user', 'signup', NULL),
(66, NULL, 'New user niki zefanya has signed up on the platform.', '2025-03-20 06:17:42', 0, 'admin', 'new_user_signup', NULL),
(67, NULL, 'New event created. Click for more info: tgif', '2025-03-20 16:08:44', 0, 'user', 'new_event', 63),
(68, 1, 'Event updated: Thesis day', '2025-03-21 09:39:19', 0, 'user', 'update_event', 62),
(69, 1, 'Event updated: defense', '2025-03-21 09:39:43', 0, 'user', 'update_event', 62),
(70, 1, 'Event updated: friday', '2025-03-21 09:41:55', 0, 'user', 'update_event', 62),
(71, 1, 'Event updated: friday', '2025-03-21 09:51:50', 0, 'user', 'update_event', 62),
(72, 1, 'Event updated: flag ceremony', '2025-03-21 09:54:02', 0, 'user', 'update_event', 62),
(73, 1, 'Event updated: flag ceremony', '2025-03-21 10:13:14', 0, 'user', 'update_event', 62),
(74, 1, 'Event updated: april fools', '2025-03-21 10:14:04', 0, 'user', 'update_event', 62),
(75, 1, 'Event updated: april fools', '2025-03-21 10:14:46', 0, 'user', 'update_event', 62),
(76, 1, 'Event updated: april fools', '2025-03-21 10:15:10', 0, 'user', 'update_event', 62),
(77, 1, 'Event updated: april fools', '2025-03-21 10:15:42', 0, 'user', 'update_event', 62),
(78, 1, 'Event updated: thesis', '2025-03-21 10:19:14', 0, 'user', 'update_event', 62),
(79, 1, 'Event updated: thesis', '2025-03-21 10:25:00', 0, 'user', 'update_event', 62),
(80, NULL, 'New event created. Click for more info: Graduation day', '2025-03-21 10:50:07', 0, 'user', 'new_event', 64),
(81, 1, 'Event updated: Graduation day', '2025-03-21 10:51:59', 0, 'user', 'update_event', 64),
(82, 1, 'Event updated: Graduation day', '2025-03-21 10:53:09', 0, 'user', 'update_event', 64),
(83, 1, 'Event updated: Graduation day', '2025-03-21 11:27:16', 0, 'user', 'update_event', 64),
(84, NULL, 'New event created. Click for more info: Today', '2025-03-21 13:45:34', 0, 'user', 'new_event', 65),
(85, NULL, 'New event created. Click for more info: april fools day', '2025-03-21 13:52:56', 0, 'user', 'new_event', 67),
(86, NULL, 'New event created. Click for more info: presentation', '2025-03-21 14:05:07', 0, 'user', 'new_event', 68),
(87, 1, 'Event updated: presentation', '2025-03-21 14:06:25', 0, 'user', 'update_event', 68),
(88, NULL, 'New event created. Click for more info: THANKS JESS', '2025-03-21 14:14:16', 0, 'user', 'new_event', 69),
(89, NULL, 'New event created. Click for more info: hershey & jansen wedding', '2025-03-21 14:18:23', 0, 'user', 'new_event', 71),
(90, NULL, 'New event created. Click for more info: jess & darrik wedding', '2025-03-21 14:30:38', 0, 'user', 'new_event', 72),
(91, NULL, 'New event created. Click for more info: cess & marvin wedding', '2025-03-21 14:44:32', 0, 'user', 'new_event', 73),
(92, 1, 'Event updated: cess & marvin wedding', '2025-03-21 14:46:49', 0, 'user', 'update_event', 73),
(93, 1, 'Event updated: Graduation day', '2025-03-24 10:11:07', 0, 'user', 'update_event', 64);

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
(3, 53, 8, '2025-03-14 07:32:35'),
(5, 54, 8, '2025-03-14 09:28:22'),
(6, 56, 9, '2025-03-19 05:19:07'),
(7, 58, 9, '2025-03-19 05:50:48');

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
(31, 41, '3', '2', '1,3,5,7'),
(32, 42, '3', '2', '1,5,10'),
(33, 44, '1', '2', '1'),
(35, 49, '1', '2,1', '1,2'),
(42, 73, '1', '2', '10'),
(44, 75, '1', '1', '3,10'),
(45, 76, '1', '1', '10'),
(46, 78, '1', '2', '10'),
(47, 82, '1,3', '1', '10');

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
(51, 56, 'jisas', '2025-03-18 22:08:27', '2025-03-18 22:08:27'),
(52, 57, 'jisas', '2025-03-18 22:17:52', '2025-03-18 22:17:52'),
(53, 58, 'jesus', '2025-03-18 22:50:29', '2025-03-18 22:50:29'),
(54, 59, 'jesus', '2025-03-18 23:14:32', '2025-03-18 23:14:32'),
(55, 60, 'Jisas', '2025-03-19 18:10:12', '2025-03-19 18:10:12'),
(62, 63, 'shinby yao', '2025-03-20 01:08:44', '2025-03-20 01:08:44'),
(63, 63, 'jess constante', '2025-03-20 01:08:44', '2025-03-20 01:08:44'),
(66, 61, 'jisas', '2025-03-20 08:45:22', '2025-03-20 08:45:22'),
(97, 62, 'shinby yao', '2025-03-20 19:25:00', '2025-03-20 19:25:00'),
(98, 62, 'jess constante', '2025-03-20 19:25:00', '2025-03-20 19:25:00'),
(103, 65, 'jess constante', '2025-03-20 22:45:34', '2025-03-20 22:45:34'),
(104, 66, 'jess constante', '2025-03-20 22:51:52', '2025-03-20 22:51:52'),
(105, 67, 'jess constante', '2025-03-20 22:52:56', '2025-03-20 22:52:56'),
(108, 68, 'jess constante', '2025-03-20 23:06:25', '2025-03-20 23:06:25'),
(109, 68, 'shinby yao', '2025-03-20 23:06:25', '2025-03-20 23:06:25'),
(110, 69, 'jess constante', '2025-03-20 23:14:16', '2025-03-20 23:14:16'),
(111, 69, 'shinby yao', '2025-03-20 23:14:16', '2025-03-20 23:14:16'),
(112, 69, 'ella mae', '2025-03-20 23:14:16', '2025-03-20 23:14:16'),
(113, 70, 'jess constante', '2025-03-20 23:17:08', '2025-03-20 23:17:08'),
(114, 71, 'jess constante', '2025-03-20 23:18:23', '2025-03-20 23:18:23'),
(115, 72, 'shinby yao', '2025-03-20 23:30:38', '2025-03-20 23:30:38'),
(116, 72, 'ella mae', '2025-03-20 23:30:38', '2025-03-20 23:30:38'),
(119, 73, 'shinby yao', '2025-03-20 23:46:49', '2025-03-20 23:46:49'),
(120, 73, 'ALEX', '2025-03-20 23:46:49', '2025-03-20 23:46:49'),
(121, 64, 'jess constante', '2025-03-23 19:11:07', '2025-03-23 19:11:07');

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
(9, 'Alessandra', 'Gupong', 'Castillas', '', 'Female', '09123456789', 'alessandra@gmail.com', '$2y$10$ga5QiQNZs8jzA45yH.586eWgCtn01sMsXVXe6Bqrab9Kyql92DjIa', '2025-03-18 00:21:14', NULL),
(10, 'chelsea', 'constante', 'dudas', '', 'Female', '09123456789', 'chelsea@gmail.com', '$2y$10$Vk1UA3flwJvRiv060h8bAeE4RQiHVK3HJ2oyy1Y1r3uw2TzW5wvQq', '2025-03-20 05:11:46', NULL),
(11, 'franchesca', 'constante', 'dudas', '', 'Female', '09123456789', 'franchesca@gmail.com', '$2y$10$2M6gF2qcfW.oMGSzHdlkhudhyVR10eP5j0nokeTIJ08N56mnwDsFm', '2025-03-20 05:13:47', NULL),
(12, 'niki', 'castillas', 'zefanya', '', 'Female', '09123456789', 'niki@gmail.com', '$2y$10$Dbp/D2sbRX95yCNySH9pJubp241nQ68nXNqyWdhS4S5p2DD/8BfHy', '2025-03-20 05:17:42', NULL);

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
(2, 9, 2, 34, 2),
(3, 10, 1, 61, 1),
(4, 11, 1, 61, 1),
(5, 12, 1, 101, 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=544;

--
-- AUTO_INCREMENT for table `eligible_participants`
--
ALTER TABLE `eligible_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `event_days`
--
ALTER TABLE `event_days`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `funding_sources`
--
ALTER TABLE `funding_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `meal_plan`
--
ALTER TABLE `meal_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `registered_users`
--
ALTER TABLE `registered_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `school_level`
--
ALTER TABLE `school_level`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_participants`
--
ALTER TABLE `school_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `speakers`
--
ALTER TABLE `speakers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `specialization`
--
ALTER TABLE `specialization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users_glms`
--
ALTER TABLE `users_glms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_lnd`
--
ALTER TABLE `users_lnd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
