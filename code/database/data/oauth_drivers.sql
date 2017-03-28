-- --------------------------------------------------------
-- Host:                         10.0.75.1
-- Server version:               5.7.17 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping data for table thaliak.oauth_drivers: ~0 rows (approximately)
/*!40000 ALTER TABLE `oauth_drivers` DISABLE KEYS */;
INSERT INTO `oauth_drivers` (`id`, `name`, `active`, `created_at`, `updated_at`) VALUES
	(1, 'google', 1, '2017-03-28 23:27:29', '2017-03-28 23:27:29'),
	(2, 'facebook', 1, '2017-03-28 23:27:34', '2017-03-28 23:27:34');
/*!40000 ALTER TABLE `oauth_drivers` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
