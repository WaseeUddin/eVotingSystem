-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 03:34 PM
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
-- Database: `evoting`
--

-- --------------------------------------------------------

--
-- Table structure for table `anomalies`
--

CREATE TABLE `anomalies` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `reported_by` int(11) NOT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `party` varchar(100) DEFAULT NULL,
  `campaign_statement` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_raised` decimal(12,2) NOT NULL DEFAULT 0.00,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `name`, `party`, `campaign_statement`, `photo`, `created_at`, `total_raised`, `user_id`) VALUES
(1, 'Sarah Johnson', 'Progressive Party', 'Education and healthcare', NULL, '2026-06-10 14:27:45', 51025.00, 3),
(2, 'Michael Chen', 'Liberty Alliance', 'Freedom and innovation', NULL, '2026-06-10 14:27:45', 167458.00, 4),
(3, 'Emily Rodriguez', 'Unity Coalition', 'Community development', NULL, '2026-06-10 14:27:45', 95350.00, 5),
(4, 'Wasee Uddin', 'BdJonota', 'You Vote ME', NULL, '2026-06-11 06:55:13', 11500.00, 2),
(5, 'Nafura Noor Nauha', 'APA', 'Joy Bangla', NULL, '2026-06-11 17:14:10', 0.00, 8);

-- --------------------------------------------------------

--
-- Table structure for table `candidate_agendas`
--

CREATE TABLE `candidate_agendas` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate_agendas`
--

INSERT INTO `candidate_agendas` (`id`, `candidate_id`, `title`, `description`, `created_at`) VALUES
(4, 4, 'Education', 'Improve education facilities', '2026-06-11 16:50:16'),
(5, 4, 'Healthcare', 'Provide better healthcare support', '2026-06-11 16:50:16'),
(6, 4, 'Community Development', 'All People Strong bonding', '2026-06-11 16:50:16'),
(7, 5, 'My Main Agenda', 'I will work for the people and community.', '2026-06-11 17:14:10');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_datetime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `voter_id`, `candidate_id`, `amount`, `donation_datetime`) VALUES
(7, 1, 1, 250.00, '2026-06-10 14:27:45'),
(8, 1, 2, 100.00, '2026-06-10 14:27:50'),
(9, 1, 3, 250.00, '2026-06-10 14:27:58'),
(10, 1, 1, 250.00, '2026-06-10 16:09:58'),
(11, 1, 1, 250.00, '2026-06-10 18:23:12'),
(12, 1, 1, 250.00, '2026-06-10 18:35:43'),
(13, 1, 2, 250.00, '2026-06-10 18:35:49'),
(14, 1, 2, 261.00, '2026-06-10 18:36:06'),
(15, 1, 1, 250.00, '2026-06-10 18:41:46'),
(16, 1, 1, 250.00, '2026-06-10 18:44:44'),
(17, 1, 2, 250.00, '2026-06-10 18:44:50'),
(18, 1, 1, 250.00, '2026-06-10 18:47:45'),
(19, 1, 1, 250.00, '2026-06-10 18:47:48'),
(20, 1, 2, 100.00, '2026-06-10 18:47:51'),
(21, 1, 3, 100.00, '2026-06-10 18:47:55'),
(22, 1, 3, 40000.00, '2026-06-10 18:48:03'),
(23, 1, 2, 78.00, '2026-06-10 18:55:24'),
(24, 1, 2, 25.00, '2026-06-10 19:03:46'),
(25, 1, 2, 5.00, '2026-06-10 19:03:54'),
(26, 1, 2, 50000.00, '2026-06-10 19:04:19'),
(27, 1, 2, 50000.00, '2026-06-10 19:07:05'),
(28, 1, 2, 25000.00, '2026-06-10 19:07:28'),
(29, 1, 1, 250.00, '2026-06-11 05:49:49'),
(30, 1, 1, 250.00, '2026-06-11 05:49:53'),
(31, 1, 1, 250.00, '2026-06-11 05:49:55'),
(32, 1, 2, 250.00, '2026-06-11 05:50:00'),
(33, 1, 1, 250.00, '2026-06-11 05:58:59'),
(34, 1, 1, 250.00, '2026-06-11 05:59:02'),
(35, 1, 2, 250.00, '2026-06-11 05:59:16'),
(36, 1, 1, 250.00, '2026-06-11 06:03:17'),
(37, 1, 3, 250.00, '2026-06-11 06:03:28'),
(38, 1, 1, 250.00, '2026-06-11 06:24:13'),
(39, 1, 1, 25.00, '2026-06-11 06:24:19'),
(40, 1, 1, 3000.00, '2026-06-11 06:24:33'),
(41, 1, 2, 3000.00, '2026-06-11 06:25:37'),
(42, 1, 3, 2500.00, '2026-06-11 06:25:55'),
(43, 6, 4, 1000.00, '2026-06-11 13:39:19'),
(44, 6, 4, 250.00, '2026-06-11 13:41:25'),
(45, 6, 3, 250.00, '2026-06-11 13:41:29'),
(46, 6, 2, 250.00, '2026-06-11 13:41:33'),
(47, 6, 1, 250.00, '2026-06-11 13:52:47'),
(48, 6, 2, 250.00, '2026-06-11 14:06:35'),
(49, 6, 4, 250.00, '2026-06-11 14:26:42'),
(50, 6, 3, 250.00, '2026-06-11 14:26:45'),
(51, 6, 4, 10000.00, '2026-06-11 16:47:01');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('active','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `election_type` enum('public','private') DEFAULT 'public',
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `title`, `description`, `start_datetime`, `end_datetime`, `status`, `created_at`, `election_type`, `created_by`) VALUES
(1, 'hello', 'Hello My Name Wasee', '2026-09-23 08:00:00', '2026-09-23 20:00:00', 'active', '2026-06-11 06:55:13', 'private', 1),
(2, '2026 General Election', 'National election for representatives', '2026-01-01 08:00:00', '2026-12-31 20:00:00', 'active', '2026-06-11 16:41:07', 'public', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `election_candidates`
--

CREATE TABLE `election_candidates` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_candidates`
--

INSERT INTO `election_candidates` (`id`, `election_id`, `candidate_id`, `created_at`) VALUES
(1, 1, 1, '2026-06-11 06:55:13'),
(2, 1, 4, '2026-06-11 06:55:13');

-- --------------------------------------------------------

--
-- Table structure for table `election_voters`
--

CREATE TABLE `election_voters` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `voter_id` int(11) DEFAULT NULL,
  `nid` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_voters`
--

INSERT INTO `election_voters` (`id`, `election_id`, `voter_id`, `nid`, `created_at`) VALUES
(1, 1, NULL, '3456789012345', '2026-06-11 06:55:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `nid` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('voter','candidate','admin') NOT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `nid`, `dob`, `gender`, `email`, `phone`, `password`, `role`, `verified`, `created_at`) VALUES
(1, 'John Doe', '1234567890', '2000-01-01', 'Male', 'john@example.com', '0123456789', '123456', 'voter', 1, '2026-06-10 14:27:45'),
(2, 'Wasee Uddin', '9999999999999', '2000-01-01', 'Male', 'wasee@test.com', '01700000000', '$2y$12$xcy0jC0RkqVN6X7nqS5bsOHaX3pedItNFhesSK7aYmLxRepkQ9cku', 'candidate', 1, '2026-06-11 09:47:05'),
(3, 'Sarah Johnson', '1111111111111', '1990-01-01', 'Female', 'sarah@test.com', '01711111111', '$2y$12$cgzC/MUb3vpY827pY7WsUuw4gIP0ZtRyNVXaV8.CwG9Rm3eI.pjQ.', 'candidate', 1, '2026-06-11 10:04:25'),
(4, 'Michael Chen', '2222222222222', '1991-01-01', 'Male', 'michael@test.com', '01722222222', '$2y$12$xcy0jC0RkqVN6X7nqS5bsOHaX3pedItNFhesSK7aYmLxRepkQ9cku', 'candidate', 1, '2026-06-11 10:07:34'),
(5, 'Emily Rodriguez', '3333333333333', '1992-01-01', 'Female', 'emily@test.com', '01733333333', '$2y$12$xcy0jC0RkqVN6X7nqS5bsOHaX3pedItNFhesSK7aYmLxRepkQ9cku', 'candidate', 1, '2026-06-11 13:12:02'),
(6, 'Dalal Shehub', '7777777777777', '2000-01-01', 'Male', 'dalal@test.com', '01777777777', '$2y$12$xcy0jC0RkqVN6X7nqS5bsOHaX3pedItNFhesSK7aYmLxRepkQ9cku', 'voter', 1, '2026-06-11 13:34:29'),
(7, 'Md Wasee Uddin', '1234565634', '2001-05-12', 'Male', 'aqibwasee8@gmail.com', '+8801688443377', '$2y$10$dUm7Evqz2ombzkC3L2TIn.GGUllE.4lwiF1jCwCrTXEodmhMvnPsK', 'voter', 1, '2026-06-11 17:02:44'),
(8, 'Nafura Noor Nauha', '0112320062', '2003-10-18', 'Female', 'Nauha@test.com', '+8801688443377', '$2y$10$baDByTI3y.uvRQghE9T2hOEPiPT/UpSksRbtrR9GMfN7Ywlc.Mqs.', 'candidate', 1, '2026-06-11 17:14:10'),
(9, 'Admin User', '8888888888888', '1995-01-01', 'Male', 'admin@test.com', '01788888888', '$2y$12$IMBTlCIScWG6j87nOH0V2OZxYvhxHydqEq5xDEH0jsyPWybZFNv2S', 'admin', 1, '2026-06-11 17:35:08');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `vote_datetime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `voter_id`, `candidate_id`, `election_id`, `vote_datetime`) VALUES
(1, 6, 3, 2, '2026-06-11 16:54:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anomalies`
--
ALTER TABLE `anomalies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `reported_by` (`reported_by`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidate_agendas`
--
ALTER TABLE `candidate_agendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `election_candidates`
--
ALTER TABLE `election_candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_election_candidate` (`election_id`,`candidate_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `election_voters`
--
ALTER TABLE `election_voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_election_voter_nid` (`election_id`,`nid`),
  ADD KEY `voter_id` (`voter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nid` (`nid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `election_id` (`election_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anomalies`
--
ALTER TABLE `anomalies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `candidate_agendas`
--
ALTER TABLE `candidate_agendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `election_candidates`
--
ALTER TABLE `election_candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `election_voters`
--
ALTER TABLE `election_voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anomalies`
--
ALTER TABLE `anomalies`
  ADD CONSTRAINT `anomalies_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`),
  ADD CONSTRAINT `anomalies_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `candidate_agendas`
--
ALTER TABLE `candidate_agendas`
  ADD CONSTRAINT `candidate_agendas_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`voter_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`);

--
-- Constraints for table `election_candidates`
--
ALTER TABLE `election_candidates`
  ADD CONSTRAINT `election_candidates_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `election_candidates_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `election_voters`
--
ALTER TABLE `election_voters`
  ADD CONSTRAINT `election_voters_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `election_voters_ibfk_2` FOREIGN KEY (`voter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`voter_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`),
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
