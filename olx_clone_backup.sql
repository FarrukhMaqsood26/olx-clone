-- MySQL dump 10.13  Distrib 8.3.0, for macos14.2 (x86_64)
--
-- Host: 127.0.0.1    Database: olx_clone
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.28-MariaDB

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

--
-- Table structure for table `ad_images`
--

DROP TABLE IF EXISTS `ad_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ad_id` (`ad_id`),
  CONSTRAINT `ad_images_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_images`
--

LOCK TABLES `ad_images` WRITE;
/*!40000 ALTER TABLE `ad_images` DISABLE KEYS */;
INSERT INTO `ad_images` VALUES (1,101,'https://www.apple.com/newsroom/images/product/iphone/geo/Apple-iPhone-14-Pro-iPhone-14-Pro-Max-deep-purple-220907-geo_inline.jpg.large.jpg',1),(2,102,'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=500&q=60',1),(3,103,'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=500&q=60',1),(4,104,'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?auto=format&fit=crop&w=500&q=60',1),(5,105,'https://images.unsplash.com/photo-1609521263047-f8f205293f24?auto=format&fit=crop&w=500&q=60',1),(6,106,'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=500&q=60',1),(7,107,'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=500&q=60',1),(8,108,'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=500&q=60',1);
/*!40000 ALTER TABLE `ad_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ads`
--

DROP TABLE IF EXISTS `ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `condition_type` enum('new','used') NOT NULL DEFAULT 'used',
  `location` varchar(100) NOT NULL,
  `status` enum('active','pending','sold','rejected') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ads_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ads`
--

LOCK TABLES `ads` WRITE;
/*!40000 ALTER TABLE `ads` DISABLE KEYS */;
INSERT INTO `ads` VALUES (101,999,1,'iPhone 14 Pro Max 256GB - Deep Purple','Brand new condition, PTA approved, 100% battery health. Comes with original box and cable.',320000.00,'used','DHA Phase 5, Lahore','sold','2026-04-22 16:19:26'),(102,999,2,'Honda Civic Oriel 1.8 i-VTEC 2020','First owner car, totally bumper to bumper genuine. 45,000 km driven. Token taxes paid up to date.',6500000.00,'used','F-11, Islamabad','active','2026-04-21 16:19:26'),(103,999,4,'Sony PlayStation 5 Disc Edition + 2 Controllers','Sparingly used PS5. Comes with two dualsense controllers and 3 physical games.',145000.00,'used','Clifton, Karachi','active','2026-04-20 16:19:26'),(104,999,1,'Samsung Galaxy S23 Ultra 512GB','Factory unlocked. Scratchless condition. Only 2 months used.',280000.00,'used','Bahria Town, Rawalpindi','active','2026-04-19 16:19:26'),(105,999,2,'Toyota Corolla Altis Grande 1.8 CVT-i 2022','White color. 100% original paint. Very carefully driven.',7200000.00,'used','Gulberg III, Lahore','active','2026-04-18 16:19:26'),(106,999,4,'Apple MacBook Air M2 13-inch 2022','Space grey, 8GB RAM, 256GB SSD. Battery cycle count is only 20.',265000.00,'used','G-10, Islamabad','active','2026-04-17 16:19:26'),(107,999,3,'10 Marla Plot for Sale in DHA Phase 8','Prime location, corner file, all dues clear. A great investment opportunity.',18000000.00,'new','DHA Phase 8, Lahore','active','2026-04-16 16:19:26'),(108,999,4,'LG 65\" OLED 4K Smart TV','Incredible picture quality. Perfect for gaming and movies. Selling due to upgrading.',450000.00,'used','DHA Phase 2, Karachi','active','2026-04-15 16:19:26'),(110,1006,1,'Test iPhone 14 Pro - QA','This is a test ad for QA purposes.',150000.00,'used','Lahore','active','2026-04-23 03:30:16');
/*!40000 ALTER TABLE `ads` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER tr_AfterAdStatusUpdate
AFTER UPDATE ON ads
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO audit_logs (table_name, record_id, action, old_value, new_value)
        VALUES ('ads', NEW.id, 'status_change', OLD.status, NEW.status);
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` varchar(20) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,'ads',101,'status_change','sold','active',NULL,'2026-04-28 18:18:49'),(2,'ads',101,'status_change','active','sold',NULL,'2026-04-28 18:18:49');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Mobiles','mobiles','fa-mobile-alt'),(2,'Vehicles','vehicles','fa-car'),(3,'Property','property','fa-home'),(4,'Electronics','electronics','fa-tv');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_typing_status`
--

DROP TABLE IF EXISTS `chat_typing_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_typing_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `is_typing` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_partner` (`user_id`,`partner_id`),
  KEY `idx_partner_user` (`partner_id`,`user_id`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_typing_status`
--

LOCK TABLES `chat_typing_status` WRITE;
/*!40000 ALTER TABLE `chat_typing_status` DISABLE KEYS */;
INSERT INTO `chat_typing_status` VALUES (1,1005,1000,0,'2026-04-27 13:08:28'),(3,1000,1005,0,'2026-04-27 13:08:37');
/*!40000 ALTER TABLE `chat_typing_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ad_id` (`ad_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `ad_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT 'text',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `ad_id` (`ad_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,1006,999,NULL,'Hello, is this available?',NULL,'text',0,'2026-04-23 04:01:33'),(2,1006,999,NULL,'hyy',NULL,'text',0,'2026-04-23 04:21:33'),(8,1009,1000,NULL,'hy',NULL,'text',1,'2026-04-25 13:09:35'),(9,1000,1009,NULL,'hello',NULL,'text',1,'2026-04-25 13:09:51'),(10,1009,1000,NULL,'how are you',NULL,'text',1,'2026-04-25 13:10:00'),(11,1005,1000,NULL,'hy I am admin',NULL,'text',1,'2026-04-25 13:10:30'),(12,1000,1005,NULL,'oo nice to meet you',NULL,'text',1,'2026-04-25 13:10:47'),(13,1005,1000,NULL,'','assets/uploads/chat/1777122660_69ecbd648eeb5.png','image',1,'2026-04-25 13:11:00'),(14,1009,1000,NULL,'','assets/uploads/chat/1777122686_69ecbd7ea77e8.webm','audio',1,'2026-04-25 13:11:26'),(15,1005,1000,NULL,'','assets/uploads/chat/1777122715_69ecbd9bb71ba.webm','audio',1,'2026-04-25 13:11:55'),(17,1012,999,NULL,'hy',NULL,'text',0,'2026-04-25 16:24:43'),(18,1012,1000,NULL,'hy',NULL,'text',1,'2026-04-25 16:27:29'),(19,1000,1012,NULL,'hello',NULL,'text',1,'2026-04-25 16:28:48'),(20,1000,1012,NULL,'','assets/uploads/chat/1777134535_69ecebc76029c.webm','audio',1,'2026-04-25 16:28:55'),(21,1012,1000,NULL,'','assets/uploads/chat/1777134557_69ecebdd069d6.png','image',1,'2026-04-25 16:29:17'),(22,1000,999,NULL,'','assets/uploads/chat/1777270717_69eeffbd3443a.png','image',0,'2026-04-27 06:18:37'),(23,1000,999,NULL,'','assets/uploads/chat/1777277234_69ef1932ebb97.webm','audio',0,'2026-04-27 08:07:14'),(24,1000,1005,NULL,'hyy',NULL,'text',1,'2026-04-27 12:58:09'),(25,1005,1000,NULL,'hyyy',NULL,'text',1,'2026-04-27 12:59:02'),(26,1005,1000,NULL,'hel ijdfkjnc osihfe coeif fiehf',NULL,'text',1,'2026-04-27 13:01:47'),(27,1000,1005,NULL,'helnkee',NULL,'text',1,'2026-04-27 13:01:57');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_face_scans`
--

DROP TABLE IF EXISTS `user_face_scans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_face_scans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `capture_type` varchar(40) NOT NULL DEFAULT 'face_mesh',
  `image_path` varchar(255) NOT NULL,
  `mesh_landmarks_json` longtext DEFAULT NULL,
  `capture_angle` enum('front','left','right','up','down') NOT NULL DEFAULT 'front',
  `quality_score` decimal(5,2) DEFAULT NULL,
  `blur_score` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_face_scans_user_id_auth` (`user_id`),
  KEY `idx_user_face_scans_angle_auth` (`capture_angle`),
  CONSTRAINT `fk_user_face_scans_user_auth` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_face_scans`
--

LOCK TABLES `user_face_scans` WRITE;
/*!40000 ALTER TABLE `user_face_scans` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_face_scans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `selector` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `is_email_verified` tinyint(1) DEFAULT 0,
  `face_verification_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `face_verified_at` datetime DEFAULT NULL,
  `face_reviewed_by` int(11) DEFAULT NULL,
  `face_review_notes` text DEFAULT NULL,
  `face_ai_decision` enum('approve','reject','review') DEFAULT NULL,
  `face_ai_reason` varchar(255) DEFAULT NULL,
  `face_ai_liveness_score` decimal(6,4) DEFAULT NULL,
  `face_ai_spoof_score` decimal(6,4) DEFAULT NULL,
  `face_ai_mask_score` decimal(6,4) DEFAULT NULL,
  `face_ai_quality_score` decimal(6,4) DEFAULT NULL,
  `face_ai_raw_json` longtext DEFAULT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `is_face_verified` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1019 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (999,'Test Seller','seller','seller@olx.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user',NULL,0,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default.png','2026-04-22 16:19:26',NULL,NULL,0),(1000,'Arslan','arslannaeem5787','arslannaeem5787@gmail.com','$2y$10$9gL3k20Ai0awNjXNeIKUTeQgyw4OGNHDVkwnencMRfYPhgeuvmE6u','user','03281575712',1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1777123203_69ecbf835c0f6.png','2026-04-22 18:49:48',NULL,NULL,1),(1001,'Test User','testuser_1713840000','testuser_1713840000@example.com','$2y$10$QfYn5rNPDfD3eqKcGtOnGegbXVBvCmxUHiEb4rHF10z1G.ESIxCBS','user','+923001234567',1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default.png','2026-04-22 19:21:50',NULL,NULL,1),(1003,'QA Tester Updated','qa_tester','qa_tester@example.com','$2y$10$CfxTvZi1JR03/WqXfPGMleyHLHi3.1uTkkp/BMAIpfpsPkhHSVCRG','user','+923009876543',1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default.png','2026-04-23 02:24:10',NULL,NULL,1),(1005,'Admin Test','admin','admin@olx.com','$2y$10$14Q/Mggf0ON9qhbg0DaLOennCmD4LYgFGntqdXxDyhjnD09atIfRy','admin','03001234567',1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default.png','2026-04-23 03:10:58',NULL,NULL,1),(1006,'Tester One','tester_one','tester_one@example.com','$2y$10$.v3p8GaChcklLMW4SIA/Tu74wFIH5WZGnZALDu6ZvrCPtyFVDCH86','user','+923001111111',1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default.png','2026-04-23 03:24:39',NULL,NULL,1),(1007,'23242q3','tiurje','tiurje@mf.com','$2y$10$1zPnzXlEqLh/8Z1V2Up8ZeCv4C/B3VJwnngzgOse.GQvuhZmXTRbu','user','324534654321',0,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'a4b7d1703ca6919065b1620e8885733dce05dca03d13446304369e9de9b5d687','default.png','2026-04-24 18:28:23',NULL,NULL,0),(1009,'ali','alii','ali@gmail.com','$2y$10$Z40B0VJQ5lmHcZG63LH5geodnuHDKdqYapKrhIwCqrZmSognwLavW','user','0389438343',1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default.png','2026-04-25 13:08:03',NULL,NULL,1),(1010,'arslan naeem','arslan5757','arslannaeem806@gmail.com','$2y$10$nGuEDkCRaPkEi442vFh60erk/k0EueInMe/9DQltWzsVpWkDo.OEu','user','03093435334',0,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'041992',NULL,'default.png','2026-04-25 13:15:48',NULL,NULL,0),(1011,'arslann12','arslannaeem12','arslandeveloper074@gmail.com','$2y$10$DKORUnmKGWrpq6p.VjVzy.DYAGDYRJbdu.jEso7qzlrFi66TPfd0e','user','0328138743847',1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default.png','2026-04-25 13:31:46',NULL,NULL,1),(1012,'hussain jaffery','hussainjaffery','farrukhmaqsood26@gmail.com','$2y$10$8d11JRAWfaBqNq/SKe/YaeAgfnIhQLscpmzNE1g2/Wmtqr9xZP9IO','user','0302702323',1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1777133905_69ece9510a415.png','2026-04-25 16:17:12',NULL,NULL,1),(1013,'Arslan Developer','arslandeveloper81','developer5787@gmail.com','$2y$10$9wqZEePnEZqxclJRRuv/dOC3IIfDBqJy4wx/JnKMGzH0xSV/5XZOu','user',NULL,1,'approved',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'default.png','2026-04-25 16:52:11',NULL,NULL,1),(1018,'olxp8348','olxp8348','olxp8348@gmail.com','$2y$10$4oUiU3GnmoELLP7Wp0NGv.ViGh2aUOTq2JkQAIhX39K9CUhBM/zMm','user','03202323345',0,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'652762',NULL,'default.png','2026-04-28 18:43:47',NULL,NULL,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_active_ads`
--

DROP TABLE IF EXISTS `view_active_ads`;
/*!50001 DROP VIEW IF EXISTS `view_active_ads`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_active_ads` AS SELECT 
 1 AS `id`,
 1 AS `user_id`,
 1 AS `category_id`,
 1 AS `title`,
 1 AS `description`,
 1 AS `price`,
 1 AS `location`,
 1 AS `condition_type`,
 1 AS `status`,
 1 AS `created_at`,
 1 AS `category_name`,
 1 AS `category_slug`,
 1 AS `seller_name`,
 1 AS `primary_image`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_user_stats`
--

DROP TABLE IF EXISTS `view_user_stats`;
/*!50001 DROP VIEW IF EXISTS `view_user_stats`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_user_stats` AS SELECT 
 1 AS `user_id`,
 1 AS `name`,
 1 AS `total_ads`,
 1 AS `sold_ads`,
 1 AS `favorite_ads_count`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `view_active_ads`
--

/*!50001 DROP VIEW IF EXISTS `view_active_ads`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 */
/*!50001 VIEW `view_active_ads` AS select `a`.`id` AS `id`,`a`.`user_id` AS `user_id`,`a`.`category_id` AS `category_id`,`a`.`title` AS `title`,`a`.`description` AS `description`,`a`.`price` AS `price`,`a`.`location` AS `location`,`a`.`condition_type` AS `condition_type`,`a`.`status` AS `status`,`a`.`created_at` AS `created_at`,`c`.`name` AS `category_name`,`c`.`slug` AS `category_slug`,`u`.`name` AS `seller_name`,(select `ad_images`.`image_path` from `ad_images` where `ad_images`.`ad_id` = `a`.`id` order by `ad_images`.`is_primary` desc limit 1) AS `primary_image` from ((`ads` `a` join `categories` `c` on(`a`.`category_id` = `c`.`id`)) join `users` `u` on(`a`.`user_id` = `u`.`id`)) where `a`.`status` = 'active' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_user_stats`
--

/*!50001 DROP VIEW IF EXISTS `view_user_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 */
/*!50001 VIEW `view_user_stats` AS select `u`.`id` AS `user_id`,`u`.`name` AS `name`,count(distinct `a`.`id`) AS `total_ads`,count(distinct case when `a`.`status` = 'sold' then `a`.`id` end) AS `sold_ads`,count(distinct `f`.`id`) AS `favorite_ads_count` from ((`users` `u` left join `ads` `a` on(`u`.`id` = `a`.`user_id`)) left join `favorites` `f` on(`u`.`id` = `f`.`user_id`)) group by `u`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-28 23:44:52
