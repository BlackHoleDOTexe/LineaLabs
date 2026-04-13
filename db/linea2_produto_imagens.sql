-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: linea2
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `produto_imagens`
--

DROP TABLE IF EXISTS `produto_imagens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_imagens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produto_id` int NOT NULL,
  `arquivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordem` int NOT NULL DEFAULT '0',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_produto_imagens_produto` (`produto_id`),
  CONSTRAINT `fk_produto_imagens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produto_imagens`
--

LOCK TABLES `produto_imagens` WRITE;
/*!40000 ALTER TABLE `produto_imagens` DISABLE KEYS */;
INSERT INTO `produto_imagens` VALUES (26,7,'produto_7_69bc7b4dc78de5.80746247.jpg',0,'2026-03-19 22:40:13'),(27,7,'produto_7_69bc7b4dc7aad9.52089711.jpg',1,'2026-03-19 22:40:13'),(28,7,'produto_7_69bc7b4dc84e33.45747807.jpg',2,'2026-03-19 22:40:13'),(29,8,'produto_8_69bc809d659637.19452551.png',0,'2026-03-19 23:02:53'),(30,9,'produto_9_69bc834ce97d91.80781232.jpg',0,'2026-03-19 23:14:20'),(31,9,'produto_9_69bc834cea93d2.67494453.jpg',1,'2026-03-19 23:14:20'),(32,9,'produto_9_69bc834ceb4cb1.82345104.jpg',2,'2026-03-19 23:14:20'),(33,9,'produto_9_69bc834cec12d9.12362187.jpg',3,'2026-03-19 23:14:20'),(34,10,'produto_10_69bc84ea82d130.92903758.png',0,'2026-03-19 23:21:14'),(36,12,'produto_12_69bc87005e8a95.17189521.jpg',0,'2026-03-19 23:30:08'),(37,12,'produto_12_69bc87005f1f77.16029274.jpg',1,'2026-03-19 23:30:08'),(38,12,'produto_12_69bc87005fb662.77392115.jpg',2,'2026-03-19 23:30:08'),(40,11,'produto_69bd9c1bcc9669.12684466.webp',1,'2026-03-20 19:12:27'),(41,11,'produto_69bd9c219ed465.75940810.webp',2,'2026-03-20 19:12:33'),(42,19,'produto_19_69c17bc8a694f1.90686924.png',0,'2026-03-23 17:43:36'),(43,20,'produto_20_69c17becd6dc28.09144539.png',0,'2026-03-23 17:44:12'),(44,21,'produto_21_69c17c19233063.77383867.png',0,'2026-03-23 17:44:57'),(45,22,'produto_22_69c17c3c3aebe3.59480377.png',0,'2026-03-23 17:45:32'),(46,23,'produto_23_69c17c5fb5b262.47036646.png',0,'2026-03-23 17:46:07');
/*!40000 ALTER TABLE `produto_imagens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-12 20:24:02
