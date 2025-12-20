-- ============================================================
-- ACTUALIZACIÓN TABLA AREAS CON ÁREAS DE CONOCIMIENTO MEN
-- Clase de Ciencia - Colombia 2025
-- ============================================================
-- Agrega campo explicacion y actualiza con áreas de conocimiento
-- relevantes para proyectos científicos según MEN Colombia
-- ============================================================

-- Agregar campo explicacion a la tabla areas
ALTER TABLE `areas` 
ADD COLUMN `explicacion` TEXT NULL AFTER `slug`;

-- Actualizar registros existentes (IDs 1-5)
-- Primero actualizamos los que ya existen para evitar conflictos de UNIQUE slug

UPDATE `areas` SET 
    `nombre` = 'Física',
    `slug` = 'fisica',
    `explicacion` = 'Estudia las propiedades de la materia, la energía y sus interacciones. Incluye mecánica, electricidad, magnetismo, óptica, termodinámica y ondas. Fundamental para proyectos de electricidad, magnetismo, fuerzas y movimiento.'
WHERE `id` = 1;

UPDATE `areas` SET 
    `nombre` = 'Química',
    `slug` = 'quimica',
    `explicacion` = 'Analiza la composición, estructura y propiedades de las sustancias, así como sus transformaciones. Abarca reacciones químicas, enlaces, ácidos-bases, y procesos de cambio de estado. Esencial para experimentos con materiales, cristales, baterías y reacciones.'
WHERE `id` = 2;

UPDATE `areas` SET 
    `nombre` = 'Biología',
    `slug` = 'biologia',
    `explicacion` = 'Investiga los seres vivos, su estructura, funciones, crecimiento, evolución y relaciones con el medio. Incluye botánica, zoología, microbiología y genética. Clave para proyectos de plantas, células, ADN y ecosistemas.'
WHERE `id` = 3;

UPDATE `areas` SET 
    `nombre` = 'Tecnología e Informática',
    `slug` = 'tecnologia',
    `explicacion` = 'Área que estudia el diseño, desarrollo y aplicación de herramientas, sistemas y procesos tecnológicos para resolver problemas. Incluye electrónica, programación, robótica, diseño de circuitos y automatización. Central para proyectos con Arduino, sensores y sistemas interactivos.'
WHERE `id` = 4;

UPDATE `areas` SET 
    `nombre` = 'Ciencias Ambientales',
    `slug` = 'ambiental',
    `explicacion` = 'Estudia las interacciones entre los sistemas físicos, químicos y biológicos del ambiente, y su relación con los sistemas sociales y culturales. Aborda sostenibilidad, conservación, cambio climático y desarrollo sostenible.'
WHERE `id` = 5;

-- Insertar nuevas áreas (IDs 6-10)
INSERT INTO `areas` (`id`, `nombre`, `slug`, `explicacion`) VALUES
-- CIENCIAS EXACTAS
(6, 'Matemáticas', 'matematicas', 
'Disciplina que estudia las propiedades de los números, las formas geométricas, las operaciones y las relaciones abstractas. Incluye álgebra, geometría, estadística y cálculo. Fundamental para análisis de datos, mediciones y modelos matemáticos en proyectos científicos.'),

-- INGENIERÍA
(7, 'Ingeniería y Diseño', 'ingenieria', 
'Aplica principios científicos y matemáticos para diseñar, construir y optimizar estructuras, máquinas y sistemas. Incluye mecánica, electrónica, diseño de prototipos y fabricación. Relevante para proyectos de construcción, máquinas simples y dispositivos.'),

-- CIENCIAS SOCIALES (para contexto de proyectos CTeI)
(8, 'Ciencias Sociales', 'sociales', 
'Estudia las sociedades humanas, sus estructuras, procesos históricos y relaciones culturales. Incluye historia, geografía, economía y democracia. Importante para contextualizar el impacto social de proyectos científicos y CTeI.'),

-- ÁREAS COMPLEMENTARIAS
(9, 'Educación Artística', 'artistica', 
'Desarrolla capacidades expresivas y creativas a través del arte visual, musical y escénico. Relevante para diseño de prototipos, presentaciones creativas y comunicación visual de proyectos científicos.'),

(10, 'Lenguaje y Comunicación', 'lenguaje', 
'Desarrolla competencias en lectura, escritura, expresión oral y comprensión de textos. Incluye comunicación científica, redacción de informes, presentaciones y documentación de proyectos. Esencial para comunicar resultados científicos.')
ON DUPLICATE KEY UPDATE
    `nombre` = VALUES(`nombre`),
    `slug` = VALUES(`slug`),
    `explicacion` = VALUES(`explicacion`);

-- Ajustar AUTO_INCREMENT
ALTER TABLE `areas` AUTO_INCREMENT = 11;

-- ============================================================
-- VERIFICACIÓN
-- ============================================================
-- Ejecutar después de aplicar cambios:
-- SELECT id, nombre, slug, LEFT(explicacion, 100) as explicacion_preview FROM areas ORDER BY id;

-- ============================================================
-- NOTAS DE IMPLEMENTACIÓN
-- ============================================================
-- 1. El campo explicacion ayuda a los profesores a entender el alcance de cada área
-- 2. Las áreas 1-4 (Ciencias Naturales) son las más usadas en proyectos científicos
-- 3. Matemáticas (5) y Tecnología (6) son transversales a casi todos los proyectos
-- 4. Las áreas 7-10 son complementarias para proyectos interdisciplinarios
-- 5. Los registros existentes se actualizan sin pérdida de relaciones en clase_areas
-- ============================================================
