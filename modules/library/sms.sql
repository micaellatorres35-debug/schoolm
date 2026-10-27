-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2026 at 10:17 AM
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
-- Database: `sms`
--

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
(1, 'Member Updated', 'Updated member \"Pedro Reyes\"', 'Librarian', '2026-10-15 18:46:59'),
(2, 'Book Borrowed', '\"A Farewell to Arms\" borrowed by Pedro Reyes (due 2026-10-16)', 'Librarian', '2026-10-15 18:47:17'),
(3, 'Book Lost', '\"A Farewell to Arms\" reported as lost', 'Librarian', '2026-10-15 18:48:03'),
(4, 'Book Borrowed', '\"Mga Kuwento ni Lola Basyang\" borrowed by Juan Dela Cruz (due 2026-10-29)', 'Librarian', '2026-10-15 18:52:56'),
(5, 'Book Returned', '\"Mga Kuwento ni Lola Basyang\" returned', 'Librarian', '2026-10-15 18:53:10'),
(6, 'Lost Book Removed', 'Removed lost record for \"A Farewell to Arms\" (Transaction #1)', 'Librarian', '2026-10-15 18:55:11'),
(7, 'Book Borrowed', '\"Mga Kuwento ni Lola Basyang\" borrowed by Juan Dela Cruz (due 2026-10-29)', 'Librarian', '2026-10-15 19:03:55'),
(8, 'Book Lost', '\"Mga Kuwento ni Lola Basyang\" reported as lost', 'Librarian', '2026-10-15 19:04:04'),
(9, 'Lost Book Removed', 'Removed lost record for \"Mga Kuwento ni Lola Basyang\" — book stock restored (Transaction #3)', 'Librarian', '2026-10-15 19:04:09'),
(10, 'Book Borrowed', '\"Mga Kuwento ni Lola Basyang\" borrowed by Juan Dela Cruz (due 2026-10-29)', 'Librarian', '2026-10-15 19:04:21'),
(11, 'Book Lost', '\"Mga Kuwento ni Lola Basyang\" reported as lost', 'Librarian', '2026-10-15 19:04:33'),
(12, 'Lost Book Removed', 'Removed lost record for \"Mga Kuwento ni Lola Basyang\" — book stock restored (Transaction #4)', 'Librarian', '2026-10-15 19:04:36'),
(13, 'Book Deleted', 'Deleted \"Republic Act 9163: National Service Training Program\"', 'Librarian', '2026-10-15 19:07:10'),
(14, 'Book Borrowed', '\"Harry Potter and the Sorcerer\'s Stone\" borrowed by Juan Dela Cruz (due 2026-10-29)', 'Librarian', '2026-10-15 19:20:16'),
(15, 'Book Lost', '\"Harry Potter and the Sorcerer\'s Stone\" reported as lost', 'Librarian', '2026-10-15 19:20:28'),
(16, 'Lost Book Removed', 'Removed lost record for \"Harry Potter and the Sorcerer\'s Stone\" — book stock restored (Transaction #5)', 'Librarian', '2026-10-15 19:20:37'),
(17, 'Book Deleted', 'Deleted \"Mga Kuwento ni Lola Basyang\"', 'Librarian', '2026-10-15 19:34:38'),
(18, 'Book Deleted', 'Deleted \"Harry Potter and the Sorcerer\'s Stone\"', 'Librarian', '2026-10-15 19:35:34'),
(19, 'Book Deleted', 'Deleted \"A Farewell to Arms\"', 'Librarian', '2026-10-15 19:46:43'),
(20, 'Book Borrowed', '\"Mga Kuwento ni Lola Basyang\" borrowed by Juan Dela Cruz (due 2026-10-29)', 'Librarian', '2026-10-15 19:46:55'),
(21, 'Book Lost', '\"Mga Kuwento ni Lola Basyang\" reported as lost', 'Librarian', '2026-10-15 19:47:00'),
(22, 'Lost Book Removed', 'Removed lost record for \"Mga Kuwento ni Lola Basyang\" — book stock restored (Transaction #6)', 'Librarian', '2026-10-15 19:47:08'),
(23, 'Book Borrowed', '\"Mga Kuwento ni Lola Basyang\" borrowed by Maria Santos (due 2026-10-17)', 'Librarian', '2026-10-16 07:22:36'),
(24, 'Book Lost', '\"Mga Kuwento ni Lola Basyang\" reported as lost', 'Librarian', '2026-10-16 07:22:49'),
(25, 'Lost Book Removed', 'Removed lost record for \"Mga Kuwento ni Lola Basyang\" — book stock restored (Transaction #7)', 'Librarian', '2026-10-16 07:23:30'),
(26, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:24:29'),
(27, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:24:30'),
(28, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:24:32'),
(29, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:45:46'),
(30, 'Book Borrowed', '\"Mga Kuwento ni Lola Basyang\" borrowed by Juan Dela Cruz (due 2026-10-30)', 'Librarian', '2026-10-16 07:46:06'),
(31, 'Book Returned', '\"Mga Kuwento ni Lola Basyang\" returned', 'Librarian', '2026-10-16 07:46:13'),
(32, 'Book Borrowed', '\"Mga Kuwento ni Lola Basyang\" borrowed by Juan Dela Cruz (due 2026-10-21)', 'Librarian', '2026-10-16 07:50:53'),
(33, 'Book Returned', '\"Mga Kuwento ni Lola Basyang\" returned', 'Librarian', '2026-10-16 07:51:12'),
(34, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:51:29'),
(35, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:51:43'),
(36, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:51:44'),
(37, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:51:45'),
(38, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:51:51'),
(39, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:56:30'),
(40, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:56:31'),
(41, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 07:58:51'),
(42, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:05:41'),
(43, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:07:03'),
(44, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:07:09'),
(45, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:07:09'),
(46, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:07:18'),
(47, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:07:19'),
(48, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:11:10'),
(49, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:13:15'),
(50, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:13:17'),
(51, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:13:17'),
(52, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:13:17'),
(53, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:13:17'),
(54, 'Settings Updated', 'Library settings updated', 'Librarian', '2026-10-16 08:13:26');

-- --------------------------------------------------------

--
-- Table structure for table `lbr_books`
--

CREATE TABLE `lbr_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `genre` varchar(100) DEFAULT 'General Reference',
  `year` year(4) DEFAULT NULL,
  `isbn` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_url` varchar(500) DEFAULT NULL,
  `cover_local` varchar(255) DEFAULT NULL,
  `status` enum('available','borrowed','lost','damaged') DEFAULT 'available',
  `condition` enum('Excellent','Good','Fair','Poor') DEFAULT 'Good',
  `added_date` date DEFAULT curdate(),
  `total_stock` int(11) NOT NULL DEFAULT 1,
  `available_stock` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_books`
--

INSERT INTO `lbr_books` (`id`, `title`, `author`, `genre`, `year`, `isbn`, `description`, `cover_url`, `cover_local`, `status`, `condition`, `added_date`, `total_stock`, `available_stock`) VALUES
(1, 'Merriam-Webster Collegiate Dictionary', 'Merriam-Webster', 'General Reference', '2020', '9780877798095', 'The most trusted English language reference with over 225,000 definitions, usage examples, and word origins.', 'https://covers.openlibrary.org/b/isbn/9780877798095-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 3, 3),
(2, 'The World Almanac and Book of Facts 2024', 'Sarah Janssen', 'General Reference', '2024', '9781510777408', 'The most trusted reference book in the world with up-to-date facts, statistics, and records on every topic.', 'https://covers.openlibrary.org/b/isbn/9781510777408-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(3, 'Introduction to Computer Science', 'Nell Dale', 'Computer Science & IT', '2019', '9781284077309', 'A comprehensive introduction to computing concepts, programming, data structures, and algorithms.', 'https://covers.openlibrary.org/b/isbn/9781284077309-L.jpg', NULL, 'available', 'Good', '2026-10-15', 4, 4),
(4, 'Think: A Compelling Introduction to Philosophy', 'Simon Blackburn', 'Philosophy', '2001', '9780192854254', 'An accessible exploration of the great questions of philosophy covering knowledge, mind, free will, and ethics.', 'https://covers.openlibrary.org/b/isbn/9780192854254-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(5, 'Psychology: The Science of Mind and Behaviour', 'Richard Gross', 'Psychology', '2020', '9781510455955', 'A comprehensive guide to psychology covering cognition, development, social behavior, and mental health.', 'https://covers.openlibrary.org/b/isbn/9781510455955-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 3, 3),
(6, 'The Art of Thinking Clearly', 'Rolf Dobelli', 'Ethics & Logic', '2013', '9780062219695', 'A collection of 99 cognitive biases and logical errors that influence our decisions and thinking.', 'https://covers.openlibrary.org/b/isbn/9780062219695-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(7, 'The Bible: New International Version', 'Various Authors', 'Bible & Sacred Texts', '2011', '9780310430049', 'The complete Holy Bible in the New International Version, widely used in academic and religious settings.', 'https://covers.openlibrary.org/b/isbn/9780310430049-L.jpg', NULL, 'available', 'Good', '2026-10-15', 5, 5),
(8, 'World Religions: A Very Short Introduction', 'Malory Nye', 'Religion & Theology', '2008', '9780199552665', 'An overview of the world\'s major religions including Christianity, Islam, Buddhism, Hinduism, and Judaism.', 'https://covers.openlibrary.org/b/isbn/9780199552665-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(9, 'Introduction to Political Science', 'Mark Dickerson', 'Political Science', '2018', '9781285462233', 'A foundational text covering political theory, government systems, public policy, and international relations.', 'https://covers.openlibrary.org/b/isbn/9781285462233-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 3, 3),
(10, 'Economics: Principles and Applications', 'Robert Hall', 'Economics', '2017', '9781305088276', 'Covers microeconomics and macroeconomics principles with real-world applications and case studies.', 'https://covers.openlibrary.org/b/isbn/9781305088276-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(11, 'Philippine Constitutional Law', 'Isagani Cruz', 'Law & Government', '2015', '9789712338779', 'A definitive reference on the 1987 Philippine Constitution covering civil liberties, government structure, and rights.', 'https://covers.openlibrary.org/b/isbn/9789712338779-L.jpg', NULL, 'available', 'Good', '2026-10-15', 4, 4),
(12, 'Foundations of Education in the Philippines', 'Rosita Navarro', 'Education', '2019', '9789712348310', 'An overview of the Philippine educational system, philosophy of education, and teaching methodologies.', 'https://covers.openlibrary.org/b/isbn/9789712348310-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(13, 'Business Organization and Management', 'Jose Paguio', 'Commerce & Business', '2020', '9789712347887', 'Covers the fundamentals of business management, organization theories, and Philippine business environment.', 'https://covers.openlibrary.org/b/isbn/9789712347887-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 4, 4),
(14, 'Introduction to Criminology', 'Jose Desierto', 'Criminology', '2021', '9789712349157', 'A comprehensive guide for criminology students covering criminal behavior, law enforcement, and the justice system.', 'https://covers.openlibrary.org/b/isbn/9789712349157-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 5, 5),
(15, 'Gramatikang Filipino', 'Paz Arriola-Valeros', 'Filipino Language', '2016', '9789712346996', 'A comprehensive grammar reference for the Filipino language covering syntax, morphology, and usage rules.', 'https://covers.openlibrary.org/b/isbn/9789712346996-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(16, 'English Grammar in Use', 'Raymond Murphy', 'English Language', '2019', '9781108457651', 'The world\'s best-selling grammar book for learners of English with clear explanations and practice exercises.', 'https://covers.openlibrary.org/b/isbn/9781108457651-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 5, 5),
(17, 'Oxford Spanish Dictionary', 'Oxford University Press', 'Foreign Language', '2017', '9780198604457', 'The most comprehensive Spanish-English dictionary with over 300,000 words, phrases, and translations.', 'https://covers.openlibrary.org/b/isbn/9780198604457-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(18, 'Calculus: Early Transcendentals', 'James Stewart', 'Mathematics', '2015', '9781285741550', 'The most widely used calculus textbook covering limits, derivatives, integrals, and series.', 'https://covers.openlibrary.org/b/isbn/9781285741550-L.jpg', NULL, 'available', 'Good', '2026-10-15', 4, 4),
(19, 'Biology: The Unity and Diversity of Life', 'Cecie Starr', 'Biology', '2016', '9781305073951', 'A complete introduction to biology covering cells, genetics, evolution, ecology, and human physiology.', 'https://covers.openlibrary.org/b/isbn/9781305073951-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 3, 3),
(20, 'Chemistry: The Central Science', 'Theodore Brown', 'Chemistry', '2017', '9780134553986', 'A leading general chemistry textbook that integrates the quantitative and visual aspects of modern chemistry.', 'https://covers.openlibrary.org/b/isbn/9780134553986-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(21, 'University Physics with Modern Physics', 'Hugh Young', 'Physics', '2019', '9780135216118', 'A comprehensive university physics text covering mechanics, thermodynamics, electromagnetism, and modern physics.', 'https://covers.openlibrary.org/b/isbn/9780135216118-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(22, 'Earth: An Introduction to Physical Geology', 'Edward Tarbuck', 'Earth Science', '2016', '9780134127941', 'Covers plate tectonics, volcanoes, earthquakes, minerals, rocks, and the geologic history of Earth.', 'https://covers.openlibrary.org/b/isbn/9780134127941-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(23, 'Fundamentals of Engineering', 'Rajib Mall', 'Technology & Engineering', '2018', '9780198093695', 'Covers essential engineering principles including mechanics, thermodynamics, circuits, and materials science.', 'https://covers.openlibrary.org/b/isbn/9780198093695-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(24, 'Medical-Surgical Nursing', 'Joyce Black', 'Medicine & Health', '2018', '9780323401425', 'A comprehensive clinical nursing reference covering assessment, diagnosis, and care for medical-surgical patients.', 'https://covers.openlibrary.org/b/isbn/9780323401425-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 4, 4),
(25, 'Fundamentals of Nursing', 'Carol Taylor', 'Nursing', '2019', '9781496347428', 'Covers the core concepts and skills of nursing practice including patient care, safety, and clinical procedures.', 'https://covers.openlibrary.org/b/isbn/9781496347428-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 5, 5),
(26, 'Soil Science: An Introduction', 'Robert Plaster', 'Agriculture', '2013', '9781111312190', 'A foundational text on soil properties, plant nutrition, water management, and sustainable agriculture.', 'https://covers.openlibrary.org/b/isbn/9781111312190-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(27, 'Music: An Appreciation', 'Roger Kamien', 'Arts & Music', '2017', '9781259447099', 'An introduction to music history and appreciation covering classical, romantic, and contemporary styles.', 'https://covers.openlibrary.org/b/isbn/9781259447099-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(28, 'Physical Education and Health', 'Arnav Dutta', 'Sports & Recreation', '2020', '9789712349928', 'Covers physical fitness, health concepts, sports, and the importance of an active lifestyle for Filipino students.', 'https://covers.openlibrary.org/b/isbn/9789712349928-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(29, 'Architecture: Form, Space, and Order', 'Francis Ching', 'Architecture & Design', '2015', '9781118745083', 'A classic visual reference for architectural design covering principles of form, space, organization, and order.', 'https://covers.openlibrary.org/b/isbn/9781118745083-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 2, 2),
(30, 'Noli Me Tangere', 'Jose Rizal', 'Filipino Literature', '0000', '9789712907142', 'Jose Rizal\'s landmark novel exposing the abuses of Spanish colonial rule in the Philippines.', 'https://covers.openlibrary.org/b/isbn/9789712907142-L.jpg', NULL, 'available', 'Good', '2026-10-15', 5, 5),
(31, 'El Filibusterismo', 'Jose Rizal', 'Filipino Literature', '0000', '9789712907159', 'The sequel to Noli Me Tangere, following Crisostomo Ibarra\'s return as the mysterious Simoun.', 'https://covers.openlibrary.org/b/isbn/9789712907159-L.jpg', NULL, 'available', 'Good', '2026-10-15', 5, 5),
(32, 'Florante at Laura', 'Francisco Balagtas', 'Filipino Literature', '0000', '9789712907166', 'A masterpiece of Philippine literature written in verse, the greatest work in the Tagalog language.', 'https://covers.openlibrary.org/b/isbn/9789712907166-L.jpg', NULL, 'available', 'Fair', '2026-10-15', 3, 3),
(33, 'To Kill a Mockingbird', 'Harper Lee', 'Fiction', '1960', '9780061935466', 'A Pulitzer Prize-winning novel about racial injustice and moral growth in the American South.', 'https://covers.openlibrary.org/b/isbn/9780061935466-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(34, 'The Great Gatsby', 'F. Scott Fitzgerald', 'Fiction', '1925', '9780743273565', 'A classic American novel about wealth, love, and the pursuit of the American Dream in the 1920s.', 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(35, 'The Catcher in the Rye', 'J.D. Salinger', 'Fiction', '1951', '9780316769174', 'A coming-of-age novel narrated by Holden Caulfield about alienation, identity, and the loss of innocence.', 'https://covers.openlibrary.org/b/isbn/9780316769174-L.jpg', NULL, 'available', 'Good', '2026-10-15', 2, 2),
(36, 'Pride and Prejudice', 'Jane Austen', 'English Literature', '0000', '9780141439518', 'Jane Austen\'s beloved novel about love, marriage, and social class in 19th century England.', 'https://covers.openlibrary.org/b/isbn/9780141439518-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(37, 'Selected Poems of Jose Garcia Villa', 'Jose Garcia Villa', 'Poetry & Drama', '1958', '9789712907173', 'A collection of poems by the most celebrated Filipino poet in the English language.', 'https://covers.openlibrary.org/b/isbn/9789712907173-L.jpg', NULL, 'available', 'Fair', '2026-10-15', 2, 2),
(38, 'A History of the Philippines', 'David Steinberg', 'Philippine History', '2000', '9780813333984', 'A comprehensive narrative of Philippine history from pre-colonial times through the modern era.', 'https://covers.openlibrary.org/b/isbn/9780813333984-L.jpg', NULL, 'available', 'Good', '2026-10-15', 4, 4),
(39, 'Kasaysayan ng Pilipinas', 'Milagros Guerrero', 'Philippine History', '2017', '9789712349447', 'An in-depth study of Philippine history written in Filipino, covering colonialism, independence, and national identity.', 'https://covers.openlibrary.org/b/isbn/9789712349447-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 4, 4),
(40, 'World History: Patterns of Interaction', 'Roger Beck', 'World History', '2012', '9780547491127', 'A global perspective on world history covering ancient civilizations through the modern world.', 'https://covers.openlibrary.org/b/isbn/9780547491127-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(41, 'National Geographic Atlas of the World', 'National Geographic Society', 'Geography & Travel', '2019', '9781426220104', 'The most authoritative world atlas featuring detailed maps, satellite imagery, and geographic data.', 'https://covers.openlibrary.org/b/isbn/9781426220104-L.jpg', NULL, 'available', 'Excellent', '2026-10-15', 2, 2),
(42, 'Rizal Without the Overcoat', 'Ambeth Ocampo', 'Biography & Autobiography', '2012', '9789712719677', 'A collection of essays that humanizes Jose Rizal, revealing little-known facts about the national hero.', 'https://covers.openlibrary.org/b/isbn/9789712719677-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(43, 'Mga Piling Akda sa Panitikang Pilipino', 'Various Filipino Authors', 'Filipiniana', '2018', '9789712349928', 'A curated anthology of Philippine literature featuring poetry, short stories, and essays by Filipino writers.', 'https://covers.openlibrary.org/b/isbn/9789712349928-L.jpg', NULL, 'available', 'Good', '2026-10-15', 4, 4),
(44, 'Philippine Science and Technology Review', 'DOST Philippines', 'Periodicals & Journals', '2023', NULL, 'A quarterly journal featuring the latest research and developments in science and technology in the Philippines.', NULL, NULL, 'available', 'Excellent', '2026-10-15', 1, 1),
(45, 'Animal Farm', 'George Orwell', 'Fiction', '1945', '9780451526342', 'A political allegory about farm animals who overthrow their farmer, mirroring the Russian Revolution.', 'https://covers.openlibrary.org/b/isbn/9780451526342-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(46, 'The Little Prince', 'Antoine de Saint-Exupery', 'Children & Young Adult', '1943', '9780156012195', 'A beloved philosophical tale about a young prince who visits various planets and learns about life.', 'https://covers.openlibrary.org/b/isbn/9780156012195-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 3),
(47, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 'Children & Young Adult', '1997', '9780590353427', 'The first Harry Potter book following a young wizard\'s journey at Hogwarts School of Witchcraft and Wizardry.', 'https://covers.openlibrary.org/b/isbn/9780590353427-L.jpg', NULL, 'available', 'Good', '2026-10-15', 4, 4),
(48, 'Republic Act 9163: National Service Training Program', 'Congress of the Philippines', 'Government Documents', '2001', NULL, 'The official text of the National Service Training Program Act of 2001 and its implementing rules.', NULL, NULL, 'available', 'Good', '2026-10-15', 2, 2),
(49, 'Mga Kuwento ni Lola Basyang', 'Severino Reyes', 'Filipiniana', '1925', '9789712349058', 'Classic Filipino folk tales originally published in Liwayway magazine, beloved by generations of Filipinos.', 'https://covers.openlibrary.org/b/isbn/9789712349058-L.jpg', NULL, 'available', 'Good', '2026-10-15', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `lbr_book_similarity`
--

CREATE TABLE `lbr_book_similarity` (
  `id` int(11) NOT NULL,
  `book_id_a` int(11) NOT NULL,
  `book_id_b` int(11) NOT NULL,
  `similarity_score` decimal(5,4) DEFAULT 0.0000,
  `computed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `grade` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `join_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_borrowers`
--

INSERT INTO `lbr_borrowers` (`id`, `name`, `borrower_id`, `email`, `phone`, `type`, `grade`, `address`, `active`, `join_date`) VALUES
(1, 'Juan Dela Cruz', '20241234', 'juan@bcp.edu.ph', '09171234567', 'Student', 'BSCS - Year 1', NULL, 1, '2026-10-15'),
(2, 'Maria Santos', 'T-1001', 'maria@bcp.edu.ph', '09179876543', 'Teacher', 'Faculty - Math', NULL, 1, '2026-10-15'),
(3, 'Pedro Reyes', 'S-2001', NULL, NULL, 'Student', NULL, NULL, 1, '2026-10-15');

-- --------------------------------------------------------

--
-- Table structure for table `lbr_borrow_history`
--

CREATE TABLE `lbr_borrow_history` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `days_late` int(11) DEFAULT 0,
  `was_late` tinyint(1) DEFAULT 0,
  `fine_charged` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_borrow_history`
--

INSERT INTO `lbr_borrow_history` (`id`, `borrower_id`, `book_id`, `genre`, `borrow_date`, `due_date`, `return_date`, `days_late`, `was_late`, `fine_charged`, `created_at`) VALUES
(2, 1, 49, 'Filipiniana', '2026-10-16', '2026-10-30', '2026-10-16', 0, 0, 0.00, '2026-10-16 07:46:13'),
(3, 1, 49, 'Filipiniana', '2026-10-16', '2026-10-21', '2026-10-16', 0, 0, 0.00, '2026-10-16 07:51:12');

-- --------------------------------------------------------

--
-- Table structure for table `lbr_recommendations`
--

CREATE TABLE `lbr_recommendations` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `recommendation_type` enum('genre_based','collaborative','trending','new_arrival') DEFAULT 'genre_based',
  `score` decimal(5,4) DEFAULT 0.0000,
  `was_borrowed` tinyint(1) DEFAULT 0,
  `shown_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lbr_reminders`
--

CREATE TABLE `lbr_reminders` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `reminder_type` enum('sms','email','both') DEFAULT 'email',
  `reminder_stage` enum('3_day','1_day','overdue','final') DEFAULT '3_day',
  `risk_score` decimal(5,2) DEFAULT 0.00,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `message_preview` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lbr_settings`
--

CREATE TABLE `lbr_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_settings`
--

INSERT INTO `lbr_settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'library_name', 'School Library Management System'),
(2, 'max_borrow_days', '14'),
(3, 'max_books_per_member', '5'),
(4, 'daily_fine_rate', '0.50'),
(5, 'auto_save', 'true'),
(6, 'ai_reminder_enabled', 'true'),
(7, 'ai_reminder_threshold', '60'),
(8, 'ai_sms_enabled', 'false'),
(9, 'ai_email_enabled', 'true'),
(10, 'ai_recommendations_count', '5'),
(11, 'ai_rec_min_similarity', '0.3');

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
  `status` enum('active','returned','overdue','lost') DEFAULT 'active',
  `condition` varchar(50) DEFAULT 'Good',
  `fine` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `is_lost` tinyint(1) NOT NULL DEFAULT 0,
  `lost_date` date DEFAULT NULL,
  `lost_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lbr_transactions`
--

INSERT INTO `lbr_transactions` (`id`, `book_id`, `borrower_id`, `borrow_date`, `due_date`, `return_date`, `status`, `condition`, `fine`, `notes`, `is_lost`, `lost_date`, `lost_notes`, `created_at`) VALUES
(8, 49, 1, '2026-10-16', '2026-10-30', '2026-10-16', 'returned', 'Good', 0.00, '', 0, NULL, NULL, '2026-10-16 07:46:06'),
(9, 49, 1, '2026-10-16', '2026-10-21', '2026-10-16', 'returned', 'Good', 0.00, '', 0, NULL, NULL, '2026-10-16 07:50:53');

-- --------------------------------------------------------

--
-- Table structure for table `sd_position`
--

CREATE TABLE `sd_position` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_position`
--

INSERT INTO `sd_position` (`position_id`, `position_name`) VALUES
(22, 'Assistant Librarian');

-- --------------------------------------------------------

--
-- Table structure for table `sd_roles`
--

CREATE TABLE `sd_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sd_roles`
--

INSERT INTO `sd_roles` (`role_id`, `role_name`) VALUES
(1, 'Staff'),
(2, 'Librarian'),
(3, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `sms_employee`
--

CREATE TABLE `sms_employee` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `department` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_employee`
--

INSERT INTO `sms_employee` (`employee_id`, `first_name`, `middle_name`, `last_name`, `role`, `status`, `department`, `position`) VALUES
(1005, 'JohnDale', NULL, 'Cabab', 2, 'active', 5, 22);

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_id`, `employee_id`, `password`) VALUES
(1, 1005, '$2y$10$MjlfZZgenEGdtQawnwYAhu/y/sztb37lkF6nIZlY4Lr2wcjbvM09S');

--
-- Indexes for dumped tables
--

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
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_genre` (`genre`);

--
-- Indexes for table `lbr_book_similarity`
--
ALTER TABLE `lbr_book_similarity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_pair` (`book_id_a`,`book_id_b`),
  ADD KEY `book_id_b` (`book_id_b`),
  ADD KEY `idx_score` (`similarity_score`);

--
-- Indexes for table `lbr_borrowers`
--
ALTER TABLE `lbr_borrowers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `borrower_id` (`borrower_id`),
  ADD KEY `idx_borrower_id` (`borrower_id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `lbr_borrow_history`
--
ALTER TABLE `lbr_borrow_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_borrower` (`borrower_id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_genre` (`genre`),
  ADD KEY `idx_was_late` (`was_late`);

--
-- Indexes for table `lbr_recommendations`
--
ALTER TABLE `lbr_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_borrower` (`borrower_id`),
  ADD KEY `idx_book` (`book_id`);

--
-- Indexes for table `lbr_reminders`
--
ALTER TABLE `lbr_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction` (`transaction_id`),
  ADD KEY `idx_borrower` (`borrower_id`),
  ADD KEY `idx_sent_at` (`sent_at`);

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
  ADD KEY `idx_borrower` (`borrower_id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `sd_position`
--
ALTER TABLE `sd_position`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `sd_roles`
--
ALTER TABLE `sd_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `sms_employee`
--
ALTER TABLE `sms_employee`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uk_employee` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lbr_activity_log`
--
ALTER TABLE `lbr_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `lbr_books`
--
ALTER TABLE `lbr_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `lbr_book_similarity`
--
ALTER TABLE `lbr_book_similarity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lbr_borrowers`
--
ALTER TABLE `lbr_borrowers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lbr_borrow_history`
--
ALTER TABLE `lbr_borrow_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lbr_recommendations`
--
ALTER TABLE `lbr_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lbr_reminders`
--
ALTER TABLE `lbr_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lbr_settings`
--
ALTER TABLE `lbr_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `lbr_transactions`
--
ALTER TABLE `lbr_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sd_position`
--
ALTER TABLE `sd_position`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sd_roles`
--
ALTER TABLE `sd_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sms_employee`
--
ALTER TABLE `sms_employee`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1006;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lbr_book_similarity`
--
ALTER TABLE `lbr_book_similarity`
  ADD CONSTRAINT `lbr_book_similarity_ibfk_1` FOREIGN KEY (`book_id_a`) REFERENCES `lbr_books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lbr_book_similarity_ibfk_2` FOREIGN KEY (`book_id_b`) REFERENCES `lbr_books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lbr_borrow_history`
--
ALTER TABLE `lbr_borrow_history`
  ADD CONSTRAINT `lbr_borrow_history_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `lbr_borrowers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lbr_borrow_history_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `lbr_books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lbr_recommendations`
--
ALTER TABLE `lbr_recommendations`
  ADD CONSTRAINT `lbr_recommendations_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `lbr_borrowers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lbr_recommendations_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `lbr_books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lbr_reminders`
--
ALTER TABLE `lbr_reminders`
  ADD CONSTRAINT `lbr_reminders_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `lbr_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lbr_reminders_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `lbr_borrowers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lbr_transactions`
--
ALTER TABLE `lbr_transactions`
  ADD CONSTRAINT `lbr_transactions_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `lbr_books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lbr_transactions_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `lbr_borrowers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
