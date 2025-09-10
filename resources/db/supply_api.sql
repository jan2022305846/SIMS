-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 06, 2025 at 07:37 PM
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
-- Database: `supply_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `log_name`, `description`, `subject_type`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `event`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8001\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-04 15:26:34'),
(2, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8001\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-04 15:26:50'),
(3, 'user_activity', '{causer} logged into the system', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"POST\",\"path\":\"login\",\"url\":\"http:\\/\\/127.0.0.1:8001\\/login\",\"status_code\":302,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-04 22:37:46'),
(4, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8001\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-04 22:37:46'),
(5, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8001\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-04 22:37:54'),
(6, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8001\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-04 22:38:06'),
(7, 'user_activity', '{causer} logged into the system', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"POST\",\"path\":\"login\",\"url\":\"http:\\/\\/127.0.0.1:8001\\/login\",\"status_code\":302,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:13:45'),
(8, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8001\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:13:45'),
(9, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:56:39'),
(10, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:56:44'),
(11, 'user_activity', '{causer} viewed user list', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"users\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/users\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:56:45'),
(12, 'user_activity', '{causer} viewed items list', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"items\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/items\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:56:47'),
(13, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:56:57'),
(14, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:56:58'),
(15, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 08:57:38'),
(16, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:00:00'),
(17, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:00:00'),
(18, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:00:01'),
(19, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:00:02'),
(20, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:00:53'),
(21, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:00:56'),
(22, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:09'),
(23, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:10'),
(24, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:10'),
(25, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:11'),
(26, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:22'),
(27, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:22'),
(28, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:22'),
(29, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:22'),
(30, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:24'),
(31, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:25'),
(32, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:44'),
(33, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:45'),
(34, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:45'),
(35, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:02:59'),
(36, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:03:00'),
(37, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:03:02'),
(38, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:05:30'),
(39, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:05:31'),
(40, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:05:31'),
(41, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:05:47'),
(42, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:27'),
(43, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:28'),
(44, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:28'),
(45, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:28'),
(46, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:28'),
(47, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:34'),
(48, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:35'),
(49, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:35'),
(50, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:37'),
(51, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:39'),
(52, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:07:41'),
(53, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:08:08'),
(54, 'user_activity', '{causer} accessed QR code scanner', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"qr\\/scanner\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/qr\\/scanner\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:08:10'),
(55, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:08:37'),
(56, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:14'),
(57, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:15'),
(58, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:15'),
(59, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:15'),
(60, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:15'),
(61, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:16'),
(62, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:16'),
(63, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:16'),
(64, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:16'),
(65, 'user_activity', '{causer} accessed the dashboard', NULL, NULL, 'App\\Models\\User', 6, '{\"method\":\"GET\",\"path\":\"dashboard\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/dashboard\",\"status_code\":200,\"user_agent\":\"Mozilla\\/5.0 (X11; Linux x86_64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"ip_address\":\"127.0.0.1\"}', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-09-06 09:09:28');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('consumable','non-consumable') NOT NULL DEFAULT 'non-consumable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Consumable', NULL, 'non-consumable', '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(2, 'Non-Consumable', NULL, 'non-consumable', '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(3, 'Office Supplies', 'Basic office supplies and stationery', 'consumable', '2025-09-03 13:40:55', '2025-09-06 09:21:08'),
(4, 'Electronics', 'Electronic equipment and devices', 'non-consumable', '2025-09-03 13:40:55', '2025-09-06 09:21:08'),
(5, 'Furniture', 'Office furniture and fixtures', 'non-consumable', '2025-09-03 13:40:55', '2025-09-06 09:21:08'),
(6, 'Cleaning Supplies', 'Cleaning materials and chemicals', 'consumable', '2025-09-03 13:40:55', '2025-09-06 09:21:08'),
(7, 'IT Equipment', 'Computers, printers, and IT hardware', 'non-consumable', '2025-09-06 09:21:08', '2025-09-06 09:21:08'),
(8, 'Medical Supplies', 'Medical and first aid supplies', 'consumable', '2025-09-06 09:21:08', '2025-09-06 09:21:08'),
(9, 'Educational Materials', 'Books, teaching aids, and educational resources', 'non-consumable', '2025-09-06 09:21:08', '2025-09-06 09:21:08'),
(10, 'Laboratory Equipment', 'Scientific instruments and lab equipment', 'non-consumable', '2025-09-06 09:21:08', '2025-09-06 09:21:08'),
(11, 'Safety Equipment', 'Safety gear and protective equipment', 'non-consumable', '2025-09-06 09:21:08', '2025-09-06 09:21:08'),
(12, 'Consumables', 'General consumable items', 'consumable', '2025-09-06 09:21:09', '2025-09-06 09:21:09');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `qr_code_data` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `warranty_date` date DEFAULT NULL,
  `minimum_stock` int(11) NOT NULL DEFAULT 1,
  `maximum_stock` int(11) NOT NULL DEFAULT 100,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_holder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `assignment_notes` text DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `condition` varchar(255) NOT NULL DEFAULT 'Good',
  `qr_code` varchar(255) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `category_id`, `name`, `description`, `barcode`, `qr_code_data`, `quantity`, `price`, `brand`, `supplier`, `warranty_date`, `minimum_stock`, `maximum_stock`, `current_stock`, `unit_price`, `total_value`, `current_holder_id`, `assigned_at`, `assignment_notes`, `unit`, `location`, `condition`, `qr_code`, `expiry_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'id qui', 'Adipisci tenetur dolores fugit.', NULL, NULL, 31, 4670, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'IT Building', 'Needs Repair', 'c0073a32-9739-3440-9e13-d89c7fd9edc2', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(2, 1, 'perspiciatis provident', 'Sit vel et nam asperiores.', NULL, NULL, 9, 3199, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'Faculty Office', 'Needs Repair', '41ad34fb-97dd-3a8f-b6b6-6597d38c73ef', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(3, 2, 'voluptas quos', 'Repellendus consectetur laudantium ratione eligendi.', NULL, NULL, 37, 26250, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'liters', 'Admin Office', 'Good', '999e8a5d-f2e4-3b59-8c01-da69b2afeb15', '2025-05-30', '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(4, 1, 'tenetur porro', 'Id nihil quaerat porro iusto.', NULL, NULL, 4, 29602, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'pcs', 'IT Building', 'Needs Repair', 'd6e5e6e0-7784-341a-b0d0-b60a46adf20b', '2026-03-17', '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(5, 2, 'vel aliquid', 'Nam maiores similique ea consequatur laboriosam dolorem ea.', NULL, NULL, 6, 15006, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'Admin Office', 'Good', '8037db42-0d9f-34ba-8807-44a35061cf30', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(6, 1, 'ipsam et', 'Labore ut omnis quis magnam libero.', NULL, NULL, 3, 672, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'IT Building', 'Needs Repair', '01848533-c75b-36ca-9c74-6734c376e006', '2025-12-02', '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(7, 2, 'laborum doloremque', 'Sint sit autem cumque incidunt nam.', NULL, NULL, 43, 36059, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'Faculty Office', 'Good', '6bbd27e1-6903-33a1-9515-0caf2c72dd18', '2025-07-08', '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(8, 1, 'ea cupiditate', 'Hic placeat nihil nobis atque voluptatem possimus.', NULL, NULL, 39, 26569, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'liters', 'Admin Office', 'Needs Repair', '8dd57c30-b71a-3131-90b5-600f8af7ab46', '2026-01-10', '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(9, 1, 'repellat voluptatem', 'Et ea est molestiae inventore eos et.', NULL, NULL, 20, 25458, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'pcs', 'IT Building', 'Good', '66597f3f-8a04-384b-91d2-b1cbb5abb2ed', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(10, 2, 'repellat velit', 'Quaerat voluptatem natus dolores cupiditate labore dicta sed.', NULL, NULL, 11, 4964, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'Faculty Office', 'Needs Repair', '4be0621c-ef72-3680-b6ab-900b575e0f11', '2026-01-02', '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(11, 1, 'unde dignissimos', 'Tempore aspernatur illum aut error voluptas eveniet odio.', NULL, NULL, 44, 43302, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'pcs', 'Admin Office', 'Needs Repair', '2c1e8861-94c3-3c39-b674-65ecd274c379', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(12, 1, 'dolorum eius', 'Consequatur ex cumque est doloremque molestiae.', NULL, NULL, 45, 46126, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'Admin Office', 'Needs Repair', '6c85a477-3cc5-314f-8fd6-8c9bc65053b5', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(13, 1, 'rem nostrum', 'Quae similique vero aut non aliquid consequatur veritatis voluptas.', NULL, NULL, 32, 47794, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'liters', 'Admin Office', 'Needs Repair', '7bbe85a0-0da7-3cf0-9f24-2e5eebf5ad37', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(14, 2, 'nihil eos', 'Quod impedit consequatur vel quaerat aperiam ut aut dolore.', NULL, NULL, 8, 641, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'Admin Office', 'Good', '09d567fe-7b1d-3d9c-aeb1-3a9ecb74fb62', '2025-06-22', '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(15, 2, 'optio id', 'Sapiente vel repellat quas ex et autem delectus consequuntur.', NULL, NULL, 8, 46351, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'liters', 'Faculty Office', 'Needs Repair', 'e1fb8120-7fac-388e-8f9a-13aa44e8d9e9', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08', NULL),
(16, 2, 'Bond Paper', 'USA BRAND', NULL, NULL, 12, 1233, NULL, NULL, NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'boxes', 'Supply Office', 'New', '2fe5b5e5-dcdf-3363-80f5-6bee3545ce02', '2025-05-23', '2025-05-25 06:19:26', '2025-05-25 06:19:26', NULL),
(17, 1, 'Ballpoint Pen (Blue)', 'Standard blue ink ballpoint pen', NULL, NULL, 50, 15, 'Pilot', 'Office Depot', NULL, 10, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'pieces', 'Storage Room A', 'Good', 'PEN-001', NULL, '2025-09-03 13:43:12', '2025-09-03 13:43:12', NULL),
(18, 1, 'A4 Bond Paper', '500 sheets white bond paper', NULL, NULL, 25, 250, 'Paperline', 'National Bookstore', NULL, 5, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'reams', 'Storage Room A', 'Good', 'PAPER-001', NULL, '2025-09-03 13:43:12', '2025-09-03 13:43:12', NULL),
(19, 2, 'Wireless Mouse', 'Wireless optical mouse', NULL, NULL, 8, 850, 'Logitech', 'PC Express', NULL, 3, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'pieces', 'IT Storage', 'Good', 'MOUSE-001', NULL, '2025-09-03 13:43:12', '2025-09-03 13:43:12', NULL),
(20, 3, 'Office Chair', 'Ergonomic office chair with back support', NULL, NULL, 2, 5500, 'Ergoflex', 'Furniture Plus', NULL, 1, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'pieces', 'Furniture Storage', 'Good', 'CHAIR-001', NULL, '2025-09-03 13:43:12', '2025-09-03 13:43:12', NULL),
(21, 4, 'Hand Sanitizer', '500ml alcohol-based hand sanitizer', NULL, NULL, 5, 120, 'SafeGuard', 'Mercury Drug', NULL, 10, 100, 0, 0.00, 0.00, NULL, NULL, NULL, 'bottles', 'Medical Storage', 'Good', 'SANITIZER-001', '2027-03-03', '2025-09-03 13:43:12', '2025-09-03 13:43:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `item_scan_logs`
--

CREATE TABLE `item_scan_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT '2025-09-06 08:43:15',
  `location` varchar(255) DEFAULT NULL,
  `scanner_type` varchar(255) NOT NULL DEFAULT 'admin',
  `scan_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scan_data`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(5, '2025_04_15_032149_update_users_table', 1),
(18, '0001_01_01_000000_create_users_table', 2),
(19, '0001_01_01_000001_create_cache_table', 2),
(20, '0001_01_01_000002_create_jobs_table', 2),
(21, '2025_04_15_024237_create_personal_access_tokens_table', 2),
(22, '2025_04_30_020618_create_categories_table', 2),
(23, '2025_04_30_020741_create_items_table', 2),
(24, '2025_04_30_020834_create_requests_table', 2),
(26, '2025_04_30_020908_create_logs_table', 3),
(27, '2025_09_03_213339_add_school_id_to_users_table', 3),
(28, '2025_09_03_214024_add_description_to_categories_table', 4),
(29, '2025_09_03_214215_add_missing_columns_to_items_table', 5),
(30, '2025_09_03_214641_add_missing_columns_to_requests_table', 6),
(31, '2025_09_04_220205_add_qr_code_fields_to_items_table', 7),
(32, '2025_09_04_222904_add_advanced_workflow_to_requests_table', 8),
(33, '2025_09_04_223029_add_advanced_workflow_to_requests_table', 8),
(34, '2025_09_04_225548_create_activity_logs_table', 9),
(35, '2025_09_06_163047_create_offices_table', 10),
(36, '2025_09_06_163136_create_item_scan_logs_table', 10),
(37, '2025_09_06_163216_add_current_holder_id_to_items_table', 10),
(38, '2025_09_06_163322_add_office_id_to_users_table', 10),
(39, '2025_09_06_164656_add_user_id_to_item_scan_logs_table', 11),
(40, '2025_09_06_171947_add_type_to_categories_table', 12),
(41, '2025_09_06_172342_add_report_fields_to_requests_table', 13);

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `office_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `name`, `code`, `description`, `location`, `office_head_id`, `created_at`, `updated_at`) VALUES
(1, 'Supply Office', 'SUPPLY', 'Main supply and inventory management office', 'Administration Building, Room 101', 8, '2025-09-06 08:43:16', '2025-09-06 08:43:17'),
(2, 'Computer Science Department', 'BSIT', 'Bachelor of Science in Information Technology', 'IT Building, 2nd Floor', 9, '2025-09-06 08:43:16', '2025-09-06 08:43:17'),
(3, 'Business Management Department', 'BSMB', 'Bachelor of Science in Management', 'Business Building, 3rd Floor', NULL, '2025-09-06 08:43:16', '2025-09-06 08:43:16'),
(4, 'Home Economics Department', 'BTLE-HE', 'Bachelor of Technology and Livelihood Education - Home Economics', 'TLE Building, 1st Floor', NULL, '2025-09-06 08:43:16', '2025-09-06 08:43:16'),
(5, 'Industrial Arts Department', 'BTLE-IA', 'Bachelor of Technology and Livelihood Education - Industrial Arts', 'TLE Building, 2nd Floor', NULL, '2025-09-06 08:43:16', '2025-09-06 08:43:16'),
(6, 'Engineering Department', 'BSIE', 'Bachelor of Science in Industrial Engineering', 'Engineering Building, Ground Floor', NULL, '2025-09-06 08:43:16', '2025-09-06 08:43:16');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('abuabujanny99@gmail.com', '$2y$12$Ewj0N7.Nurkbg7YH6OBYiOnOUzkzd8U0zkAp1rSH3BmOwal9K0brm', '2025-06-08 01:57:41');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(215, 'App\\Models\\User', 2, 'auth_token', 'fe60a6e7187d22ff9a0c45cfbd394656773b9c350e834d527ab097a36b5d3b42', '[\"*\"]', '2025-06-11 17:20:11', NULL, '2025-06-11 16:12:34', '2025-06-11 17:20:11'),
(216, 'App\\Models\\User', 1, 'auth_token', '50bdf09e61cb71a02d36a9abda9347ed48ae2da8973cf10190ae6ea4866a1795', '[\"*\"]', '2025-06-11 20:19:18', NULL, '2025-06-11 20:18:42', '2025-06-11 20:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_approved` int(11) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `needed_date` date DEFAULT NULL,
  `status` enum('pending','approved','declined','returned') NOT NULL DEFAULT 'pending',
  `workflow_status` enum('pending','approved_by_office_head','approved_by_admin','fulfilled','claimed','declined_by_office_head','declined_by_admin') NOT NULL DEFAULT 'pending',
  `approved_by_office_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by_admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fulfilled_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `claimed_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `office_head_approval_date` timestamp NULL DEFAULT NULL,
  `admin_approval_date` timestamp NULL DEFAULT NULL,
  `fulfilled_date` timestamp NULL DEFAULT NULL,
  `claimed_date` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `office_head_notes` text DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `claim_slip_number` varchar(255) DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `request_date` date NOT NULL DEFAULT '2025-05-21',
  `remarks` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `user_id`, `item_id`, `quantity`, `quantity_approved`, `purpose`, `needed_date`, `status`, `workflow_status`, `approved_by_office_head_id`, `approved_by_admin_id`, `fulfilled_by_id`, `claimed_by_id`, `office_head_approval_date`, `admin_approval_date`, `fulfilled_date`, `claimed_date`, `processed_at`, `processed_by`, `department`, `office_head_notes`, `priority`, `claim_slip_number`, `attachments`, `request_date`, `remarks`, `admin_notes`, `approval_date`, `return_date`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 1, NULL, NULL, NULL, 'returned', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '2022-12-15', NULL, NULL, '2015-07-10', '1970-02-12', '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(2, 4, 5, 5, NULL, NULL, NULL, 'returned', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '2013-04-02', NULL, NULL, NULL, '1983-11-22', '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(3, 3, 15, 1, NULL, NULL, NULL, 'returned', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '2023-09-10', NULL, NULL, NULL, NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(4, 3, 4, 1, NULL, NULL, NULL, 'approved', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '2010-08-13', NULL, NULL, '1993-01-22', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(5, 4, 12, 1, NULL, NULL, NULL, 'returned', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '1992-09-05', NULL, NULL, NULL, '2006-11-11', '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(6, 5, 6, 3, NULL, NULL, NULL, 'returned', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '1995-04-19', NULL, NULL, NULL, NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(7, 3, 1, 4, NULL, NULL, NULL, 'declined', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '1992-07-13', NULL, NULL, '2003-04-01', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(8, 3, 5, 4, NULL, NULL, NULL, 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '1998-07-16', NULL, NULL, '1999-01-28', NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(9, 4, 12, 3, NULL, NULL, NULL, 'declined', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '1979-09-01', NULL, NULL, NULL, NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(10, 3, 13, 1, NULL, NULL, NULL, 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '1983-10-02', NULL, NULL, NULL, NULL, '2025-05-21 12:57:08', '2025-05-21 12:57:08'),
(11, 2, 1, 10, NULL, 'For classroom use', '2025-09-10', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '2025-05-21', NULL, NULL, NULL, NULL, '2025-09-03 13:47:27', '2025-09-03 13:47:27'),
(12, 2, 2, 2, NULL, 'For student handouts', '2025-09-06', 'approved', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'normal', NULL, NULL, '2025-05-21', NULL, 'Approved for educational use', NULL, NULL, '2025-09-03 13:47:27', '2025-09-03 13:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3YIRdCjwoe3XTooM3LbEIl1BJFQdlQVS8bjrOMVO', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVWF5RjVzQXI1RGxJOU15Z3J6R25xNURQTmNuUEkwRXBHNmJRRVMwSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjI5OTM2Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936229),
('5iWpzTnuVoUyiIJQvblBrW19JQV27ktPaiYnTjT9', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRGRqck0wM3ZQMGNwbEUxUGFWWm5udGZTcHAwd1FLc0lmUHFOaUdSTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9kYXNoYm9hcmQiO319', 1756936955),
('5ra3oLIUR2bSz12ri8oPgBbOzQ6c9RfmJw7iN6zc', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidDVMQkdlemhVTG9YQXpoYkxGaVF4Vk5tcU1yZm5GWXYyblJudEtGbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MTQ5MjYyIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936149),
('5y6cM4MXah2KUflQDlvWo3ws6bW1Tsi8AK33RopF', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiclBJRzJURTlKb3hQb1F4QlFrSnN1TExSU3Y5VWFFN2xBM3UweDZSWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjYxMTY4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936261),
('6FbicHUCJ4CgzCAz5Ag7cI1faZBHuOGXYJ3MrqGV', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUVU2RENqSzVNaXJmSVBBUlRVTUYxWXJnY016czgwc0FOZlZpakFQVCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('8cFiVa7QVPDDbibFvG4rKL6K48htepWmXIalSYgC', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiODZoVW5FNmtwVnFaaFZ2SEU3eFFNMGRUOUJ3bFp4QnIxa29IZ0I4TyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936262),
('8FbFmQVkf3SCJxNfNGi9ymLzczcYz49QSEHYNKjE', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT2kwMkRGeVV2ajR6TU9BdnVXalkxRUNIRFFvUTk3VWJhcHVXODRHNiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756935567),
('8RLVrK6cl06RHFh5ejo3SK6PBYPyoa3paiP96F8i', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM2F4d3FWTUFtUzAxME9CQ1NIaWtiU1VVaEJUT1hkSjlLamFjcjJaQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756937207),
('AhnntOYJ2Lm9kwUBf4ohBldi4Pcv0FvZiysyqo9J', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoianFyc0R2SWVnZUNTME1mckpwcHJja2FGcWxrQVFCNGxDVzdlTHFucyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936915),
('brO6M7OggxRU0jhhGLADGl0im4e3fSFlYcRCjUPN', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidkEwcDl2bDg3QjlvVGEzaW9OeVF0dkZ5SEt2dEZYeGc4VGRxMG50eiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936261),
('cAwoC7Oufaw0Bd4AH2TQD93MSPyj8rNtC5gaPMpa', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiazhOdlpqU0JKaHJodndXYWVoU01GY1JYeFk3cnhvNjRpV0FvTklrdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936263),
('cBMFZKQ09r4RE8QFfH60mIASKgUCuxxGrWXJCWUV', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRDIyYWowaFpRRVF6dUd4OUZDVHJkVFhPODVaQTNvNUw4ZkRrbnp6dyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAxL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936915),
('D9JFOHPg3KRWzDywuFWvMBJCGCvbw37XHlJjSeHu', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaXpLaXh2QWtMbjllWEJsS1VYNnphRWpzVGVOT1Mwa0N6SWxRSUN4VCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MDgzMTYxIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936083),
('dGQxSt43wbLAGwL7NNWVDZ3nBmqYxZyVAtW9gCJd', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSFJtTU01dk1kM2VpR21Mam16RW1lZzBONk1TWVlsdm93aHBpelNjeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTAwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvbG9naW4/aWQ9ODIyMWMyYjUtYmYwMy00MzZhLWE1ZWQtN2M5OWExYWViNTY3JnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM3MTEzMDMwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756937113),
('ejT2GyIiVXBzbp7EJ2EwPtThYIaaDEvNqX086C6f', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTDZZeGZFbmRPemNaWVhUb01SSkF2aDZPbk1vRkVyU1paZHJCSW44TyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936209),
('EmrduQraEM3tOdb3eipwUFr9IQeztvRfKZtd3b9N', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT2VVVnBFSEUxY3BvTmQxUWdPY0Y1YWkzSlQ3N0NXWXg0SkxHcU9INSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936261),
('Fa3zSayD0dpsFjA4qgx9Prxw1Z9cKaTnC5fN1qsI', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiU21odjhaNEVmN0RBNDhUdmc5MjlNYnBmdk9uOEZTTTVTWGJSSkhlayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756937123),
('fe0kDodewu1GhUNhFBfjGafSz1k8H8igqlOfAXex', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRUlubUhaWWVEQTV4MXlsSm9TOUhYRnZvNXRqOUs5aUVIV0V0ZU9VeiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAxL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756937207),
('FOhJhNPOpU4mJiyuY8el26gojCNDHbb64TNJd861', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoicHlyMHVnZTVad2hBMkxZQVJ5bVYyckRFU2Fyc0FUZjBzOUoxcU9TUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756937217),
('gLdtGx1w8p7bXhuvClwt2SvgrHTgQYjgyB6CeQf6', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZFMxR2xWSjJ2aVRTS0lpUjNxbFZEazFxT1FBdkpBMUlwcEFabnBhYSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936261),
('h29AeVxLq3YUyJm9nPsbGj7ZHAiazfFKiSVTblnF', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNEZUbzhwWUhSNFBKNG8ySDdoTzNUV3E2WXhFVUZZZWk2RWRPMTJBSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936230),
('Hv7OFRykLTo1GzPDPtEReVUL0HGIfR48M4UHD86F', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOEhMM3EybnVlSDhvbWVPSDlUb3ZSd3BqZE9TbXdjZDJsdEdNMVNXeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjYyNzU5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('ic2tBbiZdkCv1LlJ4CjOtvw3cAFNfXgUhCmXBNjL', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMDVmSFFvMVRNSVdaVXViMDdLUFdKRE9iWkFOWllTZnBibnUyaUhGZSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936254),
('INFihkoGg2LWI39NX2guIolQgMAIPWzNs2ieNDdE', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWnV1YjRKV250VDdJVFJMT3ZMOERXYmE0R0p0RlRndzR2c05aeG1peiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('iQ5aZJXgCIkrrqiCBD52KXUcuCBkYk2sfZaYejl6', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibURvSE1HbUV4c1dwaUNJM1RpdzFEZkpLTXVxSGh4QTlzVlF1dE5CeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjU0MjI3Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936254),
('ItuXCY2uiOyGXIKR6rNW5V1xKYmpkc0cJaXp59Ym', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiTlpMTGN1ZXBFYXhFQnRPWjVIQXEwb2RTN0FlTUFqcGI5SDNaSnFoUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756937136),
('JaKphe15kMyGQ4xnf7izJ6n74vyRcC3JAOGqxHM5', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZkx6bERhcDJNT1l1TjY1ZEhURXF6a3hLTGNuQWN1S2x6RXpXNm5PMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936128),
('Jg8kKaOopCAdRn4xIKEFh0MzdhYOKEDgV17Rdcob', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2dDU2VDbVNIS21mbzR0Q21DUUt1NEJkN2F1QUNFZGs0d0FBYlF5MyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjYyOTQ3Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('jJmkk4PRO6cFk41ztIsgtjmDclYx9Qf3xhkjQrBh', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidlU5cFloalk2T0RVeXRLVzVXcjBHemVRaHJ1RVVzUmJiRDk5Y2U4MyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936149),
('KPUtscJ4N1kZMK7I5re2fBLnyMDxP5SrtNJSTyLs', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQUNseDZOaU92VWdXVk83bk5XcGs0ZVVkQm5vQUVNOXY1SXRpWDYwZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756935567),
('lE4oglUBQtP22Fgs4USJVDcvG30HoJMSpCipeVT1', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieGNVV3FsR05iWUFnMzVRTnk1Sm9Td0Y1QTZkaG9kOFVNVWNXTUd2YyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjMwNzg0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936230),
('lkYciqIJFwqZmrqQkzpoIOZCk6EEHWxZmGRl7yuA', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidkN2cXBodVRlMGs3eVREUkI1Tm9YR0JIcFVWZUUxQjVWMTdram4yTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756937126),
('LrIMQwlUcVp1at7Wk199OKLNXeT0C8kK7aOEwabo', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYll4M0VnbndpVFUxeWk5RHpsbjg3YmswM3h4dlBzak8zakVqZUFsNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756935531),
('lZet3fMXfcTp0WuWX7fxS0NGjbxbB6y3ryp0wsEm', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZE9zemM5aTY3aEs3MUExQjJTR0NsSWJwc1F6NHVaM0phT3Z3ZnQ1TyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM1NTY3MDUwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756935567),
('n2rqeDPG8XvGzX4dO69CTAPZSCPdtQ1y0eBo7n2d', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHBwYmxJTTE4d0NiTG5hQ3hqNU5pT2cwOUhDTkV1U0tyd3FsTkEwaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936083),
('nJMEMw1965hOI1txSLsvFOy8o2XErvQrnKx8yRTZ', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYkdyQmxoYVI5NFFuYVY5dDZBV3NZdmgwSzJXd25DRGJJOHBZUUJ5ZSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936263),
('OP1U8Hj9IeMMq57ibmQ3fVptysChzpdWjtLq0MYn', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTkdVUWs2V0tPdjV3Uk5hVm1BQlhHUzJYWXhTaTAwSTZIeVFFcVNUNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjYyMDg4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('p4qpqK9ym5AT6ZkqBqJCSqb65YIS1dgxBjPzJ4Be', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYnFLU0g2N3pCMFZWUUNablptNjVnTDNnWldVc1BqWkptYzU1OXhIUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjYxOTExIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936261),
('phSRgeBkGItE5b6kKmaswX6NIpsBiMAT3vrMV8JF', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNkFMZUhCZHh0YkR1SmNtd2NQM2kwV2Nia3MyaWVLb3oxOGNqYjVnZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936262),
('Pk4uosLCD9cPmeYMhXRRvP92V04wGOq2UnaHuHxp', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaUh3T2V0WVZVeGpRck5tcEFnc01OcUJGSEV1QUpWcWVUdHNHOEc3RSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjYyMjQxIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('pl95rq3fZVSEl6LIJPvyU81fpj7OnR410FkZ9z1J', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaW84R1liQlpJQkNHVjhZbXFMUldsTFBGQlM3TnVPYkZWNVRWWHFZbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936262),
('Py9WohH4IhqpVGtdHSJEv9zwUmo8neculxTJdeZi', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTG1Xd3lqSzdzVExVZjFQcGNIbjZlUHhKOXd4anZMR1REeFBBVktJdiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936083),
('QyaSKJTgeUwUjS8lF0buwe8iIZbSW9k9ACOINZ7f', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidTdBZWJjb0lxMmJCUHNmYllCNjhjd0dhVGJKSUJSYnNvYzE2aXJPZyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAxL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756937126),
('QyKnxIbfqVqck41GPCSSZhst7m8rxlasI924fD2C', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoic1hac1FvWVlMVVV3UEF0M3lNVUJZQ0d4eDB2Yk95cTBCbEdxQTVaVSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936229),
('qzBu2oYuE1fgzhPZ6EPi50PWvy8c5ghhv8MmWbkX', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHQxMFYxU0dxNm9WT253NjF2a2syUG85dmJHQjY5czZaOUJDU2Q1byI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjA5NTQ2Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936209),
('R8qby045AQ0opdlvCLqWIS4zfp7zD42dHSrF8c4J', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVVRlUEp3UEN6TlBBWkpXVTJMVE1DODYwVWZVb3BHVWlMUzRpNjVOTyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756935531),
('rCrLg5oNQ1aAEHjwqHLCSC8z7vfIjyzh6ii0LlHk', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibkNsTFFNQWZrbDN0NXl5eEdkVEJIM0habE5ZNE82ckxuTWtLd0dKNyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('RHRRfYQREck685jWtl5oMb5WqTdo3NLD1w0EZPEK', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVHRNREZGMUQweFRWNWcxaVZtOGh1NnVlMERYUnI2Qk8wV0tEd1RUUCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('RXBjL5RkEyl2Af3fLKtxYc5L9TuxmXgGbWTovu4i', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ3dYaGtCSVNzSmg1SUdCNmpZWms5UGhPUEl2eEM2VkU2d1llT0w3YyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('RXvzGnfZPhJugSu1mOLqX1DwkfK3fg5a6wvxpiWg', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNHJuMTFtQUdPQVlnN3k5dkZJb0V6ek5GVE53dXpnbTVwR2wyMnE4NiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936262),
('s3IqByavA6XIa0YE31VHknVPXvHeBhFwfVwA546k', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWWZ0NUhGMEtVSUhxeTNEMm1pczh3cG4xWlg2UVZMd05wYzkzOXRJeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936254),
('SkyFwrdJm2n4pCNWBHOdUTPyNdw84Iu1NYzeDzYd', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicE1sNmY2RFVpZkNuZEdpMjdKVEsyVWNpOFNtZHo0SE5jVVcwMGsxWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936261),
('Sm4qzwgwhETKUD7Oj6rJxqFr4G0WcOD7V9eyWEjx', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibXAxeWVQazhQdWlWQmpqMWlObngxTlN2RVB1ZDJweGt4UEYyeEJRbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936149),
('sVMEAb5Gkdz4zDWYlKi1k14oHAwe3IZMGyJqHbYU', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiT3JJNU4zVmUyaGJ4RGpGWUtnRXBYbGZGQ1VXNEhhVUlNeXpxWE93SyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936280),
('vChYXHApGIyiIWw4YT2U6BLCLf34kWywIg3hJheZ', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicTQ0VEFmeHllaThZdzJNVHdDYVFCR2FGU3cxNWdoUm9FQXR2eVFiQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS8/aWQ9ODIyMWMyYjUtYmYwMy00MzZhLWE1ZWQtN2M5OWExYWViNTY3JnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM3MjA3MzE3Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756937207),
('vGFQH3TzMQhv58IwlPLcnZjrtdpWvKwGisVEham4', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibng3a1M3ZG56R1FxWlhZV0c1TGJHOUVYVGVMczVyT0hjU1Q1c1RhMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936230),
('VkVEXbfj6dLoppRZMpAETjg6YRxthZqgBAqSdRqC', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiak12R202aEhZT3NIbUhlazMzU2FCcllnQ1k0eGc0WFpWTU9CQnZIaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756936262),
('vtVIu4JwECGELPxA5rhXjTcisGAtYx0PoRhm34pM', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiajdtRkNSanNIYk5sMHdYMDloTW9sbTU2aUdtVUFtWnBLbUxOZk1QbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjYyMzk5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('Wf4NNlO5CUPVeclpTcusjrFH9ibk1li5PiAXRrgL', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibE9EWFlxZHJIQVZGMU55cDFya1FzN0tpQnF6QmN1Skh0cDhiMzVTNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MTI3OTc5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936128),
('WHv2X0FsOuCxyXfCNh53xoZJHQaeAzY6tIJQWdyz', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNnR6UEp1SzhsU0swWGFpOWdvYnIzazF6ajM4TGw3dEYyZklueThkcSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936128),
('XGwIzSly2E0a2umSfWrSOBizDbtYtmE5NRWd8Ggt', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiekNGYkNxYUtHeE82T3pDbHFWdWZSaVBQU2tLMm1Dd3hhU09TNHdaTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM1NTMwODgwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756935531),
('yFlKSvWBBwbdWZthw47kkDmVwEhZRRUQfoeVx2Wd', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidkxJN3NoNW94aWVHR29CUGVwSlpaZWl2b2JvY2t3cjlHRkFNOXpGWiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936230),
('Z2F2biGSPV9fFRPXilm4rkEqZwwWT9pLUnryumms', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaWgwZkY4SE9vRGZvYm1LRmw5cTkwQkhiVFlnbTR3Q0ZyS2FJOFZwSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC8/aWQ9NzQzYjNlNTItYWI1OC00YzA5LTg0OTUtN2ZmNDNkN2YzNTYxJnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2MjYyNTkxIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936262),
('ZopC6oCedrSWzFpSlaGSifGqm6HqBVCql32iIqLH', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZVRrYTRvMFZCQTAyd3hnank0OUd0b3BXMHV0NjhKM2w0YlV1dlowNCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936209),
('ZP1tuN8cRnZwrRYdEU0DCXLcNAIQxArLMfC4JFJY', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaXZCeUdnaVMya3BQNzNWZkZDWVdFZnZlQm1CdXNCNlVWUTg3dVhUcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS8/aWQ9ODIyMWMyYjUtYmYwMy00MzZhLWE1ZWQtN2M5OWExYWViNTY3JnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM3MTI2MDg0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756937126),
('ZzcUMY3adIUszSL0DBzj9M4vNW94pyG5ZgMfak5z', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.103.2 Chrome/138.0.7204.100 Electron/37.2.3 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZFZWVDAwcHVCODg0SmJzREI2ZkQyUEh5ZXpQWG8yc2pmWFdRNG05USI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS8/aWQ9ODIyMWMyYjUtYmYwMy00MzZhLWE1ZWQtN2M5OWExYWViNTY3JnZzY29kZUJyb3dzZXJSZXFJZD0xNzU2OTM2OTE1MDI0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756936915);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `school_id` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `office_id` bigint(20) UNSIGNED DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `department`, `email`, `school_id`, `email_verified_at`, `password`, `role`, `office_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Denver Gemino', 'denver123', NULL, 'denverg@ustp.edu.com', '', '2025-05-21 12:57:07', '$2y$12$NEex9X/rUPX7yEo9nqG.QuovkRmFtks7dwRREaCw7fCjJ6XXHmteq', 'admin', 1, 'w2AL1U2DRq', '2025-05-21 12:57:07', '2025-09-06 08:43:17'),
(2, 'Mark Rey Embudo', 'markrey123', 'BSIT', 'markreyembudo@ustp.edu.com', '', '2025-05-21 12:57:07', '$2y$12$SULUzzLNBfS7/MzsfaJjg.8V8ZzSrpRnzHCqI0Wy2Vlp0bHvhx7yO', 'faculty', 2, 'RJWfltigKG', '2025-05-21 12:57:07', '2025-09-06 08:43:17'),
(3, 'Judd O\'Kon', 'fvon', 'BSMB', 'abuabujanny99@gmail.com', '', '2025-05-21 12:57:07', '$2y$12$BKH3VwMTqrRBt3oQBT1yUOJUI70KyiTzxkK7GXGJgPRs2IyPj4Wf6', 'faculty', 3, '1F3Y60RD97', '2025-05-21 12:57:08', '2025-09-06 08:43:17'),
(4, 'Dr. Minnie McClure PhD', 'fadel.kailyn', 'BTLE_IA', 'hertha.haley@example.net', '', '2025-05-21 12:57:08', '$2y$12$BKH3VwMTqrRBt3oQBT1yUOJUI70KyiTzxkK7GXGJgPRs2IyPj4Wf6', 'faculty', 5, 'F4tJtE9It0', '2025-05-21 12:57:08', '2025-09-06 08:43:17'),
(5, 'Mr. Keeley Adams', 'mertz.trent', 'BTLE-HE', 'kaleigh98@example.com', '', '2025-05-21 12:57:08', '$2y$12$BKH3VwMTqrRBt3oQBT1yUOJUI70KyiTzxkK7GXGJgPRs2IyPj4Wf6', 'faculty', 4, 'MbMN7NiZby', '2025-05-21 12:57:08', '2025-09-06 08:43:17'),
(6, 'Admin User', 'admin', 'Supply Office', 'admin@ustp.edu.ph', 'ADMIN001', NULL, '$2y$12$eE5nFnKYuH5m7VBGkO6c/OKG9Ao/ypx3EYmxvQEMr7DNBOfWQ7U4i', 'admin', 1, NULL, '2025-09-03 13:34:49', '2025-09-06 08:43:17'),
(7, 'Faculty User', 'faculty1', 'Computer Science', 'faculty@ustp.edu.ph', 'FAC001', NULL, '$2y$12$T/7CFJdtn1c48atCeFz7sOLxvwAZX4FXd0tQFhgXRS5X6JL0e3m8C', 'faculty', NULL, NULL, '2025-09-03 13:34:49', '2025-09-03 13:34:49'),
(8, 'Supply Office Head', 'supplyhead', 'Supply Office', 'supplyhead@ustp.edu.ph', 'SOH001', NULL, '$2y$12$W1DnErxx5gy5GzrSU3rRG.5vH5sY8PfPQVcbvdgHhl./QkgmwKp6e', 'office_head', 1, NULL, '2025-09-06 08:43:17', '2025-09-06 08:43:17'),
(9, 'BSIT Department Head', 'bsithead', 'Computer Science', 'bsithead@ustp.edu.ph', 'BSITH001', NULL, '$2y$12$7.GXav66pGDJkiMm6/M1auOEM/J6aP8411GZ05Y2QHh9ClGTDVi8G', 'office_head', 2, NULL, '2025-09-06 08:43:17', '2025-09-06 08:43:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_logs_log_name_index` (`log_name`),
  ADD KEY `activity_logs_subject_type_index` (`subject_type`),
  ADD KEY `activity_logs_subject_id_index` (`subject_id`),
  ADD KEY `activity_logs_causer_type_index` (`causer_type`),
  ADD KEY `activity_logs_causer_id_index` (`causer_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `items_qr_code_unique` (`qr_code`),
  ADD KEY `items_category_id_foreign` (`category_id`),
  ADD KEY `items_current_holder_id_index` (`current_holder_id`);

--
-- Indexes for table `item_scan_logs`
--
ALTER TABLE `item_scan_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_scan_logs_item_id_scanned_at_index` (`item_id`,`scanned_at`),
  ADD KEY `item_scan_logs_scanned_at_index` (`scanned_at`),
  ADD KEY `item_scan_logs_user_id_scanned_at_index` (`user_id`,`scanned_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `offices_code_unique` (`code`),
  ADD KEY `offices_office_head_id_foreign` (`office_head_id`),
  ADD KEY `offices_name_index` (`name`),
  ADD KEY `offices_code_index` (`code`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `requests_claim_slip_number_unique` (`claim_slip_number`),
  ADD KEY `requests_user_id_foreign` (`user_id`),
  ADD KEY `requests_item_id_foreign` (`item_id`),
  ADD KEY `requests_approved_by_office_head_id_foreign` (`approved_by_office_head_id`),
  ADD KEY `requests_approved_by_admin_id_foreign` (`approved_by_admin_id`),
  ADD KEY `requests_fulfilled_by_id_foreign` (`fulfilled_by_id`),
  ADD KEY `requests_claimed_by_id_foreign` (`claimed_by_id`),
  ADD KEY `requests_workflow_status_index` (`workflow_status`),
  ADD KEY `requests_priority_index` (`priority`),
  ADD KEY `requests_department_index` (`department`),
  ADD KEY `requests_processed_by_foreign` (`processed_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_office_id_index` (`office_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `item_scan_logs`
--
ALTER TABLE `item_scan_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `items_current_holder_id_foreign` FOREIGN KEY (`current_holder_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `item_scan_logs`
--
ALTER TABLE `item_scan_logs`
  ADD CONSTRAINT `item_scan_logs_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_scan_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offices`
--
ALTER TABLE `offices`
  ADD CONSTRAINT `offices_office_head_id_foreign` FOREIGN KEY (`office_head_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_approved_by_admin_id_foreign` FOREIGN KEY (`approved_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_approved_by_office_head_id_foreign` FOREIGN KEY (`approved_by_office_head_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_claimed_by_id_foreign` FOREIGN KEY (`claimed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_fulfilled_by_id_foreign` FOREIGN KEY (`fulfilled_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
