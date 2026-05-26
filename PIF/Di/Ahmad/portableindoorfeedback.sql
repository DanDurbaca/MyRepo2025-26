-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: portableindoorfeedback
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
create database portableindoorfeedback;
use portableindoorfeedback;
--
-- Table structure for table `collection`
--

DROP TABLE IF EXISTS `collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collection` (
  `pk_collection` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `fk_user_creates` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `start_timestamp` datetime NOT NULL,
  `end_timestamp` datetime NOT NULL,
  `fk_station_source` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`pk_collection`),
  KEY `fkc_user_creates` (`fk_user_creates`),
  KEY `fkc_station_source` (`fk_station_source`),
  CONSTRAINT `fkc_station_source` FOREIGN KEY (`fk_station_source`) REFERENCES `station` (`pk_serialNumber`) ON DELETE CASCADE,
  CONSTRAINT `fkc_user_creates` FOREIGN KEY (`fk_user_creates`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collection`
--

LOCK TABLES `collection` WRITE;
/*!40000 ALTER TABLE `collection` DISABLE KEYS */;
INSERT INTO `collection` VALUES (1,'testCollection',NULL,'a','2026-01-13 09:22:00','2026-01-14 09:22:00','SN-999'),(2,'testRename',NULL,'a','2026-01-13 09:22:00','2026-01-14 09:22:00','SN-999');
/*!40000 ALTER TABLE `collection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friend_request`
--

DROP TABLE IF EXISTS `friend_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `friend_request` (
  `sender` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `receiver` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`sender`,`receiver`),
  KEY `fkc_receiver` (`receiver`),
  CONSTRAINT `fkc_receiver` FOREIGN KEY (`receiver`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE,
  CONSTRAINT `fkc_sender` FOREIGN KEY (`sender`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friend_request`
--

LOCK TABLES `friend_request` WRITE;
/*!40000 ALTER TABLE `friend_request` DISABLE KEYS */;
INSERT INTO `friend_request` VALUES ('a','testuser');
/*!40000 ALTER TABLE `friend_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hasaccess`
--

DROP TABLE IF EXISTS `hasaccess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hasaccess` (
  `fk_collection` int NOT NULL,
  `fk_user_recipient` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`fk_collection`,`fk_user_recipient`),
  KEY `fkc_user_share` (`fk_user_recipient`),
  CONSTRAINT `fkc_coll_share` FOREIGN KEY (`fk_collection`) REFERENCES `collection` (`pk_collection`) ON DELETE CASCADE,
  CONSTRAINT `fkc_user_share` FOREIGN KEY (`fk_user_recipient`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hasaccess`
--

LOCK TABLES `hasaccess` WRITE;
/*!40000 ALTER TABLE `hasaccess` DISABLE KEYS */;
INSERT INTO `hasaccess` VALUES (2,'testuser1');
/*!40000 ALTER TABLE `hasaccess` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `isfriend`
--

DROP TABLE IF EXISTS `isfriend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `isfriend` (
  `pkfk_user_user` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `pkfk_user_friend` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`pkfk_user_user`,`pkfk_user_friend`),
  KEY `fkc_friend` (`pkfk_user_friend`),
  CONSTRAINT `fkc_friend` FOREIGN KEY (`pkfk_user_friend`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE,
  CONSTRAINT `fkc_user` FOREIGN KEY (`pkfk_user_user`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `isfriend`
--

LOCK TABLES `isfriend` WRITE;
/*!40000 ALTER TABLE `isfriend` DISABLE KEYS */;
INSERT INTO `isfriend` VALUES ('testuser1','a'),('a','testuser1');
/*!40000 ALTER TABLE `isfriend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `measurement`
--

DROP TABLE IF EXISTS `measurement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `measurement` (
  `pk_measurement` int NOT NULL AUTO_INCREMENT,
  `temperature` decimal(5,2) NOT NULL,
  `humidity` decimal(5,2) NOT NULL,
  `pressure` decimal(6,2) NOT NULL,
  `light` decimal(6,2) NOT NULL,
  `gas` decimal(6,2) NOT NULL,
  `timestamp` datetime NOT NULL,
  `fk_station_records` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`pk_measurement`),
  KEY `fkc_station_records_measurement` (`fk_station_records`),
  CONSTRAINT `fkc_station_records_measurement` FOREIGN KEY (`fk_station_records`) REFERENCES `station` (`pk_serialNumber`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `measurement`
--

LOCK TABLES `measurement` WRITE;
/*!40000 ALTER TABLE `measurement` DISABLE KEYS */;
/*!40000 ALTER TABLE `measurement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `station`
--

DROP TABLE IF EXISTS `station`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station` (
  `pk_serialNumber` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `fk_user_owns` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`pk_serialNumber`),
  KEY `fkc_user_owns_station` (`fk_user_owns`),
  CONSTRAINT `fkc_user_owns_station` FOREIGN KEY (`fk_user_owns`) REFERENCES `user` (`pk_username`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `station`
--

LOCK TABLES `station` WRITE;
/*!40000 ALTER TABLE `station` DISABLE KEYS */;
INSERT INTO `station` VALUES ('SN-001','Main Lab','Test Station','testuser'),('SN-123','stationD',NULL,'d'),('SN-999','New Station','Waiting for user','a');
/*!40000 ALTER TABLE `station` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `pk_username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `firstName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('User','Admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'User',
  PRIMARY KEY (`pk_username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('a','a','a','$2y$10$gqSyCPDhK/.7DWgBsclfk.f9GXRw6KJ.lFzDJLaMH3BcrzrReBgWa','a@a.com','Admin'),('d','Admin','User','$2y$10$T6vQphl/EywtLdAiqsGZ2u69tZWf5URRa70sW.McY6x8xL4DUQ1hO','admin@example.com','Admin'),('testuser','John','Doe','$2y0K9p6yVp5P4U.g3.vXzNHe1A6uY4z2fX1lR5t9y7u8v9w0x1y2z3','test@example.com','User'),('testuser1','testuser1','testuser1','$2y$10$ajJQhL43vAwehn3cpAG09e9pUm3oLsgCEXlIvV0ly5TEaRnRuD2AS','testuser1@testuser1','User');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-16  8:29:14
