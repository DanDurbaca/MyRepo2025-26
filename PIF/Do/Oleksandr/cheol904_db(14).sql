-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: May 22, 2026 at 11:56 PM
-- Server version: 11.8.6-MariaDB-ubu2404
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cheol904_db`
--

-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `cheol904_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cheol904_db`;
--
-- Table structure for table `admin_post`
--

CREATE TABLE `admin_post` (
  `pk_postID` int(11) NOT NULL,
  `fk_author` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversation`
--

CREATE TABLE `chat_conversation` (
  `pk_conversationID` int(11) NOT NULL,
  `type` enum('private','group') NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `createdBy` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_message`
--

CREATE TABLE `chat_message` (
  `pk_messageID` int(11) NOT NULL,
  `fk_conversation` int(11) NOT NULL,
  `fk_sender` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('draft','sent') NOT NULL DEFAULT 'draft',
  `sentAt` datetime DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_message_attachment`
--

CREATE TABLE `chat_message_attachment` (
  `pk_fileID` int(11) NOT NULL,
  `fk_message` int(11) NOT NULL,
  `filePath` varchar(1024) NOT NULL,
  `fileName` varchar(1024) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_participant`
--

CREATE TABLE `chat_participant` (
  `fk_conversation` int(11) NOT NULL,
  `fk_user` varchar(50) NOT NULL,
  `joinedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_read_state`
--

CREATE TABLE `chat_read_state` (
  `fk_conversation` int(11) NOT NULL,
  `fk_user` varchar(50) NOT NULL,
  `lastReadMessageId` int(11) DEFAULT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `pk_collectionID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `fk_user` varchar(50) NOT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contains`
--

CREATE TABLE `contains` (
  `pkfk_measurement` int(11) NOT NULL,
  `pkfk_collection` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verification`
--

CREATE TABLE `email_verification` (
  `pk_verificationID` int(11) NOT NULL,
  `fk_user` varchar(50) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiresAt` datetime NOT NULL,
  `used` tinyint(4) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friendship`
--

CREATE TABLE `friendship` (
  `pkfk_user1` varchar(50) NOT NULL,
  `pkfk_user2` varchar(50) NOT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement`
--

CREATE TABLE `measurement` (
  `pk_measurementID` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `airPressure` decimal(6,2) DEFAULT NULL,
  `lightIntensity` decimal(6,2) DEFAULT NULL,
  `airQuality` decimal(6,2) DEFAULT NULL,
  `fk_station` varchar(50) NOT NULL,
  `fk_ownerId` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `measurement`
--
DELIMITER $$
CREATE TRIGGER `trg_measurement_set_owner` BEFORE INSERT ON `measurement` FOR EACH ROW BEGIN
    DECLARE v_owner VARCHAR(50);

    SELECT oh.fk_ownerId
      INTO v_owner
      FROM ownership_history oh
     WHERE oh.fk_serialNumber = NEW.fk_station
       AND oh.unregisteredAt IS NULL
     ORDER BY oh.registeredAt DESC
     LIMIT 1;

    SET NEW.fk_ownerId = v_owner;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `pk_notificationID` int(11) NOT NULL,
  `fk_user` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `isRead` tinyint(1) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ownership_history`
--

CREATE TABLE `ownership_history` (
  `pk_ID` int(11) NOT NULL,
  `fk_serialNumber` varchar(50) NOT NULL,
  `fk_ownerId` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `registeredAt` datetime NOT NULL DEFAULT current_timestamp(),
  `unregisteredAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `pk_resetID` int(11) NOT NULL,
  `fk_user` varchar(50) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiresAt` datetime NOT NULL,
  `used` tinyint(4) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `pk_requestID` int(11) NOT NULL,
  `fk_sender` varchar(50) NOT NULL,
  `fk_receiver` varchar(50) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

CREATE TABLE `shares` (
  `pkfk_user` varchar(50) NOT NULL,
  `pkfk_collection` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slot`
--

CREATE TABLE `slot` (
  `pk_sampleID` int(11) NOT NULL,
  `fk_collection` int(11) NOT NULL,
  `fk_station` varchar(50) NOT NULL,
  `startDateTime` datetime NOT NULL,
  `endDateTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `station`
--

CREATE TABLE `station` (
  `pk_serialNumber` varchar(50) NOT NULL,
  `fk_createdBy` varchar(50) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `registration_code` varchar(8) DEFAULT NULL,
  `registration_token` varchar(64) DEFAULT NULL,
  `registration_requested_at` datetime DEFAULT NULL,
  `registration_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `pk_username` varchar(50) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `isEmailVerified` tinyint(1) NOT NULL DEFAULT 0,
  `emailVerifiedAt` datetime DEFAULT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `role` enum('User','Admin') NOT NULL DEFAULT 'User',
  `avatar` varchar(255) DEFAULT NULL,
  `locale` varchar(5) NOT NULL DEFAULT 'en',
  `theme` varchar(10) NOT NULL DEFAULT 'light',
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_post`
--
ALTER TABLE `admin_post`
  ADD PRIMARY KEY (`pk_postID`),
  ADD KEY `fk_author` (`fk_author`);

--
-- Indexes for table `chat_conversation`
--
ALTER TABLE `chat_conversation`
  ADD PRIMARY KEY (`pk_conversationID`),
  ADD KEY `createdBy` (`createdBy`);

--
-- Indexes for table `chat_message`
--
ALTER TABLE `chat_message`
  ADD PRIMARY KEY (`pk_messageID`),
  ADD KEY `fk_conversation` (`fk_conversation`),
  ADD KEY `fk_sender` (`fk_sender`),
  ADD KEY `idx_chat_message_conv_status_created` (`fk_conversation`,`status`,`createdAt`),
  ADD KEY `idx_chat_message_draft_lookup` (`fk_conversation`,`fk_sender`,`status`,`pk_messageID`);

--
-- Indexes for table `chat_message_attachment`
--
ALTER TABLE `chat_message_attachment`
  ADD PRIMARY KEY (`pk_fileID`),
  ADD KEY `fk_message` (`fk_message`);

--
-- Indexes for table `chat_participant`
--
ALTER TABLE `chat_participant`
  ADD PRIMARY KEY (`fk_conversation`,`fk_user`),
  ADD KEY `fk_user` (`fk_user`);

--
-- Indexes for table `chat_read_state`
--
ALTER TABLE `chat_read_state`
  ADD PRIMARY KEY (`fk_conversation`,`fk_user`),
  ADD KEY `idx_chat_read_user` (`fk_user`),
  ADD KEY `fk_chat_read_state_message` (`lastReadMessageId`);

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`pk_collectionID`),
  ADD UNIQUE KEY `uq_collection_owner_name` (`fk_user`,`name`);

--
-- Indexes for table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`pkfk_measurement`,`pkfk_collection`),
  ADD KEY `pkfk_collection` (`pkfk_collection`);

--
-- Indexes for table `email_verification`
--
ALTER TABLE `email_verification`
  ADD PRIMARY KEY (`pk_verificationID`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_user` (`fk_user`);

--
-- Indexes for table `friendship`
--
ALTER TABLE `friendship`
  ADD UNIQUE KEY `uniq_friendship` (`pkfk_user1`,`pkfk_user2`),
  ADD KEY `fk_friendship_user2` (`pkfk_user2`);

--
-- Indexes for table `measurement`
--
ALTER TABLE `measurement`
  ADD PRIMARY KEY (`pk_measurementID`),
  ADD KEY `idx_measurement_owner_id` (`fk_ownerId`),
  ADD KEY `idx_measurement_station_time` (`fk_station`,`timestamp`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`pk_notificationID`),
  ADD KEY `fk_user` (`fk_user`);

--
-- Indexes for table `ownership_history`
--
ALTER TABLE `ownership_history`
  ADD PRIMARY KEY (`pk_ID`) USING BTREE,
  ADD KEY `idx_active_owner` (`fk_serialNumber`,`unregisteredAt`),
  ADD KEY `fk_ownership_history_user` (`fk_ownerId`),
  ADD KEY `idx_ownership_history_active` (`fk_serialNumber`,`unregisteredAt`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`pk_resetID`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_user` (`fk_user`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`pk_requestID`),
  ADD KEY `fk_sender` (`fk_sender`),
  ADD KEY `fk_receiver` (`fk_receiver`);

--
-- Indexes for table `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`pkfk_user`,`pkfk_collection`),
  ADD KEY `fk_shares_collection` (`pkfk_collection`);

--
-- Indexes for table `slot`
--
ALTER TABLE `slot`
  ADD PRIMARY KEY (`pk_sampleID`),
  ADD KEY `fk_collection` (`fk_collection`),
  ADD KEY `fk_station` (`fk_station`);

--
-- Indexes for table `station`
--
ALTER TABLE `station`
  ADD PRIMARY KEY (`pk_serialNumber`),
  ADD KEY `fk_createdBy` (`fk_createdBy`),
  ADD KEY `idx_station_reg_code` (`registration_code`),
  ADD KEY `idx_station_reg_expires` (`registration_expires_at`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`pk_username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_post`
--
ALTER TABLE `admin_post`
  MODIFY `pk_postID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_conversation`
--
ALTER TABLE `chat_conversation`
  MODIFY `pk_conversationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_message`
--
ALTER TABLE `chat_message`
  MODIFY `pk_messageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_message_attachment`
--
ALTER TABLE `chat_message_attachment`
  MODIFY `pk_fileID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `pk_collectionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verification`
--
ALTER TABLE `email_verification`
  MODIFY `pk_verificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurement`
--
ALTER TABLE `measurement`
  MODIFY `pk_measurementID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `pk_notificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ownership_history`
--
ALTER TABLE `ownership_history`
  MODIFY `pk_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `pk_resetID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `pk_requestID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slot`
--
ALTER TABLE `slot`
  MODIFY `pk_sampleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_post`
--
ALTER TABLE `admin_post`
  ADD CONSTRAINT `admin_post_ibfk_1` FOREIGN KEY (`fk_author`) REFERENCES `user` (`pk_username`) ON DELETE SET NULL;

--
-- Constraints for table `chat_conversation`
--
ALTER TABLE `chat_conversation`
  ADD CONSTRAINT `chat_conversation_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `user` (`pk_username`) ON DELETE SET NULL;

--
-- Constraints for table `chat_message_attachment`
--
ALTER TABLE `chat_message_attachment`
  ADD CONSTRAINT `fk_chat_message` FOREIGN KEY (`fk_message`) REFERENCES `chat_message` (`pk_messageID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
