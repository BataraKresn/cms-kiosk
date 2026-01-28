/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.15-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: platform
-- ------------------------------------------------------
-- Server version	10.11.15-MariaDB-ubu2204-log

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
-- Table structure for table `custom_layout`
--

DROP TABLE IF EXISTS `custom_layout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_layout` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `data_layout` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `data_html` text DEFAULT NULL,
  `data_css` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_layout`
--

LOCK TABLES `custom_layout` WRITE;
/*!40000 ALTER TABLE `custom_layout` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_layout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(100) DEFAULT NULL,
  `ip_device` varchar(100) DEFAULT NULL,
  `port_device` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status_device` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
INSERT INTO `devices` VALUES
(37,'RRCW503BTTX','100.123.141.43','5551','2024-11-22 12:06:29',NULL,NULL,'HP - 001'),
(38,'2PXWDR0H3C','100.106.14.91','5552','2024-11-22 12:55:18',NULL,NULL,'KIOSK-01');
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `displays`
--

DROP TABLE IF EXISTS `displays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `displays` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `screen_id` bigint(20) unsigned DEFAULT NULL,
  `display_type` varchar(191) NOT NULL DEFAULT 'other',
  `operating_system` varchar(191) NOT NULL DEFAULT 'android',
  `schedule_id` bigint(20) unsigned DEFAULT NULL,
  `lat` decimal(11,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `location` text DEFAULT NULL,
  `location_description` text DEFAULT NULL,
  `group` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `displays`
--

LOCK TABLES `displays` WRITE;
/*!40000 ALTER TABLE `displays` DISABLE KEYS */;
INSERT INTO `displays` VALUES
(1,'9xB473QCP6kcMRvFWezPJoQR1LLLuf8U6WyhW6WhfsTo61r7ZBxaRxuyeOozwldzpF1Gwp3CeuJxXWUn20231106025459','DEPAN BANK MANDIRI',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-06 19:54:59','2025-09-23 20:17:46',NULL),
(2,'bCTBcbDIa2NHDnoKIgQSN988Huzbp3F9Od9zDuppVsgWXVjJxjGQjMGqgPyRqQWHYfECgJLL4o6Npy7k20231113091316','DEPAN KOMISI 11',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:13:16','2024-12-30 21:24:28',NULL),
(3,'brq96lp5Kk6CXjmeSZWwVu6Ojo9Mdg5pEVsnmeDFOQvLuxjq9fBk0BXoC7dinYtBekSmA73DQeeB4xx020231113091413','DEPAN KOMISI 10',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:14:13','2025-09-23 20:17:43',NULL),
(4,'fmsg9w5jKIh6kvVH75TarJp9lcLMSOXQ4xXbHygD9nbmhC0vHRXHZ3vHJs9Qoxin0HcWWcWn82sb9lo020231113091444','DEPAN BANK BCA',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:14:44','2025-04-20 21:20:15',NULL),
(5,'196IMoMt8hEqs6Qyz7DW4oOHsR6yKle88qhu0hA10OTyAUsWVeUkp3RDffboofloIbbgzZWgl4MiqBnn20231113091510','DEPAN ESKALATOR',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:15:10','2025-09-23 20:17:39',NULL),
(6,'XM06S6RTe5LGppg4HLwwaZakFHY6TwAhFJ1a2PhCzETFhyce9V7q3OlvozYMAiETmxbyZazg0CzJJSco20231113091635','DEPAN BIRO',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:16:35','2025-04-20 21:20:09',NULL),
(7,'imVNwl1DntYNccR94GLfqmogjgx4DYYJFtmTX8t1MZIUIsTEuYWj4niuIKmqtKsjakydjg6D3SMPNgFz20231113091700','DEPAN PARIPURNA',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:17:00','2025-08-20 18:57:28',NULL),
(8,'wvB5COYwtXkvBtRGqYE9c1cK0WyYQsqvvRG3FixtsZRKJulfRhAYXTSRQPmRG63lIhkOkXxKPQhC6U7820231113091725','DEPAN YANKES',NULL,1,'digital_signage','android',14,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:17:25','2025-04-20 21:19:32',NULL),
(9,'erl40A5hpImVKD319j8EXA0etjQPAZQ4VYH02whlThYishX8m4hArwYB5p1vNs1l6SKtinBV3dWeXkL920231113091750','DEPAN MUSIUM',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:17:50','2025-08-20 18:57:17',NULL),
(10,'KPqwfMlf6uNiusiLxpq0g3NttfMR4bdu1ZbCh4oSToBCpdO2SYTTaV2eKHk15sjJ9bCbbptuI2YoWX2l20231113091812','DEPAN ABDUL MUIS',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:18:12','2025-04-20 21:19:24',NULL),
(11,'M2vd1W42BM7zGODUqNtkPj7wprBfYPwEPtqBCVB8sVEHpQO2junDsZTf4cVItXHzpwuO8HMOh4BVnOlh20231113091843','NUS 3 LT 1',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:18:43','2025-09-23 20:17:35',NULL),
(12,'KGRzj1JFs1T53rYp0d8dFcMjxNfDKPAK4KMwgyKwAEDSDCR0dsJ7rhObB2RQGAE6aIaDHeoLxv9p8dr920231113091904','NUS 3 LT 2',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:19:04','2025-01-31 11:46:24',NULL),
(13,'dHFi1ZxacQ6OG6yiMxBhs0u3TVa7hgRC8CJzqkQ6JLgPCF4bF5IpTve9OYAHZ3Y6LB1aM6Ix9Am9udpW20231113091928','NUS 3 LT 4',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:19:28','2025-04-20 21:19:10',NULL),
(14,'GLLSaI8INGw9JZu41SpSl0MClrwmaKJKJOC84wFC14O4w8EZr9NATQUNPjSMHYtr2ltAPH3NHS6o5xj220231113091952','SETJEN LT 3',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:19:52','2025-04-20 21:19:04',NULL),
(15,'wIBn340srdyiC17CmimHKC9iTCdKxsoLTkQDXnJlIZeoKSHav2AVGqijq3fdcnx26WitCPJrsjEIvD3v20231113092015','SETJEN LT 1',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:20:15','2025-04-20 21:18:29',NULL),
(16,'MQ1sVebufboY7muTObEstVoZUoSl4xPYKUtuso5ke8JV7TFpzOVuCjS6r3lt0qK4P7Wj3sGCXPwrGZOB20231113092034','KOPO DEPAN SIDANG 2',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:20:34','2025-08-20 18:56:35',NULL),
(17,'QJRs8rqfOLA5Uamus7IVHSzZ4uV5GWXvBZ7QLEacY3IqoZDXEqOGKINVOheu1LPJhZpUwKlvXGoUpRC920231113092056','KOPO DEPAN SIDANG 1',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:20:56','2025-08-20 18:56:22',NULL),
(18,'VOnENtJPtiD9mCBn7Tb3HWG8fx9vS0gtdLgbwlNVDKAiemv19hgYLEAzg41RSkOmJGP600fOjB4ipCHH20231113092119','UNIV VETERAN 1',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:21:19','2025-09-23 20:17:32',NULL),
(19,'v38HvK1XwijtmdC0KRoMlDHMABlW8XWNQsxq2yjai3zYSeDEkTnUNbbJkw22YeLg51ClVDDfslIYkUA620231113092140','UNIV VETERAN 2',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:21:40','2025-09-23 20:17:28',NULL),
(20,'HAwo4g0Jusr8TJeolEwDoVOGpTxluGcXwBj2aHmfoz2uHnYqoHVLy6DfSvTzitu5qyRzZMCB574VleDq20231113092204','GAMBIR 1',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:22:04','2025-09-23 20:17:23',NULL),
(21,'hKPmR52ufxzG24GDm34nkhOWDjySue2n2y6s4KH5yMw5TtHOU3S34TWkv0oX1li74Lwao4ZLtfIeWhUv20231113092223','GAMBIR 2',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:22:23','2025-09-23 20:17:19',NULL),
(22,'f1gsQ6rGnpGqsvigkdMfqAZu20kIl8MJo7cSB7zUvNvezi4WW1P7AV2ys42gdfc5GqQP5fyYAouGo9Pv20231113092244','HLM 1',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:22:44','2024-10-02 11:44:51',NULL),
(23,'NPaEOi4OUdUkUMRaE4oHyweHbmZ65a32wnHuJk2Q2oWSkN5Ati6PRHyAcMdWyrsnyurT86qonSzxD1CP20231113092304','HLM 2',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:23:04','2024-10-02 11:44:59',NULL),
(24,'8i2uuJ4brdfp8Hz8wc2qHix11A5MQxfF62lmNn5kTPnfFIOf4Ws8VQSrdTOnpLv0VacOmgOg23MrMHYq20231113092321','HLM 3',NULL,1,'digital_signage','android',8,0.00000000,0.00000000,NULL,NULL,NULL,'2023-11-13 14:23:21','2024-10-02 11:45:10',NULL),
(25,'JVADdj5CqDKOn0q3Cyj0D85eLVLJPTiWrb9UHSmb0bShLyl9JMIkBiim2vb8rxkbehf8Fm6Tj2j2e13r20231120042542','MIKTA',NULL,1,'digital_signage','android',3,-6.21062206,106.80258222,NULL,NULL,NULL,'2023-11-20 09:25:42','2023-11-28 13:42:41','2023-11-28 13:42:41'),
(26,'TjDtYcBnu9sxeODM816VPbHlMP3A6yfcVXDAo4zgaWzK72ew4f3ojQT3oB0BvdtSMJCZDsU7lDLCUraV20231211125114','PERPUSNAS 2',NULL,1,'digital_signage','android',8,-6.21062206,106.80258222,NULL,NULL,NULL,'2023-12-11 05:51:14','2025-09-23 20:17:15',NULL),
(27,'oJdrUA5u0EzGXaNUtz3896EBdmQLqDRsr59SHzUPKZwlUhew4YI2GuBfWEEl7yq4vLYhtaY2FXc2R3jF20240709041849','PERPUSNAS 1',NULL,1,'digital_signage','android',8,-6.21062206,106.80258222,NULL,NULL,NULL,'2024-07-09 08:18:49','2025-09-23 20:17:11',NULL),
(28,'7gNWhIcjJqsY5wX4Fc0RX6m4viMOCUmgwQ46hGTfR3ToJXLmffFRlrH2wbGUpZjkKqHMLcS53HdxoC5O20240806091423','New Display 20240806091423',NULL,NULL,'other','android',NULL,NULL,NULL,NULL,NULL,NULL,'2024-08-06 13:14:23','2024-09-03 07:25:25','2024-09-03 07:25:25'),
(29,'k7gvRe5iZShYM7w0s5h9UxjNPbGgQkNzr47KSWJSHvnYhnI3ajEzvk06QUJFzzZMrNd0szngKV2zcaZB20240930035800','New Display 20240930035800',NULL,NULL,'other','android',NULL,NULL,NULL,NULL,NULL,NULL,'2024-09-30 19:58:00','2024-10-31 11:04:19','2024-10-31 11:04:19'),
(30,'0k7kObsWw8YXVxTl4ZhRN9sbPpCLHoWOBSk4kcLVavhkxFscdYuuBp20cuYNlqwdwPTCHQtgcd7tj1iv20241031070203','BANDARA SOETA TERMINAL 3',NULL,1,'digital_signage','android',8,-6.21062206,106.80258222,NULL,NULL,NULL,'2024-10-31 11:02:03','2025-02-03 11:14:17',NULL),
(31,'ncZsB5XzaxnmTZMefYrwzzROHuNzZEw4VYwiylNfijseABfRdl8xGM4Cd8ZyJCgosQwwUAMFM6I8ueUj20241031070434','BANDARA SOETA TERMINAL 2',NULL,1,'digital_signage','android',8,-6.21062206,106.80258222,NULL,NULL,NULL,'2024-10-31 11:04:34','2025-02-03 11:14:30',NULL),
(32,'BUFrLPkciz20241217042111','New Display 20241217042111 baru nih',NULL,1,'other','android',1,-6.21062206,106.80258222,NULL,NULL,NULL,'2024-12-17 09:21:11','2024-12-17 09:24:26','2024-12-17 09:24:26'),
(33,'vzNoFh6YdK20250203105101','New Display 20250203105101',NULL,NULL,'other','android',NULL,NULL,NULL,NULL,NULL,NULL,'2025-02-03 15:51:01','2025-02-03 15:51:23','2025-02-03 15:51:23'),
(34,'qxAcCvXi4m20250203030352','New Display 20250203030352',NULL,1,'other','android',22,-6.21062206,106.80258222,NULL,NULL,NULL,'2025-02-03 20:03:52','2025-04-16 02:11:17','2025-04-16 02:11:17'),
(35,'xzgPWiqJKK20250206034407','RUANG RAPAT PENERBITAN',NULL,1,'digital_signage','android',4,-6.21062206,106.80258222,NULL,NULL,NULL,'2025-02-06 08:44:07','2025-05-21 21:48:28',NULL),
(36,'89niAbpoNb20250225070913','Depan Perpustakaan',NULL,1,'digital_signage','android',8,-6.21062206,106.80258222,NULL,NULL,NULL,'2025-02-25 00:09:13','2025-08-20 18:54:01',NULL),
(37,'KdkL2NDabP20251007032035','New Display 20251007032035',NULL,NULL,'other','android',NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-06 20:20:35','2025-10-06 20:21:13','2025-10-06 20:21:13');
/*!40000 ALTER TABLE `displays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `layouts`
--

DROP TABLE IF EXISTS `layouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `layouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `screen_id` bigint(20) unsigned NOT NULL,
  `running_text_is_include` tinyint(1) NOT NULL DEFAULT 0,
  `running_text_position` varchar(191) NOT NULL DEFAULT 'bottom',
  `running_text_id` bigint(20) unsigned DEFAULT NULL,
  `is_template` tinyint(1) NOT NULL DEFAULT 0,
  `children` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_layouts_deleted_at` (`deleted_at`),
  KEY `idx_layouts_created_at` (`created_at`),
  KEY `idx_layouts_name` (`name`),
  KEY `idx_layouts_deleted_created` (`deleted_at`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `layouts`
--

LOCK TABLES `layouts` WRITE;
/*!40000 ALTER TABLE `layouts` DISABLE KEYS */;
INSERT INTO `layouts` VALUES
(1,'Template (7 Spot)',1,0,'bottom',NULL,1,NULL,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(2,'Template (1 Spot Full)',1,0,'bottom',NULL,1,NULL,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(3,'Template (1 Spot Full Landscape)',2,0,'bottom',NULL,1,NULL,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(4,'DPR 7 LAYOUT VIDEO OFFLINE VERSI 1',1,1,'bottom',1,0,NULL,'2023-11-06 19:41:40','2023-12-13 17:10:29',NULL),
(5,'DPR 7 LAYOUT 15 MENIT VERSI 3',1,1,'bottom',1,0,NULL,'2023-11-06 19:44:39','2024-05-16 10:28:06',NULL),
(6,'DPR 7 LAYOUT 15 MENIT VERSI 1',1,1,'bottom',1,0,NULL,'2023-11-06 19:44:43','2024-05-16 10:27:55',NULL),
(7,'DPR 7 LAYOUT 15 MENIT VERSI 2',1,1,'bottom',1,0,NULL,'2023-11-06 19:44:44','2024-05-16 10:27:45',NULL),
(8,'DPR 7 LAYOUT LIVE TVR HLS VERSI 1',1,1,'bottom',1,0,NULL,'2023-11-06 19:49:28','2023-12-05 12:35:35',NULL),
(9,'CLONE [Template (1 Spot Full)]',1,0,'bottom',NULL,0,NULL,'2023-11-08 11:01:55','2023-11-08 18:36:30','2023-11-08 18:36:30'),
(10,'DPR LAYOUT EMEDIA',1,1,'bottom',1,0,NULL,'2023-11-08 18:37:51','2024-02-01 07:33:25',NULL),
(11,'REVISI 1440 KIOSK',1,1,'bottom',1,0,NULL,'2023-11-17 20:42:36','2023-11-27 16:26:23','2023-11-27 16:26:23'),
(12,'MIKTA',1,0,'bottom',NULL,0,NULL,'2023-11-20 09:09:02','2023-11-28 14:19:32','2023-11-28 14:19:32'),
(13,'Template (8 Spot)',1,0,'bottom',NULL,1,NULL,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(14,'DPR 8 LAYOUT LIVE TVR VERSI 1',1,1,'bottom',1,0,NULL,'2023-11-21 14:38:05','2025-02-06 08:49:19','2025-02-06 08:49:19'),
(15,'Layout URL',1,0,'bottom',NULL,0,NULL,'2023-11-24 14:29:50','2023-12-05 12:44:14','2023-12-05 12:44:14'),
(16,'Template (7 Spot - Req - 001)',1,0,'bottom',1,1,NULL,'2023-11-30 18:03:49','2023-11-30 18:03:49',NULL),
(17,'Template (11 Spot - Req - 002)',1,0,'bottom',1,1,NULL,'2023-11-30 18:44:51','2023-11-30 18:44:51',NULL),
(18,'Template (6 Spot - Req - 003)',1,0,'bottom',1,1,NULL,'2023-11-30 18:53:34','2023-11-30 18:53:34',NULL),
(19,'DPR 11 LAYOUT VERSI 1',1,1,'bottom',1,0,NULL,'2023-11-30 21:22:54','2023-12-05 12:31:35',NULL),
(20,'DPR 7 LAYOUT 15 MENIT VERSI 4',1,1,'bottom',1,0,NULL,'2023-12-04 19:17:40','2024-05-16 10:28:24',NULL),
(21,'CLONE [Template (1 Spot Full)]',1,0,'bottom',NULL,0,NULL,'2023-12-06 06:03:53','2023-12-06 06:04:37','2023-12-06 06:04:37'),
(22,'CLONE [Template (1 Spot Full)]',1,0,'bottom',NULL,0,NULL,'2023-12-06 06:06:45','2023-12-06 06:07:20','2023-12-06 06:07:20'),
(23,'CLONE [Template (6 Spot - Req - 003)]',1,1,'bottom',1,0,NULL,'2023-12-06 06:10:25','2024-10-08 07:12:18','2024-10-08 07:12:18'),
(24,'DPR LAYOUT SLIDER POTRAIT',1,1,'bottom',1,0,NULL,'2024-02-01 07:30:15','2025-03-05 00:23:48',NULL),
(25,'DPR LAYOUT POTRAIT 1',1,1,'bottom',1,0,NULL,'2024-02-01 12:11:31','2024-03-14 06:15:29',NULL),
(26,'Rewaind',1,1,'bottom',1,0,NULL,'2024-02-06 13:59:03','2024-02-06 15:08:43','2024-02-06 15:08:43'),
(27,'dpr rewind 2023 FORUM DISKUSI',1,0,'bottom',NULL,0,NULL,'2024-02-06 14:09:41','2024-02-06 15:08:33','2024-02-06 15:08:33'),
(28,'DPR 7 LAYOUT YOUTUBE',1,0,'bottom',NULL,0,NULL,'2024-02-06 15:08:56','2024-02-06 15:09:30',NULL),
(29,'DPR LAYOUT FULL SCREEN 1',1,1,'bottom',1,0,NULL,'2024-02-26 08:53:23','2024-09-30 19:56:42',NULL),
(30,'DPR LAYOUT POTRAIT 2',1,1,'bottom',1,0,NULL,'2024-03-14 06:15:47','2024-03-14 06:16:25',NULL),
(31,'DPR 7 LAYOUT 15 MENIT VERSI 5',1,1,'bottom',1,0,NULL,'2024-05-16 10:29:05','2024-05-16 10:35:45',NULL),
(32,'CLONE [Template (7 Spot - Req - 001)]',1,0,'bottom',1,0,NULL,'2024-05-16 10:32:08','2024-05-16 10:33:08','2024-05-16 10:33:08'),
(33,'DPR 7 LAYOUT 15 MENIT VERSI 6',1,1,'bottom',1,0,NULL,'2024-05-16 10:33:16','2024-05-17 10:59:03',NULL),
(34,'DPR 7 LAYOUT 15 MENIT VERSI 7',1,1,'bottom',1,0,NULL,'2024-05-16 10:33:46','2024-05-17 11:00:58',NULL),
(35,'DPR 7 LAYOUT 15 MENIT VERSI 8',1,1,'bottom',1,0,NULL,'2024-05-17 10:55:21','2024-05-17 10:57:15',NULL),
(36,'DPR LAYOUT POTRAIT YANKES',1,1,'bottom',1,0,NULL,'2024-09-03 07:16:37','2025-03-05 00:24:19',NULL),
(37,'DPR LAYOUT 7 YANKES',1,1,'bottom',1,0,NULL,'2024-09-03 07:32:37','2025-03-05 00:23:32',NULL),
(38,'DPR LAYOUT PARJA',1,1,'bottom',1,0,NULL,'2024-09-11 08:32:25','2025-02-12 01:15:35','2025-02-12 01:15:35'),
(39,'DPR 7 LAYOUT 15 MENIT VERSI 9',1,1,'bottom',1,0,NULL,'2024-09-20 07:42:51','2024-09-20 07:45:12',NULL),
(40,'DPR 7 LAYOUT 15 MENIT VERSI 10',1,1,'bottom',1,0,NULL,'2024-09-20 07:45:22','2024-09-20 07:48:34',NULL),
(41,'DPR LAYOUT KONTEN REQUEST',1,1,'bottom',1,0,NULL,'2024-10-01 04:30:45','2024-10-15 06:57:39',NULL),
(42,'Template (7 Spot - Req - 004)',1,0,'bottom',1,1,NULL,'2024-10-08 09:15:34','2024-10-08 09:15:34',NULL),
(43,'DPR 7 LAYOUT 15 MENIT VERSI 11',1,1,'bottom',1,0,NULL,'2024-10-08 09:28:48','2024-10-08 10:55:18',NULL),
(44,'TEST LAYOUT',1,0,'bottom',NULL,1,NULL,'2024-11-05 08:58:32','2025-02-06 08:49:59','2025-02-06 08:49:59'),
(45,'DPR LAYOUT KONTEN ISU UTAMA',1,1,'bottom',1,0,NULL,'2024-11-21 09:12:25','2024-11-21 09:13:37',NULL),
(46,'tes',1,0,'bottom',NULL,1,NULL,'2024-12-11 17:29:19','2025-02-06 08:49:55','2025-02-06 08:49:55'),
(47,'Layouts test',1,0,'bottom',NULL,1,NULL,'2024-12-13 08:51:09','2025-02-06 08:49:51','2025-02-06 08:49:51'),
(48,'CLONE [Template (7 Spot - Req - 004)]',1,0,'bottom',1,0,NULL,'2024-12-13 12:55:53','2024-12-13 12:56:03','2024-12-13 12:56:03'),
(49,'test2',1,0,'bottom',NULL,1,NULL,'2024-12-14 23:43:49','2025-02-06 08:49:47','2025-02-06 08:49:47'),
(50,'LAYOUT BARU',1,0,'bottom',NULL,1,NULL,'2024-12-16 11:13:29','2024-12-16 11:13:29',NULL),
(51,'LAYOUT BARU UJI COBA',1,1,'bottom',1,0,NULL,'2024-12-16 11:18:23','2024-12-16 13:20:39',NULL),
(52,'LAYOUT EMEDIA',1,0,'bottom',NULL,1,NULL,'2024-12-16 11:33:28','2024-12-16 11:33:28',NULL),
(53,'LAYOUT EMEDIA UJI COBA',1,1,'bottom',1,0,NULL,'2024-12-16 11:33:37','2024-12-16 11:34:46',NULL),
(54,'demo layout',1,0,'bottom',NULL,1,NULL,'2024-12-16 12:46:25','2024-12-16 12:46:25',NULL),
(55,'test layout 1 new edit yaudah',1,0,'bottom',NULL,1,NULL,'2024-12-17 09:57:38','2024-12-17 11:52:38','2024-12-17 11:52:38'),
(56,'CLONE [demo layout]',1,0,'bottom',NULL,0,NULL,'2025-02-06 08:54:40','2025-02-17 17:53:59','2025-02-17 17:53:59'),
(57,'CLONE [demo layout]',1,0,'bottom',NULL,0,NULL,'2025-02-06 09:00:04','2025-02-06 09:00:04',NULL),
(58,'LAYOUT LIVE YT DPR RI',1,1,'bottom',1,0,NULL,'2025-02-12 01:16:21','2025-02-17 17:56:58',NULL),
(59,'LAYOUT MESJID 1',1,1,'bottom',1,0,NULL,'2025-03-02 20:57:24','2025-03-02 21:03:18',NULL),
(60,'LAYOUT MESJID 2',1,1,'bottom',1,0,NULL,'2025-03-02 20:58:21','2025-03-03 18:19:27',NULL),
(61,'test layout live url al quran',1,0,'bottom',NULL,1,NULL,'2025-03-02 22:51:53','2025-03-02 22:52:21','2025-03-02 22:52:21'),
(62,'test live url jadwal sholat',1,1,'bottom',1,0,NULL,'2025-03-02 22:52:41','2025-03-03 00:57:04',NULL),
(63,'LAYOUT ISU UTAMA TESTING',1,0,'bottom',NULL,1,NULL,'2025-03-05 00:42:30','2025-03-05 00:44:24','2025-03-05 00:44:24'),
(64,'LAYOUT ISU UTAMA TESTING',1,1,'bottom',1,0,NULL,'2025-03-05 00:44:14','2025-03-05 00:46:39',NULL),
(65,'LAYOUT KEGIATAN RAMADHAN',1,1,'bottom',1,0,NULL,'2025-03-05 17:49:26','2025-03-05 17:50:04',NULL),
(66,'LAYOUT LIVE MASJID',1,0,'bottom',NULL,1,NULL,'2025-03-10 22:42:31','2025-03-10 22:42:31',NULL),
(67,'LAYOUT LIVE MASJID',1,1,'bottom',1,0,NULL,'2025-03-10 22:49:12','2025-03-10 22:52:36',NULL),
(68,'TEST LAYOUT',1,1,'bottom',4,0,NULL,'2025-03-17 19:49:58','2025-03-17 22:20:40','2025-03-17 22:20:40'),
(69,'Test media video template 7 full',1,1,'bottom',4,0,NULL,'2025-03-23 20:20:48','2025-04-07 21:26:53','2025-04-07 21:26:53'),
(70,'Layout Buku',1,0,'bottom',NULL,1,NULL,'2025-06-24 19:44:24','2025-06-24 19:44:24',NULL),
(71,'Layout Rak Buku',1,1,'bottom',1,0,NULL,'2025-06-24 19:46:25','2025-06-24 19:48:18',NULL),
(72,'DPR LAYOUT 7 VERSI 2025',1,0,'bottom',NULL,1,NULL,'2025-08-25 20:26:17','2025-08-25 20:26:17',NULL),
(73,'DPR LAYOUT 7 VERSI 2025',1,1,'bottom',1,0,NULL,'2025-08-25 20:35:44','2025-08-25 20:57:07',NULL);
/*!40000 ALTER TABLE `layouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `mediable_type` varchar(191) NOT NULL,
  `mediable_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_mediable_type_mediable_id_index` (`mediable_type`,`mediable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES
(1,'No Media','No Media','App\\Models\\MediaImage',1,'2023-11-06 15:32:58','2025-02-06 09:23:29','2025-02-06 09:23:29'),
(4,'Logo Kiosk','Logo Kiosk','App\\Models\\MediaImage',2,'2023-11-06 19:27:20','2023-11-27 16:10:50','2023-11-27 16:10:50'),
(6,'Live Streaming TVR','Live Streaming TVR','App\\Models\\MediaHls',4,'2023-11-06 19:30:56','2025-02-06 08:52:57',NULL),
(9,'KONTEN VIDEO ','KONTEN VIDEO ','App\\Models\\MediaVideo',5,'2023-11-06 19:33:56','2023-11-19 21:07:10',NULL),
(10,'VIDEO 15 MENIT 1','VIDEO 15 MENIT 1','App\\Models\\MediaVideo',1,'2023-11-06 19:34:45','2024-11-14 07:18:08',NULL),
(11,'VIDEO 15 MENIT 2','VIDEO 15 MENIT 2','App\\Models\\MediaVideo',2,'2023-11-06 19:35:43','2024-11-14 07:18:51',NULL),
(12,'VIDEO 15 MENIT 3','VIDEO 15 MENIT 3','App\\Models\\MediaVideo',3,'2023-11-06 19:36:32','2024-11-14 07:19:10',NULL),
(13,'Emedia','Emedia','App\\Models\\MediaHls',3,'2023-11-08 18:37:38','2023-11-27 16:11:00','2023-11-27 16:11:00'),
(14,'DPR Live Youtube','DPR Live Youtube','App\\Models\\MediaLiveUrl',5,'2023-11-09 20:06:03','2023-12-05 12:41:19','2023-12-05 12:41:19'),
(22,'MIKTA MP4','MIKTA MP4','App\\Models\\MediaVideo',7,'2023-11-20 18:24:12','2023-11-24 14:11:51','2023-11-24 14:11:51'),
(23,'LIVE STREAMING TVR','LIVE STREAMING TVR','App\\Models\\MediaHls',5,'2023-11-21 11:24:30','2023-11-27 16:22:17','2023-11-27 16:22:17'),
(24,'Konten Url','Konten url','App\\Models\\MediaLiveUrl',5,'2023-11-24 14:28:49','2023-12-05 12:41:09','2023-12-05 12:41:09'),
(25,'LOGO KIOSK','LOGO KIOSK\n','App\\Models\\MediaImage',3,'2023-11-27 16:14:59','2023-11-27 16:14:59',NULL),
(34,'Live Emedia','Live Emedia','App\\Models\\MediaLiveUrl',5,'2023-11-27 18:24:06','2023-11-27 18:24:06',NULL),
(35,'url contoh','contoh url','App\\Models\\MediaLiveUrl',6,'2023-11-27 18:59:13','2023-11-27 19:41:16','2023-11-27 19:41:16'),
(36,'bandara url','bandara url','App\\Models\\MediaLiveUrl',7,'2023-11-27 19:00:42','2023-11-27 19:41:12','2023-11-27 19:41:12'),
(37,'traveloka','traveloka','App\\Models\\MediaLiveUrl',8,'2023-11-27 19:04:26','2023-11-27 19:41:09','2023-11-27 19:41:09'),
(38,'ANTARA','KAI','App\\Models\\MediaLiveUrl',10,'2023-11-27 19:06:16','2023-11-27 19:41:05','2023-11-27 19:41:05'),
(39,'DPR RI YOUTUBE','DPR RI UTUBE','App\\Models\\MediaLiveUrl',13,'2023-11-27 19:12:48','2023-11-27 19:41:02','2023-11-27 19:41:02'),
(40,'VIDEO 15 MENIT 4','VIDEO 15 MENIT 4','App\\Models\\MediaVideo',4,'2023-11-29 07:16:28','2024-11-14 07:19:36',NULL),
(42,'HTML E MEDIA','HTML E MEDIA\n','App\\Models\\MediaHtml',41,'2023-11-30 21:16:18','2023-11-30 21:16:18',NULL),
(43,'HTML JADWAL RAPAT','JADWAL RAPAT','App\\Models\\MediaHtml',42,'2023-11-30 21:17:32','2023-11-30 21:17:32',NULL),
(44,'HTML IG MEDSOS','IG MEDSOS','App\\Models\\MediaHtml',43,'2023-11-30 21:18:31','2023-11-30 21:18:31',NULL),
(45,'RAK PENERBITAN AUTO 2025','RAK PENERBITAN','App\\Models\\MediaHtml',44,'2023-11-30 21:19:23','2025-03-03 23:57:09',NULL),
(46,'HTML IG TVR','HTML IG TVR','App\\Models\\MediaHtml',45,'2023-11-30 21:21:04','2023-11-30 21:21:04',NULL),
(47,'SLIDER ','SLIDER TEST','App\\Models\\MediaSlider',1,'2023-11-30 21:27:02','2023-12-01 07:37:17','2023-12-01 07:37:17'),
(48,'KONTEN SLIDER','KONTEN SLIDER','App\\Models\\MediaSlider',2,'2023-12-01 08:47:14','2023-12-04 19:10:55','2023-12-04 19:10:55'),
(49,'E-Cuti','E-Cuti','App\\Models\\MediaHtml',46,'2023-12-04 07:05:08','2023-12-04 07:05:08',NULL),
(50,'Video Yankes Landscape spot 1','yankes\n','App\\Models\\MediaVideo',9,'2023-12-06 06:09:46','2023-12-06 06:09:46',NULL),
(51,'slider','slider','App\\Models\\MediaSlider',4,'2023-12-06 06:11:27','2023-12-06 06:11:27',NULL),
(52,'konten slider potrait','konten slider potrait','App\\Models\\MediaSlider',5,'2024-02-01 07:29:07','2024-02-01 07:29:07',NULL),
(53,'VIDEO POTRAIT IG 2','VIDEO POTRAIT IG 2','App\\Models\\MediaVideo',10,'2024-02-01 12:10:42','2024-03-14 06:09:37',NULL),
(54,'Rewaind','Rewaind','App\\Models\\MediaLiveUrl',14,'2024-02-06 13:58:43','2024-02-06 15:11:14','2024-02-06 15:11:14'),
(55,'DPR CONTENT REWIND 2023','DPR REWIND FORUM DISKUSI','App\\Models\\MediaLiveUrl',15,'2024-02-06 14:09:14','2024-02-06 14:23:30','2024-02-06 14:23:30'),
(56,'KONTEN YOUTUBE','KONTEN YOUTUBE','App\\Models\\MediaLiveUrl',16,'2024-02-06 14:26:00','2024-02-06 15:11:53',NULL),
(57,'MTQ 2024','MTQ 2024','App\\Models\\MediaImage',4,'2024-02-26 08:52:42','2024-02-26 08:52:42',NULL),
(58,'VIDEO POTRAIT IG 1','VIDEO POTRAIT IG 1','App\\Models\\MediaVideo',12,'2024-03-14 06:14:27','2024-03-14 06:14:27',NULL),
(59,'VIDEO 15 MENIT 5','VIDEO 15 MENIT 5','App\\Models\\MediaVideo',13,'2024-05-16 11:20:56','2024-05-16 11:20:56',NULL),
(60,'VIDEO 15 MENIT 6','VIDEO 15 MENIT 6','App\\Models\\MediaVideo',14,'2024-05-17 10:51:56','2024-05-17 10:51:56',NULL),
(61,'VIDEO 15 MENIT 7','VIDEO 15 MENIT 7','App\\Models\\MediaVideo',15,'2024-05-17 10:53:07','2024-05-17 10:53:07',NULL),
(62,'VIDEO 15 MENIT 8','VIDEO 15 MENIT 8','App\\Models\\MediaVideo',16,'2024-05-17 10:54:52','2024-05-17 10:54:52',NULL),
(63,'YANKES POTRAIT','YANKES POTRAIT','App\\Models\\MediaVideo',17,'2024-09-03 07:19:17','2024-09-03 07:19:17',NULL),
(64,'HTML IG YANKES','IG YANKES','App\\Models\\MediaHtml',47,'2024-09-03 07:41:44','2024-09-03 07:41:44',NULL),
(65,'PARJA','PARJA','App\\Models\\MediaVideo',18,'2024-09-11 08:35:20','2024-11-26 11:19:09','2024-11-26 11:19:09'),
(66,'VIDEO 15 MENIT 9','VIDEO 15 MENIT 9','App\\Models\\MediaVideo',19,'2024-09-20 07:42:07','2024-09-20 07:42:07',NULL),
(67,'VIDEO 15 MENIT 10','VIDEO 15 MENIT 10','App\\Models\\MediaVideo',20,'2024-09-20 07:47:17','2024-09-20 07:47:17',NULL),
(68,'KONTEN FULL POTRAIT','KONTEN FULL POTRAIT','App\\Models\\MediaVideo',21,'2024-09-30 18:36:58','2024-09-30 18:36:58',NULL),
(69,'KONTEN REQUEST','KONTEN REQUEST','App\\Models\\MediaVideo',22,'2024-10-01 04:30:19','2024-10-01 04:30:19',NULL),
(70,'Test upload media','Test upload media','App\\Models\\MediaImage',5,'2024-10-31 11:26:31','2025-02-06 09:23:03','2025-02-06 09:23:03'),
(71,'KONTEN ISU UTAMA','KONTEN ISU UTAMA','App\\Models\\MediaVideo',23,'2024-11-21 09:12:01','2024-11-21 09:12:06',NULL),
(72,'test1 edit','yaudah edit','App\\Models\\MediaImage',4,'2024-12-17 12:02:08','2024-12-17 12:02:51','2024-12-17 12:02:51'),
(73,'KONTEN LIVE DPR RI','KONTEN LIVE DPR RI','App\\Models\\MediaLiveUrl',18,'2025-01-30 09:16:40','2025-02-17 18:02:25',NULL),
(74,'test media video','test media video','App\\Models\\MediaVideo',151,'2025-02-25 09:29:07','2025-03-23 20:08:03',NULL),
(75,'JADWAL SHOLAT','JADWAL SHOLAT','App\\Models\\MediaLiveUrl',21,'2025-03-02 21:02:35','2025-03-03 18:04:30',NULL),
(76,'Test upload media video','Test upload media video','App\\Models\\MediaVideo',112,'2025-03-02 22:07:40','2025-03-02 22:08:23','2025-03-02 22:08:23'),
(77,'Test media live url','Test media live url','App\\Models\\MediaLiveUrl',19,'2025-03-02 22:08:52','2025-03-02 22:09:06','2025-03-02 22:09:06'),
(78,'Test media live url ','Test media live url ','App\\Models\\MediaLiveUrl',20,'2025-03-02 22:09:28','2025-03-02 22:09:43','2025-03-02 22:09:43'),
(79,'test','test','App\\Models\\MediaVideo',105,'2025-03-03 02:28:58','2025-03-03 02:29:16','2025-03-03 02:29:16'),
(80,'KONTEN MASJID 2','KONTEN MASJID 2','App\\Models\\MediaVideo',119,'2025-03-03 18:07:26','2025-03-03 18:07:26',NULL),
(81,'ISU UTAMA TESTING','ISU UTAMA TESTING','App\\Models\\MediaVideo',136,'2025-03-05 00:46:22','2025-03-05 00:46:22',NULL),
(82,'KEGIATAN RAMADHAN','KEGIATAN RAMADHAN','App\\Models\\MediaVideo',137,'2025-03-05 17:49:05','2025-03-05 17:49:05',NULL),
(83,'KONTEN 1080X1200','KONTEN 1080X1200','App\\Models\\MediaVideo',140,'2025-03-11 00:20:10','2025-03-11 00:20:10',NULL),
(84,'test video','test video','App\\Models\\MediaVideo',105,'2025-03-17 19:42:38','2025-03-17 22:20:50','2025-03-17 22:20:50'),
(85,'test media image','test media image','App\\Models\\MediaImage',19,'2025-03-24 21:32:11','2025-03-24 21:32:11',NULL),
(86,'Rak Buku','Rak Buku','App\\Models\\MediaHtml',52,'2025-06-24 19:43:40','2025-06-24 19:43:40',NULL),
(87,'HTML IG MEDSOS TEST','HTML IG MEDSOS TEST','App\\Models\\MediaHtml',53,'2025-08-25 20:24:15','2025-08-25 20:24:15',NULL);
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_hls`
--

DROP TABLE IF EXISTS `media_hls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_hls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `url` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_hls`
--

LOCK TABLES `media_hls` WRITE;
/*!40000 ALTER TABLE `media_hls` DISABLE KEYS */;
INSERT INTO `media_hls` VALUES
(1,'Live TVR HLS',NULL,'https://ssv1.dpr.go.id/golive/livestream/playlist.m3u8','2023-11-06 19:30:53','2025-02-06 08:48:30','2025-02-06 08:48:30'),
(2,'Emedia',NULL,'https://emedia.dpr.go.id/','2023-11-08 18:37:37','2023-11-08 18:37:37',NULL),
(3,'Emedia',NULL,'https://emedia.dpr.go.id','2023-11-08 18:41:46','2025-02-06 08:48:37','2025-02-06 08:48:37'),
(4,'LIVE TVR PARLEMEN',NULL,'https://ssv1.dpr.go.id/golive/livestream/playlist.m3u8','2023-11-27 12:38:19','2025-02-06 12:06:15',NULL),
(5,'LIVE STREAMING TVR',NULL,'https://ssv1.dpr.go.id/golive/livestream/playlist.m3u8','2023-11-27 12:38:38','2025-02-06 08:47:48','2025-02-06 08:47:48'),
(6,'DPR HLS',NULL,'https://www.youtube.com/watch?v=7TX85fTNVVo','2024-02-06 14:25:35','2024-02-06 14:27:46','2024-02-06 14:27:46'),
(7,'DPR REWIND YOUTUBE',NULL,'https://www.youtube.com/watch?v=7TX85fTNVVo','2024-02-06 14:25:49','2024-02-06 14:27:39','2024-02-06 14:27:39');
/*!40000 ALTER TABLE `media_hls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_htmls`
--

DROP TABLE IF EXISTS `media_htmls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_htmls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `path` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_htmls`
--

LOCK TABLES `media_htmls` WRITE;
/*!40000 ALTER TABLE `media_htmls` DISABLE KEYS */;
INSERT INTO `media_htmls` VALUES
(41,'HTML E MEDIA',NULL,'html-html-banner-e-media-auto.html','2023-11-30 21:16:05','2024-12-13 08:36:41',NULL),
(42,'JADWAL RAPAT',NULL,'html-html-jadwal-rapat 2025.html','2023-11-30 21:17:02','2025-04-14 20:57:48',NULL),
(43,'IG MEDSOS',NULL,'html-html-ig-medsos.html','2023-11-30 21:18:19','2024-01-10 08:17:00',NULL),
(44,'RAK PENERBITAN DPR',NULL,'html-html-rak penerbitan-2025.html','2023-11-30 21:19:09','2025-03-03 23:56:54',NULL),
(45,'IG TVR',NULL,'html-html-ig-tvr.html','2023-11-30 21:20:49','2024-01-09 12:53:48',NULL),
(46,'E-Cuti',NULL,'html-html-ecuti.html','2023-12-04 07:05:06','2023-12-04 07:05:06',NULL),
(47,'HTML IG YANKES',NULL,'html-html-ig-yankes.html','2024-09-03 07:41:42','2024-09-03 07:41:42',NULL),
(48,'test html 1',NULL,'html-html-rak penerbitan-2024.html','2024-12-10 09:04:08','2024-12-10 09:04:08',NULL),
(49,'test html 2',NULL,'html-html-rak penerbitan-2024.html','2024-12-10 09:23:32','2024-12-10 09:23:32',NULL),
(50,'test html',NULL,'html-html-html-rak penerbitan-2024.html','2025-03-11 02:29:15','2025-03-11 03:09:20','2025-03-11 03:09:20'),
(51,'test html 3',NULL,'html-html-html-rak penerbitan-2024.html','2025-03-11 02:33:49','2025-03-11 02:33:49',NULL),
(52,'Rak Buku',NULL,'html-html-rak buku.html','2025-06-24 19:43:20','2025-06-24 19:43:20',NULL),
(53,'HTML IG MEDSOS TEST',NULL,'html-html-ig-medsos-auto.html','2025-08-25 20:22:10','2025-08-25 20:22:10',NULL);
/*!40000 ALTER TABLE `media_htmls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_images`
--

DROP TABLE IF EXISTS `media_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `path` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_images`
--

LOCK TABLES `media_images` WRITE;
/*!40000 ALTER TABLE `media_images` DISABLE KEYS */;
INSERT INTO `media_images` VALUES
(1,'No Media','no-media-content','no-media.png','2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(2,'Logo Kiosk',NULL,'attachment-attachment-label kiosk.jpg','2023-11-06 19:27:18','2025-03-17 22:24:28',NULL),
(3,'LOGO KIOSK',NULL,'attachment-label kiosk.jpg','2023-11-27 16:14:51','2024-12-09 10:57:34',NULL),
(4,'MTQ 2024',NULL,'attachment-mtq untuk dikiosk.jpg','2024-02-26 08:52:10','2024-02-26 08:52:10',NULL),
(5,'Test upload (should be deleted)',NULL,'01JBGQFKTN6WT25075PMZC66TT.jpg','2024-10-31 11:26:18','2024-11-06 13:41:11','2024-11-06 13:41:11'),
(6,'test prod upload image 1',NULL,'01JC0A9X7Q2XTD79J6HCVGB6QM.jpeg','2024-11-06 13:43:51','2024-11-06 13:44:24','2024-11-06 13:44:24'),
(7,'test',NULL,'01JEDZYP91GERXJG6V9PKK6PPP.jpg','2024-12-06 17:43:43','2024-12-06 17:46:34','2024-12-06 17:46:34'),
(8,'test',NULL,'01JEE04GQY26KAT1STMSK0QQ8P.jpg','2024-12-06 17:46:54','2024-12-08 21:49:14','2024-12-08 21:49:14'),
(9,'test 2',NULL,'01JEH04JVN2WHX3PR5ABHKNY0Z.jpg','2024-12-07 21:44:39','2024-12-08 21:49:07','2024-12-08 21:49:07'),
(10,'test1',NULL,'01JEKKF9AD4NW1E5M387AAYFFN.jpg','2024-12-08 21:49:35','2024-12-08 22:01:40','2024-12-08 22:01:40'),
(11,'test1',NULL,'01JEKKH3KMDQQXM1T7CJ0H8Q3J.jpg','2024-12-08 22:02:01','2024-12-08 22:33:22','2024-12-08 22:33:22'),
(12,'test1',NULL,'01JEKNNB3BE37M6V74KV9JQB4Q.jpg','2024-12-08 22:33:41','2024-12-10 08:47:35','2024-12-10 08:47:35'),
(13,'test2',NULL,'attachment-test anlytic.jpg','2024-12-09 08:19:22','2024-12-10 08:47:23','2024-12-10 08:47:23'),
(14,'test upload without minio 1',NULL,'attachment-test anlytic.jpg','2024-12-10 08:13:29','2024-12-10 08:47:10','2024-12-10 08:47:10'),
(15,'test upload without minio 1',NULL,'attachment-test anlytic.jpg','2024-12-10 08:23:08','2024-12-10 08:46:13','2024-12-10 08:46:13'),
(16,'test ori upload image 1',NULL,'attachment-test anlytic.jpg','2024-12-10 08:42:47','2024-12-10 08:46:44','2024-12-10 08:46:44'),
(17,'test upload image 1',NULL,'attachment-test anlytic.jpg','2024-12-10 08:48:20','2024-12-10 09:15:05','2024-12-10 09:15:05'),
(18,'test image 2',NULL,'attachment-test anlytic.jpg','2024-12-10 09:14:43','2024-12-10 09:14:43',NULL),
(19,'test image 1',NULL,'attachment-attachment-label kiosk.jpg','2024-12-11 08:25:29','2025-02-18 02:39:14',NULL),
(20,'test 3',NULL,'attachment-merah muda putih minimalis estetik jadwal kuliah wallpaper telepon.png','2025-03-24 23:38:56','2025-03-24 23:42:51','2025-03-24 23:42:51'),
(21,'tes image 3',NULL,'attachment-pexels-pixabay-416160.jpg','2025-03-25 00:09:58','2025-03-25 02:07:29','2025-03-25 02:07:29'),
(22,'test image 4',NULL,'images/attachment-pexels-pixabay-416160.jpg','2025-03-25 01:50:42','2025-03-25 02:06:22','2025-03-25 02:06:22'),
(23,'test image 3',NULL,'/attachment-josh-couch-vv45xemjwzk-unsplash.webp','2025-03-25 02:37:05','2025-03-25 02:53:04','2025-03-25 02:53:04'),
(24,'test image',NULL,'/attachment-kate-stone-matheson-uy5t-cjuik4-unsplash.webp','2025-03-25 02:58:29','2025-03-25 03:52:54','2025-03-25 03:52:54'),
(25,'test webp dan jpg',NULL,'/attachment-josh-couch-vv45xemjwzk-unsplash.webp','2025-03-25 03:14:00','2025-03-25 03:51:28','2025-03-25 03:51:28'),
(26,'tes lagi jpg dan webp',NULL,'/attachment-pexels-pixabay-416160.webp','2025-03-25 03:23:43','2025-03-25 03:49:58','2025-03-25 03:49:58'),
(27,'test image k',NULL,'images/attachment-kate-stone-matheson-uy5t-cjuik4-unsplash.webp','2025-03-25 03:31:24','2025-03-25 03:47:10','2025-03-25 03:47:10'),
(28,'test image upload',NULL,'/attachment-kate-stone-matheson-uy5t-cjuik4-unsplash.webp','2025-03-25 19:45:16','2025-03-25 19:45:16',NULL);
/*!40000 ALTER TABLE `media_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_live_urls`
--

DROP TABLE IF EXISTS `media_live_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_live_urls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `url` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_live_urls`
--

LOCK TABLES `media_live_urls` WRITE;
/*!40000 ALTER TABLE `media_live_urls` DISABLE KEYS */;
INSERT INTO `media_live_urls` VALUES
(1,'Embed Youtube',NULL,'https://www.youtube.com/embed/NI-zI57ONEQ?si=dtAJRDbMhiwJYf30','2023-11-09 20:05:56','2023-11-30 06:36:03','2023-11-30 06:36:03'),
(2,'panduan cuti',NULL,'https://penerbitan-dpr.id/cuti/','2023-11-20 09:06:54','2023-12-05 12:42:49','2023-12-05 12:42:49'),
(3,'LIVE STREAMING TVR',NULL,'https://ssv1.dpr.go.id/golive/livestream/playlist.m3u8','2023-11-21 11:24:27','2023-11-30 06:35:56','2023-11-30 06:35:56'),
(4,'Konten url',NULL,'https://www.detik.com/','2023-11-24 14:28:46','2023-11-30 06:35:46','2023-11-30 06:35:46'),
(5,'Live Emedia',NULL,'https://emedia.dpr.go.id/','2023-11-27 18:24:02','2025-02-18 06:58:02',NULL),
(6,'cntoh url',NULL,'https://www.traveloka.com/','2023-11-27 18:59:04','2023-11-30 06:35:36','2023-11-30 06:35:36'),
(7,'bandara url',NULL,'https://soekarnohatta-airport.co.id/','2023-11-27 19:00:31','2023-11-30 06:35:32','2023-11-30 06:35:32'),
(8,'traveloka',NULL,'https://www.traveloka.com/en-id/','2023-11-27 19:04:14','2023-11-30 06:35:27','2023-11-30 06:35:27'),
(9,'KAI',NULL,'https://www.kai.id/','2023-11-27 19:06:09','2023-11-30 06:35:23','2023-11-30 06:35:23'),
(10,'ANTARA',NULL,'https://www.antaranews.com/','2023-11-27 19:08:42','2023-11-30 06:35:03','2023-11-30 06:35:03'),
(11,'DPR RI YOUTUBE',NULL,'https://youtu.be/0lUXiarqlsY?si=wt9I5q3x9qmrukQ1','2023-11-27 19:12:32','2023-11-30 06:35:08','2023-11-30 06:35:08'),
(12,'DPR RI',NULL,'https://www.youtube.com/embed/H9AQePpZV34?si=s-8vxE8YeNE3w0os','2023-11-27 19:15:32','2023-11-30 06:35:12','2023-11-30 06:35:12'),
(13,'DPR RI YOUTUBE',NULL,'https://www.youtube.com/embed/videoseries?si=9hc1J3wI5SrBfrpG&amp;list=PL1i5C6Kd5FQi1wMRUObocFUIPnULBgO0L','2023-11-27 19:17:25','2023-11-30 06:35:16','2023-11-30 06:35:16'),
(14,'Rewaind',NULL,'https://www.youtube.com/live/7TX85fTNVVo?si=gSibhj6-HTm4qXrc','2024-02-06 13:58:40','2024-02-06 14:50:51','2024-02-06 14:50:51'),
(15,'REWIND DPR',NULL,'https://www.youtube.com/live/7TX85fTNVVo?si=rJ_TKT7iXCtyIVBb','2024-02-06 14:08:59','2024-02-06 14:50:38','2024-02-06 14:50:38'),
(16,'URL YOUTUBE',NULL,'https://www.youtube.com/embed/UIM8MIT-XI8?si=lNCyM3mk-uKv-tmH','2024-02-06 14:29:28','2024-02-06 14:53:11',NULL),
(17,'LIVE DPR RI',NULL,'https://www.youtube.com/watch?v=hw4mSxWVmCg','2025-02-12 01:11:25','2025-02-17 17:57:46','2025-02-17 17:57:46'),
(18,'LIVE DPR RI',NULL,'https://www.youtube.com/watch?v=458uPJooe9g&t=14s','2025-02-12 01:12:15','2025-02-18 01:31:17',NULL),
(19,'test yt',NULL,'https://www.youtube.com/watch?v=wXJZ-_Pm3d8','2025-02-25 09:27:57','2025-02-25 09:27:57',NULL),
(20,'KONTEN MESJID 1',NULL,'https://www.jadwalsholat.org/','2025-03-02 21:02:08','2025-03-03 18:04:52','2025-03-03 18:04:52'),
(21,'JADWAL SHOLAT',NULL,'https://www.jadwalsholat.org','2025-03-02 22:50:58','2025-03-11 00:42:06',NULL);
/*!40000 ALTER TABLE `media_live_urls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_qr_codes`
--

DROP TABLE IF EXISTS `media_qr_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_qr_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `path` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_qr_codes`
--

LOCK TABLES `media_qr_codes` WRITE;
/*!40000 ALTER TABLE `media_qr_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `media_qr_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_slider_contents`
--

DROP TABLE IF EXISTS `media_slider_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_slider_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL DEFAULT 'Content',
  `media_slider_id` bigint(20) unsigned NOT NULL,
  `path` varchar(191) NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `mime` varchar(255) NOT NULL,
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_slider_contents`
--

LOCK TABLES `media_slider_contents` WRITE;
/*!40000 ALTER TABLE `media_slider_contents` DISABLE KEYS */;
INSERT INTO `media_slider_contents` VALUES
(1,'SLIDER',1,'attachment-attachment-portrait car indonesia.mp4',12000,'2023-11-30 21:24:20','2023-11-30 21:25:57',NULL,'video/mp4',NULL),
(2,'IMAGE',1,'attachment-whatsapp image 2023-11-04 at 16.16.27.jpeg',5000,'2023-11-30 21:24:45','2023-11-30 21:24:45',NULL,'image/jpeg',NULL),
(3,'SLIDER 3',1,'attachment-attachment-indonesia.mp4',13000,'2023-11-30 21:25:38','2023-11-30 21:25:38',NULL,'video/mp4',NULL),
(4,'KONTEN SLIDER',2,'attachment-1.png',3,'2023-12-01 07:25:51','2023-12-04 08:54:56',NULL,'image/png',NULL),
(5,'KONTEN SLIDER VIDEO 1',2,'attachment-2.mp4',59,'2023-12-01 07:26:59','2023-12-04 08:55:01',NULL,'video/mp4',NULL),
(6,'KONTEN SLIDER GMBR 1',2,'attachment-3.mp4',3,'2023-12-01 07:27:26','2023-12-04 08:55:05',NULL,'video/mp4',NULL),
(7,'KONTEN SLIDER VIDEO 2',2,'attachment-4.mp4',37,'2023-12-01 07:29:36','2023-12-04 08:55:11',NULL,'video/mp4',NULL),
(8,'Yankes Slider',4,'attachment-slide1.jpg',5000,'2023-12-06 05:58:45','2023-12-06 05:58:45',NULL,'image/jpeg',NULL),
(9,'Yankes Slider',4,'attachment-slide2.jpg',5000,'2023-12-06 05:58:59','2023-12-06 05:58:59',NULL,'image/jpeg',NULL),
(10,'Yankes Slider',4,'attachment-slide3.jpg',5000,'2023-12-06 05:59:16','2023-12-06 05:59:16',NULL,'image/jpeg',NULL),
(11,'Yankes Slider',4,'attachment-slide4.jpg',5000,'2023-12-06 05:59:27','2023-12-06 05:59:27',NULL,'image/jpeg',NULL),
(12,'Yankes Slider',4,'attachment-slide5.jpg',5000,'2023-12-06 05:59:38','2023-12-06 05:59:38',NULL,'image/jpeg',NULL),
(13,'Yankes Slider',4,'attachment-slide6.jpg',5000,'2023-12-06 05:59:49','2023-12-06 05:59:49',NULL,'image/jpeg',NULL),
(14,'Yankes Slider',4,'attachment-slide7.jpg',5000,'2023-12-06 06:00:00','2023-12-06 06:00:00',NULL,'image/jpeg',NULL),
(15,'Yankes Slider',4,'attachment-slide8.jpg',5000,'2023-12-06 06:00:11','2023-12-06 06:00:11',NULL,'image/jpeg',NULL),
(16,'Yankes Slider',4,'attachment-slide9.jpg',5000,'2023-12-06 06:00:25','2023-12-06 06:00:25',NULL,'image/jpeg',NULL),
(17,'1mnt 3detik',5,'attachment-1mnt 3detik.mp4',61800,'2024-02-01 07:21:04','2024-02-01 07:21:04',NULL,'video/mp4',NULL),
(18,'1mnt 34detik',5,'attachment-1mnt 34detik.mp4',80400,'2024-02-01 07:21:47','2024-02-01 07:21:47',NULL,'video/mp4',NULL),
(19,'1mnt 58detik',5,'attachment-1mnt 58detik.mp4',94800,'2024-02-01 07:22:14','2024-02-01 07:22:14',NULL,'video/mp4',NULL),
(20,'31detik',5,'attachment-31detik.mp4',31000,'2024-02-01 07:23:46','2024-02-01 07:23:46',NULL,'video/mp4',NULL),
(21,'38detik',5,'attachment-38detik.mp4',38000,'2024-02-01 07:24:10','2024-02-01 07:24:10',NULL,'video/mp4',NULL),
(22,'46detik',5,'attachment-46detik.mp4',46000,'2024-02-01 07:25:26','2024-02-01 07:25:26',NULL,'video/mp4',NULL),
(23,'47detik',5,'attachment-47detik.mp4',47000,'2024-02-01 07:26:24','2024-02-01 07:26:24',NULL,'video/mp4',NULL),
(24,'47dtk',5,'attachment-47dtk.mp4',47000,'2024-02-01 07:26:45','2024-02-01 07:26:45',NULL,'video/mp4',NULL),
(25,'48detik',5,'attachment-48detik.mp4',48000,'2024-02-01 07:28:19','2024-02-01 07:28:19',NULL,'video/mp4',NULL),
(26,'Yankes Slider',4,'attachment-img-20240903-wa0003.jpg',5000,'2024-09-03 10:17:33','2024-09-03 10:17:33',NULL,'image/jpeg',NULL),
(27,'Yankes Slider',4,'attachment-img-20240903-wa0004.jpg',5000,'2024-09-03 10:17:47','2024-09-03 10:17:47',NULL,'image/jpeg',NULL),
(28,'Yankes Slider',4,'attachment-img-20240903-wa0005.jpg',5000,'2024-09-03 10:17:58','2024-09-03 10:17:58',NULL,'image/jpeg',NULL);
/*!40000 ALTER TABLE `media_slider_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_sliders`
--

DROP TABLE IF EXISTS `media_sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_sliders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `animation_type` varchar(191) NOT NULL DEFAULT 'slide',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_sliders`
--

LOCK TABLES `media_sliders` WRITE;
/*!40000 ALTER TABLE `media_sliders` DISABLE KEYS */;
INSERT INTO `media_sliders` VALUES
(1,'SLIDER 1 ','fade','2023-11-27 18:14:31','2023-12-01 07:35:18','2023-12-01 07:35:18'),
(2,'KONTEN SLIDER','slide','2023-12-01 07:24:59','2023-12-06 06:09:10','2023-12-06 06:09:10'),
(3,'KONTEN SLIDER','slide','2023-12-01 07:39:27','2023-12-01 07:41:30','2023-12-01 07:41:30'),
(4,'Yankes Slider','slide','2023-12-06 05:55:36','2023-12-06 05:55:36',NULL),
(5,'konten slider potrait','slide','2024-02-01 07:07:38','2024-02-01 07:07:38',NULL),
(6,'test1 edit','flip','2024-12-17 11:58:51','2024-12-17 11:59:36','2024-12-17 11:59:36');
/*!40000 ALTER TABLE `media_sliders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_videos`
--

DROP TABLE IF EXISTS `media_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_videos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `path` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_videos`
--

LOCK TABLES `media_videos` WRITE;
/*!40000 ALTER TABLE `media_videos` DISABLE KEYS */;
INSERT INTO `media_videos` VALUES
(1,'VIDEO 15 MENIT 1',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2023-11-06 19:33:54','2025-05-14 03:32:40',NULL),
(2,'VIDEO 15 MENIT 2',NULL,'PLAYLIST 2 VIDEO 15 MENIT.mp4','2023-11-06 19:34:40','2025-05-20 01:30:53',NULL),
(3,'VIDEO 15 MENIT 3',NULL,'PLAYLIST 3 VIDEO 15 MENIT.mp4','2023-11-06 19:35:39','2025-04-07 23:30:40',NULL),
(4,'VIDEO 15 MENIT 4',NULL,'PLAYLIST 4 VIDEO 15 MENIT.mp4','2023-11-06 19:36:21','2025-09-15 21:39:45',NULL),
(5,'KONTEN VIDEO ',NULL,'attachment-full.mp4','2023-11-19 21:07:08','2024-01-04 11:12:28',NULL),
(6,'MIKTA MPA',NULL,'attachment-mikta.mp4 (1).mp4','2023-11-20 18:23:23','2023-12-12 20:09:04','2023-12-12 20:09:04'),
(7,'MIKTA MP4',NULL,'attachment-mikta.mp4 (1).mp4','2023-11-20 18:23:59','2023-12-12 20:09:00','2023-12-12 20:09:00'),
(8,'VIDEO 6 MENIT',NULL,'attachment-6 menit.mp4','2023-11-29 07:16:23','2023-12-12 20:08:53','2023-12-12 20:08:53'),
(9,'Video Yankes Landscape spot 1',NULL,'attachment-attachment-konten video_yankes 20 juni 2024.mp4','2023-12-06 05:55:03','2024-12-09 09:20:48',NULL),
(10,'VIDEO POTRAIT IG 2',NULL,'15.2 UPDATE.mp4','2024-02-01 12:10:25','2026-01-15 01:06:10',NULL),
(11,'VIDEO POTRAIT IG 1',NULL,'attachment-14.1.mp4','2024-03-14 06:08:20','2024-03-14 06:10:26','2024-03-14 06:10:26'),
(12,'VIDEO POTRAIT IG 1',NULL,'15.1.mp4','2024-03-14 06:14:02','2026-01-15 00:35:24',NULL),
(13,'VIDEO 15 MENIT 5',NULL,'PLAYLIST 5 VIDEO 15 MENIT.mp4','2024-05-16 11:20:53','2025-05-14 03:32:32',NULL),
(14,'VIDEO 15 MENIT 6',NULL,'PLAYLIST 6 VIDEO 15 MENIT.mp4','2024-05-17 10:51:52','2025-05-14 05:30:31',NULL),
(15,'VIDEO 15 MENIT 7',NULL,'PLAYLIST 7 VIDEO 15 MENIT.mp4','2024-05-17 10:52:58','2025-05-14 03:48:54',NULL),
(16,'VIDEO 15 MENIT 8',NULL,'PLAYLIST 8 VIDEO 15 MENIT.mp4','2024-05-17 10:54:50','2025-05-14 05:12:17',NULL),
(17,'YANKES POTRAIT',NULL,'attachment-yankes 1.mp4','2024-09-03 07:19:12','2024-09-03 07:19:12',NULL),
(18,'PARJA',NULL,'attachment-parja 2024 2.mp4','2024-09-11 08:35:17','2025-03-11 21:09:31','2025-03-11 21:09:31'),
(19,'VIDEO 15 MENIT 9',NULL,'PLAYLIST 9 VIDEO 15 MENIT.mp4','2024-09-20 07:42:05','2025-05-14 03:48:59',NULL),
(20,'VIDEO 15 MENIT 10',NULL,'PLAYLIST 10 VIDEO 15 MENIT.mp4','2024-09-20 07:47:15','2025-09-15 02:08:46',NULL),
(21,'KONTEN FULL POTRAIT',NULL,'attachment-konten 1 oktober 2024 update.mp4','2024-09-30 18:36:54','2024-10-01 14:14:51',NULL),
(22,'KONTEN REQUEST',NULL,'MASJID.mp4','2024-10-01 04:30:17','2025-03-02 21:50:09',NULL),
(23,'KONTEN ISU UTAMA',NULL,'KONTEN ISU UTAMA 15 JANUARI 2026.mp4','2024-11-21 09:11:58','2026-01-15 02:47:30',NULL),
(24,'test video 1',NULL,'attachment-testerrorvideoanalytic1.mp4','2024-12-09 08:28:36','2024-12-16 08:19:49','2024-12-16 08:19:49'),
(25,'test video 2',NULL,'attachment-attachment-testerrorvideoanalytic1.mp4','2024-12-10 09:21:04','2024-12-16 08:19:47','2024-12-16 08:19:47'),
(26,'test video 3',NULL,'attachment-attachment-testerrorvideoanalytic1.mp4','2024-12-11 08:28:29','2024-12-16 08:19:45','2024-12-16 08:19:45'),
(27,'test video 4',NULL,'attachment-attachment-testerrorvideoanalytic1.mp4','2024-12-11 12:21:19','2024-12-16 08:19:43','2024-12-16 08:19:43'),
(28,'KONTEN REQUEST COMPRESSED',NULL,'attachment-lama 720p.mp4','2025-01-30 09:07:22','2025-01-30 15:56:27',NULL),
(29,'KONTEN REQUEST COMPRESSED 2',NULL,'attachment-telegram web result.mp4','2025-01-30 09:50:42','2025-01-30 09:50:42',NULL),
(87,'test1 edit',NULL,'0NnONFbb6DibfVAQk0Yq2FmFaKKcK3FQCUXWrUcU.mp4','2025-02-14 04:15:44','2025-02-16 05:30:09','2025-02-16 05:30:09'),
(88,'test',NULL,'testvideolama.mp4','2025-02-16 05:30:41','2025-02-17 00:07:55','2025-02-17 00:07:55'),
(89,'test',NULL,'testvideolama.mp4','2025-02-17 00:08:12','2025-02-17 00:11:51','2025-02-17 00:11:51'),
(90,'test',NULL,'testvideolama.mp4','2025-02-17 00:13:26','2025-02-17 03:48:52','2025-02-17 03:48:52'),
(91,'test chunk file',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-17 03:37:37','2025-02-17 03:38:43','2025-02-17 03:38:43'),
(92,'test chunk file',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-17 03:38:21','2025-02-17 03:38:36','2025-02-17 03:38:36'),
(93,'test chunk file',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-17 03:39:10','2025-02-17 03:43:21','2025-02-17 03:43:21'),
(94,'test chunk file',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-17 03:43:43','2025-02-17 03:45:30','2025-02-17 03:45:30'),
(95,'test chunk file',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-17 03:46:16','2025-02-17 03:47:25','2025-02-17 03:47:25'),
(96,'test chunk file',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-17 03:47:47','2025-02-17 03:48:49','2025-02-17 03:48:49'),
(97,'local test chunk',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-17 03:56:56','2025-02-18 02:18:48','2025-02-18 02:18:48'),
(98,'localtestchunk2',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-17 04:01:12','2025-02-18 02:18:40','2025-02-18 02:18:40'),
(99,'test',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-02-18 02:19:13','2025-02-25 05:56:45','2025-02-25 05:56:45'),
(100,'test',NULL,'test.mp4','2025-02-25 05:28:40','2025-02-25 05:56:32','2025-02-25 05:56:32'),
(101,'test',NULL,'videotest.mp4','2025-02-25 05:43:17','2025-02-25 05:56:20','2025-02-25 05:56:20'),
(102,'test1 edit',NULL,'videotest(1).mp4','2025-02-25 06:07:34','2025-02-25 06:16:09','2025-02-25 06:16:09'),
(103,'test',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-02-25 06:17:07','2025-02-25 09:22:23','2025-02-25 09:22:23'),
(104,'test2',NULL,'videotest(1).mp4','2025-02-25 07:58:09','2025-02-25 09:22:20','2025-02-25 09:22:20'),
(105,'PLAYLIST 1 VIDEO 15 MENIT',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-02-25 09:22:59','2025-03-18 00:30:09',NULL),
(106,'test upload',NULL,'8K HDR IMAX _ Target -02_15.00 (Top Gun Maverick) _ Dolby 5.1.mp4','2025-02-26 00:38:43','2025-02-26 02:47:09','2025-02-26 02:47:09'),
(107,'ini coba upload video 2',NULL,'saveinsta.cc_1080p-8k-hdr-imax-target-02-15-00-top-gun-maverick-dolby-5-1.mp4','2025-02-26 02:00:20','2025-02-26 02:46:57','2025-02-26 02:46:57'),
(108,'test upload',NULL,'saveinsta.cc_1080p-8k-hdr-imax-target-02-15-00-top-gun-maverick-dolby-5-1.mp4','2025-02-26 02:49:33','2025-02-26 04:12:16','2025-02-26 04:12:16'),
(109,'test lagi',NULL,'saveinsta.cc_1080p-8k-hdr-imax-target-02-15-00-top-gun-maverick-dolby-5-1.mp4','2025-02-26 03:14:08','2025-02-26 04:10:51','2025-02-26 04:10:51'),
(110,'test yang ke 3',NULL,'saveinsta.cc_1080p-8k-hdr-imax-target-02-15-00-top-gun-maverick-dolby-5-1.mp4','2025-02-26 03:28:26','2025-02-26 03:44:50','2025-02-26 03:44:50'),
(111,'tes lagi 3',NULL,'saveinsta.cc_1080p-8k-hdr-imax-target-02-15-00-top-gun-maverick-dolby-5-1.mp4','2025-02-26 03:41:26','2025-02-26 03:52:31','2025-02-26 03:52:31'),
(112,'coba tes lagi',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-02-26 04:16:24','2025-03-03 19:51:37','2025-03-03 19:51:37'),
(113,'tes bang',NULL,'saveinsta.cc_1080p-8k-hdr-imax-target-02-15-00-top-gun-maverick-dolby-5-1.mp4','2025-02-26 04:19:54','2025-02-26 04:20:45','2025-02-26 04:20:45'),
(114,'PLAYLIST 1 VIDEO 15 MENIT.mp4',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-02-27 20:21:49','2025-02-27 20:21:49',NULL),
(115,'PLAYLIST 1 VIDEO 15 MENIT.mp4',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-02-27 20:22:30','2025-02-27 20:22:30',NULL),
(116,'video test',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-02-27 21:08:17','2025-02-27 21:11:40','2025-02-27 21:11:40'),
(117,'test 1',NULL,'testvideolama.mp4','2025-03-03 02:41:00','2025-03-03 02:54:14','2025-03-03 02:54:14'),
(118,'test again',NULL,'attachment-playlist 6 video 15 menit.mp4','2025-03-03 02:55:59','2025-03-03 02:56:09','2025-03-03 02:56:09'),
(119,'KONTEN MASJID 2',NULL,'KONTEN MASJID.mp4','2025-03-03 18:06:40','2025-03-26 20:59:52',NULL),
(120,'test upload',NULL,'testvideolama(1).mp4','2025-03-03 21:02:17','2025-03-03 21:03:28','2025-03-03 21:03:28'),
(121,'test upload video',NULL,'testvideolama(1).mp4','2025-03-03 21:04:39','2025-03-03 21:06:56','2025-03-03 21:06:56'),
(122,'test upload',NULL,'testvideolama(1).mp4','2025-03-03 21:08:20','2025-03-03 21:09:22','2025-03-03 21:09:22'),
(123,'test upload',NULL,'testvideolama(1).mp4','2025-03-03 21:10:30','2025-03-03 21:12:24','2025-03-03 21:12:24'),
(124,'test upload',NULL,'testvideolama(1).mp4','2025-03-03 21:14:14','2025-03-03 21:14:37','2025-03-03 21:14:37'),
(125,'upload test',NULL,'testvideolama(1).mp4','2025-03-03 21:15:46','2025-03-03 21:16:42','2025-03-03 21:16:42'),
(126,'upload test',NULL,'testvideolama(1).mp4','2025-03-03 21:18:48','2025-03-03 21:20:34','2025-03-03 21:20:34'),
(127,'test upload',NULL,'testvideolama(1).mp4','2025-03-03 21:24:54','2025-03-03 21:25:11','2025-03-03 21:25:11'),
(128,'upload test',NULL,'testvideolama(1).mp4','2025-03-03 21:33:53','2025-03-03 21:38:15','2025-03-03 21:38:15'),
(129,'test upload',NULL,'testvideolama(1).mp4','2025-03-03 21:39:27','2025-03-03 23:18:54','2025-03-03 23:18:54'),
(130,'upload test',NULL,'testvideolama(1).mp4','2025-03-03 23:32:30','2025-03-03 23:34:24','2025-03-03 23:34:24'),
(131,'test upload',NULL,'testvideolama(1).mp4','2025-03-03 23:42:43','2025-03-03 23:54:33','2025-03-03 23:54:33'),
(132,'test upload 2',NULL,'testvideolama(1).mp4','2025-03-03 23:48:26','2025-03-03 23:53:16','2025-03-03 23:53:16'),
(133,'test upload 3',NULL,'testvideolama(1).mp4','2025-03-03 23:49:38','2025-03-03 23:53:03','2025-03-03 23:53:03'),
(134,'test video teru',NULL,'testvideolama(1).mp4','2025-03-03 23:51:53','2025-03-03 23:52:53','2025-03-03 23:52:53'),
(135,'test lagi',NULL,'testvideolama(1).mp4','2025-03-04 00:31:17','2025-03-04 00:31:36','2025-03-04 00:31:36'),
(136,'ISU UTAMA TESTING',NULL,'0217.mp4','2025-03-05 00:39:27','2025-03-05 00:39:58',NULL),
(137,'KEGIATAN RAMADHAN',NULL,'KEGIATAN RAMADHAN.mp4','2025-03-05 17:48:50','2025-03-18 00:16:18',NULL),
(138,'test upload refresh',NULL,'PLAYLIST 1 VIDEO 15 MENIT - Test Refresh.mp4','2025-03-10 00:14:10','2025-03-10 00:29:24','2025-03-10 00:29:24'),
(139,'test lagi untuk refresh',NULL,'PLAYLIST 1 VIDEO 15 MENIT - Test Refresh.mp4','2025-03-10 00:33:42','2025-03-10 21:41:09','2025-03-10 21:41:09'),
(140,'KONTEN 1080X1200',NULL,'0310-copy.mp4','2025-03-11 00:19:50','2025-03-11 00:19:50',NULL),
(141,'test 1',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-03-12 02:43:23','2025-03-12 02:45:31','2025-03-12 02:45:31'),
(142,'tes upload',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-03-12 19:04:31','2025-03-12 19:04:54','2025-03-12 19:04:54'),
(143,'test upload',NULL,'gantinama.mp4','2025-03-12 19:06:42','2025-03-12 19:08:59','2025-03-12 19:08:59'),
(144,'tes upload',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-03-12 19:13:12','2025-03-12 19:13:28','2025-03-12 19:13:28'),
(145,'test upload',NULL,'PLAYLIST 1 VIDEO 15 MENIT.mp4','2025-03-12 19:24:42','2025-03-12 19:30:15','2025-03-12 19:30:15'),
(146,'test upload',NULL,'gantinama.mp4','2025-03-12 19:41:19','2025-03-12 19:42:00','2025-03-12 19:42:00'),
(147,'test upload baru',NULL,'8K HDR IMAX _ Target -02_15.00 (Top Gun Maverick) _ Dolby 5.1.mp4','2025-03-18 01:01:08','2025-03-18 03:19:32','2025-03-18 03:19:32'),
(148,'tes lagi',NULL,'test upload.mp4','2025-03-18 01:05:30','2025-03-18 01:06:17','2025-03-18 01:06:17'),
(149,'test upload mantap',NULL,'test upload.mp4','2025-03-18 01:26:19','2025-03-18 01:27:37','2025-03-18 01:27:37'),
(150,'test upload mantap',NULL,'PLAYLIST 1 VIDEO 15 MENIT - Test Refresh.mp4','2025-03-18 01:32:49','2025-03-18 01:33:39','2025-03-18 01:33:39'),
(151,'test',NULL,'KONTEN_DEPAN_PPID_1_DESEMBER_2025.mp4','2025-03-18 03:37:06','2026-01-26 11:52:43',NULL);
/*!40000 ALTER TABLE `media_videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'2014_10_12_000000_create_users_table',1),
(2,'2014_10_12_100000_create_password_reset_tokens_table',1),
(3,'2019_08_19_000000_create_failed_jobs_table',1),
(4,'2019_12_14_000001_create_personal_access_tokens_table',1),
(5,'2023_09_03_054546_create_permission_tables',1),
(6,'2023_09_10_083918_create_media_table',1),
(7,'2023_09_10_092941_create_running_texts_table',1),
(8,'2023_09_10_173424_create_screens_table',1),
(9,'2023_09_10_173425_create_layouts_table',1),
(10,'2023_09_13_022102_create_spots_table',1),
(11,'2023_09_17_092150_create_media_images_table',1),
(12,'2023_09_17_102957_create_media_htmls_table',1),
(13,'2023_09_17_113129_create_media_hls_table',1),
(14,'2023_09_17_113809_create_media_videos_table',1),
(15,'2023_09_17_114621_create_media_qr_codes_table',1),
(16,'2023_09_19_145332_create_media_live_urls_table',1),
(17,'2023_09_19_152146_create_media_sliders_table',1),
(18,'2023_09_19_153727_create_media_slider_contents_table',1),
(19,'2023_10_03_130021_create_playlists_table',1),
(20,'2023_10_03_133238_create_playlist_layouts_table',1),
(21,'2023_10_03_154250_create_schedules_table',1),
(22,'2023_10_03_154311_create_schedule_playlists_table',1),
(23,'2023_10_03_162924_create_displays_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES
(1,'App\\Models\\User',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES
(1,'view_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(2,'view_any_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(3,'create_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(4,'update_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(5,'restore_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(6,'restore_any_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(7,'replicate_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(8,'reorder_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(9,'delete_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(10,'delete_any_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(11,'force_delete_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(12,'force_delete_any_display','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(13,'view_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(14,'view_any_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(15,'create_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(16,'update_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(17,'restore_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(18,'restore_any_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(19,'replicate_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(20,'reorder_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(21,'delete_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(22,'delete_any_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(23,'force_delete_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(24,'force_delete_any_layout','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(25,'view_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(26,'view_any_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(27,'create_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(28,'update_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(29,'restore_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(30,'restore_any_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(31,'replicate_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(32,'reorder_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(33,'delete_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(34,'delete_any_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(35,'force_delete_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(36,'force_delete_any_media','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(37,'view_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(38,'view_any_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(39,'create_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(40,'update_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(41,'restore_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(42,'restore_any_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(43,'replicate_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(44,'reorder_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(45,'delete_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(46,'delete_any_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(47,'force_delete_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(48,'force_delete_any_media::hls','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(49,'view_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(50,'view_any_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(51,'create_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(52,'update_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(53,'restore_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(54,'restore_any_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(55,'replicate_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(56,'reorder_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(57,'delete_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(58,'delete_any_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(59,'force_delete_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(60,'force_delete_any_media::html','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(61,'view_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(62,'view_any_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(63,'create_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(64,'update_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(65,'restore_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(66,'restore_any_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(67,'replicate_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(68,'reorder_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(69,'delete_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(70,'delete_any_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(71,'force_delete_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(72,'force_delete_any_media::image','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(73,'view_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(74,'view_any_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(75,'create_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(76,'update_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(77,'restore_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(78,'restore_any_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(79,'replicate_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(80,'reorder_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(81,'delete_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(82,'delete_any_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(83,'force_delete_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(84,'force_delete_any_media::live::url','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(85,'view_media::qr::code','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(86,'view_any_media::qr::code','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(87,'create_media::qr::code','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(88,'update_media::qr::code','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(89,'restore_media::qr::code','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(90,'restore_any_media::qr::code','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(91,'replicate_media::qr::code','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(92,'reorder_media::qr::code','web','2023-11-06 15:33:08','2023-11-06 15:33:08'),
(93,'delete_media::qr::code','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(94,'delete_any_media::qr::code','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(95,'force_delete_media::qr::code','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(96,'force_delete_any_media::qr::code','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(97,'view_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(98,'view_any_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(99,'create_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(100,'update_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(101,'restore_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(102,'restore_any_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(103,'replicate_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(104,'reorder_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(105,'delete_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(106,'delete_any_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(107,'force_delete_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(108,'force_delete_any_media::slider','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(109,'view_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(110,'view_any_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(111,'create_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(112,'update_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(113,'restore_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(114,'restore_any_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(115,'replicate_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(116,'reorder_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(117,'delete_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(118,'delete_any_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(119,'force_delete_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(120,'force_delete_any_media::slider::content','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(121,'view_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(122,'view_any_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(123,'create_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(124,'update_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(125,'restore_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(126,'restore_any_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(127,'replicate_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(128,'reorder_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(129,'delete_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(130,'delete_any_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(131,'force_delete_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(132,'force_delete_any_media::video','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(133,'view_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(134,'view_any_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(135,'create_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(136,'update_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(137,'restore_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(138,'restore_any_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(139,'replicate_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(140,'reorder_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(141,'delete_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(142,'delete_any_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(143,'force_delete_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(144,'force_delete_any_playlist','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(145,'view_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(146,'view_any_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(147,'create_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(148,'update_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(149,'restore_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(150,'restore_any_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(151,'replicate_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(152,'reorder_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(153,'delete_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(154,'delete_any_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(155,'force_delete_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(156,'force_delete_any_running::text','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(157,'view_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(158,'view_any_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(159,'create_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(160,'update_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(161,'restore_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(162,'restore_any_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(163,'replicate_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(164,'reorder_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(165,'delete_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(166,'delete_any_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(167,'force_delete_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(168,'force_delete_any_schedule','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(169,'view_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(170,'view_any_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(171,'create_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(172,'update_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(173,'restore_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(174,'restore_any_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(175,'replicate_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(176,'reorder_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(177,'delete_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(178,'delete_any_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(179,'force_delete_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09'),
(180,'force_delete_any_user','web','2023-11-06 15:33:09','2023-11-06 15:33:09');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playlist_layouts`
--

DROP TABLE IF EXISTS `playlist_layouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `playlist_layouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `playlist_id` bigint(20) unsigned NOT NULL,
  `layout_id` bigint(20) unsigned NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playlist_layouts`
--

LOCK TABLES `playlist_layouts` WRITE;
/*!40000 ALTER TABLE `playlist_layouts` DISABLE KEYS */;
INSERT INTO `playlist_layouts` VALUES
(2,3,4,'01:01:01','23:57:59','2023-11-17 20:23:16','2023-11-20 18:28:16',NULL),
(4,5,8,'00:59:59','23:59:59','2023-11-21 05:38:16','2023-11-21 05:40:45',NULL),
(5,6,4,'00:00:01','07:00:59','2023-11-21 05:49:58','2023-12-04 21:14:55',NULL),
(6,6,25,'07:01:01','08:00:59','2023-11-21 05:49:58','2024-03-14 06:20:56',NULL),
(7,7,14,'00:00:01','23:59:59','2023-11-21 14:57:54','2023-11-21 15:02:07',NULL),
(8,8,24,'00:00:01','23:59:59','2023-12-01 08:52:12','2024-02-01 07:35:48',NULL),
(9,6,30,'08:01:01','09:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(10,6,20,'09:01:01','10:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(11,6,14,'10:01:01','11:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(12,6,6,'11:01:01','12:01:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(13,6,7,'12:02:01','13:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(14,6,25,'13:01:01','14:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(15,6,30,'14:01:01','15:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(16,6,20,'15:01:01','16:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(17,6,5,'16:01:01','17:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(18,6,6,'17:01:01','18:00:59','2023-12-04 19:32:11','2024-03-14 06:20:56',NULL),
(19,9,6,'00:00:01','07:00:59','2023-12-04 19:53:49','2024-05-16 11:35:15',NULL),
(20,9,45,'07:01:01','08:00:59','2023-12-04 19:53:49','2025-03-03 17:52:18',NULL),
(21,9,7,'08:01:01','09:00:59','2023-12-04 19:53:49','2024-05-16 11:35:15',NULL),
(22,9,25,'09:01:01','10:00:59','2023-12-04 19:53:49','2025-02-16 23:30:22',NULL),
(23,9,5,'10:01:01','11:00:59','2023-12-04 19:53:49','2024-05-16 11:35:15',NULL),
(24,9,45,'11:01:01','12:00:59','2023-12-04 19:53:49','2025-02-26 21:38:40',NULL),
(25,9,20,'12:01:01','13:00:59','2023-12-04 19:53:49','2024-05-16 11:32:14',NULL),
(26,9,45,'13:01:01','14:00:59','2023-12-04 19:53:49','2025-02-26 21:38:40',NULL),
(27,9,31,'14:01:01','15:00:59','2023-12-04 19:53:49','2024-05-16 11:35:15',NULL),
(28,9,45,'15:01:01','16:00:59','2023-12-04 19:53:49','2025-03-03 17:52:18',NULL),
(29,9,33,'16:01:01','17:00:59','2023-12-04 19:53:49','2024-05-17 11:02:16',NULL),
(30,9,45,'17:01:01','18:00:59','2023-12-04 19:53:49','2025-02-13 21:14:31',NULL),
(31,9,34,'18:01:01','19:00:59','2023-12-04 19:53:49','2024-05-17 11:02:16',NULL),
(32,9,30,'19:01:01','20:00:59','2023-12-04 19:53:49','2025-02-26 21:38:40',NULL),
(33,9,35,'20:01:01','21:00:59','2023-12-04 19:53:49','2024-05-17 11:02:16',NULL),
(34,9,45,'21:01:01','22:00:59','2023-12-04 19:53:49','2025-02-26 21:38:40',NULL),
(35,9,39,'22:01:01','23:00:59','2023-12-04 19:53:49','2024-09-20 07:49:16',NULL),
(36,9,40,'23:01:01','23:59:59','2023-12-04 19:53:49','2024-09-20 07:49:16',NULL),
(46,11,29,'00:00:01','23:59:59','2024-02-01 12:14:13','2024-09-30 18:38:50',NULL),
(47,6,7,'18:01:01','19:00:59','2024-02-01 13:00:24','2024-03-14 06:20:56',NULL),
(48,6,25,'19:01:01','20:00:59','2024-02-01 13:00:24','2024-03-14 06:20:56',NULL),
(49,6,30,'20:01:01','21:00:59','2024-02-01 13:00:24','2024-03-14 06:20:56',NULL),
(56,6,20,'21:01:01','22:00:59','2024-02-01 13:00:24','2024-03-14 06:20:56',NULL),
(63,6,5,'22:01:01','23:59:59','2024-02-01 13:00:24','2024-03-14 06:20:56',NULL),
(64,12,28,'00:00:01','23:59:59','2024-02-06 14:30:51','2024-02-06 15:15:55',NULL),
(65,13,29,'00:00:01','23:59:59','2024-02-26 08:56:11','2024-02-26 08:56:11',NULL),
(67,15,36,'00:00:01','08:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(68,15,37,'08:01:01','09:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(69,15,36,'09:01:01','10:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(70,15,37,'10:01:01','11:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(71,15,36,'11:01:01','12:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(72,15,37,'12:01:01','13:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(73,15,36,'13:01:01','14:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(74,15,37,'14:01:01','15:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(75,15,36,'15:01:01','16:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(76,15,37,'16:01:01','17:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(77,15,36,'17:01:01','18:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(78,15,37,'18:01:01','19:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(79,15,36,'19:01:01','20:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(80,15,37,'20:01:01','21:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(81,15,36,'21:01:01','22:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(82,15,37,'22:01:01','23:00:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(83,15,36,'23:01:01','23:59:59','2024-09-03 07:53:03','2024-09-03 07:53:03',NULL),
(84,16,38,'00:00:01','23:59:59','2024-09-11 08:46:57','2024-09-11 08:46:57',NULL),
(85,17,41,'00:00:01','23:59:59','2024-10-01 04:35:49','2024-10-01 04:35:49',NULL),
(86,18,45,'00:00:01','23:59:59','2024-11-21 09:16:16','2024-11-21 09:16:16',NULL),
(87,19,51,'00:00:01','23:59:59','2024-12-16 11:27:47','2024-12-16 11:27:47',NULL),
(88,20,53,'00:00:01','23:59:59','2024-12-16 11:35:39','2024-12-16 11:35:39',NULL),
(89,22,4,'01:00:01','21:21:59','2024-12-17 09:43:44','2024-12-17 09:43:44',NULL),
(90,23,34,'00:00:01','23:59:59','2025-01-14 09:53:47','2025-01-14 09:53:47',NULL),
(91,24,69,'00:00:01','23:59:59','2025-02-03 19:58:39','2025-03-23 20:40:43',NULL),
(92,25,59,'00:00:01','11:30:59','2025-03-02 20:56:37','2025-03-03 18:16:24',NULL),
(93,25,60,'11:31:01','14:50:59','2025-03-02 20:56:37','2025-03-03 18:16:24',NULL),
(94,25,59,'14:51:01','16:00:59','2025-03-03 18:16:24','2025-03-03 18:16:24',NULL),
(95,25,60,'16:01:01','17:40:59','2025-03-03 18:16:24','2025-03-03 18:16:24',NULL),
(96,25,59,'17:41:01','18:30:59','2025-03-03 18:16:24','2025-03-03 18:16:24',NULL),
(97,25,60,'18:31:01','18:50:59','2025-03-03 18:16:24','2025-03-03 18:16:24',NULL),
(98,25,59,'18:51:01','19:50:59','2025-03-03 18:16:24','2025-03-03 18:16:24',NULL),
(99,25,60,'19:51:01','23:59:59','2025-03-03 18:16:24','2025-03-03 18:16:24',NULL),
(100,26,64,'00:00:01','23:59:59','2025-03-05 00:47:33','2025-03-05 00:47:33',NULL),
(101,27,65,'00:00:01','23:59:59','2025-03-05 17:50:56','2025-03-05 17:50:56',NULL),
(102,28,71,'00:00:01','23:59:59','2025-06-24 19:50:03','2025-06-24 19:50:03',NULL);
/*!40000 ALTER TABLE `playlist_layouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playlists`
--

DROP TABLE IF EXISTS `playlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `playlists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `is_all_day` tinyint(1) NOT NULL DEFAULT 0,
  `layout_interval` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_playlists_deleted_at` (`deleted_at`),
  KEY `idx_playlists_created_at` (`created_at`),
  KEY `idx_playlists_name` (`name`),
  KEY `idx_playlists_deleted_created` (`deleted_at`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playlists`
--

LOCK TABLES `playlists` WRITE;
/*!40000 ALTER TABLE `playlists` DISABLE KEYS */;
INSERT INTO `playlists` VALUES
(1,'PLAYLIST DPR LY 7','PLAYLIST DPR LY 7',1,1,'2023-11-06 19:51:06','2024-01-10 08:28:39','2024-01-10 08:28:39'),
(2,'panduan cuti','display',1,1,'2023-11-08 11:00:05','2023-11-21 14:56:55','2023-11-21 14:56:55'),
(3,'KIOSK BARU 1440','KIOSK BARU\nSETJEN',1,1,'2023-11-17 20:22:27','2024-01-10 08:28:24','2024-01-10 08:28:24'),
(4,'MIKTA','MIKTA',1,1,'2023-11-20 18:37:40','2023-11-28 14:22:04','2023-11-28 14:22:04'),
(5,'PLAYLIST DPR LIVE TVR','PLAYLIST DPR LIVE TVR',0,1,'2023-11-21 05:31:27','2023-11-21 05:40:48',NULL),
(6,'PLAYLIST DPR 1','PLAYLIST DPR 1',1,1,'2023-11-21 05:47:59','2025-03-20 01:13:03',NULL),
(7,'aplikasi cuti','TEST',1,1,'2023-11-21 14:37:57','2023-11-21 20:53:16',NULL),
(8,'PLAYLIST DPR LY SLIDER','PLAYLIST DPR LY SLIDER',1,1,'2023-12-01 08:51:26','2024-02-01 12:12:31',NULL),
(9,'PLAYLIST DPR 2','PLAYLIST DPR 2',1,1,'2023-12-04 19:33:51','2025-03-03 17:52:18',NULL),
(10,'Video Yankes Landscape spot 1','yankes landscape',1,1,'2023-12-06 06:02:48','2023-12-06 06:31:09',NULL),
(11,'PLAYLIST DPR LY POTRAIT','PLAYLIST DPR LY POTRAIT',1,1,'2024-02-01 12:12:55','2024-09-30 18:38:52',NULL),
(12,'dpr rewind','DPR REWIND',1,1,'2024-02-06 14:30:13','2025-01-14 09:52:14','2025-01-14 09:52:14'),
(13,'PLAYLIST DPR LY FULL SCREEN 1','PLAYLIST DPR LY FULL SCREEN 1',1,1,'2024-02-26 08:55:23','2024-10-01 04:33:12',NULL),
(14,'PLAYLIST YANKES FULL','PLAYLIST YANKES FULL',1,1,'2024-09-03 07:21:10','2024-09-03 10:22:14',NULL),
(15,'PLAYLIST 2 YANKES','PLAYLIST 2 YANKES',1,1,'2024-09-03 07:45:25','2024-09-03 07:53:07',NULL),
(16,'PLAYLIST PARJA 2024','PLAYLIST PARJA 2024',1,1,'2024-09-11 08:46:21','2024-09-11 08:46:59',NULL),
(17,'PLAYLIST DPR LY REQUEST','PLAYLIST DPR LY REQUEST',1,1,'2024-10-01 04:32:24','2024-10-01 04:35:52',NULL),
(18,'PLAYLIST DPR LY ISU UTAMA','PLAYLIST DPR LY ISU UTAMA',1,1,'2024-11-21 09:14:58','2024-11-25 10:21:02',NULL),
(19,'PLAYLIST UJI COBA','PLAYLIST UJI COBA',1,1,'2024-12-16 11:26:59','2024-12-16 11:27:47',NULL),
(20,'PLAYLIST EMEDIA UJI COBA','PLAYLIST EMEDIA UJI COBA',1,1,'2024-12-16 11:35:08','2024-12-16 11:35:43',NULL),
(21,'test1','yaudah1',0,1,'2024-12-17 09:41:04','2024-12-17 09:41:37','2024-12-17 09:41:37'),
(22,'test1 edit','test edit',1,1,'2024-12-17 09:41:57','2024-12-17 09:44:04','2024-12-17 09:44:04'),
(23,'PLAYLIST DPR LY 7','PLAYLIST DPR LY 7',1,1,'2025-01-14 09:52:41','2025-01-14 09:53:49',NULL),
(24,'test','test',1,1,'2025-02-03 19:56:45','2025-04-16 02:10:48','2025-04-16 02:10:48'),
(25,'PLAYLIST MESJID','PLAYLIST MESJID',1,1,'2025-03-02 20:53:29','2025-03-03 18:16:24',NULL),
(26,'PLAYLISTISU UTAMA TESTING','PLAYLISTISU UTAMA TESTING',1,1,'2025-03-05 00:41:50','2025-03-05 00:47:33',NULL),
(27,'PLAYLIST KEGIATAN RAMADHAN','PLAYLIST KEGIATAN RAMADHAN',1,1,'2025-03-05 17:50:34','2025-03-05 17:50:56',NULL),
(28,'PLAYLIST RAK BUKU','PLAYLIST RAK BUKU',1,1,'2025-06-24 19:49:15','2025-06-24 19:50:03',NULL),
(29,'plalist test','plalist test',1,1,'2025-10-06 20:16:20','2025-10-06 20:16:20',NULL);
/*!40000 ALTER TABLE `playlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remotes`
--

DROP TABLE IF EXISTS `remotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `remotes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL COMMENT 'Last time device responded successfully',
  `last_checked_at` timestamp NULL DEFAULT NULL COMMENT 'Last time system checked device status',
  `url` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_name` (`name`),
  KEY `idx_deleted_created` (`deleted_at`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remotes`
--

LOCK TABLES `remotes` WRITE;
/*!40000 ALTER TABLE `remotes` DISABLE KEYS */;
INSERT INTO `remotes` VALUES
(5,'DEPAN ESKALATOR','Disconnected',NULL,'2026-01-27 12:18:27','http://100.114.35.108:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.114.35.108&port=5900','2024-12-06 09:33:13','2025-05-21 00:19:38',NULL),
(19,'DEPAN ATM BCA','Disconnected',NULL,'2026-01-27 12:18:32','http://100.102.146.102:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.102.146.102&port=5900','2024-12-18 08:49:31','2025-06-30 00:02:09',NULL),
(22,'yaudah','Disconnected',NULL,'2026-01-24 01:20:30','http://10.20.47.155:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.155&port=5900','2024-12-18 14:01:43','2024-12-18 14:17:06','2024-12-18 14:17:06'),
(23,'STASIUN KAI BANDARA','Disconnected',NULL,'2026-01-27 12:18:32','http://100.114.86.67:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.114.86.67&port=5900','2024-12-20 14:39:43','2025-11-12 07:22:57',NULL),
(24,'GAMBIR KIOSK LAMA','Disconnected',NULL,'2026-01-27 12:18:32','http://100.127.107.8:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.127.107.8&port=5900','2024-12-20 14:40:20','2025-09-16 20:07:43',NULL),
(25,'Wisma Kopo 1','Disconnected',NULL,'2026-01-27 12:18:32','http://10.27.2.91:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.27.2.91&port=5900','2025-01-09 14:31:56','2025-01-09 14:31:56',NULL),
(26,'test','Disconnected',NULL,'2026-01-24 01:20:30','http://17.1.17.46:5800/vnc.html?autoconnect=true&show_dot=true&&host=17.1.17.46&port=5900','2025-01-13 08:46:13','2025-02-24 23:31:17','2025-02-24 23:31:17'),
(27,'Depan Abdul Muis','Disconnected',NULL,'2026-01-24 01:20:30','http://10.12.11.77:5800/vnc.html?autoconnect=true&show_dot=true&=&host=10.12.11.77&port=5900','2025-01-21 08:18:09','2025-02-24 01:05:04','2025-02-24 01:05:04'),
(28,'RUANG RAPAT PENERBITAN ','Disconnected',NULL,'2026-01-24 01:20:30','http://192.168.43.208:5800/vnc.html?autoconnect=true&show_dot=true&=&host=192.168.43.208&port=5900','2025-02-03 11:27:28','2025-02-03 12:08:39','2025-02-03 12:08:39'),
(29,'STASIUN SUDIRMAN BARU','Disconnected',NULL,'2026-01-27 12:18:32','http://100.117.173.104:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.117.173.104&port=5900','2025-02-03 12:09:46','2025-11-12 06:03:58',NULL),
(30,'TERMINAL II SOETTA ','Disconnected',NULL,'2026-01-27 12:18:32','http://100.66.215.11:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.66.215.11&port=5900','2025-02-10 20:29:45','2025-02-10 20:29:45',NULL),
(31,'Depan Abdul Muis ','Disconnected',NULL,'2026-01-24 01:20:30','http://100.116.128.24:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.116.128.24&port=5900','2025-02-11 20:51:51','2025-02-24 23:25:59','2025-02-24 23:25:59'),
(32,'DEPAN BIRO','Disconnected',NULL,'2026-01-24 01:20:30','http://10.20.47.12:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.12&port=5900','2025-02-23 23:51:26','2025-02-24 23:31:41','2025-02-24 23:31:41'),
(33,'DEPAN BANK MANDIRI ','Disconnected',NULL,'2026-01-27 12:18:32','http://100.110.67.27:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.110.67.27&port=5900','2025-02-24 00:30:39','2025-05-12 19:42:51',NULL),
(34,'DEPAN PARIPURNA','Disconnected',NULL,'2026-01-27 12:18:32','http://10.20.47.13:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.13&port=5900','2025-02-24 21:28:25','2025-02-24 21:28:25',NULL),
(35,'DEPAN BIRO','Disconnected',NULL,'2026-01-27 12:18:32','http://10.20.47.12:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.12&port=5900','2025-02-24 22:07:05','2025-02-24 22:07:05',NULL),
(36,'DEPAN YANKES','Disconnected',NULL,'2026-01-27 12:18:30','http://100.72.119.27:5800/vnc.html?autoconnect=true&show_dot=true&&host=http://100.72.119.27:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.24&port=5900&port=5900','2025-02-24 22:19:34','2025-05-21 21:05:02',NULL),
(37,'SETJEN LT.1','Disconnected',NULL,'2026-01-27 12:18:32','http://10.20.47.23:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.23&port=5900','2025-02-24 22:35:57','2025-02-24 22:35:57',NULL),
(38,'SETJEN LT.3','Disconnected','2026-01-24 01:20:30','2026-01-27 12:18:32','http://10.20.47.22:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.22&port=5900','2025-02-24 22:48:03','2025-03-04 17:58:53',NULL),
(39,'NUS 3, LT 4','Disconnected',NULL,'2026-01-27 12:18:32','http://10.20.47.21:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.21&port=5900','2025-02-24 22:58:56','2025-02-24 22:58:56',NULL),
(40,'NUS 3 LT.1','Disconnected',NULL,'2026-01-27 12:18:32','http://10.20.47.20:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.20.47.20&port=5900','2025-02-24 23:10:54','2025-02-24 23:10:54',NULL),
(41,'DEPAN ABDUL MUIS','Disconnected',NULL,'2026-01-27 12:18:32','http://100.103.151.27:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.103.151.27&port=5900','2025-02-24 23:22:10','2025-04-22 00:51:04',NULL),
(42,'PERPUSTAKAAN','Disconnected',NULL,'2026-01-27 12:18:32','http://100.100.194.10:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.100.194.10&port=5900','2025-02-24 23:58:40','2025-06-25 18:53:32',NULL),
(43,'TERMINAL 3 SOETTA','Disconnected',NULL,'2026-01-27 12:18:28','http://100.113.131.82:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.113.131.82&port=5900','2025-03-18 21:21:43','2025-03-18 23:32:54',NULL),
(44,'test','Disconnected',NULL,'2026-01-24 01:20:30','http://10.0.2.16:5800/vnc.html?autoconnect=true&show_dot=true&&host=10.0.2.16&port=5900','2025-04-09 03:48:44','2025-04-09 03:49:18','2025-04-09 03:49:18'),
(45,'RUANG RAPAT PENERBITAN','Disconnected',NULL,'2026-01-27 12:18:32','http://100.112.155.26:5800/vnc.html?autoconnect=true&show_dot=true&&host=100.112.155.26&port=5900','2025-06-25 18:54:45','2025-06-25 18:54:45',NULL),
(46,'Depan Komisi X','Disconnected',NULL,'2026-01-27 12:18:32','http://100.67.2.35:5800/vnc.html?autoconnect=true&show_dot=true&=&host=100.67.2.35&port=5900','2025-08-14 17:15:42','2025-08-14 17:15:42',NULL);
/*!40000 ALTER TABLE `remotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES
(1,1),
(2,1),
(3,1),
(4,1),
(5,1),
(6,1),
(7,1),
(8,1),
(9,1),
(10,1),
(11,1),
(12,1),
(13,1),
(14,1),
(15,1),
(16,1),
(17,1),
(18,1),
(19,1),
(20,1),
(21,1),
(22,1),
(23,1),
(24,1),
(25,1),
(26,1),
(27,1),
(28,1),
(29,1),
(30,1),
(31,1),
(32,1),
(33,1),
(34,1),
(35,1),
(36,1),
(37,1),
(38,1),
(39,1),
(40,1),
(41,1),
(42,1),
(43,1),
(44,1),
(45,1),
(46,1),
(47,1),
(48,1),
(49,1),
(50,1),
(51,1),
(52,1),
(53,1),
(54,1),
(55,1),
(56,1),
(57,1),
(58,1),
(59,1),
(60,1),
(61,1),
(62,1),
(63,1),
(64,1),
(65,1),
(66,1),
(67,1),
(68,1),
(69,1),
(70,1),
(71,1),
(72,1),
(73,1),
(74,1),
(75,1),
(76,1),
(77,1),
(78,1),
(79,1),
(80,1),
(81,1),
(82,1),
(83,1),
(84,1),
(85,1),
(86,1),
(87,1),
(88,1),
(89,1),
(90,1),
(91,1),
(92,1),
(93,1),
(94,1),
(95,1),
(96,1),
(97,1),
(98,1),
(99,1),
(100,1),
(101,1),
(102,1),
(103,1),
(104,1),
(105,1),
(106,1),
(107,1),
(108,1),
(109,1),
(110,1),
(111,1),
(112,1),
(113,1),
(114,1),
(115,1),
(116,1),
(117,1),
(118,1),
(119,1),
(120,1),
(121,1),
(122,1),
(123,1),
(124,1),
(125,1),
(126,1),
(127,1),
(128,1),
(129,1),
(130,1),
(131,1),
(132,1),
(133,1),
(134,1),
(135,1),
(136,1),
(137,1),
(138,1),
(139,1),
(140,1),
(141,1),
(142,1),
(143,1),
(144,1),
(145,1),
(146,1),
(147,1),
(148,1),
(149,1),
(150,1),
(151,1),
(152,1),
(153,1),
(154,1),
(155,1),
(156,1),
(157,1),
(158,1),
(159,1),
(160,1),
(161,1),
(162,1),
(163,1),
(164,1),
(165,1),
(166,1),
(167,1),
(168,1),
(169,1),
(170,1),
(171,1),
(172,1),
(173,1),
(174,1),
(175,1),
(176,1),
(177,1),
(178,1),
(179,1),
(180,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'super_admin','web','2023-11-06 15:32:58','2023-11-06 15:32:58');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `running_texts`
--

DROP TABLE IF EXISTS `running_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `running_texts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `direction` varchar(191) NOT NULL,
  `speed` int(11) NOT NULL,
  `background_color` varchar(191) NOT NULL,
  `text_color` varchar(191) NOT NULL,
  `url` varchar(191) DEFAULT NULL,
  `preview` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `running_texts`
--

LOCK TABLES `running_texts` WRITE;
/*!40000 ALTER TABLE `running_texts` DISABLE KEYS */;
INSERT INTO `running_texts` VALUES
(1,'RUNNING TEXT','SELAMAT DATANG DI BIRO PEMBERITAAN PARLEMEN || ANDA MEMASUKI KAWASAN ZONA INTEGRITAS  MENUJU WILAYAH BEBAS DARI KORUPSI (WBK) DAN WILAYAH BIROKRASI BERSIH DAN MELAYANI (WBBM). ANTISIPASI TRAUMA BENCANA, ANGGOTA KOMISI VIII, SELLY ANDRIANY MINTA PEMERINTAH BERI PENDAMPINGAN PSIKOLOGIS KEPADA ANAK-ANAK KORBAN BENCANA\nWAKIL KETUA KOMISI X, MY ESTI MINTA PERLAKUAN KHUSUS TERHADAP MAHASISWA TERDAMPAK BENCANA SUMATERA YANG SEDANG KULIAH DI UNIVERSITAS UDAYANA BALI\nANGGOTA KOMISI XI, DIDIK HARYADI MENILAI TUNGGAKAN DANA TKD HAMBAT PEMBANGUNAN KALTIM\nKOMISI VIII SALURKAN BANTUAN 3,9 TON BAHAN SEMBAKO UNTUK MASYARAKAT TERDAMPAK BANJIR DI PIDIE JAYA\nWAKIL KETUA KOMISI XI, DOLFIE DORONG PEMERINTAH PERCEPAT PENCAIRAN DANA YANG MENJADI HAK DAERAH\nKOMISI VIII SALURKAN BANTUAN REHABILITASI MADRASAH NEGERI PASCA BANJIR BANDANG DI PIDIE JAYA SENILAI 1,9 MILIAR RUPIAH\nANGGOTA KOMISI XI, TOMMY KURNIAWAN DORONG PEMERINTAH PERBAIKI DANA BAGI HASIL UNTUK KALTIM\nANGGOTA KOMISI VIII, HASAN BASRI DORONG SINERGI KEMENTERIAN LEMBAGA ATASI PENANGANAN BENCANA DI ACEH\nANGGOTA KOMISI XI, MARWAN CIK ASAN : TATA KELOLA SUMBER DAYA ALAM KALTIM PERLU DIBENAHI\nKETERSEDIAAN OBAT DI PIDIE JAYA MENIPIS, ANGGOTA KOMISI VIII, M. HUSNI MINTA KEMENKES SEGERA KERAHKAN BANTUAN TAMBAHAN \nANGGOTA KOMISI XI, DIDIK HARYADI DORONG BAPPENAS SUSUN LANGKAH KONKRET TRANSFORMASI ENERGI DI KALTIM\nRASIO DOKTER DI ACEH MINIM, WAKIL KETUA KOMISI VIII, ANSORY DUKUNG PEMBENTUKAN FAKULTAS KEDOKTERAN DI UIN AR-RANIRY\nKOMISI XI APRESIASI PERTUMBUHAN EKONOMI KEPRI YANG MELAMPAUI RATA-RATA PERTUMBUHAN EKONOMI NASIONAL\nANGGOTA KOMISI VIII, ALIMUDIN KOLATLENA : UIN AMSA DIHARAPKAN MENDAPAT PERHATIAN KHUSUS DARI PEMERINTAH PUSATa\nWAKIL KETUA KOMISI XI, HANIF DHAKIRI TEKANKAN EKOSISTEM PENDANAAN YANG LEBIH INKLUSIF GUNA MEMEASTIKAN KEMUDAHAN AKSES KREDIT KE UMKM\nKOMISI VIII TEGASKAN PERHATIAN BERKELANJUTAN TERHADAP SELURUH PERGURUAN TINGGI KEAGAMAAN DI INDONESIA\nANGGOTA KOMISI XI, ANIS BYARWATI DORONG PENGUATAN EKONOMI ANAMBAS DAN NATUNA UNTUK KURANGI KETERGANTUNGAN PADA BATAM\nTINJAU KEBUN CABAI DAN DIALOG DENGAN PETANI DI NGAWI, ANGGOTA KOMISI XII, EDHIE BASKORO : JAGA HARGA PANGAN, LINDUNGI PETANI DAN DAYA BELI RAKYAT\nANGGOTA KOMISI XI, MUHIDIN TEGASKAN EFISIENSI ANGGARAN DITUJUKAN UNTUK BELANJA NON PRIORITAS \nANGGOTA KOMISI X, JULIYATMONO : SEKOLAH SWASTA JUGA MEMILIKI KONTRIBUSI BESAR DALAM DUNIA PENDIDIKAN DI INDONESIA\nANGGOTA KOMISI , MUSTHOFA MINTA KEMENKEU EVALUASI SEJUMLAH REGULASI TERKAIT TKD\nWAKIL KETUA KOMISI X, HIMMATUL ALIYAH DUKUNG KEBIJAKAN REDISTRIBUSI GURU ASN DI SEKOLAH SWASTA SEBAGAI UPAYA PEMERATAAN TENAGA PENDIDIK\nANGGOTA KOMISI XII, DEWI YUSTISIANA : PERTAMINA HARUS SIAPKAN STOK BBM EKSTRA UNTUK PEMULIHAN BENCANA SUMATERA\nANGGOTA KOMISI X, I NYOMAN PARTA MENILAI SMP 3 BEBANDEM KARANGASEM SUDAH BAGUS \nWAKIL KETUA KOMISI XII, BAMBANG HARIYADI MINTA PLN BALI PASTIKAN PASOKAN LISTRIK STABIL JELANG NATARU\nANGGOTA KOMISI II, DEDDY SITORUS TEGASKAN KAKANWIL BPN SUMUT HARUS BUAT TIMELINE DAN TANDA PENCAPAIAN UNTUK MENYELESAIKAN REDISTRIBUSI LAHAN EKS HGU PTPN II\nANGGOTA KOMISI XII, ROKHMAT ARDIYAN MINTA PERTAMINA LAKUKAN PEREMAJAAN TERMINAL BBM DAN LPG UNTUK DUKUNG SWASEMBADA ENERGI\nANGGOTA KOMISI II, BOB SITEPU KRITIK LAMBATNYA KANWIL BPN SUMUT MENINDAKLANJUTI PENGURUSAN SERTIFIKAT RUMAH IBADAH YANG BERADA DI LAHAN EKS HGU PTPN II\nANGGOTA KOMISI XII, AQIB ARDIANSYAH DORONG REVISI UU MIGAS UNTUK MENINGKATKAN LIFTING MIGAS DAN INVESTASI\nWAKIL KETUA KOMISI VIII, SINGGIH JANURATMOKO : ANGKA KEMISKINAN DAERAH DI AMBON MASIH KISARAN 15,5 PERSEN\nANGGOTA KOMISI XII, RAMSON SIAGIAN TEGUR PLN ATAS KESALAHAN DATA PEMULIHAN LISTRIK PASCA BANJIR DAN LONGSOR DI SUMATERA\nWAKIL KETUA KOMISI IV, AHMAD YOHAN APRESIASI KEBUN RAYA MANGROVE MENJAGA KELESTARIAN LINGKUNGAN HIDUP SERTA DORONG PERTUMBUHAN EKONOMI LOKAL\nANGGOTA KOMISI XII, SARTONO PANTAU KESIAPAN PERTAMINA MEMASTIKAN PASOKAN MIGAS AMAN SELAMA PERIODE NATARU\nKOMISI VIII DORONG KEMENSOS DAN PEMDA MEMPERTAHANKAN AKURASI DATA DTSEN SEBAGAI BASIS PENYALURAN BANTUAN\nANGGOTA KOMISI XII, IRSAN SOSIAWAN APRESIASI PERTAMINA DAN PLN DALAM PENANGANAN BANJIR DAN LONGSOR DI PROVINSI ACEH\nWAKIL KETUA KOMISI IV, AHMAD YOHAN BERKOMITMEN MENJAGA KELESTARIAN LINGKUNGAN HIDUP DAN MENGURANGI RISIKO BENCANA\nWAKIL KETUA KOMISI VIII, ANSORY SIREGAR DESAK PEMERINTAH TETAPKAN BENCANA NASIONAL DI SUMATERA\nANGGOTA KOMISI XII, ATENG SUTISNA MINTA PERTAMINA BUAT SISTEM PENGISIAN BBM CEPAT DI TOL TRANS JAWA UNTUK MASYARAKAT YANG TIDAK INGIN MASUK REST AREA\nANGGOTA KOMISI IV, SONNY DANAPARAMITA : PERSOALAN DI LAPANGAN TENTANG IMPOR GULA RAFINASI MASIH BANYAK SEMENTARA GULA PETANI LOKAL TIDAK TERSERAP\nANGGOTA KOMISI VIII, M. HUSNI SOROTI  MINIMNYA KETERSEDIAAN OBAT UNTUK PENGUNGSI DI KABUPATEN PIDIE JAYA\nANGGOTA KOMISI XII, ARIF RIYANTO APRESIASI PERTAMINA DAN PLN MEMBENTUK SATGAS PERSIAPAN NATAL DAN TAHUN BARU\nANGGOTA KOMISI IV, SUMAIL ABDULLAH SOROTI KEPEMILIKAN LAHAN DIBAWAH SETENGAH HEKTARE MENYULITKAN PENGEMBANGAN INDUSTRI GULA \nANGGOTA KOMISI XII, SARTONO TEKANKAN PENTINGNYA KOORDINASI LINTAS LEMBAGA AGAR DISTRIBUSI MIGAS DAN LPG TETAP BERJALAN LANCAR SELAMA NATARU\nANGGOTA KOMISI VIII, KETUT KARIYASA MINTA PENDAMPINGAN BAGI SISWA MADRASAH AGAR TIDAK TRAUMA BENCANA\nANGGOTA KOMISI IV, SULAIMAN HAMZAH DORONG PENDATAAN MENYELURUH OLEH PEMERINTAH TERKAIT PERGULAAN NASIONAL BELUM LENGKAP\nANGGOTA KOMISI VIII, SELLY ANDRIANY DORONG PEMBENTUKAN FAKULTAS KEDOKTERAN DI UIN AR RANIRY\nANGGOTA KOMISI IV, STURMAN PANJAITAN SOROTI KETERBATASAN ANGGARAN BKSDA JAWA TIMUR DALAM PENANGANAN PERDAGANGAN SATWA LIAR\nANGGOTA KOMISI V, MUSA RAJEKSHAH MINTA PENGELOLA BANDARA KUALANAMU OPTIMALKAN LAYANAN PENERBANGAN JELANG LIBUR NATARU 2026\nANGGOTA KOMISI X, LESTARI MOERDIJAT HARAP PEMERINTAH PUSAT MEMBERIKAN BANTUAN DAN PERBAIKAN YANG MEMENUHI KAIDAH MANAJEMEN SUMBER DAYA BUDAYA\nANGGOTA KOMISI VI, ISKANDAR OPTIMIS OPERATOR LAYANAN UDARA SIAP HADAPI NATARU\nANGGOTA KOMISI IV, I.N. ADI WIRYATAMA : JAGA SATWA ENDEMIK INDONESIA SEPERTI ORANG UTAN DAN KOMODO DARI KEPUNAHAN DAN PERDAGANGAN ILEGAL\nANGGOTA KOMISI V, MUSA RAJEKSHAH MINTA PENGELOLA BANDARA KUALANAMU MITIGASI RISIKO CUACA BURUK YANG BERDAMPAK PADA PENUMPUKAN PENUMPANG\nANGGOTA KOMISI VI, ZULFIKAR HAMONANGAN APRESIASI DISKON PPN DAN PENURUNAN HARGA AVTUR UNTUK MENEKAN HARGA TIKET PESAWAT JELANG NATARU\nANGGOTA KOMISI IV, DADANG M. NASER DORONG PETERNAKAN HEWAN LINDUNG DALAM SETIAP AGROFORESTRI\nANGGOTA KOMISI V, LOKOT NASUTION SOROTI PENYALAHGUNAAN TATA RUANG WILAYAH DI SUMUT YANG BERPOTENSI MENJADI PENYEBAB BENCANA\nANGGOTA KOMISI VI, NASIM KHAN TEKANKAN SINERGITAS MASKAPAI, BANDARA DAN KEMENHUB MENJAGA STABILITAS HARGA TIKET DAN KELANCARAN PENERBANGAN NATARU\nANGGOTA KOMISI IV, ROKHMIN DAHURI : SATWA LIAR MILIKI PLASMA ATAU GENETIK TINGGI UNTUK OBAT DAN KEBERLANJUTAN EKOSISTEM\nANGGOTA KOMISI V, LOKOT NASUTION MINTA KEMENTERIAN PU SEGERA MENYERAHKAN DATA KERUSAKAN TEMPAT TINGGAL KORBAN BANJIR DAN LONGSOR\nWAKIL KETUA KOMISI VI, NURDIN KHALID APRESIASI LANGKAH ANTISIPATIF OPERATOR PENERBANGAN\nWAKIL KETUA KOMISI XII, DONY MARYADI MENILAI PERLUNYA TEROBOSOAN SISTEM ANTRIAN BBM\nANGGOTA KOMISI VI, ZULFIKAR HAMONANGAN SOROTI PENTINGNYA CSR BAGI MASYARAKAT TERDAMPAK POLUSI DAN BISING PESAWAT\nWAKIL KETUA KOMISI XII, DONY MARYADI MINTA SPKLU DI REST AREA TOL TRANS JAWA DI TAMBAH\nWAKIL KETUA KOMISI VI, NURDIN HALID DORONG ANGKASA PURA WAJIBKAN GARBARATA BAGI SELURUH MASKAPAI\nWAKIL KETUA KOMISI XII, DONY MARYADI DORONG DITJEN GAKKUM DI KEMEN-ESDM LEBIH TEGAS DALAM MENINDAK MAFIA GAS BERSUBSIDI.','left',25,'#ff0000','#ffffff',NULL,NULL,'2023-11-06 19:41:21','2025-12-17 23:00:27',NULL),
(2,'April','PARLIAMENTARY MEETING ON THE OCCASION OF THE 10TH WORLD WATER FORUM || MOBILIZING PARLIAMENTARY ACTION ON WATER FOR SHARED PROSPERITY  ||  TANGGAL 19-22 MEI 2024 DI BALI, INDONESIA. ','right',5,'#ff0000','#000000',NULL,NULL,'2024-04-23 04:56:30','2024-04-23 04:56:30',NULL),
(3,'test1 edit','yaudah edit','right',10,'#ff00fc','#3d19f2',NULL,NULL,'2024-12-17 11:56:03','2024-12-17 11:57:09','2024-12-17 11:57:09'),
(4,'TEST RUNNING TEXT','TESST RUNNING TEXT','right',5,'#ff0000','#ffffff',NULL,NULL,'2025-03-17 19:48:19','2025-04-07 21:26:02','2025-04-07 21:26:02');
/*!40000 ALTER TABLE `running_texts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_playlists`
--

DROP TABLE IF EXISTS `schedule_playlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule_playlists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint(20) unsigned NOT NULL,
  `playlist_id` bigint(20) unsigned NOT NULL,
  `start_day` int(11) NOT NULL DEFAULT 0,
  `end_day` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_playlists`
--

LOCK TABLES `schedule_playlists` WRITE;
/*!40000 ALTER TABLE `schedule_playlists` DISABLE KEYS */;
INSERT INTO `schedule_playlists` VALUES
(1,1,3,0,6,'2023-11-06 19:54:51','2023-11-20 09:14:11',NULL),
(2,2,3,0,6,'2023-11-17 20:24:36','2023-11-17 20:26:03',NULL),
(3,3,6,0,6,'2023-11-20 18:38:56','2023-12-02 22:34:55',NULL),
(4,4,5,0,6,'2023-11-21 05:39:45','2023-11-21 05:39:45',NULL),
(5,5,6,0,6,'2023-11-21 05:50:38','2023-12-04 20:55:53',NULL),
(6,6,7,0,6,'2023-11-21 14:59:43','2023-11-21 15:02:23',NULL),
(7,7,8,0,6,'2023-12-01 08:52:49','2023-12-01 08:52:49',NULL),
(8,8,9,0,6,'2023-12-04 20:21:34','2023-12-04 20:21:34',NULL),
(9,9,10,0,6,'2023-12-06 06:33:15','2023-12-06 06:33:15',NULL),
(10,10,11,0,6,'2024-02-01 12:15:30','2024-02-01 12:15:30',NULL),
(11,11,12,0,6,'2024-02-06 14:32:25','2024-02-06 14:32:25',NULL),
(12,12,13,0,6,'2024-02-26 08:57:48','2024-02-26 08:57:48',NULL),
(13,13,14,0,6,'2024-09-03 07:23:09','2024-09-03 07:23:09',NULL),
(14,14,15,0,6,'2024-09-03 07:54:08','2024-09-03 07:54:08',NULL),
(15,15,16,0,6,'2024-09-11 08:48:00','2024-09-11 08:48:00',NULL),
(16,16,17,0,6,'2024-10-01 04:34:14','2024-10-01 04:34:14',NULL),
(17,17,18,0,6,'2024-11-21 09:17:21','2024-11-21 09:17:21',NULL),
(18,18,19,0,6,'2024-12-16 11:28:33','2024-12-16 11:28:40',NULL),
(19,19,20,0,6,'2024-12-16 11:36:18','2024-12-16 11:36:20',NULL),
(20,20,19,1,5,'2024-12-17 09:29:05','2024-12-17 09:37:08',NULL),
(21,21,23,0,6,'2025-01-14 09:54:29','2025-01-14 09:54:29',NULL),
(22,22,24,1,5,'2025-02-03 19:59:08','2025-02-03 19:59:08',NULL),
(23,23,25,0,6,'2025-03-02 21:11:54','2025-03-02 21:11:54',NULL),
(24,24,26,0,6,'2025-03-05 00:48:14','2025-03-05 00:48:14',NULL),
(25,25,27,0,6,'2025-03-05 17:51:40','2025-03-05 17:51:53',NULL),
(26,26,28,0,6,'2025-06-24 19:51:04','2025-06-24 19:51:04',NULL);
/*!40000 ALTER TABLE `schedule_playlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `is_whole_week` tinyint(1) NOT NULL DEFAULT 0,
  `running_text_is_include` tinyint(1) NOT NULL DEFAULT 0,
  `running_text_position` varchar(191) NOT NULL DEFAULT 'bottom',
  `running_text_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_schedules_deleted_at` (`deleted_at`),
  KEY `idx_schedules_created_at` (`created_at`),
  KEY `idx_schedules_name` (`name`),
  KEY `idx_schedules_deleted_created` (`deleted_at`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
INSERT INTO `schedules` VALUES
(1,'Schedule LY 7','Schedule LY 7',0,0,'bottom',NULL,'2023-11-06 19:54:25','2025-01-14 12:15:14','2025-01-14 12:15:14'),
(2,'SCHEDULE KIOSK BARU','BARU KIOSK',1,0,'bottom',NULL,'2023-11-17 20:24:22','2024-01-10 08:26:28','2024-01-10 08:26:28'),
(3,'MIKTA','MIKTA\n',1,0,'bottom',NULL,'2023-11-20 18:38:29','2023-12-04 06:03:37','2023-12-04 06:03:37'),
(4,'SCHEDULE DPR LIVE TVR','SCHEDULE DPR LIVE TVR',1,0,'bottom',NULL,'2023-11-21 05:34:56','2023-11-24 14:13:37',NULL),
(5,'SCHEDULE DPR 1','SCHEDULE DPR 1',1,0,'bottom',NULL,'2023-11-21 05:50:17','2024-02-06 07:27:25',NULL),
(6,'aplikasi cuti','TEST',1,0,'bottom',NULL,'2023-11-21 14:37:44','2023-11-21 20:53:32',NULL),
(7,'SCHEDULE DPR SLIDER','SCHEDULE DPR SLIDER',1,0,'bottom',NULL,'2023-12-01 08:52:29','2024-02-01 07:36:05',NULL),
(8,'SCHEDULE DPR 2','SCHEDULE DPR 2',1,0,'bottom',NULL,'2023-12-04 20:21:13','2024-11-25 11:44:56',NULL),
(9,'Video Yankes ','Yankes\n',1,0,'bottom',NULL,'2023-12-06 06:02:10','2023-12-06 06:33:15',NULL),
(10,'SCHEDULE DPR POTRAIT','SCHEDULE DPR POTRAIT',1,0,'bottom',NULL,'2024-02-01 12:14:47','2024-02-01 12:15:32',NULL),
(11,'DPR SCHEDULE YOUTUBE','DPR SCHEDULE YOUTUBE',1,0,'bottom',NULL,'2024-02-06 14:31:32','2024-02-06 15:16:31',NULL),
(12,'SCHEDULE DPR FULL SCREEN 1','SCHEDULE DPR FULL SCREEN 1',1,0,'bottom',NULL,'2024-02-26 08:57:20','2024-09-30 18:39:39',NULL),
(13,'SCHEDULE YANKES','SCHEDULE YANKES',1,0,'bottom',NULL,'2024-09-03 07:22:33','2024-09-03 07:23:11',NULL),
(14,'SCHEDULE YANKES 2','SCHEDULE YANKES 2',1,0,'bottom',NULL,'2024-09-03 07:53:41','2024-09-03 07:54:10',NULL),
(15,'SCHEDULE PARJA 2024','SCHEDULE PARJA 2024',1,0,'bottom',NULL,'2024-09-11 08:47:37','2024-09-11 08:48:02',NULL),
(16,'SCHEDULE DPR KONTEN REQUEST','SCHEDULE DPR KONTEN REQUEST',1,0,'bottom',NULL,'2024-10-01 04:33:48','2024-10-01 04:35:20',NULL),
(17,'SCHEDULE DPR ISU UTAMA','SCHEDULE DPR ISU UTAMA',1,0,'bottom',NULL,'2024-11-21 09:17:00','2024-11-25 06:56:19',NULL),
(18,'SCHEDULE UJI COBA','SCHEDULE UJI COBA',1,0,'bottom',NULL,'2024-12-16 11:28:07','2024-12-16 11:28:40',NULL),
(19,'SCHEDULE EMEDIA UJI COBA','SCHEDULE EMEDIA UJI ',1,0,'bottom',NULL,'2024-12-16 11:36:01','2024-12-17 15:28:15',NULL),
(20,'test new 1 edit','yaudah test 1',1,0,'bottom',NULL,'2024-12-17 09:26:35','2024-12-17 09:38:29','2024-12-17 09:38:29'),
(21,'Schedule LY 7','Schedule LY 7',1,0,'bottom',NULL,'2025-01-14 09:54:11','2025-01-14 09:54:29',NULL),
(22,'test','test',1,0,'bottom',NULL,'2025-02-03 19:58:56','2025-04-16 02:10:58','2025-04-16 02:10:58'),
(23,'SCHEDULE MESJID','SCHEDULE MESJID',1,0,'bottom',NULL,'2025-03-02 21:11:34','2025-03-02 21:11:54',NULL),
(24,'SCHEDULE ISU UTAMA TESTING','SCHEDULE ISU UTAMA TESTING',1,0,'bottom',NULL,'2025-03-05 00:47:59','2025-03-05 00:48:14',NULL),
(25,'SCHEDULE KEGIATAN RAMADHAN','SCHEDULE KEGIATAN RAMADHAN',1,0,'bottom',NULL,'2025-03-05 17:51:28','2025-03-05 17:51:53',NULL),
(26,'SCHEDULE RAK BUKU','SCHEDULE RAK BUKU',1,0,'bottom',NULL,'2025-06-24 19:50:27','2025-06-24 19:51:04',NULL);
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `screens`
--

DROP TABLE IF EXISTS `screens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `screens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `mode` varchar(191) NOT NULL,
  `aspect_ratio` varchar(191) NOT NULL,
  `column` int(11) NOT NULL DEFAULT 0,
  `row` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `screens`
--

LOCK TABLES `screens` WRITE;
/*!40000 ALTER TABLE `screens` DISABLE KEYS */;
INSERT INTO `screens` VALUES
(1,'1080p Portrait (9:16)',1080,1920,'portrait','9:16',18,32,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(2,'1080p (16:9)',1920,1080,'landscape','16:9',32,18,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(3,'1024x608 (16:9.5)',1024,608,'landscape','16:9.5',32,19,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(4,'900x600 (3:2)',900,600,'landscape','3:2',6,4,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL);
/*!40000 ALTER TABLE `screens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `last_activity` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('bFbENNrbb0xifDazNYNTH1Ch0ew6MXVDN10ietz3',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:136.0) Gecko/20100101 Firefox/136.0','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVnB2SzVyS3gwNFFLWGdwQXJxRW9KYkt6RW9HSjhmTDZxeGNmRlIwWSI7czozOiJ1cmwiO2E6MDp7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYmFjay1vZmZpY2UiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTAkLmsvRExNeERBTzZPWkptMHJTa3d3Lk9ic0Z3QTl4Li96Y1RYS01YWWVsZWxqeDg0SU5rTGEiO30=',1742588417),
('irdYAgUWniZtcO36wKPLhPir8EaydHeA9qZIpRNL',NULL,'103.28.21.126','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiekkxQkM3WWVJTHdOc2l0Q2JzS3B0ZnRrckdIYUxPR1JKYWZXUUdhMyI7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YTowOnt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTM0OiJodHRwczovL2tpb3NrLnBlbmVyYml0YW4tZHByLmlkL2Rpc3BsYXkvVGpEdFljQm51OXN4ZU9ETTgxNlZQYkhsTVAzQTZ5ZmNWWERBbzR6Z2FXeks3MmV3NGYzb2pRVDNvQjBCdmR0U01KQ1pEc1U3bERMQ1VyYVYyMDIzMTIxMTEyNTExNCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1742584401),
('j0VbWJl5OfAxwuEFWQsK82eieTPUIMMO8EJYOwdn',1,'114.10.42.230','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:136.0) Gecko/20100101 Firefox/136.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoib1RHQ3AzbGJ6UHlrcDVMOFJ6djM0T05EbjRCV1VidzhlMGNjTXhnYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDk6Imh0dHBzOi8va2lvc2sucGVuZXJiaXRhbi1kcHIuaWQvYmFjay1vZmZpY2UvbG9naW4iO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1742586500),
('Md4MMx3Hz0PQ4TMWl0di5D8yqt7jz3JhvvMbPmah',1,'114.10.42.230','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:136.0) Gecko/20100101 Firefox/136.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOXVzbnVKWDZreXpnYnVaMEJ4VkxRaVFDQjVlTWJ5Mm5Ed3lEdHFlSSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1742586636),
('OvAc1x0dBnz4eZeCm2VkMqCoe38pf0hTE7eiHgv1',NULL,'125.161.75.158','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYmU3ajh4WU9TWHBFREN6UG9kRUNtYldXOXdONjBGWWRPaWFTcnBxcSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTM0OiJodHRwczovL2tpb3NrLnBlbmVyYml0YW4tZHByLmlkL2Rpc3BsYXkvaEtQbVI1MnVmeHpHMjRHRG0zNG5raE9XRGp5U3VlMm4yeTZzNEtINXlNdzVUdEhPVTNTMzRUV2t2MG9YMWxpNzRMd2FvNFpMdGZJZVdoVXYyMDIzMTExMzA5MjIyMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1742586580),
('smbSq15NSuyMOngSA6sLn44iycRPX6mhRoyPIwok',NULL,'125.161.75.158','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVUtxNzEwZVhZaXpWUU9TRVZ6aXdmRTZoNXJINXk5NTJjZERja2p2eiI7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YTowOnt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTM0OiJodHRwczovL2tpb3NrLnBlbmVyYml0YW4tZHByLmlkL2Rpc3BsYXkvaEtQbVI1MnVmeHpHMjRHRG0zNG5raE9XRGp5U3VlMm4yeTZzNEtINXlNdzVUdEhPVTNTMzRUV2t2MG9YMWxpNzRMd2FvNFpMdGZJZVdoVXYyMDIzMTExMzA5MjIyMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1742584779),
('U9302dWxTuH0AFnuA4X8wpz6IVX5EhI7Nh306ogh',NULL,'103.18.181.114','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWUdqWE1TRk1IRVhXeTYxY0FSR05QV2ZIUmFjVDZYUk92V3ZvMmtlMiI7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YTowOnt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTM0OiJodHRwczovL2tpb3NrLnBlbmVyYml0YW4tZHByLmlkL2Rpc3BsYXkvZm1zZzl3NWpLSWg2a3ZWSDc1VGFySnA5bGNMTVNPWFE0eFhiSHlnRDluYm1oQzB2SFJYSFozdkhKczlRb3hpbjBIY1dXY1duODJzYjlsbzAyMDIzMTExMzA5MTQ0NCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1742584392),
('w7MOsIfcikqVyMt03BeTZAwtFmKo4e9xN6dMqlAK',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:136.0) Gecko/20100101 Firefox/136.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicUppb3NFWUlzTm5nejhZY0Nibks3SWNPWkxlNFV2MFVhMVE5Rk5aZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9iYWNrLW9mZmljZS9sb2dpbiI7fX0=',1742586434);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spots`
--

DROP TABLE IF EXISTS `spots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `spots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `layout_id` int(10) unsigned NOT NULL,
  `media_id` bigint(20) unsigned NOT NULL,
  `x` int(11) NOT NULL DEFAULT 0,
  `y` int(11) NOT NULL DEFAULT 0,
  `w` int(11) NOT NULL DEFAULT 0,
  `h` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spots_media_id_foreign` (`media_id`),
  CONSTRAINT `spots_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=547 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spots`
--

LOCK TABLES `spots` WRITE;
/*!40000 ALTER TABLE `spots` DISABLE KEYS */;
INSERT INTO `spots` VALUES
(1,1,1,0,0,18,2,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(2,1,1,0,2,18,9,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(3,1,1,0,12,9,2,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(4,1,1,10,12,9,2,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(5,1,1,0,15,9,18,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(6,1,1,9,14,9,9,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(7,1,1,9,20,9,9,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(8,2,1,0,0,18,32,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(9,3,1,0,0,32,18,'2023-11-06 15:32:58','2023-11-06 15:32:58',NULL),
(10,4,25,0,0,18,2,'2023-11-06 19:41:40','2023-11-27 16:34:41',NULL),
(11,4,9,0,2,18,9,'2023-11-06 19:41:40','2023-11-20 19:03:10',NULL),
(12,4,42,0,12,9,2,'2023-11-06 19:41:40','2023-11-30 21:21:36',NULL),
(13,4,43,10,12,9,2,'2023-11-06 19:41:40','2023-11-30 21:21:47',NULL),
(14,4,45,0,15,9,18,'2023-11-06 19:41:40','2023-11-30 21:21:58',NULL),
(15,4,44,9,14,9,9,'2023-11-06 19:41:40','2023-11-30 21:22:08',NULL),
(16,4,46,9,20,9,9,'2023-11-06 19:41:40','2023-11-30 21:22:18',NULL),
(17,5,25,0,0,18,2,'2023-11-06 19:44:39','2023-11-27 16:35:59',NULL),
(18,5,12,0,2,18,9,'2023-11-06 19:44:39','2024-05-16 11:23:35',NULL),
(19,5,42,0,12,9,2,'2023-11-06 19:44:39','2023-12-11 05:41:22',NULL),
(20,5,43,10,12,9,2,'2023-11-06 19:44:39','2023-12-11 05:41:48',NULL),
(21,5,45,0,15,9,18,'2023-11-06 19:44:39','2023-12-11 05:42:16',NULL),
(22,5,44,9,14,9,9,'2023-11-06 19:44:39','2023-12-11 05:42:32',NULL),
(23,5,46,9,20,9,9,'2023-11-06 19:44:39','2023-12-11 05:42:48',NULL),
(24,6,25,0,0,18,2,'2023-11-06 19:44:43','2023-11-27 17:08:47',NULL),
(25,6,10,0,2,18,9,'2023-11-06 19:44:43','2024-05-16 11:22:47',NULL),
(26,6,42,0,12,9,2,'2023-11-06 19:44:43','2023-12-11 05:43:37',NULL),
(27,6,43,10,12,9,2,'2023-11-06 19:44:43','2023-12-11 05:43:54',NULL),
(28,6,45,0,15,9,18,'2023-11-06 19:44:43','2023-12-11 05:44:08',NULL),
(29,6,44,9,14,9,9,'2023-11-06 19:44:43','2023-12-11 05:44:24',NULL),
(30,6,46,9,20,9,9,'2023-11-06 19:44:43','2023-12-11 05:44:40',NULL),
(31,7,25,0,0,18,2,'2023-11-06 19:44:44','2023-11-27 17:10:46',NULL),
(32,7,11,0,2,18,9,'2023-11-06 19:44:44','2024-05-16 11:23:11',NULL),
(33,7,42,0,12,9,2,'2023-11-06 19:44:44','2023-12-11 05:45:10',NULL),
(34,7,43,10,12,9,2,'2023-11-06 19:44:44','2023-12-11 05:45:32',NULL),
(35,7,45,0,15,9,18,'2023-11-06 19:44:44','2023-12-11 05:45:54',NULL),
(36,7,44,9,14,9,9,'2023-11-06 19:44:44','2023-12-11 05:46:16',NULL),
(37,7,46,9,20,9,9,'2023-11-06 19:44:44','2023-12-11 05:46:34',NULL),
(38,8,25,0,0,18,2,'2023-11-06 19:49:28','2023-11-27 16:37:15',NULL),
(39,8,6,0,2,18,9,'2023-11-06 19:49:28','2023-11-27 16:37:23',NULL),
(40,8,42,0,12,9,2,'2023-11-06 19:49:28','2023-12-01 14:56:33',NULL),
(41,8,43,10,12,9,2,'2023-11-06 19:49:28','2023-12-01 14:56:48',NULL),
(42,8,45,0,15,9,18,'2023-11-06 19:49:28','2023-12-01 14:57:13',NULL),
(43,8,44,9,14,9,9,'2023-11-06 19:49:28','2023-12-01 14:57:32',NULL),
(44,8,46,9,20,9,9,'2023-11-06 19:49:28','2023-12-01 14:57:47',NULL),
(45,9,1,0,0,18,32,'2023-11-08 11:01:55','2023-11-08 11:01:55',NULL),
(46,10,34,0,0,18,32,'2023-11-08 18:37:51','2024-01-09 12:31:33',NULL),
(47,11,1,0,0,18,2,'2023-11-17 20:42:36','2023-11-27 16:10:50',NULL),
(48,11,9,0,2,18,9,'2023-11-17 20:42:36','2023-11-17 20:43:12',NULL),
(49,11,1,0,12,9,2,'2023-11-17 20:42:36','2023-11-27 16:22:14',NULL),
(50,11,1,10,12,9,2,'2023-11-17 20:42:36','2023-11-27 16:22:33',NULL),
(51,11,1,0,15,9,18,'2023-11-17 20:42:36','2023-11-27 16:22:11',NULL),
(52,11,1,9,14,9,9,'2023-11-17 20:42:36','2023-11-27 16:11:13',NULL),
(53,11,1,9,20,9,9,'2023-11-17 20:42:36','2023-11-27 16:11:07',NULL),
(54,12,1,0,0,18,32,'2023-11-20 09:09:02','2023-11-24 14:11:51',NULL),
(55,13,1,0,0,18,2,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(56,13,1,0,2,18,9,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(57,13,1,0,7,6,2,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(58,13,1,6,7,6,2,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(59,13,1,13,7,6,2,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(60,13,1,0,15,9,18,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(61,13,1,9,14,9,9,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(62,13,1,9,20,9,9,'2023-11-09 22:58:09','2023-11-09 22:58:09',NULL),
(63,14,25,0,0,18,2,'2023-11-21 14:38:05','2023-11-27 16:15:14',NULL),
(64,14,6,0,2,18,9,'2023-11-21 14:38:05','2023-11-21 14:38:28',NULL),
(65,14,42,0,7,6,2,'2023-11-21 14:38:05','2023-12-04 06:05:23',NULL),
(66,14,43,6,7,6,2,'2023-11-21 14:38:05','2023-12-04 06:05:50',NULL),
(67,14,49,13,7,6,2,'2023-11-21 14:38:05','2023-12-04 07:06:23',NULL),
(68,14,45,0,15,9,18,'2023-11-21 14:38:05','2023-12-04 07:06:33',NULL),
(69,14,44,9,14,9,9,'2023-11-21 14:38:05','2023-12-04 07:06:41',NULL),
(70,14,46,9,20,9,9,'2023-11-21 14:38:05','2023-12-04 07:06:49',NULL),
(71,15,1,0,0,18,32,'2023-11-24 14:29:50','2023-11-27 19:41:02',NULL),
(72,16,1,0,0,18,2,'2023-11-30 18:05:01','2023-11-30 18:05:01',NULL),
(73,16,1,0,2,18,9,'2023-11-30 18:05:01','2023-11-30 18:05:01',NULL),
(74,16,1,0,11,9,2,'2023-11-30 18:05:01','2023-11-30 18:05:01',NULL),
(75,16,1,9,11,9,2,'2023-11-30 18:05:02','2023-11-30 18:05:02',NULL),
(76,16,1,0,13,9,9,'2023-11-30 18:05:02','2023-11-30 18:05:02',NULL),
(77,16,1,9,13,9,9,'2023-11-30 18:05:02','2023-11-30 18:05:02',NULL),
(78,16,1,0,22,18,10,'2023-11-30 18:05:02','2023-11-30 18:05:02',NULL),
(79,17,1,0,0,18,2,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(80,17,1,0,2,9,20,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(81,17,1,9,2,9,4,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(82,17,1,9,6,9,4,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(83,17,1,9,10,9,4,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(84,17,1,9,14,9,4,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(85,17,1,9,18,9,4,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(86,17,1,0,22,9,9,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(87,17,1,9,22,9,3,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(88,17,1,9,25,9,3,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(89,17,1,9,28,9,3,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(90,18,1,0,0,18,2,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(91,18,1,0,2,18,9,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(92,18,1,0,11,6,2,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(93,18,1,6,11,6,2,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(94,18,1,12,11,6,2,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(95,18,1,0,13,18,19,'2023-11-30 18:55:56','2023-11-30 18:55:56',NULL),
(96,19,25,0,0,18,2,'2023-11-30 21:22:54','2023-11-30 21:23:06',NULL),
(97,19,34,0,2,9,20,'2023-11-30 21:22:54','2023-12-05 12:46:04',NULL),
(98,19,43,9,2,9,4,'2023-11-30 21:22:54','2023-11-30 21:27:38',NULL),
(99,19,42,9,6,9,4,'2023-11-30 21:22:54','2023-11-30 21:27:50',NULL),
(100,19,49,9,10,9,4,'2023-11-30 21:22:54','2023-12-05 12:47:08',NULL),
(101,19,1,9,14,9,4,'2023-11-30 21:22:54','2023-11-30 21:22:54',NULL),
(102,19,1,9,18,9,4,'2023-11-30 21:22:54','2023-11-30 21:22:54',NULL),
(103,19,44,0,22,9,9,'2023-11-30 21:22:54','2023-12-05 12:46:40',NULL),
(104,19,1,9,22,9,3,'2023-11-30 21:22:54','2023-11-30 21:22:54',NULL),
(105,19,1,9,25,9,3,'2023-11-30 21:22:54','2023-11-30 21:22:54',NULL),
(106,19,1,9,28,9,3,'2023-11-30 21:22:54','2023-11-30 21:22:54',NULL),
(107,20,25,0,0,18,2,'2023-12-04 19:17:40','2023-12-04 19:18:26',NULL),
(108,20,40,0,2,18,9,'2023-12-04 19:17:40','2023-12-04 19:18:33',NULL),
(109,20,42,0,12,9,2,'2023-12-04 19:17:40','2023-12-04 19:18:51',NULL),
(110,20,43,10,12,9,2,'2023-12-04 19:17:40','2023-12-04 19:19:00',NULL),
(111,20,45,0,15,9,18,'2023-12-04 19:17:40','2023-12-04 19:19:09',NULL),
(112,20,44,9,14,9,9,'2023-12-04 19:17:40','2023-12-04 19:19:18',NULL),
(113,20,46,9,20,9,9,'2023-12-04 19:17:40','2023-12-04 19:19:27',NULL),
(114,21,1,0,0,18,32,'2023-12-06 06:03:53','2023-12-06 06:03:53',NULL),
(115,22,9,0,0,18,32,'2023-12-06 06:06:45','2023-12-06 06:07:05',NULL),
(116,23,25,0,0,18,2,'2023-12-06 06:10:25','2023-12-06 06:29:34',NULL),
(117,23,50,0,2,18,9,'2023-12-06 06:10:25','2023-12-06 06:10:41',NULL),
(118,23,42,0,11,6,2,'2023-12-06 06:10:25','2023-12-06 06:29:47',NULL),
(119,23,43,6,11,6,2,'2023-12-06 06:10:25','2023-12-06 06:30:11',NULL),
(120,23,49,12,11,6,2,'2023-12-06 06:10:25','2023-12-06 06:30:19',NULL),
(121,23,51,0,13,18,19,'2023-12-06 06:10:25','2023-12-06 06:11:43',NULL),
(122,24,52,0,0,18,32,'2024-02-01 07:30:15','2024-02-01 07:30:59',NULL),
(123,25,58,0,0,18,32,'2024-02-01 12:11:31','2024-03-14 06:17:31',NULL),
(124,26,1,0,0,18,32,'2024-02-06 13:59:03','2024-02-06 15:11:14',NULL),
(125,27,56,0,0,18,32,'2024-02-06 14:09:41','2024-02-06 14:26:16',NULL),
(126,28,25,0,0,18,2,'2024-02-06 15:08:56','2024-02-06 15:10:34',NULL),
(127,28,56,0,2,18,9,'2024-02-06 15:08:56','2024-02-06 15:12:12',NULL),
(128,28,42,0,12,9,2,'2024-02-06 15:08:56','2024-02-06 15:12:41',NULL),
(129,28,43,10,12,9,2,'2024-02-06 15:08:56','2024-02-06 15:12:52',NULL),
(130,28,45,0,15,9,18,'2024-02-06 15:08:56','2024-02-06 15:13:01',NULL),
(131,28,44,9,14,9,9,'2024-02-06 15:08:56','2024-02-06 15:13:11',NULL),
(132,28,46,9,20,9,9,'2024-02-06 15:08:56','2024-02-06 15:13:21',NULL),
(133,29,68,0,0,18,32,'2024-02-26 08:53:23','2024-09-30 18:37:51',NULL),
(134,30,53,0,0,18,32,'2024-03-14 06:15:47','2024-03-14 06:16:45',NULL),
(135,31,25,0,0,18,2,'2024-05-16 10:29:05','2024-05-16 10:34:10',NULL),
(136,31,59,0,2,18,9,'2024-05-16 10:29:05','2024-05-16 11:22:16',NULL),
(137,31,42,0,12,9,2,'2024-05-16 10:29:05','2024-05-16 10:34:37',NULL),
(138,31,43,10,12,9,2,'2024-05-16 10:29:05','2024-05-16 10:34:51',NULL),
(139,31,45,0,15,9,18,'2024-05-16 10:29:05','2024-05-16 10:35:05',NULL),
(140,31,44,9,14,9,9,'2024-05-16 10:29:05','2024-05-16 10:35:17',NULL),
(141,31,46,9,20,9,9,'2024-05-16 10:29:05','2024-05-16 10:35:27',NULL),
(142,32,1,0,0,18,2,'2024-05-16 10:32:08','2024-05-16 10:32:08',NULL),
(143,32,1,0,2,18,9,'2024-05-16 10:32:08','2024-05-16 10:32:08',NULL),
(144,32,1,0,11,9,2,'2024-05-16 10:32:08','2024-05-16 10:32:08',NULL),
(145,32,1,9,11,9,2,'2024-05-16 10:32:08','2024-05-16 10:32:08',NULL),
(146,32,1,0,13,9,9,'2024-05-16 10:32:08','2024-05-16 10:32:08',NULL),
(147,32,1,9,13,9,9,'2024-05-16 10:32:08','2024-05-16 10:32:08',NULL),
(148,32,1,0,22,18,10,'2024-05-16 10:32:08','2024-05-16 10:32:08',NULL),
(149,33,25,0,0,18,2,'2024-05-16 10:33:17','2024-05-17 10:57:37',NULL),
(150,33,60,0,2,18,9,'2024-05-16 10:33:17','2024-05-17 10:57:44',NULL),
(151,33,42,0,12,9,2,'2024-05-16 10:33:17','2024-05-17 10:57:55',NULL),
(152,33,43,10,12,9,2,'2024-05-16 10:33:17','2024-05-17 10:58:03',NULL),
(153,33,45,0,15,9,18,'2024-05-16 10:33:17','2024-05-17 10:58:18',NULL),
(154,33,44,9,14,9,9,'2024-05-16 10:33:17','2024-05-17 10:58:50',NULL),
(155,33,46,9,20,9,9,'2024-05-16 10:33:17','2024-05-17 10:58:58',NULL),
(156,34,25,0,0,18,2,'2024-05-16 10:33:46','2024-05-17 10:59:23',NULL),
(157,34,61,0,2,18,9,'2024-05-16 10:33:46','2024-05-17 10:59:29',NULL),
(158,34,42,0,12,9,2,'2024-05-16 10:33:46','2024-05-17 10:59:42',NULL),
(159,34,43,10,12,9,2,'2024-05-16 10:33:46','2024-05-17 11:00:03',NULL),
(160,34,45,0,15,9,18,'2024-05-16 10:33:46','2024-05-17 11:00:38',NULL),
(161,34,44,9,14,9,9,'2024-05-16 10:33:46','2024-05-17 11:00:30',NULL),
(162,34,46,9,20,9,9,'2024-05-16 10:33:46','2024-05-17 11:00:51',NULL),
(163,35,25,0,0,18,2,'2024-05-17 10:55:21','2024-05-17 10:55:42',NULL),
(164,35,62,0,2,18,9,'2024-05-17 10:55:21','2024-05-17 10:55:59',NULL),
(165,35,42,0,12,9,2,'2024-05-17 10:55:21','2024-05-17 10:56:07',NULL),
(166,35,43,10,12,9,2,'2024-05-17 10:55:21','2024-05-17 10:56:15',NULL),
(167,35,45,0,15,9,18,'2024-05-17 10:55:21','2024-05-17 10:56:24',NULL),
(168,35,44,9,14,9,9,'2024-05-17 10:55:21','2024-05-17 10:56:58',NULL),
(169,35,46,9,20,9,9,'2024-05-17 10:55:21','2024-05-17 10:57:06',NULL),
(170,36,63,0,0,18,32,'2024-09-03 07:16:37','2024-09-03 07:19:45',NULL),
(171,37,25,0,0,18,2,'2024-09-03 07:32:37','2024-09-03 07:33:36',NULL),
(172,37,50,0,2,18,9,'2024-09-03 07:32:37','2024-09-03 07:34:34',NULL),
(173,37,42,0,12,9,2,'2024-09-03 07:32:37','2024-09-03 07:35:02',NULL),
(174,37,43,10,12,9,2,'2024-09-03 07:32:37','2024-09-03 07:35:12',NULL),
(175,37,45,0,15,9,18,'2024-09-03 07:32:37','2024-09-03 07:35:29',NULL),
(176,37,64,9,14,9,9,'2024-09-03 07:32:37','2024-09-03 07:42:00',NULL),
(177,37,44,9,20,9,9,'2024-09-03 07:32:37','2024-09-03 07:42:12',NULL),
(178,38,1,0,0,18,32,'2024-09-11 08:32:25','2024-11-26 11:19:09',NULL),
(179,39,25,0,0,18,2,'2024-09-20 07:42:51','2024-09-20 07:43:50',NULL),
(180,39,66,0,2,18,9,'2024-09-20 07:42:51','2024-09-20 07:43:58',NULL),
(181,39,42,0,12,9,2,'2024-09-20 07:42:51','2024-09-20 07:44:16',NULL),
(182,39,43,10,12,9,2,'2024-09-20 07:42:51','2024-09-20 07:44:24',NULL),
(183,39,45,0,15,9,18,'2024-09-20 07:42:51','2024-09-20 07:44:34',NULL),
(184,39,44,9,14,9,9,'2024-09-20 07:42:51','2024-09-20 07:44:44',NULL),
(185,39,46,9,20,9,9,'2024-09-20 07:42:51','2024-09-20 07:44:53',NULL),
(186,40,25,0,0,18,2,'2024-09-20 07:45:22','2024-09-20 07:45:45',NULL),
(187,40,67,0,2,18,9,'2024-09-20 07:45:22','2024-09-20 07:47:44',NULL),
(188,40,42,0,12,9,2,'2024-09-20 07:45:22','2024-09-20 07:47:52',NULL),
(189,40,43,10,12,9,2,'2024-09-20 07:45:22','2024-09-20 07:47:59',NULL),
(190,40,45,0,15,9,18,'2024-09-20 07:45:22','2024-09-20 07:48:07',NULL),
(191,40,44,9,14,9,9,'2024-09-20 07:45:22','2024-09-20 07:48:18',NULL),
(192,40,46,9,20,9,9,'2024-09-20 07:45:22','2024-09-20 07:48:29',NULL),
(193,41,69,0,0,18,32,'2024-10-01 04:30:45','2025-02-11 18:41:50',NULL),
(194,42,1,0,0,18,2,'2024-10-08 09:18:05','2024-10-08 09:18:05',NULL),
(195,42,1,0,2,9,9,'2024-10-08 09:18:05','2024-10-08 09:18:05',NULL),
(196,42,1,9,0,9,9,'2024-10-08 09:18:05','2024-10-08 09:18:05',NULL),
(197,42,1,0,9,9,2,'2024-10-08 09:18:05','2024-10-08 09:18:05',NULL),
(198,42,1,10,9,9,2,'2024-10-08 09:18:05','2024-10-08 09:18:05',NULL),
(199,42,1,12,9,18,9,'2024-10-08 09:18:05','2024-10-08 09:18:05',NULL),
(200,42,1,13,9,18,9,'2024-10-08 09:18:06','2024-10-08 09:18:06',NULL),
(201,43,25,0,0,18,2,'2024-10-08 09:28:48','2024-10-08 09:29:13',NULL),
(202,43,44,0,2,9,9,'2024-10-08 09:28:48','2024-10-08 09:29:39',NULL),
(203,43,46,9,0,9,9,'2024-10-08 09:28:48','2024-10-08 09:29:57',NULL),
(204,43,42,0,9,9,2,'2024-10-08 09:28:48','2024-10-08 09:30:11',NULL),
(205,43,43,10,9,9,2,'2024-10-08 09:28:48','2024-10-08 09:30:38',NULL),
(206,43,10,12,9,18,9,'2024-10-08 09:28:48','2024-10-08 09:30:58',NULL),
(207,43,45,13,9,18,9,'2024-10-08 09:28:48','2024-10-08 09:31:27',NULL),
(213,45,71,0,0,18,32,'2024-11-21 09:12:25','2025-02-13 23:08:55',NULL),
(229,44,1,0,0,9,3,'2024-12-11 15:29:38',NULL,NULL),
(230,44,1,9,0,9,3,'2024-12-11 15:29:38',NULL,NULL),
(231,44,1,0,3,18,4,'2024-12-11 15:29:38',NULL,NULL),
(232,44,1,0,7,6,3,'2024-12-11 15:29:38',NULL,NULL),
(233,44,1,6,7,6,3,'2024-12-11 15:29:38',NULL,NULL),
(234,44,1,12,7,6,3,'2024-12-11 15:29:38',NULL,NULL),
(235,44,1,0,10,6,3,'2024-12-11 15:29:38',NULL,NULL),
(236,44,1,6,10,6,3,'2024-12-11 15:29:38',NULL,NULL),
(237,44,1,12,10,6,3,'2024-12-11 15:29:38',NULL,NULL),
(238,44,1,0,13,6,3,'2024-12-11 15:29:38',NULL,NULL),
(239,44,1,6,13,6,3,'2024-12-11 15:29:38',NULL,NULL),
(240,44,1,12,13,6,3,'2024-12-11 15:29:38',NULL,NULL),
(273,46,1,0,0,18,2,'2024-12-13 03:42:09',NULL,NULL),
(274,46,1,0,2,6,3,'2024-12-13 03:42:09',NULL,NULL),
(275,46,1,6,2,6,3,'2024-12-13 03:42:09',NULL,NULL),
(276,46,1,12,2,6,3,'2024-12-13 03:42:09',NULL,NULL),
(277,46,1,0,5,18,10,'2024-12-13 03:42:09',NULL,NULL),
(318,48,1,0,0,18,2,'2024-12-13 12:55:53','2024-12-13 12:55:53',NULL),
(319,48,1,0,2,9,9,'2024-12-13 12:55:53','2024-12-13 12:55:53',NULL),
(320,48,1,9,0,9,9,'2024-12-13 12:55:53','2024-12-13 12:55:53',NULL),
(321,48,1,0,9,9,2,'2024-12-13 12:55:53','2024-12-13 12:55:53',NULL),
(322,48,1,10,9,9,2,'2024-12-13 12:55:53','2024-12-13 12:55:53',NULL),
(323,48,1,12,9,18,9,'2024-12-13 12:55:53','2024-12-13 12:55:53',NULL),
(324,48,1,13,9,18,9,'2024-12-13 12:55:53','2024-12-13 12:55:53',NULL),
(325,47,1,0,0,18,2,'2024-12-13 20:30:02',NULL,NULL),
(326,47,1,0,2,18,10,'2024-12-13 20:30:02',NULL,NULL),
(327,47,1,0,12,6,3,'2024-12-13 20:30:02',NULL,NULL),
(328,47,1,6,12,6,3,'2024-12-13 20:30:02',NULL,NULL),
(329,47,1,12,12,6,3,'2024-12-13 20:30:02',NULL,NULL),
(331,49,1,0,0,18,8,'2024-12-16 17:58:31',NULL,NULL),
(332,49,1,0,8,9,7,'2024-12-16 17:58:31',NULL,NULL),
(333,49,1,9,8,9,7,'2024-12-16 17:58:31',NULL,NULL),
(341,50,1,0,0,18,2,'2024-12-16 18:18:14',NULL,NULL),
(342,50,1,0,2,18,9,'2024-12-16 18:18:14',NULL,NULL),
(343,50,1,0,11,9,2,'2024-12-16 18:18:14',NULL,NULL),
(344,50,1,9,11,9,2,'2024-12-16 18:18:14',NULL,NULL),
(345,50,1,0,13,9,9,'2024-12-16 18:18:14',NULL,NULL),
(346,50,1,9,13,9,19,'2024-12-16 18:18:14',NULL,NULL),
(347,50,1,0,22,9,10,'2024-12-16 18:18:14',NULL,NULL),
(355,53,34,0,0,18,32,'2024-12-16 18:34:18','2024-12-16 11:34:41',NULL),
(426,51,25,0,0,18,2,'2024-12-16 20:18:29','2024-12-16 13:19:16',NULL),
(427,51,59,0,2,18,9,'2024-12-16 20:18:29','2024-12-16 13:19:28',NULL),
(428,51,42,0,11,9,2,'2024-12-16 20:18:29','2024-12-16 13:19:38',NULL),
(429,51,43,9,11,9,2,'2024-12-16 20:18:29','2024-12-16 13:19:47',NULL),
(430,51,44,0,13,9,9,'2024-12-16 20:18:29','2024-12-16 13:19:57',NULL),
(431,51,45,9,13,9,18,'2024-12-16 20:18:29','2024-12-16 13:20:26',NULL),
(432,51,46,0,22,9,9,'2024-12-16 20:18:29','2024-12-16 13:20:36',NULL),
(458,54,1,0,0,18,12,'2025-02-06 15:59:54',NULL,NULL),
(459,57,56,0,0,18,12,'2025-02-06 09:00:04','2025-02-06 09:01:24',NULL),
(460,56,6,0,0,18,32,'2025-02-06 16:00:54','2025-02-06 09:06:23',NULL),
(469,58,25,0,0,18,2,'2025-02-18 00:55:13','2025-02-17 17:55:31',NULL),
(470,58,73,0,2,18,10,'2025-02-18 00:55:13','2025-02-25 09:30:09',NULL),
(471,58,42,0,12,9,2,'2025-02-18 00:55:13','2025-02-17 17:56:06',NULL),
(472,58,43,9,12,9,2,'2025-02-18 00:55:13','2025-02-17 17:56:14',NULL),
(473,58,45,0,14,9,18,'2025-02-18 00:55:13','2025-02-17 17:56:32',NULL),
(474,58,44,9,14,9,9,'2025-02-18 00:55:13','2025-02-17 17:56:43',NULL),
(475,58,46,9,23,9,9,'2025-02-18 00:55:13','2025-02-17 17:56:53',NULL),
(476,59,75,0,0,18,32,'2025-03-02 20:57:24','2025-03-02 21:03:10',NULL),
(477,60,80,0,0,18,32,'2025-03-02 20:58:21','2025-03-03 18:19:22',NULL),
(478,62,75,0,0,18,12,'2025-03-02 22:52:41','2025-03-03 00:51:02',NULL),
(479,63,1,0,0,18,32,'2025-03-05 07:43:42',NULL,NULL),
(480,64,81,0,0,18,32,'2025-03-05 00:44:14','2025-03-05 00:46:34',NULL),
(481,65,82,0,0,18,32,'2025-03-05 17:49:26','2025-03-05 17:49:57',NULL),
(485,66,1,0,0,18,2,'2025-03-11 05:47:30',NULL,NULL),
(486,66,1,0,2,18,10,'2025-03-11 05:47:30',NULL,NULL),
(487,66,1,0,12,18,20,'2025-03-11 05:47:30',NULL,NULL),
(488,67,25,0,0,18,2,'2025-03-10 22:49:12','2025-03-10 22:50:19',NULL),
(489,67,6,0,2,18,10,'2025-03-10 22:49:12','2025-03-10 22:50:53',NULL),
(490,67,83,0,12,18,20,'2025-03-10 22:49:12','2025-03-11 00:20:47',NULL),
(491,68,56,0,0,18,2,'2025-03-17 19:49:58','2025-03-17 20:47:25',NULL),
(492,68,75,0,2,18,10,'2025-03-17 19:49:59','2025-03-17 21:14:06',NULL),
(493,68,34,0,12,18,20,'2025-03-17 19:49:59','2025-03-17 20:44:38',NULL),
(494,69,74,0,0,18,32,'2025-03-23 20:20:49','2025-03-23 20:39:43',NULL),
(495,70,1,0,0,18,2,'2025-06-25 02:46:06',NULL,NULL),
(496,70,1,0,2,18,9,'2025-06-25 02:46:06',NULL,NULL),
(497,70,1,0,11,9,2,'2025-06-25 02:46:06',NULL,NULL),
(498,70,1,9,11,9,2,'2025-06-25 02:46:06',NULL,NULL),
(499,70,1,0,13,18,19,'2025-06-25 02:46:06',NULL,NULL),
(500,71,25,0,0,18,2,'2025-06-24 19:46:25','2025-06-24 19:46:50',NULL),
(501,71,6,0,2,18,9,'2025-06-24 19:46:25','2025-06-24 19:47:08',NULL),
(502,71,42,0,11,9,2,'2025-06-24 19:46:25','2025-06-24 19:47:53',NULL),
(503,71,43,9,11,9,2,'2025-06-24 19:46:25','2025-06-24 19:48:00',NULL),
(504,71,86,0,13,18,19,'2025-06-24 19:46:25','2025-06-24 19:48:11',NULL),
(512,72,1,0,0,18,2,'2025-08-26 03:33:59',NULL,NULL),
(513,72,1,0,2,18,9,'2025-08-26 03:33:59',NULL,NULL),
(514,72,1,0,11,9,2,'2025-08-26 03:33:59',NULL,NULL),
(515,72,1,9,11,9,2,'2025-08-26 03:33:59',NULL,NULL),
(516,72,1,0,13,9,18,'2025-08-26 03:33:59',NULL,NULL),
(517,72,1,9,13,9,9,'2025-08-26 03:33:59',NULL,NULL),
(518,72,1,9,22,9,9,'2025-08-26 03:33:59',NULL,NULL),
(540,73,25,0,0,18,2,'2025-08-26 03:39:04','2025-08-25 20:46:12',NULL),
(541,73,10,0,2,18,9,'2025-08-26 03:39:04','2025-08-25 20:48:49',NULL),
(542,73,42,0,11,9,2,'2025-08-26 03:39:04','2025-08-25 20:54:20',NULL),
(543,73,43,9,11,9,2,'2025-08-26 03:39:04','2025-08-25 20:55:56',NULL),
(544,73,45,0,13,9,18,'2025-08-26 03:39:04','2025-08-25 20:56:08',NULL),
(545,73,87,9,13,9,9,'2025-08-26 03:39:04','2025-08-25 20:44:37',NULL),
(546,73,46,9,22,9,9,'2025-08-26 03:39:04','2025-08-25 20:56:28',NULL);
/*!40000 ALTER TABLE `spots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Administrator','administrator@cms.id','2023-11-06 15:32:58','$2y$10$.k/DLMxDAO6OZJm0rSkww.ObsFwA9x./zcTXKMXYeleljx84INkLa','hgbBURlg2iFw4kSX7k1lKsVXzmwcuEh8VBfRXowuKFv1ZtTVYFtiZuaTDlWW','2023-11-06 15:32:58','2023-12-04 07:01:37',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'platform'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-27 12:18:36
