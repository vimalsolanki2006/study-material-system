-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2025 at 07:18 AM
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
-- Database: `study_materials_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=active, 0=inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `description`, `created_by`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'CPP', 'This course will you to gain complete knowledge on c++ programming language', 7, '2025-04-19 05:13:31', '2025-05-02 15:02:20', 1),
(2, 'PHP', 'This course will help you to build a strong foundation on PHP and MySQL.', 7, '2025-04-19 10:57:33', '2025-04-29 04:37:39', 1),
(3, 'HTML5 & CSS', 'In this course, you will learn the basics of web development, with the aim of creating websites and apps that work across multiple devices.', 7, '2025-04-20 03:35:29', '2025-05-02 15:10:09', 0),
(4, 'Arduino Programming', 'This course is designed to build up your basic fundamentals about Arduino programming with an LED, various sensors, and more.', 7, '2025-05-02 11:08:36', '2025-05-02 14:06:10', 1);

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=active, 0=inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`material_id`, `title`, `description`, `file_path`, `file_size`, `file_type`, `course_id`, `uploaded_by`, `upload_date`, `is_active`) VALUES
(3, 'CH 1', 'Basic of OOP concepts', '68047d7ea4057.pptx', 102180, 'application/vnd.openxmlformats-officedocument.pres', 1, 7, '2025-04-20 04:52:14', 1),
(4, 'Overview of web fundamentals', 'Understanding what is WWW, HTML, HTTP and URL; Basic structure of HTML Document; Creating an HTML Document; Understanding HTML Elements and Attributes; Headings; Paragraphs; Tags; Bold and Italic Texts; Whitespace in HTML; Horizontal and Vertical Line Breaks.', '68047e64113ca.pdf', 649154, 'application/pdf', 3, 7, '2025-04-20 04:56:04', 1),
(5, 'Introduction to Arduino', '', '6814a833a4184.pdf', 3011966, 'application/pdf', 4, 7, '2025-05-02 11:10:43', 1),
(6, 'Led-Interfacing with Arduino', 'This is a practice question sheet on using LEDs with the Arduino programming language. You can use the information in the introduction to Arduino to learn about this program and answer some practice questions to test your comprehension.', '6814a8a844dc4.pdf', 50920, 'application/pdf', 4, 7, '2025-05-02 11:12:40', 0),
(7, 'CH 2', 'Tokens and datatype', '6814e1727961c.pdf', 841496, 'application/pdf', 1, 9, '2025-05-02 15:14:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(7, 'Admin1', 'admin1@gmail.com', '$2y$10$5Ph3KzTU4xM0LIJsJjc/BOVWsf0QGw6R62ux0r.yx33peeUKr1yHm', 'admin', '2025-04-19 05:55:04'),
(8, 'User1', 'user1@gmail.com', '$2y$10$M76QT4Emep8E7KZ3xF.3b.SaUQM85KmGzzKR2btwlMLOBYVKvS3W2', 'student', '2025-04-19 05:55:31'),
(9, 'Admin2', 'admin2@gmail.com', '$2y$10$OlrgPsXNTk31R/F44p8wX.6olrT0BzSGjCVhyXhsviHz0MBGmYCce', 'admin', '2025-04-21 10:35:07'),
(11, 'User2', 'user2@gmail.com', '$2y$10$EYG7Ghh/xtOImGo/zN2WRujBxhE/J2ifzLdj7yY/t7wWTp9E43/me', 'student', '2025-05-02 13:51:51'),
(12, 'User3', 'user3@gmail.com', '$2y$10$KvyVP.YPOvrLPppW75NXBulwidKstC8hjpSvaqVtUzWENZX1/x4re', 'student', '2025-05-03 00:42:07'),
(13, 'User4', 'user4@gmail.com', '$2y$10$U3ieUtzLWD0G00zpbtUz0.e6bDCqPEfqJJbfh4/LN1VZTkisEQndm', 'student', '2025-05-03 04:16:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `fk_courses_creator` (`created_by`),
  ADD KEY `idx_course_name` (`course_name`),
  ADD KEY `idx_course_active` (`is_active`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `idx_material_course` (`course_id`),
  ADD KEY `idx_material_active` (`is_active`),
  ADD KEY `idx_material_uploader` (`uploaded_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `fk_materials_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_materials_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
