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

-- tablo yapısı dökülüyor fitness_db.custom_workout_programs
CREATE TABLE IF NOT EXISTS `custom_workout_programs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `day` varchar(20) NOT NULL,
  `activity` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `custom_workout_programs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- fitness_db.custom_workout_programs: ~0 rows (yaklaşık) tablosu için veriler indiriliyor
DELETE FROM `custom_workout_programs`;

-- tablo yapısı dökülüyor fitness_db.feedback
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `feedback_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `response_text` text,
  `response_status` enum('unresponded','responded','read') DEFAULT 'unresponded',
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- fitness_db.feedback: ~3 rows (yaklaşık) tablosu için veriler indiriliyor
DELETE FROM `feedback`;
INSERT INTO `feedback` (`id`, `user_id`, `feedback_text`, `created_at`, `response_text`, `response_status`, `responded_at`) VALUES
	(1, 8, 'asdasgasffasfasfsfasgasgfasgasfasfasasdasgasffasfasfsfasgasgfasgasfasfas', '2025-03-22 00:08:28', NULL, 'unresponded', NULL),
	(2, 8, 'dsafasfasfasfasfasfadsafasfasfasfasfasfadsafasfasfasfasfasfadsafasfasfasfasfasfa', '2025-03-22 02:12:04', NULL, 'unresponded', NULL),
	(3, 8, 'asdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjasasdasffasfasfasfasnbfasjfnasjfnasfjas', '2025-03-28 11:05:31', NULL, 'unresponded', NULL);

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
  `is_banned` tinyint(1) DEFAULT '0',
  `profile_picture` varchar(255) DEFAULT 'images/default_profile.png',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- fitness_db.users: ~7 rows (yaklaşık) tablosu için veriler indiriliyor
DELETE FROM `users`;
INSERT INTO `users` (`id`, `username`, `password`, `email`, `height`, `weight`, `bmi`, `fitness_goal`, `experience_level`, `preferred_exercises`, `workout_days`, `workout_duration`, `target_weight`, `target_set_date`, `target_achieved_date`, `show_name_in_success`, `show_username_in_success`, `name`, `is_admin`, `is_banned`, `profile_picture`, `last_login`, `created_at`) VALUES
	(7, 'deneme123', '$2y$10$jUO8lPK6IlVCNFJSEc2.W.DhrqyPz39kJ111j1ywRxEblV/sG8j9y', 'deneme@deneme.com', 175, 100, 32.6531, 'weight_loss', 'intermediate', 'strength', 7, 60, 100, '2025-03-12', '2025-03-19', 0, 0, 'Deneme Kullanıcı', 0, 0, 'images/default_profile.png', NULL, '2025-03-28 15:03:30'),
	(8, 'admin', '$2y$10$VGp7dcRvdP6w9TBUgSDrMOYkg40aDV0pWVuIRkvDBWCVew4Ri0mpm', 'admin@gmail.com', 175, 120, 39.1837, 'endurance', 'advanced', 'flexibility', 5, 150, 100, '2025-03-19', NULL, 1, 1, 'admin', 1, 0, 'uploads/admin_profile.jpg', NULL, '2025-03-28 15:03:30'),
	(10, 'myildirim', '$2y$10$ZeFP9c2TJCSXnsuN85ZL/.I/q7Mfuy1t42HvLV7C9X9Th2lpCV9Ee', 'myildirim31@gmail.com', 190, 85, 23.5457, 'muscle_gain', 'beginner', 'strength', 7, 120, 85, '2025-03-20', '2025-03-20', 1, 1, 'Muhammet Yıldırım', 0, 0, 'images/default_profile.png', NULL, '2025-03-28 15:03:30'),
	(11, 'deneme12', '$2y$10$sG8HmBJk4zSQdb4kyFeomeuzsE3ri70nMqtBYst.ylXeZnuvTrPIq', 'deneme12@gmail.com', 190, 75, 20.7756, 'muscle_gain', 'beginner', 'strength', 7, 120, 75, '2025-03-20', '2025-03-20', 1, 1, 'DENEME12', 0, 0, 'images/default_profile.png', NULL, '2025-03-28 15:03:30'),
	(12, 'kerimdeneme', '$2y$10$SampleHash1234567890abcdefgHIJKLMNOPQRS', 'kerimdeneme@gmail.com', 180, 80, 24.6914, 'general_fitness', 'intermediate', 'cardio', 5, 90, 75, '2025-03-21', NULL, 0, 0, 'Kerim Deneme 1', 0, 0, 'images/default_profile.png', NULL, '2025-03-28 15:03:30'),
	(16, 'yanilmadeneme', '$2y$10$Yayg7hFO4qnuJ2aBFAev.e25uflKtBGl.At6649PeCr4aFiC6EfNu', 'yanilmadeneme@gmail.com', 175, 80, 26.1224, 'weight_loss', 'beginner', 'cardio', 7, 60, 85, '2025-03-22', NULL, 0, 0, 'Deneme Yanılma', 0, 0, 'images/default_profile.png', NULL, '2025-03-28 15:03:30'),
	(17, 'admindeneme', '$2y$10$1kLZE7s9nvwHTcx3dyTlmOg88b20MnU70aVkXVL6HHeCk6.KVi0e6', 'deneme@admin.com', 175, 80, 26.1224, 'weight_loss', 'beginner', 'cardio', 7, 60, 85, '2025-03-22', NULL, 0, 0, 'Admin Deneme', 0, 0, 'images/default_profile.png', NULL, '2025-03-28 15:03:30');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
