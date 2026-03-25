-- =========================================================
-- Clase de Ciencia
-- Migracion IA + Auditoria Web v2
-- Objetivo:
-- 1) Persistencia robusta de sesiones IA (frontend + backend)
-- 2) Captura unificada de eventos de auditoria del website
-- 3) Vistas KPI para consultas IA y panel admin
--
-- Nota: Script idempotente (usa IF EXISTS / IF NOT EXISTS cuando aplica).
-- =========================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------
-- FASE 1: Extender sesiones IA
-- ---------------------------------------------------------

ALTER TABLE ia_sesiones
  ADD COLUMN IF NOT EXISTS admin_user varchar(120) NULL AFTER instancia,
  ADD COLUMN IF NOT EXISTS contexto_scope varchar(80) NULL AFTER admin_user,
  ADD COLUMN IF NOT EXISTS contexto_pagina varchar(80) NULL AFTER contexto_scope,
  ADD COLUMN IF NOT EXISTS entidad_tipo varchar(40) NULL AFTER contexto_pagina,
  ADD COLUMN IF NOT EXISTS entidad_id int(11) NULL AFTER entidad_tipo,
  ADD COLUMN IF NOT EXISTS metadata longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL CHECK (json_valid(metadata)) AFTER entidad_id;

ALTER TABLE ia_sesiones
  ADD KEY IF NOT EXISTS idx_ia_sesiones_instancia_estado (instancia, estado),
  ADD KEY IF NOT EXISTS idx_ia_sesiones_scope (contexto_scope, contexto_pagina),
  ADD KEY IF NOT EXISTS idx_ia_sesiones_entidad (entidad_tipo, entidad_id),
  ADD KEY IF NOT EXISTS idx_ia_sesiones_admin_user (admin_user);

-- Opcional de continuidad: llave de sesion conversacional por contexto
CREATE TABLE IF NOT EXISTS ia_sesiones_contexto (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  instancia enum('frontend','backend') NOT NULL,
  sesion_clave varchar(160) NOT NULL COMMENT 'Ej: backend:admin:contratos:12',
  sesion_id int(11) NOT NULL,
  activa tinyint(1) NOT NULL DEFAULT 1,
  created_at datetime NOT NULL DEFAULT current_timestamp(),
  updated_at datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY uq_ia_sesion_contexto (instancia, sesion_clave),
  KEY idx_ia_sesion_contexto_sesion_id (sesion_id),
  CONSTRAINT fk_ia_sesion_contexto_sesion FOREIGN KEY (sesion_id) REFERENCES ia_sesiones(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- FASE 2: Eventos de auditoria unificados
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS analytics_eventos (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  session_hash varchar(64) DEFAULT NULL,
  sesion_ia_id int(11) DEFAULT NULL,
  instancia enum('frontend','backend') NOT NULL DEFAULT 'frontend',
  evento varchar(64) NOT NULL COMMENT 'page_view, search_query, search_result_click, ia_question, ia_answer, ia_error, cta_click, admin_entity_update, etc.',
  tipo_pagina varchar(80) DEFAULT NULL,
  modulo varchar(80) DEFAULT NULL,
  entidad_tipo varchar(40) DEFAULT NULL,
  entidad_id int(11) DEFAULT NULL,
  clase_id int(11) DEFAULT NULL,
  kit_id int(11) DEFAULT NULL,
  componente_id int(11) DEFAULT NULL,
  manual_id int(11) DEFAULT NULL,
  termino_busqueda varchar(255) DEFAULT NULL,
  resultado_posicion int(11) DEFAULT NULL,
  referrer varchar(255) DEFAULT NULL,
  departamento varchar(120) DEFAULT NULL,
  dispositivo varchar(64) DEFAULT NULL,
  ip_anon varchar(80) DEFAULT NULL,
  user_agent varchar(255) DEFAULT NULL,
  duracion_ms int(11) DEFAULT NULL,
  valor_numerico decimal(18,6) DEFAULT NULL,
  metadata longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(metadata)),
  created_at datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_ae_evento_fecha (evento, created_at),
  KEY idx_ae_session_fecha (session_hash, created_at),
  KEY idx_ae_instancia_modulo (instancia, modulo),
  KEY idx_ae_tipo_pagina (tipo_pagina),
  KEY idx_ae_entidad (entidad_tipo, entidad_id),
  KEY idx_ae_clase (clase_id),
  KEY idx_ae_kit (kit_id),
  KEY idx_ae_busqueda (termino_busqueda),
  KEY idx_ae_created (created_at),
  CONSTRAINT fk_ae_sesion_ia FOREIGN KEY (sesion_ia_id) REFERENCES ia_sesiones(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- FASE 3: Resumen de sesion para control de tokens/contexto
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS ia_resumen_sesion (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  sesion_id int(11) NOT NULL,
  instancia enum('frontend','backend') NOT NULL DEFAULT 'frontend',
  resumen_corto text NOT NULL,
  resumen_largo mediumtext DEFAULT NULL,
  ultimo_mensaje_id bigint(20) DEFAULT NULL,
  tokens_estimados int(11) DEFAULT 0,
  updated_at datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY uq_ia_resumen_sesion (sesion_id, instancia),
  KEY idx_ia_resumen_updated (updated_at),
  CONSTRAINT fk_ia_resumen_sesion FOREIGN KEY (sesion_id) REFERENCES ia_sesiones(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- FASE 4: Vistas de auditoria orientadas a negocio
-- ---------------------------------------------------------

-- Clases mas vistas (7 dias)
DROP VIEW IF EXISTS v_auditoria_top_clases_7d;
CREATE VIEW v_auditoria_top_clases_7d AS
SELECT
  c.id AS clase_id,
  c.nombre,
  c.slug,
  COUNT(*) AS visitas_totales,
  COUNT(DISTINCT COALESCE(ae.session_hash, CONCAT('anon-', ae.id))) AS sesiones_unicas,
  SUM(CASE WHEN ae.evento = 'search_result_click' THEN 1 ELSE 0 END) AS clics_desde_busqueda,
  MAX(ae.created_at) AS ultima_interaccion
FROM analytics_eventos ae
JOIN clases c ON c.id = ae.clase_id
WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
  AND ae.instancia = 'frontend'
  AND ae.evento IN ('page_view','search_result_click')
GROUP BY c.id, c.nombre, c.slug
ORDER BY visitas_totales DESC, sesiones_unicas DESC;

-- Clases mas vistas (30 dias)
DROP VIEW IF EXISTS v_auditoria_top_clases_30d;
CREATE VIEW v_auditoria_top_clases_30d AS
SELECT
  c.id AS clase_id,
  c.nombre,
  c.slug,
  COUNT(*) AS visitas_totales,
  COUNT(DISTINCT COALESCE(ae.session_hash, CONCAT('anon-', ae.id))) AS sesiones_unicas,
  SUM(CASE WHEN ae.evento = 'search_result_click' THEN 1 ELSE 0 END) AS clics_desde_busqueda,
  MAX(ae.created_at) AS ultima_interaccion
FROM analytics_eventos ae
JOIN clases c ON c.id = ae.clase_id
WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND ae.instancia = 'frontend'
  AND ae.evento IN ('page_view','search_result_click')
GROUP BY c.id, c.nombre, c.slug
ORDER BY visitas_totales DESC, sesiones_unicas DESC;

-- Embudo Home -> Clase -> Kit (30 dias)
DROP VIEW IF EXISTS v_auditoria_funnel_home_clase_kit_30d;
CREATE VIEW v_auditoria_funnel_home_clase_kit_30d AS
SELECT
  COUNT(DISTINCT CASE WHEN ae.tipo_pagina = 'inicio' AND ae.evento = 'page_view' THEN ae.session_hash END) AS sesiones_home,
  COUNT(DISTINCT CASE WHEN ae.tipo_pagina = 'clase' AND ae.evento = 'page_view' THEN ae.session_hash END) AS sesiones_clase,
  COUNT(DISTINCT CASE WHEN ae.tipo_pagina = 'kit' AND ae.evento = 'page_view' THEN ae.session_hash END) AS sesiones_kit,
  ROUND(
    100 * COUNT(DISTINCT CASE WHEN ae.tipo_pagina = 'clase' AND ae.evento = 'page_view' THEN ae.session_hash END)
    / NULLIF(COUNT(DISTINCT CASE WHEN ae.tipo_pagina = 'inicio' AND ae.evento = 'page_view' THEN ae.session_hash END), 0),
    2
  ) AS conv_home_a_clase_pct,
  ROUND(
    100 * COUNT(DISTINCT CASE WHEN ae.tipo_pagina = 'kit' AND ae.evento = 'page_view' THEN ae.session_hash END)
    / NULLIF(COUNT(DISTINCT CASE WHEN ae.tipo_pagina = 'clase' AND ae.evento = 'page_view' THEN ae.session_hash END), 0),
    2
  ) AS conv_clase_a_kit_pct
FROM analytics_eventos ae
WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND ae.instancia = 'frontend';

-- CTR de busqueda (30 dias)
DROP VIEW IF EXISTS v_auditoria_ctr_busqueda_30d;
CREATE VIEW v_auditoria_ctr_busqueda_30d AS
SELECT
  DATE(ae.created_at) AS fecha,
  SUM(CASE WHEN ae.evento = 'search_query' THEN 1 ELSE 0 END) AS total_busquedas,
  SUM(CASE WHEN ae.evento = 'search_result_click' THEN 1 ELSE 0 END) AS total_clics_resultado,
  ROUND(
    100 * SUM(CASE WHEN ae.evento = 'search_result_click' THEN 1 ELSE 0 END)
    / NULLIF(SUM(CASE WHEN ae.evento = 'search_query' THEN 1 ELSE 0 END), 0),
    2
  ) AS ctr_busqueda_pct
FROM analytics_eventos ae
WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND ae.instancia = 'frontend'
GROUP BY DATE(ae.created_at)
ORDER BY fecha DESC;

-- Salud IA (30 dias), consolidando logs IA actuales
DROP VIEW IF EXISTS v_auditoria_ia_salud_30d;
CREATE VIEW v_auditoria_ia_salud_30d AS
SELECT
  l.instancia,
  DATE(l.fecha_hora) AS fecha,
  COUNT(*) AS total_eventos_ia,
  SUM(CASE WHEN l.tipo_evento = 'consulta' THEN 1 ELSE 0 END) AS total_consultas,
  SUM(CASE WHEN l.tipo_evento = 'error' THEN 1 ELSE 0 END) AS total_errores,
  SUM(CASE WHEN l.tipo_evento = 'guardrail_activado' THEN 1 ELSE 0 END) AS total_guardrails,
  SUM(COALESCE(l.tokens_usados, 0)) AS tokens_totales,
  ROUND(AVG(COALESCE(l.tiempo_respuesta_ms, 0)), 2) AS latencia_promedio_ms,
  ROUND(100 * SUM(CASE WHEN l.tipo_evento = 'error' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0), 2) AS tasa_error_pct
FROM ia_logs l
WHERE l.fecha_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY l.instancia, DATE(l.fecha_hora)
ORDER BY fecha DESC, instancia ASC;

-- Preguntas sin resolver (heuristica inicial, 30 dias)
DROP VIEW IF EXISTS v_auditoria_ia_preguntas_sin_resolver_30d;
CREATE VIEW v_auditoria_ia_preguntas_sin_resolver_30d AS
SELECT
  DATE(l.fecha_hora) AS fecha,
  l.instancia,
  COUNT(*) AS total_preguntas_sin_resolver
FROM ia_logs l
WHERE l.fecha_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND l.tipo_evento IN ('error', 'timeout')
GROUP BY DATE(l.fecha_hora), l.instancia
ORDER BY fecha DESC, instancia ASC;

-- ---------------------------------------------------------
-- FASE 5: Compatibilidad con analytics_visitas legado
-- ---------------------------------------------------------

-- Esta vista ayuda a migrar gradualmente reportes antiguos a la nueva capa.
DROP VIEW IF EXISTS v_auditoria_visitas_legacy_30d;
CREATE VIEW v_auditoria_visitas_legacy_30d AS
SELECT
  av.clase_id,
  c.nombre,
  c.slug,
  COUNT(*) AS visitas_totales,
  COUNT(DISTINCT DATE(av.visited_at)) AS dias_con_trafico,
  MAX(av.visited_at) AS ultima_visita
FROM analytics_visitas av
LEFT JOIN clases c ON c.id = av.clase_id
WHERE av.visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY av.clase_id, c.nombre, c.slug
ORDER BY visitas_totales DESC;

-- ---------------------------------------------------------
-- FASE 6: Consulta rapida para IA admin (resumen ejecutivo)
-- ---------------------------------------------------------
DROP VIEW IF EXISTS v_auditoria_resumen_ejecutivo;
CREATE VIEW v_auditoria_resumen_ejecutivo AS
SELECT
  (SELECT COUNT(*) FROM analytics_eventos WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND evento = 'page_view' AND instancia='frontend') AS page_views_7d,
  (SELECT COUNT(DISTINCT session_hash) FROM analytics_eventos WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND instancia='frontend') AS sesiones_unicas_7d,
  (SELECT COUNT(*) FROM ia_logs WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND tipo_evento='consulta') AS consultas_ia_7d,
  (SELECT COUNT(*) FROM ia_logs WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND tipo_evento='error') AS errores_ia_7d,
  (SELECT COUNT(*) FROM ia_logs WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND tipo_evento='guardrail_activado') AS guardrails_ia_7d,
  NOW() AS fecha_corte;

-- =========================================================
-- FIN MIGRACION
-- =========================================================
