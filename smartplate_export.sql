-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: smartplate
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `image_url` text DEFAULT NULL,
  `content` text NOT NULL,
  `author` varchar(100) DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 1 COMMENT '0 = brouillon, 1 = publi├®',
  `rating_sum` int(11) DEFAULT 0 COMMENT 'Somme des notes',
  `rating_count` int(11) DEFAULT 0 COMMENT 'Nombre de votes',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (1,'vsdvsdv','sqdvsqvqsdvqs','','sdqvvqsvICHQlggkdcJ.DCBJsdsdvdv','Admin','2026-05-08 22:08:40',1,10,2),(2,'sdvsdv','sdqvqsdv','','vsdvqsdvqsdvsdqvvqsvnwclsqnckcqscsdqcdliucsd','Admin','2026-05-08 23:19:23',1,5,1);
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avis` (
  `id_avis` int(11) NOT NULL AUTO_INCREMENT,
  `nom_user` varchar(100) NOT NULL,
  `note` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `date_avis` datetime DEFAULT current_timestamp(),
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_avis`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avis`
--

LOCK TABLES `avis` WRITE;
/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
INSERT INTO `avis` VALUES (1,'islem',4,'c\'est tres bien','2026-05-02 21:27:10',NULL),(2,'islem',4,'c\'est tres bien','2026-05-02 21:33:09',NULL),(3,'Arij',3,'normal','2026-05-03 11:25:19',NULL),(4,'klibi',5,'tr├®s bon merci','2026-05-07 18:07:49',NULL),(5,'YOUSSEF KLIBI',4,'hehe tr├®s bonnnnnn','2026-05-07 19:14:17',54),(6,'YOUSSEF KLIBI',4,'hehe tr├®s bonnnnnn','2026-05-07 19:43:33',54),(7,'YOUSSEF KLIBI',4,'hehe tr├®s bonnnnnn','2026-05-07 19:43:39',54);
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 0 COMMENT '0 = en attente, 1 = approuv├®',
  `emoji` varchar(50) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `toxic_flag` tinyint(1) DEFAULT 0 COMMENT '0 = non toxique, 1 = toxique',
  `toxic_delete_at` timestamp NULL DEFAULT NULL COMMENT 'Date de suppression automatique si toxique',
  `badge` varchar(50) DEFAULT NULL,
  `badge_assigned_at` timestamp NULL DEFAULT NULL,
  `upvotes` int(11) DEFAULT 0,
  `downvotes` int(11) DEFAULT 0,
  `reports` int(11) DEFAULT 0,
  `report_count` int(11) DEFAULT 0,
  `stance` varchar(20) DEFAULT NULL,
  `reclass_pour` int(11) DEFAULT 0,
  `reclass_contre` int(11) DEFAULT 0,
  `reclass_neutre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_article_id` (`article_id`),
  KEY `idx_status` (`status`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_toxic_flag` (`toxic_flag`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (2,1,'qsdvqsdvqsd','sdqvqsdvsd','2026-05-08 22:09:49',1,'­ƒÿÇ',NULL,0,NULL,'Top Commentaire','2026-05-08 22:12:28',0,0,0,0,NULL,0,0,0),(5,1,'sqdfsqdf','sqdqsdf','2026-05-08 22:55:38',1,'????',NULL,0,NULL,NULL,NULL,0,0,0,1,NULL,0,0,0),(6,1,'Test User','Test comment 2','2026-05-08 22:58:57',1,'????',NULL,0,NULL,NULL,NULL,0,0,0,0,NULL,0,0,0),(12,2,'dhaf thabet','dqscqs','2026-05-08 23:46:52',1,'????',NULL,0,NULL,NULL,NULL,0,0,0,0,NULL,0,0,0);
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredient`
--

DROP TABLE IF EXISTS `ingredient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingredient` (
  `id_ingredient` int(20) NOT NULL AUTO_INCREMENT,
  `nom_ingredient` varchar(100) NOT NULL,
  `type_ingredient` varchar(50) NOT NULL,
  `calories` int(11) NOT NULL,
  `saison_debut` int(11) NOT NULL,
  `saison_fin` int(11) NOT NULL,
  `proteines` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lipides` decimal(10,2) NOT NULL DEFAULT 0.00,
  `glucides` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unite` varchar(30) NOT NULL DEFAULT 'unit├®',
  PRIMARY KEY (`id_ingredient`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredient`
--

LOCK TABLES `ingredient` WRITE;
/*!40000 ALTER TABLE `ingredient` DISABLE KEYS */;
INSERT INTO `ingredient` VALUES (11,'carotte','Legume',41,1,12,0.90,0.20,10.00,'ingredient_1777820749_4061.jpg',0.40,'pi├¿ce'),(12,'banane','Fruit',89,1,12,1.10,0.30,23.00,'ingredient_1777822546_8440.jpg',0.60,'pi├¿ce'),(13,'tomate','Legume',18,5,9,0.90,0.20,3.90,'ingredient_1777820438_1880.jpg',0.80,'pi├¿ce'),(17,'Oignon','Legume',40,1,12,1.10,0.10,9.30,'ingredient_1777822663_2844.jpg',0.50,'pi├¿ce'),(18,'Poulet','Viande',239,1,12,27.00,14.00,0.00,'ingredient_1777822819_3357.jpg',18.00,'Kg'),(19,'Riz','Autre',130,1,12,2.70,0.30,28.00,'ingredient_1777822949_7796.jpg',3.50,'Kg'),(20,'Laitue','Legume',15,3,10,1.40,0.20,2.90,'ingredient_1777823045_9794.jpg',1.20,'pi├¿ce'),(21,'Pates','Autre',131,1,12,5.00,1.10,25.00,'ingredient_1777823129_2115.jpg',2.80,'Kg');
/*!40000 ALTER TABLE `ingredient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_alimentaire`
--

DROP TABLE IF EXISTS `journal_alimentaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_alimentaire` (
  `id_journal` int(11) NOT NULL AUTO_INCREMENT,
  `date_journal` date NOT NULL,
  `poids_actuel` float DEFAULT NULL,
  `humeur` enum('Excellent','Bien','Neutre','Fatigu├®','Stress├®') DEFAULT 'Bien',
  `heures_sommeil` int(11) DEFAULT 0,
  `id_utilisateur` int(11) NOT NULL,
  `id_objectif` int(11) NOT NULL,
  PRIMARY KEY (`id_journal`),
  KEY `fk_journal_objectif` (`id_objectif`),
  CONSTRAINT `fk_journal_objectif` FOREIGN KEY (`id_objectif`) REFERENCES `objectif` (`id_objectif`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_alimentaire`
--

LOCK TABLES `journal_alimentaire` WRITE;
/*!40000 ALTER TABLE `journal_alimentaire` DISABLE KEYS */;
INSERT INTO `journal_alimentaire` VALUES (42,'2026-04-19',69.5,'Neutre',6,1,48),(46,'2026-04-20',69,'Excellent',7,1,49),(47,'2026-04-21',68,'Excellent',8,1,49),(49,'2026-04-23',66,'Bien',5,1,49),(53,'2026-05-03',69,'Stress├®',6,1,49),(55,'2026-05-04',68,'Neutre',6,1,49),(56,'2026-05-07',71,'Bien',6,54,57),(57,'2026-05-06',57,'Fatigu├®',3,1,49);
/*!40000 ALTER TABLE `journal_alimentaire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_history`
--

DROP TABLE IF EXISTS `login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `login_time` datetime NOT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Success',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_history`
--

LOCK TABLES `login_history` WRITE;
/*!40000 ALTER TABLE `login_history` DISABLE KEYS */;
INSERT INTO `login_history` VALUES (10,44,'::1','Localhost','Local',36.80650000,10.18150000,'2026-05-02 21:42:34','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(14,44,'::1','Localhost','Local',36.80650000,10.18150000,'2026-05-02 21:45:38','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(18,44,'196.238.15.154','El Battan','Tunisia',36.80220000,9.83900000,'2026-05-02 22:06:16','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(27,44,'196.176.136.216','Tunis','Tunisia',36.81780000,10.16560000,'2026-05-02 22:44:49','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(29,44,'196.176.136.216','Tunis','Tunisia',36.81780000,10.16560000,'2026-05-02 22:46:13','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(65,44,'41.226.7.130','Tunis','Tunisia',36.81780000,10.16560000,'2026-05-04 20:01:54','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(68,44,'41.226.7.130','Tunis','Tunisia',36.81780000,10.16560000,'2026-05-04 20:06:14','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(74,44,'41.226.7.130','Tunis','Tunisia',36.81780000,10.16560000,'2026-05-04 20:44:24','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(79,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-04 22:33:21','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(81,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-04 22:43:59','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(83,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-04 23:06:10','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(86,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-04 23:28:07','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(88,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-04 23:38:54','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(90,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-04 23:39:50','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(94,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-04 23:53:01','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(96,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-04 23:59:12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(99,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-05 00:37:36','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(101,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-05 00:43:11','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(128,44,'102.31.161.90','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-05 01:55:52','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(138,47,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-05 02:12:38','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Success'),(139,47,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-05 02:17:56','test_agent','Success'),(140,47,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-05 02:21:16','test_agent','Success'),(150,44,'102.31.161.90','Aryanah','Tunisia',36.85620000,10.19070000,'2026-05-05 02:46:04','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(160,44,'196.203.207.182','Tunis','Tunisia',36.82440000,10.17630000,'2026-05-05 09:54:13','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(162,49,'197.14.84.35','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-05 20:46:02','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(163,49,'197.14.84.35','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-05 21:16:03','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(164,49,'197.14.84.35','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-05 21:37:02','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(165,49,'197.14.84.35','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-05 21:45:50','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(166,49,'197.14.84.35','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-05 21:54:00','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(167,49,'197.14.84.35','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-05 22:18:34','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(168,49,'197.14.84.35','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-05 23:12:18','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(170,49,'196.203.207.180','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-06 10:41:16','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(172,49,'196.203.207.180','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-06 10:50:23','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(176,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-06 22:55:01','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(177,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-06 23:09:00','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(178,49,'197.0.55.205','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-06 23:09:08','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(179,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-06 23:09:57','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(180,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-06 23:10:26','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(181,54,'197.0.55.205','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-06 23:33:41','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(182,54,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-07 10:14:07','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(183,54,'197.2.237.7','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-07 18:01:11','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(184,49,'197.2.237.7','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-07 21:06:13','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(185,54,'197.2.237.7','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-07 21:17:22','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Success'),(186,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-08 19:53:17','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Success'),(187,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-08 19:56:13','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Success'),(188,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-08 20:05:22','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Success'),(189,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-08 20:44:03','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Success'),(190,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-08 21:33:05','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Success'),(191,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-08 21:35:57','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Success'),(192,55,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-08 23:50:28','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Success'),(193,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-09 00:01:07','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Success'),(194,55,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-09 00:02:20','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Success'),(195,49,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-09 00:13:58','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Success'),(196,55,'::1','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬','Ï¬┘ê┘åÏ│',36.89880000,10.18950000,'2026-05-09 00:27:51','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Success');
/*!40000 ALTER TABLE `login_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectif`
--

DROP TABLE IF EXISTS `objectif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectif` (
  `id_objectif` int(11) NOT NULL AUTO_INCREMENT,
  `type_objectif` enum('perte_poids','maintien','prise_masse') NOT NULL,
  `poids_cible` float NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `statut` enum('en_cours','atteint','abandonne') DEFAULT 'en_cours',
  `id_utilisateur` int(11) DEFAULT NULL,
  `is_notif_enabled` tinyint(1) DEFAULT 0,
  `heure_notification` time DEFAULT '08:00:00',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_objectif`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectif`
--

LOCK TABLES `objectif` WRITE;
/*!40000 ALTER TABLE `objectif` DISABLE KEYS */;
INSERT INTO `objectif` VALUES (48,'perte_poids',66,'2026-04-19','2026-05-28','abandonne',1,0,'08:00:00','2026-05-07 22:07:06'),(49,'perte_poids',66,'2026-04-19','2026-06-10','abandonne',1,0,'09:36:00','2026-05-07 22:07:31'),(51,'maintien',80,'2026-04-15','2026-06-17','abandonne',2,0,'08:00:00','2026-04-26 22:28:04'),(54,'prise_masse',80,'2026-04-21','2026-06-18','atteint',3,0,'08:00:00','2026-04-26 22:28:04'),(55,'prise_masse',80,'2026-05-20','2026-07-01','en_cours',4,1,'10:30:00','2026-04-26 22:28:04'),(56,'maintien',80,'2026-05-04','2026-05-30','en_cours',8,1,'10:30:00','2026-05-04 19:25:06'),(57,'prise_masse',80,'2026-05-07','2026-06-27','en_cours',54,0,'08:00:00','2026-05-07 22:29:13');
/*!40000 ALTER TABLE `objectif` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `planning_hebdo`
--

DROP TABLE IF EXISTS `planning_hebdo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `planning_hebdo` (
  `id_planning` int(11) NOT NULL AUTO_INCREMENT,
  `nom_planning` varchar(150) NOT NULL,
  `objectif` enum('perte_poids','maintien','prise_masse') NOT NULL,
  `nb_jours` int(11) NOT NULL,
  `temps_max` int(11) NOT NULL,
  `calories_max` int(11) NOT NULL,
  `budget_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_creation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_planning`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planning_hebdo`
--

LOCK TABLES `planning_hebdo` WRITE;
/*!40000 ALTER TABLE `planning_hebdo` DISABLE KEYS */;
INSERT INTO `planning_hebdo` VALUES (1,'Planning 03/05/2026 12:16','perte_poids',7,20,450,0.00,'2026-05-03 11:16:30'),(2,'Planning 03/05/2026 12:17','perte_poids',7,20,450,0.00,'2026-05-03 11:17:01'),(3,'Planning 03/05/2026 12:24','perte_poids',7,15,450,0.00,'2026-05-03 11:24:04'),(4,'Planning 03/05/2026 12:56','perte_poids',7,20,450,0.00,'2026-05-03 11:56:28'),(5,'Planning 03/05/2026 12:56','perte_poids',7,20,450,0.00,'2026-05-03 11:56:35'),(6,'Planning 03/05/2026 12:58','perte_poids',7,20,450,4.80,'2026-05-03 11:58:28'),(7,'Planning 03/05/2026 13:01','perte_poids',7,20,450,4.80,'2026-05-03 12:01:00'),(8,'Planning 03/05/2026 18:19','perte_poids',7,20,450,77.00,'2026-05-03 17:19:09'),(9,'Planning 03/05/2026 22:21','perte_poids',7,20,450,77.00,'2026-05-03 21:21:12'),(10,'Planning 05/05/2026 10:31','perte_poids',7,20,450,77.00,'2026-05-05 09:31:55'),(11,'Planning 05/05/2026 10:43','perte_poids',7,20,450,77.00,'2026-05-05 09:43:49');
/*!40000 ALTER TABLE `planning_hebdo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `planning_hebdo_recette`
--

DROP TABLE IF EXISTS `planning_hebdo_recette`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `planning_hebdo_recette` (
  `id_planning_recette` int(11) NOT NULL AUTO_INCREMENT,
  `planning_id` int(11) NOT NULL,
  `jour_semaine` varchar(30) NOT NULL,
  `recette_id` int(11) NOT NULL,
  PRIMARY KEY (`id_planning_recette`),
  KEY `planning_id` (`planning_id`),
  KEY `recette_id` (`recette_id`),
  CONSTRAINT `planning_hebdo_recette_ibfk_1` FOREIGN KEY (`planning_id`) REFERENCES `planning_hebdo` (`id_planning`) ON DELETE CASCADE,
  CONSTRAINT `planning_hebdo_recette_ibfk_2` FOREIGN KEY (`recette_id`) REFERENCES `recette` (`id_recette`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planning_hebdo_recette`
--

LOCK TABLES `planning_hebdo_recette` WRITE;
/*!40000 ALTER TABLE `planning_hebdo_recette` DISABLE KEYS */;
INSERT INTO `planning_hebdo_recette` VALUES (1,1,'Lundi',12),(3,1,'Mercredi',12),(5,1,'Vendredi',12),(7,1,'Dimanche',12),(8,2,'Lundi',12),(10,2,'Mercredi',12),(12,2,'Vendredi',12),(14,2,'Dimanche',12),(15,3,'Lundi',12),(16,3,'Mardi',12),(17,3,'Mercredi',12),(18,3,'Jeudi',12),(19,3,'Vendredi',12),(20,3,'Samedi',12),(21,3,'Dimanche',12),(22,4,'Lundi',12),(24,4,'Mercredi',12),(26,4,'Vendredi',12),(28,4,'Dimanche',12),(29,5,'Lundi',12),(31,5,'Mercredi',12),(33,5,'Vendredi',12),(35,5,'Dimanche',12),(36,6,'Lundi',12),(38,6,'Mercredi',12),(40,6,'Vendredi',12),(42,6,'Dimanche',12),(43,7,'Lundi',12),(45,7,'Mercredi',12),(47,7,'Vendredi',12),(49,7,'Dimanche',12),(50,8,'Lundi',12),(51,8,'Mardi',14),(52,8,'Mercredi',13),(53,8,'Jeudi',12),(54,8,'Vendredi',14),(55,8,'Samedi',13),(56,8,'Dimanche',12),(57,9,'Lundi',12),(58,9,'Mardi',14),(59,9,'Mercredi',13),(60,9,'Jeudi',12),(61,9,'Vendredi',14),(62,9,'Samedi',13),(63,9,'Dimanche',12),(64,10,'Lundi',12),(65,10,'Mardi',14),(66,10,'Mercredi',13),(67,10,'Jeudi',12),(68,10,'Vendredi',14),(69,10,'Samedi',13),(70,10,'Dimanche',12),(71,11,'Lundi',12),(72,11,'Mardi',14),(73,11,'Mercredi',13),(74,11,'Jeudi',12),(75,11,'Vendredi',14),(76,11,'Samedi',13),(77,11,'Dimanche',12);
/*!40000 ALTER TABLE `planning_hebdo_recette` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profil_utilisateur`
--

DROP TABLE IF EXISTS `profil_utilisateur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profil_utilisateur` (
  `id_profil` int(11) NOT NULL AUTO_INCREMENT,
  `nom_utilisateur` varchar(100) NOT NULL,
  `objectif` enum('perte_poids','maintien','prise_masse') NOT NULL,
  `temps_max` int(11) NOT NULL,
  `calories_max` int(11) NOT NULL,
  PRIMARY KEY (`id_profil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profil_utilisateur`
--

LOCK TABLES `profil_utilisateur` WRITE;
/*!40000 ALTER TABLE `profil_utilisateur` DISABLE KEYS */;
/*!40000 ALTER TABLE `profil_utilisateur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profils`
--

DROP TABLE IF EXISTS `profils`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profils` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `profils_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profils`
--

LOCK TABLES `profils` WRITE;
/*!40000 ALTER TABLE `profils` DISABLE KEYS */;
/*!40000 ALTER TABLE `profils` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recette`
--

DROP TABLE IF EXISTS `recette`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recette` (
  `id_recette` int(20) NOT NULL AUTO_INCREMENT,
  `nom_recette` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `calories` int(11) NOT NULL,
  `temps_preparation` int(11) NOT NULL,
  `categorie` varchar(50) NOT NULL,
  `image` varchar(250) NOT NULL,
  `proteines` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lipides` decimal(10,2) NOT NULL DEFAULT 0.00,
  `glucides` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_recette`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recette`
--

LOCK TABLES `recette` WRITE;
/*!40000 ALTER TABLE `recette` DISABLE KEYS */;
INSERT INTO `recette` VALUES (5,'pate sauce tomate','Recette simple et savoureuse avec sauce tomate maison',189,35,'Pate','recette_1777824924_3518.jpg',7.00,1.40,38.20),(12,'Riz au poulet','Plat equilibre avec riz et poulet, riche en proteines.',427,15,'Healthy','recette_1777824429_8392.jpg',31.70,14.60,41.20),(13,'salade healthy','Salade fraiche et legere, ideale pour un repas rapide.',96,15,'Salade','top-view-tasty-salad-with-vegetables.jpg',3.40,0.50,22.20),(14,'Salade banane carotte','Salade originale sucree-salee, legere et vitaminee.',145,8,'Salade','recette_1777825016_8725.webp',3.40,0.70,35.90),(15,'Tomate farcie legere','Tomates garnies facon legere, parfaites pour une alimentation equilibree.',427,35,'Healthy','recette_1777825117_2404.jpg',31.70,14.60,41.20);
/*!40000 ALTER TABLE `recette` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recette_ingredient`
--

DROP TABLE IF EXISTS `recette_ingredient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recette_ingredient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recette_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `quantite` decimal(10,2) NOT NULL DEFAULT 1.00,
  PRIMARY KEY (`id`),
  KEY `recette_id` (`recette_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `recette_ingredient_ibfk_1` FOREIGN KEY (`recette_id`) REFERENCES `recette` (`id_recette`),
  CONSTRAINT `recette_ingredient_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredient` (`id_ingredient`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recette_ingredient`
--

LOCK TABLES `recette_ingredient` WRITE;
/*!40000 ALTER TABLE `recette_ingredient` DISABLE KEYS */;
INSERT INTO `recette_ingredient` VALUES (19,13,11,1.00),(20,13,20,1.00),(21,13,17,1.00),(22,12,17,1.00),(23,12,18,1.00),(24,12,19,1.00),(25,12,13,1.00),(26,5,17,1.00),(27,5,21,1.00),(28,5,13,1.00),(29,14,12,1.00),(30,14,11,1.00),(31,14,20,1.00),(32,15,17,1.00),(33,15,18,1.00),(34,15,19,1.00),(35,15,13,1.00);
/*!40000 ALTER TABLE `recette_ingredient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reclamations`
--

DROP TABLE IF EXISTS `reclamations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reclamations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_client` int(11) NOT NULL,
  `sujet` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_creation` date NOT NULL,
  `priorite` varchar(50) DEFAULT 'Faible',
  `statut` varchar(50) DEFAULT 'En attente',
  PRIMARY KEY (`id`),
  KEY `fk_client_id` (`id_client`),
  CONSTRAINT `fk_reclamation_user` FOREIGN KEY (`id_client`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reclamations`
--

LOCK TABLES `reclamations` WRITE;
/*!40000 ALTER TABLE `reclamations` DISABLE KEYS */;
INSERT INTO `reclamations` VALUES (2,49,'Prix','bonjour, je vous hait','2026-05-05','Faible','Trait├®');
/*!40000 ALTER TABLE `reclamations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repas`
--

DROP TABLE IF EXISTS `repas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repas` (
  `id_repas` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `type_repas` varchar(50) DEFAULT NULL,
  `heure_repas` time DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `nbre_calories` float NOT NULL,
  `proteine` float NOT NULL,
  `glucide` float NOT NULL,
  `lipide` float NOT NULL,
  `id_journal` int(11) NOT NULL,
  `image_repas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_repas`),
  KEY `fk_journal` (`id_journal`),
  CONSTRAINT `fk_journal` FOREIGN KEY (`id_journal`) REFERENCES `journal_alimentaire` (`id_journal`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repas`
--

LOCK TABLES `repas` WRITE;
/*!40000 ALTER TABLE `repas` DISABLE KEYS */;
INSERT INTO `repas` VALUES (18,'Blanc de poulet','Dejeuner','02:04:00',160,275,27,7,10,42,'repas_1776636553_48a6ea61.png'),(20,'brik','Diner','19:40:00',200,485,15,7,10,42,'repas_1776710434_1863f9f4.png'),(21,'Caf├®','Petit-Dejeuner','10:00:00',200,200,7,13,8,42,'repas_1776710708_f5300353.png'),(22,'Whey Protein','Collation','17:30:00',40,345,25,14,8,42,'repas_1776715328_26d26a51.jpg'),(24,'Blanc de poulet','Dejeuner','19:40:00',130,345,15,13,8,46,NULL),(28,'lablabi','Petit-Dejeuner','13:35:00',330,396,19.8,59.4,9.9,56,'repas_1778186916_ae5071d7.jpg'),(30,'fricass├®','Diner','19:00:00',200,430,16,13,5,56,'repas_1778187595_41a4aab8.jpg');
/*!40000 ALTER TABLE `repas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reponses`
--

DROP TABLE IF EXISTS `reponses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reponses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_reclamation` int(11) NOT NULL,
  `reponse` text NOT NULL,
  `date_reponse` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_reclamation_id` (`id_reclamation`),
  CONSTRAINT `fk_reponse_reclamation` FOREIGN KEY (`id_reclamation`) REFERENCES `reclamations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reponses`
--

LOCK TABLES `reponses` WRITE;
/*!40000 ALTER TABLE `reponses` DISABLE KEYS */;
INSERT INTO `reponses` VALUES (1,2,'bonjour, \r\nje m\'en fous \r\n    Merci','2026-05-05');
/*!40000 ALTER TABLE `reponses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `topic_suggestions`
--

DROP TABLE IF EXISTS `topic_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topic_suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `topic_suggestions`
--

LOCK TABLES `topic_suggestions` WRITE;
/*!40000 ALTER TABLE `topic_suggestions` DISABLE KEYS */;
/*!40000 ALTER TABLE `topic_suggestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
INSERT INTO `user_sessions` VALUES (35,44,'bb2a723b318ac0a43e640f613d83fb81260b036dc15949def1e1d37ec953580f','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','41.226.7.130','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Tunis, Tunisia','2026-05-04 20:44:24','2026-05-04 20:44:24',1),(40,44,'b2b6eefad14a509dbaca8fab0abfc3b65d9f048032b18c556aaef4e7dae71566','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-04 22:33:21','2026-05-04 22:33:21',1),(42,44,'82a1eafdb2db7f27d90828bf2c25cc7825e4b7de3063da76b15b0e524aa400de','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-04 22:43:59','2026-05-04 22:43:59',1),(44,44,'0c2ecc60f268932146f1afa694297c1020e82e710bdc8372c694ba795b2c438b','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-04 23:06:10','2026-05-04 23:06:10',1),(47,44,'3175f8ab5ec5c3a1b6c3a118e58c0a3bf2c0ae12fc41a7c58ca22f33a8d56789','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-04 23:28:07','2026-05-04 23:28:07',1),(49,44,'fe15183e7e3a585193b1118650ce2376807d5ed5464ac8f9f608253a11269e9c','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-04 23:38:54','2026-05-04 23:38:54',1),(51,44,'4814bf8974fb04f4b942c7babc3681aaced2b626313cd0daa3cc94c6434ca5b9','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-04 23:39:50','2026-05-04 23:39:50',1),(55,44,'bdbd9e5f79fb86a05a18ef06cbd19b0c9989e36533d3620e3ad844d17d10d505','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-04 23:53:01','2026-05-04 23:53:01',1),(57,44,'23c2b4271ca6422dc22ad67c05a16d5a977f87667eaf6a3a5005a56fff1f817e','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-04 23:59:12','2026-05-04 23:59:12',1),(60,44,'4ae127256770bf26cc86ee0ea7eb82d54693a5b203c841e4915883ca296c91a0','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-05 00:37:36','2026-05-05 00:37:36',1),(62,44,'aef79635b45388ddbbfbb7a9ba9600524f643abb9f9c79b2c7e09d331bf77b62','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-05 00:43:11','2026-05-05 00:43:11',1),(64,47,'288dbd9adc6e905358f73216f55aced5cbf35bf4b4d4021d26f3d8361e180cf4','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-05 00:53:59','2026-05-05 00:53:59',0),(66,47,'e848b43964267cd68dc6e122fc59f8d48fda764dd109692b1b0fc69ab796ec02','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-05 00:56:43','2026-05-05 00:56:43',1),(69,47,'00df4db8760700d9ed3a63f15bb7650c8cba4b468d99bacb14c6a79c09452e69','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 01:05:33','2026-05-05 01:05:33',1),(89,44,'d1858b27bcaebf4c85e38a1e1818bf35996fb62f37605a8903f0a87174223e81','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 01:55:52','2026-05-05 01:55:52',1),(98,47,'af8cd8f6d8ff71975b1a688c9af47e452e7613d197b30d1307fda8f29f192dce','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-05 02:12:00','2026-05-05 02:12:00',1),(99,47,'e27c781e614fecbbccb0a401b499c733f9f06cd8077460ec036b38349d1cc20b','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Aryanah, Tunisia','2026-05-05 02:12:38','2026-05-05 02:12:38',1),(100,47,'2505f4de44344e4ae80199378affc0666c19d6882527f49c25afcdf37ca13690','test_agent','102.31.161.90','','Aryanah, Tunisia','2026-05-05 02:17:56','2026-05-05 02:17:56',1),(101,47,'22577b4eaba7ba2dd7ae5e91c888443758cfb12c8ee21b12f84e16369fdbd0e4','test_agent','102.31.161.90','','Aryanah, Tunisia','2026-05-05 02:21:16','2026-05-05 02:21:16',1),(111,44,'9f024739384a58af299d8b8b718e7b88695d730f1074e1493b06dbc660b0438b','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','102.31.161.90','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Aryanah, Tunisia','2026-05-05 02:46:04','2026-05-05 02:46:04',1),(121,44,'b64baf22410edbf3ede572dee4b5644bbb610da8889aaf4361da59fbf0faff6d','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','196.203.207.182','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Tunis, Tunisia','2026-05-05 09:54:13','2026-05-05 09:54:13',1),(123,49,'c2ab68f4c755e34897bf3eaae44396f270ce87f44e6b478df3e7a541364fc0ff','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.14.84.35','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 20:46:02','2026-05-05 20:46:02',1),(124,49,'a94f2fb7099b91bc8de18b85c208fbd7b779fee4c814a30c5f8eda39acac0c59','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.14.84.35','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 21:16:03','2026-05-05 21:16:03',1),(125,49,'050a75c941eedb035e2927c8c6f71fbe3ff51d87e1eede3ba9e98f56a1983d8d','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.14.84.35','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 21:37:02','2026-05-05 21:37:02',1),(126,49,'8eb0de8da8890cac4760e1fe77586bc0717e9bce2eaea537434f0b97ffe67c0d','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.14.84.35','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 21:45:50','2026-05-05 21:45:50',1),(127,49,'40a6e16818f0489311059687757d38ba100b1b89161537a991919fad0f20cb75','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.14.84.35','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 21:54:00','2026-05-05 21:54:00',1),(128,49,'be23081e2e4616d22244456b5578e04876e92d4603356b1795e6ad6dacba4cc7','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.14.84.35','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 22:18:34','2026-05-05 22:18:34',1),(129,49,'3749f252a3f300cf203f44e5aa196ba97b4bf1b00fc88ec70148be5ec3aac1fe','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.14.84.35','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-05 23:12:18','2026-05-05 23:12:18',1),(131,49,'6a4a527ed476ba5f147335ed9734e0e41f1e9f1e56365975cab7c8b520fcd6ee','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','196.203.207.180','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-06 10:41:16','2026-05-06 10:41:16',1),(133,49,'3078496ecf132823efce3a3ed212facf36ca219cbd61616134d7f667147c540d','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','196.203.207.180','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-06 10:50:23','2026-05-06 10:50:23',1),(137,49,'fccc72f83251a8d9ed69470ee7b4c9f006899d47befa95631bfaffa34fae3699','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-06 22:55:01','2026-05-06 22:55:01',1),(138,49,'ce0ca3943508c91429071f1e3e5bf57d91fa1932c099ffc1cc5a7bd3a9fe7b50','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-06 23:09:00','2026-05-06 23:09:00',1),(139,49,'1ad823dfd6847d96877a2fe3e387760f8c6892938a1290b897cb07ccbfe4a504','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.0.55.205','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-06 23:09:08','2026-05-06 23:09:08',1),(140,49,'35289f8b5bde2e389c2acd61e7e1d9ed215fdcd5f1be4ee45672edbe0a183220','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-06 23:09:57','2026-05-06 23:09:57',1),(141,49,'ae12cc28a477f226cb4323da24251d8e3921e1082f6f37a847e7eb14979a12d6','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-06 23:10:26','2026-05-06 23:10:26',1),(142,54,'7359d1d6476d55f5c82ca8d5c3df7029ec7712606d2b5cddccad1d9f2f2f5d82','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.0.55.205','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-06 23:33:41','2026-05-06 23:33:41',1),(143,54,'890ba66ebc097800d5c0949c9088f701ae1055ab24a98ea31b00694eb6b3ec4a','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-07 10:14:07','2026-05-07 10:14:07',1),(144,54,'83e579a75aa2e1bfd1e226e38afe91b989aacc6dd304df6c2a3566aa7f822939','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.2.237.7','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-07 18:01:11','2026-05-07 18:01:11',1),(145,49,'d54414351cb9f49976d2e21d13b0e5b7c5152371bd74221b95d2faab5d28d6da','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.2.237.7','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-07 21:06:13','2026-05-07 21:06:13',1),(146,54,'ac51219becd55ba9d9106e3f6e3ced7b073f1426d38961993863d8d1c6c45b3d','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','197.2.237.7','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-07 21:17:22','2026-05-07 21:17:22',1),(147,49,'3d90e6b12a9c6f1512d6f9213e8321df838bf2dcf69ffea37458a4c7bea04c88','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-08 19:53:17','2026-05-08 19:53:17',1),(148,49,'ded7bc79a3a2b261f18798f116b83d1c720e63b26aee5e980f9e588e68df54ca','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-08 19:56:13','2026-05-08 19:56:13',1),(149,49,'8a31e5a1a3d71159c4030dfbc53850f4c71105f12f0f42299cf294d0e8dcb962','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-08 20:05:22','2026-05-08 20:05:22',1),(150,49,'b149debe14d897318c72a125d58bc1d9ebc7a401ad7b272489283c27e40358f7','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-08 20:44:03','2026-05-08 20:44:03',1),(151,49,'9de42fc35e7dfd9ac836f122458f62f90e4ff8a24beec3e10625e8e5efeaca2c','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-08 21:33:05','2026-05-08 21:33:05',1),(152,49,'150ba136ec712106f101b630705b46973633b727b765b33e915d309d012771d3','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-08 21:35:57','2026-05-08 21:35:57',1),(153,55,'d3c40f2231516a258d504d0ff7adde3f922b30fa8ef68fb6e13f70fe2f91e6fb','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-08 23:50:28','2026-05-08 23:50:28',1),(154,49,'03a301434352347dd5f0cd12e02b1c1056ecbc27093c57fefd706f46238a6381','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-09 00:01:07','2026-05-09 00:01:07',1),(155,55,'fe80a5d83721de95b61eaeea497a7bae5924d899939df47feee4815f85d72c7e','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-09 00:02:20','2026-05-09 00:02:20',1),(156,49,'8f3c11a85655d10047d44c5d554f0710cf49efdda907656006dcbfac8e2d3e07','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-09 00:13:58','2026-05-09 00:13:58',1),(157,55,'3f6f2d87ed79b872e6b6096374a25e772eb83fe5cad45630a3330ca16a9e3fcb','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','Ïº┘ä┘åÏ«┘è┘äÏºÏ¬, Ï¬┘ê┘åÏ│','2026-05-09 00:27:51','2026-05-09 00:27:51',1);
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prenom` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `statut` varchar(20) DEFAULT 'actif',
  `last_activity` datetime DEFAULT NULL,
  `webauthn_credential_id` text DEFAULT NULL,
  `webauthn_public_key` text DEFAULT NULL,
  `webauthn_user_handle` varchar(255) DEFAULT NULL,
  `webauthn_sign_count` int(11) DEFAULT 0,
  `webauthn_enabled` tinyint(4) DEFAULT 0,
  `last_latitude` decimal(10,8) DEFAULT NULL,
  `last_longitude` decimal(11,8) DEFAULT NULL,
  `last_location_update` datetime DEFAULT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `session_device` varchar(255) DEFAULT NULL,
  `session_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (44,'ilyes','gaied','ilyesgaied32@gmail.com','$2y$10$T5XHhvZee7OjkgG5PFLnWOxQ0DAe6MraCuY7B9FZOBk2yn..3m2Qy','2026-04-24 21:32:24','a39e2490beb8d04d232624e0ec5eb462b7107d5bdaa9897fcafc0cbb81689acf','2026-05-05 10:54:13','108705778328143604208','inactif','2026-05-05 10:29:21',NULL,NULL,NULL,0,0,36.82440000,10.17630000,'2026-05-05 09:54:13','b64baf22410edbf3ede572dee4b5644bbb610da8889aaf4361da59fbf0faff6d','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-05-05 09:54:13'),(47,'ilyes','gaied','ilyesgaied915@gmail.com','$2y$10$GqQy4dXqChN6gTXBDYjIAuFITZko0eRwW10lQEcbrp0Wia9PkYNeO','2026-04-27 16:28:51','104983b28ac84323939a196d59c1b144e0c56adf5db6080c1f2fac3f1a2b0a69','2026-05-05 03:12:39','115349092344776915444','inactif',NULL,'v17VNhIm/xnJjq8wHb1BRg==','-----BEGIN PUBLIC KEY-----\nMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAETzI/A8UofZCsOVv0+iD8zFwxHHrt\nSGB6y0XftTci9FxTFZmnusjPioLu2hfcoxidn5pa/rrOF6Hi4uST6qymOg==\n-----END PUBLIC KEY-----\n','NDc=',0,1,36.85620000,10.19070000,'2026-05-05 02:21:16','22577b4eaba7ba2dd7ae5e91c888443758cfb12c8ee21b12f84e16369fdbd0e4','test_agent','2026-05-05 02:21:16'),(49,'klibi','youssef','klibiyoussef2017@gmail.com','$2y$10$1xkO9BF9MA0sUE9ZpAKm1uPa7aCvkMeFGucM4tmryMJEXAGGU0AsS','2026-05-05 19:30:08',NULL,NULL,NULL,'inactif',NULL,NULL,NULL,NULL,0,0,36.89880000,10.18950000,'2026-05-09 00:13:58','8f3c11a85655d10047d44c5d554f0710cf49efdda907656006dcbfac8e2d3e07','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','2026-05-09 00:13:58'),(54,'Youssef','Najjar','klibiyoussef2020@gmail.com','$2y$10$a2THrrk6wjP9rwhT/SPywuT/PmR/TQl.itJmNghtWRWnLielfOara','2026-05-06 22:11:07',NULL,NULL,NULL,'inactif','2026-05-07 22:50:02',NULL,NULL,NULL,0,0,36.89880000,10.18950000,'2026-05-07 21:17:22','ac51219becd55ba9d9106e3f6e3ced7b073f1426d38961993863d8d1c6c45b3d','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-05-07 21:17:22'),(55,'dhaf','thabet','mouthaffar4242@gmail.com','$2y$10$TB1VqCb95/XnWvPuHqinr.zTgOxqS9o/BYEG/qFuWLnzKs9zSf3Xm','2026-05-08 22:35:25',NULL,NULL,NULL,'actif','2026-05-09 00:48:33',NULL,NULL,NULL,0,0,36.89880000,10.18950000,'2026-05-09 00:27:51','3f6f2d87ed79b872e6b6096374a25e772eb83fe5cad45630a3330ca16a9e3fcb','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','2026-05-09 00:27:51'),(56,'Test','User','test@example.com','$2y$10$gJUx2PsCq6ED0xXF3O2es.97zhKbsw./lVICNGTttEkRUySAyAaBO','2026-05-08 22:38:36',NULL,NULL,NULL,'inactif','2026-05-09 00:12:12',NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL),(57,'Moudhaffar','Th','thabet.moudhaffar@esprit.tn','$2y$10$f0puOLVx6sOVYQHWPOF9MOXZ1gkxUGHI.PXAuA6Ejt0O4Z2cXtVWa','2026-05-08 22:43:55',NULL,NULL,NULL,'inactif','2026-05-08 23:49:02',NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-09  0:51:20
