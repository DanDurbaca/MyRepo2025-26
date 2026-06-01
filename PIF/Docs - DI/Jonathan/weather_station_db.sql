-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 09:42 AM
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
-- Database: `weather_station_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `alert`
--

CREATE TABLE `alert` (
  `alert_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `alert_type` enum('threshold','maintenance','offline','battery') NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `triggered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `severity` enum('info','warning','critical') DEFAULT 'warning'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `collection_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_public` tinyint(1) DEFAULT 0,
  `last_updated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `collection_type` enum('personal','research','comparative','temporal') DEFAULT 'personal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection`
--

INSERT INTO `collection` (`collection_id`, `name`, `description`, `created_by`, `created_at`, `is_public`, `last_updated`, `collection_type`) VALUES
(1, 'Morning Data', 'Daily morning measurements analysis', 2, '2025-12-02 08:22:26', 0, NULL, 'personal'),
(2, 'Weekend Analysis', 'Weekend environmental patterns', 2, '2025-12-02 08:22:26', 0, NULL, 'personal'),
(3, 'Air Quality Study', 'Comparative air quality research', 3, '2025-12-02 08:22:26', 0, NULL, 'personal'),
(4, 'Empty Collection', 'For future measurements', 4, '2025-12-02 08:22:26', 0, NULL, 'personal');

-- --------------------------------------------------------

--
-- Table structure for table `collection_measurement`
--

CREATE TABLE `collection_measurement` (
  `collection_id` int(11) NOT NULL,
  `measurement_id` bigint(20) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `added_by` int(11) NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection_measurement`
--

INSERT INTO `collection_measurement` (`collection_id`, `measurement_id`, `added_at`, `added_by`, `note`) VALUES
(1, 1, '2025-12-02 08:22:26', 2, NULL),
(1, 4, '2025-12-02 08:22:26', 2, NULL),
(2, 2, '2025-12-02 08:22:26', 2, NULL),
(2, 5, '2025-12-02 08:22:26', 2, NULL),
(3, 1, '2025-12-02 08:22:26', 3, NULL),
(3, 4, '2025-12-02 08:22:26', 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `collection_sharing`
--

CREATE TABLE `collection_sharing` (
  `collection_id` int(11) NOT NULL,
  `shared_with` int(11) NOT NULL,
  `shared_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `permission_level` enum('view','edit','manage') DEFAULT 'view',
  `shared_by` int(11) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection_sharing`
--

INSERT INTO `collection_sharing` (`collection_id`, `shared_with`, `shared_at`, `permission_level`, `shared_by`, `expires_at`) VALUES
(1, 3, '2025-12-02 08:22:26', 'view', 2, NULL),
(1, 4, '2025-12-02 08:22:26', 'edit', 2, NULL),
(3, 2, '2025-12-02 08:22:26', 'view', 3, NULL),
(3, 4, '2025-12-02 08:22:26', 'view', 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `friendship`
--

CREATE TABLE `friendship` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `friendship_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','accepted','blocked') DEFAULT 'pending',
  `last_interaction` timestamp NULL DEFAULT NULL
) ;

--
-- Dumping data for table `friendship`
--

INSERT INTO `friendship` (`user_id`, `friend_id`, `friendship_date`, `status`, `last_interaction`) VALUES
(2, 3, '2025-12-02 08:22:26', 'accepted', NULL),
(2, 4, '2025-12-02 08:22:26', 'pending', NULL),
(3, 2, '2025-12-02 08:22:26', 'accepted', NULL),
(3, 4, '2025-12-02 08:22:26', 'accepted', NULL),
(4, 3, '2025-12-02 08:22:26', 'accepted', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_log`
--

CREATE TABLE `maintenance_log` (
  `log_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `maintenance_date` date NOT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `maintenance_type` enum('calibration','cleaning','repair','battery') NOT NULL,
  `description` text DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement`
--

CREATE TABLE `measurement` (
  `measurement_id` bigint(20) NOT NULL,
  `station_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `temperature` decimal(5,2) NOT NULL COMMENT 'Temperature in °C',
  `humidity` decimal(5,2) NOT NULL COMMENT 'Humidity in %',
  `air_pressure` decimal(7,2) NOT NULL COMMENT 'Air pressure in hPa',
  `light_intensity` int(11) NOT NULL COMMENT 'Light intensity in lux',
  `air_quality` int(11) NOT NULL COMMENT 'Air quality in ppm (CO2 equivalent)',
  `battery_level` decimal(4,1) DEFAULT NULL COMMENT 'Battery level in %',
  `wind_speed` decimal(5,2) DEFAULT NULL COMMENT 'Wind speed in m/s',
  `rainfall` decimal(6,2) DEFAULT NULL COMMENT 'Rainfall in mm',
  `measurement_status` enum('valid','warning','error') DEFAULT 'valid'
) ;

--
-- Dumping data for table `measurement`
--

INSERT INTO `measurement` (`measurement_id`, `station_id`, `timestamp`, `temperature`, `humidity`, `air_pressure`, `light_intensity`, `air_quality`, `battery_level`, `wind_speed`, `rainfall`, `measurement_status`) VALUES
(1, 1, '2025-01-15 07:00:00', 22.50, 45.20, 1013.20, 350, 450, 85.5, NULL, NULL, 'valid'),
(2, 1, '2025-01-15 08:00:00', 23.10, 44.80, 1013.00, 420, 460, 85.0, NULL, NULL, 'valid'),
(3, 1, '2025-01-15 09:00:00', 23.80, 43.50, 1012.80, 510, 470, 84.5, NULL, NULL, 'valid'),
(4, 2, '2025-01-15 07:00:00', 18.20, 65.30, 1013.10, 1250, 380, 92.0, NULL, NULL, 'valid'),
(5, 2, '2025-01-15 08:00:00', 19.50, 62.10, 1012.90, 1350, 390, 91.5, NULL, NULL, 'valid');

-- --------------------------------------------------------

--
-- Table structure for table `station`
--

CREATE TABLE `station` (
  `station_id` int(11) NOT NULL,
  `serial_number` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `owned_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `elevation` decimal(6,2) DEFAULT NULL COMMENT 'Elevation in meters',
  `indoor_station` tinyint(1) DEFAULT 0,
  `last_maintenance` date DEFAULT NULL
) ;

--
-- Dumping data for table `station`
--

INSERT INTO `station` (`station_id`, `serial_number`, `name`, `description`, `created_by`, `owned_by`, `created_at`, `is_active`, `latitude`, `longitude`, `elevation`, `indoor_station`, `last_maintenance`) VALUES
(1, 'SN-1001', 'Living Room Monitor', 'Main living area climate station', 1, 2, '2025-12-02 08:22:26', 1, NULL, NULL, NULL, 0, NULL),
(2, 'SN-1002', 'Garden Station', 'Outdoor weather monitoring', 1, 2, '2025-12-02 08:22:26', 1, NULL, NULL, NULL, 0, NULL),
(3, 'SN-1003', 'Bedroom Air Quality', 'Sleep environment monitor', 1, 3, '2025-12-02 08:22:26', 1, NULL, NULL, NULL, 0, NULL),
(4, 'SN-1004', 'Office Climate', 'Work environment monitoring', 1, 4, '2025-12-02 08:22:26', 1, NULL, NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `station_config`
--

CREATE TABLE `station_config` (
  `config_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `measurement_interval` int(11) DEFAULT 300 COMMENT 'Interval in seconds',
  `data_retention_days` int(11) DEFAULT 365,
  `temperature_threshold_min` decimal(5,2) DEFAULT NULL,
  `temperature_threshold_max` decimal(5,2) DEFAULT NULL,
  `air_quality_threshold` int(11) DEFAULT NULL,
  `battery_warning_level` decimal(4,1) DEFAULT 20.0,
  `last_config_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('admin','regular') DEFAULT 'regular',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `full_name`, `password_hash`, `user_type`, `created_at`, `last_login`, `is_active`) VALUES
(1, 'admin_john', 'john.admin@example.com', 'John Admin', '$2y$10$ABC123...', 'admin', '2025-12-02 08:22:26', NULL, 1),
(2, 'alice_smith', 'alice@example.com', 'Alice Smith', '$2y$10$DEF456...', 'regular', '2025-12-02 08:22:26', NULL, 1),
(3, 'bob_jones', 'bob@example.com', 'Bob Jones', '$2y$10$GHI789...', 'regular', '2025-12-02 08:22:26', NULL, 1),
(4, 'carol_white', 'carol@example.com', 'Carol White', '$2y$10$JKL012...', 'regular', '2025-12-02 08:22:26', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alert`
--
ALTER TABLE `alert`
  ADD PRIMARY KEY (`alert_id`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_triggered_at` (`triggered_at`);

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`collection_id`),
  ADD UNIQUE KEY `uq_user_collection_name` (`created_by`,`name`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_is_public` (`is_public`);

--
-- Indexes for table `collection_measurement`
--
ALTER TABLE `collection_measurement`
  ADD PRIMARY KEY (`collection_id`,`measurement_id`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `idx_measurement_id` (`measurement_id`),
  ADD KEY `idx_added_at` (`added_at`);

--
-- Indexes for table `collection_sharing`
--
ALTER TABLE `collection_sharing`
  ADD PRIMARY KEY (`collection_id`,`shared_with`),
  ADD KEY `shared_by` (`shared_by`),
  ADD KEY `idx_shared_with` (`shared_with`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `friendship`
--
ALTER TABLE `friendship`
  ADD PRIMARY KEY (`user_id`,`friend_id`),
  ADD KEY `idx_friend_id` (`friend_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `maintenance_log`
--
ALTER TABLE `maintenance_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `idx_station_date` (`station_id`,`maintenance_date`);

--
-- Indexes for table `measurement`
--
ALTER TABLE `measurement`
  ADD PRIMARY KEY (`measurement_id`),
  ADD KEY `idx_station_timestamp` (`station_id`,`timestamp`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_air_quality` (`air_quality`);

--
-- Indexes for table `station`
--
ALTER TABLE `station`
  ADD PRIMARY KEY (`station_id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `idx_serial_number` (`serial_number`),
  ADD KEY `idx_owned_by` (`owned_by`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `station_config`
--
ALTER TABLE `station_config`
  ADD PRIMARY KEY (`config_id`),
  ADD UNIQUE KEY `station_id` (`station_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alert`
--
ALTER TABLE `alert`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `maintenance_log`
--
ALTER TABLE `maintenance_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurement`
--
ALTER TABLE `measurement`
  MODIFY `measurement_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `station`
--
ALTER TABLE `station`
  MODIFY `station_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `station_config`
--
ALTER TABLE `station_config`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alert`
--
ALTER TABLE `alert`
  ADD CONSTRAINT `alert_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alert_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE SET NULL;

--
-- Constraints for table `collection`
--
ALTER TABLE `collection`
  ADD CONSTRAINT `collection_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `collection_measurement`
--
ALTER TABLE `collection_measurement`
  ADD CONSTRAINT `collection_measurement_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_measurement_ibfk_2` FOREIGN KEY (`measurement_id`) REFERENCES `measurement` (`measurement_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_measurement_ibfk_3` FOREIGN KEY (`added_by`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `collection_sharing`
--
ALTER TABLE `collection_sharing`
  ADD CONSTRAINT `collection_sharing_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_sharing_ibfk_2` FOREIGN KEY (`shared_with`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_sharing_ibfk_3` FOREIGN KEY (`shared_by`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `friendship`
--
ALTER TABLE `friendship`
  ADD CONSTRAINT `friendship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendship_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_log`
--
ALTER TABLE `maintenance_log`
  ADD CONSTRAINT `maintenance_log_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_log_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `measurement`
--
ALTER TABLE `measurement`
  ADD CONSTRAINT `measurement_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE;

--
-- Constraints for table `station`
--
ALTER TABLE `station`
  ADD CONSTRAINT `station_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `station_ibfk_2` FOREIGN KEY (`owned_by`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `station_config`
--
ALTER TABLE `station_config`
  ADD CONSTRAINT `station_config_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `station` (`station_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
