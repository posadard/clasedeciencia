-- ==============================================================
-- Clase de Ciencia - IA Search Index (v3)
-- Objetivo:
--   1) Crear un indice canonico reutilizable por buscador + IA.
--   2) Centralizar entidades (clases, kits, componentes, manuales).
--   3) Habilitar regeneracion atomica desde un solo SP.
--
-- Uso:
--   - Ejecutar este script una vez.
--   - Luego regenerar indice con: CALL sp_refresh_ia_search_index();
-- ==============================================================

SET NAMES utf8mb4;

-- --------------------------------------------------------------
-- 0) Limpieza opcional de objetos anteriores (idempotencia)
-- --------------------------------------------------------------
DROP VIEW IF EXISTS v_ia_manual_relaciones;
DROP VIEW IF EXISTS v_ia_clase_completitud;
DROP VIEW IF EXISTS v_ia_search_index_stats;

DROP PROCEDURE IF EXISTS sp_refresh_ia_search_index;

-- --------------------------------------------------------------
-- 1) Tabla principal del indice canonico
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ia_search_index (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    entity_type ENUM('clase','kit','componente','manual') NOT NULL,
    entity_id INT NOT NULL,

    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    url VARCHAR(255) DEFAULT NULL,

    status_publicacion VARCHAR(40) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,

    -- Texto bruto y texto normalizado para matching
    search_text LONGTEXT NOT NULL,
    search_text_normalized LONGTEXT NOT NULL,

    -- Metadatos enriquecidos para IA
    keywords_json JSON DEFAULT NULL,
    relations_json JSON DEFAULT NULL,

    score_base DECIMAL(10,4) NOT NULL DEFAULT 1.0000,

    source_updated_at DATETIME NULL,
    indexed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_ia_search_entity (entity_type, entity_id),
    KEY idx_ia_search_type_active (entity_type, is_active),
    KEY idx_ia_search_status (status_publicacion),
    KEY idx_ia_search_indexed_at (indexed_at),
    FULLTEXT KEY ft_ia_search_text (title, search_text, search_text_normalized)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- 2) Metadatos de regeneracion del indice
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ia_search_index_meta (
    id INT NOT NULL AUTO_INCREMENT,
    index_version VARCHAR(50) NOT NULL,
    generated_at DATETIME NOT NULL,
    source_summary JSON DEFAULT NULL,
    notes VARCHAR(500) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ia_search_meta_generated (generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- 3) Vistas de apoyo para IA (relaciones y completitud)
-- --------------------------------------------------------------
CREATE OR REPLACE VIEW v_ia_manual_relaciones AS
SELECT
    km.id AS manual_id,
    km.titulo AS manual_titulo,
    km.slug AS manual_slug,
    km.status AS manual_status,
    km.ambito,
    km.tipo_manual,
    km.kit_id,
    k.nombre AS kit_nombre,
    km.item_id,
    i.nombre_comun AS componente_nombre,

    (
        SELECT GROUP_CONCAT(DISTINCT c1.id ORDER BY c1.id SEPARATOR ',')
        FROM clase_kits ck1
        JOIN clases c1 ON c1.id = ck1.clase_id
        WHERE ck1.kit_id = km.kit_id
    ) AS clase_ids_por_kit,

    (
        SELECT GROUP_CONCAT(DISTINCT c2.nombre ORDER BY c2.nombre SEPARATOR ', ')
        FROM clase_kits ck2
        JOIN clases c2 ON c2.id = ck2.clase_id
        WHERE ck2.kit_id = km.kit_id
    ) AS clases_por_kit,

    (
        SELECT GROUP_CONCAT(DISTINCT c3.id ORDER BY c3.id SEPARATOR ',')
        FROM kit_componentes kcx
        JOIN clase_kits ck3 ON ck3.kit_id = kcx.kit_id
        JOIN clases c3 ON c3.id = ck3.clase_id
        WHERE kcx.item_id = km.item_id
    ) AS clase_ids_por_componente,

    (
        SELECT GROUP_CONCAT(DISTINCT c4.nombre ORDER BY c4.nombre SEPARATOR ', ')
        FROM kit_componentes kcy
        JOIN clase_kits ck4 ON ck4.kit_id = kcy.kit_id
        JOIN clases c4 ON c4.id = ck4.clase_id
        WHERE kcy.item_id = km.item_id
    ) AS clases_por_componente
FROM kit_manuals km
LEFT JOIN kits k ON k.id = km.kit_id
LEFT JOIN kit_items i ON i.id = km.item_id;

CREATE OR REPLACE VIEW v_ia_clase_completitud AS
SELECT
    c.id AS clase_id,
    c.nombre AS clase_nombre,
    c.ciclo,
    c.activo,
    COUNT(DISTINCT ck.kit_id) AS total_kits,
    COUNT(DISTINCT kc.item_id) AS total_componentes,
    COUNT(DISTINCT km.id) AS total_manuales_publicados,
    ROUND(
        COUNT(DISTINCT ck.kit_id) * 0.40 +
        COUNT(DISTINCT km.id) * 0.35 +
        COUNT(DISTINCT kc.item_id) * 0.25,
        4
    ) AS score_completitud
FROM clases c
LEFT JOIN clase_kits ck ON ck.clase_id = c.id
LEFT JOIN kit_componentes kc ON kc.kit_id = ck.kit_id
LEFT JOIN kit_manuals km
    ON km.status = 'published'
   AND (km.kit_id = ck.kit_id OR km.item_id = kc.item_id)
GROUP BY c.id, c.nombre, c.ciclo, c.activo;

CREATE OR REPLACE VIEW v_ia_search_index_stats AS
SELECT
    entity_type,
    COUNT(*) AS total,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS activos,
    SUM(CASE WHEN status_publicacion = 'published' THEN 1 ELSE 0 END) AS publicados,
    MAX(indexed_at) AS ultima_indexacion
FROM ia_search_index
GROUP BY entity_type;

-- --------------------------------------------------------------
-- 4) Procedimiento de regeneracion del indice
-- --------------------------------------------------------------
DELIMITER $$

CREATE PROCEDURE sp_refresh_ia_search_index()
BEGIN
    DECLARE v_now DATETIME;
    DECLARE v_total_clases INT DEFAULT 0;
    DECLARE v_total_kits INT DEFAULT 0;
    DECLARE v_total_componentes INT DEFAULT 0;
    DECLARE v_total_manuales INT DEFAULT 0;

    SET v_now = NOW();

    START TRANSACTION;

    DELETE FROM ia_search_index;

    -- ----------------------------------------------------------
    -- Clases
    -- ----------------------------------------------------------
    INSERT INTO ia_search_index (
        entity_type,
        entity_id,
        title,
        slug,
        url,
        status_publicacion,
        is_active,
        search_text,
        search_text_normalized,
        keywords_json,
        relations_json,
        score_base,
        source_updated_at,
        indexed_at
    )
    SELECT
        'clase' AS entity_type,
        c.id AS entity_id,
        c.nombre AS title,
        c.slug,
        CONCAT('/', c.slug) AS url,
        CASE WHEN c.activo = 1 THEN 'published' ELSE 'inactive' END AS status_publicacion,
        c.activo AS is_active,
        CONCAT_WS(' ',
            c.nombre,
            COALESCE(c.resumen, ''),
            COALESCE(c.objetivo_aprendizaje, ''),
            COALESCE((SELECT GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ' ')
                      FROM clase_areas ca
                      JOIN areas a ON a.id = ca.area_id
                      WHERE ca.clase_id = c.id), ''),
            COALESCE((SELECT GROUP_CONCAT(DISTINCT ct.tag SEPARATOR ' ')
                      FROM clase_tags ct
                      WHERE ct.clase_id = c.id), ''),
            CONCAT('ciclo ', COALESCE(c.ciclo, '')),
            COALESCE(c.dificultad, '')
        ) AS search_text,
        LOWER(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                CONCAT_WS(' ', c.nombre, COALESCE(c.resumen, ''), COALESCE(c.objetivo_aprendizaje, ''), COALESCE(c.dificultad, '')),
                'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'ü','u')
        ) AS search_text_normalized,
        JSON_OBJECT(
            'ciclo', c.ciclo,
            'dificultad', c.dificultad,
            'duracion_minutos', c.duracion_minutos,
            'destacado', c.destacado
        ) AS keywords_json,
        JSON_OBJECT(
            'kits_ids', (
                SELECT JSON_ARRAYAGG(DISTINCT ck.kit_id)
                FROM clase_kits ck
                WHERE ck.clase_id = c.id
            )
        ) AS relations_json,
        1.0 + (CASE WHEN c.destacado = 1 THEN 0.25 ELSE 0 END) AS score_base,
        COALESCE(c.updated_at, v_now) AS source_updated_at,
        v_now AS indexed_at
    FROM clases c;

    -- ----------------------------------------------------------
    -- Kits
    -- ----------------------------------------------------------
    INSERT INTO ia_search_index (
        entity_type,
        entity_id,
        title,
        slug,
        url,
        status_publicacion,
        is_active,
        search_text,
        search_text_normalized,
        keywords_json,
        relations_json,
        score_base,
        source_updated_at,
        indexed_at
    )
    SELECT
        'kit',
        k.id,
        k.nombre,
        k.slug,
        CONCAT('/', k.slug),
        CASE WHEN k.activo = 1 THEN 'published' ELSE 'inactive' END,
        k.activo,
        CONCAT_WS(' ',
            k.nombre,
            COALESCE(k.codigo, ''),
            COALESCE(k.version, ''),
            COALESCE(k.resumen, ''),
            COALESCE((SELECT GROUP_CONCAT(DISTINCT c.nombre SEPARATOR ' ')
                      FROM clase_kits ck
                      JOIN clases c ON c.id = ck.clase_id
                      WHERE ck.kit_id = k.id), '')
        ),
        LOWER(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                CONCAT_WS(' ', k.nombre, COALESCE(k.codigo, ''), COALESCE(k.version, ''), COALESCE(k.resumen, '')),
                'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'ü','u')
        ),
        JSON_OBJECT(
            'codigo', k.codigo,
            'version', k.version
        ),
        JSON_OBJECT(
            'clases_ids', (
                SELECT JSON_ARRAYAGG(DISTINCT ck.clase_id)
                FROM clase_kits ck
                WHERE ck.kit_id = k.id
            ),
            'componentes_ids', (
                SELECT JSON_ARRAYAGG(DISTINCT kc.item_id)
                FROM kit_componentes kc
                WHERE kc.kit_id = k.id
            )
        ),
        1.0,
        COALESCE(k.updated_at, v_now),
        v_now
    FROM kits k;

    -- ----------------------------------------------------------
    -- Componentes
    -- ----------------------------------------------------------
    INSERT INTO ia_search_index (
        entity_type,
        entity_id,
        title,
        slug,
        url,
        status_publicacion,
        is_active,
        search_text,
        search_text_normalized,
        keywords_json,
        relations_json,
        score_base,
        source_updated_at,
        indexed_at
    )
    SELECT
        'componente',
        i.id,
        i.nombre_comun,
        i.slug,
        CONCAT('/', i.slug),
        CASE WHEN i.activo = 1 THEN 'published' ELSE 'inactive' END,
        i.activo,
        CONCAT_WS(' ',
            i.nombre_comun,
            COALESCE(i.sku, ''),
            COALESCE(i.descripcion_corta, ''),
            COALESCE(i.advertencias_seguridad, ''),
            COALESCE(cat.nombre, '')
        ),
        LOWER(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                CONCAT_WS(' ', i.nombre_comun, COALESCE(i.sku, ''), COALESCE(i.descripcion_corta, ''), COALESCE(cat.nombre, '')),
                'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'ü','u')
        ),
        JSON_OBJECT(
            'sku', i.sku,
            'categoria', cat.nombre
        ),
        JSON_OBJECT(
            'kits_ids', (
                SELECT JSON_ARRAYAGG(DISTINCT kc.kit_id)
                FROM kit_componentes kc
                WHERE kc.item_id = i.id
            )
        ),
        1.0,
        COALESCE(i.updated_at, v_now),
        v_now
    FROM kit_items i
    LEFT JOIN categorias_items cat ON cat.id = i.categoria_id;

    -- ----------------------------------------------------------
    -- Manuales
    -- ----------------------------------------------------------
    INSERT INTO ia_search_index (
        entity_type,
        entity_id,
        title,
        slug,
        url,
        status_publicacion,
        is_active,
        search_text,
        search_text_normalized,
        keywords_json,
        relations_json,
        score_base,
        source_updated_at,
        indexed_at
    )
    SELECT
        'manual',
        km.id,
        km.titulo,
        km.slug,
        CONCAT('/manual/', km.slug),
        km.status,
        CASE WHEN km.status = 'published' THEN 1 ELSE 0 END,
        CONCAT_WS(' ',
            km.titulo,
            COALESCE(km.descripcion, ''),
            COALESCE(km.ambito, ''),
            COALESCE(km.tipo_manual, ''),
            COALESCE(k.nombre, ''),
            COALESCE(i.nombre_comun, '')
        ),
        LOWER(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                CONCAT_WS(' ', km.titulo, COALESCE(km.descripcion, ''), COALESCE(km.ambito, ''), COALESCE(km.tipo_manual, ''), COALESCE(k.nombre, ''), COALESCE(i.nombre_comun, '')),
                'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'ü','u')
        ),
        JSON_OBJECT(
            'ambito', km.ambito,
            'tipo_manual', km.tipo_manual,
            'kit_id', km.kit_id,
            'item_id', km.item_id
        ),
        JSON_OBJECT(
            'kit_nombre', k.nombre,
            'componente_nombre', i.nombre_comun,
            'clase_ids_por_kit', (
                SELECT JSON_ARRAYAGG(DISTINCT ck.clase_id)
                FROM clase_kits ck
                WHERE ck.kit_id = km.kit_id
            ),
            'clase_ids_por_componente', (
                SELECT JSON_ARRAYAGG(DISTINCT ck2.clase_id)
                FROM kit_componentes kc2
                JOIN clase_kits ck2 ON ck2.kit_id = kc2.kit_id
                WHERE kc2.item_id = km.item_id
            )
        ),
        CASE WHEN km.status = 'published' THEN 1.2 ELSE 0.7 END,
        COALESCE(km.updated_at, v_now),
        v_now
    FROM kit_manuals km
    LEFT JOIN kits k ON k.id = km.kit_id
    LEFT JOIN kit_items i ON i.id = km.item_id;

    -- ----------------------------------------------------------
    -- Meta de control
    -- ----------------------------------------------------------
    SELECT COUNT(*) INTO v_total_clases FROM clases;
    SELECT COUNT(*) INTO v_total_kits FROM kits;
    SELECT COUNT(*) INTO v_total_componentes FROM kit_items;
    SELECT COUNT(*) INTO v_total_manuales FROM kit_manuals;

    INSERT INTO ia_search_index_meta (
        index_version,
        generated_at,
        source_summary,
        notes
    ) VALUES (
        'v3',
        v_now,
        JSON_OBJECT(
            'clases', v_total_clases,
            'kits', v_total_kits,
            'componentes', v_total_componentes,
            'manuales', v_total_manuales,
            'index_rows', (SELECT COUNT(*) FROM ia_search_index)
        ),
        'Regenerado por sp_refresh_ia_search_index'
    );

    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------------
-- 5) Primera regeneracion recomendada
-- --------------------------------------------------------------
CALL sp_refresh_ia_search_index();

-- --------------------------------------------------------------
-- 6) Verificaciones rapidas
-- --------------------------------------------------------------
-- SELECT * FROM v_ia_search_index_stats;
-- SELECT * FROM ia_search_index_meta ORDER BY id DESC LIMIT 5;
-- SELECT entity_type, COUNT(*) FROM ia_search_index GROUP BY entity_type;
