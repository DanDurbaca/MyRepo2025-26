-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 08, 2025 at 08:25 AM
-- Server version: 8.0.31
-- PHP Version: 8.2.0

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
  `name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `fk_user_creates` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contains`
--

CREATE TABLE `contains` (
  `pkfk_collection` int NOT NULL,
  `pkfk_measurement` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasaccess`
--

CREATE TABLE `hasaccess` (
  `pkfk_user` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `pkfk_collection` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `isfriend`
--

CREATE TABLE `isfriend` (
  `pkfk_user_user` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `pkfk_user_friend` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `fk_station_records` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `station`
--

CREATE TABLE `station` (
  `pk_serialNumber` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `fk_user_owns` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `pk_username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `firstName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('User','Admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `pk_collection` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurement`
--
ALTER TABLE `measurement`
  MODIFY `pk_measurement` int NOT NULL AUTO_INCREMENT;

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
