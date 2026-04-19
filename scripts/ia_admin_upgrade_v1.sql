-- =========================================================
-- IA Admin Upgrade v1
-- Fecha: 2026-04-19
-- Objetivo:
-- 1) Crear vistas operativas consumibles por IA backend admin.
-- 2) Corregir vista de riesgo operativo con collation consistente.
-- 3) Dejar bitacora de refresco de indice IA.
-- =========================================================

START TRANSACTION;

-- ---------------------------------------------------------
-- Bitacora de refrescos administrativos IA
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS ia_admin_refresh_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  proceso VARCHAR(80) NOT NULL,
  estado ENUM('ok','error') NOT NULL DEFAULT 'ok',
  detalle_json JSON DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ia_admin_refresh_created (created_at),
  KEY idx_ia_admin_refresh_proceso (proceso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- KPIs operativos para respuestas deterministicas backend
-- ---------------------------------------------------------
DROP VIEW IF EXISTS v_admin_kpis_contratos;
CREATE VIEW v_admin_kpis_contratos AS
SELECT
  COUNT(*) AS contratos_total,
  SUM(CASE WHEN estado_contrato = 'borrador' THEN 1 ELSE 0 END) AS contratos_borrador,
  SUM(CASE WHEN estado_contrato = 'vigente' THEN 1 ELSE 0 END) AS contratos_vigentes,
  SUM(CASE WHEN estado_contrato = 'suspendido' THEN 1 ELSE 0 END) AS contratos_suspendidos,
  SUM(CASE WHEN estado_contrato = 'finalizado' THEN 1 ELSE 0 END) AS contratos_finalizados,
  SUM(CASE WHEN estado_contrato = 'cerrado' THEN 1 ELSE 0 END) AS contratos_cerrados,
  SUM(CASE WHEN fecha_fin IS NOT NULL AND fecha_fin < CURDATE() AND estado_contrato IN ('vigente', 'suspendido') THEN 1 ELSE 0 END) AS contratos_vencidos_activos,
  SUM(CASE WHEN fecha_fin IS NOT NULL AND fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND estado_contrato IN ('vigente', 'suspendido') THEN 1 ELSE 0 END) AS contratos_por_vencer_30d,
  ROUND(SUM(COALESCE(valor, 0)), 2) AS valor_total,
  ROUND(SUM(COALESCE(valor_ejecutado, 0)), 2) AS valor_ejecutado,
  ROUND(SUM(COALESCE(valor, 0) - COALESCE(valor_ejecutado, 0)), 2) AS saldo_pendiente
FROM contratos;

DROP VIEW IF EXISTS v_admin_kpis_entregas;
CREATE VIEW v_admin_kpis_entregas AS
SELECT
  COUNT(*) AS entregas_total,
  SUM(CASE WHEN estado_entrega = 'programada' THEN 1 ELSE 0 END) AS entregas_programadas,
  SUM(CASE WHEN estado_entrega = 'en_transito' THEN 1 ELSE 0 END) AS entregas_en_transito,
  SUM(CASE WHEN estado_entrega = 'entregada' THEN 1 ELSE 0 END) AS entregas_entregadas,
  SUM(CASE WHEN estado_entrega = 'rechazada' THEN 1 ELSE 0 END) AS entregas_rechazadas,
  SUM(CASE WHEN estado_entrega = 'reprogramada' THEN 1 ELSE 0 END) AS entregas_reprogramadas,
  SUM(CASE WHEN estado_entrega IN ('programada','en_transito','reprogramada') THEN 1 ELSE 0 END) AS entregas_abiertas,
  SUM(CASE WHEN (acta_pdf IS NULL OR TRIM(acta_pdf) = '') AND estado_entrega = 'entregada' THEN 1 ELSE 0 END) AS entregas_entregadas_sin_acta,
  SUM(CASE WHEN fecha_programada IS NOT NULL AND fecha_programada < CURDATE() AND estado_entrega IN ('programada','en_transito','reprogramada') THEN 1 ELSE 0 END) AS entregas_atrasadas,
  SUM(COALESCE(cantidad_kits, 0)) AS kits_planificados,
  SUM(CASE WHEN estado_entrega = 'entregada' THEN COALESCE(cantidad_kits, 0) ELSE 0 END) AS kits_entregados
FROM entregas;

DROP VIEW IF EXISTS v_admin_kpis_lotes;
CREATE VIEW v_admin_kpis_lotes AS
SELECT
  COUNT(*) AS lotes_total,
  SUM(CASE WHEN estado_lote = 'activo' THEN 1 ELSE 0 END) AS lotes_activos,
  SUM(CASE WHEN estado_lote = 'bloqueado' THEN 1 ELSE 0 END) AS lotes_bloqueados,
  SUM(CASE WHEN estado_lote = 'agotado' THEN 1 ELSE 0 END) AS lotes_agotados,
  SUM(CASE WHEN estado_lote = 'cerrado' THEN 1 ELSE 0 END) AS lotes_cerrados,
  SUM(COALESCE(cantidad_total, 0)) AS stock_total,
  SUM(COALESCE(cantidad_disponible, 0)) AS stock_disponible,
  SUM(COALESCE(cantidad_asignada, 0)) AS stock_asignado,
  SUM(COALESCE(cantidad_entregada, 0)) AS stock_entregado,
  SUM(CASE WHEN COALESCE(cantidad_total,0) > 0 AND (COALESCE(cantidad_disponible,0) / NULLIF(cantidad_total,0)) <= 0.10 THEN 1 ELSE 0 END) AS lotes_riesgo_quiebre
FROM lotes;

-- ---------------------------------------------------------
-- Vista de riesgo operativo consolidado
-- Nota: se recrea para evitar mezclas de collation en UNION.
-- ---------------------------------------------------------
DROP VIEW IF EXISTS v_admin_riesgo_operativo;
CREATE VIEW v_admin_riesgo_operativo AS
SELECT
  'contratos' COLLATE utf8mb4_unicode_ci AS modulo,
  c.id AS entidad_id,
  c.numero COLLATE utf8mb4_unicode_ci AS referencia,
  CASE
    WHEN c.fecha_fin IS NOT NULL AND c.fecha_fin < CURDATE() THEN 'alto'
    WHEN c.fecha_fin IS NOT NULL AND c.fecha_fin <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'medio'
    ELSE 'bajo'
  END COLLATE utf8mb4_unicode_ci AS nivel_riesgo,
  CASE
    WHEN c.fecha_fin IS NOT NULL AND c.fecha_fin < CURDATE() THEN 'Contrato vencido con estado activo'
    WHEN c.fecha_fin IS NOT NULL AND c.fecha_fin <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Contrato proximo a vencer (30 dias)'
    ELSE 'Sin riesgo critico detectado'
  END COLLATE utf8mb4_unicode_ci AS descripcion,
  NOW() AS fecha_corte
FROM contratos c
WHERE c.estado_contrato IN ('vigente', 'suspendido')

UNION ALL

SELECT
  'entregas' COLLATE utf8mb4_unicode_ci AS modulo,
  e.id AS entidad_id,
  COALESCE(e.codigo_entrega, CONCAT('ENT-', LPAD(e.id, 6, '0'))) COLLATE utf8mb4_unicode_ci AS referencia,
  CASE
    WHEN e.fecha_programada IS NOT NULL AND e.fecha_programada < CURDATE() AND e.estado_entrega IN ('programada','en_transito','reprogramada') THEN 'alto'
    WHEN (e.acta_pdf IS NULL OR TRIM(e.acta_pdf) = '') AND e.estado_entrega = 'entregada' THEN 'medio'
    ELSE 'bajo'
  END COLLATE utf8mb4_unicode_ci AS nivel_riesgo,
  CASE
    WHEN e.fecha_programada IS NOT NULL AND e.fecha_programada < CURDATE() AND e.estado_entrega IN ('programada','en_transito','reprogramada') THEN 'Entrega atrasada'
    WHEN (e.acta_pdf IS NULL OR TRIM(e.acta_pdf) = '') AND e.estado_entrega = 'entregada' THEN 'Entrega sin acta cargada'
    ELSE 'Sin riesgo critico detectado'
  END COLLATE utf8mb4_unicode_ci AS descripcion,
  NOW() AS fecha_corte
FROM entregas e

UNION ALL

SELECT
  'lotes' COLLATE utf8mb4_unicode_ci AS modulo,
  l.id AS entidad_id,
  l.codigo_lote COLLATE utf8mb4_unicode_ci AS referencia,
  CASE
    WHEN l.estado_lote = 'bloqueado' THEN 'alto'
    WHEN l.estado_lote = 'activo' AND COALESCE(l.cantidad_total, 0) > 0 AND (COALESCE(l.cantidad_disponible, 0) / NULLIF(l.cantidad_total, 0)) <= 0.10 THEN 'medio'
    ELSE 'bajo'
  END COLLATE utf8mb4_unicode_ci AS nivel_riesgo,
  CASE
    WHEN l.estado_lote = 'bloqueado' THEN 'Lote bloqueado para operacion'
    WHEN l.estado_lote = 'activo' AND COALESCE(l.cantidad_total, 0) > 0 AND (COALESCE(l.cantidad_disponible, 0) / NULLIF(l.cantidad_total, 0)) <= 0.10 THEN 'Riesgo de quiebre de stock'
    ELSE 'Sin riesgo critico detectado'
  END COLLATE utf8mb4_unicode_ci AS descripcion,
  NOW() AS fecha_corte
FROM lotes l;

COMMIT;

-- =========================================================
-- POST-MIGRACION (manual recomendado por operador)
-- CALL sp_refresh_ia_search_index();
-- =========================================================
