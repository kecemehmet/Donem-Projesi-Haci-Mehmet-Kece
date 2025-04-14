-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 01 Nis 2025, 01:03:17
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `fitness_db`
--
CREATE DATABASE IF NOT EXISTS `fitness_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `fitness_db`;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `custom_workout_programs`
--

DROP TABLE IF EXISTS `custom_workout_programs`;
CREATE TABLE `custom_workout_programs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `day` varchar(20) NOT NULL,
  `activity` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `custom_workout_programs`
--

INSERT INTO `custom_workout_programs` (`id`, `user_id`, `day`, `activity`, `image`, `created_at`) VALUES
(112, 1, 'Sal??', '[]', NULL, '2025-03-30 23:29:01'),
(113, 1, '??ar??amba', '[]', NULL, '2025-03-30 23:29:01'),
(115, 1, 'Per??embe', '[]', NULL, '2025-03-30 23:29:01'),
(117, 1, 'Pazar', '{\"type\":\"rest\",\"message\":\"Bugün vücudunuzu dinlendirin.\"}', NULL, '2025-03-30 23:29:01'),
(128, 18, 'Cuma', '{\"type\":\"workout\",\"exercises\":[{\"name\":\"Plank\",\"sets\":3,\"duration\":\"30 saniye\",\"equipment\":\"Yok\",\"workout_name\":\"Core\"},{\"name\":\"Hafif Tempolu Koşu\",\"sets\":1,\"duration\":\"15 dakika\",\"equipment\":\"Yok\",\"workout_name\":\"Kardiyo\"},{\"name\":\"Tempolu Yürüyüş\",\"sets\":1,\"duration\":\"30 dakika\",\"equipment\":\"Yok\",\"workout_name\":\"Kardiyo\"},{\"name\":\"Jumping Jacks\",\"sets\":3,\"reps\":\"20\",\"equipment\":\"Yok\",\"workout_name\":\"HIIT\"}],\"duration\":60}', NULL, '2025-03-31 01:31:14'),
(129, 18, 'Cumartesi', '{\"type\":\"workout\",\"exercises\":[{\"name\":\"Wall Push-ups\",\"sets\":3,\"reps\":\"10\",\"equipment\":\"Yok\",\"workout_name\":\"Üst Vücut\"},{\"name\":\"Plank\",\"sets\":3,\"duration\":\"30 saniye\",\"equipment\":\"Yok\",\"workout_name\":\"Core\"},{\"name\":\"Diz Çekme\",\"sets\":3,\"reps\":\"15\",\"equipment\":\"Yok\",\"workout_name\":\"HIIT\"},{\"name\":\"Squat\",\"sets\":3,\"reps\":\"12\",\"equipment\":\"Yok\",\"workout_name\":\"Alt Vücut\"}],\"duration\":60}', NULL, '2025-03-31 01:31:14'),
(138, 1, 'Çarşamba', '{\"type\":\"workout\",\"exercises\":[{\"name\":\"Pistol Squats\",\"sets\":3,\"reps\":\"8 (her bacak)\",\"equipment\":\"Yok\",\"workout_name\":\"Alt Vücut\"},{\"name\":\"Plyometric Push-ups\",\"sets\":4,\"reps\":\"10\",\"equipment\":\"Yok\",\"workout_name\":\"Üst Vücut\"},{\"name\":\"Dragon Flags\",\"sets\":3,\"reps\":\"8\",\"equipment\":\"Bench\",\"workout_name\":\"Core\"},{\"name\":\"Box Jumps\",\"sets\":4,\"reps\":\"15\",\"equipment\":\"Box\",\"workout_name\":\"Pliometrik\"}],\"duration\":60}', NULL, '2025-03-31 16:00:50');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `exercises`
--

DROP TABLE IF EXISTS `exercises`;
CREATE TABLE `exercises` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('cardio','strength','flexibility','balance') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `equipment` varchar(255) NOT NULL,
  `sets` int(11) NOT NULL,
  `reps` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `exercises`
--

INSERT INTO `exercises` (`id`, `name`, `category`, `difficulty`, `equipment`, `sets`, `reps`, `description`, `created_at`) VALUES
(1, 'Y??r??y????', 'cardio', 'beginner', 'Yok', 1, '30 dakika', 'Temel kardiyo egzersizi', '2025-03-31 15:30:58'),
(2, 'Ko??u', 'cardio', 'intermediate', 'Yok', 1, '20 dakika', 'Orta seviye kardiyo egzersizi', '2025-03-31 15:30:58'),
(3, 'HIIT Ko??u', 'cardio', 'advanced', 'Yok', 4, '30 saniye ko??u, 30 saniye dinlenme', 'Y??ksek yo??unluklu interval antrenman??', '2025-03-31 15:30:58'),
(4, 'Bisiklet', 'cardio', 'beginner', 'Bisiklet', 1, '30 dakika', 'D??????k etkili kardiyo egzersizi', '2025-03-31 15:30:58'),
(5, 'Y??zme', 'cardio', 'intermediate', 'Yok', 1, '30 dakika', 'Tam v??cut kardiyo egzersizi', '2025-03-31 15:30:58'),
(6, '????nav', 'strength', 'beginner', 'Yok', 3, '10 tekrar', '??st v??cut kuvvet egzersizi', '2025-03-31 15:30:58'),
(7, 'Squat', 'strength', 'beginner', 'Yok', 3, '12 tekrar', 'Alt v??cut kuvvet egzersizi', '2025-03-31 15:30:58'),
(8, 'Dumbbell Press', 'strength', 'intermediate', 'Dumbbell', 4, '12 tekrar', 'Omuz kuvvet egzersizi', '2025-03-31 15:30:58'),
(9, 'Deadlift', 'strength', 'advanced', 'Barbell', 4, '8 tekrar', 'Tam v??cut kuvvet egzersizi', '2025-03-31 15:30:58'),
(10, 'Pull-up', 'strength', 'advanced', 'Pull-up bar', 3, '8 tekrar', '??st v??cut kuvvet egzersizi', '2025-03-31 15:30:58'),
(11, 'Yoga Pozlar??', 'flexibility', 'beginner', 'Yoga mat', 1, '30 saniye tut', 'Temel esneklik egzersizleri', '2025-03-31 15:30:58'),
(12, 'Stretching', 'flexibility', 'beginner', 'Yok', 1, '30 saniye tut', 'Genel esneklik egzersizleri', '2025-03-31 15:30:58'),
(13, 'Pilates', 'flexibility', 'intermediate', 'Yoga mat', 1, '45 dakika', 'Kor egzersizleri ve esneklik', '2025-03-31 15:30:58'),
(14, 'Mobilite ??al????mas??', 'flexibility', 'advanced', 'Yok', 1, '45 dakika', 'Geli??mi?? esneklik ve mobilite', '2025-03-31 15:30:58'),
(15, 'Tek Ayak ??st??nde Durma', 'balance', 'beginner', 'Yok', 3, '30 saniye', 'Temel denge egzersizi', '2025-03-31 15:30:58'),
(16, 'Bosu Top Egzersizleri', 'balance', 'intermediate', 'Bosu top', 3, '45 saniye', 'Orta seviye denge egzersizleri', '2025-03-31 15:30:58'),
(17, 'Yoga Denge Pozlar??', 'balance', 'advanced', 'Yoga mat', 4, '60 saniye', 'Geli??mi?? denge egzersizleri', '2025-03-31 15:30:58');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `rating`, `category`, `message`, `created_at`) VALUES
(1, 1, 5, 'feature', 'SA', '2025-03-31 19:40:48'),
(2, 1, 5, 'feature', 'sa', '2025-03-31 19:42:11');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'session_lifetime', '120', '2025-03-30 21:01:49', '2025-03-30 21:01:49'),
(2, 'max_upload_size', '10', '2025-03-30 21:01:49', '2025-03-30 21:01:49'),
(3, 'notify_new_user', '1', '2025-03-30 21:01:49', '2025-03-30 21:01:49'),
(4, 'notify_feedback', '1', '2025-03-30 21:01:49', '2025-03-30 21:01:49'),
(5, 'auto_backup', '1', '2025-03-30 21:01:49', '2025-03-30 21:01:49'),
(6, 'backup_frequency', 'daily', '2025-03-30 21:01:49', '2025-03-30 21:01:49');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `site_title` varchar(100) DEFAULT 'FitMate',
  `site_description` text DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `max_file_size` int(11) DEFAULT 5,
  `auto_backup` enum('daily','weekly','monthly','never') DEFAULT 'weekly',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `site_settings`
--

INSERT INTO `site_settings` (`id`, `site_title`, `site_description`, `contact_email`, `max_file_size`, `auto_backup`, `updated_at`) VALUES
(1, 'FitMate', 'FitMate - Kişisel Fitness Asistanınız', 'admin@fitmate.com', 5, 'weekly', '2025-03-31 19:35:12');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `height` float NOT NULL,
  `weight` float NOT NULL,
  `initial_weight` float DEFAULT NULL,
  `bmi` float DEFAULT NULL,
  `fitness_goal` varchar(50) DEFAULT NULL,
  `experience_level` varchar(50) DEFAULT NULL,
  `preferred_exercises` varchar(50) DEFAULT NULL,
  `workout_days` int(11) DEFAULT NULL,
  `workout_duration` int(11) DEFAULT NULL,
  `target_weight` float DEFAULT NULL,
  `target_set_date` date DEFAULT NULL,
  `target_achieved_date` date DEFAULT NULL,
  `show_name_in_success` tinyint(1) DEFAULT 0,
  `show_username_in_success` tinyint(1) DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `is_banned` tinyint(1) DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT 'images/default_profile.png',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `bmi_category` varchar(50) DEFAULT NULL,
  `health_risk` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `height`, `weight`, `initial_weight`, `bmi`, `fitness_goal`, `experience_level`, `preferred_exercises`, `workout_days`, `workout_duration`, `target_weight`, `target_set_date`, `target_achieved_date`, `show_name_in_success`, `show_username_in_success`, `name`, `is_admin`, `is_banned`, `profile_picture`, `last_login`, `created_at`, `bmi_category`, `health_risk`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gmail.com', 175, 108, 110, 35.3, 'weight_loss', 'advanced', 'cardio', 6, 60, 111, '0000-00-00', NULL, 1, 1, 'Admin', 1, 0, 'uploads/admin_profile.jpg', '2025-03-22 00:00:00', '2025-03-22 00:00:00', 'Normal', 'Normal', '2025-03-31 22:59:39'),
(2, 'test_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test@example.com', 170, 70, NULL, 24.22, 'muscle_gain', 'beginner', 'strength', 4, 45, 75, '2025-03-22', NULL, 1, 1, 'Test User', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(3, 'john_doe', '$2y$10$E.yxUA3BjqXEdQiselVMh.IsdJbVyr64eQdSuldIGQp0.EzdGqtkS', 'john@example.com', 175, 75, NULL, 24.49, 'weight_loss', 'intermediate', 'cardio', 5, 60, 70, '2025-03-22', NULL, 1, 1, 'John Doe', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(4, 'jane_smith', '$2y$10$4nq0l1SjseH1FGfSsjPe4e7MTXDtZoT7oiOddTp./uzkh36/Cu7Ni', 'jane@example.com', 165, 65, NULL, 23.88, 'muscle_gain', 'beginner', 'strength', 3, 45, 70, '2025-03-22', NULL, 1, 1, 'Jane Smith', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(5, 'mike_wilson', '$2y$10$6wPso9NvHXi4ffiyhGprYugCH1RhyuqVw6pYKGupKC7aRD6agrczO', 'mike@example.com1', 180, 85, NULL, 26.23, 'weight_loss', 'advanced', 'cardio', 6, 90, 80, '2025-03-22', NULL, 1, 1, 'Mike Wilson', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(7, 'david_brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'david@example.com', 175, 80, NULL, 26.12, 'weight_loss', 'beginner', 'cardio', 3, 45, 75, '2025-03-22', NULL, 1, 1, 'David Brown', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(8, 'emma_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emma@example.com', 165, 60, NULL, 22.04, 'muscle_gain', 'intermediate', 'strength', 5, 75, 65, '2025-03-22', NULL, 1, 1, 'Emma Wilson', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(9, 'james_miller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'james@example.com', 180, 90, NULL, 27.78, 'weight_loss', 'advanced', 'cardio', 6, 90, 85, '2025-03-22', NULL, 1, 1, 'James Miller', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(10, 'lisa_anderson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lisa@example.com', 170, 75, NULL, 25.95, 'muscle_gain', 'beginner', 'strength', 3, 45, 70, '2025-03-22', NULL, 1, 1, 'Lisa Anderson', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(11, 'robert_taylor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'robert@example.com', 175, 85, NULL, 27.76, 'weight_loss', 'intermediate', 'cardio', 4, 60, 80, '2025-03-22', NULL, 1, 1, 'Robert Taylor', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(12, 'sophia_white', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sophia@example.com', 165, 65, NULL, 23.88, 'muscle_gain', 'beginner', 'strength', 3, 45, 70, '2025-03-22', NULL, 1, 1, 'Sophia White', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(13, 'william_moore', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'william@example.com', 180, 95, NULL, 29.32, 'weight_loss', 'advanced', 'cardio', 6, 90, 90, '2025-03-22', NULL, 1, 1, 'William Moore', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(14, 'olivia_jackson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'olivia@example.com', 170, 70, NULL, 24.22, 'muscle_gain', 'intermediate', 'strength', 4, 60, 75, '2025-03-22', NULL, 1, 1, 'Olivia Jackson', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(15, 'daniel_lee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'daniel@example.com', 175, 80, NULL, 26.12, 'weight_loss', 'beginner', 'cardio', 3, 45, 75, '2025-03-22', NULL, 1, 1, 'Daniel Lee', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(16, 'ava_martin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ava@example.com', 165, 60, NULL, 22.04, 'muscle_gain', 'intermediate', 'strength', 5, 75, 65, '2025-03-22', NULL, 1, 1, 'Ava Martin', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(17, 'lucas_thompson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lucas@example.com', 180, 85, NULL, 26.23, 'weight_loss', 'advanced', 'cardio', 6, 90, 80, '2025-03-22', NULL, 1, 1, 'Lucas Thompson', 0, 0, 'images/default_profile.png', '2025-03-22 00:00:00', '2025-03-22 00:00:00', NULL, NULL, '2025-03-31 20:22:49'),
(18, 'Kral Abi Baba Pro', '$2y$10$8kCkyrQPC.yvITGHwHV9mePr69B9AmOtyyYx7gj0ucopF7NucWrSG', 'denemeeekral31@gmail.com', 187, 87, 87, 24.8792, 'endurance', 'beginner', 'flexibility', 6, 60, 87, '0000-00-00', '2025-03-31', 1, 1, 'denemeeekral31', 0, 0, 'images/default_profile.png', NULL, '2025-03-31 23:14:38', NULL, NULL, '2025-03-31 20:43:48'),
(20, 'deneme123', '$2y$10$.ZpcmiN98vdGCrk6WnoAY.2cPjobUbGDRpJMi65oRumiy4ljESDF.', 'deneme123@gmail.com', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 0, 'images/default_profile.png', NULL, '2025-03-31 23:14:38', NULL, NULL, '2025-03-31 20:22:49'),
(21, 'denemebida', '$2y$10$joEvB3zMqVRGwF19QUQLZub8zt74usaGVkMpIgnVa0wfxLIFRZ54W', 'denemebida@gmail.com', 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 0, 'images/default_profile.png', NULL, '2025-03-31 23:15:23', NULL, NULL, '2025-03-31 20:22:49');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_activities`
--

DROP TABLE IF EXISTS `user_activities`;
CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) DEFAULT NULL,
  `calories_burned` int(11) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `activity_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_bmi`
--

DROP TABLE IF EXISTS `user_bmi`;
CREATE TABLE `user_bmi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `height` float NOT NULL,
  `weight` float NOT NULL,
  `bmi` float NOT NULL,
  `bmi_category` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_programs`
--

DROP TABLE IF EXISTS `user_programs`;
CREATE TABLE `user_programs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `program` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`program`)),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `user_programs`
--

INSERT INTO `user_programs` (`id`, `user_id`, `program_name`, `start_date`, `duration`, `program`, `updated_at`, `created_at`) VALUES
(1, 1, '', '0000-00-00', 0, '{\"Pazartesi\":[{\"name\":\"Dragon Flags\",\"sets\":3,\"reps\":\"8\",\"equipment\":\"Bench\",\"workout_name\":\"Core\"},{\"name\":\"HIIT Sprint\",\"sets\":8,\"duration\":\"30 saniye sprint + 30 saniye dinlenme\",\"equipment\":\"Yok\",\"workout_name\":\"Kardiyo\"},{\"name\":\"Plyometric Push-ups\",\"sets\":4,\"reps\":\"10\",\"equipment\":\"Yok\",\"workout_name\":\"Üst Vücut\"},{\"name\":\"Pistol Squats\",\"sets\":3,\"reps\":\"8 (her bacak)\",\"equipment\":\"Yok\",\"workout_name\":\"Alt Vücut\"}],\"Salı\":[{\"name\":\"Pistol Squats\",\"sets\":3,\"reps\":\"8 (her bacak)\",\"equipment\":\"Yok\",\"workout_name\":\"Alt Vücut\"},{\"name\":\"Plyometric Push-ups\",\"sets\":4,\"reps\":\"10\",\"equipment\":\"Yok\",\"workout_name\":\"Üst Vücut\"},{\"name\":\"Box Jumps\",\"sets\":4,\"reps\":\"15\",\"equipment\":\"Box\",\"workout_name\":\"Pliometrik\"},{\"name\":\"Dragon Flags\",\"sets\":3,\"reps\":\"8\",\"equipment\":\"Bench\",\"workout_name\":\"Core\"}],\"Çarşamba\":[{\"name\":\"Dragon Flags\",\"sets\":3,\"reps\":\"8\",\"equipment\":\"Bench\",\"workout_name\":\"Core\"},{\"name\":\"Plyometric Push-ups\",\"sets\":4,\"reps\":\"10\",\"equipment\":\"Yok\",\"workout_name\":\"Üst Vücut\"},{\"name\":\"HIIT Sprint\",\"sets\":8,\"duration\":\"30 saniye sprint + 30 saniye dinlenme\",\"equipment\":\"Yok\",\"workout_name\":\"Kardiyo\"},{\"name\":\"Box Jumps\",\"sets\":4,\"reps\":\"15\",\"equipment\":\"Box\",\"workout_name\":\"Pliometrik\"}],\"Perşembe\":[{\"name\":\"Box Jumps\",\"sets\":4,\"reps\":\"15\",\"equipment\":\"Box\",\"workout_name\":\"Pliometrik\"},{\"name\":\"HIIT Sprint\",\"sets\":8,\"duration\":\"30 saniye sprint + 30 saniye dinlenme\",\"equipment\":\"Yok\",\"workout_name\":\"Kardiyo\"},{\"name\":\"Plyometric Push-ups\",\"sets\":4,\"reps\":\"10\",\"equipment\":\"Yok\",\"workout_name\":\"Üst Vücut\"},{\"name\":\"Box Jumps\",\"sets\":4,\"reps\":\"15\",\"equipment\":\"Box\",\"workout_name\":\"Pliometrik\"},{\"name\":\"Muscle Ups\",\"sets\":3,\"reps\":\"5\",\"equipment\":\"Bar\",\"workout_name\":\"Üst Vücut\"}],\"Cuma\":[{\"name\":\"Pistol Squats\",\"sets\":3,\"reps\":\"8 (her bacak)\",\"equipment\":\"Yok\",\"workout_name\":\"Alt Vücut\"},{\"name\":\"HIIT Sprint\",\"sets\":8,\"duration\":\"30 saniye sprint + 30 saniye dinlenme\",\"equipment\":\"Yok\",\"workout_name\":\"Kardiyo\"},{\"name\":\"Plyometric Push-ups\",\"sets\":4,\"reps\":\"10\",\"equipment\":\"Yok\",\"workout_name\":\"Üst Vücut\"}],\"Cumartesi\":[{\"name\":\"Pistol Squats\",\"sets\":3,\"reps\":\"8 (her bacak)\",\"equipment\":\"Yok\",\"workout_name\":\"Alt Vücut\"},{\"name\":\"HIIT Sprint\",\"sets\":8,\"duration\":\"30 saniye sprint + 30 saniye dinlenme\",\"equipment\":\"Yok\",\"workout_name\":\"Kardiyo\"},{\"name\":\"Muscle Ups\",\"sets\":3,\"reps\":\"5\",\"equipment\":\"Bar\",\"workout_name\":\"Üst Vücut\"},{\"name\":\"Plyometric Push-ups\",\"sets\":4,\"reps\":\"10\",\"equipment\":\"Yok\",\"workout_name\":\"Üst Vücut\"}],\"Pazar\":[]}', '2025-03-31 21:59:36', '2025-03-31 21:59:36');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `weight_history`
--

DROP TABLE IF EXISTS `weight_history`;
CREATE TABLE `weight_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `workout_programs`
--

DROP TABLE IF EXISTS `workout_programs`;
CREATE TABLE `workout_programs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `days_per_week` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `workout_programs`
--

INSERT INTO `workout_programs` (`id`, `user_id`, `program_name`, `description`, `difficulty`, `days_per_week`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ba??lang???? Seviyesi Program', 'Fitness yolculu??una yeni ba??layanlar i??in temel egzersizlerden olu??an program.', 'beginner', 3, '2025-03-31 15:30:58', '2025-03-31 15:30:58'),
(2, 1, 'Orta Seviye G???? Antrenman??', 'Kas k??tlesi kazanmak i??in tasarlanm???? orta seviye program.', 'intermediate', 4, '2025-03-31 15:30:58', '2025-03-31 15:30:58'),
(3, 1, '??leri Seviye Kardiyo Program', 'Dayan??kl??l??k ve ya?? yak??m?? odakl?? ileri seviye program.', 'advanced', 5, '2025-03-31 15:30:58', '2025-03-31 15:30:58');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `custom_workout_programs`
--
ALTER TABLE `custom_workout_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `user_bmi`
--
ALTER TABLE `user_bmi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `user_programs`
--
ALTER TABLE `user_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `weight_history`
--
ALTER TABLE `weight_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `workout_programs`
--
ALTER TABLE `workout_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_workout_programs_user_id` (`user_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `custom_workout_programs`
--
ALTER TABLE `custom_workout_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- Tablo için AUTO_INCREMENT değeri `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Tablo için AUTO_INCREMENT değeri `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Tablo için AUTO_INCREMENT değeri `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `user_bmi`
--
ALTER TABLE `user_bmi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `user_programs`
--
ALTER TABLE `user_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `weight_history`
--
ALTER TABLE `weight_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `workout_programs`
--
ALTER TABLE `workout_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `custom_workout_programs`
--
ALTER TABLE `custom_workout_programs`
  ADD CONSTRAINT `custom_workout_programs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `user_activities`
--
ALTER TABLE `user_activities`
  ADD CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `user_bmi`
--
ALTER TABLE `user_bmi`
  ADD CONSTRAINT `user_bmi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `user_programs`
--
ALTER TABLE `user_programs`
  ADD CONSTRAINT `user_programs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `weight_history`
--
ALTER TABLE `weight_history`
  ADD CONSTRAINT `weight_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `workout_programs`
--
ALTER TABLE `workout_programs`
  ADD CONSTRAINT `fk_workout_programs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
