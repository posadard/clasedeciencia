-- =====================================================
-- Clase de Ciencia - Modulos admin faltantes (CTeI + IA)
-- Archivo: scripts/admin_modulos_ctei_ia_v1.sql
-- Objetivo:
--   1) Fortalecer contratos y entregas
--   2) Crear lotes y relacion entrega_lotes
--   3) Crear auditoria administrativa
--   4) Crear vistas para consultas del asistente IA backend
--
-- Nota:
--   - Script pensado para MariaDB/MySQL.
--   - Ejecutar en entorno de desarrollo primero.
-- =====================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------
-- 1) CONTRATOS: ampliar modelo operativo
-- -----------------------------------------------------
ALTER TABLE contratos
  ADD COLUMN IF NOT EXISTS municipio VARCHAR(120) NULL AFTER departamento,
  ADD COLUMN IF NOT EXISTS fecha_inicio DATE NULL AFTER fecha,
  ADD COLUMN IF NOT EXISTS fecha_fin DATE NULL AFTER fecha_inicio,
  ADD COLUMN IF NOT EXISTS valor_ejecutado DECIMAL(16,2) NOT NULL DEFAULT 0.00 AFTER valor,
  ADD COLUMN IF NOT EXISTS estado_contrato ENUM('borrador','vigente','suspendido','finalizado','cerrado') NOT NULL DEFAULT 'borrador' AFTER valor_ejecutado,
  ADD COLUMN IF NOT EXISTS supervisor VARCHAR(180) NULL AFTER estado_contrato,
  ADD COLUMN IF NOT EXISTS objeto_contrato TEXT NULL AFTER supervisor,
  ADD COLUMN IF NOT EXISTS contrato_pdf VARCHAR(255) NULL AFTER objeto_contrato,
  ADD COLUMN IF NOT EXISTS observaciones TEXT NULL AFTER contrato_pdf,
  ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER observaciones,
  ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Backfill minimo para registros ya existentes
UPDATE contratos
SET
  fecha_inicio = COALESCE(fecha_inicio, fecha),
  estado_contrato = CASE
    WHEN estado_contrato IS NULL OR estado_contrato = '' THEN 'vigente'
    ELSE estado_contrato
  END,
  valor_ejecutado = COALESCE(valor_ejecutado, 0)
WHERE 1=1;

CREATE INDEX IF NOT EXISTS idx_contratos_estado ON contratos (estado_contrato);
CREATE INDEX IF NOT EXISTS idx_contratos_depto ON contratos (departamento);
CREATE INDEX IF NOT EXISTS idx_contratos_fechas ON contratos (fecha_inicio, fecha_fin);

-- -----------------------------------------------------
-- 2) ENTREGAS: ampliar trazabilidad operativa
-- -----------------------------------------------------
ALTER TABLE entregas
  ADD COLUMN IF NOT EXISTS codigo_entrega VARCHAR(64) NULL AFTER id,
  ADD COLUMN IF NOT EXISTS departamento VARCHAR(120) NULL AFTER institucion_educativa,
  ADD COLUMN IF NOT EXISTS municipio VARCHAR(120) NULL AFTER departamento,
  ADD COLUMN IF NOT EXISTS fecha_programada DATE NULL AFTER fecha,
  ADD COLUMN IF NOT EXISTS estado_entrega ENUM('programada','en_transito','entregada','rechazada','reprogramada') NOT NULL DEFAULT 'programada' AFTER fecha_programada,
  ADD COLUMN IF NOT EXISTS responsable_entrega VARCHAR(180) NULL AFTER estado_entrega,
  ADD COLUMN IF NOT EXISTS responsable_recepcion VARCHAR(180) NULL AFTER responsable_entrega,
  ADD COLUMN IF NOT EXISTS cantidad_kits INT NOT NULL DEFAULT 0 AFTER responsable_recepcion,
  ADD COLUMN IF NOT EXISTS recibido_ok TINYINT(1) NOT NULL DEFAULT 0 AFTER cantidad_kits,
  ADD COLUMN IF NOT EXISTS novedad TEXT NULL AFTER recibido_ok,
  ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER novedad,
  ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- codigo_entrega (si no existe) para historico
UPDATE entregas
SET codigo_entrega = CONCAT('ENT-', LPAD(id, 6, '0'))
WHERE (codigo_entrega IS NULL OR codigo_entrega = '');

-- Backfill de estados para registros existentes
UPDATE entregas
SET
  estado_entrega = CASE
    WHEN fecha IS NOT NULL THEN 'entregada'
    ELSE estado_entrega
  END,
  fecha_programada = COALESCE(fecha_programada, fecha),
  recibido_ok = CASE
    WHEN fecha IS NOT NULL THEN 1
    ELSE recibido_ok
  END
WHERE 1=1;

CREATE UNIQUE INDEX IF NOT EXISTS uq_entregas_codigo ON entregas (codigo_entrega);
CREATE INDEX IF NOT EXISTS idx_entregas_estado ON entregas (estado_entrega);
CREATE INDEX IF NOT EXISTS idx_entregas_fecha_prog ON entregas (fecha_programada);
CREATE INDEX IF NOT EXISTS idx_entregas_fecha_real ON entregas (fecha);
CREATE INDEX IF NOT EXISTS idx_entregas_geo ON entregas (departamento, municipio);

-- -----------------------------------------------------
-- 3) LOTES: tabla faltante para inventario y trazabilidad
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS lotes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  codigo_lote VARCHAR(64) NOT NULL,
  kit_id INT(11) NOT NULL,
  contrato_id INT(11) DEFAULT NULL,
  cantidad_total INT NOT NULL DEFAULT 0,
  cantidad_disponible INT NOT NULL DEFAULT 0,
  cantidad_asignada INT NOT NULL DEFAULT 0,
  cantidad_entregada INT NOT NULL DEFAULT 0,
  fecha_fabricacion DATE DEFAULT NULL,
  fecha_caducidad DATE DEFAULT NULL,
  estado_lote ENUM('activo','bloqueado','agotado','cerrado') NOT NULL DEFAULT 'activo',
  ubicacion VARCHAR(180) DEFAULT NULL,
  observaciones TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_lotes_codigo (codigo_lote),
  KEY idx_lotes_kit (kit_id),
  KEY idx_lotes_contrato (contrato_id),
  KEY idx_lotes_estado (estado_lote),
  KEY idx_lotes_disponible (cantidad_disponible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FKs de lotes
-- Si ya existen en algun entorno, ajustar nombres antes de re-ejecutar.
ALTER TABLE lotes
  ADD CONSTRAINT fk_lotes_kit
    FOREIGN KEY (kit_id) REFERENCES kits(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  ADD CONSTRAINT fk_lotes_contrato
    FOREIGN KEY (contrato_id) REFERENCES contratos(id)
    ON UPDATE CASCADE ON DELETE SET NULL;

-- -----------------------------------------------------
-- 4) RELACION ENTREGA <-> LOTES
-- Permite una entrega con multiples lotes y cantidades
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS entrega_lotes (
  entrega_id INT(11) NOT NULL,
  lote_id INT(11) NOT NULL,
  cantidad_asignada INT NOT NULL DEFAULT 0,
  cantidad_entregada INT NOT NULL DEFAULT 0,
  observaciones VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (entrega_id, lote_id),
  KEY idx_el_lote (lote_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE entrega_lotes
  ADD CONSTRAINT fk_el_entrega
    FOREIGN KEY (entrega_id) REFERENCES entregas(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  ADD CONSTRAINT fk_el_lote
    FOREIGN KEY (lote_id) REFERENCES lotes(id)
    ON UPDATE CASCADE ON DELETE RESTRICT;

-- -----------------------------------------------------
-- 5) AUDITORIA ADMINISTRATIVA
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS auditoria_admin (
  id BIGINT NOT NULL AUTO_INCREMENT,
  modulo VARCHAR(64) NOT NULL,
  entidad VARCHAR(64) NOT NULL,
  entidad_id INT(11) NOT NULL,
  accion ENUM('crear','editar','eliminar','cambio_estado') NOT NULL,
  usuario VARCHAR(120) NOT NULL,
  detalle_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  ip VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_entidad (entidad, entidad_id),
  KEY idx_audit_modulo (modulo),
  KEY idx_audit_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 6) VISTAS IA-READY PARA ASISTENTE ADMIN
-- -----------------------------------------------------

DROP VIEW IF EXISTS v_admin_contratos_resumen;
CREATE VIEW v_admin_contratos_resumen AS
SELECT
  c.id,
  c.numero,
  c.entidad_contratante,
  c.departamento,
  c.municipio,
  c.fecha,
  c.fecha_inicio,
  c.fecha_fin,
  c.valor,
  c.valor_ejecutado,
  (c.valor - c.valor_ejecutado) AS saldo_pendiente,
  CASE
    WHEN c.valor > 0 THEN ROUND((c.valor_ejecutado / c.valor) * 100, 2)
    ELSE 0
  END AS avance_financiero_pct,
  c.estado_contrato,
  c.supervisor,
  c.updated_at
FROM contratos c;

DROP VIEW IF EXISTS v_admin_entregas_resumen;
CREATE VIEW v_admin_entregas_resumen AS
SELECT
  e.id,
  e.codigo_entrega,
  e.contrato_id,
  c.numero AS contrato_numero,
  c.entidad_contratante,
  e.institucion_educativa,
  COALESCE(e.departamento, c.departamento) AS departamento,
  e.municipio,
  e.fecha_programada,
  e.fecha AS fecha_entrega,
  e.estado_entrega,
  e.cantidad_kits,
  e.recibido_ok,
  e.acta_pdf,
  CASE
    WHEN e.estado_entrega IN ('programada','reprogramada') AND e.fecha_programada IS NOT NULL AND e.fecha_programada < CURDATE() THEN 1
    ELSE 0
  END AS entrega_atrasada,
  e.updated_at
FROM entregas e
JOIN contratos c ON c.id = e.contrato_id;

DROP VIEW IF EXISTS v_admin_lotes_resumen;
CREATE VIEW v_admin_lotes_resumen AS
SELECT
  l.id,
  l.codigo_lote,
  l.kit_id,
  k.nombre AS kit_nombre,
  l.contrato_id,
  c.numero AS contrato_numero,
  l.cantidad_total,
  l.cantidad_disponible,
  l.cantidad_asignada,
  l.cantidad_entregada,
  l.estado_lote,
  l.ubicacion,
  l.fecha_fabricacion,
  l.fecha_caducidad,
  CASE
    WHEN l.cantidad_total > 0 THEN ROUND((l.cantidad_disponible / l.cantidad_total) * 100, 2)
    ELSE 0
  END AS stock_disponible_pct,
  l.updated_at
FROM lotes l
JOIN kits k ON k.id = l.kit_id
LEFT JOIN contratos c ON c.id = l.contrato_id;

DROP VIEW IF EXISTS v_admin_riesgo_operativo;
CREATE VIEW v_admin_riesgo_operativo AS
SELECT
  'contrato_por_vencer' AS tipo_riesgo,
  c.id AS entidad_id,
  c.numero AS referencia,
  CONCAT('Contrato vence en ', DATEDIFF(c.fecha_fin, CURDATE()), ' dias') AS detalle,
  c.updated_at AS fecha_ref
FROM contratos c
WHERE c.fecha_fin IS NOT NULL
  AND c.estado_contrato IN ('vigente','suspendido')
  AND DATEDIFF(c.fecha_fin, CURDATE()) BETWEEN 0 AND 30

UNION ALL

SELECT
  'entrega_atrasada' AS tipo_riesgo,
  e.id AS entidad_id,
  e.codigo_entrega AS referencia,
  CONCAT('Entrega programada para ', DATE_FORMAT(e.fecha_programada, '%Y-%m-%d'), ' sin cierre') AS detalle,
  e.updated_at AS fecha_ref
FROM entregas e
WHERE e.estado_entrega IN ('programada','reprogramada','en_transito')
  AND e.fecha_programada IS NOT NULL
  AND e.fecha_programada < CURDATE()

UNION ALL

SELECT
  'stock_bajo_lote' AS tipo_riesgo,
  l.id AS entidad_id,
  l.codigo_lote AS referencia,
  CONCAT('Stock disponible bajo: ', l.cantidad_disponible, '/', l.cantidad_total) AS detalle,
  l.updated_at AS fecha_ref
FROM lotes l
WHERE l.estado_lote = 'activo'
  AND l.cantidad_total > 0
  AND (l.cantidad_disponible / l.cantidad_total) <= 0.15;

-- -----------------------------------------------------
-- 7) SUGERENCIAS DE DATOS BASE (OPCIONAL)
-- -----------------------------------------------------
-- Ejemplo de estado por defecto en contratos existentes
UPDATE contratos
SET estado_contrato = 'vigente'
WHERE estado_contrato = 'borrador' AND (fecha_inicio IS NOT NULL OR fecha IS NOT NULL);

-- =====================================================
-- FIN SCRIPT
-- =====================================================
