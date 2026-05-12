-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 10 mai 2026 à 19:54
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `scolar_sys`
--
-- --------------------------------------------------------
CREATE DATABASE scolar_sys; 
use scolar_sys;
--
-- Structure de la table `action_archive`
--

CREATE TABLE `action_archive` (
  `id` bigint(20) NOT NULL,
  `action_queue_id` bigint(20) DEFAULT NULL,
  `action_type` varchar(60) NOT NULL,
  `target_type` varchar(60) NOT NULL,
  `target_id` varchar(60) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `requested_by` varchar(10) NOT NULL,
  `executed_by` varchar(10) DEFAULT NULL,
  `execution_status` enum('success','failed') NOT NULL DEFAULT 'success',
  `execution_message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `action_archive`
--

INSERT INTO `action_archive` (`id`, `action_queue_id`, `action_type`, `target_type`, `target_id`, `payload`, `requested_by`, `executed_by`, `execution_status`, `execution_message`, `created_at`) VALUES
(1, 1, 'SOFT_DELETE_USER', 'utilisateur', 'GE-0006', '{\"mat\":\"GE-0006\",\"nom\":\"6\",\"prenom\":\"gestionnaire\",\"email\":\"gestionnaire6@gmail.com\"}', 'AD-0001', 'GE-0001', 'success', 'Desactivation utilisateur executee apres confirmations.', '2026-05-10 15:15:46'),
(2, 2, 'DELETE_CLASSE', 'classe', '7', '{\"classe_id\":7,\"nom\":\"Cloud Computing\",\"niveau\":\"M2\"}', 'AD-0001', 'GE-0001', 'failed', 'Suppression refusee: cette classe contient des etudiants.', '2026-05-10 15:29:10'),
(3, 1, 'CANCEL_SOFT_DELETE_USER', 'utilisateur', 'GE-0006', '{\"mat\":\"GE-0006\",\"nom\":\"6\",\"prenom\":\"gestionnaire\",\"email\":\"gestionnaire6@gmail.com\"}', 'AD-0001', 'AD-0001', 'success', 'Annulation archive: Compte reactive avec succes.', '2026-05-10 17:31:13'),
(4, 1, 'CANCEL_SOFT_DELETE_USER', 'utilisateur', 'GE-0006', '{\"mat\":\"GE-0006\",\"nom\":\"6\",\"prenom\":\"gestionnaire\",\"email\":\"gestionnaire6@gmail.com\"}', 'AD-0001', 'AD-0001', 'success', 'Annulation archive: Compte reactive avec succes.', '2026-05-10 17:31:16'),
(5, 1, 'CANCEL_SOFT_DELETE_USER', 'utilisateur', 'GE-0006', '{\"mat\":\"GE-0006\",\"nom\":\"6\",\"prenom\":\"gestionnaire\",\"email\":\"gestionnaire6@gmail.com\"}', 'AD-0001', 'AD-0001', 'success', 'Annulation archive: Compte reactive avec succes.', '2026-05-10 17:31:20'),
(6, 1, 'CANCEL_SOFT_DELETE_USER', 'utilisateur', 'GE-0006', '{\"mat\":\"GE-0006\",\"nom\":\"6\",\"prenom\":\"gestionnaire\",\"email\":\"gestionnaire6@gmail.com\"}', 'AD-0001', 'AD-0001', 'success', 'Annulation archive: Compte reactive avec succes.', '2026-05-10 17:31:24');

-- --------------------------------------------------------

--
-- Structure de la table `action_confirmations`
--

CREATE TABLE `action_confirmations` (
  `id` bigint(20) NOT NULL,
  `action_queue_id` bigint(20) NOT NULL,
  `confirmed_by` varchar(10) NOT NULL,
  `decision` enum('approve','reject') NOT NULL DEFAULT 'approve',
  `comment_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `action_confirmations`
--

INSERT INTO `action_confirmations` (`id`, `action_queue_id`, `confirmed_by`, `decision`, `comment_text`, `created_at`) VALUES
(1, 1, 'GE-0001', 'approve', NULL, '2026-05-10 15:15:46'),
(2, 2, 'GE-0001', 'approve', NULL, '2026-05-10 15:29:10'),
(3, 3, 'GE-0002', 'approve', NULL, '2026-05-10 15:36:16'),
(6, 3, 'GE-0007', 'approve', NULL, '2026-05-10 17:25:49');

-- --------------------------------------------------------

--
-- Structure de la table `action_queue`
--

CREATE TABLE `action_queue` (
  `id` bigint(20) NOT NULL,
  `action_type` varchar(60) NOT NULL,
  `target_type` varchar(60) NOT NULL,
  `target_id` varchar(60) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `requested_by` varchar(10) NOT NULL,
  `status` enum('pending','approved','rejected','executed','failed') NOT NULL DEFAULT 'pending',
  `required_confirmations` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `action_queue`
--

INSERT INTO `action_queue` (`id`, `action_type`, `target_type`, `target_id`, `payload`, `requested_by`, `status`, `required_confirmations`, `created_at`, `processed_at`) VALUES
(1, 'SOFT_DELETE_USER', 'utilisateur', 'GE-0006', '{\"mat\":\"GE-0006\",\"nom\":\"6\",\"prenom\":\"gestionnaire\",\"email\":\"gestionnaire6@gmail.com\"}', 'AD-0001', 'executed', 1, '2026-05-10 15:11:53', '2026-05-10 15:15:46'),
(2, 'DELETE_CLASSE', 'classe', '7', '{\"classe_id\":7,\"nom\":\"Cloud Computing\",\"niveau\":\"M2\"}', 'AD-0001', 'failed', 1, '2026-05-10 15:18:27', '2026-05-10 15:29:10'),
(3, 'SOFT_DELETE_USER', 'etudiant', 'ET-0071', '{\"mat\":\"ET-0071\",\"nom\":\"Cisse\",\"prenom\":\"Ibrahim\",\"email\":\"etudiantcloudcomputing10@demo.local\"}', 'GE-0001', 'pending', 4, '2026-05-10 15:28:56', NULL),
(4, 'DISABLE_TEACHER', 'enseignant', 'ES-0002', '{\"mat\":\"ES-0002\"}', 'AD-0001', 'pending', 1, '2026-05-10 17:31:02', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `admin_alerts`
--

CREATE TABLE `admin_alerts` (
  `id` bigint(20) NOT NULL,
  `type` varchar(100) NOT NULL,
  `severity` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `target_mat_user` varchar(10) DEFAULT NULL,
  `created_by` varchar(10) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admin_alerts`
--

INSERT INTO `admin_alerts` (`id`, `type`, `severity`, `title`, `message`, `target_mat_user`, `created_by`, `is_read`, `created_at`) VALUES
(1, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation utilisateur', 'La demande #1 pour desactiver GE-0006 attend des confirmations.', 'GE-0001', 'AD-0001', 0, '2026-05-10 15:11:53'),
(2, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation utilisateur', 'La demande #1 pour desactiver GE-0006 attend des confirmations.', 'GE-0002', 'AD-0001', 0, '2026-05-10 15:11:53'),
(3, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation utilisateur', 'La demande #1 pour desactiver GE-0006 attend des confirmations.', 'GE-0003', 'AD-0001', 0, '2026-05-10 15:11:53'),
(4, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation utilisateur', 'La demande #1 pour desactiver GE-0006 attend des confirmations.', 'GE-0004', 'AD-0001', 0, '2026-05-10 15:11:53'),
(5, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation utilisateur', 'La demande #1 pour desactiver GE-0006 attend des confirmations.', 'GE-0005', 'AD-0001', 0, '2026-05-10 15:11:53'),
(6, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation utilisateur', 'La demande #1 pour desactiver GE-0006 attend des confirmations.', 'GE-0006', 'AD-0001', 0, '2026-05-10 15:11:53'),
(7, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #1).', 'AD-0001', 'GE-0001', 0, '2026-05-10 15:15:46'),
(8, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #1).', 'GE-0001', 'GE-0001', 0, '2026-05-10 15:15:46'),
(9, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #1).', 'GE-0002', 'GE-0001', 0, '2026-05-10 15:15:46'),
(10, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #1).', 'GE-0003', 'GE-0001', 0, '2026-05-10 15:15:46'),
(11, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #1).', 'GE-0004', 'GE-0001', 0, '2026-05-10 15:15:46'),
(12, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #1).', 'GE-0005', 'GE-0001', 0, '2026-05-10 15:15:46'),
(13, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: suppression classe', 'La demande #2 pour supprimer la classe Cloud Computing attend des confirmations.', 'GE-0001', 'AD-0001', 0, '2026-05-10 15:18:27'),
(14, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: suppression classe', 'La demande #2 pour supprimer la classe Cloud Computing attend des confirmations.', 'GE-0002', 'AD-0001', 0, '2026-05-10 15:18:27'),
(15, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: suppression classe', 'La demande #2 pour supprimer la classe Cloud Computing attend des confirmations.', 'GE-0003', 'AD-0001', 0, '2026-05-10 15:18:27'),
(16, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: suppression classe', 'La demande #2 pour supprimer la classe Cloud Computing attend des confirmations.', 'GE-0004', 'AD-0001', 0, '2026-05-10 15:18:27'),
(17, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: suppression classe', 'La demande #2 pour supprimer la classe Cloud Computing attend des confirmations.', 'GE-0005', 'AD-0001', 0, '2026-05-10 15:18:27'),
(18, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation etudiant', 'La demande #3 pour desactiver l\'etudiant ET-0071 attend des confirmations.', 'AD-0001', 'GE-0001', 0, '2026-05-10 15:28:56'),
(19, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation etudiant', 'La demande #3 pour desactiver l\'etudiant ET-0071 attend des confirmations.', 'GE-0002', 'GE-0001', 0, '2026-05-10 15:28:56'),
(20, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation etudiant', 'La demande #3 pour desactiver l\'etudiant ET-0071 attend des confirmations.', 'GE-0003', 'GE-0001', 0, '2026-05-10 15:28:56'),
(21, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation etudiant', 'La demande #3 pour desactiver l\'etudiant ET-0071 attend des confirmations.', 'GE-0004', 'GE-0001', 0, '2026-05-10 15:28:56'),
(22, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation etudiant', 'La demande #3 pour desactiver l\'etudiant ET-0071 attend des confirmations.', 'GE-0005', 'GE-0001', 0, '2026-05-10 15:28:56'),
(23, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #2).', 'AD-0001', 'GE-0001', 0, '2026-05-10 15:29:10'),
(24, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #2).', 'GE-0001', 'GE-0001', 0, '2026-05-10 15:29:10'),
(25, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #2).', 'GE-0002', 'GE-0001', 0, '2026-05-10 15:29:10'),
(26, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #2).', 'GE-0003', 'GE-0001', 0, '2026-05-10 15:29:10'),
(27, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #2).', 'GE-0004', 'GE-0001', 0, '2026-05-10 15:29:10'),
(28, 'SENSITIVE_ACTION_EXECUTED', 'medium', 'Action sensible executee', 'Une action sensible a ete validee puis traitee (ID demande #2).', 'GE-0005', 'GE-0001', 0, '2026-05-10 15:29:10'),
(29, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'AD-0001', 'GE-0002', 0, '2026-05-10 15:36:16'),
(30, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0001', 'GE-0002', 0, '2026-05-10 15:36:16'),
(31, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0003', 'GE-0002', 0, '2026-05-10 15:36:16'),
(32, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0004', 'GE-0002', 0, '2026-05-10 15:36:16'),
(33, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0005', 'GE-0002', 0, '2026-05-10 15:36:16'),
(34, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'AD-0001', 'GE-0007', 0, '2026-05-10 17:25:49'),
(35, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0001', 'GE-0007', 0, '2026-05-10 17:25:49'),
(36, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0002', 'GE-0007', 0, '2026-05-10 17:25:49'),
(37, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0003', 'GE-0007', 0, '2026-05-10 17:25:49'),
(38, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0004', 'GE-0007', 0, '2026-05-10 17:25:49'),
(39, 'SENSITIVE_ACTION_CONFIRMED', 'low', 'Nouvelle confirmation de demande sensible', 'La demande #3 a recu une nouvelle confirmation.', 'GE-0005', 'GE-0007', 0, '2026-05-10 17:25:49'),
(40, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation enseignant', 'La demande #4 pour desactiver l\'enseignant ES-0002 attend des confirmations.', 'GE-0001', 'AD-0001', 0, '2026-05-10 17:31:02'),
(41, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation enseignant', 'La demande #4 pour desactiver l\'enseignant ES-0002 attend des confirmations.', 'GE-0002', 'AD-0001', 0, '2026-05-10 17:31:02'),
(42, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation enseignant', 'La demande #4 pour desactiver l\'enseignant ES-0002 attend des confirmations.', 'GE-0003', 'AD-0001', 0, '2026-05-10 17:31:02'),
(43, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation enseignant', 'La demande #4 pour desactiver l\'enseignant ES-0002 attend des confirmations.', 'GE-0004', 'AD-0001', 0, '2026-05-10 17:31:02'),
(44, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation enseignant', 'La demande #4 pour desactiver l\'enseignant ES-0002 attend des confirmations.', 'GE-0005', 'AD-0001', 0, '2026-05-10 17:31:02'),
(45, 'SENSITIVE_ACTION_PENDING', 'high', 'Validation requise: desactivation enseignant', 'La demande #4 pour desactiver l\'enseignant ES-0002 attend des confirmations.', 'GE-0007', 'AD-0001', 0, '2026-05-10 17:31:02'),
(46, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'AD-0001', 'AD-0001', 0, '2026-05-10 17:31:13'),
(47, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0001', 'AD-0001', 0, '2026-05-10 17:31:13'),
(48, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0002', 'AD-0001', 0, '2026-05-10 17:31:13'),
(49, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0003', 'AD-0001', 0, '2026-05-10 17:31:13'),
(50, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0004', 'AD-0001', 0, '2026-05-10 17:31:13'),
(51, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0005', 'AD-0001', 0, '2026-05-10 17:31:13'),
(52, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0006', 'AD-0001', 0, '2026-05-10 17:31:13'),
(53, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0007', 'AD-0001', 0, '2026-05-10 17:31:13'),
(54, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'AD-0001', 'AD-0001', 0, '2026-05-10 17:31:16'),
(55, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0001', 'AD-0001', 0, '2026-05-10 17:31:16'),
(56, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0002', 'AD-0001', 0, '2026-05-10 17:31:16'),
(57, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0003', 'AD-0001', 0, '2026-05-10 17:31:16'),
(58, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0004', 'AD-0001', 0, '2026-05-10 17:31:16'),
(59, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0005', 'AD-0001', 0, '2026-05-10 17:31:16'),
(60, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0006', 'AD-0001', 0, '2026-05-10 17:31:16'),
(61, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0007', 'AD-0001', 0, '2026-05-10 17:31:16'),
(62, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'AD-0001', 'AD-0001', 0, '2026-05-10 17:31:20'),
(63, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0001', 'AD-0001', 0, '2026-05-10 17:31:20'),
(64, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0002', 'AD-0001', 0, '2026-05-10 17:31:20'),
(65, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0003', 'AD-0001', 0, '2026-05-10 17:31:20'),
(66, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0004', 'AD-0001', 0, '2026-05-10 17:31:20'),
(67, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0005', 'AD-0001', 0, '2026-05-10 17:31:20'),
(68, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0006', 'AD-0001', 0, '2026-05-10 17:31:20'),
(69, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0007', 'AD-0001', 0, '2026-05-10 17:31:20'),
(70, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'AD-0001', 'AD-0001', 0, '2026-05-10 17:31:24'),
(71, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0001', 'AD-0001', 0, '2026-05-10 17:31:24'),
(72, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0002', 'AD-0001', 0, '2026-05-10 17:31:24'),
(73, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0003', 'AD-0001', 0, '2026-05-10 17:31:24'),
(74, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0004', 'AD-0001', 0, '2026-05-10 17:31:24'),
(75, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0005', 'AD-0001', 0, '2026-05-10 17:31:24'),
(76, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0006', 'AD-0001', 0, '2026-05-10 17:31:24'),
(77, 'ARCHIVE_CANCELLED', 'medium', 'Action annulee depuis archive', 'L\'action archivee #1 a ete annulee par AD-0001.', 'GE-0007', 'AD-0001', 0, '2026-05-10 17:31:24');

-- --------------------------------------------------------

--
-- Structure de la table `backup_jobs`
--

CREATE TABLE `backup_jobs` (
  `id` bigint(20) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `status` enum('pending','processed','failed') NOT NULL DEFAULT 'pending',
  `scheduled_for` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `backup_jobs`
--

INSERT INTO `backup_jobs` (`id`, `entity_type`, `entity_id`, `action`, `payload`, `status`, `scheduled_for`, `processed_at`, `created_at`) VALUES
(1, 'utilisateur', 'ET-0001', 'create', '{\"matricule\":\"ET-0001\",\"nom\":\"Doumbia\",\"prenom\":\"Mariam\",\"date_de_naissance\":\"2007-11-10\",\"email\":\"mariamdoum02@gmail.com\",\"role_id\":5,\"statut\":1,\"motdepasse\":\"$2y$10$AbB1QDRigpcURJPyt8ZE9uRcICuHNBwYI20cnkjGjDBWU1q9SR2DG\",\"created_by\":\"AD-0001\",\"updated_by\":\"AD-0001\"}', 'pending', '2026-04-12 17:50:46', NULL, '2026-04-02 17:50:46'),
(2, 'utilisateur', 'ET-0001', 'create', '{\"matricule\":\"ET-0001\",\"nom\":\"TRAORE\",\"prenom\":\"ISSAKA\",\"date_de_naissance\":\"2006-11-10\",\"email\":\"issaka02@gmail.com\",\"role_id\":5,\"statut\":1,\"student_profile\":{\"classe_id\":1,\"annee_etude\":\"2026\"},\"motdepasse\":\"$2y$10$hpVqJUKKsMrfMe4zEuRRR.MJxzB5t2TAg4UxENqdMVrSDKUQj\\/4TK\",\"created_by\":\"AD-0001\",\"updated_by\":\"AD-0001\"}', 'pending', '2026-04-17 04:35:36', NULL, '2026-04-07 04:35:36'),
(3, 'utilisateur', 'ES-0001', 'create', '{\"matricule\":\"ES-0001\",\"nom\":\"Diarra\",\"prenom\":\"Maimouna\",\"date_de_naissance\":\"2005-11-20\",\"email\":\"maimouna02@gmail.com\",\"role_id\":3,\"statut\":1,\"student_profile\":null,\"motdepasse\":\"$2y$10$s6uQJs8mEFa6AearZYgJXuvYWsHILG4WB9mjis3BZyX2HIEXg4\\/iu\",\"created_by\":\"AD-0001\",\"updated_by\":\"AD-0001\"}', 'pending', '2026-04-17 06:54:10', NULL, '2026-04-07 06:54:10');

-- --------------------------------------------------------

--
-- Structure de la table `classe`
--

CREATE TABLE `classe` (
  `ID` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `niveau` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `classe`
--

INSERT INTO `classe` (`ID`, `nom`, `niveau`) VALUES
(1, 'ILD', 'L1'),
(2, 'Reseaux', 'L3'),
(3, 'Intelligence Artificielle', 'M1'),
(4, 'Big Data', 'M2'),
(5, 'Cybersecurite', 'M1'),
(6, 'Genie Logiciel', 'L3'),
(7, 'Cloud Computing', 'M2');

-- --------------------------------------------------------

--
-- Structure de la table `classe_modules`
--

CREATE TABLE `classe_modules` (
  `id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `semestre_id` int(11) DEFAULT NULL,
  `semestre` tinyint(4) NOT NULL,
  `coefficient` decimal(4,2) DEFAULT NULL,
  `credits` int(11) DEFAULT NULL,
  `heures` int(11) DEFAULT NULL,
  `type_module` varchar(30) DEFAULT 'Obligatoire',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `classe_modules`
--

INSERT INTO `classe_modules` (`id`, `classe_id`, `module_id`, `semestre_id`, `semestre`, `coefficient`, `credits`, `heures`, `type_module`, `created_at`) VALUES
(1, 1, 1, 1, 1, 3.00, NULL, 24, 'Obligatoire', '2026-05-10 14:37:16'),
(2, 1, 2, 1, 1, 3.00, NULL, 24, 'Obligatoire', '2026-05-10 14:37:36'),
(3, 1, 3, 1, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(4, 1, 4, 1, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(5, 1, 5, 1, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(6, 1, 6, 1, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(7, 1, 7, 1, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(8, 1, 8, 2, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(9, 1, 9, 2, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(10, 1, 10, 2, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(11, 1, 11, 2, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(12, 1, 12, 2, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(13, 2, 13, 3, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(14, 2, 14, 3, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(15, 2, 15, 3, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(16, 2, 16, 3, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(17, 2, 17, 3, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(18, 2, 18, 4, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(19, 2, 19, 4, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(20, 2, 20, 4, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(21, 2, 21, 4, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(22, 2, 22, 4, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(23, 3, 23, 5, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(24, 3, 24, 5, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(25, 3, 25, 5, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(26, 3, 26, 5, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(27, 3, 27, 5, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(28, 3, 28, 6, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(29, 3, 29, 6, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(30, 3, 30, 6, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(31, 3, 31, 6, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(32, 3, 32, 6, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(33, 4, 33, 7, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(34, 4, 34, 7, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(35, 4, 35, 7, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(36, 4, 36, 7, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(37, 4, 37, 7, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(38, 4, 38, 8, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(39, 4, 39, 8, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(40, 4, 40, 8, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(41, 4, 41, 8, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(42, 4, 42, 8, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(43, 5, 43, 9, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(44, 5, 44, 9, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(45, 5, 45, 9, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(46, 5, 46, 9, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(47, 5, 47, 9, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(48, 5, 48, 10, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(49, 5, 49, 10, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(50, 5, 50, 10, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(51, 5, 51, 10, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(52, 5, 52, 10, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(53, 6, 53, 11, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(54, 6, 54, 11, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(55, 6, 55, 11, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(56, 6, 56, 11, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(57, 6, 57, 11, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(58, 6, 58, 12, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(59, 6, 59, 12, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(60, 6, 60, 12, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(61, 6, 61, 12, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(62, 6, 62, 12, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(63, 7, 63, 13, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(64, 7, 64, 13, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(65, 7, 65, 13, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(66, 7, 66, 13, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(67, 7, 67, 13, 1, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(68, 7, 68, 14, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(69, 7, 69, 14, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(70, 7, 70, 14, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(71, 7, 71, 14, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48'),
(72, 7, 72, 14, 2, 1.00, 3, 24, 'Obligatoire', '2026-05-10 14:55:48');

-- --------------------------------------------------------

--
-- Structure de la table `classe_semestres`
--

CREATE TABLE `classe_semestres` (
  `id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `nom` varchar(80) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 1,
  `annee_scolaire` varchar(20) DEFAULT '2024-2025',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `classe_semestres`
--

INSERT INTO `classe_semestres` (`id`, `classe_id`, `nom`, `ordre`, `annee_scolaire`, `created_at`) VALUES
(1, 1, 'Semestre 1', 1, '2025-2026', '2026-05-10 14:36:58'),
(2, 1, 'Semestre 2', 2, '2026-2027', '2026-05-10 14:55:48'),
(3, 2, 'Semestre 1', 1, '2026-2027', '2026-05-10 14:55:48'),
(4, 2, 'Semestre 2', 2, '2026-2027', '2026-05-10 14:55:48'),
(5, 3, 'Semestre 1', 1, '2026-2027', '2026-05-10 14:55:48'),
(6, 3, 'Semestre 2', 2, '2026-2027', '2026-05-10 14:55:48'),
(7, 4, 'Semestre 1', 1, '2026-2027', '2026-05-10 14:55:48'),
(8, 4, 'Semestre 2', 2, '2026-2027', '2026-05-10 14:55:48'),
(9, 5, 'Semestre 1', 1, '2026-2027', '2026-05-10 14:55:48'),
(10, 5, 'Semestre 2', 2, '2026-2027', '2026-05-10 14:55:48'),
(11, 6, 'Semestre 1', 1, '2026-2027', '2026-05-10 14:55:48'),
(12, 6, 'Semestre 2', 2, '2026-2027', '2026-05-10 14:55:48'),
(13, 7, 'Semestre 1', 1, '2026-2027', '2026-05-10 14:55:48'),
(14, 7, 'Semestre 2', 2, '2026-2027', '2026-05-10 14:55:48');

-- --------------------------------------------------------

--
-- Structure de la table `emploi_temps`
--

CREATE TABLE `emploi_temps` (
  `id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `MAT_enseignant` varchar(10) DEFAULT NULL,
  `jour_semaine` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `salle` varchar(80) DEFAULT NULL,
  `annee_scolaire` varchar(20) NOT NULL,
  `created_by` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `emploi_temps`
--

INSERT INTO `emploi_temps` (`id`, `classe_id`, `module_id`, `MAT_enseignant`, `jour_semaine`, `heure_debut`, `heure_fin`, `salle`, `annee_scolaire`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'ES-0001', 'Lundi', '09:00:00', '12:00:00', 'Salle 12', '2026-2027', 'GE-0001', '2026-05-10 14:38:08', '2026-05-10 14:38:08');

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--

CREATE TABLE `enseignant` (
  `MAT` varchar(10) NOT NULL,
  `specialisation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `enseignant`
--

INSERT INTO `enseignant` (`MAT`, `specialisation`) VALUES
('ES-0001', 'Non renseignee'),
('ES-0002', 'Reseaux'),
('ES-0003', 'Intelligence Artificielle'),
('ES-0004', 'Big Data'),
('ES-0005', 'Cybersecurite'),
('ES-0006', 'Genie Logiciel'),
('ES-0007', 'Cloud'),
('ES-0008', 'Developpement Web'),
('ES-0009', 'Base de donnees'),
('ES-0010', 'Algorithmique'),
('ES-0011', 'Securite des SI'),
('ES-0012', 'Base de données');

-- --------------------------------------------------------

--
-- Structure de la table `enseignement_affectation`
--

CREATE TABLE `enseignement_affectation` (
  `MAT_enseignant` varchar(10) NOT NULL,
  `module_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `annee_scolaire` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `enseignement_affectation`
--

INSERT INTO `enseignement_affectation` (`MAT_enseignant`, `module_id`, `classe_id`, `annee_scolaire`) VALUES
('ES-0002', 3, 1, '2026-2027'),
('ES-0002', 32, 3, '2026-2027'),
('ES-0002', 36, 4, '2026-2027'),
('ES-0002', 45, 5, '2026-2027'),
('ES-0002', 52, 5, '2026-2027'),
('ES-0002', 55, 6, '2026-2027'),
('ES-0002', 57, 6, '2026-2027'),
('ES-0003', 10, 1, '2026-2027'),
('ES-0003', 21, 2, '2026-2027'),
('ES-0003', 50, 5, '2026-2027'),
('ES-0003', 58, 6, '2026-2027'),
('ES-0003', 70, 7, '2026-2027'),
('ES-0004', 9, 1, '2026-2027'),
('ES-0004', 15, 2, '2026-2027'),
('ES-0004', 26, 3, '2026-2027'),
('ES-0004', 35, 4, '2026-2027'),
('ES-0004', 40, 4, '2026-2027'),
('ES-0004', 68, 7, '2026-2027'),
('ES-0005', 5, 1, '2026-2027'),
('ES-0005', 8, 1, '2026-2027'),
('ES-0005', 14, 2, '2026-2027'),
('ES-0005', 16, 2, '2026-2027'),
('ES-0005', 23, 3, '2026-2027'),
('ES-0005', 25, 3, '2026-2027'),
('ES-0005', 30, 3, '2026-2027'),
('ES-0005', 31, 3, '2026-2027'),
('ES-0005', 34, 4, '2026-2027'),
('ES-0005', 54, 6, '2026-2027'),
('ES-0005', 72, 7, '2026-2027'),
('ES-0006', 11, 1, '2026-2027'),
('ES-0006', 24, 3, '2026-2027'),
('ES-0006', 62, 6, '2026-2027'),
('ES-0007', 4, 1, '2026-2027'),
('ES-0007', 19, 2, '2026-2027'),
('ES-0007', 28, 3, '2026-2027'),
('ES-0007', 37, 4, '2026-2027'),
('ES-0007', 38, 4, '2026-2027'),
('ES-0007', 43, 5, '2026-2027'),
('ES-0007', 61, 6, '2026-2027'),
('ES-0007', 65, 7, '2026-2027'),
('ES-0008', 17, 2, '2026-2027'),
('ES-0008', 20, 2, '2026-2027'),
('ES-0008', 27, 3, '2026-2027'),
('ES-0008', 42, 4, '2026-2027'),
('ES-0008', 44, 5, '2026-2027'),
('ES-0008', 59, 6, '2026-2027'),
('ES-0008', 60, 6, '2026-2027'),
('ES-0008', 66, 7, '2026-2027'),
('ES-0008', 67, 7, '2026-2027'),
('ES-0009', 6, 1, '2026-2027'),
('ES-0009', 29, 3, '2026-2027'),
('ES-0009', 46, 5, '2026-2027'),
('ES-0009', 47, 5, '2026-2027'),
('ES-0009', 53, 6, '2026-2027'),
('ES-0009', 56, 6, '2026-2027'),
('ES-0009', 63, 7, '2026-2027'),
('ES-0010', 7, 1, '2026-2027'),
('ES-0010', 12, 1, '2026-2027'),
('ES-0010', 18, 2, '2026-2027'),
('ES-0010', 33, 4, '2026-2027'),
('ES-0010', 41, 4, '2026-2027'),
('ES-0010', 48, 5, '2026-2027'),
('ES-0010', 49, 5, '2026-2027'),
('ES-0010', 51, 5, '2026-2027'),
('ES-0010', 64, 7, '2026-2027'),
('ES-0010', 69, 7, '2026-2027'),
('ES-0010', 71, 7, '2026-2027'),
('ES-0011', 13, 2, '2026-2027'),
('ES-0011', 22, 2, '2026-2027'),
('ES-0011', 39, 4, '2026-2027'),
('ES-0012', 1, 1, '2026-2027'),
('ES-0012', 33, 1, '2026-2027'),
('ES-0012', 51, 1, '2026-2027');

-- --------------------------------------------------------

--
-- Structure de la table `enseigner`
--

CREATE TABLE `enseigner` (
  `MAT_PA` varchar(10) NOT NULL,
  `module_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE `etudiant` (
  `MAT` varchar(10) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `annee_etude` year(4) NOT NULL,
  `sexe` enum('M','F') DEFAULT NULL,
  `tuteur_nom` varchar(50) DEFAULT NULL,
  `tuteur_prenom` varchar(50) DEFAULT NULL,
  `tuteur_contact` varchar(30) DEFAULT NULL,
  `adresse_domicile` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`MAT`, `classe_id`, `annee_etude`, `sexe`, `tuteur_nom`, `tuteur_prenom`, `tuteur_contact`, `adresse_domicile`) VALUES
('ET-0001', 1, '2026', NULL, NULL, NULL, NULL, NULL),
('ET-0002', 1, '2026', 'M', 'Tuteur Traore', 'Parent Moussa', '+223 76 87 15 46', 'Bamako - Quartier 1'),
('ET-0003', 1, '2026', 'F', 'Tuteur Dembele', 'Parent Abdoulaye', '+223 76 71 55 33', 'Bamako - Quartier 10'),
('ET-0004', 1, '2026', 'F', 'Tuteur Coulibaly', 'Parent Seydou', '+223 76 44 70 25', 'Bamako - Quartier 15'),
('ET-0005', 1, '2026', 'M', 'Tuteur Sissoko', 'Parent Rokia', '+223 76 25 39 39', 'Bamako - Quartier 5'),
('ET-0006', 1, '2026', 'M', 'Tuteur Sissoko', 'Parent Mamadou', '+223 76 22 53 46', 'Bamako - Quartier 18'),
('ET-0007', 1, '2026', 'M', 'Tuteur Dembele', 'Parent Nafissatou', '+223 76 27 43 13', 'Bamako - Quartier 18'),
('ET-0008', 1, '2026', 'M', 'Tuteur Barry', 'Parent Salif', '+223 76 54 99 61', 'Bamako - Quartier 10'),
('ET-0009', 1, '2026', 'F', 'Tuteur Fofana', 'Parent Samba', '+223 76 57 29 32', 'Bamako - Quartier 9'),
('ET-0010', 1, '2026', 'F', 'Tuteur Barry', 'Parent Nafissatou', '+223 76 84 66 12', 'Bamako - Quartier 5'),
('ET-0011', 1, '2026', 'M', 'Tuteur Sissoko', 'Parent Awa', '+223 76 14 21 72', 'Bamako - Quartier 18'),
('ET-0012', 2, '2026', 'F', 'Tuteur Keita', 'Parent Abdoulaye', '+223 76 42 79 81', 'Bamako - Quartier 2'),
('ET-0013', 2, '2026', 'F', 'Tuteur Diallo', 'Parent Awa', '+223 76 69 11 98', 'Bamako - Quartier 3'),
('ET-0014', 2, '2026', 'M', 'Tuteur Sow', 'Parent Samba', '+223 76 70 95 97', 'Bamako - Quartier 8'),
('ET-0015', 2, '2026', 'F', 'Tuteur Camara', 'Parent Samba', '+223 76 66 28 86', 'Bamako - Quartier 10'),
('ET-0016', 2, '2026', 'M', 'Tuteur Cisse', 'Parent Fatou', '+223 76 39 10 93', 'Bamako - Quartier 11'),
('ET-0017', 2, '2026', 'M', 'Tuteur Camara', 'Parent Rokia', '+223 76 56 11 84', 'Bamako - Quartier 20'),
('ET-0018', 2, '2026', 'F', 'Tuteur Fofana', 'Parent Modibo', '+223 76 35 36 87', 'Bamako - Quartier 14'),
('ET-0019', 2, '2026', 'F', 'Tuteur Sissoko', 'Parent Rokia', '+223 76 12 66 48', 'Bamako - Quartier 10'),
('ET-0020', 2, '2026', 'F', 'Tuteur Camara', 'Parent Seydou', '+223 76 87 52 56', 'Bamako - Quartier 17'),
('ET-0021', 2, '2026', 'F', 'Tuteur Diarra', 'Parent Samba', '+223 76 65 56 20', 'Bamako - Quartier 2'),
('ET-0022', 3, '2026', 'F', 'Tuteur Konate', 'Parent Mamadou', '+223 76 23 33 91', 'Bamako - Quartier 18'),
('ET-0023', 3, '2026', 'F', 'Tuteur Sangare', 'Parent Ibrahim', '+223 76 15 62 28', 'Bamako - Quartier 16'),
('ET-0024', 3, '2026', 'M', 'Tuteur Sidibe', 'Parent Mamadou', '+223 76 59 64 17', 'Bamako - Quartier 11'),
('ET-0025', 3, '2026', 'M', 'Tuteur Diallo', 'Parent Aminata', '+223 76 58 24 81', 'Bamako - Quartier 8'),
('ET-0026', 3, '2026', 'M', 'Tuteur Dembele', 'Parent Samba', '+223 76 79 34 92', 'Bamako - Quartier 2'),
('ET-0027', 3, '2026', 'F', 'Tuteur Cisse', 'Parent Aicha', '+223 76 79 75 19', 'Bamako - Quartier 7'),
('ET-0028', 3, '2026', 'F', 'Tuteur Keita', 'Parent Aicha', '+223 76 49 92 31', 'Bamako - Quartier 20'),
('ET-0029', 3, '2026', 'M', 'Tuteur Fofana', 'Parent Fatou', '+223 76 65 22 57', 'Bamako - Quartier 1'),
('ET-0030', 3, '2026', 'F', 'Tuteur Sangare', 'Parent Seydou', '+223 76 88 15 70', 'Bamako - Quartier 16'),
('ET-0031', 3, '2026', 'F', 'Tuteur Cisse', 'Parent Kadidia', '+223 76 65 81 54', 'Bamako - Quartier 12'),
('ET-0032', 4, '2026', 'F', 'Tuteur Coulibaly', 'Parent Aicha', '+223 76 84 54 90', 'Bamako - Quartier 13'),
('ET-0033', 4, '2026', 'F', 'Tuteur Toure', 'Parent Modibo', '+223 76 23 88 72', 'Bamako - Quartier 4'),
('ET-0034', 4, '2026', 'M', 'Tuteur Dembele', 'Parent Kadidia', '+223 76 55 65 36', 'Bamako - Quartier 8'),
('ET-0035', 4, '2026', 'F', 'Tuteur Toure', 'Parent Aminata', '+223 76 62 61 29', 'Bamako - Quartier 3'),
('ET-0036', 4, '2026', 'F', 'Tuteur Sidibe', 'Parent Mariam', '+223 76 50 93 67', 'Bamako - Quartier 16'),
('ET-0037', 4, '2026', 'F', 'Tuteur Toure', 'Parent Salif', '+223 76 16 80 76', 'Bamako - Quartier 16'),
('ET-0038', 4, '2026', 'F', 'Tuteur Maiga', 'Parent Samba', '+223 76 91 39 58', 'Bamako - Quartier 20'),
('ET-0039', 4, '2026', 'F', 'Tuteur Barry', 'Parent Awa', '+223 76 89 18 99', 'Bamako - Quartier 17'),
('ET-0040', 4, '2026', 'M', 'Tuteur Barry', 'Parent Salif', '+223 76 68 72 76', 'Bamako - Quartier 6'),
('ET-0041', 4, '2026', 'F', 'Tuteur Diallo', 'Parent Mamadou', '+223 76 37 83 37', 'Bamako - Quartier 13'),
('ET-0042', 5, '2026', 'F', 'Tuteur Keita', 'Parent Mariam', '+223 76 86 92 87', 'Bamako - Quartier 16'),
('ET-0043', 5, '2026', 'M', 'Tuteur Fofana', 'Parent Modibo', '+223 76 50 95 77', 'Bamako - Quartier 20'),
('ET-0044', 5, '2026', 'F', 'Tuteur Cisse', 'Parent Fatou', '+223 76 42 13 58', 'Bamako - Quartier 2'),
('ET-0045', 5, '2026', 'M', 'Tuteur Cisse', 'Parent Aminata', '+223 76 26 92 66', 'Bamako - Quartier 14'),
('ET-0046', 5, '2026', 'F', 'Tuteur Konate', 'Parent Mariam', '+223 76 14 55 62', 'Bamako - Quartier 9'),
('ET-0047', 5, '2026', 'M', 'Tuteur Coulibaly', 'Parent Aicha', '+223 76 58 12 24', 'Bamako - Quartier 15'),
('ET-0048', 5, '2026', 'M', 'Tuteur Dembele', 'Parent Nafissatou', '+223 76 35 44 47', 'Bamako - Quartier 9'),
('ET-0049', 5, '2026', 'M', 'Tuteur Dembele', 'Parent Samba', '+223 76 81 71 76', 'Bamako - Quartier 4'),
('ET-0050', 5, '2026', 'M', 'Tuteur Toure', 'Parent Fatou', '+223 76 58 35 41', 'Bamako - Quartier 7'),
('ET-0051', 5, '2026', 'F', 'Tuteur Barry', 'Parent Modibo', '+223 76 33 83 81', 'Bamako - Quartier 18'),
('ET-0052', 6, '2026', 'F', 'Tuteur Sissoko', 'Parent Ousmane', '+223 76 17 41 85', 'Bamako - Quartier 10'),
('ET-0053', 6, '2026', 'M', 'Tuteur Fofana', 'Parent Fatou', '+223 76 32 90 20', 'Bamako - Quartier 15'),
('ET-0054', 6, '2026', 'M', 'Tuteur Kone', 'Parent Aminata', '+223 76 92 20 43', 'Bamako - Quartier 2'),
('ET-0055', 6, '2026', 'M', 'Tuteur Maiga', 'Parent Kadiatou', '+223 76 18 16 45', 'Bamako - Quartier 17'),
('ET-0056', 6, '2026', 'M', 'Tuteur Maiga', 'Parent Boubacar', '+223 76 17 81 90', 'Bamako - Quartier 13'),
('ET-0057', 6, '2026', 'M', 'Tuteur Sissoko', 'Parent Mariam', '+223 76 37 39 54', 'Bamako - Quartier 16'),
('ET-0058', 6, '2026', 'F', 'Tuteur Fofana', 'Parent Aminata', '+223 76 98 69 54', 'Bamako - Quartier 9'),
('ET-0059', 6, '2026', 'M', 'Tuteur Diarra', 'Parent Seydou', '+223 76 99 60 46', 'Bamako - Quartier 19'),
('ET-0060', 6, '2026', 'M', 'Tuteur Dembele', 'Parent Ibrahim', '+223 76 90 98 24', 'Bamako - Quartier 9'),
('ET-0061', 6, '2026', 'F', 'Tuteur Sidibe', 'Parent Rokia', '+223 76 69 39 66', 'Bamako - Quartier 5'),
('ET-0062', 7, '2026', 'M', 'Tuteur Traore', 'Parent Kadidia', '+223 76 89 23 42', 'Bamako - Quartier 19'),
('ET-0063', 7, '2026', 'M', 'Tuteur Fofana', 'Parent Awa', '+223 76 70 32 80', 'Bamako - Quartier 15'),
('ET-0064', 7, '2026', 'M', 'Tuteur Cisse', 'Parent Awa', '+223 76 15 28 92', 'Bamako - Quartier 15'),
('ET-0065', 7, '2026', 'F', 'Tuteur Sissoko', 'Parent Kadidia', '+223 76 75 11 51', 'Bamako - Quartier 10'),
('ET-0066', 7, '2026', 'F', 'Tuteur Sanogo', 'Parent Samba', '+223 76 80 20 82', 'Bamako - Quartier 14'),
('ET-0067', 7, '2026', 'M', 'Tuteur Coulibaly', 'Parent Ibrahim', '+223 76 37 49 38', 'Bamako - Quartier 16'),
('ET-0068', 7, '2026', 'M', 'Tuteur Dembele', 'Parent Moussa', '+223 76 59 38 30', 'Bamako - Quartier 9'),
('ET-0069', 7, '2026', 'F', 'Tuteur Sanogo', 'Parent Kadiatou', '+223 76 49 92 64', 'Bamako - Quartier 2'),
('ET-0070', 7, '2026', 'F', 'Tuteur Dembele', 'Parent Moussa', '+223 76 31 33 75', 'Bamako - Quartier 15'),
('ET-0071', 7, '2026', 'M', 'Tuteur Cisse', 'Parent Ibrahim', '+223 76 86 68 95', 'Bamako - Quartier 11'),
('ET-0072', 1, '2026', 'M', 'Toure', 'Moussa', '+223 89 99 00 00', 'Bamako');

-- --------------------------------------------------------

--
-- Structure de la table `gerer`
--

CREATE TABLE `gerer` (
  `ID_note` int(11) NOT NULL,
  `MAT_PA` varchar(10) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `MAT` varchar(10) NOT NULL,
  `annee` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `module`
--

CREATE TABLE `module` (
  `ID` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `module`
--

INSERT INTO `module` (`ID`, `nom`) VALUES
(33, 'Big Data M2 - M01'),
(34, 'Big Data M2 - M02'),
(35, 'Big Data M2 - M03'),
(36, 'Big Data M2 - M04'),
(37, 'Big Data M2 - M05'),
(38, 'Big Data M2 - M06'),
(39, 'Big Data M2 - M07'),
(40, 'Big Data M2 - M08'),
(41, 'Big Data M2 - M09'),
(42, 'Big Data M2 - M10'),
(63, 'Cloud Computing M2 - M01'),
(64, 'Cloud Computing M2 - M02'),
(65, 'Cloud Computing M2 - M03'),
(66, 'Cloud Computing M2 - M04'),
(67, 'Cloud Computing M2 - M05'),
(68, 'Cloud Computing M2 - M06'),
(69, 'Cloud Computing M2 - M07'),
(70, 'Cloud Computing M2 - M08'),
(71, 'Cloud Computing M2 - M09'),
(72, 'Cloud Computing M2 - M10'),
(43, 'Cybersecurite M1 - M01'),
(44, 'Cybersecurite M1 - M02'),
(45, 'Cybersecurite M1 - M03'),
(46, 'Cybersecurite M1 - M04'),
(47, 'Cybersecurite M1 - M05'),
(48, 'Cybersecurite M1 - M06'),
(49, 'Cybersecurite M1 - M07'),
(50, 'Cybersecurite M1 - M08'),
(51, 'Cybersecurite M1 - M09'),
(52, 'Cybersecurite M1 - M10'),
(1, 'Génie logiciel'),
(53, 'Genie Logiciel L3 - M01'),
(54, 'Genie Logiciel L3 - M02'),
(55, 'Genie Logiciel L3 - M03'),
(56, 'Genie Logiciel L3 - M04'),
(57, 'Genie Logiciel L3 - M05'),
(58, 'Genie Logiciel L3 - M06'),
(59, 'Genie Logiciel L3 - M07'),
(60, 'Genie Logiciel L3 - M08'),
(61, 'Genie Logiciel L3 - M09'),
(62, 'Genie Logiciel L3 - M10'),
(3, 'ILD L1 - M01'),
(4, 'ILD L1 - M02'),
(5, 'ILD L1 - M03'),
(6, 'ILD L1 - M04'),
(7, 'ILD L1 - M05'),
(8, 'ILD L1 - M06'),
(9, 'ILD L1 - M07'),
(10, 'ILD L1 - M08'),
(11, 'ILD L1 - M09'),
(12, 'ILD L1 - M10'),
(23, 'Intelligence Artificielle M1 - M01'),
(24, 'Intelligence Artificielle M1 - M02'),
(25, 'Intelligence Artificielle M1 - M03'),
(26, 'Intelligence Artificielle M1 - M04'),
(27, 'Intelligence Artificielle M1 - M05'),
(28, 'Intelligence Artificielle M1 - M06'),
(29, 'Intelligence Artificielle M1 - M07'),
(30, 'Intelligence Artificielle M1 - M08'),
(31, 'Intelligence Artificielle M1 - M09'),
(32, 'Intelligence Artificielle M1 - M10'),
(2, 'Programmation logique'),
(13, 'Reseaux L3 - M01'),
(14, 'Reseaux L3 - M02'),
(15, 'Reseaux L3 - M03'),
(16, 'Reseaux L3 - M04'),
(17, 'Reseaux L3 - M05'),
(18, 'Reseaux L3 - M06'),
(19, 'Reseaux L3 - M07'),
(20, 'Reseaux L3 - M08'),
(21, 'Reseaux L3 - M09'),
(22, 'Reseaux L3 - M10');

-- --------------------------------------------------------

--
-- Structure de la table `note`
--

CREATE TABLE `note` (
  `ID` int(11) NOT NULL,
  `MAT_ET` varchar(10) NOT NULL,
  `module_id` int(11) NOT NULL,
  `semestre_id` int(11) DEFAULT NULL,
  `valeur` decimal(4,2) NOT NULL,
  `poids` int(11) DEFAULT NULL CHECK (`poids` between 0 and 100),
  `penalite` tinyint(4) DEFAULT NULL,
  `devoir_1` decimal(4,2) DEFAULT NULL,
  `devoir_2` decimal(4,2) DEFAULT NULL,
  `devoir_3` decimal(4,2) DEFAULT NULL,
  `note_classe` decimal(4,2) DEFAULT NULL,
  `note_examen` decimal(4,2) DEFAULT NULL,
  `note_finale` decimal(4,2) DEFAULT NULL,
  `created_by` varchar(10) DEFAULT NULL,
  `updated_by` varchar(10) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Déchargement des données de la table `note`
--

INSERT INTO `note` (`ID`, `MAT_ET`, `module_id`, `semestre_id`, `valeur`, `poids`, `penalite`, `devoir_1`, `devoir_2`, `devoir_3`, `note_classe`, `note_examen`, `note_finale`, `created_by`, `updated_by`, `updated_at`) VALUES
(1, 'ET-0001', 33, NULL, 0.00, 100, 0, 12.00, 11.00, 11.00, 11.33, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(2, 'ET-0002', 33, NULL, 0.00, 100, 0, 10.00, 19.00, 10.00, 13.00, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(3, 'ET-0003', 33, NULL, 0.00, 100, 0, 10.00, 10.00, 10.00, 10.00, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(4, 'ET-0004', 33, NULL, 0.00, 100, 0, 11.00, 10.00, 15.00, 12.00, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(5, 'ET-0005', 33, NULL, 0.00, 100, 0, 11.00, 11.00, 10.00, 10.67, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(6, 'ET-0006', 33, NULL, 0.00, 100, 0, 12.00, 10.00, 11.00, 11.00, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(7, 'ET-0007', 33, NULL, 0.00, 100, 0, 13.00, 14.00, 12.00, 13.00, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(8, 'ET-0008', 33, NULL, 0.00, 100, 0, 11.00, 10.00, 11.00, 10.67, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(9, 'ET-0009', 33, NULL, 0.00, 100, 0, 12.00, 10.00, 13.00, 11.67, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(10, 'ET-0010', 33, NULL, 0.00, 100, 0, 12.00, 12.00, 12.50, 12.17, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(11, 'ET-0011', 33, NULL, 0.00, 100, 0, 10.00, 13.00, 14.00, 12.33, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(12, 'ET-0072', 33, NULL, 0.00, 100, 0, 11.00, 12.00, 12.00, 11.67, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:40:12'),
(13, 'ET-0003', 1, 1, 0.00, 100, 0, 10.00, 11.00, 12.00, 11.00, NULL, NULL, 'ES-0012', 'ES-0012', '2026-05-10 16:55:58'),
(14, 'ET-0072', 1, 1, 15.67, 100, 0, 12.00, 13.00, 14.00, 13.00, 17.00, 15.67, 'ES-0012', 'ES-0012', '2026-05-10 16:55:58');

-- --------------------------------------------------------

--
-- Structure de la table `pa`
--

CREATE TABLE `pa` (
  `MAT` varchar(10) NOT NULL,
  `post` varchar(60) NOT NULL
) ;

--
-- Déchargement des données de la table `pa`
--

INSERT INTO `pa` (`MAT`, `post`) VALUES
('AD-0001', 'ADMIN'),
('GE-0001', 'GESTIONNAIRE'),
('GE-0006', 'GESTIONNAIRE'),
('GE-0007', 'GESTIONNAIRE');

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `code`, `description`, `created_at`) VALUES
(1, 'dashboard.view', 'Acceder au tableau de bord', '2026-04-02 17:29:55'),
(2, 'users.create', 'Creer un utilisateur', '2026-04-02 17:29:55'),
(3, 'users.read', 'Consulter les utilisateurs', '2026-04-02 17:29:55'),
(4, 'users.update', 'Modifier un utilisateur', '2026-04-02 17:29:55'),
(5, 'users.delete', 'Supprimer logiquement un utilisateur', '2026-04-02 17:29:55'),
(6, 'roles.manage', 'Gerer les roles et permissions', '2026-04-02 17:29:55'),
(7, 'security.logs.read', 'Consulter les journaux de securite', '2026-04-02 17:29:55');

-- --------------------------------------------------------

--
-- Structure de la table `reclamations`
--

CREATE TABLE `reclamations` (
  `id` int(11) NOT NULL,
  `MAT_etudiant` varchar(10) NOT NULL,
  `sujet` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `statut` enum('EN_ATTENTE','APPROUVEE','REJETEE') NOT NULL DEFAULT 'EN_ATTENTE',
  `reponse` text DEFAULT NULL,
  `traite_par` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(2, 'ADMIN', 'Administrateur fonctionnel', '2026-04-02 17:29:55'),
(3, 'ENSEIGNANT', 'Personnel enseignant', '2026-04-02 17:29:55'),
(4, 'GESTIONNAIRE', 'Gestionnaire scolaire', '2026-04-02 17:29:55'),
(5, 'ETUDIANT', 'Compte etudiant', '2026-04-02 17:29:55');

-- --------------------------------------------------------

--
-- Structure de la table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5);

-- --------------------------------------------------------

--
-- Structure de la table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` bigint(20) NOT NULL,
  `mat_user` varchar(10) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `security_logs`
--

INSERT INTO `security_logs` (`id`, `mat_user`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 17:30:11'),
(2, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 17:33:38'),
(3, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 17:39:30'),
(4, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 17:39:33'),
(5, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 17:48:43'),
(6, 'AD-0001', 'users.create', 'Creation de l\'utilisateur mariamdoum02@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 17:50:46'),
(7, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 17:58:10'),
(8, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 17:59:55'),
(9, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 18:00:23'),
(10, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 18:01:01'),
(11, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 20:15:52'),
(12, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 20:17:36'),
(13, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 20:24:01'),
(14, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 20:31:00'),
(15, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 20:32:35'),
(16, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 20:59:29'),
(17, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 21:03:02'),
(18, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 21:03:03'),
(19, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 21:03:15'),
(20, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 21:14:04'),
(21, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-02 21:25:21'),
(22, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-06 21:10:28'),
(23, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:32:15'),
(24, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:32:16'),
(25, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:32:29'),
(26, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:33:47'),
(27, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:33:49'),
(28, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:34:10'),
(29, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:34:12'),
(30, 'AD-0001', 'users.create', 'Creation de l\'utilisateur issaka02@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:35:36'),
(31, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:35:44'),
(32, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:37:32'),
(33, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 04:37:34'),
(34, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:48:06'),
(35, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:48:07'),
(36, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:48:26'),
(37, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:48:39'),
(38, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:48:44'),
(39, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:48:45'),
(40, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:53:00'),
(41, 'AD-0001', 'users.create', 'Creation de l\'utilisateur maimouna02@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:54:10'),
(42, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:54:37'),
(43, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:55:10'),
(44, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 06:55:54'),
(45, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 09:04:31'),
(46, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 09:04:41'),
(47, 'AD-0001', 'login.success', 'Connexion reussie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 CCleaner/145.0.34271.162', '2026-04-07 10:37:47'),
(48, 'AD-0001', 'CREATE_USER', 'Création de l\'utilisateur GE-0001', NULL, NULL, '2026-05-10 14:25:56'),
(49, 'GE-0001', 'CHANGE_PASSWORD', 'Changement obligatoire du mot de passe', NULL, NULL, '2026-05-10 14:26:30'),
(50, 'AD-0001', 'CREATE_USER', 'Création de l\'utilisateur GE-0006', NULL, NULL, '2026-05-10 14:46:24'),
(51, 'AD-0001', 'UPDATE_USER', 'Modification de l\'utilisateur GE-0006', NULL, NULL, '2026-05-10 14:49:10'),
(52, 'AD-0001', 'CREATE_USER', 'Création de l\'utilisateur GE-0007', NULL, NULL, '2026-05-10 16:14:35'),
(53, 'GE-0007', 'CHANGE_PASSWORD', 'Changement obligatoire du mot de passe', NULL, NULL, '2026-05-10 16:15:01'),
(54, 'ET-0072', 'CHANGE_PASSWORD', 'Changement obligatoire du mot de passe', NULL, NULL, '2026-05-10 16:18:53'),
(55, 'AD-0001', 'CREATE_USER', 'Création de l\'utilisateur ES-0012', NULL, NULL, '2026-05-10 16:31:44'),
(56, 'ES-0012', 'CHANGE_PASSWORD', 'Changement obligatoire du mot de passe', NULL, NULL, '2026-05-10 16:32:56');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `MAT` varchar(10) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `date_de_naissance` date DEFAULT NULL,
  `email` varchar(80) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `statut` tinyint(1) DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `password_changed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` varchar(10) DEFAULT NULL,
  `updated_by` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deactivation_reason` text DEFAULT NULL,
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `deactivated_by` varchar(10) DEFAULT NULL,
  `deletion_requested` tinyint(1) NOT NULL DEFAULT 0,
  `deletion_requested_at` timestamp NULL DEFAULT NULL,
  `deletion_requested_by` varchar(10) DEFAULT NULL,
  `reactivated_at` timestamp NULL DEFAULT NULL,
  `reactivated_by` varchar(10) DEFAULT NULL
) ;

--
-- Déchargement des données de la table `utilisateur`
--
"
INSERT INTO `utilisateur` (`MAT`, `nom`, `prenom`, `date_de_naissance`, `email`, `telephone`, `motdepasse`, `role_id`, `statut`, `must_change_password`, `password_changed_at`, `deleted_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deactivation_reason`, `deactivated_at`, `deactivated_by`, `deletion_requested`, `deletion_requested_at`, `deletion_requested_by`, `reactivated_at`, `reactivated_by`) VALUES
('AD-0001', 'Admin', 'Principal', '1990-01-01', 'admin@scolarsys.test', NULL, '$2y$12$yECiXDf7VHlA0kRWNVQMoejsdNbdUAriegjpyzAbH7jnZJbGkylWm', 2, 1, 0, NULL, NULL, NULL, NULL, '2026-04-02 17:29:55', '2026-05-10 13:48:21', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0001', 'Diarra', 'Maimouna', '2005-11-20', 'maimouna02@gmail.com', NULL, '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', 'AD-0001', '2026-04-07 06:54:10', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0002', 'Enseignant', '1', '1994-02-20', 'enseignant1@demo.local', '+223 65 70 28 19', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0003', 'Enseignant', '2', '1981-01-17', 'enseignant2@demo.local', '+223 65 23 45 95', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0004', 'Enseignant', '3', '1992-01-21', 'enseignant3@demo.local', '+223 65 28 61 11', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0005', 'Enseignant', '4', '1996-03-06', 'enseignant4@demo.local', '+223 65 75 48 95', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0006', 'Enseignant', '5', '1991-09-01', 'enseignant5@demo.local', '+223 65 33 65 12', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0007', 'Enseignant', '6', '1998-04-24', 'enseignant6@demo.local', '+223 65 59 13 77', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:54', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0008', 'Enseignant', '7', '1980-12-10', 'enseignant7@demo.local', '+223 65 11 79 87', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:54', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0009', 'Enseignant', '8', '1997-12-31', 'enseignant8@demo.local', '+223 65 74 18 72', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:54', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0010', 'Enseignant', '9', '1992-09-09', 'enseignant9@demo.local', '+223 65 36 22 16', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:54', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0011', 'Enseignant', '10', '1982-08-07', 'enseignant10@demo.local', '+223 65 67 43 62', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:54', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ES-0012', 'Ali', 'Ali', NULL, 'alienseigant@gmail.com', '+223 78 99 00 00', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 3, 1, 0, NULL, NULL, 'AD-0001', 'ES-0012', '2026-05-10 16:31:44', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0001', 'TRAORE', 'ISSAKA', '2006-11-10', 'issaka02@gmail.com', NULL, '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', 'AD-0001', '2026-04-07 04:35:36', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0002', 'Traore', 'Moussa', '2008-02-23', 'etudiantild1@demo.local', '+223 70 42 77 70', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:48', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0003', 'Dembele', 'Abdoulaye', '2000-05-03', 'etudiantild2@demo.local', '+223 70 71 22 45', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:48', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0004', 'Coulibaly', 'Seydou', '2005-10-01', 'etudiantild3@demo.local', '+223 70 74 88 85', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:48', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0005', 'Sissoko', 'Rokia', '2007-10-12', 'etudiantild4@demo.local', '+223 70 79 49 34', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:48', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0006', 'Sissoko', 'Mamadou', '2002-12-14', 'etudiantild5@demo.local', '+223 70 99 94 62', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:48', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0007', 'Dembele', 'Nafissatou', '2004-11-13', 'etudiantild6@demo.local', '+223 70 84 82 56', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:48', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0008', 'Barry', 'Salif', '2007-04-13', 'etudiantild7@demo.local', '+223 70 65 72 53', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0009', 'Fofana', 'Samba', '2001-11-09', 'etudiantild8@demo.local', '+223 70 75 91 14', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0010', 'Barry', 'Nafissatou', '2004-10-05', 'etudiantild9@demo.local', '+223 70 30 86 78', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0011', 'Sissoko', 'Awa', '2002-03-14', 'etudiantild10@demo.local', '+223 70 29 86 93', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0012', 'Keita', 'Abdoulaye', '2001-09-27', 'etudiantreseaux1@demo.local', '+223 70 91 29 86', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0013', 'Diallo', 'Awa', '2000-10-09', 'etudiantreseaux2@demo.local', '+223 70 58 99 30', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0014', 'Sow', 'Samba', '2008-01-24', 'etudiantreseaux3@demo.local', '+223 70 70 86 18', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0015', 'Camara', 'Samba', '2007-11-18', 'etudiantreseaux4@demo.local', '+223 70 83 73 56', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0016', 'Cisse', 'Fatou', '2002-10-01', 'etudiantreseaux5@demo.local', '+223 70 67 55 55', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0017', 'Camara', 'Rokia', '2008-05-08', 'etudiantreseaux6@demo.local', '+223 70 77 20 81', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0018', 'Fofana', 'Modibo', '2007-08-06', 'etudiantreseaux7@demo.local', '+223 70 59 34 22', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0019', 'Sissoko', 'Rokia', '1999-08-12', 'etudiantreseaux8@demo.local', '+223 70 19 14 40', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0020', 'Camara', 'Seydou', '2002-11-18', 'etudiantreseaux9@demo.local', '+223 70 51 91 94', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0021', 'Diarra', 'Samba', '2001-07-28', 'etudiantreseaux10@demo.local', '+223 70 39 18 23', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:49', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0022', 'Konate', 'Mamadou', '2000-12-11', 'etudiantintelligenceartificielle1@demo.local', '+223 70 39 41 31', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0023', 'Sangare', 'Ibrahim', '2002-12-02', 'etudiantintelligenceartificielle2@demo.local', '+223 70 40 12 27', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0024', 'Sidibe', 'Mamadou', '2001-08-12', 'etudiantintelligenceartificielle3@demo.local', '+223 70 89 18 35', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0025', 'Diallo', 'Aminata', '2003-12-31', 'etudiantintelligenceartificielle4@demo.local', '+223 70 16 90 31', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0026', 'Dembele', 'Samba', '2004-09-26', 'etudiantintelligenceartificielle5@demo.local', '+223 70 21 81 54', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0027', 'Cisse', 'Aicha', '2008-05-09', 'etudiantintelligenceartificielle6@demo.local', '+223 70 95 63 50', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0028', 'Keita', 'Aicha', '2004-09-04', 'etudiantintelligenceartificielle7@demo.local', '+223 70 34 15 98', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0029', 'Fofana', 'Fatou', '2000-10-18', 'etudiantintelligenceartificielle8@demo.local', '+223 70 62 25 50', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0030', 'Sangare', 'Seydou', '2004-03-17', 'etudiantintelligenceartificielle9@demo.local', '+223 70 43 46 36', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0031', 'Cisse', 'Kadidia', '2003-03-23', 'etudiantintelligenceartificielle10@demo.local', '+223 70 28 70 16', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0032', 'Coulibaly', 'Aicha', '2002-09-16', 'etudiantbigdata1@demo.local', '+223 70 39 21 36', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0033', 'Toure', 'Modibo', '1999-10-12', 'etudiantbigdata2@demo.local', '+223 70 80 16 40', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0034', 'Dembele', 'Kadidia', '2003-12-27', 'etudiantbigdata3@demo.local', '+223 70 34 92 81', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:50', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0035', 'Toure', 'Aminata', '2001-04-06', 'etudiantbigdata4@demo.local', '+223 70 45 52 77', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0036', 'Sidibe', 'Mariam', '2000-11-27', 'etudiantbigdata5@demo.local', '+223 70 50 76 65', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0037', 'Toure', 'Salif', '2000-07-21', 'etudiantbigdata6@demo.local', '+223 70 54 61 39', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0038', 'Maiga', 'Samba', '2001-09-10', 'etudiantbigdata7@demo.local', '+223 70 26 58 46', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0039', 'Barry', 'Awa', '2001-11-15', 'etudiantbigdata8@demo.local', '+223 70 23 79 52', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0040', 'Barry', 'Salif', '2003-10-02', 'etudiantbigdata9@demo.local', '+223 70 72 20 45', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0041', 'Diallo', 'Mamadou', '2000-07-14', 'etudiantbigdata10@demo.local', '+223 70 79 49 25', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0042', 'Keita', 'Mariam', '2001-04-26', 'etudiantcybersecurite1@demo.local', '+223 70 91 23 59', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0043', 'Fofana', 'Modibo', '2000-07-18', 'etudiantcybersecurite2@demo.local', '+223 70 90 32 12', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0044', 'Cisse', 'Fatou', '2006-01-30', 'etudiantcybersecurite3@demo.local', '+223 70 98 95 71', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0045', 'Cisse', 'Aminata', '2003-02-03', 'etudiantcybersecurite4@demo.local', '+223 70 47 12 31', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0046', 'Konate', 'Mariam', '1999-10-04', 'etudiantcybersecurite5@demo.local', '+223 70 71 67 20', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0047', 'Coulibaly', 'Aicha', '1999-07-18', 'etudiantcybersecurite6@demo.local', '+223 70 67 24 53', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0048', 'Dembele', 'Nafissatou', '2008-02-14', 'etudiantcybersecurite7@demo.local', '+223 70 78 99 70', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:51', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0049', 'Dembele', 'Samba', '2005-11-29', 'etudiantcybersecurite8@demo.local', '+223 70 52 18 56', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0050', 'Toure', 'Fatou', '2003-03-24', 'etudiantcybersecurite9@demo.local', '+223 70 96 28 97', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0051', 'Barry', 'Modibo', '2006-09-09', 'etudiantcybersecurite10@demo.local', '+223 70 84 44 88', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0052', 'Sissoko', 'Ousmane', '2003-01-02', 'etudiantgenielogiciel1@demo.local', '+223 70 79 52 96', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0053', 'Fofana', 'Fatou', '2003-08-13', 'etudiantgenielogiciel2@demo.local', '+223 70 72 76 97', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0054', 'Kone', 'Aminata', '2003-11-06', 'etudiantgenielogiciel3@demo.local', '+223 70 53 98 40', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0055', 'Maiga', 'Kadiatou', '2005-08-04', 'etudiantgenielogiciel4@demo.local', '+223 70 96 71 27', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0056', 'Maiga', 'Boubacar', '1999-09-08', 'etudiantgenielogiciel5@demo.local', '+223 70 40 69 68', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0057', 'Sissoko', 'Mariam', '2004-03-05', 'etudiantgenielogiciel6@demo.local', '+223 70 21 24 86', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0058', 'Fofana', 'Aminata', '2001-07-22', 'etudiantgenielogiciel7@demo.local', '+223 70 95 86 91', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0059', 'Diarra', 'Seydou', '2003-12-02', 'etudiantgenielogiciel8@demo.local', '+223 70 35 29 16', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0060', 'Dembele', 'Ibrahim', '2004-03-17', 'etudiantgenielogiciel9@demo.local', '+223 70 29 56 84', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0061', 'Sidibe', 'Rokia', '1999-10-24', 'etudiantgenielogiciel10@demo.local', '+223 70 51 56 26', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0062', 'Traore', 'Kadidia', '2002-09-25', 'etudiantcloudcomputing1@demo.local', '+223 70 93 22 95', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:52', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0063', 'Fofana', 'Awa', '2006-10-25', 'etudiantcloudcomputing2@demo.local', '+223 70 50 38 59', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0064', 'Cisse', 'Awa', '2008-02-25', 'etudiantcloudcomputing3@demo.local', '+223 70 93 35 55', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0065', 'Sissoko', 'Kadidia', '2003-10-05', 'etudiantcloudcomputing4@demo.local', '+223 70 22 49 21', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0066', 'Sanogo', 'Samba', '2000-02-08', 'etudiantcloudcomputing5@demo.local', '+223 70 42 74 81', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0067', 'Coulibaly', 'Ibrahim', '2000-07-15', 'etudiantcloudcomputing6@demo.local', '+223 70 59 30 89', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0068', 'Dembele', 'Moussa', '2001-02-25', 'etudiantcloudcomputing7@demo.local', '+223 70 12 41 61', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0069', 'Sanogo', 'Kadiatou', '2000-01-07', 'etudiantcloudcomputing8@demo.local', '+223 70 40 10 39', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0070', 'Dembele', 'Moussa', '2006-03-09', 'etudiantcloudcomputing9@demo.local', '+223 70 97 30 49', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0071', 'Cisse', 'Ibrahim', '2002-08-04', 'etudiantcloudcomputing10@demo.local', '+223 70 93 11 90', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'AD-0001', NULL, '2026-05-10 14:55:53', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ET-0072', 'Ali', 'Ali', '2007-11-20', 'ali_etudiant02@gmail.com', '+223 78 99 00 00', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 5, 1, 0, NULL, NULL, 'GE-0007', 'ET-0072', '2026-05-10 16:18:22', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GE-0001', 'gestionnaire', '1', '2007-11-10', 'gestionnaire1@gmail.com', '+223 65432099', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 4, 1, 0, NULL, NULL, 'AD-0001', 'GE-0001', '2026-05-10 14:25:56', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GE-0002', 'Gestionnaire', '2', '2000-01-01', 'gestionnaire2@gmail.com', '+223 65432100', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 4, 1, 0, NULL, NULL, NULL, NULL, '2026-05-10 14:40:43', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GE-0003', 'Gestionnaire', '3', '2000-01-01', 'gestionnaire3@gmail.com', '+223 65432101', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 4, 1, 0, NULL, NULL, NULL, NULL, '2026-05-10 14:40:43', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GE-0004', 'Gestionnaire', '4', '2000-01-01', 'gestionnaire4@gmail.com', '+223 65432102', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 4, 1, 0, NULL, NULL, NULL, NULL, '2026-05-10 14:40:43', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GE-0005', 'Gestionnaire', '5', '2000-01-01', 'gestionnaire5@gmail.com', '+223 65432103', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 4, 1, 0, NULL, NULL, NULL, NULL, '2026-05-10 14:40:43', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GE-0006', '6', 'gestionnaire', '2007-11-10', 'gestionnaire6@gmail.com', '+223 73 23 03 45', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 4, 1, 0, NULL, NULL, 'AD-0001', 'AD-0001', '2026-05-10 14:46:24', '2026-05-10 17:31:24', NULL, NULL, NULL, 0, NULL, NULL, '2026-05-10 17:31:24', 'AD-0001'),
('GE-0007', 'Ali', 'Ali', NULL, 'ali02@gmail.com', '+223 78 99 00 00', '$2y$10$TcRIRuHIMT7VfDStjufk4OxmRrGpGP8nnVQWrimoLQiwSTAcJG6hq', 4, 1, 0, NULL, NULL, 'AD-0001', 'GE-0007', '2026-05-10 16:14:35', '2026-05-10 17:10:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `action_archive`
--
ALTER TABLE `action_archive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action_archive_type_target` (`action_type`,`target_type`,`target_id`),
  ADD KEY `fk_action_archive_queue` (`action_queue_id`),
  ADD KEY `fk_action_archive_requested_by` (`requested_by`),
  ADD KEY `fk_action_archive_executed_by` (`executed_by`);

--
-- Index pour la table `action_confirmations`
--
ALTER TABLE `action_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_action_confirmation` (`action_queue_id`,`confirmed_by`),
  ADD KEY `idx_action_confirm_action` (`action_queue_id`),
  ADD KEY `fk_action_confirm_user` (`confirmed_by`);

--
-- Index pour la table `action_queue`
--
ALTER TABLE `action_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action_queue_status` (`status`),
  ADD KEY `idx_action_queue_type_target` (`action_type`,`target_type`,`target_id`),
  ADD KEY `fk_action_queue_user` (`requested_by`);

--
-- Index pour la table `admin_alerts`
--
ALTER TABLE `admin_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_admin_alerts_target` (`target_mat_user`),
  ADD KEY `fk_admin_alerts_creator` (`created_by`);

--
-- Index pour la table `backup_jobs`
--
ALTER TABLE `backup_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `classe`
--
ALTER TABLE `classe`
  ADD PRIMARY KEY (`ID`);

--
-- Index pour la table `classe_modules`
--
ALTER TABLE `classe_modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_classe_module_semestre` (`classe_id`,`module_id`,`semestre`),
  ADD KEY `fk_cm_module` (`module_id`),
  ADD KEY `fk_cm_semestre` (`semestre_id`);

--
-- Index pour la table `classe_semestres`
--
ALTER TABLE `classe_semestres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_classe_semestre_nom` (`classe_id`,`nom`),
  ADD KEY `fk_cs_classe` (`classe_id`);

--
-- Index pour la table `emploi_temps`
--
ALTER TABLE `emploi_temps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_edt_classe` (`classe_id`),
  ADD KEY `fk_edt_module` (`module_id`),
  ADD KEY `fk_edt_enseignant` (`MAT_enseignant`);

--
-- Index pour la table `enseignant`
--
ALTER TABLE `enseignant`
  ADD PRIMARY KEY (`MAT`);

--
-- Index pour la table `enseignement_affectation`
--
ALTER TABLE `enseignement_affectation`
  ADD PRIMARY KEY (`MAT_enseignant`,`module_id`,`classe_id`,`annee_scolaire`),
  ADD UNIQUE KEY `uq_affectation_module_classe_annee` (`module_id`,`classe_id`,`annee_scolaire`),
  ADD KEY `fk_affectation_classe` (`classe_id`);

--
-- Index pour la table `enseigner`
--
ALTER TABLE `enseigner`
  ADD PRIMARY KEY (`MAT_PA`,`module_id`),
  ADD KEY `fk_enseigner_module` (`module_id`);

--
-- Index pour la table `etudiant`
--
ALTER TABLE `etudiant`
  ADD PRIMARY KEY (`MAT`),
  ADD KEY `fk_etudiant_classe` (`classe_id`);

--
-- Index pour la table `gerer`
--
ALTER TABLE `gerer`
  ADD PRIMARY KEY (`ID_note`,`MAT_PA`),
  ADD KEY `fk_gere_PA` (`MAT_PA`);

--
-- Index pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`MAT`,`annee`);

--
-- Index pour la table `module`
--
ALTER TABLE `module`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_note_module` (`module_id`),
  ADD KEY `fk_note_etudiant` (`MAT_ET`);

--
-- Index pour la table `pa`
--
ALTER TABLE `pa`
  ADD PRIMARY KEY (`MAT`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `reclamations`
--
ALTER TABLE `reclamations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reclamations_etudiant` (`MAT_etudiant`),
  ADD KEY `idx_reclamations_statut` (`statut`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Index pour la table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_security_logs_user` (`mat_user`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`MAT`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_utilisateur_role` (`role_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `action_archive`
--
ALTER TABLE `action_archive`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `action_confirmations`
--
ALTER TABLE `action_confirmations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `action_queue`
--
ALTER TABLE `action_queue`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `admin_alerts`
--
ALTER TABLE `admin_alerts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT pour la table `backup_jobs`
--
ALTER TABLE `backup_jobs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `classe`
--
ALTER TABLE `classe`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `classe_modules`
--
ALTER TABLE `classe_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT pour la table `classe_semestres`
--
ALTER TABLE `classe_semestres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `emploi_temps`
--
ALTER TABLE `emploi_temps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `module`
--
ALTER TABLE `module`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT pour la table `note`
--
ALTER TABLE `note`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `reclamations`
--
ALTER TABLE `reclamations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `action_archive`
--
ALTER TABLE `action_archive`
  ADD CONSTRAINT `fk_action_archive_executed_by` FOREIGN KEY (`executed_by`) REFERENCES `utilisateur` (`MAT`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_action_archive_queue` FOREIGN KEY (`action_queue_id`) REFERENCES `action_queue` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_action_archive_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `action_confirmations`
--
ALTER TABLE `action_confirmations`
  ADD CONSTRAINT `fk_action_confirm_queue` FOREIGN KEY (`action_queue_id`) REFERENCES `action_queue` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_action_confirm_user` FOREIGN KEY (`confirmed_by`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `action_queue`
--
ALTER TABLE `action_queue`
  ADD CONSTRAINT `fk_action_queue_user` FOREIGN KEY (`requested_by`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `admin_alerts`
--
ALTER TABLE `admin_alerts`
  ADD CONSTRAINT `fk_admin_alerts_creator` FOREIGN KEY (`created_by`) REFERENCES `utilisateur` (`MAT`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_admin_alerts_target` FOREIGN KEY (`target_mat_user`) REFERENCES `utilisateur` (`MAT`) ON DELETE SET NULL;

--
-- Contraintes pour la table `classe_modules`
--
ALTER TABLE `classe_modules`
  ADD CONSTRAINT `fk_cm_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cm_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cm_semestre` FOREIGN KEY (`semestre_id`) REFERENCES `classe_semestres` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `classe_semestres`
--
ALTER TABLE `classe_semestres`
  ADD CONSTRAINT `fk_cs_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `enseignant`
--
ALTER TABLE `enseignant`
  ADD CONSTRAINT `fk_enseignant_pa` FOREIGN KEY (`MAT`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `enseignement_affectation`
--
ALTER TABLE `enseignement_affectation`
  ADD CONSTRAINT `fk_affectation_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_affectation_enseignant` FOREIGN KEY (`MAT_enseignant`) REFERENCES `enseignant` (`MAT`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_affectation_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `enseigner`
--
ALTER TABLE `enseigner`
  ADD CONSTRAINT `fk_enseigner_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enseigner_pa` FOREIGN KEY (`MAT_PA`) REFERENCES `pa` (`MAT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `etudiant`
--
ALTER TABLE `etudiant`
  ADD CONSTRAINT `fk_etudiant_classe` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`ID`),
  ADD CONSTRAINT `fk_etudiant_utilisateur` FOREIGN KEY (`MAT`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `gerer`
--
ALTER TABLE `gerer`
  ADD CONSTRAINT `fk_gere_PA` FOREIGN KEY (`MAT_PA`) REFERENCES `pa` (`MAT`),
  ADD CONSTRAINT `fk_gerer_notes` FOREIGN KEY (`ID_note`) REFERENCES `note` (`ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `fk_inscription_etudiant` FOREIGN KEY (`MAT`) REFERENCES `etudiant` (`MAT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `fk_note_etudiant` FOREIGN KEY (`MAT_ET`) REFERENCES `etudiant` (`MAT`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_note_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`ID`);

--
-- Contraintes pour la table `pa`
--
ALTER TABLE `pa`
  ADD CONSTRAINT `fk_PA_utilisateur` FOREIGN KEY (`MAT`) REFERENCES `utilisateur` (`MAT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `fk_security_logs_user` FOREIGN KEY (`mat_user`) REFERENCES `utilisateur` (`MAT`) ON DELETE SET NULL;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `fk_utilisateur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
