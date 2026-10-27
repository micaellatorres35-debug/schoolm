-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2026 at 04:36 PM
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
-- Database: `sms1`
--

-- --------------------------------------------------------

--
-- Table structure for table `cc_events`
--

CREATE TABLE `cc_events` (
  `event_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_type` enum('Academic','Meeting','Seminar','Institutional Event','Cultural Event','Sports Event','Orientation','Other') NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `target_audience` varchar(100) DEFAULT 'All Students',
  `status` enum('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_events`
--

INSERT INTO `cc_events` (`event_id`, `template_id`, `event_title`, `event_type`, `event_date`, `start_time`, `end_time`, `description`, `location`, `target_audience`, `status`, `created_at`) VALUES
(1, NULL, 'Academic Orientation 2026', 'Orientation', '2026-06-10', '08:00:00', '12:00:00', 'Orientation program for new students covering academic policies, curriculum overview, and student guidelines.', 'University Auditorium', 'First Year Students', 'cancelled', '2026-03-15 00:27:02'),
(2, NULL, 'Foundation Day Celebration 2026', 'Institutional Event', '2026-08-15', '09:00:00', '17:00:00', 'Annual school foundation day celebration with programs, performances, and institutional activities.', 'School Grounds', 'All Students and Staff', 'upcoming', '2026-03-15 00:27:02'),
(3, NULL, 'Annual Sportsfest 2026', 'Sports Event', '2026-09-05', '06:30:00', '18:00:00', 'Inter-department sports competition promoting teamwork, discipline, and school spirit.', 'School Gymnasium and Field', 'All Students', 'completed', '2026-03-15 00:27:02'),
(4, NULL, 'College Stage Play Presentation', 'Cultural Event', '2026-07-20', '13:00:00', '16:00:00', 'Student-led stage play showcasing talents in performing arts and literature.', 'Auditorium', 'All Students', 'ongoing', '2026-03-15 00:27:02'),
(15, NULL, 'CLOUD SWIFY', 'Orientation', '2026-03-20', '08:00:00', '12:00:00', 'ahfvajs', 'gym', 'all students and faculty', 'completed', '2026-03-19 03:15:48'),
(19, NULL, 'research forum', 'Meeting', '2026-07-20', '07:07:00', '17:00:00', 'sdfsdf', 'sdgdsfsdfsxfdx', 'nya', 'completed', '2026-07-20 03:08:59'),
(20, NULL, 'acquiantance party', 'Institutional Event', '2026-07-21', '08:00:00', '17:00:00', 'dfsdfs', 'gymrferfef', 'fsdfs', 'upcoming', '2026-07-20 12:44:02'),
(24, NULL, 'Meeting', 'Academic', '2026-07-29', '12:00:00', '17:00:00', 'sdfsf', 'room', 'sdfsf', 'ongoing', '2026-07-26 14:43:01'),
(27, 4, 'Orientation Program', 'Orientation', '2026-07-27', '12:00:00', '14:00:00', 'Welcome to the orientation program. Attendance is required.', 'Auditorium', 'First Year Students', 'upcoming', '2026-07-27 15:12:59'),
(33, 4, 'Orientation Program', 'Orientation', '2026-07-29', '23:10:00', '23:13:00', 'Welcome to the orientation program. Attendance is required.', 'Auditorium', 'First Year Students', 'upcoming', '2026-07-29 15:06:38'),
(34, 2, 'Sports Event', 'Sports Event', '2026-07-29', '23:31:00', '23:35:00', 'The Sports Event will be conducted for all participants.', 'School Gymnasium', 'Students', 'upcoming', '2026-07-29 15:28:14'),
(35, 4, 'Orientation Program', 'Orientation', '2026-07-30', '07:00:00', '17:00:00', 'Welcome to the orientation program. Attendance is required.', 'Auditorium', 'First Year Students', 'upcoming', '2026-07-29 15:32:24'),
(38, 3, 'Faculty Meeting', 'Meeting', '2026-08-09', '12:10:00', '12:30:00', 'This is to inform all faculty members that a meeting will be conducted regarding important school matters.', 'Conference Room', 'Faculty', 'upcoming', '2026-08-09 04:01:58'),
(39, 1, 'Academic Activity', 'Academic', '2026-08-12', '12:00:00', '13:00:00', 'This activity is part of the academic requirements. Please attend on the scheduled date.', 'TBA', 'Students', 'upcoming', '2026-08-12 03:40:20'),
(40, 3, 'Faculty Meeting', 'Meeting', '2026-08-12', '16:00:00', '17:00:00', 'This is to inform all faculty members that a meeting will be conducted regarding important school matters.', 'Conference Room', 'Faculty', 'upcoming', '2026-08-12 07:04:17'),
(41, NULL, 'si carlo badings', 'Seminar', '2026-08-20', '17:00:00', '18:00:00', 'section', 'sogo', 'mamamo', 'upcoming', '2026-08-20 08:55:16'),
(42, 4, 'Orientation Program', 'Orientation', '2026-08-21', '07:00:00', '10:00:00', 'Welcome to the orientation program. Attendance is required.', 'Auditorium', 'First Year Students', 'upcoming', '2026-08-20 08:55:59'),
(43, 1, 'Academic Activity', 'Academic', '2026-08-20', '21:00:00', '22:00:00', 'This activity is part of the academic requirements. Please attend on the scheduled date.', 'TBA', 'Students', 'upcoming', '2026-08-20 12:11:22'),
(44, 4, 'Orientation Program', 'Orientation', '2026-08-21', '20:00:00', '12:00:00', 'Welcome to the orientation program. Attendance is required.', 'Auditorium', 'First Year Students', 'upcoming', '2026-08-20 12:14:05'),
(45, 2, 'Sports Event', 'Sports Event', '2026-08-25', '12:00:00', '17:00:00', 'The Sports Event will be conducted for all participants.', 'School Gymnasium', 'Students', 'upcoming', '2026-08-20 12:15:22'),
(46, 1, 'Academic Activity', 'Academic', '2026-09-27', '23:00:00', '00:00:00', 'This activity is part of the academic requirements. Please attend on the scheduled date.', 'TBA', 'Students', 'upcoming', '2026-08-27 14:51:50'),
(48, 3, 'Faculty Meeting', 'Meeting', '2026-08-28', '15:10:00', '15:30:00', 'This is to inform all faculty members that a meeting will be conducted regarding important school matters.', 'Conference Room', 'Faculty', 'upcoming', '2026-08-28 06:59:51'),
(49, 4, 'Orientation Program', 'Orientation', '2026-08-29', '07:09:00', '10:00:00', 'Welcome to the orientation program. Attendance is required.', 'Auditorium', 'First Year Students', 'upcoming', '2026-08-28 07:00:33');

-- --------------------------------------------------------

--
-- Table structure for table `cc_event_templates`
--

CREATE TABLE `cc_event_templates` (
  `template_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `event_type` enum('Academic','Meeting','Seminar','Institutional Event','Cultural Event','Sports Event','Orientation','Other') NOT NULL,
  `default_title` varchar(255) NOT NULL,
  `default_description` text DEFAULT NULL,
  `default_location` varchar(255) DEFAULT NULL,
  `default_target_audience` varchar(100) DEFAULT NULL,
  `default_status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `priority` enum('Normal','High','Urgent') DEFAULT 'Normal',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_event_templates`
--

INSERT INTO `cc_event_templates` (`template_id`, `template_name`, `event_type`, `default_title`, `default_description`, `default_location`, `default_target_audience`, `default_status`, `priority`, `created_at`) VALUES
(1, 'Academic Activity', 'Academic', 'Academic Activity', 'This activity is part of the academic requirements. Please attend on the scheduled date.', 'TBA', 'Students', 'upcoming', 'Normal', '2026-07-26 13:22:37'),
(2, 'Sports Event', 'Sports Event', 'Sports Event', 'The Sports Event will be conducted for all participants.', 'School Gymnasium', 'Students', 'upcoming', 'Normal', '2026-07-26 13:22:37'),
(3, 'Faculty Meeting', 'Meeting', 'Faculty Meeting', 'This is to inform all faculty members that a meeting will be conducted regarding important school matters.', 'Conference Room', 'Faculty', 'upcoming', 'High', '2026-07-26 13:22:37'),
(4, 'Orientation', 'Orientation', 'Orientation Program', 'Welcome to the orientation program. Attendance is required.', 'Auditorium', 'First Year Students', 'upcoming', 'Normal', '2026-07-26 13:22:37');

-- --------------------------------------------------------

--
-- Table structure for table `cc_exams`
--

CREATE TABLE `cc_exams` (
  `id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `exam_type` enum('Preliminary','Midterm','Final','Special') NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Draft','Scheduled','Ongoing','Completed','Cancelled') DEFAULT 'Draft',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_exams`
--

INSERT INTO `cc_exams` (`id`, `exam_name`, `exam_type`, `school_year_id`, `semester_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Prelim Examination', 'Preliminary', 31, 13, '2026-08-31', '2026-09-02', 'Scheduled', '2026-08-28 05:19:18', '2026-08-28 05:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `cc_exam_proctor`
--

CREATE TABLE `cc_exam_proctor` (
  `id` int(11) NOT NULL,
  `exam_schedule_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `role` enum('Lead Proctor','Proctor','Reliever') DEFAULT 'Proctor',
  `status` enum('Assigned','Confirmed','Completed','Cancelled') DEFAULT 'Assigned',
  `assigned_at` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_exam_proctor`
--

INSERT INTO `cc_exam_proctor` (`id`, `exam_schedule_id`, `faculty_id`, `role`, `status`, `assigned_at`, `created_at`, `updated_at`) VALUES
(1, 1, 24, 'Proctor', 'Assigned', '2026-09-01 15:35:26', '2026-09-01 07:35:26', '2026-09-01 07:35:26'),
(2, 4, 24, 'Proctor', 'Assigned', '2026-09-01 15:35:26', '2026-09-01 07:35:26', '2026-09-01 07:35:26'),
(3, 5, 21, 'Proctor', 'Assigned', '2026-09-01 15:49:06', '2026-09-01 07:49:06', '2026-09-01 07:49:06'),
(4, 7, 21, 'Proctor', 'Assigned', '2026-09-01 15:49:06', '2026-09-01 07:49:06', '2026-09-01 07:49:06'),
(5, 8, 21, 'Proctor', 'Assigned', '2026-09-01 15:53:15', '2026-09-01 07:53:15', '2026-09-01 07:53:15'),
(6, 10, 21, 'Proctor', 'Assigned', '2026-09-01 15:53:15', '2026-09-01 07:53:15', '2026-09-01 07:53:15'),
(7, 11, 24, 'Proctor', 'Assigned', '2026-09-01 17:21:10', '2026-09-01 09:21:10', '2026-09-01 09:21:10'),
(8, 13, 24, 'Proctor', 'Assigned', '2026-09-01 17:21:10', '2026-09-01 09:21:10', '2026-09-01 09:21:10'),
(9, 14, 17, 'Proctor', 'Assigned', '2026-09-01 17:45:54', '2026-09-01 09:45:54', '2026-09-01 09:45:54'),
(10, 16, 17, 'Proctor', 'Assigned', '2026-09-01 17:45:54', '2026-09-01 09:45:54', '2026-09-01 09:45:54');

-- --------------------------------------------------------

--
-- Table structure for table `cc_exam_schedule`
--

CREATE TABLE `cc_exam_schedule` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `schedule_type` enum('Exam','Break Time') NOT NULL DEFAULT 'Exam',
  `subject_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('Scheduled','Ongoing','Completed','Cancelled') DEFAULT 'Scheduled',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_exam_schedule`
--

INSERT INTO `cc_exam_schedule` (`id`, `exam_id`, `schedule_type`, `subject_id`, `section_id`, `room_id`, `exam_date`, `start_time`, `end_time`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Exam', 17, 1, 1, '2026-08-31', '10:00:00', '11:00:00', 'Scheduled', '2026-08-28 05:21:06', '2026-08-28 05:21:06'),
(3, 1, 'Break Time', NULL, 1, 1, '2026-08-31', '11:00:00', '11:30:00', 'Scheduled', '2026-08-31 06:56:15', '2026-08-31 06:56:15'),
(4, 1, 'Exam', 22, 1, 1, '2026-08-31', '11:30:00', '12:30:00', 'Scheduled', '2026-09-01 07:28:49', '2026-09-01 07:28:49'),
(5, 1, 'Exam', 22, 2, 2, '2026-08-31', '10:00:00', '11:00:00', 'Scheduled', '2026-09-01 07:47:14', '2026-09-01 07:47:14'),
(6, 1, 'Break Time', NULL, 2, 2, '2026-08-31', '11:00:00', '11:30:00', 'Scheduled', '2026-09-01 07:48:05', '2026-09-01 07:48:05'),
(7, 1, 'Exam', 17, 2, 2, '2026-08-31', '11:30:00', '12:30:00', 'Scheduled', '2026-09-01 07:48:43', '2026-09-01 07:48:43'),
(8, 1, 'Exam', 26, 1, 4, '2026-09-01', '10:00:00', '11:00:00', 'Scheduled', '2026-09-01 07:50:58', '2026-09-01 07:50:58'),
(9, 1, 'Break Time', NULL, 1, 4, '2026-09-01', '11:00:00', '11:30:00', 'Scheduled', '2026-09-01 07:51:52', '2026-09-01 07:51:52'),
(10, 1, 'Exam', 13, 1, 4, '2026-09-01', '11:30:00', '12:30:00', 'Scheduled', '2026-09-01 07:52:49', '2026-09-01 07:52:49'),
(11, 1, 'Exam', 13, 2, 5, '2026-09-01', '10:00:00', '11:00:00', 'Scheduled', '2026-09-01 09:17:43', '2026-09-01 09:17:43'),
(12, 1, 'Break Time', NULL, 2, 5, '2026-09-01', '11:00:00', '11:30:00', 'Scheduled', '2026-09-01 09:19:24', '2026-09-01 09:19:24'),
(13, 1, 'Exam', 26, 2, 5, '2026-09-01', '11:30:00', '12:30:00', 'Scheduled', '2026-09-01 09:20:21', '2026-09-01 09:20:21'),
(14, 1, 'Exam', 21, 26, 6, '2026-08-31', '10:30:00', '12:00:00', 'Scheduled', '2026-09-01 09:41:40', '2026-09-01 09:41:40'),
(15, 1, 'Break Time', NULL, 26, 6, '2026-08-31', '12:00:00', '12:30:00', 'Scheduled', '2026-09-01 09:44:21', '2026-09-01 09:44:21'),
(16, 1, 'Exam', 24, 26, 6, '2026-08-31', '12:30:00', '14:00:00', 'Scheduled', '2026-09-01 09:45:31', '2026-09-01 09:45:31');

-- --------------------------------------------------------

--
-- Table structure for table `cc_faculty`
--

CREATE TABLE `cc_faculty` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `faculty_code` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `max_load` int(11) NOT NULL DEFAULT 15,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_faculty`
--

INSERT INTO `cc_faculty` (`id`, `employee_id`, `faculty_code`, `first_name`, `middle_name`, `last_name`, `email`, `department`, `max_load`, `created_at`) VALUES
(10, 9, 'EMP-000009', 'Elena', 'G.', 'Ocampo', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(11, 10, 'EMP-000010', 'Fiona', 'G.', 'Ramos', 'admin@gmail.com', NULL, 15, '2026-08-14 14:03:58'),
(12, 11, 'EMP-000011', 'Aaron', '', 'Mendoza', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(13, 12, 'EMP-000012', 'Caleb', '', 'Santos', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(14, 13, 'EMP-000013', 'David', '', 'Aquino', 'admin@hrsystem.com', NULL, 45, '2026-08-14 14:03:58'),
(15, 14, 'EMP-000014', 'Ethan', '', 'Garcia', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(16, 15, 'EMP-000015', 'Felix', '', 'Del Rosario', 'admin@gmail.com', NULL, 4, '2026-08-14 14:03:58'),
(17, 16, 'EMP-000016', 'Gabriel', '', 'Gonzales', 'admin@gmail.com', NULL, 15, '2026-08-14 14:03:58'),
(18, 17, 'EMP-000017', 'Hugo', '', 'Villanueva', 'admin@gmail.com', NULL, 15, '2026-08-14 14:03:58'),
(19, 18, 'EMP-000018', 'Ian', '', 'Fernandez', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(20, 19, 'EMP-000019', 'Jacob', '', 'Lopez', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(21, 20, 'EMP-000020', 'Ian', '', 'Perez', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(22, 21, 'EMP-000021', 'Gia', '', 'Valdez', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(23, 22, 'EMP-000022', 'Aaron', '', 'Valdez', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(24, 23, 'EMP-000023', 'Aaron', '', 'Pascual', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(25, 24, 'EMP-000024', 'Iris', '', 'Soriano', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(26, 25, 'EMP-000025', 'Zenith', '', 'Tolentino', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(27, 26, 'EMP-000026', 'Lumina', '', 'Tolentino', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(28, 27, 'EMP-000027', 'Vibe', '', 'Mercado', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58'),
(29, 28, 'EMP-000028', 'Diana', '', 'Mercado', 'admin@gmail.com', NULL, 45, '2026-08-14 14:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `cc_faculty_load`
--

CREATE TABLE `cc_faculty_load` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `school_year_id` int(11) DEFAULT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_faculty_load`
--

INSERT INTO `cc_faculty_load` (`id`, `faculty_id`, `section_id`, `subject_id`, `school_year_id`, `semester_id`, `created_at`) VALUES
(76, 14, 10, 82, 31, 13, '2026-09-03 08:46:43'),
(77, 14, 10, 78, 31, 13, '2026-09-03 08:47:02'),
(78, 14, 11, 78, 31, 13, '2026-09-03 08:47:18'),
(79, 17, 10, 84, 31, 13, '2026-09-03 08:47:38'),
(80, 17, 10, 81, 31, 13, '2026-09-03 08:47:55'),
(81, 17, 11, 84, 31, 13, '2026-09-03 08:48:14'),
(82, 17, 11, 81, 31, 13, '2026-09-03 08:48:42'),
(83, 17, 11, 82, 31, 13, '2026-09-03 08:49:24'),
(84, 17, 11, 79, 31, 13, '2026-09-03 08:49:46'),
(85, 19, 10, 83, 31, 13, '2026-09-03 08:59:11'),
(86, 15, 8, 68, 31, 13, '2026-09-03 09:00:39'),
(87, 15, 8, 65, 31, 13, '2026-09-03 09:00:50'),
(88, 15, 8, 67, 31, 13, '2026-09-03 09:00:58'),
(89, 15, 9, 67, 31, 13, '2026-09-03 09:01:09'),
(90, 15, 9, 68, 31, 13, '2026-09-03 09:01:17'),
(91, 11, 1, 33, 31, 13, '2026-09-03 09:02:15'),
(92, 11, 1, 32, 31, 13, '2026-09-03 09:02:23'),
(93, 11, 1, 36, 31, 13, '2026-09-03 09:02:32'),
(94, 11, 2, 36, 31, 13, '2026-09-03 09:02:42'),
(95, 11, 2, 34, 31, 13, '2026-09-03 09:02:50');

-- --------------------------------------------------------

--
-- Table structure for table `cc_faculty_load_summary`
--

CREATE TABLE `cc_faculty_load_summary` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `total_units` int(11) NOT NULL DEFAULT 0,
  `max_load` int(11) NOT NULL,
  `load_status` enum('Underloaded','Normal Load','Overloaded') NOT NULL,
  `computed_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_faculty_load_summary`
--

INSERT INTO `cc_faculty_load_summary` (`id`, `faculty_id`, `school_year_id`, `semester_id`, `total_units`, `max_load`, `load_status`, `computed_at`) VALUES
(1, 14, 31, 13, 9, 45, 'Underloaded', '2026-09-03 09:02:42'),
(4, 17, 31, 13, 18, 15, 'Overloaded', '2026-09-03 09:02:42'),
(10, 19, 31, 13, 3, 45, 'Underloaded', '2026-09-03 09:02:42'),
(11, 15, 31, 13, 15, 45, 'Underloaded', '2026-09-03 09:02:42'),
(16, 11, 31, 13, 12, 15, 'Underloaded', '2026-09-03 09:02:42');

-- --------------------------------------------------------

--
-- Table structure for table `cc_room`
--

CREATE TABLE `cc_room` (
  `id` int(11) NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `building` varchar(100) DEFAULT 'Main Building',
  `floor` enum('1st Floor','2nd Floor','3rd Floor','4th Floor') NOT NULL,
  `room_type` enum('Lecture Room','Computer Laboratory','Science Laboratory','Library','Office','AVR','Court Room','Other') NOT NULL,
  `capacity` int(11) DEFAULT 40,
  `status` enum('Available','Maintenance','Unavailable') DEFAULT 'Available',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_room`
--

INSERT INTO `cc_room` (`id`, `room_code`, `room_name`, `building`, `floor`, `room_type`, `capacity`, `status`, `created_at`) VALUES
(1, '101', 'Room 101', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(2, '102', 'Room 102', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(3, '103', 'Room 103', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(4, '104', 'Room 104', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(5, '105', 'Room 105', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(6, '106', 'Room 106', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(7, '107', 'Room 107', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(8, '108', 'Room 108', 'Main Building', '1st Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:03:33'),
(9, 'COMLAB1', 'Computer Laboratory 1', 'Main Building', '1st Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:03:33'),
(10, '202', 'Room 202', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(11, '203', 'Room 203', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(12, '204', 'Room 204', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(13, '205', 'Room 205', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(14, '206', 'Room 206', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(15, '207', 'Room 207', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(16, '208', 'Room 208', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(17, '210', 'Room 210', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(18, '211', 'Room 211', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(19, '212', 'Room 212', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(20, '213', 'Room 213', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(21, '214', 'Room 214', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(22, '215', 'Room 215', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(23, '216', 'Room 216', 'Main Building', '2nd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:05:53'),
(24, 'PSYCHLAB', 'Psychology Laboratory', 'Main Building', '2nd Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:05:53'),
(25, 'ITLAB', 'IT Laboratory', 'Main Building', '2nd Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:05:53'),
(26, '302', 'Room 302', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(27, '303', 'Room 303', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(28, '304', 'Room 304', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(29, '305', 'Room 305', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(30, '306', 'Room 306', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(31, '307', 'Room 307', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(32, '308', 'Room 308', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(33, '309', 'Room 309', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(34, '310', 'Room 310', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(35, '311', 'Room 311', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(36, '312', 'Room 312', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(37, '313', 'Room 313', 'Main Building', '3rd Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:09:36'),
(38, 'ETLAB1', 'Educational Technology Room 1', 'Main Building', '3rd Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:09:36'),
(39, 'ETLAB2', 'Educational Technology Room 2', 'Main Building', '3rd Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:09:36'),
(40, 'CHEMLAB', 'Chemistry Laboratory', 'Main Building', '3rd Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:09:36'),
(41, '403', 'Room 403', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(42, '404', 'Room 404', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(43, '405', 'Room 405', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(44, '406', 'Room 406', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(45, '407', 'Room 407', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(46, '408', 'Room 408', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(47, '409', 'Room 409', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(48, '410', 'Room 410', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(49, '411', 'Room 411', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(50, '412', 'Room 412', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(51, '413', 'Room 413', 'Main Building', '4th Floor', 'Lecture Room', 50, 'Available', '2026-08-07 16:11:15'),
(52, 'PHYSICSLAB', 'Physics Laboratory', 'Main Building', '4th Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:11:15'),
(53, 'FORENSICLAB', 'Forensics Laboratory', 'Main Building', '4th Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:11:15'),
(54, 'QDLAB', 'Questioned Documents Laboratory', 'Main Building', '4th Floor', 'Science Laboratory', 50, 'Available', '2026-08-07 16:11:15'),
(55, 'COMLAB4', 'Computer Laboratory 4', 'Main Building', '4th Floor', 'Computer Laboratory', 50, 'Available', '2026-08-07 16:11:15');

-- --------------------------------------------------------

--
-- Table structure for table `cc_schedule`
--

CREATE TABLE `cc_schedule` (
  `id` int(11) NOT NULL,
  `faculty_load_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `schedule_type` enum('Class','Break Time') DEFAULT 'Class',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `school_year_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_schedule`
--

INSERT INTO `cc_schedule` (`id`, `faculty_load_id`, `room_id`, `schedule_type`, `start_time`, `end_time`, `status`, `day_of_week`, `section_id`, `faculty_id`, `subject_id`, `school_year_id`, `semester_id`, `created_at`, `updated_at`) VALUES
(1, 76, 1, 'Class', '07:00:00', '09:00:00', 'Scheduled', 'Monday', 10, 14, 82, 31, 13, '2026-09-04 05:53:22', '2026-09-04 05:53:22'),
(3, 77, 2, 'Class', '09:30:00', '11:30:00', 'Scheduled', 'Monday', 10, 14, 78, 31, 13, '2026-09-04 05:54:51', '2026-09-04 05:54:51'),
(4, 78, 9, 'Class', '10:00:00', '12:00:00', 'Scheduled', 'Wednesday', 11, 14, 78, 31, 13, '2026-09-04 06:07:55', '2026-09-04 06:07:55'),
(5, 85, 52, 'Class', '09:00:01', '10:00:01', 'Scheduled', 'Monday', 27, 20, 75, 31, 13, '2026-09-18 08:34:08', '2026-09-18 08:35:21');

-- --------------------------------------------------------

--
-- Table structure for table `cc_sections`
--

CREATE TABLE `cc_sections` (
  `id` int(11) NOT NULL,
  `section_code` varchar(50) DEFAULT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `school_year_id` int(11) DEFAULT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_sections`
--

INSERT INTO `cc_sections` (`id`, `section_code`, `grade_level`, `program_id`, `school_year_id`, `semester_id`, `adviser_id`, `created_at`) VALUES
(1, 'BSIS-1001', '1st Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(2, 'BSIS-1002', '1st Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(3, 'BSIS-1003', '1st Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(4, 'BSIS-1004', '1st Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(5, 'BSIS-2001', '2nd Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(6, 'BSIS-2002', '2nd Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(7, 'BSIS-2003', '2nd Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(8, 'BSIS-3001', '3rd Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(9, 'BSIS-3002', '3rd Year', 26, 31, 13, NULL, '2026-07-29 18:51:28'),
(10, 'BSIS-4001', '4th Year', 26, 31, 13, 14, '2026-07-29 18:51:28'),
(11, 'BSIS-4002', '4th Year', 26, 31, 13, 17, '2026-07-29 18:51:28'),
(12, 'BSIS-1001', '1st Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(13, 'BSIS-1002', '1st Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(14, 'BSIS-1003', '1st Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(15, 'BSIS-1004', '1st Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(16, 'BSIS-2001', '2nd Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(17, 'BSIS-2002', '2nd Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(18, 'BSIS-2003', '2nd Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(19, 'BSIS-3001', '3rd Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(20, 'BSIS-3002', '3rd Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(21, 'BSIS-4001', '4th Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(22, 'BSIS-4002', '4th Year', 26, 31, 14, NULL, '2026-07-29 18:51:28'),
(23, 'BSIT-1001', '1st Year', 38, 31, 13, NULL, '2026-08-09 03:35:10'),
(24, 'BSIT-1002', '1st Year', 38, 31, 13, NULL, '2026-08-09 03:35:10'),
(25, 'BSIT-1003', '1st Year', 38, 31, 13, NULL, '2026-08-09 03:35:10'),
(26, 'BSPsych-1001', '1st Year', 41, 31, 13, NULL, '2026-08-15 10:43:05'),
(27, 'BSPsych-1002', '1st Year', 41, 31, 13, NULL, '2026-08-15 10:43:05');

-- --------------------------------------------------------

--
-- Table structure for table `cc_section_faculty`
--

CREATE TABLE `cc_section_faculty` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `school_year_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `status` enum('Active','Completed','Reassigned','Cancelled') NOT NULL DEFAULT 'Active',
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cc_section_faculty`
--

INSERT INTO `cc_section_faculty` (`id`, `section_id`, `faculty_id`, `role`, `school_year_id`, `semester_id`, `status`, `assigned_at`, `ended_at`, `created_at`, `updated_at`) VALUES
(1, 10, 14, 'Adviser', 31, 13, 'Active', '2026-09-03 16:44:57', NULL, '2026-09-03 16:44:57', '2026-09-03 16:44:57'),
(2, 11, 17, 'Adviser', 31, 13, 'Active', '2026-09-03 16:45:09', NULL, '2026-09-03 16:45:09', '2026-09-03 16:45:09'),
(3, 10, 14, 'Instructor', 31, 13, 'Active', '2026-09-03 16:45:33', NULL, '2026-09-03 16:45:33', '2026-09-03 16:45:33'),
(4, 11, 14, 'Instructor', 31, 13, 'Active', '2026-09-03 16:45:39', NULL, '2026-09-03 16:45:39', '2026-09-03 16:45:39'),
(5, 10, 17, 'Instructor', 31, 13, 'Active', '2026-09-03 16:45:55', NULL, '2026-09-03 16:45:55', '2026-09-03 16:45:55'),
(6, 11, 17, 'Instructor', 31, 13, 'Active', '2026-09-03 16:46:06', NULL, '2026-09-03 16:46:06', '2026-09-03 16:46:06'),
(7, 10, 19, 'Instructor', 31, 13, 'Active', '2026-09-03 16:58:41', NULL, '2026-09-03 16:58:41', '2026-09-03 16:58:41'),
(8, 11, 19, 'Instructor', 31, 13, 'Active', '2026-09-03 16:59:00', NULL, '2026-09-03 16:59:00', '2026-09-03 16:59:00'),
(9, 8, 15, 'Instructor', 31, 13, 'Active', '2026-09-03 17:00:19', NULL, '2026-09-03 17:00:19', '2026-09-03 17:00:19'),
(10, 9, 15, 'Instructor', 31, 13, 'Active', '2026-09-03 17:00:27', NULL, '2026-09-03 17:00:27', '2026-09-03 17:00:27'),
(11, 1, 11, 'Instructor', 31, 13, 'Active', '2026-09-03 17:01:54', NULL, '2026-09-03 17:01:54', '2026-09-03 17:01:54'),
(12, 2, 11, 'Instructor', 31, 13, 'Active', '2026-09-03 17:02:04', NULL, '2026-09-03 17:02:04', '2026-09-03 17:02:04');

-- --------------------------------------------------------

--
-- Table structure for table `cln_clinic_visits`
--

CREATE TABLE `cln_clinic_visits` (
  `visit_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `visit_date` datetime NOT NULL,
  `reason` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cln_departments`
--

CREATE TABLE `cln_departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cln_doctors`
--

CREATE TABLE `cln_doctors` (
  `doctor_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cln_incidents`
--

CREATE TABLE `cln_incidents` (
  `incident_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `incident_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reported_by` varchar(50) DEFAULT NULL,
  `date_reported` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Open',
  `resolved_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cln_inventory_log`
--

CREATE TABLE `cln_inventory_log` (
  `inventory_id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `action` enum('ADD','REMOVE') NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cln_medications`
--

CREATE TABLE `cln_medications` (
  `medication_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cln_patients`
--

CREATE TABLE `cln_patients` (
  `patient_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cln_roles`
--

CREATE TABLE `cln_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cln_staff`
--

CREATE TABLE `cln_staff` (
  `staff_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enr_applicants`
--

CREATE TABLE `enr_applicants` (
  `applicant_id` int(11) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `admission_type` enum('freshmen','transferee','returnee','senior_high') DEFAULT NULL,
  `working_student` enum('Yes','No') DEFAULT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `address_barangay` varchar(100) NOT NULL,
  `address_city` varchar(100) NOT NULL,
  `address_province` varchar(100) NOT NULL,
  `address_complete` text DEFAULT NULL,
  `school_last_attended` varchar(150) NOT NULL,
  `year_graduated` year(4) NOT NULL,
  `how_hear` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `place_of_birth` varchar(150) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `civil_status` enum('Single','Married','Divorced','Widowed') NOT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `contact_number` varchar(20) NOT NULL,
  `facebook` varchar(100) DEFAULT NULL,
  `messenger` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `parent_full_name` varchar(150) NOT NULL,
  `parent_contact` varchar(20) DEFAULT NULL,
  `parent_address` text DEFAULT NULL,
  `course_id` int(10) DEFAULT NULL,
  `preferred_section_id` int(11) DEFAULT NULL,
  `status` enum('pending','converted','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enr_applicants`
--

INSERT INTO `enr_applicants` (`applicant_id`, `surname`, `first_name`, `middle_name`, `suffix`, `admission_type`, `working_student`, `sex`, `address_barangay`, `address_city`, `address_province`, `address_complete`, `school_last_attended`, `year_graduated`, `how_hear`, `email`, `date_of_birth`, `place_of_birth`, `age`, `civil_status`, `religion`, `contact_number`, `facebook`, `messenger`, `address`, `parent_full_name`, `parent_contact`, `parent_address`, `course_id`, `preferred_section_id`, `status`, `notes`, `submitted_at`, `updated_at`) VALUES
(1, 'Villanueva', 'Bea', 'F.', '', 'freshmen', 'No', 'Female', 'San roque', 'Csjdm', 'Bulacan', 'San roque, Csjdm, Bulacan', 'Kakawates', '2025', '', 'villanueva@gmail.com', '2004-08-14', 'Santa Maria', 21, 'Single', 'IRM', '09810203900', 'N/A', 'N/A', NULL, 'mamako', '09123456789', '', 26, NULL, 'converted', NULL, '2026-08-04 10:39:05', '2026-08-10 16:44:22'),
(2, 'marga', 'kim', 'G.', '', 'freshmen', 'No', 'Female', 'San Roque', 'CSJDM', 'Bulacan', '', 'kakawate', '2025', '', 'kim@gmail.com', '2004-12-15', 'Bulacan', 22, 'Single', '', '09123456789', '', '', NULL, 'mamamo', '09123456789', '', 26, NULL, 'converted', NULL, '2026-08-04 11:11:32', '2026-08-04 11:12:13'),
(3, 'elyasdjhasjd', 'asdjfkjkdsaf', 'adsjkfjadshfj', '', 'freshmen', 'Yes', 'Male', 'adsfadsf', 'adsfadsf', 'adsfadsfadsf', 'adlsjkfjds', 'asjdfhilsgdhfa', '2025', 'social_media', 'sdghafhdgshaf@gmail.com', '2004-05-15', 'adsjfjadshfj', 22, 'Single', 'hisad', '09262063178', '', '', NULL, 'adsfadsfa', '09262063178', 'dsafsdfsd', 26, NULL, 'pending', NULL, '2026-08-08 04:08:21', '2026-08-08 04:08:21');

-- --------------------------------------------------------

--
-- Table structure for table `enr_enrollments`
--

CREATE TABLE `enr_enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `enrollment_date` date NOT NULL,
  `enrollment_status` enum('enrolled','dropped','completed') DEFAULT 'enrolled',
  `academic_standing` varchar(50) DEFAULT 'Good Standing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `schedule_id` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enr_enrollments`
--

INSERT INTO `enr_enrollments` (`enrollment_id`, `student_id`, `section_id`, `school_year`, `enrollment_date`, `enrollment_status`, `academic_standing`, `created_at`, `schedule_id`) VALUES
(1, 3, 1, '2026-2027', '2026-08-04', 'enrolled', 'Good Standing', '2026-08-04 11:04:15', 3),
(2, 4, 1, '2026-2027', '2026-08-04', 'enrolled', 'Good Standing', '2026-08-04 11:12:13', 1),
(17, 3, 12, '2026-2027', '2026-08-20', 'enrolled', 'Good Standing', '2026-08-20 01:21:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `enr_requirements`
--

CREATE TABLE `enr_requirements` (
  `requirement_id` int(11) NOT NULL,
  `requirement_name` varchar(100) NOT NULL,
  `requirement_category` enum('freshmen','transferee','continuing') NOT NULL,
  `is_mandatory` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enr_requirements`
--

INSERT INTO `enr_requirements` (`requirement_id`, `requirement_name`, `requirement_category`, `is_mandatory`, `created_at`) VALUES
(1, 'Form 138 (Report Card)', 'freshmen', 1, '2026-07-23 00:29:27'),
(2, 'Form 137', 'freshmen', 1, '2026-07-23 00:29:27'),
(3, 'Certificate of Good Moral', 'transferee', 1, '2026-07-23 00:29:27'),
(4, 'PSA Authenticated Birth Certificate', 'transferee', 1, '2026-07-23 00:29:27'),
(5, 'Passport Size ID Picture - 2pcs', 'transferee', 1, '2026-07-23 00:29:27'),
(6, 'Barangay Clearance', 'transferee', 1, '2026-07-23 00:29:27'),
(7, 'Transcript of Records from Previous School', 'transferee', 1, '2026-07-23 00:29:27'),
(8, 'Honorable Dismissal', 'transferee', 1, '2026-07-23 00:29:27'),
(9, 'Payment Records', 'continuing', 1, '2026-07-23 00:29:27'),
(10, 'Payment', 'freshmen', 1, '2026-08-04 11:14:41');

-- --------------------------------------------------------

--
-- Table structure for table `enr_students`
--

CREATE TABLE `enr_students` (
  `student_id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(10) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `year_level` int(11) DEFAULT 1,
  `enrollment_status` enum('enrolled','on_leave','graduated','dropped') DEFAULT 'enrolled',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `followup_date` date DEFAULT NULL,
  `followup_notes` text DEFAULT NULL,
  `followup_status` enum('pending','done') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enr_students`
--

INSERT INTO `enr_students` (`student_id`, `applicant_id`, `student_number`, `user_id`, `course_id`, `section_id`, `year_level`, `enrollment_status`, `enrolled_at`, `followup_date`, `followup_notes`, `followup_status`) VALUES
(3, 1, '260804001', 1, 26, 10, 1, 'enrolled', '2026-08-04 05:04:15', NULL, NULL, 'pending'),
(4, 2, '260804002', 1, 26, 10, 1, 'enrolled', '2026-08-04 05:12:13', NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `enr_student_requirements`
--

CREATE TABLE `enr_student_requirements` (
  `student_requirement_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `requirement_id` int(11) NOT NULL,
  `is_submitted` tinyint(1) DEFAULT 0,
  `submitted_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enr_student_requirements`
--

INSERT INTO `enr_student_requirements` (`student_requirement_id`, `student_id`, `requirement_id`, `is_submitted`, `submitted_date`, `notes`, `created_at`, `updated_at`) VALUES
(5, 3, 1, 1, '2026-08-04', '', '2026-08-04 11:04:15', '2026-08-04 11:15:25'),
(6, 3, 2, 1, '2026-08-04', '', '2026-08-04 11:04:15', '2026-08-04 11:15:25'),
(7, 4, 1, 1, '2026-08-04', '', '2026-08-04 11:12:13', '2026-08-04 11:12:13'),
(8, 4, 2, 1, '2026-08-04', '', '2026-08-04 11:12:13', '2026-08-04 11:12:13');

-- --------------------------------------------------------

--
-- Table structure for table `enr_users`
--

CREATE TABLE `enr_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `student_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enr_users`
--

INSERT INTO `enr_users` (`user_id`, `username`, `password`, `email`, `full_name`, `student_id`, `is_active`, `created_at`, `last_login`, `updated_at`) VALUES
(1, '260804001', '$2a$12$aExjOjkhkCPaMlKpUzo63OfsWLRTdUzyvi26Enrl9pDSS.prfwR4e', 'bea.villanueva@email.com', 'Bea F. Villanueva', 3, 1, '2026-08-03 21:04:15', NULL, '2026-08-25 16:27:46'),
(2, '260811002', '$2a$12$ZGYeII8V92Yz3qQvrMw2FOAb68YEzoYnaVjTRtlAg/MbYQyevJ6aK', 'juan.delacruz@email.com', 'Juan Santos Dela Cruz Jr.', 4, 1, '2026-08-11 06:03:08', NULL, '2026-08-29 01:35:55');

-- --------------------------------------------------------

--
-- Table structure for table `gd_counselors`
--

CREATE TABLE `gd_counselors` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gd_counselors`
--

INSERT INTO `gd_counselors` (`id`, `first_name`, `last_name`, `email`, `contact_number`, `specialization`, `created_at`, `updated_at`) VALUES
(1, 'johary', 'Dimatingkal', '', NULL, NULL, '2026-03-17 07:05:51', '2026-03-17 07:05:51');

-- --------------------------------------------------------

--
-- Table structure for table `gd_events`
--

CREATE TABLE `gd_events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `organizer` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gd_incident_attachments`
--

CREATE TABLE `gd_incident_attachments` (
  `attachment_id` int(11) NOT NULL,
  `incident_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gd_incident_reports`
--

CREATE TABLE `gd_incident_reports` (
  `incident_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_name` varchar(150) NOT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time DEFAULT NULL,
  `incident_location` varchar(150) DEFAULT NULL,
  `incident_type` varchar(100) DEFAULT NULL,
  `incident_description` text NOT NULL,
  `reported_by` varchar(150) NOT NULL,
  `reporter_role` varchar(100) DEFAULT NULL,
  `report_date` datetime DEFAULT current_timestamp(),
  `witnesses` text DEFAULT NULL,
  `involved_students` text DEFAULT NULL,
  `immediate_action` text DEFAULT NULL,
  `guidance_action` text DEFAULT NULL,
  `disciplinary_action` text DEFAULT NULL,
  `follow_up_required` tinyint(1) DEFAULT 0,
  `follow_up_date` date DEFAULT NULL,
  `follow_up_notes` text DEFAULT NULL,
  `status` enum('Pending','Under Review','Resolved','Closed') DEFAULT 'Pending',
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gd_incident_types`
--

CREATE TABLE `gd_incident_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gd_logs`
--

CREATE TABLE `gd_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `performed_by` varchar(50) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gd_sessions`
--

CREATE TABLE `gd_sessions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `session_date` datetime NOT NULL,
  `session_type` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gd_students_profile`
--

CREATE TABLE `gd_students_profile` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `course` varchar(50) NOT NULL,
  `year_level` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gd_student_concerns`
--

CREATE TABLE `gd_student_concerns` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `concern_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reported_date` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Open',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_ballistic_damage`
--

CREATE TABLE `lab_ballistic_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_ballistic_damage`
--

INSERT INTO `lab_ballistic_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(5, 'sdfgsdfgs', 'Ballistic Laboratory', 'sdfgsfd', 'sdfgdsf', '2026-09-19', 'Damaged', 1),
(6, 'sfaadsfad', 'Ballistic Laboratory', 'Not Working', 'Juan Doe', '2026-09-19', 'Damaged', 0),
(7, 'dreeqwrfq', 'Ballistic Laboratory', 'ewrtwerfgwre', 'refgerfvsd', '2026-09-19', 'Damaged', 1),
(8, 'ergferg', 'Ballistic Laboratory', 'sdfgsdf', 'sdfgsdfg', '2026-09-19', 'Unavailable', 0),
(9, 'adswfasdf', 'Ballistic Laboratory', 'asdfasdf', 'asdfasd', '2026-09-19', 'Damaged', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_ballistic_inventory`
--

CREATE TABLE `lab_ballistic_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_ballistic_inventory`
--

INSERT INTO `lab_ballistic_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'ghsdfg', 'sdfgsdf', 'Ballistic Laboratory', 4, 4, 'Working', 1),
(2, 'asdfgadewsf', 'sdfads', 'Ballistic Laboratory', 34, 34, 'Under Maintenance', 1),
(3, 'adsfasd', 'asdfasd', 'Ballistic Laboratory', 3, 3, 'Working', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_ballistic_monitoring`
--

CREATE TABLE `lab_ballistic_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_ballistic_monitoring`
--

INSERT INTO `lab_ballistic_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'asdfadf', 'Ballistic Laboratory', 'Working', '2026-09-21', 'adsfad', 'adsfadsf', 1),
(2, 'adfasd', 'Ballistic Laboratory', 'Damaged', '2026-09-21', 'asdfad', 'asdfadsf', 0),
(3, 'saedfasdfasdf', 'Ballistic Laboratory', 'Working', '2026-09-21', 'sdfadsfa', 'asdfads', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_chemistry_damage`
--

CREATE TABLE `lab_chemistry_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_chemistry_damage`
--

INSERT INTO `lab_chemistry_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'sdfadf', 'Crime Scene Laboratory', 'sdgsdfg', 'fsgfdgs', '2026-08-24', 'Fixed', 1),
(2, 'dfaa', 'Chemistry Laboratory', 'dfsdf', 'sdfsdf', '2026-09-10', 'Damaged', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_chemistry_inventory`
--

CREATE TABLE `lab_chemistry_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_chemistry_inventory`
--

INSERT INTO `lab_chemistry_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'fgasd', 'asdfasd', 'Chemistry Laboratory', 32, 23, 'Working', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_chemistry_monitoring`
--

CREATE TABLE `lab_chemistry_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_chemistry_monitoring`
--

INSERT INTO `lab_chemistry_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'sdfasd', 'Chemistry Laboratory', 'Working', '2026-09-21', 'dfaadswfa', 'asdfasdfa', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_crime_damage`
--

CREATE TABLE `lab_crime_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_crime_damage`
--

INSERT INTO `lab_crime_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'sadfgd', 'Crime Scene Laboratory', 'sdfgsf', 'edfdsafdw', '2026-09-20', 'Damaged', 0),
(2, 'esgfsdfg', 'Crime Scene Laboratory', 'sdfgsdfg', 'asdfgadsf', '2026-09-20', 'Damaged', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_crime_inventory`
--

CREATE TABLE `lab_crime_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_crime_inventory`
--

INSERT INTO `lab_crime_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'edgdfsag', 'sdfgsdfg', 'Crime Scene Laboratory', 34, 34, 'Under Maintenance', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_crime_monitoring`
--

CREATE TABLE `lab_crime_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_crime_monitoring`
--

INSERT INTO `lab_crime_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'fradfa', 'Crime Scene Laboratory', 'Damaged', '2026-09-21', 'asdfadsf', 'adsfasdfadsf', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_defense_damage`
--

CREATE TABLE `lab_defense_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_defense_damage`
--

INSERT INTO `lab_defense_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'afdsfa', 'Defense Tactics Laboratory', 'asdfadsf', 'asdfasdf', '2026-09-20', 'Damaged', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_defense_inventory`
--

CREATE TABLE `lab_defense_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_defense_inventory`
--

INSERT INTO `lab_defense_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'sdfgsdf', 'asdfads', 'Defense and Tactics Laboratory', 3, 3, 'Damaged', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_defense_monitoring`
--

CREATE TABLE `lab_defense_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_defense_monitoring`
--

INSERT INTO `lab_defense_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'fgasdf', 'Defense Tactics Laboratory', 'Under Maintenance', '2026-09-21', 'adfads', 'asdfadsf', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_fingerprint_damage`
--

CREATE TABLE `lab_fingerprint_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_fingerprint_damage`
--

INSERT INTO `lab_fingerprint_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'dgsdfg', 'Fingerprint Laboratory', 'sdfgsdfgs', 'dfgsdfgsdf', '2026-09-20', 'Damaged', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_fingerprint_inventory`
--

CREATE TABLE `lab_fingerprint_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(250) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_fingerprint_inventory`
--

INSERT INTO `lab_fingerprint_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'swdfasd', 'asdfad', 'Fingerprint Laboratory', 3, 3, 'Under Maintenance', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_fingerprint_monitoring`
--

CREATE TABLE `lab_fingerprint_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_fingerprint_monitoring`
--

INSERT INTO `lab_fingerprint_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'efgadsfg', 'Fingerprint Laboratory', 'Working', '2026-09-21', 'adsfasdfasdf', 'adfasdfa', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_he_borrow`
--

CREATE TABLE `lab_he_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_he_borrow`
--

INSERT INTO `lab_he_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`, `is_active`) VALUES
(1, 'HE Lab', 'sdfgsdf', 'sdfgsdfg', 'sdfgsdfg', 'sdfgsfdg', 4, '2026-09-21', '2026-09-24', '0000-00-00', 'Borrowed', 1),
(2, 'HE Lab', 'asdfasdfasd', 'adfasdf', 'asdfasdf', 'sadfasd', 2, '2026-09-21', '2026-09-24', '0000-00-00', 'Overdue', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_he_damage`
--

CREATE TABLE `lab_he_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_he_damage`
--

INSERT INTO `lab_he_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'sdfgsdfsdfg', 'Home Economics Laboratory', 'sdfgsdfg', 'sdfgsdf', '2026-09-20', 'Fixed', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_he_inventory`
--

CREATE TABLE `lab_he_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_he_inventory`
--

INSERT INTO `lab_he_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'adsfad', 'asdfa', 'Home Economics Laboratory', 223, 23, 'Damaged', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_he_monitoring`
--

CREATE TABLE `lab_he_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_he_monitoring`
--

INSERT INTO `lab_he_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'adfdsf', 'HE Lab', 'Working', '2026-09-21', 'adsfadsf', 'asdfasdf', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it1_borrow`
--

CREATE TABLE `lab_it1_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it1_borrow`
--

INSERT INTO `lab_it1_borrow` (`id`, `laboratory`, `borrower_name`, `student_id`, `section`, `item_name`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`, `is_active`) VALUES
(1, 'IT Lab 1', 'adfads', 'adsfasd', 'asdfasd', 'asdfasdf', 342, '2026-09-21', '2026-09-24', '2026-09-21', 'Returned', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it1_damage`
--

CREATE TABLE `lab_it1_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it1_damage`
--

INSERT INTO `lab_it1_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'dfghfes', 'IT Laboratory 1', 'dfhgsfd', 'sdfgsfdg', '2026-09-20', 'Damaged', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it1_inventory`
--

CREATE TABLE `lab_it1_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it1_inventory`
--

INSERT INTO `lab_it1_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(3, 'asdfa', 'asdfa', 'IT Lab 1', 3, 3, 'Working', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it1_monitoring`
--

CREATE TABLE `lab_it1_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it1_monitoring`
--

INSERT INTO `lab_it1_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'fgawe', 'IT Lab 1', 'Under Maintenance', '2026-09-21', 'asdfasdf', 'asdfadsfgsdaf', 0),
(2, 'fdgsdfgsdfdsfgsdaf', 'IT Lab 1', 'Under Maintenance', '2026-09-21', 'adsfasdf', 'asdfasdf', 0),
(3, 'asdfads', 'IT Lab 1', 'Working', '2026-09-21', 'asdfasdf', 'asdfasd', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it2_borrow`
--

CREATE TABLE `lab_it2_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it2_borrow`
--

INSERT INTO `lab_it2_borrow` (`id`, `laboratory`, `borrower_name`, `item_name`, `student_id`, `section`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`, `is_active`) VALUES
(1, 'IT Lab 2', 'sfgsfdg', 'sdfgsdf', 'sdfgsdf', 'sdfgsdf', 34, '2026-09-21', '2026-09-24', '2026-09-21', 'Returned', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it2_damage`
--

CREATE TABLE `lab_it2_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it2_damage`
--

INSERT INTO `lab_it2_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'asdfads', 'IT Laboratory 2', 'asdfadsf', 'adsfads', '2026-09-20', 'Fixed', 1),
(2, 'asdfads', 'IT Laboratory 2', 'asdfad', 'asdfasdf', '2026-09-20', 'Damaged', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it2_inventory`
--

CREATE TABLE `lab_it2_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it2_inventory`
--

INSERT INTO `lab_it2_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'dsefgsdefg', 'sdfg', 'IT Lab 2', 343, 435, 'Working', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it2_monitoring`
--

CREATE TABLE `lab_it2_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it2_monitoring`
--

INSERT INTO `lab_it2_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(0, 'sdfgadswf', 'IT Lab 2', 'Unavailable', '2026-09-21', 'asdfasdf', 'asdfasdf', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it3_borrow`
--

CREATE TABLE `lab_it3_borrow` (
  `id` int(11) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `borrower_name` varchar(100) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_date` date NOT NULL DEFAULT current_timestamp(),
  `expected_return` date DEFAULT current_timestamp(),
  `returned_date` date DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it3_borrow`
--

INSERT INTO `lab_it3_borrow` (`id`, `laboratory`, `borrower_name`, `item_name`, `student_id`, `section`, `quantity`, `borrowed_date`, `expected_return`, `returned_date`, `status`, `is_active`) VALUES
(1, 'IT Lab 3', 'asdfasd', 'adsfasd', 'asdfasd', 'adsfads', 23, '2026-09-21', '2026-09-24', '2026-09-21', 'Returned', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it3_damage`
--

CREATE TABLE `lab_it3_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it3_damage`
--

INSERT INTO `lab_it3_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'fgasdf', 'IT Laboratory 3', 'asdfasdf', 'asdfasdf', '2026-09-20', 'Damaged', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it3_inventory`
--

CREATE TABLE `lab_it3_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it3_inventory`
--

INSERT INTO `lab_it3_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'sdfgsd', 'sdfgs', 'IT Lab 3', 34, 34, 'Working', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_it3_monitoring`
--

CREATE TABLE `lab_it3_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `laboratory` varchar(255) NOT NULL,
  `equipment_condition` varchar(255) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(255) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_it3_monitoring`
--

INSERT INTO `lab_it3_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(0, 'fdgsdfg', 'IT Lab 3', 'Under Maintenance', '2026-09-21', 'sdfgsdfg', 'sdfgsdfg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_phys_damage`
--

CREATE TABLE `lab_phys_damage` (
  `id` int(100) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_phys_damage`
--

INSERT INTO `lab_phys_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'rfgadsfadws', 'Physics Laboratory', 'asdfasd', 'asdfads', '2026-09-20', 'Damaged', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_phys_inventory`
--

CREATE TABLE `lab_phys_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_phys_inventory`
--

INSERT INTO `lab_phys_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'sdfa', 'asdf', 'Physics Laboratory', 23, 23, 'Under Maintenance', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_phys_monitoring`
--

CREATE TABLE `lab_phys_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_phys_monitoring`
--

INSERT INTO `lab_phys_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'sdafsadf', 'Physics Lab', 'Under Maintenance', '2026-09-21', 'asdfasdf', 'asdfsadfa', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_psy_damage`
--

CREATE TABLE `lab_psy_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(11) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_psy_damage`
--

INSERT INTO `lab_psy_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'asdfasd', 'Psychology Laboratory', 'adsfads', 'dfasd', '2026-09-20', 'Damaged', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_psy_inventory`
--

CREATE TABLE `lab_psy_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` varchar(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_psy_inventory`
--

INSERT INTO `lab_psy_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'asdfa', 'asdf', 'Psychology Laboratory', 3, '3', 'Damaged', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_psy_monitoring`
--

CREATE TABLE `lab_psy_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_psy_monitoring`
--

INSERT INTO `lab_psy_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(1, 'sdfgsdfg', 'Psychology Lab', 'Under Maintenance', '2026-09-21', 'gsdfgsdf', 'agfadfgsafdeg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_qd_damage`
--

CREATE TABLE `lab_qd_damage` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `date_reported` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_qd_damage`
--

INSERT INTO `lab_qd_damage` (`id`, `item_name`, `laboratory`, `issue`, `reported_by`, `date_reported`, `status`, `is_active`) VALUES
(1, 'fdszgadsgsd', 'Question Document Laboratory', 'gsdfgsdfg', 'sdfgsdfg', '2026-09-20', 'Damaged', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_qd_inventory`
--

CREATE TABLE `lab_qd_inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `total_item` int(11) NOT NULL,
  `available_item` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_qd_inventory`
--

INSERT INTO `lab_qd_inventory` (`id`, `item_name`, `category`, `laboratory`, `total_item`, `available_item`, `status`, `is_active`) VALUES
(1, 'asdfgads', 'asdfas', 'Questioned Documents Lab', 3, 3, 'Working', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lab_qd_monitoring`
--

CREATE TABLE `lab_qd_monitoring` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `laboratory` varchar(100) NOT NULL,
  `equipment_condition` varchar(100) NOT NULL,
  `last_checked` date NOT NULL DEFAULT current_timestamp(),
  `checked_by` varchar(100) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_qd_monitoring`
--

INSERT INTO `lab_qd_monitoring` (`id`, `item_name`, `laboratory`, `equipment_condition`, `last_checked`, `checked_by`, `remarks`, `is_active`) VALUES
(0, 'gsadfgsdf', 'Question Document Laboratory', 'Under Maintenance', '2026-09-21', 'sdfgsdf', 'sdfgsdfg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab_users`
--

CREATE TABLE `lab_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Staff',
  `laboratory` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_users`
--

INSERT INTO `lab_users` (`user_id`, `username`, `password`, `full_name`, `role`, `laboratory`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38'),
(1, 'admin', '$2y$10$mTtIq5yQfqseBydoYS6rruj1qpy7Keo/yYedtHh2ITATJfcdEsS36', 'System Administrator', 'Admin', NULL, 'Active', '2026-09-12 11:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `lbr_activity_log`
--

CREATE TABLE `lbr_activity_log` (
  `id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `user` varchar(100) DEFAULT 'Librarian',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_activity_log`
--

INSERT INTO `lbr_activity_log` (`id`, `action`, `details`, `user`, `created_at`) VALUES
(1, 'System Initialized', 'Library Management System database created with sample data', 'System', '2026-03-15 02:56:43'),
(2, 'Books Added', 'Added 12 sample books to the library collection', 'System', '2026-03-15 02:56:43'),
(3, 'Members Added', 'Added 7 sample library members', 'System', '2026-03-15 02:56:43'),
(4, 'Book Borrowed', '\"Brave New World\" borrowed by David Brown (due 2026-03-22)', 'Librarian', '2026-03-18 06:05:24'),
(5, 'Book Borrowed', '\"The Little Prince\" borrowed by Bob Martinez (due 2026-03-23)', 'Librarian', '2026-03-18 06:05:37'),
(6, 'Book Borrowed', '\"The Hunger Games\" borrowed by Alice Johnson (due 2026-03-20)', 'Librarian', '2026-03-18 06:05:52'),
(7, 'Book Returned', '\"The Hunger Games\" returned (Fine: ₱10)', 'Librarian', '2026-03-18 06:50:43');

-- --------------------------------------------------------

--
-- Table structure for table `lbr_books`
--

CREATE TABLE `lbr_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `genre` varchar(100) DEFAULT 'General',
  `year` int(11) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('available','borrowed','lost','damaged') DEFAULT 'available',
  `condition` enum('Excellent','Good','Fair','Poor') DEFAULT 'Excellent',
  `cover_url` varchar(500) DEFAULT NULL,
  `cover_local` varchar(255) DEFAULT NULL,
  `added_date` date DEFAULT curdate(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_books`
--

INSERT INTO `lbr_books` (`id`, `title`, `author`, `genre`, `year`, `isbn`, `description`, `status`, `condition`, `cover_url`, `cover_local`, `added_date`, `last_updated`) VALUES
(1, 'To Kill a Mockingbird', 'Harper Lee', 'Fiction', 1960, '9780061935466', 'A Pulitzer Prize-winning masterwork of honor and injustice in the deep South—and the heroism of one man in the face of blind and violent hatred.', 'available', 'Excellent', 'https://covers.openlibrary.org/b/isbn/9780061935466-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(2, '1984', 'George Orwell', 'Science Fiction', 1949, '9780451524935', 'A dystopian novel set in a totalitarian society where Big Brother watches everything and independent thought is a crime.', 'available', 'Good', 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(3, 'The Great Gatsby', 'F. Scott Fitzgerald', 'Fiction', 1925, '9780743273565', 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.', 'borrowed', 'Good', 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(4, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 'Fantasy', 1997, '9780590353427', 'The first book in the beloved Harry Potter series following a young wizard\'s journey at Hogwarts School of Witchcraft and Wizardry.', 'available', 'Excellent', 'https://covers.openlibrary.org/b/isbn/9780590353427-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(5, 'The Hunger Games', 'Suzanne Collins', 'Science Fiction', 2008, '9780439023481', 'In a dark future, teenager Katniss Everdeen volunteers to take her sister\'s place in the deadly Hunger Games.', 'available', 'Poor', 'https://covers.openlibrary.org/b/isbn/9780439023481-L.jpg', NULL, '2026-03-15', '2026-03-18 06:50:43'),
(6, 'Pride and Prejudice', 'Jane Austen', 'Romance', 1813, '9780141439518', 'The story of Elizabeth Bennet and her evolving relationship with the proud Mr. Darcy in Regency England.', 'available', 'Excellent', 'https://covers.openlibrary.org/b/isbn/9780141439518-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(7, 'The Hobbit', 'J.R.R. Tolkien', 'Fantasy', 1937, '9780547928227', 'Bilbo Baggins, a hobbit who enjoys a comfortable life, is swept into an epic quest to reclaim treasure from a dragon.', 'available', 'Good', 'https://covers.openlibrary.org/b/isbn/9780547928227-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(8, 'Animal Farm', 'George Orwell', 'Fiction', 1945, '9780452284241', 'A satirical allegorical novella about a group of farm animals who rebel against their human farmer.', 'available', 'Fair', 'https://covers.openlibrary.org/b/isbn/9780452284241-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(9, 'The Catcher in the Rye', 'J.D. Salinger', 'Fiction', 1951, '9780316769174', 'A story about teenage rebellion and alienation told through the eyes of Holden Caulfield.', 'borrowed', 'Good', 'https://covers.openlibrary.org/b/isbn/9780316769174-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(10, 'Brave New World', 'Aldous Huxley', 'Science Fiction', 1932, '9780060850524', 'A dystopian novel set in a futuristic World State where citizens are environmentally engineered into intelligence castes.', 'borrowed', 'Excellent', 'https://covers.openlibrary.org/b/isbn/9780060850524-L.jpg', NULL, '2026-03-15', '2026-03-18 06:05:24'),
(11, 'Charlotte\'s Web', 'E.B. White', 'Children', 1952, '9780064400558', 'The story of a pig named Wilbur and his friendship with a barn spider named Charlotte.', 'available', 'Good', 'https://covers.openlibrary.org/b/isbn/9780064400558-L.jpg', NULL, '2026-03-15', '2026-03-15 02:56:43'),
(12, 'The Little Prince', 'Antoine de Saint-Exupéry', 'Children', 1943, '9780156012195', 'A poetic tale about a young prince who travels from planet to planet and lands on Earth.', 'borrowed', 'Excellent', 'https://covers.openlibrary.org/b/isbn/9780156012195-L.jpg', NULL, '2026-03-15', '2026-03-18 06:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `lbr_borrowers`
--

CREATE TABLE `lbr_borrowers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `borrower_id` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `type` enum('Student','Teacher','Staff') DEFAULT 'Student',
  `grade` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `join_date` date DEFAULT curdate(),
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_borrowers`
--

INSERT INTO `lbr_borrowers` (`id`, `name`, `borrower_id`, `email`, `phone`, `type`, `grade`, `address`, `join_date`, `active`) VALUES
(1, 'Alice Johnson', 'STU-001', 'alice@school.edu', '555-0101', 'Student', 'Grade 10', NULL, '2024-01-15', 1),
(2, 'Bob Martinez', 'STU-002', 'bob@school.edu', '555-0102', 'Student', 'Grade 11', NULL, '2024-01-20', 1),
(3, 'Carol Williams', 'STU-003', 'carol@school.edu', '555-0103', 'Student', 'Grade 9', NULL, '2024-02-01', 1),
(4, 'David Brown', 'STU-004', 'david@school.edu', '555-0104', 'Student', 'Grade 12', NULL, '2024-02-10', 1),
(5, 'Ms. Emily Chen', 'TCH-001', 'emily.chen@school.edu', '555-0201', 'Teacher', 'Science Dept', NULL, '2023-09-01', 1),
(6, 'Mr. James Wilson', 'TCH-002', 'james.wilson@school.edu', '555-0202', 'Teacher', 'English Dept', NULL, '2023-09-01', 1),
(7, 'Sarah Davis', 'STF-001', 'sarah.davis@school.edu', '555-0301', 'Staff', 'Administration', NULL, '2023-08-15', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lbr_settings`
--

CREATE TABLE `lbr_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_settings`
--

INSERT INTO `lbr_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'library_name', 'School Library Management System', '2026-03-15 02:56:43'),
(2, 'max_borrow_days', '14', '2026-03-15 02:56:43'),
(3, 'max_books_per_member', '3', '2026-03-15 02:56:43'),
(4, 'daily_fine_rate', '0.50', '2026-03-15 02:56:43'),
(5, 'auto_save', 'true', '2026-03-15 02:56:43');

-- --------------------------------------------------------

--
-- Table structure for table `lbr_transactions`
--

CREATE TABLE `lbr_transactions` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('active','returned','overdue') DEFAULT 'active',
  `condition` varchar(50) DEFAULT NULL,
  `fine` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_transactions`
--

INSERT INTO `lbr_transactions` (`id`, `book_id`, `borrower_id`, `borrow_date`, `due_date`, `return_date`, `status`, `condition`, `fine`, `notes`, `created_at`) VALUES
(1, 3, 1, '2026-03-10', '2026-03-24', NULL, 'active', NULL, 0.00, NULL, '2026-03-15 02:56:43'),
(2, 9, 2, '2026-02-23', '2026-03-09', NULL, 'overdue', NULL, 4.50, NULL, '2026-03-15 02:56:43'),
(3, 10, 4, '2026-03-18', '2026-03-22', NULL, 'active', NULL, 0.00, '', '2026-03-18 06:05:24'),
(4, 12, 2, '2026-03-18', '2026-03-23', NULL, 'active', NULL, 0.00, '', '2026-03-18 06:05:37'),
(5, 5, 1, '2026-03-18', '2026-03-20', '2026-03-18', 'returned', 'Poor', 10.00, '', '2026-03-18 06:05:52');

-- --------------------------------------------------------

--
-- Table structure for table `mon_attendance`
--

CREATE TABLE `mon_attendance` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `status` enum('Present','Late','Absent','Official Business','Early Dismissal','Academic Tour','No Teacher','Early Break') DEFAULT 'Present',
  `remarks` int(11) DEFAULT NULL,
  `student_count` int(11) DEFAULT 0,
  `class_type` enum('onsite','online') DEFAULT 'onsite',
  `online_platform` varchar(50) DEFAULT NULL,
  `meeting_link` text DEFAULT NULL,
  `meeting_id` varchar(100) DEFAULT NULL,
  `meeting_password` varchar(100) DEFAULT NULL,
  `online_attendance_file` varchar(255) DEFAULT NULL,
  `internet_status` enum('stable','unstable','intermittent') DEFAULT NULL,
  `connectivity_issues` text DEFAULT NULL,
  `recorded_by` varchar(100) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_courses`
--

CREATE TABLE `mon_courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(50) DEFAULT NULL,
  `course_name` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) DEFAULT 3,
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_enrollment`
--

CREATE TABLE `mon_enrollment` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `school_year` varchar(50) DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_facilities_monitor`
--

CREATE TABLE `mon_facilities_monitor` (
  `id` int(11) NOT NULL,
  `room` varchar(100) NOT NULL,
  `issue_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('Low','Medium','High','Urgent') DEFAULT 'Medium',
  `reported_by` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Fixed') DEFAULT 'Pending',
  `date_reported` date DEFAULT NULL,
  `date_fixed` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_faculty`
--

CREATE TABLE `mon_faculty` (
  `id` int(11) NOT NULL,
  `faculty_code` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_program_monitor`
--

CREATE TABLE `mon_program_monitor` (
  `id` int(11) NOT NULL,
  `program` varchar(100) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `school_year` varchar(50) NOT NULL,
  `student_count` int(11) NOT NULL DEFAULT 0,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_reports`
--

CREATE TABLE `mon_reports` (
  `id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `school_year` varchar(50) DEFAULT NULL,
  `report_date` date NOT NULL,
  `summary` text DEFAULT NULL,
  `generated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_schedule`
--

CREATE TABLE `mon_schedule` (
  `id` int(11) NOT NULL,
  `room` varchar(20) DEFAULT NULL,
  `official_time` varchar(50) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `subject_code` varchar(50) DEFAULT NULL,
  `grade_section_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `school_year` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_sections`
--

CREATE TABLE `mon_sections` (
  `id` int(11) NOT NULL,
  `section_code` varchar(50) DEFAULT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `school_year` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_utilities_monitor`
--

CREATE TABLE `mon_utilities_monitor` (
  `id` int(11) NOT NULL,
  `utility_type` varchar(50) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `school_year` varchar(50) DEFAULT NULL,
  `reading_date` date NOT NULL,
  `usage_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` varchar(100) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mon_visitors_log`
--

CREATE TABLE `mon_visitors_log` (
  `id` int(11) NOT NULL,
  `visitor_name` varchar(255) NOT NULL,
  `purpose` text DEFAULT NULL,
  `person_to_visit` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `id_presented` varchar(100) DEFAULT NULL,
  `recorded_by` varchar(100) DEFAULT NULL,
  `time_in` datetime DEFAULT current_timestamp(),
  `time_out` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rgr_activity_log`
--

CREATE TABLE `rgr_activity_log` (
  `id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `action` varchar(250) NOT NULL,
  `description` varchar(250) NOT NULL,
  `ip_address` varchar(250) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_activity_log`
--

INSERT INTO `rgr_activity_log` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`, `updated_at`) VALUES
(9, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-02-23 18:47:18', '2026-02-23 18:47:18'),
(10, NULL, 'Get A eXCEL all student Report', 'Downloading a Excel file contains all students information', '::1', '2026-02-25 06:46:53', '2026-02-25 06:46:53'),
(11, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '127.0.0.1', '2026-02-25 09:00:35', '2026-02-25 09:00:35'),
(12, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '127.0.0.1', '2026-02-25 09:02:21', '2026-02-25 09:02:21'),
(13, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-02-25 09:05:28', '2026-02-25 09:05:28'),
(14, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-02-25 09:05:51', '2026-02-25 09:05:51'),
(42, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-26 20:35:42', '2026-02-26 20:35:42'),
(43, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-02-26 20:38:49', '2026-02-26 20:38:49'),
(44, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-26 20:40:35', '2026-02-26 20:40:35'),
(45, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-26 21:23:02', '2026-02-26 21:23:02'),
(66, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-28 02:23:34', '2026-02-28 02:23:34'),
(75, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-02-28 18:03:07', '2026-02-28 18:03:07'),
(76, NULL, 'Delete Course Input', 'Deleting 3 item/s Course Information', '::1', '2026-02-28 18:03:19', '2026-02-28 18:03:19'),
(77, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-28 18:03:41', '2026-02-28 18:03:41'),
(78, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-02-28 18:03:48', '2026-02-28 18:03:48'),
(79, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-28 18:20:39', '2026-02-28 18:20:39'),
(80, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-28 20:08:42', '2026-02-28 20:08:42'),
(81, NULL, 'Delete Course Input', 'Deleting 2 item/s Course Information', '::1', '2026-02-28 20:14:47', '2026-02-28 20:14:47'),
(82, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-28 20:23:28', '2026-02-28 20:23:28'),
(83, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-02-28 20:24:32', '2026-02-28 20:24:32'),
(84, NULL, 'Delete Course Input', 'Deleting 2 item/s Course Information', '::1', '2026-03-01 15:05:54', '2026-03-01 15:05:54'),
(114, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-02 18:45:05', '2026-03-02 18:45:05'),
(115, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-03-02 18:45:52', '2026-03-02 18:45:52'),
(116, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-02 19:03:48', '2026-03-02 19:03:48'),
(117, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-02 19:25:59', '2026-03-02 19:25:59'),
(118, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-02 21:39:05', '2026-03-02 21:39:05'),
(119, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-02 21:39:14', '2026-03-02 21:39:14'),
(120, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-02 21:39:23', '2026-03-02 21:39:23'),
(121, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-03-02 22:01:53', '2026-03-02 22:01:53'),
(122, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-02 22:02:12', '2026-03-02 22:02:12'),
(123, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-03-03 15:06:56', '2026-03-03 15:06:56'),
(124, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-03-03 15:07:16', '2026-03-03 15:07:16'),
(125, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-03 15:07:29', '2026-03-03 15:07:29'),
(126, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-03-03 15:07:36', '2026-03-03 15:07:36'),
(127, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-03-03 15:23:36', '2026-03-03 15:23:36'),
(128, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-03 15:23:45', '2026-03-03 15:23:45'),
(129, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:28:21', '2026-03-04 04:28:21'),
(130, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:29:30', '2026-03-04 04:29:30'),
(131, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:29:32', '2026-03-04 04:29:32'),
(132, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:29:33', '2026-03-04 04:29:33'),
(133, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:29:43', '2026-03-04 04:29:43'),
(134, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:29:54', '2026-03-04 04:29:54'),
(135, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:32:56', '2026-03-04 04:32:56'),
(136, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:33:06', '2026-03-04 04:33:06'),
(137, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:33:44', '2026-03-04 04:33:44'),
(138, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:35:41', '2026-03-04 04:35:41'),
(139, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:36:53', '2026-03-04 04:36:53'),
(140, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:36:53', '2026-03-04 04:36:53'),
(141, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:42:10', '2026-03-04 04:42:10'),
(142, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:42:21', '2026-03-04 04:42:21'),
(143, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 04:42:23', '2026-03-04 04:42:23'),
(164, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-04 07:32:50', '2026-03-04 07:32:50'),
(165, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-04 07:33:07', '2026-03-04 07:33:07'),
(172, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-03-05 00:16:41', '2026-03-05 00:16:41'),
(173, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-05 01:25:00', '2026-03-05 01:25:00'),
(174, NULL, 'Deleted A Semester Record', 'Deleted A Semester Record Information for System', '::1', '2026-03-05 01:26:01', '2026-03-05 01:26:01'),
(175, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-05 01:26:13', '2026-03-05 01:26:13'),
(176, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-05 01:26:27', '2026-03-05 01:26:27'),
(177, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-03-05 01:26:41', '2026-03-05 01:26:41'),
(178, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-05 06:38:03', '2026-03-05 06:38:03'),
(179, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-05 06:38:24', '2026-03-05 06:38:24'),
(180, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-03-05 06:46:31', '2026-03-05 06:46:31'),
(181, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-03-05 06:47:01', '2026-03-05 06:47:01'),
(182, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 06:47:11', '2026-03-05 06:47:11'),
(183, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 07:06:52', '2026-03-05 07:06:52'),
(184, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 07:07:18', '2026-03-05 07:07:18'),
(185, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 07:07:27', '2026-03-05 07:07:27'),
(186, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 07:08:03', '2026-03-05 07:08:03'),
(187, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 07:10:41', '2026-03-05 07:10:41'),
(188, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 07:25:52', '2026-03-05 07:25:52'),
(189, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 07:26:08', '2026-03-05 07:26:08'),
(190, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 07:26:57', '2026-03-05 07:26:57'),
(213, NULL, 'Get A CSV of School Year Report', 'Downloading a CSV file contains School Year Information', '::1', '2026-03-05 19:24:38', '2026-03-05 19:24:38'),
(214, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-03-05 19:32:23', '2026-03-05 19:32:23'),
(215, NULL, 'Updated A Course', 'Updated the Course Information.', '::1', '2026-03-05 19:32:42', '2026-03-05 19:32:42'),
(216, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-03-05 19:32:50', '2026-03-05 19:32:50'),
(217, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-05 21:56:45', '2026-03-05 21:56:45'),
(218, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-05 21:57:04', '2026-03-05 21:57:04'),
(219, NULL, 'Updated A Course', 'Updated the Course Information.', '::1', '2026-03-06 03:03:40', '2026-03-06 03:03:40'),
(220, NULL, 'Updated A Course', 'Updated the Course Information.', '::1', '2026-03-06 03:03:46', '2026-03-06 03:03:46'),
(221, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-06 03:03:50', '2026-03-06 03:03:50'),
(222, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-06 03:05:30', '2026-03-06 03:05:30'),
(223, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-03-06 03:06:02', '2026-03-06 03:06:02'),
(224, NULL, 'Get A Excel Course student Report', 'Downloading a Excel file contains Course Information', '::1', '2026-03-06 03:16:42', '2026-03-06 03:16:42'),
(225, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-03-06 03:24:15', '2026-03-06 03:24:15'),
(226, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-06 03:38:01', '2026-03-06 03:38:01'),
(227, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-03-06 03:45:31', '2026-03-06 03:45:31'),
(232, NULL, 'Get A CSV Course student Report', 'Downloading a CSV file contains Course Information', '::1', '2026-03-06 07:10:29', '2026-03-06 07:10:29'),
(233, NULL, 'Deleted A Semester Record', 'Deleted A Semester Record Information for System', '::1', '2026-03-06 17:40:13', '2026-03-06 17:40:13'),
(234, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-06 17:48:32', '2026-03-06 17:48:32'),
(235, NULL, 'Created A New School Semester', 'Created A New Schoool Semester Information for System', '::1', '2026-03-06 18:07:39', '2026-03-06 18:07:39'),
(236, NULL, 'Created A New Strand', 'Created A New Strand Information for System', '::1', '2026-03-06 18:31:19', '2026-03-06 18:31:19'),
(237, NULL, 'Created A New Strand', 'Created A New Strand Information for System', '::1', '2026-03-06 18:41:52', '2026-03-06 18:41:52'),
(238, NULL, 'Created A New Strand', 'Created A New Strand Information for System', '::1', '2026-03-06 18:44:12', '2026-03-06 18:44:12'),
(239, NULL, 'Created A New Strand', 'Created A New Strand Information for System', '::1', '2026-03-06 18:44:45', '2026-03-06 18:44:45'),
(240, NULL, 'Get A CSV Course student Report', 'Downloading a CSV file contains Course Information', '::1', '2026-03-06 18:44:53', '2026-03-06 18:44:53'),
(241, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-06 18:47:26', '2026-03-06 18:47:26'),
(242, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-06 18:47:43', '2026-03-06 18:47:43'),
(243, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-06 18:47:44', '2026-03-06 18:47:44'),
(244, NULL, 'Delete Strand Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-06 18:48:36', '2026-03-06 18:48:36'),
(245, NULL, 'Delete Strand Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-06 18:48:41', '2026-03-06 18:48:41'),
(246, NULL, 'Delete Strand Input', 'Deleting 2 item/s Course Information', '::1', '2026-03-06 18:48:49', '2026-03-06 18:48:49'),
(247, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-06 19:23:49', '2026-03-06 19:23:49'),
(248, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-06 19:23:51', '2026-03-06 19:23:51'),
(249, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-06 19:24:02', '2026-03-06 19:24:02'),
(250, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-06 19:24:19', '2026-03-06 19:24:19'),
(251, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-06 19:24:38', '2026-03-06 19:24:38'),
(252, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-06 19:25:23', '2026-03-06 19:25:23'),
(253, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-06 19:25:40', '2026-03-06 19:25:40'),
(254, NULL, 'Created A New Strand', 'Created A New Strand Information for System', '::1', '2026-03-06 19:26:51', '2026-03-06 19:26:51'),
(255, NULL, 'Delete Strand Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-06 19:26:59', '2026-03-06 19:26:59'),
(258, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-07 18:32:41', '2026-03-07 18:32:41'),
(259, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-07 18:33:42', '2026-03-07 18:33:42'),
(260, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-07 18:33:58', '2026-03-07 18:33:58'),
(261, NULL, 'Get A PDF of Strands Report', 'Downloading a PDF file contains Strands Information', '::1', '2026-03-07 18:34:38', '2026-03-07 18:34:38'),
(262, NULL, 'Get A PDF of Strands Report', 'Downloading a PDF file contains Strands Information', '::1', '2026-03-07 18:34:54', '2026-03-07 18:34:54'),
(263, NULL, 'Get A Excel of Strand Report', 'Downloading a Excel file contains Semester Information', '::1', '2026-03-07 18:40:30', '2026-03-07 18:40:30'),
(264, NULL, 'Get A CSV of School Year Report', 'Downloading a CSV file contains School Year Information', '::1', '2026-03-07 18:43:15', '2026-03-07 18:43:15'),
(265, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-07 18:44:04', '2026-03-07 18:44:04'),
(266, NULL, 'Updated A Strand', 'Updated the Strand Information.', '::1', '2026-03-07 18:44:09', '2026-03-07 18:44:09'),
(267, NULL, 'Get A CSV of Strand Report', 'Downloading a CSV file contains Strand Information', '::1', '2026-03-07 18:47:48', '2026-03-07 18:47:48'),
(268, NULL, 'Get A Excel of Strand Report', 'Downloading a Excel file contains Semester Information', '::1', '2026-03-07 18:48:12', '2026-03-07 18:48:12'),
(269, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-08 05:36:43', '2026-03-08 05:36:43'),
(270, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-09 14:52:05', '2026-03-09 14:52:05'),
(271, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-09 15:59:37', '2026-03-09 15:59:37'),
(272, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-09 16:05:23', '2026-03-09 16:05:23'),
(275, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-11 08:04:21', '2026-03-11 08:04:21'),
(276, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-11 08:55:21', '2026-03-11 08:55:21'),
(277, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-11 09:01:14', '2026-03-11 09:01:14'),
(278, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-03-11 09:17:13', '2026-03-11 09:17:13'),
(279, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-12 03:25:14', '2026-03-12 03:25:14'),
(280, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-12 03:25:18', '2026-03-12 03:25:18'),
(281, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-12 03:25:28', '2026-03-12 03:25:28'),
(282, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-12 03:25:29', '2026-03-12 03:25:29'),
(283, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-12 03:25:29', '2026-03-12 03:25:29'),
(284, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-12 03:26:25', '2026-03-12 03:26:25'),
(285, NULL, 'Get A CSV Course student Report', 'Downloading a CSV file contains Course Information', '::1', '2026-03-12 03:26:43', '2026-03-12 03:26:43'),
(286, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-12 03:27:06', '2026-03-12 03:27:06'),
(287, NULL, 'Delete Subject Input', 'Deleting 2 item/s Course Information', '::1', '2026-03-12 03:32:19', '2026-03-12 03:32:19'),
(288, NULL, 'Delete Subject Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-12 03:34:12', '2026-03-12 03:34:12'),
(289, NULL, 'Delete Subject Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-12 03:36:02', '2026-03-12 03:36:02'),
(290, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:39:37', '2026-03-12 03:39:37'),
(291, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:41:40', '2026-03-12 03:41:40'),
(292, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:42:39', '2026-03-12 03:42:39'),
(293, NULL, 'Delete Strand Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-12 03:44:39', '2026-03-12 03:44:39'),
(294, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:47:49', '2026-03-12 03:47:49'),
(295, NULL, 'Get A CSV Course student Report', 'Downloading a CSV file contains Course Information', '::1', '2026-03-12 03:48:23', '2026-03-12 03:48:23'),
(296, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:49:19', '2026-03-12 03:49:19'),
(297, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:49:54', '2026-03-12 03:49:54'),
(298, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:51:40', '2026-03-12 03:51:40'),
(299, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:53:50', '2026-03-12 03:53:50'),
(300, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:54:14', '2026-03-12 03:54:14'),
(301, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:54:21', '2026-03-12 03:54:21'),
(302, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:55:21', '2026-03-12 03:55:21'),
(303, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:55:26', '2026-03-12 03:55:26'),
(304, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-12 03:57:10', '2026-03-12 03:57:10'),
(305, NULL, 'Deleted A Semester Record', 'Deleted A Semester Record Information for System', '::1', '2026-03-12 03:59:14', '2026-03-12 03:59:14'),
(306, NULL, 'Delete Subject Input', 'Deleting 3 item/s Subject Information', '::1', '2026-03-12 04:01:04', '2026-03-12 04:01:04'),
(307, NULL, 'Delete Subject Input', 'Deleting 4 item/s Subject Information', '::1', '2026-03-12 04:01:16', '2026-03-12 04:01:16'),
(308, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-12 04:01:50', '2026-03-12 04:01:50'),
(309, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-15 03:33:15', '2026-03-15 03:33:15'),
(310, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-03-15 04:37:52', '2026-03-15 04:37:52'),
(311, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-15 04:38:00', '2026-03-15 04:38:00'),
(312, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-03-15 04:38:15', '2026-03-15 04:38:15'),
(313, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-03-15 04:38:22', '2026-03-15 04:38:22'),
(316, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-15 04:40:59', '2026-03-15 04:40:59'),
(317, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-15 04:41:04', '2026-03-15 04:41:04'),
(318, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-03-15 04:41:14', '2026-03-15 04:41:14'),
(319, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-03-15 04:42:33', '2026-03-15 04:42:33'),
(320, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-15 04:42:43', '2026-03-15 04:42:43'),
(321, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-15 06:42:46', '2026-03-15 06:42:46'),
(322, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-03-15 06:44:12', '2026-03-15 06:44:12'),
(323, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-03-17 07:01:17', '2026-03-17 07:01:17'),
(324, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '192.168.68.139', '2026-03-17 09:02:27', '2026-03-17 09:02:27'),
(325, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '192.168.68.139', '2026-03-17 09:02:44', '2026-03-17 09:02:44'),
(326, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '192.168.68.139', '2026-03-17 09:02:47', '2026-03-17 09:02:47'),
(327, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '192.168.68.139', '2026-03-17 09:02:55', '2026-03-17 09:02:55'),
(328, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '192.168.68.139', '2026-03-17 09:02:57', '2026-03-17 09:02:57'),
(329, NULL, 'Created A New Strand', 'Created A New Strand Information for System', '192.168.68.139', '2026-03-17 09:05:25', '2026-03-17 09:05:25'),
(330, NULL, 'Delete Strand Input', 'Deleting 1 item/s Course Information', '192.168.68.139', '2026-03-17 09:05:53', '2026-03-17 09:05:53'),
(331, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '192.168.68.139', '2026-03-17 09:06:35', '2026-03-17 09:06:35'),
(332, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '192.168.68.139', '2026-03-17 09:08:02', '2026-03-17 09:08:02'),
(333, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '192.168.68.139', '2026-03-17 09:08:10', '2026-03-17 09:08:10'),
(334, NULL, 'Created A New Course', 'Created A New Course Information for System', '192.168.68.139', '2026-03-17 10:00:22', '2026-03-17 10:00:22'),
(335, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '192.168.68.139', '2026-03-17 10:00:28', '2026-03-17 10:00:28'),
(336, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '192.168.68.139', '2026-03-17 10:00:42', '2026-03-17 10:00:42'),
(337, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-03-17 13:09:17', '2026-03-17 13:09:17'),
(338, NULL, 'Created A New Course', 'Created A New Course Information for System', '10.110.161.222', '2026-03-18 04:56:18', '2026-03-18 04:56:18'),
(339, NULL, 'Updated A Course', 'Updated the Course Information.', '10.110.161.222', '2026-03-18 05:15:58', '2026-03-18 05:15:58'),
(340, NULL, 'Updated A Course', 'Updated the Course Information.', '10.110.161.222', '2026-03-18 05:16:04', '2026-03-18 05:16:04'),
(341, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '10.110.161.222', '2026-03-18 05:16:24', '2026-03-18 05:16:24'),
(342, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '10.110.161.222', '2026-03-18 05:16:38', '2026-03-18 05:16:38'),
(343, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '10.110.161.222', '2026-03-18 05:16:42', '2026-03-18 05:16:42'),
(344, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '10.110.161.222', '2026-03-18 05:16:58', '2026-03-18 05:16:58'),
(345, NULL, 'Get A CSV of School Year Report', 'Downloading a CSV file contains School Year Information', '::1', '2026-03-19 06:13:40', '2026-03-19 06:13:40'),
(346, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-19 06:40:14', '2026-03-19 06:40:14'),
(347, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-19 06:41:04', '2026-03-19 06:41:04'),
(348, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-03-19 06:43:00', '2026-03-19 06:43:00'),
(349, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-03-19 08:04:17', '2026-03-19 08:04:17'),
(350, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-19 08:46:28', '2026-03-19 08:46:28'),
(351, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-19 09:52:29', '2026-03-19 09:52:29'),
(352, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-19 09:52:52', '2026-03-19 09:52:52'),
(353, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-19 10:24:39', '2026-03-19 10:24:39'),
(354, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-19 10:25:47', '2026-03-19 10:25:47'),
(355, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-19 10:26:15', '2026-03-19 10:26:15'),
(356, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-19 10:27:42', '2026-03-19 10:27:42'),
(357, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-19 10:27:57', '2026-03-19 10:27:57'),
(358, NULL, 'Delete Subject Input', 'Deleting 6 item/s Subject Information', '::1', '2026-03-19 10:45:18', '2026-03-19 10:45:18'),
(359, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-19 10:45:40', '2026-03-19 10:45:40'),
(360, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-19 10:46:19', '2026-03-19 10:46:19'),
(361, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-03-22 05:13:33', '2026-03-22 05:13:33'),
(362, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-03-22 05:13:38', '2026-03-22 05:13:38'),
(363, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-22 05:14:01', '2026-03-22 05:14:01'),
(364, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-22 05:14:21', '2026-03-22 05:14:21'),
(365, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-22 05:14:45', '2026-03-22 05:14:45'),
(366, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 05:18:00', '2026-03-22 05:18:00'),
(367, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:31:05', '2026-03-22 09:31:05'),
(368, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-03-22 09:31:11', '2026-03-22 09:31:11'),
(369, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-03-22 09:34:06', '2026-03-22 09:34:06'),
(370, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:34:49', '2026-03-22 09:34:49'),
(371, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-03-22 09:35:05', '2026-03-22 09:35:05'),
(372, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:35:17', '2026-03-22 09:35:17'),
(373, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:35:38', '2026-03-22 09:35:38'),
(374, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:36:10', '2026-03-22 09:36:10'),
(375, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:36:44', '2026-03-22 09:36:44'),
(376, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:51:49', '2026-03-22 09:51:49'),
(377, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-03-22 09:52:29', '2026-03-22 09:52:29'),
(378, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:52:46', '2026-03-22 09:52:46'),
(379, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:53:30', '2026-03-22 09:53:30'),
(380, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-22 09:54:04', '2026-03-22 09:54:04'),
(381, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-22 09:54:56', '2026-03-22 09:54:56'),
(382, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-22 09:55:43', '2026-03-22 09:55:43'),
(383, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-22 09:56:22', '2026-03-22 09:56:22'),
(384, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-22 09:56:37', '2026-03-22 09:56:37'),
(385, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-22 09:56:52', '2026-03-22 09:56:52'),
(386, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-22 10:07:10', '2026-03-22 10:07:10'),
(387, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-22 10:07:30', '2026-03-22 10:07:30'),
(388, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:08:28', '2026-03-22 10:08:28'),
(389, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:08:57', '2026-03-22 10:08:57'),
(390, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:09:13', '2026-03-22 10:09:13'),
(391, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:09:34', '2026-03-22 10:09:34'),
(392, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:10:08', '2026-03-22 10:10:08'),
(393, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:10:25', '2026-03-22 10:10:25'),
(394, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:10:48', '2026-03-22 10:10:48'),
(395, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:11:04', '2026-03-22 10:11:04'),
(396, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:21:11', '2026-03-22 10:21:11'),
(397, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-03-22 10:21:20', '2026-03-22 10:21:20'),
(398, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:23:46', '2026-03-22 10:23:46'),
(399, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:24:05', '2026-03-22 10:24:05'),
(400, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:24:27', '2026-03-22 10:24:27'),
(401, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-03-22 10:25:06', '2026-03-22 10:25:06'),
(402, NULL, 'Created A New School Semester', 'Created A New Schoool Semester Information for System', '::1', '2026-03-22 14:19:04', '2026-03-22 14:19:04'),
(403, NULL, 'Get A CSV Course student Report', 'Downloading a CSV file contains Course Information', '::1', '2026-03-22 23:51:07', '2026-03-22 23:51:07'),
(404, NULL, 'Created A Room', 'Created A Room Information for System', '::1', '2026-03-24 04:26:50', '2026-03-24 04:26:50'),
(405, NULL, 'Delete Course Input', 'Deleting 2 item/s Course Information', '::1', '2026-03-24 06:00:52', '2026-03-24 06:00:52'),
(406, NULL, 'Delete Course Input', 'Deleting 4 item/s Course Information', '::1', '2026-03-24 06:19:01', '2026-03-24 06:19:01'),
(407, NULL, 'Created A New Teacher', 'Created A New Teacher Information for System', '::1', '2026-03-24 06:44:57', '2026-03-24 06:44:57'),
(408, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-24 06:51:10', '2026-03-24 06:51:10'),
(409, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-03-24 06:52:59', '2026-03-24 06:52:59'),
(410, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-24 07:06:19', '2026-03-24 07:06:19'),
(411, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-24 07:08:32', '2026-03-24 07:08:32'),
(412, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-24 07:21:26', '2026-03-24 07:21:26'),
(413, NULL, 'Delete Course Input', 'Deleting 2 item/s Course Information', '::1', '2026-03-24 19:24:47', '2026-03-24 19:24:47'),
(414, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-26 17:35:55', '2026-03-26 17:35:55'),
(415, NULL, 'Delete Course Input', 'Deleting 2 item/s Course Information', '::1', '2026-03-26 17:37:21', '2026-03-26 17:37:21'),
(416, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-29 01:57:43', '2026-03-29 01:57:43'),
(417, NULL, 'Delete Course Input', 'Deleting 2 item/s Course Information', '::1', '2026-03-29 01:58:34', '2026-03-29 01:58:34'),
(418, NULL, 'Delete Course Input', 'Deleting 2 item/s Course Information', '::1', '2026-03-29 02:01:06', '2026-03-29 02:01:06'),
(419, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-03-29 03:48:14', '2026-03-29 03:48:14'),
(420, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:19:58', '2026-03-29 18:19:58'),
(421, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:20:25', '2026-03-29 18:20:25'),
(422, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:25:24', '2026-03-29 18:25:24'),
(423, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:26:29', '2026-03-29 18:26:29'),
(424, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:26:45', '2026-03-29 18:26:45'),
(425, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:28:18', '2026-03-29 18:28:18'),
(426, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:29:46', '2026-03-29 18:29:46'),
(427, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:30:01', '2026-03-29 18:30:01'),
(428, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:37:43', '2026-03-29 18:37:43'),
(429, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:38:09', '2026-03-29 18:38:09'),
(430, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:40:10', '2026-03-29 18:40:10'),
(431, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:40:24', '2026-03-29 18:40:24'),
(432, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:44:08', '2026-03-29 18:44:08'),
(433, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:44:17', '2026-03-29 18:44:17'),
(434, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:44:29', '2026-03-29 18:44:29'),
(435, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:44:41', '2026-03-29 18:44:41'),
(436, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:45:42', '2026-03-29 18:45:42'),
(437, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:47:30', '2026-03-29 18:47:30'),
(438, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:47:49', '2026-03-29 18:47:49'),
(439, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:48:17', '2026-03-29 18:48:17'),
(440, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:48:42', '2026-03-29 18:48:42'),
(441, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:49:32', '2026-03-29 18:49:32'),
(442, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:50:17', '2026-03-29 18:50:17'),
(443, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:50:29', '2026-03-29 18:50:29'),
(444, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:50:43', '2026-03-29 18:50:43'),
(445, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:51:46', '2026-03-29 18:51:46'),
(446, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:52:02', '2026-03-29 18:52:02'),
(447, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:54:15', '2026-03-29 18:54:15'),
(448, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:54:23', '2026-03-29 18:54:23'),
(449, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:54:57', '2026-03-29 18:54:57'),
(450, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:55:30', '2026-03-29 18:55:30'),
(451, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:55:44', '2026-03-29 18:55:44'),
(452, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:56:12', '2026-03-29 18:56:12'),
(453, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 18:58:41', '2026-03-29 18:58:41'),
(454, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:11:16', '2026-03-29 19:11:16'),
(455, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:11:28', '2026-03-29 19:11:28'),
(456, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:12:15', '2026-03-29 19:12:15'),
(457, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:12:23', '2026-03-29 19:12:23'),
(458, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:12:37', '2026-03-29 19:12:37'),
(459, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:13:40', '2026-03-29 19:13:40'),
(460, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:14:03', '2026-03-29 19:14:03'),
(461, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:15:50', '2026-03-29 19:15:50'),
(462, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:16:28', '2026-03-29 19:16:28'),
(463, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:28:42', '2026-03-29 19:28:42'),
(464, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-03-29 19:28:58', '2026-03-29 19:28:58'),
(465, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-01 05:22:52', '2026-04-01 05:22:52'),
(466, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-01 05:27:00', '2026-04-01 05:27:00'),
(467, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-01 05:29:01', '2026-04-01 05:29:01'),
(468, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-01 05:30:03', '2026-04-01 05:30:03'),
(469, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-02 05:08:51', '2026-04-02 05:08:51'),
(470, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-02 05:09:12', '2026-04-02 05:09:12'),
(471, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-02 05:11:01', '2026-04-02 05:11:01'),
(472, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-02 05:11:16', '2026-04-02 05:11:16'),
(473, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-02 05:11:29', '2026-04-02 05:11:29'),
(474, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-04 02:51:16', '2026-04-04 02:51:16'),
(475, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-04 03:06:30', '2026-04-04 03:06:30'),
(476, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-04 03:06:43', '2026-04-04 03:06:43'),
(477, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-04 03:08:40', '2026-04-04 03:08:40'),
(478, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-04 03:28:09', '2026-04-04 03:28:09'),
(479, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-09 03:16:38', '2026-04-09 03:16:38'),
(480, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-09 03:16:57', '2026-04-09 03:16:57'),
(481, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-09 18:40:49', '2026-04-09 18:40:49'),
(482, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-09 20:28:52', '2026-04-09 20:28:52'),
(483, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-09 20:34:30', '2026-04-09 20:34:30'),
(484, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-09 20:35:13', '2026-04-09 20:35:13'),
(485, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-09 20:41:11', '2026-04-09 20:41:11'),
(486, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-09 20:51:17', '2026-04-09 20:51:17'),
(487, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-12 16:17:41', '2026-04-12 16:17:41'),
(488, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-12 16:18:01', '2026-04-12 16:18:01'),
(489, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-12 16:18:34', '2026-04-12 16:18:34'),
(490, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-12 16:20:14', '2026-04-12 16:20:14'),
(491, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-12 16:28:43', '2026-04-12 16:28:43'),
(492, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-13 16:36:42', '2026-04-13 16:36:42'),
(493, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-04-13 16:39:27', '2026-04-13 16:39:27'),
(494, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-04-13 16:51:30', '2026-04-13 16:51:30'),
(495, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-13 17:10:04', '2026-04-13 17:10:04'),
(496, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-13 17:12:18', '2026-04-13 17:12:18'),
(497, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-04-13 17:38:26', '2026-04-13 17:38:26'),
(498, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-04-13 17:39:16', '2026-04-13 17:39:16'),
(499, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-04-13 17:39:36', '2026-04-13 17:39:36'),
(500, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-04-13 17:40:50', '2026-04-13 17:40:50');
INSERT INTO `rgr_activity_log` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`, `updated_at`) VALUES
(501, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-04-13 17:42:46', '2026-04-13 17:42:46'),
(502, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-04-13 18:31:11', '2026-04-13 18:31:11'),
(503, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-04-14 20:10:43', '2026-04-14 20:10:43'),
(504, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-16 08:21:56', '2026-04-16 08:21:56'),
(505, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-16 08:25:10', '2026-04-16 08:25:10'),
(506, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-16 08:33:51', '2026-04-16 08:33:51'),
(507, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-04-16 08:38:38', '2026-04-16 08:38:38'),
(508, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-16 08:39:05', '2026-04-16 08:39:05'),
(509, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-19 05:33:58', '2026-04-19 05:33:58'),
(510, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-19 05:34:44', '2026-04-19 05:34:44'),
(511, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-19 05:49:30', '2026-04-19 05:49:30'),
(512, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-19 05:50:50', '2026-04-19 05:50:50'),
(513, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 00:29:53', '2026-04-20 00:29:53'),
(514, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 00:31:06', '2026-04-20 00:31:06'),
(515, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-20 00:32:08', '2026-04-20 00:32:08'),
(516, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 00:32:19', '2026-04-20 00:32:19'),
(517, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 01:30:34', '2026-04-20 01:30:34'),
(518, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 01:33:46', '2026-04-20 01:33:46'),
(519, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:19:02', '2026-04-20 02:19:02'),
(520, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:23:07', '2026-04-20 02:23:07'),
(521, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:25:03', '2026-04-20 02:25:03'),
(522, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:25:34', '2026-04-20 02:25:34'),
(523, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:25:53', '2026-04-20 02:25:53'),
(524, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:27:55', '2026-04-20 02:27:55'),
(525, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:28:50', '2026-04-20 02:28:50'),
(526, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:33:32', '2026-04-20 02:33:32'),
(527, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:34:20', '2026-04-20 02:34:20'),
(528, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:38:42', '2026-04-20 02:38:42'),
(529, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:40:11', '2026-04-20 02:40:11'),
(530, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:41:13', '2026-04-20 02:41:13'),
(531, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:41:54', '2026-04-20 02:41:54'),
(532, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:42:31', '2026-04-20 02:42:31'),
(533, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:43:05', '2026-04-20 02:43:05'),
(534, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:43:55', '2026-04-20 02:43:55'),
(535, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:44:24', '2026-04-20 02:44:24'),
(536, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 02:54:06', '2026-04-20 02:54:06'),
(537, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 03:05:20', '2026-04-20 03:05:20'),
(538, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 03:16:29', '2026-04-20 03:16:29'),
(539, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 22:07:48', '2026-04-20 22:07:48'),
(540, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-20 22:14:53', '2026-04-20 22:14:53'),
(541, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-21 00:07:52', '2026-04-21 00:07:52'),
(542, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-04-21 00:07:59', '2026-04-21 00:07:59'),
(543, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-04-22 20:56:57', '2026-04-22 20:56:57'),
(544, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-24 21:40:48', '2026-04-24 21:40:48'),
(545, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-27 02:35:54', '2026-04-27 02:35:54'),
(546, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-27 02:36:28', '2026-04-27 02:36:28'),
(547, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-30 03:05:54', '2026-04-30 03:05:54'),
(548, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-30 03:06:08', '2026-04-30 03:06:08'),
(549, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-30 03:06:36', '2026-04-30 03:06:36'),
(550, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-30 04:15:10', '2026-04-30 04:15:10'),
(551, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-04-30 04:15:31', '2026-04-30 04:15:31'),
(552, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-01 02:29:09', '2026-05-01 02:29:09'),
(553, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-01 02:29:28', '2026-05-01 02:29:28'),
(554, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-01 02:29:39', '2026-05-01 02:29:39'),
(555, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-01 02:29:48', '2026-05-01 02:29:48'),
(556, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-01 02:36:30', '2026-05-01 02:36:30'),
(557, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-01 02:38:12', '2026-05-01 02:38:12'),
(558, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-01 02:52:36', '2026-05-01 02:52:36'),
(559, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-01 03:27:30', '2026-05-01 03:27:30'),
(560, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-01 04:26:09', '2026-05-01 04:26:09'),
(561, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-01 04:29:22', '2026-05-01 04:29:22'),
(562, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-02 09:54:09', '2026-05-02 09:54:09'),
(563, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-02 23:08:34', '2026-05-02 23:08:34'),
(564, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-05-03 03:42:31', '2026-05-03 03:42:31'),
(565, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-05 03:37:06', '2026-05-05 03:37:06'),
(566, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-05 04:18:38', '2026-05-05 04:18:38'),
(567, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-05-05 04:18:46', '2026-05-05 04:18:46'),
(568, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-05 04:19:26', '2026-05-05 04:19:26'),
(569, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-05-06 00:40:24', '2026-05-06 00:40:24'),
(570, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-05-06 00:40:31', '2026-05-06 00:40:31'),
(571, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-06 00:41:03', '2026-05-06 00:41:03'),
(572, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-06 00:41:48', '2026-05-06 00:41:48'),
(573, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-06 03:01:04', '2026-05-06 03:01:04'),
(574, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-06 03:01:29', '2026-05-06 03:01:29'),
(575, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-06 03:01:41', '2026-05-06 03:01:41'),
(576, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-06 03:25:55', '2026-05-06 03:25:55'),
(577, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 03:56:04', '2026-05-06 03:56:04'),
(578, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 03:58:55', '2026-05-06 03:58:55'),
(579, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 03:59:32', '2026-05-06 03:59:32'),
(580, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 03:59:44', '2026-05-06 03:59:44'),
(581, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 04:01:00', '2026-05-06 04:01:00'),
(582, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 04:03:19', '2026-05-06 04:03:19'),
(583, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 05:53:48', '2026-05-06 05:53:48'),
(584, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 05:54:06', '2026-05-06 05:54:06'),
(585, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-06 09:08:24', '2026-05-06 09:08:24'),
(586, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-07 01:38:19', '2026-05-07 01:38:19'),
(587, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-07 01:38:29', '2026-05-07 01:38:29'),
(588, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-07 03:18:46', '2026-05-07 03:18:46'),
(589, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-07 03:19:19', '2026-05-07 03:19:19'),
(590, NULL, 'Created A New School Semester', 'Created A New Schoool Semester Information for System', '::1', '2026-05-07 07:59:07', '2026-05-07 07:59:07'),
(591, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-08 05:06:30', '2026-05-08 05:06:30'),
(592, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-08 05:06:42', '2026-05-08 05:06:42'),
(593, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-08 05:20:07', '2026-05-08 05:20:07'),
(594, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-08 05:20:19', '2026-05-08 05:20:19'),
(595, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-08 05:24:50', '2026-05-08 05:24:50'),
(596, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-08 07:52:59', '2026-05-08 07:52:59'),
(597, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-08 07:53:10', '2026-05-08 07:53:10'),
(598, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 04:53:59', '2026-05-09 04:53:59'),
(599, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 04:54:15', '2026-05-09 04:54:15'),
(600, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 04:54:23', '2026-05-09 04:54:23'),
(601, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 04:54:37', '2026-05-09 04:54:37'),
(602, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 04:55:02', '2026-05-09 04:55:02'),
(603, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 04:55:09', '2026-05-09 04:55:09'),
(604, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 05:38:42', '2026-05-09 05:38:42'),
(605, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 05:38:55', '2026-05-09 05:38:55'),
(606, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 05:38:58', '2026-05-09 05:38:58'),
(607, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 05:39:26', '2026-05-09 05:39:26'),
(608, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 06:12:43', '2026-05-09 06:12:43'),
(609, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-09 06:13:01', '2026-05-09 06:13:01'),
(610, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 06:13:18', '2026-05-09 06:13:18'),
(611, NULL, 'Created A New School Semester', 'Created A New Schoool Semester Information for System', '::1', '2026-05-09 06:14:47', '2026-05-09 06:14:47'),
(612, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-09 06:15:08', '2026-05-09 06:15:08'),
(613, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 06:15:23', '2026-05-09 06:15:23'),
(614, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 06:15:58', '2026-05-09 06:15:58'),
(615, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-09 06:16:08', '2026-05-09 06:16:08'),
(616, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-11 01:57:41', '2026-05-11 01:57:41'),
(617, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-11 04:20:57', '2026-05-11 04:20:57'),
(618, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-11 06:41:53', '2026-05-11 06:41:53'),
(619, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-11 06:41:59', '2026-05-11 06:41:59'),
(620, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-11 10:16:30', '2026-05-11 10:16:30'),
(621, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-14 21:02:54', '2026-05-14 21:02:54'),
(622, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-14 21:03:11', '2026-05-14 21:03:11'),
(623, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-14 22:45:34', '2026-05-14 22:45:34'),
(624, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-14 22:45:45', '2026-05-14 22:45:45'),
(625, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-14 22:46:23', '2026-05-14 22:46:23'),
(626, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-14 22:47:51', '2026-05-14 22:47:51'),
(627, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-14 22:47:57', '2026-05-14 22:47:57'),
(628, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-05-15 06:59:55', '2026-05-15 06:59:55'),
(629, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-05-15 07:29:55', '2026-05-15 07:29:55'),
(630, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-05-15 07:30:08', '2026-05-15 07:30:08'),
(631, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-05-15 07:30:18', '2026-05-15 07:30:18'),
(632, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-05-15 07:30:40', '2026-05-15 07:30:40'),
(633, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-05-15 07:30:52', '2026-05-15 07:30:52'),
(634, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-16 13:35:41', '2026-05-16 13:35:41'),
(635, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-16 13:36:19', '2026-05-16 13:36:19'),
(636, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-16 13:37:37', '2026-05-16 13:37:37'),
(637, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-20 21:16:25', '2026-05-20 21:16:25'),
(638, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-05-20 21:24:19', '2026-05-20 21:24:19'),
(639, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-05-20 21:27:00', '2026-05-20 21:27:00'),
(640, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-20 21:31:36', '2026-05-20 21:31:36'),
(641, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-20 21:31:45', '2026-05-20 21:31:45'),
(642, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-20 21:38:17', '2026-05-20 21:38:17'),
(643, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-22 05:34:11', '2026-05-22 05:34:11'),
(644, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-22 06:24:29', '2026-05-22 06:24:29'),
(645, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-22 07:25:17', '2026-05-22 07:25:17'),
(646, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-22 07:25:18', '2026-05-22 07:25:18'),
(647, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-22 07:30:38', '2026-05-22 07:30:38'),
(648, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-22 07:33:21', '2026-05-22 07:33:21'),
(649, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-22 07:43:52', '2026-05-22 07:43:52'),
(650, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-22 07:44:34', '2026-05-22 07:44:34'),
(651, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-22 10:48:13', '2026-05-22 10:48:13'),
(652, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-23 06:50:43', '2026-05-23 06:50:43'),
(653, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-23 07:06:36', '2026-05-23 07:06:36'),
(654, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-05-24 22:02:02', '2026-05-24 22:02:02'),
(655, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:37:03', '2026-05-26 02:37:03'),
(656, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:37:38', '2026-05-26 02:37:38'),
(657, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:37:46', '2026-05-26 02:37:46'),
(658, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:37:53', '2026-05-26 02:37:53'),
(659, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:38:00', '2026-05-26 02:38:00'),
(660, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:38:42', '2026-05-26 02:38:42'),
(661, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:38:45', '2026-05-26 02:38:45'),
(662, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:39:47', '2026-05-26 02:39:47'),
(663, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:43:27', '2026-05-26 02:43:27'),
(664, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:45:53', '2026-05-26 02:45:53'),
(665, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:57:28', '2026-05-26 02:57:28'),
(666, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:58:02', '2026-05-26 02:58:02'),
(667, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:58:57', '2026-05-26 02:58:57'),
(668, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 02:59:01', '2026-05-26 02:59:01'),
(669, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:02:47', '2026-05-26 04:02:47'),
(670, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:02:50', '2026-05-26 04:02:50'),
(671, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:03:08', '2026-05-26 04:03:08'),
(672, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:03:42', '2026-05-26 04:03:42'),
(673, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:03:52', '2026-05-26 04:03:52'),
(674, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:03:56', '2026-05-26 04:03:56'),
(675, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:04:27', '2026-05-26 04:04:27'),
(676, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:04:31', '2026-05-26 04:04:31'),
(677, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:05:04', '2026-05-26 04:05:04'),
(678, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:05:15', '2026-05-26 04:05:15'),
(679, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:06:11', '2026-05-26 04:06:11'),
(680, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:06:14', '2026-05-26 04:06:14'),
(681, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:06:20', '2026-05-26 04:06:20'),
(682, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:07:54', '2026-05-26 04:07:54'),
(683, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:08:14', '2026-05-26 04:08:14'),
(684, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:14:38', '2026-05-26 04:14:38'),
(685, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:15:05', '2026-05-26 04:15:05'),
(686, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:16:21', '2026-05-26 04:16:21'),
(687, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:17:41', '2026-05-26 04:17:41'),
(688, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:17:55', '2026-05-26 04:17:55'),
(689, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:18:43', '2026-05-26 04:18:43'),
(690, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:19:07', '2026-05-26 04:19:07'),
(691, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:19:24', '2026-05-26 04:19:24'),
(692, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:20:22', '2026-05-26 04:20:22'),
(693, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:20:25', '2026-05-26 04:20:25'),
(694, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:20:29', '2026-05-26 04:20:29'),
(695, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:20:56', '2026-05-26 04:20:56'),
(696, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:21:00', '2026-05-26 04:21:00'),
(697, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 04:21:02', '2026-05-26 04:21:02'),
(698, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:10:58', '2026-05-26 18:10:58'),
(699, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:11:11', '2026-05-26 18:11:11'),
(700, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:49:10', '2026-05-26 18:49:10'),
(701, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:49:24', '2026-05-26 18:49:24'),
(702, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:51:16', '2026-05-26 18:51:16'),
(703, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:52:01', '2026-05-26 18:52:01'),
(704, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:52:33', '2026-05-26 18:52:33'),
(705, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:52:35', '2026-05-26 18:52:35'),
(706, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:52:39', '2026-05-26 18:52:39'),
(707, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:52:45', '2026-05-26 18:52:45'),
(708, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:54:39', '2026-05-26 18:54:39'),
(709, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:54:49', '2026-05-26 18:54:49'),
(710, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:55:20', '2026-05-26 18:55:20'),
(711, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 18:55:33', '2026-05-26 18:55:33'),
(712, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:10:03', '2026-05-26 19:10:03'),
(713, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:10:12', '2026-05-26 19:10:12'),
(714, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:13:21', '2026-05-26 19:13:21'),
(715, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:13:25', '2026-05-26 19:13:25'),
(716, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:13:36', '2026-05-26 19:13:36'),
(717, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:13:44', '2026-05-26 19:13:44'),
(718, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:13:47', '2026-05-26 19:13:47'),
(719, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:13:50', '2026-05-26 19:13:50'),
(720, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:13:52', '2026-05-26 19:13:52'),
(721, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:14:11', '2026-05-26 19:14:11'),
(722, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:14:18', '2026-05-26 19:14:18'),
(723, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:14:25', '2026-05-26 19:14:25'),
(724, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:14:29', '2026-05-26 19:14:29'),
(725, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:14:39', '2026-05-26 19:14:39'),
(726, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:14:45', '2026-05-26 19:14:45'),
(727, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:14:54', '2026-05-26 19:14:54'),
(728, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:15:16', '2026-05-26 19:15:16'),
(729, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:15:23', '2026-05-26 19:15:23'),
(730, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:15:34', '2026-05-26 19:15:34'),
(731, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:16:50', '2026-05-26 19:16:50'),
(732, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:16:56', '2026-05-26 19:16:56'),
(733, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:17:03', '2026-05-26 19:17:03'),
(734, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:17:09', '2026-05-26 19:17:09'),
(735, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:18:04', '2026-05-26 19:18:04'),
(736, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:18:24', '2026-05-26 19:18:24'),
(737, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-26 19:18:33', '2026-05-26 19:18:33'),
(738, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-05-27 19:24:52', '2026-05-27 19:24:52'),
(739, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-27 19:25:08', '2026-05-27 19:25:08'),
(740, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-27 19:25:11', '2026-05-27 19:25:11'),
(741, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-27 19:25:15', '2026-05-27 19:25:15'),
(742, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-27 19:25:17', '2026-05-27 19:25:17'),
(743, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-27 19:46:07', '2026-05-27 19:46:07'),
(744, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-27 19:46:12', '2026-05-27 19:46:12'),
(745, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-27 19:46:16', '2026-05-27 19:46:16'),
(746, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-27 19:49:43', '2026-05-27 19:49:43'),
(747, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-28 03:45:22', '2026-05-28 03:45:22'),
(748, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-28 03:45:28', '2026-05-28 03:45:28'),
(749, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:51:13', '2026-05-28 18:51:13'),
(750, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:51:19', '2026-05-28 18:51:19'),
(751, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:55:55', '2026-05-28 18:55:55'),
(752, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:56:08', '2026-05-28 18:56:08'),
(753, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:56:11', '2026-05-28 18:56:11'),
(754, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:56:21', '2026-05-28 18:56:21'),
(755, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:56:29', '2026-05-28 18:56:29'),
(756, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:56:59', '2026-05-28 18:56:59'),
(757, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-28 18:57:02', '2026-05-28 18:57:02'),
(758, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 18:59:39', '2026-05-28 18:59:39'),
(759, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 18:59:47', '2026-05-28 18:59:47'),
(760, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:00:22', '2026-05-28 19:00:22'),
(761, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:00:30', '2026-05-28 19:00:30'),
(762, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:01:31', '2026-05-28 19:01:31'),
(763, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:03:24', '2026-05-28 19:03:24'),
(764, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:03:31', '2026-05-28 19:03:31'),
(765, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:06:38', '2026-05-28 19:06:38'),
(766, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:13:24', '2026-05-28 19:13:24'),
(767, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:13:29', '2026-05-28 19:13:29'),
(768, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:14:33', '2026-05-28 19:14:33'),
(769, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:14:41', '2026-05-28 19:14:41'),
(770, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-28 19:16:53', '2026-05-28 19:16:53'),
(771, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-28 19:17:35', '2026-05-28 19:17:35'),
(772, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:34:50', '2026-05-28 19:34:50'),
(773, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 19:35:10', '2026-05-28 19:35:10'),
(774, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-28 19:36:13', '2026-05-28 19:36:13'),
(775, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-28 19:37:47', '2026-05-28 19:37:47'),
(776, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-05-28 19:43:51', '2026-05-28 19:43:51'),
(777, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 20:00:09', '2026-05-28 20:00:09'),
(778, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 20:00:18', '2026-05-28 20:00:18'),
(779, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 20:29:51', '2026-05-28 20:29:51'),
(780, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 20:29:59', '2026-05-28 20:29:59'),
(781, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-05-28 20:30:40', '2026-05-28 20:30:40'),
(782, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 20:31:08', '2026-05-28 20:31:08'),
(783, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 20:33:39', '2026-05-28 20:33:39'),
(784, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-05-28 20:33:50', '2026-05-28 20:33:50'),
(785, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 20:33:58', '2026-05-28 20:33:58'),
(786, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 20:54:37', '2026-05-28 20:54:37'),
(787, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 20:55:25', '2026-05-28 20:55:25'),
(788, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 20:56:37', '2026-05-28 20:56:37'),
(789, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 20:58:51', '2026-05-28 20:58:51'),
(790, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 20:59:04', '2026-05-28 20:59:04'),
(791, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:00:14', '2026-05-28 21:00:14'),
(792, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:00:37', '2026-05-28 21:00:37'),
(793, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:01:53', '2026-05-28 21:01:53'),
(794, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:04:44', '2026-05-28 21:04:44'),
(795, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:05:35', '2026-05-28 21:05:35'),
(796, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:10:26', '2026-05-28 21:10:26'),
(797, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:13:10', '2026-05-28 21:13:10'),
(798, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-05-28 21:16:01', '2026-05-28 21:16:01'),
(799, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:17:31', '2026-05-28 21:17:31'),
(800, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-28 21:17:48', '2026-05-28 21:17:48'),
(801, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-29 05:14:29', '2026-05-29 05:14:29'),
(802, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-29 05:15:51', '2026-05-29 05:15:51'),
(803, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-29 05:56:25', '2026-05-29 05:56:25'),
(804, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-29 08:59:58', '2026-05-29 08:59:58'),
(805, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-29 09:00:09', '2026-05-29 09:00:09'),
(806, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-30 16:52:53', '2026-05-30 16:52:53'),
(807, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-30 16:53:57', '2026-05-30 16:53:57'),
(808, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-30 16:54:23', '2026-05-30 16:54:23'),
(809, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-30 16:55:25', '2026-05-30 16:55:25'),
(810, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-30 17:34:22', '2026-05-30 17:34:22'),
(811, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-05-30 17:48:40', '2026-05-30 17:48:40'),
(812, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:45:38', '2026-05-31 17:45:38'),
(813, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:46:22', '2026-05-31 17:46:22'),
(814, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:47:34', '2026-05-31 17:47:34'),
(815, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:48:47', '2026-05-31 17:48:47'),
(816, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:48:58', '2026-05-31 17:48:58'),
(817, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:50:05', '2026-05-31 17:50:05'),
(818, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:51:08', '2026-05-31 17:51:08'),
(819, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:51:30', '2026-05-31 17:51:30'),
(820, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:52:44', '2026-05-31 17:52:44'),
(821, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 17:54:47', '2026-05-31 17:54:47'),
(822, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 18:13:20', '2026-05-31 18:13:20'),
(823, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 18:14:33', '2026-05-31 18:14:33'),
(824, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 18:15:31', '2026-05-31 18:15:31'),
(825, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 18:15:47', '2026-05-31 18:15:47'),
(826, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 18:16:04', '2026-05-31 18:16:04'),
(827, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 18:16:23', '2026-05-31 18:16:23'),
(828, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:01:56', '2026-05-31 19:01:56'),
(829, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:10:36', '2026-05-31 19:10:36'),
(830, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:10:46', '2026-05-31 19:10:46'),
(831, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:10:48', '2026-05-31 19:10:48'),
(832, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:10:49', '2026-05-31 19:10:49'),
(833, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:10:55', '2026-05-31 19:10:55'),
(834, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:11:46', '2026-05-31 19:11:46'),
(835, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:12:24', '2026-05-31 19:12:24'),
(836, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:12:42', '2026-05-31 19:12:42'),
(837, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:16:03', '2026-05-31 19:16:03'),
(838, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:17:41', '2026-05-31 19:17:41'),
(839, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-31 19:18:27', '2026-05-31 19:18:27'),
(840, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:18:51', '2026-05-31 19:18:51'),
(841, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 19:20:33', '2026-05-31 19:20:33'),
(842, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:20:38', '2026-05-31 19:20:38'),
(843, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:21:48', '2026-05-31 19:21:48'),
(844, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-31 19:23:13', '2026-05-31 19:23:13'),
(845, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-05-31 19:25:23', '2026-05-31 19:25:23'),
(846, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:25:30', '2026-05-31 19:25:30'),
(847, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-31 19:26:05', '2026-05-31 19:26:05');
INSERT INTO `rgr_activity_log` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`, `updated_at`) VALUES
(848, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-31 19:28:39', '2026-05-31 19:28:39'),
(849, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-31 19:28:47', '2026-05-31 19:28:47'),
(850, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:31:13', '2026-05-31 19:31:13'),
(851, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-05-31 19:31:34', '2026-05-31 19:31:34'),
(852, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-05-31 19:32:28', '2026-05-31 19:32:28'),
(853, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-01 19:36:09', '2026-06-01 19:36:09'),
(854, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-01 19:36:50', '2026-06-01 19:36:50'),
(855, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-01 19:42:40', '2026-06-01 19:42:40'),
(856, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-01 19:42:52', '2026-06-01 19:42:52'),
(857, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-01 19:43:42', '2026-06-01 19:43:42'),
(858, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-01 19:44:12', '2026-06-01 19:44:12'),
(859, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-01 19:46:24', '2026-06-01 19:46:24'),
(860, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-01 19:48:10', '2026-06-01 19:48:10'),
(861, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-01 19:49:19', '2026-06-01 19:49:19'),
(862, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-01 19:49:56', '2026-06-01 19:49:56'),
(863, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-03 06:08:11', '2026-06-03 06:08:11'),
(864, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-03 20:05:07', '2026-06-03 20:05:07'),
(865, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-03 20:05:10', '2026-06-03 20:05:10'),
(866, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-03 20:05:29', '2026-06-03 20:05:29'),
(867, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-03 20:05:41', '2026-06-03 20:05:41'),
(868, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-03 21:48:02', '2026-06-03 21:48:02'),
(869, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-03 21:48:09', '2026-06-03 21:48:09'),
(870, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-04 18:30:43', '2026-06-04 18:30:43'),
(871, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-04 18:30:50', '2026-06-04 18:30:50'),
(872, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-04 18:45:57', '2026-06-04 18:45:57'),
(873, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-04 18:46:31', '2026-06-04 18:46:31'),
(874, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-04 18:51:54', '2026-06-04 18:51:54'),
(875, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-04 18:52:42', '2026-06-04 18:52:42'),
(876, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-04 19:20:13', '2026-06-04 19:20:13'),
(877, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-05 07:39:04', '2026-06-05 07:39:04'),
(878, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-05 07:40:08', '2026-06-05 07:40:08'),
(879, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-05 07:40:21', '2026-06-05 07:40:21'),
(880, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-05 07:40:53', '2026-06-05 07:40:53'),
(881, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-05 07:41:09', '2026-06-05 07:41:09'),
(882, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-05 07:42:19', '2026-06-05 07:42:19'),
(883, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-05 07:42:28', '2026-06-05 07:42:28'),
(884, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-05 07:42:34', '2026-06-05 07:42:34'),
(885, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-06-05 07:43:58', '2026-06-05 07:43:58'),
(886, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-06-05 07:45:31', '2026-06-05 07:45:31'),
(887, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-05 07:45:46', '2026-06-05 07:45:46'),
(888, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-05 07:46:10', '2026-06-05 07:46:10'),
(889, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-05 07:46:59', '2026-06-05 07:46:59'),
(890, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-06-05 07:47:31', '2026-06-05 07:47:31'),
(891, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-06-05 07:47:52', '2026-06-05 07:47:52'),
(892, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-06-05 07:47:54', '2026-06-05 07:47:54'),
(893, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-06-05 07:48:05', '2026-06-05 07:48:05'),
(894, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-05 07:48:32', '2026-06-05 07:48:32'),
(895, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-05 07:48:51', '2026-06-05 07:48:51'),
(896, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-05 07:49:09', '2026-06-05 07:49:09'),
(897, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-05 07:49:20', '2026-06-05 07:49:20'),
(898, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-05 07:49:55', '2026-06-05 07:49:55'),
(899, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-05 07:50:03', '2026-06-05 07:50:03'),
(900, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-05 21:02:40', '2026-06-05 21:02:40'),
(901, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-06-06 00:40:11', '2026-06-06 00:40:11'),
(902, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 00:40:27', '2026-06-06 00:40:27'),
(903, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 01:12:35', '2026-06-06 01:12:35'),
(904, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 01:12:47', '2026-06-06 01:12:47'),
(905, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 01:59:45', '2026-06-06 01:59:45'),
(906, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 01:59:55', '2026-06-06 01:59:55'),
(907, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:00:31', '2026-06-06 02:00:31'),
(908, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:00:54', '2026-06-06 02:00:54'),
(909, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:01:00', '2026-06-06 02:01:00'),
(910, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:01:12', '2026-06-06 02:01:12'),
(911, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:01:43', '2026-06-06 02:01:43'),
(912, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:02:28', '2026-06-06 02:02:28'),
(913, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:02:43', '2026-06-06 02:02:43'),
(914, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:02:50', '2026-06-06 02:02:50'),
(915, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:07:37', '2026-06-06 02:07:37'),
(916, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:07:56', '2026-06-06 02:07:56'),
(917, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:17:46', '2026-06-06 02:17:46'),
(918, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 02:17:59', '2026-06-06 02:17:59'),
(919, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:06:21', '2026-06-06 04:06:21'),
(920, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:06:29', '2026-06-06 04:06:29'),
(921, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-06-06 04:10:42', '2026-06-06 04:10:42'),
(922, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-06 04:11:09', '2026-06-06 04:11:09'),
(923, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:14:15', '2026-06-06 04:14:15'),
(924, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:17:13', '2026-06-06 04:17:13'),
(925, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:52:06', '2026-06-06 04:52:06'),
(926, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:53:29', '2026-06-06 04:53:29'),
(927, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:53:39', '2026-06-06 04:53:39'),
(928, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:54:13', '2026-06-06 04:54:13'),
(929, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:54:47', '2026-06-06 04:54:47'),
(930, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:54:54', '2026-06-06 04:54:54'),
(931, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:58:16', '2026-06-06 04:58:16'),
(932, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:58:25', '2026-06-06 04:58:25'),
(933, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:59:11', '2026-06-06 04:59:11'),
(934, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:59:14', '2026-06-06 04:59:14'),
(935, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 04:59:18', '2026-06-06 04:59:18'),
(936, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:02:08', '2026-06-06 05:02:08'),
(937, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:02:11', '2026-06-06 05:02:11'),
(938, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:02:19', '2026-06-06 05:02:19'),
(939, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:02:23', '2026-06-06 05:02:23'),
(940, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:02:29', '2026-06-06 05:02:29'),
(941, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:03:06', '2026-06-06 05:03:06'),
(942, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:03:15', '2026-06-06 05:03:15'),
(943, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:06:00', '2026-06-06 05:06:00'),
(944, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:07:48', '2026-06-06 05:07:48'),
(945, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:07:52', '2026-06-06 05:07:52'),
(946, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:08:07', '2026-06-06 05:08:07'),
(947, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:08:50', '2026-06-06 05:08:50'),
(948, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:08:53', '2026-06-06 05:08:53'),
(949, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:08:57', '2026-06-06 05:08:57'),
(950, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:09:03', '2026-06-06 05:09:03'),
(951, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:13:33', '2026-06-06 05:13:33'),
(952, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:13:45', '2026-06-06 05:13:45'),
(953, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:14:03', '2026-06-06 05:14:03'),
(954, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:14:06', '2026-06-06 05:14:06'),
(955, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:15:05', '2026-06-06 05:15:05'),
(956, NULL, 'Created A New School Year', 'Created A New Schoool Year Information for System', '::1', '2026-06-06 05:16:33', '2026-06-06 05:16:33'),
(957, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:16:45', '2026-06-06 05:16:45'),
(958, NULL, 'Deleted A New School Year', 'Deleted A New Schoool Year Information for System', '::1', '2026-06-06 05:16:51', '2026-06-06 05:16:51'),
(959, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:17:07', '2026-06-06 05:17:07'),
(960, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 05:17:12', '2026-06-06 05:17:12'),
(961, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 07:03:08', '2026-06-06 07:03:08'),
(962, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 07:03:15', '2026-06-06 07:03:15'),
(963, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 07:03:29', '2026-06-06 07:03:29'),
(964, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 07:03:33', '2026-06-06 07:03:33'),
(965, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 07:03:41', '2026-06-06 07:03:41'),
(966, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 07:03:44', '2026-06-06 07:03:44'),
(967, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 07:58:43', '2026-06-06 07:58:43'),
(968, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 07:58:52', '2026-06-06 07:58:52'),
(969, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 08:00:19', '2026-06-06 08:00:19'),
(970, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-06 08:00:24', '2026-06-06 08:00:24'),
(971, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-07 19:08:37', '2026-06-07 19:08:37'),
(972, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-07 20:10:10', '2026-06-07 20:10:10'),
(973, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-08 08:59:08', '2026-06-08 08:59:08'),
(974, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-08 09:56:42', '2026-06-08 09:56:42'),
(975, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-06-09 22:57:43', '2026-06-09 22:57:43'),
(976, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-09 22:59:07', '2026-06-09 22:59:07'),
(977, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-06-09 22:59:54', '2026-06-09 22:59:54'),
(978, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-09 23:00:01', '2026-06-09 23:00:01'),
(979, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-06-10 23:31:41', '2026-06-10 23:31:41'),
(980, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-10 23:38:16', '2026-06-10 23:38:16'),
(981, NULL, 'Created A New Curriculum ', 'Created A New Curriculum for System', '::1', '2026-06-10 23:40:34', '2026-06-10 23:40:34'),
(982, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-10 23:42:26', '2026-06-10 23:42:26'),
(983, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-10 23:42:30', '2026-06-10 23:42:30'),
(984, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-10 23:44:28', '2026-06-10 23:44:28'),
(985, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-06-10 23:46:47', '2026-06-10 23:46:47'),
(986, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-18 22:50:10', '2026-06-18 22:50:10'),
(987, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-06-23 01:42:20', '2026-06-23 01:42:20'),
(988, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-06-25 08:59:10', '2026-06-25 08:59:10'),
(989, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-07-01 15:11:40', '2026-07-01 15:11:40'),
(990, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-07-02 11:29:46', '2026-07-02 11:29:46'),
(991, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-07-13 16:47:48', '2026-07-13 16:47:48'),
(992, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-07-13 17:01:01', '2026-07-13 17:01:01'),
(993, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-07-13 17:02:49', '2026-07-13 17:02:49'),
(994, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:05:42', '2026-07-13 17:05:42'),
(995, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:05:47', '2026-07-13 17:05:47'),
(996, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:06:06', '2026-07-13 17:06:06'),
(997, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:06:23', '2026-07-13 17:06:23'),
(998, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:06:25', '2026-07-13 17:06:25'),
(999, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:06:37', '2026-07-13 17:06:37'),
(1000, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:07:46', '2026-07-13 17:07:46'),
(1001, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:07:48', '2026-07-13 17:07:48'),
(1002, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:01', '2026-07-13 17:09:01'),
(1003, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:02', '2026-07-13 17:09:02'),
(1004, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:03', '2026-07-13 17:09:03'),
(1005, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:04', '2026-07-13 17:09:04'),
(1006, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:21', '2026-07-13 17:09:21'),
(1007, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:22', '2026-07-13 17:09:22'),
(1008, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:23', '2026-07-13 17:09:23'),
(1009, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:28', '2026-07-13 17:09:28'),
(1010, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:09:39', '2026-07-13 17:09:39'),
(1011, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:10:42', '2026-07-13 17:10:42'),
(1012, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:10:53', '2026-07-13 17:10:53'),
(1013, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:15:51', '2026-07-13 17:15:51'),
(1014, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:16:25', '2026-07-13 17:16:25'),
(1015, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:18:17', '2026-07-13 17:18:17'),
(1016, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:18:38', '2026-07-13 17:18:38'),
(1017, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:19:42', '2026-07-13 17:19:42'),
(1018, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:20:58', '2026-07-13 17:20:58'),
(1019, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:22:25', '2026-07-13 17:22:25'),
(1020, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:22:50', '2026-07-13 17:22:50'),
(1021, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:23:28', '2026-07-13 17:23:28'),
(1022, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:24:24', '2026-07-13 17:24:24'),
(1023, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:25:33', '2026-07-13 17:25:33'),
(1024, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:26:58', '2026-07-13 17:26:58'),
(1025, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:27:40', '2026-07-13 17:27:40'),
(1026, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:28:45', '2026-07-13 17:28:45'),
(1027, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:37:09', '2026-07-13 17:37:09'),
(1028, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-13 17:37:24', '2026-07-13 17:37:24'),
(1029, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-14 11:28:05', '2026-07-14 11:28:05'),
(1030, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-07-14 12:47:44', '2026-07-14 12:47:44'),
(1031, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-14 13:06:01', '2026-07-14 13:06:01'),
(1032, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-14 13:06:12', '2026-07-14 13:06:12'),
(1033, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-07-14 13:23:42', '2026-07-14 13:23:42'),
(1034, NULL, 'Created A New Subject', 'Created A New Subject Information for System', '::1', '2026-07-14 13:40:41', '2026-07-14 13:40:41'),
(1035, NULL, 'Delete Subject Input', 'Deleting 1 item/s Subject Information', '::1', '2026-07-14 13:40:53', '2026-07-14 13:40:53'),
(1036, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-14 13:48:25', '2026-07-14 13:48:25'),
(1037, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-14 13:53:30', '2026-07-14 13:53:30'),
(1038, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-14 19:27:02', '2026-07-14 19:27:02'),
(1039, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-15 02:29:55', '2026-07-15 02:29:55'),
(1040, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-15 02:30:00', '2026-07-15 02:30:00'),
(1041, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-07-15 02:43:30', '2026-07-15 02:43:30'),
(1042, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-15 02:46:28', '2026-07-15 02:46:28'),
(1043, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-15 02:46:35', '2026-07-15 02:46:35'),
(1044, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-16 03:43:37', '2026-07-16 03:43:37'),
(1045, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-16 03:43:45', '2026-07-16 03:43:45'),
(1046, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-16 03:44:15', '2026-07-16 03:44:15'),
(1047, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-16 03:46:18', '2026-07-16 03:46:18'),
(1048, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-16 04:31:22', '2026-07-16 04:31:22'),
(1049, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-20 04:52:48', '2026-07-20 04:52:48'),
(1050, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-27 13:22:55', '2026-07-27 13:22:55'),
(1051, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-07-27 13:39:27', '2026-07-27 13:39:27'),
(1052, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-07-27 13:39:52', '2026-07-27 13:39:52'),
(1053, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-07-27 13:47:53', '2026-07-27 13:47:53'),
(1054, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-07-27 13:48:12', '2026-07-27 13:48:12'),
(1055, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-27 14:02:24', '2026-07-27 14:02:24'),
(1056, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-07-27 14:03:23', '2026-07-27 14:03:23'),
(1057, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-07-27 14:06:06', '2026-07-27 14:06:06'),
(1058, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-27 14:06:20', '2026-07-27 14:06:20'),
(1059, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-07-27 14:09:08', '2026-07-27 14:09:08'),
(1060, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-07-27 14:09:14', '2026-07-27 14:09:14'),
(1061, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-27 14:09:23', '2026-07-27 14:09:23'),
(1062, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-07-27 14:12:37', '2026-07-27 14:12:37'),
(1063, NULL, 'Deleted A Section Record', 'Deleted A Section Record Information for System', '::1', '2026-07-27 14:12:46', '2026-07-27 14:12:46'),
(1064, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-07-27 14:13:02', '2026-07-27 14:13:02'),
(1065, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-28 08:40:37', '2026-07-28 08:40:37'),
(1066, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-07-28 09:02:30', '2026-07-28 09:02:30'),
(1067, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:39:40', '2026-07-29 12:39:40'),
(1068, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:40:24', '2026-07-29 12:40:24'),
(1069, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:41:07', '2026-07-29 12:41:07'),
(1070, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:41:09', '2026-07-29 12:41:09'),
(1071, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-07-29 12:41:15', '2026-07-29 12:41:15'),
(1072, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:41:49', '2026-07-29 12:41:49'),
(1073, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:42:43', '2026-07-29 12:42:43'),
(1074, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:42:55', '2026-07-29 12:42:55'),
(1075, NULL, 'Delete Course Input', 'Deleting 1 item/s Course Information', '::1', '2026-07-29 12:43:12', '2026-07-29 12:43:12'),
(1076, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:43:51', '2026-07-29 12:43:51'),
(1077, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:45:21', '2026-07-29 12:45:21'),
(1078, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:45:52', '2026-07-29 12:45:52'),
(1079, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-07-29 12:47:01', '2026-07-29 12:47:01'),
(1080, NULL, 'Get A PDF Course student Report', 'Downloading a PDF file contains Course Information', '::1', '2026-07-29 13:06:40', '2026-07-29 13:06:40'),
(1081, NULL, 'Created A Section', 'Created A New Section Information for System', '::1', '2026-07-31 09:43:23', '2026-07-31 09:43:23'),
(1082, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-02 02:21:43', '2026-08-02 02:21:43'),
(1083, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-02 02:21:48', '2026-08-02 02:21:48'),
(1084, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-02 13:07:24', '2026-08-02 13:07:24'),
(1085, NULL, 'Created A New Course', 'Created A New Course Information for System', '::1', '2026-08-04 15:16:37', '2026-08-04 15:16:37'),
(1086, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-08-06 01:04:39', '2026-08-06 01:04:39'),
(1087, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-08-06 01:04:49', '2026-08-06 01:04:49'),
(1088, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-06 02:08:36', '2026-08-06 02:08:36'),
(1089, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-06 02:08:53', '2026-08-06 02:08:53'),
(1090, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-06 02:09:53', '2026-08-06 02:09:53'),
(1091, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-06 02:10:04', '2026-08-06 02:10:04'),
(1092, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-08-08 15:18:54', '2026-08-08 15:18:54'),
(1093, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-08-08 15:20:01', '2026-08-08 15:20:01'),
(1094, NULL, 'Get A Excel all student Report', 'Downloading a Excel file contains all students information', '::1', '2026-08-08 15:20:16', '2026-08-08 15:20:16'),
(1095, NULL, 'Get A PDF for Enrollee', 'Downloading a PDF file contains Enrollee Information', '::1', '2026-08-09 00:56:34', '2026-08-09 00:56:34'),
(1096, NULL, 'Get A Excel all student Report', 'Downloading a Excel file contains all students information', '::1', '2026-08-11 18:15:12', '2026-08-11 18:15:12'),
(1097, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-11 18:38:31', '2026-08-11 18:38:31'),
(1098, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-11 18:46:00', '2026-08-11 18:46:00'),
(1099, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-11 18:48:45', '2026-08-11 18:48:45'),
(1100, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-11 18:52:20', '2026-08-11 18:52:20'),
(1101, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-11 19:37:10', '2026-08-11 19:37:10'),
(1102, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-11 19:41:11', '2026-08-11 19:41:11'),
(1103, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-12 02:03:39', '2026-08-12 02:03:39'),
(1104, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-12 02:48:06', '2026-08-12 02:48:06'),
(1105, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-12 02:55:07', '2026-08-12 02:55:07'),
(1106, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:23:27', '2026-08-15 09:23:27'),
(1107, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:23:48', '2026-08-15 09:23:48'),
(1108, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:28:07', '2026-08-15 09:28:07'),
(1109, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:29:17', '2026-08-15 09:29:17'),
(1110, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:30:31', '2026-08-15 09:30:31'),
(1111, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:30:58', '2026-08-15 09:30:58'),
(1112, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:32:46', '2026-08-15 09:32:46'),
(1113, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:33:18', '2026-08-15 09:33:18'),
(1114, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:44:21', '2026-08-15 09:44:21'),
(1115, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:44:39', '2026-08-15 09:44:39'),
(1116, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:44:41', '2026-08-15 09:44:41'),
(1117, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:44:42', '2026-08-15 09:44:42'),
(1118, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:44:42', '2026-08-15 09:44:42'),
(1119, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:44:43', '2026-08-15 09:44:43'),
(1120, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:44:56', '2026-08-15 09:44:56'),
(1121, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:45:43', '2026-08-15 09:45:43'),
(1122, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-15 09:46:01', '2026-08-15 09:46:01'),
(1123, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-08-16 05:08:33', '2026-08-16 05:08:33'),
(1124, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-17 03:36:57', '2026-08-17 03:36:57'),
(1125, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-17 03:37:03', '2026-08-17 03:37:03'),
(1126, NULL, 'Get A PDF 4 student Report', 'Downloading a PDF file contains 4 students information', '::1', '2026-08-18 22:54:36', '2026-08-18 22:54:36'),
(1127, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-08-20 01:34:32', '2026-08-20 01:34:32'),
(1128, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-20 03:43:08', '2026-08-20 03:43:08'),
(1129, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-20 03:43:42', '2026-08-20 03:43:42'),
(1130, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-20 04:05:26', '2026-08-20 04:05:26'),
(1131, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-20 04:07:35', '2026-08-20 04:07:35'),
(1132, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-20 04:09:23', '2026-08-20 04:09:23'),
(1133, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-20 04:14:54', '2026-08-20 04:14:54'),
(1134, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-20 04:16:02', '2026-08-20 04:16:02'),
(1135, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-20 04:16:34', '2026-08-20 04:16:34'),
(1136, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-20 04:17:31', '2026-08-20 04:17:31'),
(1137, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-20 04:17:41', '2026-08-20 04:17:41'),
(1138, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-20 04:22:19', '2026-08-20 04:22:19'),
(1139, NULL, 'Updated A New School Year', 'Updated A New Schoool Year Information for System', '::1', '2026-08-20 04:24:52', '2026-08-20 04:24:52'),
(1140, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-20 06:00:39', '2026-08-20 06:00:39'),
(1141, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-24 07:11:04', '2026-08-24 07:11:04'),
(1142, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-08-28 05:41:03', '2026-08-28 05:41:03'),
(1143, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-08-28 05:41:06', '2026-08-28 05:41:06'),
(1144, NULL, 'Created A New Curriculum Subject ', 'Created A New Curriculum Subject for System', '::1', '2026-08-28 05:42:02', '2026-08-28 05:42:02'),
(1145, NULL, 'Updated A ', 'Updated A New Schoool Year Information for System', '::1', '2026-08-28 06:09:38', '2026-08-28 06:09:38'),
(1146, NULL, 'Updated A ', 'Updated A New Schoool Year Information for System', '::1', '2026-08-28 06:10:48', '2026-08-28 06:10:48'),
(1147, NULL, 'Updated A ', 'Updated A New Schoool Year Information for System', '::1', '2026-08-28 06:11:05', '2026-08-28 06:11:05'),
(1148, NULL, 'Updated A ', 'Updated A New Schoool Year Information for System', '::1', '2026-08-28 07:51:55', '2026-08-28 07:51:55'),
(1149, NULL, 'Updated A ', 'Updated A New Schoool Year Information for System', '::1', '2026-08-28 07:52:19', '2026-08-28 07:52:19'),
(1150, NULL, 'Updated A ', 'Updated A New Schoool Year Information for System', '::1', '2026-08-28 07:53:44', '2026-08-28 07:53:44'),
(1151, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-28 08:05:52', '2026-08-28 08:05:52'),
(1152, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-28 08:06:28', '2026-08-28 08:06:28'),
(1153, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 08:06:46', '2026-08-28 08:06:46'),
(1154, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-28 08:06:53', '2026-08-28 08:06:53'),
(1155, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-28 08:07:02', '2026-08-28 08:07:02'),
(1156, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-28 08:11:21', '2026-08-28 08:11:21'),
(1157, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-28 08:23:32', '2026-08-28 08:23:32'),
(1158, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 08:24:07', '2026-08-28 08:24:07'),
(1159, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-28 08:24:09', '2026-08-28 08:24:09'),
(1160, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-28 08:24:16', '2026-08-28 08:24:16'),
(1161, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-28 08:24:22', '2026-08-28 08:24:22'),
(1162, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 17:37:50', '2026-08-28 17:37:50'),
(1163, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-28 17:37:53', '2026-08-28 17:37:53'),
(1164, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-28 17:37:57', '2026-08-28 17:37:57'),
(1165, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-28 17:38:00', '2026-08-28 17:38:00'),
(1166, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 17:51:37', '2026-08-28 17:51:37'),
(1167, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 17:56:17', '2026-08-28 17:56:17'),
(1168, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 17:56:59', '2026-08-28 17:56:59'),
(1169, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 18:09:57', '2026-08-28 18:09:57'),
(1170, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-28 18:20:05', '2026-08-28 18:20:05'),
(1171, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-28 18:21:16', '2026-08-28 18:21:16'),
(1172, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 18:21:51', '2026-08-28 18:21:51'),
(1173, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-28 18:24:58', '2026-08-28 18:24:58'),
(1174, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-28 19:56:13', '2026-08-28 19:56:13'),
(1175, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 20:28:54', '2026-08-28 20:28:54'),
(1176, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 20:30:03', '2026-08-28 20:30:03'),
(1177, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 20:31:12', '2026-08-28 20:31:12'),
(1178, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 20:37:02', '2026-08-28 20:37:02'),
(1179, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 20:40:36', '2026-08-28 20:40:36'),
(1180, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 20:41:41', '2026-08-28 20:41:41'),
(1181, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-28 20:44:32', '2026-08-28 20:44:32'),
(1182, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-08-29 19:28:25', '2026-08-29 19:28:25'),
(1183, NULL, 'Get A PDF 3 student Report', 'Downloading a PDF file contains 3 students information', '::1', '2026-08-30 05:29:41', '2026-08-30 05:29:41'),
(1184, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 09:09:29', '2026-08-30 09:09:29'),
(1185, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-30 09:10:22', '2026-08-30 09:10:22'),
(1186, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-30 09:11:08', '2026-08-30 09:11:08'),
(1187, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-30 09:11:32', '2026-08-30 09:11:32');
INSERT INTO `rgr_activity_log` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`, `updated_at`) VALUES
(1188, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 09:13:05', '2026-08-30 09:13:05'),
(1189, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 20:18:25', '2026-08-30 20:18:25'),
(1190, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 20:25:31', '2026-08-30 20:25:31'),
(1191, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-30 20:25:57', '2026-08-30 20:25:57'),
(1192, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-30 20:26:06', '2026-08-30 20:26:06'),
(1193, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-30 20:26:22', '2026-08-30 20:26:22'),
(1194, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-30 20:26:31', '2026-08-30 20:26:31'),
(1195, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-30 20:26:33', '2026-08-30 20:26:33'),
(1196, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 20:30:53', '2026-08-30 20:30:53'),
(1197, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-30 20:31:40', '2026-08-30 20:31:40'),
(1198, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-30 20:32:01', '2026-08-30 20:32:01'),
(1199, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-30 20:32:12', '2026-08-30 20:32:12'),
(1200, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 20:39:16', '2026-08-30 20:39:16'),
(1201, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-30 21:16:09', '2026-08-30 21:16:09'),
(1202, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-30 21:34:41', '2026-08-30 21:34:41'),
(1203, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-30 21:34:56', '2026-08-30 21:34:56'),
(1204, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 21:38:08', '2026-08-30 21:38:08'),
(1205, NULL, 'Updated Document Request ', 'Processing a student document request', '::1', '2026-08-30 21:38:18', '2026-08-30 21:38:18'),
(1206, NULL, 'Updated Document Request ', 'Mark Ready a student document request', '::1', '2026-08-30 21:39:36', '2026-08-30 21:39:36'),
(1207, NULL, 'Updated Document Request ', 'Released a student document request', '::1', '2026-08-30 21:48:02', '2026-08-30 21:48:02'),
(1208, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 22:12:49', '2026-08-30 22:12:49'),
(1209, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-08-30 22:17:23', '2026-08-30 22:17:23'),
(1210, NULL, 'Created A New Report Approval', 'Created A New Report Approval for System', '::1', '2026-08-31 17:48:07', '2026-08-31 17:48:07'),
(1211, NULL, 'Created A New Report Submission', 'Created A New Report Submission for System', '::1', '2026-08-31 18:25:43', '2026-08-31 18:25:43'),
(1212, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-08-31 18:34:59', '2026-08-31 18:34:59'),
(1213, NULL, 'Get A PDF on_leave student Report', 'Downloading a PDF file contains on_leave students information', '::1', '2026-09-01 06:27:40', '2026-09-01 06:27:40'),
(1214, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-09-01 08:11:40', '2026-09-01 08:11:40'),
(1215, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-09-03 18:17:20', '2026-09-03 18:17:20'),
(1216, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-09-04 06:53:00', '2026-09-04 06:53:00'),
(1217, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-09-04 18:48:08', '2026-09-04 18:48:08'),
(1218, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-09-06 21:46:31', '2026-09-06 21:46:31'),
(1219, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-09-07 14:17:21', '2026-09-07 14:17:21'),
(1220, NULL, 'Updated A Status Curriculum', 'Updated Status of Curriculum for System', '::1', '2026-09-07 14:17:43', '2026-09-07 14:17:43'),
(1221, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-09-07 18:01:14', '2026-09-07 18:01:14'),
(1222, NULL, 'Updated Document Request ', 'Verified a student document request', '::1', '2026-09-07 18:20:52', '2026-09-07 18:20:52'),
(1223, NULL, 'Get A PDF of School Year Report', 'Downloading a PDF file contains School Year Information', '::1', '2026-09-08 04:42:41', '2026-09-08 04:42:41'),
(1224, NULL, 'Get A PDF all student Report', 'Downloading a PDF file contains all students information', '::1', '2026-09-08 12:34:33', '2026-09-08 12:34:33'),
(1225, NULL, 'Get A PDF of Semester Report', 'Downloading a PDF file contains Semester Information', '::1', '2026-09-18 06:50:00', '2026-09-18 06:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_class_offerings`
--

CREATE TABLE `rgr_class_offerings` (
  `id` int(10) NOT NULL,
  `subject_id` int(10) NOT NULL,
  `semester_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `room_id` int(10) NOT NULL,
  `strand_id` int(10) DEFAULT NULL,
  `course_id` int(10) DEFAULT NULL,
  `section_id` int(10) NOT NULL,
  `year_level` int(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_class_offerings`
--

INSERT INTO `rgr_class_offerings` (`id`, `subject_id`, `semester_id`, `teacher_id`, `room_id`, `strand_id`, `course_id`, `section_id`, `year_level`) VALUES
(152, 13, 12, 7, 6, NULL, 38, 27, 1),
(153, 14, 12, 7, 7, NULL, 38, 27, 1),
(154, 13, 13, 7, 7, NULL, 38, 25, 1),
(155, 17, 12, 8, 7, NULL, 34, 23, 1),
(156, 17, 13, 11, 6, NULL, 38, 25, 1),
(157, 24, 13, 9, 7, NULL, 41, 24, 1),
(158, 13, 13, 7, 6, NULL, 38, 28, 1);

-- --------------------------------------------------------

--
-- Table structure for table `rgr_class_schedules`
--

CREATE TABLE `rgr_class_schedules` (
  `id` int(10) NOT NULL,
  `class_offering_id` int(10) NOT NULL,
  `day` varchar(250) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_class_schedules`
--

INSERT INTO `rgr_class_schedules` (`id`, `class_offering_id`, `day`, `start_time`, `end_time`) VALUES
(90, 152, 'Monday', '08:00:00', '09:00:00'),
(91, 153, 'Wednesday', '08:00:00', '10:00:00'),
(92, 154, 'Monday', '09:00:00', '10:00:00'),
(93, 155, 'Tuesday', '09:00:00', '10:00:00'),
(94, 156, 'Tuesday', '09:00:00', '10:00:00'),
(95, 157, 'Thursday', '08:00:00', '09:00:00'),
(96, 158, 'Tuesday', '08:00:00', '09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_courses`
--

CREATE TABLE `rgr_courses` (
  `id` int(10) NOT NULL,
  `code` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `years` int(10) NOT NULL,
  `description` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_courses`
--

INSERT INTO `rgr_courses` (`id`, `code`, `name`, `years`, `description`) VALUES
(26, 'BSIS', 'Bachelor of Science in Information Systems', 4, 'The Bachelor of Science in Information System course provides a comprehensive blend of computer science, business management, and information technology.'),
(34, 'BSHM', 'Bachelor of Science in Hospitality Management', 4, 'The Bachelor of Science in Hospitality Management program aims to produce highly skilled, service-driven, and globally competitive hospitality professionals, integrating theoretical knowledge with practical training and meaningful industry exposure.'),
(35, 'BSCrim', 'Bachelor of Science in Criminology', 4, 'The Bachelor of Science in Criminology program at Bestlink College of the Philippines prepares students for meaningful careers in law enforcement, public safety, and the criminal justice system.'),
(38, 'BSIT', 'Bachelor of Science in Information Technology', 4, 'The program offers three specialized tracks — network administration, information security, and information management — each designed to give graduates deep insights and skills to succeed in their chosen fields.'),
(41, 'BSPsych', 'Bachelor of Science in Psychology', 4, 'The Bachelor of Science in Psychology program offers a scientific understanding of human behavior and mental processes, preparing students for careers in psychology and allied fields.'),
(42, 'BSOA', 'Bachelor of Science in Office Administration', 4, 'The Bachelor of Science in Office Administration (BSOA) program is established in accordance with CHED Memorandum Order No. 19, series of 2017, providing training in office management, administrative support, communication, and information technology'),
(43, 'BSBA', 'Bachelor of Science in Business Administration', 4, 'The Bachelor of Science in Business Administration program develops competent, ethical, and socially responsible business professionals. Students receive instruction in business theories, analytical competencies, and practical applications aligned wi'),
(45, 'BEEd', 'Bachelor of Elementary Education', 4, 'The Bachelor of Elementary Education program develops competent, caring, and reflective elementary school teachers grounded in strong content knowledge and modern pedagogy.'),
(46, 'BSEd', 'Bachelor of Secondary Education', 4, 'The Bachelor of Secondary Education program trains future high school teachers who are experts in their chosen field of specialization and skilled in evidence-based teaching.'),
(47, 'BSCpE', 'Bachelor of Science in Computer Engineering', 4, 'The Bachelor of Science in Computer Engineering program produces engineers who design and build the hardware and software systems that power modern technology.'),
(49, 'BSTM', 'Bachelor of Science in Tourism Management', 4, 'The Bachelor of Science in Tourism Management program prepares globally competitive professionals for the dynamic travel, tourism, and destination management industry.'),
(50, 'BSEnt', 'Bachelor of Science in Entrepreneurship', 4, 'The Bachelor of Science in Entrepreneurship at Bestlink College of the Philippines is a forward-looking academic program designed to nurture entrepreneurial talent, foster innovation, and develop self-reliant, socially responsible business leaders.'),
(51, 'BSAIS', 'Bachelor of Science in Accounting Information System', 4, 'The Bachelor of Science in Accounting Information System program blends solid accounting foundations with information technology and data-driven financial systems.'),
(52, 'BLIS', 'Bachelor of Library and Information Science', 4, 'The Bachelor of Library and Information Science program prepares professionals to organize, manage, and provide access to information resources in the digital age.');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_curriculums`
--

CREATE TABLE `rgr_curriculums` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `curriculum_name` varchar(120) NOT NULL,
  `effective_year` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_curriculums`
--

INSERT INTO `rgr_curriculums` (`id`, `course_id`, `curriculum_name`, `effective_year`, `is_active`) VALUES
(28, 26, 'BSIS Curriculum 2026', 2026, 1),
(29, 34, 'BSHM Curriculum 2026', 2026, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rgr_curriculum_subjects`
--

CREATE TABLE `rgr_curriculum_subjects` (
  `id` int(11) NOT NULL,
  `curriculum_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `year_level` int(11) NOT NULL,
  `semester` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_curriculum_subjects`
--

INSERT INTO `rgr_curriculum_subjects` (`id`, `curriculum_id`, `subject_id`, `year_level`, `semester`) VALUES
(47, 28, 28, 1, '1st Semester'),
(48, 28, 29, 1, '1st Semester'),
(49, 28, 30, 1, '1st Semester'),
(50, 28, 31, 1, '1st Semester'),
(51, 28, 32, 1, '1st Semester'),
(52, 28, 33, 1, '1st Semester'),
(53, 28, 34, 1, '1st Semester'),
(54, 28, 35, 1, '1st Semester'),
(55, 28, 36, 1, '1st Semester'),
(56, 28, 37, 1, '2nd Semester'),
(57, 28, 38, 1, '2nd Semester'),
(58, 28, 39, 1, '2nd Semester'),
(59, 28, 40, 1, '2nd Semester'),
(60, 28, 41, 1, '2nd Semester'),
(61, 28, 42, 1, '2nd Semester'),
(62, 28, 43, 1, '2nd Semester'),
(63, 28, 44, 1, '2nd Semester'),
(64, 28, 45, 1, '2nd Semester'),
(66, 28, 47, 2, '1st Semester'),
(67, 28, 48, 2, '1st Semester'),
(68, 28, 49, 2, '1st Semester'),
(69, 28, 50, 2, '1st Semester'),
(70, 28, 51, 2, '1st Semester'),
(71, 28, 55, 2, '1st Semester'),
(72, 28, 52, 2, '1st Semester'),
(73, 28, 53, 2, '1st Semester'),
(75, 28, 54, 2, '1st Semester'),
(76, 28, 56, 2, '2nd Semester'),
(77, 28, 57, 2, '2nd Semester'),
(78, 28, 58, 2, '2nd Semester'),
(79, 28, 59, 2, '2nd Semester'),
(80, 28, 60, 2, '2nd Semester'),
(81, 28, 61, 2, '2nd Semester'),
(82, 28, 62, 2, '2nd Semester'),
(83, 28, 63, 2, '2nd Semester'),
(84, 28, 64, 3, '1st Semester'),
(85, 28, 65, 3, '1st Semester'),
(86, 28, 66, 3, '1st Semester'),
(87, 28, 67, 3, '1st Semester'),
(88, 28, 68, 3, '1st Semester'),
(89, 28, 69, 3, '1st Semester'),
(90, 28, 70, 3, '1st Semester'),
(91, 28, 71, 3, '2nd Semester'),
(92, 28, 72, 3, '2nd Semester'),
(93, 28, 73, 3, '2nd Semester'),
(94, 28, 74, 3, '2nd Semester'),
(95, 28, 75, 3, '2nd Semester'),
(96, 28, 76, 3, '2nd Semester'),
(97, 28, 77, 3, '2nd Semester'),
(98, 28, 78, 4, '1st Semester'),
(99, 28, 79, 4, '1st Semester'),
(100, 28, 82, 4, '1st Semester'),
(101, 28, 81, 4, '1st Semester'),
(102, 28, 83, 4, '1st Semester'),
(103, 28, 84, 4, '1st Semester'),
(104, 28, 85, 4, '2nd Semester'),
(105, 28, 86, 4, '2nd Semester');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_document_requests`
--

CREATE TABLE `rgr_document_requests` (
  `id` int(10) NOT NULL,
  `request_number` varchar(250) NOT NULL,
  `student_id` int(10) DEFAULT NULL,
  `document_type` enum('TOR','COR') NOT NULL,
  `semester_id` int(10) DEFAULT NULL,
  `school_year_id` int(10) DEFAULT NULL,
  `purpose` varchar(250) NOT NULL,
  `copies` int(10) NOT NULL,
  `image_file_path` varchar(250) DEFAULT NULL,
  `status` enum('processing','pending','rejected','released','ready for release','verified') NOT NULL DEFAULT 'pending',
  `requested_at` date NOT NULL,
  `processed_by` int(10) DEFAULT NULL,
  `processed_at` date DEFAULT NULL,
  `released_by` int(10) DEFAULT NULL,
  `released_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_document_requests`
--

INSERT INTO `rgr_document_requests` (`id`, `request_number`, `student_id`, `document_type`, `semester_id`, `school_year_id`, `purpose`, `copies`, `image_file_path`, `status`, `requested_at`, `processed_by`, `processed_at`, `released_by`, `released_at`, `created_at`, `updated_at`) VALUES
(40, 'REQ-20260831-3664', 3, 'TOR', NULL, 0, 'Employment', 1, 'uploads/documents/doc_6a9494dc434402.61302234.jpg', 'released', '2026-08-31', 1006, NULL, NULL, NULL, '2026-08-30 20:38:52', '2026-08-30 21:34:56'),
(41, 'REQ-20260831-5422', 3, 'TOR', NULL, 0, 'Employment', 1, 'uploads/documents/doc_6a94a2a41a7ae6.68726547.jpg', 'released', '2026-08-31', 1006, NULL, NULL, NULL, '2026-08-30 21:37:40', '2026-08-30 21:48:02'),
(42, 'REQ-20260831-3862', 3, 'COR', 14, 31, 'Board Exam', 1, 'uploads/documents/doc_6a94aac1acdc48.67282053.jpg', 'rejected', '2026-08-31', 1006, NULL, NULL, NULL, '2026-08-30 22:12:17', '2026-08-30 22:17:23'),
(43, 'REQ-20260908-9159', 3, 'COR', 13, 31, 'Employment', 1, 'uploads/documents/doc_6a9f0021bb7765.38812334.png', 'rejected', '2026-09-08', 1006, NULL, NULL, NULL, '2026-09-07 18:19:13', '2026-09-07 18:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_enrollments`
--

CREATE TABLE `rgr_enrollments` (
  `id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `semester_id` int(10) NOT NULL,
  `year_level` int(10) NOT NULL,
  `status` enum('enrolled','not_enrolled') NOT NULL DEFAULT 'not_enrolled',
  `is_locked` int(10) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_enrollments`
--

INSERT INTO `rgr_enrollments` (`id`, `student_id`, `semester_id`, `year_level`, `status`, `is_locked`, `created_at`) VALUES
(7, 20, 13, 1, 'not_enrolled', 0, '2026-07-28'),
(8, 21, 13, 1, 'not_enrolled', 0, '2026-07-28'),
(11, 24, 13, 1, 'not_enrolled', 0, '2026-07-28'),
(12, 25, 13, 1, 'not_enrolled', 0, '2026-07-28');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_enrollment_subjects`
--

CREATE TABLE `rgr_enrollment_subjects` (
  `id` int(10) NOT NULL,
  `enrollment_id` int(10) NOT NULL,
  `final_grade` int(10) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `updated_at` date NOT NULL DEFAULT current_timestamp(),
  `class_offering_id` int(10) DEFAULT NULL,
  `remarks` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rgr_events`
--

CREATE TABLE `rgr_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT 0,
  `background_color` varchar(255) DEFAULT NULL,
  `border_color` varchar(255) DEFAULT NULL,
  `text_color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rgr_events`
--

INSERT INTO `rgr_events` (`id`, `title`, `description`, `start`, `end`, `all_day`, `background_color`, `border_color`, `text_color`, `created_at`, `updated_at`) VALUES
(96, 'Warning', 'test', '2026-02-17 00:00:00', '2026-02-19 00:00:00', 0, '#ffc107', NULL, NULL, '2026-02-09 17:45:26', '2026-02-09 17:45:26'),
(100, 'test', 'test', '2026-02-10 00:00:00', '2026-02-12 00:00:00', 0, '#dc3545', NULL, NULL, '2026-02-17 03:48:37', '2026-02-17 03:48:37'),
(101, 'try', 'try', '2026-02-19 00:00:00', '2026-02-22 00:00:00', 0, '#0d6efd', NULL, NULL, '2026-02-17 03:49:12', '2026-02-17 03:49:12'),
(102, 'cache', 'cache', '2026-02-20 00:00:00', '2026-02-22 00:00:00', 0, '#198754', NULL, NULL, '2026-02-17 03:49:30', '2026-02-17 03:49:30');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_grades`
--

CREATE TABLE `rgr_grades` (
  `id` int(10) NOT NULL,
  `enrollment_id` int(10) DEFAULT NULL,
  `prelim` int(10) DEFAULT NULL,
  `midterm` int(10) DEFAULT NULL,
  `finals` int(10) DEFAULT NULL,
  `grade` decimal(4,2) DEFAULT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rgr_notifications`
--

CREATE TABLE `rgr_notifications` (
  `id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `student_id` int(10) DEFAULT NULL,
  `recipient_type` enum('registrar','student') NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_notifications`
--

INSERT INTO `rgr_notifications` (`id`, `user_id`, `student_id`, `recipient_type`, `type`, `reference_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(69, NULL, 3, 'registrar', 'documents', 0, 'TOR Request', 'A student has submitted a new Transcript of Records (TOR) request. Please review and process the request.', 1, '2026-08-30 20:38:52'),
(70, NULL, 3, 'student', 'document', 40, 'Request Update', 'Great news! Your document submission has been verified. Your application will now move to the processing stage.', 1, '2026-08-30 20:39:16'),
(71, NULL, 3, 'student', 'document', 40, 'Request Update', 'Your application is officially under review. Registrar is processing your details, and we will update you once it\'s complete.', 1, '2026-08-30 21:16:09'),
(72, NULL, 3, 'student', 'document', 40, 'Request Update', 'Good news! Your file has been processed successfully and is now ready for release.', 1, '2026-08-30 21:34:42'),
(73, NULL, 3, 'student', 'document', 40, 'Request Update', 'Your document is ready for pickup! Please bring a valid ID (and authorization letter if represented) to the Registrar\'s Office during office hours.', 1, '2026-08-30 21:34:56'),
(74, NULL, 3, 'registrar', 'documents', 0, 'TOR Request', 'A student has submitted a new Transcript of Records (TOR) request. Please review and process the request.', 1, '2026-08-30 21:37:40'),
(75, NULL, 3, 'student', 'document', 41, 'Request Update', 'Great news! Your document submission has been verified. Your application will now move to the processing stage.', 1, '2026-08-30 21:38:08'),
(76, NULL, 3, 'student', 'document', 41, 'Request Update', 'Your application is officially under review. Registrar is processing your details, and we will update you once it\'s complete.', 1, '2026-08-30 21:38:18'),
(77, NULL, 3, 'student', 'document', 41, 'Request Update', 'Good news! Your file has been processed successfully and is now ready for release.', 1, '2026-08-30 21:39:36'),
(78, NULL, 3, 'student', 'document', 41, 'Request Update', 'Your document is ready for pickup! Please bring a valid ID (and authorization letter if represented) to the Registrar\'s Office during office hours.', 1, '2026-08-30 21:48:02'),
(79, NULL, 3, 'registrar', 'documents', 0, 'COR Request', 'A student has submitted a new Certificate of Registration (COR) request. Please review and process the request.', 1, '2026-08-30 22:12:17'),
(81, NULL, 3, 'student', 'document', 42, 'Request Update', 'Your document request has been rejected because your \r\n                  student record requires verification. \r\n                  Please contact the Registrar\'s Office for assistance.', 1, '2026-08-30 22:17:23'),
(82, NULL, 3, 'registrar', 'documents', 0, 'COR Request', 'A student has submitted a new Certificate of Registration (COR) request. Please review and process the request.', 1, '2026-09-07 18:19:14'),
(83, NULL, 3, 'student', 'document', 43, 'Request Update', 'Your document request has been rejected because the requested document is currently unavailable. Please contact the Registrar\'s Office for assistance.', 1, '2026-09-07 18:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_rooms`
--

CREATE TABLE `rgr_rooms` (
  `id` int(10) NOT NULL,
  `name` varchar(250) NOT NULL,
  `building` varchar(250) DEFAULT NULL,
  `capacity` int(10) DEFAULT NULL,
  `type` varchar(250) NOT NULL DEFAULT 'lecture'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_rooms`
--

INSERT INTO `rgr_rooms` (`id`, `name`, `building`, `capacity`, `type`) VALUES
(6, 'Room 1', 'Building 2', 45, 'Lecture'),
(7, 'Room 2', 'Building 2', 40, 'Lecture'),
(8, 'Room 3', 'Building 2', 40, 'lecture'),
(9, 'ComLab 1', 'Building 2', 40, 'Laboratory'),
(10, 'ComLab 2', 'Building 2', 40, 'Laboratory'),
(11, 'Comlab 3', 'Building 2', 40, 'Laboratory'),
(12, 'HELab1', 'Building 1', 30, 'Laboratory');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_school_years`
--

CREATE TABLE `rgr_school_years` (
  `id` int(10) NOT NULL,
  `name` varchar(250) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_school_years`
--

INSERT INTO `rgr_school_years` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(28, '2025-2026', 0, '2026-03-05 06:38:03', '2026-08-06 02:10:04'),
(29, '2024-2025', 0, '2026-03-06 03:38:01', '2026-07-20 04:52:48'),
(31, '2026-2027', 1, '2026-04-16 08:38:38', '2026-08-06 02:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_section`
--

CREATE TABLE `rgr_section` (
  `id` int(10) NOT NULL,
  `name` varchar(150) NOT NULL,
  `year_level` int(10) NOT NULL,
  `strand_id` int(10) DEFAULT NULL,
  `course_id` int(10) DEFAULT NULL,
  `capacity` int(10) DEFAULT NULL,
  `semester_id` int(10) DEFAULT NULL,
  `teacher_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_section`
--

INSERT INTO `rgr_section` (`id`, `name`, `year_level`, `strand_id`, `course_id`, `capacity`, `semester_id`, `teacher_id`) VALUES
(16, 'BSIS12A', 1, NULL, 26, 40, 9, NULL),
(17, 'BSIS11A', 1, NULL, 26, 40, 12, NULL),
(18, 'BSIT12A', 1, NULL, 38, 40, 9, NULL),
(19, 'BSIT12B', 1, NULL, 38, 40, 9, NULL),
(20, 'BSCrim12A', 1, NULL, 35, 40, 9, NULL),
(23, 'BSHM11A', 1, NULL, 34, 45, 12, NULL),
(24, 'BSPsych11A', 1, NULL, 41, 40, 13, NULL),
(25, 'BSIT11A', 1, NULL, 38, 40, 13, NULL),
(27, 'BSIT11A', 1, NULL, 38, 40, 12, NULL),
(28, 'BSIT11B', 1, NULL, 38, 40, 13, NULL),
(36, 'BSOA11A', 1, NULL, 42, 50, 13, 7);

-- --------------------------------------------------------

--
-- Table structure for table `rgr_semesters`
--

CREATE TABLE `rgr_semesters` (
  `id` int(10) NOT NULL,
  `name` varchar(250) NOT NULL,
  `school_year_id` int(10) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_semesters`
--

INSERT INTO `rgr_semesters` (`id`, `name`, `school_year_id`, `is_active`, `created_at`, `updated_at`) VALUES
(9, '2nd Semester', 28, 0, '2026-03-05 06:38:24', '2026-07-15 02:46:35'),
(11, '2nd Semester', 29, 0, '2026-03-06 18:07:39', '2026-07-20 04:52:48'),
(12, '1st Semester', 28, 0, '2026-03-22 14:19:04', '2026-08-06 02:10:04'),
(13, '1st Semester', 31, 1, '2026-05-07 07:59:07', '2026-08-20 04:24:52'),
(14, '2nd Semester', 31, 0, '2026-05-09 06:14:47', '2026-08-20 04:24:52');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_strands`
--

CREATE TABLE `rgr_strands` (
  `id` int(10) NOT NULL,
  `name` varchar(250) NOT NULL,
  `code` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_strands`
--

INSERT INTO `rgr_strands` (`id`, `name`, `code`) VALUES
(2, 'Humanities and Social Sciences', 'HUMSS');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_students_users`
--

CREATE TABLE `rgr_students_users` (
  `id` int(10) NOT NULL,
  `student_id` int(10) NOT NULL,
  `username` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_students_users`
--

INSERT INTO `rgr_students_users` (`id`, `student_id`, `username`, `password`, `email`, `last_login_at`, `last_login_ip`, `status`, `created_at`, `updated_at`) VALUES
(5, 20, 'Mark@gmail.com', '$2a$12$nGJqeroB90qHmsGtLStjjebrcvU82hUHHcRQuMl0uXebTp1/6OSba', 'Mark@gmail.com', NULL, '::1', 'Active', '2026-07-28 08:53:59', '2026-08-02 12:32:41'),
(6, 21, 'Rusty@gmail.com', '$2y$10$kKtEU.GnRyq42VHs9gmhtumxmEyfrQajp1OKu.CyLYYazjUsNgHKG', 'Rusty@gmail.com', NULL, '::1', 'Active', '2026-07-28 08:55:23', '2026-07-28 08:55:23'),
(9, 24, 'akjsdhv@gmail.com', '$2y$10$0JK5vymYIERV7yEBIFwBUOZtTIXpeFO7i2jA9L/vuvfkuf6waxMpK', 'akjsdhv@gmail.com', NULL, '::1', 'Active', '2026-07-28 09:08:06', '2026-07-28 09:08:06'),
(10, 25, 'ksjrgo@gmail.com', '$2y$10$Fa5jqNAClwQP0bwoT8tO7uAAElrojQOgqvW.rJLExkA3yKz3mYYky', 'ksjrgo@gmail.com', NULL, '::1', 'Active', '2026-07-28 13:47:29', '2026-07-28 13:47:29');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_subjects`
--

CREATE TABLE `rgr_subjects` (
  `id` int(10) NOT NULL,
  `code` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `units` int(11) NOT NULL,
  `lecture_hours` int(10) NOT NULL,
  `lab_hours` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_subjects`
--

INSERT INTO `rgr_subjects` (`id`, `code`, `name`, `units`, `lecture_hours`, `lab_hours`) VALUES
(13, 'IT101', 'Introduction to Computing', 3, 2, 0),
(14, 'IT102', 'Computer Programming 1', 2, 3, 2),
(15, 'IT103', 'Discrete Mathematics', 2, 2, 0),
(16, 'IT104', 'Computer Hardware Fundamentals', 2, 3, 0),
(17, 'GE101', 'Understanding the Self', 2, 3, 0),
(18, 'GE102', 'Purposive Communication', 2, 3, 0),
(19, 'GE103', 'Readings in Philippine History', 2, 2, 0),
(21, 'GE104', 'Mathematics in the Modern World', 1, 2, 0),
(22, 'PE101', 'Physical Education 1', 2, 2, 1),
(23, 'PE102', 'Physical Education 2', 2, 2, 0),
(24, 'NSTP101', 'National Service Training Program 1', 2, 2, 0),
(25, 'NSTP102', 'National Service Training Program 2', 2, 2, 0),
(26, 'IS101', 'Introduction to Information System', 3, 2, 1),
(28, 'GE 1', 'Understanding the Self', 3, 3, 0),
(29, 'GE 2', 'Mathematics in the Modern World', 3, 3, 0),
(30, 'GE 3', 'Science, Technology and Society', 3, 3, 0),
(31, 'GE 4', 'Purposive Communication', 3, 1, 0),
(32, 'FIL 1', 'Kontekstwalisadong Komunikasyon sa Filipino', 3, 3, 0),
(33, 'CC 101', 'Introduction to Computing', 3, 3, 0),
(34, 'CC 102', 'Computer Programming 1', 3, 2, 3),
(35, 'PE 1', 'Physical Fitness 1', 2, 2, 0),
(36, 'NSTP 1', 'National Service Training Program 1', 3, 3, 0),
(37, 'GE 5', 'Readings in the Philippine History', 3, 3, 0),
(38, 'GE 6', 'Art Appreciation', 3, 3, 0),
(39, 'GE 7', 'Ethics', 3, 3, 0),
(40, 'IS 1', 'Fundamentals of Information System', 3, 3, 0),
(41, 'CC 3', 'Computer Programming 2', 3, 2, 1),
(42, 'WEB', 'Web Development (Advance Web / Platform )', 3, 2, 1),
(43, 'FIL 2', 'Filipino sa iba\'t ibang DIsiplina', 3, 3, 0),
(44, 'PE 2', 'Folk Dance and Rhythmic Activities', 2, 2, 0),
(45, 'NSTP 2', 'National Service and Training Program 2', 3, 3, 0),
(47, 'GE 9', 'The Life and Works of Jose Rizal', 3, 3, 0),
(48, 'CC 104', 'Data Structures and Algorithms', 3, 2, 3),
(49, 'DM 101', 'Organization and Management Concepts', 3, 3, 0),
(50, 'GE Elec 1', 'Living in IT Era', 3, 3, 0),
(51, 'IS 102', 'Professional Issues in Information System', 3, 3, 0),
(52, 'IS 103', 'IT Infrastructure and Network Technologies', 3, 3, 0),
(53, 'FIL 3', 'Sosyedad at Literatura', 3, 3, 0),
(54, 'PE 3', 'Individual and Dual Sports', 2, 2, 0),
(55, 'GE 8', 'The Contemporary World', 3, 3, 0),
(56, 'ADV 01', 'Enterprise System: Concept and Practice', 3, 3, 2),
(57, 'DM 2', 'Financial Management', 3, 3, 0),
(58, 'CC 5', 'Information Management 1', 3, 2, 3),
(59, 'NET 1', 'Data Communication and Networking 1', 3, 2, 3),
(60, 'GE Elec 2', 'Reading Visual Arts', 3, 3, 0),
(61, 'IS 4', 'System and Analysis Design', 3, 2, 3),
(62, 'IS 5', 'Enterprise Architecture', 3, 2, 3),
(63, 'PE 4', 'Team Sports', 2, 2, 0),
(64, 'DM 103', 'Business Process Management', 3, 3, 0),
(65, 'CC 106', 'Application Development and Emerging Technologies', 3, 2, 3),
(66, 'QUAMET', 'Quantitative Methods with Modeling Simulation', 3, 3, 0),
(67, 'IS 106', 'IS Project Management 1', 3, 2, 3),
(68, 'ADV 03', 'IT Audit and Controls', 3, 2, 3),
(69, 'ADV 04', 'IS Innovation and New Technologies', 3, 2, 3),
(70, 'IS ELEC 1', 'introduction to Human Computer Interaction', 3, 2, 3),
(71, 'CAPSTONE 1', 'Capstone Project 1', 3, 2, 3),
(72, 'IS 107', 'IS Strategy, Management and Acquisition', 3, 2, 3),
(73, 'AD 05', 'IT Security and Management', 3, 2, 3),
(74, 'APPSDEV 2', 'Application Development and Emerging Technology 2', 3, 2, 3),
(75, 'RESEARCH', 'Methods of research in Computing', 3, 3, 0),
(76, 'IS PROF\'L ES', 'Business Intelligence', 3, 3, 0),
(77, 'DM 4', 'Evaluation of Business Performance', 3, 3, 0),
(78, 'ADV 07', 'IS Project Management 2', 3, 2, 3),
(79, 'ADV 11', 'Supply Chain Management', 3, 3, 0),
(81, 'ADV 12', 'Costumer Relationship Management4', 3, 3, 0),
(82, 'CAP 102', 'Capstone Project and Research 2', 3, 2, 3),
(83, 'ADV 08', 'Data Mining', 3, 2, 3),
(84, 'GE ELEC 3', 'Technopreneurship', 3, 3, 0),
(85, 'PRAC 101', 'OJT Practicum 1', 6, 6, 0),
(86, 'SEMTOUR', 'Seminars and Tour', 3, 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rgr_teachers`
--

CREATE TABLE `rgr_teachers` (
  `id` int(10) NOT NULL,
  `employee_id` int(10) DEFAULT NULL,
  `first_name` varchar(250) NOT NULL,
  `last_name` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_teachers`
--

INSERT INTO `rgr_teachers` (`id`, `employee_id`, `first_name`, `last_name`, `email`) VALUES
(7, 3, 'Juan', 'Dela Cruz', 'juandelacruz@gmail.com'),
(8, 5, 'Karen', 'Francisco', 'karenfrancisco@gmail.com'),
(9, 3, 'Maria Angela', 'Aguilar', 'mariaaguilar@gmail.com'),
(10, 45, 'Jessica Pestri', 'Peralta', 'peraltajessy@gmail.com'),
(11, 42, 'Angelica', 'Antonio', 'angelicareal@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `rgr_users`
--

CREATE TABLE `rgr_users` (
  `id` int(10) NOT NULL,
  `username` varchar(10) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rgr_users`
--

INSERT INTO `rgr_users` (`id`, `username`, `password`) VALUES
(1, 'davidjauri', '$2a$12$aExjOjkhkCPaMlKpUzo63OfsWLRTdUzyvi26Enrl9pDSS.prfwR4e');

-- --------------------------------------------------------

--
-- Table structure for table `sd_announcements`
--

CREATE TABLE `sd_announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `publish_date` datetime DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `image_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_announcements`
--

INSERT INTO `sd_announcements` (`announcement_id`, `title`, `message`, `publish_date`, `created_by`, `image_file`) VALUES
(43, 'test', 'test', NULL, 1001, 'test.php');

-- --------------------------------------------------------

--
-- Table structure for table `sd_approvals`
--

CREATE TABLE `sd_approvals` (
  `approval_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `submitted_on` datetime DEFAULT NULL,
  `submit_by` bigint(100) DEFAULT NULL,
  `department` int(11) DEFAULT NULL,
  `approver_id` bigint(20) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `decision` enum('Approved','Rejected','Pending') DEFAULT 'Pending',
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_approvals`
--

INSERT INTO `sd_approvals` (`approval_id`, `title`, `description`, `remarks`, `submitted_on`, `submit_by`, `department`, `approver_id`, `approved_at`, `decision`, `file_path`) VALUES
(18, 'Sample 1', '', '', NULL, 1004, NULL, 1001, '2026-03-16 13:33:01', 'Rejected', 'uploads/approvals/approval_69b780e1d46ea2.08407274.pdf'),
(19, 'Sample 2', '', '', NULL, 1001, NULL, 1001, '2026-03-16 13:33:02', 'Rejected', 'uploads/approvals/approval_69b781aaca59c6.10525276.pdf'),
(20, 'sdadsadsa', '', '', NULL, 1009, NULL, 1001, '2026-03-16 13:33:03', 'Rejected', 'uploads/approvals/approval_69b78b4c86d0d3.16285597.pdf'),
(23, 'Sample 3', '', NULL, NULL, 1005, NULL, NULL, NULL, 'Pending', 'uploads/approvals/approval_69b799b4110929.36952499.pdf'),
(24, 'dsdasdas', '', NULL, NULL, 1008, NULL, NULL, NULL, 'Pending', 'uploads/approvals/approval_69b79f56f2d104.85520681.pdf'),
(25, 'fsffsdfs', '', '', NULL, 1008, NULL, 1001, '2026-03-17 12:19:33', 'Approved', 'uploads/approvals/approval_69b8d63c297868.44131655.docx'),
(27, 'waddwadaw', 'bjdhkkdja', NULL, '2026-03-17 17:58:59', 1009, 8, 1008, NULL, 'Pending', NULL),
(28, 'test', 'test', NULL, '2026-03-17 18:04:40', NULL, 9, NULL, NULL, 'Pending', NULL),
(29, 'test', 'fjfj', NULL, NULL, 1003, NULL, NULL, NULL, 'Pending', 'uploads/approvals/approval_69b9527d22d69.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `sd_department`
--

CREATE TABLE `sd_department` (
  `department_id` int(100) NOT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `department_head` bigint(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_department`
--

INSERT INTO `sd_department` (`department_id`, `department_name`, `department_head`) VALUES
(1, 'Office of the Directress', NULL),
(2, 'Admissions and Enrollment', NULL),
(3, 'Registrar\'s Office', NULL),
(4, 'School Clinic', NULL),
(5, 'Library', NULL),
(6, 'Laboratory ', NULL),
(7, 'Operations Monitoring', NULL),
(8, 'Guidance and Counseling Office', NULL),
(9, 'Academic Affairs', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sd_issues`
--

CREATE TABLE `sd_issues` (
  `issue_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `department` int(100) NOT NULL,
  `submitted_by` bigint(20) NOT NULL,
  `submitted_on` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_issues`
--

INSERT INTO `sd_issues` (`issue_id`, `title`, `description`, `department`, `submitted_by`, `submitted_on`, `status`, `updated_at`, `file_path`) VALUES
(20, 'Sample 1', '', 4, 1004, '2026-03-16 12:02:24', 'open', '2026-03-16 13:32:54', 'uploads/issues/issue_69b780d0199462.30837965.pdf'),
(21, 'Sample 2', '', 1, 1001, '2026-03-16 12:06:31', 'open', NULL, 'uploads/issues/issue_69b781c7618945.22013948.pdf'),
(22, 'sadasdasd', '', 9, 1009, '2026-03-16 12:47:02', 'open', NULL, 'uploads/issues/issue_69b78b460a5335.99879484.pdf'),
(23, 'Sample 2', '', 2, 1002, '2026-03-16 13:30:17', 'open', NULL, 'uploads/issues/issue_69b79569bcae82.17216626.pdf'),
(24, 'Sample 2', '', 8, 1007, '2026-03-16 13:40:37', 'open', NULL, 'uploads/issues/issue_69b797d52749f3.61164867.pdf'),
(25, 'Sample 2', '', 5, 1005, '2026-03-16 13:48:26', 'open', NULL, 'uploads/issues/issue_69b799aac91c50.12531845.pdf'),
(26, 'dasdasdasd', '', 7, 1008, '2026-03-16 14:12:46', 'open', NULL, 'uploads/issues/issue_69b79f5ee82e47.52956687.pdf'),
(27, 'fhgfh', '', 9, 1009, '2026-03-17 12:10:45', 'resolved', '2026-03-17 12:11:59', 'uploads/issues/issue_69b8d4457fcbe8.26237426.png'),
(28, 'Hello Mother Father', '', 8, 1007, '2026-03-17 12:12:17', 'open', NULL, 'uploads/issues/issue_69b8d4a15a3897.93728867.jpg'),
(29, 'fsdfdsdfs', '', 8, 1007, '2026-03-17 12:24:28', 'open', NULL, 'uploads/issues/issue_69b8d77c34f183.99565017.jpg'),
(30, 'gege', '', 8, 1007, '2026-03-17 12:37:13', 'open', NULL, 'uploads/issues/issue_69b8da794d3369.14019155.jpg'),
(31, 'afsafasdas', '', 4, 1004, '2026-03-17 16:56:33', 'resolved', '2026-03-17 16:56:53', 'uploads/issues/issue_69b917410e3682.59828306.png'),
(32, 'test', 'tets', 3, 1003, '2026-09-01 01:48:07', 'open', NULL, 'uploads/approvals/approval_6a95be571ecdc.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `sd_position`
--

CREATE TABLE `sd_position` (
  `position_id` int(10) NOT NULL,
  `position_name` varchar(100) DEFAULT NULL,
  `department` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_position`
--

INSERT INTO `sd_position` (`position_id`, `position_name`, `department`) VALUES
(1, 'School Directress', 1),
(2, 'Executive Assistant ', 1),
(3, 'Administrative Officer', 1),
(4, 'Secretary', 1),
(5, 'Records Staff', 1),
(6, 'Admission Officer', 2),
(7, 'Enrollment Coordinator', 2),
(8, 'Enrollment Clerk', 2),
(9, 'Document Verifier', 2),
(10, 'School Registrar', 3),
(11, 'Assistant Registrar', 3),
(12, 'Records Officer', 3),
(13, 'Transcript Processor', 3),
(14, 'Scheduling Officer ', 3),
(15, 'Records Clerk', 3),
(16, 'School Nurse ', 4),
(17, 'School Physician ', 4),
(18, 'Medical Assistant ', 4),
(19, 'Clinical Aide', 4),
(20, 'Health Records Staff', 4),
(21, 'Head Librarian ', 5),
(22, 'Assistant Librarian', 5),
(23, 'Library Staff', 5),
(24, 'Library Aide', 5),
(25, 'Laboratory Supervisor', 6),
(26, 'Lab Technician', 6),
(27, 'Lab Assistant', 6),
(28, 'Safety Assistant', 6),
(29, 'Monitoring Officer', 7),
(30, 'Attendance Officer', 7),
(31, 'Discipline Officer', 7),
(32, 'Compliance Staff', 7),
(33, 'Guidance Counselor', 8),
(34, 'Counseling Assistant', 8),
(35, 'Case Manager', 8),
(36, 'College Coordinator', 9),
(37, 'Program Chair', 9),
(38, 'Academic Secretary', 9),
(39, 'Faculty Coordinatory', 9),
(40, 'Curriculum Officer ', 9);

-- --------------------------------------------------------

--
-- Table structure for table `sd_reports`
--

CREATE TABLE `sd_reports` (
  `report_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `submitted_by` bigint(20) DEFAULT NULL,
  `status` enum('Pending','Approved','Returned') DEFAULT 'Pending',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `report_type` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_reports`
--

INSERT INTO `sd_reports` (`report_id`, `title`, `description`, `file_path`, `department_id`, `submitted_by`, `status`, `submitted_at`, `report_type`) VALUES
(28, 'Sample 1', '', 'uploads/reports/report_69b780bacf34d6.15115163.pdf', 4, 1004, 'Pending', '2026-03-16 12:02:02', 13),
(29, 'Sample 2', '', 'uploads/reports/report_69b781bd1acf39.96001867.pdf', 1, 1001, 'Pending', '2026-03-16 12:06:21', 4),
(30, 'asdasdsad', '', 'uploads/reports/report_69b78b3e894484.07349849.pdf', 9, 1009, 'Pending', '2026-03-16 12:46:54', 25),
(31, 'Sample 3', '', 'uploads/reports/report_69b7957b84f415.04711894.pdf', 2, 1002, 'Pending', '2026-03-16 13:30:35', 7),
(32, 'Sample 1', '', 'uploads/reports/report_69b797cccff0e0.49659657.pdf', 8, 1007, 'Pending', '2026-03-16 13:40:28', 23),
(33, 'Sample 1', '', 'uploads/reports/report_69b799a0e8b638.41306710.pdf', 5, 1005, 'Pending', '2026-03-16 13:48:16', 16),
(34, 'dsadsadasd', '', 'uploads/reports/report_69b79f663949a2.60519511.pdf', 7, 1008, 'Pending', '2026-03-16 14:12:54', 22),
(36, 'pipehhh', '', 'uploads/reports/report_69ba11b31e76b0.25445082.pdf', 4, 1004, 'Pending', '2026-03-18 10:45:07', 13),
(37, 'labo', '', 'uploads/reports/report_69ba468abc1d98.10568920.pdf', 4, 1004, 'Pending', '2026-03-18 14:30:34', 15),
(38, 'ATTENDANCE', '', 'uploads/reports/report_69ba4b19a87ff3.23135058.docx', 7, 1008, 'Pending', '2026-03-18 14:50:01', 21);

-- --------------------------------------------------------

--
-- Table structure for table `sd_report_type`
--

CREATE TABLE `sd_report_type` (
  `type_id` int(11) NOT NULL,
  `report_type` varchar(100) DEFAULT NULL,
  `department_id` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_report_type`
--

INSERT INTO `sd_report_type` (`type_id`, `report_type`, `department_id`) VALUES
(1, 'Enrollment Summary Report', 1),
(2, 'Financial Summary Report', 1),
(3, 'Academic Performance Summary', 1),
(4, 'Attendance Summary Report', 1),
(5, 'Incident Summary Report', 1),
(6, 'New Enrollees Report', 2),
(7, 'Enrollment Status Report', 2),
(8, 'Section Capacity Report', 2),
(9, 'Student Master List Report', 3),
(10, 'Grade Report (Per Term)', 3),
(11, 'Student Academic History Report', 3),
(12, 'Graduation Eligibility Report', 3),
(13, 'Clinic Visit Log Report', 4),
(14, 'Student Medical Record Summary', 4),
(15, 'Incident / Injury Report', 4),
(16, 'Borrowed Books Report', 5),
(17, 'Overdue Books Report', 5),
(18, 'Library Inventory Summary Report', 5),
(19, 'Laboratory Equipment Inventory Report', 6),
(20, 'Damaged Equipment Report', 6),
(21, 'Attendance Monitoring Report', 7),
(22, 'At-Risk Student Report', 7),
(23, 'Counseling Session Report', 8),
(24, 'Discipline Case Report', 8),
(25, 'Course Enrollment Report', 9),
(26, 'Faculty Load Report', 9),
(27, 'Graduation Progress Report', 9);

-- --------------------------------------------------------

--
-- Table structure for table `sd_roles`
--

CREATE TABLE `sd_roles` (
  `role_id` int(10) NOT NULL,
  `role_name` char(100) DEFAULT NULL,
  `department` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_roles`
--

INSERT INTO `sd_roles` (`role_id`, `role_name`, `department`) VALUES
(1, 'School Directress', 1),
(2, 'Enrollment Officer', 2),
(3, 'Registrar', 3),
(4, 'Clinic Staff', 4),
(5, 'Librarian', 5),
(6, 'Laboratory Staff', 6),
(7, 'Monitoring Officer', 7),
(8, 'Guidance Counselor', 8),
(9, 'College Coordinator', 9);

-- --------------------------------------------------------

--
-- Table structure for table `sms_employee`
--

CREATE TABLE `sms_employee` (
  `employee_id` bigint(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `role` int(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `department` int(100) DEFAULT NULL,
  `position` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_employee`
--

INSERT INTO `sms_employee` (`employee_id`, `first_name`, `middle_name`, `last_name`, `role`, `status`, `date_hired`, `user_id`, `department`, `position`) VALUES
(1001, 'Andrie', 'Sta Maria', 'Elbambuena', 1, 'active', '2026-03-01', NULL, 1, 2),
(1002, 'El ', 'Dikoalam', 'Yazier', 2, 'active', NULL, 12, 2, 6),
(1003, 'Carl James', 'Dialam', 'Langres', 3, 'active', NULL, 16, 3, 11),
(1004, 'Mj ', 'Dontknow', 'Estomaguio', 4, 'active', NULL, 10, 4, 19),
(1005, 'JohnDale', 'Dekoalam', 'Cabab', 5, 'active', NULL, 14, 5, 22),
(1006, 'Justin', 'Dunknow', 'Hereda', 6, 'active', NULL, 17, 6, 27),
(1007, 'Johary', 'Dekodinalam', 'Dimatingkal', 8, 'active', NULL, 13, 8, 33),
(1008, 'John Paul ', 'Dunno', 'Corre', 7, 'active', NULL, 15, 7, 29),
(1009, 'Sayurie', 'Donno', 'Torres', 9, 'active', NULL, 11, 9, 36);

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_id` int(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` bigint(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `remember_token` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_id`, `password`, `employee_id`, `email`, `created_at`, `remember_token`) VALUES
(1, '$2y$10$1P2v4toYP6ZNlkbLhkwufOsPI3e4zH0Rm401NmI3ghVRs762h9p/.', 1001, NULL, '2026-03-15 13:27:25', NULL),
(10, '$2y$10$6KIkuZSXS8Xz5F8412fbHuC/iTy5rPY0pCH95h1LfzmvhQLmuNlx2', 1004, NULL, '2026-03-15 14:15:13', NULL),
(11, '$2y$10$.46Nwr3ERHyItVQi5h42KObJTsmuZsdWunRu.QNOWNjeuse.nQin.', 1009, NULL, '2026-03-16 04:33:42', NULL),
(12, '$2y$10$QP30TpWyZXtKZ/kSvp9Rt.A8RCPtvNAKxRMr08Jn1JCnBt1JZCMOm', 1002, NULL, '2026-03-16 05:29:17', NULL),
(13, '$2y$10$OPykXcYyFeG2LLU0J8.e5e8SsVbV/IdWLyNq2xjpcBaKUG55th24S', 1007, NULL, '2026-03-16 05:39:36', NULL),
(14, '$2y$10$MjlfZZgenEGdtQawnwYAhu/y/sztb37lkF6nIZlY4Lr2wcjbvM09S', 1005, NULL, '2026-03-16 05:46:51', NULL),
(15, '$2y$10$m9eI4tmhtIhhiUaYNAVBWe9hBjNHoEwB15yMekpq8IanPx0PrsNCq', 1008, NULL, '2026-03-16 06:03:14', NULL),
(16, '$2a$12$VjEzgrjTLJ1SzmgODq1mRe0dJA3Cww4siZ1WjT7fhfD0u3awOLzcm', 1003, NULL, '2026-03-16 07:18:38', NULL),
(17, '$2y$10$VCeB5AlD2X/RHuWm24BIIuByXygltj6rWQmnxkj3VvMCZYGCgu1.m', 1006, NULL, '2026-03-16 07:18:59', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cc_events`
--
ALTER TABLE `cc_events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `fk_event_template` (`template_id`);

--
-- Indexes for table `cc_event_templates`
--
ALTER TABLE `cc_event_templates`
  ADD PRIMARY KEY (`template_id`);

--
-- Indexes for table `cc_exams`
--
ALTER TABLE `cc_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exam_sy` (`school_year_id`),
  ADD KEY `idx_exam_sem` (`semester_id`);

--
-- Indexes for table `cc_exam_proctor`
--
ALTER TABLE `cc_exam_proctor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_exam_proctor` (`exam_schedule_id`,`faculty_id`),
  ADD KEY `idx_exam_schedule` (`exam_schedule_id`),
  ADD KEY `idx_faculty` (`faculty_id`);

--
-- Indexes for table `cc_exam_schedule`
--
ALTER TABLE `cc_exam_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exam` (`exam_id`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_section` (`section_id`),
  ADD KEY `idx_room` (`room_id`),
  ADD KEY `idx_exam_date` (`exam_date`);

--
-- Indexes for table `cc_faculty`
--
ALTER TABLE `cc_faculty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cc_faculty_employee` (`employee_id`);

--
-- Indexes for table `cc_faculty_load`
--
ALTER TABLE `cc_faculty_load`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_section_subject` (`section_id`,`subject_id`),
  ADD UNIQUE KEY `uq_faculty_load` (`section_id`,`subject_id`,`school_year_id`,`semester_id`),
  ADD KEY `idx_faculty` (`faculty_id`),
  ADD KEY `idx_section` (`section_id`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_school_year` (`school_year_id`),
  ADD KEY `idx_semester` (`semester_id`);

--
-- Indexes for table `cc_faculty_load_summary`
--
ALTER TABLE `cc_faculty_load_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_faculty_period` (`faculty_id`,`school_year_id`,`semester_id`),
  ADD KEY `idx_faculty` (`faculty_id`),
  ADD KEY `idx_school_year` (`school_year_id`),
  ADD KEY `idx_semester` (`semester_id`);

--
-- Indexes for table `cc_room`
--
ALTER TABLE `cc_room`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_code` (`room_code`);

--
-- Indexes for table `cc_schedule`
--
ALTER TABLE `cc_schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_faculty_schedule` (`faculty_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  ADD UNIQUE KEY `uk_section_schedule` (`section_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  ADD UNIQUE KEY `uk_room_schedule` (`room_id`,`day_of_week`,`start_time`,`end_time`,`semester_id`,`school_year_id`),
  ADD KEY `fk_schedule_load` (`faculty_load_id`),
  ADD KEY `fk_schedule_subject` (`subject_id`),
  ADD KEY `fk_schedule_sy` (`school_year_id`),
  ADD KEY `fk_schedule_sem` (`semester_id`);

--
-- Indexes for table `cc_sections`
--
ALTER TABLE `cc_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_section` (`section_code`,`school_year_id`,`semester_id`),
  ADD KEY `fk_section_program` (`program_id`);

--
-- Indexes for table `cc_section_faculty`
--
ALTER TABLE `cc_section_faculty`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_section_faculty_role` (`section_id`,`faculty_id`,`role`,`school_year_id`,`semester_id`),
  ADD KEY `fk_sectionfaculty_sy` (`school_year_id`),
  ADD KEY `fk_sectionfaculty_sem` (`semester_id`),
  ADD KEY `fk_sectionfaculty_faculty` (`faculty_id`);

--
-- Indexes for table `cln_clinic_visits`
--
ALTER TABLE `cln_clinic_visits`
  ADD PRIMARY KEY (`visit_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `cln_departments`
--
ALTER TABLE `cln_departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `cln_doctors`
--
ALTER TABLE `cln_doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `cln_incidents`
--
ALTER TABLE `cln_incidents`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `cln_inventory_log`
--
ALTER TABLE `cln_inventory_log`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `medication_id` (`medication_id`);

--
-- Indexes for table `cln_medications`
--
ALTER TABLE `cln_medications`
  ADD PRIMARY KEY (`medication_id`);

--
-- Indexes for table `cln_patients`
--
ALTER TABLE `cln_patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `cln_roles`
--
ALTER TABLE `cln_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `cln_staff`
--
ALTER TABLE `cln_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `enr_applicants`
--
ALTER TABLE `enr_applicants`
  ADD PRIMARY KEY (`applicant_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `preferred_section_id` (`preferred_section_id`);

--
-- Indexes for table `enr_enrollments`
--
ALTER TABLE `enr_enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`section_id`,`school_year`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `enr_students_ibfk_3` (`schedule_id`);

--
-- Indexes for table `enr_requirements`
--
ALTER TABLE `enr_requirements`
  ADD PRIMARY KEY (`requirement_id`);

--
-- Indexes for table `enr_students`
--
ALTER TABLE `enr_students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `applicant_id` (`applicant_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `enr_student_requirements`
--
ALTER TABLE `enr_student_requirements`
  ADD PRIMARY KEY (`student_requirement_id`),
  ADD UNIQUE KEY `unique_student_requirement` (`student_id`,`requirement_id`),
  ADD KEY `requirement_id` (`requirement_id`);

--
-- Indexes for table `enr_users`
--
ALTER TABLE `enr_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `gd_counselors`
--
ALTER TABLE `gd_counselors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `gd_events`
--
ALTER TABLE `gd_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gd_incident_attachments`
--
ALTER TABLE `gd_incident_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `incident_id` (`incident_id`);

--
-- Indexes for table `gd_incident_reports`
--
ALTER TABLE `gd_incident_reports`
  ADD PRIMARY KEY (`incident_id`);

--
-- Indexes for table `gd_incident_types`
--
ALTER TABLE `gd_incident_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `gd_logs`
--
ALTER TABLE `gd_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gd_sessions`
--
ALTER TABLE `gd_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `counselor_id` (`counselor_id`);

--
-- Indexes for table `gd_students_profile`
--
ALTER TABLE `gd_students_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `gd_student_concerns`
--
ALTER TABLE `gd_student_concerns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `lab_ballistic_damage`
--
ALTER TABLE `lab_ballistic_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_ballistic_inventory`
--
ALTER TABLE `lab_ballistic_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_ballistic_monitoring`
--
ALTER TABLE `lab_ballistic_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_chemistry_damage`
--
ALTER TABLE `lab_chemistry_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_chemistry_inventory`
--
ALTER TABLE `lab_chemistry_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_chemistry_monitoring`
--
ALTER TABLE `lab_chemistry_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_crime_damage`
--
ALTER TABLE `lab_crime_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_crime_inventory`
--
ALTER TABLE `lab_crime_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_crime_monitoring`
--
ALTER TABLE `lab_crime_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_defense_damage`
--
ALTER TABLE `lab_defense_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_defense_inventory`
--
ALTER TABLE `lab_defense_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_defense_monitoring`
--
ALTER TABLE `lab_defense_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_fingerprint_damage`
--
ALTER TABLE `lab_fingerprint_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_fingerprint_inventory`
--
ALTER TABLE `lab_fingerprint_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_fingerprint_monitoring`
--
ALTER TABLE `lab_fingerprint_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_he_borrow`
--
ALTER TABLE `lab_he_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_he_damage`
--
ALTER TABLE `lab_he_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_he_inventory`
--
ALTER TABLE `lab_he_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_he_monitoring`
--
ALTER TABLE `lab_he_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it1_borrow`
--
ALTER TABLE `lab_it1_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it1_damage`
--
ALTER TABLE `lab_it1_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it1_inventory`
--
ALTER TABLE `lab_it1_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it1_monitoring`
--
ALTER TABLE `lab_it1_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it2_borrow`
--
ALTER TABLE `lab_it2_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it2_damage`
--
ALTER TABLE `lab_it2_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it2_inventory`
--
ALTER TABLE `lab_it2_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it2_monitoring`
--
ALTER TABLE `lab_it2_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it3_borrow`
--
ALTER TABLE `lab_it3_borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it3_damage`
--
ALTER TABLE `lab_it3_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it3_inventory`
--
ALTER TABLE `lab_it3_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_it3_monitoring`
--
ALTER TABLE `lab_it3_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_phys_damage`
--
ALTER TABLE `lab_phys_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_phys_inventory`
--
ALTER TABLE `lab_phys_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_phys_monitoring`
--
ALTER TABLE `lab_phys_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_psy_damage`
--
ALTER TABLE `lab_psy_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_psy_inventory`
--
ALTER TABLE `lab_psy_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_psy_monitoring`
--
ALTER TABLE `lab_psy_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_qd_damage`
--
ALTER TABLE `lab_qd_damage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_qd_inventory`
--
ALTER TABLE `lab_qd_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_qd_monitoring`
--
ALTER TABLE `lab_qd_monitoring`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lbr_activity_log`
--
ALTER TABLE `lbr_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `lbr_books`
--
ALTER TABLE `lbr_books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_isbn` (`isbn`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_genre` (`genre`);

--
-- Indexes for table `lbr_borrowers`
--
ALTER TABLE `lbr_borrowers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `borrower_id` (`borrower_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_borrower_id` (`borrower_id`);

--
-- Indexes for table `lbr_settings`
--
ALTER TABLE `lbr_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `lbr_transactions`
--
ALTER TABLE `lbr_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_book_id` (`book_id`),
  ADD KEY `idx_borrower_id` (`borrower_id`);

--
-- Indexes for table `rgr_activity_log`
--
ALTER TABLE `rgr_activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_class_offerings`
--
ALTER TABLE `rgr_class_offerings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_class_offering_subject` (`subject_id`),
  ADD KEY `fk_class_offering_semester` (`semester_id`),
  ADD KEY `fk_class_offering_teacher` (`teacher_id`),
  ADD KEY `fk_class_offering_room` (`room_id`),
  ADD KEY `fk_class_offering_strand` (`strand_id`),
  ADD KEY `fk_class_offering_course` (`course_id`),
  ADD KEY `fk_class_offering_section` (`section_id`);

--
-- Indexes for table `rgr_class_schedules`
--
ALTER TABLE `rgr_class_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_schedules` (`class_offering_id`);

--
-- Indexes for table `rgr_courses`
--
ALTER TABLE `rgr_courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_curriculums`
--
ALTER TABLE `rgr_curriculums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_curriculum_course` (`course_id`);

--
-- Indexes for table `rgr_curriculum_subjects`
--
ALTER TABLE `rgr_curriculum_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_curriculum_sub_curriculum` (`curriculum_id`),
  ADD KEY `fk_curriculum_sub_subject` (`subject_id`);

--
-- Indexes for table `rgr_document_requests`
--
ALTER TABLE `rgr_document_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_enrollments`
--
ALTER TABLE `rgr_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enrollment_semester` (`semester_id`),
  ADD KEY `fk_enrollment_student` (`student_id`);

--
-- Indexes for table `rgr_enrollment_subjects`
--
ALTER TABLE `rgr_enrollment_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enrollment_sub_enrollments` (`enrollment_id`),
  ADD KEY `fk_enrollment_sub_class_offerings` (`class_offering_id`);

--
-- Indexes for table `rgr_events`
--
ALTER TABLE `rgr_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_grades`
--
ALTER TABLE `rgr_grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grade_enrollment` (`enrollment_id`);

--
-- Indexes for table `rgr_notifications`
--
ALTER TABLE `rgr_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_rooms`
--
ALTER TABLE `rgr_rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_school_years`
--
ALTER TABLE `rgr_school_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_section`
--
ALTER TABLE `rgr_section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_section_strand` (`strand_id`),
  ADD KEY `fk_section_course` (`course_id`),
  ADD KEY `fk_section_semester` (`semester_id`),
  ADD KEY `fk_section_teacher` (`teacher_id`);

--
-- Indexes for table `rgr_semesters`
--
ALTER TABLE `rgr_semesters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_semesters_school_year` (`school_year_id`);

--
-- Indexes for table `rgr_strands`
--
ALTER TABLE `rgr_strands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_students_users`
--
ALTER TABLE `rgr_students_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_students` (`student_id`);

--
-- Indexes for table `rgr_subjects`
--
ALTER TABLE `rgr_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_teachers`
--
ALTER TABLE `rgr_teachers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rgr_users`
--
ALTER TABLE `rgr_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sd_announcements`
--
ALTER TABLE `sd_announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sd_approvals`
--
ALTER TABLE `sd_approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD KEY `approver_id` (`approver_id`),
  ADD KEY `department` (`department`),
  ADD KEY `submit_by` (`submit_by`);

--
-- Indexes for table `sd_department`
--
ALTER TABLE `sd_department`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `department_head` (`department_head`);

--
-- Indexes for table `sd_issues`
--
ALTER TABLE `sd_issues`
  ADD PRIMARY KEY (`issue_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `department` (`department`);

--
-- Indexes for table `sd_position`
--
ALTER TABLE `sd_position`
  ADD PRIMARY KEY (`position_id`),
  ADD KEY `department` (`department`);

--
-- Indexes for table `sd_reports`
--
ALTER TABLE `sd_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `report_type` (`report_type`);

--
-- Indexes for table `sd_report_type`
--
ALTER TABLE `sd_report_type`
  ADD PRIMARY KEY (`type_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `sd_roles`
--
ALTER TABLE `sd_roles`
  ADD PRIMARY KEY (`role_id`),
  ADD KEY `department` (`department`);

--
-- Indexes for table `sms_employee`
--
ALTER TABLE `sms_employee`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `demo_employee_ibfk_1` (`user_id`),
  ADD KEY `demo_employee_ibfk_2` (`role`),
  ADD KEY `department` (`department`),
  ADD KEY `position` (`position`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cc_events`
--
ALTER TABLE `cc_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `cc_event_templates`
--
ALTER TABLE `cc_event_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cc_exams`
--
ALTER TABLE `cc_exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cc_exam_proctor`
--
ALTER TABLE `cc_exam_proctor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cc_exam_schedule`
--
ALTER TABLE `cc_exam_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cc_faculty`
--
ALTER TABLE `cc_faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `cc_faculty_load`
--
ALTER TABLE `cc_faculty_load`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `cc_faculty_load_summary`
--
ALTER TABLE `cc_faculty_load_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `cc_room`
--
ALTER TABLE `cc_room`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `cc_schedule`
--
ALTER TABLE `cc_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cc_section_faculty`
--
ALTER TABLE `cc_section_faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cln_clinic_visits`
--
ALTER TABLE `cln_clinic_visits`
  MODIFY `visit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cln_departments`
--
ALTER TABLE `cln_departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cln_doctors`
--
ALTER TABLE `cln_doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cln_incidents`
--
ALTER TABLE `cln_incidents`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cln_inventory_log`
--
ALTER TABLE `cln_inventory_log`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cln_medications`
--
ALTER TABLE `cln_medications`
  MODIFY `medication_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cln_patients`
--
ALTER TABLE `cln_patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cln_roles`
--
ALTER TABLE `cln_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cln_staff`
--
ALTER TABLE `cln_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enr_applicants`
--
ALTER TABLE `enr_applicants`
  MODIFY `applicant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enr_enrollments`
--
ALTER TABLE `enr_enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `enr_requirements`
--
ALTER TABLE `enr_requirements`
  MODIFY `requirement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `enr_students`
--
ALTER TABLE `enr_students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enr_student_requirements`
--
ALTER TABLE `enr_student_requirements`
  MODIFY `student_requirement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `enr_users`
--
ALTER TABLE `enr_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gd_counselors`
--
ALTER TABLE `gd_counselors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gd_events`
--
ALTER TABLE `gd_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gd_incident_attachments`
--
ALTER TABLE `gd_incident_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gd_incident_reports`
--
ALTER TABLE `gd_incident_reports`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gd_incident_types`
--
ALTER TABLE `gd_incident_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gd_logs`
--
ALTER TABLE `gd_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gd_sessions`
--
ALTER TABLE `gd_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gd_students_profile`
--
ALTER TABLE `gd_students_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gd_student_concerns`
--
ALTER TABLE `gd_student_concerns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_ballistic_damage`
--
ALTER TABLE `lab_ballistic_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lab_ballistic_inventory`
--
ALTER TABLE `lab_ballistic_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lab_ballistic_monitoring`
--
ALTER TABLE `lab_ballistic_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lab_chemistry_damage`
--
ALTER TABLE `lab_chemistry_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lab_chemistry_inventory`
--
ALTER TABLE `lab_chemistry_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_chemistry_monitoring`
--
ALTER TABLE `lab_chemistry_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_crime_damage`
--
ALTER TABLE `lab_crime_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lab_crime_inventory`
--
ALTER TABLE `lab_crime_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_crime_monitoring`
--
ALTER TABLE `lab_crime_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_defense_damage`
--
ALTER TABLE `lab_defense_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_defense_inventory`
--
ALTER TABLE `lab_defense_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_defense_monitoring`
--
ALTER TABLE `lab_defense_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_fingerprint_damage`
--
ALTER TABLE `lab_fingerprint_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_fingerprint_inventory`
--
ALTER TABLE `lab_fingerprint_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_fingerprint_monitoring`
--
ALTER TABLE `lab_fingerprint_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_he_borrow`
--
ALTER TABLE `lab_he_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lab_he_damage`
--
ALTER TABLE `lab_he_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_he_inventory`
--
ALTER TABLE `lab_he_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_he_monitoring`
--
ALTER TABLE `lab_he_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_it1_borrow`
--
ALTER TABLE `lab_it1_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_it1_damage`
--
ALTER TABLE `lab_it1_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_it1_inventory`
--
ALTER TABLE `lab_it1_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lab_it1_monitoring`
--
ALTER TABLE `lab_it1_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lab_it2_borrow`
--
ALTER TABLE `lab_it2_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_it2_damage`
--
ALTER TABLE `lab_it2_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lab_it2_inventory`
--
ALTER TABLE `lab_it2_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_it3_borrow`
--
ALTER TABLE `lab_it3_borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_it3_damage`
--
ALTER TABLE `lab_it3_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_it3_inventory`
--
ALTER TABLE `lab_it3_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_phys_damage`
--
ALTER TABLE `lab_phys_damage`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_phys_inventory`
--
ALTER TABLE `lab_phys_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_phys_monitoring`
--
ALTER TABLE `lab_phys_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_psy_damage`
--
ALTER TABLE `lab_psy_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_psy_inventory`
--
ALTER TABLE `lab_psy_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_psy_monitoring`
--
ALTER TABLE `lab_psy_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_qd_damage`
--
ALTER TABLE `lab_qd_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_qd_inventory`
--
ALTER TABLE `lab_qd_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lbr_activity_log`
--
ALTER TABLE `lbr_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `lbr_books`
--
ALTER TABLE `lbr_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `lbr_borrowers`
--
ALTER TABLE `lbr_borrowers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `lbr_settings`
--
ALTER TABLE `lbr_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lbr_transactions`
--
ALTER TABLE `lbr_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rgr_activity_log`
--
ALTER TABLE `rgr_activity_log`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1226;

--
-- AUTO_INCREMENT for table `rgr_class_offerings`
--
ALTER TABLE `rgr_class_offerings`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `rgr_class_schedules`
--
ALTER TABLE `rgr_class_schedules`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `rgr_courses`
--
ALTER TABLE `rgr_courses`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `rgr_curriculums`
--
ALTER TABLE `rgr_curriculums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `rgr_document_requests`
--
ALTER TABLE `rgr_document_requests`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `rgr_enrollments`
--
ALTER TABLE `rgr_enrollments`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rgr_enrollment_subjects`
--
ALTER TABLE `rgr_enrollment_subjects`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rgr_events`
--
ALTER TABLE `rgr_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `rgr_grades`
--
ALTER TABLE `rgr_grades`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rgr_notifications`
--
ALTER TABLE `rgr_notifications`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `rgr_rooms`
--
ALTER TABLE `rgr_rooms`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rgr_school_years`
--
ALTER TABLE `rgr_school_years`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `rgr_section`
--
ALTER TABLE `rgr_section`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `rgr_semesters`
--
ALTER TABLE `rgr_semesters`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `rgr_strands`
--
ALTER TABLE `rgr_strands`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rgr_students_users`
--
ALTER TABLE `rgr_students_users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rgr_subjects`
--
ALTER TABLE `rgr_subjects`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `rgr_teachers`
--
ALTER TABLE `rgr_teachers`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rgr_users`
--
ALTER TABLE `rgr_users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sd_announcements`
--
ALTER TABLE `sd_announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `sd_approvals`
--
ALTER TABLE `sd_approvals`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `sd_department`
--
ALTER TABLE `sd_department`
  MODIFY `department_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sd_issues`
--
ALTER TABLE `sd_issues`
  MODIFY `issue_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `sd_position`
--
ALTER TABLE `sd_position`
  MODIFY `position_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `sd_reports`
--
ALTER TABLE `sd_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `sd_report_type`
--
ALTER TABLE `sd_report_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `sd_roles`
--
ALTER TABLE `sd_roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sms_employee`
--
ALTER TABLE `sms_employee`
  MODIFY `employee_id` bigint(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1010;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cc_exam_schedule`
--
ALTER TABLE `cc_exam_schedule`
  ADD CONSTRAINT `fk_exam_schedule_section` FOREIGN KEY (`section_id`) REFERENCES `cc_sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_schedule_subject` FOREIGN KEY (`subject_id`) REFERENCES `rgr_subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_faculty_load`
--
ALTER TABLE `cc_faculty_load`
  ADD CONSTRAINT `fk_faculty_load_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `cc_faculty` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_faculty_load_school_year` FOREIGN KEY (`school_year_id`) REFERENCES `rgr_school_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_faculty_load_section` FOREIGN KEY (`section_id`) REFERENCES `cc_sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_faculty_load_semester` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_faculty_load_subject` FOREIGN KEY (`subject_id`) REFERENCES `rgr_subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cc_faculty_load_summary`
--
ALTER TABLE `cc_faculty_load_summary`
  ADD CONSTRAINT `fk_summary_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `cc_faculty` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_summary_school_year` FOREIGN KEY (`school_year_id`) REFERENCES `rgr_school_years` (`id`),
  ADD CONSTRAINT `fk_summary_semester` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`);

--
-- Constraints for table `cc_schedule`
--
ALTER TABLE `cc_schedule`
  ADD CONSTRAINT `fk_schedule_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `cc_faculty` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedule_faculty_load` FOREIGN KEY (`faculty_load_id`) REFERENCES `cc_faculty_load` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedule_room` FOREIGN KEY (`room_id`) REFERENCES `cc_room` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedule_school_year` FOREIGN KEY (`school_year_id`) REFERENCES `rgr_school_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedule_section` FOREIGN KEY (`section_id`) REFERENCES `cc_sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedule_semester` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedule_subject` FOREIGN KEY (`subject_id`) REFERENCES `rgr_subjects` (`id`);

--
-- Constraints for table `cc_sections`
--
ALTER TABLE `cc_sections`
  ADD CONSTRAINT `fk_section_program` FOREIGN KEY (`program_id`) REFERENCES `rgr_courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cln_clinic_visits`
--
ALTER TABLE `cln_clinic_visits`
  ADD CONSTRAINT `cln_clinic_visits_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `cln_patients` (`patient_id`),
  ADD CONSTRAINT `cln_clinic_visits_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `cln_doctors` (`doctor_id`);

--
-- Constraints for table `cln_doctors`
--
ALTER TABLE `cln_doctors`
  ADD CONSTRAINT `cln_doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `cln_departments` (`department_id`);

--
-- Constraints for table `cln_incidents`
--
ALTER TABLE `cln_incidents`
  ADD CONSTRAINT `cln_incidents_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `cln_patients` (`patient_id`);

--
-- Constraints for table `cln_inventory_log`
--
ALTER TABLE `cln_inventory_log`
  ADD CONSTRAINT `cln_inventory_log_ibfk_1` FOREIGN KEY (`medication_id`) REFERENCES `cln_medications` (`medication_id`);

--
-- Constraints for table `cln_staff`
--
ALTER TABLE `cln_staff`
  ADD CONSTRAINT `cln_staff_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `cln_departments` (`department_id`),
  ADD CONSTRAINT `cln_staff_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `cln_roles` (`role_id`);

--
-- Constraints for table `enr_applicants`
--
ALTER TABLE `enr_applicants`
  ADD CONSTRAINT `enr_applicants_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `rgr_courses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enr_enrollments`
--
ALTER TABLE `enr_enrollments`
  ADD CONSTRAINT `enr_enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `enr_students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enr_enrollments_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `cc_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enr_students`
--
ALTER TABLE `enr_students`
  ADD CONSTRAINT `enr_students_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `enr_applicants` (`applicant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enr_students_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `rgr_courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enr_student_requirements`
--
ALTER TABLE `enr_student_requirements`
  ADD CONSTRAINT `enr_student_requirements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `enr_students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enr_student_requirements_ibfk_2` FOREIGN KEY (`requirement_id`) REFERENCES `enr_requirements` (`requirement_id`) ON DELETE CASCADE;

--
-- Constraints for table `gd_incident_attachments`
--
ALTER TABLE `gd_incident_attachments`
  ADD CONSTRAINT `gd_incident_attachments_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `gd_incident_reports` (`incident_id`) ON DELETE CASCADE;

--
-- Constraints for table `gd_sessions`
--
ALTER TABLE `gd_sessions`
  ADD CONSTRAINT `gd_sessions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `gd_students_profile` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gd_sessions_ibfk_2` FOREIGN KEY (`counselor_id`) REFERENCES `gd_counselors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gd_student_concerns`
--
ALTER TABLE `gd_student_concerns`
  ADD CONSTRAINT `gd_student_concerns_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `gd_students_profile` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lbr_transactions`
--
ALTER TABLE `lbr_transactions`
  ADD CONSTRAINT `lbr_transactions_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `lbr_books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lbr_transactions_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `lbr_borrowers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rgr_class_offerings`
--
ALTER TABLE `rgr_class_offerings`
  ADD CONSTRAINT `fk_class_offering_course` FOREIGN KEY (`course_id`) REFERENCES `rgr_courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_offering_room` FOREIGN KEY (`room_id`) REFERENCES `rgr_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_offering_section` FOREIGN KEY (`section_id`) REFERENCES `rgr_section` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_offering_semester` FOREIGN KEY (`semester_id`) REFERENCES `rgr_semesters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_offering_strand` FOREIGN KEY (`strand_id`) REFERENCES `rgr_strands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_offering_subject` FOREIGN KEY (`subject_id`) REFERENCES `rgr_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_offering_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `rgr_teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rgr_class_schedules`
--
ALTER TABLE `rgr_class_schedules`
  ADD CONSTRAINT `fk_schedules` FOREIGN KEY (`class_offering_id`) REFERENCES `rgr_class_offerings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rgr_enrollment_subjects`
--
ALTER TABLE `rgr_enrollment_subjects`
  ADD CONSTRAINT `fk_enrollment_sub_class_offerings` FOREIGN KEY (`class_offering_id`) REFERENCES `rgr_class_offerings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrollment_sub_enrollments` FOREIGN KEY (`enrollment_id`) REFERENCES `rgr_enrollments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
