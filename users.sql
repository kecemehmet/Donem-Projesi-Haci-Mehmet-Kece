-- --------------------------------------------------------
-- Sunucu:                       127.0.0.1
-- Sunucu sürümü:                8.4.3 - MySQL Community Server - GPL
-- Sunucu İşletim Sistemi:       Win64
-- HeidiSQL Sürüm:               12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- fitness_db için veritabanı yapısı dökülüyor
CREATE DATABASE IF NOT EXISTS `fitness_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `fitness_db`;

-- tablo yapısı dökülüyor fitness_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `height` float NOT NULL,
  `weight` float NOT NULL,
  `bmi` float DEFAULT NULL,
  `fitness_goal` varchar(50) DEFAULT NULL,
  `experience_level` varchar(50) DEFAULT NULL,
  `preferred_exercises` varchar(50) DEFAULT NULL,
  `workout_days` int DEFAULT NULL,
  `workout_duration` int DEFAULT NULL,
  `target_weight` float DEFAULT NULL,
  `target_set_date` date DEFAULT NULL,
  `target_achieved_date` date DEFAULT NULL,
  `show_name_in_success` tinyint(1) DEFAULT '0',
  `show_username_in_success` tinyint(1) DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- fitness_db.users: ~4 rows (yaklaşık) tablosu için veriler indiriliyor
DELETE FROM `users`;
INSERT INTO `users` (`id`, `username`, `password`, `email`, `height`, `weight`, `bmi`, `fitness_goal`, `experience_level`, `preferred_exercises`, `workout_days`, `workout_duration`, `target_weight`, `target_set_date`, `target_achieved_date`, `show_name_in_success`, `show_username_in_success`, `name`, `is_admin`) VALUES
	(7, 'deneme123', '$2y$10$jUO8lPK6IlVCNFJSEc2.W.DhrqyPz39kJ111j1ywRxEblV/sG8j9y', 'deneme@deneme.com', 175, 100, 32.6531, 'weight_loss', 'intermediate', 'strength', 7, 60, 100, '2025-03-12', '2025-03-19', 0, 0, 'Deneme Kullanıcı', 0),
	(8, 'admin', '$2y$10$VGp7dcRvdP6w9TBUgSDrMOYkg40aDV0pWVuIRkvDBWCVew4Ri0mpm', 'admin@gmail.com', 175, 120, 39.1837, 'endurance', 'advanced', 'flexibility', 6, 60, 100, '2025-03-19', NULL, 0, 0, 'admin', 1),
	(10, 'myildirim', '$2y$10$ZeFP9c2TJCSXnsuN85ZL/.I/q7Mfuy1t42HvLV7C9X9Th2lpCV9Ee', 'myildirim@gmail.com', 190, 85, 23.5457, 'muscle_gain', 'beginner', 'strength', 7, 120, 85, '2025-03-20', '2025-03-20', 1, 1, 'Muhammet Yıldırım', 0),
	(11, 'deneme12', '$2y$10$sG8HmBJk4zSQdb4kyFeomeuzsE3ri70nMqtBYst.ylXeZnuvTrPIq', 'deneme12@gmail.com', 190, 75, 20.7756, 'muscle_gain', 'beginner', 'strength', 7, 120, 75, '2025-03-20', '2025-03-20', 1, 1, '', 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
