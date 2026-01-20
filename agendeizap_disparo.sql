-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 20/01/2026 às 01:39
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
(38, 2, 17, 1500, 4000, 'Teste rogerio', 2, 4, 20, 30, NULL, 'finished', 0, 0, 0, NULL, NULL, '2026-01-13 03:57:10', '2026-01-13 03:57:46'),
(39, 2, 17, 1500, 4000, 'nova para dia 13', 2, 4, 20, 30, NULL, 'finished', 0, 0, 0, NULL, NULL, '2026-01-13 03:59:11', '2026-01-13 18:23:38');

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
  `updated_at` timestamp NULL DEFAULT NULL,
  `media_type` varchar(20) DEFAULT NULL,
  `media_url` text DEFAULT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_name` varchar(200) DEFAULT NULL,
  `caption` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `campaign_messages`
--

INSERT INTO `campaign_messages` (`id`, `campaign_id`, `position`, `text`, `primary_type`, `location_lat`, `location_lng`, `location_name`, `location_address`, `created_at`, `updated_at`, `media_type`, `media_url`, `mime_type`, `file_name`, `caption`) VALUES
(54, 38, 1, 'teste 21   57', 'text', NULL, NULL, NULL, NULL, '2026-01-13 03:57:40', '2026-01-13 03:57:40', NULL, NULL, NULL, NULL, NULL),
(55, 39, 1, '🚨ATENÇÃO! ÚLTIMAS VAGAS COM BENEFÍCIO EXCLUSIVO!\r\n\r\n\r\n ⚠️SOMENTE ESTA SEMANA!\r\n\r\n🎁 1ª mensalidade GRÁTIS para matrículas realizadas até 16/01/26.. \r\n\r\nPara agilizar, venha presencialmente ao campus de Bento Gonçalves – Bloco B.\r\n\r\n🕗 Horário de atendimento:\r\n⏰ 08:00 às 18:00\r\n⏸️ Intervalo: 11:30 às 13:30\r\n\r\n\r\n⏰ Não perca!', 'text', NULL, NULL, NULL, NULL, '2026-01-13 04:01:06', '2026-01-13 04:01:06', NULL, NULL, NULL, NULL, NULL);

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
(611, 38, NULL, '54984359885', '5554984359885', 1, NULL, 'sent', '2026-01-13 03:57:46', NULL, NULL, '2026-01-13 03:57:27', '2026-01-13 03:57:46'),
(612, 39, 'Giovana Lopes Costa', '5554996206200', '5554996206200', 1, NULL, 'sent', '2026-01-13 17:56:39', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:56:39'),
(613, 39, 'Andressa Civardi', '5554996229801', '5554996229801', 1, NULL, 'sent', '2026-01-13 17:56:44', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:56:44'),
(614, 39, 'Larissa Ramansini Titon', '5554999011204', '5554999011204', 1, NULL, 'sent', '2026-01-13 17:56:49', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:56:49'),
(615, 39, 'Eduarda Roos Sperafico', '5554997003816', '5554997003816', 1, NULL, 'sent', '2026-01-13 17:56:54', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:56:54'),
(616, 39, 'Amanda Glembotzky Freitag', '5554991527644', '5554991527644', 1, NULL, 'sent', '2026-01-13 17:57:01', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:01'),
(617, 39, 'Tiago Darfais', '5554999907499', '5554999907499', 1, NULL, 'sent', '2026-01-13 17:57:04', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:04'),
(618, 39, 'Kauã De Borba Maceno', '5551989051596', '5551989051596', 1, NULL, 'sent', '2026-01-13 17:57:10', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:10'),
(619, 39, 'Carla Eduarda Barros Tonet', '5554984336936', '5554984336936', 1, NULL, 'sent', '2026-01-13 17:57:15', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:15'),
(620, 39, 'Heloise Vitória Mendonça de Aguiar', '5554991419354', '5554991419354', 1, NULL, 'sent', '2026-01-13 17:57:19', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:19'),
(621, 39, 'Otavio Bernardines', '5554991217673', '5554991217673', 1, NULL, 'sent', '2026-01-13 17:57:22', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:22'),
(622, 39, 'Débora Cenci', '5554996728700', '5554996728700', 1, NULL, 'sent', '2026-01-13 17:57:25', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:25'),
(623, 39, 'Camila Ivone Batista Ribeiro', '5554996444310', '5554996444310', 1, NULL, 'sent', '2026-01-13 17:57:30', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:30'),
(624, 39, 'Igor Daniel Dos Santos Benítez', '5554999138634', '5554999138634', 1, NULL, 'sent', '2026-01-13 17:57:34', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:34'),
(625, 39, 'ALBINO SALERI JUNIOR', '5584991317984', '5584991317984', 1, NULL, 'sent', '2026-01-13 17:57:38', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:38'),
(626, 39, 'Otávio Gandini Passarin', '5554996123361', '5554996123361', 1, NULL, 'sent', '2026-01-13 17:57:42', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:42'),
(627, 39, 'Pamela Gabriela Dos Santos', '5551998294239', '5551998294239', 1, NULL, 'sent', '2026-01-13 17:57:47', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:47'),
(628, 39, 'Jordana Maria Camargo', '5554996980291', '5554996980291', 1, NULL, 'sent', '2026-01-13 17:57:50', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:50'),
(629, 39, 'Henrique Maciel Thomas', '5554994169009', '5554994169009', 1, NULL, 'sent', '2026-01-13 17:57:54', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:54'),
(630, 39, 'Raphael De Souza Canal', '5554991737535', '5554991737535', 1, NULL, 'sent', '2026-01-13 17:57:59', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:57:59'),
(631, 39, 'Mateus Aimi Gonçalves', '5554996720736', '5554996720736', 1, NULL, 'sent', '2026-01-13 17:58:04', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:04'),
(632, 39, 'Monica Pinheiro Pedroso', '5554996013913', '5554996013913', 1, NULL, 'sent', '2026-01-13 17:58:08', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:08'),
(633, 39, 'Lucas Ventura Gregio', '5554991678830', '5554991678830', 1, NULL, 'sent', '2026-01-13 17:58:13', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:13'),
(634, 39, 'Fernando Golin Zanela', '5554999562010', '5554999562010', 1, NULL, 'sent', '2026-01-13 17:58:17', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:17'),
(635, 39, 'Luciana Dai Pra Penteado', '5551997792643', '5551997792643', 1, NULL, 'sent', '2026-01-13 17:58:23', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:23'),
(636, 39, 'Mario Mauricio Da Silva Xavier', '5555999858080', '5555999858080', 1, NULL, 'sent', '2026-01-13 17:58:26', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:26'),
(637, 39, 'Valéria De Vargas Cecchin', '5554996530415', '5554996530415', 1, NULL, 'sent', '2026-01-13 17:58:31', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:31'),
(638, 39, 'Vinicius Thuns', '5554996607963', '5554996607963', 1, NULL, 'sent', '2026-01-13 17:58:34', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:34'),
(639, 39, 'Tainá Silva', '5554999589259', '5554999589259', 1, NULL, 'sent', '2026-01-13 17:58:37', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:37'),
(640, 39, 'Luan Luvisa', '5554992024895', '5554992024895', 1, NULL, 'sent', '2026-01-13 17:58:42', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:42'),
(641, 39, 'Évelin Vitória Salvi', '5554996325048', '5554996325048', 1, NULL, 'sent', '2026-01-13 17:58:45', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:45'),
(642, 39, 'Pedro Henrique Michellon', '5554991882525', '5554991882525', 1, NULL, 'sent', '2026-01-13 17:58:50', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:50'),
(643, 39, 'Luiz Gabriel Cornelius Schafer', '5551980512507', '5551980512507', 1, NULL, 'sent', '2026-01-13 17:58:55', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:55'),
(644, 39, 'Emilly Boaro', '5554993701444', '5554993701444', 1, NULL, 'sent', '2026-01-13 17:58:59', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:58:59'),
(645, 39, 'Felipe Zanetti Mita', '5554996617222', '5554996617222', 1, NULL, 'sent', '2026-01-13 17:59:02', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:02'),
(646, 39, 'Nicolle Ramos Nunes', '5554996170410', '5554996170410', 1, NULL, 'sent', '2026-01-13 17:59:05', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:05'),
(647, 39, 'Gabriela Goulart', '5551997593244', '5551997593244', 1, NULL, 'sent', '2026-01-13 17:59:10', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:10'),
(648, 39, 'Giovanna Berghann', '5554999707775', '5554999707775', 1, NULL, 'sent', '2026-01-13 17:59:13', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:13'),
(649, 39, 'Grasiela Fernandes da Silva Schafer', '5554997047873', '5554997047873', 1, NULL, 'sent', '2026-01-13 17:59:19', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:19'),
(650, 39, 'Cássio Augusto Willers', '5551984154947', '5551984154947', 1, NULL, 'sent', '2026-01-13 17:59:24', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:24'),
(651, 39, 'Amanda Bagatini Berti', '5554999401140', '5554999401140', 1, NULL, 'sent', '2026-01-13 17:59:27', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:27'),
(652, 39, 'Daniele Gräff', '5551980658847', '5551980658847', 1, NULL, 'sent', '2026-01-13 17:59:31', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:31'),
(653, 39, 'Kauana Policarpio', '5551980366898', '5551980366898', 1, NULL, 'sent', '2026-01-13 17:59:35', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:35'),
(654, 39, 'Rogério De Bortoli Foresti', '5554996576976', '5554996576976', 1, NULL, 'sent', '2026-01-13 17:59:38', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:38'),
(655, 39, 'Brenda Yasmin Soares', '5554996403654', '5554996403654', 1, NULL, 'sent', '2026-01-13 17:59:41', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:41'),
(656, 39, 'Vitor Geraldo Waechter', '5554996886587', '5554996886587', 1, NULL, 'sent', '2026-01-13 17:59:46', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:46'),
(657, 39, 'Guilherme Antony Marcolin', '5554996360889', '5554996360889', 1, NULL, 'sent', '2026-01-13 17:59:49', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:49'),
(658, 39, 'Jamesson Jairo Paiva do Nascimento', '5581999385463', '5581999385463', 1, NULL, 'sent', '2026-01-13 17:59:57', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 17:59:57'),
(659, 39, 'Brenda Huve Schwening', '5554992624141', '5554992624141', 1, NULL, 'sent', '2026-01-13 18:00:04', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:00:04'),
(661, 39, 'Eliezer Dos Santos Pereira', '5554996647042', '5554996647042', 1, NULL, 'sent', '2026-01-13 18:18:20', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:18:20'),
(662, 39, '5554993296669', '4147543014', '4147543014', 0, 'Formato inesperado (esperado 55 + DDD + número).', 'pending', NULL, NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(663, 39, 'Yuri Cziraski Da Silva Vianna', '5551998889922', '5551998889922', 1, NULL, 'sent', '2026-01-13 18:18:21', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:18:21'),
(664, 39, 'Gabriely Menegon', '5554999546547', '5554999546547', 1, NULL, 'sent', '2026-01-13 18:18:22', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:18:22'),
(665, 39, 'Brenda Ferrari', '5554996542280', '5554996542280', 1, NULL, 'sent', '2026-01-13 18:18:26', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:18:26'),
(666, 39, 'Felipe Marcon', '5554999993498', '5554999993498', 1, NULL, 'sent', '2026-01-13 18:18:27', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:18:27'),
(667, 39, 'Gabriela Gioriatti', '5554996004578', '5554996004578', 1, NULL, 'sent', '2026-01-13 18:18:29', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:18:29'),
(668, 39, 'Celina Binotti Vanás', '5554991615974', '5554991615974', 1, NULL, 'sent', '2026-01-13 18:18:31', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:18:31'),
(669, 39, 'Felipe Misturini Sievering', '5551992930044', '5551992930044', 1, NULL, 'sent', '2026-01-13 18:18:32', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:18:32'),
(671, 39, 'Murilo Tremarin', '5554999877770', '5554999877770', 1, NULL, 'sent', '2026-01-13 18:19:18', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:18'),
(672, 39, 'Maicon Gabriel Pereira Ribeiro', '5554992084222', '5554992084222', 1, NULL, 'sent', '2026-01-13 18:19:19', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:19'),
(673, 39, 'Thaís de Morais Lanes', '5554999302712', '5554999302712', 1, NULL, 'sent', '2026-01-13 18:19:20', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:20'),
(674, 39, 'Fabio Roberto Mühlbeier', '555496059869', '555496059869', 1, NULL, 'sent', '2026-01-13 18:19:23', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:23'),
(675, 39, 'Gabrieli Maria Fink', '555491953917', '555491953917', 1, NULL, 'sent', '2026-01-13 18:19:24', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:24'),
(676, 39, 'Cristiano Buffon', '555491511303', '555491511303', 1, NULL, 'sent', '2026-01-13 18:19:26', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:26'),
(677, 39, 'Barbara Maria Ferreira dos Santos', '5554999496037', '5554999496037', 1, NULL, 'sent', '2026-01-13 18:19:27', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:27'),
(678, 39, 'Lucas Alan Tiburski', '5554984473800', '5554984473800', 1, NULL, 'sent', '2026-01-13 18:19:29', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:29'),
(679, 39, 'Mayara Zanotto', '5554981366068', '5554981366068', 1, NULL, 'sent', '2026-01-13 18:19:31', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:31'),
(680, 39, 'Erica Da Silva Dos Santos', '5554993199704', '5554993199704', 1, NULL, 'sent', '2026-01-13 18:19:32', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:32'),
(681, 39, 'Valdecir severgnini', '555499333305', '555499333305', 1, NULL, 'sent', '2026-01-13 18:19:35', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:35'),
(682, 39, 'Evelyn Cristine dos Santos', '5554992255088', '5554992255088', 1, NULL, 'sent', '2026-01-13 18:19:36', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:36'),
(683, 39, 'Jéssica Teixeira', '5554993313408', '5554993313408', 1, NULL, 'sent', '2026-01-13 18:19:37', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:37'),
(684, 39, 'Guilherme Augusto Ribeiro Montemezzo', '5554999143229', '5554999143229', 1, NULL, 'sent', '2026-01-13 18:19:40', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:40'),
(685, 39, 'Gabrielly Silva Sauthier', '5551995538981', '5551995538981', 1, NULL, 'sent', '2026-01-13 18:19:41', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:41'),
(686, 39, 'Antonia Luisa Löff', '5551996119969', '5551996119969', 1, NULL, 'sent', '2026-01-13 18:19:42', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:42'),
(687, 39, 'Érika Vicari', '5554999658674', '5554999658674', 1, NULL, 'sent', '2026-01-13 18:19:45', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:45'),
(688, 39, 'Raisa Lopes de Souza', '5554996727056', '5554996727056', 1, NULL, 'sent', '2026-01-13 18:19:46', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:46'),
(689, 39, 'Augusto Denardi Mecca', '5554992120220', '5554992120220', 1, NULL, 'sent', '2026-01-13 18:19:47', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:47'),
(690, 39, 'Martinha Gotardo', '5554999539688', '5554999539688', 1, NULL, 'sent', '2026-01-13 18:19:49', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:19:49'),
(693, 39, 'Paula Beatriz de Souza Paulino', '5554981102466', '5554981102466', 1, NULL, 'sent', '2026-01-13 18:20:59', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:20:59'),
(694, 39, 'Luana Donatti da Silva', '5554981679604', '5554981679604', 1, NULL, 'sent', '2026-01-13 18:21:00', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:00'),
(695, 39, 'Ana Luiza Piccoli Perera', '5554992339276', '5554992339276', 1, NULL, 'sent', '2026-01-13 18:21:01', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:01'),
(696, 39, 'Camilly Vitória da Conceição', '5554999413904', '5554999413904', 1, NULL, 'sent', '2026-01-13 18:21:05', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:05'),
(697, 39, 'Marcelo Gregoletto', '555496124506', '555496124506', 1, NULL, 'sent', '2026-01-13 18:21:06', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:06'),
(698, 39, 'João Augusto Maraschim Weber', '5551996588221', '5551996588221', 1, NULL, 'sent', '2026-01-13 18:21:08', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:08'),
(699, 39, 'Mauricio Sonaglio', '5554999989214', '5554999989214', 1, NULL, 'sent', '2026-01-13 18:21:10', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:10'),
(700, 39, 'Maria Eduarda Tomasi', '5554981375097', '5554981375097', 1, NULL, 'sent', '2026-01-13 18:21:11', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:11'),
(701, 39, 'Alexandra Fitarelli', '555496766679', '555496766679', 1, NULL, 'sent', '2026-01-13 18:21:13', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:13'),
(702, 39, 'Julia Monteblanco Cavalini', '555492107071', '555492107071', 1, NULL, 'sent', '2026-01-13 18:21:14', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:14'),
(703, 39, 'Tiago Casagrande Otaram', '5554981534229', '5554981534229', 1, NULL, 'sent', '2026-01-13 18:21:15', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:15'),
(705, 39, 'Mariana Martini Civardi', '5554984082426', '5554984082426', 1, NULL, 'sent', '2026-01-13 18:21:31', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:31'),
(706, 39, 'Suelen Rocha Souza', '555491269837', '555491269837', 1, NULL, 'sent', '2026-01-13 18:21:34', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:34'),
(707, 39, 'Tamara Nunes Girardello', '5554999100954', '5554999100954', 1, NULL, 'sent', '2026-01-13 18:21:35', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:35'),
(708, 39, 'Maria Eduarda dos Santos Seixas', '5591981607358', '5591981607358', 1, NULL, 'sent', '2026-01-13 18:21:36', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:36'),
(709, 39, 'Stephanie Regina de Carli Farias', '5554996866101', '5554996866101', 1, NULL, 'sent', '2026-01-13 18:21:37', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:37'),
(710, 39, 'Maely Ananda Ferreira de Santana', '5591983313088', '5591983313088', 1, NULL, 'sent', '2026-01-13 18:21:38', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:38'),
(711, 39, 'Cristiane Valmorbida Dallepiane', '555491958188', '555491958188', 1, NULL, 'sent', '2026-01-13 18:21:42', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:42'),
(712, 39, 'Ester Mirian da Silva', '5554981562892', '5554981562892', 1, NULL, 'sent', '2026-01-13 18:21:43', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:43'),
(713, 39, 'Julia Zaffari', '555499537916', '555499537916', 1, NULL, 'sent', '2026-01-13 18:21:44', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:44'),
(714, 39, 'Amdré Guzzo Lazzarotto', '5554992013658', '5554992013658', 1, NULL, 'sent', '2026-01-13 18:21:47', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:47'),
(715, 39, 'Willian Lando Czeikoski', '555484083597', '555484083597', 1, NULL, 'sent', '2026-01-13 18:21:48', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:48'),
(716, 39, 'Cristina Chies da Silva', '5554996971346', '5554996971346', 1, NULL, 'sent', '2026-01-13 18:21:50', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:50'),
(717, 39, 'Lizandro Felipe Maslowski', '5555996591697', '5555996591697', 1, NULL, 'sent', '2026-01-13 18:21:51', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:51'),
(718, 39, 'Jennifer Alves do Amaral de Matos', '5554996368475', '5554996368475', 1, NULL, 'sent', '2026-01-13 18:21:54', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:54'),
(719, 39, 'Isadora Rigotti Tristacci', '5554996201927', '5554996201927', 1, NULL, 'sent', '2026-01-13 18:21:55', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:55'),
(720, 39, 'Gabriela Gasque Cantarelli Pereira', '5554981231795', '5554981231795', 1, NULL, 'sent', '2026-01-13 18:21:56', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:21:56'),
(721, 39, 'Relmer Souza Barbosa', '5575982940121', '5575982940121', 1, NULL, 'sent', '2026-01-13 18:22:00', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:00'),
(722, 39, 'Suene Gomes Silva', '555197650203', '555197650203', 1, NULL, 'sent', '2026-01-13 18:22:01', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:01'),
(723, 39, 'Ana Caroline Pereira Ferreira', '5553999300597', '5553999300597', 1, NULL, 'sent', '2026-01-13 18:22:02', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:02'),
(724, 39, 'Elto Rodrigues', '5554981337777', '5554981337777', 1, NULL, 'sent', '2026-01-13 18:22:04', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:04'),
(725, 39, 'Manuela de Camargo Schäfer', '5551980299829', '5551980299829', 1, NULL, 'sent', '2026-01-13 18:22:06', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:06'),
(726, 39, 'Gabrieli Kovaleski', '5554991392153', '5554991392153', 1, NULL, 'sent', '2026-01-13 18:22:07', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:07'),
(727, 39, 'Ana Luiza Angeli da Rosa', '5554996083185', '5554996083185', 1, NULL, 'sent', '2026-01-13 18:22:08', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:08'),
(728, 39, 'Maria Eduarda dos Santos Costa', '5554996635139', '5554996635139', 1, NULL, 'sent', '2026-01-13 18:22:11', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:11'),
(729, 39, 'Angélica Milena Cislaghi', '5551998775513', '5551998775513', 1, NULL, 'sent', '2026-01-13 18:22:12', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:12'),
(730, 39, 'Raíssa Carteri Bonamigo', '5554999139415', '5554999139415', 1, NULL, 'sent', '2026-01-13 18:22:13', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:13'),
(731, 39, 'Marceli Dallabrida', '555499896645', '555499896645', 1, NULL, 'sent', '2026-01-13 18:22:17', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:17'),
(732, 39, 'Emanuelle de Souza Castilho', '5554992800110', '5554992800110', 1, NULL, 'sent', '2026-01-13 18:22:18', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:18'),
(733, 39, 'Jenifer de Oliveira Caron', '5554996108797', '5554996108797', 1, NULL, 'sent', '2026-01-13 18:22:19', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:19'),
(734, 39, 'Vanessa Fátima Antoniolli', '555499422327', '555499422327', 1, NULL, 'sent', '2026-01-13 18:22:21', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:21'),
(735, 39, 'Guilherme Jardim Comassetto', '555399616471', '555399616471', 1, NULL, 'sent', '2026-01-13 18:22:22', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:22'),
(736, 39, 'Giovana Guisso Possa', '5554984085458', '5554984085458', 1, NULL, 'sent', '2026-01-13 18:22:23', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:23'),
(737, 39, 'Érik Kauã Patias', '5554994068778', '5554994068778', 1, NULL, 'sent', '2026-01-13 18:22:27', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:27'),
(738, 39, 'Talita Tatiane Ortolan', '5554984007862', '5554984007862', 1, NULL, 'sent', '2026-01-13 18:22:29', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:29'),
(739, 39, 'Joana de Deus Goulart', '5554996765377', '5554996765377', 1, NULL, 'sent', '2026-01-13 18:22:30', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:30'),
(740, 39, 'Iasmin Ramos Carvalho', '5554996797730', '5554996797730', 1, NULL, 'sent', '2026-01-13 18:22:33', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:33'),
(741, 39, 'Taliza Smaltti', '5554981667508', '5554981667508', 1, NULL, 'sent', '2026-01-13 18:22:34', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:34'),
(742, 39, 'Hellen Stefany Ferreira da Silva Pellizzari', '5554992734202', '5554992734202', 1, NULL, 'sent', '2026-01-13 18:22:35', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:35'),
(743, 39, 'Maria Eduarda da Silva Soares', '5554984061498', '5554984061498', 1, NULL, 'sent', '2026-01-13 18:22:38', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:38'),
(744, 39, 'Joel guerville', '5554981267720', '5554981267720', 1, NULL, 'sent', '2026-01-13 18:22:39', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:39'),
(745, 39, 'Mateus de Souza Rocha', '5551997742142', '5551997742142', 1, NULL, 'sent', '2026-01-13 18:22:40', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:40'),
(746, 39, 'Andréia Dettoni', '555497135587', '555497135587', 1, NULL, 'sent', '2026-01-13 18:22:42', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:42'),
(747, 39, 'Gabriel de Jesus Hansen', '5554999883277', '5554999883277', 1, NULL, 'sent', '2026-01-13 18:22:44', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:44'),
(748, 39, 'Adriana Sousa Lisboa', '5551999574083', '5551999574083', 1, NULL, 'sent', '2026-01-13 18:22:45', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:45'),
(749, 39, 'Julia Rostirolla Machado', '5554991059400', '5554991059400', 1, NULL, 'sent', '2026-01-13 18:22:46', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:46'),
(750, 39, 'Cristian Athirson Peniche de Oliveira', '5554993004545', '5554993004545', 1, NULL, 'sent', '2026-01-13 18:22:48', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:48'),
(751, 39, 'Julio César Do Amaral', '5554996705459', '5554996705459', 1, NULL, 'sent', '2026-01-13 18:22:50', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:50'),
(752, 39, 'Vinícius Zanatta Santos', '5554981292806', '5554981292806', 1, NULL, 'sent', '2026-01-13 18:22:51', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:51'),
(753, 39, 'Arlei Morais', '5554981499932', '5554981499932', 1, NULL, 'sent', '2026-01-13 18:22:54', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:54'),
(754, 39, 'Gabriele Luana Geleinski', '5554996164344', '5554996164344', 1, NULL, 'sent', '2026-01-13 18:22:55', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:55'),
(755, 39, 'Kelvin Livano Guindani', '5554999093994', '5554999093994', 1, NULL, 'sent', '2026-01-13 18:22:56', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:56'),
(756, 39, 'Luís Felipe Casagrande', '5554994138888', '5554994138888', 1, NULL, 'sent', '2026-01-13 18:22:59', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:22:59'),
(757, 39, 'Lorenzo Giovanni Froner', '5554996661329', '5554996661329', 1, NULL, 'sent', '2026-01-13 18:23:00', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:00'),
(758, 39, 'Dieler Batista', '555197709935', '555197709935', 1, NULL, 'sent', '2026-01-13 18:23:02', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:02'),
(759, 39, 'Fabiane Carbonera Maran', '5554999323261', '5554999323261', 1, NULL, 'sent', '2026-01-13 18:23:03', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:03'),
(760, 39, 'CARLOS GREGORIO LOPEZ GONZALEZ', '5551996519141', '5551996519141', 1, NULL, 'sent', '2026-01-13 18:23:05', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:05'),
(761, 39, 'Camila Lotis', '555499529948', '555499529948', 1, NULL, 'sent', '2026-01-13 18:23:06', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:06'),
(762, 39, 'Mariana Fernandes Campana', '5554984058565', '5554984058565', 1, NULL, 'sent', '2026-01-13 18:23:07', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:07'),
(763, 39, 'Júlia de Azevedo Muniz', '5554996005069', '5554996005069', 1, NULL, 'sent', '2026-01-13 18:23:08', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:08'),
(764, 39, 'Glória de Vargas dos Santos', '5554999921410', '5554999921410', 1, NULL, 'sent', '2026-01-13 18:23:10', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:10'),
(765, 39, 'Ariane dos Santos Dorneles', '5554991224727', '5554991224727', 1, NULL, 'sent', '2026-01-13 18:23:11', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:11'),
(766, 39, 'RICARDO REICHEMBACH DOS SANTOS', '5551999109490', '5551999109490', 1, NULL, 'sent', '2026-01-13 18:23:13', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:13'),
(767, 39, 'Maria Eduarda dos Santos', '5554991226726', '5554991226726', 1, NULL, 'sent', '2026-01-13 18:23:15', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:15'),
(768, 39, 'Beatriz Todeschini Lovatto', '5554996116093', '5554996116093', 1, NULL, 'sent', '2026-01-13 18:23:16', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:16'),
(769, 39, 'Tamar Taina Santiago de Souza', '5554996724182', '5554996724182', 1, NULL, 'sent', '2026-01-13 18:23:18', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:18'),
(770, 39, 'Rodrigo Cunha Amorim', '555195890547', '555195890547', 1, NULL, 'sent', '2026-01-13 18:23:19', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:19'),
(771, 39, 'Tiago Luiz Menegat Todeschini', '5554999868141', '5554999868141', 1, NULL, 'sent', '2026-01-13 18:23:20', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:20'),
(772, 39, 'Ana Cláudia da Silva Pereira', '5551996470405', '5551996470405', 1, NULL, 'sent', '2026-01-13 18:23:23', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:23'),
(773, 39, 'Eduarda Zago Antunes', '5554996744381', '5554996744381', 1, NULL, 'sent', '2026-01-13 18:23:24', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:24'),
(775, 39, 'Lisiane Tonet Fernandes Falcão', '5555999201426', '5555999201426', 1, NULL, 'sent', '2026-01-13 18:23:37', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:37'),
(776, 39, 'Fernando Bissolotti', '5554996691770', '5554996691770', 1, NULL, 'sent', '2026-01-13 18:23:38', NULL, NULL, '2026-01-13 03:59:22', '2026-01-13 18:23:38');

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
(1, 2, 8, '5554984359885@s.whatsapp.net', NULL, '2026-01-12 00:36:31', '2025-12-17 03:13:41', '2026-01-12 00:36:31'),
(2, 2, 8, '5555999595193@s.whatsapp.net', 'Teste', '2025-12-18 06:35:13', '2025-12-17 06:06:11', '2025-12-18 06:35:13');

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
(598, 1, NULL, NULL, '+5554984359885', '54984359885', NULL, NULL, 0, NULL, '2026-01-13 03:08:43', '2026-01-13 03:57:27'),
(603, 1, NULL, NULL, '+54984359885', '54984359885', NULL, NULL, 0, NULL, '2026-01-13 03:57:16', '2026-01-13 03:57:16'),
(605, 1, 'Giovana Lopes Costa', NULL, '+5554996206200', '5554996206200', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(606, 1, 'Andressa Civardi', NULL, '+5554996229801', '5554996229801', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(607, 1, 'Larissa Ramansini Titon', NULL, '+5554999011204', '5554999011204', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(608, 1, 'Eduarda Roos Sperafico', NULL, '+5554997003816', '5554997003816', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(609, 1, 'Amanda Glembotzky Freitag', NULL, '+5554991527644', '5554991527644', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(610, 1, 'Tiago Darfais', NULL, '+5554999907499', '5554999907499', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(611, 1, 'Kauã De Borba Maceno', NULL, '+5551989051596', '5551989051596', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(612, 1, 'Carla Eduarda Barros Tonet', NULL, '+5554984336936', '5554984336936', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(613, 1, 'Heloise Vitória Mendonça de Aguiar', NULL, '+5554991419354', '5554991419354', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(614, 1, 'Otavio Bernardines', NULL, '+5554991217673', '5554991217673', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(615, 1, 'Débora Cenci', NULL, '+5554996728700', '5554996728700', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(616, 1, 'Camila Ivone Batista Ribeiro', NULL, '+5554996444310', '5554996444310', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(617, 1, 'Igor Daniel Dos Santos Benítez', NULL, '+5554999138634', '5554999138634', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(618, 1, 'ALBINO SALERI JUNIOR', NULL, '+5584991317984', '5584991317984', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(619, 1, 'Otávio Gandini Passarin', NULL, '+5554996123361', '5554996123361', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(620, 1, 'Pamela Gabriela Dos Santos', NULL, '+5551998294239', '5551998294239', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(621, 1, 'Jordana Maria Camargo', NULL, '+5554996980291', '5554996980291', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(622, 1, 'Henrique Maciel Thomas', NULL, '+5554994169009', '5554994169009', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(623, 1, 'Raphael De Souza Canal', NULL, '+5554991737535', '5554991737535', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(624, 1, 'Mateus Aimi Gonçalves', NULL, '+5554996720736', '5554996720736', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(625, 1, 'Monica Pinheiro Pedroso', NULL, '+5554996013913', '5554996013913', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(626, 1, 'Lucas Ventura Gregio', NULL, '+5554991678830', '5554991678830', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(627, 1, 'Fernando Golin Zanela', NULL, '+5554999562010', '5554999562010', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(628, 1, 'Luciana Dai Pra Penteado', NULL, '+5551997792643', '5551997792643', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(629, 1, 'Mario Mauricio Da Silva Xavier', NULL, '+5555999858080', '5555999858080', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(630, 1, 'Valéria De Vargas Cecchin', NULL, '+5554996530415', '5554996530415', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(631, 1, 'Vinicius Thuns', NULL, '+5554996607963', '5554996607963', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(632, 1, 'Tainá Silva', NULL, '+5554999589259', '5554999589259', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(633, 1, 'Luan Luvisa', NULL, '+5554992024895', '5554992024895', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(634, 1, 'Évelin Vitória Salvi', NULL, '+5554996325048', '5554996325048', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(635, 1, 'Pedro Henrique Michellon', NULL, '+5554991882525', '5554991882525', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(636, 1, 'Luiz Gabriel Cornelius Schafer', NULL, '+5551980512507', '5551980512507', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(637, 1, 'Emilly Boaro', NULL, '+5554993701444', '5554993701444', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(638, 1, 'Felipe Zanetti Mita', NULL, '+5554996617222', '5554996617222', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(639, 1, 'Nicolle Ramos Nunes', NULL, '+5554996170410', '5554996170410', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(640, 1, 'Gabriela Goulart', NULL, '+5551997593244', '5551997593244', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(641, 1, 'Giovanna Berghann', NULL, '+5554999707775', '5554999707775', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(642, 1, 'Grasiela Fernandes da Silva Schafer', NULL, '+5554997047873', '5554997047873', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(643, 1, 'Cássio Augusto Willers', NULL, '+5551984154947', '5551984154947', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(644, 1, 'Amanda Bagatini Berti', NULL, '+5554999401140', '5554999401140', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(645, 1, 'Daniele Gräff', NULL, '+5551980658847', '5551980658847', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(646, 1, 'Kauana Policarpio', NULL, '+5551980366898', '5551980366898', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(647, 1, 'Rogério De Bortoli Foresti', NULL, '+5554996576976', '5554996576976', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(648, 1, 'Brenda Yasmin Soares', NULL, '+5554996403654', '5554996403654', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(649, 1, 'Vitor Geraldo Waechter', NULL, '+5554996886587', '5554996886587', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(650, 1, 'Guilherme Antony Marcolin', NULL, '+5554996360889', '5554996360889', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(651, 1, 'Jamesson Jairo Paiva do Nascimento', NULL, '+5581999385463', '5581999385463', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(652, 1, 'Brenda Huve Schwening', NULL, '+5554992624141', '5554992624141', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(653, 1, 'Manuela Cunico Dos Santos', NULL, '+5554992853636', '5554992853636', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(654, 1, 'Eliezer Dos Santos Pereira', NULL, '+5554996647042', '5554996647042', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(655, 1, '5554993296669', NULL, '+4147543014', '4147543014', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(656, 1, 'Yuri Cziraski Da Silva Vianna', NULL, '+5551998889922', '5551998889922', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(657, 1, 'Gabriely Menegon', NULL, '+5554999546547', '5554999546547', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(658, 1, 'Brenda Ferrari', NULL, '+5554996542280', '5554996542280', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(659, 1, 'Felipe Marcon', NULL, '+5554999993498', '5554999993498', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(660, 1, 'Gabriela Gioriatti', NULL, '+5554996004578', '5554996004578', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(661, 1, 'Celina Binotti Vanás', NULL, '+5554991615974', '5554991615974', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(662, 1, 'Felipe Misturini Sievering', NULL, '+5551992930044', '5551992930044', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(663, 1, 'Lara Corti Borges', NULL, '+5554984283530', '5554984283530', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(664, 1, 'Murilo Tremarin', NULL, '+5554999877770', '5554999877770', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(665, 1, 'Maicon Gabriel Pereira Ribeiro', NULL, '+5554992084222', '5554992084222', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(666, 1, 'Thaís de Morais Lanes', NULL, '+5554999302712', '5554999302712', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(667, 1, 'Fabio Roberto Mühlbeier', NULL, '+555496059869', '555496059869', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(668, 1, 'Gabrieli Maria Fink', NULL, '+555491953917', '555491953917', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(669, 1, 'Cristiano Buffon', NULL, '+555491511303', '555491511303', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(670, 1, 'Barbara Maria Ferreira dos Santos', NULL, '+5554999496037', '5554999496037', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(671, 1, 'Lucas Alan Tiburski', NULL, '+5554984473800', '5554984473800', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(672, 1, 'Mayara Zanotto', NULL, '+5554981366068', '5554981366068', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(673, 1, 'Erica Da Silva Dos Santos', NULL, '+5554993199704', '5554993199704', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(674, 1, 'Valdecir severgnini', NULL, '+555499333305', '555499333305', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(675, 1, 'Evelyn Cristine dos Santos', NULL, '+5554992255088', '5554992255088', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(676, 1, 'Jéssica Teixeira', NULL, '+5554993313408', '5554993313408', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(677, 1, 'Guilherme Augusto Ribeiro Montemezzo', NULL, '+5554999143229', '5554999143229', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(678, 1, 'Gabrielly Silva Sauthier', NULL, '+5551995538981', '5551995538981', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(679, 1, 'Antonia Luisa Löff', NULL, '+5551996119969', '5551996119969', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(680, 1, 'Érika Vicari', NULL, '+5554999658674', '5554999658674', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(681, 1, 'Raisa Lopes de Souza', NULL, '+5554996727056', '5554996727056', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(682, 1, 'Augusto Denardi Mecca', NULL, '+5554992120220', '5554992120220', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(683, 1, 'Martinha Gotardo', NULL, '+5554999539688', '5554999539688', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(684, 1, 'Marcos Felipe da SIlva Baia', NULL, '+5554996598905', '5554996598905', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(685, 1, 'Jadeson Ferreira Ritter', NULL, '+5554991002616', '5554991002616', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(686, 1, 'Paula Beatriz de Souza Paulino', NULL, '+5554981102466', '5554981102466', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(687, 1, 'Luana Donatti da Silva', NULL, '+5554981679604', '5554981679604', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(688, 1, 'Ana Luiza Piccoli Perera', NULL, '+5554992339276', '5554992339276', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(689, 1, 'Camilly Vitória da Conceição', NULL, '+5554999413904', '5554999413904', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(690, 1, 'Marcelo Gregoletto', NULL, '+555496124506', '555496124506', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(691, 1, 'João Augusto Maraschim Weber', NULL, '+5551996588221', '5551996588221', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(692, 1, 'Mauricio Sonaglio', NULL, '+5554999989214', '5554999989214', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(693, 1, 'Maria Eduarda Tomasi', NULL, '+5554981375097', '5554981375097', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(694, 1, 'Alexandra Fitarelli', NULL, '+555496766679', '555496766679', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(695, 1, 'Julia Monteblanco Cavalini', NULL, '+555492107071', '555492107071', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(696, 1, 'Tiago Casagrande Otaram', NULL, '+5554981534229', '5554981534229', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(697, 1, 'Isabely da Silva Raphaelli', NULL, '+5551993984604', '5551993984604', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(698, 1, 'Mariana Martini Civardi', NULL, '+5554984082426', '5554984082426', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(699, 1, 'Suelen Rocha Souza', NULL, '+555491269837', '555491269837', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(700, 1, 'Tamara Nunes Girardello', NULL, '+5554999100954', '5554999100954', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(701, 1, 'Maria Eduarda dos Santos Seixas', NULL, '+5591981607358', '5591981607358', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(702, 1, 'Stephanie Regina de Carli Farias', NULL, '+5554996866101', '5554996866101', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(703, 1, 'Maely Ananda Ferreira de Santana', NULL, '+5591983313088', '5591983313088', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(704, 1, 'Cristiane Valmorbida Dallepiane', NULL, '+555491958188', '555491958188', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(705, 1, 'Ester Mirian da Silva', NULL, '+5554981562892', '5554981562892', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(706, 1, 'Julia Zaffari', NULL, '+555499537916', '555499537916', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(707, 1, 'Amdré Guzzo Lazzarotto', NULL, '+5554992013658', '5554992013658', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(708, 1, 'Willian Lando Czeikoski', NULL, '+555484083597', '555484083597', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(709, 1, 'Cristina Chies da Silva', NULL, '+5554996971346', '5554996971346', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(710, 1, 'Lizandro Felipe Maslowski', NULL, '+5555996591697', '5555996591697', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(711, 1, 'Jennifer Alves do Amaral de Matos', NULL, '+5554996368475', '5554996368475', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(712, 1, 'Isadora Rigotti Tristacci', NULL, '+5554996201927', '5554996201927', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(713, 1, 'Gabriela Gasque Cantarelli Pereira', NULL, '+5554981231795', '5554981231795', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(714, 1, 'Relmer Souza Barbosa', NULL, '+5575982940121', '5575982940121', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(715, 1, 'Suene Gomes Silva', NULL, '+555197650203', '555197650203', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(716, 1, 'Ana Caroline Pereira Ferreira', NULL, '+5553999300597', '5553999300597', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(717, 1, 'Elto Rodrigues', NULL, '+5554981337777', '5554981337777', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(718, 1, 'Manuela de Camargo Schäfer', NULL, '+5551980299829', '5551980299829', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(719, 1, 'Gabrieli Kovaleski', NULL, '+5554991392153', '5554991392153', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(720, 1, 'Ana Luiza Angeli da Rosa', NULL, '+5554996083185', '5554996083185', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(721, 1, 'Maria Eduarda dos Santos Costa', NULL, '+5554996635139', '5554996635139', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(722, 1, 'Angélica Milena Cislaghi', NULL, '+5551998775513', '5551998775513', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(723, 1, 'Raíssa Carteri Bonamigo', NULL, '+5554999139415', '5554999139415', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(724, 1, 'Marceli Dallabrida', NULL, '+555499896645', '555499896645', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(725, 1, 'Emanuelle de Souza Castilho', NULL, '+5554992800110', '5554992800110', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(726, 1, 'Jenifer de Oliveira Caron', NULL, '+5554996108797', '5554996108797', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(727, 1, 'Vanessa Fátima Antoniolli', NULL, '+555499422327', '555499422327', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(728, 1, 'Guilherme Jardim Comassetto', NULL, '+555399616471', '555399616471', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(729, 1, 'Giovana Guisso Possa', NULL, '+5554984085458', '5554984085458', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(730, 1, 'Érik Kauã Patias', NULL, '+5554994068778', '5554994068778', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(731, 1, 'Talita Tatiane Ortolan', NULL, '+5554984007862', '5554984007862', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(732, 1, 'Joana de Deus Goulart', NULL, '+5554996765377', '5554996765377', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(733, 1, 'Iasmin Ramos Carvalho', NULL, '+5554996797730', '5554996797730', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(734, 1, 'Taliza Smaltti', NULL, '+5554981667508', '5554981667508', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(735, 1, 'Hellen Stefany Ferreira da Silva Pellizzari', NULL, '+5554992734202', '5554992734202', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(736, 1, 'Maria Eduarda da Silva Soares', NULL, '+5554984061498', '5554984061498', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(737, 1, 'Joel guerville', NULL, '+5554981267720', '5554981267720', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(738, 1, 'Mateus de Souza Rocha', NULL, '+5551997742142', '5551997742142', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(739, 1, 'Andréia Dettoni', NULL, '+555497135587', '555497135587', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(740, 1, 'Gabriel de Jesus Hansen', NULL, '+5554999883277', '5554999883277', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(741, 1, 'Adriana Sousa Lisboa', NULL, '+5551999574083', '5551999574083', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(742, 1, 'Julia Rostirolla Machado', NULL, '+5554991059400', '5554991059400', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(743, 1, 'Cristian Athirson Peniche de Oliveira', NULL, '+5554993004545', '5554993004545', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(744, 1, 'Julio César Do Amaral', NULL, '+5554996705459', '5554996705459', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(745, 1, 'Vinícius Zanatta Santos', NULL, '+5554981292806', '5554981292806', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(746, 1, 'Arlei Morais', NULL, '+5554981499932', '5554981499932', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(747, 1, 'Gabriele Luana Geleinski', NULL, '+5554996164344', '5554996164344', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(748, 1, 'Kelvin Livano Guindani', NULL, '+5554999093994', '5554999093994', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(749, 1, 'Luís Felipe Casagrande', NULL, '+5554994138888', '5554994138888', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(750, 1, 'Lorenzo Giovanni Froner', NULL, '+5554996661329', '5554996661329', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(751, 1, 'Dieler Batista', NULL, '+555197709935', '555197709935', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(752, 1, 'Fabiane Carbonera Maran', NULL, '+5554999323261', '5554999323261', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(753, 1, 'CARLOS GREGORIO LOPEZ GONZALEZ', NULL, '+5551996519141', '5551996519141', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(754, 1, 'Camila Lotis', NULL, '+555499529948', '555499529948', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(755, 1, 'Mariana Fernandes Campana', NULL, '+5554984058565', '5554984058565', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(756, 1, 'Júlia de Azevedo Muniz', NULL, '+5554996005069', '5554996005069', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(757, 1, 'Glória de Vargas dos Santos', NULL, '+5554999921410', '5554999921410', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(758, 1, 'Ariane dos Santos Dorneles', NULL, '+5554991224727', '5554991224727', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(759, 1, 'RICARDO REICHEMBACH DOS SANTOS', NULL, '+5551999109490', '5551999109490', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(760, 1, 'Maria Eduarda dos Santos', NULL, '+5554991226726', '5554991226726', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(761, 1, 'Beatriz Todeschini Lovatto', NULL, '+5554996116093', '5554996116093', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(762, 1, 'Tamar Taina Santiago de Souza', NULL, '+5554996724182', '5554996724182', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(763, 1, 'Rodrigo Cunha Amorim', NULL, '+555195890547', '555195890547', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(764, 1, 'Tiago Luiz Menegat Todeschini', NULL, '+5554999868141', '5554999868141', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(765, 1, 'Ana Cláudia da Silva Pereira', NULL, '+5551996470405', '5551996470405', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(766, 1, 'Eduarda Zago Antunes', NULL, '+5554996744381', '5554996744381', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(767, 1, 'Sarah da Silva Machado', NULL, '+5554992810779', '5554992810779', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(768, 1, 'Lisiane Tonet Fernandes Falcão', NULL, '+5555999201426', '5555999201426', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22'),
(769, 1, 'Fernando Bissolotti', NULL, '+5554996691770', '5554996691770', NULL, NULL, 0, NULL, '2026-01-13 03:59:22', '2026-01-13 03:59:22');

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
-- Estrutura para tabela `jobs`
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

--
-- Despejando dados para a tabela `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(771, 'campaigns', '{\"uuid\":\"18f70de8-16f5-4653-8ef9-f7eba5099cd1\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:23;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-01-12 01:59:39.366210\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768183179, 1768183178),
(772, 'campaigns', '{\"uuid\":\"8d1eda48-c724-4cf7-82bd-b2a888f21633\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:26;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768254286, 1768254286),
(773, 'campaigns', '{\"uuid\":\"04b52ae5-1640-4845-a18a-abb700d05b9f\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:26;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768254335, 1768254335),
(774, 'campaigns', '{\"uuid\":\"2c3047cf-8ee6-4d88-8b8c-fb91d9735d62\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:27;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768254489, 1768254489),
(775, 'campaigns', '{\"uuid\":\"aeffbf5d-f83b-493e-9950-d685c21d19f6\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:28;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768255140, 1768255140),
(776, 'campaigns', '{\"uuid\":\"afa30df5-48a3-4b84-b145-d30837e62961\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:28;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768255444, 1768255444),
(777, 'campaigns', '{\"uuid\":\"f961cfa0-7383-40c5-a0ee-455c7ba2728f\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:28;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768255622, 1768255622),
(778, 'campaigns', '{\"uuid\":\"025c51a4-501a-4b6a-8ba7-3a59157f42bb\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:28;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768255626, 1768255626),
(779, 'campaigns', '{\"uuid\":\"defd307b-02d6-429e-abdc-4c8dc09a0164\",\"displayName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\",\"command\":\"O:35:\\\"App\\\\Jobs\\\\ProcessCampaignDispatchJob\\\":14:{s:10:\\\"campaignId\\\";i:28;s:6:\\\"userId\\\";i:2;s:7:\\\"timeout\\\";i:120;s:5:\\\"tries\\\";i:1;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";s:9:\\\"campaigns\\\";s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1768255663, 1768255663);

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
(13, 2, NULL, '3EB0B44802523779AA52C0', 'outbound', 'text', 'kkkkkkkkkkkkk', 'sent', '2025-12-17 06:35:57', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555599595193@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0B44802523779AA52C0\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"kkkkkkkkkkkkk\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769759,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942559,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/pipe\",\"payload\":{\"number\":\"5555999595193\",\"text\":\"kkkkkkkkkkkkk\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555599595193@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB0B44802523779AA52C0\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"kkkkkkkkkkkkk\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765769759,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1765942559,\"instanceId\":\"2ad82f51-39d9-4617-a76e-dd23f790273a\",\"source\":\"web\"}}]}', '2025-12-17 06:35:57', '2025-12-17 06:35:57'),
(14, 2, NULL, NULL, 'outbound', 'text', '98798789789789789789789789', 'failed', '2025-12-18 04:44:31', '{\"ok\":false,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/teste90\",\"http_status\":400,\"message\":\"TypeError: Cannot read properties of undefined (reading \'id\')\",\"body\":{\"status\":400,\"error\":\"Bad Request\",\"response\":{\"message\":[\"TypeError: Cannot read properties of undefined (reading \'id\')\"]}},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/teste90\",\"payload\":{\"number\":\"5555999595193\",\"text\":\"98798789789789789789789789\"},\"http_status\":400,\"body\":{\"status\":400,\"error\":\"Bad Request\",\"response\":{\"message\":[\"TypeError: Cannot read properties of undefined (reading \'id\')\"]}}}]}', '2025-12-18 04:44:31', '2025-12-18 04:44:31'),
(15, 2, NULL, '3EB001B95869FDC8B08AF2', 'outbound', 'text', 'ssssssss', 'sent', '2025-12-18 06:35:13', '{\"ok\":true,\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/Comportamento\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555599595193@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB001B95869FDC8B08AF2\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"ssssssss\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765856117,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1766028917,\"instanceId\":\"5b6dc144-44e8-4d7c-93b4-252539e03114\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evolutionapi.agendeizap.com.br\\/message\\/sendText\\/Comportamento\",\"payload\":{\"number\":\"5555999595193\",\"text\":\"ssssssss\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555599595193@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB001B95869FDC8B08AF2\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"ssssssss\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1765856117,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1766028917,\"instanceId\":\"5b6dc144-44e8-4d7c-93b4-252539e03114\",\"source\":\"web\"}}]}', '2025-12-18 06:35:13', '2025-12-18 06:35:13'),
(16, 1, NULL, '3EB091F28D07B1F0DB0F07', 'outbound', 'text', 'oi', 'sent', '2026-01-12 00:36:31', '{\"ok\":true,\"url\":\"https:\\/\\/evo-evolution-api.e3aqwk.easypanel.host\\/message\\/sendText\\/xicara\",\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB091F28D07B1F0DB0F07\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"oi\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1767994591,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1768167391,\"instanceId\":\"5bd450e0-97fc-438d-a0b8-b4a0f132b750\",\"source\":\"web\"},\"tries\":[{\"url\":\"https:\\/\\/evo-evolution-api.e3aqwk.easypanel.host\\/message\\/sendText\\/xicara\",\"payload\":{\"number\":\"5554984359885\",\"text\":\"oi\"},\"http_status\":201,\"body\":{\"key\":{\"remoteJid\":\"555484359885@s.whatsapp.net\",\"fromMe\":true,\"id\":\"3EB091F28D07B1F0DB0F07\"},\"pushName\":\"Voc\\u00ea\",\"status\":\"PENDING\",\"message\":{\"conversation\":\"oi\"},\"contextInfo\":{\"mentionedJid\":[],\"groupMentions\":[],\"ephemeralSettingTimestamp\":{\"low\":1767994591,\"high\":0,\"unsigned\":false},\"disappearingMode\":{\"initiator\":0}},\"messageType\":\"conversation\",\"messageTimestamp\":1768167391,\"instanceId\":\"5bd450e0-97fc-438d-a0b8-b4a0f132b750\",\"source\":\"web\"}}]}', '2026-01-12 00:36:31', '2026-01-12 00:36:31');

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
(21, '2025_12_16_000002_create_messages_table', 1001),
(22, '2026_01_11_221555_create_jobs_table', 1002),
(23, '2026_01_11_000001_add_media_fields_to_campaign_messages_table', 1003);

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
(2, 1, 'teste02', 'teste02', 'teste2@teste.com', NULL, '$2a$12$VhDGgKRXWO2TxbeKGOaH3e7KPY46LrbnkBwE8c5kVDzPTzBxpVERi', 'wGhRxFCZGrbE6ou20bL2BEXu3Was0U480oRr8eDcYs2VHnJCu4vJNHUCimtl', 1, 1, 220, 'ehrmoGaBK9Q5y460xFEPIj4Hba4htxJhPgVRkHhPhZnE3qCAGXwgKDy6Xirm', '2025-12-15 19:56:55', '2025-12-15 19:56:55'),
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
(17, 2, 'xicara', 'xicara', 'YukRzT6r6NUCssutVLvHq6ZB63slLwnnbcoHScHFmaUBaztjSRWkqkOuquDxunOa', 1, 1, 200, 0, NULL, '2026-01-10 18:16:40', '2026-01-10 18:16:40'),
(18, 2, 'Meu54', 'meu54', 'l20dBTd1fsQn60BEg5CRSMLM4iyTJjmB0qSGretwA4CVu8Bf7XzJFFEwLrcuy6CQ', 1, 1, 200, 0, NULL, '2026-01-13 00:38:35', '2026-01-13 00:38:35');

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
-- Índices de tabela `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de tabela `campaign_attachments`
--
ALTER TABLE `campaign_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `campaign_messages`
--
ALTER TABLE `campaign_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de tabela `campaign_recipients`
--
ALTER TABLE `campaign_recipients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=777;

--
-- AUTO_INCREMENT de tabela `chats`
--
ALTER TABLE `chats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=770;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1756;

--
-- AUTO_INCREMENT de tabela `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
