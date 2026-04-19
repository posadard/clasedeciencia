-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 19, 2026 at 07:55 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u626603208_clasedeciencia`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` PROCEDURE `sp_buscar_respuesta_cache_clase` (IN `p_clase_id` INT, IN `p_pregunta` VARCHAR(500))   BEGIN
  DECLARE v_pregunta_norm VARCHAR(500);
  SET v_pregunta_norm = LOWER(TRIM(p_pregunta));
  SELECT id, respuesta, veces_usada
  FROM ia_respuestas_cache
  WHERE clase_id = p_clase_id
    AND pregunta_normalizada = v_pregunta_norm
    AND activa = 1
  LIMIT 1;
  -- Aumentar contador si se encontró
  -- Nota: MariaDB FOUND_ROWS requiere SQL_CALC_FOUND_ROWS; usamos una actualización defensiva
  UPDATE ia_respuestas_cache
  SET veces_usada = veces_usada + 1,
      ultima_vez_usada = NOW()
  WHERE clase_id = p_clase_id
    AND pregunta_normalizada = v_pregunta_norm
    AND activa = 1
  LIMIT 1;
END$$

CREATE DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` PROCEDURE `sp_limpiar_sesiones_antiguas` ()   BEGIN
  UPDATE ia_sesiones
  SET estado = 'timeout'
  WHERE estado = 'activa'
    AND fecha_ultima_interaccion < DATE_SUB(NOW(), INTERVAL 1 HOUR);
END$$

CREATE DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` PROCEDURE `sp_obtener_contexto_clase` (IN `p_clase_id` INT)   BEGIN
  SELECT * FROM v_clase_contexto_ia WHERE clase_id = p_clase_id;
  SELECT * FROM v_clase_kits_detalle WHERE clase_id = p_clase_id;
  SELECT url, tipo, titulo
  FROM recursos_multimedia
  WHERE clase_id = p_clase_id
  ORDER BY sort_order;
END$$

CREATE DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` PROCEDURE `sp_registrar_interaccion_ia_clase` (IN `p_sesion_id` INT, IN `p_clase_id` INT, IN `p_pregunta` TEXT, IN `p_respuesta` TEXT, IN `p_tokens` INT, IN `p_tiempo_ms` INT, IN `p_modelo` VARCHAR(100), IN `p_costo` DECIMAL(10,6), IN `p_guardrail_activado` BOOLEAN)   BEGIN
  INSERT INTO ia_mensajes (sesion_id, rol, contenido, tokens, metadata)
  VALUES (p_sesion_id, 'user', p_pregunta, 0, JSON_OBJECT('timestamp', NOW()));
  INSERT INTO ia_mensajes (sesion_id, rol, contenido, tokens, metadata)
  VALUES (p_sesion_id, 'assistant', p_respuesta, p_tokens, JSON_OBJECT('modelo', p_modelo));

  UPDATE ia_sesiones
  SET total_mensajes = total_mensajes + 2,
      tokens_usados = tokens_usados + p_tokens,
      fecha_ultima_interaccion = NOW()
  WHERE id = p_sesion_id;

  INSERT INTO ia_logs (sesion_id, clase_id, tipo_evento, tokens_usados, tiempo_respuesta_ms, modelo_usado, costo_estimado)
  VALUES (p_sesion_id, p_clase_id, 'respuesta', p_tokens, p_tiempo_ms, p_modelo, p_costo);

  IF p_guardrail_activado THEN
    INSERT INTO ia_guardrails_log (sesion_id, clase_id, pregunta_usuario, palabra_detectada, tipo_alerta)
    VALUES (p_sesion_id, p_clase_id, p_pregunta, 'detectada', 'peligro');
  END IF;

  INSERT INTO ia_stats_clase (clase_id, total_consultas, total_sesiones, tokens_totales, ultima_consulta)
  VALUES (p_clase_id, 1, 1, p_tokens, NOW())
  ON DUPLICATE KEY UPDATE
    total_consultas = total_consultas + 1,
    tokens_totales = tokens_totales + p_tokens,
    ultima_consulta = NOW();
END$$

--
-- Functions
--
CREATE DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` FUNCTION `fn_es_pregunta_peligrosa` (`pregunta` TEXT) RETURNS TINYINT(1) DETERMINISTIC BEGIN
  DECLARE palabras_json JSON;
  DECLARE palabra VARCHAR(255);
  DECLARE i INT DEFAULT 0;
  DECLARE total INT;

  SELECT valor INTO palabras_json FROM configuracion_ia WHERE clave = 'palabras_peligro';
  SET total = JSON_LENGTH(palabras_json);

  WHILE i < total DO
    SET palabra = JSON_UNQUOTE(JSON_EXTRACT(palabras_json, CONCAT('$[', i, ']')));
    IF LOWER(pregunta) LIKE CONCAT('%', LOWER(palabra), '%') THEN
      RETURN TRUE;
    END IF;
    SET i = i + 1;
  END WHILE;
  RETURN FALSE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `analytics_eventos`
--

CREATE TABLE `analytics_eventos` (
  `id` bigint(20) NOT NULL,
  `session_hash` varchar(64) DEFAULT NULL,
  `sesion_ia_id` int(11) DEFAULT NULL,
  `instancia` enum('frontend','backend') NOT NULL DEFAULT 'frontend',
  `evento` varchar(64) NOT NULL COMMENT 'page_view, search_query, search_result_click, ia_question, ia_answer, ia_error, cta_click, admin_entity_update, etc.',
  `tipo_pagina` varchar(80) DEFAULT NULL,
  `modulo` varchar(80) DEFAULT NULL,
  `entidad_tipo` varchar(40) DEFAULT NULL,
  `entidad_id` int(11) DEFAULT NULL,
  `clase_id` int(11) DEFAULT NULL,
  `kit_id` int(11) DEFAULT NULL,
  `componente_id` int(11) DEFAULT NULL,
  `manual_id` int(11) DEFAULT NULL,
  `termino_busqueda` varchar(255) DEFAULT NULL,
  `resultado_posicion` int(11) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `departamento` varchar(120) DEFAULT NULL,
  `dispositivo` varchar(64) DEFAULT NULL,
  `ip_anon` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `duracion_ms` int(11) DEFAULT NULL,
  `valor_numerico` decimal(18,6) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `analytics_eventos`
--

INSERT INTO `analytics_eventos` (`id`, `session_hash`, `sesion_ia_id`, `instancia`, `evento`, `tipo_pagina`, `modulo`, `entidad_tipo`, `entidad_id`, `clase_id`, `kit_id`, `componente_id`, `manual_id`, `termino_busqueda`, `resultado_posicion`, `referrer`, `departamento`, `dispositivo`, `ip_anon`, `user_agent`, `duracion_ms`, `valor_numerico`, `metadata`, `created_at`) VALUES
(1, NULL, NULL, 'backend', 'page_view', 'admin', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/clases/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/clases/index.php\"}', '2026-03-25 00:48:38'),
(2, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/index.php\"}', '2026-03-25 00:48:40'),
(3, NULL, 3, 'backend', 'ia_question', 'admin', 'kits', '', NULL, NULL, NULL, NULL, NULL, 'cual kit tiene el mayor numero de clases', NULL, 'https://clasedeciencia.com/admin/kits/index.php', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 00:48:55'),
(4, NULL, 3, 'backend', 'ia_answer', 'admin', 'kits', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 287, 1095.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 00:48:55'),
(5, NULL, 3, 'backend', 'ia_question', 'admin', 'kits', '', NULL, NULL, NULL, NULL, NULL, 'cual clase es l;a mas completa en relacion a manuales, kits componentes', NULL, 'https://clasedeciencia.com/admin/kits/index.php', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 00:49:16'),
(6, NULL, 3, 'backend', 'ia_answer', 'admin', 'kits', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 613, 1191.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 00:49:16'),
(7, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/index.php\"}', '2026-03-25 00:56:41'),
(8, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 00:56:57'),
(9, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 00:57:04'),
(10, NULL, 3, 'backend', 'ia_question', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, 'hola cual es la clase mas completa lo que implica numero de kits, componentes, manuales', NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 00:57:32'),
(11, NULL, 3, 'backend', 'ia_answer', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1158, 3004.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 00:57:33'),
(12, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:03:10'),
(13, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:03:24'),
(14, NULL, 3, 'backend', 'ia_question', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, 'que clase tiene mas elementos es decir kit, manual, componentes, la que almenos tenga uno', NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:03:46'),
(15, NULL, 3, 'backend', 'ia_answer', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1093, 3482.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 01:03:47'),
(16, NULL, 3, 'backend', 'ia_question', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, 'que hay de el emisor de ondas', NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:04:10'),
(17, NULL, 3, 'backend', 'ia_answer', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 434, 2254.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 01:04:11'),
(18, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/manuals/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/manuals/index.php\"}', '2026-03-25 01:04:17'),
(19, NULL, 3, 'backend', 'ia_question', 'admin', 'kits', '', NULL, NULL, NULL, NULL, NULL, 'hay un manual de un coponente a que pertenece?', NULL, 'https://clasedeciencia.com/admin/kits/manuals/index.php', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:04:32'),
(20, NULL, 3, 'backend', 'ia_answer', 'admin', 'kits', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 368, 2581.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 01:04:33'),
(21, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:06:48'),
(22, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:12:43'),
(23, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:12:45'),
(24, NULL, 3, 'backend', 'ia_question', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, 'que manuales tenemos?', NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:12:55'),
(25, NULL, 3, 'backend', 'ia_answer', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 844, 2880.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 01:12:56'),
(26, NULL, 3, 'backend', 'ia_question', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, 'que manual es dime el nombre', NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:13:11'),
(27, NULL, 3, 'backend', 'ia_answer', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 492, 2882.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 01:13:11'),
(28, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:14:41'),
(29, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:14:42'),
(30, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:14:43'),
(31, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:14:45'),
(32, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:14:46'),
(33, NULL, 3, 'backend', 'ia_question', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, 'que manuales tenemos?', NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:14:55'),
(34, NULL, 3, 'backend', 'ia_answer', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"deterministic-backend:manuales_estado\"}', '2026-03-25 01:14:55'),
(35, NULL, 3, 'backend', 'ia_question', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, 'Profundiza en tu última respuesta y dame más detalles.', NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:15:00'),
(36, NULL, 3, 'backend', 'ia_answer', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 575, 2661.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 01:15:01'),
(37, NULL, 3, 'backend', 'ia_question', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, 'cual es el manual?', NULL, 'https://clasedeciencia.com/admin/ia/index.php?tab=estado', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:15:09'),
(38, NULL, 3, 'backend', 'ia_answer', 'admin', 'ia', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 381, 2619.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 01:15:09'),
(39, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/manuals/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/manuals/index.php\"}', '2026-03-25 01:15:16'),
(40, NULL, 3, 'backend', 'ia_question', 'admin', 'kits', '', NULL, NULL, NULL, NULL, NULL, 'dime que manual tenemos?', NULL, 'https://clasedeciencia.com/admin/kits/manuals/index.php', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 01:15:27'),
(41, NULL, 3, 'backend', 'ia_answer', 'admin', 'kits', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 412, 3009.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"openai/gpt-oss-20b\"}', '2026-03-25 01:15:27'),
(42, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 01:16:05'),
(43, 'deb5d8db30982bc67f2d9fc0cb38b71a', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.79.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.153 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-25 04:26:17'),
(44, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-25 19:15:09'),
(45, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-25 19:15:20'),
(46, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clase-emision-de-ondas-am', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/clase-emision-de-ondas-am\",\"title\":\"Clase de Ciencia - Ciencias Ambientales: Emisión de ondas AM - Clase de Ciencia\"}', '2026-03-25 19:15:22'),
(47, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/kit-radio-de-cristal', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/kit-radio-de-cristal\",\"title\":\"Kit de Ciencia - Ciencias Ambientales: Radio de cristal - Clase de Ciencia\"}', '2026-03-25 19:15:24'),
(48, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-diode-germanio', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/componente-diode-germanio\",\"title\":\"Diode germanio - Clase de Ciencia\"}', '2026-03-25 19:15:28'),
(49, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/manual-armado-componente-diode-germanio-09-03-26-v1-0', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/manual-armado-componente-diode-germanio-09-03-26-v1-0\",\"title\":\"Manual de Armado Diode germanio versión 1.0 - Clase de Ciencia\"}', '2026-03-25 19:15:30'),
(50, '9bf772e005fd63f4f40ce2f67bd921e8', 4, 'frontend', 'ia_question', 'manual', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'what is this for', NULL, 'https://clasedeciencia.com/manual-armado-componente-diode-germanio-09-03-26-v1-0', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 19:15:38'),
(51, '9bf772e005fd63f4f40ce2f67bd921e8', 4, 'frontend', 'ia_answer', 'manual', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1052, 700.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"llama-3.3-70b-versatile\"}', '2026-03-25 19:15:39'),
(52, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-25 19:15:46'),
(53, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'search_query', 'buscar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'crista', NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 2.000000, '{\"clases\":0,\"kits\":1,\"componentes\":1}', '2026-03-25 19:15:59'),
(54, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'search_query', 'buscar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'cristal', NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 2.000000, '{\"clases\":0,\"kits\":1,\"componentes\":1}', '2026-03-25 19:16:00'),
(55, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-25 19:16:02'),
(56, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clase-emision-de-ondas-am', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/clase-emision-de-ondas-am\",\"title\":\"Clase de Ciencia - Ciencias Ambientales: Emisión de ondas AM - Clase de Ciencia\"}', '2026-03-25 19:16:05'),
(57, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-diode-germanio', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/componente-diode-germanio\",\"title\":\"Diode germanio - Clase de Ciencia\"}', '2026-03-25 19:16:19'),
(58, NULL, NULL, 'backend', 'page_view', 'admin', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/dashboard.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/dashboard.php\"}', '2026-03-25 19:16:52'),
(59, NULL, NULL, 'backend', 'page_view', 'admin', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/clases/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/clases/index.php\"}', '2026-03-25 19:17:00'),
(60, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/index.php\"}', '2026-03-25 19:17:01'),
(61, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/manuals/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/manuals/index.php\"}', '2026-03-25 19:17:02'),
(62, NULL, NULL, 'backend', 'page_view', 'admin', 'contratos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/contratos/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/contratos/index.php\"}', '2026-03-25 19:17:02'),
(63, NULL, NULL, 'backend', 'page_view', 'admin', 'entregas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/entregas/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/entregas/index.php\"}', '2026-03-25 19:17:05'),
(64, NULL, NULL, 'backend', 'page_view', 'admin', 'lotes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/lotes/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/lotes/index.php\"}', '2026-03-25 19:17:06'),
(65, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 19:17:07'),
(66, NULL, NULL, 'backend', 'page_view', 'admin', 'lotes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/lotes/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/lotes/index.php\"}', '2026-03-25 19:17:24'),
(67, NULL, NULL, 'backend', 'page_view', 'admin', 'entregas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/entregas/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/entregas/index.php\"}', '2026-03-25 19:17:25'),
(68, NULL, NULL, 'backend', 'page_view', 'admin', 'contratos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/contratos/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/contratos/index.php\"}', '2026-03-25 19:17:26'),
(69, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/manuals/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/manuals/index.php\"}', '2026-03-25 19:17:26'),
(70, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/index.php\"}', '2026-03-25 19:17:27'),
(71, NULL, NULL, 'backend', 'page_view', 'admin', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/clases/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/clases/index.php\"}', '2026-03-25 19:17:28'),
(72, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-25 19:17:30'),
(73, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/cimentacion', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/cimentacion\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-25 19:18:12'),
(74, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'search_query', 'buscar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rq', NULL, 'https://clasedeciencia.com/cimentacion', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 0.000000, '{\"clases\":0,\"kits\":0,\"componentes\":0}', '2026-03-25 19:18:30'),
(75, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'search_query', 'buscar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'emison', NULL, 'https://clasedeciencia.com/cimentacion', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 0.000000, '{\"clases\":0,\"kits\":0,\"componentes\":0}', '2026-03-25 19:18:33'),
(76, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'search_query', 'buscar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'em', NULL, 'https://clasedeciencia.com/cimentacion', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 7.000000, '{\"clases\":6,\"kits\":0,\"componentes\":1}', '2026-03-25 19:18:35'),
(77, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'search_result_click', 'buscar', NULL, 'clase', 6, NULL, NULL, NULL, NULL, 'em', 1, 'https://clasedeciencia.com/cimentacion', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, NULL, '2026-03-25 19:18:40'),
(78, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clase-emision-de-ondas-am', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/clase-emision-de-ondas-am\",\"title\":\"Clase de Ciencia - Ciencias Ambientales: Emisión de ondas AM - Clase de Ciencia\"}', '2026-03-25 19:18:40'),
(79, NULL, NULL, 'backend', 'page_view', 'admin', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/dashboard.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/dashboard.php\"}', '2026-03-25 19:19:14'),
(80, NULL, NULL, 'backend', 'page_view', 'admin', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/clases/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/clases/index.php\"}', '2026-03-25 19:19:16'),
(81, NULL, NULL, 'backend', 'page_view', 'admin', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/clases/edit.php?id=6', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/clases/edit.php\"}', '2026-03-25 19:19:18'),
(82, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-03-25 19:20:08'),
(83, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-25 19:20:30'),
(84, '9bf772e005fd63f4f40ce2f67bd921e8', 4, 'frontend', 'ia_question', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'i need a kit for waves emision electromagnetism', NULL, 'https://clasedeciencia.com/', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 19:20:43'),
(85, '9bf772e005fd63f4f40ce2f67bd921e8', 4, 'frontend', 'ia_answer', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 397, 449.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"llama-3.3-70b-versatile\"}', '2026-03-25 19:20:44'),
(86, '9bf772e005fd63f4f40ce2f67bd921e8', 4, 'frontend', 'ia_question', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Electromagnetismo', NULL, 'https://clasedeciencia.com/', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 19:20:53'),
(87, '9bf772e005fd63f4f40ce2f67bd921e8', 4, 'frontend', 'ia_answer', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 450, 522.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"llama-3.3-70b-versatile\"}', '2026-03-25 19:20:53'),
(88, '9bf772e005fd63f4f40ce2f67bd921e8', 4, 'frontend', 'ia_question', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Profundiza en tu última respuesta y dame más detalles.', NULL, 'https://clasedeciencia.com/', NULL, 'desktop', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-03-25 19:20:57'),
(89, '9bf772e005fd63f4f40ce2f67bd921e8', 4, 'frontend', 'ia_answer', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 767, 724.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"llama-3.3-70b-versatile\"}', '2026-03-25 19:20:58'),
(90, NULL, NULL, 'backend', 'page_view', 'admin', 'kits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/kits/index.php', NULL, 'desktop', '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/kits/index.php\"}', '2026-03-25 19:21:35'),
(91, 'a92122f99f5f17962a4ab99bdf8acc76', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-alambre-esmaltado-28awg', NULL, 'desktop', '17.241.75.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-alambre-esmaltado-28awg\",\"title\":\"Alambre esmaltado 28AWG - Clase de Ciencia\"}', '2026-03-25 20:57:42'),
(92, '6914e9ce257e8e9b61edc57ba87facc7', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-bombillo-led-3v', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-bombillo-led-3v\",\"title\":\"Bombillo LED 3V - Clase de Ciencia\"}', '2026-03-25 21:13:10'),
(93, '908f837fa39957c87ec929e8eead3af1', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-iman-neodimio', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-iman-neodimio\",\"title\":\"Imán neodimio - Clase de Ciencia\"}', '2026-03-26 03:31:42'),
(94, 'dd50b716bc92ad7b022c03eb6d06a5be', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=optica', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-26 05:55:57'),
(95, 'ecb70a8ce28be0c20fcdabb68fc5e31b', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-pila-aa', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-pila-aa\",\"title\":\"Pila AA - Clase de Ciencia\"}', '2026-03-26 07:44:49'),
(96, 'bab71d2c63f8f918e5729b9361a9f4a4', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=induccion', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-26 14:27:55'),
(97, '78cd45d38476259fe7f90d80e71d170c', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?ciclo=1', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-26 22:52:16'),
(98, '438846641b451889ac3432e0ce944f22', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '2a02:9130:2ef::0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-26 23:10:48'),
(99, '215077e6e01f425772dab0d2aaa4854d', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '79.117.255.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.7444.138 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-26 23:10:51'),
(100, '490efd87bdc791b579502ac030420cab', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?ciclo=2', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-26 23:21:45'),
(101, '03d95ee9b0dd28361beaf0ee0872087f', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=experimento', NULL, 'desktop', '17.241.75.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-26 23:59:12'),
(102, '71ce99e77a5a4b4d55328d519b245eed', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=anatomia', NULL, 'mobile', '66.249.79.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.153 Mobile Safari/537.36 (compatible; GoogleOther)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-27 00:46:45'),
(103, '21d2a10c37904c621878cb14318c4f33', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?ciclo=1', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-27 06:07:05'),
(104, '8a9ba459db6c557626a370a6df3e0b40', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=aumento', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-27 15:22:23'),
(105, '8cd6fd951eaf037b73805a756cb05f75', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=ondas', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-27 20:44:20'),
(106, 'b2d2ab7794ca798c6203425fa8e4741e', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/kits?page=2', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/kits\",\"title\":\"Kits - Clase de Ciencia\"}', '2026-03-27 23:08:13'),
(107, '8311dec381a87fca22db2b33530d7487', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?area=biologia', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-27 23:21:34'),
(108, 'c22f3398113783facffe4d7b51133581', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=generador', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-27 23:37:51'),
(109, 'a5f836e6701a053602205e115493a91f', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/biologia', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/biologia\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-28 02:35:13'),
(110, '1065b33752d1d16f9142845c4b792cfe', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '202.8.42.0', 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-28 10:02:34'),
(111, '18f11509b2af872e9f92babea9166fd5', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=led', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-28 12:09:55'),
(112, '47135e9e74d1fee23a6dea78c88a9ce2', NULL, 'frontend', 'page_view', 'otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/proyecto.php?slug=radio-de-cristal', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/proyecto.php\",\"title\":\"404 - Page Not Found - Clase de Ciencia\"}', '2026-03-28 14:31:35'),
(113, 'da13e5e31d01fe08224d4d9933c18cab', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=generador', NULL, 'mobile', '66.249.79.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.153 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-28 22:40:55'),
(114, '21b885b6803d7115dfc8607a19437a37', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=celula', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-30 05:40:55'),
(115, 'dbd9b5a6a658ba8cee2f10b487004d95', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=acidos', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-30 14:50:46'),
(116, 'c65de7cb74186ca7428fe0e33d69e4ae', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-motor-dc-3-6v', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-motor-dc-3-6v\",\"title\":\"Motor DC 3-6V - Clase de Ciencia\"}', '2026-03-30 21:24:52'),
(117, '92fb743c6a550f2c231c3a1c47d34174', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componentes?category=quimica', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componentes\",\"title\":\"Componentes - Clase de Ciencia\"}', '2026-03-31 05:04:34'),
(118, 'f6ba3717917be386cfdd7ca025ff0bb2', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '49.12.126.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-31 08:00:20'),
(119, 'bde282c86c3e210a6b35eac27de78698', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '49.12.126.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-31 08:00:30'),
(120, '1df10f32a7422d80af98821293ef6091', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '49.12.126.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-31 08:00:37'),
(121, '9f389c723a686f0d66ebdb02518a2d9b', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '49.12.126.0', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-31 08:01:30');
INSERT INTO `analytics_eventos` (`id`, `session_hash`, `sesion_ia_id`, `instancia`, `evento`, `tipo_pagina`, `modulo`, `entidad_tipo`, `entidad_id`, `clase_id`, `kit_id`, `componente_id`, `manual_id`, `termino_busqueda`, `resultado_posicion`, `referrer`, `departamento`, `dispositivo`, `ip_anon`, `user_agent`, `duracion_ms`, `valor_numerico`, `metadata`, `created_at`) VALUES
(122, '01703b0392b14e7432c66fc812a02b09', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '49.12.126.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-31 08:01:37'),
(123, '24a83b7b296689a5648dc57c79fcac93', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/kit-separacion-de-mezclas', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/kit-separacion-de-mezclas\",\"title\":\"Kit educativo: Separación de mezclas - Clase de Ciencia\"}', '2026-03-31 08:55:52'),
(124, '1e5018a05e3ffed2d68846d51271f7df', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '65.55.210.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/100.0.4896.127 Safari/537.36', NULL, NULL, '{\"path\":\"/carro-trampa-de-raton\",\"title\":\"Carro trampa de ratón - Clase de Ciencia - Clase de Ciencia\"}', '2026-03-31 09:21:01'),
(125, '0504532cfddca1ed86a0c374abc0c8a5', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=viento', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-31 11:47:06'),
(126, 'a2eb36fe61b944910b1e454238bde9f3', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/kits?page=1', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/kits\",\"title\":\"Kits - Clase de Ciencia\"}', '2026-03-31 14:33:17'),
(127, 'd3001315f31713c247b20cc64859898b', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=electromagnetismo', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-31 15:56:05'),
(128, '5b47983b9a8cc53f7c502bee91b35ede', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.79.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.164 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-03-31 19:54:24'),
(129, '87b74dd87b6cdb50b505b31d6fbcc500', NULL, 'frontend', 'page_view', 'otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/proyecto.php?slug=separacion-de-mezclas', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/proyecto.php\",\"title\":\"404 - Page Not Found - Clase de Ciencia\"}', '2026-03-31 20:07:10'),
(130, '1cc3e7bc0fde94fa1ad81809f338ad59', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=vegetales', NULL, 'mobile', '66.249.79.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.164 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-03-31 22:22:22'),
(131, '734143b9f03c60e8cd82288d83b2526b', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '40.77.167.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-01 05:09:32'),
(132, '11c7f1469a104cdf23d9857d9eb68102', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/kits?area=ambiental', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/kits\",\"title\":\"Kits - Clase de Ciencia\"}', '2026-04-01 08:41:06'),
(133, '61aee2d3115e5b41c10ce700b99ace64', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '40.77.167.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', NULL, NULL, '{\"path\":\"/kit-tratamiento-de-agua\",\"title\":\"Kit educativo: Kit: Tratamiento de agua - Clase de Ciencia\"}', '2026-04-02 14:00:35'),
(134, '3fb0f8a8d70ae5396d7665d3e16ac989', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=osmosis', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-03 02:09:04'),
(135, 'b842fafec5bd987420a215b1f0e70211', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=volumen', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-03 07:19:08'),
(136, 'f6aff420b3a86aaaa877099af0bdb6d7', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/kits?page=1', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/kits\",\"title\":\"Kits - Clase de Ciencia\"}', '2026-04-03 08:30:27'),
(137, '8e394010f4937e5945e133004a71ba39', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=bateria', NULL, 'desktop', '17.246.19.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-03 08:46:32'),
(138, '20b71bdc478cc1918aac97a56b91fc21', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-motor-dc-3-6v', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-motor-dc-3-6v\",\"title\":\"Motor DC 3-6V - Clase de Ciencia\"}', '2026-04-03 11:38:06'),
(139, '54deada0c720cbe01033eed6c460d0f1', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-sal-de-mesa', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-sal-de-mesa\",\"title\":\"Sal de mesa - Clase de Ciencia\"}', '2026-04-03 23:39:52'),
(140, 'f87e8bc6e7d4bfb9cc182fe7fd07eee7', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-embudo-plastico', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-embudo-plastico\",\"title\":\"Embudo plástico - Clase de Ciencia\"}', '2026-04-04 04:24:13'),
(141, '60327823c152360c81747129e8be51de', NULL, 'frontend', 'page_view', 'otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/proyecto.php?slug=test-de-ph', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/proyecto.php\",\"title\":\"404 - Page Not Found - Clase de Ciencia\"}', '2026-04-04 04:40:48'),
(142, 'dce887c74139325c258efbd8e3804f78', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-vaso-precipitado-plastico', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-vaso-precipitado-plastico\",\"title\":\"Vaso precipitado plástico - Clase de Ciencia\"}', '2026-04-04 06:57:36'),
(143, '23e5f2b3147de2ebd12bafe73dc9c9e1', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=presion', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-04 08:55:50'),
(144, 'dfd9ee776a1f37174495647b604075db', NULL, 'frontend', 'page_view', 'otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/proyecto.php?slug=circuito-electrico-basico', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/proyecto.php\",\"title\":\"404 - Page Not Found - Clase de Ciencia\"}', '2026-04-04 09:09:43'),
(145, '41fa135bae02d578586cfbbd73bba31f', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-tiras-de-ph', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-tiras-de-ph\",\"title\":\"Tiras de pH - Clase de Ciencia\"}', '2026-04-04 09:34:30'),
(146, '80c8af86023b8a50a63edb453016b8a5', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=respiracion', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-04 10:27:34'),
(147, 'c8beed283587f752b4a73ed923bb34bf', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '40.77.167.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', NULL, NULL, '{\"path\":\"/microscopio-sencillo\",\"title\":\"Microscopio sencillo - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-04 11:07:44'),
(148, 'd33b47ef2a003a09400fc35697214089', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-papel-filtro', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-papel-filtro\",\"title\":\"Papel filtro - Clase de Ciencia\"}', '2026-04-04 11:15:57'),
(149, '2bcef2b801e0d699f7db24336d0b60f1', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '2607:fb91:1ce9::0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-04 17:58:48'),
(150, '54279147b23545f494659de89bf62719', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-04 22:57:09'),
(151, '0ae6d0c4de3e21f2e6f1e783130bf70e', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.164 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-04 22:57:18'),
(152, '4b7eabf32ca0b273ea1822e5f8a28473', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'desktop', '66.249.66.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/146.0.7680.164 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-04 22:57:20'),
(153, 'ff1fb4359192198cf42c3cd45e837bc7', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-05 08:10:33'),
(154, '8ee53def8bdee30042ea40b333aa6463', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=electricidad', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-05 23:19:39'),
(155, '0e55fe599544924e6c68d3c45b06aefd', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clase-emision-de-ondas-am', NULL, 'desktop', '40.77.179.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/clase-emision-de-ondas-am\",\"title\":\"Clase de Ciencia - Ciencias Ambientales: Emisión de ondas AM - Clase de Ciencia\"}', '2026-04-06 19:39:35'),
(156, '253215fa8d1dd0402d365780e7a780bc', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componentes?category=quimica', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componentes\",\"title\":\"Componentes - Clase de Ciencia\"}', '2026-04-06 21:14:13'),
(157, '00c1dd17eafce3acacd6a6e7e3aa2602', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '157.55.39.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', NULL, NULL, '{\"path\":\"/kit-separacion-de-mezclas\",\"title\":\"Kit educativo: Separación de mezclas - Clase de Ciencia\"}', '2026-04-06 23:14:38'),
(158, 'a455cdcd64014906bd055a461925c311', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/kit-separacion-de-mezclas', NULL, 'desktop', '65.55.210.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/kit-separacion-de-mezclas\",\"title\":\"Kit educativo: Separación de mezclas - Clase de Ciencia\"}', '2026-04-06 23:30:43'),
(159, '401b2bbdda4c8e2ab4f741f778322873', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '157.55.39.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', NULL, NULL, '{\"path\":\"/clases/ciclos/5\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-07 00:47:36'),
(160, '8ef0f6fa33d0444dff8221a145af2be8', NULL, 'frontend', 'page_view', 'otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/proyecto.php?slug=pulmon-mecanico', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/proyecto.php\",\"title\":\"404 - Page Not Found - Clase de Ciencia\"}', '2026-04-07 07:12:09'),
(161, '10a09449e5943add441d02c724e99993', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'desktop', '66.249.66.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/146.0.7680.164 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-07 08:25:52'),
(162, '1d2ba71fa837fe066ef6fd657b62d2dc', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/generador-manual-dinamo', NULL, 'desktop', '40.77.178.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/generador-manual-dinamo\",\"title\":\"Generador manual (dinamo) - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-07 10:56:35'),
(163, '60570503412be3243871810da4c6f635', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/kit-radio-de-cristal', NULL, 'desktop', '40.77.179.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/kit-radio-de-cristal\",\"title\":\"Kit de Ciencia - Ciencias Ambientales: Radio de cristal - Clase de Ciencia\"}', '2026-04-07 11:09:38'),
(164, 'ebd5513af08f770965fdf49e1e03a9ca', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componentes?page=2', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componentes\",\"title\":\"Componentes - Clase de Ciencia\"}', '2026-04-07 21:30:23'),
(165, '530ca7d2d467dc00122029a8bbf28ac5', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/kit-tratamiento-de-agua', NULL, 'desktop', '38.255.109.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', NULL, NULL, '{\"path\":\"/kit-tratamiento-de-agua\",\"title\":\"Kit educativo: Kit: Tratamiento de agua - Clase de Ciencia\"}', '2026-04-08 02:01:13'),
(166, 'e276904c41a078814336ce4b47563abf', NULL, 'frontend', 'page_view', 'otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/privacy.php', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/privacy.php\",\"title\":\"Política de Privacidad - Clase de Ciencia\"}', '2026-04-08 03:21:10'),
(167, '6682438f48a4b69ddde8b8d7a8b7695b', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/ingenieria', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/ingenieria\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-08 18:22:23'),
(168, '9868692243efb845f188334be6a22556', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=laboratorio', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-08 20:05:45'),
(169, '9d20f909a838f0d3b67f8cf7c9771f12', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-tiras-de-ph', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-tiras-de-ph\",\"title\":\"Tiras de pH - Clase de Ciencia\"}', '2026-04-08 22:21:39'),
(170, 'a03ef92cb2ac7da225d063b36d4b8964', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=tratamiento', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-09 01:57:50'),
(171, '6291cc544d5e840a0646716c2dcffa9b', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-sal-de-mesa', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-sal-de-mesa\",\"title\":\"Sal de mesa - Clase de Ciencia\"}', '2026-04-09 02:21:59'),
(172, '2f87cd572dac9588ede33d76d10a4ef2', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=renovable', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-09 05:39:34'),
(173, '016366fcdfbd9f91dae692a6d3c1f5ed', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '52.167.144.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', NULL, NULL, '{\"path\":\"/carro-trampa-de-raton\",\"title\":\"Carro trampa de ratón - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-09 05:54:49'),
(174, '4e7df9bef391b9bbeb9b5845e981371a', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-embudo-plastico', NULL, 'desktop', '17.246.19.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-embudo-plastico\",\"title\":\"Embudo plástico - Clase de Ciencia\"}', '2026-04-09 05:57:34'),
(175, 'b41776e5826fe9a2026d0d5255194a5e', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/carro-trampa-de-raton', NULL, 'desktop', '40.77.179.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/carro-trampa-de-raton\",\"title\":\"Carro trampa de ratón - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-09 06:10:39'),
(176, '4e8b08fda3c52181f2a886137d56b4e7', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-vaso-precipitado-plastico', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-vaso-precipitado-plastico\",\"title\":\"Vaso precipitado plástico - Clase de Ciencia\"}', '2026-04-09 07:35:22'),
(177, 'f06fb312705289279cdb6da2cc48ac49', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-trampa-de-raton', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-trampa-de-raton\",\"title\":\"Trampa de ratón - Clase de Ciencia\"}', '2026-04-09 08:33:19'),
(178, 'a0cb7ea7c9aa08a71f23a3ee6e7df35d', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=analisis', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-09 09:08:37'),
(179, '8658ab8896da28f3d10d6366a4a38b08', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=movimiento', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-09 09:27:54'),
(180, '59cba5b332105bcdf001a9809c3c05da', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '40.77.177.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-09 13:22:39'),
(181, '69c24286c8583fe9cd1a71b2cb8d2414', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-clavo-de-hierro', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-clavo-de-hierro\",\"title\":\"Clavo de hierro - Clase de Ciencia\"}', '2026-04-09 18:17:45'),
(182, '77b58c4b0e99fe494093f797a84e84d2', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=antena', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-09 21:22:49'),
(183, '4dd5a39d6a168dbb7d2339a57a2a0544', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=lentes', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-09 21:27:10'),
(184, '6287188e6f632ba3e85a74782457199c', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '181.197.55.0', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Mobile/15E148 Safari/604.1', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-09 23:03:00'),
(185, '2bcef2b801e0d699f7db24336d0b60f1', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '2607:fb90:9363::0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-09 23:25:32'),
(186, '2bcef2b801e0d699f7db24336d0b60f1', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/cimentacion', NULL, 'mobile', '2607:fb90:9363::0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/cimentacion\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-09 23:25:36'),
(187, '2bcef2b801e0d699f7db24336d0b60f1', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/circuito-electrico-basico', NULL, 'mobile', '2607:fb90:9363::0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/circuito-electrico-basico\",\"title\":\"Circuito eléctrico básico - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-09 23:25:40'),
(188, '369301fc65eb1f74d7e563ad2d41739a', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/turbina-eolica-de-mesa', NULL, 'desktop', '40.77.179.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/turbina-eolica-de-mesa\",\"title\":\"Turbina eólica de mesa - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-09 23:30:05'),
(189, 'f473cf17883a4fd86d7a7b06b02fba73', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=modelo', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-09 23:48:12'),
(190, '2bcef2b801e0d699f7db24336d0b60f1', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/cimentacion', NULL, 'mobile', '2600:1001:a110::0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/cimentacion\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-10 00:20:38'),
(191, '4ae2fe2c6ee03ab3b3640183fc7fd875', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=celula', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-10 01:23:42'),
(192, '94d4c79e356248fe709efbce889fe9dc', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '40.77.177.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/100.0.4896.127 Safari/537.36', NULL, NULL, '{\"path\":\"/kit-tratamiento-de-agua\",\"title\":\"Kit educativo: Kit: Tratamiento de agua - Clase de Ciencia\"}', '2026-04-10 02:21:43'),
(193, '521e401271cd3386c0007c42ec35da0d', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-porta-baterias-aa', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-porta-baterias-aa\",\"title\":\"Porta baterías AA - Clase de Ciencia\"}', '2026-04-10 16:58:03'),
(194, 'fc002c2e89fbc583be578d1f55c59d01', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'desktop', '65.55.210.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/100.0.4896.127 Safari/537.36', NULL, NULL, '{\"path\":\"/pulmon-mecanico\",\"title\":\"Pulmón mecánico - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-10 23:48:38'),
(195, 'c8eac8c55f738470e82d46a6491f967f', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/kit-microscopio-sencillo', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/kit-microscopio-sencillo\",\"title\":\"Kit educativo: Microscopio sencillo - Clase de Ciencia\"}', '2026-04-11 00:36:43'),
(196, '254abb52c6c5ede60f5a14bd56676728', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=metodos-fisicos', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 02:42:11'),
(197, '0ab0dbf168a1e97336e3573c95dac84a', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=sustancias', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 03:29:11'),
(198, '14cf8c546d6e1603e369cbdbe2c62767', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-trampa-de-raton', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-trampa-de-raton\",\"title\":\"Trampa de ratón - Clase de Ciencia\"}', '2026-04-11 06:46:48'),
(199, '815dc439fa8c38d58687c1667ce540e0', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=magnetismo', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 07:44:56'),
(200, '60406283b10bb20f99fb36111d192f9c', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=electroiman', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 09:07:11'),
(201, 'f68e8184185862f87d31f4c71ecc7da1', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=filtracion', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 10:02:19'),
(202, '0b69bd2b6539b6c80ccd419412de3e72', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-11 10:05:29'),
(203, '24139d6e3ad25541a96e37dca975540f', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=mezclas', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 12:54:50'),
(204, '445922795ccca6495258e196676e9146', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=movimiento', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 13:39:46'),
(205, 'c7ee5061646ec77ef40549391010a2ab', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=fuerzas', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 14:34:44'),
(206, '2bcef2b801e0d699f7db24336d0b60f1', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-11 15:34:55'),
(207, '2bcef2b801e0d699f7db24336d0b60f1', 2, 'frontend', 'ia_question', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Hola que eres', NULL, 'https://clasedeciencia.com/', NULL, 'mobile', NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-04-11 15:35:05'),
(208, '2bcef2b801e0d699f7db24336d0b60f1', 2, 'frontend', 'ia_answer', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 441, 437.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"llama-3.3-70b-versatile\"}', '2026-04-11 15:35:06'),
(209, '2bcef2b801e0d699f7db24336d0b60f1', 2, 'frontend', 'ia_question', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Universidad', NULL, 'https://clasedeciencia.com/', NULL, 'mobile', NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-04-11 15:35:17'),
(210, '2bcef2b801e0d699f7db24336d0b60f1', 2, 'frontend', 'ia_answer', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 577, 503.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"llama-3.3-70b-versatile\"}', '2026-04-11 15:35:17'),
(211, '2bcef2b801e0d699f7db24336d0b60f1', 2, 'frontend', 'ia_question', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Física', NULL, 'https://clasedeciencia.com/', NULL, 'mobile', NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"source\":\"api/ia-consulta.php\"}', '2026-04-11 15:35:27'),
(212, '2bcef2b801e0d699f7db24336d0b60f1', 2, 'frontend', 'ia_answer', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 306, 542.000000, '{\"guardrail_activado\":false,\"cached\":false,\"modelo_usado\":\"llama-3.3-70b-versatile\"}', '2026-04-11 15:35:27'),
(213, '2bcef2b801e0d699f7db24336d0b60f1', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-11 15:38:37'),
(214, 'fb5eb8228c159327324764689ce77972', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/carro-solar', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/carro-solar\",\"title\":\"Carro solar - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-11 18:46:33'),
(215, '2948c47b80a38f9541df05d82e889fbc', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-11 20:03:31'),
(216, '0b7326a2e34e3fbde6a98c4244d45219', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '66.249.66.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/146.0.7680.164 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-11 20:04:15'),
(217, '80206ca62739cb6ca4a98205849007fe', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.164 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-11 20:04:21'),
(218, '579178a64e8c45068f5ffec6abb318b7', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '66.249.66.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/146.0.7680.164 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-11 20:04:22'),
(219, 'a5034f4a42f0ebf3c6c78ebd336a1f7e', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=laboratorio', NULL, 'desktop', '17.246.19.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 21:07:25'),
(220, '1523043176d7f0f8d34095fe9fcb32f1', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=volumen', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-11 22:48:04'),
(221, 'd430f4d0089d1582507220df347bfa13', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=observacion', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-12 01:41:10'),
(222, '0ddc8c40242036a42a854ebbc6233696', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=decantacion', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-12 03:45:57'),
(223, 'ddd6c5b2002a7679004a75b6ab8a9b58', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-banda-elastica', NULL, 'desktop', '17.246.19.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-banda-elastica\",\"title\":\"Banda elástica - Clase de Ciencia\"}', '2026-04-12 20:48:17');
INSERT INTO `analytics_eventos` (`id`, `session_hash`, `sesion_ia_id`, `instancia`, `evento`, `tipo_pagina`, `modulo`, `entidad_tipo`, `entidad_id`, `clase_id`, `kit_id`, `componente_id`, `manual_id`, `termino_busqueda`, `resultado_posicion`, `referrer`, `departamento`, `dispositivo`, `ip_anon`, `user_agent`, `duracion_ms`, `valor_numerico`, `metadata`, `created_at`) VALUES
(224, 'bebd561eddd9323f584050de5b5d9508', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componentes?category=magneticos', NULL, 'desktop', '17.241.75.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componentes\",\"title\":\"Componentes - Clase de Ciencia\"}', '2026-04-12 21:33:51'),
(225, 'bf2cb874a025addde54ad6b9ffcb82a0', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componentes?page=3', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componentes\",\"title\":\"Componentes - Clase de Ciencia\"}', '2026-04-13 00:11:32'),
(226, 'aa290913dd14790b53dd9f3569a48869', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componentes?category=tecnologia', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componentes\",\"title\":\"Componentes - Clase de Ciencia\"}', '2026-04-13 02:31:56'),
(227, 'df498a994fae21b2e052c1aca7649301', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-bomba-de-aire-manual', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-bomba-de-aire-manual\",\"title\":\"Bomba de aire manual - Clase de Ciencia\"}', '2026-04-13 04:37:24'),
(228, '3acb4d22739bc41c2e83727a34c403be', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/kit-radio-de-cristal', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/kit-radio-de-cristal\",\"title\":\"Kit de Ciencia - Ciencias Ambientales: Radio de cristal - Clase de Ciencia\"}', '2026-04-13 09:02:57'),
(229, 'c166cc71921ab93239cd4c4cbe13847c', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componentes?page=1', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componentes\",\"title\":\"Componentes - Clase de Ciencia\"}', '2026-04-13 09:02:59'),
(230, '8b567e0579b30a79f373db2e7b078195', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-13 12:12:07'),
(231, '6a32a1927bcb041c4b0a0be8ed871c21', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/turbina-eolica-de-mesa', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/turbina-eolica-de-mesa\",\"title\":\"Turbina eólica de mesa - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-13 17:34:16'),
(232, '4119345db9025e8752f5ee4b99597e26', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=acidos', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-13 17:57:55'),
(233, '4f312d35702e0d77ee46ea06df09a11f', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componentes?category=tecnologia', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componentes\",\"title\":\"Componentes - Clase de Ciencia\"}', '2026-04-13 19:36:40'),
(234, 'fc729b722bb6646c6e024e8640f33539', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=reacciones', NULL, 'desktop', '17.241.75.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-14 23:29:55'),
(235, '7b50edb318a9d44ea260aad507474106', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=filtracion', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-15 01:34:54'),
(236, '97b3faca31468625ca681480e896b8b9', NULL, 'frontend', 'page_view', 'otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/proyecto.php?slug=test-de-ph', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/proyecto.php\",\"title\":\"404 - Page Not Found - Clase de Ciencia\"}', '2026-04-15 02:13:26'),
(237, '16aaa1eda7bfd7b81cf54cb48a0f5492', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=membrana', NULL, 'desktop', '17.241.75.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-15 04:25:53'),
(238, 'b06910b0caeaf6ee48e7dfbfc45122ba', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=sustancias', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-15 05:07:44'),
(239, '40f819f16e2d13815705781868962944', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/ingenieria', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/ingenieria\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-15 05:12:35'),
(240, '52543d1726f06940e639cf9a455e12f8', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=mecanica', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-15 08:07:01'),
(241, 'f91b001dae7dc9b58fe794ad08940af3', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=friccion', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-15 09:37:02'),
(242, 'dc076fca708ef4b21ccf64eda0b0229d', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=circuito', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-15 12:03:26'),
(243, '3eb5a6ad2de7c02c439a9a754281148a', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases?busqueda=generador', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-15 13:03:26'),
(244, 'e496ae9b820faceca3651b3efc802b2a', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-panel-solar-5v', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-panel-solar-5v\",\"title\":\"Panel solar 5V - Clase de Ciencia\"}', '2026-04-15 19:15:42'),
(245, 'a7d37bade36377f63951c5feae42a077', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-manivela-plastica', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-manivela-plastica\",\"title\":\"Manivela plástica - Clase de Ciencia\"}', '2026-04-15 21:20:47'),
(246, '5ccdf913bbf126b6b7bab072025257cc', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-carton-rigido', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-carton-rigido\",\"title\":\"Cartón rígido - Clase de Ciencia\"}', '2026-04-15 22:57:37'),
(247, '866293a5fd1ecbf463770b2c8b523bbd', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-rueda-plastica-50mm', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-rueda-plastica-50mm\",\"title\":\"Rueda plástica 50mm - Clase de Ciencia\"}', '2026-04-15 23:19:28'),
(248, '6b703e0e15e22fc4d90c8c1c613f4c5d', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-eje-metalico', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-eje-metalico\",\"title\":\"Eje metálico - Clase de Ciencia\"}', '2026-04-15 23:45:58'),
(249, '5405eeb5333bf8360860811b511c67b1', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-helice-plastica', NULL, 'desktop', '17.241.227.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-helice-plastica\",\"title\":\"Hélice plástica - Clase de Ciencia\"}', '2026-04-16 00:40:26'),
(250, 'b0782199f441edf27f4e0ebb8af6a202', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/kit-tratamiento-de-agua', NULL, 'desktop', '45.6.140.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', NULL, NULL, '{\"path\":\"/kit-tratamiento-de-agua\",\"title\":\"Kit educativo: Kit: Tratamiento de agua - Clase de Ciencia\"}', '2026-04-16 00:54:37'),
(251, '14a2804a47f7740dd35498de0888b3f3', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=sistema-respiratorio', NULL, 'desktop', '17.246.15.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-16 04:32:37'),
(252, 'a259996f8c7d219809cd9d8739d07c8d', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-botella-plastica-500ml', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-botella-plastica-500ml\",\"title\":\"Botella plástica 500ml - Clase de Ciencia\"}', '2026-04-16 09:44:02'),
(253, '19e74313a9d00e87ab2bd1867660c535', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/generador-manual-dinamo', NULL, 'desktop', '40.77.167.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.193', NULL, NULL, '{\"path\":\"/generador-manual-dinamo\",\"title\":\"Generador manual (dinamo) - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-16 12:31:39'),
(254, '90d3aee25caa4ec501361c2fc6eeeba9', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/quimica', NULL, 'desktop', '17.22.237.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/quimica\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-16 17:56:45'),
(255, '7ac5115966e7cb21294bf8a88a14096e', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/carro-trampa-de-raton', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/carro-trampa-de-raton\",\"title\":\"Carro trampa de ratón - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-16 22:02:48'),
(256, 'd2f19a8570813400d091898e594e0618', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/clases?busqueda=vegetales', NULL, 'desktop', '17.246.23.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-17 05:48:19'),
(257, '2cf4f7465025771f76c0695ac02b888a', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/carro-trampa-de-raton', NULL, 'desktop', '40.77.178.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/carro-trampa-de-raton\",\"title\":\"Carro trampa de ratón - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-17 07:20:13'),
(258, '7c2d081fc45192e9dc59781550416a17', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-17 23:03:04'),
(259, 'd8dfd8cfd1617c817696e4b138c97a10', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '205.169.39.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.5938.132 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-18 01:47:52'),
(260, 'aa3ae44cb28ddae81644d7e5fdd65097', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-18 07:02:30'),
(261, '46cd759c0b260d24562d5790ab2a8719', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'desktop', '66.249.66.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/146.0.7680.177 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-18 07:02:40'),
(262, 'b6d755dedd0cb97070c2a616adacb174', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-18 07:02:41'),
(263, 'b7dbfd94935df22d0a9bace8e20cf90a', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/electroiman', NULL, 'desktop', '17.22.245.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/electroiman\",\"title\":\"Electroimán - Clase de Ciencia - Clase de Ciencia\"}', '2026-04-18 16:54:53'),
(264, 'bcdf23cf8872554e6664600424f58e27', NULL, 'frontend', 'page_view', 'clases', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/clases', NULL, 'desktop', '40.77.167.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/136.0.0.0 Safari/537.36 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.193', NULL, NULL, '{\"path\":\"/clases\",\"title\":\"Clases - Clase de Ciencia\"}', '2026-04-18 19:09:13'),
(265, '22a4b6637534f9c68db9e93e52673f1b', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/componente-botella-plastica-500ml', NULL, 'desktop', '17.241.219.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-botella-plastica-500ml\",\"title\":\"Botella plástica 500ml - Clase de Ciencia\"}', '2026-04-18 23:38:57'),
(266, 'fd77558c2405d61d6008ab5cc91c9134', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'desktop', '66.249.66.0', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/146.0.7680.177 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-19 02:16:50'),
(267, '7389a07ebb2fd2597386f010b2500ccc', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/', NULL, 'mobile', '66.249.66.0', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.177 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-19 02:16:51'),
(268, 'c52805f7bcafec3f96826a672484f542', NULL, 'frontend', 'page_view', 'clase', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.clasedeciencia.com/componente-carbon-activado', NULL, 'desktop', '17.22.253.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', NULL, NULL, '{\"path\":\"/componente-carbon-activado\",\"title\":\"Carbón activado - Clase de Ciencia\"}', '2026-04-19 04:12:44'),
(269, 'd5c21494d10484856db76c133c6e1e0e', NULL, 'frontend', 'page_view', 'inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/\",\"title\":\"Inicio - Clase de Ciencia\"}', '2026-04-19 19:53:39'),
(270, NULL, NULL, 'backend', 'page_view', 'admin', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/dashboard.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/dashboard.php\"}', '2026-04-19 19:53:58'),
(271, NULL, NULL, 'backend', 'page_view', 'admin', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/footer/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/footer/index.php\"}', '2026-04-19 19:54:01'),
(272, NULL, NULL, 'backend', 'page_view', 'admin', 'paginas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/paginas/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/paginas/index.php\"}', '2026-04-19 19:54:01'),
(273, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-04-19 19:54:02'),
(274, NULL, NULL, 'backend', 'page_view', 'admin', 'lotes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/lotes/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/lotes/index.php\"}', '2026-04-19 19:54:03'),
(275, NULL, NULL, 'backend', 'page_view', 'admin', 'entregas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/entregas/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/entregas/index.php\"}', '2026-04-19 19:54:05'),
(276, NULL, NULL, 'backend', 'page_view', 'admin', 'contratos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/contratos/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/contratos/index.php\"}', '2026-04-19 19:54:06'),
(277, NULL, NULL, 'backend', 'page_view', 'admin', 'componentes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/componentes/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/componentes/index.php\"}', '2026-04-19 19:54:07'),
(278, NULL, NULL, 'backend', 'page_view', 'admin', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/dashboard.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/dashboard.php\"}', '2026-04-19 19:54:09'),
(279, NULL, NULL, 'backend', 'page_view', 'admin', 'contratos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/contratos/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/contratos/index.php\"}', '2026-04-19 19:54:20'),
(280, NULL, NULL, 'backend', 'page_view', 'admin', 'componentes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/componentes/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/componentes/index.php\"}', '2026-04-19 19:54:27'),
(281, NULL, NULL, 'backend', 'page_view', 'admin', 'contratos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/contratos/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/contratos/index.php\"}', '2026-04-19 19:54:28'),
(282, NULL, NULL, 'backend', 'page_view', 'admin', 'entregas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/entregas/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/entregas/index.php\"}', '2026-04-19 19:54:29'),
(283, NULL, NULL, 'backend', 'page_view', 'admin', 'lotes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/lotes/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/lotes/index.php\"}', '2026-04-19 19:54:30'),
(284, NULL, NULL, 'backend', 'page_view', 'admin', 'ia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/ia/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/ia/index.php\"}', '2026-04-19 19:54:31'),
(285, NULL, NULL, 'backend', 'page_view', 'admin', 'paginas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://clasedeciencia.com/admin/paginas/index.php', NULL, 'desktop', '2600:1001:a110::0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, '{\"path\":\"/admin/paginas/index.php\"}', '2026-04-19 19:54:36');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_visitas`
--

CREATE TABLE `analytics_visitas` (
  `id` bigint(20) NOT NULL,
  `clase_id` int(11) DEFAULT NULL,
  `tipo_pagina` varchar(64) NOT NULL,
  `departamento` varchar(120) DEFAULT NULL,
  `dispositivo` varchar(64) DEFAULT NULL,
  `visited_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `explicacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `nombre`, `slug`, `explicacion`) VALUES
(1, 'Física', 'fisica', 'Estudia las propiedades de la materia, la energía y sus interacciones. Incluye mecánica, electricidad, magnetismo, óptica, termodinámica y ondas. Fundamental para proyectos de electricidad, magnetismo, fuerzas y movimiento.'),
(2, 'Química', 'quimica', 'Analiza la composición, estructura y propiedades de las sustancias, así como sus transformaciones. Abarca reacciones químicas, enlaces, ácidos-bases, y procesos de cambio de estado. Esencial para experimentos con materiales, cristales, baterías y reacciones.'),
(3, 'Biología', 'biologia', 'Investiga los seres vivos, su estructura, funciones, crecimiento, evolución y relaciones con el medio. Incluye botánica, zoología, microbiología y genética. Clave para proyectos de plantas, células, ADN y ecosistemas.'),
(4, 'Tecnología e Informática', 'tecnologia', 'Área que estudia el diseño, desarrollo y aplicación de herramientas, sistemas y procesos tecnológicos para resolver problemas. Incluye electrónica, programación, robótica, diseño de circuitos y automatización. Central para proyectos con Arduino, sensores y sistemas interactivos.'),
(5, 'Ciencias Ambientales', 'ambiental', 'Estudia las interacciones entre los sistemas físicos, químicos y biológicos del ambiente, y su relación con los sistemas sociales y culturales. Aborda sostenibilidad, conservación, cambio climático y desarrollo sostenible.'),
(6, 'Matemáticas', 'matematicas', 'Disciplina que estudia las propiedades de los números, las formas geométricas, las operaciones y las relaciones abstractas. Incluye álgebra, geometría, estadística y cálculo. Fundamental para análisis de datos, mediciones y modelos matemáticos en proyectos científicos.'),
(7, 'Ingeniería y Diseño', 'ingenieria', 'Aplica principios científicos y matemáticos para diseñar, construir y optimizar estructuras, máquinas y sistemas. Incluye mecánica, electrónica, diseño de prototipos y fabricación. Relevante para proyectos de construcción, máquinas simples y dispositivos.'),
(8, 'Ciencias Sociales', 'sociales', 'Estudia las sociedades humanas, sus estructuras, procesos históricos y relaciones culturales. Incluye historia, geografía, economía y democracia. Importante para contextualizar el impacto social de proyectos científicos y CTeI.'),
(9, 'Educación Artística', 'artistica', 'Desarrolla capacidades expresivas y creativas a través del arte visual, musical y escénico. Relevante para diseño de prototipos, presentaciones creativas y comunicación visual de proyectos científicos.'),
(10, 'Lenguaje y Comunicación', 'lenguaje', 'Desarrolla competencias en lectura, escritura, expresión oral y comprensión de textos. Incluye comunicación científica, redacción de informes, presentaciones y documentación de proyectos. Esencial para comunicar resultados científicos.');

-- --------------------------------------------------------

--
-- Table structure for table `atributos_contenidos`
--

CREATE TABLE `atributos_contenidos` (
  `id` bigint(20) NOT NULL,
  `tipo_entidad` enum('clase','manual','multimedia','kit','componente') NOT NULL,
  `entidad_id` int(11) NOT NULL,
  `atributo_id` int(11) NOT NULL,
  `valor_string` text DEFAULT NULL,
  `valor_numero` decimal(18,6) DEFAULT NULL,
  `valor_entero` int(11) DEFAULT NULL,
  `valor_booleano` tinyint(1) DEFAULT NULL,
  `valor_fecha` date DEFAULT NULL,
  `valor_datetime` datetime DEFAULT NULL,
  `valor_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `unidad_codigo` varchar(12) DEFAULT NULL COMMENT 'unitCode UNECE (ej: KGM, CMT, VLT, LTR)',
  `lang` varchar(10) DEFAULT NULL COMMENT 'ej: es-CO',
  `orden` int(11) NOT NULL DEFAULT 0,
  `fuente` varchar(32) DEFAULT NULL COMMENT 'manual|import|api',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `atributos_contenidos`
--

INSERT INTO `atributos_contenidos` (`id`, `tipo_entidad`, `entidad_id`, `atributo_id`, `valor_string`, `valor_numero`, `valor_entero`, `valor_booleano`, `valor_fecha`, `valor_datetime`, `valor_json`, `unidad_codigo`, `lang`, `orden`, `fuente`, `created_at`, `updated_at`) VALUES
(1, 'kit', 4, 1, 'madera', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-21 19:42:19', '2025-12-21 19:42:19'),
(2, 'componente', 12, 2, 'Blanco', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-21 19:45:31', '2025-12-21 19:45:31'),
(3, 'kit', 4, 2, 'rojo, azul, verde', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-21 19:47:50', '2025-12-21 19:47:50'),
(5, 'clase', 1, 22, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-21 20:58:13', '2025-12-21 20:58:13'),
(27, 'componente', 4, 1, 'blanco', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-23 02:58:40', '2025-12-23 02:58:40'),
(28, 'componente', 4, 2, 'total', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-23 02:58:47', '2025-12-23 02:58:47'),
(40, 'componente', 1, 1, 'rojo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-23 03:19:07', '2025-12-23 03:19:07'),
(42, 'clase', 6, 24, 'peedro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-23 03:19:35', '2025-12-23 03:19:35'),
(43, 'clase', 6, 22, 'toda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-23 03:20:11', '2025-12-23 03:20:11'),
(73, 'kit', 6, 2, 'rojo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-23 04:20:05', '2025-12-23 04:20:05'),
(75, 'componente', 16, 1, 'plastico', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'es-CO', 1, 'manual', '2025-12-25 01:20:44', '2025-12-25 01:20:44');

-- --------------------------------------------------------

--
-- Table structure for table `atributos_definiciones`
--

CREATE TABLE `atributos_definiciones` (
  `id` int(11) NOT NULL,
  `clave` varchar(120) NOT NULL COMMENT 'Identificador técnico estable (ej: peso, tension_v)',
  `etiqueta` varchar(160) NOT NULL COMMENT 'Nombre visible (ej: Peso, Tensión)',
  `descripcion` text DEFAULT NULL,
  `tipo_dato` enum('string','integer','number','boolean','date','datetime','json') NOT NULL DEFAULT 'string',
  `cardinalidad` enum('one','many') NOT NULL DEFAULT 'one',
  `grupo` varchar(64) DEFAULT NULL COMMENT 'ficha|seguridad|empaque|electrico|multimedia|otros',
  `estado` enum('activo','borrador') NOT NULL DEFAULT 'activo',
  `schema_propiedad` varchar(160) DEFAULT NULL COMMENT 'Prop. de schema.org o ruta (ej: Product.weight, additionalProperty.voltage)',
  `unidad_defecto` varchar(12) DEFAULT NULL COMMENT 'unitCode UNECE/UN/CEFACT (ej: KGM, CMT, VLT)',
  `opciones_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `unidades_permitidas_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `aplica_a_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Ej: kit|componente',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `atributos_definiciones`
--

INSERT INTO `atributos_definiciones` (`id`, `clave`, `etiqueta`, `descripcion`, `tipo_dato`, `cardinalidad`, `grupo`, `estado`, `schema_propiedad`, `unidad_defecto`, `opciones_json`, `unidades_permitidas_json`, `aplica_a_json`, `created_at`, `updated_at`) VALUES
(1, 'material', 'Material', 'Material(es) principal(es) del producto', 'string', 'many', 'ficha', 'activo', 'Product.material', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(2, 'color', 'Color', 'Color principal', 'string', 'one', 'ficha', 'activo', 'Product.color', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(3, 'peso', 'Peso', 'Peso neto del producto', 'number', 'one', 'ficha', 'activo', 'Product.weight', 'KGM', NULL, '[\"KGM\",\"GRM\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(4, 'alto', 'Alto', 'Altura del producto', 'number', 'one', 'ficha', 'activo', 'Product.height', 'CMT', NULL, '[\"CMT\",\"MMT\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(5, 'ancho', 'Ancho', 'Ancho del producto', 'number', 'one', 'ficha', 'activo', 'Product.width', 'CMT', NULL, '[\"CMT\",\"MMT\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(6, 'largo', 'Largo', 'Largo/profundidad del producto', 'number', 'one', 'ficha', 'activo', 'Product.depth', 'CMT', NULL, '[\"CMT\",\"MMT\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(7, 'volumen', 'Volumen', 'Volumen del contenido', 'number', 'one', 'ficha', 'activo', 'Product.volume', 'LTR', NULL, '[\"LTR\",\"MLT\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(8, 'pais_fabricacion', 'País de fabricación', 'País de origen del producto', 'string', 'one', 'ficha', 'activo', 'Product.countryOfOrigin', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(9, 'garantia_meses', 'Garantía (meses)', 'Duración de la garantía en meses', 'integer', 'one', 'ficha', 'activo', 'Offer.warranty', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(10, 'tension_v', 'Tensión (V)', 'Tensión de operación', 'number', 'one', 'electrico', 'activo', 'additionalProperty.voltage', 'VLT', NULL, '[\"VLT\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(11, 'corriente_a', 'Corriente (A)', 'Corriente de operación', 'number', 'one', 'electrico', 'activo', 'additionalProperty.current', 'AMP', NULL, '[\"AMP\",\"mA\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(12, 'potencia_w', 'Potencia (W)', 'Potencia nominal', 'number', 'one', 'electrico', 'activo', 'additionalProperty.power', 'WTT', NULL, '[\"WTT\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(13, 'polaridad', 'Polaridad', 'Tipo de corriente o polaridad', 'string', 'one', 'electrico', 'activo', 'additionalProperty.polarity', NULL, '[\"AC\",\"DC\",\"AC/DC\"]', NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(14, 'norma_certificacion', 'Norma/Certificación', 'Normas o certificaciones aplicables (CE, ASTM, ISO)', 'string', 'many', 'seguridad', 'activo', 'conformsTo', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(15, 'edad_segura_min', 'Edad segura mínima', 'Edad mínima recomendada para uso', 'integer', 'one', 'seguridad', 'activo', 'audience.suggestedMinAge', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(16, 'edad_segura_max', 'Edad segura máxima', 'Edad máxima recomendada para uso', 'integer', 'one', 'seguridad', 'activo', 'audience.suggestedMaxAge', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(17, 'pictogramas_ghs', 'Pictogramas GHS', 'Pictogramas de peligrosidad química (multi)', 'string', 'many', 'seguridad', 'activo', 'additionalProperty.ghsPictograms', NULL, '[\"GHS01\",\"GHS02\",\"GHS03\",\"GHS04\",\"GHS05\",\"GHS06\",\"GHS07\",\"GHS08\",\"GHS09\"]', NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(18, 'epp_requerido', 'EPP Requerido', 'Equipo de protección personal recomendado', 'string', 'many', 'seguridad', 'activo', 'additionalProperty.PPE', NULL, '[\"Gafas\",\"Guantes\",\"Bata\",\"Mascarilla\",\"Protección auditiva\"]', NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:44', '2025-12-21 19:07:44'),
(19, 'contenido_piezas', 'Contenido (piezas)', 'Número total de piezas incluidas', 'integer', 'one', 'empaque', 'activo', 'Product.numberOfItems', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(20, 'peso_empaque', 'Peso con empaque', 'Peso total para envío', 'number', 'one', 'empaque', 'activo', 'Product.shippingWeight', 'KGM', NULL, '[\"KGM\",\"GRM\"]', '[\"kit\",\"componente\"]', '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(21, 'condiciones_almacenamiento', 'Condiciones de almacenamiento', 'Recomendaciones de almacenamiento', 'string', 'one', 'empaque', 'activo', 'additionalProperty.storageConditions', NULL, NULL, NULL, '[\"kit\",\"componente\"]', '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(22, 'interactivity_type', 'Tipo de interactividad', 'Expositivo, activo o mixto', 'string', 'one', 'didactica', 'activo', 'LearningResource.interactivityType', NULL, NULL, NULL, '[\"clase\"]', '2025-12-21 20:44:33', '2025-12-21 20:44:33'),
(23, 'course_mode', 'Modalidad del curso', 'Presencial, laboratorio, taller, etc.', 'string', 'one', 'didactica', 'activo', 'CourseInstance.courseMode', NULL, NULL, NULL, '[\"clase\"]', '2025-12-21 20:45:43', '2025-12-21 20:45:43'),
(24, 'instructor_notes', 'Notas del docente', 'Orientaciones/metodología para el docente', 'string', 'one', 'didactica', 'activo', 'CreativeWork.teachingMethod', NULL, NULL, NULL, '[\"clase\"]', '2025-12-21 20:46:09', '2025-12-21 20:46:09'),
(25, 'tool_extra', 'Herramientas adicionales', 'Herramientas no incluidas en el kit', 'string', 'many', 'didactica', 'activo', 'HowTo.tool', NULL, NULL, NULL, '[\"clase\"]', '2025-12-21 20:46:09', '2025-12-21 20:46:09'),
(26, 'supply_extra', 'Insumos adicionales', 'Materiales extra fuera del kit', 'string', 'many', 'didactica', 'activo', 'HowTo.supply', NULL, NULL, NULL, '[\"clase\"]', '2025-12-21 20:46:09', '2025-12-21 20:46:09'),
(27, 'accessibility_summary', 'Resumen de accesibilidad', 'Resumen de apoyos o ajustes', 'string', 'one', 'accesibilidad', 'activo', 'CreativeWork.accessibilitySummary', NULL, NULL, NULL, '[\"clase\"]', '2025-12-21 20:46:27', '2025-12-21 20:46:27'),
(28, 'accessibility_feature', 'Características de accesibilidad', 'Captions, transcript, lectura fácil, etc.', 'string', 'many', 'accesibilidad', 'activo', 'CreativeWork.accessibilityFeature', NULL, NULL, NULL, '[\"clase\"]', '2025-12-21 20:46:28', '2025-12-21 20:46:28'),
(29, 'test', 'test', NULL, 'string', 'one', NULL, 'activo', NULL, 'mt', NULL, '[\"1\"]', '[\"kit\"]', '2025-12-23 02:55:59', '2025-12-23 02:55:59'),
(30, 'poder', 'poder', NULL, 'string', 'one', NULL, 'activo', NULL, 'mt', NULL, NULL, '[\"kit\"]', '2025-12-23 04:20:34', '2025-12-23 04:20:34');

-- --------------------------------------------------------

--
-- Table structure for table `atributos_mapeo`
--

CREATE TABLE `atributos_mapeo` (
  `id` int(11) NOT NULL,
  `atributo_id` int(11) NOT NULL,
  `tipo_entidad` enum('clase','manual','multimedia','kit','componente') NOT NULL,
  `requerido` tinyint(1) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `validaciones_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ui_hint` varchar(32) DEFAULT NULL COMMENT 'input|select|slider|quantitative|tags',
  `buscable` tinyint(1) NOT NULL DEFAULT 0,
  `facetable` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `atributos_mapeo`
--

INSERT INTO `atributos_mapeo` (`id`, `atributo_id`, `tipo_entidad`, `requerido`, `visible`, `orden`, `validaciones_json`, `ui_hint`, `buscable`, `facetable`, `created_at`, `updated_at`) VALUES
(1, 1, 'kit', 0, 1, 10, NULL, 'tags', 1, 1, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(2, 2, 'kit', 0, 1, 20, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(3, 3, 'kit', 0, 1, 30, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(4, 4, 'kit', 0, 1, 40, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(5, 5, 'kit', 0, 1, 50, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(6, 6, 'kit', 0, 1, 60, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(7, 7, 'kit', 0, 1, 70, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(8, 8, 'kit', 0, 1, 80, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(9, 9, 'kit', 0, 1, 90, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(10, 10, 'kit', 0, 1, 110, NULL, 'input', 1, 1, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(11, 11, 'kit', 0, 1, 120, NULL, 'input', 1, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(12, 12, 'kit', 0, 1, 130, NULL, 'input', 1, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(13, 13, 'kit', 0, 1, 140, NULL, 'input', 1, 1, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(14, 14, 'kit', 0, 1, 210, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(15, 15, 'kit', 0, 1, 220, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(16, 16, 'kit', 0, 1, 230, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(17, 17, 'kit', 0, 1, 240, NULL, 'tags', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(18, 18, 'kit', 0, 1, 250, NULL, 'tags', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(19, 19, 'kit', 0, 1, 310, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(20, 20, 'kit', 0, 1, 320, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(21, 21, 'kit', 0, 1, 330, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(32, 1, 'componente', 0, 1, 10, NULL, 'tags', 1, 1, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(33, 2, 'componente', 0, 1, 20, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(34, 3, 'componente', 0, 1, 30, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(35, 4, 'componente', 0, 1, 40, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(36, 5, 'componente', 0, 1, 50, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(37, 6, 'componente', 0, 1, 60, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(38, 7, 'componente', 0, 1, 70, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(39, 8, 'componente', 0, 1, 80, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(40, 9, 'componente', 0, 1, 90, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(41, 10, 'componente', 0, 1, 110, NULL, 'input', 1, 1, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(42, 11, 'componente', 0, 1, 120, NULL, 'input', 1, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(43, 12, 'componente', 0, 1, 130, NULL, 'input', 1, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(44, 13, 'componente', 0, 1, 140, NULL, 'input', 1, 1, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(45, 14, 'componente', 0, 1, 210, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(46, 15, 'componente', 0, 1, 220, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(47, 16, 'componente', 0, 1, 230, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(48, 17, 'componente', 0, 1, 240, NULL, 'tags', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(49, 18, 'componente', 0, 1, 250, NULL, 'tags', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(50, 19, 'componente', 0, 1, 310, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(51, 20, 'componente', 0, 1, 320, NULL, 'quantitative', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(52, 21, 'componente', 0, 1, 330, NULL, 'input', 0, 0, '2025-12-21 19:07:45', '2025-12-21 19:07:45'),
(63, 22, 'clase', 0, 1, 10, NULL, 'input', 0, 0, '2025-12-21 20:46:35', '2025-12-21 20:46:35'),
(64, 23, 'clase', 0, 1, 20, NULL, 'input', 0, 0, '2025-12-21 20:47:53', '2025-12-21 20:47:53'),
(65, 24, 'clase', 0, 1, 30, NULL, 'input', 0, 0, '2025-12-21 20:47:53', '2025-12-21 20:47:53'),
(66, 25, 'clase', 0, 1, 40, NULL, 'tags', 0, 0, '2025-12-21 20:47:53', '2025-12-21 20:47:53'),
(67, 26, 'clase', 0, 1, 50, NULL, 'tags', 0, 0, '2025-12-21 20:47:53', '2025-12-21 20:47:53'),
(68, 27, 'clase', 0, 1, 60, NULL, 'input', 0, 0, '2025-12-21 20:47:53', '2025-12-21 20:47:53'),
(69, 28, 'clase', 0, 1, 70, NULL, 'tags', 0, 0, '2025-12-21 20:47:53', '2025-12-21 20:47:53'),
(70, 29, 'kit', 0, 1, 331, NULL, NULL, 0, 0, '2025-12-23 02:55:59', '2025-12-23 02:55:59'),
(71, 30, 'kit', 0, 1, 332, NULL, NULL, 0, 0, '2025-12-23 04:20:34', '2025-12-23 04:20:34');

-- --------------------------------------------------------

--
-- Table structure for table `auditoria_admin`
--

CREATE TABLE `auditoria_admin` (
  `id` bigint(20) NOT NULL,
  `modulo` varchar(64) NOT NULL,
  `entidad` varchar(64) NOT NULL,
  `entidad_id` int(11) NOT NULL,
  `accion` enum('crear','editar','eliminar','cambio_estado') NOT NULL,
  `usuario` varchar(120) NOT NULL,
  `detalle_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorias_items`
--

CREATE TABLE `categorias_items` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorias_items`
--

INSERT INTO `categorias_items` (`id`, `nombre`, `slug`) VALUES
(1, 'Eléctricos', 'electricos'),
(2, 'Magnéticos', 'magneticos'),
(3, 'Biología', 'biologia'),
(4, 'Química', 'quimica'),
(5, 'Tecnología', 'tecnologia'),
(6, 'Herramientas', 'herramientas'),
(7, 'Seguridad', 'seguridad');

-- --------------------------------------------------------

--
-- Table structure for table `ciclos`
--

CREATE TABLE `ciclos` (
  `id` int(11) NOT NULL,
  `numero` int(11) NOT NULL COMMENT 'Número de ciclo (0-5)',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre del ciclo (ej: Exploración)',
  `slug` varchar(100) NOT NULL COMMENT 'URL-friendly identifier',
  `edad_min` int(11) NOT NULL COMMENT 'Edad mínima en años',
  `edad_max` int(11) NOT NULL COMMENT 'Edad máxima en años',
  `grados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array JSON de grados (ej: [6,7])',
  `grados_texto` varchar(100) DEFAULT NULL COMMENT 'Representación textual de grados (ej: 6° a 7°)',
  `proposito` text NOT NULL COMMENT 'Propósito educativo del ciclo',
  `explicacion` text DEFAULT NULL COMMENT 'Explicación detallada del ciclo',
  `nivel_educativo` varchar(100) DEFAULT NULL COMMENT 'Equivalencia en sistema colombiano',
  `isced_level` varchar(20) DEFAULT NULL COMMENT 'Código UNESCO ISCED',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Si el ciclo está activo para uso',
  `orden` int(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ciclos de aprendizaje';

--
-- Dumping data for table `ciclos`
--

INSERT INTO `ciclos` (`id`, `numero`, `nombre`, `slug`, `edad_min`, `edad_max`, `grados`, `grados_texto`, `proposito`, `explicacion`, `nivel_educativo`, `isced_level`, `activo`, `orden`) VALUES
(1, 0, 'Desarrollo Inicial', 'desarrollo-inicial', 0, 5, '[\"Jardín\", \"Transición\"]', 'Jardín y Transición', 'Estimulación temprana y socialización.', 'Ciclo enfocado en el desarrollo de habilidades motrices, lenguaje básico y socialización inicial. Los niños exploran el mundo a través del juego, desarrollan autonomía básica y establecen sus primeras relaciones sociales fuera del entorno familiar. Este ciclo sienta las bases para el aprendizaje formal.', 'Educación Inicial y Preescolar', 'ISCED 0', 0, 0),
(2, 1, 'Cimentación', 'cimentacion', 6, 8, '[1, 2, 3]', '1° a 3°', 'Alfabetización inicial y pensamiento numérico básico.', 'Desarrollo de competencias fundamentales en lectura, escritura y operaciones matemáticas básicas. Los estudiantes aprenden a seguir instrucciones, trabajar en grupo y desarrollan curiosidad por el mundo que les rodea. Introducción a conceptos científicos mediante observación directa.', 'Educación Básica Primaria', 'ISCED 1', 1, 1),
(3, 2, 'Consolidación', 'consolidacion', 9, 11, '[4, 5]', '4° a 5°', 'Desarrollo de autonomía y competencias de investigación.', 'Fortalecimiento de habilidades académicas y desarrollo de pensamiento crítico inicial. Los estudiantes aprenden a formular preguntas, buscar información y presentar sus hallazgos. Introducción al método científico mediante experimentos guiados y proyectos sencillos.', 'Educación Básica Primaria', 'ISCED 1', 1, 2),
(4, 3, 'Exploración', 'exploracion', 12, 13, '[6, 7]', '6° a 7°', 'Descubrimiento de intereses vocacionales y cambios físicos/sociales.', 'Transición a secundaria con énfasis en exploración de áreas de interés. Los estudiantes desarrollan habilidades de investigación más estructuradas, aprenden a observar fenómenos científicos y describir sus características. Desarrollo socioemocional durante cambios de la adolescencia temprana.', 'Educación Básica Secundaria', 'ISCED 2', 1, 3),
(5, 4, 'Experimentación y Profundización', 'experimentacion', 14, 15, '[8, 9]', '8° a 9°', 'Aplicación del conocimiento y resolución de problemas complejos.', 'Desarrollo de competencias científicas avanzadas con énfasis en experimentación controlada, análisis de variables y establecimiento de relaciones causales. Los estudiantes aplican el método científico de manera independiente, comparan resultados y explican fenómenos naturales con fundamento teórico.', 'Educación Básica Secundaria', 'ISCED 2', 1, 4),
(6, 5, 'Análisis y Proyección', 'analisis-proyeccion', 16, 17, '[10, 11]', '10° a 11°', 'Especialización académica o técnica y preparación para la vida adulta.', 'Culminación del proceso educativo básico con énfasis en análisis crítico, argumentación científica y conexión con problemas reales. Los estudiantes desarrollan proyectos de investigación complejos, evalúan impactos tecnológicos y sociales, y se preparan para educación superior o inserción laboral. Énfasis en sostenibilidad y responsabilidad social.', 'Educación Media (Bachillerato)', 'ISCED 3', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `clases`
--

CREATE TABLE `clases` (
  `id` int(11) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `ciclo` tinyint(1) NOT NULL,
  `grados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grados`)),
  `dificultad` varchar(32) DEFAULT NULL,
  `duracion_minutos` int(11) DEFAULT NULL,
  `resumen` text DEFAULT NULL,
  `objetivo_aprendizaje` text DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `video_portada` varchar(255) DEFAULT NULL,
  `seguridad` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`seguridad`)),
  `seo_title` varchar(160) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `orden_popularidad` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `autor` varchar(120) DEFAULT NULL,
  `contenido_html` mediumtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clases`
--

INSERT INTO `clases` (`id`, `nombre`, `slug`, `ciclo`, `grados`, `dificultad`, `duracion_minutos`, `resumen`, `objetivo_aprendizaje`, `imagen_portada`, `video_portada`, `seguridad`, `seo_title`, `seo_description`, `activo`, `destacado`, `orden_popularidad`, `status`, `published_at`, `autor`, `contenido_html`, `created_at`, `updated_at`) VALUES
(1, 'Microscopio sencillo', 'microscopio-sencillo', 1, '[6, 7]', 'facil', 60, 'Construye un microscopio artesanal para observar detalles invisibles.', 'Reconocer el uso de lentes para aumentar imágenes y describir observaciones científicas.', NULL, NULL, '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"⚠️ Manipular lentes y objetos pequeños con cuidado\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(2, 'Pulmón mecánico', 'pulmon-mecanico', 1, '[6, 7]', 'facil', 60, 'Modelo funcional de los pulmones usando presión de aire y movimiento.', 'Explicar la relación entre presión y volumen en un sistema respiratorio sencillo.', NULL, NULL, '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"⚠️ Supervisar uso de globos\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(3, 'Circuito eléctrico básico', 'circuito-electrico-basico', 1, '[6, 7]', 'facil', 60, 'Arma un circuito simple con batería, interruptor y LED.', 'Identificar componentes eléctricos básicos y observar transformaciones de energía.', NULL, NULL, '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"⚠️ No cortocircuitar baterías\"}', NULL, NULL, 1, 1, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(4, 'Separación de mezclas', 'separacion-de-mezclas', 1, '[6, 7]', 'facil', 60, 'Aplica métodos físicos para separar mezclas cotidianas.', 'Clasificar mezclas y aplicar filtración y decantación de manera segura.', NULL, NULL, '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"⚠️ Manejo cuidadoso de agua y utensilios\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(5, 'Test de pH', 'test-de-ph', 1, '[6, 7]', 'facil', 45, 'Usa tiras de pH para identificar ácidos y bases.', 'Reconocer propiedades químicas y aplicar normas de seguridad en el laboratorio escolar.', NULL, NULL, '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"⚠️ No ingerir sustancias\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(6, 'Emisión de ondas AM', 'clase-emision-de-ondas-am', 4, '[8,9,10]', 'media', 90, 'Comprende cómo se generan, transmiten y reciben las ondas de radio AM. Analiza el sistema emisor–canal–receptor, la modulación en amplitud, la resonancia LC y la detección por diodo desde una perspectiva conceptual.', 'Explicar la modulación AM y la propagación de ondas electromagnéticas. Describir el rol de antena, tierra, circuito resonante LC, diodo detector y auricular; interpretar selectividad, sensibilidad, ancho de banda y acoplo de impedancias en la recepción de señales.', '/assets/images/uploads/clase/2026/03/clase-20260309-201227-84e075038fa8.webp', 'https://www.youtube.com/embed/example-radio-cristal', '{\"edad_min\":14,\"edad_max\":18,\"notas\":\"Evita cables muy extensos en interiores y no conectes el circuito a la red el\\u00e9ctrica. Usa antena y tierra de manera segura, preferiblemente con supervisi\\u00f3n. No uses herramientas punzantes sin cuidado.\"}', 'Clase de Ciencia - Ciencias Ambientales: Emisión de ondas AM', 'Ciclo 4 (8° a 9°): Comprende cómo se generan, transmiten y reciben las ondas de radio AM. Analiza el sistema emisor–canal–receptor, la modulación en amplitud,', 1, 1, 5, 'published', '2025-12-20 10:00:00', 'Clase de Ciencia SAS', '<h2>Introducci&oacute;n</h2>\r\n\r\n<p>Las ondas de radio son ondas electromagn&eacute;ticas que viajan por el espacio y permiten la comunicaci&oacute;n a distancia. En la <strong>modulaci&oacute;n en amplitud (AM)</strong>, la amplitud de una onda portadora de alta frecuencia var&iacute;a siguiendo la forma de la se&ntilde;al de audio.</p>\r\n\r\n<h2>&iquest;Para qu&eacute; sirven las ondas de radio?</h2>\r\n\r\n<ul>\r\n	<li>Radiodifusi&oacute;n (AM), comunicaci&oacute;n mar&iacute;tima y aeron&aacute;utica, avisos de emergencia.</li>\r\n	<li>Sistemas educativos y culturales con amplia cobertura territorial.</li>\r\n</ul>\r\n\r\n<h2>Modelo de comunicaci&oacute;n AM</h2>\r\n\r\n<ul>\r\n	<li><strong>Emisor:</strong> Genera una portadora y la modula en amplitud con una se&ntilde;al de audio.</li>\r\n	<li><strong>Canal:</strong> Propagaci&oacute;n de la onda por el aire (y reflexi&oacute;n ionosf&eacute;rica en ciertas bandas y horarios).</li>\r\n	<li><strong>Receptor:</strong> Sintoniza una frecuencia, detecta la envolvente (audio) y la convierte en sonido.</li>\r\n</ul>\r\n\r\n<h2>Componentes del receptor (rol acad&eacute;mico)</h2>\r\n\r\n<ul>\r\n	<li><strong>Antena:</strong> Intercepta parte de la energ&iacute;a de la onda electromagn&eacute;tica y la convierte en una peque&ntilde;a se&ntilde;al el&eacute;ctrica.</li>\r\n	<li><strong>Tierra (referencia):</strong> Cierra el circuito y estabiliza potenciales, favoreciendo la circulaci&oacute;n de corriente de RF.</li>\r\n	<li><strong>Circuito resonante LC:</strong> Un inductor (L) y un capacitor (C) forman un filtro selectivo que <em>resuena</em> en una frecuencia. Su funci&oacute;n es <em>seleccionar</em> una emisora dentro del espectro.</li>\r\n	<li><strong>Diodo detector:</strong> Rectifica la se&ntilde;al AM (permite mayor paso de un semiciclo), de modo que puede recuperarse la <em>envolvente</em> (audio).</li>\r\n	<li><strong>Auricular de alta impedancia:</strong> Transduce la se&ntilde;al detectada en sonido; su alta impedancia minimiza la carga sobre el circuito.</li>\r\n	<li><strong>(Opcional) Capacitor de filtro:</strong> Suaviza la se&ntilde;al rectificada para perfilar la envolvente (equilibrando fidelidad y respuesta).</li>\r\n</ul>\r\n\r\n<h2>Funcionamiento del circuito</h2>\r\n\r\n<p>El circuito LC logra un m&aacute;ximo de respuesta en su frecuencia de resonancia, que depende de L y C. Aproximadamente: f &asymp; 1/(2&pi;&radic;(LC)). En resonancia, la tensi&oacute;n de RF en el nodo sintonizado aumenta. El diodo deja pasar preferentemente un semiciclo; combinando con la inercia del circuito y/o un filtro, se obtiene la envolvente (la informaci&oacute;n de audio), que el auricular transforma en sonido.</p>\r\n\r\n<h2>Variables y relaciones clave</h2>\r\n\r\n<ul>\r\n	<li><strong>Frecuencia de sinton&iacute;a (f):</strong> Cambia al variar L o C. Menor L o C &rarr; mayor f.</li>\r\n	<li><strong>Selectividad y factor Q:</strong> Q alto &rarr; banda estrecha y mejor separaci&oacute;n entre emisoras, pero m&aacute;s sensible a desajustes.</li>\r\n	<li><strong>Sensibilidad:</strong> Capacidad para captar se&ntilde;ales d&eacute;biles; mejora con antena adecuada y p&eacute;rdidas bajas.</li>\r\n	<li><strong>Impedancia y acoplo:</strong> Una carga muy baja &ldquo;tira&rdquo; la se&ntilde;al. Auriculares de alta impedancia reducen p&eacute;rdidas; un transformador puede ayudar al acoplo.</li>\r\n	<li><strong>Antena:</strong> Longitud y ubicaci&oacute;n influyen en la cantidad de se&ntilde;al captada; el entorno (edificios, cables) modifica la recepci&oacute;n.</li>\r\n	<li><strong>Comportamiento del canal:</strong> En AM de onda media, la propagaci&oacute;n cambia entre d&iacute;a y noche por la ionosfera.</li>\r\n</ul>\r\n\r\n<h2>Actividades de an&aacute;lisis conceptual (sin armado)</h2>\r\n\r\n<ul>\r\n	<li>Identifica en un diagrama de bloques d&oacute;nde ocurren <em>selecci&oacute;n (LC)</em>, <em>detecci&oacute;n (diodo)</em> y <em>transducci&oacute;n (auricular)</em>.</li>\r\n	<li>Predice c&oacute;mo cambia la frecuencia de sinton&iacute;a al duplicar C; explica el efecto sobre selectividad.</li>\r\n	<li>Compara AM vs FM: &iquest;qu&eacute; caracter&iacute;stica de la portadora cambia en cada caso?</li>\r\n	<li>Discute por qu&eacute; un diodo de germanio (&asymp;0,2&ndash;0,3 V) suele ser mejor que uno de silicio (&asymp;0,6&ndash;0,7 V) para se&ntilde;ales peque&ntilde;as.</li>\r\n	<li>Relaciona <em>ancho de banda</em> con <em>calidad de audio</em> y con la separaci&oacute;n entre emisoras.</li>\r\n</ul>\r\n\r\n<h2>Evaluaci&oacute;n formativa</h2>\r\n\r\n<ul>\r\n	<li>Explica con tus palabras la diferencia entre portadora, se&ntilde;al modulante y envolvente.</li>\r\n	<li>Describe el papel de la antena y de la tierra en la captaci&oacute;n de la se&ntilde;al.</li>\r\n	<li>Argumenta c&oacute;mo el circuito LC act&uacute;a como &ldquo;sintonizador&rdquo;.</li>\r\n	<li>Prop&oacute;n mejoras te&oacute;ricas para aumentar sensibilidad sin sacrificar demasiada selectividad.</li>\r\n</ul>\r\n\r\n<h2>Glosario b&aacute;sico</h2>\r\n\r\n<ul>\r\n	<li><strong>Portadora:</strong> Se&ntilde;al de alta frecuencia que transporta la informaci&oacute;n.</li>\r\n	<li><strong>Modulaci&oacute;n:</strong> Proceso de &ldquo;imprimir&rdquo; informaci&oacute;n en la portadora (en AM: variar amplitud).</li>\r\n	<li><strong>Resonancia:</strong> M&aacute;xima respuesta de un circuito a una frecuencia espec&iacute;fica.</li>\r\n	<li><strong>Envolvente:</strong> Perfil lento que contiene la informaci&oacute;n de audio.</li>\r\n	<li><strong>Selectividad:</strong> Capacidad de separar se&ntilde;ales cercanas en frecuencia.</li>\r\n</ul>\r\n', '2025-12-20 04:46:28', '2026-03-10 01:12:34'),
(7, 'Motor eléctrico simple', 'motor-electrico-simple', 2, '[8, 9]', 'media', 90, 'Arma un motor básico que convierte energía eléctrica en movimiento.', 'Relacionar electricidad y magnetismo y analizar variables que afectan el movimiento.', NULL, NULL, '{\"edad_min\": 13, \"edad_max\": 15, \"notas\": \"⚠️ Imán potente, evitar acercar a dispositivos\"}', NULL, NULL, 1, 1, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(8, 'Osmosis con vegetales', 'osmosis-con-vegetales', 2, '[8, 9]', 'media', 60, 'Observa cambios por transporte celular en vegetales con soluciones salinas.', 'Explicar procesos celulares usando evidencia experimental.', NULL, NULL, '{\"edad_min\": 13, \"edad_max\": 15, \"notas\": \"⚠️ Higiene y manejo de alimentos\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(9, 'Carro trampa de ratón', 'carro-trampa-de-raton', 2, '[8, 9]', 'media', 90, 'Construye un carro impulsado por energía potencial de una trampa.', 'Analizar fuerzas, fricción y transformación de energías en sistemas mecánicos.', NULL, NULL, '{\"edad_min\": 13, \"edad_max\": 15, \"notas\": \"⚠️ Riesgo de pellizco, usar bajo supervisión\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(10, 'Generador manual (dinamo)', 'generador-manual-dinamo', 2, '[8, 9]', 'media', 90, 'Genera electricidad manualmente mediante inducción electromagnética.', 'Explicar generación eléctrica relacionando movimiento y energía.', NULL, NULL, '{\"edad_min\": 13, \"edad_max\": 15, \"notas\": \"⚠️ Cuidado con conexiones eléctricas\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(11, 'Carro solar', 'carro-solar', 3, '[10, 11]', 'dificil', 120, 'Construye y evalúa un vehículo impulsado por energía solar.', 'Analizar eficiencia energética y sostenibilidad en sistemas tecnológicos.', NULL, NULL, '{\"edad_min\": 15, \"edad_max\": 18, \"notas\": \"⚠️ Panel frágil, manipulación cuidadosa\"}', NULL, NULL, 1, 1, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(12, 'Turbina eólica de mesa', 'turbina-eolica-de-mesa', 3, '[10, 11]', 'dificil', 120, 'Diseña una turbina de mesa para convertir energía del viento.', 'Evaluar fuentes alternativas y analizar impacto tecnológico.', NULL, NULL, '{\"edad_min\": 15, \"edad_max\": 18, \"notas\": \"⚠️ Hélice en movimiento, mantener distancia\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(13, 'Electroimán', 'electroiman', 3, '[10, 11]', 'dificil', 90, 'Construye un electroimán y analiza variables de fuerza y campo.', 'Analizar relación corriente-campo y formular explicaciones causales.', NULL, NULL, '{\"edad_min\": 15, \"edad_max\": 18, \"notas\": \"⚠️ Calentamiento por corriente, usar brevemente\"}', NULL, NULL, 1, 1, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(14, 'Tratamiento de agua', 'tratamiento-de-agua', 3, '[10, 11]', 'dificil', 120, 'Implementa un filtro de agua con capas y evalúa calidad.', 'Explicar procesos físico-químicos y relacionar ciencia con el entorno.', NULL, NULL, '{\"edad_min\": 15, \"edad_max\": 18, \"notas\": \"⚠️ Uso responsable de reactivos y desecho\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(15, 'Análisis químico del entorno', 'analisis-quimico-del-entorno', 3, '[10, 11]', 'dificil', 120, 'Realiza pruebas químicas seguras a sustancias cotidianas.', 'Explicar transformaciones químicas con principios de seguridad y ética.', NULL, NULL, '{\"edad_min\": 15, \"edad_max\": 18, \"notas\": \"⚠️ No ingerir sustancias, guantes recomendados\"}', NULL, NULL, 1, 0, 0, 'published', '2025-12-20 04:46:28', NULL, NULL, '2025-12-20 04:46:28', '2025-12-20 04:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `clase_areas`
--

CREATE TABLE `clase_areas` (
  `clase_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clase_areas`
--

INSERT INTO `clase_areas` (`clase_id`, `area_id`) VALUES
(1, 3),
(2, 3),
(3, 1),
(4, 2),
(5, 2),
(6, 1),
(6, 4),
(6, 5),
(7, 1),
(8, 3),
(9, 1),
(9, 4),
(10, 1),
(11, 1),
(11, 4),
(12, 1),
(12, 4),
(13, 1),
(14, 2),
(14, 5),
(15, 2);

-- --------------------------------------------------------

--
-- Table structure for table `clase_competencias`
--

CREATE TABLE `clase_competencias` (
  `clase_id` int(11) NOT NULL,
  `competencia_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clase_competencias`
--

INSERT INTO `clase_competencias` (`clase_id`, `competencia_id`) VALUES
(6, 2),
(6, 5),
(6, 11);

-- --------------------------------------------------------

--
-- Table structure for table `clase_kits`
--

CREATE TABLE `clase_kits` (
  `clase_id` int(11) NOT NULL,
  `kit_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `es_principal` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Kit principal de la clase',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clase_kits`
--

INSERT INTO `clase_kits` (`clase_id`, `kit_id`, `sort_order`, `es_principal`, `created_at`) VALUES
(1, 1, 1, 1, '2025-12-21 23:20:07'),
(2, 2, 1, 1, '2025-12-20 21:53:17'),
(3, 3, 1, 1, '2025-12-20 21:53:17'),
(4, 4, 1, 1, '2025-12-21 19:48:45'),
(5, 5, 1, 1, '2025-12-20 21:53:17'),
(6, 6, 2, 0, '2026-03-10 02:48:17'),
(7, 7, 1, 1, '2025-12-20 21:53:17'),
(8, 8, 1, 1, '2025-12-20 21:53:17'),
(9, 9, 1, 1, '2025-12-20 21:53:17'),
(10, 10, 1, 1, '2025-12-20 21:53:17'),
(11, 6, 1, 1, '2026-03-10 02:48:17'),
(11, 11, 1, 1, '2025-12-20 21:53:17'),
(12, 12, 1, 1, '2025-12-20 21:53:17'),
(13, 13, 1, 1, '2025-12-25 04:03:46'),
(14, 14, 1, 1, '2025-12-25 04:03:31'),
(15, 15, 1, 1, '2025-12-20 21:53:17');

-- --------------------------------------------------------

--
-- Table structure for table `clase_tags`
--

CREATE TABLE `clase_tags` (
  `clase_id` int(11) NOT NULL,
  `tag` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clase_tags`
--

INSERT INTO `clase_tags` (`clase_id`, `tag`) VALUES
(5, 'acidos'),
(14, 'agua'),
(6, 'am'),
(11, 'ambiental'),
(12, 'ambiental'),
(14, 'ambiental'),
(15, 'ambiental'),
(15, 'analisis'),
(2, 'anatomia'),
(6, 'antena'),
(1, 'aumento'),
(5, 'bases'),
(3, 'bateria'),
(1, 'biologia'),
(2, 'biologia'),
(8, 'biologia'),
(13, 'campo-magnetico'),
(8, 'celula'),
(3, 'circuito'),
(6, 'circuito-lc'),
(6, 'comunicacion'),
(13, 'corriente'),
(4, 'decantacion'),
(6, 'detector'),
(10, 'dinamo'),
(11, 'eficiencia'),
(3, 'electricidad'),
(7, 'electricidad'),
(10, 'electricidad'),
(13, 'electricidad'),
(13, 'electroiman'),
(7, 'electromagnetismo'),
(10, 'electromagnetismo'),
(13, 'electromagnetismo'),
(3, 'electronica'),
(3, 'energia'),
(7, 'energia'),
(10, 'energia'),
(12, 'energia-eolica'),
(9, 'energia-potencial'),
(11, 'energia-solar'),
(8, 'experimento'),
(13, 'experimento'),
(15, 'experimento'),
(1, 'experimento-casero'),
(4, 'filtracion'),
(14, 'filtracion'),
(7, 'fisica'),
(9, 'fisica'),
(10, 'fisica'),
(11, 'fisica'),
(12, 'fisica'),
(13, 'fisica'),
(9, 'friccion'),
(9, 'fuerzas'),
(10, 'generador'),
(5, 'indicadores'),
(10, 'induccion'),
(9, 'ingenieria'),
(5, 'laboratorio'),
(15, 'laboratorio'),
(3, 'led'),
(1, 'lentes'),
(7, 'magnetismo'),
(10, 'magnetismo'),
(13, 'magnetismo'),
(9, 'mecanica'),
(8, 'membrana'),
(4, 'metodos-fisicos'),
(4, 'mezclas'),
(2, 'modelo'),
(6, 'modulacion'),
(7, 'motor'),
(7, 'movimiento'),
(9, 'movimiento'),
(1, 'observacion'),
(6, 'ondas'),
(1, 'optica'),
(8, 'osmosis'),
(11, 'panel-solar'),
(5, 'ph'),
(2, 'presion'),
(14, 'purificacion'),
(4, 'quimica'),
(5, 'quimica'),
(14, 'quimica'),
(15, 'quimica'),
(6, 'radio'),
(15, 'reacciones'),
(11, 'renovable'),
(12, 'renovable'),
(2, 'respiracion'),
(5, 'seguridad'),
(15, 'seguridad'),
(4, 'separacion'),
(2, 'sistema-respiratorio'),
(11, 'sostenibilidad'),
(12, 'sostenibilidad'),
(14, 'sostenibilidad'),
(15, 'sustancias'),
(3, 'tecnologia'),
(7, 'tecnologia'),
(9, 'tecnologia'),
(10, 'tecnologia'),
(11, 'tecnologia'),
(12, 'tecnologia'),
(14, 'tecnologia'),
(8, 'transporte-celular'),
(14, 'tratamiento'),
(12, 'turbina'),
(8, 'vegetales'),
(12, 'viento'),
(2, 'volumen');

-- --------------------------------------------------------

--
-- Table structure for table `competencias`
--

CREATE TABLE `competencias` (
  `id` int(11) NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `subcategoria` varchar(100) DEFAULT NULL,
  `nombre` varchar(160) NOT NULL,
  `explicacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `competencias`
--

INSERT INTO `competencias` (`id`, `codigo`, `subcategoria`, `nombre`, `explicacion`) VALUES
(1, 'CB-CN-IND-01', 'Competencias Básicas - Ciencias Naturales: Indagación', 'Formulo preguntas sobre fenómenos naturales y diseño experimentos', 'Capacidad para identificar problemas científicos, plantear preguntas investigables y diseñar procedimientos experimentales controlados.'),
(2, 'CB-CN-IND-02', 'Competencias Básicas - Ciencias Naturales: Indagación', 'Observo, registro y analizo datos de manera sistemática', 'Habilidad para realizar observaciones detalladas, registrar información organizada y analizar patrones en los datos obtenidos.'),
(3, 'CB-CN-EXP-01', 'Competencias Básicas - Ciencias Naturales: Explicación de fenómenos', 'Establezco relaciones causales entre fenómenos científicos', 'Capacidad para identificar relaciones causa-efecto en procesos naturales usando principios científicos.'),
(4, 'CB-CN-EXP-02', 'Competencias Básicas - Ciencias Naturales: Explicación de fenómenos', 'Modelo fenómenos naturales con representaciones', 'Habilidad para crear modelos conceptuales, diagramas y esquemas que expliquen sistemas naturales.'),
(5, 'CB-CN-USO-01', 'Competencias Básicas - Ciencias Naturales: Uso comprensivo del conocimiento', 'Aplico conceptos científicos a situaciones cotidianas', 'Capacidad para transferir conocimiento científico a contextos de la vida diaria.'),
(6, 'CB-CN-USO-02', 'Competencias Básicas - Ciencias Naturales: Uso comprensivo del conocimiento', 'Evalúo impactos de la ciencia en sociedad y ambiente', 'Competencia para analizar consecuencias sociales, éticas y ambientales del conocimiento científico.'),
(7, 'CB-MAT-NUM-01', 'Competencias Básicas - Matemáticas: Pensamiento numérico', 'Realizo cálculos, estimaciones y mediciones precisas', 'Capacidad para efectuar operaciones matemáticas y realizar mediciones con instrumentos apropiados.'),
(8, 'CB-MAT-ESP-01', 'Competencias Básicas - Matemáticas: Pensamiento espacial', 'Interpreto representaciones geométricas y espaciales', 'Habilidad para visualizar formas, ubicaciones y relaciones espaciales en dos y tres dimensiones.'),
(9, 'CB-MAT-MET-01', 'Competencias Básicas - Matemáticas: Pensamiento métrico', 'Uso unidades de medida y estimo magnitudes', 'Competencia para seleccionar unidades apropiadas y convertir entre sistemas de medida.'),
(10, 'CB-MAT-ALE-01', 'Competencias Básicas - Matemáticas: Pensamiento aleatorio', 'Analizo datos, interpreto gráficas y probabilidades', 'Capacidad para organizar, representar e interpretar datos mediante gráficas estadísticas.'),
(11, 'CB-MAT-VAR-01', 'Competencias Básicas - Matemáticas: Pensamiento variacional', 'Identifico patrones, regularidades y relaciones', 'Habilidad para reconocer secuencias, patrones de cambio y relaciones funcionales.'),
(12, 'CB-LEN-PRO-01', 'Competencias Básicas - Lenguaje: Producción textual', 'Produzco textos científicos con estructura lógica', 'Competencia para redactar informes de laboratorio y reportes con lenguaje técnico apropiado.'),
(13, 'CB-LEN-COM-01', 'Competencias Básicas - Lenguaje: Comprensión e interpretación', 'Interpreto textos científicos y técnicos', 'Capacidad para leer comprensivamente artículos científicos extrayendo ideas principales.'),
(14, 'CB-LEN-MED-01', 'Competencias Básicas - Lenguaje: Medios de comunicación', 'Evalúo críticamente información científica en medios', 'Habilidad para analizar noticias científicas identificando fuentes confiables y sesgos.'),
(15, 'CB-LEN-ETI-01', 'Competencias Básicas - Lenguaje: Ética de la comunicación', 'Cito fuentes y respeto autoría intelectual', 'Competencia para reconocer y referenciar apropiadamente el trabajo de otros científicos.'),
(16, 'CC-PAZ-01', 'Competencias Ciudadanas - Convivencia y Paz', 'Trabajo colaborativamente y manejo conflictos', 'Capacidad para resolver desacuerdos constructivamente y mantener respeto mutuo en equipos.'),
(17, 'CC-PAZ-02', 'Competencias Ciudadanas - Convivencia y Paz', 'Respeto la integridad y rechazo la violencia', 'Competencia para seguir protocolos de seguridad y cuidar la integridad propia y de otros.'),
(18, 'CC-PAR-01', 'Competencias Ciudadanas - Participación y Responsabilidad Democrática', 'Participo en toma de decisiones colectivas', 'Habilidad para escuchar opiniones diversas y llegar a consensos justos en grupos.'),
(19, 'CC-PAR-02', 'Competencias Ciudadanas - Participación y Responsabilidad Democrática', 'Ejerzo el poder de forma responsable', 'Capacidad para liderar equipos distribuyendo tareas equitativamente y ejerciendo autoridad ética.'),
(20, 'CC-PLU-01', 'Competencias Ciudadanas - Pluralidad, Identidad y Valoración de Diferencias', 'Reconozco y valoro la diversidad de ideas', 'Competencia para apreciar diferentes perspectivas y métodos en el trabajo científico.'),
(21, 'CC-PLU-02', 'Competencias Ciudadanas - Pluralidad, Identidad y Valoración de Diferencias', 'Rechazo la discriminación y promuevo equidad', 'Habilidad para identificar y oponerme a tratos injustos basados en diferencias individuales.'),
(22, 'CLG-PER-01', 'Competencias Laborales - Personales', 'Demuestro orientación ética y responsabilidad', 'Competencia para actuar con honestidad científica y asumir responsabilidad por resultados.'),
(23, 'CLG-PER-02', 'Competencias Laborales - Personales', 'Gestiono inteligencia emocional', 'Habilidad para mantener la calma ante fracasos y adaptarme a resultados inesperados.'),
(24, 'CLG-PER-03', 'Competencias Laborales - Personales', 'Me adapto al cambio y muestro resiliencia', 'Capacidad para ajustar estrategias cuando las condiciones experimentales varían.'),
(25, 'CLG-INT-01', 'Competencias Laborales - Interpersonales', 'Me comunico efectivamente', 'Competencia para expresar ideas claramente y mantener comunicación asertiva en equipos.'),
(26, 'CLG-INT-02', 'Competencias Laborales - Interpersonales', 'Trabajo en equipo coordinadamente', 'Habilidad para colaborar respetando roles y coordinando tareas grupales.'),
(27, 'CLG-INT-03', 'Competencias Laborales - Interpersonales', 'Ejerzo liderazgo y manejo conflictos', 'Capacidad para guiar equipos, mediar en desacuerdos y motivar compañeros.'),
(28, 'CLG-ORG-01', 'Competencias Laborales - Organizacionales', 'Gestiono información eficientemente', 'Competencia para organizar datos, documentar procesos y mantener registros ordenados.'),
(29, 'CLG-ORG-02', 'Competencias Laborales - Organizacionales', 'Gestiono recursos y tiempo', 'Habilidad para planificar tiempos, usar materiales sin desperdicio y cumplir plazos.'),
(30, 'CLG-ORG-03', 'Competencias Laborales - Organizacionales', 'Me oriento al servicio y calidad', 'Capacidad para ejecutar tareas con excelencia y enfoque en resultados útiles.'),
(31, 'CLG-TEC-01', 'Competencias Laborales - Tecnológicas', 'Manejo herramientas e instrumentos', 'Competencia para seleccionar, operar y mantener equipos de laboratorio apropiadamente.'),
(32, 'CLG-TEC-02', 'Competencias Laborales - Tecnológicas', 'Identifico fallas y propongo soluciones', 'Habilidad para detectar problemas en procedimientos e implementar mejoras.'),
(33, 'CLG-TEC-03', 'Competencias Laborales - Tecnológicas', 'Innovo y optimizo procesos', 'Capacidad para proponer modificaciones creativas que mejoren resultados experimentales.'),
(34, 'CLG-EMP-01', 'Competencias Laborales - Empresariales y Emprendimiento', 'Identifico oportunidades de innovación', 'Competencia para reconocer problemas que pueden resolverse mediante soluciones científicas.'),
(35, 'CLG-EMP-02', 'Competencias Laborales - Empresariales y Emprendimiento', 'Muestro creatividad y asumo riesgos', 'Habilidad para diseñar propuestas viables y ejecutarlas asumiendo riesgos calculados.'),
(36, 'NCP-SOC-01', 'Nuevas Competencias 2025 - Socioemocionales', 'Gestiono mis emociones y autoestima', 'Capacidad para identificar estados emocionales y cómo afectan el aprendizaje científico.'),
(37, 'NCP-SOC-02', 'Nuevas Competencias 2025 - Socioemocionales', 'Desarrollo resiliencia y persisto ante dificultades', 'Habilidad para mantener motivación y recuperarme de contratiempos experimentales.'),
(38, 'NCP-SOC-03', 'Nuevas Competencias 2025 - Socioemocionales', 'Practico empatía y apoyo solidario', 'Competencia para comprender dificultades de compañeros y ofrecer ayuda constructiva.'),
(39, 'NCP-SOC-04', 'Nuevas Competencias 2025 - Socioemocionales', 'Cuido mi salud mental integral', 'Capacidad para reconocer cuando necesito apoyo y mantener hábitos saludables de estudio.'),
(40, 'NCP-DIG-01', 'Nuevas Competencias 2025 - Digitales y Ciudadanía Digital', 'Uso IA y tecnología de forma ética', 'Competencia para utilizar herramientas digitales citando fuentes y evitando plagio.'),
(41, 'NCP-DIG-02', 'Nuevas Competencias 2025 - Digitales y Ciudadanía Digital', 'Prevengo ciberacoso y protejo datos', 'Habilidad para proteger información personal y reportar comportamientos abusivos en línea.'),
(42, 'NCP-DIG-03', 'Nuevas Competencias 2025 - Digitales y Ciudadanía Digital', 'Practico alfabetización mediática', 'Capacidad para verificar veracidad de información científica e identificar noticias falsas.'),
(43, 'NCP-SOS-01', 'Nuevas Competencias 2025 - Desarrollo Sostenible', 'Implemento proyectos ambientales PRAE', 'Competencia para diseñar y ejecutar proyectos ambientales escolares con impacto real.'),
(44, 'NCP-SOS-02', 'Nuevas Competencias 2025 - Desarrollo Sostenible', 'Demuestro conciencia climática', 'Habilidad para comprender causas del cambio climático y proponer acciones de mitigación.'),
(45, 'NCP-SOS-03', 'Nuevas Competencias 2025 - Desarrollo Sostenible', 'Gestiono recursos responsablemente', 'Capacidad para minimizar desperdicio de materiales, agua y energía en experimentos.'),
(46, 'NCP-FIN-01', 'Nuevas Competencias 2025 - Educación Financiera y Vial', 'Gestiono recursos económicos en proyectos', 'Competencia para presupuestar materiales y optimizar costos en actividades científicas.'),
(47, 'NCP-VIA-01', 'Nuevas Competencias 2025 - Educación Financiera y Vial', 'Aplico seguridad en movilidad escolar', 'Habilidad para trasladar materiales y equipos de forma segura siguiendo normas viales.'),
(48, 'TRANS-MET-01', 'Transversales - Método Científico', 'Aplico el ciclo completo de investigación', 'Competencia para seguir todas las etapas: observación, pregunta, hipótesis, experimentación, análisis y conclusión.'),
(49, 'TRANS-SEG-01', 'Transversales - Seguridad y Bioseguridad', 'Aplico normas de bioseguridad', 'Habilidad para usar EPP, manipular sustancias químicas de forma segura y responder ante emergencias.'),
(50, 'TRANS-DOC-01', 'Transversales - Documentación Científica', 'Registro procesos con rigor científico', 'Capacidad para llevar bitácora de laboratorio y documentar observaciones precisas y honestas.');

-- --------------------------------------------------------

--
-- Table structure for table `configuracion_ia`
--

CREATE TABLE `configuracion_ia` (
  `id` int(11) NOT NULL,
  `instancia` enum('frontend','backend') NOT NULL DEFAULT 'frontend',
  `pagina` varchar(40) DEFAULT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('texto','numero','booleano','json','secreto') DEFAULT 'texto',
  `descripcion` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuracion_ia`
--

INSERT INTO `configuracion_ia` (`id`, `instancia`, `pagina`, `clave`, `valor`, `tipo`, `descripcion`, `updated_at`) VALUES
(1, 'frontend', NULL, 'palabras_peligro', '[\"fuego\", \"explosión\", \"ácido fuerte\", \"cortocircuito\", \"veneno\"]', 'json', 'Palabras que activan guardrails de seguridad', '2026-03-21 02:23:32'),
(2, 'frontend', NULL, 'ia_activa', '1', 'booleano', 'Activa o desactiva la IA del estudiante (1=activa, 0=inactiva)', '2026-03-21 02:23:32'),
(3, 'frontend', NULL, 'groq_api_key', '', 'secreto', 'API Key de Groq para la instancia frontend. Completar desde el panel admin.', '2026-03-21 01:19:55'),
(4, 'frontend', NULL, 'groq_model_1', 'llama-3.3-70b-versatile', 'texto', 'Modelo principal frontend — pedagógico y preciso', '2026-03-21 02:23:32'),
(5, 'frontend', NULL, 'groq_model_2', 'llama-3.1-8b-instant', 'texto', 'Fallback 1 frontend — rápido y económico (560 t/s)', '2026-03-21 02:23:32'),
(6, 'frontend', NULL, 'groq_model_3', 'openai/gpt-oss-20b', 'texto', 'Fallback 2 frontend — último recurso (1000 t/s)', '2026-03-21 02:23:32'),
(7, 'frontend', NULL, 'groq_temperature', '0.5', 'numero', 'Temperatura de muestreo (0-2). 0.5 = respuestas consistentes y seguras', '2026-03-21 02:23:32'),
(8, 'frontend', NULL, 'groq_max_tokens', '400', 'numero', 'Máximo de tokens en la respuesta al estudiante', '2026-03-21 02:23:32'),
(9, 'frontend', NULL, 'groq_top_p', '0.9', 'numero', 'Top-p para sampleo de tokens (0-1)', '2026-03-21 02:23:32'),
(10, 'frontend', NULL, 'prompt_sistema', 'Eres un asistente científico educativo para estudiantes colombianos de ciencia (grados 1° a 11°). Tu misión es GUIAR, no resolver: usa preguntas socráticas para que el estudiante descubra las respuestas por sí mismo. NUNCA resuelvas preguntas de examen o evaluaciones directamente. no SIEMPRE menciona las normas de seguridad antes de cualquier instrucción experimental, solo cuando sea necesario. Habla con lenguaje claro, amigable y motivador, apropiado para el ciclo educativo del estudiante. Si la pregunta se sale del ámbito científico educativo, redirige amablemente al tema. Responde siempre en español colombiano.', 'texto', 'Prompt del sistema que define el comportamiento base de la IA del estudiante', '2026-03-21 02:23:32'),
(11, 'frontend', NULL, 'guardrails_activos', '1', 'booleano', 'Activa el sistema de filtrado de contenido peligroso (1=activo, 0=desactivo)', '2026-03-21 02:23:32'),
(12, 'frontend', NULL, 'palabras_tematicas', '[\"política\",\"religión\",\"violencia\",\"drogas\",\"alcohol\",\"armas\",\"sexo\",\"apuestas\",\"odio\",\"insultos\"]', 'json', 'Palabras fuera del ámbito educativo que activan el guardrail temático', '2026-03-21 02:23:32'),
(13, 'frontend', NULL, 'nivel_safety', 'estricto', 'texto', 'Nivel de safety: estricto|moderado|libre', '2026-03-21 02:23:32'),
(14, 'frontend', NULL, 'mensaje_guardrail', '⚠️ Esa pregunta está fuera del ámbito de esta clase. Consulta con tu profesor. Si tienes dudas sobre seguridad en el experimento, sigue siempre las instrucciones del kit.', 'texto', 'Mensaje mostrado al estudiante cuando se activa un guardrail', '2026-03-21 02:23:32'),
(42, 'backend', NULL, 'ia_activa', '1', 'booleano', 'Activa o desactiva la IA del administrador (1=activa, 0=inactiva)', '2026-03-21 01:29:41'),
(43, 'backend', NULL, 'groq_api_key', '', 'secreto', 'API Key de Groq para la instancia backend. Puede ser la misma del frontend o diferente.', '2026-03-21 01:20:13'),
(44, 'backend', NULL, 'groq_model_1', 'openai/gpt-oss-20b', 'texto', 'Modelo principal backend — rápido y técnico (1000 t/s)', '2026-03-21 01:29:41'),
(45, 'backend', NULL, 'groq_model_2', 'llama-3.3-70b-versatile', 'texto', 'Fallback 1 backend — preciso y capaz (280 t/s)', '2026-03-21 01:29:41'),
(46, 'backend', NULL, 'groq_model_3', 'llama-3.1-8b-instant', 'texto', 'Fallback 2 backend — último recurso (560 t/s)', '2026-03-21 01:29:41'),
(47, 'backend', NULL, 'groq_temperature', '0.3', 'numero', 'Temperatura (0-2). 0.3 = respuestas factuales y precisas', '2026-03-21 01:29:41'),
(48, 'backend', NULL, 'groq_max_tokens', '1200', 'numero', 'Máximo de tokens en la respuesta al administrador', '2026-03-21 01:29:41'),
(49, 'backend', NULL, 'groq_top_p', '0.95', 'numero', 'Top-p para sampleo de tokens (0-1)', '2026-03-21 01:29:41'),
(50, 'backend', NULL, 'prompt_sistema', 'Eres un asistente técnico y operativo para el equipo administrativo de Clase de Ciencia SAS. Tienes acceso al estado actual del sistema: contratos CTeI, entregas de kits educativos, lotes de materiales, clases y métricas de la IA. Responde con precisión usando los datos del contexto proporcionado. Puedes sugerir acciones administrativas, detectar inconsistencias en los datos y ayudar a redactar documentos operativos. Sé directo y conciso. Responde en español.', 'texto', 'Prompt del sistema que define el comportamiento base de la IA del administrador', '2026-03-21 01:29:41'),
(51, 'backend', NULL, 'guardrails_activos', '1', 'booleano', 'Guardrails desactivados por defecto para el admin (1=activar si se requiere)', '2026-03-21 01:29:41'),
(52, 'backend', NULL, 'palabras_peligro', '[]', 'json', 'Sin palabras peligro para el admin (lista vacía)', '2026-03-21 01:29:41'),
(53, 'backend', NULL, 'palabras_tematicas', '[]', 'json', 'Sin filtro temático para el admin (lista vacía)', '2026-03-21 01:29:41'),
(54, 'backend', NULL, 'nivel_safety', 'moderado', 'texto', 'Nivel de safety del admin: estricto|moderado|libre', '2026-03-21 01:29:41'),
(55, 'backend', NULL, 'mensaje_guardrail', '⚠️ Esta consulta fue bloqueada por el sistema de seguridad.', 'texto', 'Mensaje de guardrail para el admin (raramente se activa)', '2026-03-21 01:29:41'),
(56, 'frontend', 'clase', 'prompt_sistema', 'Eres Clase de CiencIA, guía científico del estudiante durante un experimento. Usa el método socrático: haz preguntas que lleven al estudiante a descubrir por sí mismo. NUNCA des la respuesta directa a una evaluación. Usa el contexto de la clase (materiales, pasos, guía pedagógica) para hacer referencias concretas. Menciona seguridad solo cuando sea relevante al paso actual. Haz máximo UNA pregunta de seguimiento por respuesta. Responde en español colombiano.', 'texto', 'Prompt socrático — experimento activo', '2026-03-23 23:39:19'),
(57, 'frontend', 'clase', 'groq_max_tokens', '700', 'numero', 'Tokens extendidos para explicaciones de experimento', '2026-03-23 23:39:19'),
(58, 'frontend', 'clase', 'groq_temperature', '0.4', 'numero', 'Precisión científica para guía de experimento', '2026-03-23 23:39:19'),
(59, 'frontend', 'kit', 'prompt_sistema', 'Eres Clase de CiencIA, asistente técnico de kits educativos. El estudiante está viendo un kit de ciencia. Explica qué hace cada componente, cómo se ensamblan y para qué sirve el kit. Usa los datos del kit disponibles en el contexto. Menciona precauciones de seguridad cuando corresponda. Sé directo y concreto: no hagas múltiples preguntas al mismo tiempo. Responde en español colombiano.', 'texto', 'Prompt técnico — vista de kit', '2026-03-23 23:48:46'),
(60, 'frontend', 'kit', 'groq_max_tokens', '500', 'numero', 'Tokens medios para descripción de kit', '2026-03-23 23:48:46'),
(61, 'frontend', 'componente', 'prompt_sistema', 'Eres Clase de CiencIA. El estudiante consulta un componente de kit. Explica qué es, cómo funciona, en qué experimentos se usa y qué precauciones debe tener al manipularlo. Usa los datos del componente disponibles en el contexto. Sé breve y preciso. Responde en español colombiano.', 'texto', 'Prompt específico — vista de componente', '2026-03-23 23:39:19'),
(62, 'frontend', 'componente', 'groq_max_tokens', '400', 'numero', 'Tokens cortos para consultas de componente', '2026-03-23 23:39:19'),
(63, 'frontend', 'manual', 'prompt_sistema', 'Eres Clase de CiencIA, guía de armado y uso. El estudiante está siguiendo un manual. Aclara dudas sobre los pasos: explica por qué se realiza cada uno, qué puede salir mal y cómo corregirlo. Usa los pasos del manual disponibles en el contexto. Si el estudiante pregunta por un paso específico, ubícalo en la secuencia y explica su propósito. Responde en español colombiano.', 'texto', 'Prompt guía-de-pasos — modo manual', '2026-03-23 23:39:19'),
(64, 'frontend', 'manual', 'groq_max_tokens', '600', 'numero', 'Tokens extendidos para aclaraciones de pasos', '2026-03-23 23:39:19'),
(65, 'frontend', 'manual', 'groq_temperature', '0.3', 'numero', 'Temperatura baja para instrucciones precisas', '2026-03-23 23:39:19'),
(66, 'frontend', 'inicio', 'prompt_sistema', 'Eres Clase de CiencIA, el asistente de bienvenida de clasedeciencia.com. Orienta al estudiante: pregunta su grado o interés, recomienda una clase o kit concreto según lo que exprese, y anímalo a explorar. Sé amigable y conciso. Haz solo UNA pregunta de seguimiento por turno. No expliques toda la plataforma de golpe. Responde en español colombiano.', 'texto', 'Prompt orientador — página de inicio', '2026-03-23 23:39:19'),
(67, 'frontend', 'inicio', 'groq_max_tokens', '350', 'numero', 'Respuestas cortas para bienvenida', '2026-03-23 23:39:19'),
(68, 'frontend', 'inicio', 'groq_temperature', '0.6', 'numero', 'Tono más dinámico para descubrimiento', '2026-03-23 23:39:19'),
(69, 'frontend', 'catalogo', 'prompt_sistema', 'Eres Clase de CiencIA. El estudiante navega el catálogo de clases científicas. Recomienda la clase más adecuada según el interés, grado o área que mencione. Usa nombres concretos de clases cuando tengas datos del catálogo en el contexto. Sé motivador y directo: una recomendación clara, no una lista de preguntas. Responde en español colombiano.', 'texto', 'Prompt recomendador — catálogo de clases', '2026-03-23 23:39:19'),
(70, 'frontend', 'catalogo', 'groq_max_tokens', '450', 'numero', 'Tokens medios para recomendaciones de catálogo', '2026-03-23 23:39:19');

-- --------------------------------------------------------

--
-- Table structure for table `contratos`
--

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `numero` varchar(64) NOT NULL,
  `entidad_contratante` varchar(255) NOT NULL,
  `departamento` varchar(120) NOT NULL,
  `municipio` varchar(120) DEFAULT NULL,
  `valor` decimal(16,2) NOT NULL,
  `valor_ejecutado` decimal(16,2) NOT NULL DEFAULT 0.00,
  `estado_contrato` enum('borrador','vigente','suspendido','finalizado','cerrado') NOT NULL DEFAULT 'borrador',
  `supervisor` varchar(180) DEFAULT NULL,
  `objeto_contrato` text DEFAULT NULL,
  `contrato_pdf` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha` date DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `codigo_entrega` varchar(64) DEFAULT NULL,
  `contrato_id` int(11) NOT NULL,
  `institucion_educativa` varchar(255) NOT NULL,
  `departamento` varchar(120) DEFAULT NULL,
  `municipio` varchar(120) DEFAULT NULL,
  `fecha` date NOT NULL,
  `fecha_programada` date DEFAULT NULL,
  `estado_entrega` enum('programada','en_transito','entregada','rechazada','reprogramada') NOT NULL DEFAULT 'programada',
  `responsable_entrega` varchar(180) DEFAULT NULL,
  `responsable_recepcion` varchar(180) DEFAULT NULL,
  `cantidad_kits` int(11) NOT NULL DEFAULT 0,
  `recibido_ok` tinyint(1) NOT NULL DEFAULT 0,
  `novedad` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `acta_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entrega_lotes`
--

CREATE TABLE `entrega_lotes` (
  `entrega_id` int(11) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `cantidad_asignada` int(11) NOT NULL DEFAULT 0,
  `cantidad_entregada` int(11) NOT NULL DEFAULT 0,
  `observaciones` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `footer_enlaces`
--

CREATE TABLE `footer_enlaces` (
  `id` int(10) UNSIGNED NOT NULL,
  `grupo_id` int(10) UNSIGNED NOT NULL,
  `etiqueta` varchar(120) NOT NULL,
  `url` varchar(512) NOT NULL,
  `externo` tinyint(1) NOT NULL DEFAULT 0,
  `orden` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `footer_enlaces`
--

INSERT INTO `footer_enlaces` (`id`, `grupo_id`, `etiqueta`, `url`, `externo`, `orden`, `activo`) VALUES
(1, 1, 'Inicio', '/', 0, 1, 1),
(2, 1, 'Clases', '/clases', 0, 2, 1),
(3, 1, 'Kits', '/kits', 0, 3, 1),
(4, 1, 'Componentes', '/componentes', 0, 4, 1),
(5, 1, 'Manuales', '/manuales.php', 0, 5, 1),
(6, 1, 'Contacto', '/contact.php', 0, 6, 1),
(7, 2, 'Sobre Nosotros', '/sobre-nosotros.php', 0, 1, 1),
(8, 2, 'Términos de Uso', '/terms.php', 0, 2, 1),
(9, 2, 'Política de Privacidad', '/privacy.php', 0, 3, 1),
(10, 3, 'UNESCO - Educación', 'https://www.unesco.org/es/education', 1, 1, 1),
(11, 3, 'Ministerio de Educación (Colombia)', 'https://www.mineducacion.gov.co/', 1, 2, 1),
(12, 3, 'IBE-UNESCO', 'https://www.ibe.unesco.org/', 1, 3, 1),
(13, 3, 'OEA - Educación', 'https://www.oas.org/es/sedi/dde/', 1, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `footer_grupos`
--

CREATE TABLE `footer_grupos` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(80) NOT NULL,
  `orden` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `footer_grupos`
--

INSERT INTO `footer_grupos` (`id`, `titulo`, `orden`, `activo`) VALUES
(1, 'Enlaces Rápidos', 1, 1),
(2, 'Información', 2, 1),
(3, 'Páginas de Interés', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `guias`
--

CREATE TABLE `guias` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `pasos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pasos`)),
  `explicacion_cientifica` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guias`
--

INSERT INTO `guias` (`id`, `clase_id`, `pasos`, `explicacion_cientifica`, `created_at`, `updated_at`) VALUES
(1, 1, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(2, 2, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(3, 3, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(4, 4, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(5, 5, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(6, 6, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(7, 7, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(8, 8, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(9, 9, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(10, 10, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(11, 11, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(12, 12, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(13, 13, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(14, 14, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(15, 15, '[{\"titulo\": \"Preparación\", \"detalle\": \"Revisa materiales y normas de seguridad.\"}, {\"titulo\": \"Construcción\", \"detalle\": \"Sigue la guía para armar el sistema.\"}, {\"titulo\": \"Observación\", \"detalle\": \"Registra resultados y comportamientos.\"}, {\"titulo\": \"Análisis\", \"detalle\": \"Responde preguntas guiadas y explica el fenómeno.\"}]', 'Relación directa con los conceptos clave del portafolio.', '2025-12-20 04:46:28', '2025-12-20 04:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `ia_guardrails_log`
--

CREATE TABLE `ia_guardrails_log` (
  `id` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `clase_id` int(11) DEFAULT NULL,
  `pregunta_usuario` text NOT NULL,
  `palabra_detectada` varchar(255) NOT NULL,
  `tipo_alerta` enum('peligro','advertencia','info') DEFAULT 'peligro',
  `respuesta_dada` text DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `instancia` enum('frontend','backend') NOT NULL DEFAULT 'frontend'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ia_logs`
--

CREATE TABLE `ia_logs` (
  `id` bigint(20) NOT NULL,
  `sesion_id` int(11) DEFAULT NULL,
  `clase_id` int(11) DEFAULT NULL,
  `tipo_evento` enum('consulta','respuesta','error','guardrail_activado','timeout') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tokens_usados` int(11) DEFAULT 0,
  `tiempo_respuesta_ms` int(11) DEFAULT NULL,
  `modelo_usado` varchar(100) DEFAULT NULL,
  `costo_estimado` decimal(10,6) DEFAULT NULL COMMENT 'Costo en USD',
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `instancia` enum('frontend','backend') NOT NULL DEFAULT 'frontend'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ia_logs`
--

INSERT INTO `ia_logs` (`id`, `sesion_id`, `clase_id`, `tipo_evento`, `descripcion`, `tokens_usados`, `tiempo_respuesta_ms`, `modelo_usado`, `costo_estimado`, `fecha_hora`, `metadata`, `instancia`) VALUES
(1, 1, 6, 'consulta', 'Consulta Groq', 880, 1085, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:22:42', NULL, 'frontend'),
(2, 1, NULL, 'consulta', 'Consulta Groq', 400, 820, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:23:03', NULL, 'frontend'),
(3, 1, NULL, 'respuesta', NULL, 423, 1529, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:23:05', NULL, 'frontend'),
(4, 1, NULL, 'consulta', 'Consulta Groq', 420, 1078, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:24:01', NULL, 'frontend'),
(5, 1, NULL, 'respuesta', NULL, 486, 1805, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:24:03', NULL, 'frontend'),
(6, 1, 1, 'consulta', 'Consulta Groq', 835, 1059, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:25:33', NULL, 'frontend'),
(7, NULL, 1, 'consulta', 'Respuesta desde caché', 0, NULL, NULL, 0.000000, '2026-03-21 01:25:33', NULL, 'frontend'),
(8, 1, 1, 'respuesta', NULL, 0, 0, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:25:33', NULL, 'frontend'),
(9, 1, NULL, 'consulta', 'Consulta Groq', 380, 694, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:29:44', NULL, 'frontend'),
(10, 1, NULL, 'respuesta', NULL, 470, 1756, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:29:45', NULL, 'frontend'),
(11, NULL, NULL, 'consulta', 'Consulta Groq', 266, 2987, 'openai/gpt-oss-20b', 0.000000, '2026-03-21 01:31:04', NULL, 'backend'),
(12, 1, NULL, 'respuesta', NULL, 117, 535, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:31:04', NULL, 'frontend'),
(13, NULL, NULL, 'consulta', 'Consulta Groq', 281, 153, 'openai/gpt-oss-20b', 0.000000, '2026-03-21 01:32:57', NULL, 'backend'),
(14, 1, NULL, 'respuesta', NULL, 86, 158, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:32:57', NULL, 'frontend'),
(15, NULL, NULL, 'consulta', 'Consulta Groq', 281, 192, 'openai/gpt-oss-20b', 0.000000, '2026-03-21 01:39:19', NULL, 'backend'),
(16, 1, NULL, 'consulta', 'Consulta Groq', 383, 1083, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:39:23', NULL, 'frontend'),
(17, 1, 6, 'consulta', 'Consulta Groq', 872, 914, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:39:36', NULL, 'frontend'),
(18, 1, 6, 'consulta', 'Consulta Groq', 1063, 1356, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:51:34', NULL, 'frontend'),
(19, 2, NULL, 'consulta', 'Consulta Groq', 472, 996, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:55:00', NULL, 'frontend'),
(20, 2, NULL, 'consulta', 'Consulta Groq', 450, 1406, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:55:14', NULL, 'frontend'),
(21, 2, NULL, 'consulta', 'Consulta Groq', 552, 3684, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 01:55:35', NULL, 'frontend'),
(22, 1, NULL, 'consulta', 'Consulta Groq', 470, 1532, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:05:57', NULL, 'frontend'),
(23, 1, NULL, 'consulta', 'Consulta Groq', 414, 1035, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:06:10', NULL, 'frontend'),
(24, 2, NULL, 'consulta', 'Consulta Groq', 277, 509, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:06:56', NULL, 'frontend'),
(25, 2, NULL, 'consulta', 'Consulta Groq', 553, 1752, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:07:12', NULL, 'frontend'),
(26, 1, NULL, 'consulta', 'Consulta Groq', 543, 1730, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:15:55', NULL, 'frontend'),
(27, 1, NULL, 'consulta', 'Consulta Groq', 430, 1335, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:17:17', NULL, 'frontend'),
(28, 1, NULL, 'consulta', 'Consulta Groq', 421, 1243, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:19:25', NULL, 'frontend'),
(29, 1, NULL, 'consulta', 'Consulta Groq', 448, 1618, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:20:46', NULL, 'frontend'),
(30, 1, NULL, 'consulta', 'Consulta Groq', 319, 479, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:23:51', NULL, 'frontend'),
(31, 1, NULL, 'consulta', 'Consulta Groq', 361, 748, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:28:29', NULL, 'frontend'),
(32, 1, NULL, 'consulta', 'Consulta Groq', 569, 809, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:28:54', NULL, 'frontend'),
(33, 1, NULL, 'consulta', 'Consulta Groq', 868, 826, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:29:05', NULL, 'frontend'),
(34, 1, NULL, 'consulta', 'Consulta Groq', 455, 1223, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:43:29', NULL, 'frontend'),
(35, 1, 6, 'consulta', 'Consulta Groq', 1143, 815, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:43:52', NULL, 'frontend'),
(36, 1, 6, 'consulta', 'Consulta Groq', 1546, 1133, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:44:07', NULL, 'frontend'),
(37, 1, 6, 'consulta', 'Consulta Groq', 1652, 928, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:44:31', NULL, 'frontend'),
(38, 1, 6, 'consulta', 'Consulta Groq', 2046, 1560, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 02:44:44', NULL, 'frontend'),
(39, 2, NULL, 'consulta', 'Consulta Groq', 368, 631, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 03:14:32', NULL, 'frontend'),
(40, 2, NULL, 'consulta', 'Consulta Groq', 436, 950, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 17:07:35', NULL, 'frontend'),
(41, 2, NULL, 'consulta', 'Consulta Groq', 718, 944, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 17:08:12', NULL, 'frontend'),
(42, 2, NULL, 'consulta', 'Consulta Groq', 1097, 1426, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 17:08:55', NULL, 'frontend'),
(43, 2, NULL, 'consulta', 'Consulta Groq', 1168, 798, 'llama-3.3-70b-versatile', 0.000000, '2026-03-21 17:09:15', NULL, 'frontend'),
(44, 1, NULL, 'consulta', 'Consulta Groq', 474, 1751, 'llama-3.3-70b-versatile', 0.000000, '2026-03-23 23:29:27', NULL, 'frontend'),
(45, 1, NULL, 'consulta', 'Consulta Groq', 784, 1302, 'llama-3.3-70b-versatile', 0.000000, '2026-03-23 23:29:44', NULL, 'frontend'),
(46, 1, NULL, 'consulta', 'Consulta Groq', 258, 680, 'llama-3.3-70b-versatile', 0.000000, '2026-03-23 23:49:11', NULL, 'frontend'),
(47, 1, NULL, 'consulta', 'Consulta Groq', 383, 697, 'llama-3.3-70b-versatile', 0.000000, '2026-03-23 23:49:26', NULL, 'frontend'),
(48, 1, NULL, 'consulta', 'Consulta Groq', 227, 403, 'llama-3.3-70b-versatile', 0.000000, '2026-03-23 23:52:48', NULL, 'frontend'),
(49, 1, NULL, 'consulta', 'Consulta Groq', 322, 602, 'llama-3.3-70b-versatile', 0.000000, '2026-03-23 23:59:44', NULL, 'frontend'),
(50, 1, NULL, 'consulta', 'Consulta Groq', 478, 627, 'llama-3.3-70b-versatile', 0.000000, '2026-03-23 23:59:58', NULL, 'frontend'),
(51, 1, NULL, 'consulta', 'Consulta Groq', 577, 574, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:00:04', NULL, 'frontend'),
(52, 1, NULL, 'consulta', 'Consulta Groq', 341, 650, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:02:48', NULL, 'frontend'),
(53, 1, NULL, 'consulta', 'Consulta Groq', 447, 536, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:04:06', NULL, 'frontend'),
(54, 1, NULL, 'consulta', 'Consulta Groq', 532, 583, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:04:16', NULL, 'frontend'),
(55, 1, NULL, 'consulta', 'Consulta Groq', 605, 533, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:04:19', NULL, 'frontend'),
(56, 1, NULL, 'consulta', 'Consulta Groq', 343, 553, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:12:02', NULL, 'frontend'),
(57, 1, 7, 'consulta', 'Consulta Groq', 869, 621, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:12:15', NULL, 'frontend'),
(58, 1, NULL, 'consulta', 'Consulta Groq', 360, 476, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:15:47', NULL, 'frontend'),
(59, 1, NULL, 'consulta', 'Consulta Groq', 437, 533, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:16:01', NULL, 'frontend'),
(60, 1, NULL, 'consulta', 'Consulta Groq', 300, 462, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:26:23', NULL, 'frontend'),
(61, 1, NULL, 'consulta', 'Consulta Groq', 369, 379, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:26:28', NULL, 'frontend'),
(62, 1, NULL, 'consulta', 'Consulta Groq', 422, 474, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:26:33', NULL, 'frontend'),
(63, 1, NULL, 'consulta', 'Consulta Groq', 469, 306, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:26:36', NULL, 'frontend'),
(64, 1, NULL, 'consulta', 'Consulta Groq', 526, 461, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:26:43', NULL, 'frontend'),
(65, 1, NULL, 'consulta', 'Consulta Groq', 581, 246, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:26:46', NULL, 'frontend'),
(66, 1, NULL, 'consulta', 'Consulta Groq', 650, 309, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:26:52', NULL, 'frontend'),
(67, 1, NULL, 'consulta', 'Consulta Groq', 639, 478, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:26:56', NULL, 'frontend'),
(68, 1, NULL, 'consulta', 'Consulta Groq', 646, 433, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:27:01', NULL, 'frontend'),
(69, 1, NULL, 'consulta', 'Consulta Groq', 913, 1255, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:27:08', NULL, 'frontend'),
(70, 1, NULL, 'consulta', 'Consulta Groq', 872, 567, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:27:36', NULL, 'frontend'),
(71, 1, NULL, 'consulta', 'Consulta Groq', 429, 450, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:32:58', NULL, 'frontend'),
(72, 1, NULL, 'consulta', 'Consulta Groq', 496, 651, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:33:04', NULL, 'frontend'),
(73, 1, NULL, 'consulta', 'Consulta Groq', 558, 489, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:33:12', NULL, 'frontend'),
(74, 1, NULL, 'consulta', 'Consulta Groq', 619, 482, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:33:23', NULL, 'frontend'),
(75, 1, NULL, 'consulta', 'Consulta Groq', 425, 340, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:44:21', NULL, 'frontend'),
(76, 1, NULL, 'consulta', 'Consulta Groq', 503, 496, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:44:28', NULL, 'frontend'),
(77, 1, NULL, 'consulta', 'Consulta Groq', 557, 338, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:44:33', NULL, 'frontend'),
(78, 1, NULL, 'consulta', 'Consulta Groq', 632, 519, 'llama-3.3-70b-versatile', 0.000000, '2026-03-24 00:44:51', NULL, 'frontend'),
(79, NULL, NULL, 'consulta', 'Consulta Groq', 276, 1161, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 00:23:43', NULL, 'backend'),
(80, NULL, NULL, 'consulta', 'Consulta Groq', 677, 374, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 00:33:24', NULL, 'backend'),
(81, 3, NULL, 'consulta', 'Consulta Groq', 958, 582, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 00:44:39', NULL, 'backend'),
(82, 3, NULL, 'consulta', 'Consulta Groq', 752, 225, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 00:45:06', NULL, 'backend'),
(83, 3, NULL, 'consulta', 'Consulta Groq', 842, 245, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 00:46:30', NULL, 'backend'),
(84, 3, NULL, 'consulta', 'Consulta Groq', 1095, 287, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 00:48:55', NULL, 'backend'),
(85, 3, NULL, 'consulta', 'Consulta Groq', 1191, 613, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 00:49:16', NULL, 'backend'),
(86, 3, NULL, 'consulta', 'Consulta Groq', 3004, 1158, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 00:57:33', NULL, 'backend'),
(87, 3, NULL, 'consulta', 'Consulta Groq', 3482, 1093, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 01:03:47', NULL, 'backend'),
(88, 3, NULL, 'consulta', 'Consulta Groq', 2254, 434, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 01:04:11', NULL, 'backend'),
(89, 3, NULL, 'consulta', 'Consulta Groq', 2581, 368, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 01:04:33', NULL, 'backend'),
(90, 3, NULL, 'consulta', 'Consulta Groq', 2880, 844, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 01:12:56', NULL, 'backend'),
(91, 3, NULL, 'consulta', 'Consulta Groq', 2882, 492, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 01:13:11', NULL, 'backend'),
(92, 3, NULL, 'consulta', 'Consulta Groq', 0, 0, 'deterministic-backend:manuales_estado', 0.000000, '2026-03-25 01:14:55', NULL, 'backend'),
(93, 3, NULL, 'consulta', 'Consulta Groq', 2661, 575, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 01:15:01', NULL, 'backend'),
(94, 3, NULL, 'consulta', 'Consulta Groq', 2619, 381, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 01:15:09', NULL, 'backend'),
(95, 3, NULL, 'consulta', 'Consulta Groq', 3009, 412, 'openai/gpt-oss-20b', 0.000000, '2026-03-25 01:15:27', NULL, 'backend'),
(96, 4, NULL, 'consulta', 'Consulta Groq', 700, 1052, 'llama-3.3-70b-versatile', 0.000000, '2026-03-25 19:15:39', NULL, 'frontend'),
(97, 4, NULL, 'consulta', 'Consulta Groq', 449, 397, 'llama-3.3-70b-versatile', 0.000000, '2026-03-25 19:20:44', NULL, 'frontend'),
(98, 4, NULL, 'consulta', 'Consulta Groq', 522, 450, 'llama-3.3-70b-versatile', 0.000000, '2026-03-25 19:20:53', NULL, 'frontend'),
(99, 4, NULL, 'consulta', 'Consulta Groq', 724, 767, 'llama-3.3-70b-versatile', 0.000000, '2026-03-25 19:20:58', NULL, 'frontend'),
(100, 2, NULL, 'consulta', 'Consulta Groq', 437, 441, 'llama-3.3-70b-versatile', 0.000000, '2026-04-11 15:35:06', NULL, 'frontend'),
(101, 2, NULL, 'consulta', 'Consulta Groq', 503, 577, 'llama-3.3-70b-versatile', 0.000000, '2026-04-11 15:35:17', NULL, 'frontend'),
(102, 2, NULL, 'consulta', 'Consulta Groq', 542, 306, 'llama-3.3-70b-versatile', 0.000000, '2026-04-11 15:35:27', NULL, 'frontend');

-- --------------------------------------------------------

--
-- Table structure for table `ia_mensajes`
--

CREATE TABLE `ia_mensajes` (
  `id` bigint(20) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `rol` enum('user','assistant','system') NOT NULL,
  `contenido` text NOT NULL,
  `tokens` int(11) DEFAULT 0,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `instancia` enum('frontend','backend') NOT NULL DEFAULT 'frontend'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ia_mensajes`
--

INSERT INTO `ia_mensajes` (`id`, `sesion_id`, `rol`, `contenido`, `tokens`, `fecha_hora`, `metadata`, `instancia`) VALUES
(1, 1, 'user', '¿Qué materiales necesito para este experimento?', 0, '2026-03-21 01:23:05', '{\"timestamp\": \"2026-03-21 01:23:05\"}', 'frontend'),
(2, 1, 'assistant', 'Para proporcionar una respuesta precisa, necesitaría saber a qué experimento te refieres. Sin embargo, puedo ofrecerte una guía general sobre cómo identificar los materiales necesarios para un experimento científico.\n\n1. **Lee detenidamente el protocolo del experimento**: La mayoría de los experimentos científicos vienen con un protocolo o una guía que detalla los pasos a seguir y los materiales necesarios.\n\n2. **Identifica los materiales básicos**: Dependiendo del tipo de experimento, es posible que necesites materiales básicos como tubos de ensayo, un microscopio, una balanza, o equipos de seguridad como guantes y gafas de seguridad.\n\n3. **Materiales específicos del experimento**: Algunos experimentos requieren materiales específicos relacionados con el tema de estudio, como sustancias químicas, muestras biológicas, o componentes electrónicos.\n\n4. **Herramientas y equipo**: Asegúrate de tener acceso a las herramientas y al equipo necesario para manipular y medir los materiales durante el experimento.\n\n5. **Verifica la disponibilidad**: Antes de comenzar, verifica que tengas acceso a todos los materiales necesarios. Si algunos materiales no están disponibles, considera alternativas o busca formas de adquirirlos.\n\n6. **Seguridad**: Asegúrate de seguir todas las medidas de seguridad recomendadas para el manejo de los materiales y el equipo que vas a utilizar.\n\nSi me proporcionas más detalles sobre el experimento específico que estás planeando realizar, podré darte una lista más precisa de los materiales necesarios.', 423, '2026-03-21 01:23:05', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(3, 1, 'user', '¿Qué materiales necesito para este experimento?', 0, '2026-03-21 01:24:03', '{\"timestamp\": \"2026-03-21 01:24:03\"}', 'frontend'),
(4, 1, 'assistant', 'Para proporcionar una respuesta precisa, necesitaría saber a qué experimento te refieres. Sin embargo, puedo ofrecerte una guía general sobre cómo identificar los materiales necesarios para un experimento científico.\n\n1. **Lee la descripción del experimento**: La mayoría de las guías de experimentos incluyen una lista de materiales necesarios al principio o al final de la descripción.\n\n2. **Identifica el propósito del experimento**: Entender qué se intenta demostrar o investigar puede ayudarte a deducir qué materiales serán necesarios. Por ejemplo, si es un experimento químico, probablemente necesitarás sustancias químicas específicas, equipo de laboratorio como tubos de ensayo, y posiblemente equipo de protección personal.\n\n3. **Considera el entorno y la seguridad**: Algunos experimentos requieren un espacio específico o condiciones ambientales particulares. También es crucial considerar el equipo de seguridad necesario, como guantes, gafas de seguridad, o un delantal, dependiendo de la naturaleza del experimento.\n\n4. **Busca en línea o en libros de texto**: Si no tienes acceso a la descripción detallada del experimento, puedes buscar en línea o consultar libros de texto relacionados con el tema. Muchos sitios web educativos y recursos científicos ofrecen listas de materiales necesarios para una amplia variedad de experimentos.\n\n5. **Prepara un kit de experimentos básico**: Si planeas realizar experimentos con frecuencia, considera preparar un kit de experimentos básico que incluya materiales comunes como cinta adhesiva, tijeras, papel, lápices, una regla, un cronómetro, y cualquier otro material que se utilice con frecuencia en experimentos generales.\n\nSi me proporcionas más detalles sobre el experimento específico que estás planeando, podría ofrecerte una lista más precisa de los materiales necesarios.', 486, '2026-03-21 01:24:03', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(5, 1, 'user', '¿Qué materiales necesito para este experimento?', 0, '2026-03-21 01:25:33', '{\"timestamp\": \"2026-03-21 01:25:33\"}', 'frontend'),
(6, 1, 'assistant', 'Excelente pregunta! Antes de empezar, es importante recordar las normas de seguridad básicas de laboratorio. ¿Recuerdas cuáles son algunas de las normas más importantes que debemos seguir en un entorno experimental?\n\nAhora, veamos los materiales que necesitarás para construir tu microscopio sencillo. Según la guía, necesitarás:\n\n* 2 lentes plásticas de 10x\n* 1 cartón rígido\n\n¿Puedes pensar en por qué se utilizan lentes plásticas en lugar de vidrio? ¿Y qué papel crees que juega el cartón rígido en la construcción del microscopio?\n\nRecuerda que es importante leer la guía cuidadosamente y seguir las instrucciones paso a paso para asegurarte de que tengas todo lo que necesitas para completar el experimento de manera segura y efectiva. ¿Quieres seguir adelante y explorar cómo se ensamblan estos materiales para crear un microscopio sencillo?', 0, '2026-03-21 01:25:33', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(7, 1, 'user', '¿Qué materiales necesito para este experimento?', 0, '2026-03-21 01:29:45', '{\"timestamp\": \"2026-03-21 01:29:45\"}', 'frontend'),
(8, 1, 'assistant', 'Para proporcionar una respuesta precisa, necesitaría saber a qué experimento te refieres. Sin embargo, puedo ofrecerte una guía general sobre cómo identificar los materiales necesarios para un experimento científico.\n\n1. **Lee detenidamente el protocolo del experimento**: La mayoría de los experimentos científicos incluyen una lista de materiales necesarios. Asegúrate de leer cuidadosamente las instrucciones para entender qué materiales se requieren.\n\n2. **Identifica el tipo de experimento**: Dependiendo del tipo de experimento (químico, biológico, físico, etc.), los materiales necesarios pueden variar significativamente. Por ejemplo, un experimento químico puede requerir sustancias químicas, mientras que un experimento biológico puede necesitar equipo de laboratorio específico como microscopios o incubadoras.\n\n3. **Considera el entorno y la seguridad**: Asegúrate de tener el equipo de seguridad adecuado, como guantes, gafas de seguridad, y un delantal, especialmente si el experimento involucra sustancias químicas o procedimientos que puedan ser peligrosos.\n\n4. **Verifica la disponibilidad de los materiales**: Una vez que tengas la lista de materiales, verifica si tienes acceso a ellos. Algunos materiales pueden ser comunes y fáciles de encontrar, mientras que otros pueden requerir una compra especializada o incluso la solicitud a un proveedor específico.\n\n5. **Prepara un espacio de trabajo adecuado**: Asegúrate de que tu espacio de trabajo esté limpio, bien ventilado, y libre de distracciones. Esto es crucial para la seguridad y el éxito del experimento.\n\nSi me proporcionas más detalles sobre el experimento específico que estás planeando realizar, podré ofrecerte una lista más precisa de los materiales necesarios.', 470, '2026-03-21 01:29:45', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(9, 1, 'user', '¿Cuántas clases activas hay?', 0, '2026-03-21 01:31:04', '{\"timestamp\": \"2026-03-21 01:31:04\"}', 'frontend'),
(10, 1, 'assistant', 'Lo siento, pero no tengo información específica sobre el número de clases activas en este momento. La información proporcionada no incluye detalles sobre clases activas. Si necesitas ayuda con algo más o tienes más detalles para proporcionar, estaré encantado de ayudarte.', 117, '2026-03-21 01:31:04', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(11, 1, 'user', '¿Cuántas clases activas hay?', 0, '2026-03-21 01:32:57', '{\"timestamp\": \"2026-03-21 01:32:57\"}', 'frontend'),
(12, 1, 'assistant', 'Lo siento, pero no tengo información sobre el número de clases activas. ¿Hay algo más en lo que pueda ayudarte?', 86, '2026-03-21 01:32:57', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(13, 3, 'user', 'que clase es la mas completa', 0, '2026-03-25 00:44:39', '{\"timestamp\": \"2026-03-25 00:44:39\"}', 'backend'),
(14, 3, 'assistant', 'No dispongo de métricas de profundidad o número de sub‑temas por clase.  \nEntre las clases publicadas y activas, la que suele considerarse más completa es **“Análisis químico del entorno”** (Ciclo 3), porque abarca la evaluación de varios parámetros ambientales (pH, turbidez, contaminantes, etc.) y suele incluir procedimientos de muestreo, análisis y reporte.  \n\nSi necesitas comparar objetivamente las clases, sería útil contar con datos de duración, número de actividades o evaluaciones.', 958, '2026-03-25 00:44:39', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(15, 3, 'user', 'me refiero con kit, manuales, componentes bien descritos', 0, '2026-03-25 00:45:06', '{\"timestamp\": \"2026-03-25 00:45:06\"}', 'backend'),
(16, 3, 'assistant', 'No dispongo de información sobre los kits, manuales o componentes asociados a cada clase.  \nPara determinar cuál es la más completa en ese sentido, revisa el inventario de kits y la documentación técnica de cada curso; la clase que cuente con el mayor número de piezas, instrucciones detalladas y materiales listados será la más completa.', 752, '2026-03-25 00:45:06', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(17, 3, 'user', 'revisa', 0, '2026-03-25 00:46:30', '{\"timestamp\": \"2026-03-25 00:46:30\"}', 'backend'),
(18, 3, 'assistant', 'No dispongo de datos sobre kits, manuales o componentes asociados a cada clase.  \nPara poder revisar cuál es la más completa en ese aspecto, necesitaría la información de inventario de kits, la lista de componentes y los manuales de cada curso. Si me proporcionas esos datos, podré hacer el análisis solicitado.', 842, '2026-03-25 00:46:30', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(19, 3, 'user', 'cual kit tiene el mayor numero de clases', 0, '2026-03-25 00:48:55', '{\"timestamp\": \"2026-03-25 00:48:55\"}', 'backend'),
(20, 3, 'assistant', 'En la lista que me has proporcionado, cada kit está asociado a **una sola clase**.  \nPor lo tanto, **todos los kits tienen el mismo número de clases (1)**.', 1095, '2026-03-25 00:48:55', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(21, 3, 'user', 'cual clase es l;a mas completa en relacion a manuales, kits componentes', 0, '2026-03-25 00:49:16', '{\"timestamp\": \"2026-03-25 00:49:16\"}', 'backend'),
(22, 3, 'assistant', 'Según la información de kits que me has proporcionado, la **clase “Carro solar”** es la que cuenta con más kits asociados (2 kits: *Carro solar* y *Radio de cristal*).  \n\nEn ausencia de datos sobre manuales y componentes, esta es la única métrica que podemos usar para determinar la “completitud” de una clase. Si dispones de los registros de manuales y de los componentes de cada kit, podríamos hacer un análisis más exhaustivo.', 1191, '2026-03-25 00:49:16', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(23, 3, 'user', 'hola cual es la clase mas completa lo que implica numero de kits, componentes, manuales', 0, '2026-03-25 00:57:33', '{\"timestamp\": \"2026-03-25 00:57:33\"}', 'backend'),
(24, 3, 'assistant', 'La clase que presenta la mayor “completitud” en cuanto a **kits, componentes y manuales** es:\n\n| Clase | Kits | Manuales | Componentes | Total (K+M+C) |\n|-------|------|----------|-------------|---------------|\n| **Carro solar** | 2 | 0 | 6 | **8** |\n\n**Por qué es la más completa**\n\n1. **Mayor número de kits** – 2 kits (Carro solar + Radio de cristal).  \n2. **Mayor número de componentes** – 6 piezas (el mayor conteo entre todas las clases).  \n3. **Manual** – No hay manuales publicados para ninguna clase, por lo que el conteo de manuales es 0 en todas.  \n4. **Puntuación de completitud** – 2.1, la más alta de la lista.\n\nEn resumen, **Carro solar** es la clase con la mayor cobertura de recursos (kits + componentes), lo que la convierte en la más completa según los datos disponibles.', 3004, '2026-03-25 00:57:33', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(25, 3, 'user', 'que clase tiene mas elementos es decir kit, manual, componentes, la que almenos tenga uno', 0, '2026-03-25 01:03:47', '{\"timestamp\": \"2026-03-25 01:03:47\"}', 'backend'),
(26, 3, 'assistant', 'Respuesta corta: Carro solar.\n\nEvidencia:\n- Kits: 2\n- Manuales: 0\n- Componentes: 6\n- Total elementos: 8 (máximo entre todas las clases)\n\nSiguiente accion: Si necesitas que se añadan manuales o se actualicen los componentes, indícalo para proceder.', 3482, '2026-03-25 01:03:47', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(27, 3, 'user', 'que hay de el emisor de ondas', 0, '2026-03-25 01:04:11', '{\"timestamp\": \"2026-03-25 01:04:11\"}', 'backend'),
(28, 3, 'assistant', 'Respuesta corta: Emisión de ondas AM (Clase 6).\n\nEvidencia:\n- Ciclo: 4\n- Kits: 1\n- Manuales: 0\n- Componentes: 4\n- Score de completitud: 1.25\n\nSiguiente accion: Si necesitas listar los componentes específicos o planificar su entrega, indícalo.', 2254, '2026-03-25 01:04:11', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(29, 3, 'user', 'hay un manual de un coponente a que pertenece?', 0, '2026-03-25 01:04:33', '{\"timestamp\": \"2026-03-25 01:04:33\"}', 'backend'),
(30, 3, 'assistant', 'Respuesta corta: No existen manuales publicados en el sistema.\n\nEvidencia:\n- Manuales publicados: 0\n\nSiguiente accion: Si necesitas crear o asignar un manual a un componente, indícalo con el nombre del componente y el contenido deseado.', 2581, '2026-03-25 01:04:33', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(31, 3, 'user', 'que manuales tenemos?', 0, '2026-03-25 01:12:56', '{\"timestamp\": \"2026-03-25 01:12:56\"}', 'backend'),
(32, 3, 'assistant', 'Respuesta corta: Existe 1 manual publicado, pero no se indica a qué componente pertenece.\n\nEvidencia:\n- Manuales publicados (DB): 1\n- Manuales publicados por componente: 1\n- Manuales publicados por kit: 0\n- Manuales totales en resumen: 0 (inconsistencia)\n\nSiguiente accion: Proporcione el nombre del componente al que está asociado este manual para poder identificarlo correctamente.', 2880, '2026-03-25 01:12:56', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(33, 3, 'user', 'que manual es dime el nombre', 0, '2026-03-25 01:13:11', '{\"timestamp\": \"2026-03-25 01:13:11\"}', 'backend'),
(34, 3, 'assistant', 'Respuesta corta: No se dispone del nombre del manual.\n\nEvidencia:\n- Manuales publicados (DB): 1\n- No se registra nombre en los datos actuales.\n\nSiguiente accion: Por favor indique el nombre del manual o el componente asociado para poder identificarlo.', 2882, '2026-03-25 01:13:11', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(35, 3, 'user', 'que manuales tenemos?', 0, '2026-03-25 01:14:55', '{\"timestamp\": \"2026-03-25 01:14:55\"}', 'backend'),
(36, 3, 'assistant', 'Respuesta corta: Si hay manuales en el sistema.\nEvidencia:\n- Total: 1\n- Publicados: 1\n- Borrador: 0\n- Archivados/Inactivos: 0\n- Publicados de kit: 0\n- Publicados de componente: 1\nSiguiente accion: Si quieres, te filtro por componente o por kit especifico.', 0, '2026-03-25 01:14:55', '{\"modelo\": \"deterministic-backend:manuales_estado\"}', 'backend'),
(37, 3, 'user', 'Profundiza en tu última respuesta y dame más detalles.', 0, '2026-03-25 01:15:01', '{\"timestamp\": \"2026-03-25 01:15:01\"}', 'backend'),
(38, 3, 'assistant', 'Respuesta corta: Solo existe un manual publicado, asociado a un componente, pero no se dispone de su título ni descripción.\n\nEvidencia:\n- Total manuales: 1\n- Publicados: 1\n- Borrador: 0\n- Archivados/Inactivos: 0\n- Publicados de kit: 0\n- Publicados de componente: 1\n\nSiguiente accion: Indícame el ID o nombre del componente para que pueda mostrarte el manual correspondiente.', 2661, '2026-03-25 01:15:01', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(39, 3, 'user', 'cual es el manual?', 0, '2026-03-25 01:15:09', '{\"timestamp\": \"2026-03-25 01:15:09\"}', 'backend'),
(40, 3, 'assistant', 'Respuesta corta: No dispongo del título ni contenido del manual publicado.\n\nEvidencia:\n- Solo existe 1 manual publicado en el sistema.\n- No se registra título, descripción ni enlace asociado.\n\nSiguiente accion: Proporcióname el ID o nombre del componente al que pertenece el manual para poder localizarlo.', 2619, '2026-03-25 01:15:09', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(41, 3, 'user', 'dime que manual tenemos?', 0, '2026-03-25 01:15:27', '{\"timestamp\": \"2026-03-25 01:15:27\"}', 'backend'),
(42, 3, 'assistant', 'Respuesta corta: No se dispone de información detallada del manual publicado; solo sabemos que existe 1 manual en estado publicado.\n\nEvidencia:\n- Total manuales: 1\n- Publicados: 1\n- Borrador: 0\n- Archivados/Inactivos: 0\n- Publicados de kit: 0\n- Publicados de componente: 1\n\nSiguiente accion: Indícame el ID o nombre del componente al que pertenece el manual para poder localizarlo.', 3009, '2026-03-25 01:15:27', '{\"modelo\": \"openai/gpt-oss-20b\"}', 'backend'),
(43, 4, 'user', 'what is this for', 0, '2026-03-25 19:15:39', '{\"timestamp\": \"2026-03-25 19:15:39\"}', 'frontend'),
(44, 4, 'assistant', 'Este manual parece ser para armar y utilizar un componente electrónico que involucra un diodo de germanio. El propósito del armado y uso de este componente no está explícitamente mencionado, pero considerando los pasos y la mención de una antena y tierra, es probable que se trate de un proyecto relacionado con la recepción o transmisión de señales de radio.\n\nLos pasos mencionados en el manual sugieren que se está construyendo un circuito que involucra una bobina, un condensador (LC) y un diodo, lo que podría ser parte de un receptor de radio o un circuito de detección de señales. El uso de un diodo de germanio específicamente podría indicar que se está trabajando con señales de baja frecuencia o con aplicaciones que requieren una alta sensibilidad.\n\nSi necesitas más información o aclaraciones sobre algún paso específico, puedo intentar ayudarte.\n\nOpciones: preguntar_paso|explicar_componentes|otros', 700, '2026-03-25 19:15:39', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(45, 4, 'user', 'i need a kit for waves emision electromagnetism', 0, '2026-03-25 19:20:44', '{\"timestamp\": \"2026-03-25 19:20:44\"}', 'frontend'),
(46, 4, 'assistant', 'Parece que estás interesado en explorar el fascinante mundo de las ondas electromagnéticas. Aun no tenemos un kit específico sobre emisión de ondas electromagnéticas, pero puedo contarte más sobre ello si te interesa seguir explorando.\n\nOpciones: Fisica|Electromagnetismo|Ondas|Teoria', 449, '2026-03-25 19:20:44', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(47, 4, 'user', 'Electromagnetismo', 0, '2026-03-25 19:20:53', '{\"timestamp\": \"2026-03-25 19:20:53\"}', 'frontend'),
(48, 4, 'assistant', 'El electromagnetismo es un tema fundamental en la física. Aun no tenemos una clase o kit sobre este tema específico, pero puedo contarte más sobre los conceptos básicos de electromagnetismo, como campos eléctricos y magnéticos, si te interesa.\nOpciones: Física|Ingeniería|Electrónica|Otro', 522, '2026-03-25 19:20:53', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(49, 4, 'user', 'Profundiza en tu última respuesta y dame más detalles.', 0, '2026-03-25 19:20:58', '{\"timestamp\": \"2026-03-25 19:20:58\"}', 'frontend'),
(50, 4, 'assistant', 'Claro, con gusto. El electromagnetismo es una rama de la física que estudia la interacción entre cargas eléctricas y campos magnéticos. Incluye conceptos como la electricidad estática, la corriente eléctrica, los campos magnéticos y las ondas electromagnéticas. Estos conceptos son fundamentales para entender cómo funcionan muchos dispositivos y tecnologías que utilizamos a diario, como motores, generadores, radios y teléfonos móviles.\n\nSi estás interesado en aprender más sobre el electromagnetismo, podríamos explorar temas como la ley de Coulomb, la inducción electromagnética de Faraday, o la teoría de Maxwell, que unificó las leyes del electromagnetismo en un conjunto coherente de ecuaciones.\n\nOpciones: Física|Matemáticas|Ingeniería|Otras', 724, '2026-03-25 19:20:58', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(51, 2, 'user', 'Hola que eres', 0, '2026-04-11 15:35:06', '{\"timestamp\": \"2026-04-11 15:35:06\"}', 'frontend'),
(52, 2, 'assistant', 'Hola, soy Clase de CiencIA, tu asistente de bienvenida en clasedeciencia.com. Estoy aquí para ayudarte a encontrar clases y kits interesantes sobre ciencia y tecnología.\n\n¿Cuál es tu grado de estudio o interés? \nOpciones: Primaria|Secundaria|Universidad|Otro', 437, '2026-04-11 15:35:06', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(53, 2, 'user', 'Universidad', 0, '2026-04-11 15:35:17', '{\"timestamp\": \"2026-04-11 15:35:17\"}', 'frontend'),
(54, 2, 'assistant', 'En la universidad, hay muchas áreas interesantes para explorar. Estamos desarrollando contenido sobre ciencias exactas, tecnología y más.\n\n¿Te interesa algo en particular como ingeniería, biología, física o quizás programación? \nOpciones: Ingeniería|Biología|Física|Programación', 503, '2026-04-11 15:35:17', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend'),
(55, 2, 'user', 'Física', 0, '2026-04-11 15:35:27', '{\"timestamp\": \"2026-04-11 15:35:27\"}', 'frontend'),
(56, 2, 'assistant', 'La física es una rama fascinante que nos ayuda a entender el mundo que nos rodea. \n\nOpciones: Mecánica|Electromagnetismo|Termodinámica|Otros', 542, '2026-04-11 15:35:27', '{\"modelo\": \"llama-3.3-70b-versatile\"}', 'frontend');

-- --------------------------------------------------------

--
-- Table structure for table `ia_respuestas_cache`
--

CREATE TABLE `ia_respuestas_cache` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `pregunta_normalizada` varchar(500) NOT NULL COMMENT 'Pregunta sin acentos, lowercase',
  `pregunta_original` text NOT NULL,
  `respuesta` text NOT NULL,
  `veces_usada` int(11) DEFAULT 0,
  `ultima_vez_usada` timestamp NULL DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ia_respuestas_cache`
--

INSERT INTO `ia_respuestas_cache` (`id`, `clase_id`, `pregunta_normalizada`, `pregunta_original`, `respuesta`, `veces_usada`, `ultima_vez_usada`, `activa`, `created_at`) VALUES
(1, 6, 'que es este proyecto?', 'que es este proyecto?', '¡Hola! Este proyecto se trata de la emisión de ondas AM, específicamente de cómo se generan, transmiten y reciben las ondas de radio AM. En este proyecto, exploraremos el sistema emisor-canal-receptor, la modulación en amplitud, la resonancia LC y la detección por diodo.\n\nPero antes de empezar, ¿qué sabes sobre las ondas de radio AM? ¿Has escuchado alguna vez la radio y te has preguntado cómo funcionan las ondas de radio para que podamos escuchar música o programas en vivo?\n\nTambién es importante recordar que, antes de comenzar cualquier experimento, debemos seguir las normas de seguridad básicas de laboratorio, como usar equipo de protección personal, evitar cortocircuitos y no tocar componentes eléctricos con las manos desnudas. ¿Recuerdas algunas de estas normas de seguridad?', 0, NULL, 1, '2026-03-21 01:22:42'),
(2, 1, '¿qué materiales necesito para este experimento?', '¿Qué materiales necesito para este experimento?', 'Excelente pregunta! Antes de empezar, es importante recordar las normas de seguridad básicas de laboratorio. ¿Recuerdas cuáles son algunas de las normas más importantes que debemos seguir en un entorno experimental?\n\nAhora, veamos los materiales que necesitarás para construir tu microscopio sencillo. Según la guía, necesitarás:\n\n* 2 lentes plásticas de 10x\n* 1 cartón rígido\n\n¿Puedes pensar en por qué se utilizan lentes plásticas en lugar de vidrio? ¿Y qué papel crees que juega el cartón rígido en la construcción del microscopio?\n\nRecuerda que es importante leer la guía cuidadosamente y seguir las instrucciones paso a paso para asegurarte de que tengas todo lo que necesitas para completar el experimento de manera segura y efectiva. ¿Quieres seguir adelante y explorar cómo se ensamblan estos materiales para crear un microscopio sencillo?', 1, '2026-03-21 01:25:33', 1, '2026-03-21 01:25:33'),
(3, 6, 'hola que es este proyecto?', 'hola que es este proyecto?', '¡Hola! Este proyecto se llama \"Emisión de ondas AM\" y es parte de una clase de física y tecnología. El objetivo es que comprendas cómo se generan, transmiten y reciben las ondas de radio AM, que son un tipo de onda electromagnética.\n\nPero antes de empezar, ¿qué sabes sobre las ondas de radio? ¿Has escuchado alguna vez la radio y te has preguntado cómo funciona? ¿Qué crees que es necesario para que una radio funcione correctamente?\n\nRecuerda que es importante seguir las normas de seguridad en el laboratorio, como no tocar equipos eléctricos con las manos mojadas y no mirar directamente a las luces intensas. ¿Has trabajado antes en un laboratorio o has seguido normas de seguridad similares?', 0, NULL, 1, '2026-03-21 01:39:36'),
(4, 6, 'como se escucha de donde entra el sonido', 'como se escucha de donde entra el sonido', 'Excelente pregunta! La emisión de ondas AM es un tema fascinante. Para entender cómo se escucha el sonido, debemos considerar el sistema emisor-canal-receptor.\n\nImagina que estás en una radio de cristal, y quieres escuchar tu programa favorito. ¿Qué crees que sucede cuando el sonido de la radio llega a tus oídos?\n\nPiensa en los siguientes pasos:\n\n1. **La señal de audio**: La radio emite una señal de audio, que es la voz o la música que se quiere transmitir.\n2. **La modulación**: La señal de audio se modula en amplitud, lo que significa que se combina con una onda portadora de alta frecuencia.\n3. **La transmisión**: La onda modulada se transmite a través del aire como una onda electromagnética.\n4. **La recepción**: La onda electromagnética llega a la antena de la radio de cristal.\n5. **La detección**: La onda electromagnética se detecta y se convierte en una señal de audio nuevamente.\n\nAhora, pregúntate: ¿Qué componente de la radio de cristal es responsable de convertir la onda electromagnética en una señal de audio que puedes escuchar?\n\n¿Es el circuito resonante LC, el diodo detector o el auricular? ¿O es una combinación de todos ellos?\n\nRecuerda que, antes de realizar cualquier experimento, debes seguir las normas de seguridad básicas de laboratorio y seguridad eléctrica. ¿Qué medidas de seguridad crees que debes tomar al trabajar con radios de cristal?', 0, NULL, 1, '2026-03-21 01:51:34'),
(5, 6, 'ok ya estamos aqui entonces que es este kit', 'ok ya estamos aqui entonces que es este kit', '¡Excelente! Ahora que estamos listos para empezar, veamos qué componentes tiene el kit de radio de cristal que tienes.\n\n¿Puedes describirme los componentes que ves en el kit? ¿Hay un cristal de galena o cuarzo, un diodo, un condensador, un resistor, un auricular, una antena y un circuito impreso?\n\n¿Sabes qué función cumple cada uno de estos componentes en el radio de cristal? Por ejemplo, ¿para qué se utiliza el cristal de galena o cuarzo? ¿Y qué papel juega el diodo en el circuito?\n\nVamos a explorar juntos cada uno de estos componentes y cómo se relacionan entre sí para crear un radio de cristal funcional.\n\n¿Qué te parece si empezamos por el cristal de galena o cuarzo? ¿Sabes qué es lo que hace que este cristal sea especial y cómo se utiliza en el radio de cristal?', 0, NULL, 1, '2026-03-21 02:43:52'),
(6, 6, '¿hay un cristal de galena o cuarzo, un diodo, un condensador, un resistor, un auricular, una antena y un circuito impreso?', '¿Hay un cristal de galena o cuarzo, un diodo, un condensador, un resistor, un auricular, una antena y un circuito impreso?', '¡Exacto! Parece que tienes un kit de radio de cristal completo.\n\nAhora, vamos a analizar cada uno de estos componentes y su función en el radio de cristal.\n\n* El cristal de galena o cuarzo es el corazón del radio de cristal. ¿Sabes qué propiedad tiene este cristal que lo hace útil para detectar señales de radio?\n* El diodo es un componente que ayuda a \"rectificar\" la señal de radio. ¿Qué significa \"rectificar\" en este contexto?\n* El condensador es un componente que almacena energía eléctrica. ¿Cómo se utiliza el condensador en el radio de cristal para mejorar la recepción de señales?\n* El resistor es un componente que controla el flujo de corriente eléctrica. ¿Qué papel juega el resistor en el radio de cristal?\n* El auricular es el componente que convierte la señal de radio en sonido. ¿Cómo funciona el auricular para que podamos escuchar la radio?\n* La antena es el componente que recibe las señales de radio del aire. ¿Qué tipo de antena se utiliza comúnmente en los radios de cristal?\n* El circuito impreso es el \"esqueleto\" del radio de cristal, que conecta todos los componentes entre sí. ¿Qué papel juega el circuito impreso en la funcionalidad del radio de cristal?\n\nVamos a explorar cada uno de estos componentes y su función en el radio de cristal. ¿Qué te parece si empezamos por el cristal de galena o cuarzo y su propiedad especial?', 0, NULL, 1, '2026-03-21 02:44:07'),
(7, 6, '¿qué significa \"rectificar\" en este contexto?', '¿Qué significa \"rectificar\" en este contexto?', 'Excelente pregunta! En el contexto de un radio de cristal, \"rectificar\" se refiere al proceso de convertir una señal de radio alternada (AC) en una señal de corriente continua (DC).\n\nLa señal de radio que se recibe a través de la antena es una señal alternada, lo que significa que la corriente eléctrica cambia de dirección constantemente. Sin embargo, el auricular y otros componentes del radio de cristal necesitan una señal de corriente continua para funcionar correctamente.\n\nEl diodo es el componente que se encarga de \"rectificar\" la señal de radio, es decir, de convertir la señal alternada en una señal de corriente continua. Esto se logra porque el diodo solo permite que la corriente fluya en una dirección, bloqueando la corriente en la otra dirección.\n\n¿Puedes imaginar cómo funciona esto en la práctica? ¿Qué pasaría si la señal de radio no se rectificara antes de llegar al auricular?', 0, NULL, 1, '2026-03-21 02:44:31'),
(8, 6, 'profundiza en tu última respuesta y dame más detalles.', 'Profundiza en tu última respuesta y dame más detalles.', 'Claro, con gusto.\n\nEn un radio de cristal, la señal de radio que se recibe a través de la antena es una señal de corriente alterna (AC). Esto significa que la corriente eléctrica cambia de dirección constantemente, como una onda sinusoidal. La forma de la señal de radio es similar a una onda sinusoidal, con una parte positiva y una parte negativa.\n\nEl problema es que el auricular y otros componentes del radio de cristal necesitan una señal de corriente continua (DC) para funcionar correctamente. La corriente continua es una corriente que fluye en una sola dirección, sin cambiar de dirección.\n\nAquí es donde entra en juego el diodo. El diodo es un componente semiconductor que solo permite que la corriente fluya en una dirección, bloqueando la corriente en la otra dirección. Esto se conoce como \"rectificación\" de la señal de radio.\n\nCuando la señal de radio alternada (AC) llega al diodo, el diodo solo permite que la parte positiva de la señal de radio pase a través de él, bloqueando la parte negativa. De esta manera, la señal de radio se convierte en una señal de corriente continua (DC), que es lo que necesita el auricular y otros componentes del radio de cristal.\n\nEl proceso de rectificación es importante porque permite que el radio de cristal funcione correctamente. Sin la rectificación, la señal de radio no podría ser procesada por el auricular y otros componentes, y no podríamos escuchar la radio.\n\n¿Puedes imaginar cómo funciona esto en la práctica? ¿Cómo crees que la rectificación afecta la calidad de la señal de radio que se escucha en el auricular?\n\nAdemás, ¿sabes', 0, NULL, 1, '2026-03-21 02:44:44'),
(9, 7, 'que tengo que hacer aqui?', 'que tengo que hacer aqui?', 'Estás en la clase de \"Motor eléctrico simple\", donde vas a armar un motor básico que convierte energía eléctrica en movimiento. Para empezar, debes seguir la guía paso a paso que se te proporciona.\n\n¿Has revisado los materiales que se encuentran en el kit, como el alambre esmaltado, los imanes neodimio y el clavo de hierro? \n\nOpciones: si|no|no_se', 0, NULL, 1, '2026-03-24 00:12:14');

--
-- Triggers `ia_respuestas_cache`
--
DELIMITER $$
CREATE TRIGGER `trg_actualizar_cache_stats_clase` AFTER UPDATE ON `ia_respuestas_cache` FOR EACH ROW BEGIN
  IF NEW.veces_usada > OLD.veces_usada THEN
    INSERT INTO ia_logs (clase_id, tipo_evento, descripcion, tokens_usados, costo_estimado)
    VALUES (NEW.clase_id, 'consulta', 'Respuesta desde caché', 0, 0.00);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ia_resumen_sesion`
--

CREATE TABLE `ia_resumen_sesion` (
  `id` bigint(20) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `instancia` enum('frontend','backend') NOT NULL DEFAULT 'frontend',
  `resumen_corto` text NOT NULL,
  `resumen_largo` mediumtext DEFAULT NULL,
  `ultimo_mensaje_id` bigint(20) DEFAULT NULL,
  `tokens_estimados` int(11) DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ia_search_index`
--

CREATE TABLE `ia_search_index` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` enum('clase','kit','componente','manual') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `status_publicacion` varchar(40) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `search_text` longtext NOT NULL,
  `search_text_normalized` longtext NOT NULL,
  `keywords_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`keywords_json`)),
  `relations_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`relations_json`)),
  `score_base` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `source_updated_at` datetime DEFAULT NULL,
  `indexed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ia_search_index_meta`
--

CREATE TABLE `ia_search_index_meta` (
  `id` int(11) NOT NULL,
  `index_version` varchar(50) NOT NULL,
  `generated_at` datetime NOT NULL,
  `source_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`source_summary`)),
  `notes` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ia_sesiones`
--

CREATE TABLE `ia_sesiones` (
  `id` int(11) NOT NULL,
  `sesion_hash` varchar(64) NOT NULL COMMENT 'Hash anónimo del usuario',
  `clase_id` int(11) DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_ultima_interaccion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_mensajes` int(11) DEFAULT 0,
  `tokens_usados` int(11) DEFAULT 0,
  `estado` enum('activa','finalizada','timeout') DEFAULT 'activa',
  `instancia` enum('frontend','backend') NOT NULL DEFAULT 'frontend',
  `admin_user` varchar(120) DEFAULT NULL,
  `contexto_scope` varchar(80) DEFAULT NULL,
  `contexto_pagina` varchar(80) DEFAULT NULL,
  `entidad_tipo` varchar(40) DEFAULT NULL,
  `entidad_id` int(11) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ia_sesiones`
--

INSERT INTO `ia_sesiones` (`id`, `sesion_hash`, `clase_id`, `fecha_inicio`, `fecha_ultima_interaccion`, `total_mensajes`, `tokens_usados`, `estado`, `instancia`, `admin_user`, `contexto_scope`, `contexto_pagina`, `entidad_tipo`, `entidad_id`, `metadata`) VALUES
(1, 'd5c21494d10484856db76c133c6e1e0e', 7, '2026-03-21 01:22:41', '2026-03-24 00:44:51', 12, 1582, 'activa', 'frontend', NULL, NULL, NULL, NULL, NULL, NULL),
(2, '2bcef2b801e0d699f7db24336d0b60f1', NULL, '2026-03-21 01:54:59', '2026-04-11 15:35:27', 6, 1482, 'activa', 'frontend', NULL, NULL, NULL, NULL, NULL, NULL),
(3, '237e9991264658c794e93abe676002550a43ddba26523a61024682aca16c342f', NULL, '2026-03-25 00:44:38', '2026-03-25 01:15:27', 30, 30210, 'activa', 'backend', 'admin', 'admin_global', 'kits', NULL, NULL, NULL),
(4, '9bf772e005fd63f4f40ce2f67bd921e8', NULL, '2026-03-25 19:15:38', '2026-03-25 19:20:58', 8, 2395, 'activa', 'frontend', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ia_sesiones_contexto`
--

CREATE TABLE `ia_sesiones_contexto` (
  `id` bigint(20) NOT NULL,
  `instancia` enum('frontend','backend') NOT NULL,
  `sesion_clave` varchar(160) NOT NULL COMMENT 'Ej: backend:admin:contratos:12',
  `sesion_id` int(11) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ia_sesiones_contexto`
--

INSERT INTO `ia_sesiones_contexto` (`id`, `instancia`, `sesion_clave`, `sesion_id`, `activa`, `created_at`, `updated_at`) VALUES
(1, 'backend', 'backend:admin:admin_global', 3, 1, '2026-03-25 00:44:38', '2026-03-25 00:44:38');

-- --------------------------------------------------------

--
-- Table structure for table `ia_stats_clase`
--

CREATE TABLE `ia_stats_clase` (
  `clase_id` int(11) NOT NULL,
  `total_consultas` int(11) DEFAULT 0,
  `total_sesiones` int(11) DEFAULT 0,
  `tokens_totales` int(11) DEFAULT 0,
  `costo_total` decimal(10,2) DEFAULT 0.00,
  `promedio_mensajes_sesion` decimal(5,2) DEFAULT 0.00,
  `guardrails_activados` int(11) DEFAULT 0,
  `ultima_consulta` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ia_stats_clase`
--

INSERT INTO `ia_stats_clase` (`clase_id`, `total_consultas`, `total_sesiones`, `tokens_totales`, `costo_total`, `promedio_mensajes_sesion`, `guardrails_activados`, `ultima_consulta`, `updated_at`) VALUES
(1, 1, 1, 0, 0.00, 0.00, 0, '2026-03-21 01:25:33', '2026-03-21 01:25:33');

-- --------------------------------------------------------

--
-- Table structure for table `kits`
--

CREATE TABLE `kits` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `codigo` varchar(64) DEFAULT NULL,
  `version` varchar(32) DEFAULT NULL,
  `resumen` text DEFAULT NULL,
  `contenido_html` mediumtext DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `video_portada` varchar(255) DEFAULT NULL,
  `seguridad` longtext DEFAULT NULL CHECK (json_valid(`seguridad`)),
  `seo_title` varchar(160) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `time_minutes` int(11) DEFAULT NULL COMMENT 'Tiempo armado por defecto',
  `dificultad_ensamble` varchar(32) DEFAULT NULL COMMENT 'Dificultad por defecto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kits`
--

INSERT INTO `kits` (`id`, `clase_id`, `nombre`, `slug`, `codigo`, `version`, `resumen`, `contenido_html`, `imagen_portada`, `video_portada`, `seguridad`, `seo_title`, `seo_description`, `activo`, `created_at`, `updated_at`, `time_minutes`, `dificultad_ensamble`) VALUES
(1, 1, 'Microscopio sencillo', 'kit-microscopio-sencillo', 'KIT-MICROSCOPIO_SENCILLO', '1.0', 'Kit para construir un microscopio sencillo para observación básica en el aula.', '<h2>Descripción</h2><p>Este kit permite armar un microscopio artesanal para iniciar la observación de objetos y texturas.</p><h3>Incluye</h3><ul><li>Lentes y elementos de soporte</li><li>Partes para estructura</li></ul><h3>Sugerencias</h3><p>Usa luz natural o una lámpara para mejorar la visualización.</p>', '/assets/images/kits/kit-1.jpg', 'https://www.youtube.com/embed/kit-1-instrucciones', '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"Manipula lentes y piezas pequeñas con cuidado. Supervisión docente recomendada.\"}', 'Kit educativo: Microscopio sencillo', 'Kit para construir un microscopio sencillo y observar detalles básicos de forma segura.', 1, '2025-12-20 04:46:28', '2025-12-21 23:20:07', NULL, NULL),
(2, 2, 'Kit: Pulmón mecánico', NULL, 'KIT-PULMON_MECANICO', '1.0', 'Modelo didáctico para comprender presión y volumen en un sistema respiratorio sencillo.', '<h2>Descripción</h2><p>Arma un modelo de pulmón mecánico para evidenciar cambios de presión y volumen.</p><h3>Aprendizajes</h3><ul><li>Relación presión-volumen</li><li>Movimiento de membrana</li></ul>', '/assets/images/kits/kit-2.jpg', 'https://www.youtube.com/embed/kit-2-instrucciones', '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"Supervisa el uso de globos. Evita golpes bruscos o estiramientos excesivos.\"}', 'Kit educativo: Kit: Pulmón mecánico', 'Modelo de pulmón mecánico para explorar presión y volumen con seguridad básica.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(3, 3, 'Kit: Circuito eléctrico básico', NULL, 'KIT-CIRCUITO_ELECTRICO_BASICO', '1.0', 'Kit para armar un circuito simple con batería, interruptor y LED.', '<h2>Descripción</h2><p>Ensamble un circuito básico y observe la transformación de energía eléctrica en luz.</p><h3>Incluye</h3><ul><li>Pilas y porta baterías</li><li>Cables, interruptor y LED</li></ul>', '/assets/images/kits/kit-3.jpg', 'https://www.youtube.com/embed/kit-3-instrucciones', '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"No cortocircuites las baterías. Verifica polaridad del LED.\"}', 'Kit educativo: Kit: Circuito eléctrico básico', 'Circuito eléctrico básico con LED para iniciar en electricidad de forma segura.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(4, 4, 'Separación de mezclas', 'kit-separacion-de-mezclas', 'KIT-SEPARACION_DE_MEZCLAS', '1.0', 'Kit para practicar métodos físicos como filtración y decantación.', '<h2>Descripción</h2><p>Explora técnicas de separación con papel filtro, embudo y recipientes.</p><h3>Actividades</h3><ul><li>Filtración de mezclas</li><li>Observación de resultados</li></ul>', '/assets/images/kits/kit-4.jpg', 'https://www.youtube.com/embed/kit-4-instrucciones', '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"Manejo cuidadoso del agua y utensilios. Mantén orden y limpieza.\"}', 'Kit educativo: Separación de mezclas', 'Separación de mezclas con filtros y embudos para actividades de laboratorio escolar.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(5, 5, 'Test de pH', NULL, 'KIT-TEST_DE_PH', '1.0', 'Kit con tiras indicadoras para identificar ácidos y bases.', '<h2>Descripción</h2><p>Mide el pH de sustancias cotidianas y registra resultados.</p><h3>Incluye</h3><ul><li>Tiras de pH</li><li>Accesorios básicos de medición</li></ul>', '/assets/images/kits/kit-5.jpg', 'https://www.youtube.com/embed/kit-5-instrucciones', '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"No ingieras sustancias. Lava manos tras la práctica.\"}', 'Kit educativo: Test de pH', 'Pruebas de pH para explorar ácidos y bases con normas básicas de seguridad.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(6, 11, 'Radio de cristal', 'kit-radio-de-cristal', 'KIT-RADIO_DE_CRISTAL', '1.0', 'Kit para comprender recepción AM con circuito resonante LC y detección por diodo.', '<h2>Descripci&oacute;n</h2>\r\n\r\n<p>Analiza la modulaci&oacute;n AM con un receptor pasivo de cristal.</p>\r\n\r\n<h3>Componentes</h3>\r\n\r\n<ul>\r\n	<li>Diodo de germanio</li>\r\n	<li>Auricular de alta impedancia</li>\r\n	<li>Alambre para bobina</li>\r\n</ul>\r\n', '/assets/images/uploads/kit/2026/03/kit-20260309-214812-c0059bffc19c.webp', 'https://www.youtube.com/embed/6HqgWcTnWTk?si=DcstazBj3Sg6hc24', '{\"edad_min\":12,\"edad_max\":18,\"notas\":\"No conectes el circuito a la red eléctrica. Usa antena y tierra de forma segura.\"}', 'Kit de Ciencia - Ciencias Ambientales: Radio de cristal', 'Kit para comprender recepción AM con circuito resonante LC y detección por diodo.', 1, '2025-12-20 04:46:28', '2026-03-10 02:48:17', 22, 'Fácil'),
(7, 7, 'Kit: Motor eléctrico simple', NULL, 'KIT-MOTOR_ELECTRICO_SIMPLE', '1.0', 'Kit para construir un motor sencillo y relacionar electricidad y magnetismo.', '<h2>Descripción</h2><p>Arma un rotor básico para observar movimiento por fuerza electromagnética.</p><h3>Aprendizajes</h3><ul><li>Interacción campo-corriente</li><li>Variables de velocidad</li></ul>', '/assets/images/kits/kit-7.jpg', 'https://www.youtube.com/embed/kit-7-instrucciones', '{\"edad_min\": 13, \"edad_max\": 15, \"notas\": \"Evita sobrecalentamiento por corrientes prolongadas. Supervisión recomendada.\"}', 'Kit educativo: Kit: Motor eléctrico simple', 'Motor eléctrico simple para analizar fuerza electromagnética de forma segura.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(8, 8, 'Kit: Osmosis con vegetales', NULL, 'KIT-OSMOSIS_CON_VEGETALES', '1.0', 'Kit para observar transporte celular con soluciones salinas en vegetales.', '<h2>Descripción</h2><p>Explora cambios por osmosis usando muestras vegetales y sal.</p><h3>Registro</h3><p>Compara longitudes/masas antes y después.</p>', '/assets/images/kits/kit-8.jpg', 'https://www.youtube.com/embed/kit-8-instrucciones', '{\"edad_min\": 13, \"edad_max\": 15, \"notas\": \"Higiene y manejo adecuado de alimentos. Limpia la mesa al finalizar.\"}', 'Kit educativo: Kit: Osmosis con vegetales', 'Experimento de osmosis con vegetales para evidenciar transporte de agua.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(9, 9, 'Kit: Carro trampa de ratón', NULL, 'KIT-CARRO_TRAMPA_DE_RATON', '1.0', 'Kit para construir un carro impulsado por energía potencial de una trampa.', '<h2>Descripción</h2><p>Convierte energía potencial en movimiento y analiza fricción.</p><h3>Sugerencias</h3><p>Prueba distintas superficies y mide distancias.</p>', '/assets/images/kits/kit-9.jpg', 'https://www.youtube.com/embed/kit-9-instrucciones', '{\"edad_min\": 13, \"edad_max\": 15, \"notas\": \"Riesgo de pellizco. Manipula la trampa con cuidado y protección.\"}', 'Kit educativo: Kit: Carro trampa de ratón', 'Carro propulsado por trampa para estudiar energía y fricción en movimiento.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(10, 10, 'Kit: Generador manual (dinamo)', NULL, 'KIT-GENERADOR_MANUAL_DINAMO', '1.0', 'Kit para generar electricidad manualmente y relacionar movimiento con energía.', '<h2>Descripción</h2><p>Acciona una dinamo y mide efectos sobre una carga.</p><h3>Variaciones</h3><p>Cambia velocidad de giro y registra resultados.</p>', '/assets/images/kits/kit-10.jpg', 'https://www.youtube.com/embed/kit-10-instrucciones', '{\"edad_min\": 13, \"edad_max\": 15, \"notas\": \"Evita conexiones inadecuadas. No fuerces el mecanismo.\"}', 'Kit educativo: Kit: Generador manual (dinamo)', 'Dinamo manual para comprender generación eléctrica segura y controlada.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(11, 11, 'Kit: Carro solar', NULL, 'KIT-CARRO_SOLAR', '1.0', 'Kit para construir un vehículo impulsado por energía solar.', '<h2>Descripción</h2><p>Integra panel solar y motor para evaluar eficiencia energética.</p><h3>Exploración</h3><p>Prueba ángulos de incidencia y sombras.</p>', '/assets/images/kits/kit-11.jpg', 'https://www.youtube.com/embed/kit-11-instrucciones', '{\"edad_min\": 15, \"edad_max\": 18, \"notas\": \"Manipula el panel con cuidado. Evita golpes y flexiones.\"}', 'Kit educativo: Kit: Carro solar', 'Carro solar para analizar eficiencia y parámetros de energía renovable.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(12, 12, 'Kit: Turbina eólica de mesa', NULL, 'KIT-TURBINA_EOLICA_DE_MESA', '1.0', 'Kit para diseñar una turbina de mesa y convertir energía del viento.', '<h2>Descripción</h2><p>Construye una hélice y mide energía generada.</p><h3>Pruebas</h3><p>Compara número de palas y ángulos.</p>', '/assets/images/kits/kit-12.jpg', 'https://www.youtube.com/embed/kit-12-instrucciones', '{\"edad_min\": 15, \"edad_max\": 18, \"notas\": \"Mantén distancia de la hélice en movimiento. Usa protección si es necesario.\"}', 'Kit educativo: Kit: Turbina eólica de mesa', 'Turbina eólica de mesa para estudiar conversión de energía del viento.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL),
(13, 13, 'Kit: Electroimán', 'kit-electroiman', 'KIT-ELECTROIMAN', '1.0', 'Kit para construir un electroimán y analizar variables de fuerza y campo.', '<h2>Descripci&oacute;n</h2>\r\n\r\n<p>Enrola alambre en un n&uacute;cleo y experimenta con corriente y vueltas.</p>\r\n\r\n<h3>Observa</h3>\r\n\r\n<p>Variaci&oacute;n de fuerza con espiras y corriente.</p>\r\n', '/assets/images/kits/kit-13.jpg', 'https://www.youtube.com/embed/kit-13-instrucciones', '{\"edad_min\":15,\"edad_max\":18,\"notas\":\"Evita calentamiento prolongado por corrientes altas. Ensayos breves.\"}', 'Kit educativo: Kit: Electroimán', 'Electroimán escolar para explorar relación corriente-campo de forma segura.', 1, '2025-12-20 04:46:28', '2025-12-25 04:03:46', NULL, NULL),
(14, 14, 'Kit: Tratamiento de agua', 'kit-tratamiento-de-agua', 'KIT-TRATAMIENTO_DE_AGUA', '1.0', 'Kit para construir un filtro por capas y evaluar calidad del agua.', '<h2>Descripci&oacute;n</h2>\r\n\r\n<p>Arma un filtro con carb&oacute;n, arena y grava para remover impurezas.</p>\r\n\r\n<h3>Registro</h3>\r\n\r\n<p>Observa claridad antes y despu&eacute;s.</p>\r\n', '/assets/images/kits/kit-14.jpg', 'https://www.youtube.com/embed/kit-14-instrucciones', '{\"edad_min\":15,\"edad_max\":18,\"notas\":\"Gestiona residuos adecuadamente. No ingieras muestras de ensayo.\"}', 'Kit educativo: Kit: Tratamiento de agua', 'Filtro de agua escolar para comprender procesos físico-químicos con seguridad.', 1, '2025-12-20 04:46:28', '2025-12-25 04:03:31', NULL, NULL),
(15, 15, 'Kit: Análisis químico del entorno', NULL, 'KIT-ANALISIS_QUIMICO_DEL_ENTORNO', '1.0', 'Kit para realizar pruebas químicas seguras a sustancias cotidianas.', '<h2>Descripción</h2><p>Aplica pruebas sencillas y documenta resultados con enfoque seguro.</p><h3>Ética</h3><p>Maneja sustancias con responsabilidad y registra observaciones.</p>', '/assets/images/kits/kit-15.jpg', 'https://www.youtube.com/embed/kit-15-instrucciones', '{\"edad_min\": 15, \"edad_max\": 18, \"notas\": \"No ingieras sustancias. Usa guantes/bata según el docente.\"}', 'Kit educativo: Kit: Análisis químico del entorno', 'Análisis químico seguro de sustancias cotidianas para el aula.', 1, '2025-12-20 04:46:28', '2025-12-21 23:19:39', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kits_areas`
--

CREATE TABLE `kits_areas` (
  `kit_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kits_areas`
--

INSERT INTO `kits_areas` (`kit_id`, `area_id`) VALUES
(6, 1),
(6, 4),
(6, 5);

-- --------------------------------------------------------

--
-- Table structure for table `kit_componentes`
--

CREATE TABLE `kit_componentes` (
  `kit_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT 1.00,
  `es_incluido_kit` tinyint(1) NOT NULL DEFAULT 1,
  `notas` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kit_componentes`
--

INSERT INTO `kit_componentes` (`kit_id`, `item_id`, `cantidad`, `es_incluido_kit`, `notas`, `sort_order`) VALUES
(1, 1, 2.00, 1, 'Lentes para aumento', 1),
(1, 2, 1.00, 1, 'Estructura', 2),
(2, 4, 2.00, 1, 'Pulmones', 1),
(2, 5, 1.00, 1, 'Caja torácica', 2),
(3, 7, 2.00, 1, 'Energía', 1),
(3, 8, 1.00, 1, 'Soporte', 2),
(3, 9, 1.50, 1, 'Conexiones', 3),
(3, 10, 1.00, 1, 'Control', 4),
(3, 11, 1.00, 1, 'Salida', 5),
(4, 12, 2.00, 1, 'Filtración', 1),
(4, 13, 1.00, 1, 'Embudo', 2),
(4, 14, 1.00, 1, 'Recipiente', 3),
(5, 15, 10.00, 1, 'Medición', 1),
(6, 9, 1.00, 1, NULL, 4),
(6, 16, 1.00, 1, NULL, 3),
(6, 17, 1.00, 1, NULL, 2),
(6, 18, 1.00, 1, NULL, 5),
(7, 18, 2.00, 1, 'Bobina', 3),
(7, 19, 2.00, 1, 'Campo magnético', 1),
(7, 20, 1.00, 1, 'Núcleo', 2),
(8, 31, 50.00, 1, 'Solución salina', 1),
(8, 32, 2.00, 1, 'Muestras vegetales', 2),
(9, 21, 1.00, 1, 'Fuente de energía potencial', 1),
(9, 22, 4.00, 1, 'Movimiento', 2),
(9, 23, 2.00, 1, 'Transmisión', 3),
(10, 24, 1.00, 1, 'Generación', 1),
(10, 25, 1.00, 1, 'Manivela', 2),
(11, 24, 1.00, 1, 'Tracción', 2),
(11, 26, 1.00, 1, 'Fuente solar', 1),
(12, 24, 1.00, 1, 'Generación', 2),
(12, 27, 1.00, 1, 'Captura de viento', 1),
(13, 18, 2.00, 1, 'Bobina', 1),
(13, 20, 1.00, 1, 'Núcleo', 2),
(14, 28, 50.00, 1, 'Purificación', 1),
(14, 29, 200.00, 1, 'Filtración', 2),
(14, 30, 200.00, 1, 'Capa inferior', 3),
(15, 15, 10.00, 1, 'Indicador seguro', 1);

-- --------------------------------------------------------

--
-- Table structure for table `kit_items`
--

CREATE TABLE `kit_items` (
  `id` int(11) NOT NULL,
  `nombre_comun` varchar(160) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `advertencias_seguridad` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`advertencias_seguridad`)),
  `descripcion_html` mediumtext DEFAULT NULL COMMENT 'Descripción en HTML del componente',
  `foto_url` varchar(255) DEFAULT NULL COMMENT 'URL de imagen representativa',
  `unidad` varchar(32) DEFAULT NULL,
  `sku` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kit_items`
--

INSERT INTO `kit_items` (`id`, `nombre_comun`, `slug`, `categoria_id`, `advertencias_seguridad`, `descripcion_html`, `foto_url`, `unidad`, `sku`) VALUES
(1, 'Lente plástico 10x', 'componente-lente-plastico-10x', 3, '{\"notas\": \"Frágil, manipular con cuidado\"}', NULL, NULL, 'pcs', 'BIO-LEN-10X'),
(2, 'Cartón rígido', 'componente-carton-rigido', 5, NULL, NULL, NULL, 'pcs', 'TEC-CAR-RIG'),
(3, 'Banda elástica', 'componente-banda-elastica', 5, NULL, NULL, NULL, 'pcs', 'TEC-BAN-ELA'),
(4, 'Globo de látex', 'componente-globo-de-latex', 3, '{\"notas\": \"Riesgo de asfixia, no apto <8 años\"}', NULL, NULL, 'pcs', 'BIO-GLO-LAT'),
(5, 'Botella plástica 500ml', 'componente-botella-plastica-500ml', 5, NULL, NULL, NULL, 'pcs', 'TEC-BOT-500'),
(6, 'Bomba de aire manual', 'componente-bomba-de-aire-manual', 6, NULL, NULL, NULL, 'pcs', 'HER-BOM-AIR'),
(7, 'Pila AA', 'componente-pila-aa', 1, '{\"notas\": \"⚠️ No cortocircuitar\"}', NULL, NULL, 'pcs', 'ELE-PIL-AA'),
(8, 'Porta baterías AA', 'componente-porta-baterias-aa', 1, NULL, NULL, NULL, 'pcs', 'ELE-POR-AA'),
(9, 'Cable conductor', 'componente-cable-conductor', 1, NULL, NULL, NULL, 'm', 'ELE-CAB-CON'),
(10, 'Interruptor mini', 'componente-interruptor-mini', 1, NULL, NULL, NULL, 'pcs', 'ELE-INT-MIN'),
(11, 'Bombillo LED 3V', 'componente-bombillo-led-3v', 1, NULL, NULL, NULL, 'pcs', 'ELE-LED-3V'),
(12, 'Papel filtro', 'componente-papel-filtro', 4, '{\"notas\": \"Material frágil\"}', NULL, NULL, 'pcs', 'QUI-PAP-FIL'),
(13, 'Embudo plástico', 'componente-embudo-plastico', 4, NULL, NULL, NULL, 'pcs', 'QUI-EMB-PLA'),
(14, 'Vaso precipitado plástico', 'componente-vaso-precipitado-plastico', 4, NULL, NULL, NULL, 'pcs', 'QUI-VAS-PLA'),
(15, 'Tiras de pH', 'componente-tiras-de-ph', 4, NULL, NULL, NULL, 'pcs', 'QUI-TIR-PH'),
(16, 'Diode germanio', 'componente-diode-germanio', 1, '{\"edad_min\": 11, \"edad_max\": 13, \"notas\": \"test\"}', '<h2>&iquest;Qu&eacute; es el diodo de germanio?</h2>\r\n\r\n<p>El diodo de germanio es un componente semiconductor que permite el paso de corriente en una direcci&oacute;n y la bloquea en la contraria. Se caracteriza por una <strong>baja tensi&oacute;n umbral</strong> (&asymp;0.2&ndash;0.3&nbsp;V), ideal para detecci&oacute;n de se&ntilde;ales d&eacute;biles como en radios de cristal.</p>\r\n\r\n<h3>Usos comunes</h3>\r\n\r\n<ul>\r\n	<li>Detector de AM en <em>radios de cristal</em>.</li>\r\n	<li>Rectificaci&oacute;n de se&ntilde;ales de baja amplitud.</li>\r\n	<li>Etapas de medici&oacute;n y prototipos educativos.</li>\r\n</ul>\r\n\r\n<h3>Especificaciones t&iacute;picas</h3>\r\n\r\n<table>\r\n	<thead>\r\n		<tr>\r\n			<th>Par&aacute;metro</th>\r\n			<th>Valor orientativo</th>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td>Tensi&oacute;n umbral</td>\r\n			<td>0.2&ndash;0.3&nbsp;V</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Corriente m&aacute;x. (se&ntilde;al)</td>\r\n			<td>10&ndash;50&nbsp;mA</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Polaridad</td>\r\n			<td>&Aacute;nodo (+) &rarr; C&aacute;todo (&ndash;)</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<blockquote>⚠️ <strong>Nota de seguridad:</strong> componente <em>fr&aacute;gil</em>. Evita doblar las patillas en exceso y no excedas la corriente recomendada.</blockquote>\r\n\r\n<p>Ejemplos: 1N34A, OA90 (modelos cl&aacute;sicos para detecci&oacute;n).</p>\r\n', '/assets/images/uploads/componente/2026/03/componente-20260309-201056-67939fe1a9ad.webp', 'pcs', 'ELE-DIO-GER'),
(17, 'Auricular cristal', 'componente-auricular-cristal', 1, NULL, NULL, NULL, 'pcs', 'ELE-AUR-CRI'),
(18, 'Alambre esmaltado 28AWG', 'componente-alambre-esmaltado-28awg', 1, NULL, NULL, NULL, 'm', 'ELE-ALM-28'),
(19, 'Imán neodimio', 'componente-iman-neodimio', 2, '{\"notas\": \"⚠️ Mantener lejos de dispositivos\"}', NULL, NULL, 'pcs', 'MAG-IMA-NEO'),
(20, 'Clavo de hierro', 'componente-clavo-de-hierro', 2, NULL, NULL, NULL, 'pcs', 'MAG-CLA-HIE'),
(21, 'Trampa de ratón', 'componente-trampa-de-raton', 5, '{\"notas\": \"⚠️ Riesgo de pellizco\"}', NULL, NULL, 'pcs', 'TEC-TRA-RAT'),
(22, 'Rueda plástica 50mm', 'componente-rueda-plastica-50mm', 5, NULL, NULL, NULL, 'pcs', 'TEC-RUE-50'),
(23, 'Eje metálico', 'componente-eje-metalico', 5, NULL, NULL, NULL, 'pcs', 'TEC-EJE-MET'),
(24, 'Motor DC 3-6V', 'componente-motor-dc-3-6v', 1, NULL, NULL, NULL, 'pcs', 'ELE-MOT-DC'),
(25, 'Manivela plástica', 'componente-manivela-plastica', 5, NULL, NULL, NULL, 'pcs', 'TEC-MAN-PLA'),
(26, 'Panel solar 5V', 'componente-panel-solar-5v', 5, NULL, NULL, NULL, 'pcs', 'TEC-PAN-5V'),
(27, 'Hélice plástica', 'componente-helice-plastica', 5, NULL, NULL, NULL, 'pcs', 'TEC-HEL-PLA'),
(28, 'Carbón activado', 'componente-carbon-activado', NULL, NULL, NULL, NULL, 'g', 'AMB-CAR-ACT'),
(29, 'Arena fina', 'componente-arena-fina', NULL, NULL, NULL, NULL, 'g', 'AMB-ARE-FIN'),
(30, 'Grava', 'componente-grava', NULL, NULL, NULL, NULL, 'g', 'AMB-GRA-STD'),
(31, 'Sal de mesa', 'componente-sal-de-mesa', 4, NULL, NULL, NULL, 'g', 'QUI-SAL-MES'),
(32, 'Rodaja de papa', 'componente-rodaja-de-papa', 3, NULL, NULL, NULL, 'pcs', 'BIO-ROD-PAP');

-- --------------------------------------------------------

--
-- Table structure for table `kit_manuals`
--

CREATE TABLE `kit_manuals` (
  `id` int(11) NOT NULL,
  `kit_id` int(11) DEFAULT NULL,
  `slug` varchar(120) NOT NULL COMMENT 'Slug del manual (por kit/idioma)',
  `version` varchar(32) NOT NULL DEFAULT '1.0',
  `autor` varchar(255) DEFAULT NULL COMMENT 'Nombre del autor del manual',
  `status` enum('draft','approved','published','discontinued') NOT NULL DEFAULT 'draft',
  `tipo_manual` enum('seguridad','armado','calibracion','uso','mantenimiento','teoria','experimento','solucion','evaluacion','docente','referencia') NOT NULL DEFAULT 'armado',
  `ambito` enum('kit','componente') NOT NULL DEFAULT 'kit',
  `item_id` int(11) DEFAULT NULL,
  `idioma` varchar(10) NOT NULL DEFAULT 'es-CO',
  `resumen` varchar(255) DEFAULT NULL,
  `time_minutes` int(11) DEFAULT NULL COMMENT 'Tiempo estimado de armado',
  `dificultad_ensamble` varchar(32) DEFAULT NULL,
  `pasos_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pasos_json`)),
  `herramientas_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`herramientas_json`)),
  `seguridad_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`seguridad_json`)),
  `html` mediumtext DEFAULT NULL,
  `render_mode` enum('legacy','fullhtml') NOT NULL DEFAULT 'legacy',
  `schema_type_preferido` enum('auto','HowTo','TechArticle') NOT NULL DEFAULT 'auto',
  `schema_overrides_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`schema_overrides_json`)),
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kit_manuals`
--

INSERT INTO `kit_manuals` (`id`, `kit_id`, `slug`, `version`, `autor`, `status`, `tipo_manual`, `ambito`, `item_id`, `idioma`, `resumen`, `time_minutes`, `dificultad_ensamble`, `pasos_json`, `herramientas_json`, `seguridad_json`, `html`, `render_mode`, `schema_type_preferido`, `schema_overrides_json`, `published_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 'manual-armado-componente-diode-germanio-09-03-26-v1-0', '1.0', 'Clase de Ciencia', 'published', 'armado', 'componente', 16, 'es-CO', NULL, 45, 'media', '[{\"orden\":1,\"titulo\":\"Preparar antena y tierra\",\"html\":\"<p>Desenrolla 10–20 m de alambre para la antena y conecta una buena tierra (por ejemplo, tubería metálica).<\\/p>\"},{\"orden\":2,\"titulo\":\"Enrollar la bobina\",\"html\":\"<p>Haz ~80–120 espiras de alambre esmaltado sobre un tubo; deja derivaciones para sintonía.<\\/p>\"},{\"orden\":3,\"titulo\":\"Conectar LC y diodo\",\"html\":\"<p>Conecta la bobina al capacitor variable (si aplica) y el diodo de germanio como detector AM.<\\/p>\"},{\"orden\":4,\"titulo\":\"Auricular y prueba\",\"html\":\"<p>Conecta el auricular de alta impedancia, ajusta la sintonía y busca estaciones AM.<\\/p>\"}]', '[{\"nombre\":\"Alicates de corte\",\"cantidad\":1,\"nota\":\"para alambre\",\"seguridad\":\"peligroso\"},{\"nombre\":\"Cúter\",\"cantidad\":1,\"nota\":\"\",\"seguridad\":\"\"},{\"nombre\":\"Cinta aislante\",\"cantidad\":\"1 rollo\",\"nota\":\"\",\"seguridad\":\"\"},{\"nombre\":\"Regla\",\"cantidad\":1,\"nota\":\"medir longitudes de antena\",\"seguridad\":\"\"},{\"nombre\":\"Lija fina\",\"cantidad\":1,\"nota\":\"retirar esmalte de alambre\",\"seguridad\":\"\"}]', '{\"edad\":{\"min\":20,\"max\":30},\"notas\":[{\"nota\":\"No conectes el circuito a la red eléctrica.\",\"categoria\":\"eléctrico\"},{\"nota\":\"Usa antena y tierra con supervisión docente.\",\"categoria\":\"supervisión adulta\"},{\"nota\":\"test\",\"categoria\":\"protección personal\"}]}', '', 'legacy', 'auto', NULL, '2026-03-09 20:43:03', '2025-12-24 01:02:33', '2026-03-10 01:43:14');

--
-- Triggers `kit_manuals`
--
DELIMITER $$
CREATE TRIGGER `kit_manuals_published_at_bu` BEFORE UPDATE ON `kit_manuals` FOR EACH ROW BEGIN
  IF NEW.status = 'published'
     AND (OLD.status IS NULL OR OLD.status <> 'published')
     AND (NEW.published_at IS NULL)
  THEN
    SET NEW.published_at = NOW();
  END IF;

  -- If moving away from published, you can clear published_at here,
  -- but most teams prefer to keep the historical first published_at.
  -- Uncomment to clear on unpublish:
  -- IF OLD.status = 'published' AND NEW.status <> 'published' THEN
  --   SET NEW.published_at = NULL;
  -- END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lotes`
--

CREATE TABLE `lotes` (
  `id` int(11) NOT NULL,
  `codigo_lote` varchar(64) NOT NULL,
  `kit_id` int(11) NOT NULL,
  `contrato_id` int(11) DEFAULT NULL,
  `cantidad_total` int(11) NOT NULL DEFAULT 0,
  `cantidad_disponible` int(11) NOT NULL DEFAULT 0,
  `cantidad_asignada` int(11) NOT NULL DEFAULT 0,
  `cantidad_entregada` int(11) NOT NULL DEFAULT 0,
  `fecha_fabricacion` date DEFAULT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `estado_lote` enum('activo','bloqueado','agotado','cerrado') NOT NULL DEFAULT 'activo',
  `ubicacion` varchar(180) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paginas_estaticas`
--

CREATE TABLE `paginas_estaticas` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(80) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `meta_description` varchar(320) DEFAULT NULL,
  `contenido_html` mediumtext DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paginas_estaticas`
--

INSERT INTO `paginas_estaticas` (`id`, `slug`, `titulo`, `meta_description`, `contenido_html`, `activo`, `updated_at`, `updated_by`) VALUES
(1, 'sobre-nosotros', 'Sobre Nosotros', 'Conoce el equipo y la misión detrás de Clase de Ciencia, plataforma de formación científica para estudiantes colombianos.', '<p>Somos una plataforma de formación científica para estudiantes de educación básica y media en Colombia, con guías interactivas, proyectos prácticos y orientación personalizada.</p>\n<p>Escríbenos a <a href=\"mailto:soporte@clasedeciencia.com\">soporte@clasedeciencia.com</a></p>', 1, '2026-03-19 00:23:11', 'admin'),
(2, 'terms', 'Términos de Uso', 'Términos y condiciones de uso de Clase de Ciencia.', '<h2>Uso de la plataforma</h2>\n<p>El contenido de Clase de Ciencia es de carácter educativo y no reemplaza la orientación de un docente calificado.</p>\n<h2>Limitación de responsabilidad</h2>\n<p>Clase de Ciencia no se hace responsable por el uso inadecuado de los materiales o experimentos descritos en la plataforma.</p>\n<h2>Contacto</h2>\n<p>Para consultas sobre estos términos escríbenos a <a href=\"mailto:soporte@clasedeciencia.com\">soporte@clasedeciencia.com</a>.</p>', 1, '2026-03-19 00:23:11', 'admin'),
(3, 'privacy', 'Política de Privacidad', 'Cómo recopilamos, usamos y protegemos tu información en Clase de Ciencia.', '<h2>Información que recopilamos</h2>\n<p>No requerimos registro de usuario. Únicamente recopilamos datos anónimos de navegación para mejorar la experiencia de la plataforma.</p>\n<h2>Cookies y analíticas</h2>\n<p>Utilizamos cookies de sesión estrictas y herramientas de analítica anónima. No compartimos datos personales con terceros.</p>\n<h2>Enlaces externos</h2>\n<p>Nuestra plataforma puede contener enlaces a sitios externos. No somos responsables de sus políticas de privacidad.</p>\n<h2>Contacto</h2>\n<p>Para consultas sobre privacidad escríbenos a <a href=\"mailto:soporte@clasedeciencia.com\">soporte@clasedeciencia.com</a>.</p>', 1, '2026-03-19 00:23:11', 'admin'),
(4, 'contact', 'Contacto', 'Ponte en contacto con el equipo de Clase de Ciencia.', '<h2>Escríbenos</h2>\n<p>Para soporte técnico, preguntas pedagógicas o información sobre nuestros kits, contáctanos en:</p>\n<p><a href=\"mailto:soporte@clasedeciencia.com\">soporte@clasedeciencia.com</a></p>\n<h2>Temas que atendemos</h2>\n<ul>\n  <li>Soporte técnico con kits y componentes</li>\n  <li>Orientación pedagógica sobre las clases</li>\n  <li>Solicitudes institucionales (contratos CTeI)</li>\n  <li>Alianzas y colaboraciones educativas</li>\n</ul>', 1, '2026-03-19 00:23:11', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `prompts_clase`
--

CREATE TABLE `prompts_clase` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `prompt_contexto` text NOT NULL COMMENT 'Contexto específico de la clase para la IA',
  `conocimientos_previos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Conceptos que el estudiante debe saber' CHECK (json_valid(`conocimientos_previos`)),
  `enfoque_pedagogico` text DEFAULT NULL COMMENT 'Cómo debe guiar la IA en esta clase',
  `preguntas_frecuentes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'FAQs de la clase para respuestas rápidas' CHECK (json_valid(`preguntas_frecuentes`)),
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prompts_clase`
--

INSERT INTO `prompts_clase` (`id`, `clase_id`, `prompt_contexto`, `conocimientos_previos`, `enfoque_pedagogico`, `preguntas_frecuentes`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Contexto IA para la clase: Microscopio sencillo. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(2, 2, 'Contexto IA para la clase: Pulmón mecánico. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(3, 3, 'Contexto IA para la clase: Circuito eléctrico básico. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(4, 4, 'Contexto IA para la clase: Separación de mezclas. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(5, 5, 'Contexto IA para la clase: Test de pH. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(6, 6, 'Contexto IA para la clase: Radio de cristal. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(7, 7, 'Contexto IA para la clase: Motor eléctrico simple. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(8, 8, 'Contexto IA para la clase: Osmosis con vegetales. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(9, 9, 'Contexto IA para la clase: Carro trampa de ratón. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(10, 10, 'Contexto IA para la clase: Generador manual (dinamo). Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(11, 11, 'Contexto IA para la clase: Carro solar. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(12, 12, 'Contexto IA para la clase: Turbina eólica de mesa. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(13, 13, 'Contexto IA para la clase: Electroimán. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(14, 14, 'Contexto IA para la clase: Tratamiento de agua. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(15, 15, 'Contexto IA para la clase: Análisis químico del entorno. Conceptos clave y seguridad según guía.', '[\"Normas básicas de laboratorio\", \"Mediciones y observación\", \"Seguridad eléctrica/química según aplique\"]', 'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.', '[\"¿Qué variable afecta más el resultado?\", \"¿Cómo mejora la eficiencia?\", \"¿Qué relación hay entre concepto y observación?\"]', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `recursos_multimedia`
--

CREATE TABLE `recursos_multimedia` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) DEFAULT NULL,
  `kit_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `manual_id` int(11) DEFAULT NULL,
  `tipo` enum('imagen','video','pdf','link') NOT NULL,
  `url` varchar(255) NOT NULL,
  `titulo` varchar(180) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `schema_role` enum('primary','gallery','tutorial','download','external') NOT NULL DEFAULT 'gallery',
  `mime_type` varchar(100) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `duration_iso8601` varchar(32) DEFAULT NULL COMMENT 'Ej: PT2M10S',
  `upload_date` datetime DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `embed_url` varchar(255) DEFAULT NULL,
  `in_language` varchar(10) DEFAULT NULL,
  `license_url` varchar(255) DEFAULT NULL,
  `creator_name` varchar(160) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recursos_multimedia`
--

INSERT INTO `recursos_multimedia` (`id`, `clase_id`, `kit_id`, `item_id`, `manual_id`, `tipo`, `url`, `titulo`, `descripcion`, `sort_order`, `schema_role`, `mime_type`, `width`, `height`, `duration_iso8601`, `upload_date`, `thumbnail_url`, `embed_url`, `in_language`, `license_url`, `creator_name`, `metadata`, `created_at`) VALUES
(1, 15, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/analisis-quimico-del-entorno', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(2, 11, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/carro-solar', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(3, 9, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/carro-trampa-de-raton', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(4, 3, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/circuito-electrico-basico', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(5, 13, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/electroiman', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(6, 10, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/generador-manual-dinamo', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(7, 1, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/microscopio-sencillo', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(8, 7, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/motor-electrico-simple', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(9, 8, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/osmosis-con-vegetales', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(10, 2, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/pulmon-mecanico', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(11, 6, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/radio-de-cristal', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(12, 4, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/separacion-de-mezclas', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(13, 5, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/test-de-ph', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(14, 14, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/tratamiento-de-agua', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(15, 12, NULL, NULL, NULL, 'link', 'https://clasedeciencia.com/clase/turbina-eolica-de-mesa', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 04:46:28'),
(16, NULL, 6, NULL, NULL, 'imagen', '/assets/images/uploads/kit/2026/03/kit-20260309-214812-c0059bffc19c.webp', 'Radio de cristal', 'Kit para comprender recepción AM con circuito resonante LC y detección por diodo.', 0, 'primary', 'image/webp', 800, 800, NULL, '2026-03-09 21:48:12', NULL, NULL, 'es-CO', NULL, 'Clase de Ciencia', NULL, '2026-03-10 02:48:17');

-- --------------------------------------------------------

--
-- Table structure for table `sitio_config`
--

CREATE TABLE `sitio_config` (
  `clave` varchar(60) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sitio_config`
--

INSERT INTO `sitio_config` (`clave`, `valor`, `descripcion`, `updated_at`) VALUES
('email_contacto', 'soporte@clasedeciencia.com', 'Email de contacto principal', '2026-03-19 00:23:11'),
('facebook_url', '', 'URL del perfil de Facebook (dejar vacío para ocultar el ícono)', '2026-03-19 00:23:11'),
('footer_texto_sobre', 'Plataforma de formación científica para estudiantes de educación básica y media, con guías interactivas, proyectos prácticos y orientación personalizada.', 'Párrafo descriptivo en la columna \"Acerca de\" del footer', '2026-03-19 00:23:11'),
('instagram_url', '', 'URL del perfil de Instagram (dejar vacío para ocultar el ícono)', '2026-03-19 00:23:11'),
('sitio_descripcion', 'Proyectos científicos interactivos para estudiantes colombianos (6°-11°).', 'Meta description global del sitio', '2026-03-19 00:23:11'),
('sitio_nombre', 'Clase de Ciencia', 'Nombre del sitio (aparece en encabezados y footer)', '2026-03-19 00:23:11'),
('youtube_url', '', 'URL del canal de YouTube (dejar vacío para ocultar el ícono)', '2026-03-19 00:23:11');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_admin_contratos_resumen`
-- (See below for the actual view)
--
CREATE TABLE `v_admin_contratos_resumen` (
`id` int(11)
,`numero` varchar(64)
,`entidad_contratante` varchar(255)
,`departamento` varchar(120)
,`municipio` varchar(120)
,`fecha` date
,`fecha_inicio` date
,`fecha_fin` date
,`valor` decimal(16,2)
,`valor_ejecutado` decimal(16,2)
,`saldo_pendiente` decimal(17,2)
,`avance_financiero_pct` decimal(22,2)
,`estado_contrato` enum('borrador','vigente','suspendido','finalizado','cerrado')
,`supervisor` varchar(180)
,`updated_at` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_admin_entregas_resumen`
-- (See below for the actual view)
--
CREATE TABLE `v_admin_entregas_resumen` (
`id` int(11)
,`codigo_entrega` varchar(64)
,`contrato_id` int(11)
,`contrato_numero` varchar(64)
,`entidad_contratante` varchar(255)
,`institucion_educativa` varchar(255)
,`departamento` varchar(120)
,`municipio` varchar(120)
,`fecha_programada` date
,`fecha_entrega` date
,`estado_entrega` enum('programada','en_transito','entregada','rechazada','reprogramada')
,`cantidad_kits` int(11)
,`recibido_ok` tinyint(1)
,`acta_pdf` varchar(255)
,`entrega_atrasada` int(1)
,`updated_at` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_admin_lotes_resumen`
-- (See below for the actual view)
--
CREATE TABLE `v_admin_lotes_resumen` (
`id` int(11)
,`codigo_lote` varchar(64)
,`kit_id` int(11)
,`kit_nombre` varchar(120)
,`contrato_id` int(11)
,`contrato_numero` varchar(64)
,`cantidad_total` int(11)
,`cantidad_disponible` int(11)
,`cantidad_asignada` int(11)
,`cantidad_entregada` int(11)
,`estado_lote` enum('activo','bloqueado','agotado','cerrado')
,`ubicacion` varchar(180)
,`fecha_fabricacion` date
,`fecha_caducidad` date
,`stock_disponible_pct` decimal(16,2)
,`updated_at` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `v_admin_riesgo_operativo`
--
-- Error reading structure for table u626603208_clasedeciencia.v_admin_riesgo_operativo: #1271 - Illegal mix of collations for operation 'UNION'
-- Error reading data for table u626603208_clasedeciencia.v_admin_riesgo_operativo: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'FROM `u626603208_clasedeciencia`.`v_admin_riesgo_operativo`' at line 1

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_auditoria_ctr_busqueda_30d`
-- (See below for the actual view)
--
CREATE TABLE `v_auditoria_ctr_busqueda_30d` (
`fecha` date
,`total_busquedas` decimal(22,0)
,`total_clics_resultado` decimal(22,0)
,`ctr_busqueda_pct` decimal(28,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_auditoria_funnel_home_clase_kit_30d`
-- (See below for the actual view)
--
CREATE TABLE `v_auditoria_funnel_home_clase_kit_30d` (
`sesiones_home` bigint(21)
,`sesiones_clase` bigint(21)
,`sesiones_kit` bigint(21)
,`conv_home_a_clase_pct` decimal(26,2)
,`conv_clase_a_kit_pct` decimal(26,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_auditoria_ia_preguntas_sin_resolver_30d`
-- (See below for the actual view)
--
CREATE TABLE `v_auditoria_ia_preguntas_sin_resolver_30d` (
`fecha` date
,`instancia` enum('frontend','backend')
,`total_preguntas_sin_resolver` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_auditoria_ia_salud_30d`
-- (See below for the actual view)
--
CREATE TABLE `v_auditoria_ia_salud_30d` (
`instancia` enum('frontend','backend')
,`fecha` date
,`total_eventos_ia` bigint(21)
,`total_consultas` decimal(22,0)
,`total_errores` decimal(22,0)
,`total_guardrails` decimal(22,0)
,`tokens_totales` decimal(32,0)
,`latencia_promedio_ms` decimal(13,2)
,`tasa_error_pct` decimal(28,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_auditoria_resumen_ejecutivo`
-- (See below for the actual view)
--
CREATE TABLE `v_auditoria_resumen_ejecutivo` (
`page_views_7d` bigint(21)
,`sesiones_unicas_7d` bigint(21)
,`consultas_ia_7d` bigint(21)
,`errores_ia_7d` bigint(21)
,`guardrails_ia_7d` bigint(21)
,`fecha_corte` timestamp /* mariadb-5.3 */
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_auditoria_top_clases_7d`
-- (See below for the actual view)
--
CREATE TABLE `v_auditoria_top_clases_7d` (
`clase_id` int(11)
,`nombre` varchar(180)
,`slug` varchar(180)
,`visitas_totales` bigint(21)
,`sesiones_unicas` bigint(21)
,`clics_desde_busqueda` decimal(22,0)
,`ultima_interaccion` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_auditoria_top_clases_30d`
-- (See below for the actual view)
--
CREATE TABLE `v_auditoria_top_clases_30d` (
`clase_id` int(11)
,`nombre` varchar(180)
,`slug` varchar(180)
,`visitas_totales` bigint(21)
,`sesiones_unicas` bigint(21)
,`clics_desde_busqueda` decimal(22,0)
,`ultima_interaccion` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_auditoria_visitas_legacy_30d`
-- (See below for the actual view)
--
CREATE TABLE `v_auditoria_visitas_legacy_30d` (
`clase_id` int(11)
,`nombre` varchar(180)
,`slug` varchar(180)
,`visitas_totales` bigint(21)
,`dias_con_trafico` bigint(21)
,`ultima_visita` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_clases_populares_ia`
-- (See below for the actual view)
--
CREATE TABLE `v_clases_populares_ia` (
`id` int(11)
,`nombre` varchar(180)
,`slug` varchar(180)
,`orden_popularidad` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_clase_contexto_ia`
-- (See below for the actual view)
--
CREATE TABLE `v_clase_contexto_ia` (
`clase_id` int(11)
,`nombre` varchar(180)
,`slug` varchar(180)
,`ciclo` tinyint(1)
,`dificultad` varchar(32)
,`duracion_minutos` int(11)
,`resumen` text
,`objetivo_aprendizaje` text
,`areas` longtext
,`competencias` longtext
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_clase_kits_detalle`
-- (See below for the actual view)
--
CREATE TABLE `v_clase_kits_detalle` (
`kit_id` int(11)
,`clase_id` int(11)
,`kit_nombre` varchar(120)
,`item_id` int(11)
,`item_nombre` varchar(160)
,`cantidad` decimal(10,2)
,`es_incluido_kit` tinyint(1)
,`notas` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_ia_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `v_ia_dashboard` (
`fecha` date
,`sesiones_unicas` bigint(21)
,`total_eventos` bigint(21)
,`total_consultas` decimal(22,0)
,`total_errores` decimal(22,0)
,`alertas_seguridad` decimal(22,0)
,`tokens_totales` decimal(32,0)
,`tiempo_promedio_ms` decimal(14,4)
,`costo_total` decimal(32,6)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_ia_preguntas_frecuentes_clase`
-- (See below for the actual view)
--
CREATE TABLE `v_ia_preguntas_frecuentes_clase` (
`clase` varchar(180)
,`pregunta` text
,`veces_preguntada` bigint(21)
,`ultima_vez` timestamp
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics_eventos`
--
ALTER TABLE `analytics_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ae_evento_fecha` (`evento`,`created_at`),
  ADD KEY `idx_ae_session_fecha` (`session_hash`,`created_at`),
  ADD KEY `idx_ae_instancia_modulo` (`instancia`,`modulo`),
  ADD KEY `idx_ae_tipo_pagina` (`tipo_pagina`),
  ADD KEY `idx_ae_entidad` (`entidad_tipo`,`entidad_id`),
  ADD KEY `idx_ae_clase` (`clase_id`),
  ADD KEY `idx_ae_kit` (`kit_id`),
  ADD KEY `idx_ae_busqueda` (`termino_busqueda`),
  ADD KEY `idx_ae_created` (`created_at`),
  ADD KEY `fk_ae_sesion_ia` (`sesion_ia_id`);

--
-- Indexes for table `analytics_visitas`
--
ALTER TABLE `analytics_visitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_analytics_clase` (`clase_id`),
  ADD KEY `idx_analytics_tipo` (`tipo_pagina`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_areas_slug` (`slug`),
  ADD UNIQUE KEY `uq_areas_nombre` (`nombre`);

--
-- Indexes for table `atributos_contenidos`
--
ALTER TABLE `atributos_contenidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contenidos_entidad` (`tipo_entidad`,`entidad_id`),
  ADD KEY `idx_contenidos_atributo` (`atributo_id`),
  ADD KEY `idx_contenidos_entidad_attr` (`tipo_entidad`,`atributo_id`);

--
-- Indexes for table `atributos_definiciones`
--
ALTER TABLE `atributos_definiciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_atributos_clave` (`clave`);

--
-- Indexes for table `atributos_mapeo`
--
ALTER TABLE `atributos_mapeo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_mapeo_attr_entidad` (`atributo_id`,`tipo_entidad`),
  ADD KEY `idx_mapeo_entidad` (`tipo_entidad`,`orden`);

--
-- Indexes for table `auditoria_admin`
--
ALTER TABLE `auditoria_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_entidad` (`entidad`,`entidad_id`),
  ADD KEY `idx_audit_modulo` (`modulo`),
  ADD KEY `idx_audit_fecha` (`created_at`);

--
-- Indexes for table `categorias_items`
--
ALTER TABLE `categorias_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categorias_items_slug` (`slug`);

--
-- Indexes for table `ciclos`
--
ALTER TABLE `ciclos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ciclos_numero` (`numero`),
  ADD UNIQUE KEY `uq_ciclos_slug` (`slug`),
  ADD KEY `idx_ciclos_activo` (`activo`),
  ADD KEY `idx_ciclos_orden` (`orden`);

--
-- Indexes for table `clases`
--
ALTER TABLE `clases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_clases_slug` (`slug`),
  ADD KEY `idx_clases_activo_ciclo` (`activo`,`ciclo`),
  ADD KEY `idx_clases_status_published` (`status`,`published_at`),
  ADD KEY `idx_clases_popularidad` (`orden_popularidad`);
ALTER TABLE `clases` ADD FULLTEXT KEY `ft_clases_ia` (`nombre`,`resumen`,`objetivo_aprendizaje`);

--
-- Indexes for table `clase_areas`
--
ALTER TABLE `clase_areas`
  ADD PRIMARY KEY (`clase_id`,`area_id`),
  ADD KEY `idx_clase_areas_clase` (`clase_id`),
  ADD KEY `idx_clase_areas_area` (`area_id`);

--
-- Indexes for table `clase_competencias`
--
ALTER TABLE `clase_competencias`
  ADD PRIMARY KEY (`clase_id`,`competencia_id`),
  ADD KEY `idx_clase_competencias_clase` (`clase_id`),
  ADD KEY `idx_clase_competencias_comp` (`competencia_id`);

--
-- Indexes for table `clase_kits`
--
ALTER TABLE `clase_kits`
  ADD PRIMARY KEY (`clase_id`,`kit_id`),
  ADD KEY `idx_clase_kits_clase` (`clase_id`),
  ADD KEY `idx_clase_kits_kit` (`kit_id`),
  ADD KEY `idx_clase_kits_order` (`clase_id`,`sort_order`);

--
-- Indexes for table `clase_tags`
--
ALTER TABLE `clase_tags`
  ADD PRIMARY KEY (`clase_id`,`tag`),
  ADD KEY `idx_clase_tags_tag` (`tag`);

--
-- Indexes for table `competencias`
--
ALTER TABLE `competencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_competencias_codigo` (`codigo`);

--
-- Indexes for table `configuracion_ia`
--
ALTER TABLE `configuracion_ia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_instancia_pagina_clave` (`instancia`,`pagina`,`clave`);

--
-- Indexes for table `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contratos_numero` (`numero`),
  ADD KEY `idx_contratos_estado` (`estado_contrato`),
  ADD KEY `idx_contratos_depto` (`departamento`),
  ADD KEY `idx_contratos_fechas` (`fecha_inicio`,`fecha_fin`);

--
-- Indexes for table `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_entregas_codigo` (`codigo_entrega`),
  ADD KEY `idx_entregas_contrato` (`contrato_id`),
  ADD KEY `idx_entregas_estado` (`estado_entrega`),
  ADD KEY `idx_entregas_fecha_prog` (`fecha_programada`),
  ADD KEY `idx_entregas_fecha_real` (`fecha`),
  ADD KEY `idx_entregas_geo` (`departamento`,`municipio`);

--
-- Indexes for table `entrega_lotes`
--
ALTER TABLE `entrega_lotes`
  ADD PRIMARY KEY (`entrega_id`,`lote_id`),
  ADD KEY `idx_el_lote` (`lote_id`);

--
-- Indexes for table `footer_enlaces`
--
ALTER TABLE `footer_enlaces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_grupo` (`grupo_id`);

--
-- Indexes for table `footer_grupos`
--
ALTER TABLE `footer_grupos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guias`
--
ALTER TABLE `guias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_guias_clase` (`clase_id`);

--
-- Indexes for table `ia_guardrails_log`
--
ALTER TABLE `ia_guardrails_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ia_guardrails_tipo` (`tipo_alerta`),
  ADD KEY `idx_ia_guardrails_clase` (`clase_id`),
  ADD KEY `idx_ia_guardrails_fecha` (`fecha_hora`),
  ADD KEY `idx_ia_guardrails_mix` (`clase_id`,`tipo_alerta`,`fecha_hora`);

--
-- Indexes for table `ia_logs`
--
ALTER TABLE `ia_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ia_logs_tipo` (`tipo_evento`),
  ADD KEY `idx_ia_logs_clase` (`clase_id`),
  ADD KEY `idx_ia_logs_fecha` (`fecha_hora`),
  ADD KEY `idx_ia_logs_analytics` (`fecha_hora`,`tipo_evento`,`clase_id`);

--
-- Indexes for table `ia_mensajes`
--
ALTER TABLE `ia_mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ia_mensajes_sesion` (`sesion_id`),
  ADD KEY `idx_ia_mensajes_fecha` (`fecha_hora`);

--
-- Indexes for table `ia_respuestas_cache`
--
ALTER TABLE `ia_respuestas_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ia_cache_clase_pregunta` (`clase_id`,`pregunta_normalizada`(255));

--
-- Indexes for table `ia_resumen_sesion`
--
ALTER TABLE `ia_resumen_sesion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ia_resumen_sesion` (`sesion_id`,`instancia`),
  ADD KEY `idx_ia_resumen_updated` (`updated_at`);

--
-- Indexes for table `ia_search_index`
--
ALTER TABLE `ia_search_index`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ia_search_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_ia_search_type_active` (`entity_type`,`is_active`),
  ADD KEY `idx_ia_search_status` (`status_publicacion`),
  ADD KEY `idx_ia_search_indexed_at` (`indexed_at`);
ALTER TABLE `ia_search_index` ADD FULLTEXT KEY `ft_ia_search_text` (`title`,`search_text`,`search_text_normalized`);

--
-- Indexes for table `ia_search_index_meta`
--
ALTER TABLE `ia_search_index_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ia_search_meta_generated` (`generated_at`);

--
-- Indexes for table `ia_sesiones`
--
ALTER TABLE `ia_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sesion_hash` (`sesion_hash`),
  ADD KEY `idx_ia_sesiones_clase` (`clase_id`),
  ADD KEY `idx_sesiones_activas` (`estado`,`fecha_ultima_interaccion`),
  ADD KEY `idx_ia_sesiones_instancia_estado` (`instancia`,`estado`),
  ADD KEY `idx_ia_sesiones_scope` (`contexto_scope`,`contexto_pagina`),
  ADD KEY `idx_ia_sesiones_entidad` (`entidad_tipo`,`entidad_id`),
  ADD KEY `idx_ia_sesiones_admin_user` (`admin_user`);

--
-- Indexes for table `ia_sesiones_contexto`
--
ALTER TABLE `ia_sesiones_contexto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ia_sesion_contexto` (`instancia`,`sesion_clave`),
  ADD KEY `idx_ia_sesion_contexto_sesion_id` (`sesion_id`);

--
-- Indexes for table `ia_stats_clase`
--
ALTER TABLE `ia_stats_clase`
  ADD PRIMARY KEY (`clase_id`);

--
-- Indexes for table `kits`
--
ALTER TABLE `kits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kits_codigo` (`codigo`),
  ADD UNIQUE KEY `uk_kits_slug` (`slug`),
  ADD KEY `idx_kits_clase` (`clase_id`);
ALTER TABLE `kits` ADD FULLTEXT KEY `ft_kits_ia` (`nombre`,`resumen`);

--
-- Indexes for table `kits_areas`
--
ALTER TABLE `kits_areas`
  ADD PRIMARY KEY (`kit_id`,`area_id`),
  ADD KEY `idx_kits_areas_kit` (`kit_id`),
  ADD KEY `idx_kits_areas_area` (`area_id`);

--
-- Indexes for table `kit_componentes`
--
ALTER TABLE `kit_componentes`
  ADD PRIMARY KEY (`kit_id`,`item_id`),
  ADD KEY `idx_kit_componentes_order` (`kit_id`,`sort_order`),
  ADD KEY `fk_kit_componentes_item` (`item_id`);

--
-- Indexes for table `kit_items`
--
ALTER TABLE `kit_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kit_items_nombre` (`nombre_comun`),
  ADD KEY `idx_kit_items_categoria` (`categoria_id`);
ALTER TABLE `kit_items` ADD FULLTEXT KEY `ft_items_ia` (`nombre_comun`);

--
-- Indexes for table `kit_manuals`
--
ALTER TABLE `kit_manuals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_kit_manual_slug_locale` (`kit_id`,`slug`,`idioma`),
  ADD KEY `idx_kit_manuals_kit` (`kit_id`),
  ADD KEY `idx_kit_manuals_status` (`status`),
  ADD KEY `idx_kit_manuals_kitid_status` (`kit_id`,`status`),
  ADD KEY `idx_km_kit_status_type` (`kit_id`,`status`,`tipo_manual`),
  ADD KEY `idx_km_scope_type` (`ambito`,`tipo_manual`,`status`),
  ADD KEY `idx_km_item_status` (`item_id`,`status`),
  ADD KEY `idx_km_status_pubat` (`status`,`published_at`);

--
-- Indexes for table `lotes`
--
ALTER TABLE `lotes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lotes_codigo` (`codigo_lote`),
  ADD KEY `idx_lotes_kit` (`kit_id`),
  ADD KEY `idx_lotes_contrato` (`contrato_id`),
  ADD KEY `idx_lotes_estado` (`estado_lote`),
  ADD KEY `idx_lotes_disponible` (`cantidad_disponible`);

--
-- Indexes for table `paginas_estaticas`
--
ALTER TABLE `paginas_estaticas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_slug` (`slug`);

--
-- Indexes for table `prompts_clase`
--
ALTER TABLE `prompts_clase`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prompts_clase` (`clase_id`);

--
-- Indexes for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rm_clase` (`clase_id`),
  ADD KEY `idx_rm_order` (`clase_id`,`sort_order`),
  ADD KEY `idx_rm_kit` (`kit_id`),
  ADD KEY `idx_rm_item` (`item_id`),
  ADD KEY `idx_rm_manual` (`manual_id`),
  ADD KEY `idx_rm_tipo_role` (`tipo`,`schema_role`),
  ADD KEY `idx_rm_clase_order` (`clase_id`,`sort_order`),
  ADD KEY `idx_rm_kit_order` (`kit_id`,`sort_order`),
  ADD KEY `idx_rm_item_order` (`item_id`,`sort_order`),
  ADD KEY `idx_rm_manual_order` (`manual_id`,`sort_order`);

--
-- Indexes for table `sitio_config`
--
ALTER TABLE `sitio_config`
  ADD PRIMARY KEY (`clave`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics_eventos`
--
ALTER TABLE `analytics_eventos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=286;

--
-- AUTO_INCREMENT for table `analytics_visitas`
--
ALTER TABLE `analytics_visitas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `atributos_contenidos`
--
ALTER TABLE `atributos_contenidos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `atributos_definiciones`
--
ALTER TABLE `atributos_definiciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `atributos_mapeo`
--
ALTER TABLE `atributos_mapeo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `auditoria_admin`
--
ALTER TABLE `auditoria_admin`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorias_items`
--
ALTER TABLE `categorias_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ciclos`
--
ALTER TABLE `ciclos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `clases`
--
ALTER TABLE `clases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `competencias`
--
ALTER TABLE `competencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `configuracion_ia`
--
ALTER TABLE `configuracion_ia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `footer_enlaces`
--
ALTER TABLE `footer_enlaces`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `footer_grupos`
--
ALTER TABLE `footer_grupos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `guias`
--
ALTER TABLE `guias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `ia_guardrails_log`
--
ALTER TABLE `ia_guardrails_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_logs`
--
ALTER TABLE `ia_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `ia_mensajes`
--
ALTER TABLE `ia_mensajes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `ia_respuestas_cache`
--
ALTER TABLE `ia_respuestas_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ia_resumen_sesion`
--
ALTER TABLE `ia_resumen_sesion`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_search_index`
--
ALTER TABLE `ia_search_index`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_search_index_meta`
--
ALTER TABLE `ia_search_index_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_sesiones`
--
ALTER TABLE `ia_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ia_sesiones_contexto`
--
ALTER TABLE `ia_sesiones_contexto`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kits`
--
ALTER TABLE `kits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kit_items`
--
ALTER TABLE `kit_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `kit_manuals`
--
ALTER TABLE `kit_manuals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paginas_estaticas`
--
ALTER TABLE `paginas_estaticas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prompts_clase`
--
ALTER TABLE `prompts_clase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

-- --------------------------------------------------------

--
-- Structure for view `v_admin_contratos_resumen`
--
DROP TABLE IF EXISTS `v_admin_contratos_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_admin_contratos_resumen`  AS SELECT `c`.`id` AS `id`, `c`.`numero` AS `numero`, `c`.`entidad_contratante` AS `entidad_contratante`, `c`.`departamento` AS `departamento`, `c`.`municipio` AS `municipio`, `c`.`fecha` AS `fecha`, `c`.`fecha_inicio` AS `fecha_inicio`, `c`.`fecha_fin` AS `fecha_fin`, `c`.`valor` AS `valor`, `c`.`valor_ejecutado` AS `valor_ejecutado`, `c`.`valor`- `c`.`valor_ejecutado` AS `saldo_pendiente`, CASE WHEN `c`.`valor` > 0 THEN round(`c`.`valor_ejecutado` / `c`.`valor` * 100,2) ELSE 0 END AS `avance_financiero_pct`, `c`.`estado_contrato` AS `estado_contrato`, `c`.`supervisor` AS `supervisor`, `c`.`updated_at` AS `updated_at` FROM `contratos` AS `c` ;

-- --------------------------------------------------------

--
-- Structure for view `v_admin_entregas_resumen`
--
DROP TABLE IF EXISTS `v_admin_entregas_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_admin_entregas_resumen`  AS SELECT `e`.`id` AS `id`, `e`.`codigo_entrega` AS `codigo_entrega`, `e`.`contrato_id` AS `contrato_id`, `c`.`numero` AS `contrato_numero`, `c`.`entidad_contratante` AS `entidad_contratante`, `e`.`institucion_educativa` AS `institucion_educativa`, coalesce(`e`.`departamento`,`c`.`departamento`) AS `departamento`, `e`.`municipio` AS `municipio`, `e`.`fecha_programada` AS `fecha_programada`, `e`.`fecha` AS `fecha_entrega`, `e`.`estado_entrega` AS `estado_entrega`, `e`.`cantidad_kits` AS `cantidad_kits`, `e`.`recibido_ok` AS `recibido_ok`, `e`.`acta_pdf` AS `acta_pdf`, CASE WHEN `e`.`estado_entrega` in ('programada','reprogramada') AND `e`.`fecha_programada` is not null AND `e`.`fecha_programada` < curdate() THEN 1 ELSE 0 END AS `entrega_atrasada`, `e`.`updated_at` AS `updated_at` FROM (`entregas` `e` join `contratos` `c` on(`c`.`id` = `e`.`contrato_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_admin_lotes_resumen`
--
DROP TABLE IF EXISTS `v_admin_lotes_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_admin_lotes_resumen`  AS SELECT `l`.`id` AS `id`, `l`.`codigo_lote` AS `codigo_lote`, `l`.`kit_id` AS `kit_id`, `k`.`nombre` AS `kit_nombre`, `l`.`contrato_id` AS `contrato_id`, `c`.`numero` AS `contrato_numero`, `l`.`cantidad_total` AS `cantidad_total`, `l`.`cantidad_disponible` AS `cantidad_disponible`, `l`.`cantidad_asignada` AS `cantidad_asignada`, `l`.`cantidad_entregada` AS `cantidad_entregada`, `l`.`estado_lote` AS `estado_lote`, `l`.`ubicacion` AS `ubicacion`, `l`.`fecha_fabricacion` AS `fecha_fabricacion`, `l`.`fecha_caducidad` AS `fecha_caducidad`, CASE WHEN `l`.`cantidad_total` > 0 THEN round(`l`.`cantidad_disponible` / `l`.`cantidad_total` * 100,2) ELSE 0 END AS `stock_disponible_pct`, `l`.`updated_at` AS `updated_at` FROM ((`lotes` `l` join `kits` `k` on(`k`.`id` = `l`.`kit_id`)) left join `contratos` `c` on(`c`.`id` = `l`.`contrato_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_auditoria_ctr_busqueda_30d`
--
DROP TABLE IF EXISTS `v_auditoria_ctr_busqueda_30d`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_auditoria_ctr_busqueda_30d`  AS SELECT cast(`ae`.`created_at` as date) AS `fecha`, sum(case when `ae`.`evento` = 'search_query' then 1 else 0 end) AS `total_busquedas`, sum(case when `ae`.`evento` = 'search_result_click' then 1 else 0 end) AS `total_clics_resultado`, round(100 * sum(case when `ae`.`evento` = 'search_result_click' then 1 else 0 end) / nullif(sum(case when `ae`.`evento` = 'search_query' then 1 else 0 end),0),2) AS `ctr_busqueda_pct` FROM `analytics_eventos` AS `ae` WHERE `ae`.`created_at` >= current_timestamp() - interval 30 day AND `ae`.`instancia` = 'frontend' GROUP BY cast(`ae`.`created_at` as date) ORDER BY cast(`ae`.`created_at` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_auditoria_funnel_home_clase_kit_30d`
--
DROP TABLE IF EXISTS `v_auditoria_funnel_home_clase_kit_30d`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_auditoria_funnel_home_clase_kit_30d`  AS SELECT count(distinct case when `ae`.`tipo_pagina` = 'inicio' and `ae`.`evento` = 'page_view' then `ae`.`session_hash` end) AS `sesiones_home`, count(distinct case when `ae`.`tipo_pagina` = 'clase' and `ae`.`evento` = 'page_view' then `ae`.`session_hash` end) AS `sesiones_clase`, count(distinct case when `ae`.`tipo_pagina` = 'kit' and `ae`.`evento` = 'page_view' then `ae`.`session_hash` end) AS `sesiones_kit`, round(100 * count(distinct case when `ae`.`tipo_pagina` = 'clase' and `ae`.`evento` = 'page_view' then `ae`.`session_hash` end) / nullif(count(distinct case when `ae`.`tipo_pagina` = 'inicio' and `ae`.`evento` = 'page_view' then `ae`.`session_hash` end),0),2) AS `conv_home_a_clase_pct`, round(100 * count(distinct case when `ae`.`tipo_pagina` = 'kit' and `ae`.`evento` = 'page_view' then `ae`.`session_hash` end) / nullif(count(distinct case when `ae`.`tipo_pagina` = 'clase' and `ae`.`evento` = 'page_view' then `ae`.`session_hash` end),0),2) AS `conv_clase_a_kit_pct` FROM `analytics_eventos` AS `ae` WHERE `ae`.`created_at` >= current_timestamp() - interval 30 day AND `ae`.`instancia` = 'frontend' ;

-- --------------------------------------------------------

--
-- Structure for view `v_auditoria_ia_preguntas_sin_resolver_30d`
--
DROP TABLE IF EXISTS `v_auditoria_ia_preguntas_sin_resolver_30d`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_auditoria_ia_preguntas_sin_resolver_30d`  AS SELECT cast(`l`.`fecha_hora` as date) AS `fecha`, `l`.`instancia` AS `instancia`, count(0) AS `total_preguntas_sin_resolver` FROM `ia_logs` AS `l` WHERE `l`.`fecha_hora` >= current_timestamp() - interval 30 day AND `l`.`tipo_evento` in ('error','timeout') GROUP BY cast(`l`.`fecha_hora` as date), `l`.`instancia` ORDER BY cast(`l`.`fecha_hora` as date) DESC, `l`.`instancia` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_auditoria_ia_salud_30d`
--
DROP TABLE IF EXISTS `v_auditoria_ia_salud_30d`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_auditoria_ia_salud_30d`  AS SELECT `l`.`instancia` AS `instancia`, cast(`l`.`fecha_hora` as date) AS `fecha`, count(0) AS `total_eventos_ia`, sum(case when `l`.`tipo_evento` = 'consulta' then 1 else 0 end) AS `total_consultas`, sum(case when `l`.`tipo_evento` = 'error' then 1 else 0 end) AS `total_errores`, sum(case when `l`.`tipo_evento` = 'guardrail_activado' then 1 else 0 end) AS `total_guardrails`, sum(coalesce(`l`.`tokens_usados`,0)) AS `tokens_totales`, round(avg(coalesce(`l`.`tiempo_respuesta_ms`,0)),2) AS `latencia_promedio_ms`, round(100 * sum(case when `l`.`tipo_evento` = 'error' then 1 else 0 end) / nullif(count(0),0),2) AS `tasa_error_pct` FROM `ia_logs` AS `l` WHERE `l`.`fecha_hora` >= current_timestamp() - interval 30 day GROUP BY `l`.`instancia`, cast(`l`.`fecha_hora` as date) ORDER BY cast(`l`.`fecha_hora` as date) DESC, `l`.`instancia` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_auditoria_resumen_ejecutivo`
--
DROP TABLE IF EXISTS `v_auditoria_resumen_ejecutivo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_auditoria_resumen_ejecutivo`  AS SELECT (select count(0) from `analytics_eventos` where `analytics_eventos`.`created_at` >= current_timestamp() - interval 7 day and `analytics_eventos`.`evento` = 'page_view' and `analytics_eventos`.`instancia` = 'frontend') AS `page_views_7d`, (select count(distinct `analytics_eventos`.`session_hash`) from `analytics_eventos` where `analytics_eventos`.`created_at` >= current_timestamp() - interval 7 day and `analytics_eventos`.`instancia` = 'frontend') AS `sesiones_unicas_7d`, (select count(0) from `ia_logs` where `ia_logs`.`fecha_hora` >= current_timestamp() - interval 7 day and `ia_logs`.`tipo_evento` = 'consulta') AS `consultas_ia_7d`, (select count(0) from `ia_logs` where `ia_logs`.`fecha_hora` >= current_timestamp() - interval 7 day and `ia_logs`.`tipo_evento` = 'error') AS `errores_ia_7d`, (select count(0) from `ia_logs` where `ia_logs`.`fecha_hora` >= current_timestamp() - interval 7 day and `ia_logs`.`tipo_evento` = 'guardrail_activado') AS `guardrails_ia_7d`, current_timestamp() AS `fecha_corte` ;

-- --------------------------------------------------------

--
-- Structure for view `v_auditoria_top_clases_7d`
--
DROP TABLE IF EXISTS `v_auditoria_top_clases_7d`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_auditoria_top_clases_7d`  AS SELECT `c`.`id` AS `clase_id`, `c`.`nombre` AS `nombre`, `c`.`slug` AS `slug`, count(0) AS `visitas_totales`, count(distinct coalesce(`ae`.`session_hash`,concat('anon-',`ae`.`id`))) AS `sesiones_unicas`, sum(case when `ae`.`evento` = 'search_result_click' then 1 else 0 end) AS `clics_desde_busqueda`, max(`ae`.`created_at`) AS `ultima_interaccion` FROM (`analytics_eventos` `ae` join `clases` `c` on(`c`.`id` = `ae`.`clase_id`)) WHERE `ae`.`created_at` >= current_timestamp() - interval 7 day AND `ae`.`instancia` = 'frontend' AND `ae`.`evento` in ('page_view','search_result_click') GROUP BY `c`.`id`, `c`.`nombre`, `c`.`slug` ORDER BY count(0) DESC, count(distinct coalesce(`ae`.`session_hash`,concat('anon-',`ae`.`id`))) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_auditoria_top_clases_30d`
--
DROP TABLE IF EXISTS `v_auditoria_top_clases_30d`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_auditoria_top_clases_30d`  AS SELECT `c`.`id` AS `clase_id`, `c`.`nombre` AS `nombre`, `c`.`slug` AS `slug`, count(0) AS `visitas_totales`, count(distinct coalesce(`ae`.`session_hash`,concat('anon-',`ae`.`id`))) AS `sesiones_unicas`, sum(case when `ae`.`evento` = 'search_result_click' then 1 else 0 end) AS `clics_desde_busqueda`, max(`ae`.`created_at`) AS `ultima_interaccion` FROM (`analytics_eventos` `ae` join `clases` `c` on(`c`.`id` = `ae`.`clase_id`)) WHERE `ae`.`created_at` >= current_timestamp() - interval 30 day AND `ae`.`instancia` = 'frontend' AND `ae`.`evento` in ('page_view','search_result_click') GROUP BY `c`.`id`, `c`.`nombre`, `c`.`slug` ORDER BY count(0) DESC, count(distinct coalesce(`ae`.`session_hash`,concat('anon-',`ae`.`id`))) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_auditoria_visitas_legacy_30d`
--
DROP TABLE IF EXISTS `v_auditoria_visitas_legacy_30d`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_auditoria_visitas_legacy_30d`  AS SELECT `av`.`clase_id` AS `clase_id`, `c`.`nombre` AS `nombre`, `c`.`slug` AS `slug`, count(0) AS `visitas_totales`, count(distinct cast(`av`.`visited_at` as date)) AS `dias_con_trafico`, max(`av`.`visited_at`) AS `ultima_visita` FROM (`analytics_visitas` `av` left join `clases` `c` on(`c`.`id` = `av`.`clase_id`)) WHERE `av`.`visited_at` >= current_timestamp() - interval 30 day GROUP BY `av`.`clase_id`, `c`.`nombre`, `c`.`slug` ORDER BY count(0) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_clases_populares_ia`
--
DROP TABLE IF EXISTS `v_clases_populares_ia`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_clases_populares_ia`  AS SELECT `c`.`id` AS `id`, `c`.`nombre` AS `nombre`, `c`.`slug` AS `slug`, `c`.`orden_popularidad` AS `orden_popularidad` FROM `clases` AS `c` WHERE `c`.`activo` = 1 ORDER BY `c`.`orden_popularidad` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_clase_contexto_ia`
--
DROP TABLE IF EXISTS `v_clase_contexto_ia`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_clase_contexto_ia`  AS SELECT `c`.`id` AS `clase_id`, `c`.`nombre` AS `nombre`, `c`.`slug` AS `slug`, `c`.`ciclo` AS `ciclo`, `c`.`dificultad` AS `dificultad`, `c`.`duracion_minutos` AS `duracion_minutos`, `c`.`resumen` AS `resumen`, `c`.`objetivo_aprendizaje` AS `objetivo_aprendizaje`, (select json_arrayagg(`a`.`nombre`) from (`clase_areas` `ca` join `areas` `a` on(`a`.`id` = `ca`.`area_id`)) where `ca`.`clase_id` = `c`.`id`) AS `areas`, (select json_arrayagg(`comp`.`nombre`) from (`clase_competencias` `cc` join `competencias` `comp` on(`comp`.`id` = `cc`.`competencia_id`)) where `cc`.`clase_id` = `c`.`id`) AS `competencias` FROM `clases` AS `c` WHERE `c`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `v_clase_kits_detalle`
--
DROP TABLE IF EXISTS `v_clase_kits_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_clase_kits_detalle`  AS SELECT `k`.`id` AS `kit_id`, `k`.`clase_id` AS `clase_id`, `k`.`nombre` AS `kit_nombre`, `i`.`id` AS `item_id`, `i`.`nombre_comun` AS `item_nombre`, `kc`.`cantidad` AS `cantidad`, `kc`.`es_incluido_kit` AS `es_incluido_kit`, `kc`.`notas` AS `notas` FROM ((`kits` `k` join `kit_componentes` `kc` on(`kc`.`kit_id` = `k`.`id`)) join `kit_items` `i` on(`i`.`id` = `kc`.`item_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_ia_dashboard`
--
DROP TABLE IF EXISTS `v_ia_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_ia_dashboard`  AS SELECT cast(`l`.`fecha_hora` as date) AS `fecha`, count(distinct `l`.`sesion_id`) AS `sesiones_unicas`, count(`l`.`id`) AS `total_eventos`, sum(case when `l`.`tipo_evento` = 'consulta' then 1 else 0 end) AS `total_consultas`, sum(case when `l`.`tipo_evento` = 'error' then 1 else 0 end) AS `total_errores`, sum(case when `l`.`tipo_evento` = 'guardrail_activado' then 1 else 0 end) AS `alertas_seguridad`, sum(`l`.`tokens_usados`) AS `tokens_totales`, avg(`l`.`tiempo_respuesta_ms`) AS `tiempo_promedio_ms`, sum(`l`.`costo_estimado`) AS `costo_total` FROM `ia_logs` AS `l` GROUP BY cast(`l`.`fecha_hora` as date) ORDER BY cast(`l`.`fecha_hora` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_ia_preguntas_frecuentes_clase`
--
DROP TABLE IF EXISTS `v_ia_preguntas_frecuentes_clase`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u626603208_clasedeciencia`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_ia_preguntas_frecuentes_clase`  AS SELECT `c`.`nombre` AS `clase`, `im`.`contenido` AS `pregunta`, count(0) AS `veces_preguntada`, max(`im`.`fecha_hora`) AS `ultima_vez` FROM ((`ia_mensajes` `im` join `ia_sesiones` `s` on(`im`.`sesion_id` = `s`.`id`)) left join `clases` `c` on(`s`.`clase_id` = `c`.`id`)) WHERE `im`.`rol` = 'user' GROUP BY `s`.`clase_id`, `im`.`contenido` HAVING count(0) >= 3 ORDER BY count(0) DESC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analytics_eventos`
--
ALTER TABLE `analytics_eventos`
  ADD CONSTRAINT `fk_ae_sesion_ia` FOREIGN KEY (`sesion_ia_id`) REFERENCES `ia_sesiones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `atributos_contenidos`
--
ALTER TABLE `atributos_contenidos`
  ADD CONSTRAINT `fk_contenidos_atributo` FOREIGN KEY (`atributo_id`) REFERENCES `atributos_definiciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `atributos_mapeo`
--
ALTER TABLE `atributos_mapeo`
  ADD CONSTRAINT `fk_mapeo_atributo` FOREIGN KEY (`atributo_id`) REFERENCES `atributos_definiciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `clase_areas`
--
ALTER TABLE `clase_areas`
  ADD CONSTRAINT `fk_clase_areas_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clase_areas_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `clase_competencias`
--
ALTER TABLE `clase_competencias`
  ADD CONSTRAINT `fk_clase_competencias_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clase_competencias_comp` FOREIGN KEY (`competencia_id`) REFERENCES `competencias` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `clase_kits`
--
ALTER TABLE `clase_kits`
  ADD CONSTRAINT `fk_clase_kits_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clase_kits_kit` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `clase_tags`
--
ALTER TABLE `clase_tags`
  ADD CONSTRAINT `fk_clase_tags_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `fk_entregas_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entrega_lotes`
--
ALTER TABLE `entrega_lotes`
  ADD CONSTRAINT `fk_el_entrega` FOREIGN KEY (`entrega_id`) REFERENCES `entregas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_el_lote` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `footer_enlaces`
--
ALTER TABLE `footer_enlaces`
  ADD CONSTRAINT `fk_fenlaces_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `footer_grupos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `guias`
--
ALTER TABLE `guias`
  ADD CONSTRAINT `fk_guias_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ia_mensajes`
--
ALTER TABLE `ia_mensajes`
  ADD CONSTRAINT `fk_ia_mensajes_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `ia_sesiones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ia_resumen_sesion`
--
ALTER TABLE `ia_resumen_sesion`
  ADD CONSTRAINT `fk_ia_resumen_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `ia_sesiones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ia_sesiones_contexto`
--
ALTER TABLE `ia_sesiones_contexto`
  ADD CONSTRAINT `fk_ia_sesion_contexto_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `ia_sesiones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kits`
--
ALTER TABLE `kits`
  ADD CONSTRAINT `fk_kits_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kits_areas`
--
ALTER TABLE `kits_areas`
  ADD CONSTRAINT `fk_kits_areas_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kits_areas_kit` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kit_componentes`
--
ALTER TABLE `kit_componentes`
  ADD CONSTRAINT `fk_kit_componentes_item` FOREIGN KEY (`item_id`) REFERENCES `kit_items` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kit_componentes_kit` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kit_items`
--
ALTER TABLE `kit_items`
  ADD CONSTRAINT `fk_kit_items_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kit_manuals`
--
ALTER TABLE `kit_manuals`
  ADD CONSTRAINT `fk_kit_manuals_kit` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_km_item` FOREIGN KEY (`item_id`) REFERENCES `kit_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `lotes`
--
ALTER TABLE `lotes`
  ADD CONSTRAINT `fk_lotes_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lotes_kit` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  ADD CONSTRAINT `fk_rm_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rm_item` FOREIGN KEY (`item_id`) REFERENCES `kit_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rm_kit` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rm_manual` FOREIGN KEY (`manual_id`) REFERENCES `kit_manuals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
