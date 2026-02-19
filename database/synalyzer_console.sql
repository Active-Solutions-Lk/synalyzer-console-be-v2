-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2026 at 04:01 AM
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
-- Database: `synalyzer_console`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@synalyzer.com', '$2b$10$gJjXVVY95zbwDlXi7qvsieqm7fzAobiaPuKlBPf7S0bf5ZU7B39SO', 'superadmin', '2026-02-14 21:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `analyzers`
--

CREATE TABLE `analyzers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analyzers`
--

INSERT INTO `analyzers` (`id`, `name`, `ip`, `domain`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Test-Analyzer-01', '::1', 'http://localhost', 1, '2026-02-14 21:17:35', '2026-02-14 21:34:54');

-- --------------------------------------------------------

--
-- Table structure for table `analyzer_health`
--

CREATE TABLE `analyzer_health` (
  `id` int(11) NOT NULL,
  `analyzer_id` int(11) NOT NULL,
  `cpu_load` float NOT NULL COMMENT 'Should be a percentage value',
  `ram_load` float NOT NULL COMMENT 'Should be a percentage value',
  `disk_capacity` float NOT NULL COMMENT 'Should be a percentage value',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analyzer_health`
--

INSERT INTO `analyzer_health` (`id`, `analyzer_id`, `cpu_load`, `ram_load`, `disk_capacity`, `created_at`, `updated_at`) VALUES
(25, 1, 28.8899, 50.9204, 60, '2026-02-15 14:16:18', '2026-02-15 14:16:18'),
(26, 1, 15.2754, 53.6444, 60, '2026-02-15 15:16:18', '2026-02-15 15:16:18'),
(27, 1, 24.3653, 54.7866, 60, '2026-02-15 16:16:18', '2026-02-15 16:16:18'),
(28, 1, 22.7435, 52.8971, 60, '2026-02-15 17:16:18', '2026-02-15 17:16:18'),
(29, 1, 17.8918, 52.7705, 60, '2026-02-15 18:16:18', '2026-02-15 18:16:18'),
(30, 1, 18.4931, 53.1097, 60, '2026-02-15 19:16:18', '2026-02-15 19:16:18'),
(31, 1, 22.8431, 54.5509, 60, '2026-02-15 20:16:18', '2026-02-15 20:16:18'),
(32, 1, 26.2819, 50.7168, 60, '2026-02-15 21:16:18', '2026-02-15 21:16:18'),
(33, 1, 24.9165, 54.0605, 60, '2026-02-15 22:16:18', '2026-02-15 22:16:18'),
(34, 1, 26.5934, 51.8231, 60, '2026-02-15 23:16:18', '2026-02-15 23:16:18'),
(35, 1, 25.9241, 52.2952, 60, '2026-02-16 00:16:18', '2026-02-16 00:16:18'),
(36, 1, 23.0946, 53.3872, 60, '2026-02-16 01:16:18', '2026-02-16 01:16:18'),
(37, 1, 26.719, 53.013, 60, '2026-02-16 02:16:18', '2026-02-16 02:16:18'),
(38, 1, 22.5266, 52.0796, 60, '2026-02-16 03:16:18', '2026-02-16 03:16:18'),
(39, 1, 21.7609, 54.9805, 60, '2026-02-16 04:16:18', '2026-02-16 04:16:18'),
(40, 1, 19.7038, 53.6364, 60, '2026-02-16 05:16:18', '2026-02-16 05:16:18'),
(41, 1, 26.0725, 53.3153, 60, '2026-02-16 06:16:18', '2026-02-16 06:16:18'),
(42, 1, 25.4871, 50.091, 60, '2026-02-16 07:16:18', '2026-02-16 07:16:18'),
(43, 1, 25.346, 52.1088, 60, '2026-02-16 08:16:18', '2026-02-16 08:16:18'),
(44, 1, 21.2608, 54.2732, 60, '2026-02-16 09:16:18', '2026-02-16 09:16:18'),
(45, 1, 21.0804, 54.335, 60, '2026-02-16 10:16:18', '2026-02-16 10:16:18'),
(46, 1, 16.6829, 50.3071, 60, '2026-02-16 11:16:18', '2026-02-16 11:16:18'),
(47, 1, 28.4696, 50.7207, 60, '2026-02-16 12:16:18', '2026-02-16 12:16:18'),
(48, 1, 26.1227, 52.4852, 60, '2026-02-16 13:16:18', '2026-02-16 13:16:18');

-- --------------------------------------------------------

--
-- Table structure for table `collectors`
--

CREATE TABLE `collectors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collectors`
--

INSERT INTO `collectors` (`id`, `name`, `ip`, `domain`, `secret_key`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Collector-Mumbai-01', '192.168.1.50', 'http://localhost:8080', 'collector_secret_key_mumbai_001', 1, '2026-02-14 21:13:56', '2026-02-14 21:13:56'),
(2, 'Collector-Singapore-01', '192.168.1.51', 'http://localhost:8081', 'collector_secret_key_singapore_001', 1, '2026-02-14 21:13:56', '2026-02-14 21:13:56');

-- --------------------------------------------------------

--
-- Table structure for table `collector_health`
--

CREATE TABLE `collector_health` (
  `id` int(11) NOT NULL,
  `collector_id` int(11) NOT NULL,
  `cpu_load` float NOT NULL,
  `ram_load` float NOT NULL,
  `disk_capacity` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collector_health`
--

INSERT INTO `collector_health` (`id`, `collector_id`, `cpu_load`, `ram_load`, `disk_capacity`, `created_at`, `updated_at`) VALUES
(25, 1, 35.7794, 46.0558, 47.3, '2026-02-15 14:16:18', '2026-02-15 14:16:18'),
(26, 1, 38.2369, 43.3767, 47.2, '2026-02-15 15:16:18', '2026-02-15 15:16:18'),
(27, 1, 41.3442, 41.8141, 47.1, '2026-02-15 16:16:18', '2026-02-15 16:16:18'),
(28, 1, 36.984, 45.9522, 47, '2026-02-15 17:16:18', '2026-02-15 17:16:18'),
(29, 1, 31.0558, 47.5782, 46.9, '2026-02-15 18:16:18', '2026-02-15 18:16:18'),
(30, 1, 47.2638, 42.9774, 46.8, '2026-02-15 19:16:18', '2026-02-15 19:16:18'),
(31, 1, 47.4947, 43.5297, 46.7, '2026-02-15 20:16:18', '2026-02-15 20:16:18'),
(32, 1, 31.247, 42.8134, 46.6, '2026-02-15 21:16:18', '2026-02-15 21:16:18'),
(33, 1, 68.7048, 44.457, 46.5, '2026-02-15 22:16:18', '2026-02-15 22:16:18'),
(34, 1, 67.9366, 43.633, 46.4, '2026-02-15 23:16:18', '2026-02-15 23:16:18'),
(35, 1, 66.1534, 45.0209, 46.3, '2026-02-16 00:16:18', '2026-02-16 00:16:18'),
(36, 1, 56.2911, 48.9282, 46.2, '2026-02-16 01:16:18', '2026-02-16 01:16:18'),
(37, 1, 67.2934, 41.4608, 46.1, '2026-02-16 02:16:18', '2026-02-16 02:16:18'),
(38, 1, 67.7741, 42.4851, 46, '2026-02-16 03:16:18', '2026-02-16 03:16:18'),
(39, 1, 53.1907, 48.6241, 45.9, '2026-02-16 04:16:18', '2026-02-16 04:16:18'),
(40, 1, 52.2952, 49.4755, 45.8, '2026-02-16 05:16:18', '2026-02-16 05:16:18'),
(41, 1, 57.9395, 41.8338, 45.7, '2026-02-16 06:16:18', '2026-02-16 06:16:18'),
(42, 1, 46.9171, 46.1954, 45.6, '2026-02-16 07:16:18', '2026-02-16 07:16:18'),
(43, 1, 36.7289, 47.6855, 45.5, '2026-02-16 08:16:18', '2026-02-16 08:16:18'),
(44, 1, 48.1574, 43.8346, 45.4, '2026-02-16 09:16:18', '2026-02-16 09:16:18'),
(45, 1, 34.5753, 46.1637, 45.3, '2026-02-16 10:16:18', '2026-02-16 10:16:18'),
(46, 1, 39.4669, 43.5339, 45.2, '2026-02-16 11:16:18', '2026-02-16 11:16:18'),
(47, 1, 30.8317, 43.7518, 45.1, '2026-02-16 12:16:18', '2026-02-16 12:16:18'),
(48, 1, 45.7849, 44.017, 45, '2026-02-16 13:16:18', '2026-02-16 13:16:18');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `device_key` varchar(200) NOT NULL,
  `log_duration` int(11) NOT NULL DEFAULT 30 COMMENT 'days',
  `package_start_at` date NOT NULL,
  `package_end_at` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `project_id`, `device_key`, `log_duration`, `package_start_at`, `package_end_at`, `created_at`, `updated_at`) VALUES
(7, 6, 'CF3806CCC9B442DE36F24B8BAAC3153D', 30, '2026-02-15', '2026-03-15', '2026-02-15 02:44:37', '2026-02-15 02:44:37'),
(8, 9, '496B097CB465247E8786B92F67D698C3', 30, '2026-02-15', '2026-03-15', '2026-02-15 06:38:02', '2026-02-15 06:38:02'),
(10, 9, 'B952D8A03B9B31DA994A0B5D0BAB971B', 30, '2026-02-15', '2026-03-15', '2026-02-15 06:50:34', '2026-02-15 06:50:34'),
(14, 8, 'FDD7E9F12736BF3400006BAA877516DE', 30, '2026-02-15', '2026-03-15', '2026-02-15 07:25:39', '2026-02-15 07:25:39'),
(15, 8, 'BD9B7B81C5F1F97A11B8357C3E931C76', 30, '2026-02-15', '2026-03-15', '2026-02-15 07:25:39', '2026-02-15 07:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `end_customer`
--

CREATE TABLE `end_customer` (
  `id` int(11) NOT NULL,
  `company` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_person` varchar(100) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `end_customer`
--

INSERT INTO `end_customer` (`id`, `company`, `address`, `contact_person`, `tel`, `email`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Test Company Alpha', '123 Test Street, Mumbai, India', 'John Doe', '9876543210', 'john@testcompany.com', 1, '2026-02-14 21:13:56', '2026-02-14 21:13:56'),
(2, 'Test Company Beta', '456 Demo Avenue, Singapore', 'Jane Smith', '8765432109', 'jane@betacompany.com', 1, '2026-02-14 21:13:56', '2026-02-14 21:13:56'),
(3, 'Test Company Gamma', '789 Sample Road, Bangalore, India', 'Bob Wilson', '7654321098', 'bob@gammacompany.com', 1, '2026-02-14 21:13:56', '2026-02-14 21:13:56');

-- --------------------------------------------------------

--
-- Table structure for table `ports`
--

CREATE TABLE `ports` (
  `id` int(11) NOT NULL,
  `port` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ports`
--

INSERT INTO `ports` (`id`, `port`, `description`, `created_at`) VALUES
(1, 514, 'Default Syslog Port', '2026-02-14 21:13:55'),
(2, 515, 'Alternate Syslog Port', '2026-02-14 21:13:55'),
(3, 1514, 'Secure Syslog Port', '2026-02-14 21:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `activation_key` varchar(100) NOT NULL,
  `project_type_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `reseller_id` int(11) DEFAULT NULL,
  `end_customer_id` int(11) DEFAULT NULL,
  `collector_id` int(11) NOT NULL,
  `analyzer_id` int(11) DEFAULT NULL,
  `device_count` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `activation_key`, `project_type_id`, `port_id`, `admin_id`, `reseller_id`, `end_customer_id`, `collector_id`, `analyzer_id`, `device_count`, `created_at`, `updated_at`) VALUES
(4, 'DEMO-PROJ-001', 1, 1, 1, 1, NULL, 1, 1, 5, '2026-02-14 17:50:23', '2026-02-14 17:50:23'),
(5, 'DEMO-PROJ-001-5', 1, 1, 1, 1, NULL, 1, 1, 5, '2026-02-14 17:53:02', '2026-02-15 09:10:48'),
(6, 'DEMO-PROJ-001-6', 1, 1, 1, 1, NULL, 1, 1, 5, '2026-02-14 18:06:36', '2026-02-15 09:10:48'),
(7, 'DEMO-PROJ-001-7', 1, 1, 1, 1, NULL, 1, 1, 5, '2026-02-14 18:13:39', '2026-02-15 09:10:48'),
(8, 'DEMO-PROJ-001-8', 1, 1, 1, 1, NULL, 1, 1, 5, '2026-02-14 18:14:58', '2026-02-15 09:10:48'),
(9, 'X6WQ-CXAE-KE62', 1, 1, 1, 1, 1, 1, 1, 2, '2026-02-15 06:38:01', '2026-02-15 06:38:01');

-- --------------------------------------------------------

--
-- Table structure for table `project_types`
--

CREATE TABLE `project_types` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_types`
--

INSERT INTO `project_types` (`id`, `type`, `description`, `created_at`) VALUES
(1, 'on-premises', 'Standard monitoring package', '2026-02-14 21:13:55'),
(2, 'cloud', 'Premium monitoring with advanced features', '2026-02-14 21:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `reseller`
--

CREATE TABLE `reseller` (
  `id` int(11) NOT NULL,
  `company` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_person` varchar(100) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reseller`
--

INSERT INTO `reseller` (`id`, `company`, `address`, `contact_person`, `tel`, `email`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tech Solutions India', 'Mumbai Tech Park, Mumbai', 'Rajesh Kumar', '9988776655', 'rajesh@techsolutions.in', 1, '2026-02-14 21:13:56', '2026-02-14 21:13:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `analyzers`
--
ALTER TABLE `analyzers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `analyzer_health`
--
ALTER TABLE `analyzer_health`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_analyzer_id` (`analyzer_id`);

--
-- Indexes for table `collectors`
--
ALTER TABLE `collectors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `collector_health`
--
ALTER TABLE `collector_health`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_collector` (`collector_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `end_customer`
--
ALTER TABLE `end_customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ports`
--
ALTER TABLE `ports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projects_activation_key_key` (`activation_key`),
  ADD KEY `project_type_id` (`project_type_id`),
  ADD KEY `port_id` (`port_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `reseller_id` (`reseller_id`),
  ADD KEY `end_customer_id` (`end_customer_id`),
  ADD KEY `collector_id` (`collector_id`),
  ADD KEY `analyzer_id` (`analyzer_id`);

--
-- Indexes for table `project_types`
--
ALTER TABLE `project_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reseller`
--
ALTER TABLE `reseller`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `analyzers`
--
ALTER TABLE `analyzers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `analyzer_health`
--
ALTER TABLE `analyzer_health`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `collectors`
--
ALTER TABLE `collectors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `collector_health`
--
ALTER TABLE `collector_health`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `end_customer`
--
ALTER TABLE `end_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ports`
--
ALTER TABLE `ports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `project_types`
--
ALTER TABLE `project_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reseller`
--
ALTER TABLE `reseller`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analyzer_health`
--
ALTER TABLE `analyzer_health`
  ADD CONSTRAINT `fk_analyzer_id` FOREIGN KEY (`analyzer_id`) REFERENCES `analyzers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `collector_health`
--
ALTER TABLE `collector_health`
  ADD CONSTRAINT `fk_collector` FOREIGN KEY (`collector_id`) REFERENCES `collectors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_admin_id_admins_id` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `fk_end_customer_id` FOREIGN KEY (`end_customer_id`) REFERENCES `end_customer` (`id`),
  ADD CONSTRAINT `fk_port_id_with_ports_id` FOREIGN KEY (`port_id`) REFERENCES `ports` (`id`),
  ADD CONSTRAINT `fk_reseller_id_with_reseller_id` FOREIGN KEY (`reseller_id`) REFERENCES `reseller` (`id`),
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`project_type_id`) REFERENCES `project_types` (`id`),
  ADD CONSTRAINT `projects_ibfk_6` FOREIGN KEY (`collector_id`) REFERENCES `collectors` (`id`),
  ADD CONSTRAINT `projects_ibfk_7` FOREIGN KEY (`analyzer_id`) REFERENCES `analyzers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
