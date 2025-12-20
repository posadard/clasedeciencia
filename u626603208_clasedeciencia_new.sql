-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 20, 2025 at 04:35 AM
-- Server version: 11.8.3-MariaDB-log
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
  `slug` varchar(80) NOT NULL
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
  `canonical_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `orden_popularidad` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `autor` varchar(120) DEFAULT NULL,
  `contenido_html` mediumtext DEFAULT NULL,
  `seccion_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clase_areas`
--

CREATE TABLE `clase_areas` (
  `clase_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clase_competencias`
--

CREATE TABLE `clase_competencias` (
  `clase_id` int(11) NOT NULL,
  `competencia_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clase_tags`
--

CREATE TABLE `clase_tags` (
  `clase_id` int(11) NOT NULL,
  `tag` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competencias`
--

CREATE TABLE `competencias` (
  `id` int(11) NOT NULL,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `configuracion_ia`
--

CREATE TABLE `configuracion_ia` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('texto','numero','booleano','json','secreto') DEFAULT 'texto',
  `descripcion` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contratos`
--

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `numero` varchar(64) NOT NULL,
  `entidad_contratante` varchar(255) NOT NULL,
  `departamento` varchar(120) NOT NULL,
  `valor` decimal(16,2) NOT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `institucion_educativa` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `acta_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp()
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
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `estado` enum('activa','finalizada','timeout') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `kits`
--

CREATE TABLE `kits` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `codigo` varchar(64) DEFAULT NULL,
  `version` varchar(32) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `kit_items`
--

CREATE TABLE `kit_items` (
  `id` int(11) NOT NULL,
  `nombre_comun` varchar(160) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `advertencias_seguridad` text DEFAULT NULL,
  `unidad` varchar(32) DEFAULT NULL,
  `sku` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `recursos_multimedia`
--

CREATE TABLE `recursos_multimedia` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `tipo` enum('imagen','video','pdf','link') NOT NULL,
  `url` varchar(255) NOT NULL,
  `titulo` varchar(180) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secciones`
--

CREATE TABLE `secciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Indexes for table `categorias_items`
--
ALTER TABLE `categorias_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categorias_items_slug` (`slug`);

--
-- Indexes for table `clases`
--
ALTER TABLE `clases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_clases_slug` (`slug`),
  ADD KEY `idx_clases_activo_ciclo` (`activo`,`ciclo`),
  ADD KEY `idx_clases_status_published` (`status`,`published_at`),
  ADD KEY `idx_clases_popularidad` (`orden_popularidad`),
  ADD KEY `idx_clases_seccion` (`seccion_id`);

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
  ADD UNIQUE KEY `uq_config_ia_clave` (`clave`);

--
-- Indexes for table `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contratos_numero` (`numero`);

--
-- Indexes for table `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entregas_contrato` (`contrato_id`);

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
-- Indexes for table `ia_sesiones`
--
ALTER TABLE `ia_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sesion_hash` (`sesion_hash`),
  ADD KEY `idx_ia_sesiones_clase` (`clase_id`),
  ADD KEY `idx_sesiones_activas` (`estado`,`fecha_ultima_interaccion`);

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
  ADD KEY `idx_kits_clase` (`clase_id`);

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
  ADD KEY `idx_rm_order` (`clase_id`,`sort_order`);

--
-- Indexes for table `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_secciones_slug` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics_visitas`
--
ALTER TABLE `analytics_visitas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorias_items`
--
ALTER TABLE `categorias_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clases`
--
ALTER TABLE `clases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competencias`
--
ALTER TABLE `competencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `configuracion_ia`
--
ALTER TABLE `configuracion_ia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `guias`
--
ALTER TABLE `guias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_guardrails_log`
--
ALTER TABLE `ia_guardrails_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_logs`
--
ALTER TABLE `ia_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_mensajes`
--
ALTER TABLE `ia_mensajes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_respuestas_cache`
--
ALTER TABLE `ia_respuestas_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ia_sesiones`
--
ALTER TABLE `ia_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kits`
--
ALTER TABLE `kits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kit_items`
--
ALTER TABLE `kit_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prompts_clase`
--
ALTER TABLE `prompts_clase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `clases`
--
ALTER TABLE `clases`
  ADD CONSTRAINT `fk_clases_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
-- Constraints for table `kits`
--
ALTER TABLE `kits`
  ADD CONSTRAINT `fk_kits_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  ADD CONSTRAINT `fk_rm_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
