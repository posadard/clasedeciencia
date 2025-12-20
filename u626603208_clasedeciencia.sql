-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 20, 2025 at 01:08 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `analytics_interacciones`
--

CREATE TABLE `analytics_interacciones` (
  `id` bigint(20) NOT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
  `tipo_interaccion` enum('descarga_pdf','consulta_ia','click_material','compartir') DEFAULT NULL,
  `detalles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalles`)),
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `sesion_hash` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `analytics_visitas`
--

CREATE TABLE `analytics_visitas` (
  `id` bigint(20) NOT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
  `tipo_pagina` enum('home','catalogo','proyecto','material','busqueda') DEFAULT NULL,
  `url_visitada` varchar(500) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `pais` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `dispositivo` enum('mobile','tablet','desktop') DEFAULT NULL,
  `navegador` varchar(100) DEFAULT NULL,
  `sesion_hash` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `color` varchar(7) DEFAULT NULL COMMENT 'Código hex para badges',
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `nombre`, `slug`, `color`, `descripcion`) VALUES
(1, 'Física', 'fisica', '#2c5aa0', 'Proyectos relacionados con fuerzas, energía, electricidad y movimiento'),
(2, 'Química', 'quimica', '#e74c3c', 'Proyectos relacionados con reacciones químicas, mezclas y transformaciones'),
(3, 'Biología', 'biologia', '#27ae60', 'Proyectos relacionados con seres vivos, células y ecosistemas'),
(4, 'Tecnología', 'tecnologia', '#f39c12', 'Proyectos de ingeniería, electrónica y construcción'),
(5, 'Ambiental', 'ambiental', '#16a085', 'Proyectos relacionados con medio ambiente y sostenibilidad');

-- --------------------------------------------------------

--
-- Table structure for table `categorias_materiales`
--

CREATE TABLE `categorias_materiales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icono` varchar(50) DEFAULT NULL COMMENT 'emoji o clase CSS',
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorias_materiales`
--

INSERT INTO `categorias_materiales` (`id`, `nombre`, `slug`, `icono`, `descripcion`) VALUES
(1, 'Electrónica', 'electronica', '⚡', 'Componentes electrónicos, cables, pilas'),
(2, 'Química', 'quimica', '🧪', 'Sustancias químicas seguras, indicadores, reactivos'),
(3, 'Mecánica', 'mecanica', '⚙️', 'Piezas mecánicas, engranajes, poleas'),
(4, 'Óptica', 'optica', '🔍', 'Lentes, espejos, prismas'),
(5, 'Materiales de Construcción', 'construccion', '🔨', 'Madera, cartón, pegamentos'),
(6, 'Herramientas', 'herramientas', '🛠️', 'Destornilladores, pinzas, tijeras'),
(7, 'Biológicos', 'biologicos', '🌱', 'Semillas, muestras, cultivos');

-- --------------------------------------------------------

--
-- Table structure for table `competencias`
--

CREATE TABLE `competencias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('indagacion','explicacion','uso_conocimiento') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `competencias`
--

INSERT INTO `competencias` (`id`, `nombre`, `descripcion`, `tipo`) VALUES
(1, 'Observo fenómenos específicos', 'Capacidad de identificar y describir fenómenos naturales y científicos', 'indagacion'),
(2, 'Formulo preguntas', 'Planteo preguntas sobre fenómenos observados', 'indagacion'),
(3, 'Formulo hipótesis', 'Propongo explicaciones previas basadas en conocimientos', 'indagacion'),
(4, 'Realizo mediciones', 'Registro datos cuantitativos de forma precisa', 'indagacion'),
(5, 'Registro observaciones', 'Documento de forma sistemática lo observado', 'indagacion'),
(6, 'Analizo resultados', 'Interpreto datos y busco patrones', 'indagacion'),
(7, 'Establezco relaciones causales', 'Identifico causa y efecto en fenómenos', 'explicacion'),
(8, 'Modelo fenómenos', 'Represento procesos científicos mediante modelos', 'explicacion'),
(9, 'Uso conceptos científicos', 'Aplico terminología y conceptos apropiados', 'explicacion'),
(10, 'Argumento con evidencia', 'Sustento conclusiones con datos obtenidos', 'explicacion'),
(11, 'Aplico conocimientos a situaciones', 'Uso lo aprendido en contextos reales', 'uso_conocimiento'),
(12, 'Propongo soluciones', 'Planteo alternativas basadas en conocimiento científico', 'uso_conocimiento'),
(13, 'Tomo decisiones informadas', 'Evalúo opciones considerando evidencia científica', 'uso_conocimiento');

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
(1, 'groq_api_key', '', 'secreto', 'API Key de Groq (dejar vacío hasta configurar en admin)', '2025-12-20 01:08:37'),
(2, 'groq_model', 'llama-3.3-70b-versatile', 'texto', 'Modelo de Groq a utilizar', '2025-12-20 01:08:37'),
(3, 'groq_temperature', '0.7', 'numero', 'Temperatura del modelo (0-1)', '2025-12-20 01:08:37'),
(4, 'groq_max_tokens', '2000', 'numero', 'Máximo de tokens por respuesta', '2025-12-20 01:08:37'),
(5, 'ia_activa', '1', 'booleano', 'Activar/desactivar asistente IA globalmente', '2025-12-20 01:08:37'),
(6, 'guardrails_activos', '1', 'booleano', 'Activar validación de seguridad en respuestas', '2025-12-20 01:08:37'),
(7, 'palabras_peligro', '[\"fuego sin supervisión\",\"explosión\",\"ácido fuerte\",\"químico peligroso\",\"alta temperatura sin control\"]', 'json', 'Lista de palabras/frases que activan alerta de seguridad', '2025-12-20 01:08:37'),
(8, 'mensaje_guardrail', '⚠️ Esta pregunta requiere supervisión de tu profesor. Por seguridad, consulta antes de intentar modificaciones al experimento.', 'texto', 'Mensaje cuando se detecta contenido peligroso', '2025-12-20 01:08:37'),
(9, 'contexto_sistema', 'Eres un asistente educativo para proyectos científicos de estudiantes colombianos de grados 6° a 11°. Tu rol es explicar conceptos, resolver dudas sobre procedimientos y fomentar el pensamiento crítico. NUNCA sugieras modificaciones peligrosas a los experimentos.', 'texto', 'Prompt del sistema para la IA', '2025-12-20 01:08:37'),
(10, 'max_conversaciones_dia', '50', 'numero', 'Límite de conversaciones por día (para controlar costos)', '2025-12-20 01:08:37'),
(11, 'log_ia_activo', '1', 'booleano', 'Guardar logs de todas las consultas a IA', '2025-12-20 01:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `contratos`
--

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `numero_contrato` varchar(100) NOT NULL,
  `entidad_contratante` varchar(255) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `municipios_alcance` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`municipios_alcance`)),
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `valor_contrato` decimal(15,2) DEFAULT NULL,
  `objeto_contrato` text DEFAULT NULL,
  `supervisor` varchar(255) DEFAULT NULL,
  `ie_beneficiarias` int(11) DEFAULT NULL,
  `estudiantes_estimados` int(11) DEFAULT NULL,
  `docentes_estimados` int(11) DEFAULT NULL,
  `ciclos_incluidos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ciclos_incluidos`)),
  `grados_incluidos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grados_incluidos`)),
  `estado` enum('borrador','activo','ejecucion','finalizado') DEFAULT 'borrador',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contrato_proyectos`
--

CREATE TABLE `contrato_proyectos` (
  `contrato_id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `cantidad_kits` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `institucion_educativa` varchar(255) NOT NULL,
  `codigo_dane` varchar(50) DEFAULT NULL,
  `municipio` varchar(100) NOT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_entrega` datetime NOT NULL,
  `responsable_entrega` varchar(255) DEFAULT NULL,
  `responsable_recepcion` varchar(255) DEFAULT NULL,
  `cargo_recepcion` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `evidencia_fotografica` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`evidencia_fotografica`)),
  `firma_digital` varchar(255) DEFAULT NULL,
  `acta_generada` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entrega_lotes`
--

CREATE TABLE `entrega_lotes` (
  `entrega_id` int(11) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `cantidad_entregada` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guias`
--

CREATE TABLE `guias` (
  `id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `version` varchar(20) DEFAULT '1.0',
  `introduccion` text DEFAULT NULL,
  `materiales_kit` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de materiales incluidos' CHECK (json_valid(`materiales_kit`)),
  `materiales_adicionales` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de materiales externos' CHECK (json_valid(`materiales_adicionales`)),
  `seccion_seguridad` text DEFAULT NULL,
  `pasos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de pasos con texto, imagenes, videos' CHECK (json_valid(`pasos`)),
  `explicacion_cientifica` text DEFAULT NULL,
  `conceptos_clave` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de conceptos' CHECK (json_valid(`conceptos_clave`)),
  `conexiones_realidad` text DEFAULT NULL,
  `para_profundizar` text DEFAULT NULL,
  `competencias_men` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`competencias_men`)),
  `dba_relacionados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dba_relacionados`)),
  `estandares_men` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`estandares_men`)),
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ia_guardrails_log`
--

CREATE TABLE `ia_guardrails_log` (
  `id` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
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
  `proyecto_id` int(11) DEFAULT NULL,
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
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Info adicional: temperatura, modelo usado, etc.' CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ia_respuestas_cache`
--

CREATE TABLE `ia_respuestas_cache` (
  `id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `pregunta_normalizada` varchar(500) NOT NULL COMMENT 'Pregunta sin acentos, lowercase',
  `pregunta_original` text NOT NULL,
  `respuesta` text NOT NULL,
  `veces_usada` int(11) DEFAULT 0,
  `ultima_vez_usada` timestamp NULL DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ia_sesiones`
--

CREATE TABLE `ia_sesiones` (
  `id` int(11) NOT NULL,
  `sesion_hash` varchar(64) NOT NULL COMMENT 'Hash anónimo del usuario',
  `proyecto_id` int(11) DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_ultima_interaccion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_mensajes` int(11) DEFAULT 0,
  `tokens_usados` int(11) DEFAULT 0,
  `estado` enum('activa','finalizada','timeout') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ia_stats_proyecto`
--

CREATE TABLE `ia_stats_proyecto` (
  `proyecto_id` int(11) NOT NULL,
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
-- Table structure for table `justificacion_ctei`
--

CREATE TABLE `justificacion_ctei` (
  `contrato_id` int(11) NOT NULL,
  `justificacion_ctei` text DEFAULT NULL,
  `actividades_decreto_591` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`actividades_decreto_591`)),
  `alineacion_ley_1286` text DEFAULT NULL,
  `competencias_men_globales` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`competencias_men_globales`)),
  `metodologia_pedagogica` text DEFAULT NULL,
  `componente_innovacion` text DEFAULT NULL,
  `indicadores_propuestos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`indicadores_propuestos`)),
  `metas_propuestas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metas_propuestas`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lotes_kits`
--

CREATE TABLE `lotes_kits` (
  `id` int(11) NOT NULL,
  `codigo_lote` varchar(100) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_produccion` date DEFAULT NULL,
  `estado` enum('producido','bodega','despachado','entregado') DEFAULT 'producido',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materiales`
--

CREATE TABLE `materiales` (
  `id` int(11) NOT NULL,
  `nombre_comun` varchar(255) NOT NULL,
  `nombre_tecnico` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `advertencias_seguridad` text DEFAULT NULL,
  `manejo_recomendado` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prompts_proyecto`
--

CREATE TABLE `prompts_proyecto` (
  `id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `prompt_contexto` text NOT NULL COMMENT 'Contexto específico del proyecto para la IA',
  `conocimientos_previos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Conceptos que el estudiante debe saber' CHECK (json_valid(`conocimientos_previos`)),
  `enfoque_pedagogico` text DEFAULT NULL COMMENT 'Cómo debe guiar la IA en este proyecto',
  `preguntas_frecuentes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'FAQs del proyecto para respuestas rápidas' CHECK (json_valid(`preguntas_frecuentes`)),
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proyectos`
--

CREATE TABLE `proyectos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `ciclo` enum('1','2','3') NOT NULL COMMENT '1:6-7°, 2:8-9°, 3:10-11°',
  `grados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array de grados [6,7]' CHECK (json_valid(`grados`)),
  `areas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array de IDs de areas' CHECK (json_valid(`areas`)),
  `duracion_minutos` int(11) DEFAULT 60,
  `dificultad` enum('facil','medio','dificil') DEFAULT 'medio',
  `resumen` text DEFAULT NULL,
  `objetivo_aprendizaje` text DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `video_portada` varchar(255) DEFAULT NULL,
  `seguridad` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{edad_min, requiere_supervision, advertencias[]}' CHECK (json_valid(`seguridad`)),
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `destacado` tinyint(1) DEFAULT 0,
  `orden_popularidad` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proyectos`
--

INSERT INTO `proyectos` (`id`, `nombre`, `slug`, `ciclo`, `grados`, `areas`, `duracion_minutos`, `dificultad`, `resumen`, `objetivo_aprendizaje`, `imagen_portada`, `video_portada`, `seguridad`, `seo_title`, `seo_description`, `canonical_url`, `activo`, `destacado`, `orden_popularidad`, `created_at`, `updated_at`) VALUES
(1, 'Circuito Eléctrico Básico', 'circuito-electrico-basico', '1', '[6, 7]', '[1]', 60, 'facil', 'Construye tu primer circuito eléctrico con interruptor, bombillo y batería. Aprende cómo fluye la electricidad.', 'Comprender los conceptos básicos de corriente eléctrica y circuitos cerrados mediante la construcción de un circuito funcional.', NULL, NULL, '{\"edad_min\": 11, \"edad_max\": 13, \"requiere_supervision\": true, \"advertencias\": [\"No conectar a corriente del hogar\", \"Verificar polaridad de la batería\", \"No hacer cortocircuito\"]}', 'Circuito Eléctrico Básico - Proyecto de Física Grado 6° y 7°', 'Aprende a construir circuitos eléctricos simples. Proyecto ideal para estudiantes de 6° y 7° grado. Incluye materiales y guía paso a paso.', NULL, 1, 1, 100, '2025-12-20 00:59:33', '2025-12-20 00:59:33'),
(2, 'Separación de Mezclas', 'separacion-de-mezclas', '1', '[6, 7]', '[2]', 45, 'facil', 'Aprende diferentes métodos para separar mezclas: filtración, decantación y evaporación usando materiales cotidianos.', 'Identificar y aplicar métodos físicos de separación de mezclas según las propiedades de sus componentes.', NULL, NULL, '{\"edad_min\": 11, \"edad_max\": 13, \"requiere_supervision\": true, \"advertencias\": [\"Cuidado con agua caliente en evaporación\", \"No probar las sustancias\", \"Usar gafas de protección\"]}', 'Separación de Mezclas - Experimento de Química Grado 6° y 7°', 'Descubre cómo separar mezclas con métodos simples. Proyecto de química para estudiantes de 6° y 7°. Guía completa incluida.', NULL, 1, 1, 95, '2025-12-20 00:59:33', '2025-12-20 00:59:33'),
(3, 'Test de pH', 'test-de-ph', '1', '[6, 7]', '[2]', 50, 'facil', 'Crea tu propio indicador de pH con repollo morado y clasifica sustancias cotidianas como ácidas o básicas.', 'Comprender el concepto de pH y la clasificación de sustancias según su acidez o alcalinidad.', NULL, NULL, '{\"edad_min\": 11, \"edad_max\": 13, \"requiere_supervision\": true, \"advertencias\": [\"No mezclar sustancias desconocidas\", \"Evitar contacto con ojos\", \"Lavar manos después del experimento\"]}', 'Test de pH con Repollo Morado - Química Grado 6° y 7°', 'Aprende sobre ácidos y bases creando tu indicador de pH. Experimento seguro para estudiantes de 6° y 7°.', NULL, 1, 0, 85, '2025-12-20 00:59:33', '2025-12-20 00:59:33'),
(4, 'Radio de Cristal', 'radio-de-cristal', '2', '[8, 9]', '[1, 4]', 120, 'medio', 'Construye un receptor de radio AM que funciona sin baterías, aprovechando únicamente la energía de las ondas electromagnéticas.', 'Comprender el funcionamiento de las ondas electromagnéticas y su aplicación en las comunicaciones mediante la construcción de un receptor de radio.', NULL, NULL, '{\"edad_min\": 13, \"edad_max\": 15, \"requiere_supervision\": true, \"advertencias\": [\"Manipular con cuidado el diodo detector\", \"Verificar que la antena no toque cables eléctricos\", \"Componentes delicados - evitar caídas\"]}', 'Radio de Cristal - Proyecto de Física Grado 8° y 9°', 'Construye tu propio radio sin baterías. Aprende sobre ondas electromagnéticas. Proyecto para estudiantes de 8° y 9° grado.', NULL, 1, 1, 90, '2025-12-20 00:59:33', '2025-12-20 00:59:33'),
(5, 'Motor Eléctrico Simple', 'motor-electrico-simple', '2', '[8, 9]', '[1, 4]', 90, 'medio', 'Construye un motor eléctrico funcional usando un imán, alambre de cobre y una batería. Observa la conversión de energía eléctrica en movimiento.', 'Explicar la relación entre electricidad y magnetismo, y comprender cómo un motor convierte energía eléctrica en energía mecánica.', NULL, NULL, '{\"edad_min\": 13, \"edad_max\": 15, \"requiere_supervision\": true, \"advertencias\": [\"Cuidado con el alambre al enrollar - puede cortarse\", \"No usar baterías de más de 9V\", \"El motor puede calentarse al funcionar mucho tiempo\"]}', 'Motor Eléctrico Simple - Física y Tecnología Grado 8° y 9°', 'Construye un motor eléctrico real. Aprende electromagnetismo de forma práctica. Para estudiantes de 8° y 9°.', NULL, 1, 1, 88, '2025-12-20 00:59:33', '2025-12-20 00:59:33'),
(6, 'Osmosis con Vegetales', 'osmosis-con-vegetales', '2', '[8, 9]', '[3]', 60, 'facil', 'Observa el proceso de ósmosis usando papa o zanahoria en diferentes concentraciones de sal. Aprende sobre transporte celular.', 'Comprender el proceso de ósmosis y su importancia en las células mediante experimentación con tejidos vegetales.', NULL, NULL, '{\"edad_min\": 13, \"edad_max\": 15, \"requiere_supervision\": false, \"advertencias\": [\"Usar cuchillo con precaución\", \"No consumir los vegetales del experimento\"]}', 'Ósmosis con Vegetales - Biología Grado 8° y 9°', 'Descubre cómo funciona la ósmosis celular. Experimento visual con papas. Para estudiantes de biología de 8° y 9°.', NULL, 1, 0, 80, '2025-12-20 00:59:33', '2025-12-20 00:59:33'),
(7, 'Electroimán', 'electroiman', '3', '[10, 11]', '[1]', 75, 'medio', 'Construye un electroimán y analiza cómo variables como número de vueltas y corriente afectan su fuerza magnética.', 'Analizar cuantitativamente la relación entre corriente eléctrica, número de espiras y fuerza del campo magnético generado.', NULL, NULL, '{\"edad_min\": 15, \"edad_max\": 18, \"requiere_supervision\": true, \"advertencias\": [\"No usar más de 12V\", \"El núcleo puede calentarse\", \"No acercar a dispositivos electrónicos\"]}', 'Electroimán - Experimento de Física Grado 10° y 11°', 'Analiza variables electromagnéticas construyendo un electroimán. Proyecto avanzado para estudiantes de 10° y 11°.', NULL, 1, 1, 85, '2025-12-20 00:59:33', '2025-12-20 00:59:33'),
(8, 'Tratamiento de Agua', 'tratamiento-de-agua', '3', '[10, 11]', '[2, 5]', 90, 'medio', 'Construye un sistema de filtración y purificación de agua. Analiza la efectividad de diferentes métodos: filtración, adsorción y cloración.', 'Evaluar la efectividad de diferentes procesos de tratamiento de agua y comprender su aplicación en contextos reales.', NULL, NULL, '{\"edad_min\": 15, \"edad_max\": 18, \"requiere_supervision\": true, \"advertencias\": [\"No beber el agua tratada\", \"Usar cloro con precaución\", \"Manipular carbón activado con guantes\"]}', 'Tratamiento de Agua - Química Ambiental Grado 10° y 11°', 'Construye un sistema de purificación de agua. Aprende métodos de tratamiento. Para estudiantes de 10° y 11°.', NULL, 1, 1, 82, '2025-12-20 00:59:33', '2025-12-20 00:59:33'),
(9, 'Turbina Eólica de Mesa', 'turbina-eolica', '3', '[10, 11]', '[1, 4, 5]', 120, 'dificil', 'Diseña y construye una turbina eólica funcional. Mide su eficiencia y analiza factores que afectan la generación eléctrica.', 'Analizar la conversión de energía cinética del viento en energía eléctrica, evaluando eficiencia y variables de diseño.', NULL, NULL, '{\"edad_min\": 15, \"edad_max\": 18, \"requiere_supervision\": true, \"advertencias\": [\"Aspas en movimiento - mantener distancia\", \"Verificar conexiones eléctricas\", \"Usar ventilador con precaución\"]}', 'Turbina Eólica - Energía Renovable Grado 10° y 11°', 'Construye una turbina eólica funcional. Analiza eficiencia energética. Proyecto avanzado para 10° y 11°.', NULL, 1, 0, 75, '2025-12-20 00:59:33', '2025-12-20 00:59:33');

-- --------------------------------------------------------

--
-- Table structure for table `proyecto_areas`
--

CREATE TABLE `proyecto_areas` (
  `proyecto_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proyecto_areas`
--

INSERT INTO `proyecto_areas` (`proyecto_id`, `area_id`) VALUES
(1, 1),
(4, 1),
(5, 1),
(7, 1),
(9, 1),
(2, 2),
(3, 2),
(8, 2),
(6, 3),
(4, 4),
(5, 4),
(9, 4),
(8, 5),
(9, 5);

-- --------------------------------------------------------

--
-- Table structure for table `proyecto_competencias`
--

CREATE TABLE `proyecto_competencias` (
  `proyecto_id` int(11) NOT NULL,
  `competencia_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proyecto_competencias`
--

INSERT INTO `proyecto_competencias` (`proyecto_id`, `competencia_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(3, 4),
(6, 4),
(7, 4),
(8, 4),
(9, 4),
(1, 5),
(2, 5),
(6, 6),
(7, 6),
(8, 6),
(9, 6),
(2, 7),
(4, 7),
(5, 7),
(1, 8),
(3, 8),
(4, 8),
(5, 8),
(6, 8),
(7, 8),
(4, 9),
(5, 9),
(7, 10),
(8, 10),
(9, 10),
(8, 11),
(9, 11),
(9, 12);

-- --------------------------------------------------------

--
-- Table structure for table `proyecto_materiales`
--

CREATE TABLE `proyecto_materiales` (
  `proyecto_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `cantidad` varchar(50) DEFAULT NULL,
  `es_incluido_kit` tinyint(1) DEFAULT 1,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recursos_multimedia`
--

CREATE TABLE `recursos_multimedia` (
  `id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `tipo` enum('imagen','video','simulacion','pdf') NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics_interacciones`
--
ALTER TABLE `analytics_interacciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proyecto` (`proyecto_id`),
  ADD KEY `idx_tipo` (`tipo_interaccion`);

--
-- Indexes for table `analytics_visitas`
--
ALTER TABLE `analytics_visitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proyecto` (`proyecto_id`),
  ADD KEY `idx_fecha` (`fecha_hora`),
  ADD KEY `idx_departamento` (`departamento`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `categorias_materiales`
--
ALTER TABLE `categorias_materiales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `competencias`
--
ALTER TABLE `competencias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `configuracion_ia`
--
ALTER TABLE `configuracion_ia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indexes for table `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contrato_proyectos`
--
ALTER TABLE `contrato_proyectos`
  ADD PRIMARY KEY (`contrato_id`,`proyecto_id`),
  ADD KEY `fk_cp_proyecto` (`proyecto_id`);

--
-- Indexes for table `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_entregas_contrato` (`contrato_id`);

--
-- Indexes for table `entrega_lotes`
--
ALTER TABLE `entrega_lotes`
  ADD PRIMARY KEY (`entrega_id`,`lote_id`),
  ADD KEY `fk_el_lote` (`lote_id`);

--
-- Indexes for table `guias`
--
ALTER TABLE `guias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proyecto_activa` (`proyecto_id`,`activa`);

--
-- Indexes for table `ia_guardrails_log`
--
ALTER TABLE `ia_guardrails_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo` (`tipo_alerta`),
  ADD KEY `idx_proyecto` (`proyecto_id`),
  ADD KEY `idx_fecha` (`fecha_hora`);

--
-- Indexes for table `ia_logs`
--
ALTER TABLE `ia_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo_evento` (`tipo_evento`),
  ADD KEY `idx_proyecto` (`proyecto_id`),
  ADD KEY `idx_fecha` (`fecha_hora`);

--
-- Indexes for table `ia_mensajes`
--
ALTER TABLE `ia_mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sesion` (`sesion_id`),
  ADD KEY `idx_fecha` (`fecha_hora`);

--
-- Indexes for table `ia_respuestas_cache`
--
ALTER TABLE `ia_respuestas_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proyecto_pregunta` (`proyecto_id`,`pregunta_normalizada`(255));

--
-- Indexes for table `ia_sesiones`
--
ALTER TABLE `ia_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sesion_hash` (`sesion_hash`),
  ADD KEY `idx_proyecto` (`proyecto_id`),
  ADD KEY `idx_fecha` (`fecha_inicio`);

--
-- Indexes for table `ia_stats_proyecto`
--
ALTER TABLE `ia_stats_proyecto`
  ADD PRIMARY KEY (`proyecto_id`);

--
-- Indexes for table `justificacion_ctei`
--
ALTER TABLE `justificacion_ctei`
  ADD PRIMARY KEY (`contrato_id`);

--
-- Indexes for table `lotes_kits`
--
ALTER TABLE `lotes_kits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_lote` (`codigo_lote`),
  ADD KEY `fk_lotes_proyecto` (`proyecto_id`),
  ADD KEY `fk_lotes_contrato` (`contrato_id`);

--
-- Indexes for table `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categoria` (`categoria_id`);

--
-- Indexes for table `prompts_proyecto`
--
ALTER TABLE `prompts_proyecto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proyecto` (`proyecto_id`);

--
-- Indexes for table `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_ciclo` (`ciclo`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_destacado` (`destacado`);

--
-- Indexes for table `proyecto_areas`
--
ALTER TABLE `proyecto_areas`
  ADD PRIMARY KEY (`proyecto_id`,`area_id`),
  ADD KEY `fk_pa_area` (`area_id`);

--
-- Indexes for table `proyecto_competencias`
--
ALTER TABLE `proyecto_competencias`
  ADD PRIMARY KEY (`proyecto_id`,`competencia_id`),
  ADD KEY `fk_pc_competencia` (`competencia_id`);

--
-- Indexes for table `proyecto_materiales`
--
ALTER TABLE `proyecto_materiales`
  ADD PRIMARY KEY (`proyecto_id`,`material_id`),
  ADD KEY `fk_pm_material` (`material_id`);

--
-- Indexes for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proyecto` (`proyecto_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics_interacciones`
--
ALTER TABLE `analytics_interacciones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `analytics_visitas`
--
ALTER TABLE `analytics_visitas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categorias_materiales`
--
ALTER TABLE `categorias_materiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `competencias`
--
ALTER TABLE `competencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `configuracion_ia`
--
ALTER TABLE `configuracion_ia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
-- AUTO_INCREMENT for table `lotes_kits`
--
ALTER TABLE `lotes_kits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materiales`
--
ALTER TABLE `materiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prompts_proyecto`
--
ALTER TABLE `prompts_proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contrato_proyectos`
--
ALTER TABLE `contrato_proyectos`
  ADD CONSTRAINT `fk_cp_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cp_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`);

--
-- Constraints for table `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `fk_entregas_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`);

--
-- Constraints for table `entrega_lotes`
--
ALTER TABLE `entrega_lotes`
  ADD CONSTRAINT `fk_el_entrega` FOREIGN KEY (`entrega_id`) REFERENCES `entregas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_el_lote` FOREIGN KEY (`lote_id`) REFERENCES `lotes_kits` (`id`);

--
-- Constraints for table `guias`
--
ALTER TABLE `guias`
  ADD CONSTRAINT `fk_guias_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ia_mensajes`
--
ALTER TABLE `ia_mensajes`
  ADD CONSTRAINT `fk_mensajes_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `ia_sesiones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ia_respuestas_cache`
--
ALTER TABLE `ia_respuestas_cache`
  ADD CONSTRAINT `fk_cache_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ia_stats_proyecto`
--
ALTER TABLE `ia_stats_proyecto`
  ADD CONSTRAINT `fk_iastats_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `justificacion_ctei`
--
ALTER TABLE `justificacion_ctei`
  ADD CONSTRAINT `fk_justificacion_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lotes_kits`
--
ALTER TABLE `lotes_kits`
  ADD CONSTRAINT `fk_lotes_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  ADD CONSTRAINT `fk_lotes_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`);

--
-- Constraints for table `materiales`
--
ALTER TABLE `materiales`
  ADD CONSTRAINT `fk_materiales_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_materiales` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prompts_proyecto`
--
ALTER TABLE `prompts_proyecto`
  ADD CONSTRAINT `fk_prompts_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proyecto_areas`
--
ALTER TABLE `proyecto_areas`
  ADD CONSTRAINT `fk_pa_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pa_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proyecto_competencias`
--
ALTER TABLE `proyecto_competencias`
  ADD CONSTRAINT `fk_pc_competencia` FOREIGN KEY (`competencia_id`) REFERENCES `competencias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pc_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proyecto_materiales`
--
ALTER TABLE `proyecto_materiales`
  ADD CONSTRAINT `fk_pm_material` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pm_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recursos_multimedia`
--
ALTER TABLE `recursos_multimedia`
  ADD CONSTRAINT `fk_recursos_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
