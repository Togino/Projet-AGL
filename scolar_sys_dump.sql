-- MySQL dump 10.13  Distrib 9.7.0, for Win64 (x86_64)
--
-- Host: localhost    Database: scolar_sys
-- ------------------------------------------------------
-- Server version	9.7.0

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'a4391870-421a-11f1-bd1d-644ed78725e9:1-40';

--
-- Current Database: `scolar_sys`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `scolar_sys` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `scolar_sys`;

--
-- Table structure for table `admin_alerts`
--

DROP TABLE IF EXISTS `admin_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_alerts` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_mat_user` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_admin_alerts_target` (`target_mat_user`),
  KEY `fk_admin_alerts_creator` (`created_by`),
  CONSTRAINT `fk_admin_alerts_creator` FOREIGN KEY (`created_by`) REFERENCES `utilisateur` (`MAT`) ON DELETE SET NULL,
  CONSTRAINT `fk_admin_alerts_target` FOREIGN KEY (`target_mat_user`) REFERENCES `utilisateur` (`MAT`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_alerts`
--

LOCK TABLES `admin_alerts` WRITE;
/*!40000 ALTER TABLE `admin_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_jobs`
--

DROP TABLE IF EXISTS `backup_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_jobs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json NOT NULL,
  `status` enum('pending','processed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `scheduled_for` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_jobs`
--

LOCK TABLES `backup_jobs` WRITE;
/*!40000 ALTER TABLE `backup_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classe`
--

DROP TABLE IF EXISTS `classe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classe` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `niveau` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classe`
--

LOCK TABLES `classe` WRITE;
/*!40000 ALTER TABLE `classe` DISABLE KEYS */;
/*!40000 ALTER TABLE `classe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classe_semestres`
--

DROP TABLE IF EXISTS `classe_semestres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classe_semestres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `classe_id` int NOT NULL,
  `nom` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordre` int NOT NULL DEFAULT '1',
  `annee_scolaire` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '2024-2025',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_classe_semestre_nom` (`classe_id`,`nom`),
  KEY `fk_cs_classe` (`classe_id`),
  CONSTRAINT `fk_cs_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classe_semestres`
--

LOCK TABLES `classe_semestres` WRITE;
/*!40000 ALTER TABLE `classe_semestres` DISABLE KEYS */;
/*!40000 ALTER TABLE `classe_semestres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classe_modules`
--

DROP TABLE IF EXISTS `classe_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classe_modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `classe_id` int NOT NULL,
  `module_id` int NOT NULL,
  `semestre_id` int DEFAULT NULL,
  `semestre` tinyint NOT NULL,
  `coefficient` decimal(4,2) DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `heures` int DEFAULT NULL,
  `type_module` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Obligatoire',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_classe_module_semestre` (`classe_id`,`module_id`,`semestre`),
  KEY `fk_cm_module` (`module_id`),
  KEY `fk_cm_semestre` (`semestre_id`),
  CONSTRAINT `fk_cm_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_semestre` FOREIGN KEY (`semestre_id`) REFERENCES `classe_semestres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classe_modules`
--

LOCK TABLES `classe_modules` WRITE;
/*!40000 ALTER TABLE `classe_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `classe_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enseignant`
--

DROP TABLE IF EXISTS `enseignant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enseignant` (
  `MAT` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialisation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`MAT`),
  CONSTRAINT `fk_enseignant_pa` FOREIGN KEY (`MAT`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enseignant`
--

LOCK TABLES `enseignant` WRITE;
/*!40000 ALTER TABLE `enseignant` DISABLE KEYS */;
/*!40000 ALTER TABLE `enseignant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enseignement_affectation`
--

DROP TABLE IF EXISTS `enseignement_affectation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enseignement_affectation` (
  `MAT_enseignant` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_id` int NOT NULL,
  `classe_id` int NOT NULL,
  `annee_scolaire` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MAT_enseignant`,`module_id`,`classe_id`,`annee_scolaire`),
  UNIQUE KEY `uq_affectation_module_classe_annee` (`module_id`,`classe_id`,`annee_scolaire`),
  KEY `fk_affectation_classe` (`classe_id`),
  CONSTRAINT `fk_affectation_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_affectation_enseignant` FOREIGN KEY (`MAT_enseignant`) REFERENCES `enseignant` (`MAT`) ON DELETE CASCADE,
  CONSTRAINT `fk_affectation_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enseignement_affectation`
--

LOCK TABLES `enseignement_affectation` WRITE;
/*!40000 ALTER TABLE `enseignement_affectation` DISABLE KEYS */;
/*!40000 ALTER TABLE `enseignement_affectation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enseigner`
--

DROP TABLE IF EXISTS `enseigner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enseigner` (
  `MAT_PA` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_id` int NOT NULL,
  PRIMARY KEY (`MAT_PA`,`module_id`),
  KEY `fk_enseigner_module` (`module_id`),
  CONSTRAINT `fk_enseigner_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_enseigner_pa` FOREIGN KEY (`MAT_PA`) REFERENCES `pa` (`MAT`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enseigner`
--

LOCK TABLES `enseigner` WRITE;
/*!40000 ALTER TABLE `enseigner` DISABLE KEYS */;
/*!40000 ALTER TABLE `enseigner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `etudiant`
--

DROP TABLE IF EXISTS `etudiant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etudiant` (
  `MAT` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `classe_id` int NOT NULL,
  `annee_etude` year NOT NULL,
  `sexe` enum('M','F') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tuteur_nom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tuteur_prenom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tuteur_contact` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse_domicile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`MAT`),
  KEY `fk_etudiant_classe` (`classe_id`),
  CONSTRAINT `fk_etudiant_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`ID`),
  CONSTRAINT `fk_etudiant_utilisateur` FOREIGN KEY (`MAT`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `etudiant`
--

LOCK TABLES `etudiant` WRITE;
/*!40000 ALTER TABLE `etudiant` DISABLE KEYS */;
/*!40000 ALTER TABLE `etudiant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gerer`
--

DROP TABLE IF EXISTS `gerer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gerer` (
  `ID_note` int NOT NULL,
  `MAT_PA` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_note`,`MAT_PA`),
  KEY `fk_gere_PA` (`MAT_PA`),
  CONSTRAINT `fk_gere_PA` FOREIGN KEY (`MAT_PA`) REFERENCES `pa` (`MAT`),
  CONSTRAINT `fk_gerer_notes` FOREIGN KEY (`ID_note`) REFERENCES `note` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gerer`
--

LOCK TABLES `gerer` WRITE;
/*!40000 ALTER TABLE `gerer` DISABLE KEYS */;
/*!40000 ALTER TABLE `gerer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module`
--

LOCK TABLES `module` WRITE;
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
/*!40000 ALTER TABLE `module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note`
--

DROP TABLE IF EXISTS `note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `note` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `MAT_ET` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_id` int NOT NULL,
  `valeur` decimal(4,2) NOT NULL,
  `poids` int DEFAULT NULL,
  `penalite` tinyint DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `fk_note_module` (`module_id`),
  KEY `fk_note_etudiant` (`MAT_ET`),
  CONSTRAINT `fk_note_etudiant` FOREIGN KEY (`MAT_ET`) REFERENCES `etudiant` (`MAT`) ON DELETE CASCADE,
  CONSTRAINT `fk_note_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`ID`),
  CONSTRAINT `ck_valeur` CHECK ((`valeur` between 0 and 20)),
  CONSTRAINT `note_chk_1` CHECK ((`poids` between 0 and 100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note`
--

LOCK TABLES `note` WRITE;
/*!40000 ALTER TABLE `note` DISABLE KEYS */;
/*!40000 ALTER TABLE `note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa`
--

DROP TABLE IF EXISTS `pa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa` (
  `MAT` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MAT`),
  CONSTRAINT `fk_PA_utilisateur` FOREIGN KEY (`MAT`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE,
  CONSTRAINT `ck_post` CHECK ((`post` in (_cp850'ADMIN',_cp850'GESTIONNAIRE')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa`
--

LOCK TABLES `pa` WRITE;
/*!40000 ALTER TABLE `pa` DISABLE KEYS */;
INSERT INTO `pa` VALUES ('AD-0001','ADMIN');
/*!40000 ALTER TABLE `pa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.view','Acceder au tableau de bord','2026-05-04 14:37:44'),(2,'users.create','Creer un utilisateur','2026-05-04 14:37:44'),(3,'users.read','Consulter les utilisateurs','2026-05-04 14:37:44'),(4,'users.update','Modifier un utilisateur','2026-05-04 14:37:44'),(5,'users.delete','Supprimer logiquement un utilisateur','2026-05-04 14:37:44'),(6,'roles.manage','Gerer les roles et permissions','2026-05-04 14:37:44'),(7,'security.logs.read','Consulter les journaux de securite','2026-05-04 14:37:44');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1),(2,1),(1,2),(2,2),(1,3),(2,3),(1,4),(2,4),(1,5),(2,5),(1,6),(1,7);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'SUPER_ADMIN','Gestionnaire principal avec tous les droits','2026-05-04 14:37:44'),(2,'ADMIN','Administrateur fonctionnel','2026-05-04 14:37:44'),(3,'ENSEIGNANT','Personnel enseignant','2026-05-04 14:37:44'),(4,'GESTIONNAIRE','Gestionnaire scolaire','2026-05-04 14:37:44'),(5,'ETUDIANT','Compte etudiant','2026-05-04 14:37:44');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_logs`
--

DROP TABLE IF EXISTS `security_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `mat_user` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_security_logs_user` (`mat_user`),
  CONSTRAINT `fk_security_logs_user` FOREIGN KEY (`mat_user`) REFERENCES `utilisateur` (`MAT`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_logs`
--

LOCK TABLES `security_logs` WRITE;
/*!40000 ALTER TABLE `security_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateur` (
  `MAT` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_de_naissance` date NOT NULL,
  `email` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motdepasse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) DEFAULT '0',
  `role_id` int NOT NULL,
  `statut` tinyint(1) DEFAULT '1',
  `deleted_at` datetime DEFAULT NULL,
  `deactivation_reason` text COLLATE utf8mb4_unicode_ci,
  `deactivated_at` datetime DEFAULT NULL,
  `deactivated_by` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deletion_requested` tinyint(1) DEFAULT '0',
  `deletion_requested_at` datetime DEFAULT NULL,
  `deletion_requested_by` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reactivated_at` datetime DEFAULT NULL,
  `reactivated_by` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`MAT`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_utilisateur_role` (`role_id`),
  CONSTRAINT `fk_utilisateur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `ck_MAT` CHECK (((`MAT` like _cp850'AD-%') or (`MAT` like _cp850'GE-%') or (`MAT` like _cp850'ES-%') or (`MAT` like _cp850'ET-%')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateur`
--

LOCK TABLES `utilisateur` WRITE;
/*!40000 ALTER TABLE `utilisateur` DISABLE KEYS */;
INSERT INTO `utilisateur` VALUES ('AD-0001','Super','Admin','1990-01-01','admin@scolarsys.test','$2y$12$NGui9C56yT72CSrbW47Xde2O4bMO22wmJUi.pE85drzOleB3cJxxO',0,1,1,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-04 14:37:44','2026-05-04 14:40:59');
/*!40000 ALTER TABLE `utilisateur` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-04 15:59:14
