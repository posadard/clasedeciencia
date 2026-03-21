-- ============================================================
-- IA Migration v1.0 — Clase de Ciencia
-- Sistema de 2 instancias IA (frontend + backend)
-- Fecha: Marzo 2026
-- ============================================================
-- INSTRUCCIONES:
--   1. Ejecutar los bloques en orden (1 → 5)
--   2. Verificar con la consulta del Bloque 5 al finalizar
--   3. Completar groq_api_key desde el panel admin/ia/index.php
-- ============================================================


-- ----------------------------------------------------------------
-- BLOQUE 1: ALTER TABLE (idempotente — seguro de re-ejecutar)
-- ----------------------------------------------------------------

-- 1a. Añadir columna instancia (IF NOT EXISTS = seguro si ya existe)
ALTER TABLE `configuracion_ia`
  ADD COLUMN IF NOT EXISTS `instancia` ENUM('frontend','backend') NOT NULL DEFAULT 'frontend' AFTER `id`;

ALTER TABLE `ia_sesiones`
  ADD COLUMN IF NOT EXISTS `instancia` ENUM('frontend','backend') NOT NULL DEFAULT 'frontend';

ALTER TABLE `ia_logs`
  ADD COLUMN IF NOT EXISTS `instancia` ENUM('frontend','backend') NOT NULL DEFAULT 'frontend';

ALTER TABLE `ia_guardrails_log`
  ADD COLUMN IF NOT EXISTS `instancia` ENUM('frontend','backend') NOT NULL DEFAULT 'frontend';

ALTER TABLE `ia_mensajes`
  ADD COLUMN IF NOT EXISTS `instancia` ENUM('frontend','backend') NOT NULL DEFAULT 'frontend';

-- 1b. Eliminar índice único simple si aún existe (bloquearía duplicar claves por instancia)
-- Ejecutar solo si SHOW INDEX FROM configuracion_ia muestra 'uq_config_ia_clave'
SET @idx := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name   = 'configuracion_ia'
    AND index_name   = 'uq_config_ia_clave'
);
SET @sql := IF(@idx > 0,
  'ALTER TABLE `configuracion_ia` DROP INDEX `uq_config_ia_clave`',
  'SELECT ''(uq_config_ia_clave ya no existe, OK)'' AS info'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;

-- 1c. Añadir índice compuesto (instancia + clave) si no existe
SET @idx2 := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name   = 'configuracion_ia'
    AND index_name   = 'uk_instancia_clave'
);
SET @sql2 := IF(@idx2 = 0,
  'ALTER TABLE `configuracion_ia` ADD UNIQUE KEY `uk_instancia_clave` (`instancia`, `clave`)',
  'SELECT ''(uk_instancia_clave ya existe, OK)'' AS info'
);
PREPARE _stmt2 FROM @sql2; EXECUTE _stmt2; DEALLOCATE PREPARE _stmt2;


-- ----------------------------------------------------------------
-- BLOQUE 2: UPDATE
-- La fila existente de palabras_peligro pertenece al frontend
-- ----------------------------------------------------------------

UPDATE `configuracion_ia`
SET `instancia` = 'frontend'
WHERE `clave` = 'palabras_peligro';


-- ----------------------------------------------------------------
-- BLOQUE 3: INSERT — configuracion_ia FRONTEND (13 filas nuevas)
-- INSERT IGNORE = salta silenciosamente si la fila ya existe
-- ----------------------------------------------------------------

INSERT IGNORE INTO `configuracion_ia` (`instancia`, `clave`, `valor`, `tipo`, `descripcion`) VALUES

('frontend', 'ia_activa',
 '0',
 'booleano',
 'Activa o desactiva la IA del estudiante (1=activa, 0=inactiva)'),

('frontend', 'groq_api_key',
 '',
 'secreto',
 'API Key de Groq para la instancia frontend. Completar desde el panel admin.'),

('frontend', 'groq_model_1',
 'llama-3.3-70b-versatile',
 'texto',
 'Modelo principal frontend — pedagógico y preciso'),

('frontend', 'groq_model_2',
 'llama-3.1-8b-instant',
 'texto',
 'Fallback 1 frontend — rápido y económico (560 t/s)'),

('frontend', 'groq_model_3',
 'openai/gpt-oss-20b',
 'texto',
 'Fallback 2 frontend — último recurso (1000 t/s)'),

('frontend', 'groq_temperature',
 '0.5',
 'numero',
 'Temperatura de muestreo (0-2). 0.5 = respuestas consistentes y seguras'),

('frontend', 'groq_max_tokens',
 '800',
 'numero',
 'Máximo de tokens en la respuesta al estudiante'),

('frontend', 'groq_top_p',
 '0.9',
 'numero',
 'Top-p para sampleo de tokens (0-1)'),

('frontend', 'prompt_sistema',
 'Eres un asistente científico educativo para estudiantes colombianos de secundaria (grados 6° a 11°). Tu misión es GUIAR, no resolver: usa preguntas socráticas para que el estudiante descubra las respuestas por sí mismo. NUNCA resuelvas preguntas de examen o evaluaciones directamente. SIEMPRE menciona las normas de seguridad antes de cualquier instrucción experimental. Habla con lenguaje claro, amigable y motivador, apropiado para el ciclo educativo del estudiante. Si la pregunta se sale del ámbito científico educativo, redirige amablemente al tema. Responde siempre en español colombiano.',
 'texto',
 'Prompt del sistema que define el comportamiento base de la IA del estudiante'),

('frontend', 'guardrails_activos',
 '1',
 'booleano',
 'Activa el sistema de filtrado de contenido peligroso (1=activo, 0=desactivo)'),

('frontend', 'palabras_tematicas',
 '["política","religión","violencia","drogas","alcohol","armas","sexo","apuestas","odio","insultos"]',
 'json',
 'Palabras fuera del ámbito educativo que activan el guardrail temático'),

('frontend', 'nivel_safety',
 'estricto',
 'texto',
 'Nivel de safety: estricto|moderado|libre'),

('frontend', 'mensaje_guardrail',
 '⚠️ Esa pregunta está fuera del ámbito de esta clase. Consulta con tu profesor. Si tienes dudas sobre seguridad en el experimento, sigue siempre las instrucciones del kit.',
 'texto',
 'Mensaje mostrado al estudiante cuando se activa un guardrail');


-- ----------------------------------------------------------------
-- BLOQUE 4: INSERT — configuracion_ia BACKEND (14 filas nuevas)
-- INSERT IGNORE = salta silenciosamente si la fila ya existe
-- ----------------------------------------------------------------

INSERT IGNORE INTO `configuracion_ia` (`instancia`, `clave`, `valor`, `tipo`, `descripcion`) VALUES

('backend', 'ia_activa',
 '0',
 'booleano',
 'Activa o desactiva la IA del administrador (1=activa, 0=inactiva)'),

('backend', 'groq_api_key',
 '',
 'secreto',
 'API Key de Groq para la instancia backend. Puede ser la misma del frontend o diferente.'),

('backend', 'groq_model_1',
 'openai/gpt-oss-20b',
 'texto',
 'Modelo principal backend — rápido y técnico (1000 t/s)'),

('backend', 'groq_model_2',
 'llama-3.3-70b-versatile',
 'texto',
 'Fallback 1 backend — preciso y capaz (280 t/s)'),

('backend', 'groq_model_3',
 'llama-3.1-8b-instant',
 'texto',
 'Fallback 2 backend — último recurso (560 t/s)'),

('backend', 'groq_temperature',
 '0.3',
 'numero',
 'Temperatura (0-2). 0.3 = respuestas factuales y precisas'),

('backend', 'groq_max_tokens',
 '1200',
 'numero',
 'Máximo de tokens en la respuesta al administrador'),

('backend', 'groq_top_p',
 '0.95',
 'numero',
 'Top-p para sampleo de tokens (0-1)'),

('backend', 'prompt_sistema',
 'Eres un asistente técnico y operativo para el equipo administrativo de Clase de Ciencia SAS. Tienes acceso al estado actual del sistema: contratos CTeI, entregas de kits educativos, lotes de materiales, clases y métricas de la IA. Responde con precisión usando los datos del contexto proporcionado. Puedes sugerir acciones administrativas, detectar inconsistencias en los datos y ayudar a redactar documentos operativos. Sé directo y conciso. Responde en español.',
 'texto',
 'Prompt del sistema que define el comportamiento base de la IA del administrador'),

('backend', 'guardrails_activos',
 '0',
 'booleano',
 'Guardrails desactivados por defecto para el admin (1=activar si se requiere)'),

('backend', 'palabras_peligro',
 '[]',
 'json',
 'Sin palabras peligro para el admin (lista vacía)'),

('backend', 'palabras_tematicas',
 '[]',
 'json',
 'Sin filtro temático para el admin (lista vacía)'),

('backend', 'nivel_safety',
 'moderado',
 'texto',
 'Nivel de safety del admin: estricto|moderado|libre'),

('backend', 'mensaje_guardrail',
 '⚠️ Esta consulta fue bloqueada por el sistema de seguridad.',
 'texto',
 'Mensaje de guardrail para el admin (raramente se activa)');


-- ----------------------------------------------------------------
-- BLOQUE 5: VERIFICACIÓN
-- Ejecutar al final para confirmar la migración completa
-- Debe retornar 28 filas: 14 frontend + 14 backend
-- ----------------------------------------------------------------

SELECT
  instancia,
  clave,
  tipo,
  CASE tipo
    WHEN 'secreto' THEN CONCAT('****', RIGHT(IFNULL(valor,''), 4))
    ELSE LEFT(IFNULL(valor, ''), 60)
  END AS valor_preview
FROM configuracion_ia
ORDER BY instancia, clave;
