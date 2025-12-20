-- ============================================================
-- TABLA CICLOS - LEY 2491/2025 COLOMBIA
-- Clase de Ciencia - Convención de Ciclos de Aprendizaje
-- ============================================================
-- Incluye edad, propósito, grados y equivalencia ISCED/UNESCO
-- ============================================================

CREATE TABLE `ciclos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `orden` int(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ciclos_numero` (`numero`),
  UNIQUE KEY `uq_ciclos_slug` (`slug`),
  KEY `idx_ciclos_activo` (`activo`),
  KEY `idx_ciclos_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Ciclos de aprendizaje según Ley 2491/2025 Colombia';

-- ============================================================
-- INSERTAR CICLOS SEGÚN LEY 2491/2025
-- ============================================================

INSERT INTO `ciclos` 
(`numero`, `nombre`, `slug`, `edad_min`, `edad_max`, `grados`, `grados_texto`, `proposito`, `explicacion`, `nivel_educativo`, `isced_level`, `activo`, `orden`) 
VALUES

-- CICLO 0: Desarrollo Inicial (Primera Infancia)
(0, 'Desarrollo Inicial', 'desarrollo-inicial', 0, 5, '["Jardín", "Transición"]', 'Jardín y Transición',
'Estimulación temprana y socialización.',
'Ciclo enfocado en el desarrollo de habilidades motrices, lenguaje básico y socialización inicial. Los niños exploran el mundo a través del juego, desarrollan autonomía básica y establecen sus primeras relaciones sociales fuera del entorno familiar. Este ciclo sienta las bases para el aprendizaje formal.',
'Educación Inicial y Preescolar',
'ISCED 0',
0, -- No activo para proyectos científicos escolares
0),

-- CICLO 1: Cimentación (Primeros años de primaria)
(1, 'Cimentación', 'cimentacion', 6, 8, '[1, 2, 3]', '1° a 3°',
'Alfabetización inicial y pensamiento numérico básico.',
'Desarrollo de competencias fundamentales en lectura, escritura y operaciones matemáticas básicas. Los estudiantes aprenden a seguir instrucciones, trabajar en grupo y desarrollan curiosidad por el mundo que les rodea. Introducción a conceptos científicos mediante observación directa.',
'Educación Básica Primaria',
'ISCED 1',
1,
1),

-- CICLO 2: Consolidación (Final de primaria)
(2, 'Consolidación', 'consolidacion', 9, 11, '[4, 5]', '4° a 5°',
'Desarrollo de autonomía y competencias de investigación.',
'Fortalecimiento de habilidades académicas y desarrollo de pensamiento crítico inicial. Los estudiantes aprenden a formular preguntas, buscar información y presentar sus hallazgos. Introducción al método científico mediante experimentos guiados y proyectos sencillos.',
'Educación Básica Primaria',
'ISCED 1',
1,
2),

-- CICLO 3: Exploración (Inicio de secundaria)
(3, 'Exploración', 'exploracion', 12, 13, '[6, 7]', '6° a 7°',
'Descubrimiento de intereses vocacionales y cambios físicos/sociales.',
'Transición a secundaria con énfasis en exploración de áreas de interés. Los estudiantes desarrollan habilidades de investigación más estructuradas, aprenden a observar fenómenos científicos y describir sus características. Desarrollo socioemocional durante cambios de la adolescencia temprana.',
'Educación Básica Secundaria',
'ISCED 2',
1,
3),

-- CICLO 4: Experimentación y Profundización (Final de secundaria)
(4, 'Experimentación y Profundización', 'experimentacion', 14, 15, '[8, 9]', '8° a 9°',
'Aplicación del conocimiento y resolución de problemas complejos.',
'Desarrollo de competencias científicas avanzadas con énfasis en experimentación controlada, análisis de variables y establecimiento de relaciones causales. Los estudiantes aplican el método científico de manera independiente, comparan resultados y explican fenómenos naturales con fundamento teórico.',
'Educación Básica Secundaria',
'ISCED 2',
1,
4),

-- CICLO 5: Análisis y Proyección (Educación Media)
(5, 'Análisis y Proyección', 'analisis-proyeccion', 16, 17, '[10, 11]', '10° a 11°',
'Especialización académica o técnica y preparación para la vida adulta.',
'Culminación del proceso educativo básico con énfasis en análisis crítico, argumentación científica y conexión con problemas reales. Los estudiantes desarrollan proyectos de investigación complejos, evalúan impactos tecnológicos y sociales, y se preparan para educación superior o inserción laboral. Énfasis en sostenibilidad y responsabilidad social.',
'Educación Media (Bachillerato)',
'ISCED 3',
1,
5);

-- ============================================================
-- AJUSTAR AUTO_INCREMENT
-- ============================================================
ALTER TABLE `ciclos` AUTO_INCREMENT = 7;

-- ============================================================
-- VERIFICACIÓN
-- ============================================================
-- SELECT numero, nombre, CONCAT(edad_min, '-', edad_max, ' años') as rango_edad, 
--        grados_texto, isced_level, activo 
-- FROM ciclos 
-- ORDER BY numero;

-- ============================================================
-- NOTAS DE IMPLEMENTACIÓN
-- ============================================================
-- 1. El campo `numero` coincide con el valor INT actual en la tabla `clases`
-- 2. Ciclos 3, 4, 5 corresponden a los proyectos científicos actuales (6°-11°)
-- 3. Ciclo 0 está inactivo porque no se trabaja con primera infancia
-- 4. Ciclos 1-2 están activos para expansión futura a primaria
-- 5. El campo `grados` usa JSON para flexibilidad (puede incluir texto como "Jardín")
-- 6. Compatible con estándares ISCED/UNESCO para reportes internacionales
-- 7. Ley 2491/2025 enfatiza desarrollo socioemocional en cada ciclo
-- 8. Mantener consistencia: numero en ciclos = ciclo en clases (3,4,5)
-- ============================================================

-- ============================================================
-- MIGRACIÓN OPCIONAL (NO EJECUTAR AÚN)
-- ============================================================
-- Si en el futuro deseas usar FK en lugar de INT:
-- 
-- ALTER TABLE `clases` 
-- ADD CONSTRAINT `fk_clases_ciclo` 
-- FOREIGN KEY (`ciclo`) REFERENCES `ciclos` (`numero`)
-- ON DELETE RESTRICT ON UPDATE CASCADE;
-- ============================================================
