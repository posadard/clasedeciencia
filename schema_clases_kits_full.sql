-- ============================================
-- Clase de Ciencia - Full DB Schema Bootstrap
-- Clases + Kits (replacing Proyectos + Materiales)
-- Date: 2025-12-19
-- ============================================

-- Recommended session settings
SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;

-- ============================================
-- 0) Create database and select it
-- ============================================
CREATE DATABASE IF NOT EXISTS `u626603208_clasedeciencia`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `u626603208_clasedeciencia`;

-- ============================================
-- 1) Editorial taxonomies and catalogs
-- ============================================

-- Secciones editoriales (opcional para categorizar clases)
CREATE TABLE IF NOT EXISTS secciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  UNIQUE KEY uq_secciones_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Áreas disciplinares (Física, Química, Biología, Tecnología, Ambiental)
CREATE TABLE IF NOT EXISTS areas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  UNIQUE KEY uq_areas_slug (slug),
  UNIQUE KEY uq_areas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Competencias MEN (indagacion, explicacion, uso_conocimiento)
CREATE TABLE IF NOT EXISTS competencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(80) NOT NULL,     -- ej: 'indagacion'
  nombre VARCHAR(160) NOT NULL,    -- ej: 'Formulo preguntas, observo, registro datos'
  UNIQUE KEY uq_competencias_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías del catálogo de ítems del kit
CREATE TABLE IF NOT EXISTS categorias_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  UNIQUE KEY uq_categorias_items_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2) Clases (antes Proyectos)
-- ============================================
CREATE TABLE IF NOT EXISTS clases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(180) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  ciclo TINYINT(1) NOT NULL,               -- 1: 6°-7°, 2: 8°-9°, 3: 10°-11°
  grados JSON NULL,                         -- opcional, lista de grados
  dificultad VARCHAR(32) NULL,             -- 'facil' | 'media' | 'dificil'
  duracion_minutos INT NULL,
  resumen TEXT NULL,
  objetivo_aprendizaje TEXT NULL,
  imagen_portada VARCHAR(255) NULL,
  video_portada VARCHAR(255) NULL,
  seguridad JSON NULL,                      -- notas de seguridad
  seo_title VARCHAR(160) NULL,
  seo_description VARCHAR(255) NULL,
  canonical_url VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  orden_popularidad INT NOT NULL DEFAULT 0,
  -- Editorial extras
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  autor VARCHAR(120) NULL,
  contenido_html MEDIUMTEXT NULL,
  -- Relaciones
  seccion_id INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_clases_slug (slug),
  INDEX idx_clases_activo_ciclo (activo, ciclo),
  INDEX idx_clases_status_published (status, published_at),
  INDEX idx_clases_popularidad (orden_popularidad),
  INDEX idx_clases_seccion (seccion_id),
  CONSTRAINT fk_clases_seccion FOREIGN KEY (seccion_id) REFERENCES secciones(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relación N–N: Clase ↔ Área
CREATE TABLE IF NOT EXISTS clase_areas (
  clase_id INT NOT NULL,
  area_id INT NOT NULL,
  PRIMARY KEY (clase_id, area_id),
  INDEX idx_clase_areas_clase (clase_id),
  INDEX idx_clase_areas_area (area_id),
  CONSTRAINT fk_clase_areas_clase FOREIGN KEY (clase_id) REFERENCES clases(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_clase_areas_area FOREIGN KEY (area_id) REFERENCES areas(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relación N–N: Clase ↔ Competencia MEN
CREATE TABLE IF NOT EXISTS clase_competencias (
  clase_id INT NOT NULL,
  competencia_id INT NOT NULL,
  PRIMARY KEY (clase_id, competencia_id),
  INDEX idx_clase_competencias_clase (clase_id),
  INDEX idx_clase_competencias_comp (competencia_id),
  CONSTRAINT fk_clase_competencias_clase FOREIGN KEY (clase_id) REFERENCES clases(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_clase_competencias_comp FOREIGN KEY (competencia_id) REFERENCES competencias(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags libres para clases
CREATE TABLE IF NOT EXISTS clase_tags (
  clase_id INT NOT NULL,
  tag VARCHAR(64) NOT NULL,
  PRIMARY KEY (clase_id, tag),
  INDEX idx_clase_tags_tag (tag),
  CONSTRAINT fk_clase_tags_clase FOREIGN KEY (clase_id) REFERENCES clases(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3) Kits (antes Materiales)
-- ============================================

-- Catálogo de ítems (antes 'materiales')
CREATE TABLE IF NOT EXISTS kit_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre_comun VARCHAR(160) NOT NULL,
  categoria_id INT NULL,
  advertencias_seguridad TEXT NULL,
  unidad VARCHAR(32) NULL,                 -- ej: 'pcs', 'ml', 'g'
  sku VARCHAR(64) NULL,
  INDEX idx_kit_items_nombre (nombre_comun),
  INDEX idx_kit_items_categoria (categoria_id),
  CONSTRAINT fk_kit_items_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_items(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Un kit por clase (permite código y versiones)
CREATE TABLE IF NOT EXISTS kits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clase_id INT NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  codigo VARCHAR(64) NULL,
  version VARCHAR(32) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kits_codigo (codigo),
  INDEX idx_kits_clase (clase_id),
  CONSTRAINT fk_kits_clase FOREIGN KEY (clase_id) REFERENCES clases(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Componentes del kit: relación N–N con cantidades y orden
CREATE TABLE IF NOT EXISTS kit_componentes (
  kit_id INT NOT NULL,
  item_id INT NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
  es_incluido_kit TINYINT(1) NOT NULL DEFAULT 1,
  notas VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (kit_id, item_id),
  INDEX idx_kit_componentes_order (kit_id, sort_order),
  CONSTRAINT fk_kit_componentes_kit FOREIGN KEY (kit_id) REFERENCES kits(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_kit_componentes_item FOREIGN KEY (item_id) REFERENCES kit_items(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4) Guías y recursos multimedia
-- ============================================

CREATE TABLE IF NOT EXISTS guias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clase_id INT NOT NULL,
  pasos JSON NULL,                          -- arreglo de pasos con texto/imagen/video
  explicacion_cientifica TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_guias_clase (clase_id),
  CONSTRAINT fk_guias_clase FOREIGN KEY (clase_id) REFERENCES clases(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recursos_multimedia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clase_id INT NOT NULL,
  tipo ENUM('imagen','video','pdf','link') NOT NULL,
  url VARCHAR(255) NOT NULL,
  titulo VARCHAR(180) NULL,
  descripcion VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rm_clase (clase_id),
  INDEX idx_rm_order (clase_id, sort_order),
  CONSTRAINT fk_rm_clase FOREIGN KEY (clase_id) REFERENCES clases(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5) Analytics y administración CTeI
-- ============================================

CREATE TABLE IF NOT EXISTS analytics_visitas (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  clase_id INT NULL,                        -- puede ser NULL para visitas generales
  tipo_pagina VARCHAR(64) NOT NULL,        -- ej: 'clase', 'catalogo', 'home'
  departamento VARCHAR(120) NULL,
  dispositivo VARCHAR(64) NULL,            -- 'mobile' | 'desktop' ...
  visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_analytics_clase (clase_id),
  INDEX idx_analytics_tipo (tipo_pagina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contratos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero VARCHAR(64) NOT NULL,
  entidad_contratante VARCHAR(255) NOT NULL,
  departamento VARCHAR(120) NOT NULL,
  valor DECIMAL(16,2) NOT NULL,
  fecha DATE NULL,
  UNIQUE KEY uq_contratos_numero (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS entregas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  contrato_id INT NOT NULL,
  institucion_educativa VARCHAR(255) NOT NULL,
  fecha DATE NOT NULL,
  acta_pdf VARCHAR(255) NULL,
  INDEX idx_entregas_contrato (contrato_id),
  CONSTRAINT fk_entregas_contrato FOREIGN KEY (contrato_id) REFERENCES contratos(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6) Helpful views for IA and reporting
-- ============================================

-- Contexto IA por clase
CREATE OR REPLACE VIEW v_clase_contexto_ia AS
SELECT c.id AS clase_id,
       c.nombre,
       c.slug,
       c.ciclo,
       c.dificultad,
       c.duracion_minutos,
       c.resumen,
       c.objetivo_aprendizaje,
       (SELECT JSON_ARRAYAGG(a.nombre)
        FROM clase_areas ca
        JOIN areas a ON a.id = ca.area_id
        WHERE ca.clase_id = c.id) AS areas,
       (SELECT JSON_ARRAYAGG(comp.nombre)
        FROM clase_competencias cc
        JOIN competencias comp ON comp.id = cc.competencia_id
        WHERE cc.clase_id = c.id) AS competencias
FROM clases c
WHERE c.activo = 1;

-- Detalle de componentes de kit por clase
CREATE OR REPLACE VIEW v_clase_kits_detalle AS
SELECT k.id AS kit_id,
       k.clase_id,
       k.nombre AS kit_nombre,
       i.id AS item_id,
       i.nombre_comun AS item_nombre,
       kc.cantidad,
       kc.es_incluido_kit,
       kc.notas
FROM kits k
JOIN kit_componentes kc ON kc.kit_id = k.id
JOIN kit_items i ON i.id = kc.item_id;

-- Clases populares para IA/UI
CREATE OR REPLACE VIEW v_clases_populares_ia AS
SELECT c.id,
       c.nombre,
       c.slug,
       c.orden_popularidad
FROM clases c
WHERE c.activo = 1
ORDER BY c.orden_popularidad DESC;

-- ============================================
-- 7) Optional seeds (comment out if not needed)
-- ============================================

-- INSERT INTO areas (nombre, slug) VALUES
--   ('Física','fisica'),('Química','quimica'),('Biología','biologia'),
--   ('Tecnología','tecnologia'),('Ambiental','ambiental');

-- INSERT INTO competencias (codigo, nombre) VALUES
--   ('indagacion','Formulo preguntas, observo, registro datos'),
--   ('explicacion','Establezco relaciones causales, modelo fenómenos'),
--   ('uso_conocimiento','Aplico conceptos a situaciones reales');

-- ============================================
-- 8) Restore FK checks
-- ============================================
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
