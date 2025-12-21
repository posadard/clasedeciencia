-- Clase de Ciencia - Sistema de Atributos Técnicos (Definiciones, Mapeo y Contenidos)
-- Nota: Este script SOLO define tablas y semillas. No reemplaza campos core existentes.
-- Motor/Collation alineados con el esquema actual.

-- =============================================================
-- 1) Definiciones de Atributos (catálogo maestro)
-- =============================================================
DROP TABLE IF EXISTS `atributos_definiciones`;
CREATE TABLE `atributos_definiciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(120) NOT NULL COMMENT 'Identificador técnico estable (ej: peso, tension_v)',
  `etiqueta` varchar(160) NOT NULL COMMENT 'Nombre visible (ej: Peso, Tensión)',
  `descripcion` text DEFAULT NULL,
  `tipo_dato` enum('string','integer','number','boolean','date','datetime','json') NOT NULL DEFAULT 'string',
  `cardinalidad` enum('one','many') NOT NULL DEFAULT 'one',
  `grupo` varchar(64) DEFAULT NULL COMMENT 'ficha|seguridad|empaque|electrico|multimedia|otros',
  `estado` enum('activo','borrador') NOT NULL DEFAULT 'activo',
  `schema_propiedad` varchar(160) DEFAULT NULL COMMENT 'Prop. de schema.org o ruta (ej: Product.weight, additionalProperty.voltage)',
  `unidad_defecto` varchar(12) DEFAULT NULL COMMENT 'unitCode UNECE/UN/CEFACT (ej: KGM, CMT, VLT)',
  `opciones_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `unidades_permitidas_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `aplica_a_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Ej: kit|componente',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_atributos_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 2) Mapeo atributo ↔ tipo de entidad (visibilidad/validación/UI)
-- =============================================================
DROP TABLE IF EXISTS `atributos_mapeo`;
CREATE TABLE `atributos_mapeo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `atributo_id` int(11) NOT NULL,
  `tipo_entidad` enum('clase','manual','multimedia','kit','componente') NOT NULL,
  `requerido` tinyint(1) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `validaciones_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ui_hint` varchar(32) DEFAULT NULL COMMENT 'input|select|slider|quantitative|tags',
  `buscable` tinyint(1) NOT NULL DEFAULT 0,
  `facetable` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mapeo_attr_entidad` (`atributo_id`,`tipo_entidad`),
  KEY `idx_mapeo_entidad` (`tipo_entidad`,`orden`),
  CONSTRAINT `fk_mapeo_atributo` FOREIGN KEY (`atributo_id`) REFERENCES `atributos_definiciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 3) Contenidos (valores de atributos por entidad)
-- =============================================================
DROP TABLE IF EXISTS `atributos_contenidos`;
CREATE TABLE `atributos_contenidos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tipo_entidad` enum('clase','manual','multimedia','kit','componente') NOT NULL,
  `entidad_id` int(11) NOT NULL,
  `atributo_id` int(11) NOT NULL,
  `valor_string` text DEFAULT NULL,
  `valor_numero` decimal(18,6) DEFAULT NULL,
  `valor_entero` int(11) DEFAULT NULL,
  `valor_booleano` tinyint(1) DEFAULT NULL,
  `valor_fecha` date DEFAULT NULL,
  `valor_datetime` datetime DEFAULT NULL,
  `valor_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `unidad_codigo` varchar(12) DEFAULT NULL COMMENT 'unitCode UNECE (ej: KGM, CMT, VLT, LTR)',
  `lang` varchar(10) DEFAULT NULL COMMENT 'ej: es-CO',
  `orden` int(11) NOT NULL DEFAULT 0,
  `fuente` varchar(32) DEFAULT NULL COMMENT 'manual|import|api',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contenidos_entidad` (`tipo_entidad`,`entidad_id`),
  KEY `idx_contenidos_atributo` (`atributo_id`),
  KEY `idx_contenidos_entidad_attr` (`tipo_entidad`,`atributo_id`),
  CONSTRAINT `fk_contenidos_atributo` FOREIGN KEY (`atributo_id`) REFERENCES `atributos_definiciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- Semilla de atributos (evitando duplicar campos core ya existentes)
-- Evitamos: 
--   clases.duracion_minutos / dificultad / ciclo / grados / resumen / objetivo_aprendizaje
--   kits.codigo / version
--   kit_items.sku / unidad / advertencias_seguridad
--   kit_manuals.time_minutes / dificultad_ensamble / seguridad_json / herramientas_json / pasos_json / html / idioma
-- =============================================================

-- Helper: función para JSON en aplica_a (indicativo; en MySQL se inserta como texto JSON)
-- a) Ficha técnica comunes (kits y componentes)
INSERT INTO `atributos_definiciones`
(`clave`,`etiqueta`,`descripcion`,`tipo_dato`,`cardinalidad`,`grupo`,`estado`,`schema_propiedad`,`unidad_defecto`,`opciones_json`,`unidades_permitidas_json`,`aplica_a_json`)
VALUES
('material','Material','Material(es) principal(es) del producto','string','many','ficha','activo','Product.material',NULL,NULL,NULL,'["kit","componente"]'),
('color','Color','Color principal','string','one','ficha','activo','Product.color',NULL,NULL,NULL,'["kit","componente"]'),
('peso','Peso','Peso neto del producto','number','one','ficha','activo','Product.weight','KGM',NULL,'["KGM","GRM"]','["kit","componente"]'),
('alto','Alto','Altura del producto','number','one','ficha','activo','Product.height','CMT',NULL,'["CMT","MMT"]','["kit","componente"]'),
('ancho','Ancho','Ancho del producto','number','one','ficha','activo','Product.width','CMT',NULL,'["CMT","MMT"]','["kit","componente"]'),
('largo','Largo','Largo/profundidad del producto','number','one','ficha','activo','Product.depth','CMT',NULL,'["CMT","MMT"]','["kit","componente"]'),
('volumen','Volumen','Volumen del contenido','number','one','ficha','activo','Product.volume','LTR',NULL,'["LTR","MLT"]','["kit","componente"]'),
('pais_fabricacion','País de fabricación','País de origen del producto','string','one','ficha','activo','Product.countryOfOrigin',NULL,NULL,NULL,'["kit","componente"]'),
('garantia_meses','Garantía (meses)','Duración de la garantía en meses','integer','one','ficha','activo','Offer.warranty',NULL,NULL,NULL,'["kit","componente"]');

-- b) Atributos eléctricos (kits/componentes)
INSERT INTO `atributos_definiciones`
(`clave`,`etiqueta`,`descripcion`,`tipo_dato`,`cardinalidad`,`grupo`,`estado`,`schema_propiedad`,`unidad_defecto`,`opciones_json`,`unidades_permitidas_json`,`aplica_a_json`)
VALUES
('tension_v','Tensión (V)','Tensión de operación','number','one','electrico','activo','additionalProperty.voltage','VLT',NULL,'["VLT"]','["kit","componente"]'),
('corriente_a','Corriente (A)','Corriente de operación','number','one','electrico','activo','additionalProperty.current','AMP',NULL,'["AMP","mA"]','["kit","componente"]'),
('potencia_w','Potencia (W)','Potencia nominal','number','one','electrico','activo','additionalProperty.power','WTT',NULL,'["WTT"]','["kit","componente"]'),
('polaridad','Polaridad','Tipo de corriente o polaridad','string','one','electrico','activo','additionalProperty.polarity',NULL,'["AC","DC","AC/DC"]',NULL,'["kit","componente"]');

-- c) Seguridad (kits/componentes)
INSERT INTO `atributos_definiciones`
(`clave`,`etiqueta`,`descripcion`,`tipo_dato`,`cardinalidad`,`grupo`,`estado`,`schema_propiedad`,`unidad_defecto`,`opciones_json`,`unidades_permitidas_json`,`aplica_a_json`)
VALUES
('norma_certificacion','Norma/Certificación','Normas o certificaciones aplicables (CE, ASTM, ISO)','string','many','seguridad','activo','conformsTo',NULL,NULL,NULL,'["kit","componente"]'),
('edad_segura_min','Edad segura mínima','Edad mínima recomendada para uso','integer','one','seguridad','activo','audience.suggestedMinAge',NULL,NULL,NULL,'["kit","componente"]'),
('edad_segura_max','Edad segura máxima','Edad máxima recomendada para uso','integer','one','seguridad','activo','audience.suggestedMaxAge',NULL,NULL,NULL,'["kit","componente"]'),
('pictogramas_ghs','Pictogramas GHS','Pictogramas de peligrosidad química (multi)','string','many','seguridad','activo','additionalProperty.ghsPictograms',NULL,'["GHS01","GHS02","GHS03","GHS04","GHS05","GHS06","GHS07","GHS08","GHS09"]',NULL,'["kit","componente"]'),
('epp_requerido','EPP Requerido','Equipo de protección personal recomendado','string','many','seguridad','activo','additionalProperty.PPE',NULL,'["Gafas","Guantes","Bata","Mascarilla","Protección auditiva"]',NULL,'["kit","componente"]');

-- d) Empaque/Logística (kits/componentes)
INSERT INTO `atributos_definiciones`
(`clave`,`etiqueta`,`descripcion`,`tipo_dato`,`cardinalidad`,`grupo`,`estado`,`schema_propiedad`,`unidad_defecto`,`opciones_json`,`unidades_permitidas_json`,`aplica_a_json`)
VALUES
('contenido_piezas','Contenido (piezas)','Número total de piezas incluidas','integer','one','empaque','activo','Product.numberOfItems',NULL,NULL,NULL,'["kit","componente"]'),
('peso_empaque','Peso con empaque','Peso total para envío','number','one','empaque','activo','Product.shippingWeight','KGM',NULL,'["KGM","GRM"]','["kit","componente"]'),
('condiciones_almacenamiento','Condiciones de almacenamiento','Recomendaciones de almacenamiento','string','one','empaque','activo','additionalProperty.storageConditions',NULL,NULL,NULL,'["kit","componente"]');

-- =============================================================
-- Mapeo por entidad (kits y componentes primero). No incluimos campos core duplicados.
-- =============================================================

-- Obtener IDs de atributos recién creados (ejemplo orientativo: el integrador puede ajustar órdenes)
-- Nota: en ejecución manual, puede usarse SELECT para ver IDs; aquí asumimos inserts consecutivos.

-- Kits
INSERT INTO `atributos_mapeo` (`atributo_id`,`tipo_entidad`,`requerido`,`visible`,`orden`,`ui_hint`,`buscable`,`facetable`)
SELECT id, 'kit', 0, 1,
CASE `clave`
  WHEN 'material' THEN 10
  WHEN 'color' THEN 20
  WHEN 'peso' THEN 30
  WHEN 'alto' THEN 40
  WHEN 'ancho' THEN 50
  WHEN 'largo' THEN 60
  WHEN 'volumen' THEN 70
  WHEN 'pais_fabricacion' THEN 80
  WHEN 'garantia_meses' THEN 90
  WHEN 'tension_v' THEN 110
  WHEN 'corriente_a' THEN 120
  WHEN 'potencia_w' THEN 130
  WHEN 'polaridad' THEN 140
  WHEN 'norma_certificacion' THEN 210
  WHEN 'edad_segura_min' THEN 220
  WHEN 'edad_segura_max' THEN 230
  WHEN 'pictogramas_ghs' THEN 240
  WHEN 'epp_requerido' THEN 250
  WHEN 'contenido_piezas' THEN 310
  WHEN 'peso_empaque' THEN 320
  WHEN 'condiciones_almacenamiento' THEN 330
  ELSE 999 END,
CASE `clave`
  WHEN 'material' THEN 'tags'
  WHEN 'pictogramas_ghs' THEN 'tags'
  WHEN 'epp_requerido' THEN 'tags'
  WHEN 'peso' THEN 'quantitative'
  WHEN 'alto' THEN 'quantitative'
  WHEN 'ancho' THEN 'quantitative'
  WHEN 'largo' THEN 'quantitative'
  WHEN 'volumen' THEN 'quantitative'
  WHEN 'peso_empaque' THEN 'quantitative'
  ELSE 'input' END,
CASE `clave`
  WHEN 'material' THEN 1
  WHEN 'tension_v' THEN 1
  WHEN 'corriente_a' THEN 1
  WHEN 'potencia_w' THEN 1
  WHEN 'polaridad' THEN 1
  ELSE 0 END AS buscable,
CASE `clave`
  WHEN 'material' THEN 1
  WHEN 'tension_v' THEN 1
  WHEN 'polaridad' THEN 1
  ELSE 0 END AS facetable
FROM `atributos_definiciones` WHERE `aplica_a_json` LIKE '%"kit"%';

-- Componentes
INSERT INTO `atributos_mapeo` (`atributo_id`,`tipo_entidad`,`requerido`,`visible`,`orden`,`ui_hint`,`buscable`,`facetable`)
SELECT id, 'componente', 0, 1,
CASE `clave`
  WHEN 'material' THEN 10
  WHEN 'color' THEN 20
  WHEN 'peso' THEN 30
  WHEN 'alto' THEN 40
  WHEN 'ancho' THEN 50
  WHEN 'largo' THEN 60
  WHEN 'volumen' THEN 70
  WHEN 'pais_fabricacion' THEN 80
  WHEN 'garantia_meses' THEN 90
  WHEN 'tension_v' THEN 110
  WHEN 'corriente_a' THEN 120
  WHEN 'potencia_w' THEN 130
  WHEN 'polaridad' THEN 140
  WHEN 'norma_certificacion' THEN 210
  WHEN 'edad_segura_min' THEN 220
  WHEN 'edad_segura_max' THEN 230
  WHEN 'pictogramas_ghs' THEN 240
  WHEN 'epp_requerido' THEN 250
  WHEN 'contenido_piezas' THEN 310
  WHEN 'peso_empaque' THEN 320
  WHEN 'condiciones_almacenamiento' THEN 330
  ELSE 999 END,
CASE `clave`
  WHEN 'material' THEN 'tags'
  WHEN 'pictogramas_ghs' THEN 'tags'
  WHEN 'epp_requerido' THEN 'tags'
  WHEN 'peso' THEN 'quantitative'
  WHEN 'alto' THEN 'quantitative'
  WHEN 'ancho' THEN 'quantitative'
  WHEN 'largo' THEN 'quantitative'
  WHEN 'volumen' THEN 'quantitative'
  WHEN 'peso_empaque' THEN 'quantitative'
  ELSE 'input' END,
CASE `clave`
  WHEN 'material' THEN 1
  WHEN 'tension_v' THEN 1
  WHEN 'corriente_a' THEN 1
  WHEN 'potencia_w' THEN 1
  WHEN 'polaridad' THEN 1
  ELSE 0 END AS buscable,
CASE `clave`
  WHEN 'material' THEN 1
  WHEN 'tension_v' THEN 1
  WHEN 'polaridad' THEN 1
  ELSE 0 END AS facetable
FROM `atributos_definiciones` WHERE `aplica_a_json` LIKE '%"componente"%';

-- Fin del script.
