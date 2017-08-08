-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2017 at 05:38 AM
-- Server version: 10.1.22-MariaDB
-- PHP Version: 7.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `frc238`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_user_notes`
--

CREATE TABLE `admin_user_notes` (
  `user_id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `note` varchar(255) NOT NULL,
  `server_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` int(6) NOT NULL,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `relationship` int(11) NOT NULL,
  `phone` varchar(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `list_products`
--

CREATE TABLE `list_products` (
  `id` int(6) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `price` decimal(10,0) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `list_relationships`
--

CREATE TABLE `list_relationships` (
  `id` int(2) NOT NULL,
  `description` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_orders`
--

CREATE TABLE `product_orders` (
  `id` int(6) NOT NULL,
  `user_profile_id` int(11) NOT NULL,
  `order_time` datetime NOT NULL,
  `total_price` decimal(10,0) NOT NULL DEFAULT '0',
  `paid` decimal(10,0) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_order_contents`
--

CREATE TABLE `product_order_contents` (
  `product_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(4) NOT NULL DEFAULT '0',
  `size` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `registration_invitations`
--

CREATE TABLE `registration_invitations` (
  `id` int(4) NOT NULL,
  `email_invited` varchar(255) NOT NULL,
  `name_invited` varchar(40) NOT NULL,
  `requester` varchar(80) NOT NULL,
  `requester_user_id` int(11) NOT NULL,
  `key` varchar(10) NOT NULL,
  `used` tinyint(1) NOT NULL,
  `server_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `registration_invitations`
--

INSERT INTO `registration_invitations` (`id`, `email_invited`, `name_invited`, `requester`, `requester_user_id`, `key`, `used`, `server_stamp`) VALUES
(1, 'abc@me.com', 'ABC PERSON', 'DEF PERSON', 1, 'abcdefg', 1, '2017-07-01 04:00:05'),
(2, 'email@m.m', 'my name', 'michael phelps', 1, '7c17b9bf94', 0, '2017-07-01 04:00:05'),
(3, 'inviteperson@gmail.com', 'cool kid invite', 'michael phelps', 1, '019252394f', 0, '2017-07-01 04:00:05'),
(4, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, 'dd8e38bbed', 0, '2017-07-01 04:00:05'),
(5, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, '663c8f268a', 0, '2017-07-01 04:00:05'),
(6, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, 'ee844a902e', 1, '2017-07-01 04:00:05'),
(7, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, '5d1304e243', 0, '2017-07-01 04:00:05'),
(8, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, '6328207565', 0, '2017-07-01 04:00:05'),
(9, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, '9d1e8def21', 0, '2017-07-01 04:00:05'),
(10, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, '9fc14c6e9c', 0, '2017-07-01 04:00:05'),
(11, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, '0da1606033', 0, '2017-07-01 04:00:05'),
(12, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, 'f3a3094fca', 0, '2017-07-01 04:00:05'),
(13, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, '13fe489226', 0, '2017-07-01 04:00:05'),
(14, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, 'dd8a5fd19c', 0, '2017-07-01 04:00:05'),
(15, 'thisone@gmail.com', 'who is invites', 'michael phelps', 1, '162d278d2e', 0, '2017-07-01 04:00:05'),
(16, 'abc@email.com', 'abc full name', 'michael phelps', 1, '56a026b616', 1, '2017-07-01 04:00:05');

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `id` int(4) NOT NULL,
  `year` int(4) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'FRC TBD',
  `is_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `server_action_log`
--

CREATE TABLE `server_action_log` (
  `id` int(6) NOT NULL,
  `status_id` int(11) NOT NULL,
  `server_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `server_action_log`
--

INSERT INTO `server_action_log` (`id`, `status_id`, `server_stamp`, `url`, `message`, `user_id`) VALUES
(2, 1011, '2017-07-10 03:53:13', '/frc238/logout', 'Logout Complete', 1),
(3, 1011, '2017-08-08 03:38:02', '/frc238/logout', 'Logout Complete', 1);

-- --------------------------------------------------------

--
-- Table structure for table `server_email_log`
--

CREATE TABLE `server_email_log` (
  `id` int(6) NOT NULL,
  `status_id` int(11) NOT NULL,
  `server_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `user_profile_id` int(11) DEFAULT NULL,
  `sent` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(1) NOT NULL,
  `allow_new_accounts` tinyint(1) NOT NULL,
  `site_email_enabled` tinyint(1) NOT NULL,
  `announcement` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `allow_new_accounts`, `site_email_enabled`, `announcement`) VALUES
(1, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `status_codes`
--

CREATE TABLE `status_codes` (
  `id` int(4) NOT NULL,
  `isError` tinyint(1) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `status_codes`
--

INSERT INTO `status_codes` (`id`, `isError`, `message`) VALUES
(1001, 1, 'test'),
(1002, 0, 'Test message'),
(1003, 0, 'Test message'),
(1004, 1, 'error example'),
(1005, 0, 'test'),
(1006, 1, 'test'),
(1007, 1, 'test'),
(1008, 1, 'test'),
(1009, 1, 'test'),
(1010, 1, 'test'),
(1011, 0, 'test'),
(1012, 1, 'test'),
(1013, 0, 'test'),
(1014, 0, 'test'),
(1015, 1, 'test'),
(1016, 0, 'test'),
(1017, 1, 'test'),
(1018, 0, 'test'),
(1019, 1, 'test'),
(1020, 1, 'test'),
(1021, 0, 'test'),
(1022, 1, 'test'),
(1023, 1, 'test'),
(1024, 0, 'test'),
(1025, 0, 'test'),
(1026, 0, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `team_activities`
--

CREATE TABLE `team_activities` (
  `id` int(6) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `register_start` datetime NOT NULL,
  `register_end` datetime NOT NULL,
  `requires_permission_slip` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(6) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(64) NOT NULL,
  `server_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `server_stamp`) VALUES
(1, 'test@me.com', 'OTg3NjU0MzIx', '2017-07-10 03:44:16'),
(11, 'abc@email.com', 'OTg3NjU0MzIx', '2017-06-30 06:59:26');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_reg`
--

CREATE TABLE `user_activity_reg` (
  `user_profile_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `is_attending` tinyint(1) NOT NULL,
  `did_attend` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE `user_details` (
  `user_id` int(6) NOT NULL,
  `account_approved` tinyint(1) NOT NULL,
  `forcePwChange` tinyint(1) NOT NULL,
  `emailKey` varchar(12) NOT NULL,
  `emailVerified` tinyint(1) NOT NULL,
  `createdOn` datetime NOT NULL,
  `registrationIP` varchar(45) NOT NULL,
  `server_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL,
  `first_name` varchar(40) DEFAULT NULL,
  `last_name` varchar(40) DEFAULT NULL,
  `dob` date NOT NULL,
  `pwResetKey` varchar(12) DEFAULT NULL,
  `pwResetExpiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`user_id`, `account_approved`, `forcePwChange`, `emailKey`, `emailVerified`, `createdOn`, `registrationIP`, `server_stamp`, `is_deleted`, `first_name`, `last_name`, `dob`, `pwResetKey`, `pwResetExpiration`) VALUES
(1, 1, 0, '2ca1e56b3ec3', 1, '2017-06-28 00:54:41', '::1', '2017-06-30 05:55:57', 0, 'michael', 'phelps', '1992-07-04', NULL, NULL),
(11, 1, 0, 'invited', 1, '2017-06-30 02:59:15', '::1', '2017-06-30 06:59:26', 0, 'NULL', 'NULL', '0000-00-00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_profile`
--

CREATE TABLE `user_profile` (
  `id` int(6) NOT NULL,
  `season_id` int(4) NOT NULL,
  `user_id` int(6) NOT NULL,
  `registration_type` int(1) NOT NULL,
  `preferred_first_name` varchar(40) DEFAULT NULL,
  `profile_started` datetime NOT NULL,
  `cell_phone` varchar(14) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `shirt_size` varchar(6) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `address_city` varchar(255) DEFAULT NULL,
  `address_state` varchar(255) DEFAULT NULL,
  `address_zip` int(5) DEFAULT NULL,
  `emergency_contact_id` int(11) DEFAULT NULL,
  `emergency_contact_user_id` int(11) DEFAULT NULL,
  `biography` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_profile_mentor_specific`
--

CREATE TABLE `user_profile_mentor_specific` (
  `user_profile_id` int(11) NOT NULL,
  `profession` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_profile_student_specific`
--

CREATE TABLE `user_profile_student_specific` (
  `user_profile_id` int(11) NOT NULL,
  `grade_level` int(11) DEFAULT NULL,
  `msd_student_id` varchar(10) DEFAULT NULL,
  `permission_slip_signed` tinyint(1) DEFAULT '0',
  `permission_slip_signed_stamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_relationships`
--

CREATE TABLE `user_relationships` (
  `user_id_from` int(11) NOT NULL,
  `relationship` int(11) NOT NULL,
  `user_id_to` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `user_id` int(11) NOT NULL,
  `activeSessionId` varchar(64) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `sessionStart` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_user_notes`
--
ALTER TABLE `admin_user_notes`
  ADD KEY `admin_user_notes_fk0` (`user_id`),
  ADD KEY `admin_user_notes_fk1` (`admin_user_id`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emergency_contacts_fk0` (`relationship`);

--
-- Indexes for table `list_products`
--
ALTER TABLE `list_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `list_relationships`
--
ALTER TABLE `list_relationships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_orders`
--
ALTER TABLE `product_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_orders_fk0` (`user_profile_id`);

--
-- Indexes for table `product_order_contents`
--
ALTER TABLE `product_order_contents`
  ADD KEY `product_order_contents_fk0` (`product_order_id`),
  ADD KEY `product_order_contents_fk1` (`product_id`);

--
-- Indexes for table `registration_invitations`
--
ALTER TABLE `registration_invitations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_invitations_fk0` (`requester_user_id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`);

--
-- Indexes for table `server_action_log`
--
ALTER TABLE `server_action_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_action_log_fk0` (`status_id`),
  ADD KEY `server_action_log_fk1` (`user_id`);

--
-- Indexes for table `server_email_log`
--
ALTER TABLE `server_email_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_email_log_fk0` (`status_id`),
  ADD KEY `server_email_log_fk1` (`user_id`),
  ADD KEY `server_email_log_fk2` (`user_profile_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_codes`
--
ALTER TABLE `status_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_activities`
--
ALTER TABLE `team_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity_reg`
--
ALTER TABLE `user_activity_reg`
  ADD KEY `user_activity_reg_fk0` (`user_profile_id`),
  ADD KEY `user_activity_reg_fk1` (`activity_id`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_profile_fk0` (`season_id`),
  ADD KEY `user_profile_fk1` (`user_id`),
  ADD KEY `user_profile_fk2` (`emergency_contact_id`),
  ADD KEY `user_profile_fk3` (`emergency_contact_user_id`);

--
-- Indexes for table `user_profile_mentor_specific`
--
ALTER TABLE `user_profile_mentor_specific`
  ADD KEY `user_profile_mentor_specific_fk0` (`user_profile_id`);

--
-- Indexes for table `user_profile_student_specific`
--
ALTER TABLE `user_profile_student_specific`
  ADD KEY `user_profile_student_specific_fk0` (`user_profile_id`);

--
-- Indexes for table `user_relationships`
--
ALTER TABLE `user_relationships`
  ADD KEY `user_relationships_fk0` (`user_id_from`),
  ADD KEY `user_relationships_fk1` (`relationship`),
  ADD KEY `user_relationships_fk2` (`user_id_to`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD KEY `user_sessions_fk0` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `list_products`
--
ALTER TABLE `list_products`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `list_relationships`
--
ALTER TABLE `list_relationships`
  MODIFY `id` int(2) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `product_orders`
--
ALTER TABLE `product_orders`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `registration_invitations`
--
ALTER TABLE `registration_invitations`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `server_action_log`
--
ALTER TABLE `server_action_log`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `server_email_log`
--
ALTER TABLE `server_email_log`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `status_codes`
--
ALTER TABLE `status_codes`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1027;
--
-- AUTO_INCREMENT for table `team_activities`
--
ALTER TABLE `team_activities`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `user_profile`
--
ALTER TABLE `user_profile`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_user_notes`
--
ALTER TABLE `admin_user_notes`
  ADD CONSTRAINT `admin_user_notes_fk0` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `admin_user_notes_fk1` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_fk0` FOREIGN KEY (`relationship`) REFERENCES `list_relationships` (`id`);

--
-- Constraints for table `product_orders`
--
ALTER TABLE `product_orders`
  ADD CONSTRAINT `product_orders_fk0` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profile` (`id`);

--
-- Constraints for table `product_order_contents`
--
ALTER TABLE `product_order_contents`
  ADD CONSTRAINT `product_order_contents_fk0` FOREIGN KEY (`product_order_id`) REFERENCES `product_orders` (`id`),
  ADD CONSTRAINT `product_order_contents_fk1` FOREIGN KEY (`product_id`) REFERENCES `list_products` (`id`);

--
-- Constraints for table `registration_invitations`
--
ALTER TABLE `registration_invitations`
  ADD CONSTRAINT `registration_invitations_fk0` FOREIGN KEY (`requester_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `server_action_log`
--
ALTER TABLE `server_action_log`
  ADD CONSTRAINT `server_action_log_fk0` FOREIGN KEY (`status_id`) REFERENCES `status_codes` (`id`),
  ADD CONSTRAINT `server_action_log_fk1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `server_email_log`
--
ALTER TABLE `server_email_log`
  ADD CONSTRAINT `server_email_log_fk0` FOREIGN KEY (`status_id`) REFERENCES `status_codes` (`id`),
  ADD CONSTRAINT `server_email_log_fk1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `server_email_log_fk2` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profile` (`id`);

--
-- Constraints for table `user_activity_reg`
--
ALTER TABLE `user_activity_reg`
  ADD CONSTRAINT `user_activity_reg_fk0` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profile` (`id`),
  ADD CONSTRAINT `user_activity_reg_fk1` FOREIGN KEY (`activity_id`) REFERENCES `team_activities` (`id`);

--
-- Constraints for table `user_details`
--
ALTER TABLE `user_details`
  ADD CONSTRAINT `user_details_fk0` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `user_profile_fk0` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`),
  ADD CONSTRAINT `user_profile_fk1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_profile_fk2` FOREIGN KEY (`emergency_contact_id`) REFERENCES `emergency_contacts` (`id`),
  ADD CONSTRAINT `user_profile_fk3` FOREIGN KEY (`emergency_contact_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_profile_mentor_specific`
--
ALTER TABLE `user_profile_mentor_specific`
  ADD CONSTRAINT `user_profile_mentor_specific_fk0` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profile` (`id`);

--
-- Constraints for table `user_profile_student_specific`
--
ALTER TABLE `user_profile_student_specific`
  ADD CONSTRAINT `user_profile_student_specific_fk0` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profile` (`id`);

--
-- Constraints for table `user_relationships`
--
ALTER TABLE `user_relationships`
  ADD CONSTRAINT `user_relationships_fk0` FOREIGN KEY (`user_id_from`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_relationships_fk1` FOREIGN KEY (`relationship`) REFERENCES `list_relationships` (`id`),
  ADD CONSTRAINT `user_relationships_fk2` FOREIGN KEY (`user_id_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_fk0` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
