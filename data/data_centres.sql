-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.7.16 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
-- Dumping data for table thaliak.data_centres: ~0 rows (approximately)
/*!40000 ALTER TABLE `data_centres` DISABLE KEYS */;
INSERT INTO `data_centres` (`id`, `name`) VALUES
	(1, 'Elemental'),
	(2, 'Gaia'),
	(3, 'Mana'),
	(4, 'Aether'),
	(5, 'Primal'),
	(6, 'Chaos');
/*!40000 ALTER TABLE `data_centres` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
