-- ============================================
-- Clase de Ciencia - IA + Analytics Schema Add-on
-- Clases + Kits compatible IA (renamed from Proyectos)
-- Date: 2025-12-19
-- ============================================

SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;

USE `u626603208_clasedeciencia`;

-- ============================================
-- 1) Configuración IA
-- ============================================
CREATE TABLE IF NOT EXISTS configuracion_ia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(100) NOT NULL,
  valor TEXT DEFAULT NULL,
  tipo ENUM('texto','numero','booleano','json','secreto') DEFAULT 'texto',
  descripcion TEXT DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_config_ia_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2) IA Sesiones, Mensajes, Logs
-- ============================================
CREATE TABLE IF NOT EXISTS ia_sesiones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sesion_hash VARCHAR(64) NOT NULL COMMENT 'Hash anónimo del usuario',
  clase_id INT NULL,
  fecha_inicio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_ultima_interaccion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  total_mensajes INT DEFAULT 0,
  tokens_usados INT DEFAULT 0,
  estado ENUM('activa','finalizada','timeout') DEFAULT 'activa',
  INDEX idx_sesion_hash (sesion_hash),
  INDEX idx_ia_sesiones_clase (clase_id),
  INDEX idx_sesiones_activas (estado, fecha_ultima_interaccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ia_mensajes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sesion_id INT NOT NULL,
  rol ENUM('user','assistant','system') NOT NULL,
  contenido TEXT NOT NULL,
  tokens INT DEFAULT 0,
  fecha_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  metadata LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(metadata)),
  INDEX idx_ia_mensajes_sesion (sesion_id),
  INDEX idx_ia_mensajes_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ia_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sesion_id INT DEFAULT NULL,
  clase_id INT DEFAULT NULL,
  tipo_evento ENUM('consulta','respuesta','error','guardrail_activado','timeout') NOT NULL,
  descripcion TEXT DEFAULT NULL,
  tokens_usados INT DEFAULT 0,
  tiempo_respuesta_ms INT DEFAULT NULL,
  modelo_usado VARCHAR(100) DEFAULT NULL,
  costo_estimado DECIMAL(10,6) DEFAULT NULL COMMENT 'Costo en USD',
  fecha_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  metadata LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(metadata)),
  INDEX idx_ia_logs_tipo (tipo_evento),
  INDEX idx_ia_logs_clase (clase_id),
  INDEX idx_ia_logs_fecha (fecha_hora),
  INDEX idx_ia_logs_analytics (fecha_hora, tipo_evento, clase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE ia_mensajes
  ADD CONSTRAINT fk_ia_mensajes_sesion FOREIGN KEY (sesion_id) REFERENCES ia_sesiones(id) ON DELETE CASCADE;

-- ============================================
-- 3) IA Cache y Guardrails
-- ============================================
CREATE TABLE IF NOT EXISTS ia_respuestas_cache (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clase_id INT NOT NULL,
  pregunta_normalizada VARCHAR(500) NOT NULL COMMENT 'Pregunta sin acentos, lowercase',
  pregunta_original TEXT NOT NULL,
  respuesta TEXT NOT NULL,
  veces_usada INT DEFAULT 0,
  ultima_vez_usada TIMESTAMP NULL DEFAULT NULL,
  activa TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ia_cache_clase_pregunta (clase_id, pregunta_normalizada(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ia_guardrails_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sesion_id INT NOT NULL,
  clase_id INT DEFAULT NULL,
  pregunta_usuario TEXT NOT NULL,
  palabra_detectada VARCHAR(255) NOT NULL,
  tipo_alerta ENUM('peligro','advertencia','info') DEFAULT 'peligro',
  respuesta_dada TEXT DEFAULT NULL,
  fecha_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ia_guardrails_tipo (tipo_alerta),
  INDEX idx_ia_guardrails_clase (clase_id),
  INDEX idx_ia_guardrails_fecha (fecha_hora),
  INDEX idx_ia_guardrails_mix (clase_id, tipo_alerta, fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger: actualizar stats cuando se usa cache
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_actualizar_cache_stats_clase
AFTER UPDATE ON ia_respuestas_cache
FOR EACH ROW
BEGIN
  IF NEW.veces_usada > OLD.veces_usada THEN
    INSERT INTO ia_logs (clase_id, tipo_evento, descripcion, tokens_usados, costo_estimado)
    VALUES (NEW.clase_id, 'consulta', 'Respuesta desde caché', 0, 0.00);
  END IF;
END$$
DELIMITER ;

-- ============================================
-- 4) IA Stats por Clase
-- ============================================
CREATE TABLE IF NOT EXISTS ia_stats_clase (
  clase_id INT NOT NULL PRIMARY KEY,
  total_consultas INT DEFAULT 0,
  total_sesiones INT DEFAULT 0,
  tokens_totales INT DEFAULT 0,
  costo_total DECIMAL(10,2) DEFAULT 0.00,
  promedio_mensajes_sesion DECIMAL(5,2) DEFAULT 0.00,
  guardrails_activados INT DEFAULT 0,
  ultima_consulta TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5) Prompts por Clase
-- ============================================
CREATE TABLE IF NOT EXISTS prompts_clase (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clase_id INT NOT NULL,
  prompt_contexto TEXT NOT NULL COMMENT 'Contexto específico de la clase para la IA',
  conocimientos_previos LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Conceptos que el estudiante debe saber' CHECK (json_valid(conocimientos_previos)),
  enfoque_pedagogico TEXT DEFAULT NULL COMMENT 'Cómo debe guiar la IA en esta clase',
  preguntas_frecuentes LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'FAQs de la clase para respuestas rápidas' CHECK (json_valid(preguntas_frecuentes)),
  activo TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_prompts_clase (clase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6) Procedures & Functions (versión Clase)
-- ============================================
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_buscar_respuesta_cache_clase (IN p_clase_id INT, IN p_pregunta VARCHAR(500))
BEGIN
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

CREATE PROCEDURE IF NOT EXISTS sp_limpiar_sesiones_antiguas ()
BEGIN
  UPDATE ia_sesiones
  SET estado = 'timeout'
  WHERE estado = 'activa'
    AND fecha_ultima_interaccion < DATE_SUB(NOW(), INTERVAL 1 HOUR);
END$$

CREATE PROCEDURE IF NOT EXISTS sp_obtener_contexto_clase (IN p_clase_id INT)
BEGIN
  SELECT * FROM v_clase_contexto_ia WHERE clase_id = p_clase_id;
  SELECT * FROM v_clase_kits_detalle WHERE clase_id = p_clase_id;
  SELECT url, tipo, titulo
  FROM recursos_multimedia
  WHERE clase_id = p_clase_id
  ORDER BY sort_order;
END$$

CREATE PROCEDURE IF NOT EXISTS sp_registrar_interaccion_ia_clase (
  IN p_sesion_id INT,
  IN p_clase_id INT,
  IN p_pregunta TEXT,
  IN p_respuesta TEXT,
  IN p_tokens INT,
  IN p_tiempo_ms INT,
  IN p_modelo VARCHAR(100),
  IN p_costo DECIMAL(10,6),
  IN p_guardrail_activado BOOLEAN)
BEGIN
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

CREATE FUNCTION IF NOT EXISTS fn_es_pregunta_peligrosa (pregunta TEXT) RETURNS TINYINT(1)
DETERMINISTIC
BEGIN
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

-- ============================================
-- 7) Views for IA reporting (versión Clase)
-- ============================================
DROP VIEW IF EXISTS v_ia_dashboard;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW v_ia_dashboard AS
SELECT CAST(l.fecha_hora AS DATE) AS fecha,
       COUNT(DISTINCT l.sesion_id) AS sesiones_unicas,
       COUNT(l.id) AS total_eventos,
       SUM(CASE WHEN l.tipo_evento = 'consulta' THEN 1 ELSE 0 END) AS total_consultas,
       SUM(CASE WHEN l.tipo_evento = 'error' THEN 1 ELSE 0 END) AS total_errores,
       SUM(CASE WHEN l.tipo_evento = 'guardrail_activado' THEN 1 ELSE 0 END) AS alertas_seguridad,
       SUM(l.tokens_usados) AS tokens_totales,
       AVG(l.tiempo_respuesta_ms) AS tiempo_promedio_ms,
       SUM(l.costo_estimado) AS costo_total
FROM ia_logs l
GROUP BY CAST(l.fecha_hora AS DATE)
ORDER BY CAST(l.fecha_hora AS DATE) DESC;

DROP VIEW IF EXISTS v_ia_preguntas_frecuentes_clase;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW v_ia_preguntas_frecuentes_clase AS
SELECT c.nombre AS clase,
       im.contenido AS pregunta,
       COUNT(*) AS veces_preguntada,
       MAX(im.fecha_hora) AS ultima_vez
FROM ia_mensajes im
JOIN ia_sesiones s ON im.sesion_id = s.id
LEFT JOIN clases c ON s.clase_id = c.id
WHERE im.rol = 'user'
GROUP BY s.clase_id, im.contenido
HAVING COUNT(*) >= 3
ORDER BY COUNT(*) DESC;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
