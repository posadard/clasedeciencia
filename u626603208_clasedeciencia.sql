-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 21, 2025 at 03:19 PM
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
(6, 'Radio de cristal', 'radio-de-cristal', 4, '[8,9]', 'media', 90, 'Construye un receptor de radio sin batería usando un diodo, bobina y antena. Capta ondas electromagnéticas del aire y conviértelas en audio sin necesidad de electricidad externa.', 'Explicar la propagación de ondas electromagnéticas y la conversión de energía en sistemas de comunicación. Relacionar conceptos de electromagnetismo, resonancia y semiconductores con aplicaciones tecnológicas del entorno.', '/assets/images/clases/radio-cristal-portada.jpg', 'https://www.youtube.com/embed/example-radio-cristal', '{\"edad_min\":14,\"edad_max\":18,\"notas\":\"Manipular alambres y componentes electr\\u00f3nicos con cuidado. Supervisi\\u00f3n adulta recomendada al enrollar bobinas y soldar (si aplica). No acercar imanes potentes a dispositivos electr\\u00f3nicos.\"}', 'Clase de Ciencia - Física: Radio de cristal', 'Ciclo 4 (8° a 9°): Construye un receptor de radio sin batería usando un diodo, bobina y antena. Capta ondas electromagnéticas del aire y conviértelas en audio', 1, 1, 5, 'published', '2025-12-20 10:00:00', 'Clase de Ciencia SAS', '<h2>contenido</h2>\r\n', '2025-12-20 04:46:28', '2025-12-21 15:06:35'),
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
(1, 1, 1, 1, '2025-12-20 21:53:17'),
(2, 2, 1, 1, '2025-12-20 21:53:17'),
(3, 3, 1, 1, '2025-12-20 21:53:17'),
(4, 4, 1, 1, '2025-12-20 21:53:17'),
(5, 5, 1, 1, '2025-12-20 21:53:17'),
(6, 6, 1, 1, '2025-12-21 15:06:35'),
(7, 7, 1, 1, '2025-12-20 21:53:17'),
(8, 8, 1, 1, '2025-12-20 21:53:17'),
(9, 9, 1, 1, '2025-12-20 21:53:17'),
(10, 10, 1, 1, '2025-12-20 21:53:17'),
(11, 11, 1, 1, '2025-12-20 21:53:17'),
(12, 12, 1, 1, '2025-12-20 21:53:17'),
(13, 13, 1, 1, '2025-12-20 21:53:17'),
(14, 14, 1, 1, '2025-12-20 21:53:17'),
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
(6, 'comunicacion'),
(13, 'corriente'),
(4, 'decantacion'),
(10, 'dinamo'),
(6, 'diodo'),
(11, 'eficiencia'),
(3, 'electricidad'),
(6, 'electricidad'),
(7, 'electricidad'),
(10, 'electricidad'),
(13, 'electricidad'),
(13, 'electroiman'),
(6, 'electromagnetismo'),
(7, 'electromagnetismo'),
(10, 'electromagnetismo'),
(13, 'electromagnetismo'),
(3, 'electronica'),
(6, 'electronica'),
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
(6, 'fisica'),
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
(6, 'resonancia'),
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
(6, 'tecnologia'),
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
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('texto','numero','booleano','json','secreto') DEFAULT 'texto',
  `descripcion` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuracion_ia`
--

INSERT INTO `configuracion_ia` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `updated_at`) VALUES
(1, 'palabras_peligro', '[\"fuego\", \"explosión\", \"ácido fuerte\", \"cortocircuito\", \"veneno\"]', 'json', 'Palabras que activan guardrails de seguridad', '2025-12-20 04:46:28');

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
  `slug` varchar(120) DEFAULT NULL,
  `codigo` varchar(64) DEFAULT NULL,
  `version` varchar(32) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kits`
--

INSERT INTO `kits` (`id`, `clase_id`, `nombre`, `slug`, `codigo`, `version`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Microscopio sencillo', NULL, 'KIT-MICROSCOPIO_SENCILLO', '1.0', 1, '2025-12-20 04:46:28', '2025-12-21 04:15:30'),
(2, 2, 'Kit: Pulmón mecánico', NULL, 'KIT-PULMON_MECANICO', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(3, 3, 'Kit: Circuito eléctrico básico', NULL, 'KIT-CIRCUITO_ELECTRICO_BASICO', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(4, 4, 'Kit: Separación de mezclas', NULL, 'KIT-SEPARACION_DE_MEZCLAS', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(5, 5, 'Kit: Test de pH', NULL, 'KIT-TEST_DE_PH', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(6, 6, 'Radio de cristal', NULL, 'KIT-RADIO_DE_CRISTAL', '1.0', 1, '2025-12-20 04:46:28', '2025-12-21 14:49:32'),
(7, 7, 'Kit: Motor eléctrico simple', NULL, 'KIT-MOTOR_ELECTRICO_SIMPLE', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(8, 8, 'Kit: Osmosis con vegetales', NULL, 'KIT-OSMOSIS_CON_VEGETALES', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(9, 9, 'Kit: Carro trampa de ratón', NULL, 'KIT-CARRO_TRAMPA_DE_RATON', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(10, 10, 'Kit: Generador manual (dinamo)', NULL, 'KIT-GENERADOR_MANUAL_DINAMO', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(11, 11, 'Kit: Carro solar', NULL, 'KIT-CARRO_SOLAR', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(12, 12, 'Kit: Turbina eólica de mesa', NULL, 'KIT-TURBINA_EOLICA_DE_MESA', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(13, 13, 'Kit: Electroimán', NULL, 'KIT-ELECTROIMAN', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(14, 14, 'Kit: Tratamiento de agua', NULL, 'KIT-TRATAMIENTO_DE_AGUA', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28'),
(15, 15, 'Kit: Análisis químico del entorno', NULL, 'KIT-ANALISIS_QUIMICO_DEL_ENTORNO', '1.0', 1, '2025-12-20 04:46:28', '2025-12-20 04:46:28');

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
(6, 16, 1.00, 1, 'Detector', 1),
(6, 17, 1.00, 1, 'Audio', 2),
(6, 18, 5.00, 1, 'Bobina', 3),
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
  `categoria_id` int(11) DEFAULT NULL,
  `advertencias_seguridad` text DEFAULT NULL,
  `unidad` varchar(32) DEFAULT NULL,
  `sku` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kit_items`
--

INSERT INTO `kit_items` (`id`, `nombre_comun`, `categoria_id`, `advertencias_seguridad`, `unidad`, `sku`) VALUES
(1, 'Lente plástico 10x', 3, 'Frágil, manipular con cuidado', 'pcs', 'BIO-LEN-10X'),
(2, 'Cartón rígido', 5, NULL, 'pcs', 'TEC-CAR-RIG'),
(3, 'Banda elástica', 5, NULL, 'pcs', 'TEC-BAN-ELA'),
(4, 'Globo de látex', 3, 'Riesgo de asfixia, no apto <8 años', 'pcs', 'BIO-GLO-LAT'),
(5, 'Botella plástica 500ml', 5, NULL, 'pcs', 'TEC-BOT-500'),
(6, 'Bomba de aire manual', 6, NULL, 'pcs', 'HER-BOM-AIR'),
(7, 'Pila AA', 1, '⚠️ No cortocircuitar', 'pcs', 'ELE-PIL-AA'),
(8, 'Porta baterías AA', 1, NULL, 'pcs', 'ELE-POR-AA'),
(9, 'Cable conductor', 1, NULL, 'm', 'ELE-CAB-CON'),
(10, 'Interruptor mini', 1, NULL, 'pcs', 'ELE-INT-MIN'),
(11, 'Bombillo LED 3V', 1, NULL, 'pcs', 'ELE-LED-3V'),
(12, 'Papel filtro', 4, '⚠️ Material frágil', 'pcs', 'QUI-PAP-FIL'),
(13, 'Embudo plástico', 4, NULL, 'pcs', 'QUI-EMB-PLA'),
(14, 'Vaso precipitado plástico', 4, NULL, 'pcs', 'QUI-VAS-PLA'),
(15, 'Tiras de pH', 4, NULL, 'pcs', 'QUI-TIR-PH'),
(16, 'Diode germanio', 1, 'Material frágil', 'pcs', 'ELE-DIO-GER'),
(17, 'Auricular cristal', 1, NULL, 'pcs', 'ELE-AUR-CRI'),
(18, 'Alambre esmaltado 28AWG', 1, NULL, 'm', 'ELE-ALM-28'),
(19, 'Imán neodimio', 2, '⚠️ Mantener lejos de dispositivos', 'pcs', 'MAG-IMA-NEO'),
(20, 'Clavo de hierro', 2, NULL, 'pcs', 'MAG-CLA-HIE'),
(21, 'Trampa de ratón', 5, '⚠️ Riesgo de pellizco', 'pcs', 'TEC-TRA-RAT'),
(22, 'Rueda plástica 50mm', 5, NULL, 'pcs', 'TEC-RUE-50'),
(23, 'Eje metálico', 5, NULL, 'pcs', 'TEC-EJE-MET'),
(24, 'Motor DC 3-6V', 1, NULL, 'pcs', 'ELE-MOT-DC'),
(25, 'Manivela plástica', 5, NULL, 'pcs', 'TEC-MAN-PLA'),
(26, 'Panel solar 5V', 5, NULL, 'pcs', 'TEC-PAN-5V'),
(27, 'Hélice plástica', 5, NULL, 'pcs', 'TEC-HEL-PLA'),
(28, 'Carbón activado', NULL, NULL, 'g', 'AMB-CAR-ACT'),
(29, 'Arena fina', NULL, NULL, 'g', 'AMB-ARE-FIN'),
(30, 'Grava', NULL, NULL, 'g', 'AMB-GRA-STD'),
(31, 'Sal de mesa', 4, NULL, 'g', 'QUI-SAL-MES'),
(32, 'Rodaja de papa', 3, NULL, 'pcs', 'BIO-ROD-PAP');

-- --------------------------------------------------------

--
-- Table structure for table `kit_manuals`
--

CREATE TABLE `kit_manuals` (
  `id` int(11) NOT NULL,
  `kit_id` int(11) NOT NULL,
  `slug` varchar(120) NOT NULL COMMENT 'Slug del manual (por kit/idioma)',
  `version` varchar(32) NOT NULL DEFAULT '1.0',
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `idioma` varchar(10) NOT NULL DEFAULT 'es-CO',
  `time_minutes` int(11) DEFAULT NULL COMMENT 'Tiempo estimado de armado',
  `dificultad_ensamble` varchar(32) DEFAULT NULL,
  `pasos_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pasos_json`)),
  `herramientas_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`herramientas_json`)),
  `seguridad_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`seguridad_json`)),
  `html` mediumtext DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
  `clase_id` int(11) NOT NULL,
  `tipo` enum('imagen','video','pdf','link') NOT NULL,
  `url` varchar(255) NOT NULL,
  `titulo` varchar(180) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recursos_multimedia`
--

INSERT INTO `recursos_multimedia` (`id`, `clase_id`, `tipo`, `url`, `titulo`, `descripcion`, `sort_order`, `created_at`) VALUES
(1, 15, 'link', 'https://clasedeciencia.com/clase/analisis-quimico-del-entorno', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(2, 11, 'link', 'https://clasedeciencia.com/clase/carro-solar', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(3, 9, 'link', 'https://clasedeciencia.com/clase/carro-trampa-de-raton', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(4, 3, 'link', 'https://clasedeciencia.com/clase/circuito-electrico-basico', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(5, 13, 'link', 'https://clasedeciencia.com/clase/electroiman', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(6, 10, 'link', 'https://clasedeciencia.com/clase/generador-manual-dinamo', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(7, 1, 'link', 'https://clasedeciencia.com/clase/microscopio-sencillo', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(8, 7, 'link', 'https://clasedeciencia.com/clase/motor-electrico-simple', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(9, 8, 'link', 'https://clasedeciencia.com/clase/osmosis-con-vegetales', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(10, 2, 'link', 'https://clasedeciencia.com/clase/pulmon-mecanico', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(11, 6, 'link', 'https://clasedeciencia.com/clase/radio-de-cristal', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(12, 4, 'link', 'https://clasedeciencia.com/clase/separacion-de-mezclas', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(13, 5, 'link', 'https://clasedeciencia.com/clase/test-de-ph', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(14, 14, 'link', 'https://clasedeciencia.com/clase/tratamiento-de-agua', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28'),
(15, 12, 'link', 'https://clasedeciencia.com/clase/turbina-eolica-de-mesa', 'Guía interactiva', 'Accede a la guía digital de la clase', 1, '2025-12-20 04:46:28');

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
  ADD UNIQUE KEY `uk_kits_slug` (`slug`),
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
-- Indexes for table `kit_manuals`
--
ALTER TABLE `kit_manuals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_kit_manual_slug_locale` (`kit_id`,`slug`,`idioma`),
  ADD KEY `idx_kit_manuals_kit` (`kit_id`),
  ADD KEY `idx_kit_manuals_status` (`status`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prompts_clase`
--
ALTER TABLE `prompts_clase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- Constraints for table `kit_manuals`
--
ALTER TABLE `kit_manuals`
  ADD CONSTRAINT `fk_kit_manuals_kit` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  ADD CONSTRAINT `fk_rm_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
