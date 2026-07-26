-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 08:32 AM
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
-- Database: `eventpro_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
-- Has a one to many relationship, one user can host multiple events
-- the user_id is the foreign key that links this table to the users table
-- IF the user gets deleted, their events get deleted as well via the ON DELETE CASCADE


DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `event_date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `category`, `event_date`, `location`, `description`, `created_at`) VALUES
(8, 11, 'Online CA test  group study session', 'Education', '2026-05-02', 'Online via Teams', 'Join us for an online group study session ahead of our upcoming tests. Please send me a message to book your spot! A link will be sent closer to the time.', '2026-04-28 04:53:32');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
-- This table uses the stores one on one private chats between users
-- The php logic ensures that users can only query rows where their session ID matches either th sender or the receiver column

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(3, 11, 13, 'Hi Lily, can you please help to organize the online study session for this weekend?', '2026-04-28 05:25:59'),
(4, 11, 13, 'Can you also please send the Teams invite on the BSc IT second year group?', '2026-04-28 05:28:20'),
(5, 13, 11, 'Hi Waldo, yes, not a problem, I will get on that ASAP.', '2026-04-28 05:32:42'),
(6, 13, 11, 'For the invite flyer, should we get a group photo of some of the students to encourage engagement?', '2026-04-28 05:35:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
-- Passwords are not store as plain text but secured using php's password_hash(). 
-- Profile pictures store the file path.

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `profile_picture`, `reset_token`, `reset_expires`) VALUES
(11, 'Waldo Oosthuizen (Event Host)', '402415832@my.richfield.ac.za', '$2y$10$TqfDEAvCYmtFmvFFjJ.WoOnpNh2ClrUvx8kbVbJ24.2U7xE5S9s3u', 'uploads/profile_69f039b84c5bd2.32944967.jpg', NULL, NULL),
(13, 'Lily Smith', 'lily@example.com', '$2y$10$twivX7A2IBKFhC9G2m8OQOKXh62gFCrR9BgEJK4A5aCd1LXaJj45K', 'uploads/profile_69f03b7c731299.87079953.jpg', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
