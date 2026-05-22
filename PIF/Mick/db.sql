-- Simple webapp schema for WEBAP2_2026
-- Create database and tables. Import this file into phpMyAdmin or run with mysql client.

-- Fresh rotated schema for EnvWatch (sensor-focused), database kept as `webapp`.
-- Import this file into phpMyAdmin or run with the mysql client while connected to your server.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `webapp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `webapp`;

-- Drop rotated tables if present so import is clean (safe for fresh import)
DROP TABLE IF EXISTS `env_friend_request`;
DROP TABLE IF EXISTS `env_friend`;
DROP TABLE IF EXISTS `env_access`;
DROP TABLE IF EXISTS `env_contains`;
DROP TABLE IF EXISTS `env_collection`;
DROP TABLE IF EXISTS `env_record`;
DROP TABLE IF EXISTS `env_station`;
DROP TABLE IF EXISTS `env_user`;

-- Table: env_user (rotated 'user')
CREATE TABLE `env_user` (
  `usr_name` varchar(50) NOT NULL,
  `usr_first` varchar(50) NOT NULL,
  `usr_last` varchar(50) NOT NULL,
  `usr_pwd` varchar(255) NOT NULL,
  `usr_email` varchar(100) NOT NULL,
  `usr_role` enum('User','Admin') NOT NULL DEFAULT 'User',
  `usr_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usr_name`),
  UNIQUE KEY `uniq_usr_email` (`usr_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_station (rotated 'station')
CREATE TABLE `env_station` (
  `st_serial` varchar(50) NOT NULL,
  `st_label` varchar(100) DEFAULT NULL,
  `st_description` text,
  `st_owner` varchar(50) DEFAULT NULL,
  `st_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`st_serial`),
  KEY `idx_st_owner` (`st_owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_record (rotated 'measurement')
CREATE TABLE `env_record` (
  `rec_id` int NOT NULL AUTO_INCREMENT,
  `rec_temperature` decimal(6,2) DEFAULT NULL,
  `rec_humidity` decimal(6,2) DEFAULT NULL,
  `rec_pressure` decimal(7,2) DEFAULT NULL,
  `rec_light` decimal(7,2) DEFAULT NULL,
  `rec_gas` decimal(7,2) DEFAULT NULL,
  `rec_timestamp` datetime DEFAULT NULL,
  `rec_station` varchar(50) DEFAULT NULL,
  `rec_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rec_id`),
  KEY `idx_rec_station` (`rec_station`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_collection (rotated 'collection')
CREATE TABLE `env_collection` (
  `col_id` int NOT NULL AUTO_INCREMENT,
  `col_name` varchar(80) DEFAULT NULL,
  `col_description` text,
  `col_owner` varchar(50) NOT NULL,
  `col_station` varchar(50) DEFAULT NULL,
  `col_start` datetime DEFAULT NULL,
  `col_end` datetime DEFAULT NULL,
  `col_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`col_id`),
  KEY `idx_col_owner` (`col_owner`),
  KEY `idx_col_station` (`col_station`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_contains (rotated 'contains')
CREATE TABLE `env_contains` (
  `col_ref` int NOT NULL,
  `rec_ref` int NOT NULL,
  PRIMARY KEY (`col_ref`,`rec_ref`),
  KEY `idx_contains_rec` (`rec_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_access (rotated 'hasaccess')
CREATE TABLE `env_access` (
  `usr_ref` varchar(50) NOT NULL,
  `col_ref` int NOT NULL,
  PRIMARY KEY (`usr_ref`,`col_ref`),
  KEY `idx_access_col` (`col_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_friend (rotated 'isfriend')
CREATE TABLE `env_friend` (
  `usr_main` varchar(50) NOT NULL,
  `usr_friend` varchar(50) NOT NULL,
  PRIMARY KEY (`usr_main`,`usr_friend`),
  KEY `idx_friend` (`usr_friend`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_friend_request (new) - friend request workflow
CREATE TABLE `env_friend_request` (
  `req_id` int NOT NULL AUTO_INCREMENT,
  `req_from` varchar(50) NOT NULL,
  `req_to` varchar(50) NOT NULL,
  `req_status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `req_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`req_id`),
  UNIQUE KEY `uniq_request` (`req_from`,`req_to`),
  KEY `idx_req_to` (`req_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_user_settings (new) - user preferences (theme, language)
CREATE TABLE `env_user_settings` (
  `usr_ref` varchar(50) NOT NULL,
  `theme` varchar(20) DEFAULT 'light',
  `language` varchar(10) DEFAULT 'en',
  `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`usr_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_notification (new) - user notifications
CREATE TABLE `env_notification` (
  `notif_id` int NOT NULL AUTO_INCREMENT,
  `notif_to` varchar(50) NOT NULL,
  `notif_type` varchar(50) NOT NULL,
  `notif_title` varchar(255),
  `notif_message` text,
  `notif_related_user` varchar(50),
  `notif_related_item` int,
  `notif_read` tinyint DEFAULT 0,
  `notif_email_sent` tinyint DEFAULT 0,
  `notif_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notif_id`),
  KEY `idx_notif_to` (`notif_to`),
  KEY `idx_notif_type` (`notif_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: env_chat_message (new) - direct messages between users
CREATE TABLE `env_chat_message` (
  `msg_id` int NOT NULL AUTO_INCREMENT,
  `msg_from` varchar(50) NOT NULL,
  `msg_to` varchar(50) NOT NULL,
  `msg_content` text NOT NULL,
  `msg_read` tinyint DEFAULT 0,
  `msg_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`msg_id`),
  KEY `idx_msg_from` (`msg_from`),
  KEY `idx_msg_to` (`msg_to`),
  KEY `idx_conversation` (`msg_from`, `msg_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Indexes & foreign keys
ALTER TABLE `env_station`
  ADD CONSTRAINT `fk_station_owner` FOREIGN KEY (`st_owner`) REFERENCES `env_user` (`usr_name`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `env_record`
  ADD CONSTRAINT `fk_record_station` FOREIGN KEY (`rec_station`) REFERENCES `env_station` (`st_serial`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `env_collection`
  ADD CONSTRAINT `fk_collection_owner` FOREIGN KEY (`col_owner`) REFERENCES `env_user` (`usr_name`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `env_contains`
  ADD CONSTRAINT `fk_contains_collection` FOREIGN KEY (`col_ref`) REFERENCES `env_collection` (`col_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contains_record` FOREIGN KEY (`rec_ref`) REFERENCES `env_record` (`rec_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `env_access`
  ADD CONSTRAINT `fk_access_collection` FOREIGN KEY (`col_ref`) REFERENCES `env_collection` (`col_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_access_user` FOREIGN KEY (`usr_ref`) REFERENCES `env_user` (`usr_name`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `env_friend`
  ADD CONSTRAINT `fk_friend_user` FOREIGN KEY (`usr_friend`) REFERENCES `env_user` (`usr_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_friend_user_main` FOREIGN KEY (`usr_main`) REFERENCES `env_user` (`usr_name`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `env_user_settings`
  ADD CONSTRAINT `fk_settings_user` FOREIGN KEY (`usr_ref`) REFERENCES `env_user` (`usr_name`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `env_notification`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`notif_to`) REFERENCES `env_user` (`usr_name`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `env_chat_message`
  ADD CONSTRAINT `fk_chat_from` FOREIGN KEY (`msg_from`) REFERENCES `env_user` (`usr_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chat_to` FOREIGN KEY (`msg_to`) REFERENCES `env_user` (`usr_name`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

-- Notes:
-- This is a fresh schema. Import into an empty or backed-up `webapp` database.
-- After importing, register a user via the site and set `usr_role='Admin'` manually if you need an initial admin.
