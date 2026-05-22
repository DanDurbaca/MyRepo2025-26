-- Extended PIF schema additions
-- Run this AFTER importing portableindoorfeedback.sql

USE `portableindoorfeedback`;

-- Friend request table (used to track pending/accepted/declined requests
-- before promoting to a row in `isfriend`).
CREATE TABLE IF NOT EXISTS `friendrequest` (
  `pk_id`        INT NOT NULL AUTO_INCREMENT,
  `fk_sender`    VARCHAR(50) NOT NULL,
  `fk_receiver`  VARCHAR(50) NOT NULL,
  `status`       ENUM('pending','accepted','declined') DEFAULT 'pending',
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pk_id`),
  UNIQUE KEY `uniq_request` (`fk_sender`, `fk_receiver`),
  FOREIGN KEY (`fk_sender`)   REFERENCES `user`(`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`fk_receiver`) REFERENCES `user`(`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Chat messages between friends
CREATE TABLE IF NOT EXISTS `message` (
  `pk_id`       INT NOT NULL AUTO_INCREMENT,
  `fk_sender`   VARCHAR(50) NOT NULL,
  `fk_receiver` VARCHAR(50) NOT NULL,
  `body`        TEXT NOT NULL,
  `sent_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_read`     TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`pk_id`),
  KEY `idx_chat` (`fk_sender`,`fk_receiver`,`sent_at`),
  KEY `idx_unread` (`fk_receiver`,`is_read`),
  FOREIGN KEY (`fk_sender`)   REFERENCES `user`(`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`fk_receiver`) REFERENCES `user`(`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- In-app notifications (friend requests, share, chat message)
CREATE TABLE IF NOT EXISTS `notification` (
  `pk_id`      INT NOT NULL AUTO_INCREMENT,
  `fk_user`    VARCHAR(50) NOT NULL,
  `type`       VARCHAR(50) NOT NULL,
  `message`    TEXT NOT NULL,
  `link`       VARCHAR(255) DEFAULT NULL,
  `is_read`    TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pk_id`),
  KEY `idx_user_unread` (`fk_user`,`is_read`,`created_at`),
  FOREIGN KEY (`fk_user`) REFERENCES `user`(`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Invitation links to invite new users (one-time tokens).
CREATE TABLE IF NOT EXISTS `invite` (
  `pk_token`    VARCHAR(64) NOT NULL,
  `fk_creator`  VARCHAR(50) NOT NULL,
  `used_by`     VARCHAR(50) DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expires_at`  DATETIME DEFAULT NULL,
  PRIMARY KEY (`pk_token`),
  FOREIGN KEY (`fk_creator`) REFERENCES `user`(`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- User preferences: theme + interface language
ALTER TABLE `user`
  ADD COLUMN IF NOT EXISTS `theme`    ENUM('dark','light') DEFAULT 'dark',
  ADD COLUMN IF NOT EXISTS `language` ENUM('en','uk','lb') DEFAULT 'en';
