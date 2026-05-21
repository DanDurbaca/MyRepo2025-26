-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 03, 2026 at 09:02 AM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portableindoorfeedback`
--


CREATE DATABASE IF NOT EXISTS `portableindoorfeedback` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `portableindoorfeedback`;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `pk_collection` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `fk_user_creates` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection`
--

INSERT INTO `collection` (`pk_collection`, `name`, `description`, `fk_user_creates`) VALUES
(3, 'collection 1', '', 'admin'),
(4, 'collection 1', '', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `contains`
--

CREATE TABLE `contains` (
  `pkfk_collection` int NOT NULL,
  `pkfk_measurement` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contains`
--

-- --------------------------------------------------------

--
-- Table structure for table `hasaccess`
--

CREATE TABLE `hasaccess` (
  `pkfk_user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pkfk_collection` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `isfriend`
--

CREATE TABLE `isfriend` (
  `pkfk_user_user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pkfk_user_friend` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `isfriend`
--

INSERT INTO `isfriend` (`pkfk_user_user`, `pkfk_user_friend`) VALUES
('admin', 'testfriend');

-- --------------------------------------------------------

--
-- Table structure for table `measurement`
--

CREATE TABLE `measurement` (
  `pk_measurement` int NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `humidity` decimal(5,2) NOT NULL,
  `pressure` decimal(6,2) NOT NULL,
  `light` decimal(6,2) NOT NULL,
  `gas` decimal(6,2) NOT NULL,
  `timestamp` datetime NOT NULL,
  `fk_station_records` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `measurement`
--

INSERT INTO `measurement` (`pk_measurement`, `temperature`, `humidity`, `pressure`, `light`, `gas`, `timestamp`, `fk_station_records`) VALUES
(9055, 25.35, 0.00, 985.51, 910.00, 1598.00, '2026-04-29 10:58:13', 'STAMA314'),
(9056, 25.35, 0.00, 985.50, 914.17, 1596.00, '2026-04-29 10:58:15', 'STAMA314'),
(9057, 25.36, 0.00, 985.51, 915.83, 1589.00, '2026-04-29 10:58:17', 'STAMA314'),
(9058, 25.37, 0.00, 985.51, 920.83, 1601.00, '2026-04-29 10:58:19', 'STAMA314'),
(9059, 25.37, 0.00, 985.50, 924.17, 1601.00, '2026-04-29 10:58:21', 'STAMA314'),
(9060, 25.37, 0.00, 985.50, 928.33, 1604.00, '2026-04-29 10:58:24', 'STAMA314'),
(9061, 25.37, 0.00, 985.50, 935.83, 1601.00, '2026-04-29 10:58:26', 'STAMA314'),
(9062, 25.38, 0.00, 985.50, 940.83, 1601.00, '2026-04-29 10:58:28', 'STAMA314'),
(9063, 25.38, 0.00, 985.51, 945.00, 1581.00, '2026-04-29 10:58:30', 'STAMA314'),
(9064, 25.39, 0.00, 985.52, 942.50, 1600.00, '2026-04-29 10:58:32', 'STAMA314'),
(9065, 25.39, 0.00, 985.55, 943.33, 1603.00, '2026-04-29 10:58:34', 'STAMA314'),
(9066, 25.39, 0.00, 985.58, 955.83, 1595.00, '2026-04-29 10:58:36', 'STAMA314'),
(9067, 25.39, 0.00, 985.59, 961.67, 1598.00, '2026-04-29 10:58:38', 'STAMA314'),
(9068, 25.39, 0.00, 985.58, 963.33, 1602.00, '2026-04-29 10:58:40', 'STAMA314');

-- --------------------------------------------------------

--
-- Table structure for table `station`
--

CREATE TABLE `station` (
  `pk_serialNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `fk_user_owns` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `station`
--

INSERT INTO `station` (`pk_serialNumber`, `name`, `description`, `fk_user_owns`) VALUES
('SN-1001', 'Server Room', 'Tracks temperature and humidity', 'admin'),
('SN-1002', 'Lab', 'Experimental sensor', NULL),
('SN-1003', 'Cafeteria', 'Indoor air quality', NULL),
('SN-1004', 'Main Office', 'CO2 and occupancy sensor', NULL),
('SN-1005', 'Conference Room A', 'Air quality monitoring', NULL),
('SN-1006', 'Conference Room B', 'Air quality monitoring', NULL),
('SN-1007', 'Break Room', 'Monitors CO2 and humidity', NULL),
('SN-1008', 'Hallway 1', 'Motion and temperature', NULL),
('SN-1009', 'Hallway 2', 'Motion and temperature', NULL),
('SN-1010', 'Warehouse', 'Humidity and temperature monitoring', NULL),
('SN-1011', 'Loading Dock', 'Temperature and motion monitoring', NULL),
('SN-1012', 'Lobby', 'Temperature and motion sensor', NULL),
('SN-1013', 'Printer Room', 'Temperature monitoring', NULL),
('SN-1014', 'Server Room 2', 'Tracks temperature and humidity', NULL),
('SN-1015', 'Training Room', 'Temperature and light', NULL),
('SN-1016', 'Office 1', 'Temperature and light monitoring', NULL),
('SN-1017', 'Office 2', 'Tracks occupancy and air quality', NULL),
('SN-1018', 'Conference North', 'CO2 and occupancy sensor', NULL),
('SN-1019', 'Conference South', 'Tracks air quality', NULL),
('SN-1020', 'Main Kitchen', 'Air quality and CO2 sensor', NULL),
('STAMA314', 'My Station', '', 'stama314');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `pk_username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `firstName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('User','Admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'User',
  `theme` enum('light','dark') NOT NULL DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`pk_username`, `firstName`, `lastName`, `password`, `email`, `role`, `theme`) VALUES
('admin', 'Admin', '.', '123', 'admin@example.com', 'Admin', 'light'),
('stama314', 'Maciej', 'Stankowski', '123', 'mjstankowski213@gmail.com', 'User', 'light'),
('testfriend', 'Test', 'Friend', 'password', 'test@example.com', 'User', 'light');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`pk_collection`),
  ADD KEY `fkc_user_creates_collection` (`fk_user_creates`);

--
-- Indexes for table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`pkfk_collection`,`pkfk_measurement`),
  ADD KEY `fkc_contains_measurement` (`pkfk_measurement`);

--
-- Indexes for table `hasaccess`
--
ALTER TABLE `hasaccess`
  ADD PRIMARY KEY (`pkfk_user`,`pkfk_collection`),
  ADD KEY `fkc_hasaccess_collection` (`pkfk_collection`);

--
-- Indexes for table `isfriend`
--
ALTER TABLE `isfriend`
  ADD PRIMARY KEY (`pkfk_user_user`,`pkfk_user_friend`),
  ADD KEY `fkc_isfriend_friend` (`pkfk_user_friend`);

--
-- Indexes for table `measurement`
--
ALTER TABLE `measurement`
  ADD PRIMARY KEY (`pk_measurement`),
  ADD KEY `fkc_station_records_measurement` (`fk_station_records`);

--
-- Indexes for table `station`
--
ALTER TABLE `station`
  ADD PRIMARY KEY (`pk_serialNumber`),
  ADD KEY `fkc_user_owns_station` (`fk_user_owns`);

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
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `pk_collection` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `measurement`
--
ALTER TABLE `measurement`
  MODIFY `pk_measurement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9069;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `collection`
--
ALTER TABLE `collection`
  ADD CONSTRAINT `fkc_user_creates_collection` FOREIGN KEY (`fk_user_creates`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `fkc_contains_collection` FOREIGN KEY (`pkfk_collection`) REFERENCES `collection` (`pk_collection`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkc_contains_measurement` FOREIGN KEY (`pkfk_measurement`) REFERENCES `measurement` (`pk_measurement`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hasaccess`
--
ALTER TABLE `hasaccess`
  ADD CONSTRAINT `fkc_hasaccess_collection` FOREIGN KEY (`pkfk_collection`) REFERENCES `collection` (`pk_collection`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkc_hasaccess_user` FOREIGN KEY (`pkfk_user`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `isfriend`
--
ALTER TABLE `isfriend`
  ADD CONSTRAINT `fkc_isfriend_friend` FOREIGN KEY (`pkfk_user_friend`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkc_isfriend_user` FOREIGN KEY (`pkfk_user_user`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `measurement`
--
ALTER TABLE `measurement`
  ADD CONSTRAINT `fkc_station_records_measurement` FOREIGN KEY (`fk_station_records`) REFERENCES `station` (`pk_serialNumber`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `station`
--
ALTER TABLE `station`
  ADD CONSTRAINT `fkc_user_owns_station` FOREIGN KEY (`fk_user_owns`) REFERENCES `user` (`pk_username`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
