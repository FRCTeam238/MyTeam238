SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myFRC238`
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
  `relationship` int(2) NOT NULL,
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

--
-- Dumping data for table `seasons`
--

INSERT INTO `seasons` (`id`, `year`, `name`, `is_active`) VALUES
(2017, 2017, 'FRC TBD', 1);

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
  `new_accounts_access_code` varchar(20) DEFAULT NULL COMMENT 'If not null, code must be entered to create an account. Will bypass admin approval. Case in-sensitive.',
  `site_email_enabled` tinyint(1) NOT NULL,
  `announcement` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `allow_new_accounts`, `new_accounts_access_code`, `site_email_enabled`, `announcement`) VALUES
(1, 1, NULL, 1, NULL);

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
(0, 0, 'Other'),
(1001, 1, 'Supplied email is already in use, and cannot be used to create an account.'),
(1002, 0, 'Your account has been created! Please check your email to confirm your address.'),
(1003, 1, 'Your account has been created, but we weren\'t able to notify you via email. You\'ll need to contact support to verify your account.'),
(1004, 1, 'There was an issue when attempting to create your account. Please contact support.'),
(1005, 0, 'Congrats! Your email has been verified. You\'re all set to log in.'),
(1006, 1, 'Something went wrong when attempting to activate your account. Please contact support.'),
(1007, 1, 'Unable to log in. Please verify your email and password before trying again.'),
(1008, 1, 'You cannot log in until your email has been verified. Please click the enclosed link.'),
(1009, 1, 'Your account has not yet been approved, and therefore cannot be accessed. For security purposes, each account must be approved before it can be used.'),
(1010, 1, 'Your password must be changed, either due to an account change you\'ve made or the request of an administrator.'),
(1011, 0, 'You\'ve been logged out. See you next time!'),
(1012, 1, 'Unable to reset password, the specified email address could not be found or there was an error when initiating the reset.'),
(1013, 1, 'Your reset was initiated, but we were unable to notify you via email. Please contact support.'),
(1014, 0, 'Your password has been updated. You\'re ready to log in.'),
(1015, 1, 'There was a problem while attempting to change your password, perhaps the link has expired. Please try again or contact support.'),
(1016, 0, 'Welcome! Before you continue, we\'ll need you to tell us a little more about you.'),
(1017, 1, 'The server has detected a problem with your session, and you\'ll need to try logging in again.'),
(1018, 0, 'User details updated'),
(1019, 1, 'Something went wrong while attempting to update user details, please contact support.'),
(1020, 1, 'Something has gone wrong while attempting to accept the invitation. Please contact support.'),
(1021, 0, 'Email invitation has been accepted.'),
(1022, 1, 'Something has gone wrong with the invitation process. Please contact support.'),
(1023, 1, 'We couldn\'t change your password because the current password entered was incorrect, or the new password didn\'t match its confirmation.'),
(1024, 0, 'Account information has been updated.'),
(1025, 1, 'There was an error while updating account information. Please try again, and contact support if the problem persists.'),
(1026, 1, 'Page is unavailable because you\'ve either already completed it, or it does not apply to you.'),
(1027, 0, 'Your email address has been updated. Please click the activation link sent to the new address to verify your account.'),
(1028, 0, 'Your account has been deactivated. We\'re sorry to see you go!'),
(1029, 1, 'The file uploaded does not meet the requirements.'),
(1030, 1, 'Page could not be accessed because it requires a season to be joined.'),
(1031, 1, 'You\'re trying to access the system too quickly. Please wait at least 10 seconds between attempts.'),
(1032, 1, 'The system has determined that you may be a threat. Please come back later if you wish to attempt access again.'),
(1033, 0, 'Requested relationship has been added, but requires verification by the other party before it will be confirmed.'),
(1034, 1, 'Requested relationship already existed, and cannot be added again.'),
(1035, 0, 'Relationship has been updated.'),
(1036, 1, 'An access code is required at this time, and was either incorrect or missing.');

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
  `profile_pic_key` varchar(10) DEFAULT NULL,
  `first_name` varchar(40) DEFAULT NULL,
  `last_name` varchar(40) DEFAULT NULL,
  `dob` date NOT NULL,
  `pwResetKey` varchar(12) DEFAULT NULL,
  `pwResetExpiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `behavior_contract` tinyint(1) DEFAULT NULL,
  `cell_phone` varchar(14) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `shirt_size` varchar(6) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `address_city` varchar(255) DEFAULT NULL,
  `address_state` varchar(255) DEFAULT NULL,
  `address_zip` varchar(5) DEFAULT NULL,
  `emergency_contact_id` int(11) DEFAULT NULL,
  `emergency_contact_user_id` int(11) DEFAULT NULL,
  `biography` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_profile_alumni_specific`
--

CREATE TABLE `user_profile_alumni_specific` (
  `user_profile_id` int(11) NOT NULL,
  `graduation_year` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_profile_mentorparent_specific`
--

CREATE TABLE `user_profile_mentorparent_specific` (
  `user_profile_id` int(11) NOT NULL,
  `profession` varchar(50) DEFAULT NULL,
  `employer` varchar(50) DEFAULT NULL
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
  `permission_slip_signed_when` datetime DEFAULT NULL,
  `permission_slip_signed_who` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_relationships`
--

CREATE TABLE `user_relationships` (
  `user_id_from` int(11) NOT NULL,
  `relationship` int(2) NOT NULL,
  `user_id_to` int(11) NOT NULL,
  `accepted` tinyint(1) NOT NULL,
  `is_deleted` binary(1) NOT NULL
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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `list_products`
--
ALTER TABLE `list_products`
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
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `profile_pic_key` (`profile_pic_key`);

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
-- Indexes for table `user_profile_alumni_specific`
--
ALTER TABLE `user_profile_alumni_specific`
  ADD UNIQUE KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `user_profile_mentorparent_specific`
--
ALTER TABLE `user_profile_mentorparent_specific`
  ADD UNIQUE KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `user_profile_student_specific`
--
ALTER TABLE `user_profile_student_specific`
  ADD UNIQUE KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `user_relationships`
--
ALTER TABLE `user_relationships`
  ADD KEY `user_relationships_fk0` (`user_id_from`),
  ADD KEY `user_relationships_fk1` (`user_id_to`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD UNIQUE KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `product_orders`
--
ALTER TABLE `product_orders`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `registration_invitations`
--
ALTER TABLE `registration_invitations`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2018;
--
-- AUTO_INCREMENT for table `server_action_log`
--
ALTER TABLE `server_action_log`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
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
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1037;
--
-- AUTO_INCREMENT for table `team_activities`
--
ALTER TABLE `team_activities`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
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
-- Constraints for table `user_profile_alumni_specific`
--
ALTER TABLE `user_profile_alumni_specific`
  ADD CONSTRAINT `user_profile_alumni_specific_fk0` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profile` (`id`);

--
-- Constraints for table `user_profile_mentorparent_specific`
--
ALTER TABLE `user_profile_mentorparent_specific`
  ADD CONSTRAINT `user_profile_mentorparent_specific_fk0` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profile` (`id`);

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
  ADD CONSTRAINT `user_relationships_fk1` FOREIGN KEY (`user_id_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_fk0` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
