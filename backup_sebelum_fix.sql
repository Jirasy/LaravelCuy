-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: laravel
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `_majors`
--

DROP TABLE IF EXISTS `_majors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `_majors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mjr_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mjr_abreviation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_majors`
--

LOCK TABLES `_majors` WRITE;
/*!40000 ALTER TABLE `_majors` DISABLE KEYS */;
/*!40000 ALTER TABLE `_majors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ast_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ast_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ast_qty` int NOT NULL,
  `ast_brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ast_category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assets_ast_code_unique` (`ast_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bentrok_jadwal_pilihans`
--

DROP TABLE IF EXISTS `bentrok_jadwal_pilihans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bentrok_jadwal_pilihans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `hari` enum('senin','selasa','rabu','kamis','jumat','sabtu','minggu') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ekskul_a_id` bigint unsigned NOT NULL,
  `ekskul_b_id` bigint unsigned NOT NULL,
  `pilihan` enum('ekskul_a','ekskul_b','bagi_waktu') COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bentrok_pilihan_unik` (`user_id`,`hari`,`ekskul_a_id`,`ekskul_b_id`),
  KEY `bentrok_jadwal_pilihans_ekskul_a_id_foreign` (`ekskul_a_id`),
  KEY `bentrok_jadwal_pilihans_ekskul_b_id_foreign` (`ekskul_b_id`),
  CONSTRAINT `bentrok_jadwal_pilihans_ekskul_a_id_foreign` FOREIGN KEY (`ekskul_a_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bentrok_jadwal_pilihans_ekskul_b_id_foreign` FOREIGN KEY (`ekskul_b_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bentrok_jadwal_pilihans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bentrok_jadwal_pilihans`
--

LOCK TABLES `bentrok_jadwal_pilihans` WRITE;
/*!40000 ALTER TABLE `bentrok_jadwal_pilihans` DISABLE KEYS */;
/*!40000 ALTER TABLE `bentrok_jadwal_pilihans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cars`
--

DROP TABLE IF EXISTS `cars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `car_brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `car_model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `car_engine` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `car_fuel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `car_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cars`
--

LOCK TABLES `cars` WRITE;
/*!40000 ALTER TABLE `cars` DISABLE KEYS */;
/*!40000 ALTER TABLE `cars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `casis_pendaftarans`
--

DROP TABLE IF EXISTS `casis_pendaftarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `casis_pendaftarans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `osis_periode_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `motivasi` text COLLATE utf8mb4_unicode_ci,
  `pengalaman` text COLLATE utf8mb4_unicode_ci,
  `visi_misi` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','diajukan','diterima','ditolak','mengundurkan_diri') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `didaftarkan_oleh` bigint unsigned DEFAULT NULL,
  `diputuskan_oleh` bigint unsigned DEFAULT NULL,
  `catatan_keputusan` text COLLATE utf8mb4_unicode_ci,
  `diputuskan_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tahap_sekarang` smallint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `casis_pendaftarans_osis_periode_id_foreign` (`osis_periode_id`),
  KEY `casis_pendaftarans_user_id_foreign` (`user_id`),
  KEY `casis_pendaftarans_didaftarkan_oleh_foreign` (`didaftarkan_oleh`),
  KEY `casis_pendaftarans_diputuskan_oleh_foreign` (`diputuskan_oleh`),
  CONSTRAINT `casis_pendaftarans_didaftarkan_oleh_foreign` FOREIGN KEY (`didaftarkan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `casis_pendaftarans_diputuskan_oleh_foreign` FOREIGN KEY (`diputuskan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `casis_pendaftarans_osis_periode_id_foreign` FOREIGN KEY (`osis_periode_id`) REFERENCES `osis_periodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `casis_pendaftarans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `casis_pendaftarans`
--

LOCK TABLES `casis_pendaftarans` WRITE;
/*!40000 ALTER TABLE `casis_pendaftarans` DISABLE KEYS */;
/*!40000 ALTER TABLE `casis_pendaftarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `casis_reviews`
--

DROP TABLE IF EXISTS `casis_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `casis_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `casis_pendaftaran_id` bigint unsigned NOT NULL,
  `reviewer_id` bigint unsigned NOT NULL,
  `tipe` enum('pembina','admin','senior') COLLATE utf8mb4_unicode_ci NOT NULL,
  `skor` tinyint unsigned NOT NULL,
  `rekomendasi` enum('lolos','pertimbangkan','tidak_lolos') COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `seleksi_tahap_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `casis_reviews_per_tahap_unique` (`casis_pendaftaran_id`,`reviewer_id`,`seleksi_tahap_id`),
  KEY `casis_reviews_reviewer_id_foreign` (`reviewer_id`),
  KEY `casis_reviews_seleksi_tahap_id_foreign` (`seleksi_tahap_id`),
  CONSTRAINT `casis_reviews_casis_pendaftaran_id_foreign` FOREIGN KEY (`casis_pendaftaran_id`) REFERENCES `casis_pendaftarans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `casis_reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `casis_reviews_seleksi_tahap_id_foreign` FOREIGN KEY (`seleksi_tahap_id`) REFERENCES `seleksi_tahaps` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `casis_reviews`
--

LOCK TABLES `casis_reviews` WRITE;
/*!40000 ALTER TABLE `casis_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `casis_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_anggota_jabatans`
--

DROP TABLE IF EXISTS `ekskul_anggota_jabatans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_anggota_jabatans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` bigint unsigned NOT NULL,
  `jabatan_id` bigint unsigned NOT NULL,
  `periode_mulai` smallint unsigned NOT NULL,
  `periode_selesai` smallint unsigned DEFAULT NULL,
  `ditetapkan_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_anggota_jabatans_ditetapkan_oleh_foreign` (`ditetapkan_oleh`),
  KEY `ekskul_anggota_jabatans_jabatan_id_periode_selesai_index` (`jabatan_id`,`periode_selesai`),
  KEY `ekskul_anggota_jabatans_pendaftaran_id_periode_selesai_index` (`pendaftaran_id`,`periode_selesai`),
  CONSTRAINT `ekskul_anggota_jabatans_ditetapkan_oleh_foreign` FOREIGN KEY (`ditetapkan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_anggota_jabatans_jabatan_id_foreign` FOREIGN KEY (`jabatan_id`) REFERENCES `ekskul_jabatans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_anggota_jabatans_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_ekskuls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_anggota_jabatans`
--

LOCK TABLES `ekskul_anggota_jabatans` WRITE;
/*!40000 ALTER TABLE `ekskul_anggota_jabatans` DISABLE KEYS */;
INSERT INTO `ekskul_anggota_jabatans` VALUES (1,2,1,2026,NULL,1,'2026-10-26 19:22:34','2026-10-26 19:22:34'),(2,5,2,2026,NULL,1,'2026-10-26 23:23:39','2026-10-26 23:23:39'),(3,13,9,2026,NULL,1,'2026-10-28 21:41:02','2026-10-28 21:41:02'),(4,15,8,2026,NULL,1,'2026-10-28 21:41:24','2026-10-28 21:41:24'),(5,8,5,2026,NULL,1,'2026-10-28 21:42:40','2026-10-28 21:42:40'),(6,8,12,2026,NULL,1,'2026-10-28 21:42:40','2026-10-28 21:42:40'),(7,7,4,2026,NULL,1,'2026-10-28 21:43:20','2026-10-28 21:43:20'),(8,7,12,2026,NULL,1,'2026-10-28 21:43:20','2026-10-28 21:43:20'),(9,6,3,2026,NULL,1,'2026-10-28 21:43:55','2026-10-28 21:43:55'),(10,6,12,2026,NULL,1,'2026-10-28 21:43:55','2026-10-28 21:43:55'),(11,12,9,2026,NULL,1,'2026-10-28 21:44:28','2026-10-28 21:44:28'),(12,9,6,2026,NULL,1,'2026-10-28 21:45:21','2026-10-28 21:45:21'),(13,9,10,2026,NULL,1,'2026-10-28 21:45:22','2026-10-28 21:45:22'),(14,9,1,2026,NULL,1,'2026-10-28 21:45:22','2026-10-28 21:45:22'),(15,14,11,2026,NULL,1,'2026-10-28 21:46:22','2026-10-28 21:46:22'),(16,14,12,2026,NULL,1,'2026-10-28 21:46:22','2026-10-28 21:46:22'),(17,7,11,2026,NULL,1,'2026-10-28 21:46:55','2026-10-28 21:46:55'),(18,11,10,2026,NULL,1,'2026-10-28 21:47:27','2026-10-28 21:47:27'),(19,10,7,2026,NULL,1,'2026-10-28 21:48:03','2026-10-28 21:48:03'),(20,10,9,2026,NULL,1,'2026-10-28 21:48:03','2026-10-28 21:48:03'),(21,6,11,2026,NULL,1,'2026-10-28 21:48:46','2026-10-28 21:48:46'),(22,11,7,2026,NULL,1,'2026-10-28 21:49:29','2026-10-28 21:49:29');
/*!40000 ALTER TABLE `ekskul_anggota_jabatans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_events`
--

DROP TABLE IF EXISTS `ekskul_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('lomba','pentas','latihan','rapat','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lomba',
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `lokasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_events_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  CONSTRAINT `ekskul_events_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_events`
--

LOCK TABLES `ekskul_events` WRITE;
/*!40000 ALTER TABLE `ekskul_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekskul_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_fotos`
--

DROP TABLE IF EXISTS `ekskul_fotos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_fotos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `event_id` bigint unsigned DEFAULT NULL,
  `diunggah_oleh` bigint unsigned DEFAULT NULL,
  `berkas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_berkas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategori` enum('latihan','tanding','lomba','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'latihan',
  `diambil_pada` date DEFAULT NULL,
  `disetujui` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_fotos_event_id_foreign` (`event_id`),
  KEY `ekskul_fotos_diunggah_oleh_foreign` (`diunggah_oleh`),
  KEY `ekskul_fotos_ekstrakurikuler_id_disetujui_created_at_index` (`ekstrakurikuler_id`,`disetujui`,`created_at`),
  CONSTRAINT `ekskul_fotos_diunggah_oleh_foreign` FOREIGN KEY (`diunggah_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_fotos_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_fotos_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `ekskul_events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_fotos`
--

LOCK TABLES `ekskul_fotos` WRITE;
/*!40000 ALTER TABLE `ekskul_fotos` DISABLE KEYS */;
INSERT INTO `ekskul_fotos` VALUES (1,6,NULL,1,'galeri/6/oxprrQE1qMJMqFdx34f3cfY6bWTvWXzKxDF2XwPt.jpg',NULL,'IMG_5633.JPG','image/jpeg','mahafest','lomba','2026-08-24',1,'2026-10-26 21:47:23','2026-10-26 21:47:23'),(2,6,NULL,1,'galeri/6/9k73cV4qaSYMMNLHdg40jd4m66KCIDXFKlwiumA7.jpg',NULL,'IMG_5635.JPG','image/jpeg','mahafest','lomba','2026-08-24',1,'2026-10-26 21:47:23','2026-10-26 21:47:23'),(3,6,NULL,1,'galeri/6/1Bx6XdFBz5YWJaScORFlcKdW6ArWNClRvAAJ9LAi.jpg',NULL,'IMG_5634.JPG','image/jpeg','mahafest','lomba','2026-08-24',1,'2026-10-26 21:47:24','2026-10-26 21:47:24'),(4,6,NULL,1,'galeri/6/xejJocm2fh2NihYj1nkAsb6IrFhmvRFXOM3DVmdM.jpg',NULL,'IMG_5644.JPG','image/jpeg','mahafest','lomba','2026-08-24',1,'2026-10-26 21:47:25','2026-10-26 21:47:25');
/*!40000 ALTER TABLE `ekskul_fotos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_haris`
--

DROP TABLE IF EXISTS `ekskul_haris`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_haris` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `hari` enum('senin','selasa','rabu','kamis','jumat','sabtu','minggu') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ekskul_haris_ekstrakurikuler_id_hari_unique` (`ekstrakurikuler_id`,`hari`),
  CONSTRAINT `ekskul_haris_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_haris`
--

LOCK TABLES `ekskul_haris` WRITE;
/*!40000 ALTER TABLE `ekskul_haris` DISABLE KEYS */;
INSERT INTO `ekskul_haris` VALUES (1,1,'sabtu','08:00:00','10:00:00'),(2,2,'selasa','15:20:00','16:30:00'),(3,2,'rabu','15:20:00','16:30:00'),(4,3,'selasa','15:20:00','16:30:00'),(5,4,'sabtu','10:00:00','13:00:00'),(6,5,'rabu','15:20:00','17:30:00'),(7,5,'sabtu','10:00:00','13:00:00'),(8,6,'senin','05:00:00','17:00:00'),(9,6,'selasa','05:00:00','17:00:00'),(10,6,'rabu','05:00:00','17:00:00'),(11,6,'kamis','05:00:00','17:00:00'),(12,6,'jumat','05:00:00','17:00:00'),(13,6,'sabtu','05:00:00','17:00:00'),(14,6,'minggu','05:00:00','17:00:00'),(15,7,'sabtu','08:00:00','12:00:00');
/*!40000 ALTER TABLE `ekskul_haris` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_jabatans`
--

DROP TABLE IF EXISTS `ekskul_jabatans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_jabatans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `varian` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `tugas` text COLLATE utf8mb4_unicode_ci,
  `tanggung_jawab` text COLLATE utf8mb4_unicode_ci,
  `urutan` tinyint unsigned NOT NULL DEFAULT '0',
  `kuota` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ekskul_jabatans_ekstrakurikuler_id_nama_varian_unique` (`ekstrakurikuler_id`,`nama`,`varian`),
  CONSTRAINT `ekskul_jabatans_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_jabatans`
--

LOCK TABLES `ekskul_jabatans` WRITE;
/*!40000 ALTER TABLE `ekskul_jabatans` DISABLE KEYS */;
INSERT INTO `ekskul_jabatans` VALUES (1,6,'Seksi Kesehatan','','Seksi Kesehatan OSIS adalah bidang yang bertugas membantu menjaga kesehatan, kebersihan, dan kenyamanan warga sekolah serta mendukung terciptanya lingkungan sekolah yang sehat.','Mengadakan kegiatan yang berkaitan dengan kesehatan dan kebersihan sekolah.\r\nMembantu pelaksanaan program UKS (Usaha Kesehatan Sekolah).\r\nMengingatkan siswa untuk menerapkan pola hidup bersih dan sehat.\r\nMembantu dalam penanganan siswa yang membutuhkan pertolongan pertama.','Menjaga kebersihan dan kesehatan lingkungan sekolah.\r\nMengoordinasikan kegiatan kesehatan yang diselenggarakan OSIS.\r\nMenjadi contoh dalam menerapkan gaya hidup sehat.\r\nMelaporkan kondisi kesehatan dan kebersihan sekolah kepada pembina atau pengurus OSIS.',11,NULL,'2026-10-26 19:22:10','2026-10-28 21:33:51'),(2,2,'Bendahara','1','Bendahara Rohani Islam bertugas mengelola keuangan ekstrakurikuler secara tertib, transparan, dan bertanggung jawab untuk mendukung seluruh kegiatan keagamaan yang diselenggarakan oleh Rohis.','Mengelola pemasukan dan pengeluaran dana Rohis.\r\nMencatat seluruh transaksi keuangan secara rapi.\r\nMenyusun laporan keuangan kegiatan dan kas Rohis.\r\nMenyimpan bukti transaksi dan dokumen keuangan.\r\nBerkoordinasi dengan ketua dan pembina terkait kebutuhan anggaran kegiatan.','Menjaga keamanan dan ketertiban pengelolaan dana Rohis.\r\nMemastikan penggunaan dana sesuai kebutuhan dan kesepakatan organisasi.\r\nMenyampaikan laporan keuangan secara jujur dan transparan.\r\nBertanggung jawab atas saldo kas dan administrasi keuangan Rohis.',4,NULL,'2026-10-26 23:22:59','2026-10-26 23:22:59'),(3,6,'Ketua','','Pemimpin tertinggi organisasi siswa yang bertanggung jawab penuh atas seluruh arah kebijakan, kegiatan, dan kinerja kepengurusan OSIS.','Memimpin dan mengoordinasikan seluruh pengurus serta kegiatan OSIS.\r\n\r\nMenjadi perwakilan resmi siswa dalam hubungan internal sekolah maupun eksternal.\r\n\r\nMengarahkan penyusunan Program Kerja (Progja) tahunan.','Menjamin kelancaran dan keberhasilan seluruh program kerja OSIS.\r\n\r\nMempertanggungjawabkan seluruh kinerja OSIS kepada Pembina OSIS dan pihak Sekolah.',1,1,'2026-10-28 21:25:09','2026-10-28 21:25:09'),(4,6,'Wakil Ketua','','Pendamping utama Ketua OSIS yang membantu koordinasi internal dan siap menggantikan fungsi Ketua saat berhalangan.','Mendampingi Ketua OSIS dalam menjalankan roda organisasi.\r\n\r\nMengawasi dan mengevaluasi kinerja seksi-seksi bidang.\r\n\r\nMenggantikan tugas Ketua OSIS jika Ketua berhalangan hadir.','Terjaga ketertiban dan soliditas internal kepengurusan OSIS.\r\n\r\nKeselarasan kinerja antar Seksi Bidang berjalan dengan lancar.',2,1,'2026-10-28 21:26:13','2026-10-28 21:26:13'),(5,6,'Sekretaris','(internal)','Penanggung jawab utama dalam pengelolaan administrasi internal dan persuratan di dalam lingkungan sekolah/OSIS.','Mengelola agenda internal, notulensi rapat, dan arsip dokumen organisasi.\r\n\r\nMenyusun surat-surat resmi untuk pihak internal sekolah (Pengurus, Guru, Pembina).\r\n\r\nMenyiapkan Laporan Pertanggungjawaban (LPJ) kegiatan secara berkala.','Ketertiban, kelengkapan, dan kerapian seluruh administrasi internal OSIS.',3,NULL,'2026-10-28 21:28:07','2026-10-28 21:28:07'),(6,6,'Sekretaris','(Eksternal)','Penanggung jawab administrasi komunikasi dan korespondensi yang berhubungan dengan pihak di luar organisasi OSIS.','Menyusun dan mengelola surat-surat keluar untuk pihak eksternal (Sekolah lain, Sponsor, Undangan luar, Komunitas).\r\n\r\nMembantu Sekretaris 1 dalam pembuatan proposal kegiatan dan dokumen pendukung.\r\n\r\nMengarsip data dan berkas kerja sama dengan pihak luar.','Ketepatan dan kelancaran alur surat-menyurat serta dokumen ke pihak luar.',4,NULL,'2026-10-28 21:29:20','2026-10-28 21:29:20'),(7,6,'Bendahara','','Pengelola utama keuangan organisasi yang mengatur arus kas masuk dan keluar secara akuntabel.','Mengatur dan mencatat kas rutin OSIS serta anggaran tiap kegiatan.\r\n\r\nMemeriksa dan memverifikasi nota/kuitansi pengeluaran.\r\n\r\nMenyusun laporan keuangan berkala untuk pembina dan pengurus.','Transparansi, akurasi, dan keamanan seluruh dana operasional OSIS.',5,NULL,'2026-10-28 21:30:26','2026-10-28 21:30:26'),(8,6,'Seksi Keagamaan','','Bidang yang memfasilitasi pembinaan keimanan, ketaqwaan, dan toleransi antarumat beragama di sekolah.','Mengembangkan program peringatan hari-hari besar keagamaan (PHBK).\r\n\r\nMengatur kegiatan ibadah rutin dan kajian keagamaan siswa.\r\n\r\nMembina toleransi dan kerukunan antar siswa yang berbeda keyakinan.','Terlaksananya kegiatan keagamaan yang inklusif dan kondusif di sekolah.',6,NULL,'2026-10-28 21:31:56','2026-10-28 21:31:56'),(9,6,'Seksi Seni & Olahraga','','Bidang yang bertugas menyalurkan dan mengembangkan potensi, bakat, serta minat siswa di bidang kesenian dan keolahragaan.','Menyelenggarakan kegiatan Porseni, perlombaan antar-kelas (Classmeeting), atau pentas seni.\r\n\r\nMengoordinasikan pembinaan ekstrakurikuler seni dan olahraga.\r\n\r\nMemfasilitasi siswa dalam mengikuti kompetisi di luar sekolah.','Aktifnya wadah pengembangan bakat dan peningkatan kebugaran serta kreativitas siswa.',7,NULL,'2026-10-28 21:33:10','2026-10-28 21:33:10'),(10,6,'Seksi Kewirausahaan','','Bidang yang berfokus pada pengembangan jiwa entrepreneurship (kewirausahaan) dan pencarian dana mandiri untuk organisasi.','Mengelola unit usaha OSIS (seperti Kantin Kejujuran, penjualan merchandise, atau bazaar sekolah).\r\n\r\nMengadakan pelatihan atau seminar kewirausahaan bagi siswa.\r\n\r\nMembantu pencarian kemitraan/sponsorship untuk mendukung kegiatan OSIS.','Ketersediaan dana tambahan mandiri dan edukasi kewirausahaan bagi siswa.',8,NULL,'2026-10-28 21:35:26','2026-10-28 21:35:26'),(11,6,'Seksi PDD (Publikasi, Dekorasi, & Dokumentasi / IT)','','Bidang teknis yang mengelola media informasi digital, dokumentasi visual, serta dukungan IT organisasi.','Mengelola media sosial resmi OSIS, website, dan saluran publikasi digital lainnya.\r\n\r\nMengambil dokumentasi (foto/video) dan merancang desain grafis/dekorasi untuk setiap acara sekolah.\r\n\r\nMembantu kebutuhan teknis perangkat IT saat penyelenggaraan acara.','Kualitas citra publik OSIS, ketepatan penyampaian informasi, serta kerapian dokumentasi acara.',9,NULL,'2026-10-28 21:37:10','2026-10-28 21:37:10'),(12,6,'Seksi Humas (Hubungan Masyarakat)','','Jembatan komunikasi dan penghubung antara OSIS dengan siswa, guru, alumni, maupun pihak luar.','Menyebarkan informasi program kerja OSIS kepada seluruh warga sekolah.\r\n\r\nMembangun hubungan baik dengan organisasi siswa sekolah lain dan pihak sponsor/mitra.\r\n\r\nMenjadi penampung aspirasi dan masukan dari siswa untuk disampaikan ke pengurus OSIS.','Terjaga dan terjalinnya hubungan harmonis serta komunikasi dua arah antara OSIS dan pihak luar/internal.',10,NULL,'2026-10-28 21:38:20','2026-10-28 21:38:20'),(13,6,'Seksi Bela Negara & Wawasan Kebangsaan','','Bidang yang bertugas menumbuhkan rasa cinta tanah air, jiwa patriotisme, kedisiplinan, serta wawasan kebangsaan di kalangan siswa.','Mengatur dan mempersiapkan pelaksanaan Upacara Bendera rutin (setiap Hari Senin) maupun upacara peringatan Hari Besar Nasional (seperti 17 Agustus, Hari Sumpah Pemuda, Hari Pahlawan, dll.).\r\n\r\nMengoordinasikan pelatihan kedisiplinan, peraturan baris-berbaris (PBB), dan tata upacara sekolah (TUS).\r\n\r\nMengadakan kegiatan pembinaan kesadaran bela negara, kedisiplinan, dan wawasan nusantara (misalnya loka karya kebangsaan, kepramukaan, atau kemah bakti).\r\n\r\nMembantu penegakan kedisiplinan dan tata tertib siswa di lingkungan sekolah bersama pihak Guru BK/Kesiswaan.','Terciptanya lingkungan sekolah yang disiplin, tertib, dan memiliki rasa nasionalisme yang tinggi.\r\n\r\nKelancaran dan kekhidmatan setiap pelaksanaan Upacara Bendera di sekolah.',12,NULL,'2026-10-28 21:40:36','2026-10-28 21:40:36');
/*!40000 ALTER TABLE `ekskul_jabatans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_kehadirans`
--

DROP TABLE IF EXISTS `ekskul_kehadirans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_kehadirans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pertemuan_id` bigint unsigned NOT NULL,
  `pendaftaran_id` bigint unsigned NOT NULL,
  `status` enum('hadir','izin','sakit','alpa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hadir',
  `catatan` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ekskul_kehadirans_pertemuan_id_pendaftaran_id_unique` (`pertemuan_id`,`pendaftaran_id`),
  KEY `ekskul_kehadirans_pendaftaran_id_status_index` (`pendaftaran_id`,`status`),
  CONSTRAINT `ekskul_kehadirans_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_ekskuls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_kehadirans_pertemuan_id_foreign` FOREIGN KEY (`pertemuan_id`) REFERENCES `ekskul_pertemuans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_kehadirans`
--

LOCK TABLES `ekskul_kehadirans` WRITE;
/*!40000 ALTER TABLE `ekskul_kehadirans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekskul_kehadirans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_materis`
--

DROP TABLE IF EXISTS `ekskul_materis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_materis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `jenis` enum('video','foto','dokumen','link') COLLATE utf8mb4_unicode_ci NOT NULL,
  `berkas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_berkas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diunggah_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_materis_diunggah_oleh_foreign` (`diunggah_oleh`),
  KEY `ekskul_materis_ekstrakurikuler_id_created_at_index` (`ekstrakurikuler_id`,`created_at`),
  CONSTRAINT `ekskul_materis_diunggah_oleh_foreign` FOREIGN KEY (`diunggah_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_materis_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_materis`
--

LOCK TABLES `ekskul_materis` WRITE;
/*!40000 ALTER TABLE `ekskul_materis` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekskul_materis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_nilais`
--

DROP TABLE IF EXISTS `ekskul_nilais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_nilais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` bigint unsigned NOT NULL,
  `periode` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterampilan` tinyint unsigned NOT NULL,
  `disiplin` tinyint unsigned NOT NULL,
  `kerja_sama` tinyint unsigned NOT NULL,
  `sikap` tinyint unsigned NOT NULL,
  `saran` text COLLATE utf8mb4_unicode_ci,
  `kritik` text COLLATE utf8mb4_unicode_ci,
  `penilai_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ekskul_nilais_pendaftaran_id_periode_unique` (`pendaftaran_id`,`periode`),
  KEY `ekskul_nilais_penilai_id_foreign` (`penilai_id`),
  CONSTRAINT `ekskul_nilais_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_ekskuls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_nilais_penilai_id_foreign` FOREIGN KEY (`penilai_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_nilais`
--

LOCK TABLES `ekskul_nilais` WRITE;
/*!40000 ALTER TABLE `ekskul_nilais` DISABLE KEYS */;
INSERT INTO `ekskul_nilais` VALUES (1,5,'Semester Ganjil 2026/2027',100,100,100,100,'tajwid dan panjang pendeknya',NULL,1,'2026-10-26 21:55:00','2026-10-26 21:55:00');
/*!40000 ALTER TABLE `ekskul_nilais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_pembinas`
--

DROP TABLE IF EXISTS `ekskul_pembinas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_pembinas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `mulai` date NOT NULL,
  `selesai` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_pembinas_user_id_selesai_index` (`user_id`,`selesai`),
  KEY `ekskul_pembinas_ekstrakurikuler_id_selesai_index` (`ekstrakurikuler_id`,`selesai`),
  CONSTRAINT `ekskul_pembinas_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_pembinas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_pembinas`
--

LOCK TABLES `ekskul_pembinas` WRITE;
/*!40000 ALTER TABLE `ekskul_pembinas` DISABLE KEYS */;
INSERT INTO `ekskul_pembinas` VALUES (1,7,7,'2026-10-28',NULL,'2026-10-28 01:33:30','2026-10-28 01:33:30'),(2,6,2,'2026-10-28',NULL,'2026-10-28 01:34:47','2026-10-28 01:34:47');
/*!40000 ALTER TABLE `ekskul_pembinas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_pengumumans`
--

DROP TABLE IF EXISTS `ekskul_pengumumans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_pengumumans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_pengumumans_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  KEY `ekskul_pengumumans_user_id_foreign` (`user_id`),
  CONSTRAINT `ekskul_pengumumans_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_pengumumans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_pengumumans`
--

LOCK TABLES `ekskul_pengumumans` WRITE;
/*!40000 ALTER TABLE `ekskul_pengumumans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekskul_pengumumans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_pengunduran_diris`
--

DROP TABLE IF EXISTS `ekskul_pengunduran_diris`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_pengunduran_diris` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `pendaftaran_id` bigint unsigned DEFAULT NULL,
  `peran` enum('anggota','pembina') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('mengundurkan_diri','dikeluarkan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `diajukan_oleh` bigint unsigned DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `diputuskan_oleh` bigint unsigned DEFAULT NULL,
  `catatan_keputusan` text COLLATE utf8mb4_unicode_ci,
  `diputuskan_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_pengunduran_diris_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  KEY `ekskul_pengunduran_diris_pendaftaran_id_foreign` (`pendaftaran_id`),
  KEY `ekskul_pengunduran_diris_diajukan_oleh_foreign` (`diajukan_oleh`),
  KEY `ekskul_pengunduran_diris_diputuskan_oleh_foreign` (`diputuskan_oleh`),
  KEY `ekskul_pengunduran_diris_status_ekstrakurikuler_id_index` (`status`,`ekstrakurikuler_id`),
  KEY `ekskul_pengunduran_diris_user_id_ekstrakurikuler_id_status_index` (`user_id`,`ekstrakurikuler_id`,`status`),
  CONSTRAINT `ekskul_pengunduran_diris_diajukan_oleh_foreign` FOREIGN KEY (`diajukan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_pengunduran_diris_diputuskan_oleh_foreign` FOREIGN KEY (`diputuskan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_pengunduran_diris_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_pengunduran_diris_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_ekskuls` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_pengunduran_diris_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_pengunduran_diris`
--

LOCK TABLES `ekskul_pengunduran_diris` WRITE;
/*!40000 ALTER TABLE `ekskul_pengunduran_diris` DISABLE KEYS */;
INSERT INTO `ekskul_pengunduran_diris` VALUES (1,1,3,1,'anggota','mengundurkan_diri','test aja jir',3,'disetujui',1,NULL,'2026-10-27 04:31:48','2026-10-27 04:31:03','2026-10-27 04:31:48');
/*!40000 ALTER TABLE `ekskul_pengunduran_diris` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_pertemuans`
--

DROP TABLE IF EXISTS `ekskul_pertemuans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_pertemuans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `topik` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dibuat_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_pertemuans_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `ekskul_pertemuans_ekstrakurikuler_id_tanggal_index` (`ekstrakurikuler_id`,`tanggal`),
  CONSTRAINT `ekskul_pertemuans_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_pertemuans_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_pertemuans`
--

LOCK TABLES `ekskul_pertemuans` WRITE;
/*!40000 ALTER TABLE `ekskul_pertemuans` DISABLE KEYS */;
INSERT INTO `ekskul_pertemuans` VALUES (1,2,'2026-10-28','15:20:00','16:30:00','instagram rohis',1,'2026-10-28 01:06:32','2026-10-28 01:06:32');
/*!40000 ALTER TABLE `ekskul_pertemuans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_pr_pengumpulans`
--

DROP TABLE IF EXISTS `ekskul_pr_pengumpulans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_pr_pengumpulans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pr_id` bigint unsigned NOT NULL,
  `pendaftaran_id` bigint unsigned NOT NULL,
  `jawaban` text COLLATE utf8mb4_unicode_ci,
  `berkas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_berkas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dikumpulkan_pada` timestamp NOT NULL,
  `nilai` tinyint unsigned DEFAULT NULL,
  `komentar` text COLLATE utf8mb4_unicode_ci,
  `dinilai_oleh` bigint unsigned DEFAULT NULL,
  `dinilai_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ekskul_pr_pengumpulans_pr_id_pendaftaran_id_unique` (`pr_id`,`pendaftaran_id`),
  KEY `ekskul_pr_pengumpulans_pendaftaran_id_foreign` (`pendaftaran_id`),
  KEY `ekskul_pr_pengumpulans_dinilai_oleh_foreign` (`dinilai_oleh`),
  CONSTRAINT `ekskul_pr_pengumpulans_dinilai_oleh_foreign` FOREIGN KEY (`dinilai_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_pr_pengumpulans_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_ekskuls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_pr_pengumpulans_pr_id_foreign` FOREIGN KEY (`pr_id`) REFERENCES `ekskul_prs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_pr_pengumpulans`
--

LOCK TABLES `ekskul_pr_pengumpulans` WRITE;
/*!40000 ALTER TABLE `ekskul_pr_pengumpulans` DISABLE KEYS */;
INSERT INTO `ekskul_pr_pengumpulans` VALUES (1,1,2,'sedang kami kerjakan',NULL,NULL,NULL,'2026-10-27 20:14:42',100,NULL,1,'2026-10-27 23:34:49','2026-10-27 20:14:42','2026-10-27 23:34:49');
/*!40000 ALTER TABLE `ekskul_pr_pengumpulans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_prestasi_anggota`
--

DROP TABLE IF EXISTS `ekskul_prestasi_anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_prestasi_anggota` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prestasi_id` bigint unsigned NOT NULL,
  `pendaftaran_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ekskul_prestasi_anggota_prestasi_id_pendaftaran_id_unique` (`prestasi_id`,`pendaftaran_id`),
  KEY `ekskul_prestasi_anggota_pendaftaran_id_foreign` (`pendaftaran_id`),
  CONSTRAINT `ekskul_prestasi_anggota_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_ekskuls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_prestasi_anggota_prestasi_id_foreign` FOREIGN KEY (`prestasi_id`) REFERENCES `ekskul_prestasis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_prestasi_anggota`
--

LOCK TABLES `ekskul_prestasi_anggota` WRITE;
/*!40000 ALTER TABLE `ekskul_prestasi_anggota` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekskul_prestasi_anggota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_prestasis`
--

DROP TABLE IF EXISTS `ekskul_prestasis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_prestasis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` enum('sekolah','kecamatan','kabupaten','provinsi','nasional','internasional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sekolah',
  `peringkat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun` smallint unsigned NOT NULL,
  `penyelenggara` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_prestasis_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  CONSTRAINT `ekskul_prestasis_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_prestasis`
--

LOCK TABLES `ekskul_prestasis` WRITE;
/*!40000 ALTER TABLE `ekskul_prestasis` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekskul_prestasis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_prs`
--

DROP TABLE IF EXISTS `ekskul_prs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_prs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `tenggat` date DEFAULT NULL,
  `nilai_maks` tinyint unsigned NOT NULL DEFAULT '100',
  `dibuat_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_prs_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `ekskul_prs_ekstrakurikuler_id_created_at_index` (`ekstrakurikuler_id`,`created_at`),
  CONSTRAINT `ekskul_prs_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_prs_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_prs`
--

LOCK TABLES `ekskul_prs` WRITE;
/*!40000 ALTER TABLE `ekskul_prs` DISABLE KEYS */;
INSERT INTO `ekskul_prs` VALUES (1,6,'siapin hari guru, pahlawan, bulan bahasa',NULL,'2026-11-10',100,1,'2026-10-27 18:10:42','2026-10-27 18:10:42');
/*!40000 ALTER TABLE `ekskul_prs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_sarans`
--

DROP TABLE IF EXISTS `ekskul_sarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_sarans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `ekstrakurikuler_id` bigint unsigned DEFAULT NULL,
  `jenis` enum('kritik','saran') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'saran',
  `isi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `anonim` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('baru','dibaca','ditindaklanjuti') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baru',
  `balasan` text COLLATE utf8mb4_unicode_ci,
  `dibalas_oleh` bigint unsigned DEFAULT NULL,
  `dibalas_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_sarans_dibalas_oleh_foreign` (`dibalas_oleh`),
  KEY `ekskul_sarans_ekstrakurikuler_id_status_index` (`ekstrakurikuler_id`,`status`),
  KEY `ekskul_sarans_user_id_index` (`user_id`),
  CONSTRAINT `ekskul_sarans_dibalas_oleh_foreign` FOREIGN KEY (`dibalas_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_sarans_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_sarans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_sarans`
--

LOCK TABLES `ekskul_sarans` WRITE;
/*!40000 ALTER TABLE `ekskul_sarans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekskul_sarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_tautans`
--

DROP TABLE IF EXISTS `ekskul_tautans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_tautans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned DEFAULT NULL,
  `platform` enum('youtube','instagram','tiktok','whatsapp','website') COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` tinyint unsigned NOT NULL DEFAULT '0',
  `dibuat_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_tautans_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  KEY `ekskul_tautans_dibuat_oleh_foreign` (`dibuat_oleh`),
  CONSTRAINT `ekskul_tautans_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_tautans_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_tautans`
--

LOCK TABLES `ekskul_tautans` WRITE;
/*!40000 ALTER TABLE `ekskul_tautans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekskul_tautans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_tugas`
--

DROP TABLE IF EXISTS `ekskul_tugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_tugas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned DEFAULT NULL,
  `judul` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `tenggat` date DEFAULT NULL,
  `dibuat_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ekskul_tugas_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  KEY `ekskul_tugas_dibuat_oleh_foreign` (`dibuat_oleh`),
  CONSTRAINT `ekskul_tugas_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekskul_tugas_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_tugas`
--

LOCK TABLES `ekskul_tugas` WRITE;
/*!40000 ALTER TABLE `ekskul_tugas` DISABLE KEYS */;
INSERT INTO `ekskul_tugas` VALUES (1,NULL,'bantuin pak enjang beli lemineral',NULL,'2026-10-28',1,'2026-10-27 18:08:29','2026-10-27 18:08:29');
/*!40000 ALTER TABLE `ekskul_tugas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekskul_tugas_penerimas`
--

DROP TABLE IF EXISTS `ekskul_tugas_penerimas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekskul_tugas_penerimas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tugas_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('belum','dikerjakan','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum',
  `selesai_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ekskul_tugas_penerimas_tugas_id_user_id_unique` (`tugas_id`,`user_id`),
  KEY `ekskul_tugas_penerimas_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `ekskul_tugas_penerimas_tugas_id_foreign` FOREIGN KEY (`tugas_id`) REFERENCES `ekskul_tugas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ekskul_tugas_penerimas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekskul_tugas_penerimas`
--

LOCK TABLES `ekskul_tugas_penerimas` WRITE;
/*!40000 ALTER TABLE `ekskul_tugas_penerimas` DISABLE KEYS */;
INSERT INTO `ekskul_tugas_penerimas` VALUES (1,1,4,'selesai','2026-10-27 20:15:20','2026-10-27 18:08:29','2026-10-27 20:15:20');
/*!40000 ALTER TABLE `ekskul_tugas_penerimas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekstrakurikulers`
--

DROP TABLE IF EXISTS `ekstrakurikulers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekstrakurikulers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_ekstrakurikuler` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pembina` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `pendaftaran_dibuka` tinyint(1) NOT NULL DEFAULT '1',
  `pendaftaran_tutup_pada` datetime DEFAULT NULL,
  `kuota` smallint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Umum',
  `alur_pendaftaran` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reguler',
  `dibuat_oleh` bigint unsigned DEFAULT NULL,
  `diresmikan_oleh` bigint unsigned DEFAULT NULL,
  `diresmikan_pada` timestamp NULL DEFAULT NULL,
  `tipe` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'biasa',
  PRIMARY KEY (`id`),
  KEY `ekstrakurikulers_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `ekstrakurikulers_diresmikan_oleh_foreign` (`diresmikan_oleh`),
  CONSTRAINT `ekstrakurikulers_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ekstrakurikulers_diresmikan_oleh_foreign` FOREIGN KEY (`diresmikan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekstrakurikulers`
--

LOCK TABLES `ekstrakurikulers` WRITE;
/*!40000 ALTER TABLE `ekstrakurikulers` DISABLE KEYS */;
INSERT INTO `ekstrakurikulers` VALUES (1,'Silat','kang Rizky','Melatih teknik dasar silat dan persiapan kompetisi.','aktif',1,NULL,NULL,'2026-10-22 20:53:42','2026-10-22 20:53:42','Umum','reguler',NULL,NULL,NULL,'biasa'),(2,'Rohani Islam','Pak Angga Abdul Latief','Membimbing siswa dalam kegiatan keagamaan dan pengembangan spiritual.','aktif',1,NULL,NULL,'2026-10-22 20:53:42','2026-10-22 20:53:42','Umum','reguler',NULL,NULL,NULL,'biasa'),(3,'english club','Mrs Dila','Meningkatkan kemampuan berbahasa Inggris siswa melalui berbagai kegiatan dan latihan.','aktif',1,NULL,NULL,'2026-10-22 20:53:43','2026-10-22 20:53:43','Umum','reguler',NULL,NULL,NULL,'biasa'),(4,'Paskibra','Pak Eman','Melatih siswa dalam baris-berbaris dan persiapan upacara bendera.','aktif',1,NULL,NULL,'2026-10-22 20:53:43','2026-10-22 20:53:43','Umum','reguler',NULL,NULL,NULL,'biasa'),(5,'Futsal','Coach Reza','Melatih teknik dasar futsal dan persiapan kompetisi.','aktif',1,NULL,NULL,'2026-10-22 20:53:43','2026-10-22 20:53:43','Umum','reguler',NULL,NULL,NULL,'biasa'),(6,'Organisasi Siswa Intra Sekolah','Teh Siti Nurrohmah','OSIS merupakan wadah dan sarana untuk melatih kepemimpinan, berorganisasi, menyalurkan minat dan bakat, serta mempererat kerja sama antar siswa guna mencapai tujuan bersama yang positif.','aktif',1,NULL,NULL,'2026-10-26 17:50:33','2026-10-29 04:34:47','OSIS','casis_osis',NULL,NULL,NULL,'seleksi'),(7,'Palang Merah Remaja','kang Idris','Wadah pelatihan pertolongan pertama dan jiwa kemanusiaan siswa.','aktif',1,NULL,NULL,'2026-10-28 01:33:29','2026-10-29 06:15:09','Umum','reguler',NULL,NULL,NULL,'biasa');
/*!40000 ALTER TABLE `ekstrakurikulers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employes`
--

DROP TABLE IF EXISTS `employes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `emp_number` int NOT NULL,
  `emp_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emp_position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emp_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employes_emp_number_unique` (`emp_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employes`
--

LOCK TABLES `employes` WRITE;
/*!40000 ALTER TABLE `employes` DISABLE KEYS */;
/*!40000 ALTER TABLE `employes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
-- Table structure for table `healths`
--

DROP TABLE IF EXISTS `healths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `healths` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hlt_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hlt_blood_type` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hlt_illness` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hlt_disability` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hlt_height` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hlt_weight` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `healths`
--

LOCK TABLES `healths` WRITE;
/*!40000 ALTER TABLE `healths` DISABLE KEYS */;
/*!40000 ALTER TABLE `healths` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jihys`
--

DROP TABLE IF EXISTS `jihys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jihys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jihy_model_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_fuel_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_transmission` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_body_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_seating_capacity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_engine_capacity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_price` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_status` tinyint(1) NOT NULL DEFAULT '1',
  `jihy_origin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jihy_founded_year` int DEFAULT NULL,
  `jihy_founder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jihy_headquarters` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jihy_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jihys`
--

LOCK TABLES `jihys` WRITE;
/*!40000 ALTER TABLE `jihys` DISABLE KEYS */;
/*!40000 ALTER TABLE `jihys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jimnys`
--

DROP TABLE IF EXISTS `jimnys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jimnys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jimny_nip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jimny_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jimny_hobby` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jimny_do` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jimny_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jimny_project` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jimny_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jimny_call` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jimnys`
--

LOCK TABLES `jimnys` WRITE;
/*!40000 ALTER TABLE `jimnys` DISABLE KEYS */;
/*!40000 ALTER TABLE `jimnys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kenzo`
--

DROP TABLE IF EXISTS `kenzo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kenzo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kenzo`
--

LOCK TABLES `kenzo` WRITE;
/*!40000 ALTER TABLE `kenzo` DISABLE KEYS */;
/*!40000 ALTER TABLE `kenzo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kredensial_sementara`
--

DROP TABLE IF EXISTS `kredensial_sementara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kredensial_sementara` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `ekstrakurikuler_id` bigint unsigned DEFAULT NULL,
  `dibuat_oleh` bigint unsigned DEFAULT NULL,
  `password_terenkripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `kedaluwarsa_pada` timestamp NOT NULL,
  `terakhir_dilihat_pada` timestamp NULL DEFAULT NULL,
  `terakhir_dilihat_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kredensial_sementara_user_id_unique` (`user_id`),
  KEY `kredensial_sementara_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  KEY `kredensial_sementara_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `kredensial_sementara_terakhir_dilihat_oleh_foreign` (`terakhir_dilihat_oleh`),
  KEY `kredensial_sementara_kedaluwarsa_pada_index` (`kedaluwarsa_pada`),
  CONSTRAINT `kredensial_sementara_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kredensial_sementara_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kredensial_sementara_terakhir_dilihat_oleh_foreign` FOREIGN KEY (`terakhir_dilihat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kredensial_sementara_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kredensial_sementara`
--

LOCK TABLES `kredensial_sementara` WRITE;
/*!40000 ALTER TABLE `kredensial_sementara` DISABLE KEYS */;
INSERT INTO `kredensial_sementara` VALUES (1,208,NULL,1,'eyJpdiI6InBBdkliNHB2NytkbkJZeVNhYUJiV3c9PSIsInZhbHVlIjoiOFI4QjUyVDh1ZEVhNC8veHNKVThTdz09IiwibWFjIjoiNjljOWQyNDdiODNiOWFkMjkzMmFlOWIwZTQxYjExYmFjZjUwZDhjMThlNDlhNmVhZmQxM2YxMjZiMjc3YWExZiIsInRhZyI6IiJ9','2026-11-04 20:31:13','2026-10-28 20:43:34',1,'2026-10-28 20:31:13','2026-10-28 20:43:34'),(2,209,NULL,1,'eyJpdiI6Ind0dEJXenAzVU9NRnF0L2thaXVBcGc9PSIsInZhbHVlIjoiLzRaRXh3ZWUvVSt6alVvcGxZVnBsZz09IiwibWFjIjoiMDc0MTkzMjA4YzFlZjM4OGFkZjhkZTIwOWMzMjJlMDE1ODU4ZWJkMTkxZGNhYTg0NWQ1NTYwMmVjNGZlNDI2ZSIsInRhZyI6IiJ9','2026-11-04 20:31:14','2026-10-28 20:43:34',1,'2026-10-28 20:31:14','2026-10-28 20:43:34'),(3,210,NULL,1,'eyJpdiI6InhydkdYM01FRkFJeDBlR0swZ2pQVkE9PSIsInZhbHVlIjoiUTBVbVJCakRXdWd5UURYVnhSTFpuUT09IiwibWFjIjoiNjYzNjNjNjdjZWZlNjQ3MzJkNzgxYjNmZjZmMTBmOGRmY2VhY2ViYjE5MzU1Yjg3MzZlMWM3NDVjMTZlMWI2OCIsInRhZyI6IiJ9','2026-11-04 20:31:15','2026-10-28 20:43:34',1,'2026-10-28 20:31:15','2026-10-28 20:43:34'),(4,211,NULL,1,'eyJpdiI6IkNSK2ZJMUNSdEphRzNlVW9nRFVmdnc9PSIsInZhbHVlIjoiMlFRZE9icHYxNWxVdHlIcksyaGY4UT09IiwibWFjIjoiYWE3YTVlZWI5MWU3Zjc2N2MyMjczNjc4MjU1MTExOTk4OWY5M2Y1YzliODk0NzViOWNlOGQ0ZGE2NDg1NjJkZCIsInRhZyI6IiJ9','2026-11-04 20:31:16','2026-10-28 20:43:34',1,'2026-10-28 20:31:16','2026-10-28 20:43:34'),(5,212,NULL,1,'eyJpdiI6InlWMnhyR0ZNZzZJcDJmUlJHLzBmTnc9PSIsInZhbHVlIjoiay90eURydHUwVkMwMTU2M0JTSkIwZz09IiwibWFjIjoiYjliZGE3MzA1MmQzYjdhY2JjMTM3OGNhZjUxZjUxODc1MGNmMTQ5NmM1Y2QwNTcxMTRmMmQ2ODA2ODJlNWYzNyIsInRhZyI6IiJ9','2026-11-04 20:31:17','2026-10-28 20:43:34',1,'2026-10-28 20:31:17','2026-10-28 20:43:34'),(6,213,NULL,1,'eyJpdiI6Ik5yTjdTMyszaStOZTlncVBLSUxCUnc9PSIsInZhbHVlIjoiTlV3UjByNHZ0b3V3RUVPV2JDUDVsZz09IiwibWFjIjoiNmFjZGFiOTBkZWNmNzQ3MDI1OGFiNTY2Y2IxZWIxOTBhOWUzNmI3MTFhZjUxNGJjZWYyODBmYTc3OTIyMTVlNCIsInRhZyI6IiJ9','2026-11-04 20:31:18','2026-10-28 20:43:34',1,'2026-10-28 20:31:18','2026-10-28 20:43:34'),(7,214,NULL,1,'eyJpdiI6IkhLeXZFSWtQOXVFaUlCTnhCRldUMHc9PSIsInZhbHVlIjoiVk94Q3NLb0tseUlzblRyeDNDTGt1dz09IiwibWFjIjoiZjcxMDFiMjVhOTdlZjNmNjQ1ZTcxNTdmYmNjZWYwNTM5YWVjYWMyNWY3YTExMTgxMWY0ODQ3YWVmYWQyODcxZiIsInRhZyI6IiJ9','2026-11-04 20:31:19','2026-10-28 20:43:34',1,'2026-10-28 20:31:19','2026-10-28 20:43:34'),(8,215,NULL,1,'eyJpdiI6IkxwbDVoNGJpZU9INWQxWWhzVmdNeVE9PSIsInZhbHVlIjoiYkIxQmU5M2JVTVB4dnhXb2hXQXJrZz09IiwibWFjIjoiY2U1NGZiNmNkYWI4ODNkM2U3NGZhMGEwZGRlYTU4OWUyNTYwNjExMTAzMTBkMDc3NzVhMzIyMGFlNDZhNjQ0NCIsInRhZyI6IiJ9','2026-11-04 20:31:20','2026-10-28 20:43:34',1,'2026-10-28 20:31:20','2026-10-28 20:43:34'),(9,216,NULL,1,'eyJpdiI6IjdYdVNQaTZDc0lwVCtMSVVFUWRtaUE9PSIsInZhbHVlIjoiWG0yVTE2enIrZllSazRUME9xVWxsdz09IiwibWFjIjoiY2NjMjAyZmYyY2I1MDc1YTY3ZjgyMjQ2NjE5NGQ4ODU2OWM3NjcxNTNlNWIyYTg4YzVmYWFkNmE0YWZhZjdhNSIsInRhZyI6IiJ9','2026-11-04 20:31:21','2026-10-28 20:43:34',1,'2026-10-28 20:31:21','2026-10-28 20:43:34'),(10,217,NULL,1,'eyJpdiI6IlpxcHp2TVJQZnFscDExaEdhTW5PTVE9PSIsInZhbHVlIjoiWVVBcWl1VWViNDEvRWtwNnVNTFhjUT09IiwibWFjIjoiNGFlNWQ0NWZjMWM4NDc4ZWVjZTg3OGEzNmMwYjkwMzBhMzY4ZjJkMmZlNjRjZGE3MWI4NDVlMDQ4ODk2NzMxNCIsInRhZyI6IiJ9','2026-11-04 20:31:22','2026-10-28 20:43:34',1,'2026-10-28 20:31:22','2026-10-28 20:43:34'),(11,218,NULL,1,'eyJpdiI6IkxuYStNYWRBWXVDZ3ZOdEpnMTJDMEE9PSIsInZhbHVlIjoiNWNBTnlhUDZQcW84YnhVRkpETERvUT09IiwibWFjIjoiM2U5OTY2NDdkY2MzMjAwMzM4MzQ5ODVmNGVlZGI0NTFlMzBiYTFiZTU4N2Q1OWYyZmUxZGI4YjYxZThkYzhhMSIsInRhZyI6IiJ9','2026-11-04 20:31:23','2026-10-28 20:43:34',1,'2026-10-28 20:31:23','2026-10-28 20:43:34'),(12,219,NULL,1,'eyJpdiI6IkRvbmZUQmZOZjh2WlhQUVIvdHRGNnc9PSIsInZhbHVlIjoiL0laOFNNTG4yN3RhRHhkZFZ4dEJrZz09IiwibWFjIjoiZWQ1OTZhNThiZDE1MzhmODlmYzgxZGIwN2EzM2FlNTIzYThiMTIyNTI0MTk3OTY1ZGQ3MDgwM2I4NzQ0NDhiZiIsInRhZyI6IiJ9','2026-11-04 20:31:24','2026-10-28 20:43:34',1,'2026-10-28 20:31:24','2026-10-28 20:43:34'),(13,220,NULL,1,'eyJpdiI6IlVmbTBVYlBBNE9UZ1RyS1ZYMGVROEE9PSIsInZhbHVlIjoieEJSckZXU0o5S0hwV0ZQTzBPQVNQUT09IiwibWFjIjoiM2M3NGJhYWZjNWI0ZDk2NWI0ZTIxYzk3NjQ5ODY4Y2JlNjczMjgwMDA2M2VmODIwZjA1MzdlNzFjNzA2MGM5MyIsInRhZyI6IiJ9','2026-11-04 20:31:25','2026-10-28 20:43:34',1,'2026-10-28 20:31:25','2026-10-28 20:43:34'),(14,221,NULL,1,'eyJpdiI6IjdQWEJzRW0wbUtiZzJTa0ViRlA1aWc9PSIsInZhbHVlIjoiUDhMUjJweWwzZ21NampQaDhoZDhCQT09IiwibWFjIjoiYTFiYzI2NjMwMTA5YTdiODI5M2E2MGZmNDg0NDVkOWNhNTBiMjY4MjliODRkYWIzOWE2MjZkOGU2MzhkM2QyNiIsInRhZyI6IiJ9','2026-11-04 20:31:26','2026-10-28 20:43:34',1,'2026-10-28 20:31:26','2026-10-28 20:43:34'),(15,222,NULL,1,'eyJpdiI6InJ1cXNHS2ZxK2IvK2JSUHRuWDRDcmc9PSIsInZhbHVlIjoiWXk0S3U1c1ZxYnRGS2xOdlMrckVLdz09IiwibWFjIjoiY2U2MDVjNDM3NTU0NDI5N2NmMzAyMDQ4NTAwNmQxOTRmZWExNDZhMjUyYTg1MGRkMzQyYTA4ZTQ4YTJkMzI0MCIsInRhZyI6IiJ9','2026-11-04 20:31:26','2026-10-28 20:43:34',1,'2026-10-28 20:31:26','2026-10-28 20:43:34'),(16,223,NULL,1,'eyJpdiI6InEzODZlTDJsZXBibUwrTVV0dXY0c1E9PSIsInZhbHVlIjoidEx4K3cydlUrY1BvbDF1YzN6dGdPdz09IiwibWFjIjoiMjQwMGZiM2I4MzQyZTBlZDFlZmUzZGQ1ODM3ZjMwZDE5MzNlZGI5MWRmMzVmNDU4NmZjZGVhMTY4YjAwN2VhZSIsInRhZyI6IiJ9','2026-11-04 20:31:27','2026-10-28 20:43:34',1,'2026-10-28 20:31:27','2026-10-28 20:43:34'),(17,224,NULL,1,'eyJpdiI6IkUrNnhaRmtKUktXTlBwcTExaVNqeXc9PSIsInZhbHVlIjoiSElidlpmMk9OQm5tOFpMaU81TEZHQT09IiwibWFjIjoiMGQ3YWI2NzFkOWQ1ODIxZWVmOGMwZDU4NWJjN2I1YTk4NTFlYTQ4MTk1MGVmNzhhOTYwMjdlZjRjNjEwOWNhZSIsInRhZyI6IiJ9','2026-11-04 20:31:28','2026-10-28 20:43:34',1,'2026-10-28 20:31:28','2026-10-28 20:43:34'),(18,225,NULL,1,'eyJpdiI6Ikl2TU1aejJ0NnQxNU9tOExnODB4Smc9PSIsInZhbHVlIjoiZkszRUtWRUY2NTFrdWZpVWlqU3p6dz09IiwibWFjIjoiZjU4NzBiYWYxMGU4N2Y4YmM4ZjcwZTc2YTFjNWUyODJhZmEwYjNhZjc5OTFjNGU2Njg1M2QzNzQzYTliM2M2NyIsInRhZyI6IiJ9','2026-11-04 20:31:29','2026-10-28 20:43:34',1,'2026-10-28 20:31:29','2026-10-28 20:43:34'),(19,226,NULL,1,'eyJpdiI6Iml0QXBKMDN5OG9ndngxSUFRVWVDSUE9PSIsInZhbHVlIjoic1huQ1hZaUlZdW9uYTFvSXJYbU1RQT09IiwibWFjIjoiODgzNTY3ZjY1MzU4MTNiYmQyMTJhYWU1YzZmNjRkNTc3N2UzOGUwNmYyYzgwMzJlM2Q2OGM1NjYzZjNkOTc2MCIsInRhZyI6IiJ9','2026-11-04 20:31:30','2026-10-28 20:43:34',1,'2026-10-28 20:31:30','2026-10-28 20:43:34'),(20,227,NULL,1,'eyJpdiI6InZwVlM1ckh5emd4WFRQekFYM2cyWUE9PSIsInZhbHVlIjoidXA3Wk9RUG5DT3dCMEk3TU9DZzVUUT09IiwibWFjIjoiMTFjMTBjZWNjYWMxZjBkYjc0MmZhNzE2NjQ3NzEyYmVkZWRiODQ5NTU0ZTY5ZGZkMDdjNTA3MzNjZTVjYmE5MSIsInRhZyI6IiJ9','2026-11-04 20:31:31','2026-10-28 20:43:34',1,'2026-10-28 20:31:31','2026-10-28 20:43:34'),(21,228,NULL,1,'eyJpdiI6Im5yMVZmcUVKL0xZZU9nRWRFNklsSnc9PSIsInZhbHVlIjoiNU80YkluWkNqRTFUNVQrZmpoZ01tZz09IiwibWFjIjoiM2E4MWQ3NzViNDUyNzNmMDU5YTIxNzVjMGQxZWVmMTI5NjkzZjU1YmE3MzQ1MDk3MmJjZmU5Zjc0NWQyZmNhYyIsInRhZyI6IiJ9','2026-11-04 20:31:32','2026-10-28 20:43:34',1,'2026-10-28 20:31:32','2026-10-28 20:43:34'),(22,229,NULL,1,'eyJpdiI6Impoc3llbjMzM2xWckZoQWF1Ynh3aHc9PSIsInZhbHVlIjoiQU9ORHpvTjJBQkVQeXFtRTdScytQdz09IiwibWFjIjoiZjE0ZDU1Y2E3YjBlYjVlM2I2OGY1ZjBlMjMzNmRkM2MwZjUzYTY1NmMzYWIyYmMwNDRjNDZjMmMzZTk5MDY2OSIsInRhZyI6IiJ9','2026-11-04 20:31:33','2026-10-28 20:43:34',1,'2026-10-28 20:31:33','2026-10-28 20:43:34'),(23,230,NULL,1,'eyJpdiI6IlFqN1BuQW1ObkdKWWFsM1JCSWpwOEE9PSIsInZhbHVlIjoiYnpRYUlnTzZRc2lYMVFJajRCQk9Ddz09IiwibWFjIjoiYTBiZWFiNDJkY2UyZGU2ZmUzYWQ4Mjg1NGVmNWViYzhjZDk5ZTIwOTc2OWU2MTMzZWY5YzI0NzRiZmVmMDY2YyIsInRhZyI6IiJ9','2026-11-04 20:31:34','2026-10-28 20:43:34',1,'2026-10-28 20:31:34','2026-10-28 20:43:34'),(24,231,NULL,1,'eyJpdiI6IjlISkVBRGc2WEpjaFlZKzMyZTM3S3c9PSIsInZhbHVlIjoibC95dTNTVk05ZFQzbytaTTEzZ0JZZz09IiwibWFjIjoiNzUwNDg3MmU1ZWU4MTMwYWNjMTRjZWI4YmY1OTQ1ODVhYTk2Yjc0MzFkNTc3Nzk5YTg2YzI3MDNhZGFhNTVhZCIsInRhZyI6IiJ9','2026-11-04 20:31:35','2026-10-28 20:43:34',1,'2026-10-28 20:31:35','2026-10-28 20:43:34'),(25,232,NULL,1,'eyJpdiI6IkNJUlJZVnRqdWZnRG1CKzJBM0dXREE9PSIsInZhbHVlIjoiZ0x3UzU4N0tKcTZRdUVDVk5YNWIxUT09IiwibWFjIjoiM2FjZGU2MDA4ZmYwODk0YTMwY2M0MzlhNDgzNWE0YjE4YzBkZmVjNGZiNDYzZWI3OWM1YjBhMWZkMmYzODQ5YiIsInRhZyI6IiJ9','2026-11-04 20:31:36','2026-10-28 20:43:34',1,'2026-10-28 20:31:36','2026-10-28 20:43:34'),(26,233,NULL,1,'eyJpdiI6ImlYRDBhV0FQblk4QmwralF1YUdWMkE9PSIsInZhbHVlIjoiTy9uNlNKSzBIZ1ZjcjMwVUcwZ3NBdz09IiwibWFjIjoiYTA2ZTgzNzdhYmNlNDRkNDgwN2ExZDczZWY5NDEwYTY0ZmE2YTBmMmQ5OTI2N2UyYTliMzlmYjJiNzBjNmVjNyIsInRhZyI6IiJ9','2026-11-04 20:31:37','2026-10-28 20:43:34',1,'2026-10-28 20:31:37','2026-10-28 20:43:34'),(27,234,NULL,1,'eyJpdiI6ImRWRTBmMGNsTDZqZHZuMHNsNzExeFE9PSIsInZhbHVlIjoiVDJ1NWgvV0twdDZ6bmhRZG1jSVdVUT09IiwibWFjIjoiYjJiNjMyYTg1NTJlNzI2ODQ3NDMzZTY4ZGMwYWFkMGQ3ZTUzYWJjYWE1YjkwMDRhZDhlMGRjOGUzN2MyZTMyYSIsInRhZyI6IiJ9','2026-11-04 20:31:38','2026-10-28 20:43:34',1,'2026-10-28 20:31:38','2026-10-28 20:43:34'),(28,235,NULL,1,'eyJpdiI6Ims5RmN5RmI5bUxhRzRwdXRuUTFsbVE9PSIsInZhbHVlIjoiVktXWkd5Tmw1NG1obTJMMUVKWkRXQT09IiwibWFjIjoiYzRhMTNlMmNiNjBiN2ExN2Y3MWEwNGZhNzgxNTE4OWE0NjRhZTdkMjllMTc1ODhlNDkyZmVjMWUwNWNjYzA3NiIsInRhZyI6IiJ9','2026-11-04 20:31:39','2026-10-28 20:43:34',1,'2026-10-28 20:31:39','2026-10-28 20:43:34'),(29,236,NULL,1,'eyJpdiI6ImNFQWErUlBHQWVDVnp2OXlwdE5idFE9PSIsInZhbHVlIjoiMWlLZVk5QWY5cjIrWEUzdzUwMGJEdz09IiwibWFjIjoiNDJjNjM2MjE1MjI5OTA3ZDNjNmExODJlYzEyNjc5NTgyN2FhZDkwZTRmNDVmZGJjM2NiNzRhYzA3MmIyN2JlNiIsInRhZyI6IiJ9','2026-11-04 20:31:39','2026-10-28 20:43:34',1,'2026-10-28 20:31:39','2026-10-28 20:43:34'),(30,237,NULL,1,'eyJpdiI6InNRVmRWeVlHSW95RG56MFZsdEQ5MlE9PSIsInZhbHVlIjoid2FSU3I0VGNxbXhzdEczQWhCY2NuQT09IiwibWFjIjoiZTRmOWI1ODdmMzk4OGY0YmYwNGZmMTlmY2I5MGE5MjY2NDliN2U5MWQzYjRkZDU1OTkzYTZhNTEyZDQ4MThmNiIsInRhZyI6IiJ9','2026-11-04 20:31:40','2026-10-28 20:43:34',1,'2026-10-28 20:31:40','2026-10-28 20:43:34'),(31,238,NULL,1,'eyJpdiI6Ik9EL0cvSFJ4aHhpVmw4bkpJcElUeHc9PSIsInZhbHVlIjoiM3Y5U01SREdNT3FDYnRGMU5VTDE1dz09IiwibWFjIjoiYjU4NTY5ZjY1NGZjZDY3OTZhMWNmZjZlODc5YjU1NWQ5OTI2NzkyNGMwZDg0YzMxMTliNDYwNmMwMzg0MmEzOSIsInRhZyI6IiJ9','2026-11-04 20:31:41','2026-10-28 20:43:34',1,'2026-10-28 20:31:41','2026-10-28 20:43:34'),(32,239,NULL,1,'eyJpdiI6Imtvcmk5MHVnTDlUTzEvMnNEOUUrWkE9PSIsInZhbHVlIjoiZlFEc3FhZFBYajM5Y0dpci9DaFF5UT09IiwibWFjIjoiZDQwNWEyM2ZhYzM2MzY1YTM5YWNhY2NiMWEwMmNjZDNhMjBmNzY2ZWRjMjA1MzI0M2IyZDcyZWI2ZjE5Mzk0NCIsInRhZyI6IiJ9','2026-11-04 20:31:43','2026-10-28 20:43:34',1,'2026-10-28 20:31:43','2026-10-28 20:43:34'),(33,240,NULL,1,'eyJpdiI6Ii9CS1AyaEVBbU5ObCtlYnVlTy81U1E9PSIsInZhbHVlIjoiR2U1alB2Q0xLTEppaEdXblF0aUo1Zz09IiwibWFjIjoiNWJiNjNmZTE0NjJlZTBmMTQxYTA3NzU4YmZiMTkyMWVmNTZmNTI2YWVkOGJkZjA3YzBlOWU2NDc5ZTlmZWU1OCIsInRhZyI6IiJ9','2026-11-04 20:31:44','2026-10-28 20:43:34',1,'2026-10-28 20:31:44','2026-10-28 20:43:34'),(34,241,NULL,1,'eyJpdiI6Ikd3RS9rWHdSUTVGbFZuRzVONGZKbkE9PSIsInZhbHVlIjoiTmorS0d1aldKMGZaSHQrZ1FZVjRiZz09IiwibWFjIjoiOWVhMjRkMTM4OTIxYzNkYTg4M2VlODYxNTZjMjcyNzA4MGJlOGFkZjFjNDNhMGJiZmRiMTY3ZGRjYTljN2E3ZiIsInRhZyI6IiJ9','2026-11-04 20:31:45','2026-10-28 20:43:34',1,'2026-10-28 20:31:45','2026-10-28 20:43:34'),(35,242,NULL,1,'eyJpdiI6IjRIWjhtdVpxRGV1dk9qUE9kRFN5ZFE9PSIsInZhbHVlIjoiZ1hpOWYvcFJKRlVYMWJlY1Jic3JLZz09IiwibWFjIjoiOGE5ZTU1ZWMyYWYwZWM4NWVhN2UzYjIwOTI3YzkxN2JjZTVkYTgyYmYzNGU1Y2YyZDU2ZDZmMzA3MDAyNTAyZSIsInRhZyI6IiJ9','2026-11-04 20:31:46','2026-10-28 20:43:34',1,'2026-10-28 20:31:46','2026-10-28 20:43:34'),(36,243,NULL,1,'eyJpdiI6IkxkVUcvTElNMHlzTjFMWHdqNk1OQ2c9PSIsInZhbHVlIjoiUSsvNDN6MHViZFBDWDFiS2VJczFWZz09IiwibWFjIjoiNTJjM2IwYmZlYjBhYTI5Y2Q3MmI4OTU4NWE3MGVmMGI4N2U2ODhmM2I2ZWQ4MDBlZDBjNzA5NGM4ZDYzY2Y2MiIsInRhZyI6IiJ9','2026-11-04 20:31:48','2026-10-28 20:43:34',1,'2026-10-28 20:31:48','2026-10-28 20:43:34'),(37,244,NULL,1,'eyJpdiI6Ikdjd3ZYT2M3NXFvTUNLYXExMG1HZGc9PSIsInZhbHVlIjoiT2xiZjRkem16TzBqUEtzd0MvZ0I2dz09IiwibWFjIjoiMTJjOGRkYjc0Y2Y4ZTM5OGE0ODQ3MmJjNmIyMDU3MGUzODJmNDUwY2U4YWM0NDJmZTYyYTQ2ZWM3OTJlNGIxMyIsInRhZyI6IiJ9','2026-11-04 20:31:49','2026-10-28 20:43:34',1,'2026-10-28 20:31:49','2026-10-28 20:43:34'),(38,245,NULL,1,'eyJpdiI6IjZKWDBBRFlueWx4aGQ3dWRZZUJXakE9PSIsInZhbHVlIjoia0l5TThhdU9zZm9WUjZRUS9mallQZz09IiwibWFjIjoiMGE1MDdhYTc3OWU2Y2QwZjlmODRhM2IzY2JhMDJmMTRjNTQwNWVkMTQ1MWI0Y2M2YWNlMGNiN2FiM2E5YTJhOCIsInRhZyI6IiJ9','2026-11-04 20:31:49','2026-10-28 20:43:34',1,'2026-10-28 20:31:49','2026-10-28 20:43:34'),(39,246,NULL,1,'eyJpdiI6IkFJc3lrb1FBN0xBM1FKU2loanhZaUE9PSIsInZhbHVlIjoieFdRUUw4MXd1WXd5UnRZZ0g5eDg4UT09IiwibWFjIjoiZTVjODc2NWNmZDhkMDM3NjFjMGVkZDIyMWM0ZGIzMzg0ODJmNjI3ZTQzYjkwOWFhYWE3YTdlNTVmMmM1Yjc0NSIsInRhZyI6IiJ9','2026-11-04 20:31:50','2026-10-28 20:43:34',1,'2026-10-28 20:31:50','2026-10-28 20:43:34'),(40,247,NULL,1,'eyJpdiI6ImlWaTRWY2ViUXhSOTlUcVpxR1pabnc9PSIsInZhbHVlIjoid3ZQUnhpa1JoRFM3NDFGcm5RQk05dz09IiwibWFjIjoiOGVjMDZjMDFiMjdiYTg0YmFiZDcyODVlY2U2MDYwMzUzMjE1MDQ0M2VjZDAzODFlYTEzM2I3OGQ2YWZiOWVjZiIsInRhZyI6IiJ9','2026-11-04 20:31:51','2026-10-28 20:43:34',1,'2026-10-28 20:31:51','2026-10-28 20:43:34'),(41,248,NULL,1,'eyJpdiI6IlJyNExNcVRDY2ZaVTNvdC9NS2l2VFE9PSIsInZhbHVlIjoiOGVpdENIR0RuUG9hUUJhMG14WGc0UT09IiwibWFjIjoiYTdkYzIzMjc0OTE0MTc5MjYyMjYyYWIwZDUwYWNmZjA2YzBlNDYyMzg5Y2JiZjZlODc1ZmUzN2FmZjhhNzA3MCIsInRhZyI6IiJ9','2026-11-04 20:31:52','2026-10-28 20:43:34',1,'2026-10-28 20:31:52','2026-10-28 20:43:34'),(42,249,NULL,1,'eyJpdiI6IkJkMFUzQVR0QkJvMGkwSzRSb1NRaUE9PSIsInZhbHVlIjoiOU5YcCsvVmtQRjJGN1RwYjMwbHBvZz09IiwibWFjIjoiN2NkZjU4NTFlODlhZWVlMmNlZmU0NDJjYTkxNGI5NzU0ODk3YjQyZDM3MzRjNTBlMmNmODQ3YTU0ZjBiYjQ1OSIsInRhZyI6IiJ9','2026-11-04 20:31:53','2026-10-28 20:43:34',1,'2026-10-28 20:31:53','2026-10-28 20:43:34'),(43,250,NULL,1,'eyJpdiI6ImRjSlNQbUJjWGRRckloUExhZVpDRmc9PSIsInZhbHVlIjoiMFF6cXF0YnVjZ1ppZEw5WmFqV1VnZz09IiwibWFjIjoiOWQ1MjAyZmI1Y2U2Zjk4MzIzNzhlMjFlMjczYzZkMDk5Y2FlM2EzOWNkMzQyZGM4ZmRkOTQzNGUxZDlmYWM3MiIsInRhZyI6IiJ9','2026-11-04 20:31:55','2026-10-28 20:43:34',1,'2026-10-28 20:31:55','2026-10-28 20:43:34'),(44,251,NULL,1,'eyJpdiI6Ijhkd085UUtEcFpMWHkwakhSR3lzcVE9PSIsInZhbHVlIjoiTytkMXY3MVZOazRBaG1qZ2lqM3ZQZz09IiwibWFjIjoiMjM1ZTlmZTE0ZDBiMGEyYWZhZWUwYjE5ZGU1NWQ0M2JhNTUwNjc1NThiZDJiMTUyZDgwNGI4NzUwYjQ5YTU5NCIsInRhZyI6IiJ9','2026-11-04 20:31:56','2026-10-28 20:43:34',1,'2026-10-28 20:31:56','2026-10-28 20:43:34'),(45,252,NULL,1,'eyJpdiI6ImczRzZORHEvVUkydjd5cFpBMDZHL1E9PSIsInZhbHVlIjoiTXhWMnI2cTFGeWRHa2NoY1psZTB5QT09IiwibWFjIjoiNDBkNGFkZWUyZGMyMmVjNDZiZDZlMDczNGFhMjY5NGIxYjViNzdhMmZiMmE3NGQ4MWViOTM3M2MyZmNiYzQ4YSIsInRhZyI6IiJ9','2026-11-04 20:31:57','2026-10-28 20:43:34',1,'2026-10-28 20:31:57','2026-10-28 20:43:34'),(46,253,NULL,1,'eyJpdiI6IjRZSzQ0bTF6bEVjWm5UaStuL1pRK0E9PSIsInZhbHVlIjoiSXhMdmVhSnlNejdORy80Y3ZGV01vUT09IiwibWFjIjoiZWU3MWRkZGMyOTQ5NDI4YjkxOGY5MzY1YTY1MTczMjcyMTBiYzQ0MTcxNzJmNGJiNDg5NGE4ODQ2MGEyNTI3NyIsInRhZyI6IiJ9','2026-11-04 20:31:58','2026-10-28 20:43:34',1,'2026-10-28 20:31:58','2026-10-28 20:43:34'),(47,254,NULL,1,'eyJpdiI6IjhKQU9hK1RSWC9MWXRzeTFjZU42UFE9PSIsInZhbHVlIjoiVTdzbWxKQVlxMzJCNUsxbDI3QjIzQT09IiwibWFjIjoiY2RlNDJjZWFmZWY2ZmUzZTk2ODAwZDY4MDExMWNmZTY4Mzc1ODU3Nzk4M2JmNWQxOTc2Njg5ZGIzNDg5Njk5YSIsInRhZyI6IiJ9','2026-11-04 20:31:59','2026-10-28 20:43:34',1,'2026-10-28 20:31:59','2026-10-28 20:43:34'),(48,255,NULL,1,'eyJpdiI6IkJjeFF3dUQ5THpmMUJEelhhMStWMEE9PSIsInZhbHVlIjoiMDdSZytFUEJBT2dnLzU4bDZOSFgydz09IiwibWFjIjoiODBjNGQ1MWU0ZmU1ZTNiNjllMGU2NTQzYjVhMWE1Y2JjODUzZWVlOTZjY2MwYTExYzIxZjRiMWUwNWZhNWVkZCIsInRhZyI6IiJ9','2026-11-04 20:32:00','2026-10-28 20:43:34',1,'2026-10-28 20:32:00','2026-10-28 20:43:34'),(49,256,NULL,1,'eyJpdiI6ImJwSnp0RlFvSHAzY0c1VEFsWDNCTnc9PSIsInZhbHVlIjoiNjk5K1E0QTUwYjFsRzFPMUR4cndDZz09IiwibWFjIjoiNjJjYjI3MDQ5NzJhMTNjNzg0OThiZTk2NTdjYzMzODM2Y2ZhOTA0NjdjNzdlOWJkZmE1ZGIyMTk2ZTIwZDczNyIsInRhZyI6IiJ9','2026-11-04 20:32:01','2026-10-28 20:43:34',1,'2026-10-28 20:32:01','2026-10-28 20:43:34'),(50,257,NULL,1,'eyJpdiI6IkZZbGhtZDFnY3NHTmx2Q21NNXlnYVE9PSIsInZhbHVlIjoiSmQ2TXhQMlJmaVRsclBWeXJUTW9Kdz09IiwibWFjIjoiMmUwMjM2ZjAwYWRiOTBkNDEzNTllYjBmYmU2OTc3MGVlNTc0YTY0OGU2MDQ5ZjAxODhkM2RiZmYzMGIyY2VjOCIsInRhZyI6IiJ9','2026-11-04 20:32:02','2026-10-28 20:43:34',1,'2026-10-28 20:32:02','2026-10-28 20:43:34'),(51,258,NULL,1,'eyJpdiI6ImcxTFFsbHpsdFBvQ0ppMGlaVWJBRmc9PSIsInZhbHVlIjoiU1VuSUJHdFlxMHpJQXIydWhKTzZNZz09IiwibWFjIjoiYjEyNDQyNzE1ZDQzMWFiOGU5MmVkZWRiNTM3NjY4ZTA5YmY4MjA2OTBhZDBkYjEwOTU2YzQzNDM1YWE4MmQ5MSIsInRhZyI6IiJ9','2026-11-04 20:32:03','2026-10-28 20:43:34',1,'2026-10-28 20:32:03','2026-10-28 20:43:34'),(52,259,NULL,1,'eyJpdiI6InZVMnNpQ3k1RVZpUWJjV1UxdHpCaVE9PSIsInZhbHVlIjoibUZWNmFpdms4SFdzdEZOT2pHT3dwQT09IiwibWFjIjoiMzYyMWJhNzI5MjhmZjgwZDc4YTE2NTIzOTEwZmUzOGE4MjZhNzllYWJkYWU2ZGUzYmQ4YTc1OGNkZWYyY2M1MSIsInRhZyI6IiJ9','2026-11-04 20:32:05','2026-10-28 20:43:34',1,'2026-10-28 20:32:05','2026-10-28 20:43:34'),(53,260,NULL,1,'eyJpdiI6IkcrSHhRNXNLUzhWSWZUS2ZGZ1RrOUE9PSIsInZhbHVlIjoibU5xVG1aVlk0Q3psZ1B0YldudGEwQT09IiwibWFjIjoiMTRkYjQ0YjgyMDVmMzY3N2I1MjgxZTFjZTAxYTI0MjEyMTdhMmMwMzJjMWI2NmVmYWQ2ZWY4NGFiNTRhOGRmYSIsInRhZyI6IiJ9','2026-11-04 20:32:06','2026-10-28 20:43:34',1,'2026-10-28 20:32:06','2026-10-28 20:43:34'),(54,261,NULL,1,'eyJpdiI6IjhwK3p2OW9vY2NWMnJmV0FNc3RhUmc9PSIsInZhbHVlIjoiTWx6ckhxUHBFdHBBeEg1T0JwMEswZz09IiwibWFjIjoiN2VjODViNzVkMzc5ODZlNGE5ZjY1MDNjOTY2NjA4M2VmZWVlNzU0MjY0YmIxODhhMzg0MWQ2NWQ0YWY2N2EzMyIsInRhZyI6IiJ9','2026-11-04 20:32:07','2026-10-28 20:43:34',1,'2026-10-28 20:32:07','2026-10-28 20:43:34'),(55,262,NULL,1,'eyJpdiI6InZRS0lRLzYyMVZNZ1dsQTdXcStXNWc9PSIsInZhbHVlIjoiemttWmRXc0Fta29vRGRTMkZnajcxQT09IiwibWFjIjoiNjBkZmU2N2RmZWQ4MTczMzU1NTk4ODU4NTkzYWVjZGU5NGM1YTczOTc1NzEzODY2MmYzNTlkMGJiZjc2ZGY0MCIsInRhZyI6IiJ9','2026-11-04 20:32:08','2026-10-28 20:43:34',1,'2026-10-28 20:32:08','2026-10-28 20:43:34'),(56,263,NULL,1,'eyJpdiI6IlJuSjB3MEZ0UHZ2M3htNTF6UTJYSXc9PSIsInZhbHVlIjoiYmhXc1llUjR0bjlUY2hXRkdkUHI2UT09IiwibWFjIjoiMmFlZWE0NjJkYjQ3ZGU1YzY5M2RmZjU5YjZkMjdmYzc3MGUxMDEwN2IwYWNlMTJiNGQwNDUyMDIwM2ZhZTNiYyIsInRhZyI6IiJ9','2026-11-04 20:32:09','2026-10-28 20:43:34',1,'2026-10-28 20:32:09','2026-10-28 20:43:34'),(57,264,NULL,1,'eyJpdiI6ImZhN21JT2Z4cnZRUzNzT0x1MEZhL1E9PSIsInZhbHVlIjoidi9rMXgwUmVuSEMxRWpxQ00wOUhMQT09IiwibWFjIjoiNGIyZGQ1MGJmZTRkZWQxZjFlOWEwZWUyZGM0NTllYjUxZjViYzRlMzg3ZjM1NGFiYWE2OTYxNGFiYTUyOGM2ZiIsInRhZyI6IiJ9','2026-11-04 20:32:10','2026-10-28 20:43:34',1,'2026-10-28 20:32:10','2026-10-28 20:43:34'),(58,265,NULL,1,'eyJpdiI6IlJ3cnJldytkVWFIekN4QVF0UWFtMHc9PSIsInZhbHVlIjoiOHFzMzNMRldNcnovQUVSb2VvYnJnUT09IiwibWFjIjoiYmUyMzk5NDJkNWEwODMxODU4MWM2N2Q5ZGMyODNhNWFlMGJmM2Q3MWQwMzFlMWYwOGZlZWYxNTM0Yzg1YzA5YyIsInRhZyI6IiJ9','2026-11-04 20:32:11','2026-10-28 20:43:34',1,'2026-10-28 20:32:11','2026-10-28 20:43:34'),(59,266,NULL,1,'eyJpdiI6ImZ6OTVSTm9VeDVwT3FVbUtrQkNzcEE9PSIsInZhbHVlIjoiUy9OZ3E5U0Vmc3o5UEZxaDE4QmtmZz09IiwibWFjIjoiNzY5MGJmYjllMmVhMWZhODJiNzExNTAyZTdiYjY0YzBhYmVlZWM4MzQ0OTc1ZWUxZDkwMjlkYTNjOTA0YTFhMyIsInRhZyI6IiJ9','2026-11-04 20:32:12','2026-10-28 20:43:34',1,'2026-10-28 20:32:12','2026-10-28 20:43:34'),(60,267,NULL,1,'eyJpdiI6IjNLSUhCWTZJNm5lbm1RUmtTemZ3YUE9PSIsInZhbHVlIjoiYWZISC9rSlJtUlBJbGpGaElHcXhHZz09IiwibWFjIjoiOGFlNjA4MDljNmEzZmNhMTk3NTM3NjU1NGEwYzhjNzNiNjIyM2NmMDliOWQ1NTRjOGVhOWY5YTIwYjUwYzMwMCIsInRhZyI6IiJ9','2026-11-04 20:32:13','2026-10-28 20:43:34',1,'2026-10-28 20:32:13','2026-10-28 20:43:34'),(61,268,NULL,1,'eyJpdiI6ImJOTUxsMzRYT1Z0SHpFZ29SL0Z0WFE9PSIsInZhbHVlIjoiNEY3MUJwZTZZZW1yRHUraEZTT1JtUT09IiwibWFjIjoiZWZiYzYyZGQ4MWEwYTA5Zjc4ZjdjZTU3ZTRhZGNlNmFkY2E2MDBhYTE3NzExMzM4YmZkMjIyYjg2ODdiMTY2ZiIsInRhZyI6IiJ9','2026-11-04 20:32:14','2026-10-28 20:43:34',1,'2026-10-28 20:32:14','2026-10-28 20:43:34'),(62,269,NULL,1,'eyJpdiI6IjJEeVdENGxRdzJWMUlSRzF4d3ozZ1E9PSIsInZhbHVlIjoiYXFpbzFvT2c4SGdTY3BsSVlqcVlCdz09IiwibWFjIjoiZGEwOTgxZDdmY2UzMTQzYjEwZTc3YTgwYTA4MmM5ODJjYTU0M2Q1Yzc3Zjk0NGE0Zjc1NDE0ZGYzMjU2YTJjNCIsInRhZyI6IiJ9','2026-11-04 20:32:15','2026-10-28 20:43:34',1,'2026-10-28 20:32:15','2026-10-28 20:43:34'),(63,270,NULL,1,'eyJpdiI6IjRCVUlqME4wVFprV3daYW5oYTMrOHc9PSIsInZhbHVlIjoid0MxWForaDdXR2pJTWIvazllYmlPUT09IiwibWFjIjoiODhlYmVkMmYwZjFkMTRlMWFmOTcxZDEwOWE2MjM4MzQyOWIwMGY3M2M1MmJkMjVjNjliYjc2ZWNjNjEyOGI5MSIsInRhZyI6IiJ9','2026-11-04 20:32:16','2026-10-28 20:43:34',1,'2026-10-28 20:32:16','2026-10-28 20:43:34'),(64,271,NULL,1,'eyJpdiI6IjlpNHVna0FiTk5WMEk0VkRZSTVvZmc9PSIsInZhbHVlIjoiUVE1V1JKNzBDWExNNXR6bmwxNTAxdz09IiwibWFjIjoiZWY4YTY2OGY3NjJmOTBkY2Q1MGZlOTZhMGNkYTNkNDI1MWRhOThmMzczODdmN2UwYWQ5YjlmYjg1YzdjZmE1NSIsInRhZyI6IiJ9','2026-11-04 20:32:17','2026-10-28 20:43:34',1,'2026-10-28 20:32:17','2026-10-28 20:43:34'),(65,272,NULL,1,'eyJpdiI6Im55cU1LeC93bi9wd0lDZnAvWGVjdkE9PSIsInZhbHVlIjoiQWYxcFhGU1czZFdXN0x0cTlTb0NmUT09IiwibWFjIjoiN2FmMjM1YzA0ZWI0YzllYTQ0N2U0ODE4MmFlNjA2MzU5NWExZGYxODcwMTU3ZmJlMDMyZDIzMGM5ZmNiZTBlMCIsInRhZyI6IiJ9','2026-11-04 20:32:18','2026-10-28 20:43:34',1,'2026-10-28 20:32:18','2026-10-28 20:43:34'),(66,273,NULL,1,'eyJpdiI6IkJqN1NhVzg2OHV2MElDaTdBeDlzTWc9PSIsInZhbHVlIjoiOHlqYmRUcG0wVllEZ0haRWJQL0dLUT09IiwibWFjIjoiNWRiOGM0OGY4NzY1M2ZjZGQ3YmY1Y2U1MjMzMjU4NmQzYjU3NmVmODk3ZWQ3NDQ5ZjJmMWU2M2E4ZDQ5NTg3ZiIsInRhZyI6IiJ9','2026-11-04 20:32:18','2026-10-28 20:43:34',1,'2026-10-28 20:32:18','2026-10-28 20:43:34'),(67,274,NULL,1,'eyJpdiI6Ikg5Qkw1OEZaLytZb3BKc0FsSkFvREE9PSIsInZhbHVlIjoiby9lMFhUQmZzdlJkVElEMEZsblMzdz09IiwibWFjIjoiNjJlMGQyZTZkZmZiYTI3NTdiZjc4N2NjMTRmNWNkNzYyYjA4NTZjNjNjMjU3MjMwZjJkOGI5OTE3MWZhZTdkOCIsInRhZyI6IiJ9','2026-11-04 20:32:19','2026-10-28 20:43:34',1,'2026-10-28 20:32:19','2026-10-28 20:43:34'),(68,275,NULL,1,'eyJpdiI6IlF2NEFmeGh6ejJnY3JWWnFpdHVIUEE9PSIsInZhbHVlIjoiZldDSEhNRUJ6OWhNT204M0c3THJxdz09IiwibWFjIjoiNzRhN2I4ZGEyNTc4YThlOTExNzY3YjQ4NmVhZDMwNzM3MDEwYmJhMmNjMjhlNjE5NTAzMTA0MTU1ODBlMDMxMyIsInRhZyI6IiJ9','2026-11-04 20:32:21','2026-10-28 20:43:34',1,'2026-10-28 20:32:21','2026-10-28 20:43:34'),(69,276,NULL,1,'eyJpdiI6InhyS0dvT24rcmg5ZmZSN2ZpY0pMZXc9PSIsInZhbHVlIjoiaS9oZzhHN0kzdWFjWGUzU0pqOVZ3Zz09IiwibWFjIjoiOTkzN2JhYzU5NWZhYTM5ZTRiOWZlZWQ1NWY0Mzg3MjhmMTQyMGZiMDdiNTM2YjFjMWNhNjYzNWNkYWMxYzVkNyIsInRhZyI6IiJ9','2026-11-04 20:32:22','2026-10-28 20:43:34',1,'2026-10-28 20:32:22','2026-10-28 20:43:34'),(70,277,NULL,1,'eyJpdiI6IjBQMi9lK21uQ0JFQXRab2d6MCs1THc9PSIsInZhbHVlIjoiWlgwZWM1L2dDcjN4RGFwUmRpSnpXUT09IiwibWFjIjoiM2VmMmMxNzAxNzlmYTI0OTQ0ZmVlMjM3MGQ0NjA5YTkwMzc1MzFmZjVkNmEzZjk2MzQ1YTI0YmE5MTZhNTE1MiIsInRhZyI6IiJ9','2026-11-04 20:32:23','2026-10-28 20:43:34',1,'2026-10-28 20:32:23','2026-10-28 20:43:34'),(71,278,NULL,1,'eyJpdiI6IjJ3RWZjNHU2bU1WYXdGQlYrZnFXb0E9PSIsInZhbHVlIjoiSU9pT1hUWlNVaTk5c1hNWWxuUkI0dz09IiwibWFjIjoiYmI1NDJlNGQzZTRhMGJmMThlMjg0NjViNjVjYzgzNWFlMmFlYTBlZDQ3NTliMDEzNWEyM2MzZTk2M2U4OGNkNSIsInRhZyI6IiJ9','2026-11-04 20:32:25','2026-10-28 20:43:34',1,'2026-10-28 20:32:25','2026-10-28 20:43:34'),(72,279,NULL,1,'eyJpdiI6Ild6RFJPWmpwbWcwR0grTnYrYXNiZ2c9PSIsInZhbHVlIjoiOGkwN011V2lzR2hQRi8zaGdGcTdwQT09IiwibWFjIjoiOWE0ZTY4MGZlZDg0MmIzYzZlOTZlMjBlMWYzNWQyYWZjOTFlYjUxODE3MTcyODk5M2QxZjRhN2RhYzYxMmM5YSIsInRhZyI6IiJ9','2026-11-04 20:32:26','2026-10-28 20:43:34',1,'2026-10-28 20:32:26','2026-10-28 20:43:34'),(73,280,NULL,1,'eyJpdiI6ImppTU1Lc2ZvS1FPVDRHWS9MK25JbGc9PSIsInZhbHVlIjoiZE5hNDVsd3hTeGhtcm9oZlpHU2JXQT09IiwibWFjIjoiMTNiYTAzMTNjODlhMGFkM2QyMWNkMGYzMmYxMTA5YjM0ZTk2NjYxMjViN2Q3NTU3MjU4YmUzOGI1NjhhNDFhNCIsInRhZyI6IiJ9','2026-11-04 20:32:27','2026-10-28 20:43:34',1,'2026-10-28 20:32:27','2026-10-28 20:43:34'),(74,281,NULL,1,'eyJpdiI6Im56Y0dpOEVjSHpsZ0FId3d2WHhScmc9PSIsInZhbHVlIjoiSGI4YlZMdnBhc3BPc2hodWh3WkhHZz09IiwibWFjIjoiODljM2Q2MGRlYmM2NGE1NzI5ZGM2ZDdhMWQ2MWJmYmI3NTEwNWY5NzBjZjM1NTg2NzE2YTlkYzk4MGM3MjI5ZCIsInRhZyI6IiJ9','2026-11-04 20:32:28','2026-10-28 20:43:34',1,'2026-10-28 20:32:28','2026-10-28 20:43:34'),(75,282,NULL,1,'eyJpdiI6Ill0c0d0QndlMWtYalI0SmUrM3kwUVE9PSIsInZhbHVlIjoiUEtWaVhybkl0aXYrN1lnVU5nU0JsQT09IiwibWFjIjoiNzZiZWE4MGQ2ZjQyNTI3NzcxNGZmNTI5NWM3OTEzYjE5MWM3YzhhOGZhNGU0MDhmMzgwMmRhMzM4N2M2Nzg5YyIsInRhZyI6IiJ9','2026-11-04 20:32:29','2026-10-28 20:43:34',1,'2026-10-28 20:32:29','2026-10-28 20:43:34'),(76,283,NULL,1,'eyJpdiI6Ii96L3IwRm9BQWh3LzlwUUJSWHBVbUE9PSIsInZhbHVlIjoiSUl6QUdVWkd1bnNMZGJYRzhlWnpvUT09IiwibWFjIjoiOTYxMmZkZGJiNThlMDRiMmEzNjEyZGE4MTg4MmM3NWRhYWRmYTJlM2IyNzU5MDI2NDBiOTE0N2Y5MWU5NTQ4YSIsInRhZyI6IiJ9','2026-11-04 20:32:30','2026-10-28 20:43:34',1,'2026-10-28 20:32:30','2026-10-28 20:43:34'),(77,284,NULL,1,'eyJpdiI6IkV1RCtPZmpMNU03dmRhZ3M0c3ZXYVE9PSIsInZhbHVlIjoibUVNV1I0bTBjN3BJOXg3WjVXdUN0dz09IiwibWFjIjoiNDIxMzU4ZDUyMjE3MWFkOWU5MjgxZTA4ZjBlMTdhMjZmZjI4YzFjYWIwYTA3NTJkOThlYzQyMWFhMTE3NjI4OSIsInRhZyI6IiJ9','2026-11-04 20:32:31','2026-10-28 20:43:34',1,'2026-10-28 20:32:31','2026-10-28 20:43:34'),(78,285,NULL,1,'eyJpdiI6Ii9qeDJyUUREbVJKOHpYWWlzc3AwbWc9PSIsInZhbHVlIjoiS0VWVG9xNnFqRnpyYWVoSWIzWmtTUT09IiwibWFjIjoiMzE1OWNiY2E4ZGE5ZjNjMWI1MzNhODk3NTRmNzc1ZjU1ZmU2NGEwMmIwNTY4NTE2ZWNjOTUzYzEyMWFmMWE5NyIsInRhZyI6IiJ9','2026-11-04 20:32:32','2026-10-28 20:43:34',1,'2026-10-28 20:32:32','2026-10-28 20:43:34'),(79,286,NULL,1,'eyJpdiI6IlJWajNFSnZmM1dZQTdBbjBzZmQ2d0E9PSIsInZhbHVlIjoiU1ZZYXFLOVdrcjhMTDFmc01xRUxoQT09IiwibWFjIjoiMGFjMWU3Mzg2YTZmZTllY2Y0NzRhN2YyMTRiNWNlYmIxYTE3NzIwMzFlMWM0ZjY2MjJjZWZiN2FhM2Q0YjRkNiIsInRhZyI6IiJ9','2026-11-04 20:32:33','2026-10-28 20:43:34',1,'2026-10-28 20:32:33','2026-10-28 20:43:34'),(80,287,NULL,1,'eyJpdiI6IloyT0JUb1FzelpIMHA3ZWtYVWpzUnc9PSIsInZhbHVlIjoiMVV1MmlESE9CUHk0UUtZUVluUXBMUT09IiwibWFjIjoiZDI1NGQ2YmQxY2FmYTQ1YWMwNzM1OTk2OTIyN2U2YTYwMGVlNWIzZWY0ZDVlMmY2YTMwMjAwZjk0Mjc3NWU3MCIsInRhZyI6IiJ9','2026-11-04 20:32:34','2026-10-28 20:43:34',1,'2026-10-28 20:32:34','2026-10-28 20:43:34'),(81,288,NULL,1,'eyJpdiI6ImNKb1AzMEd4bnRMQTd5eFU0d3ZXWWc9PSIsInZhbHVlIjoiS2VOcW5UdUx2MmRycUpvUzBUK2FRQT09IiwibWFjIjoiMzdlNjI3ZGRjOWYxNjI4YjRmM2NkOTcxZjI2MjNlZjY1NjY5YWEwNmRlNThjZjBkYTc5NjQ1OTg0ODRhYzY5MCIsInRhZyI6IiJ9','2026-11-04 20:32:35','2026-10-28 20:43:34',1,'2026-10-28 20:32:35','2026-10-28 20:43:34'),(82,289,NULL,1,'eyJpdiI6Imt0YTg0eTdHRFBwa3V3bFY0c1RQYVE9PSIsInZhbHVlIjoiZnlMWnRlZlJmUUNTa1NKSnVuMlUrZz09IiwibWFjIjoiODU1NmQ2ZjM5NWUyZTI5NjJmZGU2NTUyNDkxNDRhZGYzZmNjY2QzZDk2YTRjM2JkYmRlYmQ0OTM1NWZkYjVkMiIsInRhZyI6IiJ9','2026-11-04 20:32:36','2026-10-28 20:43:34',1,'2026-10-28 20:32:36','2026-10-28 20:43:34'),(83,290,NULL,1,'eyJpdiI6IkozQWRLdVVoT2J2ZzJBSDh0UUU3VEE9PSIsInZhbHVlIjoiVDlTWDJ4YnREZVMyeTUxQ3VQY3JnZz09IiwibWFjIjoiNTdkYjc0NDMxNDc0NDY3NjZmOTRlNmI3NmY2MDg5NmJhOTE1YzY4MzY4NGI4ODE3NzQ0Zjg3N2NlNzg4Y2IwMiIsInRhZyI6IiJ9','2026-11-04 20:32:36','2026-10-28 20:43:34',1,'2026-10-28 20:32:36','2026-10-28 20:43:34'),(84,291,NULL,1,'eyJpdiI6IkRsYjIzV0dwRkFsMUYvL0ZzK2tUMFE9PSIsInZhbHVlIjoicnpaMGZSSS90SEUvR2NMbkRuOGJ1UT09IiwibWFjIjoiMmEzYzc5MTBiNTI4ZmY0ZDU5ODdmZjMxMDUyYTM1OTlmNjgwYmVlYjI5YjRiZmU0MDdkY2VjZWQ4NzBhYTM0ZCIsInRhZyI6IiJ9','2026-11-04 20:32:37','2026-10-28 20:43:34',1,'2026-10-28 20:32:37','2026-10-28 20:43:34'),(85,292,NULL,1,'eyJpdiI6IjFJUTF0R0gwcHV1eGJyWjJ0UTNudVE9PSIsInZhbHVlIjoiWUJqTTVXSjc4SE1MdXFaa1JOUlR0QT09IiwibWFjIjoiYzgwM2I1MmEzMWE0OWNlYjI3NmNiNGZjNWRiOWE5ZTM3YmNhMTI1MGMxNmQ1ODYyMDk2YzUyODA2YzNkYjhlYiIsInRhZyI6IiJ9','2026-11-04 20:32:38','2026-10-28 20:43:34',1,'2026-10-28 20:32:38','2026-10-28 20:43:34'),(86,293,NULL,1,'eyJpdiI6IlNMQkVyNEIrWHAxbkp5QTE3bVp2Qmc9PSIsInZhbHVlIjoidHp4WHhQeXp2RVJuaHdKblYxUEdLQT09IiwibWFjIjoiMGQxZWYzY2JmNDFmMjNhYzdlODA5ZTZjNGQ2ZDgyOTRhNDUxNjA2MTk0ZmRlMjhlMTdhMjg3MTg0NWJhNjFmYSIsInRhZyI6IiJ9','2026-11-04 20:32:40','2026-10-28 20:43:34',1,'2026-10-28 20:32:40','2026-10-28 20:43:34'),(87,294,NULL,1,'eyJpdiI6ImtRbS9Bang1bkdkeTl0WmpTQ0NRN0E9PSIsInZhbHVlIjoiT2dhNGFTSGxUdDJaMWsrM0lvUHhydz09IiwibWFjIjoiYWU1MDE3M2QyNTQ3NjYzYzk2MGRiNDU4ZjVlZmUyN2JjNDI4MWU1Yzc2M2U4MzMzZjFlOGJlNTIzZWE3YWU4MiIsInRhZyI6IiJ9','2026-11-04 20:32:41','2026-10-28 20:43:34',1,'2026-10-28 20:32:41','2026-10-28 20:43:34'),(88,295,NULL,1,'eyJpdiI6InpaMkVWZDRDUkY0a3BBaWUzTzRSbHc9PSIsInZhbHVlIjoiQ3lQRUFGSE5VK2kxWWZkWjc4UlJoZz09IiwibWFjIjoiN2JjNzBjMjdmOGU2Nzk5ZjJjNDQ5MDA4Njg3ZDVhNTQ4NTU0M2ExMDM1MWMzMWJhODdlNGYyYWM5OTM2ZWJjMyIsInRhZyI6IiJ9','2026-11-04 20:32:42','2026-10-28 20:43:34',1,'2026-10-28 20:32:42','2026-10-28 20:43:34'),(89,296,NULL,1,'eyJpdiI6IklYcW5yNkEzWVpidGJqNFJ2YU1sdlE9PSIsInZhbHVlIjoiVUxCeGN4WStEVE8xeWFRbnR5SXBYZz09IiwibWFjIjoiNDBiYzViODM5YzdjMjk2MmQ1ZGVhMTQ4MWJkZmZiMGIzZjkyNGYyYjA2NzMzNjAwOTBlOWI1NWY5ZjViYTc4ZSIsInRhZyI6IiJ9','2026-11-04 20:32:43','2026-10-28 20:43:34',1,'2026-10-28 20:32:43','2026-10-28 20:43:34'),(90,297,NULL,1,'eyJpdiI6IjF4dXFid2ZkdGVzQkJQYXVqZEg3cVE9PSIsInZhbHVlIjoiNjBZUlpiYnY4TWRIZGVTMHJKc2o1Zz09IiwibWFjIjoiMjBmZmE3OTVlMGZlNDFiOWFkZDhmZjZkMjMwN2YyNzlhYzQ0NTNkYmQ3NmIyOWMxMjE2NTg4ZGM0YWFiM2MwNiIsInRhZyI6IiJ9','2026-11-04 20:32:44','2026-10-28 20:43:34',1,'2026-10-28 20:32:44','2026-10-28 20:43:34'),(91,298,NULL,1,'eyJpdiI6IitIOFJiaUt0dlBXL1pQTmg3Q3o0M1E9PSIsInZhbHVlIjoiV25tZHorRTNrN25tSHZSTjB6bFVZZz09IiwibWFjIjoiYjk0MzJjNDRkZDFhM2NkNzg5ODVkOGEyYmU5MzViOTZhNmVmNTdjMjQ3MmU2YmNlY2YwN2RkZjc1Y2M4Mjg2NSIsInRhZyI6IiJ9','2026-11-04 20:32:45','2026-10-28 20:43:34',1,'2026-10-28 20:32:45','2026-10-28 20:43:34'),(92,299,NULL,1,'eyJpdiI6IklzU1lKMDNqL1I0anJtT0ZPRDlUMnc9PSIsInZhbHVlIjoiYzAyRXk3QXEzcHh6b2lrNTladnl5UT09IiwibWFjIjoiMDI4N2JjMDEzZDQ4YjA2MTQxOWQyZTlkZjA0MThmZTViMjkzNjhlZjliYTI3OWZlMWYwMzYyMWU3Nzc4YWE0NSIsInRhZyI6IiJ9','2026-11-04 20:32:46','2026-10-28 20:43:34',1,'2026-10-28 20:32:46','2026-10-28 20:43:34'),(93,300,NULL,1,'eyJpdiI6Im91R3pSUms2R1oyVU5TVWRLVGRpTUE9PSIsInZhbHVlIjoiUUhuempBcHA5YnVNUFpya0hoN0JDZz09IiwibWFjIjoiNzQ5NTg0ZDQ1OTQ5N2Y5YjZhNTMyNDYyOTgzMzE2ZDBjZGFlOWU0ZTBjMTMxMDQ1NzY1ZWJhNDA5ZGZkZTI3ZiIsInRhZyI6IiJ9','2026-11-04 20:32:47','2026-10-28 20:43:34',1,'2026-10-28 20:32:47','2026-10-28 20:43:34'),(94,301,NULL,1,'eyJpdiI6IlRxMnh5TitnVVRMZlJuRTRHbTFTeUE9PSIsInZhbHVlIjoibHJyaFI0czI2UUhiQ2tVZlMzV3NvZz09IiwibWFjIjoiZmY3OTBhNzM2NDBlMmZhMjAxNWJkNWMyYmNiZWMyODRlZTQ2NTI4NDg1MjcyMzYwNmNjZTY2MDc0ZTY5YjVmYyIsInRhZyI6IiJ9','2026-11-04 20:32:47','2026-10-28 20:43:34',1,'2026-10-28 20:32:47','2026-10-28 20:43:34'),(95,302,NULL,1,'eyJpdiI6InNZMXREckh0MUh2OEgrOEJkeVkveWc9PSIsInZhbHVlIjoicTZtOXJ5R0tZQ2xBWW9YQ0RnYXE3dz09IiwibWFjIjoiYmNiOTE3MGVlYTgxNjMwOWVjMjYyMGE4NmNjOTM4ZTA0N2VkZmE4YzkyYzE0NmVlYjNmMGUwNzc1Yjk5OGQzYyIsInRhZyI6IiJ9','2026-11-04 20:32:48','2026-10-28 20:43:34',1,'2026-10-28 20:32:48','2026-10-28 20:43:34'),(96,303,NULL,1,'eyJpdiI6IkNxdDRRZWJjUFQzaVpnYXdYZ3hHeUE9PSIsInZhbHVlIjoiSWpwd1BWcjdqMUdRQnkvcHlRNHdZQT09IiwibWFjIjoiODM2ZTRmZGRiOGRjMzRjNzBhMmQxZWQ1MWUyMjIyMjE2YTk5N2ZlYzBiM2UxNDZiYTE1YTUwY2M3YzlmZjczZCIsInRhZyI6IiJ9','2026-11-04 20:32:49','2026-10-28 20:43:34',1,'2026-10-28 20:32:49','2026-10-28 20:43:34'),(97,304,NULL,1,'eyJpdiI6IkJxVDFmaXhsL0FKNTVxTnROY0I0clE9PSIsInZhbHVlIjoiT0ZTeEYydE95ak9nVFhTQ29uMnMwZz09IiwibWFjIjoiMTA2ZDdmNjMyMDE5Mjk0ZjNiOTdkNDkyMzVkMDgxOGI3OTUwNGM5MWFmMmFmZWY0OGU2NGJmMGRiN2VhZDU3MSIsInRhZyI6IiJ9','2026-11-04 20:32:50','2026-10-28 20:43:34',1,'2026-10-28 20:32:50','2026-10-28 20:43:34'),(98,305,NULL,1,'eyJpdiI6IjFCbE41ZE1QREs2Z1NUZlRqcDRxZXc9PSIsInZhbHVlIjoidVJDandPWldZUWpnK2dseGhKdExDQT09IiwibWFjIjoiOGE5OGY0MmVmMjNlZDgxMTg5NDIwZjNjZmUwN2NiYmVjZjA5ODE4YTZmMmFmZmJkMmRiNTI5NmRiMzE3NjdhNSIsInRhZyI6IiJ9','2026-11-04 20:32:51','2026-10-28 20:43:34',1,'2026-10-28 20:32:51','2026-10-28 20:43:34'),(99,306,NULL,1,'eyJpdiI6ImdoNTJNTGdaNGpiTTlYc3kyM2Fqenc9PSIsInZhbHVlIjoiYTdFTnBPWEJjckFWRkFPWTB6dnNiQT09IiwibWFjIjoiYmVlMTYxNDNjN2NmMzU2OGNkMmIyNjdhNDFhNDExNTE3ZWM4ODIxY2Y0Mzc5NTk0MDIzZjJkYzFmODU4ZGM3MSIsInRhZyI6IiJ9','2026-11-04 20:32:52','2026-10-28 20:43:34',1,'2026-10-28 20:32:52','2026-10-28 20:43:34'),(100,307,NULL,1,'eyJpdiI6IlBHbFVFcjg4cC9NaFRFZHVpUFl0SFE9PSIsInZhbHVlIjoicmNCVmlQK1hNdlFILytqZ3Q1d01SQT09IiwibWFjIjoiZDRmYmZlYTA0N2Q2YzkzZTIwZThiNjcxZWY4Y2U5MWJjOGZlN2ZlZGY4MDA4ZDJjMWE3ZWYwYWUyZjczN2RhYiIsInRhZyI6IiJ9','2026-11-04 20:32:53','2026-10-28 20:43:34',1,'2026-10-28 20:32:53','2026-10-28 20:43:34'),(101,308,NULL,1,'eyJpdiI6Ii9kb2NCS2JsS0N0TmkzWVZYZFhzU0E9PSIsInZhbHVlIjoiT2pJbnUzZS9aNlk2TUhaUUptSDVaUT09IiwibWFjIjoiMzM5YTdjNWFhN2I5NmZmMGEzYjhjMWYyNDY0Y2Q5MDI4NWU5OTVlNTVmNzJiY2Q4MDY5YzM4MGEwYzE3MGJiOSIsInRhZyI6IiJ9','2026-11-04 20:32:54','2026-10-28 20:43:34',1,'2026-10-28 20:32:54','2026-10-28 20:43:34'),(102,309,NULL,1,'eyJpdiI6IjgrMG56VDVhbE11dUhYd24wbmRYUlE9PSIsInZhbHVlIjoiVndtUXdxODlsM256NHMwbW1pdTU2Zz09IiwibWFjIjoiNGEyMGVmOWZkZmIyZGM3MzcyMGZiMDY2MGU4N2MxZWYxMDBiYzg1MTA0ZTY1M2JmNDQyMmE4YzZiN2IxYTEwNCIsInRhZyI6IiJ9','2026-11-04 20:32:55','2026-10-28 20:43:34',1,'2026-10-28 20:32:55','2026-10-28 20:43:34'),(103,310,NULL,1,'eyJpdiI6IkRNWGhXdzd4UDJKZGtaLzRaUE5WWGc9PSIsInZhbHVlIjoiblRsRlUrVzdzbS9VOFJRcjZnWmYwUT09IiwibWFjIjoiYjFhNmNjNzUwMDIyNGI4OTcxMWY1NzA5M2QyYjI3NWNjZGM4NDRmYzQ4YzJiZDJiMGJlNWNlZmY3NGVjYzA3ZCIsInRhZyI6IiJ9','2026-11-04 20:32:56','2026-10-28 20:43:34',1,'2026-10-28 20:32:56','2026-10-28 20:43:34'),(104,311,NULL,1,'eyJpdiI6ImFNUE10UHYvTy9HdXBIODlkMm5XRlE9PSIsInZhbHVlIjoiUFVDOVMrRElvUzlkYTFXNHdBRlVhQT09IiwibWFjIjoiNGU3OTUwOTQ1NzY0ZTk4MDNkOTUzNjQzOGMyOWU3OGQwYWU3NTE0N2VlODhhZjVmOWNlMDFhNGRiMzdkZGY1ZCIsInRhZyI6IiJ9','2026-11-04 20:32:57','2026-10-28 20:43:34',1,'2026-10-28 20:32:57','2026-10-28 20:43:34'),(105,312,NULL,1,'eyJpdiI6IkxwTzZ3SittVkptOWNUMytZNGw2dHc9PSIsInZhbHVlIjoiRVJ2UE5UQjBTWlBLVE1aYVNCS2twUT09IiwibWFjIjoiODY0MWRkMDdiYzFmODJiYmNiOTllNjA2ZjZkYWRlMWZhYjZmNDRjYzk5NjQzNzBkMTZmNjY2ZWNiNzgyNDQ1ZiIsInRhZyI6IiJ9','2026-11-04 20:32:58','2026-10-28 20:43:34',1,'2026-10-28 20:32:58','2026-10-28 20:43:34'),(106,313,NULL,1,'eyJpdiI6IlR0c0FjTXdNWmVtOHNwZUlGdVZNbHc9PSIsInZhbHVlIjoiY3FrV3R4VHNUeU9ibWphK200dVZzQT09IiwibWFjIjoiM2ZjYzQwYTdjNDVlYjExM2ViZTU1MGVlNjczNGE5NzVjMjM4ZDA1ZWNlMGE2Y2JhMDRhYTNjMDFjZDg2YTQ5NiIsInRhZyI6IiJ9','2026-11-04 20:33:00','2026-10-28 20:43:34',1,'2026-10-28 20:33:00','2026-10-28 20:43:34'),(107,314,NULL,1,'eyJpdiI6ImpmanNwYjQ1ait3aU5QWXEwYk5mV2c9PSIsInZhbHVlIjoiSmU2SnVtK0l1ak5BUjcxaFpaYmZCQT09IiwibWFjIjoiNTY3YTEyYTU2OGJkOTVkYjcwODdhMTdmYWVhNDM1N2ZhZTVjMGZlZjhlNmYzY2ExYTlhZDUyMzI2OWM5YTNjNCIsInRhZyI6IiJ9','2026-11-04 20:33:01','2026-10-28 20:43:34',1,'2026-10-28 20:33:01','2026-10-28 20:43:34'),(108,315,NULL,1,'eyJpdiI6IllyTlZVN00vR1ZXZ2hMbmpmeURmN1E9PSIsInZhbHVlIjoiVlk1endIOHpHT05iOHFkOFpybDZyUT09IiwibWFjIjoiOTM1NjNlOThhZmYyODM2YjM1Y2VkNjY3NWVmMWU4OTdjNTczNGQyOWE0NGJhMDgxZjc4YzZhOGI3YzQ1NzMyZiIsInRhZyI6IiJ9','2026-11-04 20:33:02','2026-10-28 20:43:34',1,'2026-10-28 20:33:02','2026-10-28 20:43:34'),(109,316,NULL,1,'eyJpdiI6InVMZ0ZjMzd5eCtPbHNBSE9nckgxWEE9PSIsInZhbHVlIjoiZGxlZXpYUFJBMDRQMW10VGYxRmNsZz09IiwibWFjIjoiODBhODYwMDEyOTVmNDlhZTIyNDUwMTc1ZTdiYmE1NzY3Nzg0ZjE5MTA1NDE1NzQ4YTFkODIzYjRlYWIwOTRjYyIsInRhZyI6IiJ9','2026-11-04 20:33:03','2026-10-28 20:43:34',1,'2026-10-28 20:33:03','2026-10-28 20:43:34'),(110,317,NULL,1,'eyJpdiI6IkdzendmdGpGVGJIKys4YmQyM0pxeEE9PSIsInZhbHVlIjoiaG01d3ozNjJBYzFvMFI3VmNZOHNqUT09IiwibWFjIjoiZTc3NDAzMzRkM2EyNjQ2MmMxNWQ3YmYzZWQzYTBiY2UwZjM4ZWIxNTBhYzllMmY5Yzg5NmFjYWNmMDczMGI4MiIsInRhZyI6IiJ9','2026-11-04 20:33:04','2026-10-28 20:43:34',1,'2026-10-28 20:33:04','2026-10-28 20:43:34'),(111,318,NULL,1,'eyJpdiI6InlSMWNJd3NCaUIrTW5EMjNzZHBxWFE9PSIsInZhbHVlIjoickUwL2FGbitWRCtFamtXZTFTLzhldz09IiwibWFjIjoiOWE1MzY0MjVlMjM2YzcyZGUxNmMzMmNhN2NmMWZmZGEyY2RkYTU2NmNjMjIwNjk2ODFmNmJjNWY2Yzk1YjBmYyIsInRhZyI6IiJ9','2026-11-04 20:33:05','2026-10-28 20:43:34',1,'2026-10-28 20:33:05','2026-10-28 20:43:34'),(112,319,NULL,1,'eyJpdiI6Ilk3T0ZLMW15OE9vNS95YS8wL0p4Q1E9PSIsInZhbHVlIjoiOHE5dWVQbW9QakNvcVV6dFhOMjVEUT09IiwibWFjIjoiZDgwNWM5ODM5ODQxNTFlZDkxM2Q1NjM1NWIxYTFiMjIxYWI1YTQyMDE3NTE1Y2ZkMDg5N2E1MzBjMDQ2Zjk3MCIsInRhZyI6IiJ9','2026-11-04 20:33:06','2026-10-28 20:43:34',1,'2026-10-28 20:33:06','2026-10-28 20:43:34'),(113,320,NULL,1,'eyJpdiI6IkU4b08vN2RxaG1UdHFxa0M2bmR5RXc9PSIsInZhbHVlIjoiZ1E4Y2lxZTlIWkRzTnFENEJ1K091dz09IiwibWFjIjoiODczYmYyM2Y2MWQzZGM1MTVkNjNkZjQ3MTUyMzkyOGM3MDJlZDkyMzk1MDU0MTdjYWU0MDkzM2I0OTZlYmFiYiIsInRhZyI6IiJ9','2026-11-04 20:33:07','2026-10-28 20:43:34',1,'2026-10-28 20:33:07','2026-10-28 20:43:34'),(114,321,NULL,1,'eyJpdiI6IlJIUnlacmlSWG5yTkV2eVQraGZabFE9PSIsInZhbHVlIjoiSWRYT2kxdW1vdXhzNEJkV3c2R2lQdz09IiwibWFjIjoiMGEwNDk1ZDQ0ZmJkZmFjYjQ3MzY0NDcwMmFjMDFjYmExODMzMmYyNDcwMzE1MDJiNjM1NWIxODVlNzIyYWZmNyIsInRhZyI6IiJ9','2026-11-04 20:33:09','2026-10-28 20:43:34',1,'2026-10-28 20:33:09','2026-10-28 20:43:34'),(115,322,NULL,1,'eyJpdiI6IkI5akhvelIzTmJtT3ovVW93by9oUXc9PSIsInZhbHVlIjoiTDF1YmVHR2pGVkJKMVIvRjRLckJkQT09IiwibWFjIjoiOGY0NDYwMTliYTM5NDgzYjc0MDkxY2QxYTQ5MDZkNmZmZTJkYzczNDRkNzdiNzQyYzA4Y2I0NTI1MGViN2U1NyIsInRhZyI6IiJ9','2026-11-04 20:33:10','2026-10-28 20:43:34',1,'2026-10-28 20:33:10','2026-10-28 20:43:34'),(116,323,NULL,1,'eyJpdiI6ImFNdElUaGtwTnp3KzlzVWFRbkRja0E9PSIsInZhbHVlIjoibFV5ZktvOVVJY1pVc0JnOTlrdFB0QT09IiwibWFjIjoiMDY3NzFiZjlhOGEyMzdmMDE3NDUwMDMzM2FmZjdkMzJhNTU2YzBhZjNlZDVjNTM0ZWMzYzBhMmMzNzhmYTU1MCIsInRhZyI6IiJ9','2026-11-04 20:33:11','2026-10-28 20:43:34',1,'2026-10-28 20:33:11','2026-10-28 20:43:34'),(117,324,NULL,1,'eyJpdiI6Im1LT25tcW9qOXJVVksxOGk3SjN6Q0E9PSIsInZhbHVlIjoiZXd4Sm1PdWxVNnJDNU5sTWVVQTN0QT09IiwibWFjIjoiYTY5YTZiNjM4NDQ4YmRmYTQ5ZGI1YjllNTkzYTk0YzM2ZmM4MTVlYWVhZDU0MThmMjIwOTFlZGZjZDcwN2E2NiIsInRhZyI6IiJ9','2026-11-04 20:33:12','2026-10-28 20:43:34',1,'2026-10-28 20:33:12','2026-10-28 20:43:34'),(118,325,NULL,1,'eyJpdiI6IjcrVFFxUjNLQzJRdTdvZXVNbmk2SHc9PSIsInZhbHVlIjoiYTRERHpkUkV6c3M1K2Q0S2FtaGlYUT09IiwibWFjIjoiZTdiNjcxY2RiMGY3MGJkM2ViZmYxZTE3ZTE5NmEwODM4YzNkMGI4NDk3ZTQyMWUwNmNhYzc4YmUyNjJjOWVmMCIsInRhZyI6IiJ9','2026-11-04 20:33:13','2026-10-28 20:43:34',1,'2026-10-28 20:33:13','2026-10-28 20:43:34'),(119,326,NULL,1,'eyJpdiI6IjlaZlE3eExPZzJHWkJXcVdMaEJ4MWc9PSIsInZhbHVlIjoiR01pYUVrSkk4WWtBUyt0bnVlZU9zZz09IiwibWFjIjoiNWNiNzEwZGRkNTg3MWFlMmQ0NzQ1YTU3OTY2ZTAzZjUyOGMzMTZiOTRiMjI0MTgzODA2N2IzZDViNjBjYjI4NSIsInRhZyI6IiJ9','2026-11-04 20:33:14','2026-10-28 20:43:34',1,'2026-10-28 20:33:14','2026-10-28 20:43:34'),(120,327,NULL,1,'eyJpdiI6ImVtclAzSmREcDc5bUhNcEo3QkpUOEE9PSIsInZhbHVlIjoiZFBQQytQSGxIY0tFVHNtNUtIZW80Zz09IiwibWFjIjoiNWExYjBjMTIxNjFkOTQ5ZDY0ZTkwOWE2YWFlZThmMjA0M2ZkMmU5MjYzYmJhZGFhYjYzNDI3Y2JiNTg4M2U3YyIsInRhZyI6IiJ9','2026-11-04 20:33:15','2026-10-28 20:43:34',1,'2026-10-28 20:33:15','2026-10-28 20:43:34'),(121,328,NULL,1,'eyJpdiI6IjdmcGI3M2psdkVyM1hhVDA1SkExZHc9PSIsInZhbHVlIjoiN1BpTk05c2lHbEdVdlFtTktNQWw5dz09IiwibWFjIjoiNmYyZDIzNjBiYmQ3MzU2OTQ1ZGUzNmNhNmQ1NWM5ZDU0MGM1OGUxYjEwNDE2ZWJmNTUxNWQ5YTNmNDIwNTU3OSIsInRhZyI6IiJ9','2026-11-04 20:33:16','2026-10-28 20:43:34',1,'2026-10-28 20:33:16','2026-10-28 20:43:34'),(122,329,NULL,1,'eyJpdiI6Ii8vYW53bjlNNVd0bk9QdXpGUzVVTEE9PSIsInZhbHVlIjoiNDQ0YzdjdmV0RlVJdXREaUc4R1Vrdz09IiwibWFjIjoiYmJiNTIyZGRjYmJmMzY2Mjg5ODg5YTM3ZWExMGY2N2NjY2IwN2Q1ODRhNGUwYjVmNGVlNGQ5NTVhNmMwMWJlMyIsInRhZyI6IiJ9','2026-11-04 20:33:17','2026-10-28 20:43:34',1,'2026-10-28 20:33:17','2026-10-28 20:43:34'),(123,330,NULL,1,'eyJpdiI6InFaMURHRkhUMUlXT3oreDdrWFFWd0E9PSIsInZhbHVlIjoiVWlnaUQxVnVhaVltNHlVQU1DaGlWUT09IiwibWFjIjoiOWQzYWNmNjhkMTI1Yzc4M2IwNGFiYWI0M2Q0NWY0NmYwYTUyODMyZWViZjhmZGE1ZWRjYzlkMDQyZGUyMWVmNyIsInRhZyI6IiJ9','2026-11-04 20:33:18','2026-10-28 20:43:34',1,'2026-10-28 20:33:18','2026-10-28 20:43:34'),(124,331,NULL,1,'eyJpdiI6ImxRYVJpbXBJQ2c3eFZ6V3E3N0FNVVE9PSIsInZhbHVlIjoiMTdGQStMQ2VrVUVGOXVFL1A3ZitKUT09IiwibWFjIjoiMTU1OTRjOTE1M2ZkNDY1MzA1MmRkYTZlNTg2ZmNjNmI1NDUxZGQ4OWNlNWJmMmIwMDAwMjI3Nzc5NTk5NTQ2MyIsInRhZyI6IiJ9','2026-11-04 20:33:19','2026-10-28 20:43:34',1,'2026-10-28 20:33:19','2026-10-28 20:43:34'),(125,332,NULL,1,'eyJpdiI6ImhmUkovNlZLU00wNUVvSFpicTNMQXc9PSIsInZhbHVlIjoiT3grNDZyU0gvKzNvM1ZvRENIYkpJUT09IiwibWFjIjoiNWU0NmJhZTQwZDI4NjE1ZjgxN2MzNGY1YmJhNzIzNTJkYzViZTk5MWU4MGQxYTFkM2ViMGFhMWM5YTIyOGE4NiIsInRhZyI6IiJ9','2026-11-04 20:33:19','2026-10-28 20:43:34',1,'2026-10-28 20:33:19','2026-10-28 20:43:34'),(126,333,NULL,1,'eyJpdiI6IkpZc3pzSlltV1ZqYXpYVHA4NnQwV2c9PSIsInZhbHVlIjoiUTRMMXNOaFJyR2p2MXV5aS9PenRDQT09IiwibWFjIjoiNWQxZDljM2M4YTc0MmMzYjI5ZmEyODk1OTM3NDlkYzIwZGE2ZTEwZWNjMDk0MmEwOTIwZGRhMTQwNTgwZjAxMiIsInRhZyI6IiJ9','2026-11-04 20:33:20','2026-10-28 20:43:34',1,'2026-10-28 20:33:20','2026-10-28 20:43:34'),(127,334,NULL,1,'eyJpdiI6IkMzUVVNb3IrRVRlVVlxbW5JdXgwYmc9PSIsInZhbHVlIjoiV2xXMVhoOVM2azdaQnZUY1pTS3RwZz09IiwibWFjIjoiNjAzZTZiZWUyMDE0MmY5NDVlZmNmMDJjYTA1NWE2YjNhYThhMzRmZjBjNDQ1ZmJjYjJkNDBkY2NhNTM4NTI3NSIsInRhZyI6IiJ9','2026-11-04 20:33:22','2026-10-28 20:43:34',1,'2026-10-28 20:33:22','2026-10-28 20:43:34'),(128,335,NULL,1,'eyJpdiI6IkRzOGpFQVF1MkxkL3g0VHA1ZmwzTWc9PSIsInZhbHVlIjoiTVVDaDBXWTU2eGIxL21zTkViL1llQT09IiwibWFjIjoiMzUyNzc5ZTQyY2MwNjFlMjliNzRjMjdmYjgxOTZiMzY3YjZiNzY3ZmIwOTJjNTFhYThiMjEwNWI5OTY2NWU4YSIsInRhZyI6IiJ9','2026-11-04 20:33:23','2026-10-28 20:43:34',1,'2026-10-28 20:33:23','2026-10-28 20:43:34'),(129,336,NULL,1,'eyJpdiI6InNLT2o5cFQ2NDc4VmFvakd1ZGxoanc9PSIsInZhbHVlIjoiV3Q4VFU5SWREbHBKUmpFMzdSR2ZaZz09IiwibWFjIjoiYmRmZDYyMzkxZTE0YTc4OGVhMjJmM2ZjNzg5YThjNDYwNTFlZDc2YWE5ODNmZjQ5OTA2YWMyMWJlNzk2OWI4YyIsInRhZyI6IiJ9','2026-11-04 20:33:24','2026-10-28 20:43:34',1,'2026-10-28 20:33:24','2026-10-28 20:43:34'),(130,337,NULL,1,'eyJpdiI6InMzRFJJRExmUGRZYlZDRC9rZzMxWXc9PSIsInZhbHVlIjoiK1NESmlWQk1SZGgwbHVNR05lUmtxZz09IiwibWFjIjoiNmViZTMwODEwZDdlNmUxYTIwZTJhMjU0Nzc1ZTQyNWIyODUwYWFkMjFiOWUyYjRmNThmYmJkOTlhN2U5NTRiYyIsInRhZyI6IiJ9','2026-11-04 20:33:25','2026-10-28 20:43:34',1,'2026-10-28 20:33:25','2026-10-28 20:43:34'),(131,338,NULL,1,'eyJpdiI6IklkRnpUVEpDeDh2aGJyK3Zkcm5lY3c9PSIsInZhbHVlIjoibWNTVVB1c3hHM2tSdExzM3hHK3NRdz09IiwibWFjIjoiNDE0OTYzOTUzOWIxZWVmMDcwMjc1NDgwNjZkOGZlMGYzNzQyYmNiMTA4ZTQyNWU0YWEyMmMzZTQyOGZjYTVlMiIsInRhZyI6IiJ9','2026-11-04 20:33:26','2026-10-28 20:43:34',1,'2026-10-28 20:33:26','2026-10-28 20:43:34'),(132,339,NULL,1,'eyJpdiI6IjMxUEdJMXZmTlV0c1ZoUFFsM2dHckE9PSIsInZhbHVlIjoiSWp4S2Z5VkljUVkxR1VaWGZGK1I0Zz09IiwibWFjIjoiMmY5YjY4ZThmZGRmZmM0N2Y3MGIxZjczNmQ2YjdhNWU4MzIwOWVjMTUwNTI4YzBlYzM4OTVjZmE5MmMwOTMwMCIsInRhZyI6IiJ9','2026-11-04 20:33:27','2026-10-28 20:43:34',1,'2026-10-28 20:33:27','2026-10-28 20:43:34'),(133,340,NULL,1,'eyJpdiI6InR0QlFLYXM4cjNmUjd5dFBFbVZJY2c9PSIsInZhbHVlIjoiY2krR0psb2FCenEyNk54VS9PWUg4dz09IiwibWFjIjoiMDIwY2RhN2MzZDcyZjIwMmQ1OTM4ODA4MzRkYmU1NDA3ZjFmYzFiZThmNWE0NGJkN2E5Yzk0YmJlOGM3MzI4NCIsInRhZyI6IiJ9','2026-11-04 20:33:28','2026-10-28 20:43:34',1,'2026-10-28 20:33:28','2026-10-28 20:43:34'),(134,341,NULL,1,'eyJpdiI6Im1lQVFmanFNS2tWVlpWS3VINDFNZmc9PSIsInZhbHVlIjoidTIrYVQyYlZyWlNMSHYvcTZBd05Idz09IiwibWFjIjoiZTgyNzExZmZiMjEzYjhlZTZmYjdjYzhmYTYwNDQxZTU0MzhkMDNjOTYzMWFiY2UwNzQ1NWUzMDMxYjczNDExYiIsInRhZyI6IiJ9','2026-11-04 20:33:29','2026-10-28 20:43:34',1,'2026-10-28 20:33:29','2026-10-28 20:43:34'),(135,342,NULL,1,'eyJpdiI6ImREckZpaHlYZGhrWG1GSSs3RG1vQ3c9PSIsInZhbHVlIjoiUk9tTlZNdENYVDA5c1YyWmZoTEpHUT09IiwibWFjIjoiN2ZjYTFmN2YwMzhlYWRjNjI3NDhiN2Y0YmJlODk2MDQ1NWI0MzUyZDQ0YzM5Y2NkMzliNGU0M2ZjYjhiNjQ1ZSIsInRhZyI6IiJ9','2026-11-04 20:33:30','2026-10-28 20:43:34',1,'2026-10-28 20:33:30','2026-10-28 20:43:34'),(136,343,NULL,1,'eyJpdiI6IldQb3lwekFIamRaVUxHNGhkV3YrVVE9PSIsInZhbHVlIjoiNFRMMXFCaExlWndSV21rbkxtRWRndz09IiwibWFjIjoiNjMxNjQ5MDBlODE4ZjhmOGMyOTZmY2ZlYTFmZWIyNTBhMjE1ZTExNDIzY2EwNmI2ODA2YTMyMWFhOWRjNmZlNyIsInRhZyI6IiJ9','2026-11-04 20:33:31','2026-10-28 20:43:34',1,'2026-10-28 20:33:31','2026-10-28 20:43:34'),(137,344,NULL,1,'eyJpdiI6IjNvY0JVR3AxVkg1c0lDMSt2K2tIYWc9PSIsInZhbHVlIjoidmVuSnErQUhlMk5pSzM4aHdZSUF0Zz09IiwibWFjIjoiM2Q3OTFmNTY0ZDZiYWNjZjc0MDRmZmJlZDhiNGI3OTc4MTExZWMxZjhiYzJkMTBiYTdjYzE2MjkwOTUxMTZjYiIsInRhZyI6IiJ9','2026-11-04 20:33:32','2026-10-28 20:43:34',1,'2026-10-28 20:33:32','2026-10-28 20:43:34'),(138,345,NULL,1,'eyJpdiI6ImNVdkV1MGJPSVhzclFraWk0OEhaNEE9PSIsInZhbHVlIjoiNGpuL2pPK205eVlRY0tPWURUQmIxUT09IiwibWFjIjoiYTcxZDA5NzdjMTUyYmQzNjRkNDVlMjVhOWIzNTJiZGUxMTI3ZWFjNzgxYzQwNjBmZTY2NjU2ZWE5MWU5ZTFlYiIsInRhZyI6IiJ9','2026-11-04 20:33:33','2026-10-28 20:43:34',1,'2026-10-28 20:33:33','2026-10-28 20:43:34'),(139,346,NULL,1,'eyJpdiI6IkJib0V3Vkw1QTZJRTRsMnZHK3ZXMlE9PSIsInZhbHVlIjoiQXBsUmdoUUlDN2tGUW1oRnAxZ2ZEdz09IiwibWFjIjoiOWQwYWU0ZGI2YmQzYzdlOTI5ZDFjMTM1YTM2OGVkYWNhZWYzYzAzZDE0M2JiOWVmZjYxOTE2MjNmNWNlMWQ2OSIsInRhZyI6IiJ9','2026-11-04 20:33:34','2026-10-28 20:43:34',1,'2026-10-28 20:33:34','2026-10-28 20:43:34'),(140,347,NULL,1,'eyJpdiI6Imd0clI1aXlJbW93MGU0VEdBQlJvTWc9PSIsInZhbHVlIjoiTGE4SHBONGd4OU1makFNd1pST1AyUT09IiwibWFjIjoiMTVjY2YxYTUzZjEzMGYwZGQxNzQwNDlkZTFlN2JkYmYzNDAyM2QxOWJjMzU5MjU1ZDEyZjg5M2RmOTczZGQ5NyIsInRhZyI6IiJ9','2026-11-04 20:33:35','2026-10-28 20:43:34',1,'2026-10-28 20:33:35','2026-10-28 20:43:34'),(141,348,NULL,1,'eyJpdiI6Im5MQTljUm1Ka21mRlE2M0MwcmJzM1E9PSIsInZhbHVlIjoiMEVvN3JkS3JrVDFUODNJZ3pKQ05IUT09IiwibWFjIjoiNTMwZGZmODkxZDI1YTRjMzk1YmU0NTBjNTRkNmIxYWU5MzZlNDNiNjg1Y2IyNDVmYmJjNjVkNDgzYTAxMTE2MSIsInRhZyI6IiJ9','2026-11-04 20:33:37','2026-10-28 20:43:34',1,'2026-10-28 20:33:37','2026-10-28 20:43:34'),(142,349,NULL,1,'eyJpdiI6IkhsMHpucUdkL1VZZnNGWi80RExpT2c9PSIsInZhbHVlIjoiOFpiWG5CSHhSMmFTL1VndHdTTm9iQT09IiwibWFjIjoiZmJhZjdhNTExN2ViYWI5MGVlNTE1YWJhZGI0OGEyOThkMDkwYjU1ZDIxYTdkMjg3ZjgyOTM5Y2E4MTM2ZmU3YiIsInRhZyI6IiJ9','2026-11-04 20:33:37','2026-10-28 20:43:34',1,'2026-10-28 20:33:37','2026-10-28 20:43:34'),(143,350,NULL,1,'eyJpdiI6IlEybUh2NkV4NWVWRFBQRzczemZiUWc9PSIsInZhbHVlIjoiQ1IwaS9jc2NYN0d0b1RvRmY1YWRSdz09IiwibWFjIjoiMWIzMmY1MjkzMzQyN2ExYzBhNDMyMzUyZTFhZjY5YTQ5OTBhNThjZWQxZmZlYjQ0MWZjZjZhODc3ZDc2MTdmZiIsInRhZyI6IiJ9','2026-11-04 20:33:39','2026-10-28 20:43:34',1,'2026-10-28 20:33:39','2026-10-28 20:43:34'),(144,351,NULL,1,'eyJpdiI6ImNScm85MlR5YVI2RUNETDNPSDJIcGc9PSIsInZhbHVlIjoiMVVTbDRDNGJtdWtFWnFrWlQrb1VaQT09IiwibWFjIjoiOGI4ODc2Mzk3MmRhYzkyOTNmMGQ3ODQ4NTg5ZDM3M2IyMzhkMjUzMzU4MjBlZTM4MWZiMmYwNDU1NmRmYjk2NCIsInRhZyI6IiJ9','2026-11-04 20:33:40','2026-10-28 20:43:34',1,'2026-10-28 20:33:40','2026-10-28 20:43:34'),(145,352,NULL,1,'eyJpdiI6IlFLcXk5V2ptTVU5bzN1QUJ0ZGhoZ0E9PSIsInZhbHVlIjoiYUxuSjdLL2JWbVFpRDNZRmoyM1Zkdz09IiwibWFjIjoiNDBkNGNhNWRlMzYwODJhNjM2NjY1MTA2OGQ0MjRhYTMzZjExNzM2YmYxNTJlMTkzNjI2NWNmZjNiYTEwNTc5NyIsInRhZyI6IiJ9','2026-11-04 20:33:41','2026-10-28 20:43:34',1,'2026-10-28 20:33:41','2026-10-28 20:43:34'),(146,353,NULL,1,'eyJpdiI6ImRLYmowNzNmVkswc0lyU1VtM1VwbGc9PSIsInZhbHVlIjoialFad3VxVlRkVURYOHJ0TEdnSFJSZz09IiwibWFjIjoiZDFhMTVhODIxZjc0MDRkNDRjMTkxNWEwNTFkMzExMmMwOTk2MmFmYzg3ZDFhY2IyMzM5MGMzZGE0ZDQ5MDUzYSIsInRhZyI6IiJ9','2026-11-04 20:33:41','2026-10-28 20:43:34',1,'2026-10-28 20:33:41','2026-10-28 20:43:34'),(147,354,NULL,1,'eyJpdiI6Iit3NktLWm5YdXpWdHVpQWYrV1ZOWmc9PSIsInZhbHVlIjoibFhNSmNOSkVXNjVQd01DNFlHVlZPdz09IiwibWFjIjoiMzNmNjU2MmRkYWVkY2MyZjkzZjg2ZTFmZTc2ODYxZmFkNjE4MWQwNWQ4YzcyZTViYTlhNDBlZmJhODYxYmZlMSIsInRhZyI6IiJ9','2026-11-04 20:33:42','2026-10-28 20:43:34',1,'2026-10-28 20:33:42','2026-10-28 20:43:34'),(148,355,NULL,1,'eyJpdiI6IlhxVjFDOXlsYmJIVHU0OXUxeitDNVE9PSIsInZhbHVlIjoiRUtJNTFIY3FxUWhJdUFzVWdhYUtSUT09IiwibWFjIjoiODZlZjIyNTg0YzZiMzY4NDA0MDRhYjZiMDAxMjYzYjMwNmRiYTQwYWFlMDVlODM5MDYyMmQxMmNmYTU5ODAxOSIsInRhZyI6IiJ9','2026-11-04 20:33:43','2026-10-28 20:43:34',1,'2026-10-28 20:33:43','2026-10-28 20:43:34'),(149,356,NULL,1,'eyJpdiI6IlozQ1Fya2d6VEdDOVBZY3dXZWR3d2c9PSIsInZhbHVlIjoidjVpa3gvUE5Fcm1NdzFIbWpuRHZzZz09IiwibWFjIjoiNjM4NTlhOTBmNzAzNzFmYjFhMGQwODg0ZmY4NjRkNTEzZTFmZWJlYzRjYjg0ZDIyMzczNTdiNmQ1MjY2ZDg4NCIsInRhZyI6IiJ9','2026-11-04 20:33:44','2026-10-28 20:43:34',1,'2026-10-28 20:33:44','2026-10-28 20:43:34'),(150,357,NULL,1,'eyJpdiI6ImZUTjNWaWhqR1JGa011YTdzM3FDZFE9PSIsInZhbHVlIjoidFA5U2hYZGk5dkpIY3ZyOU9YQnI4QT09IiwibWFjIjoiMDhkNzc0YTdhNGUzNjAzYzQ2NzY1YmFlOWE2NGVkYThhYTFhNmQ1NDQ5MjZkOWVjMjBjYTc2OWI4YWU5MjVhMyIsInRhZyI6IiJ9','2026-11-04 20:33:45','2026-10-28 20:43:34',1,'2026-10-28 20:33:45','2026-10-28 20:43:34'),(151,358,NULL,1,'eyJpdiI6IlBIQ0NzQk4vbGpJVlF5MDNIWTRZVHc9PSIsInZhbHVlIjoicjVPOERCUkVJR2cvaGtVY1NxZjVQZz09IiwibWFjIjoiMjYxNWI5M2RjMjQ1YmU4ODRjYjE2ODZiMDQxMDYxZmYxYTU3MmU2YzhjNzUxY2U3MTM2MThlMzVjZmFjODUzNyIsInRhZyI6IiJ9','2026-11-04 20:33:46','2026-10-28 20:43:34',1,'2026-10-28 20:33:46','2026-10-28 20:43:34'),(152,359,NULL,1,'eyJpdiI6IkhzQ0ZZYlh5amJxd240N2hnQ3hjVGc9PSIsInZhbHVlIjoiWUl4ZklnU3JVRWRHWiswdGJCYitLUT09IiwibWFjIjoiMTA5NjVlNjNhYjAzNDc0NzI2N2YwOWZmMzI2Y2I3NmZhZjYzMTFiYmVlMTc4OTYwMjM4Y2Y2NDU5OWIwODM0OCIsInRhZyI6IiJ9','2026-11-04 20:33:47','2026-10-28 20:43:34',1,'2026-10-28 20:33:47','2026-10-28 20:43:34'),(153,360,NULL,1,'eyJpdiI6IjVHTzlQWmovNStuUDdTbGozcTFSVWc9PSIsInZhbHVlIjoiQ1VaUSs5NnBSRTVqbVlNcGRIdHhndz09IiwibWFjIjoiYzM0OTdmYjNjNjA5YmIyMTQwNTZlZmU3YWU0ODkzNjc1YjI2OWU5ZDQxMjdjMDE1ODBlM2JjMDRkNGQ3NTRhZiIsInRhZyI6IiJ9','2026-11-04 20:33:48','2026-10-28 20:43:34',1,'2026-10-28 20:33:48','2026-10-28 20:43:34'),(154,361,NULL,1,'eyJpdiI6Ilk5cUZaUFIyZ3U0Q0xBYjcvaE1mM0E9PSIsInZhbHVlIjoiVjlwaGJqdjRDb2dZL1hQM25tRkZRQT09IiwibWFjIjoiZjY3NjE3YTNjOTYzZjVhNjlkZDEwYmI4ZDUxNGQ5ZDZhOGRhODRkMjRmYWE3YmEzMjg5NjUwOGQ1NmFjMDI4NSIsInRhZyI6IiJ9','2026-11-04 20:33:49','2026-10-28 20:43:34',1,'2026-10-28 20:33:49','2026-10-28 20:43:34'),(155,362,NULL,1,'eyJpdiI6IkR6MnhvV1ZtRXZUalBmNnNqU1YxMkE9PSIsInZhbHVlIjoiRjVRcCttVXhrbVhFSVZWNWJ1VUsxZz09IiwibWFjIjoiNTUwOTQ5OTA5MWNlNjBkNTg1ZWNiN2FkZTJjYWIwMDg1NTg3NmMzYWM0OGQzMDFhZTExMDYyMmY1MzI0MTBkNCIsInRhZyI6IiJ9','2026-11-04 20:33:50','2026-10-28 20:43:34',1,'2026-10-28 20:33:50','2026-10-28 20:43:34'),(156,363,NULL,1,'eyJpdiI6IjRTVFB6UVVUcE9rZE5YN1ZlVm1ac2c9PSIsInZhbHVlIjoiL0tCb2l6ZEhtUjk0RnJjZmZEWWJNQT09IiwibWFjIjoiNDU5MTU2NjliZjNkZWQ1OWRhZDMzODY0NDdiYjkyOWRmZjkwMTAxNzE5NGEyNmE1YzA5MzQ2OWVlNDhiNmZkMCIsInRhZyI6IiJ9','2026-11-04 20:33:51','2026-10-28 20:43:34',1,'2026-10-28 20:33:51','2026-10-28 20:43:34'),(157,364,NULL,1,'eyJpdiI6IkoxelVGWmJISVEzT0FrRGlNYlVIWUE9PSIsInZhbHVlIjoidXJHekdKQ1Y5ZXR1SDU1cHhFUVZRZz09IiwibWFjIjoiNGM5MjQ1NTUzOTEyYzFkNjcyNjI5N2EwYmU0OGZlYWE0MDM3NjVmN2FiOTUzMzI0OGYwN2Q0MGQwZTZmNzk0NyIsInRhZyI6IiJ9','2026-11-04 20:33:52','2026-10-28 20:43:34',1,'2026-10-28 20:33:52','2026-10-28 20:43:34'),(158,365,NULL,1,'eyJpdiI6IjlhZ2M4cTRpeVdXbVJTdlNrR2RiVmc9PSIsInZhbHVlIjoiUmxTcjUrZFRUbUNaT2N3Z1gzQmdRZz09IiwibWFjIjoiZDdjZjQ4NDFhNDJkZmIwMTZhZDNiNmE2YzMxNTEyNmQ5NDc0OWJkMTBkNTAzNWJlZGMxMWQ0YjUzNGU4Njc0YyIsInRhZyI6IiJ9','2026-11-04 20:33:52','2026-10-28 20:43:34',1,'2026-10-28 20:33:52','2026-10-28 20:43:34'),(159,366,NULL,1,'eyJpdiI6IlJxWXVaSnZsTmhEUzJzdTlIeGIrVmc9PSIsInZhbHVlIjoiR2oxK085U3g3R3NDZGlYY3pSSHc1UT09IiwibWFjIjoiODI4N2ExMTE3ZjQ5MDZiZjM4OGRlN2ViMDM4OGZmYjQ3MTUyMmY5YzA4YTQzYTdmMmNhZGM1NDNjMDEwYjRjNSIsInRhZyI6IiJ9','2026-11-04 20:33:53','2026-10-28 20:43:34',1,'2026-10-28 20:33:53','2026-10-28 20:43:34'),(160,367,NULL,1,'eyJpdiI6IkVhZXlyaDBoclZOS2tnNWVUSzFYM2c9PSIsInZhbHVlIjoiYWJSLy8xTEVzZTdtMDNVVVJoR25kUT09IiwibWFjIjoiNjA3NGMzZGI3NjBiZGRmNWMzZTA4ZTA0ZGMyOWIyNDExZmM4ZTNlNjZkZGM0M2M1ZGNkYmFjOTBmMjYyNmQ4MCIsInRhZyI6IiJ9','2026-11-04 20:33:54','2026-10-28 20:43:34',1,'2026-10-28 20:33:54','2026-10-28 20:43:34'),(161,368,NULL,1,'eyJpdiI6IjdQR0NTVWgySFRZVDMzTzFDOGppU3c9PSIsInZhbHVlIjoibWpNL3YzU2ZmQXZWOWtUbnNWcHhpQT09IiwibWFjIjoiMWUxNmE4YWRmY2EyY2UzM2MwYTk3NzA5ZWNkOGI2Njc2MWEyZGE4ZjVjMjRmN2FmYTQ4OTBhMjU3OGYxMTkxMyIsInRhZyI6IiJ9','2026-11-04 20:33:55','2026-10-28 20:43:34',1,'2026-10-28 20:33:55','2026-10-28 20:43:34'),(162,369,NULL,1,'eyJpdiI6InRuMUJqalFrUWdOS29BSzhoUXprQVE9PSIsInZhbHVlIjoiYnNOQnpkVUo0MmVNaS9mQWhiYlN5dz09IiwibWFjIjoiYmU5ZTgxYTVjN2I4YWE5MTU0NDgxMGFmZjE5OTc2NDhiNDRmZDhkMWM1ODg4ZTc2M2E5NWFkNjJhNmZiMDZmZiIsInRhZyI6IiJ9','2026-11-04 20:33:56','2026-10-28 20:43:34',1,'2026-10-28 20:33:56','2026-10-28 20:43:34'),(163,370,NULL,1,'eyJpdiI6InJIKzBqKy9mUHh6Y2NJVktIdnZtVFE9PSIsInZhbHVlIjoiUitIQkZ1L3dmY3Z2T1lXU25UVFVIZz09IiwibWFjIjoiOWUwZThhOTRlNjc2MjkyODc2NmJhZWYwZjYwNGUxNDgzNjA5ZDA3ODM0ZmY4NDk3ODA4MjQxZjU3OWE2MWMxMCIsInRhZyI6IiJ9','2026-11-04 20:33:57','2026-10-28 20:43:34',1,'2026-10-28 20:33:57','2026-10-28 20:43:34'),(164,371,NULL,1,'eyJpdiI6IkhNNHpmTTVPL0FMa3hiODRqUTlXSGc9PSIsInZhbHVlIjoiQ3RDM2pqckRIRy96ZklhakVxbm11QT09IiwibWFjIjoiM2M2MTViZjY5MmIxMTg1ZjliOWQ0NTFmNTE0YjczNzYwNTk4MWI4YmNlOTQ3NjUyNjc3NDUzY2Q4OWE3NWZkNyIsInRhZyI6IiJ9','2026-11-04 20:33:58','2026-10-28 20:43:34',1,'2026-10-28 20:33:58','2026-10-28 20:43:34'),(165,372,NULL,1,'eyJpdiI6IkdYalBBZnpWbFdodXYzOVJYZGNBbkE9PSIsInZhbHVlIjoidVpEM2VrTVZKa1ZZa1ZCMWZQWU40dz09IiwibWFjIjoiYzc4YzI2NWY3YmZmNjhmOWY3M2ExMWY5YTdmZTE3YzUxZjM1NTRkYjc2ODIzYzJlMTJlYzdmNjNmOTczZDU2YiIsInRhZyI6IiJ9','2026-11-04 20:33:59','2026-10-28 20:43:34',1,'2026-10-28 20:33:59','2026-10-28 20:43:34'),(166,373,NULL,1,'eyJpdiI6IjBjaGYzVEpRNjRSME8xZ0xHM0FzWUE9PSIsInZhbHVlIjoibUtDOTh6T3dYSFFxekh0dlVpSjladz09IiwibWFjIjoiYTVhNzI5MThiNTljNzgxMGNhMjQ1ZjEwYWViODg3YWNlY2VkNTA5ZTMwZmIwZGNhMWUxMjI0OTI5YjczOWIwOCIsInRhZyI6IiJ9','2026-11-04 20:34:00','2026-10-28 20:43:34',1,'2026-10-28 20:34:00','2026-10-28 20:43:34'),(167,374,NULL,1,'eyJpdiI6ImRBWkl1bmlXNHEyM2o5bW9UaG5TUVE9PSIsInZhbHVlIjoieTAyc2QxWm02RVZ6ZTU5YTgrZ0lNUT09IiwibWFjIjoiZjQ4YTg4YzE1ODQxZmVhZDJhYWJkZjljMTRmNDM4M2IwOTQ4OTgyOTU5ZjIzOWQyMWU5MzY4Y2U2ZDJlYTFlYSIsInRhZyI6IiJ9','2026-11-04 20:34:01','2026-10-28 20:43:34',1,'2026-10-28 20:34:01','2026-10-28 20:43:34'),(168,375,NULL,1,'eyJpdiI6InU2K05ValhPbnhXUjNPZzBEdVJxTXc9PSIsInZhbHVlIjoidXNJQjRPL1EvMEdKOGYxVFV4WUpsQT09IiwibWFjIjoiZjhiNTdhOWU2NDU5YjIyZTI0NDQ5NTEzYzA4ZjVmNWQwZGMxYTAxNjY0ZmMwNDE4MTVhMDg5MWFmOTM1ZjhhYiIsInRhZyI6IiJ9','2026-11-04 20:34:02','2026-10-28 20:43:34',1,'2026-10-28 20:34:02','2026-10-28 20:43:34'),(169,376,NULL,1,'eyJpdiI6ImFZYWRKMlFDa3BSMjlMc00zTEllUEE9PSIsInZhbHVlIjoiZGpvZ0poamhMTFVYMzNRdTJpR1pwZz09IiwibWFjIjoiOTRlYTIwNjI4MWM4Yzk1ODRlZDJjM2ZkMmQ5NWY1MGY5MTBjMDUxNjhiMTdmMDExY2MwNWE0NjFkNzA0OTA1YSIsInRhZyI6IiJ9','2026-11-04 20:34:03','2026-10-28 20:43:34',1,'2026-10-28 20:34:03','2026-10-28 20:43:34'),(170,377,NULL,1,'eyJpdiI6Inl2ZUttcmFsaUp5N3JuTC9uY3E2clE9PSIsInZhbHVlIjoieTEzUy9xaUJqTEVoUHBtdi9NZ3JXZz09IiwibWFjIjoiMjkzZTEwMjc1ZTViZTkzM2FlMDIzYjBlNzkyMGY1ZDJlYTUwMjcwNjQ2ODc2NGUwMDU0MDg5YTMyMzkxY2I1OSIsInRhZyI6IiJ9','2026-11-04 20:34:04','2026-10-28 20:43:34',1,'2026-10-28 20:34:04','2026-10-28 20:43:34'),(171,378,NULL,1,'eyJpdiI6InRwcFBJTHBzZlZTMlZ4diszaCtnREE9PSIsInZhbHVlIjoiNk9ZekJCaFNjeCtCUkJzNWlJZHhpdz09IiwibWFjIjoiNDNkNGYyZmQ5MTEyODRmNjFjODA4ODhiMDIzNGU0OGE4ZTM4MGY4YjJkN2NmNzE2YWY3NmIzMjE3OTI0NDhlYSIsInRhZyI6IiJ9','2026-11-04 20:34:05','2026-10-28 20:43:34',1,'2026-10-28 20:34:05','2026-10-28 20:43:34'),(172,379,NULL,1,'eyJpdiI6IjhKZmpZUHpYR1lQQmJBZTdEUnlkdXc9PSIsInZhbHVlIjoiWVZqQUk1c25tNW1qMnh2WCs3SWJuUT09IiwibWFjIjoiNWZkODZhMjlmZDVhMjYwOTNiM2VhMTMxMjYwZjZkYTdjZDE4MzM0ZDRhMTJmZTIyMjIyNzZjNzQ3NWZlNTVjNyIsInRhZyI6IiJ9','2026-11-04 20:34:07','2026-10-28 20:43:34',1,'2026-10-28 20:34:07','2026-10-28 20:43:34'),(173,380,NULL,1,'eyJpdiI6IkZHRkxSV2hZNzF5VTRteVVENHY4Qnc9PSIsInZhbHVlIjoidzNRcTF4QUNKS0hBNzNDZ1RLQzAzZz09IiwibWFjIjoiNDNkOGI1ODc2NWY0YmE4YmZlOGIyNGJlMzExZmY2ODVjZjEzZTMxOGYyYmU3ZDk0ODdmMTMzZWQ0M2MyMWUzOCIsInRhZyI6IiJ9','2026-11-04 20:34:08','2026-10-28 20:43:34',1,'2026-10-28 20:34:08','2026-10-28 20:43:34'),(174,381,NULL,1,'eyJpdiI6IjNVU2RlWnppRmsvY2RGTU5GR2ZRd0E9PSIsInZhbHVlIjoiQm9RakEwZlp3ak5uSDcrOWh6ZDRFQT09IiwibWFjIjoiMWE3ZjRlMWE4YzUzNTVkZTBkMTY2YmViNzNjYzBjZGZlNDgxNTNmNjEwZjM3NmQyMmU1ZDUwMzdkM2FkNTJmNiIsInRhZyI6IiJ9','2026-11-04 20:34:09','2026-10-28 20:43:34',1,'2026-10-28 20:34:09','2026-10-28 20:43:34'),(175,382,NULL,1,'eyJpdiI6ImxzT20xTWFOTzdnN0cxY0NlUURjb0E9PSIsInZhbHVlIjoiOFRhakR5UCtnbGNMSnFCSlY4U2MvQT09IiwibWFjIjoiZGZjODI0YWNiM2RiMDQyMGZjOGMyOTllMmNlMjllZDNhYjIzODIxZGY1YzM4OTNkNjM2ZTBkY2VlMDFmNjQ2YyIsInRhZyI6IiJ9','2026-11-04 20:34:10','2026-10-28 20:43:34',1,'2026-10-28 20:34:10','2026-10-28 20:43:34'),(176,383,NULL,1,'eyJpdiI6IlRqNGFiYVBiV2MydGdTc3gvejhETUE9PSIsInZhbHVlIjoiMEtscExJVUprbm1HQVFVRXM2aUdMdz09IiwibWFjIjoiNzkwYWNjZGZhNjAxYzc5MTYyOWRmNzY1MmRhMDhmOGI3MzIzY2MwNDc2ZWEwMDUwYzkyYjg1NzAyNDk1NTQ0NyIsInRhZyI6IiJ9','2026-11-04 20:34:11','2026-10-28 20:43:34',1,'2026-10-28 20:34:11','2026-10-28 20:43:34'),(177,384,NULL,1,'eyJpdiI6IitrRTBHa1d2MUx6RmZiMy9UWEdpMkE9PSIsInZhbHVlIjoicHBkOHZwbnJNUDdqKzBSK3R2WUNFUT09IiwibWFjIjoiM2ZlMWMyMDE3ODZjOTdkYzQ5NjA2NGQ0YWE3ZGU1NGUwZTgyMmRjYzgyNTg0NGY2ZTk4NTdhMGMyM2IyYzZmYSIsInRhZyI6IiJ9','2026-11-04 20:34:12','2026-10-28 20:43:34',1,'2026-10-28 20:34:12','2026-10-28 20:43:34'),(178,385,NULL,1,'eyJpdiI6IkRQRWdOQ0MwcFdlUEdvT2RuY0E4UHc9PSIsInZhbHVlIjoiblNDU3pSd0tGeW45aE43RnFpVWY1QT09IiwibWFjIjoiZTQ5Y2NlNjc4MmRiZTdmY2QzNGI4MTdmOWFlMzEyOGI0ZjRkZjc2NWUwZTRhNzQ3YjllNDY3MTcwZDBjYWI5YSIsInRhZyI6IiJ9','2026-11-04 20:34:13','2026-10-28 20:43:34',1,'2026-10-28 20:34:13','2026-10-28 20:43:34'),(179,386,NULL,1,'eyJpdiI6ImdkK2wvK0ZsQ2UyVmZOd2kvSzB1OEE9PSIsInZhbHVlIjoiMGh1bEh3TTdPTEw3dmt3amNsemJxQT09IiwibWFjIjoiZTM0YzA1ZDNmMjRhZWZmMWNmOTQyYzcwNWYxOTM3OTEyYmIxYTQzMDA4N2Q1N2M2MzQxMWZiMTNjZDgxNjQ5MSIsInRhZyI6IiJ9','2026-11-04 20:34:13','2026-10-28 20:43:34',1,'2026-10-28 20:34:13','2026-10-28 20:43:34'),(180,387,NULL,1,'eyJpdiI6Im9XQk1JWFpteE5qa251aWtvQUdBc0E9PSIsInZhbHVlIjoiT2xyeldxY0c1TEtRbzlIL01raFozUT09IiwibWFjIjoiOGZmNjk1ZTBhZjAwMzE5YjJkY2ZjMDQ1NjI3MDU2OTI1YmY3ODU3MDRiYzYwODIyMjAzZTgwMTVlYjg5ZmFlZSIsInRhZyI6IiJ9','2026-11-04 20:34:14','2026-10-28 20:43:34',1,'2026-10-28 20:34:14','2026-10-28 20:43:34'),(182,389,NULL,1,'eyJpdiI6InZHMEVFYUxVZFlZd2dLQU9md1NyZXc9PSIsInZhbHVlIjoiUUk1bzc2cXFFWi9vaFpRTGF4NXM4Zz09IiwibWFjIjoiMzYzNTMyNDk2ZDkxMjQ4NmIwYTQwNjRmMjE1ZDE0MDg1ODFhODJmNjUzNGQ3MTlkZTZlYWJjMjAyMGMxZjQ5OSIsInRhZyI6IiJ9','2026-11-04 20:34:16','2026-10-28 20:43:34',1,'2026-10-28 20:34:16','2026-10-28 20:43:34'),(183,390,NULL,1,'eyJpdiI6IkxHb0Rqa2IwRE5xWVBIRXBmOEIrQXc9PSIsInZhbHVlIjoiVncxTCtiSGgvUTVvcFMrVHlJbHdSUT09IiwibWFjIjoiOWEyMmE5MmI0Y2ZiZGU1NjA5YjJiNzQ0NDgxM2VkNDNiZDA2ZjQ1MmFiODExZTFmZjJjNjI5NjlhNmVjMTBiNyIsInRhZyI6IiJ9','2026-11-04 20:34:17','2026-10-28 20:43:34',1,'2026-10-28 20:34:17','2026-10-28 20:43:34'),(184,391,NULL,1,'eyJpdiI6IjRTL054NENUMUF1WXhPZW5HU0JPSVE9PSIsInZhbHVlIjoiZjdFY0JBTFJmdzBheWRXMi84U2xqQT09IiwibWFjIjoiZGVjYTdjYmIzNDBmNzBmNzMzMjAyN2VkYWNiMjI1YTRiYWE2N2Q3Yzg2Y2VhMzgwZGU2NGU2ODc0NThiOTJhYiIsInRhZyI6IiJ9','2026-11-04 20:34:18','2026-10-28 20:43:34',1,'2026-10-28 20:34:18','2026-10-28 20:43:34'),(185,392,NULL,1,'eyJpdiI6Im1xU0x4cUJYYWVKTGNkYkJ0K3N6WlE9PSIsInZhbHVlIjoiakFDSVV3a2tyZkd1TS9ZYVR0NVJOQT09IiwibWFjIjoiNjhlMTk4NDYyYjU4NTRmMDU0NTY5MmQxMjMyMmI5MTRjNzFjZGI0YmU2NTFmYzViMWE2ZDc1OWIxZDlmOTllZSIsInRhZyI6IiJ9','2026-11-04 20:34:19','2026-10-28 20:43:34',1,'2026-10-28 20:34:19','2026-10-28 20:43:34'),(186,393,NULL,1,'eyJpdiI6IllXUzdjYlNDVE9lQ3JObGhMRUV6MFE9PSIsInZhbHVlIjoidjdidW9zODBJNDNRSjdYbkpGYUEzdz09IiwibWFjIjoiNDdlZmFjNTIwNDk1NTJmNjk0NDMxOTMwNGIwN2IzZDdmODljMTVlYzgwZDNlZDY5ODNmOGNhZTgxODRhMTE0OCIsInRhZyI6IiJ9','2026-11-04 20:34:19','2026-10-28 20:43:34',1,'2026-10-28 20:34:19','2026-10-28 20:43:34'),(187,394,NULL,1,'eyJpdiI6IjNGVkN6ZXlYalRYcHNCY2xjRmZFb1E9PSIsInZhbHVlIjoicy9OVER4ZmF6VVlXNW9WdGk1MkM4UT09IiwibWFjIjoiNDY2YTc2MzI4OTcwODMwMWNlNGMzMjVkOWRmMjczYjg3MzIzNTZiYWI5YzA4N2QyZjE5NDIyOTI4Mzk1ZGY0MCIsInRhZyI6IiJ9','2026-11-04 20:34:20','2026-10-28 20:43:34',1,'2026-10-28 20:34:20','2026-10-28 20:43:34'),(188,395,NULL,1,'eyJpdiI6IkRiTHhFOTJaVjFqYUhxR2l3ejdBS2c9PSIsInZhbHVlIjoiWVcvb0N6UDE2T0tHUnpUWDJMQTROQT09IiwibWFjIjoiZWYyM2U0NTVjZDNkYjc0Yzk1NTEyYjFjZmFmNmRkYzMyNzNmNDhhMWQzNDg5ZTQ2MDU0ZTcwYzM4MmFhOTEwMSIsInRhZyI6IiJ9','2026-11-04 20:34:22','2026-10-28 20:43:34',1,'2026-10-28 20:34:22','2026-10-28 20:43:34'),(189,396,NULL,1,'eyJpdiI6IlNlTHlEbHJVZWpObDNEQ2tFSVVJVEE9PSIsInZhbHVlIjoiV1A3Y2FUa0tKeGRqdjZQR0lMU3BlZz09IiwibWFjIjoiNGM3NGY3NmMwNDE5MWM1ODc5YWJlZTQ3NDBmN2U3YTkxNjAwNjk5MzY0YWExZDAyOTZmZGZkY2M5N2I5ZDM1MyIsInRhZyI6IiJ9','2026-11-04 20:34:23','2026-10-28 20:43:34',1,'2026-10-28 20:34:23','2026-10-28 20:43:34'),(190,397,NULL,1,'eyJpdiI6ImpqWTlxa2JFMkZLZ2FOUHpBUVBUYXc9PSIsInZhbHVlIjoiRjNNTTVrS1pwRVBKQmtHYkp0THVyQT09IiwibWFjIjoiOThkMTliMGYwMzYxMWQ3NzI3MTQ2Nzg0YjQ2ZGI4NmE0MzhjMTI1YzJmNGQyNWViMzIyMWEyNDA2ZWMyMWI0ZSIsInRhZyI6IiJ9','2026-11-04 20:34:24','2026-10-28 20:43:34',1,'2026-10-28 20:34:24','2026-10-28 20:43:34'),(191,398,NULL,1,'eyJpdiI6Im9NdWMxMktlT2NrUkVRQlhsM0J2SHc9PSIsInZhbHVlIjoiV01DVnBqdWxleXNlU3F4MmRVR1VmQT09IiwibWFjIjoiZTA5ZTI4ZGUxNzRmZGI3YTY0ODI0ODgyMTAzY2U1MTc0Y2NmZmU5OGZiMTYyMTJkOGQ5YWUyZDkzMDU2ODkxZiIsInRhZyI6IiJ9','2026-11-04 20:34:26','2026-10-28 20:43:34',1,'2026-10-28 20:34:26','2026-10-28 20:43:34'),(192,399,NULL,1,'eyJpdiI6IlFxSEkvL1JSc2RpcW51RUYrK2F3ekE9PSIsInZhbHVlIjoiR3lLbEc2MFVsZDlEc1hHZnBmYmFndz09IiwibWFjIjoiNjgwY2QwZGIxMWMwZWZlYmY3NWM5ZWYyZDcyZDAyZDU4Y2Q5ZmFlMzM3NGQzOTQ1MDZiOWE5YzZiZGI1Zjk4YyIsInRhZyI6IiJ9','2026-11-04 20:34:26','2026-10-28 20:43:34',1,'2026-10-28 20:34:26','2026-10-28 20:43:34'),(193,400,NULL,1,'eyJpdiI6Iks5YU5GcU5KdmVVOHk3ZVhlQ0pEc0E9PSIsInZhbHVlIjoiMzlNR3FkUE9JTnVLS2RLMEkrZkE3dz09IiwibWFjIjoiMDMzZGQ2MjBiOTEzNGIwMzYxM2U5OWZjZWNjYzQ1NTM0NGRmZWY4MTIzZDU3ZTQzODdjYjE1NWY4ZDE1NWMxYiIsInRhZyI6IiJ9','2026-11-04 20:34:27','2026-10-28 20:43:34',1,'2026-10-28 20:34:27','2026-10-28 20:43:34'),(194,401,NULL,1,'eyJpdiI6Ik4vYWFSaEZiN3ZaUzNub1kwZ3lSWGc9PSIsInZhbHVlIjoiL1BNUllTaFdiM3Zuek9rbFJxNVQwdz09IiwibWFjIjoiNTQzMDM0Yjg5YjVmYmNmY2VlNTFiNGU5NGVjNjFmYzQxYzYyODY2NTRlODA3ODI3MjMzNDJhNDgxZDA0N2IwMiIsInRhZyI6IiJ9','2026-11-04 20:34:28','2026-10-28 20:43:34',1,'2026-10-28 20:34:28','2026-10-28 20:43:34'),(195,402,NULL,1,'eyJpdiI6Im9pRloxOVBLWThrOVk2V2RGNlUvNWc9PSIsInZhbHVlIjoiOGd6RzB0aFBjR1BFQ3NnNWwxOTUzQT09IiwibWFjIjoiZWI0NzBlYWQ4OGUzYTlmNDgyNWU2NzUzMjE1M2NiMTg4ODJlYmEzOTJkZjYyMDdmMWViOWQ0ODg5NWU3MGRlZSIsInRhZyI6IiJ9','2026-11-04 20:34:29','2026-10-28 20:43:34',1,'2026-10-28 20:34:29','2026-10-28 20:43:34'),(196,403,NULL,1,'eyJpdiI6IlFpYnpzZlR4RENDcUppVHZIY3puYVE9PSIsInZhbHVlIjoid0ZRZ2lSTXpvQ3NyQi9ObmNqYkQxUT09IiwibWFjIjoiNWMyMTVhOThlMjlkMTdlMjVlNDhhZmZlODM4YTY1NmNiNDg0OGYzODQ3NDc0NzdhODY5NTFkNGI4NDYzYmJiMyIsInRhZyI6IiJ9','2026-11-04 20:34:30','2026-10-28 20:43:34',1,'2026-10-28 20:34:30','2026-10-28 20:43:34'),(197,404,NULL,1,'eyJpdiI6Ikd2KzNQKzBIMkVSZjFLVHNrODQzbXc9PSIsInZhbHVlIjoiZWtjbk8zamhaUHB4ZTYwQXU5c1V6QT09IiwibWFjIjoiNmQ0NWRiZDhlMjhmZmFhMDIwNjYwMmE1MzcyMTQ1MjQ1YmRmNDQyMTI3NmQwODRmZjZlNDgwMDUwM2ZjZDU0ZiIsInRhZyI6IiJ9','2026-11-04 20:34:31','2026-10-28 20:43:34',1,'2026-10-28 20:34:31','2026-10-28 20:43:34'),(198,405,NULL,1,'eyJpdiI6Inc3c1NlL3JpQ0l0WXdoMHFHaFBZNFE9PSIsInZhbHVlIjoiQTAzNE5TS1ArVGl3cEZxUWg3QTdFUT09IiwibWFjIjoiNzIzOGRkOGQ0Y2M2MTcwOWJlOGQ3NGVmZGQwNDRkM2ZkMTFmYTY1ZTBkYzBlMWI5NDVlN2I5NzlmN2ZmNzlhMyIsInRhZyI6IiJ9','2026-11-04 20:34:33','2026-10-28 20:43:34',1,'2026-10-28 20:34:33','2026-10-28 20:43:34'),(199,406,NULL,1,'eyJpdiI6IlZpVHpFeTRsVUttR0MzOVRlLzMzZVE9PSIsInZhbHVlIjoiNmlzU0krb3hsNHRNU1dTTlZnU2FRQT09IiwibWFjIjoiYTU0MjUzYjNlZmUwOGNjMmM1MTVjNDdhNjNlYmY5N2NhNzczMTdjYTAwNjBlOGY4NjNlZDYxNzc3NTkzNWEzNyIsInRhZyI6IiJ9','2026-11-04 20:34:33','2026-10-28 20:43:34',1,'2026-10-28 20:34:33','2026-10-28 20:43:34'),(200,407,NULL,1,'eyJpdiI6ImVuNTZkcUNOVUlQQ0p6dTVYbkcyQ1E9PSIsInZhbHVlIjoiNXZkK1plSHY3RS9Rb1VqUis2QmwwQT09IiwibWFjIjoiNTcyZWRlZTY1Y2RjNzg4N2Y1ODYyMzEwZGE2NzZjMTQxMjQ1ZmI3NGI0MmM4MWY2MDZkYjg4ZmQzMDRiOWU5ZCIsInRhZyI6IiJ9','2026-11-04 20:34:34','2026-10-28 20:43:34',1,'2026-10-28 20:34:34','2026-10-28 20:43:34'),(201,408,NULL,1,'eyJpdiI6Im91STl5TUI0RkN0bzFCWE1GVVBZREE9PSIsInZhbHVlIjoib0ZXWFVCU21mODc1WnVycnYzb3RDQT09IiwibWFjIjoiNDFlN2FhMWE4Y2E3ZjJmYWJhZGYzNmI2NDUyYTJmODc2NWNhZThiMTY5MDJmNTAzNjgzOTQ1ZWZkOTU1OWMxZCIsInRhZyI6IiJ9','2026-11-05 06:06:52','2026-10-29 06:07:23',1,'2026-10-29 06:06:52','2026-10-29 06:07:23');
/*!40000 ALTER TABLE `kredensial_sementara` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_21_165933_create_teachers_table',1),(5,'2026_08_21_172920_create_jimnys_table',1),(6,'2026_08_25_214623_create_kenzos_table',1),(7,'2026_08_25_220417_create_siswas_table',1),(8,'2026_08_28_150851_create_assets_table',1),(9,'2026_09_01_201649_create__majors_table',1),(10,'2026_09_04_154303_create_trees_table',1),(11,'2026_09_08_200039_create_healths_table',1),(12,'2026_09_10_154522_create_employes_table',1),(13,'2026_09_11_150610_create_violations_table',1),(14,'2026_09_16_210701_create_jihys_table',1),(15,'2026_10_01_225751_create_ekstrakurikulers_table',1),(16,'2026_10_01_225829_create_pendaftaran_ekskuls_table',1),(17,'2026_10_02_163344_create_pendaftarans_table',1),(18,'2026_10_09_151205_create_cars_table',1),(19,'2026_10_09_164259_create_ekskul_haris_table',1),(20,'2026_10_11_055641_add_is_active_to_users_table',1),(21,'2026_10_12_162814_create_ekskul_pengumumans_table',1),(22,'2026_10_16_080059_add_profil_to_users_table',1),(23,'2026_10_16_082230_add_alumni_to_users_table',1),(24,'2026_10_16_082346_add_keanggotaan_to_pendaftaran_ekskuls_table',1),(25,'2026_10_16_082446_create_ekskul_prestasis_table',1),(26,'2026_10_16_082538_create_ekskul_events_table',1),(27,'2026_10_17_032616_add_must_change_password_to_users_table',1),(28,'2026_10_17_032733_create_ekskul_pembinas_table',1),(29,'2026_10_17_032850_backfill_ekskul_pembinas',1),(30,'2026_10_17_111051_add_profil_publik_to_users_table',1),(31,'2026_10_17_153423_create_ekskul_jabatans_table',1),(32,'2026_10_17_153531_backfill_ekskul_jabatans_dari_enum',1),(33,'2026_10_17_233033_add_status_anggota_to_pendaftaran_ekskuls_table',1),(34,'2026_10_17_233109_create_ekskul_pengunduran_diris_table',1),(35,'2026_10_19_030506_add_pendaftaran_kontrol_to_ekstrakurikulers_table',1),(36,'2026_10_19_030527_create_ekskul_tugas_tables',1),(37,'2026_10_19_030553_create_ekskul_tautans_table',1),(38,'2026_10_19_030610_create_pengingat_terkirims_table',1),(39,'2026_10_19_030625_create_notifications_table',1),(40,'2026_10_19_032808_create-ekskul-absensi-tables',1),(41,'2026_10_19_032843_create-ekskul-nilais-table',1),(42,'2026_10_20_122243_add_akses_materi_to_pendaftaran_ekskuls_table',1),(43,'2026_10_20_122324_create_ekskul_materis_table',1),(44,'2026_10_20_122351_create_ekskul_sarans_table',1),(45,'2026_10_20_123124_create_ekskul_pr_tables',1),(46,'2026_10_21_154144_create_ekskul_fotos_table',1),(47,'2026_10_23_031012_add_jenis_to_ekstrakurikulers_table',1),(48,'2026_10_23_031107_create_osis_tables',1),(53,'2026_10_28_130000_add_kategori_osis_and_user_logs',5),(56,'2026_10_27_021107_create_bentrok_jadwal_pilihans_table',6),(57,'2026_10_27_045137_add_tampilan_kustom_to_users_table',6),(58,'2026_10_27_120000_create_user_panggilans_table',6),(59,'2026_10_27_120100_create_profil_tags_table',6),(60,'2026_10_29_090000_create_kredensial_sementara_table',6),(61,'2026_10_30_090000_add_selesai_dan_susulan_to_pendaftaran_ekskuls_table',6),(62,'2026_10_29_024535_restore_missing_osis_tables',7),(63,'2026_10_31_090000_create_jenis_ekskuls_table',8),(64,'2026_11_01_090000_ubah_jenis_ekskul_jadi_tipe_mekanisme',9),(65,'2026_11_02_090000_isi_kelas_dan_angkatan_pendiri_komunitas',10),(66,'2026_11_02_090000_samakan_data_pendaftar_dengan_akun',10),(67,'2026_11_01_090000_ganti_jenis_ekskul_dengan_tipe',11),(68,'2026_11_02_090000_tambah_tahap_seleksi',11),(69,'2026_10_31_090000_samakan_data_pendaftar_dengan_akun',12);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('0a7318be-9036-4ae9-bf52-c43373714141','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Task dinilai\",\"pesan\":\"siapin hari guru, pahlawan, bulan bahasa: nilai 100 dari 100.\",\"url\":\"\\/ekstrakurikuler\\/6?tab=materi\",\"ikon\":\"\\ud83d\\udcca\"}','2026-10-29 07:13:35','2026-10-27 23:34:51','2026-10-29 07:13:35'),('2d32283e-e7c4-498c-9e81-4ae7c2f3cf22','App\\Notifications\\RekapImportSiswa','App\\Models\\User',1,'{\"judul\":\"Import siswa selesai: 1 akun dibuat\",\"pesan\":\"1 akun siswa baru dibuat dari berkas \\\"Siswa_Tambahan_Dammarel.csv\\\".\\nNama: Dammarel Al Fawdzan Putra.\\nBuka untuk melihat email dan password sementara tiap siswa (tersedia 7 hari).\",\"url\":\"\\/import-siswa\\/2d32283e-e7c4-498c-9e81-4ae7c2f3cf22\",\"ikon\":\"\\ud83d\\udce5\",\"berkas\":\"Siswa_Tambahan_Dammarel.csv\",\"id_siswa\":[408],\"dilewati\":[],\"diabaikan\":0}','2026-10-29 06:11:22','2026-10-29 06:06:54','2026-10-29 06:11:22'),('3231277a-d0ad-4673-9c89-6b9f1928a509','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Nilai ekskul baru\",\"pesan\":\"Nilai Semester Ganjil 2026\\/2027 di Rohani Islam: rata-rata 100.0 (predikat A).\",\"url\":\"\\/ekstrakurikuler\\/2?tab=nilai\",\"ikon\":\"\\ud83d\\udcca\"}','2026-10-26 23:19:34','2026-10-26 21:55:01','2026-10-26 23:19:34'),('38a33ce5-adeb-4c7a-9c7b-daad60ff1f1e','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',371,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:47:30','2026-10-28 20:47:30'),('39814552-6b9a-48a0-828b-c525b0ab4a5c','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',7,'{\"judul\":\"Selamat datang, Pembina!\",\"pesan\":\"Akun pembinamu untuk ekskul Palang Merah Remaja sudah dibuat oleh Admin Sekolah.\\nEmail login: kidris@gmail.com\\nDemi keamanan, segera ganti password sementara dari admin lewat halaman Pengaturan.\",\"url\":\"\\/pengaturan\",\"ikon\":\"\\ud83d\\udc4b\"}',NULL,'2026-10-28 01:33:31','2026-10-28 01:33:31'),('3a3569b9-b586-48e1-8fb3-865c423b2821','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',1,'{\"judul\":\"Pendaftaran baru\",\"pesan\":\"Jimny Al Ramadhan Syahnendi mendaftar ke Silat.\",\"url\":\"\\/pendaftaran\",\"ikon\":\"\\ud83d\\udce5\"}','2026-10-26 21:57:08','2026-10-26 21:14:27','2026-10-26 21:57:08'),('3b609fab-b301-4ab7-9176-30368f63ef8f','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',1,'{\"judul\":\"Pendaftaran baru\",\"pesan\":\"Jimny Al Ramadhan Syahnendi mendaftar ke Rohani Islam.\",\"url\":\"\\/pendaftaran\",\"ikon\":\"\\ud83d\\udce5\"}','2026-10-26 21:57:08','2026-10-26 21:15:49','2026-10-26 21:57:08'),('413d5972-463d-4c49-b671-70c199250e88','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',1,'{\"judul\":\"Task dikumpulkan\",\"pesan\":\"Ramadhan Syahnendi mengumpulkan Task: siapin hari guru, pahlawan, bulan bahasa.\",\"url\":\"\\/pr\\/1\",\"ikon\":\"\\ud83d\\udce5\"}','2026-10-27 23:34:34','2026-10-27 20:14:44','2026-10-27 23:34:34'),('430df72b-39c2-4f97-b6d4-113e5920ce31','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',1,'{\"judul\":\"Akun pembina dibuat\",\"pesan\":\"Akun pembina kang Idris (kidris@gmail.com) untuk ekskul Palang Merah Remaja dibuat oleh Admin Sekolah.\\nPassword sementara bisa dibuka di halaman Kredensial selama 7 hari, dan terhapus otomatis setelah pembina mengganti passwordnya.\",\"url\":\"\\/kredensial\",\"ikon\":\"\\ud83d\\udd11\"}','2026-10-29 06:11:23','2026-10-28 01:33:31','2026-10-29 06:11:23'),('484cbf5a-9580-4508-afe5-af5e8541b682','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',367,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:53:27','2026-10-28 20:53:27'),('4e462002-ceaa-4c17-99c9-6292cefc9c1e','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',2,'{\"judul\":\"Penugasan pembina\",\"pesan\":\"Kamu ditugaskan sebagai pembina Organisasi Siswa Intra Sekolah.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83d\\udee1\\ufe0f\"}',NULL,'2026-10-28 01:34:47','2026-10-28 01:34:47'),('50bc3108-48eb-40e3-bb04-2382a1250e77','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Tag baru di profilmu\",\"pesan\":\"Seseorang memberi tag \\\"Si hiperaktif\\\" di profilmu. Tag ini privat: hanya kamu yang bisa melihatnya sampai kamu menampilkannya.\",\"url\":\"\\/profil\",\"ikon\":\"\\ud83c\\udff7\\ufe0f\"}','2026-10-26 23:19:34','2026-10-26 22:56:57','2026-10-26 23:19:34'),('55fdd81a-3192-45ea-b498-1417328b26d6','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Jadwal ekskulmu bentrok\",\"pesan\":\"Silat (08:00\\u201310:00) bentrok dengan Organisasi Siswa Intra Sekolah (05:00\\u201317:00) pada hari Sabtu. Pilih ekskul yang kamu hadiri atau atur pembagian waktunya.\",\"url\":\"\\/profil#bentrok-jadwal\",\"ikon\":\"\\u26a0\\ufe0f\"}','2026-10-26 23:19:35','2026-10-26 21:19:40','2026-10-26 23:19:35'),('578fd9dc-e954-47c1-bc31-cc302ca32f9d','App\\Notifications\\RekapImportSiswa','App\\Models\\User',1,'{\"judul\":\"Import siswa selesai: 200 akun dibuat\",\"pesan\":\"200 akun siswa baru dibuat dari berkas \\\"daftar-siswa.csv\\\".\\nNama: Adilla Ufairah Azmi, Febry Intania Ardilla Permana, Kiagus Muhamad Ahmad Fadlan, Nabila Farihatun Aini, Regina Mesya, Sholehati Uswatun Hasanah, Ai Marsel Lipiana, Assyifa Dita Sahira, Hadi Nur Rahma, M. Syafir Dzulfikar, Qinthara Al-Ghifari Putra Muchtar, Syalwa Rahmatul Ummah, Annisa Khumaira Setiawan, Lestari Julyanti, Irfan Ainul Yaqin dan 185 lainnya.\\nBuka untuk melihat email dan password sementara tiap siswa (tersedia 7 hari).\",\"url\":\"\\/import-siswa\\/578fd9dc-e954-47c1-bc31-cc302ca32f9d\",\"ikon\":\"\\ud83d\\udce5\",\"berkas\":\"daftar-siswa.csv\",\"id_siswa\":[8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173,174,175,176,177,178,179,180,181,182,183,184,185,186,187,188,189,190,191,192,193,194,195,196,197,198,199,200,201,202,203,204,205,206,207],\"dilewati\":[],\"diabaikan\":0}','2026-10-29 06:11:22','2026-10-28 19:29:34','2026-10-29 06:11:22'),('5a4e92d0-3141-4159-802e-24dfbeb6e756','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Foto baru di galeri\",\"pesan\":\"4 foto baru ditambahkan di Organisasi Siswa Intra Sekolah.\",\"url\":\"\\/ekstrakurikuler\\/6?tab=galeri\",\"ikon\":\"\\ud83d\\udcf7\"}','2026-10-26 23:19:35','2026-10-26 21:47:26','2026-10-26 23:19:35'),('69d87b18-f71d-4182-ad8f-54a8ec2bf821','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',242,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:56:50','2026-10-28 20:56:50'),('6c982c05-2e5f-4fed-96c7-54926043cf64','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',3,'{\"judul\":\"Pengunduran diri disetujui\",\"pesan\":\"Pengajuanmu keluar dari Silat disetujui.\",\"url\":\"\\/ekstrakurikuler\\/1\",\"ikon\":\"\\u2705\"}',NULL,'2026-10-27 04:31:49','2026-10-27 04:31:49'),('72a7f848-d1c5-427d-ad93-2ced8b95648a','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Task baru\",\"pesan\":\"Admin Sekolah memberimu task: bantuin pak enjang beli lemineral (tenggat 28-10-2026)\",\"url\":\"\\/tugas\",\"ikon\":\"\\ud83d\\udcdd\"}','2026-10-27 20:15:06','2026-10-27 18:08:29','2026-10-27 20:15:06'),('74674cc9-f018-42c5-a7f8-56598efd1b01','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',369,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 21:02:37','2026-10-28 21:02:37'),('7c41352d-366c-496c-8a48-42b11dfbc65b','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',407,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:52:29','2026-10-28 20:52:29'),('7f412340-2ff0-461f-86f3-9926dd9aa8da','App\\Notifications\\RekapImportSiswa','App\\Models\\User',1,'{\"judul\":\"Import siswa selesai: 200 akun dibuat\",\"pesan\":\"200 akun siswa baru dibuat dari berkas \\\"siswa-import.csv\\\".\\nNama: Adilla Ufairah Azmi, Febry Intania Ardilla Permana, Kiagus Muhamad Ahmad Fadlan, Nabila Farihatun Aini, Regina Mesya, Sholehati Uswatun Hasanah, Ai Marsel Lipiana, Assyifa Dita Sahira, Hadi Nur Rahma, M. Syafir Dzulfikar, Qinthara Al-Ghifari Putra Muchtar, Syalwa Rahmatul Ummah, Annisa Khumaira Setiawan, Lestari Julyanti, Irfan Ainul Yaqin dan 185 lainnya.\\nBuka untuk melihat email dan password sementara tiap siswa (tersedia 7 hari).\",\"url\":\"\\/import-siswa\\/7f412340-2ff0-461f-86f3-9926dd9aa8da\",\"ikon\":\"\\ud83d\\udce5\",\"berkas\":\"siswa-import.csv\",\"id_siswa\":[208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249,250,251,252,253,254,255,256,257,258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,407],\"dilewati\":[],\"diabaikan\":0}','2026-10-29 06:11:22','2026-10-28 20:34:35','2026-10-29 06:11:22'),('8523a894-8465-4000-9b4e-5dea24e1e648','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',3,'{\"judul\":\"Tag baru di profilmu\",\"pesan\":\"Seseorang memberi tag \\\"test aja\\\" di profilmu. Tag ini privat: hanya kamu yang bisa melihatnya sampai kamu menampilkannya.\",\"url\":\"\\/profil\",\"ikon\":\"\\ud83c\\udff7\\ufe0f\"}',NULL,'2026-10-26 23:08:26','2026-10-26 23:08:26'),('895eed8c-e728-4e25-aaf0-91fadd009031','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',3,'{\"judul\":\"Pendaftaran disetujui\",\"pesan\":\"Pendaftaranmu ke Silat disetujui. Selamat bergabung!\",\"url\":\"\\/ekstrakurikuler\\/1\",\"ikon\":\"\\ud83c\\udf89\"}',NULL,'2026-10-26 23:07:09','2026-10-26 23:07:09'),('9acd4d23-4cdc-4a8f-8181-484ffc288e42','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',384,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:50:20','2026-10-28 20:50:20'),('9eda1c92-c1f8-4730-913e-0d106d39c00b','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',399,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:48:27','2026-10-28 20:48:27'),('9fec081a-a3b6-4d3f-adaf-999ceb7c7ded','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Pendaftaran disetujui\",\"pesan\":\"Pendaftaranmu ke english club disetujui. Selamat bergabung!\",\"url\":\"\\/ekstrakurikuler\\/3\",\"ikon\":\"\\ud83c\\udf89\"}','2026-10-26 23:19:35','2026-10-26 21:20:11','2026-10-26 23:19:35'),('a4887a8d-cd68-4794-8c96-3f1a8a46d6d9','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Jadwal ekskulmu bentrok\",\"pesan\":\"english club (15:20\\u201316:30) bentrok dengan Organisasi Siswa Intra Sekolah (05:00\\u201317:00) pada hari Selasa (dan 1 bentrok lain). Pilih ekskul yang kamu hadiri atau atur pembagian waktunya.\",\"url\":\"\\/profil#bentrok-jadwal\",\"ikon\":\"\\u26a0\\ufe0f\"}','2026-10-26 23:19:35','2026-10-26 21:20:12','2026-10-26 23:19:35'),('a4a79c66-000c-4770-b8ee-a698219f3bf5','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',1,'{\"judul\":\"Pengajuan keluar ekskul\",\"pesan\":\"Siswa test mengajukan keluar dari Silat.\",\"url\":\"\\/pengunduran-diri\",\"ikon\":\"\\ud83d\\udce4\"}','2026-10-27 18:05:58','2026-10-27 04:31:05','2026-10-27 18:05:58'),('abe17f58-27d4-4682-8465-4b24a6849489','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Akses materi diberikan\",\"pesan\":\"Kamu sekarang bisa membuka Materi & Task Organisasi Siswa Intra Sekolah.\",\"url\":\"\\/ekstrakurikuler\\/6?tab=materi\",\"ikon\":\"\\ud83d\\udd13\"}','2026-10-27 20:14:10','2026-10-27 18:11:10','2026-10-27 20:14:10'),('ad5a10af-d93c-4156-a8aa-301ca5b919ec','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',1,'{\"judul\":\"Pendaftaran baru\",\"pesan\":\"Jimny Al Ramadhan Syahnendi mendaftar ke english club.\",\"url\":\"\\/pendaftaran\",\"ikon\":\"\\ud83d\\udce5\"}','2026-10-26 21:57:08','2026-10-26 21:13:39','2026-10-26 21:57:08'),('bec59432-2829-4a6a-ba6b-9f62bbb93a38','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',1,'{\"judul\":\"Task selesai\",\"pesan\":\"Ramadhan Syahnendi menyelesaikan: bantuin pak enjang beli lemineral\",\"url\":\"\\/tugas\",\"ikon\":\"\\u2705\"}','2026-10-27 23:33:47','2026-10-27 20:15:20','2026-10-27 23:33:47'),('c0b5aab4-d57c-4e2e-b2e1-ecdd365c6ec5','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',1,'{\"judul\":\"Komunitas baru dari siswa\",\"pesan\":\"Ramadhan Syahnendi mendirikan komunitas Ngoding. Tunjuk pembina lalu resmikan bila sudah layak.\",\"url\":\"\\/ekstrakurikuler\\/8\",\"ikon\":\"\\ud83c\\udf31\"}','2026-10-30 20:42:35','2026-10-30 04:58:36','2026-10-30 20:42:35'),('c680a987-d6cd-433d-9c98-06b71f9f561e','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Pendaftaran disetujui\",\"pesan\":\"Pendaftaranmu ke Silat disetujui. Selamat bergabung!\",\"url\":\"\\/ekstrakurikuler\\/1\",\"ikon\":\"\\ud83c\\udf89\"}','2026-10-26 23:19:35','2026-10-26 21:19:40','2026-10-26 23:19:35'),('d5b627f0-3740-451f-a9f3-71d4c14efd50','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Jadwal ekskulmu bentrok\",\"pesan\":\"Rohani Islam (15:20\\u201316:30) bentrok dengan Organisasi Siswa Intra Sekolah (05:00\\u201317:00) pada hari Selasa (dan 1 bentrok lain). Pilih ekskul yang kamu hadiri atau atur pembagian waktunya.\",\"url\":\"\\/profil#bentrok-jadwal\",\"ikon\":\"\\u26a0\\ufe0f\"}','2026-10-26 23:19:36','2026-10-26 21:19:03','2026-10-26 23:19:36'),('def2f939-63a9-43e7-bf51-41d6eda7c3af','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',279,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:51:28','2026-10-28 20:51:28'),('f1708228-9bd2-446e-8e7a-e9a440773fa2','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',368,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:58:05','2026-10-28 20:58:05'),('f6265f36-cfbc-4539-8103-3a0fb5024ed5','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}','2026-10-26 23:19:36','2026-10-26 18:03:13','2026-10-26 23:19:36'),('f84ebcd8-884b-4c74-af6f-49a25026f77b','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',222,'{\"judul\":\"Kamu menjadi pengurus OSIS\",\"pesan\":\"Admin Sekolah menetapkanmu sebagai pengurus OSIS angkatan 10.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\ud83c\\udfdb\\ufe0f\"}',NULL,'2026-10-28 20:49:28','2026-10-28 20:49:28'),('fb424725-a02c-4ebc-a35c-a3737e204a74','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Pendaftaran disetujui\",\"pesan\":\"Pendaftaranmu ke Rohani Islam disetujui. Selamat bergabung!\",\"url\":\"\\/ekstrakurikuler\\/2\",\"ikon\":\"\\ud83c\\udf89\"}','2026-10-26 23:19:36','2026-10-26 21:18:59','2026-10-26 23:19:36'),('fd1f0a8a-4430-4799-b859-f30e4ee4b088','App\\Notifications\\PemberitahuanEkskul','App\\Models\\User',4,'{\"judul\":\"Pengingat latihan\",\"pesan\":\"Organisasi Siswa Intra Sekolah akan dimulai besok pukul 05:00 WIB.\",\"url\":\"\\/ekstrakurikuler\\/6\",\"ikon\":\"\\u23f0\"}','2026-10-27 20:15:44','2026-10-27 17:45:00','2026-10-27 20:15:44');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osis_periode_seniors`
--

DROP TABLE IF EXISTS `osis_periode_seniors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `osis_periode_seniors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `osis_periode_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `osis_periode_seniors_osis_periode_id_user_id_unique` (`osis_periode_id`,`user_id`),
  KEY `osis_periode_seniors_user_id_foreign` (`user_id`),
  CONSTRAINT `osis_periode_seniors_osis_periode_id_foreign` FOREIGN KEY (`osis_periode_id`) REFERENCES `osis_periodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `osis_periode_seniors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osis_periode_seniors`
--

LOCK TABLES `osis_periode_seniors` WRITE;
/*!40000 ALTER TABLE `osis_periode_seniors` DISABLE KEYS */;
/*!40000 ALTER TABLE `osis_periode_seniors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osis_periodes`
--

DROP TABLE IF EXISTS `osis_periodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `osis_periodes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `nama` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('rekrutmen','seleksi','berjalan','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rekrutmen',
  `buka_pada` date DEFAULT NULL,
  `tutup_pada` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `osis_periodes_ekstrakurikuler_id_nama_unique` (`ekstrakurikuler_id`,`nama`),
  CONSTRAINT `osis_periodes_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osis_periodes`
--

LOCK TABLES `osis_periodes` WRITE;
/*!40000 ALTER TABLE `osis_periodes` DISABLE KEYS */;
INSERT INTO `osis_periodes` VALUES (1,6,'Osis 11','rekrutmen','2026-08-10','2027-06-27','2026-10-26 17:53:07','2026-10-26 17:53:07');
/*!40000 ALTER TABLE `osis_periodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `pendaftaran_ekskuls`
--

DROP TABLE IF EXISTS `pendaftaran_ekskuls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendaftaran_ekskuls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `osis_periode_id` bigint unsigned DEFAULT NULL,
  `nama_pendaftar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `peran` enum('siswa','admin','pembina') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'siswa',
  `jabatan` enum('anggota','ketua','wakil_ketua','sekretaris','bendahara','koordinator','pelatih','pembina_pendamping') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'anggota',
  `angkatan_ekskul` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_mulai` smallint unsigned DEFAULT NULL,
  `tahun_selesai` smallint unsigned DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `kelas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nisn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan_mendaftar` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','diterima','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `status_anggota` enum('aktif','keluar','dikeluarkan','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `akses_materi` tinyint(1) NOT NULL DEFAULT '0',
  `alasan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `dibuat_susulan_oleh` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pendaftaran_ekskuls_user_id_foreign` (`user_id`),
  KEY `pendaftaran_ekskuls_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  KEY `pendaftaran_ekskuls_osis_periode_id_foreign` (`osis_periode_id`),
  KEY `pendaftaran_ekskuls_dibuat_susulan_oleh_foreign` (`dibuat_susulan_oleh`),
  CONSTRAINT `pendaftaran_ekskuls_dibuat_susulan_oleh_foreign` FOREIGN KEY (`dibuat_susulan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pendaftaran_ekskuls_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pendaftaran_ekskuls_osis_periode_id_foreign` FOREIGN KEY (`osis_periode_id`) REFERENCES `osis_periodes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pendaftaran_ekskuls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftaran_ekskuls`
--

LOCK TABLES `pendaftaran_ekskuls` WRITE;
/*!40000 ALTER TABLE `pendaftaran_ekskuls` DISABLE KEYS */;
INSERT INTO `pendaftaran_ekskuls` VALUES (1,3,1,NULL,'Siswa test','siswa','anggota',NULL,2026,2026,'2026-10-27','TEST','1234567890','Ingin mengembangkan hobi.','diterima','keluar',0,NULL,'2026-10-22 20:53:43','2026-10-27 04:31:48',NULL),(2,4,6,NULL,'Ramadhan Syahnendi','siswa','anggota','10',2026,2027,NULL,'XI PPLG','2526.010.050','Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',1,NULL,'2026-10-26 18:03:08','2026-10-27 18:11:09',NULL),(3,4,3,NULL,'Ramadhan Syahnendi','siswa','anggota',NULL,2026,NULL,NULL,'XI PPLG','2526.010.050','pengen bisa berbicara bahasa inggris dengan lebih baik','diterima','aktif',0,NULL,'2026-10-26 21:13:38','2026-10-26 21:20:11',NULL),(4,4,1,NULL,'Ramadhan Syahnendi','siswa','anggota',NULL,2026,NULL,NULL,'XI PPLG','2526.010.050','ingin bisa bela diri','diterima','aktif',0,NULL,'2026-10-26 21:14:27','2026-10-26 21:19:40',NULL),(5,4,2,NULL,'Ramadhan Syahnendi','siswa','anggota','10',2025,2027,NULL,'XI PPLG','2526.010.050','pengen lancar baca hadist dan qur an','diterima','aktif',0,NULL,'2026-10-26 21:15:42','2026-10-26 23:23:42',NULL),(6,371,6,NULL,'Muhammad Airlangga Alfarisi','siswa','anggota','10',2026,2027,NULL,'XI PPLG',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:47:30','2026-10-28 20:47:30',1),(7,399,6,NULL,'Khansa Ataya Kurniawan','siswa','anggota','10',2026,2027,NULL,'XI DKV',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:48:27','2026-10-28 20:48:27',1),(8,222,6,NULL,'Irfan Ainul Yaqin','siswa','anggota','10',2026,2027,NULL,'XI PPLG',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:49:27','2026-10-28 20:49:27',1),(9,384,6,NULL,'Najla Zahirah','siswa','anggota','10',2026,2027,NULL,'XI DKV',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:50:19','2026-10-28 20:50:19',1),(10,279,6,NULL,'Zhafirah Annisah','siswa','anggota','10',2026,2027,NULL,'XI DKV',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:51:28','2026-10-28 20:51:28',1),(11,407,6,NULL,'Syifa Satriani Putri Iskandar','siswa','anggota','10',2026,2027,NULL,'XI DKV',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:52:29','2026-10-28 20:52:29',1),(12,367,6,NULL,'Muhammad Raif Anaqie Purnama','siswa','anggota','10',2026,2027,NULL,'XI DKV',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:53:27','2026-10-28 20:53:27',1),(13,242,6,NULL,'Alfathih Mahesatora Suwignyo','siswa','anggota','10',2026,2027,NULL,'XI PPLG',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:56:50','2026-10-28 20:56:50',1),(14,368,6,NULL,'Satria Fauzi Ahmad','siswa','anggota','10',2026,2027,NULL,'XI DKV',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 20:58:05','2026-10-28 20:58:05',1),(15,369,6,NULL,'Evan Kusumah Setiawan','siswa','anggota','10',2026,2027,NULL,'XI PPLG',NULL,'Ditetapkan langsung oleh Admin Sekolah','diterima','aktif',0,NULL,'2026-10-28 21:02:37','2026-10-28 21:02:37',1);
/*!40000 ALTER TABLE `pendaftaran_ekskuls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendaftarans`
--

DROP TABLE IF EXISTS `pendaftarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendaftarans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` bigint unsigned NOT NULL,
  `nama_siswa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pembina` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','diterima','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pendaftarans_ekstrakurikuler_id_foreign` (`ekstrakurikuler_id`),
  CONSTRAINT `pendaftarans_ekstrakurikuler_id_foreign` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftarans`
--

LOCK TABLES `pendaftarans` WRITE;
/*!40000 ALTER TABLE `pendaftarans` DISABLE KEYS */;
/*!40000 ALTER TABLE `pendaftarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengingat_terkirims`
--

DROP TABLE IF EXISTS `pengingat_terkirims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengingat_terkirims` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ekskul_hari_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengingat_terkirims_ekskul_hari_id_tanggal_unique` (`ekskul_hari_id`,`tanggal`),
  CONSTRAINT `pengingat_terkirims_ekskul_hari_id_foreign` FOREIGN KEY (`ekskul_hari_id`) REFERENCES `ekskul_haris` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengingat_terkirims`
--

LOCK TABLES `pengingat_terkirims` WRITE;
/*!40000 ALTER TABLE `pengingat_terkirims` DISABLE KEYS */;
INSERT INTO `pengingat_terkirims` VALUES (1,11,'2026-10-29','2026-10-27 17:45:00','2026-10-27 17:45:00');
/*!40000 ALTER TABLE `pengingat_terkirims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profil_tags`
--

DROP TABLE IF EXISTS `profil_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profil_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `pemberi_id` bigint unsigned DEFAULT NULL,
  `teks` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('privat','tampil') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'privat',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profil_tags_user_id_pemberi_id_teks_unique` (`user_id`,`pemberi_id`,`teks`),
  KEY `profil_tags_pemberi_id_foreign` (`pemberi_id`),
  KEY `profil_tags_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `profil_tags_pemberi_id_foreign` FOREIGN KEY (`pemberi_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `profil_tags_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profil_tags`
--

LOCK TABLES `profil_tags` WRITE;
/*!40000 ALTER TABLE `profil_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `profil_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seleksi_tahaps`
--

DROP TABLE IF EXISTS `seleksi_tahaps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seleksi_tahaps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `osis_periode_id` bigint unsigned NOT NULL,
  `urutan` smallint unsigned NOT NULL,
  `nama` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mulai_pada` datetime DEFAULT NULL,
  `selesai_pada` datetime DEFAULT NULL,
  `tempat` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `dibuat_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seleksi_tahaps_osis_periode_id_urutan_unique` (`osis_periode_id`,`urutan`),
  KEY `seleksi_tahaps_dibuat_oleh_foreign` (`dibuat_oleh`),
  CONSTRAINT `seleksi_tahaps_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seleksi_tahaps_osis_periode_id_foreign` FOREIGN KEY (`osis_periode_id`) REFERENCES `osis_periodes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seleksi_tahaps`
--

LOCK TABLES `seleksi_tahaps` WRITE;
/*!40000 ALTER TABLE `seleksi_tahaps` DISABLE KEYS */;
/*!40000 ALTER TABLE `seleksi_tahaps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('j8Fyb6iSZSdHdhQGQdPP4yLvBKnHOzXIMCoNAQTx',4,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSjlrelduZXFBdk1Vd09QVU0yRzc0ZVEzSmZEMW00eFlrWldOQ2xZdSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZWtzdHJha3VyaWt1bGVyIjtzOjU6InJvdXRlIjtzOjIxOiJla3N0cmFrdXJpa3VsZXIuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo0O30=',1793424271),('pAi6TwFOdHHfz8D4fUNN1veq8paKaTvPvfPIhhcX',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTVhjemFtMkNVVmQxOVRkaTBkVzFvS25RbHVUTlVDOGxTN1hldXoweiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZWtzdHJha3VyaWt1bGVyIjtzOjU6InJvdXRlIjtzOjIxOiJla3N0cmFrdXJpa3VsZXIuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1793424302);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siswas`
--

DROP TABLE IF EXISTS `siswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siswas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ssw_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ssw_nisn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ssw_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ssw_major` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `siswas_ssw_nisn_unique` (`ssw_nisn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siswas`
--

LOCK TABLES `siswas` WRITE;
/*!40000 ALTER TABLE `siswas` DISABLE KEYS */;
/*!40000 ALTER TABLE `siswas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teachers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tcr_nip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tcr_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tcr_subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tcr_total_time` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trees`
--

DROP TABLE IF EXISTS `trees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tre_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tre_family` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tre_age` int NOT NULL,
  `tre_origin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tre_condition` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trees`
--

LOCK TABLES `trees` WRITE;
/*!40000 ALTER TABLE `trees` DISABLE KEYS */;
/*!40000 ALTER TABLE `trees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_logs`
--

DROP TABLE IF EXISTS `user_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint unsigned DEFAULT NULL,
  `subject_user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_logs_actor_id_created_at_index` (`actor_id`,`created_at`),
  KEY `user_logs_subject_user_id_created_at_index` (`subject_user_id`,`created_at`),
  CONSTRAINT `user_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_logs_subject_user_id_foreign` FOREIGN KEY (`subject_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_logs`
--

LOCK TABLES `user_logs` WRITE;
/*!40000 ALTER TABLE `user_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_panggilans`
--

DROP TABLE IF EXISTS `user_panggilans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_panggilans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nama` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utama` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_panggilans_user_id_nama_unique` (`user_id`,`nama`),
  CONSTRAINT `user_panggilans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_panggilans`
--

LOCK TABLES `user_panggilans` WRITE;
/*!40000 ALTER TABLE `user_panggilans` DISABLE KEYS */;
INSERT INTO `user_panggilans` VALUES (1,4,'Jimny',1,'2026-10-30 20:59:53','2026-10-30 20:59:53'),(2,4,'Syahnendi',0,'2026-10-30 21:00:11','2026-10-30 21:00:11'),(3,4,'Ramadhan',0,'2026-10-30 21:00:26','2026-10-30 21:00:26'),(4,4,'Jimmy',0,'2026-10-30 21:00:44','2026-10-30 21:00:44');
/*!40000 ALTER TABLE `user_panggilans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nisn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_tampil` enum('staff','anggota_ekskul','semua') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `bio` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','siswa','pembina') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'siswa',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `is_alumni` tinyint(1) NOT NULL DEFAULT '0',
  `angkatan_sekolah` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_lulus` smallint unsigned DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wallpaper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `palet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `navbar_gaya` enum('melayang','kaca','solid','ringkas') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'melayang',
  `kartu_gaya` enum('kaca','gradasi','polos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kaca',
  `kartu_aksen` enum('ikut_palet','indigo','rose','emerald','amber','sky') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ikut_palet',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `wallpaper_tipe` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `wallpaper_warna1` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wallpaper_warna2` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wallpaper_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wallpaper_mode` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tutupi',
  `aksen_tipe` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'preset',
  `aksen_warna1` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aksen_warna2` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aksen_gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aksen_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aksen_mode` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tutupi',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nisn_unique` (`nisn`)
) ENGINE=InnoDB AUTO_INCREMENT=409 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Sekolah','admin@gmail.com',NULL,'$2y$12$X6PZwNm3.G.2mHciwqxDseHxKv75DrzFKAjTs2n4Ar4HKtUOxMsUS','0000000000','Admin',NULL,'staff',NULL,'admin',0,0,NULL,NULL,NULL,NULL,'aurora','ringkas','polos','ikut_palet',1,NULL,'2026-10-22 20:53:40','2026-10-29 01:40:17','polos','#9b9ba6','#ec4899',NULL,'tutupi','preset','#6366f1','#ec4899',NULL,NULL,'tutupi'),(2,'Teh siti nurohmah','SitiNurrohmah@gmail.com',NULL,'$2y$12$8dWjfJgb5ArDmOxSIPu7XehmT345KHcRN.P3Dd03.pQbssB.yZ8.C',NULL,NULL,NULL,'staff',NULL,'pembina',0,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-22 20:53:41','2026-10-28 01:35:54','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(3,'Siswa test','siswa@gmail.com',NULL,'$2y$12$OAcN9iYEdX7onftmkkfAN.hcVdlR24cTYfy4dBk.Nv9bzQDBoM5Fa','1234567890','TEST',NULL,'staff',NULL,'siswa',0,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-22 20:53:41','2026-10-29 06:00:18','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(4,'Ramadhan Syahnendi','syahrama31dhan@gmail.com',NULL,'$2y$12$ByBtZ99ae1Inl7o9ktnDUe6qi3JCxoIuJWWRJ3zOlq3DXFtgS.shu','2526.010.050','XI PPLG','62895708291000','semua','Life is short, be weird','siswa',0,0,'2025 Angkatan 10',NULL,'avatars/CsYwnAaPCTsMRLapVCO4auXe2fpmdn4TTFBj1yJh.jpg','wallpapers/T3zO8ko9mGl2RJDoLuAphX0g2RZKdyjuJ7qgteNn.jpg','aurora','ringkas','polos','indigo',1,NULL,'2026-10-22 20:53:42','2026-10-30 21:24:22','foto','#6366f1','#ec4899','https://simdik.bandung.go.id/npsn/20219331?s=20202','tutupi','url','#ff0000','#ec4899',NULL,'https://simdik.bandung.go.id/uploads/galeri/20219331/20219331-767e447f-79c9-4615-bc05-ec56c503685c.jpg?1762740457','tutupi'),(7,'kang Idris','kidris@gmail.com',NULL,'$2y$12$jHK/tTKW9iQAAvoKCDrlRuOeOMYDDUxty4ja6.KAMPSFVizxuVQ6S',NULL,NULL,NULL,'staff',NULL,'pembina',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 01:33:30','2026-10-28 01:33:30','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(208,'Adilla Ufairah Azmi','adilla.ufairah.azmi@gmail.com',NULL,'$2y$12$0wk/6CrQZ1Cr2Q1PmqSOAeEqnoTPqe4uiXRlnUui6XnVz/0wEEnj.',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:13','2026-10-28 20:31:13','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(209,'Febry Intania Ardilla Permana','febry.intania.ardilla.permana@gmail.com',NULL,'$2y$12$Ux/781Ypvqws06mK39Au9Oc./oYwcrgzq6GrKee441gFIGIRzL2A6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:14','2026-10-28 20:31:14','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(210,'Kiagus Muhamad Ahmad Fadlan','kiagus.muhamad.ahmad.fadlan@gmail.com',NULL,'$2y$12$C7zQQ0ZN2CDuaOokjkt1tuKSbFbPUQpgdVEGTmps54gPUxV5DXHLu',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:15','2026-10-28 20:31:15','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(211,'Nabila Farihatun Aini','nabila.farihatun.aini@gmail.com',NULL,'$2y$12$Zos/ruBv/T5Oo8NGhrYg6OhhAFmOKwAAJu.s2XPvi0ttbVI9Cccle',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:16','2026-10-28 20:31:16','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(212,'Regina Mesya','regina.mesya@gmail.com',NULL,'$2y$12$ZezYrCLY1wC.O1y.bPcpvecgWI0wagi7r.RIigyOzn60kxAOiAFoO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:17','2026-10-28 20:31:17','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(213,'Sholehati Uswatun Hasanah','sholehati.uswatun.hasanah@gmail.com',NULL,'$2y$12$QQCr6Yxq6Cz0mpoT1JWtzeynO3b7CkFZPon8oPcQkwVI7.hfaa5wa',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:18','2026-10-28 20:31:18','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(214,'Ai Marsel Lipiana','ai.marsel.lipiana@gmail.com',NULL,'$2y$12$E6AOzwSHaW9ekNG3SdO4mO/5snr0mJ398zbfag0i2vcdm2Ljqd5D2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:19','2026-10-28 20:31:19','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(215,'Assyifa Dita Sahira','assyifa.dita.sahira@gmail.com',NULL,'$2y$12$KunLUIYgQv8h6xPXBOdxBeN6p/WTJoP639S24thXGt.3sHd9FolQK',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:20','2026-10-28 20:31:20','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(216,'Hadi Nur Rahma','hadi.nur.rahma@gmail.com',NULL,'$2y$12$/vGP.E6cE70R4mK8jtghAODNTvphj2cnEYuPeSn2jCRUypU2fiUv6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:21','2026-10-28 20:31:21','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(217,'M. Syafir Dzulfikar','m.syafir.dzulfikar@gmail.com',NULL,'$2y$12$GxF6oejGhPcmlWyuPHfodOB8ktz.P/W9znsNviEoeIHt0MX1GQQ6S',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:22','2026-10-28 20:31:22','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(218,'Qinthara Al-Ghifari Putra Muchtar','qinthara.al.ghifari.putra.muchtar@gmail.com',NULL,'$2y$12$XWzKA5u6oW2HIR7rwXfk/O5m3iUvrQZZKD/8pDha84BBFyNCtsbOS',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:23','2026-10-28 20:31:23','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(219,'Syalwa Rahmatul Ummah','syalwa.rahmatul.ummah@gmail.com',NULL,'$2y$12$/WcvGsGfvJVM.04S9nObmOqkr8q7bDtB.A.uBCknWrJjK/GgZM27S',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:24','2026-10-28 20:31:24','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(220,'Annisa Khumaira Setiawan','annisa.khumaira.setiawan@gmail.com',NULL,'$2y$12$P/faQ96MlRWqw.yz2blll.rB4/.gwHZJ9istjSXFRKbGz7jIBc8b6',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:25','2026-10-29 05:58:49','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(221,'Lestari Julyanti','lestari.julyanti@gmail.com',NULL,'$2y$12$X2mY.lSorX5doHWmSdNtzeY866jeLS7T.UgiUxiJmTmnn7r6lPete',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:26','2026-10-29 05:58:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(222,'Irfan Ainul Yaqin','irfan.ainul.yaqin@gmail.com',NULL,'$2y$12$LXSk7G5ZxAXscBlIRMbdueGK4f1NU4xWwaA0zcHRw1YGT3l1r0c9i',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:26','2026-10-29 05:54:22','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(223,'Syalmira','syalmira@gmail.com',NULL,'$2y$12$nNZVw7.EUv/aRHLgp/WAK.CC0dDsndqLooU0dDkI6H/WVVetx5EpW',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:27','2026-10-29 05:58:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(224,'Adly Raditya Hamdani','adly.raditya.hamdani@gmail.com',NULL,'$2y$12$1c0ynQOGi/tYoa/Ffl0MMez7Xj.9t2UulMlfhnbLd0QanzS3NOIDm',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:28','2026-10-29 05:54:19','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(225,'Ikhsan Herdiansyah','ikhsan.herdiansyah@gmail.com',NULL,'$2y$12$SzEYs8jrgowZDphIy2vy4uZf7hgY1XPcI3DQ77E.Kkfe5MguaTpd6',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:29','2026-10-29 05:54:22','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(226,'Dini Ruswanti','dini.ruswanti@gmail.com',NULL,'$2y$12$pKNaYHg0dxVbDI/v96ZCz.PXwdNqKR7vxcK4SBM5wATEx0A743SJO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:30','2026-10-28 20:31:30','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(227,'Adrian Shadra Rayana','adrian.shadra.rayana@gmail.com',NULL,'$2y$12$GUlC.Ul09NSp91nlOkBw3ugxmLnfCRL7UAGM5cNCqiTITcJu/Msrq',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:31','2026-10-28 20:31:31','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(228,'Firliano Azzami Alfarizi','firliano.azzami.alfarizi@gmail.com',NULL,'$2y$12$dYp6x8x/P8HLSXFG.ALM6uCiXyFoOmTpfHGTBCQRiZ.ATHSWqMs.6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:32','2026-10-28 20:31:32','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(229,'Lydian Naraulia Tanaya','lydian.naraulia.tanaya@gmail.com',NULL,'$2y$12$tLO.4phFDAhh8Nml1MmaAutH5mRCxfE8QdV4unNA0hKwpdof/NIp2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:33','2026-10-28 20:31:33','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(230,'Nararaya Tirta Muharram','nararaya.tirta.muharram@gmail.com',NULL,'$2y$12$1aQ445ig.QxGWom9.GS9W.yQolO9DmVmVcaBwVHNVLj0KJKLETU6u',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:34','2026-10-28 20:31:34','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(231,'Regita Juliani Herman','regita.juliani.herman@gmail.com',NULL,'$2y$12$z5IsqVo4fNPIxSsF8kAtougVV4Y.snc/s5Qf4hbUyQLGaE/1JAyGW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:35','2026-10-28 20:31:35','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(232,'Sofhia Alesha Rafa','sofhia.alesha.rafa@gmail.com',NULL,'$2y$12$v7W/pdFt/Co8jnHWbP5Nguj/hLn.bClXFiEvT/dVgIMPYIy7sm3jm',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:36','2026-10-28 20:31:36','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(233,'Ainur Mardiah','ainur.mardiah@gmail.com',NULL,'$2y$12$PBWhRL7nlctRKWIQz5JCzuUMJiXBnnpYm3OKdVZCax9o37d4nqYq2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:37','2026-10-28 20:31:37','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(234,'Audeval Nawasubhan','audeval.nawasubhan@gmail.com',NULL,'$2y$12$sZmJMreGEtnyvo.hS7ykJON.CX18jyejOerJMrcRTX388Wll1agFq',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:38','2026-10-28 20:31:38','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(235,'Hasna Adisti F Nurhasanah','hasna.adisti.f.nurhasanah@gmail.com',NULL,'$2y$12$ps1dRdKTtVtUDCHSTo6kseHEjPZaNAnJMkJy054RSlaijhqKInF5m',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:39','2026-10-28 20:31:39','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(236,'Marwah Julia Hasanah','marwah.julia.hasanah@gmail.com',NULL,'$2y$12$oL.oZALb8VtRhqYMHV7mluQ1KPIu6OscFM7M5hLfcoKEgROP.9UDG',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:39','2026-10-28 20:31:39','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(237,'Rafa Dhiyayi Haq','rafa.dhiyayi.haq@gmail.com',NULL,'$2y$12$8NEEu/QSBzVaJDMgl3ghHey22ebslSppTHr4Btd4rPORHxv7C6A56',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:40','2026-10-28 20:31:40','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(238,'Syam Fauza Syifa','syam.fauza.syifa@gmail.com',NULL,'$2y$12$kevbMEcItiPUYsXUaWB8vuId3zI6WrTFobOOTKT60yGchLM9FoEA6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:41','2026-10-28 20:31:41','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(239,'Arief Hafissatya','arief.hafissatya@gmail.com',NULL,'$2y$12$sbBvFFHdkvle8IWh667amORkUAoLIqCyJY3dJQb.B7FmMVSIxxQ0W',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:43','2026-10-29 05:58:49','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(240,'Milda Amelia','milda.amelia@gmail.com',NULL,'$2y$12$k/hjJRp5fINx/EEliUkdy.YWMQkULA50fzwXPsVPm6tQJvR6sfLFC',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:44','2026-10-29 05:58:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(241,'Radikia Sahran Akbar Wahyudin','radikia.sahran.akbar.wahyudin@gmail.com',NULL,'$2y$12$3E9YenIi.sWS3nj3DvCetu4yIsEW2Oo2YyaSnDQlSlbH7Nvu85CCW',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:45','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(242,'Alfathih Mahesatora Suwignyo','alfathih.mahesatora.suwignyo@gmail.com',NULL,'$2y$12$gUy9q6LEP4MlVFpFiPVgxeeb0GLxy5UMNWDS7G1J9iBG7x2S1K2Hi',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:46','2026-10-29 05:54:20','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(243,'Revaldi Nugraha Kasmanudin','revaldi.nugraha.kasmanudin@gmail.com',NULL,'$2y$12$S3iUb.Tsm.teJCAaLrj9ter1WQhXxNLCldxg2j7vvBopYkjenCwsu',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:48','2026-10-29 05:54:25','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(244,'Putri Keyla Solihat','putri.keyla.solihat@gmail.com',NULL,'$2y$12$drWBOqr9moiLAYib/umbZO1DDtlwuviem8sWC8EPLkBC2X7sDDmXq',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:49','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(245,'Nailah Assiffa','nailah.assiffa@gmail.com',NULL,'$2y$12$Vy.msGT68T/0Q3USlrwDmu7g0hlBjKXeyDeXwHd.bby67XZG4lmZW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:49','2026-10-28 20:31:49','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(246,'Ajeng Rosita','ajeng.rosita@gmail.com',NULL,'$2y$12$0yPGTQIlMmVhaYhXbsoR8eRQaPm41u0p8F.xi/otnHsJCIjdwREEW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:50','2026-10-28 20:31:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(247,'Freyanza Hidayat Putri','freyanza.hidayat.putri@gmail.com',NULL,'$2y$12$syt2vi0UF1iQrOTUDkaqP.oSRhi0mLuGHJws7hWOv7k10ud8a6kx6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:51','2026-10-28 20:31:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(248,'Marwah Khoerun Nisa','marwah.khoerun.nisa@gmail.com',NULL,'$2y$12$07NExoElT8s8sgygamFk6ukM80dwoPT.pCqx5w3dXADxfuyffP1wW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:52','2026-10-28 20:31:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(249,'Nasya Rahmaputri Surya','nasya.rahmaputri.surya@gmail.com',NULL,'$2y$12$/Cfs67rHflUyQO83IRGIzelXuV4RetBVB8r0OzUaRj0JBQ1MM0Lle',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:53','2026-10-28 20:31:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(250,'Reydina Nur Putri Wijaya','reydina.nur.putri.wijaya@gmail.com',NULL,'$2y$12$dYrGNwBGvl4M.IxePVLG3ONFbIOBPilW1ZVjNBVnjtYNNCOk4qx7a',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:55','2026-10-28 20:31:55','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(251,'Syafa Nafira','syafa.nafira@gmail.com',NULL,'$2y$12$QrZp2y5iKbabIz23XJKqtOzdwj75US5SkMdTt3RHGc7LVeC1lWVia',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:56','2026-10-28 20:31:56','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(252,'Alfio Fathan Shakieb','alfio.fathan.shakieb@gmail.com',NULL,'$2y$12$LhvtoEdCq7mD.tOduq7nQ.3YXbDHgyWXLFu6KHNBEJDYApcZypgz.',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:57','2026-10-28 20:31:57','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(253,'Bilal Fata Adzwan Firdaus','bilal.fata.adzwan.firdaus@gmail.com',NULL,'$2y$12$pIgugz2NJizZsd39w6IdFudjQMdgb5.GbJ7YQ0bNR1dT5B6sImFPW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:58','2026-10-28 20:31:58','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(254,'Hendi Baihaki','hendi.baihaki@gmail.com',NULL,'$2y$12$bN/ihyzcgt887Pyuwpl3uuRqZ0U7nJiyzRYCuNLYwCLOYuIsY.5zi',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:31:59','2026-10-28 20:31:59','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(255,'Mochammad Dicky Ramadhan Subrata','mochammad.dicky.ramadhan.subrata@gmail.com',NULL,'$2y$12$QIXuN.oiLTBxeSPcTT8i6eAY0jyClIxFomC/d6VCcL7SP6EXEFgTC',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:00','2026-10-28 20:32:00','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(256,'Raisya Elvira Putri','raisya.elvira.putri@gmail.com',NULL,'$2y$12$swgyLy9KJ4cfzn1muswTrOBbjchY.yNv9eGInt8kwJfzpOIMhdGxm',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:01','2026-10-28 20:32:01','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(257,'Syams Adipratama Sendjaya','syams.adipratama.sendjaya@gmail.com',NULL,'$2y$12$mKbukqhZvrYWa1f.Ol7V5el9huRV4DaADpOlBcz6Cm49YGS5lTzTa',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:02','2026-10-28 20:32:02','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(258,'Bianca Putri Ginanjar','bianca.putri.ginanjar@gmail.com',NULL,'$2y$12$jRT8TM7coshi.Iw.Ay.4reSUd.1cxC.JdTPwz91x3Ny6Ce1qaoLiS',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:03','2026-10-29 05:58:49','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(259,'Mufid Ash Shiddiq','mufid.ash.shiddiq@gmail.com',NULL,'$2y$12$ClRUF7FGn9RsEftjISj4VOFrhXW6lpMwyU1.buFi1UM6oA/BWxsIG',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:05','2026-10-29 05:58:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(260,'Rafhel Herlambang','rafhel.herlambang@gmail.com',NULL,'$2y$12$OyRrhfmJnGVqadNAGw3boerj/6NSPMbxah6sr1yFhWq/W/JP91hV2',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:06','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(261,'Zahra Melati Nurfauziyah','zahra.melati.nurfauziyah@gmail.com',NULL,'$2y$12$l9QSzr0NS7Hi2d1cgmDyB.wEpT.3TGlHbXszmI5VEWPRlulrT.8AO',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:07','2026-10-29 05:58:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(262,'Alifio Hud Maulana','alifio.hud.maulana@gmail.com',NULL,'$2y$12$8G/WR/.IHt4INAd1iQyireAuP9CZnBVcVxna4rF9pceFKPJg3CSMK',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:08','2026-10-29 05:54:20','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(263,'MIchael Dwistar Sinaga','michael.dwistar.sinaga@gmail.com',NULL,'$2y$12$LzSScHpo16h49J/vRDDg5eC0EWBVTTorVe3Nq5kFRdK9444OL2b2m',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:09','2026-10-29 05:54:22','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(264,'Alimah Nurhasanah','alimah.nurhasanah@gmail.com',NULL,'$2y$12$DcmoTPJ9BVnzFRhZC9nJX.NzhuuFwbC1fO9G5iVB7H5ydudJtKFze',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:10','2026-10-28 20:32:10','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(265,'Griselda Callysta Putri Chahensa','griselda.callysta.putri.chahensa@gmail.com',NULL,'$2y$12$xSwD3tdyvn7gkst1NawJdO4u2roL/ib.O1lR7BDxA4FdygTemdHBy',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:11','2026-10-28 20:32:11','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(266,'Mohamad Alli Fauziyah','mohamad.alli.fauziyah@gmail.com',NULL,'$2y$12$NYVKmLllf94ieaWrAQJvoO5U8ran773LcWKGJ.YO5JngkdY/XnL5m',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:12','2026-10-28 20:32:12','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(267,'Neng Rostiawati','neng.rostiawati@gmail.com',NULL,'$2y$12$wFjtwGS/0EVIAXWprwlsCOiOxISQCJE710FRwplpJPVxU8ai8FAiO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:13','2026-10-28 20:32:13','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(268,'Rival Nur Kholis','rival.nur.kholis@gmail.com',NULL,'$2y$12$uTOQXhrM9RciIJdzNv412u.ebfBW2vxsqvFwLUb.zhleikaqqUU/i',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:14','2026-10-28 20:32:14','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(269,'Thalita Adelia Putri Sofyan','thalita.adelia.putri.sofyan@gmail.com',NULL,'$2y$12$iPPITwYwqLDloiHyKWT0t.0S06WbZqpO8RGUwjYkpug/s/YqELlJS',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:15','2026-10-28 20:32:15','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(270,'Alvin Arya Pratama','alvin.arya.pratama@gmail.com',NULL,'$2y$12$zMf7b9h6jjewVY3/9F2Lju05kR7rapVeKnwRr6LnHZfAEnJPHRYIu',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:16','2026-10-28 20:32:16','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(271,'Citra Aura Salsabila','citra.aura.salsabila@gmail.com',NULL,'$2y$12$3nq0Ztk1NGgZ29usZZ36w.1eonarVx.PCKJ8ritqiXt3NN4b8nrXC',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:17','2026-10-28 20:32:17','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(272,'Ibrahim Darel Al Hadi Kusuma','ibrahim.darel.al.hadi.kusuma@gmail.com',NULL,'$2y$12$ffOmlAyoH81gjiql0dpf9.WfIP68OQZHC4tiCzjvYfWaMn93xd9eu',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:18','2026-10-28 20:32:18','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(273,'Muhamad Khairul Azka','muhamad.khairul.azka@gmail.com',NULL,'$2y$12$E/u6XbPRYFQMCySGgoEFbuHLcgvCZmdVWqmuy1aDlB8ELPoGchGU2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:18','2026-10-28 20:32:18','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(274,'Regy Dhywa Fitra','regy.dhywa.fitra@gmail.com',NULL,'$2y$12$aaUjH9GADOgKv/Ysh2DEluQNzZQM.I3pH5/OumEYVBt20bxvdGJR2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:19','2026-10-28 20:32:19','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(275,'Syifa Aulia Nuraeni','syifa.aulia.nuraeni@gmail.com',NULL,'$2y$12$TpWxW0x6HcA4l1FBJO8RDe/lxfpdtkyiXXHRx0k/OErYVUGC2U/MK',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:21','2026-10-28 20:32:21','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(276,'Destiani Aulia Azahra','destiani.aulia.azahra@gmail.com',NULL,'$2y$12$0lrpGpcBHtUqrZXUKeGrnORVwZBpnqZ6epgJoywko281MKvS0JwlG',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:22','2026-10-29 05:58:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(277,'Muh Algifari','muh.algifari@gmail.com',NULL,'$2y$12$N7pcO5qroGAIerLdTM/tWucoLftcBFwaRHZj70sC72/cDF3a0igCO',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:23','2026-10-29 05:58:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(278,'Raissya Dharmawan','raissya.dharmawan@gmail.com',NULL,'$2y$12$yGJYv31ebSX.ampAYGh/D.rR795JAgsxxuaZ2fdWWiDBmQfngORXW',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:25','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(279,'Zhafirah Annisah','zhafirah.annisah@gmail.com',NULL,'$2y$12$NWAySuR62hR.tKFf3b58m.qyHObgF7De6.SJXMvSEJQ97UtXdk9yS',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:26','2026-10-29 05:58:54','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(280,'Aliful Daffa Imtiyaz Munawar','aliful.daffa.imtiyaz.munawar@gmail.com',NULL,'$2y$12$AduzvaynVaCtvFoorqZDiuap0QXKQZHVNOwyEsnrOWgukVvBOwEKe',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:27','2026-10-29 05:54:20','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(281,'Muhammad Rizqi','muhammad.rizqi@gmail.com',NULL,'$2y$12$cZCQpBPMTz1rQnwWTWppfu/wmksBupGaxm/6V79qXp/0cFMOo81TS',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:28','2026-10-29 05:54:23','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(282,'Andhynie Nasya Maylanie','andhynie.nasya.maylanie@gmail.com',NULL,'$2y$12$1zqbR2RkzdrwwWeCGEtyQumByrMvyuq75cF9vsPAD7/.CJBFnsEBa',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:29','2026-10-28 20:32:29','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(283,'Hafiz Afathan','hafiz.afathan@gmail.com',NULL,'$2y$12$xYVB/z6nx1U9ttuMzOiY5evKUvKoQL2/x4.yJaND93gmCUIByXy7e',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:30','2026-10-28 20:32:30','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(284,'Muhamad Azka Ramdhani','muhamad.azka.ramdhani@gmail.com',NULL,'$2y$12$2oLsiG6LMU0HNPL7Jg8Ck.D82HE4vGCb2ND8SmeDgUeZ4fLv4ehRe',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:31','2026-10-28 20:32:31','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(285,'Nur Sifa Maulida Aliya','nur.sifa.maulida.aliya@gmail.com',NULL,'$2y$12$RzzotTVBFT4nVvX0L1VbzuKM15HfBbXi/4ng6viyN5oV6HVCHaguG',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:32','2026-10-28 20:32:32','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(286,'Rizki Muhamad Akbar','rizki.muhamad.akbar@gmail.com',NULL,'$2y$12$j2nKD6YL07sSUHzPxe0YxutU69oSvTAF1qvWXz3cOXlc7EgMjbx.O',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:33','2026-10-28 20:32:33','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(287,'Vanessa Pracilia Murtano','vanessa.pracilia.murtano@gmail.com',NULL,'$2y$12$wT0iBhEVbPUuNKDWp0YBCeGK77/mMJFPNk8q7n7VdFUYhEKPifmee',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:34','2026-10-28 20:32:34','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(288,'Alvino Pratama','alvino.pratama@gmail.com',NULL,'$2y$12$ZDHvm0a.trOh5CfxCKtZ3u8kfvYPTFm5006ehx4sGp/AjnTbLjCCC',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:35','2026-10-28 20:32:35','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(289,'Dani Supriatna','dani.supriatna@gmail.com',NULL,'$2y$12$92f2ObZKa6Q/ylusQ63cXu.7xgVyZzIxYP3mRXiu1RsUbaiceDakq',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:36','2026-10-28 20:32:36','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(290,'Intan Permata Putri','intan.permata.putri@gmail.com',NULL,'$2y$12$HHS/FqaoDVJatOjM/HSRcOWNBoM/UIwEuQWXj.wVUSbYs76wNgImu',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:36','2026-10-28 20:32:36','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(291,'Muhammad Faiq Azzam Setiawan','muhammad.faiq.azzam.setiawan@gmail.com',NULL,'$2y$12$ScJgRIyxd4T4eyLrv3giHO3mXTZLEE37pNuUN84WyMs5iOOE80IJG',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:37','2026-10-28 20:32:37','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(292,'Rifqy Al Farizy Ramadhana','rifqy.al.farizy.ramadhana@gmail.com',NULL,'$2y$12$GSivvwP.akyEf8k.tJNfyOfV0dSgqDCsHd4QoOmFtOyMm50uLj8Iy',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:38','2026-10-28 20:32:38','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(293,'Taufik Salsabil Salwa','taufik.salsabil.salwa@gmail.com',NULL,'$2y$12$pDMR8loDLYkyEy8iLpy4dOjn1xFObR0V3PqtaUEdQmmKBf7ZKsmDa',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:40','2026-10-28 20:32:40','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(294,'Febriyanti Putri Darmawan','febriyanti.putri.darmawan@gmail.com',NULL,'$2y$12$bEBDoQNEE3Jw3RYNb8LS0eL8Hfs9lAbLz0g.domLWRHgvkK9FwTqu',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:41','2026-10-29 05:58:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(295,'Muhamad Akbar Hidayatulloh','muhamad.akbar.hidayatulloh@gmail.com',NULL,'$2y$12$zBNB7sMlnJqbJemhIyBFpO8svBOUEC2SVfLNBTvX2cs/ZhqCrgu2.',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:42','2026-10-29 05:58:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(296,'Resta Aulya Syiham','resta.aulya.syiham@gmail.com',NULL,'$2y$12$e8dinT2Pz.r5n2H2Enf6Beo7mxMmmpOwniqymHoBxQR7pzndXLbtu',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:43','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(297,'Zildjian Mellodic Ashiilah','zildjian.mellodic.ashiilah@gmail.com',NULL,'$2y$12$IWBJeN5ZwXFQmXU1YfQ93esz6es1d9br1Cw.oKH6QUvO0DvXAGCEK',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:44','2026-10-29 05:58:54','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(298,'Azmi Tsaqib Hibatulloh','azmi.tsaqib.hibatulloh@gmail.com',NULL,'$2y$12$5RIws4WUOUjB2ozyo95Qxe6ik.gUbtByhmI9cXoU5CkV0ogk4dY/i',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:45','2026-10-29 05:54:20','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(299,'Rifky Demas Satya','rifky.demas.satya@gmail.com',NULL,'$2y$12$sCz3N5dPpZ4KSWFGYG6.Zefsx4dnVAVgd5.5GV9PCD8uXYqm514oW',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:46','2026-10-29 05:54:26','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(300,'Andi Fadilla Nur Annisa Pamessangi','andi.fadilla.nur.annisa.pamessangi@gmail.com',NULL,'$2y$12$veVGPtxC7dj3xiYUEbT3K.j2iFoR8DL2jDoi5ozLEik5GnS2bwcGq',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:47','2026-10-28 20:32:47','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(301,'Hanif Abdul Fathah','hanif.abdul.fathah@gmail.com',NULL,'$2y$12$h6ErfykJcJc3q88TQE6PPeCNWsG3p2fO52rP8xV.TWJzQvdP.l3WO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:47','2026-10-28 20:32:47','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(302,'Muhamad Ilham Hidayatulloh','muhamad.ilham.hidayatulloh@gmail.com',NULL,'$2y$12$H.TW4CIqGF0PHxuf8GvBiO/3jkeMQbgU0LXnjNTZbALtcpKxyMbIO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:48','2026-10-28 20:32:48','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(303,'Nurlaila Syaripatul Kamilah','nurlaila.syaripatul.kamilah@gmail.com',NULL,'$2y$12$MHVzvV5jgHciAhCHfjqVmeMOuIK98nNJ3ZWE3BqND0SHSMxPgHfwu',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:49','2026-10-28 20:32:49','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(304,'Rizky Ramadhan Kurniawan','rizky.ramadhan.kurniawan@gmail.com',NULL,'$2y$12$cHt0W2eDbAiwJnNVZYB9R.aZOtem6WkjgCU9Vdh.ZS9/IjcwrZJy6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:50','2026-10-28 20:32:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(305,'Virli Apriliansyah','virli.apriliansyah@gmail.com',NULL,'$2y$12$RsULynD2kpfDA7hbFs.qjOhHpfOXt9AVIkF6FCy4UBhvjx02l/fjO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:51','2026-10-28 20:32:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(306,'Amalia Putri','amalia.putri@gmail.com',NULL,'$2y$12$YKsDWPhCQn6lGXm7bqmBTuDba/Iou3PqkvRxviaC4vtMrIztfGBry',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:52','2026-10-28 20:32:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(307,'Dedi Kurniawan','dedi.kurniawan@gmail.com',NULL,'$2y$12$Y9FZpIKARipisAmFtL6b9ur7IM3QnyDnLhWGQUXNQYdzcP.stT.4K',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:53','2026-10-28 20:32:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(308,'Kayla Suci Berliana','kayla.suci.berliana@gmail.com',NULL,'$2y$12$/VFyUk3qSSrEr4vlNZ2pS.zDZVTKAvqikW2i501Ks5SDNW3s3tKiW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:54','2026-10-28 20:32:54','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(309,'Muhammad Faisal Reihan','muhammad.faisal.reihan@gmail.com',NULL,'$2y$12$E8mTmWzS50nE31e4vE08beJWpTz9qfoizcRoN3x9Z.QFQLEmHhFIW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:55','2026-10-28 20:32:55','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(310,'Safira Agistin','safira.agistin@gmail.com',NULL,'$2y$12$K602FuFAYA6xo6mcXP5hmOlJf7Ln/XsFwt5/1mx21ClA4481OrOVG',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:56','2026-10-28 20:32:56','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(311,'Vianti Almira Puteri','vianti.almira.puteri@gmail.com',NULL,'$2y$12$bloM/qqxCU6J7uM8aZ1FXeDN1AAiSQ0ORk4ZcwE2mWumcUo5.Eu46',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:57','2026-10-28 20:32:57','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(312,'Gerry Aditya','gerry.aditya@gmail.com',NULL,'$2y$12$k4EVKr4umjE1K8Raj3Yvj.8EXk.cgibku7pos3BFWNUKRaEm9q5da',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:32:58','2026-10-29 05:58:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(313,'Muhammad Fandi Andryana','muhammad.fandi.andryana@gmail.com',NULL,'$2y$12$PxOd./TlIwgMw17wuzRn0OekF7ZhWAtVPbgh6eCz/p7SXFtIDva7y',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:00','2026-10-29 05:58:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(314,'Rizki septian','rizki.septian@gmail.com',NULL,'$2y$12$..xxiD9awgIz2zqIsoI1X.6rRIQJJpWMZoo7RPYj9qu9TLr9dxxIy',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:01','2026-10-29 05:58:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(315,'David Sebastian Wijaya','david.sebastian.wijaya@gmail.com',NULL,'$2y$12$7uouZR2S3cJPeYjGLUzrH.RVQtY8ZeNfu.pd6fnxjmgsyHVxsQQNi',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:02','2026-10-29 05:54:20','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(316,'Muhammad Ikhsan Najid','muhammad.ikhsan.najid@gmail.com',NULL,'$2y$12$d6e8MBHS9OuciEWsCKxfp.xyIvDMG6ugUjMHtEsrAoLQdYq/57r5S',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:03','2026-10-29 05:54:23','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(317,'Rizki Aulia','rizki.aulia@gmail.com',NULL,'$2y$12$gkbJcYzVy95TBAIzUGx/0er4UUDw39gfg065fw8Yg/hkVDcCspQDe',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:04','2026-10-29 05:54:26','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(318,'Aqilla Haafidz Al Fiqri','aqilla.haafidz.al.fiqri@gmail.com',NULL,'$2y$12$uv../5m4AAxfivzUK2V41u4xXLgRC2ak/2QPVh.WdJtuh9pVJOq5K',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:05','2026-10-28 20:33:05','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(319,'Herawati','herawati@gmail.com',NULL,'$2y$12$OcXZAkSjFOPVXsS4fgn9VuHkT0UwzzGHM3dtXVrWpTBAcEmE1OcMW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:06','2026-10-28 20:33:06','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(320,'Muhamad Rifi Algifari','muhamad.rifi.algifari@gmail.com',NULL,'$2y$12$hPIJF6dLpk1hy1iYSMxXIuG4y.ZSXZv4TFOSBZiXqNPn8dy.tOus6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:07','2026-10-28 20:33:07','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(321,'Raffy Alyan Pratama Harefa','raffy.alyan.pratama.harefa@gmail.com',NULL,'$2y$12$49ojX0dIfErZDASax/6w/e9RNvImGMuaHaiZ2b.IxHzbcVhX4vR5i',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:09','2026-10-28 20:33:09','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(322,'Rizqy Radhyansah Sudradjat','rizqy.radhyansah.sudradjat@gmail.com',NULL,'$2y$12$ui7CTEvbidnsRupKCXgsXuCWJnqkvikVp/MG9hXs15cSNFOoHuQjC',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:10','2026-10-28 20:33:10','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(323,'Wicky Shandy Aghaza','wicky.shandy.aghaza@gmail.com',NULL,'$2y$12$q80oRTtDsXRFNPDcu0GqAun.B8S7KfPjJdVDCOwCPOEUR8NTkx/dW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:11','2026-10-28 20:33:11','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(324,'Ananda Pratama Soenardy','ananda.pratama.soenardy@gmail.com',NULL,'$2y$12$B2EtflnA11pgtmqE7EVtDu.oGWkm4bGtG0YIUP8xU9YTxUCSQkdcy',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:12','2026-10-28 20:33:12','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(325,'Devadhilan Anandhya Iskandar','devadhilan.anandhya.iskandar@gmail.com',NULL,'$2y$12$RxlxrYHQSmg87tFS6JdKNumvGPR/ZFVCAWdUQt5QNKbAU57GLdGb2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:13','2026-10-28 20:33:13','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(326,'M. Fadhil Adinata Mahardika','m.fadhil.adinata.mahardika@gmail.com',NULL,'$2y$12$mNmPJu4Rfayzaj4JdSPeHedmqneR6YZszSb3/7/T4mLD3PGfXIIra',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:14','2026-10-28 20:33:14','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(327,'Muhammad Rasyid Fadlan','muhammad.rasyid.fadlan@gmail.com',NULL,'$2y$12$UGp.geJx2lKGGKFKsBe/gud9VJDoH.SVCggq7T8uALQMy8v6MYbXO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:15','2026-10-28 20:33:15','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(328,'Salma Zahira Destianti','salma.zahira.destianti@gmail.com',NULL,'$2y$12$IBJnxPhaiPEOarDMikbrpuS7HXjm2/c6cLw2rr9MkcqC9jbpasuNe',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:16','2026-10-28 20:33:16','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(329,'Wildan Nuralisman','wildan.nuralisman@gmail.com',NULL,'$2y$12$Ha2Bo/1LdL55PuoOf8L6c.ifyrmceQZBysrmo6gtvKi73llQfpr16',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:17','2026-10-28 20:33:17','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(330,'Hany Natasya Hidayat Limbong','hany.natasya.hidayat.limbong@gmail.com',NULL,'$2y$12$aKH4ZQWCpwEa0XmvV/L.e.x6G7nzUocBc2uuJHbYxSxr8Uy5LjQiC',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:18','2026-10-29 05:58:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(331,'Muhammad Hamdani','muhammad.hamdani@gmail.com',NULL,'$2y$12$HqhaeDTUaxBfXq0Z7RMQZu.ONI6GMbfMnDAjEj2JufiFoGxtEFniK',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:19','2026-10-29 05:58:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(332,'Rifa Aulia Ramadhania','rifa.aulia.ramadhania@gmail.com',NULL,'$2y$12$H.RkxQR7WxvSVsNBT86bqeXGbNFQThFkqN37uzpOppk4FiY9uqLlC',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:19','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(333,'Dinda Zuhrupah Faujiah','dinda.zuhrupah.faujiah@gmail.com',NULL,'$2y$12$y7gqoIckoSXGJSSLe1V4beRv/JS0oOAllbrw2K1zkW0L2/HZmNd0W',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:20','2026-10-29 05:54:21','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(334,'Muhammad Sanaya Alghifari Suryatman','muhammad.sanaya.alghifari.suryatman@gmail.com',NULL,'$2y$12$oI.7TnNYUxGQiyAYn2XblusM3cd5YFyKWYCRZIvHw1Ndp/l2T.l9K',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:22','2026-10-29 05:54:24','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(335,'Roofi Nur Rohim','roofi.nur.rohim@gmail.com',NULL,'$2y$12$zjwrztc.yHMmb/P5xW9ApO7m4uwuZ6GxbFJMZatbQ0POEs/AkcrHq',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:23','2026-10-29 05:54:26','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(336,'Arsy An Najmust Tsaqib Dipraja','arsy.an.najmust.tsaqib.dipraja@gmail.com',NULL,'$2y$12$wOShaY7jJNNTBkb.Vi97qe7HJgwFVKM2MS2Mt2yvUmIAXAhreuFZO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:24','2026-10-28 20:33:24','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(337,'Husna Hanifah','husna.hanifah@gmail.com',NULL,'$2y$12$oC2opi1BEzD/nFUt/OHEhuKIsvHi0o2B3A4xL/hZKWRMtf4nHqcHC',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:25','2026-10-28 20:33:25','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(338,'Muhamad Rizki','muhamad.rizki@gmail.com',NULL,'$2y$12$1rxO0MzycsIVqD9UmMBZoeNkvMcRtTv06pv22KG4h7LLc6yzucvWu',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:26','2026-10-28 20:33:26','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(339,'Rahil Ridhwanulloh','rahil.ridhwanulloh@gmail.com',NULL,'$2y$12$QwPhVQynoBcOwPBRVqolq.CoSe0yu7VeUX4zEveSPsMqtxNYCbHYi',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:27','2026-10-28 20:33:27','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(340,'Salsabila Azzahra Putri','salsabila.azzahra.putri@gmail.com',NULL,'$2y$12$xv.MaoVwe2cB3SutP58ccuZTarOnj4C/093nocvCovBATJlNKNdM.',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:28','2026-10-28 20:33:28','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(341,'Zahira Putri Kalisya','zahira.putri.kalisya@gmail.com',NULL,'$2y$12$SLhexdDAr5RDHa2cuXatHeCqLDGQaOQbAPxgdiBO4AwuR0ZW7NaMe',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:29','2026-10-28 20:33:29','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(342,'Andhika Prathama','andhika.prathama@gmail.com',NULL,'$2y$12$JwEMQjZQgBZWurTxh6B5M.bhUWea1rcEfExteOYnlMCeEpQiQf6iu',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:30','2026-10-28 20:33:30','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(343,'Dzikri Alif Qomarudin Iskandar Putra','dzikri.alif.qomarudin.iskandar.putra@gmail.com',NULL,'$2y$12$5Ic.BX8bTpP/VlXCynaPh.1vZZSUBRJEgArk8a7D9FvvmKQDTnSPa',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:31','2026-10-28 20:33:31','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(344,'Meilani Alifa Nurtian','meilani.alifa.nurtian@gmail.com',NULL,'$2y$12$xvWjZf.Z8YjXom3DTw8uL.A2GU8.tmA2YYbPPrULqXFdyz3wlovQ6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:32','2026-10-28 20:33:32','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(345,'Nilam Xena Ardila Floren','nilam.xena.ardila.floren@gmail.com',NULL,'$2y$12$7hCEhq4cA7mOaNTB6y8wpeljQJKthrrHD4OdjnNfgmnAekeNP8iOC',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:33','2026-10-28 20:33:33','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(346,'Salsya Zitara Zuan','salsya.zitara.zuan@gmail.com',NULL,'$2y$12$bjfYZ8yT7RqVAjBLqYw/AOUAJgQQ7LA79StRdwFa/ysPklYk4FDzm',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:34','2026-10-28 20:33:34','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(347,'Windi Sihab Sari Setiawan','windi.sihab.sari.setiawan@gmail.com',NULL,'$2y$12$trzDCOqtgqIEEGspa1aSBOCNT/7rsPXB9dD2YE04r3imH.vg9OBgi',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:35','2026-10-28 20:33:35','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(348,'Jenny Zain Effendi','jenny.zain.effendi@gmail.com',NULL,'$2y$12$Av3wgryvM6De4uQCUPG8G.dvAUe3NeH9Jg1xlcjwB5KYyYAuBctL6',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:37','2026-10-29 05:58:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(349,'Muhammad Irgi Rivandi','muhammad.irgi.rivandi@gmail.com',NULL,'$2y$12$LPBypvW/dhOsGq0TTHoNhuri/yUDHtWCVG9ntWfCxnII9P3JseeO.',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:37','2026-10-29 05:58:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(350,'Rini Siti Komalia','rini.siti.komalia@gmail.com',NULL,'$2y$12$lZwFMXaIAAwfwNORXw.cmeZdyq7hl2InRwiziivaJ/exaycYTHYdm',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:39','2026-10-29 05:58:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(351,'Elvira Nova Zhaira','elvira.nova.zhaira@gmail.com',NULL,'$2y$12$j1Qc6gSG5ly8Tty.WVlAOeJvfSEphuwXdevT.izkpz5On8P.4nCZ2',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:40','2026-10-29 05:54:21','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(352,'Nanda Lulita Bela','nanda.lulita.bela@gmail.com',NULL,'$2y$12$mNXllntkfW5BLMyc4xNBgOiiPR.0lJHWf5jZ7qf7dnF5QbrF6Fayy',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:41','2026-10-29 05:54:24','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(353,'Satria Aditya Rizky','satria.aditya.rizky@gmail.com',NULL,'$2y$12$OoqPUQzAb0XykLrNZ7D5j.rII8pEzJ9VRCfhgzJ0o9KVVDAiuwZ/K',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:41','2026-10-29 05:54:26','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(354,'Destra Yuda Permana','destra.yuda.permana@gmail.com',NULL,'$2y$12$dMr8eE8qGJ6HYHw1YuPvl.QfT8F/yfZfaT3eNMtjDBZDoH1o7uTW.',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:42','2026-10-28 20:33:42','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(355,'Indra Maulana Putra','indra.maulana.putra@gmail.com',NULL,'$2y$12$Uz4F6VcN.XEmAcZd8MJ0DOfgHygVHrxI4IrKDnnk3mbDfdqdaiceq',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:43','2026-10-28 20:33:43','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(356,'Muhammad Alvino Setiawan','muhammad.alvino.setiawan@gmail.com',NULL,'$2y$12$aAbG/vV0aV2a95n.MaCOOuI1BQ7GORheW099VNvTrjnB.K7ltB/3e',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:44','2026-10-28 20:33:44','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(357,'Raihan Maulana Yusuf','raihan.maulana.yusuf@gmail.com',NULL,'$2y$12$HUIarych6zyj7b.Uff3Gbu9wMjezWV2P0hr/8PkAE/UQeuqlnCHlK',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:45','2026-10-28 20:33:45','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(358,'Salwa Anastasya','salwa.anastasya@gmail.com',NULL,'$2y$12$imBP8lh98/ZqkIXsGdnfXufFmZ1HLztM2Nq.F0STrbQtyaFKkj5Qy',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:46','2026-10-28 20:33:46','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(359,'Zahra Endang Oktaviana','zahra.endang.oktaviana@gmail.com',NULL,'$2y$12$1BxZudK8sk6Wlj8d7E0fp.Dk4M6S1FkmQv7hP6/GHthrpa87FKMrm',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:47','2026-10-28 20:33:47','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(360,'Anya Aprilia Resman','anya.aprilia.resman@gmail.com',NULL,'$2y$12$c2MLVV/QZ44CIM66gf1JHetfVIDTXiYvLpPEAYUqnB3w7X1HBoOUy',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:48','2026-10-28 20:33:48','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(361,'Elsa Thoriqoh Luthfany','elsa.thoriqoh.luthfany@gmail.com',NULL,'$2y$12$K4q6JrVJ7XOy.Xra3wuO9O4Ezr./Kt4e3dwn9h260k7X3ysLrj22y',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:49','2026-10-28 20:33:49','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(362,'Muhammad Akhlam','muhammad.akhlam@gmail.com',NULL,'$2y$12$yXgr.6HJq0r69tz7fa7YM.WA.vYKNTNxgZYYuSkZ5Et8oHw83TDCm',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:50','2026-10-28 20:33:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(363,'Nissa Aulia Fitri','nissa.aulia.fitri@gmail.com',NULL,'$2y$12$73mDt64ZRTp5x/XmgUxr8eMkLm8ixGaRbsEHt1nJiMKk6FkPTZpOy',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:51','2026-10-28 20:33:51','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(364,'Sapta Fitra Aidil','sapta.fitra.aidil@gmail.com',NULL,'$2y$12$eSdFKLOJ3Hl7isAeJR0xROo.LfyuVCg35OREJqIoNOEHeUMo5mum6',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:52','2026-10-28 20:33:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(365,'Zyaska Tianti Hanif','zyaska.tianti.hanif@gmail.com',NULL,'$2y$12$oLxgjLt26mJt8KnghsYs0eQ2o8wmODZc2/MDpBbAs0sz5ZyLLowTW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:52','2026-10-28 20:33:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(366,'Kaisan Al Hafiz Sidqi','kaisan.al.hafiz.sidqi@gmail.com',NULL,'$2y$12$3Y1iJnJielba.LnzMWTeOu/Ro1ahEh2BJscP3QJlH2uAaw5Zj4Yny',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:53','2026-10-29 05:58:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(367,'Muhammad Raif Anaqie Purnama','muhammad.raif.anaqie.purnama@gmail.com',NULL,'$2y$12$HGNpIwxlpyDGmRdpctl6peySwuPTS9MSvIT8DOVVS5Oqq/8LEQAJW',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:54','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(368,'Satria Fauzi Ahmad','satria.fauzi.ahmad@gmail.com',NULL,'$2y$12$vAjGg1fT20R0hEZqnaZezuXNTaC31nrfl5xEHgQPaaa3ink8miqDK',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:55','2026-10-29 05:58:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(369,'Evan Kusumah Setiawan','evan.kusumah.setiawan@gmail.com',NULL,'$2y$12$IVfTobvjvBB5fhQNm56GH.3gbmSj5AZVQEYLh1EvrwIkLdTbYFB2W',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:56','2026-10-29 05:54:21','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(370,'Naufal Azaria Nurman','naufal.azaria.nurman@gmail.com',NULL,'$2y$12$Fufx9UkWy4ODBQ7cOBLka.zwZenjH3Ga59X6C9xdqaiGxx55FBkyW',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:57','2026-10-29 05:54:24','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(371,'Muhammad Airlangga Alfarisi','muhammad.airlangga.alfarisi@gmail.com',NULL,'$2y$12$T062hzfz/2T36sHfPjr9/ugs53nJNxBr/3pR8.YmRn/u8m2U7bADi',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:58','2026-10-29 05:54:23','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(372,'Dini Aurelia','dini.aurelia@gmail.com',NULL,'$2y$12$vsx0UIAXIKCf6zuxgHCq8.9oZP9EWYyWGyM5wEpctFegyhfYpeD1K',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:33:59','2026-10-28 20:33:59','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(373,'Jundi Miftahuddin','jundi.miftahuddin@gmail.com',NULL,'$2y$12$bHASVnojKb6pumZHAxgCY.c.WVLx.1CMOOluc78TB0tSAoed1B3aG',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:00','2026-10-28 20:34:00','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(374,'Muhammad Keysha Arsalla Muttaqin','muhammad.keysha.arsalla.muttaqin@gmail.com',NULL,'$2y$12$RPGY1Mh0hnLD5yIPF27G1uo6aFGJ9TC7EN0bJw/ZRzIvhqvdRQ7b2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:01','2026-10-28 20:34:01','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(375,'Raissa Fauziah Qoyyuma','raissa.fauziah.qoyyuma@gmail.com',NULL,'$2y$12$K8Eck/ZusObN59xua3XBtO8ZUED/eWidHIPuxKu5d1ijGZLYEzCni',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:02','2026-10-28 20:34:02','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(376,'Sarah Nur Aisyah','sarah.nur.aisyah@gmail.com',NULL,'$2y$12$JI2zjZRNozeLYcqeFZlrDOWuliFyi/h1OZG/WzEO5NwwW/rTIgCi2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:03','2026-10-28 20:34:03','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(377,'Zakia Zahra Aulia','zakia.zahra.aulia@gmail.com',NULL,'$2y$12$rCo0UGP4o5bGiZtgPwxB.ej2WA4jmxoBWEf.046MD6GmwzkEhZ2LW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:04','2026-10-28 20:34:04','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(378,'Anzeli Naima Syakiri','anzeli.naima.syakiri@gmail.com',NULL,'$2y$12$FmU3Yqo2IdiK85RBRK9mKepqrqZ/aJorGMR7d1yZf79BAq0UrVGyK',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:05','2026-10-28 20:34:05','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(379,'Qianu Ahriza Ezar','qianu.ahriza.ezar@gmail.com',NULL,'$2y$12$CiDHSokEtULvDUuZxXXuY.CULJVnh4WXf1vfg0jxhsCG9VHt5t/jG',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:07','2026-10-28 20:34:07','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(380,'Muhammad Dimyati Salimuddin','muhammad.dimyati.salimuddin@gmail.com',NULL,'$2y$12$mRdhwxog058ETBB7yHNtKOyg9OBkWBjc./Um6ofHbE3PB/AlVvafK',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:08','2026-10-28 20:34:08','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(381,'Nizam Ahmad Maulana','nizam.ahmad.maulana@gmail.com',NULL,'$2y$12$b/PBKobQrf78xovnTnnEceCLDVVOtJaqcGeR/EiJ.neFVa.1yyvMW',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:09','2026-10-28 20:34:09','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(382,'Shilvany Dwi Nur Aisyah','shilvany.dwi.nur.aisyah@gmail.com',NULL,'$2y$12$9nwnO8c1PXLIrQuJOr.EXe9uNNVLIapi9/LY/PfqcjnRxI/qS0BdO',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:10','2026-10-28 20:34:10','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(383,'Kalya Janitra Hermawati','kalya.janitra.hermawati@gmail.com',NULL,'$2y$12$Y8pohv57.LbbVj/nMTHwVOu5BfqMkz96xWhM0xKyW0HRmAzgwExjC',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:11','2026-10-29 05:58:50','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(384,'Najla Zahirah','najla.zahirah@gmail.com',NULL,'$2y$12$LisVdFYqNTZZvNR.5ujpFesvXtSgA32xd47du6tfAzANgfQ5odQeG',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:12','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(385,'Sinari Bumi Pertiwi','sinari.bumi.pertiwi@gmail.com',NULL,'$2y$12$sRX4wKFj5WO8NjnreOV/BuuoRntIyGNP8yczcsnNJB8kqA8xkSz9O',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:13','2026-10-29 05:58:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(386,'Gilang Aditya Pratama','gilang.aditya.pratama@gmail.com',NULL,'$2y$12$BC82GaBGgAoC9JdO2dUwkOGvztFx0pNhZzt2VwkO5z./RZwv7KJBW',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:13','2026-10-29 05:54:21','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(387,'Raditya Muhamad Ramadhan','raditya.muhamad.ramadhan@gmail.com',NULL,'$2y$12$8i.PexohSbb1GXSmBS383upc2A49LtDtHMfH5foH9hrrR4UquM8yK',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:14','2026-10-29 05:54:24','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(389,'Ricky Ferdinan','ricky.ferdinan@gmail.com',NULL,'$2y$12$bxpEdczHEJBw9y4UgowkV.40UT23QqBh0El7e7XCszrJendeFLumm',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:16','2026-10-29 05:54:25','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(390,'Dita Putri Kirana','dita.putri.kirana@gmail.com',NULL,'$2y$12$YqtaHtrTtzgR0swOROTLsebSgZM6adAArPpkzJ2VVGsU4BZXBlSh2',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:17','2026-10-28 20:34:17','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(391,'Kalinda Labibah Zona','kalinda.labibah.zona@gmail.com',NULL,'$2y$12$Qf8E/MeJRj4bhU6h1TK3tOIzR9WdnCsJ9KG0wl5AhXE2jtdX/APla',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:18','2026-10-28 20:34:18','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(392,'Rasya Meylano Prasetio','rasya.meylano.prasetio@gmail.com',NULL,'$2y$12$G6WMzLP9G20wtH8MRslO..w9LbYJSPGZ1qz2CwefT0pJ7550txmi.',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:19','2026-10-28 20:34:19','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(393,'Saskia olivia','saskia.olivia@gmail.com',NULL,'$2y$12$Q/DNpYD0G5H8lE3l6IOppe9fnUVldgr6seO3YdNb5iD4FtF5qSw4W',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:19','2026-10-28 20:34:19','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(394,'Batrisya Rahmalia Ramadhan','batrisya.rahmalia.ramadhan@gmail.com',NULL,'$2y$12$lI9Kn29kaDRZr9s4u6b0T.pVR4FccZvulEzLlpp05GyhXApX3pz4a',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:20','2026-10-28 20:34:20','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(395,'Arkhan Firdaus Atallah','arkhan.firdaus.atallah@gmail.com',NULL,'$2y$12$7S/F9OesRA1n/MM.QZNKEuyJvJR8qWqnb1j.XQ4.xJiFoV0ONEJk.',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:22','2026-10-28 20:34:22','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(396,'Fadil Putra Maulana','fadil.putra.maulana@gmail.com',NULL,'$2y$12$dN7LZN8Q.ecGvqiayC6SCuVf9CeI0QnHIsCxY30u/D4lOhQio1V0i',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:23','2026-10-28 20:34:23','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(397,'Putri Gendis Khairunnisa','putri.gendis.khairunnisa@gmail.com',NULL,'$2y$12$Vs6ty.aKcqROw.Pju6qkHeguJHxPMfOLlj5E6qDc9Zg/XA4hi3/s.',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:24','2026-10-28 20:34:24','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(398,'Sihindy Oktavani','sihindy.oktavani@gmail.com',NULL,'$2y$12$.rv.oYVzo.CWGTflnXCbh.vCDRiYyeGWWCBaHtUYdVMFVkZAcjbgS',NULL,'X',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:26','2026-10-28 20:34:26','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(399,'Khansa Ataya Kurniawan','khansa.ataya.kurniawan@gmail.com',NULL,'$2y$12$pd.gXSL5ItUU2gZqyP4EWufMM7ETTQUleG6A9cQqugqsJQi5QqbRG',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:26','2026-10-29 05:59:36','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(400,'Pasya Aprilla','pasya.aprilla@gmail.com',NULL,'$2y$12$flYpi7m7KixUrYEdpu1Tkemi5Kbk8qrHtfMsToFv4uRurGxOABvWy',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:27','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(401,'Sinta Bella','sinta.bella@gmail.com',NULL,'$2y$12$fgI2i.mtYV/aff7lfqGHQeJJxm5MjQlgBNskVxoKCPpIaJC7pirSW',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:28','2026-10-29 05:58:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(402,'Gilang Wardani','gilang.wardani@gmail.com',NULL,'$2y$12$n19Llk1HFg2z.XTlMZ.M2OhC5uPKXEvMAUE1OMD8mPVRefueBOmVa',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:29','2026-10-29 05:54:21','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(403,'Radythia Ardian Ilham Giffary Nugroho','radythia.ardian.ilham.giffary.nugroho@gmail.com',NULL,'$2y$12$2Z9w5D2tQIJtmSz4j9TH.ef6UzEAl62OCrLfSabG3CyKLXYahySkK',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:30','2026-10-29 05:54:25','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(404,'Yemima Hanna Marcelia','yemima.hanna.marcelia@gmail.com',NULL,'$2y$12$ebLOcfJNXM32/7tSJO7RTOqzSNSojM8mioUEFk5f1pU1dLDh4NlyW',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:31','2026-10-29 05:54:27','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(405,'Rezky handika','rezky.handika@gmail.com',NULL,'$2y$12$vdL9sRMTAIb8Q9x7YCMYFesYFYezndW3iwWpKsZofCTSn/NuJSkay',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:33','2026-10-29 05:58:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(406,'Ricko Ferdinan','ricko.ferdinan@gmail.com',NULL,'$2y$12$2ye.JR5sNXm/opeRcylg5us/VkgRraHbdZKOpu0gm/ZeHRkPRRQrK',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:33','2026-10-29 05:54:25','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(407,'Syifa Satriani Putri Iskandar','syifa.satriani.putri.iskandar@gmail.com',NULL,'$2y$12$U8UfEct2p/LYL9C5mrEky.MuVie5kwqcevW2J1a71JPJjPGlaULdm',NULL,'XI DKV',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-28 20:34:34','2026-10-29 05:58:53','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi'),(408,'Dammarel Al Fawdzan Putra','dammarel.al.fawdzan.putra@gmail.com',NULL,'$2y$12$61HYmu7pfQlHuJOmc02wDOoHbl43foG4lrPISuPWONqMVFvkD.HLS',NULL,'XI PPLG',NULL,'staff',NULL,'siswa',1,0,NULL,NULL,NULL,NULL,NULL,'melayang','kaca','ikut_palet',1,NULL,'2026-10-29 06:06:52','2026-10-29 06:06:52','default',NULL,NULL,NULL,'tutupi','preset',NULL,NULL,NULL,NULL,'tutupi');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `violations`
--

DROP TABLE IF EXISTS `violations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `violations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vlt_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vlt_point` int NOT NULL,
  `vlt_punishment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violations`
--

LOCK TABLES `violations` WRITE;
/*!40000 ALTER TABLE `violations` DISABLE KEYS */;
/*!40000 ALTER TABLE `violations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-31 19:14:57
