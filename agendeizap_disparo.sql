-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/12/2025 às 10:40
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `agendeizap_disparo`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `campaigns`
--

CREATE TABLE `campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `whatsapp_instance_id` bigint(20) UNSIGNED NOT NULL,
  `delay_min_ms` int(10) UNSIGNED NOT NULL DEFAULT 1500,
  `delay_max_ms` int(10) UNSIGNED NOT NULL DEFAULT 4000,
  `name` varchar(160) NOT NULL,
  `delay_min_seconds` int(10) UNSIGNED NOT NULL DEFAULT 9,
  `delay_max_seconds` int(10) UNSIGNED NOT NULL DEFAULT 12,
  `burst_max` int(10) UNSIGNED NOT NULL DEFAULT 20,
  `burst_pause_seconds` int(10) UNSIGNED NOT NULL DEFAULT 30,
  `daily_limit_override` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('draft','validated','queued','running','paused','finished','canceled','failed') NOT NULL DEFAULT 'draft',
  `total_recipients` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `valid_recipients` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `invalid_recipients` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `campaigns`
--

INSERT INTO `campaigns` (`id`, `user_id`, `whatsapp_instance_id`, `delay_min_ms`, `delay_max_ms`, `name`, `delay_min_seconds`, `delay_max_seconds`, `burst_max`, `burst_pause_seconds`, `daily_limit_override`, `status`, `total_recipients`, `valid_recipients`, `invalid_recipients`, `started_at`, `finished_at`, `created_at`, `updated_at`) VALUES
(1, 2, 8, 1500, 4000, 'mapos', 9, 12, 20, 30, NULL, 'finished', 3, 3, 0, NULL, NULL, '2025-12-16 01:49:53', '2025-12-16 04:10:45'),
(2, 2, 8, 1500, 4000, 'teste98', 2, 4, 20, 30, NULL, 'draft', 0, 0, 0, NULL, NULL, '2025-12-16 04:18:06', '2025-12-16 04:18:06'),
(6, 3, 10, 1500, 4000, 'teste ritieli', 2, 4, 20, 30, NULL, 'finished', 0, 0, 0, NULL, NULL, '2025-12-17 01:41:40', '2025-12-17 01:53:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `campaign_attachments`
--

CREATE TABLE `campaign_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_message_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('image','video','audio','document','sticker') NOT NULL DEFAULT 'document',
  `original_name` varchar(190) DEFAULT NULL,
  `mime` varchar(120) DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `campaign_messages`
--

CREATE TABLE `campaign_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `text` longtext DEFAULT NULL,
  `primary_type` enum('text','media','document','audio','video','image','location','mixed') NOT NULL DEFAULT 'text',
  `location_lat` decimal(10,7) DEFAULT NULL,
  `location_lng` decimal(10,7) DEFAULT NULL,
  `location_name` varchar(160) DEFAULT NULL,
  `location_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `campaign_messages`
--

INSERT INTO `campaign_messages` (`id`, `campaign_id`, `position`, `text`, `primary_type`, `location_lat`, `location_lng`, `location_name`, `location_address`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'teste de menagens dksjdksd jkdjs sd', 'text', NULL, NULL, NULL, NULL, '2025-12-16 03:41:06', '2025-12-16 03:41:06'),
(8, 6, 1, 'Amor, hoje tem tá!\r\n\r\nse prepara que eu to que to hoje....', 'text', NULL, NULL, NULL, NULL, '2025-12-17 01:42:12', '2025-12-17 01:42:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `campaign_recipients`
--

CREATE TABLE `campaign_recipients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) DEFAULT NULL,
  `phone_raw` varchar(80) DEFAULT NULL,
  `phone_digits` varchar(20) NOT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT 1,
  `validation_error` varchar(200) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `fail_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `campaign_recipients`
--

INSERT INTO `campaign_recipients` (`id`, `campaign_id`, `name`, `phone_raw`, `phone_digits`, `is_valid`, `validation_error`, `status`, `sent_at`, `delivered_at`, `fail_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ritieli', '5554999595193', '5554999595193', 1, NULL, 'failed', NULL, NULL, 'Evolution não configurado (base_url/api_key/instance_name).', '2025-12-16 03:21:48', '2025-12-16 04:10:14'),
(2, 1, 'Rogéio', '5554984359885', '5554984359885', 1, NULL, 'failed', NULL, NULL, 'Evolution não configurado (base_url/api_key/instance_name).', '2025-12-16 03:22:40', '2025-12-16 04:10:24'),
(3, 1, NULL, '55999595193;', '5555999595193', 1, NULL, 'failed', NULL, NULL, 'Evolution não configurado (base_url/api_key/instance_name).', '2025-12-16 03:40:55', '2025-12-16 04:10:36'),
(9, 2, NULL, '55999595193', '5555999595193', 1, NULL, 'pending', NULL, NULL, NULL, '2025-12-16 04:18:15', '2025-12-16 04:18:15'),
(20, 6, NULL, '54984359885', '5554984359885', 1, NULL, 'sent', NULL, NULL, NULL, '2025-12-17 01:41:49', '2025-12-17 01:53:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chats`
--

CREATE TABLE `chats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `whatsapp_instance_id` bigint(20) UNSIGNED NOT NULL,
  `remote_jid` varchar(120) NOT NULL,
  `title` varchar(190) DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `chats`
--

INSERT INTO `chats` (`id`, `user_id`, `whatsapp_instance_id`, `remote_jid`, `title`, `last_message_at`, `created_at`, `updated_at`) VALUES
(1, 2, 8, '5554984359885@s.whatsapp.net', NULL, '2025-12-17 06:32:45', '2025-12-17 03:13:41', '2025-12-17 06:32:45'),
(2, 2, 8, '5555999595193@s.whatsapp.net', 'Teste', '2025-12-17 06:35:57', '2025-12-17 06:06:11', '2025-12-17 06:35:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(160) DEFAULT NULL,
  `pushname` varchar(160) DEFAULT NULL,
  `phone_e164` varchar(20) NOT NULL,
  `phone_raw` varchar(80) DEFAULT NULL,
  `profile_pic_url` varchar(255) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `is_group` tinyint(1) NOT NULL DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `contacts`
--

INSERT INTO `contacts` (`id`, `tenant_id`, `name`, `pushname`, `phone_e164`, `phone_raw`, `profile_pic_url`, `email`, `is_group`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ritieli', NULL, '+5555999595193', '55999595193', NULL, NULL, 0, NULL, '2025-12-16 19:46:10', '2025-12-16 20:23:42'),
(2, 1, NULL, NULL, '+5554999595193', '54999595193', NULL, NULL, 0, NULL, '2025-12-16 19:46:10', '2025-12-16 19:47:05'),
(3, 1, NULL, NULL, '+5554984359885', '54984359885', NULL, NULL, 0, NULL, '2025-12-16 19:46:10', '2025-12-17 01:41:49'),
(7, 1, 'Valdecir', NULL, '+555499333305', '5499333305', NULL, NULL, 0, NULL, '2025-12-16 20:23:42', '2025-12-16 20:23:42'),
(9, 1, 'Teste', 'Teste', '5547999999999', '5547999999999', NULL, NULL, 0, NULL, '2025-12-17 03:13:41', '2025-12-17 06:06:11');

-- --------------------------------------------------------

--
-- Estrutura para tabela `failed_jobs`
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
-- Estrutura para tabela `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chat_id` bigint(20) UNSIGNED NOT NULL,
  `contact_id` bigint(20) UNSIGNED DEFAULT NULL,
  `provider_message_id` varchar(190) DEFAULT NULL,
  `direction` varchar(12) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'text',
  `body` longtext DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'received',
  `message_at` timestamp NULL DEFAULT NULL,
  `raw` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `messages`
--

INSERT INTO `messages` (`id`, `chat_id`, `contact_id`, `provider_message_id`, `direction`, `type`, `body`, `status`, `message_at`, `raw`, `created_at`, `updated_at`) VALUES
(1, 1, 9, 'abc123', 'inbound', 'text', 'Teste webhook', 'received', '2024-10-27 06:33:20', '{\"instance\":\"ritieli\",\"data\":{\"message\":{\"conversation\":\"Teste webhook\"},\"key\":{\"id\":\"abc123\",\"remoteJid\":\"5547999999999@s.whatsapp.net\"},\"messageTimestamp\":1730000000},\"token\":\"cd6fead706d14593b83f24759224a48fda39511bab54426890ad7108b2e32dc2\"}', '2025-12-17 03:13:41', '2025-12-17 03:13:41'),
(2, 1, NULL, NULL, 'outbound', 'text', '222', 'failed', '2025-12-17 03:34:04', '{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\",\"status\":404,\"body\":{\"status\":404,\"error\":\"Not Found\",\"response\":{\"message\":[\"Cannot POST \\/message\\/sendText\"]}},\"raw\":\"{\\\"status\\\":404,\\\"error\\\":\\\"Not Found\\\",\\\"response\\\":{\\\"message\\\":[\\\"Cannot POST \\/message\\/sendText\\\"]}}\",\"error\":\"Falha ao enviar na Evolution: HTTP 404\"}', '2025-12-17 03:34:04', '2025-12-17 03:34:06'),
(3, 1, NULL, '3EB0F2DC47CA7E379CDBE5', 'outbound', 'text', 'ola', 'sent', '2025-12-17 05:47:16', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0F2DC47CA7E379CDBE5\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"ola\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765766838,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765939638,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"ola\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0F2DC47CA7E379CDBE5\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"ola\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765766838,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765939638,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 05:47:16', '2025-12-17 05:47:16'),
(4, 1, NULL, '3EB0A17D849957B44907DB', 'outbound', 'text', 'via crm', 'sent', '2025-12-17 05:47:47', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0A17D849957B44907DB\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"via crm\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765766869,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765939669,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"via crm\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0A17D849957B44907DB\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"via crm\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765766869,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765939669,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 05:47:47', '2025-12-17 05:47:47'),
(5, 1, NULL, '3EB01F63A2B7C63353F5C2', 'outbound', 'text', '32132132113213', 'sent', '2025-12-17 05:49:35', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB01F63A2B7C63353F5C2\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"32132132113213\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765766977,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765939777,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"32132132113213\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB01F63A2B7C63353F5C2\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"32132132113213\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765766977,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765939777,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 05:49:35', '2025-12-17 05:49:35'),
(6, 1, NULL, '3EB004177075D802265862', 'outbound', 'text', '2131321313213213213132poiopiopip', 'sent', '2025-12-17 05:56:36', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB004177075D802265862\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"2131321313213213213132poiopiopip\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765767398,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765940198,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"2131321313213213213132poiopiopip\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB004177075D802265862\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"2131321313213213213132poiopiopip\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765767398,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765940198,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 05:56:36', '2025-12-17 05:56:36'),
(7, 1, NULL, '3EB0A74F208F98B233E5BF', 'outbound', 'text', 'fdfdfdf', 'sent', '2025-12-17 06:00:21', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0A74F208F98B233E5BF\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"fdfdfdf\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765767622,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765940422,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"fdfdfdf\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0A74F208F98B233E5BF\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"fdfdfdf\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765767622,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765940422,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 06:00:21', '2025-12-17 06:00:21'),
(8, 2, 9, 'TESTE123', 'inbound', 'text', 'teste inbound webhook', 'received', '2024-10-27 06:33:20', '{\"event\":\"messages.upsert\",\"instanceName\":\"pipe\",\"data\":{\"key\":{\"remoteJid\":\"5547999999999@s.whatsapp.net\",\"fromMe\":false,\"id\":\"TESTE123\"},\"message\":{\"conversation\":\"teste inbound webhook\"},\"messageTimestamp\":1730000000,\"pushName\":\"Teste\"},\"token\":\"cd6fead706d14593b83f24759224a48fda39511bab54426890ad7108b2e32dc2\"}', '2025-12-17 06:06:11', '2025-12-17 06:06:11'),
(9, 1, NULL, '3EB0C27D82E8BA23CCEB4C', 'outbound', 'text', 'olha só', 'sent', '2025-12-17 06:25:07', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0C27D82E8BA23CCEB4C\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"olha s\\u00f3\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769109,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765941909,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"olha s\\u00f3\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0C27D82E8BA23CCEB4C\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"olha s\\u00f3\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769109,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765941909,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 06:25:07', '2025-12-17 06:25:07'),
(10, 1, NULL, '3EB07EDAF395D97BAEC170', 'outbound', 'text', 'qqqqqqqqqqqqqqqqq', 'sent', '2025-12-17 06:28:26', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB07EDAF395D97BAEC170\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"qqqqqqqqqqqqqqqqq\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769307,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942107,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"qqqqqqqqqqqqqqqqq\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB07EDAF395D97BAEC170\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"qqqqqqqqqqqqqqqqq\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769307,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942107,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 06:28:26', '2025-12-17 06:28:26'),
(11, 1, NULL, '3EB0A046D1743D56ABF163', 'outbound', 'text', 'xccccccccccccccccccccccccccc', 'sent', '2025-12-17 06:31:26', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0A046D1743D56ABF163\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"xccccccccccccccccccccccccccc\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769487,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942287,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"xccccccccccccccccccccccccccc\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0A046D1743D56ABF163\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"xccccccccccccccccccccccccccc\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769487,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942287,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 06:31:26', '2025-12-17 06:31:26'),
(12, 1, NULL, '3EB008E4885E4FBBA22EFB', 'outbound', 'text', '9898989898989', 'sent', '2025-12-17 06:32:45', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB008E4885E4FBBA22EFB\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"9898989898989\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769566,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942366,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"9898989898989\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB008E4885E4FBBA22EFB\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"9898989898989\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769566,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942366,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 06:32:45', '2025-12-17 06:32:45'),
(13, 2, NULL, '3EB0B44802523779AA52C0', 'outbound', 'text', 'kkkkkkkkkkkkk', 'sent', '2025-12-17 06:35:57', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555599595193@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0B44802523779AA52C0\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"kkkkkkkkkkkkk\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769759,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942559,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5555999595193\",\"text\":\"kkkkkkkkkkkkk\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555599595193@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0B44802523779AA52C0\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"kkkkkkkkkkkkk\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769759,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942559,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 06:35:57', '2025-12-17 06:35:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_12_15_000001_add_company_fields_to_users_table', 2),
(6, '2025_12_15_000002_create_whatsapp_instances_table', 2),
(7, '2025_12_15_000010_create_whatsapp_messages_table', 3),
(8, '2025_12_15_000001_create_campaigns_table', 4),
(9, '2025_12_15_000002_create_campaign_recipients_table', 4),
(10, '2025_12_15_000003_create_campaign_messages_and_attachments_tables', 4),
(11, '2025_12_15_000010_create_campaign_recipients_table', 5),
(12, '2025_12_15_000010_create_campaign_messages_table', 6),
(13, '2025_12_15_000011_create_tenants_table', 7),
(14, '2025_12_15_000012_add_tenant_id_to_users_table', 7),
(15, '2025_12_15_000013_create_contacts_table', 999),
(16, '2025_12_16_000001_add_phone_normalized_to_campaign_recipients', 1000),
(17, '2025_12_16_000002_create_whatsapp_instance_events_table', 1000),
(18, '2025_12_16_000100_create_settings_table', 1000),
(19, '2025_12_16_000102_add_is_admin_to_users_table', 1000),
(20, '2025_12_16_000001_create_chats_table', 1001),
(21, '2025_12_16_000002_create_messages_table', 1001);

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `key` varchar(120) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `settings`
--

INSERT INTO `settings` (`id`, `tenant_id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 1, 'campaign.pause_every', '20', '2025-12-16 17:00:39', '2025-12-16 17:36:32'),
(2, 1, 'campaign.pause_seconds', '20', '2025-12-16 17:00:39', '2025-12-16 17:36:32'),
(8, 1, 'campaign.delay_min_seconds', '9', '2025-12-16 17:36:32', '2025-12-16 17:36:32'),
(9, 1, 'campaign.delay_max_seconds', '12', '2025-12-16 17:36:32', '2025-12-16 17:36:32'),
(10, 1, 'campaign.limit_max', '50', '2025-12-16 17:36:32', '2025-12-16 17:36:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(160) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Empresa Padrão', 'default', 1, '2025-12-17 00:25:34', '2025-12-17 00:25:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `api_token` varchar(80) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `is_admin` tinyint(4) NOT NULL DEFAULT 0,
  `daily_limit` int(10) UNSIGNED NOT NULL DEFAULT 200,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `tenant_id`, `name`, `company_name`, `email`, `email_verified_at`, `password`, `api_token`, `status`, `is_admin`, `daily_limit`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'teste01', NULL, 'teste@agendeizap.com', NULL, '$2y$10$mC9GV3Gkp.5NVGybSZDKlOsUgAirfnSJWbuBvFdiiSLZDzGyaR.tS', NULL, 1, 0, 200, 'x9sBcZv5R3FdN97kDvO7GcQwVuw0eXbyKe2A0V22WKanV7CcIXuh6CQdC0fN', '2025-12-15 19:49:53', '2025-12-15 19:49:53'),
(2, 1, 'teste02', 'teste02', 'teste2@teste.com', NULL, '$2y$10$8u1l80JuyP/.yOK1L6ZNQ.1zPSspv07Cv7PANlFw8SMU1giNy6BJW', 'wGhRxFCZGrbE6ou20bL2BEXu3Was0U480oRr8eDcYs2VHnJCu4vJNHUCimtl', 1, 1, 220, 'PqEvbqfvI6fgAuUCQgGj29K9vTrw3WckJ2P6ZlDomD4gIPqklFIYEB3ts0vB', '2025-12-15 19:56:55', '2025-12-15 19:56:55'),
(3, 1, 'ritieli cogo', 'ritieli cogo', 'multiplaensino@gmail.com', NULL, '$2y$10$gCePNbYPEh1cw72E.SZDiu2t7xTT8p/FugpIsgl5lxOpBBLTr2Ws2', '28HwgbFHRXdydiofm0fqgpWyrQMl5o4yXdpRNFplk0Q7IYiLaQL0e2yjCWNN', 1, 0, 200, 'cf7FZwtwilyN1kdx6DAkrUTGyQsHE5jfyrH87apSQ8HYLdmEJGmJaVBmvAbc', '2025-12-17 01:30:07', '2025-12-17 01:30:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_instances`
--

CREATE TABLE `whatsapp_instances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(120) DEFAULT NULL,
  `instance_name` varchar(120) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `daily_limit` int(10) UNSIGNED NOT NULL DEFAULT 200,
  `sent_today` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sent_today_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `whatsapp_instances`
--

INSERT INTO `whatsapp_instances` (`id`, `user_id`, `label`, `instance_name`, `token`, `is_active`, `enabled`, `daily_limit`, `sent_today`, `sent_today_date`, `created_at`, `updated_at`) VALUES
(8, 2, 'pipe', 'pipe', 'EDIT3S9OxpaN0YVm1poGDwVCsA2tcXAlE4lDhQPImfejVMdV4LX5zzZjYuackOAq', 1, 1, 200, 0, NULL, '2025-12-15 22:53:26', '2025-12-17 05:13:31'),
(10, 3, 'telefoneRitieli', 'ritieli', 'mziAUkg6gJs0Ae6YYdQTbvayzCmeoQ4YxRkphF0DLw3e9enm9B3fRw2RRwNjk0ky', 1, 1, 200, 0, NULL, '2025-12-17 01:38:51', '2025-12-17 01:38:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_instance_events`
--

CREATE TABLE `whatsapp_instance_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `whatsapp_instance_id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(40) NOT NULL,
  `source` varchar(30) DEFAULT NULL,
  `status` varchar(40) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(190) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_messages`
--

CREATE TABLE `whatsapp_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `whatsapp_instance_id` bigint(20) UNSIGNED NOT NULL,
  `to` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'queued',
  `http_status` smallint(5) UNSIGNED DEFAULT NULL,
  `response_json` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaigns_user_id_status_index` (`user_id`,`status`),
  ADD KEY `campaigns_whatsapp_instance_id_status_index` (`whatsapp_instance_id`,`status`);

--
-- Índices de tabela `campaign_attachments`
--
ALTER TABLE `campaign_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_attachments_campaign_message_id_type_index` (`campaign_message_id`,`type`);

--
-- Índices de tabela `campaign_messages`
--
ALTER TABLE `campaign_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campaign_messages_campaign_position` (`campaign_id`,`position`);

--
-- Índices de tabela `campaign_recipients`
--
ALTER TABLE `campaign_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_campaign_phone` (`campaign_id`,`phone_digits`),
  ADD KEY `campaign_recipients_campaign_id_index` (`campaign_id`),
  ADD KEY `campaign_recipients_campaign_id_is_valid_index` (`campaign_id`,`is_valid`);

--
-- Índices de tabela `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_chat_instance_remote` (`whatsapp_instance_id`,`remote_jid`),
  ADD KEY `chats_user_id_index` (`user_id`),
  ADD KEY `chats_whatsapp_instance_id_index` (`whatsapp_instance_id`),
  ADD KEY `chats_remote_jid_index` (`remote_jid`);

--
-- Índices de tabela `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contacts_tenant_phone` (`tenant_id`,`phone_e164`),
  ADD KEY `idx_contacts_tenant_name` (`tenant_id`,`name`),
  ADD KEY `idx_contacts_tenant_is_group` (`tenant_id`,`is_group`);

--
-- Índices de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices de tabela `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_chat_id_index` (`chat_id`),
  ADD KEY `messages_contact_id_index` (`contact_id`),
  ADD KEY `messages_provider_message_id_index` (`provider_message_id`),
  ADD KEY `messages_direction_index` (`direction`),
  ADD KEY `messages_message_at_index` (`message_at`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Índices de tabela `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Índices de tabela `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_settings_tenant_key` (`tenant_id`,`key`),
  ADD KEY `idx_settings_tenant_key` (`tenant_id`,`key`);

--
-- Índices de tabela `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_slug_unique` (`slug`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_api_token_unique` (`api_token`),
  ADD KEY `idx_users_tenant` (`tenant_id`),
  ADD KEY `users_is_admin_index` (`is_admin`);

--
-- Índices de tabela `whatsapp_instances`
--
ALTER TABLE `whatsapp_instances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_instance_name` (`user_id`,`instance_name`),
  ADD KEY `whatsapp_instances_user_id_index` (`user_id`),
  ADD KEY `whatsapp_instances_user_id_enabled_index` (`user_id`,`enabled`),
  ADD KEY `idx_whatsapp_instances_user_active` (`user_id`,`is_active`);

--
-- Índices de tabela `whatsapp_instance_events`
--
ALTER TABLE `whatsapp_instance_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evt_tenant_instance_dt` (`tenant_id`,`whatsapp_instance_id`,`created_at`),
  ADD KEY `idx_evt_tenant_event_dt` (`tenant_id`,`event`,`created_at`),
  ADD KEY `whatsapp_instance_events_whatsapp_instance_id_foreign` (`whatsapp_instance_id`);

--
-- Índices de tabela `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whatsapp_messages_user_id_whatsapp_instance_id_index` (`user_id`,`whatsapp_instance_id`),
  ADD KEY `whatsapp_messages_user_id_status_index` (`user_id`,`status`),
  ADD KEY `whatsapp_messages_whatsapp_instance_id_foreign` (`whatsapp_instance_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `campaign_attachments`
--
ALTER TABLE `campaign_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `campaign_messages`
--
ALTER TABLE `campaign_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `campaign_recipients`
--
ALTER TABLE `campaign_recipients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `chats`
--
ALTER TABLE `chats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `whatsapp_instances`
--
ALTER TABLE `whatsapp_instances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `campaigns_whatsapp_instance_id_foreign` FOREIGN KEY (`whatsapp_instance_id`) REFERENCES `whatsapp_instances` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `campaign_attachments`
--
ALTER TABLE `campaign_attachments`
  ADD CONSTRAINT `campaign_attachments_campaign_message_id_foreign` FOREIGN KEY (`campaign_message_id`) REFERENCES `campaign_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `campaign_messages`
--
ALTER TABLE `campaign_messages`
  ADD CONSTRAINT `campaign_messages_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `campaign_recipients`
--
ALTER TABLE `campaign_recipients`
  ADD CONSTRAINT `campaign_recipients_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_chat_id_foreign` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `whatsapp_instances`
--
ALTER TABLE `whatsapp_instances`
  ADD CONSTRAINT `whatsapp_instances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  ADD CONSTRAINT `whatsapp_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `whatsapp_messages_whatsapp_instance_id_foreign` FOREIGN KEY (`whatsapp_instance_id`) REFERENCES `whatsapp_instances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
